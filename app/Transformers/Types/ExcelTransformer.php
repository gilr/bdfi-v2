<?php

namespace App\Transformers\Types;

use Maatwebsite\Excel\Facades\Excel;
use App\Transformers\FormatTransformer;
use Illuminate\Http\UploadedFile;

class ExcelTransformer implements FormatTransformer
{

    /**
     *   Fonction de passage immediat au CSV sans passer par le format pivot
     *   Pour le passage inverse, voir la classe CsvTransformer
    **/
    public function toCsv(UploadedFile $input): string
    {
        $rows = Excel::toArray([], $input)[0]; // première feuille

        return collect($rows)
            ->map(fn($row) => implode(';', array_map(fn($cell) => trim((string) $cell), $row)))
            ->implode("\n");
    }

    /**
     *   Transformation vers le format pivot
    **/
    public function toArray(array|string|UploadedFile $input): array
    {
        if (!$input instanceof UploadedFile) {
            throw new \InvalidArgumentException("ExcelTransformer attend un chemin de fichier Excel.");
        }

        $rawRows = Excel::toArray([], $input)[0]; // première feuille

        // Convertit en CSV brut
        $csv = collect($rawRows)
            ->map(fn($row) => implode(';', array_map(fn($cell) => trim((string) $cell), $row)))
            ->implode("\n");

        // Utilise CsvTransformer pour appliquer l’en-tête et le map()
        return (new CsvTransformer())->toArray($csv);
    }

    /**
     *   Transformation depuis le format pivot
    **/
    public function fromArray(array $data): string
    {
        // Réutilisation du CsvTransformer pour l'export texte
        return (new CsvTransformer())->fromArray($data);
    }
}


