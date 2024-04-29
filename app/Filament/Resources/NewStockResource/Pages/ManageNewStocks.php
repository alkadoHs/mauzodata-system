<?php

namespace App\Filament\Resources\NewStockResource\Pages;

use App\Filament\Resources\NewStockResource;
use App\Models\NewStock;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewStocks extends ManageRecords
{
    protected static string $resource = NewStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successNotificationTitle('Added successfully.')
                ->mutateFormDataUsing(function (array $data) {
                    Product::find($data['product_id'])->increment('stock', $data['stock']);
                    return $data;
                })
        ];
    }
}
