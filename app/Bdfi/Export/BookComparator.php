<?php

namespace App\Bdfi\Export;

use App\Bdfi\Import\BookLookup;

/**
 * Compare deux listes de livres au format JSON canonique : celle produite par un converter
 * (<nom>.converted.json) et celle exportée de la base (<nom>.exported.json, cf. BookExporter).
 *
 * Les valeurs sont ramenées à ce que l'import écrirait réellement, pour ne signaler que les vraies
 * différences : espaces normalisés, vide = valeur par défaut SQL, booléens oui/non, auteurs et
 * retirages comparés sans tenir compte de leur ordre. Utilisable en console et en web.
 */
final class BookComparator
{
    /** Champs ignorés par défaut : numérotation propre au fichier, libellés non stockés, id technique */
    public const DEFAULT_IGNORED = ['_id', 'groupe_edition', 'rang_edition', 'editeur', 'collection', 'code_prix'];

    /** Valeur par défaut SQL appliquée par l'import quand le champ est vide */
    private const DEFAULTS = [
        'isbn'        => '?',      // vide = ISBN inconnu (BookLookup::storedIsbn)
        'est_visible' => 'oui',
        'reliure'     => 'non',
        'jaquette'    => 'non',
        'rabat'       => 'non',
        'est_verifie' => 'non',
        'status'      => 'paru',
        'support'     => 'papier',
        'format'      => 'inconnu',
        'audience'    => 'inconnu',
        'genre'       => 'inconnu',
        'sommaire.novellisation' => 'non',
        'sommaire.genre'         => 'inconnu',
        'retirages.est_verifie'  => 'non',
    ];

    private const BOOLEANS = ['est_visible', 'reliure', 'jaquette', 'rabat', 'est_verifie', 'est_reedition', 'retirages.est_verifie'];

    public function __construct(
        private array $ignored = self::DEFAULT_IGNORED,
        private bool $ignoreCase = false,
    ) {}

    /**
     * @param array<int, array> $fileBooks livres du fichier (numérotés à partir de 1 dans le résultat)
     * @param array<int, array> $baseBooks livres exportés de la base
     * @return array{
     *   matched: int,
     *   onlyInFile: array<int, array>, onlyInBase: array<int, array>,
     *   differences: array<int, array{book: int, id: ?int, isbn: string, title: string, field: string, file: string, base: string, caseOnly: bool}>
     * }
     */
    public function compare(array $fileBooks, array $baseBooks): array
    {
        [$pairs, $onlyInFile, $onlyInBase] = $this->match(array_values($fileBooks), array_values($baseBooks));

        $differences = [];

        foreach ($pairs as [$n, $fileBook, $baseBook]) {
            $a = $this->flatten($fileBook);
            $b = $this->flatten($baseBook);

            foreach (array_keys($a + $b) as $field) {
                if ($this->isIgnored($field)) {
                    continue;
                }

                // Le n° n'est stocké qu'avec une collection (collection_publication) : sans collection, rien à comparer
                if ($field === 'no_collection' && ($baseBook['collection'] ?? '') === '') {
                    continue;
                }

                $va = $a[$field] ?? '';
                $vb = $b[$field] ?? '';

                if ($va === $vb) {
                    continue;
                }

                $caseOnly = mb_strtolower($va) === mb_strtolower($vb);

                if ($caseOnly && $this->ignoreCase) {
                    continue;
                }

                $differences[] = [
                    'book'     => $n,
                    'id'       => $baseBook['_id'] ?? null,
                    'isbn'     => (string) ($fileBook['isbn'] ?? ''),
                    'title'    => (string) ($fileBook['titre'] ?? ''),
                    'field'    => $field,
                    'file'     => $va,
                    'base'     => $vb,
                    'caseOnly' => $caseOnly,
                ];
            }
        }

        return [
            'matched'     => count($pairs),
            'onlyInFile'  => $onlyInFile,
            'onlyInBase'  => $onlyInBase,
            'differences' => $differences,
        ];
    }

    /**
     * Nom générique d'un champ (sans le rang du texte ou du retirage) : "sommaire[2].titre" => "sommaire.titre".
     */
    public static function genericField(string $field): string
    {
        return preg_replace('/\[\d+\]/', '', $field);
    }

    // ------------------------------------------------------------------ Appariement

    /**
     * Passe 1 : ISBN (sans tirets) + année de parution. Passe 2, pour les restants : titre + année.
     * En cas de doublons, appariement dans l'ordre des fichiers.
     *
     * @return array{0: array<int, array{0: int, 1: array, 2: array}>, 1: array<int, array>, 2: array<int, array>}
     */
    private function match(array $fileBooks, array $baseBooks): array
    {
        $pairs = [];
        $remainingFile = [];
        foreach ($fileBooks as $i => $book) {
            $remainingFile[$i + 1] = $book;
        }
        $remainingBase = $baseBooks;

        $keys = [
            fn (array $b) => ($isbn = self::isbn($b['isbn'] ?? '')) === '' ? null : $isbn . '|' . self::year($b),
            fn (array $b) => mb_strtolower(self::norm($b['titre'] ?? '')) . '|' . self::year($b),
        ];

        foreach ($keys as $key) {
            $index = [];
            foreach ($remainingBase as $j => $book) {
                if (($k = $key($book)) !== null) {
                    $index[$k][] = $j;
                }
            }

            foreach ($remainingFile as $n => $book) {
                $k = $key($book);
                if ($k === null || empty($index[$k])) {
                    continue;
                }

                $j = array_shift($index[$k]);
                $pairs[] = [$n, $book, $remainingBase[$j]];
                unset($remainingFile[$n], $remainingBase[$j]);
            }
        }

        usort($pairs, fn ($p, $q) => $p[0] <=> $q[0]);

        return [$pairs, $remainingFile, array_values($remainingBase)];
    }

