<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrderStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }

    protected function getStats(): array
    {
        $orderData = Trend::model(Order::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $paidOrders = $this->getPageTableQuery()->withOnly('OrderItems')->where('status', 'paid')->get();
        $creditOrders = $this->getPageTableQuery()->withOnly('OrderItems')->where('status', 'credit')->get();

        // dd($paidOrders);

        return [
            Stat::make('Orders', $this->getPageTableQuery()->count())
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Paid Sales', 
                    number_format($paidOrders->reduce(fn($total, $item) => $total +
                               $item->orderItems->reduce(fn ($total2, $item2) => 
                                               $total2 + $item2->price * $item2->quantity, 0 )
                          , 0)
                    )
               ),
            Stat::make('Credit Sales', number_format(
                 $creditOrders->reduce(fn($total, $item) => $total +
                               $item->orderItems->reduce(fn ($total2, $item2) => 
                                               $total2 + $item2->price * $item2->quantity, 0 )
                          , 0)
                    )
                ),
        ];
    }
}
