<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorResource\Pages;
use App\Filament\Resources\AuthorResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Models\Author;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use App\Enums\AuthorGender;
use App\Enums\QualityStatus;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $modelLabel = 'Auteur';
    protected static ?string $pluralModelLabel = 'Auteurs';
    protected static ?string $navigationLabel = 'Auteurs';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup  = 'Auteurs';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'fullname';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Auteur : " . $record->fullname;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identification')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->maxLength(32)
                            ->helperText('Forme classique "Nom", exemple "Poe", "Levilain-Clément", "La Motte-Fouqué", "Balzac" (sans le "de")...')
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->maxLength(32),
                        Forms\Components\TextInput::make('legal_name')
                            ->label('Nom légal')
                            ->helperText('ATTENTION ! Inutile si constitué des seuls prénom et nom renseignés plus haut. Forme "Prénom(s) Nom" clasique. Sert à indiquer un nom légal plus complet, avec plus de prénoms par exemple.')
                            ->maxLength(128),
                        Forms\Components\Select::make('gender')
                            ->label('H/F & co')
                            ->enum(AuthorGender::class)
                            ->options(AuthorGender::class)
                            ->default(AuthorGender::INCONNU)
                            ->required(),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('La fiche doit s\'afficher (si décoché, elle sera cachée)')
                            ->onColor('success')
                            ->required(),
                        Forms\Components\Toggle::make('is_pseudonym')
                            ->label('Ce nom est un pseudonyme')
                            ->onColor('warning')
                            ->required(),
                        Forms\Components\TextInput::make('alt_names')
                            ->label('Autres formes de la signature')
                            ->columnSpanFull()
                            ->helperText("Variantes d'écriture, écriture dans la langue d'origine, slave par exemple. Les formes multiples sont séparées par des virgules.")
                            ->maxLength(512),
                    ])
                    ->columns(2),

                Section::make('Dates et biographie')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->label('Pays')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('country2_id')
                            ->relationship('country2', 'name')
                            ->label('Pays 2 (double nationatlité)')
                            ->searchable(),
                        Forms\Components\TextInput::make('birth_date')
                            ->label('Né le')
                            ->helperText("Format 'AAAA-MM-JJ' (exemple : 1983-05-19). 'AAAA-00-00' si l'année seule est connue, et vide ou '0000-00-00' si la date est inconnue. Les formats '1410-circa' ou '-500-circa' sont également acceptés.")
                            ->maxLength(10),
                        Forms\Components\TextInput::make('birthplace')
                            ->label('Né à')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('date_death')
                            ->label('Décédé le')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('place_death')
                            ->label('Lieux de décès')
                            ->maxLength(64),
                        Forms\Components\Textarea::make('information')
                            ->label('Courte biographie')
                            ->helperText("Biographie succincte. Pas de copier-coller de textes trouvés sur Internet (mais on peut s'inspirer pour résumer bien sur).")
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('private')
                            ->label('Infos de travail et privées')
                            ->helperText("Informations privées (que l'auteur ne souhaite pas voir diffusées) ou de travail : doutes, choses à vérifier, ce qu'il faudrait revoir...")
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nom_bdfi')
                            ->label('Ancien nommage interne BDFI')
                            ->helperText('Temporaire - Pour lien avec la page BDFI, si elle existe. Forme "NOM Prénom", exemple "POE Edgar Allan".')
                            ->maxLength(64),
                        Forms\Components\Select::make('quality')
                            ->label('Etat d\'avancement fiche')
                            ->enum(QualityStatus::class)
                            ->options(QualityStatus::class)
                            ->default(QualityStatus::VIDE)
                            ->required(),
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
                Tables\Columns\TextColumn::make('quality')
                    ->label('Etat fiche')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fullName')
                    ->label('Nom complet'),
//                    ->sortable()
//                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Nom légal')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('alt_names')
                    ->label('Autres formes')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_pseudonym')
                    ->label('Pseu')
                    ->boolean(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('H/F')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Né le')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('birthplace')
                    ->label('Né à')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_death')
                    ->label('Décés')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('place_death')
                    ->label('Décédé à')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Pays'),
                Tables\Columns\TextColumn::make('country2.name')
                    ->label('Pays 2')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Filter::make('is_pseu')
                    ->label('Pseudonymes')
                    ->query(fn (Builder $query): Builder => $query->where('is_pseudonym', true))
                    ->toggle(),
                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options(AuthorGender::class),
                Filter::make('is_hidden')
                    ->label('Cachés')
                    ->query(fn (Builder $query): Builder => $query->where('is_visible', false))
                    ->toggle(),
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
                RelationGroup::make('Refs & Pseudos', [
                    RelationManagers\ReferencesRelationManager::class,
                    RelationManagers\SignaturesRelationManager::class,
                ]),
                RelationGroup::make('Sites web', [
                    RelationManagers\WebsitesRelationManager::class,
                ]),
                RelationGroup::make('Publications', [
                    RelationManagers\PublicationsRelationManager::class,
                ]),
                RelationGroup::make('Titres', [
                    RelationManagers\TitlesRelationManager::class,
                ]),
            ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'view' => Pages\ViewAuthor::route('/{record}'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
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
