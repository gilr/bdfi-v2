<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatResource\Pages;
use App\Models\Stat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;

class StatResource extends Resource
{
    protected static ?string $model = Stat::class;

    protected static ?string $modelLabel = 'Statistique';
    protected static ?string $pluralModelLabel = 'Statistique';
    protected static ?string $navigationLabel = 'Stats';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup  = 'Site';

//    protected static ?string $maxContentWidth = '3xl'; Ne marche plus avec 3.x

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
                        Forms\Components\DatePicker::make('date')
                            ->label('Date de l\'image')
                            ->columnSpan('full')
                            ->required(),
                        Forms\Components\TextInput::make('authors')
                            ->label('Nb auteurs')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('series')
                            ->label('Nb cycles et séries')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('references')
                            ->label('Nb total références')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('novels')
                            ->label('Nb romans')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('short_stories')
                            ->label('Nb nouvelles')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('collections')
                            ->label('Nb recueils et anthologies')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('magazines')
                            ->label('Nb revues et magazines')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('essays')
                            ->label('Nb essais et non-fiction')
                            ->numeric()
                            ->required(),
                        ])
                    ->columns(2),

                Section::make('Historique fiche')
                    ->schema([
                        Forms\Components\TextInput::make('created_by')
                            ->label('Créée par'),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->native(false)
                            ->displayFormat('l j M Y H:i')
                            ->label('le'),
                        Forms\Components\TextInput::make('updated_by')
                            ->label('Mise à jour par'),
                        Forms\Components\DateTimePicker::make('updated_at')
                            ->native(false)
                            ->displayFormat('l j M Y, à G:i')
                            ->label('le'),
                        Forms\Components\TextInput::make('deleted_by')
                            ->label('Désactivée par'),
                        Forms\Components\DateTimePicker::make('deleted_at')
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
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('authors')
                    ->label('Auteurs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('series')
                    ->label('Séries')
                    ->sortable(),
                Tables\Columns\TextColumn::make('references')
                    ->label('Réfs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('novels')
                    ->label('Romans')
                    ->sortable(),
                Tables\Columns\TextColumn::make('short_stories')
                    ->label('Nouvelles')
                    ->sortable(),
                Tables\Columns\TextColumn::make('collections')
                    ->label('Recueils')
                    ->sortable(),
                Tables\Columns\TextColumn::make('magazines')
                    ->label('Revues')
                    ->sortable(),
                Tables\Columns\TextColumn::make('essays')
                    ->label('Essais')
                    ->sortable(),
                //
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('mis à jour')
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par'),
            ])
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
            'index' => Pages\ListStats::route('/'),
            'create' => Pages\CreateStat::route('/create'),
            'view' => Pages\ViewStat::route('/{record}'),
            'edit' => Pages\EditStat::route('/{record}/edit'),
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
