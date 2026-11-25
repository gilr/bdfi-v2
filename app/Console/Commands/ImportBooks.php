<?php

namespace App\Console\Commands;

use App\Bdfi\BookConverter\BookFormatRegistry;
use App\Bdfi\Import\AnalysisReport;
use App\Bdfi\Import\BookAnalyzer;
use App\Bdfi\Import\BookImporter;
use App\Bdfi\Import\BookImportPreparer;
use App\Bdfi\Import\BookLookup;
use App\Bdfi\Import\ConsoleInteractor;
use App\Bdfi\Import\ImportOptions;
use App\Bdfi\Import\PreparedImport;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

/**
 * Le fichier source doit se trouver dans storage/app/import/ (sous-dossiers acceptés).
 * Les formats acceptés sont ceux déclarés dans BookFormatRegistry.
 */
class ImportBooks extends Command
{
    protected $signature = 'bdfi:import-books
        { file : Fichier d\'entrée, relatif à storage/app/import/ }
        { --analyse : Convertit et valide tout le fichier, puis résume ce qui serait créé ou ignoré (aucune écriture) }
        { --dry-run : Simule l\'import réel et annule tout à la fin }
        { --step-by-step : Validation manuelle à chaque étape d\'un livre }
        { --book-by-book : Validation manuelle à la fin de chaque livre }
        { --offset=0 : Ignore les N premiers livres }
        { --limit= : Ne traite que N livres }
        { --encoding=auto : Encodage des fichiers CSV/COL : auto (détection), utf8, cp437, cp1252... }';

    protected $description = 'Importe un fichier de publications : éditeur & collection, auteurs, titres, cycles, publications';

    public function handle(BookImportPreparer $preparer, BookAnalyzer $analyzer, BookFormatRegistry $registry): int
    {
        // 1. Préparation : chemin, conversion, JSON, validation
        try {
            $prepared = $preparer->prepare($this->argument('file'), ['encoding' => $this->option('encoding')]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->line('Extensions acceptées : ' . implode(', ', $registry->extensions()));

            return self::FAILURE;
        }

        $this->info("JSON Livres : {$prepared->jsonPath}");
        $this->renderWarnings($prepared->warnings);

        // 2. Sélection (clés d'origine conservées => numéros de livres stables)
        $books = array_slice(
            $prepared->books,
            (int) $this->option('offset'),
            $this->option('limit') !== null ? (int) $this->option('limit') : null,
            preserve_keys: true,
        );

        if ($books === []) {
            $this->warn('Aucun livre à traiter.');

            return self::SUCCESS;
        }
        $this->info(count($books) . ' livre(s) à traiter.');

        // 3. Erreurs de structure, limitées aux livres sélectionnés
        $errors = array_intersect_key(
            $prepared->errors,
            array_flip(array_map(fn ($i) => $i + 1, array_keys($books))),
        );

        if ($errors) {
            $this->renderValidationErrors($errors, $prepared->books);

            return self::FAILURE;
        }

        // 4. Analyse seule
        if ($this->option('analyse')) {
            if ($this->option('dry-run') || $this->option('step-by-step') || $this->option('book-by-book')) {
                $this->warn('--analyse n\'écrit rien : les autres options sont ignorées.');
            }
            $report = $analyzer->analyse($books);
            $this->renderReport($report);
            $this->exportToAdd($preparer, $prepared, $report);

            return self::SUCCESS;
        }

        // 5. Import réel (ou dry-run)
        $options = new ImportOptions(
            dryRun: (bool) $this->option('dry-run'),
            stepByStep: (bool) $this->option('step-by-step'),
            bookByBook: (bool) $this->option('book-by-book'),
        );

        $stats = (new BookImporter($options, new ConsoleInteractor($this)))->run($books);

        $this->table(['Importés', 'Ignorés', 'Déjà en base', 'Annulés'], [[
            $stats['imported'], $stats['skipped'], $stats['already_imported'], $stats['rolled_back'],
        ]]);

        if ($stats['already_imported'] && !$this->getOutput()->isVerbose()) {
            $this->line('<fg=gray>Livres déjà en base (même ISBN, ou sans ISBN : même livre) ignorés sans détail : ajoutez -v pour les lister.</>');
        }

        $this->renderCyclesMissingParent($stats['cycles_missing_parent']);
        $this->renderIdMismatches($stats['id_mismatches']);

        return $stats['aborted'] ? self::FAILURE : self::SUCCESS;
    }

    // ------------------------------------------------------------------ Affichage

    /** Écarts entre l'en-tête du fichier (format COL) et les noms d'éditeur/collection trouvés en base */
    private function renderWarnings(array $warnings): void
    {
        if (!$warnings) {
            return;
        }

        $this->warn(count($warnings) . ' écart(s) fichier / base (éditeur ou collection renommés en base ?) :');
        foreach ($warnings as $w) {
            $this->line("  - {$w}");
        }
    }

    private function renderValidationErrors(array $errors, array $books): void
    {
        $this->error(count($errors) . ' livre(s) invalide(s) : corrigez le fichier avant de continuer.');

        $verbose = $this->getOutput()->isVerbose();

        $rows = [];
        foreach (array_slice($errors, 0, 50, true) as $n => $details) {
            $lines = [];
            foreach ($details as $d) {
                $line = $d['message'];

                // Avec -v : champ et valeur exacte à l'origine de l'erreur
                if ($verbose && ($shown = $this->formatValue($d['value'])) !== null) {
                    $line .= "\n   → {$d['attribute']} = {$shown}";
                }

                $lines[] = $line;
            }

            $rows[] = ["#{$n}", Str::limit((string) ($books[$n - 1]['titre'] ?? ''), 40), implode("\n", $lines)];
        }
        $this->table(['Livre', 'Titre', 'Problème(s)'], $rows);

        if (!$verbose) {
            $this->line('<fg=gray>Ajoutez -v pour afficher la valeur exacte incriminée.</>');
        }

        if (count($errors) > 50) {
            $this->line('... et ' . (count($errors) - 50) . ' autre(s).');
        }
    }

    /** Valeur à afficher : texte entre guillemets avec sa longueur ; null pour une structure (non affichée) */
    private function formatValue(mixed $value): ?string
    {
        if (is_array($value)) {
            return null;
        }
        if ($value === null) {
            return '(absent)';
        }

        $value = (string) $value;

        return $value === '' ? '(vide)' : '"' . Str::limit($value, 200) . '" (' . mb_strlen($value) . ' car.)';
    }

    /** Écrit <source>.to-add.json : uniquement les livres que l'analyse retient comme "à ajouter". */
    private function exportToAdd(BookImportPreparer $preparer, PreparedImport $prepared, AnalysisReport $report): void
    {
        $this->newLine();

        if (!$report->newPublications) {
            $this->line('Aucun livre à ajouter : pas de fichier "to-add" généré.');

            return;
        }

        // Livres annotés des ids de l'existant (null = à créer)
        $path = $preparer->writeSubset($prepared, array_keys($report->newPublications), 'to-add',
            fn (array $book) => (new BookLookup())->withIds($book));
        $relative = str_replace(realpath(storage_path('app/import')) . DIRECTORY_SEPARATOR, '', $path);

        $this->info(count($report->newPublications) . " livre(s) à ajouter exporté(s) dans : {$path}");
        $this->line("<fg=gray>Pour les importer : php artisan bdfi:import-books {$relative}</>");
    }

    private function renderReport(AnalysisReport $r): void
    {
        $this->newLine();
        $this->table(
            ['Livres', 'Importables', 'Ignorés', 'Auteurs à créer', 'Titres à créer', 'Cycles à créer'],
            [[$r->total, $r->publications, count($r->skipped),
              count($r->newAuthors), count($r->newTitles), count($r->newCycles)]],
        );

        $this->renderMissing('Éditeurs introuvables (livres ignorés)', $r->missingPublishers);
        $this->renderMissing('Collections introuvables (livres ignorés)', $r->missingCollections);

        if ($r->alreadyImported) {
            $this->warn(count($r->alreadyImported) . ' livre(s) déjà en base ou en doublon (même ISBN, ou sans ISBN : même livre), ignorés : #'
                . implode(', #', array_slice($r->alreadyImported, 0, 15))
                . (count($r->alreadyImported) > 15 ? ' ...' : ''));
        }

        $this->renderList('Auteurs qui seraient créés', array_values($r->newAuthors));
        $this->renderList('Titres qui seraient créés', array_map(
            fn ($t) => $t['title'] . ' (' . implode(', ', $t['authors']) . ')', array_values($r->newTitles)));
        $this->renderList('Cycles qui seraient créés', array_values($r->newCycles));
        $this->renderCyclesMissingParent($r->cyclesMissingParent);
        $this->renderIdMismatches($r->idMismatches);
        $this->renderList('Publications qui seraient ajoutées', array_map(
            fn ($n, $p) => "#{$n} {$p['title']} (" . implode(', ', $p['authors']) . ") — {$p['publisher']}"
                . ($p['collection'] !== '' ? " / {$p['collection']}" . ($p['number'] !== '' ? " n°{$p['number']}" : '') : '')
                . ($p['isbn'] !== '' ? " [{$p['isbn']}]" : '')
                . ($p['reprints'] > 0 ? " (+ {$p['reprints']} retirage(s))" : ''),
            array_keys($r->newPublications),
            $r->newPublications,
        ));

        $this->newLine();
        $this->line('<fg=gray>Analyse indicative : la base peut changer avant l\'import réel. Ajoutez -v pour les listes complètes.</>');
    }

    /**
     * Ids du fichier qui ne correspondent plus à la base (élément supprimé, ou modifié depuis l'analyse) :
     * la recherche complète par nom a été appliquée à leur place.
     */
    private function renderIdMismatches(array $mismatches): void
    {
        if (!$mismatches) {
            return;
        }

        $this->newLine();
        $this->warn('Ids du fichier qui ne correspondent plus à la base (' . count($mismatches) . ') — recherche par nom appliquée');
        $this->table(['Type', 'Id', 'Dans le fichier'], array_map(fn ($m) => [$m['type'], $m['id'], $m['label']], $mismatches));
    }

    /**
     * Information seulement : cycles existant en base sans parent, alors que le fichier leur en indique un.
     * @param array<string, array{cycle: string, parent: string, id: int, books: int[]}> $cycles
     */
    private function renderCyclesMissingParent(array $cycles): void
    {
        if (!$cycles) {
            return;
        }

        $limit = $this->getOutput()->isVerbose() ? PHP_INT_MAX : 20;

        $this->newLine();
        $this->warn('Cycles en base sans parent, alors que le fichier en indique un (' . count($cycles) . ') — non modifiés');
        $this->table(['Cycle (id)', 'Parent indiqué', 'Livres', 'N° (extrait)'], array_map(
            fn ($c) => ["{$c['cycle']} (#{$c['id']})", $c['parent'], count($c['books']),
                '#' . implode(', #', array_slice($c['books'], 0, 8)) . (count($c['books']) > 8 ? ' ...' : '')],
            array_slice(array_values($cycles), 0, $limit),
        ));
        if (count($cycles) > $limit) {
            $this->line('  ... et ' . (count($cycles) - $limit) . ' autre(s) (-v pour tout voir)');
        }
    }

    /** @param array<string, int[]> $missing */
    private function renderMissing(string $title, array $missing): void
    {
        if (!$missing) {
            return;
        }

        $this->newLine();
        $this->warn($title);
        $this->table(['Nom', 'Livres', 'N° (extrait)'], array_map(
            fn ($name, $nums) => [$name, count($nums), '#' . implode(', #', array_slice($nums, 0, 10)) . (count($nums) > 10 ? ' ...' : '')],
            array_keys($missing), $missing,
        ));
    }

    private function renderList(string $title, array $items): void
    {
        if (!$items) {
            return;
        }

        $limit = $this->getOutput()->isVerbose() ? PHP_INT_MAX : 20;

        $this->newLine();
        $this->info($title . ' (' . count($items) . ')');
        foreach (array_slice($items, 0, $limit) as $item) {
            $this->line("  - {$item}");
        }
        if (count($items) > $limit) {
            $this->line('  ... et ' . (count($items) - $limit) . ' autre(s)');
        }
    }
}