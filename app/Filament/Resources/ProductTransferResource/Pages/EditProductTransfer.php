<?php

namespace App\Filament\Resources\ProductTransferResource\Pages;

use App\Filament\Resources\ProductTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductTransfer extends EditRecord
{
    protected static string $resource = ProductTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
