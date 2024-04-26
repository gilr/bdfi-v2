<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

use App\Models\Stat;

class StatsV1Chart extends ChartWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static ?string $heading = 'Historique des référencements BDFI';

    protected function getData(): array
    {
//        $results = Stat::orderBy('date')->get();
        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                    'backgroundColor' => '#00FFFF',
                    'borderColor' => '#FF0000',
                    'color' => '#00FF00'
//               'fill' => true,
//               'borderColor' => 'rgb(75, 192, 192)',
//               'tension' => '0.1',
                ]
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
