<?php

namespace App\Filament\Resources\DamageResource\Pages;

use App\Filament\Resources\DamageResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageDamages extends ManageRecords
{
    protected static string $resource = DamageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successNotificationTitle('Added successfully.')
                ->mutateFormDataUsing(function (array $data, CreateAction $action) {
                    $product = Product::find($data['product_id']);
                    if($product->stock > 0 && $product->stock > $data['stock']) {
                        $product->decrement('stock', $data['stock']);
                    } else {
                        Notification::make()
                            ->title('Stock not enough.')
                            ->color('danger')
                            ->send();
                        $action->halt();
                        return;
                    }
                    return $data;
                }),
        ];
    }
}
