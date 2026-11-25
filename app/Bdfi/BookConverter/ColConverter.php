<?php

namespace App\Bdfi\BookConverter;

use App\Enums\IsNovelization;

/**
 * Conversion d'un fichier COL (format maison, CP437, colonnes fixes) vers le JSON canonique.
 *
 * Structure d'une publication dans le fichier :
 *     o|+  ligne support (sigle de collection, n°, date, ISBN)      [¡ devant = jamais paru]
 *     }    couverture (image, illustrateur de couverture, dessinateurs)
 *     !/>  commentaires (infos techniques "Poche - 108x178 - 384 pages - DL ... - AI ...", notes)
 *     x    retirage(s) : date + ses propres "}" et commentaires
 *     -    contenu principal (genre, type, auteur, titre ■ VO ■ Trad.)
 *     &    auteur(s) supplémentaire(s) en collaboration
 *     : » = )   textes inclus (sommaire)
 *
 * extract()   : fichier -> une ligne brute par publication ('o' ou '+'), retirages inclus
 * buildBook() : ligne brute -> livre canonique (voir AbstractBookConverter::$jsonSchema)
 *
 * Clés ajoutées au JSON canonique par ce format :
 *   collection_sigle, groupe_edition / rang_edition / est_reedition, retirages[],
 *   pages_dpu, pages_dpi, pagination, prix, code_prix
 */
class ColConverter extends AbstractBookConverter
{
    use ConvertsBookFields;

    private const INFO_KEYWORDS = [
        'pages', ' mm ', ' cm ', 'euros', ' nf', 'francs', 'centimes', 'jaquette', 'relié',
        'christian', 'gilles', 'gallica', 'cpri', 'poche', 'imprim', 'rabat',
    ];

    /** sigle => [éditeur, collection], lus dans les lignes "_ SIGLE Éditeur - Collection [■ années]" */
    private array $collectionNames = [];

    public static function extensions(): array
    {
        return ['col'];
    }

    // =====================================================================
    // Extraction : fichier -> une ligne brute par publication
    // =====================================================================

    protected function extract(string $source): array
    {
        $this->collectionNames = [];

        $rows = [];
        $unit = null;                 // publication en cours
        $inRetirage = false;          // les "}" et commentaires suivants concernent le dernier retirage
        $lastIncluded = false;        // les lignes "&" complètent le dernier texte inclus (sinon le contenu principal)
        $group = 0;
        $rank = 0;
        $prevKey = null;
        $originIsbn = '';             // ISBN de l'ouvrage d'origine du groupe d'éditions en cours

        foreach (explode("\n", $this->readSource($source)) as $i => $line) {
            $lineNo = $i + 1;

            if (trim($line) === '') {
                continue;
            }

            $first = mb_substr($line, 0, 1);

            if ($first === '_') {                      // sigle de collection : "_ PO_TERR Pocket - Terreur"
                $this->readCollectionName($line);
                continue;
            }

            $notPublished = ($first === '¡');           // jamais paru (ou numéro inexistant)
            if ($notPublished) {
                $line = mb_substr($line, 1);
                $first = mb_substr($line, 0, 1);
            }

            // ---- Nouvelle publication : ouvrage ou réédition ----
            if ($first === 'o' || $first === '+') {
                $this->finishUnit($unit, $rows);

                $unit = $this->newUnit($line, $first, $notPublished, $lineNo);

                // Groupe d'éditions : un "o" ouvre un groupe ; les "+" qui suivent avec le même numéro le complètent
                // (le sigle peut changer d'une réédition à l'autre : collection renommée)
                if ($first === 'o' || $prevKey !== $unit['num']) {
                    $group++;
                    $rank = ($first === 'o') ? 0 : 1;
                    // ISBN de l'édition d'origine : un vrai ISBN seulement (pas "-" ni "?")
                    $originIsbn = ($first === 'o' && !in_array($unit['isbn'], ['-', '?'], true)) ? $unit['isbn'] : '';
                } else {
                    $rank++;
                }
                $prevKey = $unit['num'];

                $unit['groupe_edition'] = $group;
                $unit['rang_edition'] = $rank;
                $unit['isbn_origine'] = ($first === 'o') ? '' : $originIsbn;
                $inRetirage = false;
                $lastIncluded = false;
                continue;
            }

            if ($unit === null) {
                if ($first === '!' || $first === '>') {
                    continue;                           // en-tête du fichier
                }
                throw new \RuntimeException("Ligne {$lineNo} : ligne \"{$first}\" avant la première référence (o).");
            }

            switch ($first) {
                case 'x':                               // retirage
                    $unit['retirages'][] = [
                        'annee'  => trim(mb_substr($line, 17, 4)),
                        'mois'   => trim(mb_substr($line, 22, 2)),
                        'infos'  => $this->defaultInfos(),
                    ];
                    $inRetirage = true;
                    break;

                case '/':                               // numéro double ou triple
                    $unit['num'] .= '-' . trim(mb_substr($line, 10, 5));
                    break;

                case '}':                               // couverture (celles des retirages sont identiques : ignorées)
                    if (!$inRetirage) {
                        $unit['couverture'] = $this->parseCover($line);
                    }
                    break;

                case '!':
                case '>':
                    // Seuls les commentaires placés AVANT la ligne de contenu ("-") appartiennent à la publication
                    if ($unit['contenu'] === null) {
                        $this->readComment($line, $unit, $inRetirage);
                    }
                    break;

                case '-':                               // contenu principal
                    $unit['contenu'] = $this->parseReference($line);
                    $inRetirage = false;
                    $lastIncluded = false;
                    break;

                case ':':
                case '»':
                case '=':
                case ')':                               // texte inclus (sommaire)
                    $ref = $this->parseReference($line);
                    $ref['niveau'] = ($first === '=') ? 2 : 1;
                    $unit['inclus'][] = $ref;
                    $lastIncluded = true;
                    break;

                case '&':                               // auteur supplémentaire (collaboration)
                    $credit = ['credit' => trim(mb_substr($line, 18, 27)), 'role' => $this->authorRole($line)];
                    if ($lastIncluded && $unit['inclus'] !== []) {
                        $unit['inclus'][array_key_last($unit['inclus'])]['auteurs'][] = $credit;
                    } elseif ($unit['contenu'] !== null) {
                        $unit['contenu']['auteurs'][] = $credit;
                    } else {
                        throw new \RuntimeException("Ligne {$lineNo} : auteur en collaboration (&) sans référence.");
                    }
                    break;

                default:
                    throw new \RuntimeException("Ligne {$lineNo} : descripteur \"{$first}\" inconnu.");
            }
        }

        $this->finishUnit($unit, $rows);

        return $rows;
    }

