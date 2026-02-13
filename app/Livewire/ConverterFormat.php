<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Route;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Transformers\Types\BBcodeTransformer;
use App\Transformers\Types\ColUtf8Transformer;
use App\Transformers\Types\ColTransformer;
use App\Transformers\Types\CsvTransformer;
use App\Transformers\Types\JsonTransformer;
use App\Transformers\Types\ExcelTransformer;

class ConverterFormat extends Component
{
    use WithFileUploads;

    public string $inputFormat = 'EXCEL';
    public string $outputFormat = 'BBCODE';
    public string $inputData = '';
    public string $outputData = '';
    public string $exportData = '';
    public $inputFile;
    public bool $wantFile = false;
    public int $uploadProgress = 0; // pour la barre de progression

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    public array $formats = [
        'EXCEL' => 'EXCEL (format CM)',
        'CSV' => 'CSV (format CM)',
        'BBCODE' => 'BBCODE (format type topic "inanna")',
        'JSON' => 'JSON (format générique réutilisable)',
        'COL UTF-8' => 'COL UTF-8 (format interne v1)',
        'COL CP437' => 'COL CP437 (format interne v1)',
        'PHP' => 'PHP (tableau, format pivot)',
    ];

    protected $rules = [
        'inputFile' => 'nullable|file|mimes:xlsx,xls,csv,txt',
    ];

    public function updatedInputFormat($value)
    {
        if ($this->outputFormat === $value) {
            // Choisir le premier format différent
            $this->outputFormat = collect($this->formats)
                ->keys()
                ->first(fn($key) => $key !== $value);
        }
    }

    public function convert()
    {
        if ($this->wantFile) {
            if (!$this->inputFile) {
                $this->addError('inputFile', 'Le fichier est en cours de téléchargement ou non sélectionné.');
                return;
            }
            $this->validate();
            $rawInput = file_get_contents($this->inputFile->getRealPath());
        }
        else
        {
            if (trim($this->inputData) === '') {
                $this->addError('inputData', 'Veuillez renseigner des données ou sélectionner un fichier.');
                return;
            }
            $rawInput = $this->inputData;
        }

        if ($this->inputFormat === 'EXCEL' && $this->outputFormat === 'CSV') {
            $this->exportData = (new ExcelTransformer())->toCsv($this->inputFile);
        }
        else if ($this->inputFormat === 'CSV' && $this->outputFormat === 'EXCEL') {
            $rawInput = $this->inputFile
                ? file_get_contents($this->inputFile->getRealPath())
                : $this->inputData;
            $this->exportData = (new CsvTransformer())->toExcel($rawInput);
        }
        else
        {
            // Conversion en tableau selon le format d'entrée
            $dataArray = match ($this->inputFormat) {
                'EXCEL' => (new ExcelTransformer())->toArray($this->inputFile),
                'CSV'   => (new CsvTransformer())->toArray($rawInput),
                'BBCODE'=> (new BBcodeTransformer())->toArray($rawInput),
                'JSON'  => (new JsonTransformer())->toArray($rawInput),
                'COL UTF-8' => (new ColUtf8Transformer())->toArray($rawInput),
                'COL CP437' => (new ColTransformer())->toArray($rawInput),
// Suppression par sécurité
//                'PHP'   => eval("return {$rawInput};"),
                default => throw new \Exception("Format d'entrée inconnu"),
            };

            // Conversion vers le format de sortie

            $this->exportData = match ($this->outputFormat) {
                'EXCEL' => (new ExcelTransformer())->fromArray($dataArray),
                'CSV'   => (new CsvTransformer())->fromArray($dataArray),
                'BBCODE'=> (new BBcodeTransformer())->fromArray($dataArray),
                'JSON'  => (new JsonTransformer())->fromArray($dataArray),
                'COL UTF-8' => (new ColUtf8Transformer())->fromArray($dataArray),
                'COL CP437' => (new ColTransformer())->fromArray($dataArray),
                'PHP'   => var_export($dataArray, true),
                default => throw new \Exception("Format de sortie inconnu"),
            };
        }
        $this->outputData = match ($this->outputFormat) {
            'COL CP437'   => iconv('CP437', 'UTF-8', $this->exportData),
            default => $this->exportData,
        };

        session([
            'exportData' => $this->exportData,
            'exportFormat' => strtolower($this->outputFormat),
        ]);
    }

    public function resetFile()
    {
        $this->inputFile = "";
        $this->wantFile = false;
    }

    public function render()
    {
        return view('livewire.converter-format');
    }
}
