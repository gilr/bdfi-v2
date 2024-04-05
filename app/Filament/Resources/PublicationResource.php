<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicationResource\Pages;
use App\Filament\Resources\PublicationResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Models\Author;
use App\Models\Title;
use App\Models\Reprint;
use App\Models\Collection;
use App\Models\Publication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;
use App\Enums\PublicationContent;
use App\Enums\PublicationSupport;
use App\Enums\PublicationStatus;
use App\Enums\PublicationFormat;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\AudienceTarget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static ?string $modelLabel = 'Publication';
    protected static ?string $pluralModelLabel = 'Publications';
    protected static ?string $navigationLabel = 'Publications';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup  = 'Publications';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Publication : " . $record->name;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Tabs::make('Label')
                ->tabs([
                Tabs\Tab::make('Général')
                    ->badge('!')
                    ->schema([
                        // Forms
                        Forms\Components\Select::make('status')
                            ->label('Etat de publication')
                            ->enum(PublicationStatus::class)
                            ->options(PublicationStatus::class)
                            ->default(PublicationStatus::PUBLIE)
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Titre de l\'ouvrage')
                            ->helperText('Titre indiqué en page titre intérieure (et non en couverture - Si différent, utiliser le champ "autres titres" pour le titre de couverture, dos, quatrième... Les titres, sous-titres, collections sont séparés par des tirets, exemple "Titre - Sous-titre".')
                            ->required()
                            ->maxLength(128),
                        Forms\Components\Select::make('publisher_id')
                            ->label('Editeur')
                            ->relationship('publisher', 'name')
                            ->helperText('Pour les collections, voir l\'onglet dédié')
                            ->searchable(['name']),
                        Forms\Components\Select::make('type')
                            ->label('Type de contenu')
                            ->enum(PublicationContent::class)
                            ->options(PublicationContent::class)
                            ->helperText('Type global, texte seul, groupe de textes, groupe de romans, revue/magazine/journal, non-fiction.')
                            ->required(),
                        Forms\Components\Select::make('support')
                            ->label('Type de support')
                            ->enum(PublicationSupport::class)
                            ->options(PublicationSupport::class)
                            ->default(PublicationSupport::PAPIER)
                            ->helperText('Papier par défaut, à modifier si besoin. Le support "Autre" corresponde aux cas particuliers, affiche, miroir, objet divers... A détailler alors dans la description.')
                            ->required(),
                        Forms\Components\TextInput::make('isbn')
                            ->helperText('Ne renseigner qu\'en cas de certitude : livre entre les mains, photo 4ième de couv, informations croisées (éditeur, BNF, libraire...).')
                            ->maxLength(18),
                        Forms\Components\TextInput::make('approximate_parution')
                            ->label('Date de parution')
                            ->helperText("Format 'AAAA-MM-00' (exemple : 1983-05-00). 'AAAA-00-00' si l\'année seule est connue, ou éventuellement 'AAAA-MM-JJ' si le jour est donné par l\'éditeur.")
                            ->regex('/([\-012][\-0-9]{3}-(T[1-4]-00|[0-9]{2}-[0-9]{2}))/')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('cover')
                            ->label('Illustrateur de couverture')
                            ->helperText('Ne renseigner qu\'en cas de certitude (signature visible sur l\'illustration, indication intérieure du livre, informations croisées).')
                            ->maxLength(256),
                        Forms\Components\TextInput::make('illustrators')
                            ->label('Illustrateurs intérieurs')
                            ->maxLength(512),
                        Forms\Components\TextInput::make('cycle')
                            ->label('Titre de série tel qu\'il apparait en page de titre')
                            ->helperText('Attention, les attachements aux séries se font au niveau des titres')
                            ->maxLength(128),
                        Forms\Components\TextInput::make('cyclenum')
                            ->label('Numéro dans la série indiqué dans l\'ouvrage')
                            ->maxLength(10),
                        Forms\Components\Toggle::make('is_hardcover')
                            ->label('Ouvrage relié')
                            ->required(),
                        Forms\Components\Toggle::make('has_dustjacket')
                            ->label('Avec jaquette')
                            ->required(),
                        Forms\Components\Toggle::make('has_coverflaps')
                            ->label('Avec couverture à rabats')
                            ->required(),
                        ])
                    ->columns(2),
                Tabs\Tab::make('BDFI Infos')
                    ->badge('!')
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible sur le site')
                            ->helperText('Décoché (gris), l\'ouvrage ne sera pas affiché. Permet de stocker des ouvrages limites ou des "faux-amis".')
                            ->default(True)
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Select::make('is_genre')
                            ->label('Appartenance au genres référencés')
                            ->enum(GenreAppartenance::class)
                            ->options(GenreAppartenance::class)
                            ->helperText('Attention, n\'influe pas sur l\'affichage ou non sur le site. Pour ça, voir le commutateur "Visible sur le site".')
                            ->required(),
                        Forms\Components\Select::make('genre_stat')
                            ->label('Genre général pour statistique')
                            ->helperText(' Si référencé sans trace SF-Fa-FY (par exemple Préhistorique ou Gore), indiquer "Autre".')
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
                Tabs\Tab::make('Infos livre')
                    ->schema([
                        Placeholder::make('No Label')
                            ->hiddenLabel()
                            ->content('Informations bibliographiques précises, imprimées ou mesurées sur le livre. D\'éventuelles autres informations imprimées et notables peuvent être indiquée dans la partie "informations".')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_verified')
                            ->label('Est vérifié ?')
                            ->helperText("Si vérifié livre entre les mains. Indique que les informations DL, AI, imprimeur... sont validées, mais aussi le sommaire réel (titres de début de page, et non sommaire).")
                            ->onColor('success')
                            ->offColor('danger')
                            ->required(),
                        Forms\Components\TextInput::make('verified_by')
                            ->label('Vérifié par')
                            ->helperText("Prénom ou identifiant du vérificateur - toujours le même de préférence sur l'ensemble des fiches. Si plusieurs vérificateurs, format \"user1; user2\".")
                            ->maxLength(256),

                        Forms\Components\TextInput::make('ai')
                            ->label('AI (Achevé d\'imprimé)')
                            ->helperText("Format 'AAAA-MM-JJ' (exemple : 1983-05-19). 'AAAA-00-00' si l'année seule est connue, et vide ou '0000-00-00' si l'AI n'est pas indiqué. Si le trimestre seul est indiqué, utilisez par exemple '1923-T3-00'. Si une seule date est imprimée (par exemple 'Publié le'), utilisez ce champ et précisez le dans le champ des autres infos imprimées.")
                            ->regex('/[\-012][\-0-9]{3}-(T[1-4]-00|[0-9]{2}-[0-9]{2})/')
                            ->Length(10),
                        Forms\Components\TextInput::make('dl')
                            ->label('DL(Dépot légal)')
                            ->helperText("Format 'AAAA-MM-JJ' (exemple : 1983-05-19). 'AAAA-00-00' si l'année seule est connue, et vide ou '0000-00-00' si le DL n'est pas indiqué. Si le trimestre seul est indiqué, utilisez par exemple '1923-T3-00'. Enfin 'A parution' est à utiliser si l\'ouvrage l\'indique")
                            ->regex('/([\-012][\-0-9]{3}-(T[1-4]-00|[0-9]{2}-[0-9]{2})|A parution)/')
                            ->Length(10),
                        Forms\Components\TextInput::make('printer')
                            ->label('Imprimeur')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('edition')
                            ->label('Xième édition')
                            ->helperText('Si indiqué dans le livre, noter l\'indication d\'édition, par exemple "1ère édition", "5ème tirage" ou "Edition VIII". Utilisé surtout dans les ouvrages anciens. Laisser vide sinon.')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('pagination')
                            ->label('Nombre total de page effectif')
                            ->helperText("Nombre de pages total (façon Gilles :-)) en comptant toutes les pages de fin, y compris blanches. Si présence de pages en chiffres romains au départ (la numérotion à 1 commençant ensuite), indiquer par exemple \"xiv + 256\".")
                            ->maxLength(32),
                        Forms\Components\TextInput::make('pages_dpi')
                            ->label('Dernière page numérotée')
                            ->helperText("Dernier numéro de page visible (façon BNF)")
                            ->numeric(),
                        Forms\Components\TextInput::make('pages_dpu')
                            ->label('Dernière page utile')
                            ->helperText("Dernier numéro de page utile (façon Christian :-))")
                            ->numeric(),
                        Forms\Components\Select::make('format')
                            ->label('Format')
                            ->enum(PublicationFormat::class)
                            ->options(PublicationFormat::class)
                            ->default(PublicationFormat::INCONNU)
                            ->required(),
                        Forms\Components\TextInput::make('dimensions')
                            ->helperText('Dimension de l\'ouvrage, en millimètres (+/- 1 mm) sous la forme "largeur x hauteur", exemple "110 x 178".')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('thickness')
                            ->label('Epaisseur')
                            ->helperText('Epaisseur de l\'ouvrage, en millimètres (+/- 1 mm, sans indiquer l\'unité). Se mesure sur la tranche haute ou basse, à distance suffisante du dos.')
                            ->maxLength(4),
                        Forms\Components\TextInput::make('printed_price')
                            ->label('Prix ou code prix')
                            ->helperText('Tel qu\'imprimé sur ou dans le livre. Par exemple "Code Prix LP13". Laisser vide si aucune indication, dans ce cas le prix "Approx" peut être utilisé')
                            ->maxLength(32),
                        ])
                    ->columns(2),
                Tabs\Tab::make('Infos "approx"')
                    ->schema([
                        Placeholder::make('No Label')
                            ->hiddenLabel()
                            ->content('Informations indicatives, déduites ou extérieures (éditeur, revendeur, autre site...)')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('approximate_pages')
                            ->label('Nombre de pages')
                            ->helperText('Nombre de pages indicatif, BNF ou revendeur...')
                            ->numeric(),
                        Forms\Components\TextInput::make('approximate_price')
                            ->label('Prix')
                            ->helperText('Prix indicatif, BNF ou revendeur...')
                            ->maxLength(32),
                        ])
                    ->columns(2),
                Tabs\Tab::make('Images')
                    ->schema([
                        Placeholder::make('No Label')
                            ->hiddenLabel()
                            ->content(new HtmlString('Imagerie. Le tout reste optionnel. Ordre de priorité : scan de couv, scan ccouv avec jaquette, 4ieème et dos. Pour tous : sans chemin, sans extension. <br /><b>ATTENTION :</b> il faudra se mettre d\'accord sur une règle de nommage unifiée'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cover_front')
                            ->label('Nom scan de couverture')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('cover_back')
                            ->label('Nom scan de la quatrième de couverture')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('cover_spine')
                            ->label('Nom scan du dos - Optionnel')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('withband_front')
                            ->label('Nom scan de couv avec bandeau - Optionnel')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('withband_back')
                            ->label('Nom scan de 4ième avec bandeau - Très optionnel')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('withband_spine')
                            ->label('Nom scan de dos avec bandeau - Très très optionnel !')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('dustjacket_front')
                            ->label('Nom scan de couv avec jaquette - Optionnel')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('dustjacket_back')
                            ->label('Nom scan de 4ième avec jaquette - Très optionnel !')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('dustjacket_spine')
                            ->label('Nom scan de dos avec jaquette - Très très optionnel !')
                            ->maxLength(64),
                        ])
                    ->columns(3),
                Tabs\Tab::make('Historique')
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
                ])
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible?')
                    ->boolean(),
                Tables\Columns\TextColumn::make('publisher.name')
                    ->label('Editeur')
                    ->limit(20)
                    ->sortable(),
                Tables\Columns\TextColumn::make('cycle')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cyclenum')
                    ->label('N° cycle')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('isbn')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cover')
                    ->label('Ill. couv.')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('illustrators')
                    ->label('Ill. int.')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('cover_front')
                    ->label('Scan')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_hardcover')
                    ->label('Relié')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_dustjacket')
                    ->label('Jaquette')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('dl')
                    ->label('DL')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ai')
                    ->label('AI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('edition')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagination')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('pages')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dimensions')
                    ->searchable(),
                Tables\Columns\TextColumn::make('thickness')
                    ->label('Epaisseur')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('printer')
                    ->label('Imprimeur')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('approximate_parution')
                    ->label('Parution')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('approximate_pages')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approximate_price')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('support')
                    ->searchable(),
                Tables\Columns\TextColumn::make('format')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('is_genre')
                    ->label('à ref.')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('genre_stat')
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
            ->filters([
                SelectFilter::make('status')
                    ->label('Etat de publication')
                    ->options(PublicationStatus::class),
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
            RelationGroup::make('Sommaire', [
                RelationManagers\TitlesRelationManager::class,
            ]),
            RelationGroup::make('Collections', [
                RelationManagers\CollectionsRelationManager::class,
            ]),
            RelationGroup::make('Retirages', [
                RelationManagers\ReprintsRelationManager::class,
            ]),
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublications::route('/'),
            'create' => Pages\CreatePublication::route('/create'),
            'view' => Pages\ViewPublication::route('/{record}'),
            'edit' => Pages\EditPublication::route('/{record}/edit'),
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
