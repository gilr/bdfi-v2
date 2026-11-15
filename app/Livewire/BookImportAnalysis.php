<?php

namespace App\Livewire;

use App\Bdfi\Import\BookAnalyzer;
use App\Bdfi\Import\BookImportPreparer;
use App\Bdfi\Import\BookLookup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

/**
 * Outils : équivalent web de "php artisan bdfi:import-books <fichier> --analyse".
 *
 * Aucune écriture en base. Fichiers écrits, comme en console : <nom>.converted.json et <nom>.to-add.json,
 * plus le fichier téléversé (storage/app/import/web/). Version simple : fichiers de MAX_BOOKS livres au plus,
 * analysés en entier (pas de tranche) ; pour un gros fichier, utiliser la commande.
 */
class BookImportAnalysis extends Component
{
    use WithFileUploads;

    // Comme ConverterFormat : interdit les appels directs au composant
    protected static $middlewares = ['auth.bdfiadmin', 'admin.maintenance'];

    /** Nombre maximal de livres d'un fichier analysé depuis le web */
    public const MAX_BOOKS = 100;

    /** Taille maximale d'un fichier (Ko) : écarte les gros fichiers avant leur conversion, qui serait longue */
    public const MAX_FILE_KB = 1024;

    /** Fichiers téléversés (et fichiers générés à partir d'eux) supprimés après ce nombre de jours */
    public const UPLOAD_RETENTION_DAYS = 7;

    private const IMPORT_DIR = 'app/import';
    private const UPLOAD_SUBDIR = 'web';
    private const EXTENSIONS = ['col', 'csv', 'json'];

    public string $source = 'existing';     // existing | upload
    public string $file = '';               // relatif à storage/app/import/
    public $upload;                         // fichier téléversé (Livewire)
    public string $encoding = 'auto';

