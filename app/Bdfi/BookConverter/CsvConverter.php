<?php

namespace App\Bdfi\BookConverter;

/**
 * Conversion d'un CSV (export Excel, séparateur tabulation) vers le JSON canonique.
 *
 * extract()   : fichier -> lignes brutes (tableaux indexés par nom de colonne)
 * buildBook() : ligne brute -> livre au format canonique (voir AbstractBookConverter::$jsonSchema)
 *
 * Colonnes du CSV volontairement non exploitées (pour l'instant) :
 *   Sexe, Sexe Il (genre auteur / illustrateur), tri, Imag/Blanche/Auto, O/R (type de tirage),
 *   Type, S Tr, PRIX (Award), AN_PRIX
 */

class CsvConverter extends AbstractBookConverter
{
    /** Marqueur "pas de valeur" utilisé dans le CSV */
    private const EMPTY_MARK = '_';

    /**
     * Codes de la colonne "Format" => [support, format, reliure, jaquette, rabat].
     * Les codes sont sensibles à la casse : "PR" (relié) et "Pr" (rabats) sont distincts.
     */
    private const FORMATS = [
        'P'   => ['papier', 'poche', false, false, false],
        'PR'  => ['papier', 'poche', true,  false, false],
        'RP'  => ['papier', 'poche', true,  false, false],
        'Pj'  => ['papier', 'poche', false, true,  false],
        'Pr'  => ['papier', 'poche', false, false, true],

        'GF'  => ['papier', 'gf', false, false, false],
        'GFR' => ['papier', 'gf', true,  false, false],
        'RGF' => ['papier', 'gf', true,  false, false],
        'GFj' => ['papier', 'gf', false, true,  false],
        'GFr' => ['papier', 'gf', false, false, true],

        'MF'  => ['papier', 'mf', false, false, false],
        'MFR' => ['papier', 'mf', true,  false, false],
        'RMF' => ['papier', 'mf', true,  false, false],
        'MFj' => ['papier', 'mf', false, true,  false],
        'MFr' => ['papier', 'mf', false, false, true],

        'N'   => ['numerique', 'n-a',   false, false, false],
        'A'   => ['audio',     'n-a',   false, false, false],
        'C'   => ['coffret',   'autre', false, false, false],
    ];
    private const FORMAT_UNKNOWN = ['papier', 'inconnu', false, false, false];

    /** Mois (minuscules) => "MM" ou "Tn" (trimestre) */
    private const MONTHS = [
        'january' => '01', 'jan' => '01', 'janv' => '01', 'janv.' => '01', 'janvier' => '01',
        'february' => '02', 'feb' => '02', 'fev' => '02', 'fevr' => '02', 'fevr.' => '02', 'fév' => '02', 'févr' => '02', 'févr.' => '02', 'fevrier' => '02', 'février' => '02',
        'march' => '03', 'mar' => '03', 'mars' => '03',
        'april' => '04', 'apr' => '04', 'avr' => '04', 'avr.' => '04', 'avril' => '04',
        'mai' => '05', 'may' => '05',
        'june' => '06', 'jun' => '06', 'juin' => '06',
        'july' => '07', 'jul' => '07', 'juil' => '07', 'juil.' => '07', 'juillet' => '07',
        'august' => '08', 'aug' => '08', 'aou' => '08', 'aout' => '08', 'août' => '08',
        'september' => '09', 'sep' => '09', 'sept' => '09', 'sept.' => '09', 'septembre' => '09',
        'october' => '10', 'oct' => '10', 'oct.' => '10', 'octobre' => '10',
        'november' => '11', 'nov' => '11', 'nov.' => '11', 'novembre' => '11',
        'december' => '12', 'dec' => '12', 'dec.' => '12', 'déc' => '12', 'déc.' => '12', 'decembre' => '12', 'décembre' => '12',
        't1' => 'T1', '1t' => 'T1',
        't2' => 'T2', '2t' => 'T2',
        't3' => 'T3', '3t' => 'T3',
        't4' => 'T4', '4t' => 'T4',
    ];

    public static function extensions(): array
    {
        return ['csv'];
    }

