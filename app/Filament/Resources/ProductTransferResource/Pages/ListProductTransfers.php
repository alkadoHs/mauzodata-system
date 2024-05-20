<?php

namespace App\Filament\Resources\ProductTransferResource\Pages;

use App\Filament\Resources\ProductTransferResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProductTransfers extends ListRecords
{
    protected static string $resource = ProductTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'newStock' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('to_team_id', Filament::getTenant()->id)->where('status', '=', 'pending')),
            'sentStock' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id)),
            'rejected' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('to_team_id', Filament::getTenant()->id)->where('status', '=', 'rejected'))
        ];
    }
}
