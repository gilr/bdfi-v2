<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Actions\ReplicateAction;
use Filament\Actions;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class ViewPublication extends ViewRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label ('Retour liste')
                ->url(static::getResource()::getUrl())
                ->button()
                ->color('info'),
            Actions\EditAction::make(),
            Actions\ReplicateAction::make()
                ->excludeAttributes(['name'])
                ->beforeReplicaSaved(function (Model $replica, Model $record): void {
                    $replica->name = "CLONE:" . $record->name;
                    $replica->created_by = Auth()->id();
                    $replica->updated_by = Auth()->id();
                    $replica->created_at = now();
                    $replica->updated_at = now();
                })
/*                ->mutateRecordDataUsing(function (array $data): array {
                    $data['name'] = "Clone";
                    $data['created_by'] = Auth()->id();
                    $data['updated_by'] = Auth()->id();
                    $data['created_at'] = now();
                    $data['updated_at'] = now();
                    return $data;
                }) */
                ->successRedirectUrl(fn (Model $replica): string => route('filament.admin.resources.publications.edit', [
                    'record' => $replica,
                ]))
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->color('danger')
                        ->duration(30000)
                        ->title('Duplication OK')
                        ->body('Attention a bien modifier toutes les informations nécessaires : changer si nécessaire l\'éditeur, attacher auteur et collection, et réattacher le contenu (s\'il est identique) (tabulations "Auteurs", "Sommaire" et "Collections"). Modifier également date de parution, ISBN, illustrateurs, ainsi que les "infos livre" et "infos approx" ! Le titre est volontaire préfixé par "CLONE:" afin de le retrouver facilement en cas de problème ou de sortie prématurée.'),
                    )
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['created_by'] = $data['created_by'] ? User::find($data['created_by'])->name : "--";
        $data['updated_by'] = $data['updated_by'] ? User::find($data['updated_by'])->name : "--";
        $data['deleted_by'] = $data['deleted_by'] ? User::find($data['deleted_by'])->name : "--";

        return $data;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
