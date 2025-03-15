<?php

namespace App\Livewire;

use App\Models\Publication;
use Livewire\Component;
use Filament\Widgets\ChartWidget;
use App\Enums\GenreStat;
use App\Enums\PublicationSupport;


class PublicationsBySupportChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des publications avec répartition par support';
    public function getDescription(): ?string
    {
        return 'Nombre annuel de publications par support, nouvelles éditions incluses.';
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

        // Définir les support à prendre en compte
        $selectedSupports = [
            PublicationSupport::PAPIER,
            PublicationSupport::NUMERIQUE,
            PublicationSupport::AUDIO,
        ];

        // Liste des valeurs (ex: 'sf', 'fantasy', etc.)
        $supportValues = array_map(fn ($support) => $support->value, $selectedSupports);

        // Liste des labels (ex: 'Science-fiction', etc.)
        $supportLabels = array_map(fn ($support) => $support->getLabel(), $selectedSupports);

        // Récupérer les années distinctes et triées
        $years = Publication::query()
            ->whereNotNull('approximate_parution')
            ->selectRaw('DISTINCT SUBSTRING(approximate_parution, 1, 4) as year')
            ->orderBy('year')
            ->pluck('year')
            ->filter(fn ($year) => is_numeric($year)) // On garde que les années valides
            ->values()
            ->toArray();

        // Initialiser les datasets pour chaque support
        $datasets = [];
        foreach ($supportValues as $support) {
            $datasets[$support] = array_fill(0, count($years), 0);
        }

        // Récupérer les données groupées (année + support) pour les publications papier uniquement
        $data = Publication::query()
            ->whereIn('genre_stat', $selectedGenres)
            ->whereIn('support', $supportValues)
            ->whereNotNull('approximate_parution')
            ->selectRaw('SUBSTRING(approximate_parution, 1, 4) as year, support, COUNT(*) as count')
            ->groupBy('year', 'support')
            ->orderBy('year')
            ->get();

        // Remplir les datasets
        foreach ($data as $item) {
            $yearIndex = array_search($item->year, $years);
            if ($yearIndex !== false) {
                $datasets[$item->support->value][$yearIndex] = $item->count;
            }
        }

        // Préparer les datasets pour le graphique
        $colors = [
            PublicationSupport::PAPIER->value => "#ff7f0e",
            PublicationSupport::NUMERIQUE->value => "#1f77b4",
            PublicationSupport::AUDIO->value => "#e62728",
        ];

        $chartDatasets = [];
        foreach ($supportValues as $index => $supportValue) {
            $chartDatasets[] = [
                'label' => $supportLabels[$index], // label propre via énum
                'data' => $datasets[$supportValue],
                'backgroundColor' => $colors[$supportValue] ?? '#cccccc',
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