    // =====================================================================
    // Extraction
    // =====================================================================

    /**
     * Les 4 variantes d'en-tête admises : colonne image "nom_img" ou "S",
     * avec ou sans les deux colonnes de prix (Award).
     */
    private function expectedHeaders(): array
    {
        $variants = [];

        foreach (['nom_img', 'S'] as $img) {
            foreach ([false, true] as $withPrize) {
                $variants[] = [
                    'Sexe', 'PRENOM', 'NOM', 'TITRE', 'Série', 'N° Série', 'TITRE_ORIGINAL', 'GENRE', 'RNO', 'A/J',
                    'Format', 'Dim./Durée', 'Pages', 'Imag/Blanche/Auto', 'EDITEUR', 'COLLECTION', 'NR', 'tri',
                    $img, 'O', 'AN_COPY', 'Mois Paru', 'AN_PARU', 'O/R', 'Type', 'DL', 'AI', 'Imprimeur',
                    ...($withPrize ? ['PRIX (Award)', 'AN_PRIX'] : []),
                    'S Tr', 'Traducteur', 'ISBN', 'Commentaires', 'Sexe Il', 'Illustr. / Narrat.',
                ];
            }
        }

        return $variants;
    }

    protected function extract(string $source): array
    {
        // fgetcsv sur un flux : gère aussi les cellules entre guillemets contenant un saut de ligne
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->readSource($source));
        rewind($stream);

        $header = null;
        $data = [];

        while (($row = fgetcsv($stream, 0, "\t", '"', '')) !== false) {
            if ($row === [null] || trim(implode('', $row)) === '') {
                continue;   // ligne vide
            }

            if ($header === null) {
                $header = $this->checkHeader($row);
                continue;
            }

            $row = array_slice($row, 0, count($header));                       // tronque si trop long
            $row = array_pad($row, count($header), '');                        // complète si trop court
            $data[] = array_combine($header, array_map(fn ($v) => (string) $v, $row));
        }

        fclose($stream);

