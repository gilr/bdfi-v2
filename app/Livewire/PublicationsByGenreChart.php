<?php

namespace App\Livewire;

use App\Models\Publication;
use Livewire\Component;
use Filament\Widgets\ChartWidget;
use App\Enums\GenreStat;
use App\Enums\PublicationSupport;


class PublicationsByGenreChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des publications avec répartition par genre';
    public function getDescription(): ?string
    {
        return 'Nombre annuel de publications papier par genre, nouvelles éditions incluses.';
    }
    protected function getType(): string
    {
        return 'bar'; // Stacked bar via options
    }

    protected function getData(): array
    {
        // Définir les genres à prendre en compte
        $selectedGenres = [
            GenreStat::SF,
            GenreStat::FANTASY,
            GenreStat::FANTASTIQUE,
            GenreStat::HYBRIDE,
        ];

        // Liste des valeurs (ex: 'sf', 'fantasy', etc.)
        $genreValues = array_map(fn ($genre) => $genre->value, $selectedGenres);

        // Liste des labels (ex: 'Science-fiction', etc.)
        $genreLabels = array_map(fn ($genre) => $genre->getLabel(), $selectedGenres);

        // Récupérer les années distinctes et triées
        $years = Publication::query()
            ->whereNotNull('approximate_parution')
            ->selectRaw('DISTINCT SUBSTRING(approximate_parution, 1, 4) as year')
            ->orderBy('year')
            ->pluck('year')
            ->filter(fn ($year) => is_numeric($year)) // On garde que les années valides
            ->values()
            ->toArray();

        // Initialiser les datasets pour chaque genre
        $datasets = [];
        foreach ($genreValues as $genre) {
            $datasets[$genre] = array_fill(0, count($years), 0);
        }

        // Récupérer les données groupées (année + genre) pour les publications papier uniquement
        $data = Publication::query()
     //       ->where('support', 'papier')
            ->whereIn('genre_stat', $genreValues)
            ->whereNotNull('approximate_parution')
            ->selectRaw('SUBSTRING(approximate_parution, 1, 4) as year, genre_stat, COUNT(*) as count')
            ->groupBy('year', 'genre_stat')
            ->orderBy('year')
            ->get();

        // Remplir les datasets
        foreach ($data as $item) {
            $yearIndex = array_search($item->year, $years);
            if ($yearIndex !== false) {
                $datasets[$item->genre_stat->value][$yearIndex] = $item->count;
            }
        }

        // Préparer les datasets pour le graphique
        $colors = [
            GenreStat::SF->value => '#1f77b4',
            GenreStat::FANTASY->value => '#ff7f0e',
            GenreStat::FANTASTIQUE->value => '#2F4F4F',
            GenreStat::HYBRIDE->value => '#e62728',
        ];

        $chartDatasets = [];
        foreach ($genreValues as $index => $genreValue) {
            $chartDatasets[] = [
                'label' => $genreLabels[$index], // label propre via énum
                'data' => $datasets[$genreValue],
                'backgroundColor' => $colors[$genreValue] ?? '#cccccc',
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

