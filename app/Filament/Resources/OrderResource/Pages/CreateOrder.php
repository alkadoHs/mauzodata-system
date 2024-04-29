<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\CreditSale;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;


    protected function getRedirectUrl(): string
    {
        return route('invoices.index', $this->record->id, false);
    }


    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Order created successfully.';
    }


    protected function afterCreate(): void
    {
        $totalPrice = $this->record->orderItems()->get()->reduce(fn ($total, $item) => $total + ($item->price * $item->quantity), 0);

        if($this->record->status == "credit") {
            //add order to the creditOrders table
            CreditSale::create([
                'team_id' => Filament::getTenant()->id,
                'user_id' => auth()->id(),
                'order_id' => $this->record->id,
            ]);
        }
    } 
}
