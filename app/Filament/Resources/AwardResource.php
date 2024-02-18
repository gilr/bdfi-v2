<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwardResource\Pages;
use App\Filament\Resources\AwardResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Models\Award;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class AwardResource extends Resource
{
    protected static ?string $model = Award::class;

    protected static ?string $modelLabel = 'Prix';
    protected static ?string $pluralModelLabel = 'Prix';
    protected static ?string $navigationLabel = 'Prix';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup  = 'Prix';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Prix : " . $record->name;
    }

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
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->helperText('Nom principal, le plus courant, francisé lorsqu\'il existe')
                            ->maxLength(128)
                            ->required(),
                        Forms\Components\Select::make('country_id')
                            ->label('Pays')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('alt_names')
                            ->label('Autres dénominations')
                            ->helperText('Autres formes du nom, séparé par des virgules')
                            ->maxLength(512),
                        Forms\Components\TextInput::make('given_for')
                            ->label('Décerné pour')
                            ->helperText('Résumé court des formats et genres récompensés')
                            ->maxLength(256)
                            ->required(),
                        Forms\Components\TextInput::make('year_start')
                            ->label('Année de création')
                            ->helperText('Année de la première récompense')
                            ->minValue(1900)
                            ->numeric()
                            ->maxLength(4)
                            ->required(),
                        Forms\Components\TextInput::make('year_end')
                            ->label('Année de fin')
                            ->helperText('Année de la dernière récompense, vide si encore actif.')
                            ->numeric()
                            ->maxLength(4),
                        Forms\Components\TextInput::make('url')
                            ->url()
                            ->columnSpan(2)
                            ->maxLength(256),
                        Forms\Components\Textarea::make('information')
                            ->rows(5)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->required(),
                        ])
                    ->columns(2),
                //
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->limit(25)
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Pays')
                    ->sortable(),
                Tables\Columns\TextColumn::make('alt_names')
                    ->label('Autres dénominations')
                    ->limit(40, "<span class='!bg-indigo-100 dark:!bg-indigo-800  '>&mldr;</span>")
                    ->html()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_start')
                    ->label('Début')
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_end')
                    ->label('Fin')
                    ->sortable(),
                Tables\Columns\IconColumn::make('url_exist')
                    ->label('URL ?')
                    ->boolean()
                    ->state(function (Award $record): bool {
                        return $record->url <> "";
                    }),
                //
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('mis à jour')
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('name', 'asc');
    }
    
    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Catégories', [
                RelationManagers\AwardCategoriesRelationManager::class,
            ]),
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAwards::route('/'),
            'create' => Pages\CreateAward::route('/create'),
            'view' => Pages\ViewAward::route('/{record}'),
            'edit' => Pages\EditAward::route('/{record}/edit'),
        ];
    }    
}
