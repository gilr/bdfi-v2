<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Enums\AnnouncementType;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $modelLabel = 'Annonce';
    protected static ?string $pluralModelLabel = 'Annonces';
    protected static ?string $navigationLabel = 'Annonces';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup  = 'Site';

    //protected static ?string $recordTitleAttribute = 'name';
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Annonce : " . $record->name;
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
                        Forms\Components\Select::make('type')
                            ->enum(AnnouncementType::class)
                            ->options(AnnouncementType::class)
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                //                    ->format('Y-m-d')
                            ->native(false)
                            ->displayFormat('l j M Y')
                            ->helperText('Si Type=[Remerciement] => date de réception de l\'aide; Sinon, saisir la date de l\'annonce ou de l\'info.')
                            ->required(),
                /*
                        Forms\Components\TextInput::make('date')
                            ->label('Date')
                            ->mask('9999-99-99')
                            ->placeholder('AAAA-MM-DD')
                            ->helperText('Si Type=[Remerciement] => date de réception de l\'aide; Sinon, saisir la date de l\'annonce ou de l\'info.')
                            ->required(),
                */
                        Forms\Components\TextInput::make('name')
                            ->label('Titre / Sujet')
                            ->helperText('Le "titre/sujet" dépend du type d\'info. Si Type=[Remerciement] ou [Consécration] => prénom + nom ou pseudo - Si Type=[Point historique] ou [Autre] => Période (mois, trimestre, ex "Avril 2014") - Si Type=[Changement site] => Sujet')
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->helperText('Optionnel. Peut être l\'URL forum si l\'annonce détaillée existe, ou si auteur, l\'URL de sa page biblio bdfi.')
                            ->url(),
                        Forms\Components\Textarea::make('information')
                            ->label('Description')
                            ->columnSpan('full')
                            ->rows(4)
                            ->autosize()
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Historique de la fiche-information')
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
                    ->label('Titre / Sujet')
                    ->limit(25)
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('information')
                    ->label('Description')
                    ->limit(50)
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
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'view' => Pages\ViewAnnouncement::route('/{record}'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
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
