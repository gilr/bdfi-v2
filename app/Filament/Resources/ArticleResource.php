<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Models\Collection;
use App\Models\Publisher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\MorphToSelect;
//use Filament\Forms\Components\RichEditor;
//use Filament\Forms\Components\MarkdownEditor;
//use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $modelLabel = 'Article';
    protected static ?string $pluralModelLabel = 'Articles';
    protected static ?string $navigationLabel = 'Articles';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup  = 'Site';

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
                    MorphToSelect::make('item')
                        ->label('Attachement de l\'article à la fiche concernée')
                        ->types([
                            MorphToSelect\Type::make(Collection::class)
                                ->label ('Collection')
                                ->titleAttribute('name')
                                ->getOptionLabelFromRecordUsing(fn (Collection $record): string => "{$record->fullName}"),
                            MorphToSelect\Type::make(Publisher::class)
                                ->label ('Editeur')
                                ->titleAttribute('name'),
                        ])->searchable(),
                    TinyEditor::make('content')
                        ->label('Contenu article')
                        ->profile('default'),
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
            ->description('Attention ! Un article créé sera visible en zone collection (uniquement pour l\'instant, même si on peut en créer aussi pour un éditeur). Cependant, l\'affichage actuel ("Voir") ne ressemble pas à ce qui sera affiché sur le site. Passer en "Modifier" pour voir un affichage plus proche de ce qui sera affiché.')
            ->columns([
                Tables\Columns\TextColumn::make('item.fullName')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('item_type')
                    ->label('Pour la zone ...'),
                Tables\Columns\TextColumn::make('item.fullName')
                    ->label('... concerne la fiche'),
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'view' => Pages\ViewArticle::route('/{record}'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
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
