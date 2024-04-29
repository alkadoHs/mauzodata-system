<?php

namespace App\Filament\Resources\CreditSaleResource\Pages;

use App\Filament\Resources\CreditSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditSale extends EditRecord
{
    protected static string $resource = CreditSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
