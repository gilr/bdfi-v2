<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteResource\Pages;
use App\Filament\Resources\AuthorResource\RelationManagers\WebsitesRelationManager;
use App\Models\Website;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Models\Author;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class WebsiteResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static ?string $modelLabel = 'Site web';
    protected static ?string $pluralModelLabel = 'Sites web';
    protected static ?string $navigationLabel = 'Sites web';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup  = 'Auteurs';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'url';

    public static function getGloballySearchableAttributes(): array
    {
        return ['url'];
    }
    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Site web : " . $record->url;
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
                        Forms\Components\Select::make('author_id')
                            ->relationship('author', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->helperText('Taper un début de prénom OU de nom')
                            ->searchable(['name', 'first_name'])
                            ->hiddenOn(WebsitesRelationManager::class)
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->label('URL du site ou de la page web')
                            ->maxLength(256),
                        Forms\Components\Select::make('website_type_id')
                            ->relationship('website_type', 'name')
                            ->label('Type de site ou page web')
                            ->required(),
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->label('pays (langue du site)')
                            ->searchable()
                            ->required(),
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
                Tables\Columns\TextColumn::make('author.fullName')
                    ->hiddenOn(WebsitesRelationManager::class),
                Tables\Columns\TextColumn::make('website_type.name'),
                Tables\Columns\TextColumn::make('country.name'),
                Tables\Columns\TextColumn::make('url')->limit(50),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('mis à jour')
                    ->dateTime('j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par')
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
            'index' => Pages\ListWebsites::route('/'),
            'create' => Pages\CreateWebsite::route('/create'),
            'view' => Pages\ViewWebsite::route('/{record}'),
            'edit' => Pages\EditWebsite::route('/{record}/edit'),
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
