<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Filament\Resources\CollectionResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Models\Collection;
use App\Models\Publication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Enums\CollectionType;
use App\Enums\CollectionPeriodicity;
use App\Enums\CollectionSupport;
use App\Enums\CollectionFormat;
use App\Enums\CollectionGenre;
use App\Enums\CollectionCible;
use App\Enums\QualityStatus;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static ?string $modelLabel = 'Collection';
    protected static ?string $pluralModelLabel = 'Collections';
    protected static ?string $navigationLabel = 'Collections';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup  = 'Publications';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'fullname';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Collection : " . $record->name;
    }

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', SlugService::createSlug(Collection::class, 'slug', $state)))
                        ->required()
                        ->maxLength(128),
                    Forms\Components\TextInput::make('slug')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Pour info, l\'URL qui sera utilisée (non modifiable manuellement)')
                        ->label('Slug'),
                    Forms\Components\TextInput::make('shortname')
                        ->label('Nom court')
                        ->required()
                        ->maxLength(128),
                    Forms\Components\TextInput::make('alt_names')
                        ->label('Variantes de nom ou autres noms usuels. Les séparer par des virgules.')
                        ->maxLength(512),
                    Forms\Components\Select::make('publisher_id')
                        ->label('Editeur')
                        ->relationship('publisher', 'name')
                        ->searchable(['name']),
                    Forms\Components\Select::make('parent_id')
                        ->label('Collection "parente"')
                        ->helperText('A utiliser pour les sous-groupes ou séries d\'une collection principale')
                        ->relationship('parent', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Collection $record) => "{$record->name} ( {$record->publisher->name} )")
                        ->searchable(['name']),
                    Forms\Components\Select::make('publisher2_id')
                        ->label('Editeur 2')
                        ->helperText('Pour les collections multi-éditeurs')
                        ->relationship('publisher2', 'name')
                        ->searchable(['name']),
                    Forms\Components\Select::make('publisher3_id')
                        ->label('Editeur 3')
                        ->helperText('Pour les collections multi-éditeurs')
                        ->relationship('publisher3', 'name')
                        ->searchable(['name']),
                    Forms\Components\TextInput::make('year_start')
                        ->label('Création')
                        ->helperText('Année de publication du premier opus de la collection ou du groupe. Obligatoire... mais peut être mis à 0 temporairement.')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('year_end')
                        ->label('Fin')
                        ->helperText('Année du dernier opus de la collection ou du groupe.')
                        ->numeric(),
                    Forms\Components\Select::make('type')
                        ->label('Type d\'ensemble d\'ouvrages')
                        ->enum(CollectionType::class)
                        ->options(CollectionType::class)
                        ->required(),
                    Forms\Components\Select::make('periodicity')
                        ->label('Periodicité (si applicable)')
                        ->enum(CollectionPeriodicity::class)
                        ->options(CollectionPeriodicity::class)
                        ->default(CollectionPeriodicity::NA)
                        ->required(),
                    Forms\Components\Select::make('support')
                        ->label('Type de support')
                        ->enum(CollectionSupport::class)
                        ->options(CollectionSupport::class)
                        ->required(),
                    Forms\Components\Select::make('format')
                        ->label('Format')
                        ->enum(CollectionFormat::class)
                        ->options(CollectionFormat::class),
                    Forms\Components\TextInput::make('dimensions')
                        ->helperText('Dimension des ouvrages du groupe, en millimètres (+/- 1 mm) sous la forme "largeur x hauteur", exemple "110 x 178". Ne renseigner que si uniforme.')
                        ->maxLength(10),
                    Forms\Components\Select::make('cible')
                        ->label('Cible d\'âge')
                        ->enum(CollectionCible::class)
                        ->options(CollectionCible::class),
                    Forms\Components\Select::make('genre')
                        ->label('Genre')
                        ->enum(CollectionGenre::class)
                        ->options(CollectionGenre::class),
                    Forms\Components\TextInput::make('forum_topic_id')
                            ->helperText('Le numéro du topic de la collection sur le forum (par exemple, 3728 est exofiction). Cf. "Accès rapide à une collection". 0 si inexistant ou inconnu.')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50000)
                            ->default(0),
                    Forms\Components\Textarea::make('information')
                        ->label('Informations notables à afficher')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                        Forms\Components\Textarea::make('private')
                            ->label('Informations de travail ou privées - Non affichées')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('quality')
                        ->label('Etat d\'avancement fiche')
                        ->enum(QualityStatus::class)
                        ->options(QualityStatus::class)
                        ->default(QualityStatus::VIDE)
                        ->required(),
                    Forms\Components\TextInput::make('sigle_bdfi')
                        ->label('Sigle BDFI')
                        ->helperText('Sigle BDFI - Temporaire - Ne pas ajouter, ne pas modifier.' )
                        ->maxLength(8),
            ])
            ->columns(2),

            Section::make('Historique de la fiche / donnée')
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
                        ->label('Détruite-désactivée par'),
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
                Tables\Columns\TextColumn::make('publisher.name')
                    ->label('Editeur')
                    ->limit(20)
                    ->sortable(),
                Tables\Columns\TextColumn::make('publisher2.name')
                    ->label('Editeur 2')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('publisher3.name')
                    ->label('Editeur 3')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
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
                Tables\Columns\TextColumn::make('alt_names')
                    ->label('Autres noms')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('support')
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_parent')
                    ->label('Fils ?')
                    ->boolean()
                    ->state(function (Collection $record): bool {
                        return $record->parent_id <> "";
                    }),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Fils de')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('sigle_bdfi')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('year_start')
                    ->label('Création')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_end')
                    ->label('Fin')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('format')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('dimensions')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cible')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('genre')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

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
            RelationGroup::make('Sous-collections', [
                RelationManagers\SubcollectionsRelationManager::class,
            ]),
            RelationGroup::make('Ouvrages', [
                RelationManagers\PublicationsRelationManager::class,
            ]),
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'view' => Pages\ViewCollection::route('/{record}'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
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
