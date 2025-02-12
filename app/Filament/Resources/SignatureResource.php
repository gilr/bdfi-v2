<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SignatureResource\Pages;
use App\Models\Signature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use App\Models\Author;

class SignatureResource extends Resource
{
    protected static ?string $model = Signature::class;

    protected static ?string $modelLabel = 'Signature';
    protected static ?string $pluralModelLabel = 'Signatures';
    protected static ?string $navigationLabel = 'Signatures';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup  = 'Auteurs';

    // table column or eloquent accessor
    protected static ?string $recordTitleAttribute = 'fullname';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('author_id')
                            ->label('Entrée de référence')
                            ->relationship('author', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->helperText('Sélectionner un auteur en tapant un extrait de son prénom OU de son nom')
                            ->searchable(['name', 'first_name'])
                            ->required(),
                        Forms\Components\Select::make('signature_id')
                            ->label('Signature liée')
                            ->relationship('signature', 'name')
                            ->different('author_id')
                            ->getOptionLabelFromRecordUsing(fn (Author $record) => "{$record->fullName}")
                            ->searchable(['name', 'first_name'])
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
                    ->label('Entrée de référence'),
                Tables\Columns\TextColumn::make('signature.fullName')
                    ->label('Signature liée'),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
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
            'index' => Pages\ListSignatures::route('/'),
            'create' => Pages\CreateSignature::route('/create'),
            'view' => Pages\ViewSignature::route('/{record}'),
            'edit' => Pages\EditSignature::route('/{record}/edit'),
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