        return $data;
    }

    /** Vérifie l'en-tête ; en cas d'écart, indique les colonnes manquantes / inattendues. */
    private function checkHeader(array $row): array
    {
        $header = array_map(fn ($c) => trim((string) $c), $row);
        while ($header !== [] && end($header) === '') {
            array_pop($header);     // colonnes vides en fin d'en-tête
        }

        $closest = null;
        $closestGap = PHP_INT_MAX;

        foreach ($this->expectedHeaders() as $expected) {
            if ($header === $expected) {
                return $header;
            }

            $gap = count(array_diff($expected, $header)) + count(array_diff($header, $expected));
            if ($gap < $closestGap) {
                $closest = $expected;
                $closestGap = $gap;
            }
        }

        $missing = array_diff($closest, $header);
        $unexpected = array_diff($header, $closest);

        throw new \Exception(
            "En-tête CSV invalide.\n"
            . ($missing ? '  Colonnes manquantes : ' . implode(', ', $missing) . "\n" : '')
            . ($unexpected ? '  Colonnes inattendues : ' . implode(', ', $unexpected) . "\n" : '')
            . (!$missing && !$unexpected ? "  Mêmes colonnes mais dans un ordre différent.\n" : '')
        );
    }

    // =====================================================================
    // Construction du livre canonique
    // =====================================================================

    protected function buildBook(array $book): array
    {
        // Lecture nettoyée d'une colonne (trim, "_" => vide, colonne absente => vide)
        $get = fn (string $key): string => $this->clean($book[$key] ?? '');

        $format = $get('Format');
        $parution = $this->getApproximateParution($book);

        // Auteurs du livre (rôle author ou editor) ; auteurs du texte : seulement ceux de rôle author
        // NON, le contenant général est indiqué comme contenu principal de niveau 0, il faut donc conserver l'ensemble des auteurs
        // $textAuthors = array_values(array_filter($authors, fn ($a) => $a['auteur_role'] === 'author'));
        $authors = $this->parseAuthors($get('PRENOM'), $get('NOM'));
        $textAuthors = $authors;

        return [
            // éditeur & collection
            'editeur' => $get('EDITEUR'),
            'collection' => $get('COLLECTION'),
            'no_collection' => $get('NR'),

            'auteurs' => $authors,

            'titre' => $get('TITRE'),
            // status - absent du CSV
            'serie' => $get('Série'),
            'num_serie' => $get('N° Série'),

            // est_visible - absent du CSV : valeur par défaut (visible)

            // TODO : attention au format avec ISBN VN
            'isbn' => $get('ISBN'),

            'illustrateur_couv' => $this->getIllustrateurs($get('Illustr. / Narrat.'), 'couv'),
            'illustrateurs' => $this->getIllustrateurs($get('Illustr. / Narrat.'), 'interieur'),
            // information - absent du CSV
            'private' => $get('Commentaires'),

            // Récupération des images possiblement connues (couv, 4ième, dos, avec bandeau, avec jaquette)
            ... $this->getCoverImages($get('nom_img') ?: $get('S')),

            'reliure' => $this->getReliure($format),
            'jaquette' => $this->getJaquette($format),
            'rabat' => $this->getRabat($format),

            'est_verifie' => $this->getVerification($get('O'), 'est_verifie'),
            'verifie_par' => $this->getVerification($get('O'), 'verifie_par'),

            // Données issues du livre
            'dl' => $this->convertDlAi($get('DL'), $parution),
            'ai' => $this->convertDlAi($get('AI'), $parution),

            'dimensions' => $get('Dim./Durée'),
            'imprimeur' => $get('Imprimeur'),

            // Données indicatives
            'approximate_pages' => $get('Pages'),
            'approximate_parution' => $parution,

            'support' => $this->getSupport($format),
            'format' => $this->getFormat($format),
            'type_ouvrage' => $this->getTypeOuvrage($get('RNO')),

            // Genre, audience
            'est_genre' => $this->getEstGenre($get('GENRE')),
            'genre' => $this->getGenre($get('GENRE')),
            'audience' => $this->getAudienceTarget($get('A/J')),

            // type_tirage (O/R + image) : pas encore en base

            'sommaire' => [
                [
                    'titre' => $get('TITRE'),
                    'auteurs' => $textAuthors,
                    'titre_vo' => $get('TITRE_ORIGINAL'),
                    // ATTENTION : on ne garde que les 4 derniers caractères comme année de copyright
                    'copyright' => substr($get('AN_COPY'), -4),
                    'serie' => $get('Série'),
                    // TODO : extraire le titre VO de la série (??)
                    'num_serie' => $get('N° Série'),
                    'type_texte' => $this->getTypeTexte($get('RNO')),
                    'traducteurs' => $get('Traducteur'),
                ],
            ],
        ];
    }

    // =====================================================================
    // Utilitaires
    // =====================================================================

    /** trim, et "_" (marqueur "pas de valeur") => chaîne vide */
    protected function clean(?string $value): string
    {
        $value = trim((string) $value);

        return $value === self::EMPTY_MARK ? '' : $value;
    }

    /**
     * Interprétation du nom d'image (colonnes nom_img / S).
     *
     * Convention : on peut ajouter en fin de nom, dans un ordre quelconque, une ou plusieurs lettres
     * signalant l'existence d'autres images du livre :
     *   v = verso (4ème de couverture), d = dos (tranche), b = face avec bandeau, j = face avec jaquette.
     * Un "_" final signifie que le nom se termine naturellement par l'une de ces lettres (anciennes images
     * uniques) : on retire le "_" et il s'agit de la couverture seule.
     *
     * Exemples : "Dune" => couv ; "Dunevd" => couv "Dune" + verso "Dunev" + dos "Duned" ; "Sud_" => couv "Sud".
     */
    private function getCoverImages(string $input): array
    {
        $images = [
            'img_couv'     => $input,
            'img_back'     => null,
            'img_dos'      => null,
            'img_bandeau'  => null,
            'img_jaquette' => null,
        ];

        // "_" final : image unique dont le nom finit par v, d, b ou j
        if (str_ends_with($input, '_')) {
            $images['img_couv'] = substr($input, 0, -1);

            return $images;
        }

        // Sinon : nom de base suivi d'une suite de lettres b, v, d, j (jamais le nom entier)
        if (preg_match('/^(.*?)([bvdj]+)$/', $input, $m) && $m[1] !== '') {
            $keys = ['v' => 'img_back', 'd' => 'img_dos', 'b' => 'img_bandeau', 'j' => 'img_jaquette'];

            $images['img_couv'] = $m[1];
            foreach (array_unique(str_split($m[2])) as $letter) {
                $images[$keys[$letter]] = $m[1] . $letter;
            }
        }

        return $images;
    }

    /**
     * Auteurs multiples : "&" entre les prénoms et entre les noms
     * (ex. PRENOM "Ugo & Thomas", NOM "Bellagamba & Day").
     * Un prénom commençant par "antho :" désigne un directeur d'anthologie (rôle "editor").
     *
     * Cas gérés : autant de prénoms que de noms (appariés dans l'ordre) ; aucun prénom (pseudonymes) ;
     * un seul nom pour plusieurs prénoms (même nom de famille). Autre incohérence : l'entrée n'est pas
     * découpée, et le validateur la signalera (longueur de nom).
     *
     * @return array<int, array{auteur_prenom: string, auteur_nom: string, auteur_role: string}>
     */
    protected function parseAuthors(string $prenoms, string $noms): array
    {
        $role = 'author';

        if (preg_match('/^antho\s*:\s*/i', $prenoms)) {
            $role = 'editor';
            $prenoms = preg_replace('/^antho\s*:\s*/i', '', $prenoms);
        }

        $firsts = array_map('trim', explode('&', $prenoms));
        $lasts = array_map('trim', explode('&', $noms));

        if (count($firsts) === 1 && $firsts[0] === '') {
            $firsts = array_fill(0, count($lasts), '');                       // aucun prénom
        } elseif (count($lasts) === 1 && count($firsts) > 1) {
            $lasts = array_fill(0, count($firsts), $lasts[0]);                // même nom de famille
        }

        if (count($firsts) !== count($lasts)) {
            $firsts = [trim($prenoms)];                                       // incohérent : non découpé
            $lasts = [trim($noms)];
        }

        $authors = [];
        foreach ($lasts as $i => $last) {
            $authors[] = [
                'auteur_prenom' => $firsts[$i],
                'auteur_nom' => $last,
                'auteur_role' => $role,
            ];
        }

        return $authors;
    }

    /** "Illustrateur couverture + illustrateur(s) intérieur" : seul le premier "+" sépare. */
    protected function getIllustrateurs(string $value, string $type): string
    {
        [$couv, $interieur] = array_pad(preg_split('/\s*\+\s*/', $value, 2), 2, '');

        return $this->clean($type === 'couv' ? $couv : $interieur);
    }

    protected function getVerification(string $value, string $type): string
    {
        if ($type === 'est_verifie') {
            return $value === 'X' ? 'oui' : 'non';
        }

        return $value === 'X' ? 'CHRISTIAN' : '';
    }

    protected function getAudienceTarget(string $value): string
    {
        return match (trim(strtolower($value))) {
            'a' => 'adulte-only',
            'j' => 'jeunesse',
            'y' => 'YA',
            default => 'inconnu',
        };
    }

    // ---------------------------------------------------------------- Dates

    /** "AAAA-MM-00" (MM = mois, "Tn" = trimestre, "xx" = inconnu) ; vide si l'année est absente */
    protected function getApproximateParution(array $book): string
    {
        $year = $this->clean($book['AN_PARU'] ?? '');

        if ($year === '') {
            return '';
        }

        return $year . '-' . $this->getMoisParution($book['Mois Paru'] ?? '') . '-00';
    }

    protected function getMoisParution(string $value): string
    {
        $value = mb_strtolower(trim($value));

        if (preg_match('/^(0?[1-9]|1[0-2])$/', $value)) {
            return str_pad($value, 2, '0', STR_PAD_LEFT);
        }

        return match ($value) {
            '1t', 't1' => 'T1',
            '2t', 't2' => 'T2',
            '3t', 't3' => 'T3',
            '4t', 't4' => 'T4',
            default => 'xx',
        };
    }

    /**
     * Dépôt légal / achevé d'imprimer => "AAAA-MM-JJ" (00 = inconnu, "Tn" = trimestre).
     * Formats acceptés : "A parution", "n.i.", "1951", "03/1951", "mars 1951", "12/03/51",
     * "1951-03-12", "2ème trimestre 1951"... Valeur non décodable => "Err Decod." (10 caractères).
     * $approxParution ("AAAA-MM-00") sert à deviner le siècle des années sur 2 chiffres.
     */
    protected function convertDlAi(string $value, string $approxParution): string
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            return '';
        }

        $special = [
            'a parution' => 'A parution',
            'à parution' => 'A parution',
            'n.i.' => 'Non indiq.',
            'n.c.' => 'Non indiq.',
        ];
        if (isset($special[$value])) {
            return $special[$value];
        }

        $approxYear = substr($approxParution, 0, 4);

        // "1er trimestre 1951", "2ème trimestre 51"
        if (preg_match('/^([1-4])\s*(?:er|ère|e|ème|eme|ième|ieme)?\s*trimestre\s+(\d{2}|\d{4})$/u', $value, $m)) {
            return $this->expandYear($m[2], $approxYear) . "-T{$m[1]}-00";
        }

        $parts = preg_split('/[\/\s-]+/', $value, -1, PREG_SPLIT_NO_EMPTY);

        [$day, $month, $year] = match (count($parts)) {
            1 => ['00', '00', $parts[0]],
            2 => strlen($parts[0]) === 4 ? ['00', $parts[1], $parts[0]] : ['00', $parts[0], $parts[1]],
            3 => strlen($parts[0]) === 4 ? [$parts[2], $parts[1], $parts[0]] : [$parts[0], $parts[1], $parts[2]],
            default => [null, null, null],
        };

        $month = $month === null ? null : $this->normalizeMonth($month);
        $day = ($day !== null && preg_match('/^\d{1,2}$/', $day)) ? str_pad($day, 2, '0', STR_PAD_LEFT) : null;

        if ($year !== null && preg_match('/^\d{2}$/', $year)) {
            $year = $this->expandYear($year, $approxYear);
        }

        if ($year === null || !preg_match('/^\d{4}$/', $year) || $month === null || $day === null) {
            return 'Err Decod.';
        }

        return "{$year}-{$month}-{$day}";
    }

    /** "3", "03", "mars", "T2"... => "MM" ou "Tn" ; null si non reconnu */
    private function normalizeMonth(string $value): ?string
    {
        if (preg_match('/^(0?[0-9]|1[0-2])$/', $value)) {
            return str_pad($value, 2, '0', STR_PAD_LEFT);
        }

        return self::MONTHS[$value] ?? null;
    }

    /** Année sur 2 chiffres => 4 chiffres : le siècle qui rapproche le plus de l'année de parution estimée */
    protected function expandYear(string $year, string $approxYear): string
    {
        if (strlen($year) === 4) {
            return $year;
        }

        $yy = (int) $year;

        if (preg_match('/^\d{4}$/', $approxYear)) {
            $ref = (int) $approxYear;
            $base = intdiv($ref, 100) * 100;
            $candidates = [$base - 100 + $yy, $base + $yy, $base + 100 + $yy];
            usort($candidates, fn ($a, $b) => abs($a - $ref) <=> abs($b - $ref));

            return (string) $candidates[0];
        }

        return (string) ($yy > (int) date('y') + 1 ? 1900 + $yy : 2000 + $yy);
    }

    // ---------------------------------------------------------------- Genre

    protected function getEstGenre(string $value): string
    {
        return match (trim(strtolower($value))) {
            'sf', 'fy', 'fant', 'hyb' => 'oui',
            'hg' => 'non',
            default => 'inconnu',
        };
    }

    protected function getGenre(string $value): string
    {
        return match (trim(strtolower($value))) {
            'sf' => 'sf',
            'fy' => 'fantasy',
            'fant' => 'fantastique',
            'hyb' => 'hybride',
            default => 'inconnu',
        };
    }

    // ---------------------------------------------------------------- Format

    /** @return array{0: string, 1: string, 2: bool, 3: bool, 4: bool} support, format, reliure, jaquette, rabat */
    private function formatInfo(string $code): array
    {
        return self::FORMATS[trim($code)] ?? self::FORMAT_UNKNOWN;
    }

    protected function getSupport(string $format): string
    {
        return $this->formatInfo($format)[0];
    }

    protected function getFormat(string $format): string
    {
        return $this->formatInfo($format)[1];
    }

    protected function getReliure(string $format): string
    {
        return $this->formatInfo($format)[2] ? 'oui' : 'non';
    }

    protected function getJaquette(string $format): string
    {
        return $this->formatInfo($format)[3] ? 'oui' : 'non';
    }

    protected function getRabat(string $format): string
    {
        return $this->formatInfo($format)[4] ? 'oui' : 'non';
    }

    // ---------------------------------------------------------------- Types

    /** Non utilisé pour l'instant (type de tirage : colonnes "O/R" et image) */
    protected function getTypeEdition(string $or, string $img): string
    {
        return match ($or) {
            'O' => 'premiere_collection',
            'R' => $img === '' ? 'retirage_collection' : 'reedition_collection',
            default => 'inedit',    // "I", et valeur inattendue (TODO : logger)
        };
    }

    /** Non utilisé pour l'instant (titles.is_novelization) */
    protected function getEstNovellisation(string $value): string
    {
        return trim(strtolower($value)) === 'novellisation' ? 'oui' : 'non';
    }

    protected function getTypeOuvrage(string $value): string
    {
        // Roman, Recueil, Anthologie, Omnibus, Novella, Nouvelle, Novellisation, Essai, Coffret, Livre-Jeu, Art-book, Théâtre et Revues
        return match (trim(strtolower($value))) {
            'roman' => 'roman',
            'recueil', 'anthologie', 'coffret' => 'compilation',
            'omnibus' => 'omnibus',
            'novella', 'nouvelle', 'theatre' => 'fiction',
            'novellisation' => 'roman',
            'essai', 'livre-jeu', 'art-book' => 'non-fiction',
            'revue' => 'periodique',
            default => '?',
        };
    }

    protected function getTypeTexte(string $value): string
    {
        return match (trim(strtolower($value))) {
            'roman' => 'novel',
            'recueil' => 'collection',
            'anthologie' => 'anthologie',
            'omnibus' => 'omnibus',
            'novella' => 'novella',
            'nouvelle' => 'shortstory',
            'novellisation' => 'roman',
            'essai' => 'essai',
            'livre-jeu' => 'gamebook',
            'theatre' => 'theatre',
            'revue' => 'magazine',
            default => 'inconnu',   // dont coffret, art-book
        };
    }

    // ---------------------------------------------------------------- Export (à venir)

    public function reverseMap(array $pivotRow): array
    {
        return [
            'Sexe' => '',
            'PRENOM' => '',
            'NOM' => '',
            'TITRE' => 'Non réalisé !!',
            'Série' => '',
            'N° Série' => '',
            'TITRE_ORIGINAL' => '',
            'GENRE' => '',
            'RNO' => '',
            'A/J' => '',
            'Format' => '',
            'Dim./Durée' => '',
            'Pages' => '',
            'Imag/Blanche/Auto' => '',
            'EDITEUR' => '',
            'COLLECTION' => '',
            'NR' => '',
            'tri' => '',
            'nom_img' => '',
            'O' => '',
            'AN_COPY' => '',
            'Mois Paru' => '',
            'AN_PARU' => '',
            'O/R' => '',
            'Type' => '',
            'DL' => '',
            'AI' => '',
            'Imprimeur' => '',
            'S Tr' => '',
            'Traducteur' => '',
            'ISBN' => '',
            'Commentaires' => '',
            'Sexe Il' => '',
            'Illustr. / Narrat.' => '',
        ];
    }
}