<?php

namespace App\Filament\Resources\ProductTransferResource\Pages;

use App\Filament\Resources\ProductTransferResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProductTransfer extends CreateRecord
{
    protected static string $resource = ProductTransferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $product = Product::find($data['product_id']);
        if($data['stock'] > $product->stock) {
            Notification::make()
                ->title('Stock not enough!')
                ->body('You can not transfer the stock you don\'t have. Please modify the stock according to the stock available.')
                ->color('danger')
                ->persistent()
                ->send();

            $this->halt();
        }
        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
                    ->success()
                    ->title('Transfered successfully.')
                    ->body('It\'s currently pending and waiting for approve.')
                    ->send();
    }
}
