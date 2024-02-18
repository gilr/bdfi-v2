<?php

namespace App\Filament\Resources\PublicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CollectionResource;
use App\Models\Collection;

class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';

    public function form(Form $form): Form
    {
        if ($form->getOperation() === 'edit')
        {
            return $form
                ->schema([
                Forms\Components\TextInput::make('order')
                    ->label ('ordre')
                    ->helperText ('Ordre de l\'ouvrage dans la collection')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->label ('Numéro')
                    ->helperText('Numéro dans la collection - Ne renseigner que si existe et est renseigné')
                    ->maxLength(16),
                ]);
        }
        else
        {
            // Re-utilisation de la form de vue/modification
            return CollectionResource::form($form);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type de contenu'),
                Tables\Columns\TextColumn::make('number')
                    ->label('Numéro'),
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordre'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->recordSelectSearchColumns(['name'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('order')
                            ->label ('ordre')
                            ->helperText ('Ordre de l\'ouvrage dans la collection')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('number')
                            ->label ('Numéro')
                            ->helperText('Numéro dans la collection - Ne renseigner que si existe et est renseigné')
                            ->maxLength(16),
                ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label('Changer numéros'),
                Tables\Actions\DetachAction::make(),
//                Tables\Actions\DeleteAction::make(),
//                Tables\Actions\ForceDeleteAction::make(),
//                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DetachBulkAction::make(),
//                    Tables\Actions\DeleteBulkAction::make(),
//                    Tables\Actions\RestoreBulkAction::make(),
//                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->recordSelectSearchColumns(['name'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('order')
                            ->label ('ordre')
                            ->helperText ('Ordre de l\'ouvrage dans la collection')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('number')
                            ->label ('Numéro')
                            ->helperText('Numéro dans la collection - Ne renseigner que si existe et est renseigné')
                            ->maxLength(16),
                ])
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
