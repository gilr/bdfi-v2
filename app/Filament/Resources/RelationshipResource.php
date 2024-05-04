<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RelationshipResource\Pages;
use App\Models\Relationship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Models\Author;

class RelationshipResource extends Resource
{
    protected static ?string $model = Relationship::class;

    protected static ?string $modelLabel = 'Relation';
    protected static ?string $pluralModelLabel = 'Relations';
    protected static ?string $navigationLabel = 'Relations';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup  = 'Auteurs';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('author1_id')
                            ->label('Auteur 1')
                            ->relationship('author1', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->searchable(['name', 'first_name'])
                            ->helperText('Sélectionner un auteur en tapant un extrait de son prénom OU de son nom')
                            ->required(),
                        Forms\Components\Select::make('author2_id')
                            ->label('Auteur 2')
                            ->relationship('author2', 'name')
                            ->different('author1_id')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->searchable(['name', 'first_name'])
                            ->required(),
                        Forms\Components\Select::make('relationship_type_id')
                            ->label('Relation auteur 1 -- auteur 2')
                            ->relationship('relationship_type', 'name')
                            ->columnSpan(2)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Historique fiche')
                    ->schema([
                        Forms\Components\TextInput::make('created_by')
                            ->label('Créée par'),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->native(false)
                            ->displayFormat('l j M Y, à G:i')
                            ->label('le'),
                        Forms\Components\TextInput::make('updated_by')
                            ->label('Mise à jour par'),
                        Forms\Components\DatePicker::make('updated_at')
                            ->native(false)
                            ->displayFormat('l j M Y, à G:i')
                            ->label('le'),
                        Forms\Components\TextInput::make('deleted_by')
                            ->label('Désactivée par'),
                        Forms\Components\DatePicker::make('deleted_at')
                            ->native(false)
                            ->displayFormat('l j M Y, à G:i')
                            ->label('le'),
                        ])
                    ->visibleOn('view')
                    ->columns(2)
                    ->collapsed()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('author1.fullName')
                    ->label('Auteur 1'),
                Tables\Columns\TextColumn::make('author2.fullName')
                    ->label('Auteur 2'),
                Tables\Columns\TextColumn::make('relationship_type.name')
                    ->label('Relation 1 - 2'),
                //
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('mis à jour')
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                    Tables\Actions\ForceDeleteBulkAction::make(),
//                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRelationships::route('/'),
            'create' => Pages\CreateRelationship::route('/create'),
            'view' => Pages\ViewRelationship::route('/{record}'),
            'edit' => Pages\EditRelationship::route('/{record}/edit'),
        ];
    }    
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
