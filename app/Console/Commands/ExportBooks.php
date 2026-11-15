<?php

namespace App\Console\Commands;

use App\Bdfi\Export\BookExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

/**
 * Inverse de bdfi:import-books : écrit au format JSON canonique les publications déjà en base,
 * pour les comparer avec le <nom>.converted.json produit par l'import.
 * Le fichier est écrit dans storage/app/import/.
 */
class ExportBooks extends Command
{
    protected $signature = 'bdfi:export-books
        { targets* : Sigle(s) de collection (alias acceptés), sigle(s) d\'éditeur (publications sans collection) ou nom(s) de collection }
        { --output= : Nom du fichier, sans extension (défaut : les cibles). Fichier écrit : <nom>.exported.json }';

    protected $description = 'Exporte en JSON canonique les publications d\'une ou plusieurs collections, pour comparaison avec un import';

    public function handle(BookExporter $exporter): int
    {
        try {
            $targets = array_map(fn (string $t) => $exporter->resolve($t), $this->argument('targets'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($targets as $target) {
            $this->line("Cible : {$target['label']}");
        }

        $books = $exporter->export($targets);

        if ($books === []) {
            $this->warn('Aucune publication trouvée.');

            return self::SUCCESS;
        }

        $name = $this->option('output')
            ?: Str::slug(implode('-', array_column($targets, 'sigle')), '-');
        $dir = storage_path('app/import');
        File::ensureDirectoryExists($dir);
        $path = $dir . DIRECTORY_SEPARATOR . basename($name) . '.exported.json';

        File::put($path, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        $reprints = array_sum(array_map(fn ($b) => count($b['retirages']), $books));
        $reissues = count(array_filter($books, fn ($b) => $b['est_reedition']));

        $this->info(count($books) . " publication(s) exportée(s) ({$reissues} réédition(s), {$reprints} retirage(s)) dans : {$path}");

        return self::SUCCESS;
    }
}
