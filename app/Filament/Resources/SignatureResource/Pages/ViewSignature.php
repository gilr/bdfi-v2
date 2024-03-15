<?php

namespace App\Filament\Resources\SignatureResource\Pages;

use App\Filament\Resources\SignatureResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Actions;
use App\Models\User;

class ViewSignature extends ViewRecord
{
    protected static string $resource = SignatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label ('Retour liste')
                ->url(static::getResource()::getUrl())
                ->button()
                ->color('info'),
            Actions\EditAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['created_by'] = $data['created_by'] ? User::find($data['created_by'])->name : "--";
        $data['updated_by'] = $data['updated_by'] ? User::find($data['updated_by'])->name : "--";
        $data['deleted_by'] = $data['deleted_by'] ? User::find($data['deleted_by'])->name : "--";

        return $data;
    }
}
