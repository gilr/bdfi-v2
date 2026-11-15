<?php

namespace App\Console\Commands;

use App\Bdfi\Export\BookComparator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Compare un JSON produit par l'import (<nom>.converted.json) avec l'export de la base
 * (<nom>.exported.json, cf. bdfi:export-books). Les deux fichiers sont dans storage/app/import/.
 * Le détail complet est écrit dans <nom>.diff.csv (séparateur ";", ouvrable dans Excel).
 */
class CompareBooks extends Command
{
    protected $signature = 'bdfi:compare-books
        { file : JSON issu de l\'import (ex. ppterr.converted.json), relatif à storage/app/import/ }
        { base : JSON exporté de la base (ex. ppterr.exported.json), relatif à storage/app/import/ }
        { --field=* : Liste toutes les différences de ce(s) champ(s) (ex. --field=auteurs --field=sommaire.titre) }
        { --ignore=* : Champ(s) à ignorer, en plus de ceux ignorés par défaut }
        { --all : Compare aussi les champs ignorés par défaut (editeur, collection, groupe_edition...) }
        { --ignore-case : Ignore les différences de casse seule (BARKER / Barker) }';

    protected $description = 'Compare un JSON d\'import avec l\'export de la base et liste les différences champ par champ';

    public function handle(): int
    {
        try {
            $fileBooks = $this->load($this->argument('file'));
            $baseBooks = $this->load($this->argument('base'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $ignored = array_merge($this->option('all') ? [] : BookComparator::DEFAULT_IGNORED, $this->option('ignore'));
        $result = (new BookComparator($ignored, (bool) $this->option('ignore-case')))->compare($fileBooks, $baseBooks);

        $this->renderSummary($fileBooks, $baseBooks, $result, $ignored);
        $this->renderUnmatched('Livres du fichier absents de la base', $result['onlyInFile'], true);
        $this->renderUnmatched('Publications de la base absentes du fichier', $result['onlyInBase'], false);
        $this->renderFields($result['differences']);

        foreach ($this->option('field') as $field) {
            $this->renderFieldDetail($field, $result['differences']);
        }

        if ($result['differences']) {
            $path = $this->writeCsv($result['differences']);
            $this->newLine();
            $this->info("Détail complet (" . count($result['differences']) . " ligne(s)) : {$path}");
        }

        return self::SUCCESS;
    }

    private function load(string $file): array
    {
        $base = realpath(storage_path('app/import'));
        $path = realpath($base . DIRECTORY_SEPARATOR . $file);

        if ($path === false || !is_file($path) || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            throw new \InvalidArgumentException("Le fichier \"{$file}\" n'existe pas dans storage/app/import/.");
        }

        $books = json_decode(File::get($path), true);

        if (!is_array($books)) {
            throw new \InvalidArgumentException("Le fichier \"{$file}\" n'est pas un JSON de livres valide.");
        }

        return $books;
    }

    // ------------------------------------------------------------------ Affichage

    private function renderSummary(array $fileBooks, array $baseBooks, array $r, array $ignored): void
    {
        $books = count(array_unique(array_column($r['differences'], 'book')));

        $this->table(
            ['Fichier', 'Base', 'Appariés', 'Identiques', 'Avec différences', 'Seulement fichier', 'Seulement base'],
            [[count($fileBooks), count($baseBooks), $r['matched'], $r['matched'] - $books, $books,
              count($r['onlyInFile']), count($r['onlyInBase'])]],
        );

        $this->line('<fg=gray>Appariement : ISBN + année de parution, puis titre + année. Champs ignorés : '
            . ($ignored ? implode(', ', $ignored) : 'aucun') . '.</>');
    }

    private function renderUnmatched(string $title, array $books, bool $numbered): void
    {
        if (!$books) {
            return;
        }

        $this->newLine();
        $this->warn("{$title} (" . count($books) . ')');

        $limit = $this->getOutput()->isVerbose() ? PHP_INT_MAX : 15;
        foreach (array_slice($books, 0, $limit, true) as $n => $b) {
            $ref = $numbered ? "#{$n}" : ('id ' . ($b['_id'] ?? '?'));
            $this->line("  - {$ref} {$b['titre']} [" . ($b['isbn'] ?? '') . '] ' . ($b['approximate_parution'] ?? '')
                . (($b['no_collection'] ?? '') !== '' ? " n°{$b['no_collection']}" : ''));
        }
        if (count($books) > $limit) {
            $this->line('  ... et ' . (count($books) - $limit) . ' autre(s) (-v pour tout voir)');
        }
    }

    /** Une ligne par champ : nombre de différences, dont casse seule, et un exemple */
    private function renderFields(array $differences): void
    {
        $this->newLine();

        if (!$differences) {
            $this->info('Aucune différence sur les livres appariés.');

            return;
        }

        $fields = [];
        foreach ($differences as $d) {
            $f = BookComparator::genericField($d['field']);
            $fields[$f] ??= ['count' => 0, 'case' => 0, 'example' => $d];
            $fields[$f]['count']++;
            $fields[$f]['case'] += $d['caseOnly'] ? 1 : 0;
        }
        uasort($fields, fn ($a, $b) => $b['count'] <=> $a['count']);

        $this->info('Différences par champ (fichier ≠ base)');
        $this->table(['Champ', 'Nb', 'Casse seule', 'Exemple'], array_map(
            fn ($f, $s) => [$f, $s['count'], $s['case'] ?: '', $this->example($s['example'])],
            array_keys($fields), $fields,
        ));
        $this->line('<fg=gray>Détail d\'un champ : --field=<champ>. Différences de casse seule ignorées avec --ignore-case.</>');
    }

    private function renderFieldDetail(string $field, array $differences): void
    {
        $rows = array_filter($differences, fn ($d) => BookComparator::genericField($d['field']) === $field || $d['field'] === $field);

        $this->newLine();
        $this->info("Champ {$field} : " . count($rows) . ' différence(s)');

        if ($rows) {
            $this->table(['Livre', 'id', 'Titre', 'Champ', 'Fichier', 'Base'], array_map(fn ($d) => [
                "#{$d['book']}", $d['id'], Str::limit($d['title'], 30), $d['field'],
                Str::limit($d['file'], 50), Str::limit($d['base'], 50),
            ], $rows));
        }
    }

    private function example(array $d): string
    {
        return "#{$d['book']} « " . Str::limit($d['file'], 30) . ' » ≠ « ' . Str::limit($d['base'], 30) . ' »';
    }

    private function writeCsv(array $differences): string
    {
        $name = Str::before(basename($this->argument('file')), '.');
        $path = realpath(storage_path('app/import')) . DIRECTORY_SEPARATOR . "{$name}.diff.csv";

        $handle = fopen($path, 'w');
        fwrite($handle, "\xEF\xBB\xBF");   // BOM : accents corrects à l'ouverture dans Excel
        fputcsv($handle, ['livre', 'id', 'isbn', 'titre', 'champ', 'fichier', 'base', 'casse_seule'], ';');
        foreach ($differences as $d) {
            fputcsv($handle, [$d['book'], $d['id'], $d['isbn'], $d['title'], $d['field'], $d['file'], $d['base'], $d['caseOnly'] ? 'oui' : ''], ';');
        }
        fclose($handle);

        return $path;
    }
}