    public ?array $result = null;           // résultat sérialisable (cf. AnalysisReport::toArray)
    public string $error = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasSysAdminRole(), 403); // Imports : sysadmin seul pour l'instant
    }

    /** Fichiers d'import disponibles (petits fichiers seulement, hors fichiers générés) */
    public function getFilesProperty(): array
    {
        return collect(File::allFiles($this->importDir()))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), self::EXTENSIONS, true))
            // Fichiers générés (conversion, analyse, export, rapports de comparaison et de correction)
            ->reject(fn ($f) => preg_match('/\.(converted|to-add|exported)\.json$|\.(diff|novelizations)\.csv$/', $f->getFilename()))
            ->filter(fn ($f) => $f->getSize() <= self::MAX_FILE_KB * 1024)
            ->map(fn ($f) => str_replace('\\', '/', $f->getRelativePathname()))
            ->sort()->values()->all();
    }

    public function analyse(BookImportPreparer $preparer, BookAnalyzer $analyzer): void
    {
        abort_unless(auth()->user()?->hasSysAdminRole(), 403); // Imports : sysadmin seul pour l'instant

        $this->reset('result', 'error');

        $this->validate([
            'source'   => 'in:existing,upload',
            'file'     => 'required_if:source,existing|nullable|string',
            'upload'   => 'required_if:source,upload|nullable|file|max:' . self::MAX_FILE_KB . '|extensions:' . implode(',', self::EXTENSIONS),
            'encoding' => 'in:auto,utf8,cp437,cp1252',
        ], [
            'file.required_if'   => 'Choisissez un fichier.',
            'upload.required_if' => 'Choisissez un fichier à téléverser.',
        ]);

        $this->purgeOldUploads();

        try {
            $file = $this->source === 'upload' ? $this->storeUpload() : $this->file;

            $path = realpath($this->importDir() . DIRECTORY_SEPARATOR . $file);
            if ($path && filesize($path) > self::MAX_FILE_KB * 1024) {
                throw new \InvalidArgumentException('Fichier trop volumineux pour une analyse web (' . self::MAX_FILE_KB . ' Ko max) : utilisez la commande.');
            }

            // Même chemin que la commande : résolution sécurisée dans storage/app/import, conversion, validation
            $prepared = $preparer->prepare($file, ['encoding' => $this->encoding]);
        } catch (Throwable $e) {
            $this->error = $e->getMessage();

            return;
        }

        $count = count($prepared->books);
        if ($count > self::MAX_BOOKS) {
            $this->error = "Le fichier contient {$count} livres : l'analyse web est limitée à " . self::MAX_BOOKS
                . ' livres. Découpez le fichier, ou utilisez la commande php artisan bdfi:import-books ' . $file . ' --analyse.';

            return;
        }

        $base = ['file' => $file, 'books' => $count];

        if ($prepared->errors) {
            $this->result = $base + [
                'errors'      => $this->formatErrors($prepared->errors, $prepared->books),
                'errorsTotal' => count($prepared->errors),
            ];

            return;
        }

        $report = $analyzer->analyse($prepared->books);

        $toAdd = $report->newPublications
            ? $preparer->writeSubset($prepared, array_keys($report->newPublications), 'to-add',
                fn (array $book) => (new BookLookup())->withIds($book))   // ids de l'existant (null = à créer)
            : null;

        // Livres ignorés : n° de livre => titre, éditeur / collection, raison (déjà en base, introuvable, doublon...)
        $skipped = [];
        foreach ($report->skipped as $n => $reason) {
            $book = $prepared->books[$n - 1] ?? [];
            $skipped[$n] = [
                'title'  => (string) ($book['titre'] ?? ''),
                'isbn'   => (string) ($book['isbn'] ?? ''),
                'reason' => $reason,
            ];
        }

        $this->result = $base + [
            'report'  => $report->toArray(),
            'skipped' => $skipped,
            'toAdd'   => $toAdd ? $this->relative($toAdd) : null,
        ];
    }

    /** Téléchargement du .to-add.json produit par la dernière analyse */
    public function downloadToAdd()
    {
        abort_unless(auth()->user()?->hasSysAdminRole(), 403); // Imports : sysadmin seul pour l'instant

        $relative = $this->result['toAdd'] ?? null;
        $path = $relative ? realpath($this->importDir() . DIRECTORY_SEPARATOR . $relative) : false;

        // Uniquement un fichier .to-add.json situé dans storage/app/import
        abort_unless($path && str_starts_with($path, $this->importDir() . DIRECTORY_SEPARATOR)
            && str_ends_with($path, '.to-add.json'), 404);

        return response()->download($path);
    }

    private function storeUpload(): string
    {
        $original = Str::slug(pathinfo($this->upload->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'fichier';
        $name = auth()->id() . '_' . date('Ymd_His') . '_' . $original . '.' . strtolower($this->upload->getClientOriginalExtension());

        // storage/app/import/web/<nom> (disque "local" = storage/app)
        $this->upload->storeAs('import/' . self::UPLOAD_SUBDIR, $name, 'local');

        return self::UPLOAD_SUBDIR . '/' . $name;
    }

    /** Supprime les fichiers de import/web/ plus anciens que UPLOAD_RETENTION_DAYS (téléversés et générés) */
    private function purgeOldUploads(): void
    {
        $dir = $this->importDir() . DIRECTORY_SEPARATOR . self::UPLOAD_SUBDIR;
        if (!is_dir($dir)) {
            return;
        }

        $limit = now()->subDays(self::UPLOAD_RETENTION_DAYS)->getTimestamp();
        foreach (File::files($dir) as $f) {
            if ($f->getMTime() < $limit) {
                File::delete($f->getPathname());
            }
        }
    }

    private function importDir(): string
    {
        return realpath(storage_path(self::IMPORT_DIR)) ?: storage_path(self::IMPORT_DIR);
    }

    private function relative(string $path): string
    {
        return str_replace('\\', '/', Str::after($path, $this->importDir() . DIRECTORY_SEPARATOR));
    }

    /** Erreurs de validation => lignes affichables */
    private function formatErrors(array $errors, array $books): array
    {
        return collect($errors)->map(fn ($details, $n) => [
            'n'        => $n,
            'title'    => Str::limit((string) ($books[$n - 1]['titre'] ?? ''), 60),
            'messages' => array_map(fn ($d) => $d['message'] . (is_scalar($d['value']) && (string) $d['value'] !== ''
                ? ' → « ' . Str::limit((string) $d['value'], 80) . ' »' : ''), $details),
        ])->values()->all();
    }

    public function render()
    {
        return view('livewire.book-import-analysis');
    }
}
