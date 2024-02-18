<?php

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PublicationResource;
use App\Enums\PublicationContent;
use App\Enums\PublicationSupport;
use App\Enums\PublicationFormat;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\AudienceTarget;

class PublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'publications';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Titre de l\'ouvrage')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('publisher_id')
                    ->label('Editeur')
                    ->relationship('publisher', 'name')
                    ->searchable(['name']),
                Section::make('Position dans la relation avec la collection')
                    ->schema([
                        Forms\Components\TextInput::make('order')->required(),
                        Forms\Components\TextInput::make('number')->required(),
                     ])
                    ->columns(2),
                Forms\Components\Select::make('type')
                    ->label('Type de contenu')
                    ->options(PublicationContent::class),
                Forms\Components\Select::make('support')
                    ->label('Type de support')
                    ->options(PublicationSupport::class)
                    ->default(PublicationSupport::PAPIER),
                Forms\Components\Toggle::make('is_visible')
                    ->label('Visible sur le site')
                    ->default(True),
                Forms\Components\Select::make('is_genre')
                    ->label('Appartenance au genres référencés')
                    ->options(GenreAppartenance::class),
                Forms\Components\TextInput::make('isbn')
                    ->maxLength(18),
                Forms\Components\TextInput::make('approximate_parution')
                    ->label('Date de parution')
                    ->maxLength(10),
                Forms\Components\TextInput::make('ai')
                    ->label('AI (Achevé d\'imprimé)')
                    ->Length(10),
                Forms\Components\TextInput::make('dl')
                    ->label('DL(Dépot légal)')
                    ->Length(10),
                ])
                  ->columns(2);
}

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('number')
                    ->label('n°'),
                Tables\Columns\TextColumn::make('order')
                    ->label('ordre'),
                Tables\Columns\TextColumn::make('approximate_parution')
                    ->label('Parution')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dl')
                    ->label('DL')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ai')
                    ->label('AI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('order')
                            ->numeric(),
                        Forms\Components\TextInput::make('number')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
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
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('order')
                            ->numeric(),
                        Forms\Components\TextInput::make('number')
                            ->required(),
                    ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
