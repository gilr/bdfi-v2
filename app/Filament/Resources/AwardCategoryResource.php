<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwardCategoryResource\Pages;
use App\Filament\Resources\AwardCategoryResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\AwardResource\RelationManagers\AwardCategoriesRelationManager;
use App\Models\AwardCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Enums\AwardCategoryType;
use App\Enums\AwardCategoryGenre;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class AwardCategoryResource extends Resource
{
    protected static ?string $model = AwardCategory::class;


    protected static ?string $modelLabel = 'Catégorie de prix';
    protected static ?string $pluralModelLabel = 'Catégories de prix';
    protected static ?string $navigationLabel = 'Catégories';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup  = 'Prix';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'fullname';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Catégorie prix : " . $record->name;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                   ->schema([
                        Forms\Components\Select::make('award_id')
                            ->label('Prix')
                            ->relationship('award', 'name')
                            ->hiddenOn(AwardCategoriesRelationManager::class)
//                            ->getOptionLabelFromRecordUsing(fn (Award $record) => "{$record->Name}")
                            ->searchable(['name'])
                            ->required(),
                        Forms\Components\TextInput::make('internal_order')
                            ->label('Ordre d\'affichage dans le prix')
                            ->helperText('Optionnel - Pour surcharger l\'ordre d\'affichage normal (par type : auteur, roman, etc...)')
                            ->numeric(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set, ?string $state) => $set('slug', SlugService::createSlug(AwardCategory::class, 'slug', $state)))
                            ->maxLength(128)
                            ->required(),
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Pour info, l\'URL qui sera utilisée (non modifiable manuellement)')
                            ->label('Slug'),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->enum(AwardCategoryType::class)
                            ->options(AwardCategoryType::class)
                            ->required(),
                        Forms\Components\Select::make('genre')
                            ->enum(AwardCategoryGenre::class)
                            ->options(AwardCategoryGenre::class)
                            ->required(),
                        Forms\Components\TextInput::make('subgenre')
                            ->label('Sous-genre')
                            ->maxLength(256),
                        Forms\Components\Textarea::make('information')
                            ->label('Description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('award.name')
                    ->label('Prix')
                    ->limit(15)
                    ->hiddenOn(AwardCategoriesRelationManager::class)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom de la catégorie')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('internal_order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('genre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subgenre')
                    ->label('Sous-genre')
                    ->limit(15, "<span class='!bg-indigo-100 dark:!bg-indigo-800'>&mldr;</span>")
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('j M Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('créé par')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Supprimé le')
                    ->dateTime('j M Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('destructor.name')
                    ->label('par')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            RelationGroup::make('Gagnants', [
                RelationManagers\AwardWinnersRelationManager::class,
            ]),
        ];
    }

    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAwardCategories::route('/'),
            'create' => Pages\CreateAwardCategory::route('/create'),
            'view' => Pages\ViewAwardCategory::route('/{record}'),
            'edit' => Pages\EditAwardCategory::route('/{record}/edit'),
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
