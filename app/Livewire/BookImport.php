<?php

namespace App\Livewire;

use App\Bdfi\Import\BookImporter;
use App\Bdfi\Import\BookImportPreparer;
use App\Bdfi\Import\ImportOptions;
use App\Bdfi\Import\WebInteractor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;

/**
 * Outils : équivalent web de "php artisan bdfi:import-books <fichier> [--dry-run]".
 *
 * Simulation (dry-run : tout est annulé à la fin) ou import réel. Pas de mode pas-à-pas ni livre par livre.
 * Mêmes limites que l'analyse web (BookImportAnalysis::MAX_BOOKS, MAX_FILE_KB). Un seul import à la fois.
 * Les publications créées portent l'administrateur connecté en created_by (Userstamps).
 */
class BookImport extends Component
{
    // Comme ConverterFormat : interdit les appels directs au composant
    protected static $middlewares = ['auth.bdfiadmin', 'admin.maintenance'];

    private const IMPORT_DIR = 'app/import';
    private const EXTENSIONS = ['col', 'csv', 'json'];
    private const LOCK = 'bdfi-book-import';

    public string $file = '';               // relatif à storage/app/import/
    public string $mode = 'dry-run';        // dry-run | real
    public bool $confirmed = false;         // import réel : confirmation explicite
    public string $encoding = 'auto';

    public ?array $result = null;
    public string $error = '';

    public function mount(string $file = ''): void
    {
        abort_unless(auth()->user()?->hasSysAdminRole(), 403); // Imports : sysadmin seul pour l'instant

        // Fichier proposé par la page d'analyse (?file=...) : seulement s'il fait partie de la liste
        if ($file !== '' && in_array($file, $this->files, true)) {
            $this->file = $file;
        }
    }

    /** Fichiers importables : petits fichiers d'import, .to-add.json compris (hors autres fichiers générés) */
    public function getFilesProperty(): array
    {
        return collect(File::allFiles($this->importDir()))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), self::EXTENSIONS, true))
            ->reject(fn ($f) => preg_match('/\.(converted|exported)\.json$|\.(diff|novelizations)\.csv$/', $f->getFilename()))
            ->filter(fn ($f) => $f->getSize() <= BookImportAnalysis::MAX_FILE_KB * 1024)
            ->map(fn ($f) => str_replace('\\', '/', $f->getRelativePathname()))
            ->sort()->values()->all();
    }

    /** Environnement et base visés (bandeau d'information) */
    public function getEnvironmentProperty(): array
    {
        return ['env' => app()->environment(), 'database' => DB::connection()->getDatabaseName()];
    }

    public function updatedMode(): void
    {
        $this->confirmed = false;
    }

    public function import(BookImportPreparer $preparer): void
    {
        abort_unless(auth()->user()?->hasSysAdminRole(), 403); // Imports : sysadmin seul pour l'instant

        $this->reset('result', 'error');

        $this->validate([
            'file'      => 'required|string',
            'mode'      => 'in:dry-run,real',
            'confirmed' => $this->mode === 'real' ? 'accepted' : 'boolean',
            'encoding'  => 'in:auto,utf8,cp437,cp1252',
        ], [
            'file.required'      => 'Choisissez un fichier.',
            'confirmed.accepted' => 'Cochez la confirmation pour lancer un import réel.',
        ]);

        if (!in_array($this->file, $this->files, true)) {
            $this->error = 'Fichier non disponible pour l\'import (absent, trop volumineux ou fichier généré).';

            return;
        }

        // Un seul import à la fois (double clic, deux onglets, deux administrateurs)
        $lock = Cache::lock(self::LOCK, 900);
        if (!$lock->get()) {
            $this->error = 'Un import est déjà en cours : réessayez dans quelques instants.';

            return;
        }

        $dryRun = $this->mode !== 'real';
        $ui = new WebInteractor();

        try {
            @set_time_limit(300);

            $prepared = $preparer->prepare($this->file, ['encoding' => $this->encoding]);

            $count = count($prepared->books);
            if ($count > BookImportAnalysis::MAX_BOOKS) {
                $this->error = "Le fichier contient {$count} livres : l'import web est limité à " . BookImportAnalysis::MAX_BOOKS
                    . " livres. Utilisez la commande php artisan bdfi:import-books {$this->file}.";

                return;
            }

            if ($prepared->errors) {
                $this->error = count($prepared->errors) . ' livre(s) invalide(s) : lancez d\'abord l\'analyse du fichier pour voir le détail.';

                return;
            }

            $stats = (new BookImporter(new ImportOptions(dryRun: $dryRun, stepByStep: false, bookByBook: false), $ui))
                ->run($prepared->books);

            $this->result = [
                'dryRun' => $dryRun,
                'books'  => $count,
                'stats'  => $stats,
                'log'    => $ui->log(),
            ];
        } catch (Throwable $e) {
            // Le livre en cours est annulé ; en import réel, les livres précédents restent enregistrés
            $this->error = 'Import interrompu : ' . Str::limit($e->getMessage(), 500)
                . ($dryRun ? ' (simulation : rien n\'a été conservé).' : ' Les livres importés avant l\'erreur sont conservés.');
            $this->result = ['dryRun' => $dryRun, 'books' => null, 'stats' => null, 'log' => $ui->log()];
        } finally {
            $lock->release();
            $this->confirmed = false;   // une nouvelle confirmation pour chaque import réel
        }
    }

    private function importDir(): string
    {
        return realpath(storage_path(self::IMPORT_DIR)) ?: storage_path(self::IMPORT_DIR);
    }

    public function render()
    {
        return view('livewire.book-import');
    }
}
