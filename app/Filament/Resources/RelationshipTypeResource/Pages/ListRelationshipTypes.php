<?php

namespace App\Filament\Resources\RelationshipTypeResource\Pages;

use App\Filament\Resources\RelationshipTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRelationshipTypes extends ListRecords
{
    protected static string $resource = RelationshipTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
