<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use App\Models\Author;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\MorphToSelect;


class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $modelLabel = 'Document';
    protected static ?string $pluralModelLabel = 'Documents';
    protected static ?string $navigationLabel = 'Documents';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup  = 'Site';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make()
                ->description('Le document ne peut concerner qu\'un auteur pour l\'instant')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom')
                        ->maxLength(32)
                        ->helperText('Nom/titre en clair du document')
                        ->required(),
                    FileUpload::make('file')
                        ->label('Fichier')
                        ->helperText('Fichier PDF à uploader')
                        ->directory('documents')
                        ->preserveFilenames()
                        ->acceptedFileTypes(['application/pdf'])
                        ->openable()
                        ->downloadable(),
//                        ->visibility('private'),
                    Forms\Components\Select::make('author_id')
                        ->label('Auteur')
                        ->relationship('author', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                        ->helperText('Nom de l\'auteur du document. Peut être vide. Pour le chercher, taper un début de prénom OU de nom')
                        ->searchable(['name', 'first_name'])
                        ->nullable(),
                    MorphToSelect::make('item')
                        ->label('Attachement du document à la fiche concernée')
                        ->types([
                            MorphToSelect\Type::make(Author::class)
                                ->label ('Author')
                                ->titleAttribute('name')
                                ->getOptionLabelFromRecordUsing(fn (Author $record): string => "{$record->fullName}"),
/*                            MorphToSelect\Type::make(Publisher::class)
                                ->label ('Editeur')
                                ->titleAttribute('name'),
*/
                        ])
                        ->searchable(),

                ]),
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
            ->description('Ne sont gérés actuellement que des documents attachés à un auteur (biblio, bio etc...).')
            ->columns([
                Tables\Columns\TextColumn::make('file')
                    ->label('Fichier'),
                Tables\Columns\TextColumn::make('author.fullName')
                    ->label('Auteur du document'),
                Tables\Columns\TextColumn::make('item_type')
                    ->label('Pour la zone ...'),
                Tables\Columns\TextColumn::make('item.fullName')
                    ->label('... concerne la fiche'),
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
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
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
