<?php

namespace App\Filament\Resources\ReprintResource\Pages;

use App\Filament\Resources\ReprintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReprints extends ListRecords
{
    protected static string $resource = ReprintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
