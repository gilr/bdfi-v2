<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;
use App\Enums\AnnouncementType;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'tous' => Tab::make(),
            'merci' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AnnouncementType::MERCI)),
            'site' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AnnouncementType::EVOL_SITE)),
            'base' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AnnouncementType::REFERENCEMENT)),
            'technique' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AnnouncementType::POINT_HISTO)),
            'points' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AnnouncementType::POINT_AIDES)),
            'stats' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AnnouncementType::POINT_STATS)),
            'consecration' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', AnnouncementType::CONSECRATION)),
        ];
    }
}
