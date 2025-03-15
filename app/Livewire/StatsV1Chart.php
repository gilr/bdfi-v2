<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use App\Models\Stat;

class StatsV1Chart extends ChartWidget
{
    public ?string $filter = 'references'; // Choix par défaut

    protected static ?string $pollingInterval = '60s';
//    protected static ?string $heading = 'Historique des statistiques';

    protected static ?string $maxHeight = '500px';

    public string $datasetType = 'default'; // Valeur par défaut si rien n'est passé

    protected function getData(): array
    {
        $stats = Stat::orderBy('date')->get();
        $dates = $stats->pluck('date')->map(fn ($date) => $date->format('Y-m-d'))->toArray();

        $datasets = match ($this->datasetType) {
            'references' => [
                ['label' => 'Références', 'data' => $stats->pluck('references')->toArray(), 'borderColor' => 'red'],
                ['label' => 'Romans', 'data' => $stats->pluck('novels')->toArray(), 'borderColor' => 'blue'],
                ['label' => 'Nouvelles', 'data' => $stats->pluck('short_stories')->toArray(), 'borderColor' => 'green'],
            ],
            'authors_series' => [
                ['label' => 'Auteurs', 'data' => $stats->pluck('authors')->toArray(), 'borderColor' => 'purple'],
                ['label' => 'Séries', 'data' => $stats->pluck('series')->toArray(), 'borderColor' => 'orange'],
            ],
            'collections_magazines_essays' => [
                ['label' => 'Recueils', 'data' => $stats->pluck('collections')->toArray(), 'borderColor' => 'pink'],
                ['label' => 'Revues & Fanzines', 'data' => $stats->pluck('magazines')->toArray(), 'borderColor' => 'cyan'],
                ['label' => 'Guides & Essais', 'data' => $stats->pluck('essays')->toArray(), 'borderColor' => 'brown'],
            ],
            'default' => [
                ['label' => 'Romans', 'data' => $stats->pluck('novels')->toArray(), 'borderColor' => 'blue'],
                ['label' => 'Nouvelles', 'data' => $stats->pluck('short_stories')->toArray(), 'borderColor' => 'green'],
            ],
        };

        return [
            'datasets' => array_map(fn ($dataset) => array_merge($dataset, ['borderWidth' => 2, 'backgroundColor' => 'transparent']), $datasets),
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

