<?php

namespace App\Livewire;

use App\Models\Publication;
use Livewire\Component;
use Filament\Widgets\ChartWidget;
use App\Enums\GenreStat;
use App\Enums\PublicationSupport;
use App\Enums\PublicationFormat;

class PublicationsByFormatChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des publications avec répartition par format';
    public function getDescription(): ?string
    {
        return 'Nombre annuel de publications papier par format, nouvelles éditions incluses.';
    }
    protected function getType(): string
    {
        return 'bar'; // Stacked bar via options
    }

    protected function getData(): array
    {
        // Définir les genres à filtrer
        $selectedGenres = [
            GenreStat::SF->value,
            GenreStat::FANTASY->value,
            GenreStat::FANTASTIQUE->value,
            GenreStat::HYBRIDE->value,
        ];

        // Définir les format à prendre en compte
        $selectedFormats = [
            PublicationFormat::POCHE,
            PublicationFormat::MF,
            PublicationFormat::GF,
        ];

        // Liste des valeurs (ex: 'sf', 'fantasy', etc.)
        $formatValues = array_map(fn ($format) => $format->value, $selectedFormats);

        // Liste des labels (ex: 'Science-fiction', etc.)
        $formatLabels = array_map(fn ($format) => $format->getLabel(), $selectedFormats);

        // Récupérer les années distinctes et triées
        $years = Publication::query()
            ->whereNotNull('approximate_parution')
            ->selectRaw('DISTINCT SUBSTRING(approximate_parution, 1, 4) as year')
            ->orderBy('year')
            ->pluck('year')
            ->filter(fn ($year) => is_numeric($year)) // On garde que les années valides
            ->values()
            ->toArray();

        // Initialiser les datasets pour chaque format
        $datasets = [];
        foreach ($formatValues as $format) {
            $datasets[$format] = array_fill(0, count($years), 0);
        }

        // Récupérer les données groupées (année + format) pour les publications papier uniquement
        $data = Publication::query()
            ->where('support', PublicationSupport::PAPIER->value)
            ->whereIn('genre_stat', $selectedGenres)
            ->whereIn('format', $formatValues)
            ->whereNotNull('approximate_parution')
            ->selectRaw('SUBSTRING(approximate_parution, 1, 4) as year, format, COUNT(*) as count')
            ->groupBy('year', 'format')
            ->orderBy('year')
            ->get();

        // Remplir les datasets
        foreach ($data as $item) {
            $yearIndex = array_search($item->year, $years);
            if ($yearIndex !== false) {
                $datasets[$item->format->value][$yearIndex] = $item->count;
            }
        }

        // Préparer les datasets pour le graphique
        $colors = [
            PublicationFormat::POCHE->value => '#4f4f24',
            PublicationFormat::MF->value => '#7fff0e',
            PublicationFormat::GF->value => '#bf4F4F',
        ];

        $chartDatasets = [];
        foreach ($formatValues as $index => $formatValue) {
            $chartDatasets[] = [
                'label' => $formatLabels[$index], // label propre via énum
                'data' => $datasets[$formatValue],
                'backgroundColor' => $colors[$formatValue] ?? '#cccccc',
            ];
        }

        return [
            'labels' => $years,
            'datasets' => $chartDatasets,
        ];
    }

    // Ajouter les options pour empiler (stack) les barres
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true, // Empilement sur X
                ],
                'y' => [
                    'stacked' => true, // Empilement sur Y
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}

