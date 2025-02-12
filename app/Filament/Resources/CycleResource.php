<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CycleResource\Pages;
use App\Filament\Resources\CycleResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Models\Cycle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;
use App\Enums\CycleType;
use App\Enums\QualityStatus;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CycleResource extends Resource
{
    protected static ?string $model = Cycle::class;

    protected static ?string $modelLabel = 'Cycle ou série';
    protected static ?string $pluralModelLabel = 'Cycles et séries';
    protected static ?string $navigationLabel = 'Cycles';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup  = 'Oeuvres';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Cycle : " . $record->name;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->helperText('Le nom (ou un des noms) le plus usuel')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', SlugService::createSlug(Cycle::class, 'slug', $state)))
                            ->required()
                            ->maxLength(128),
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Pour info, l\'URL qui sera utilisée (non modifiable manuellement)')
                            ->label('Slug'),
                        Forms\Components\TextInput::make('nom_bdfi')
                            ->helperText('Temporaire - Pour lien avec la page BDFI, si elle existe - Ne doit pas être modifié habituellement')
                            ->maxLength(128),
                        Forms\Components\TextInput::make('alt_names')
                            ->label('Autres noms')
                            ->helperText("Autres noms utilisés ou communément admis. Séparer les formes multiples par des point-virgules")
                            ->maxLength(512),
                        Forms\Components\TextInput::make('vo_names')
                            ->label('Noms VO')
                            ->helperText("Noms VO utilisés ou admis. Séparer les formes multiples par des point-virgules")
                            ->maxLength(256),
/*
                        Forms\Components\Select::make('type')
                            ->options([
                                'serie'      => 'Série',
                                'cycle'      => 'Cycle',
                                'feuilleton' => 'Feuilleton',
                                'univers'    => 'Univers',
                                'autre'      => 'Autre',
                            ])
                            ->required(),
*/
                        Forms\Components\Select::make('type')
                            ->enum(CycleType::class)
                            ->options(CycleType::class)
                            ->required(),
                        Forms\Components\Select::make('parent_id')
                            ->label('Série/univers parent')
                            ->helperText("Nota: l'attachement de sous-séries se fait depuis les sous-séries elles-mêmes.")
                            ->relationship('parent', 'name')
                            ->hiddenOn('create')
                            ->hint(fn (Cycle $record) => $record->parent_id == 0 ? "Pas de cycle parent !" : new HtmlString('<a href="' . CycleResource::getUrl('view', ['record' => $record->parent_id]) . '">Aller à la fiche parent</a>'))
                            ->hintColor('info')
                            ->hintIcon('heroicon-m-link')
                            ->searchable(['name']),
                        Forms\Components\Select::make('parent_id')
                            ->label('Série/univers parent')
                            ->helperText("Nota: l'attachement de sous-séries se fait depuis les sous-séries elles-mêmes.")
                            ->relationship('parent', 'name')
                            ->visibleOn('create')
                            ->searchable(['name']),
                    ])
                    ->columns(2),

                Section::make('Informations')
                    ->schema([
                        Forms\Components\Textarea::make('information')
                            ->label('Description')
                            ->helperText("Description succincte. Pas de copier-coller de textes trouvés sur Internet ! (mais on peut s'inspirer pour résumer bien sur).")
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('quality')
                            ->label('Etat d\'avancement fiche')
                            ->enum(QualityStatus::class)
                            ->options(QualityStatus::class)
                            ->default(QualityStatus::VIDE)
                            ->required(),
                        Forms\Components\Textarea::make('private')
                            ->label('Infos de travail et privées')
                            ->helperText("Informations diverses de travail : info à conserver, doutes, choses à vérifier, ce qu'il faudrait revoir...")
                            ->maxLength(65535)
                            ->columnSpanFull(),
                ])
                ->collapsible()
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->limit(25, "<span class='!bg-indigo-100 dark:!bg-indigo-800'>&mldr;</span>")
                    ->html()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alt_names')
                    ->label('Autres noms')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(25, "<span class='!bg-indigo-100 dark:!bg-indigo-800'>&mldr;</span>")
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('vo_names')
                    ->label('Noms VO')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(25, "<span class='!bg-indigo-100 dark:!bg-indigo-800'>&mldr;</span>")
                    ->html()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),
                Tables\Columns\IconColumn::make('parent_exist')
                    ->label('Est enfant ?')
                    ->boolean()
                    ->state(function (Cycle $record): bool {
                        return $record->parent_id <> 0;
                    }),
                Tables\Columns\TextColumn::make('quality')
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
            RelationGroup::make('Sous-séries', [
                RelationManagers\SubseriesRelationManager::class,
            ])
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCycles::route('/'),
            'create' => Pages\CreateCycle::route('/create'),
            'view' => Pages\ViewCycle::route('/{record}'),
            'edit' => Pages\EditCycle::route('/{record}/edit'),
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
