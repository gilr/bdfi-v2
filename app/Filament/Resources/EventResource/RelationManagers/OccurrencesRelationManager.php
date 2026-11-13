<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\EventOccurrence;

class OccurrencesRelationManager extends RelationManager
{
    protected static string $relationship = 'occurrences';

    protected static ?string $title = 'Occurrences';
    protected static ?string $modelLabel = 'occurrence';
    protected static ?string $pluralModelLabel = 'occurrences';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('start_date')
                    ->label('Débute le')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        if (!$get('end_date')) {
                            $set('end_date', $state);
                        }
                    })
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Se termine le')
                    ->helperText('Identique au début si l\'évènement dure une seule journée')
                    ->afterOrEqual('start_date')
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->label('Libellé')
                    ->helperText('Facultatif ("24e édition"...). À défaut : nom de l\'évènement + année.')
                    ->maxLength(128),
                Forms\Components\TextInput::make('place')
                    ->label('Lieu')
                    ->helperText(fn () => 'Précise ou remplace le lieu habituel' . ($this->getOwnerRecord()->place ? ' ("' . $this->getOwnerRecord()->place . '")' : ''))
                    ->maxLength(128),
                Forms\Components\Toggle::make('is_confirmed')
                    ->label('Est confirmé ?')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(false),
                Forms\Components\DateTimePicker::make('publication_date')
                    ->label('Date de publication')
                    ->helperText('Permet de définir à quelle date cette occurrence sera visible sur BDFI'),
                Forms\Components\Textarea::make('information')
                    ->label('Information')
                    ->helperText('Spécifique à cette occurrence (programme, invités...)')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->date('D j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('D j M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Libellé')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('place')
                    ->label('Lieu')
                    ->limit(30)
                    ->placeholder(fn () => $this->getOwnerRecord()->place ? '(' . $this->getOwnerRecord()->place . ')' : '-'),
                Tables\Columns\IconColumn::make('is_future')
                    ->label('Futur ?')
                    ->boolean()
                    ->state(fn (EventOccurrence $record): bool => $record->end_date >= today()),
                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('Confirmé')
                    ->boolean(),
                Tables\Columns\TextColumn::make('publication_date')
                    ->label('Publié le')
                    ->dateTime('j M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('j M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('editor.name')
                    ->label('par')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Prépare l'occurrence suivante : dates décalées d'un an, non confirmée, sans libellé ni information
                Tables\Actions\ReplicateAction::make()
                    ->label('Dupliquer')
                    ->modalHeading('Nouvelle occurrence (à partir de celle-ci)')
                    ->form(fn (Form $form) => $this->form($form))
                    ->fillForm(fn (EventOccurrence $record): array => [
                        'start_date'       => $record->start_date?->copy()->addYear()->format('Y-m-d'),
                        'end_date'         => $record->end_date?->copy()->addYear()->format('Y-m-d'),
                        'label'            => null,
                        'place'            => $record->place,
                        'is_confirmed'     => false,
                        'publication_date' => null,
                        'information'      => null,
                    ]),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