    private function finishUnit(?array $unit, array &$rows): void
    {
        if ($unit === null) {
            return;
        }

        if ($unit['contenu'] === null) {
            throw new \RuntimeException("Ligne {$unit['ligne']} : publication sans ligne de contenu (-).");
        }

        // Feuilleton complet ("SERIAL") : ce n'est pas une publication
        if ($unit['serial']) {
            return;
        }

        $rows[] = $unit;
    }

    private function readCollectionName(string $line): void
    {
        if (preg_match('/^_\s+(\S+)\s+(.+)$/u', $line, $m)) {
            $this->collectionNames[trim($m[1])] = $this->splitEditeurCollection(trim($m[2]));
        }
    }

    /**
     * "Éditeur - Collection ■ années d'activité" => [éditeur, collection] ; sans " - " : éditeur seul,
     * sans collection dédiée (le sigle désigne alors directement un éditeur, cf. buildBook()).
     * @return array{0: string, 1: string}
     */
    private function splitEditeurCollection(string $value): array
    {
        $value = trim(explode('■', $value, 2)[0]);
        $pos = mb_strpos($value, ' - ');

        if ($pos === false) {
            return [$value, ''];
        }

        return [trim(mb_substr($value, 0, $pos)), trim(mb_substr($value, $pos + 3))];
    }

    private function newUnit(string $line, string $kind, bool $notPublished, int $lineNo): array
    {
        $isbn = trim(mb_substr($line, 36, 17));
        // "ISBN -" : l'ouvrage n'a pas d'ISBN => "-" (affiché n/a).
        // ISBN incomplet ou inconnu (".-.....-...-.", "?"...), ou zone ISBN mal placée => "?" (ISBN inconnu).
        if (mb_substr($line, 31, 4) === 'ISBN' && $isbn === '-') {
            $isbn = '-';
        } elseif (mb_substr($line, 31, 4) !== 'ISBN' || $isbn === '' || str_starts_with($isbn, '-')
            || str_contains($isbn, '?') || str_starts_with($isbn, '.') || str_ends_with($isbn, '.')) {
            $isbn = '?';
        }

        $num = trim(mb_substr($line, 10, 5));

        return [
            'kind'           => $kind,
            'ligne'          => $lineNo,
            'non_paru'       => $notPublished,
            'serial'         => false,
            'sigle'          => trim(mb_substr($line, 2, 7)),
            'num'            => $num === '?' ? '' : $num,
            'annee'          => trim(mb_substr($line, 17, 4)),
            'mois'           => trim(mb_substr($line, 22, 2)),
            'isbn'           => $isbn,
            'couverture'     => null,
            'infos'          => $this->defaultInfos(),
            'description'    => '',
            'notes'          => '',
            'serie_imprimee' => '',
            'retirages'      => [],
            'contenu'        => null,
            'inclus'         => [],
        ];
    }

