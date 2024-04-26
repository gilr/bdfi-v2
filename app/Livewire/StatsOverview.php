<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Publication;
use App\Models\Title;
use App\Models\Collection;
use App\Models\Cycle;
use App\Models\Reprint;
use App\Models\Author;

class StatsOverview extends BaseWidget
{

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        return [
            Stat::make('Auteurs', Author::count())->description('Nombre de signatures d\'auteurs')
                ->extraAttributes(['style' => 'background-color: rgb(187 247 208);'
            ]),
            Stat::make('Collections', Collection::count())->description('Nombre de collections et de groupes d\'ouvrages'),
            Stat::make('Publications', Publication::where('status', 'paru')->count())
                ->extraAttributes(['style' => 'background-color: rgb(187 247 208);'
            ]),
            Stat::make('Retirages', Reprint::count())->description('Nombre de retirages recensés'),
            Stat::make('Oeuvres', Title::where('variant_type', '!=', 'virtuel')->count())->description('Nombre de textes et groupes de textes')
                ->extraAttributes(['style' => 'background-color: rgb(187 247 208);'
            ]),
            Stat::make('Séries', Cycle::count())->description('Nombre de cycles, séries et sous-séries'),
        ];
    }
}
