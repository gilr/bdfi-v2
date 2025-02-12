<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwardWinnerResource\Pages;
use App\Filament\Resources\AwardWinnerResource\RelationManagers;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\AwardCategoryResource\RelationManagers\AwardWinnersRelationManager;
use App\Models\AwardWinner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Models\AwardCategory;
use App\Models\Author;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class AwardWinnerResource extends Resource
{
    protected static ?string $model = AwardWinner::class;

    protected static ?string $modelLabel = 'Gagnant de prix';
    protected static ?string $pluralModelLabel = 'Gagnants de prix';
    protected static ?string $navigationLabel = 'Gagnants';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup  = 'Prix';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Gagnant prix : " . $record->name;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('award_category_id')
                            ->label('Prix et catégorie')
                            ->relationship('award_category', 'name')
                            ->getOptionLabelFromRecordUsing(fn (AwardCategory $record) => "{$record->fullName}")
                            ->helperText('Commencer à saisir le nom d\'un prix ou d\'une catégorie ("Verlanger" ou "Nouvelle" par exemple). Les prix et catégories seront proposées')
                            ->getSearchResultsUsing(fn (string $search) => AwardCategory::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('award', function (Builder $query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($category) => [
                                    $category->id => "{$category->award->name}, {$category->name}"
                                    ])
                                )
                            ->searchable()
                            ->hiddenOn(AwardWinnersRelationManager::class)
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('year')
                            ->label('Année de récompense')
                            ->numeric()
                            ->minValue(1900)
                            ->required()
                            ->maxLength(4),
                        Forms\Components\TextInput::make('position')
                            ->helperText('1 si gagnant, 50 si mention spéciale, 99 si non attribué cette année-là.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Attribué à')
                            ->helperText('Nom(s) de la ou des personne(s) lauréate(s). Si plusieurs, indiquez "Auteur 1 et Auteur 2" ou "Auteur 1, Auteur 2 et Auteur3". Vide si année non décernée (position "99")')
                            ->maxLength(256),
                        Forms\Components\Select::make('author_id')
                            ->label('Auteur')
                            ->relationship('author', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->helperText('Nom de l\'auteur numéro 1. Vide si absent de la base. Respectez l\'ordre indiqué ci-dessus !')
                            ->searchable(['name', 'first_name'])
                            ->nullable(),
                        Forms\Components\Select::make('author2_id')
                            ->label('Auteur 2')
                            ->relationship('author2', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->helperText('Nom de l\'auteur numéro 2. Vide si un seul auteur, ou si absent de la base')
                            ->searchable(['name', 'first_name'])
                            ->nullable(),
                        Forms\Components\Select::make('author3_id')
                            ->label('Auteur 3')
                            ->relationship('author3', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->helperText('Nom de l\'auteur numéro 3. Vide si un seul auteur, ou si absent de la base')
                            ->searchable(['name', 'first_name'])
                            ->nullable(),
                        Forms\Components\TextInput::make('title')
                            ->label('Attribué pour')
                            ->maxLength(256)
                            ->helperText('Titre français (original ou traduit). Vide si pour ensemble d\'une oeuvre, ou si titre non traduit, ou si année non attribuée.')
                            ->nullable(),
                        Forms\Components\TextInput::make('vo_title')
                            ->label('Titre VO')
                            ->helperText('Titre original (si non francophone).')
                            ->maxLength(256)
                            ->nullable(),
                        Forms\Components\TextInput::make('title_id')
                            ->label('Lien sur fiche titre (si existe)')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\Textarea::make('information')
                            ->helperText('Note, information, indication particulière (affichée sur site).')
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
                Tables\Columns\TextColumn::make('award_category.award.name')
                    ->label('Prix')
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->hiddenOn(AwardWinnersRelationManager::class)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('award_category.name')
                    ->label('Catégorie')
                    ->limit(15)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->hiddenOn(AwardWinnersRelationManager::class)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Attribué à')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Pour')
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('vo_title')
                    ->label('VO')
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('An')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('authors')
                    ->label('Auteurs')
                    ->state(function (AwardWinner $record): string {
                        return ($record->author_id == 0 ? '' : $record->author->name)
                         . ($record->author2_id == 0 ? '' : ', ' . $record->author2->name)
                         . ($record->author3_id == 0 ? '' : ', ' . $record->author3->name);
                    }),
                Tables\Columns\TextColumn::make('position')
                    ->label('#')
                    ->numeric()
                    ->sortable(),
                //
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
                    ->label('mis à jour')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
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
            'index' => Pages\ListAwardWinners::route('/'),
            'create' => Pages\CreateAwardWinner::route('/create'),
            'view' => Pages\ViewAwardWinner::route('/{record}'),
            'edit' => Pages\EditAwardWinner::route('/{record}/edit'),
        ];
    }    
}