    // ------------------------------------------------------------------ Normalisation

    /** Livre => [champ => valeur normalisée] ; textes du sommaire et retirages à plat */
    private function flatten(array $book): array
    {
        $out = [];

        foreach ($book as $field => $value) {
            // Identifiants (publisher_id, collection_id, _id...) : techniques, absents des fichiers source
            if (str_ends_with($field, '_id')) {
                continue;
            }

            switch ($field) {
                case 'auteurs':
                    $out['auteurs'] = self::authors($value ?? []);
                    break;

                case 'sommaire':
                    $texts = array_values($value ?? []);
                    $out['sommaire.nombre'] = (string) count($texts);
                    foreach ($texts as $i => $text) {
                        // Cycles comparés comme un ensemble "Nom [n°]" (series, sinon serie/num_serie)
                        $out['sommaire[' . ($i + 1) . '].series'] = implode(' ; ', collect(BookLookup::series($text))
                            ->map(fn ($s) => $s['name'] . ($s['number'] !== '' ? " [{$s['number']}]" : ''))
                            ->sort()->values()->all());

                        foreach ($text as $f => $v) {
                            if (in_array($f, ['serie', 'num_serie', 'series'], true) || str_ends_with($f, '_id')) {
                                continue;
                            }
                            $out['sommaire[' . ($i + 1) . "].{$f}"] = $f === 'auteurs'
                                ? self::authors($v ?? [], withRole: false)
                                : $this->scalar("sommaire.{$f}", $v);
                        }
                    }
                    break;

                case 'retirages':
                    // Comparés comme un ensemble : l'ordre des retirages n'est pas significatif
                    $reprints = array_map(fn ($r) => implode(' / ', [
                        $this->scalar('retirages.approximate_parution', $r['approximate_parution'] ?? null),
                        $this->scalar('retirages.ai', $r['ai'] ?? null),
                        $this->scalar('retirages.est_verifie', $r['est_verifie'] ?? null),
                        $this->scalar('retirages.verifie_par', $r['verifie_par'] ?? null),
                    ]), $value ?? []);
                    sort($reprints);
                    $out['retirages'] = implode(' ; ', $reprints);
                    break;

                case 'edition_originale_isbn':
                    $out[$field] = self::isbn((string) $value);
                    break;

                default:
                    $out[$field] = $this->scalar($field, $value);
            }
        }

        return $out;
    }

    private function scalar(string $field, mixed $value): string
    {
        if (is_array($value)) {
            $value = implode(', ', array_filter($value));
        }

        if (in_array($field, self::BOOLEANS, true)) {
            $value = self::bool($value);
        } elseif ($field === 'sommaire.niveau') {
            $value = ($value === null || $value === '') ? '0' : (string) (float) $value;
        } else {
            $value = self::norm(is_bool($value) ? ($value ? '1' : '0') : (string) $value);
        }

        return ($value === '' && isset(self::DEFAULTS[$field])) ? self::DEFAULTS[$field] : $value;
    }

    /** Auteurs triés : "Prénom NOM" (+ rôle sauf "author" ; sans rôle pour les textes, author_title n'en stockant pas) */
    private static function authors(array $authors, bool $withRole = true): string
    {
        $labels = array_map(function ($a) use ($withRole) {
            $label = self::norm(($a['auteur_prenom'] ?? '') . ' ' . ($a['auteur_nom'] ?? ''));
            $role = $a['auteur_role'] ?? 'author';

            return (!$withRole || $role === 'author') ? $label : "{$label} ({$role})";
        }, $authors);

        $labels = array_values(array_unique(array_filter($labels, fn ($l) => $l !== '')));
        sort($labels);

        return implode(' ; ', $labels);
    }

    private static function bool(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'oui' : 'non';
        }

        return in_array(mb_strtolower(trim((string) $value)), ['1', 'true', 'oui', 'yes', 'o'], true) ? 'oui' : 'non';
    }

    private static function norm(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private static function isbn(string $value): string
    {
        return strtoupper(preg_replace('/[^0-9Xx]/', '', $value));
    }

    private static function year(array $book): string
    {
        return substr((string) ($book['approximate_parution'] ?? ''), 0, 4);
    }

    private function isIgnored(string $field): bool
    {
        return in_array($field, $this->ignored, true) || in_array(self::genericField($field), $this->ignored, true);
    }
}
