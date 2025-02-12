<?php

namespace App\Filament\Resources\AwardCategoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AssociateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AwardWinnerResource;

class AwardWinnersRelationManager extends RelationManager
{
    protected static string $relationship = 'award_winners';

    public function getTableHeading(): ?string
    {
        return "Gagnants de cette catégorie du prix";
    }

    public function form(Form $form): Form
    {
        // Re-utilisation de la form de vue/modification
        return AwardWinnerResource::form($form);
    }

    public function table(Table $table): Table
    {
        // Re-utilisation de la table des catégories
        return AwardWinnerResource::table($table)
            ->recordTitleAttribute('name')
            ->inverseRelationship('category')
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->defaultSort('year', 'desc')
            ->headerActions([
//                Tables\Actions\AssociateAction::make()
//                    ->recordSelectSearchColumns(['name']),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                  Tables\Actions\EditAction::make(),
//                Tables\Actions\DissociateAction::make(),
//                Tables\Actions\DeleteAction::make(),
//                Tables\Actions\ForceDeleteAction::make(),
//                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
//                Tables\Actions\AssociateAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
