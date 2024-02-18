<?php

namespace App\Filament\Resources\ReprintResource\Pages;

use App\Filament\Resources\ReprintResource;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReprint extends EditRecord
{
    protected static string $resource = ReprintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->url(static::getResource()::getUrl())
                ->button()
                ->color('info'),
            Actions\DeleteAction::make(),
        ];
    }
}
