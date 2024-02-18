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
use App\Filament\Resources\AuthorResource;
use App\Enums\AuthorPublicationRole;
use App\Models\Author;

class AuthorsRelationManager extends RelationManager
{
    protected static string $relationship = 'authors';

    public function form(Form $form): Form
    {
        if ($form->getOperation() === 'edit')
        {
            return $form
            ->schema([
                Forms\Components\Select::make('role')
                    ->options(AuthorPublicationRole::class)
                    ->default(AuthorPublicationRole::AUTHOR)
                    ->required()
                ]);
        }
        else
        {
            // Re-utilisation de la form de vue/modification
            return AuthorResource::form($form);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn (Author $record): string => "{$record->name} {$record->first_name}")
            ->columns([
                Tables\Columns\TextColumn::make('fullName')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('role')
           ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->recordSelectSearchColumns(['name', 'first_name'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->helperText('Recherche par le nom OU le prénom'),
                        Forms\Components\Select::make('role')
                            ->options(AuthorPublicationRole::class)
                            ->default(AuthorPublicationRole::AUTHOR)
                            ->required()
                ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
//                Marche presque si ce n'est qu'il manque le revisionable id :-( :
                Tables\Actions\EditAction::make()->label('Changer role'),
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
                    ->recordSelectSearchColumns(['name', 'first_name'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->helperText('Recherche par le nom OU le prénom'),
                        Forms\Components\Select::make('role')
                            ->options(AuthorPublicationRole::class)
                            ->default(AuthorPublicationRole::AUTHOR)
                            ->required()
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