    // ---------------------------------------------------------------- Couverture

    /** "} pp5766 _Illustrateur couv   _Dessinateurs" ("_N" = pas d'illustrations intérieures => "n/a" ; "?" = inconnu) */
    private function parseCover(string $line): array
    {
        $scan = trim(mb_substr($line, 1, 16));
        $scan = preg_replace('/\.jpg$/i', '', $scan);
        if ($scan === '?') {
            $scan = 'noimg';
        }

        $rest = mb_substr($line, 18);
        $illustrator = trim($rest);
        $designers = '';

        // Le second champ ("_N", "_Nom" ou "?") est séparé du premier par au moins 2 espaces
        if (preg_match('/^(.*?)\s{2,}(_.*|\?)\s*$/u', $rest, $m)) {
            $illustrator = trim($m[1]);
            $designers = match ($m[2]) {
                '_N'    => 'n/a',   // pas d'illustrations intérieures
                '?'     => '',      // inconnu
                default => trim(ltrim($m[2], '_')),
            };
        }

        return [
            'image'        => $scan,
            'illustrateur' => $illustrator === '?' ? '' : $illustrator,
            'dessinateurs' => $designers,
        ];
    }

    // ---------------------------------------------------------------- Commentaires

    private function readComment(string $line, array &$unit, bool $inRetirage): void
    {
        // Nom de série tel qu'imprimé sur le livre : "!--- SERIE : Nom - n"
        if (!$inRetirage && str_starts_with($line, '!--- SERIE : ')) {
            $unit['serie_imprimee'] = trim(mb_substr($line, 13));

            return;
        }

        if (str_contains($line, 'SERIAL')) {
            $unit['serial'] = true;                     // feuilleton : non géré

            return;
        }

        if ($this->isInfoComment($line)) {
            $infos = $this->parseInfos($line);

            if ($inRetirage) {
                $unit['retirages'][array_key_last($unit['retirages'])]['infos'] = $infos;
            } else {
                $unit['infos'] = $infos;
            }

            return;
        }

        if ($inRetirage) {
            return;                                     // autres commentaires de retirage : ignorés
        }

        // ">---" = commentaire affichable (information) ; "!---" = note privée ; ">--." = suite sans saut de ligne
        $text = trim(preg_replace('/^[>!]-+\.*\s*/u', '', $line));
        if ($text === '') {
            return;
        }

        $field = (mb_substr($line, 0, 1) === '>') ? 'description' : 'notes';
        $separator = (mb_substr($line, 3, 1) === '.') ? ' ' : '<br />';
        $unit[$field] = ($unit[$field] === '') ? $text : $unit[$field] . $separator . $text;
    }

