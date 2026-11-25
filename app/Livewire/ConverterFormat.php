<?php

namespace App\Livewire;

use App\Bdfi\BookConverter\BookFormatRegistry;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

/**
 * Outils : conversion entre formats de fichier livre(s), sur la base du format pivot
 * (BookFormatRegistry / BookFormatHandler) utilisé par l'import/export BDFI.
 *
 * COL, CSV et JSON passent tous les deux par le format pivot (array de livres) : l'import
 * (texte/fichier -> pivot) est disponible pour les 3, l'export (pivot -> texte) seulement
 * pour JSON pour l'instant (ColConverter/CsvConverter::export() restent à écrire).
 * BBCODE et EXCEL sont prévus au catalogue mais non implémentés (ni l'un ni l'autre sens).
 */
class ConverterFormat extends Component
{
    use WithFileUploads;

    // Comme BookImport : interdit les appels directs au composant
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    private const NOT_IMPLEMENTED = ['bbcode', 'excel'];

    /** Seul format dont l'export (pivot -> texte) est écrit pour l'instant */
    private const EXPORT_IMPLEMENTED = ['json'];

    /** Formats texte (saisissables dans la zone de texte) ; EXCEL est binaire : fichier seul */
    private const TEXT_FORMATS = ['col', 'csv', 'json', 'bbcode'];

    /** Formats dont la lecture dépend d'un encodage source (fichiers .col / .csv anciens, souvent CP437) */
    private const ENCODED_FORMATS = ['col', 'csv'];

    public array $formats = [
        'col'    => 'COL (format interne v1)',
        'csv'    => 'CSV (export Excel, tabulations)',
        'json'   => 'JSON (format pivot, générique)',
        'bbcode' => 'BBCODE (forum type « inanna ») — non implémenté',
        'excel'  => 'EXCEL (.xlsx) — non implémenté',
    ];

    public string $inputFormat = 'csv';
    public string $outputFormat = 'json';
    public string $encoding = 'auto';   // lecture fichier : auto | utf8 | cp437 | cp1252
    public string $inputData = '';
    public $inputFile;
    public bool $wantFile = false;

    public string $outputData = '';
    public string $exportData = '';
    public string $error = '';

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    protected function rules(): array
    {
        return [
            'inputFile' => ['nullable', 'file', 'max:10240'],
            'encoding'  => ['in:auto,utf8,cp437,cp1252'],
        ];
    }

    public function updatedInputFormat(string $value): void
    {
        if ($this->outputFormat === $value) {
            // Priorité à un format de sortie réellement implémenté, sinon le premier différent (de toute façon bloqué)
            $this->outputFormat = collect(self::EXPORT_IMPLEMENTED)->first(fn ($key) => $key !== $value)
                ?? collect($this->formats)->keys()->first(fn ($key) => $key !== $value);
        }
    }

    /** Zone de texte proposée pour tout format texte (pas pour EXCEL, binaire) */
    public function getIsTextInputProperty(): bool
    {
        return in_array($this->inputFormat, self::TEXT_FORMATS, true);
    }

    /**
     * Formats de sortie proposés (hors format d'entrée) : seul JSON a un export écrit pour l'instant,
     * les autres restent affichés mais annotés et non sélectionnables.
     */
    public function isOutputImplemented(string $key): bool
    {
        return in_array($key, self::EXPORT_IMPLEMENTED, true);
    }

    public function getOutputFormatsProperty(): array
    {
        return collect($this->formats)
            ->except([$this->inputFormat])
            ->map(fn ($label, $key) => in_array($key, self::EXPORT_IMPLEMENTED, true) || str_contains($label, 'non implémenté')
                ? $label
                : "{$label} — export non implémenté")
            ->all();
    }

    /** Choix de l'encodage source : seulement utile en upload de fichier COL/CSV */
    public function getNeedsEncodingProperty(): bool
    {
        return $this->wantFile && in_array($this->inputFormat, self::ENCODED_FORMATS, true);
    }

    public function resetFile(): void
    {
        $this->inputFile = null;
        $this->wantFile = false;
    }

    public function convert(BookFormatRegistry $registry): void
    {
        // Exécution interdite aux invités (mode découverte)
        abort_unless(auth()->user()?->hasProponentRole(), 403);

        $this->reset('error', 'outputData', 'exportData');

        if (in_array($this->inputFormat, self::NOT_IMPLEMENTED, true)) {
            $this->error = "Le format d'entrée « {$this->formats[$this->inputFormat]} » n'est pas encore implémenté.";

            return;
        }

        if (!in_array($this->outputFormat, self::EXPORT_IMPLEMENTED, true)) {
            $this->error = "L'export au format « {$this->formats[$this->outputFormat]} » n'est pas encore implémenté (seul JSON est disponible en sortie pour l'instant).";

            return;
        }

        if ($this->wantFile) {
            if (!$this->inputFile) {
                $this->addError('inputFile', 'Le fichier est en cours de téléchargement ou non sélectionné.');

                return;
            }
            $this->validate();
            $path = $this->inputFile->getRealPath();
            $options = ['encoding' => $this->encoding];
            $tempPath = null;
        } else {
            if (trim($this->inputData) === '') {
                $this->addError('inputData', 'Veuillez renseigner des données ou sélectionner un fichier.');

                return;
            }
            $path = $tempPath = $this->writeTempFile($this->inputData, $this->inputFormat);
            $options = ['encoding' => 'utf8']; // saisi directement en UTF-8 dans le navigateur
        }

        try {
            $books = $registry->for($this->inputFormat)->import($path, $options);
        } catch (Throwable $e) {
            $this->error = 'Erreur de lecture (' . $this->formats[$this->inputFormat] . ') : ' . $e->getMessage();

            return;
        } finally {
            if ($tempPath !== null) {
                File::delete($tempPath);
            }
        }

        try {
            $this->exportData = $registry->for($this->outputFormat)->export($books);
        } catch (\LogicException) {
            $this->error = "L'export au format « {$this->formats[$this->outputFormat]} » n'est pas encore implémenté.";

            return;
        } catch (Throwable $e) {
            $this->error = 'Erreur de conversion (' . $this->formats[$this->outputFormat] . ') : ' . $e->getMessage();

            return;
        }

        $this->outputData = $this->exportData;

        session([
            'exportData' => $this->exportData,
            'exportFormat' => $this->outputFormat,
        ]);
    }

    /** Écrit la saisie texte dans un fichier temporaire (extension = format, pour la détection du handler) */
    private function writeTempFile(string $content, string $format): string
    {
        $dir = storage_path('app/convert-tmp');
        File::ensureDirectoryExists($dir);

        $path = $dir . DIRECTORY_SEPARATOR . Str::uuid() . '.' . $format;
        File::put($path, $content);

        return $path;
    }

    public function render()
    {
        return view('livewire.converter-format');
    }
}
