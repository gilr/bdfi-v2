<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
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
use App\Enums\EventType;
use App\Enums\EventPeriodicity;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $modelLabel = 'Evènement';
    protected static ?string $pluralModelLabel = 'Evènements';
    protected static ?string $navigationLabel = 'Evènement';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup  = 'Site';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Evènement : " . $record->name;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->maxLength(128)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', SlugService::createSlug(Event::class, 'slug', $state)))
                            ->required(),
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Pour info, l\'URL qui sera utilisée (non modifiable manuellement)')
                            ->label('Slug'),
                        Forms\Components\Select::make('type')
                            ->enum(EventType::class)
                            ->options(EventType::class)
                            ->required(),
                        Forms\Components\Select::make('periodicity')
                            ->label('Périodicité')
                            ->enum(EventPeriodicity::class)
                            ->options(EventPeriodicity::class),
                        Forms\Components\TextInput::make('place')
                            ->label('Lieu habituel')
                            ->helperText('Si toujours le même. Peut être précisé ou remplacé dans chaque occurrence.')
                            ->maxLength(64),
                        Forms\Components\Toggle::make('is_full_scope')
                            ->label('Imaginaire ET littérature ?')
                            ->onColor('success')
                            ->offColor('warning')
                            ->required(),
                        Forms\Components\TextInput::make('year_start')
                            ->label('Année de création')
                            ->helperText('Année de la première occurrence')
                            ->minValue(1900)
                            ->numeric()
                            ->maxLength(4),
                        Forms\Components\TextInput::make('year_end')
                            ->label('Année de fin')
                            ->helperText('Année de la dernière occurrence, vide si toujours actif (une nouvelle occurrence est alors à rechercher).')
                            ->numeric()
                            ->maxLength(4),
                        Forms\Components\TextInput::make('url')
                            ->maxLength(256),
                        Forms\Components\Textarea::make('information')
                            ->label('Description')
                            ->helperText('Description générale. Les informations propres à une occurrence vont dans celle-ci.')
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
                    ->limit(20, "<span class='!bg-indigo-100 dark:!bg-indigo-800'>&mldr;</span>")
                    ->html()
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
                Tables\Columns\TextColumn::make('periodicity')
                    ->label('Périodicité')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_start')
                    ->label('Depuis')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_end')
                    ->label('Jusqu\'à')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('occurrences_count')
                    ->label('Occ.')
                    ->counts('occurrences')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occurrences_max_end_date')
                    ->label('Dernière occ.')
                    ->max('occurrences', 'end_date')
                    ->date('j M Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_future')
                    ->label('Futur ?')
                    ->boolean()
                    ->state(fn (Event $record): bool => $record->occurrences_max_end_date >= today()->format('Y-m-d')),
                Tables\Columns\IconColumn::make('to_search')
                    ->label('À rechercher')
                    ->tooltip('Toujours actif (pas d\'année de fin), mais aucune occurrence à venir')
                    ->icon(fn (bool $state): ?string => $state ? 'heroicon-o-magnifying-glass' : null)
                    ->color('warning')
                    ->state(fn (Event $record): bool => !$record->year_end && $record->periodicity !== EventPeriodicity::UNIQUE
                        && !($record->occurrences_max_end_date >= today()->format('Y-m-d'))),
                Tables\Columns\TextColumn::make('place')
                    ->limit(15, "<span class='!bg-indigo-100 dark:!bg-indigo-800'>&mldr;</span>")
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_full_scope')
                    ->label('+large')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true)
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
                Tables\Columns\TextColumn::make('destroyer.name')
                    ->label('par')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('to_search')
                    ->label('À rechercher (actifs sans occurrence à venir)')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNull('year_end')
                        ->where(fn ($q) => $q->whereNull('periodicity')->orWhere('periodicity', '<>', EventPeriodicity::UNIQUE->value))
                        ->whereDoesntHave('occurrences', fn ($q) => $q->where('end_date', '>=', date('Y-m-d')))),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Avec une occurrence à venir')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereHas('occurrences', fn ($q) => $q->where('end_date', '>=', date('Y-m-d')))),
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
            RelationManagers\OccurrencesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
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
