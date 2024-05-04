<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReprintResource\Pages;
use App\Models\Reprint;
use App\Filament\Resources\PublicationResource\RelationManagers\ReprintsRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Models\Publication;

class ReprintResource extends Resource
{
    protected static ?string $model = Reprint::class;

    protected static ?string $modelLabel = 'Retirage';
    protected static ?string $pluralModelLabel = 'Retirages';
    protected static ?string $navigationLabel = 'Retirages';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup  = 'Publications';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'fullname';

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
                        Forms\Components\Select::make('publication_id')
                            ->label('Publication d\'origine')
                            ->relationship('publication', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Publication $record) => "{$record->fullName}")
                            ->hiddenOn(ReprintsRelationManager::class)
                            ->searchable(['name'])
                            ->required(),
                        Forms\Components\TextInput::make('ai')
                            ->label('Achevé d\'imprimé du retirage')
                            ->maxLength(10)
                            ->helperText('Format "AAAA-MM-JJ" (exemple : 1983-05-19). "AAAA-00-00" si l\'année seule est connue, et vide ou "0000-00-00" si l\'AI n\'est pas indiqué. Si le trimestre seul est indiqué, utilisez par exemple "1923-T3-00". Si une seule date est imprimée (par exemple "Publié le"), utilisez ce champ et précisez le dans le champ des autres infos imprimées.')
                            ->required(),
                        ])
                    ->columns(2),

                Section::make('Informations')
                    ->schema([
                        Forms\Components\Textarea::make('information')
                            ->label('Description et informations qui seront affichées sur le site')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('private')
                            ->label('Infos de travail et privées')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                ]),

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
                Tables\Columns\TextColumn::make('publication.fullName')
                    ->label('Publication')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ai')
                    ->label('AI')
                    ->searchable(),
                //
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('mis à jour')
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par'),
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
            'index' => Pages\ListReprints::route('/'),
            'create' => Pages\CreateReprint::route('/create'),
            'view' => Pages\ViewReprint::route('/{record}'),
            'edit' => Pages\EditReprint::route('/{record}/edit'),
        ];
    }    
}
