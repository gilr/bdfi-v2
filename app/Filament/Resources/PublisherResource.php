<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublisherResource\Pages;
use App\Filament\Resources\PublisherResource\RelationManagers;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;
use App\Enums\PublisherType;
use App\Enums\QualityStatus;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class PublisherResource extends Resource
{
    protected static ?string $model = Publisher::class;

    protected static ?string $modelLabel = 'Editeur';
    protected static ?string $pluralModelLabel = 'Editeurs';
    protected static ?string $navigationLabel = 'Editeurs';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup  = 'Publications';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Editeur : " . $record->name;
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
                            ->helperText('Nom principal, le plus usité ou actuel')
                            ->maxLength(128)
                            ->required(),
                        Forms\Components\TextInput::make('alt_names')
                            ->label('Autre dénominations')
                            ->helperText('Autres formes du nom, séparé par des virgules')
                            ->maxLength(512),
                        Forms\Components\Select::make('country_id')
                            ->label('Pays')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->enum(PublisherType::class)
                            ->options(PublisherType::class)
                            ->default(PublisherType::EDITEUR)
                            ->required(),
                        Forms\Components\TextInput::make('year_start')
                            ->label('Création')
                            ->helperText('Année de création de l\'éditeur. Obligatoire... mais peut-être mis à 0 temporairement.')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('year_end')
                            ->label('Fin')
                            ->helperText('Année de fermeture, faillite ou rachat (avec disparition du nom) de la maison d\'édition.')
                            ->numeric(),
                        Forms\Components\Textarea::make('address')
                            ->label('Localisation')
                            ->helperText("Dernière localisation (ville, addresse) connue.")
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('information')
                            ->helperText("Description succincte, informations, localisation antérieure... Pas de copier-coller de textes trouvés sur Internet ! (mais on peut s'inspirer pour résumer bien sur !).")
                            ->rows(3)
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
                            ->helperText('Sigle BDFI seulement en l\'absence de collection - Temporaire' )
                            ->maxLength(8),
                        Forms\Components\Textarea::make('private')
                            ->label('Infos de travail et privées')
                            ->helperText("Informations diverses de travail : doutes, choses à vérifier, ce qu'il faudrait revoir...")
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->limit(40)
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
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Pays')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_start')
                    ->label('Création')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_end')
                    ->label('Fin')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quality')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Etat')
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
            RelationManagers\CollectionsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublishers::route('/'),
            'create' => Pages\CreatePublisher::route('/create'),
            'view' => Pages\ViewPublisher::route('/{record}'),
            'edit' => Pages\EditPublisher::route('/{record}/edit'),
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
