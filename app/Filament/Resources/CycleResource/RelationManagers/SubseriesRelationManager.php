<?php

namespace App\Filament\Resources\CycleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CycleResource;

class SubseriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subseries';

    public function form(Form $form): Form
    {
        // Re-utilisation de la form de vue/modification
        return CycleResource::form($form);
    }

    public function table(Table $table): Table
    {
        return CycleResource::table($table)
            ->recordTitleAttribute('name')
            ->inverseRelationship('parent')
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
//                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
//                Tables\Actions\DeleteAction::make(),
//                Tables\Actions\ForceDeleteAction::make(),
//                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DissociateBulkAction::make(),
//                    Tables\Actions\DeleteBulkAction::make(),
//                    Tables\Actions\RestoreBulkAction::make(),
//                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
//                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
