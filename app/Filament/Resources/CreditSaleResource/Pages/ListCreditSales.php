<?php

namespace App\Filament\Resources\CreditSaleResource\Pages;

use App\Filament\Resources\CreditSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditSales extends ListRecords
{
    protected static string $resource = CreditSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
