<?php

namespace App\Filament\Resources\CreditSaleResource\Pages;

use App\Filament\Resources\CreditSaleResource;
use App\Filament\Resources\CreditSaleResource\Widgets\CreditSaleStats;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCreditSales extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = CreditSaleResource::class;


    protected function getHeaderWidgets(): array
    {
        return [
            CreditSaleStats::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
