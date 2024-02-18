<?php

namespace App\Filament\Resources\IllustratorResource\Pages;

use App\Filament\Resources\IllustratorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIllustrators extends ListRecords
{
    protected static string $resource = IllustratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
