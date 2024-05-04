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
use App\Filament\Resources\TitleResource;
use App\Models\Title;

class TitlesRelationManager extends RelationManager
{
    protected static string $relationship = 'titles';

    public function form(Form $form): Form
    {
        if ($form->getOperation() === 'edit')
        {
            return $form
                ->schema([
                Forms\Components\TextInput::make('order')
                    ->label ('ordre de l\'élément dans l\'ouvrage.')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('start_page')
                    ->label ('Numéro de première page du titre ou début du texte.')
                    ->helperText('Indiquer le numéro de page du titre s\'il est une ou plusieurs pages avant le début de texte.')
                    ->maxLength(8),
                Forms\Components\TextInput::make('end_page')
                    ->label ('Numéro de dernière page du texte.')
                    ->maxLength(8),
                Forms\Components\TextInput::make('level')
                    ->label ('"Niveau" de l\'élément')
                    ->helperText('"0.0" pour l\'équivalent à la publication elle même. "1.0" pour un texte, collection ou section de premier niveau. "2.0" pour un élément de second niveau, etc...')
                    ->numeric()
                    ->default(0)
                    ->required(),
                ]);
        }
        else
        {
            // Re-utilisation de la form de vue/modification
            return TitleResource::form($form);
        }
  }

    public function table(Table $table): Table
    {
        return $table
//            ->recordTitleAttribute('name')
            ->recordTitle(fn (Title $record): string => "{$record->name} - {$record->type->getLabel()} ({$record->copyright} {$record->title_vo})")
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type de contenu'),
                Tables\Columns\TextColumn::make('level')
                    ->label('Niveau'),
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordre'),
                Tables\Columns\TextColumn::make('start_page')
                    ->label('Page début'),
                Tables\Columns\TextColumn::make('end_page')
                    ->label('Page fin'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->recordSelectSearchColumns(['name', 'title_vo'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('order')
                            ->label ('ordre de l\'élément dans l\'ouvrage.')
                            ->helperText('L\'ordre commence à 1')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('start_page')
                            ->label ('Numéro de première page du titre ou début du texte.')
                            ->helperText('Indiquer le numéro de page du titre s\'il est une ou plusieurs pages avant le début de texte')
                            ->maxLength(8),
                        Forms\Components\TextInput::make('end_page')
                            ->label ('Numéro de dernière page du texte')
                            ->maxLength(8),
                        Forms\Components\TextInput::make('level')
                            ->label ('"Niveau" de l\'élément')
                            ->helperText('"0.0" pour l\'équivalent à la publication elle même. "1.0" pour un texte, collection ou section de premier niveau. "2.0" pour un élément de second niveau, etc...')
                            ->numeric()
                            ->default(0)
                            ->required(),
                ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label('Changer positions'),
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
//                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->recordSelectSearchColumns(['name', 'title_vo'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('order')
                            ->label ('ordre de l\'élément dans l\'ouvrage.')
                            ->helperText('L\'ordre commence à 1')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('start_page')
                            ->label ('Numéro de première page du titre ou début du texte.')
                            ->helperText('Indiquer le numéro de page du titre s\'il est une ou plusieurs pages avant le début de texte')
                            ->maxLength(8),
                        Forms\Components\TextInput::make('end_page')
                            ->label ('Numéro de dernière page du texte')
                            ->maxLength(8),
                        Forms\Components\TextInput::make('level')
                            ->label ('"Niveau" de l\'élément')
                            ->helperText('"0.0" pour l\'équivalent à la publication elle même. "1.0" pour un texte, collection ou section de premier niveau. "2.0" pour un élément de second niveau, etc...')
                            ->numeric()
                            ->default(0)
                            ->required(),
                ])
            ])
            ->defaultSort('order', 'asc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
