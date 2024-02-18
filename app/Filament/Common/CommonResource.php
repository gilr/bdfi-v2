<?php

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

// Tentative qui ne marche pas :(
//   class AnnouncementResource extends Resource
// => La class CommonResource n'est pas trouvée...

class CommonResource extends Resource
{
    /**
     * Common procedure to display the history of a record
     */
    protected function commonMetadata()
    {
        return [
            Forms\Components\TextInput::make('created_by')
                ->label('Créée par'),
            Forms\Components\DatePicker::make('created_at')
                ->label('le'),
            Forms\Components\TextInput::make('updated_by')
                ->label('Mise à jour par'),
            Forms\Components\DatePicker::make('updated_at')
                ->label('le'),
            Forms\Components\TextInput::make('deleted_by')
                ->label('Désactivée par'),
            Forms\Components\DatePicker::make('deleted_at')
                ->label('le'),
        ];
    }
}