    private function isInfoComment(string $line): bool
    {
        if (!str_contains($line, ' - ')) {
            return false;
        }

        $lower = mb_strtolower($line);
        foreach (self::INFO_KEYWORDS as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function defaultInfos(): array
    {
        return [
            'verif' => false, 'verifpar' => [], 'dl' => '', 'ai' => '', 'printer' => '', 'support' => 'papier',
            'codeprix' => '', 'prix' => '', 'dpu' => '', 'dpi' => '', 'pto' => '', 'pages' => '', 'age' => '',
            'cible' => 'inconnu', 'dim' => '', 'format' => 'inconnu', 'relie' => false, 'jaquette' => false, 'rabat' => false,
        ];
    }

    /** "!--- Poche - 108x178 - 384 pages - DL 09/2002 - AI Aug-02 - IMPRIM Bussière, ..." */
    private function parseInfos(string $comment): array
    {
        $r = $this->defaultInfos();
        $comment = preg_replace('/^[>!]-+\.*\s*/u', '', $comment);

        foreach (explode(' - ', $comment) as $data) {
            $data = trim($data);
            $upper = mb_strtoupper($data);
            $lower = mb_strtolower($data);

            if (in_array($upper, ['CHRISTIAN', 'GILLES', 'LAURENT', 'GALLICA'], true)) {
                $r['verif'] = true;
                $r['verifpar'][] = ucfirst(strtolower($upper));
            } elseif (str_starts_with($upper, 'DL')) {
                $r['dl'] = trim(preg_replace('/^DL\s+/iu', '', $data));
            } elseif (str_starts_with($upper, 'AI')) {
                $r['ai'] = trim(preg_replace('/^AI\s+/iu', '', $data));
            } elseif (str_starts_with($upper, 'IMPRIM')) {
                $r['printer'] = trim(preg_replace('/^IMPRIM\s*/iu', '', $data));
            } elseif (str_contains($lower, 'audio')) {
                $r['support'] = 'audio';
            } elseif (str_contains($lower, 'numérique')) {
                $r['support'] = 'numerique';
            } elseif (str_contains($upper, 'CPRI')) {
                $r['codeprix'] = trim(preg_replace('/CPRIX?\s*/u', '', $data));
            } elseif (str_contains($lower, 'euros')
                || preg_match('/\b(centimes|francs?|frs?\.?|nf)\b/iu', $data)) {
                $r['prix'] = $data;
            } elseif (str_contains($lower, 'pages')) {
                $pages = trim(preg_replace('/\s*pages?/iu', '', $data));
                if ($r['verif']) {
                    $r['dpu'] = $pages;                 // pagination d'un exemplaire vérifié
                } else {
                    $r['pages'] = $pages;
                }
            } elseif (str_contains($upper, 'DPI')) {
                $r['dpi'] = trim(str_replace('DPI', '', $upper));      // dernier numéro de page visible
            } elseif (str_contains($upper, 'PTO')) {
                $r['pto'] = trim(str_replace('PTO', '', $upper));      // pagination totale
            } elseif (str_contains($data, '18+')) {
                $r['cible'] = 'adulte';
                $r['age'] = '18+';
            } elseif (preg_match('/^(YA|ado)\b\s*(\S*)/iu', $data, $m)) {
                $r['cible'] = 'YA';
                $r['age'] = $m[2];
            } elseif (preg_match('/^jeunesse\b\s*(\S*)/iu', $data, $m)) {
                $r['cible'] = 'jeunesse';
                $r['age'] = $m[1];
            } elseif ($dim = $this->parseDimensions($data)) {
                $r['dim'] = $dim;
            } else {
                if (str_contains($lower, 'poche')) {
                    $r['format'] = 'poche';
                }
                if (preg_match('/\bMF\b/u', $upper)) {
                    $r['format'] = 'mf';
                }
                if (preg_match('/\bGF\b/u', $upper)) {
                    $r['format'] = 'gf';
                }
                if (str_contains($lower, 'broch')) {
                    $r['relie'] = false;
                }
                if (str_contains($lower, 'reli')) {
                    $r['relie'] = true;
                }
                if (str_contains($lower, 'jaquette')) {
                    $r['jaquette'] = true;
                }
                if (str_contains($lower, 'rabat')) {
                    $r['rabat'] = true;
                }
            }
        }

        return $r;
    }

    /** "108x178", "DIM 108x178", "10,8 x 17,8 cm", "178 mm" => "108 x 178" (en mm) ; '' si ce n'est pas une dimension */
    private function parseDimensions(string $data): string
    {
        $data = trim(preg_replace('/^DIM\s+/iu', '', $data));
        $num = '(\d+(?:[.,]\d+)?)';

        if (preg_match("/^{$num}\s*x\s*{$num}\s*(mm|cm)?$/iu", $data, $m)) {
            $unit = (strtolower($m[3] ?? '') === 'cm') ? 10 : 1;

            return $this->mm($m[1], $unit) . ' x ' . $this->mm($m[2], $unit);
        }

        if (preg_match("/^{$num}\s*(mm|cm)$/iu", $data, $m)) {
            return (string) $this->mm($m[1], strtolower($m[2]) === 'cm' ? 10 : 1);
        }

        return '';
    }

    private function mm(string $value, int $unit): string
    {
        $n = (float) str_replace(',', '.', $value) * $unit;

        return rtrim(rtrim(number_format($n, 1, '.', ''), '0'), '.');
    }

    // ---------------------------------------------------------------- Ligne de contenu

    /** Ligne "-", ":", "»", "=" ou ")" : genre, type, auteur, titre ■ VO ■ Trad. */
    private function parseReference(string $line): array
    {
        $type = mb_substr($line, 11, 1);
        $stype = mb_substr($line, 12, 1);
        $zone = mb_substr($line, 46);

        $parts = explode('■', $zone);

        // Titre, alias de recueil et cycles (règles de decomp_titre du Perl, cf. ColTitle)
        $decomposed = ColTitle::decompose(trim($parts[0]));
        $titre = $decomposed['titre'];
        $cycles = $decomposed['cycles'];
        [$copyright, $writtenYear, $titreVo] = $this->splitVo(trim($parts[1] ?? ''));
        // Série principale = le cycle le plus précis (sous-sous-cycle, sinon sous-cycle, sinon cycle)
        $cycle = $cycles[0]['nom'] ?? '';
        $cycleIndice = $cycles[0]['numero'] ?? '';

        return [
            'hg'           => mb_substr($line, 3, 1),
            'g1'           => mb_substr($line, 4, 1),
            'g2'           => mb_substr($line, 5, 1),
            'type'         => $type === '' ? ' ' : $type,
            'stype'        => $stype === '' ? ' ' : $stype,
            'page'         => preg_replace('/[=_kcgpi]$/u', '', trim(mb_substr($line, 6, 5))),
            'auteurs'      => [['credit' => trim(mb_substr($line, 18, 27)), 'role' => $this->authorRole($line)]],
            'titre'        => $titre,
            'cycle'        => $cycle,
            'cycle_indice' => $cycleIndice,
            'cycles'       => $cycles,
            'titre_vo'     => str_replace('"', "'", $titreVo),
            'copyright'    => $copyright,
            'ecriture'     => $writtenYear,
            'traducteurs'  => trim(preg_replace('/^Trad\.\s*/u', '', trim($parts[2] ?? ''))),
        ];
    }

    /** "*" devant l'auteur = anthologiste (directeur de l'ouvrage) */
    private function authorRole(string $line): string
    {
        return mb_substr($line, 17, 1) === '*' ? 'editor' : 'author';
    }

    /** "Nom du cycle - 3" => ["Nom du cycle", "3"] */
    private function splitCycle(string $value): array
    {
        $value = trim($value);
        $pos = mb_strrpos($value, ' - ');

        if ($pos === false) {
            return [$value, ''];
        }

        return [trim(mb_substr($value, 0, $pos)), trim(mb_substr($value, $pos + 3))];
    }

    /** "1985-1993 The Door to December" / "[1950]1988 Titre" / "1974" => [copyright, année d'écriture, titre VO] */
    private function splitVo(string $vo): array
    {
        if (!preg_match('/^(?:\[(\d{4})\]\s*)?(?:(\d{4})\s*-\s*)?(\d{4}|\?{4})?\s*(.*)$/us', $vo, $m)) {
            return ['', '', $vo];
        }

        $copyright = $m[3] ?? '';

        return [$copyright === '????' ? '' : $copyright, $m[1] ?? '', trim($m[4] ?? '')];
    }

    // =====================================================================
    // Construction du livre canonique
    // =====================================================================

    protected function buildBook(array $raw): array
    {
        $content = $raw['contenu'];
        $infos = $raw['infos'];
        $approx = $this->approximateParution($raw['annee'], $raw['mois']);
        $cover = $raw['couverture'] ?? ['image' => '', 'illustrateur' => '', 'dessinateurs' => ''];

        $authors = $this->toAuthors($content['auteurs']);

        [$editeur, $collection] = $this->collectionNames[$raw['sigle']] ?? ['', ''];

        // Série du livre : nom imprimé (commentaire "!--- SERIE :") sinon cycle du texte
        [$printedSerie, $printedNum] = $this->splitCycle($raw['serie_imprimee']);
        $serie = $printedSerie !== '' ? $printedSerie : $content['cycle'];
        $numSerie = $printedSerie !== '' ? $printedNum : $content['cycle_indice'];

        // Sommaire : le contenu principal (niveau 0), puis les textes inclus
        $sommaire = [$this->buildText($content, $authors, 0)];
        foreach ($raw['inclus'] as $included) {
            $sommaire[] = $this->buildText($included, $this->toAuthors($included['auteurs']), $included['niveau']);
        }

        return [
            // éditeur & collection : noms lus dans l'en-tête ("_ SIGLE ...") à titre informatif ;
            // l'import réel résout plutôt le sigle en base (collections/publishers.sigle_bdfi)
            'editeur' => $editeur,
            'collection' => $collection,
            'collection_sigle' => $raw['sigle'],
            'no_collection' => $raw['num'],

            'auteurs' => $authors,

            'titre' => $content['titre'],
            'status' => $raw['non_paru'] ? 'abandon' : 'paru',
            'serie' => $serie,
            'num_serie' => $numSerie,

            'isbn' => $raw['isbn'],

            'illustrateur_couv' => $cover['illustrateur'],
            'illustrateurs' => $cover['dessinateurs'],
            'information' => $raw['description'],
            'private' => $raw['notes'],

            ...$this->getCoverImages($this->clean($cover['image'])),

            'reliure' => $infos['relie'] ? 'oui' : 'non',
            'jaquette' => $infos['jaquette'] ? 'oui' : 'non',
            'rabat' => $infos['rabat'] ? 'oui' : 'non',

            'est_verifie' => $infos['verif'] ? 'oui' : 'non',
            'verifie_par' => implode('; ', $infos['verifpar']),

            'dl' => $this->convertDlAi($infos['dl'], $approx),
            'ai' => $this->convertDlAi($infos['ai'], $approx),

            'dimensions' => $infos['dim'],
            'imprimeur' => $infos['printer'],

            'approximate_pages' => $infos['pages'],
            'pages_dpu' => $infos['dpu'],
            'pages_dpi' => $infos['dpi'],
            'pagination' => $infos['pto'],
            'approximate_parution' => $approx,
            'prix' => $infos['prix'],
            'code_prix' => $infos['codeprix'],

            'support' => $infos['support'],
            'format' => $infos['format'],
            'type_ouvrage' => $this->pubType($content['type'], $content['stype']),

            'est_genre' => $this->pubHg($content['hg']),
            'genre' => $this->genreStat($content['hg'], $content['g1'], $content['g2']),
            'audience' => $infos['cible'],
            'age' => $infos['age'],

            // Éditions : "o" = édition d'origine (rang 0), "+" = réédition (rang 1, 2...) ; même groupe = même ouvrage
            'est_reedition' => $raw['kind'] === '+',
            'groupe_edition' => $raw['groupe_edition'],
            'rang_edition' => $raw['rang_edition'],
            'edition_originale_isbn' => $raw['isbn_origine'],

            // Retirages : table "reprint" (date, achevé d'imprimer, vérification)
            'retirages' => array_map(fn (array $x) => [
                'approximate_parution' => $this->approximateParution($x['annee'], $x['mois']),
                'ai' => $this->convertDlAi($x['infos']['ai'], $this->approximateParution($x['annee'], $x['mois'])),
                'est_verifie' => $x['infos']['verif'] ? 'oui' : 'non',
                'verifie_par' => implode('; ', $x['infos']['verifpar']),
            ], $raw['retirages']),

            'sommaire' => $sommaire,
        ];
    }

    /**
     * Niveau dans le sommaire : 0 = contenu principal, 1 = textes inclus, 2 = sommaire d'un texte inclus.
     * Une section (regroupement intermédiaire, visible dans le sommaire complet mais pas dans les
     * bibliographies) se place un demi-niveau au-dessus des textes qu'elle regroupe : 0.5, ou 1.5 au niveau 2.
     */
    private function buildText(array $ref, array $authors, int $level): array
    {
        $type = $this->textType($ref['type'], $ref['stype'], $ref['hg']);

        if ($type === 'section' && $level > 0) {
            $level -= 0.5;
        }

        return [
            'titre' => $ref['titre'],
            'auteurs' => $authors,
            'titre_vo' => $ref['titre_vo'],
            'copyright' => $ref['copyright'],
            'serie' => $ref['cycle'],
            'num_serie' => $ref['cycle_indice'],
            // Tous les cycles du texte (sous-cycles compris), du plus précis au plus général
            'series' => array_map(fn ($c) => [
                'serie'     => $c['nom'],
                'num_serie' => $c['numero'],
                'parent'    => $c['parent'],   // cycle parent (hiérarchie COL), '' si aucun
            ], $ref['cycles'] ?? []),
            'type_texte' => $type,
            'est_genre' => $this->textHg($ref['hg']),
            'novellisation' => $this->novelization($ref['type']),
            'genre' => $this->genreStat($ref['hg'], $ref['g1'], $ref['g2']),
            'traducteurs' => $ref['traducteurs'],
            'niveau' => $level,
            'page' => $ref['page'],
        ];
    }

    /** "AAAA-MM-00" ("00" = mois inconnu) ; vide si l'année est absente ou invalide */
    private function approximateParution(string $year, string $month): string
    {
        if (!preg_match('/^\d{4}$/', $year)) {
            return '';
        }

        $month = (ctype_digit($month) && (int) $month >= 1 && (int) $month <= 12)
            ? str_pad((string) (int) $month, 2, '0', STR_PAD_LEFT)
            : '00';

        return "{$year}-{$month}-00";
    }

    // ---------------------------------------------------------------- Auteurs

    /**
     * Auteurs bruts ("NOM Prénom") => format canonique. Les "****" (anonyme) et "?" sont ignorés.
     * @return array<int, array{auteur_prenom: string, auteur_nom: string, auteur_role: string}>
     */
    private function toAuthors(array $credits): array
    {
        $authors = [];

        foreach ($credits as $c) {
            $name = $this->splitName($c['credit']);
            if ($name === null) {
                continue;
            }

            $authors[] = [
                'auteur_prenom' => $name['prenom'],
                'auteur_nom' => $name['nom'],
                'auteur_role' => $c['role'],
            ];
        }

        return $authors;
    }

    /**
     * "KOONTZ Dean R." => nom "KOONTZ", prénom "Dean R." : le nom est la suite de mots en capitales au début
     * (un seul mot "M." ou initiale termine le nom ; "McCAMMON" est un nom). Sans capitales : tout en nom.
     */
    private function splitName(string $credit): ?array
    {
        $credit = trim($credit);

        if ($credit === '' || trim($credit, '*') === '' || $credit === '?') {
            return null;
        }

        $tokens = preg_split('/\s+/u', $credit);
        $count = 0;

        foreach ($tokens as $token) {
            // Une initiale ("G", "G.") ou des initiales composées ("G.-J.", "J.R.R.") commencent le prénom
            if (preg_match('/^\p{L}\.?$/u', $token) || preg_match('/^\p{L}\.(?:-?\p{L}\.)+$/u', $token)
                || !$this->isSurnameToken($token)) {
                break;
            }
            $count++;
        }

        if ($count === 0) {
            return ['nom' => $credit, 'prenom' => ''];
        }

        return [
            'nom' => self::surnameCase(implode(' ', array_slice($tokens, 0, $count))),
            'prenom' => implode(' ', array_slice($tokens, $count)),
        ];
    }

    /**
     * Les capitales du COL ne servent qu'à délimiter le nom : en base, casse normale.
     * Chaque suite de capitales garde sa première lettre et passe le reste en minuscules :
     * "MASTERTON" => "Masterton", "McCAMMON" => "McCammon", "O'KEEFE" => "O'Keefe",
     * "LE GUIN" => "Le Guin", "SAINT-EXUPÉRY" => "Saint-Exupéry".
     * Une particule qui n'est pas en tête du nom passe en minuscules : "FAIVRE D'ARCIER" => "Faivre d'Arcier",
     * "DE LA MARE" => "De la Mare" (en tête, elle garde sa majuscule).
     */
    public static function surnameCase(string $name): string
    {
        $name = preg_replace_callback('/\p{Lu}{2,}/u',
            fn ($m) => mb_substr($m[0], 0, 1) . mb_strtolower(mb_substr($m[0], 1)),
            $name);

        $words = explode(' ', $name);

        foreach ($words as $i => &$word) {
            if ($i === 0) {
                continue;
            }

            if (in_array(mb_strtolower($word), self::PARTICLES, true)) {
                $word = mb_strtolower($word);
            } elseif (preg_match("/^([DL])'(.+)$/u", $word, $m)) {   // D'Arcier => d'Arcier, L'Isle => l'Isle
                $word = mb_strtolower($m[1]) . "'" . $m[2];
            }
        }

        return implode(' ', $words);
    }

    /** Particules mises en minuscules quand elles ne sont pas en tête du nom (cf. surnameCase) */
    private const PARTICLES = ['de', 'des', 'du', 'la', 'le', 'les', 'van', 'von', 'der', 'den', 'ten', 'ter', 'da', 'di', 'del', 'della', 'dos', 'das'];

    private function isSurnameToken(string $token): bool
    {
        if (!preg_match('/\p{L}/u', $token)) {
            return false;
        }

        return $token === mb_strtoupper($token) || preg_match("/^Mc\p{Lu}[\p{Lu}'\-]*$/u", $token) === 1;
    }

    // ---------------------------------------------------------------- Types et genres

    /** Type d'ouvrage (publications.type) d'après le type et le sous-type du contenu principal */
    private function pubType(string $type, string $stype): string
    {
        if ($stype === ' ' || $stype === 'a') {
            return match (true) {
                in_array($type, ['T', 'R', 'U', 'F', 'S', 'V'], true) => 'roman',
                in_array($type, ['P', 'r', 'N', 'O', 'X'], true) => 'fiction',
                in_array($type, ['l', 'a', 'E', 'b', 'B', 'G', 'Z', 'J'], true) => 'non-fiction',
                $type === 'M' => 'periodique',
                default => '',
            };
        }

        return match (true) {
            $type === 'R' && $stype !== 'N' => 'omnibus',
            in_array($type, ['P', 'T', 'R', 'r', 'N', 'n', 'C', 'A', 'Y'], true) => 'compilation',
            $type === 'M' => 'periodique',
            $type === 'U' => 'roman',
            $type === 'E' || $type === 'G' => $stype === 'N' ? 'compilation' : 'non-fiction',
            default => '',
        };
    }

    /** Type de texte (titles.type) */
    private function textType(string $type, string $stype, string $hg): string
    {
        $plain = ($stype === ' ');

        return match (true) {
            $type === '.' => 'inconnu',
            $type === 'p' && $plain => 'preface',
            $type === 'o' && $plain => 'postface',
            $type === 'P' && $plain => 'poem',
            $type === 'l' && $plain => 'letter',
            $type === 'T' && $plain => 'theatre',
            $type === 'H' && $plain => 'radio',
            $type === 'O' && $plain => 'scenario',
            $type === 'a' && $plain => 'article',
            $type === 'R' && ($plain || $stype === 'a') => 'novel',
            $type === 'r' && ($plain || $stype === 'a') => 'novella',
            $type === 'N' && $plain => 'shortstory',
            $type === 'u' && $plain => 'fix-up-element',
            $type === 'n' && $plain => 'shortshort',
            $type === 'E' => 'essai',
            $type === 'c' => 'song',
            $type === 'b' && $plain => 'bio',
            $type === 'B' && $plain => 'biblio',
            $type === 'C' => $hg === 'C' ? 'section' : 'chroniques',
            $type === 'U' => 'fix-up',
            $type === 'G' && $plain => 'guide',     // guide (G) ; compilation de guides (G4...) => omnibus
            $type === 'G' => 'omnibus',
            in_array($type, ['F', 'S', 'V'], true) && $plain => 'novel',
            $type === 'A' && !$plain => 'anthologie',
            in_array($type, ['N', 'n', 'R', 'r', 'C', 'Y', 'P', 'T'], true) && !$plain && $stype !== 'a' => 'collection',
            $type === 'M' => 'magazine',
            $type === 'd' => 'comics',
            $type === 'Z' => 'gamebook',
            $type === 'J' => 'game',
            $type === '?' => 'inconnu',
            $type === 'X' => 'novel',
            $type === 'x' => 'shortstory',
            $type === 'I' && $plain => 'article',
            default => '',
        };
    }

    /** Appartenance au genre : oui / partiel / non */
    private function pubHg(string $hg): string
    {
        return match ($hg) {
            'x', '!' => 'non',
            'p' => 'partiel',
            default => 'oui',
        };
    }

    /** Novellisation (titles.is_novelization) d'après le type du texte : F = film, S = série télé, V = jeu vidéo */
    private function novelization(string $type): string
    {
        return match ($type) {
            'F' => IsNovelization::FILM->value,
            'S' => IsNovelization::TV->value,
            'V' => IsNovelization::JEUX->value,
            default => IsNovelization::NON->value,
        };
    }

    /** Appartenance au genre d'un texte : oui / partiel / non / inconnu */
    private function textHg(string $hg): string
    {
        return match ($hg) {
            'x', '!' => 'non',
            '?' => 'inconnu',
            'p' => 'partiel',
            default => 'oui',
        };
    }

    private function genreStat(string $hg, string $g1, string $g2): string
    {
        if ($hg === 'x' || $hg === '!') {
            return 'mainstream';
        }

        foreach ([$g1, $g2] as $g) {
            $stat = match (true) {
                $g === 'S' => 'sf',
                $g === 'Y' => 'fantasy',
                in_array($g, ['F', 'K', 'M', 'N', 'O', 'V'], true) => 'fantastique',
                in_array($g, ['I', 'U'], true) => 'hybride',
                in_array($g, ['Z', 'G', 'T'], true) => 'autre',
                default => null,
            };

            if ($stat !== null) {
                return $stat;
            }
        }

        return 'inconnu';
    }
}