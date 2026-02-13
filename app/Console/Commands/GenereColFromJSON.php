<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Transformers\FormatTransformerManager;

class GenereColFromJSON extends Command
{
    protected $signature = 'convert:json-to-col {file=input.json}';
    protected $description = 'Convertit un fichier JSON en format COL';

    public function handle(FormatTransformerManager $manager): int
    {
        $filePath = storage_path('app/' . $this->argument('file'));

        if (!file_exists($filePath)) {
            $this->error("Fichier introuvable : {$filePath}");
            return 1;
        }

        $jsonContent = file_get_contents($filePath);
        $dataArray = json_decode($jsonContent, true);

        if (!is_array($dataArray)) {
            $this->error("Le contenu JSON est invalide.");
            return 1;
        }

        try {
            $transformer = $manager->get('col');
            $colContent = $transformer->fromArray($dataArray);
        } catch (\Exception $e) {
            $this->error("Erreur de transformation : " . $e->getMessage());
            return 1;
        }

        $outputPath = storage_path('app/json.col');
        file_put_contents($outputPath, $colContent);

        $this->info("✅ Fichier COL généré : $outputPath");
        return 0;
    }
}
