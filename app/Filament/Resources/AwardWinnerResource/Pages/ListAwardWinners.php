<?php

namespace App\Filament\Resources\AwardWinnerResource\Pages;

use App\Filament\Resources\AwardWinnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAwardWinners extends ListRecords
{
    protected static string $resource = AwardWinnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
