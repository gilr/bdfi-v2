<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TitleResource\Pages;
use App\Filament\Resources\TitleResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Models\Title;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Section;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\TitleType;
use App\Enums\IsNovelization;
use App\Enums\AudienceTarget;
use App\Enums\TitleVariantType;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class TitleResource extends Resource
{
    protected static ?string $model = Title::class;

    protected static ?string $modelLabel = 'Oeuvre';
    protected static ?string $pluralModelLabel = 'Oeuvres';
    protected static ?string $navigationLabel = 'Oeuvres';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup  = 'Oeuvres';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Oeuvre : " . $record->name;
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
                            ->label('Titre du texte')
                            ->helperText('Titre tel qu\'il apparait en page de titre intérieure (et non sommaire. Si différent, l\'indiquer dans la zone information.')
                            ->required()
                            ->maxLength(256),
                        Forms\Components\Select::make('type')
                            ->enum(TitleType::class)
                            ->options(TitleType::class)
                            ->default(TitleType::INCONNU)
                            ->required(),
                        Forms\Components\TextInput::make('copyright')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('title_vo')
                            ->maxLength(512),
                        Forms\Components\Select::make('is_genre')
                            ->label('Appartenance au genre')
                            ->enum(GenreAppartenance::class)
                            ->options(GenreAppartenance::class)
                            ->required(),
                        Forms\Components\Select::make('genre_stat')
                            ->label('Genre général pour les stats')
                            ->enum(GenreStat::class)
                            ->options(GenreStat::class)
                            ->required(),
                        Forms\Components\Select::make('target_audience')
                            ->label('Public visé')
                            ->enum(AudienceTarget::class)
                            ->options(AudienceTarget::class)
                            ->default(AudienceTarget::INCONNU)
                            ->required(),
                        Forms\Components\TextInput::make('target_age')
                            ->label('Age cible')
                            ->helperText('Au format \'8+\', \'14+\' ...'),
                        Forms\Components\Select::make('parent_id')
                            ->label('Nom de texte parent')
                            ->helperText("Nota: l'attachement des variantes se fait depuis les variantes elles-mêmes.")
                            ->relationship('parent', 'name')
                            ->hiddenOn('create')
                            ->hint(fn (Title $record) => $record->parent_id == 0 ? "Pas de titre parent !" : new HtmlString('<a href="' . TitleResource::getUrl('view', ['record' => $record->parent_id]) . '">Aller à la fiche parent</a>'))
                            ->hintColor('info')
                            ->hintIcon('heroicon-m-link')
                            ->searchable(['name']),
                        Forms\Components\Select::make('parent_id')
                            ->label('Nom de texte parent')
                            ->helperText("Nota: l'attachement des variantes se fait depuis les variantes elles-mêmes.")
                            ->relationship('parent', 'name')
                            ->visibleOn('create')
                            ->searchable(['name']),
                        Forms\Components\Select::make('variant_type')
                            ->label('Première publi ou type de variante')
                            ->enum(TitleVariantType::class)
                            ->options(TitleVariantType::class)
                            ->default(TitleVariantType::PREMIER)
                            ->required(),

                        Forms\Components\Select::make('is_novelization')
                            ->label('Novelisation ?')
                            ->enum(IsNovelization::class)
                            ->options(IsNovelization::class)
                            ->default(IsNovelization::NON)
                            ->required(),
                        Forms\Components\Toggle::make('is_serial')
                            ->label('Episode feuilleton, serialisation ?')
                            ->helperText("Oui s'il s'agit de la publication d'une simple partie d'un texte. Ce texte devra alors être rattaché au texte complet, en tant que variante 'épisode'.")
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_fullserial')
                            ->label('Feuilleton complet paru en morceaux ?')
                            ->helperText("Oui s'il s'agit d'un texte complet paru en morceau. Renseignez alors le champ suivant pour les conditions de publication.")
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('serial_info')
                            ->label('Info de publication si feuilleton.')
                            ->helperText("Indiquer les publications concernées par cette publication en épisode, par exemple 'Revue XYZ n° 72 de juin 2015 au n° 83 de janvier 2016'.")
                            ->default("...")
                            ->maxLength(512),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible sur le site')
                            ->default(True)
                            ->required(),
                        Forms\Components\TextInput::make('translators')
                            ->label('Nom des traducteurs')
                            ->maxLength(512),
                        Forms\Components\Textarea::make('synopsis')
                            ->label('Synopsis')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('copyright_fr')
                            ->label('Date 1ère publi FR')
                            ->helperText("Date de première publication du texte en Français")
                            ->maxLength(10),
                        Forms\Components\TextInput::make('pub_vo')
                            ->label('Informations de première publication VO')
                            ->maxLength(256),
                        Forms\Components\Textarea::make('information')
                            ->label('Informations notables à afficher')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('private')
                            ->label('Informations de travail ou privées - Non affichées')
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
                    ->limit(30, "<span class='!bg-indigo-100 dark:!bg-indigo-800'>&mldr;</span>")
                    ->html()
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
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('is_genre')
                    ->label('Genre ?')
                    ->searchable(),
                Tables\Columns\IconColumn::make('parent_exist')
                    ->label('Variant ?')
                    ->boolean()
                    ->state(function (Title $record): bool {
                        return $record->parent_id <> 0;
                    }),
                Tables\Columns\TextColumn::make('parent.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('copyright')
                    ->searchable(),
                Tables\Columns\TextColumn::make('is_novelization')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('title_vo')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('copyright_fr')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('translators')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('genre_stat')
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
                RelationGroup::make('Auteurs', [
                    RelationManagers\AuthorsRelationManager::class,
                ]),
                RelationGroup::make('Cycles', [
                    RelationManagers\CyclesRelationManager::class,
                ]),
                RelationGroup::make('Publications', [
                    RelationManagers\PublicationsRelationManager::class,
                ]),
                RelationGroup::make('Renommages', [
                    RelationManagers\VariantsRelationManager::class,
                ]),
            ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTitles::route('/'),
            'create' => Pages\CreateTitle::route('/create'),
            'view' => Pages\ViewTitle::route('/{record}'),
            'edit' => Pages\EditTitle::route('/{record}/edit'),
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
