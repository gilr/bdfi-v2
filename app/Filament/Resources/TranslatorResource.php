<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranslatorResource\Pages;
use App\Filament\Resources\TranslatorResource\RelationManagers;
use App\Models\Translator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Enums\AuthorGender;
use App\Enums\QualityStatus;
use App\Models\Author;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class TranslatorResource extends Resource
{
    protected static ?string $model = Translator::class;

    protected static ?string $modelLabel = 'Traducteur';
    protected static ?string $pluralModelLabel = 'Traducteurs';
    protected static ?string $navigationLabel = 'Traducteurs';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup  = 'Auteurs';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return "Traducteur : " . $record->name;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom usuel principal')
                            ->helperText('Au format Prénom Nom')
                            ->required()
                            ->maxLength(32),
                        Forms\Components\TextInput::make('legal_name')
                            ->helperText('Seulement si différent du nom usuel')
                            ->label('Nom légal')
                            ->maxLength(128),
                        Forms\Components\TextInput::make('alt_names')
                            ->label('Autre formes et variantes utilisées')
                            ->helperText('Les formes multiples sont séparées par des virgules.')
                            ->maxLength(512),
                        Forms\Components\Select::make('gender')
                            ->label('H/F & co')
                            ->enum(AuthorGender::class)
                            ->options(AuthorGender::class)
                            ->default(AuthorGender::INCONNU)
                            ->required(),
                        Forms\Components\Select::make('author_id')
                            ->label('Auteur en table "Auteurs"')
                            ->helperText("Lien sur la fiche auteur si celle-ci existe.")
                            ->relationship('author', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->searchable(['name', 'first_name'])
                            ->nullable(),
                        Forms\Components\Select::make('quality')
                            ->label('Etat d\'avancement fiche')
                            ->enum(QualityStatus::class)
                            ->options(QualityStatus::class)
                            ->default(QualityStatus::VIDE)
                            ->required(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom usuel principal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Nom légal')
                    ->limit(25)
                    ->searchable(),
                Tables\Columns\TextColumn::make('author.fullName')
                    ->label('Fiche auteur ?')
                    ->sortable(),
                Tables\Columns\TextColumn::make('alt_names')
                    ->label('Variantes utilisées')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quality')
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
                Tables\Columns\TextColumn::make('destroyer.name')
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
            'index' => Pages\ListTranslators::route('/'),
            'create' => Pages\CreateTranslator::route('/create'),
            'view' => Pages\ViewTranslator::route('/{record}'),
            'edit' => Pages\EditTranslator::route('/{record}/edit'),
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
