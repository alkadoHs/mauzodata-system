<?php

namespace App\Filament\Resources\CreditSaleResource\Widgets;

use App\Filament\Resources\CreditSaleResource\Pages\ListCreditSales;
use App\Models\CreditSale;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CreditSaleStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListCreditSales::class;
    }

    protected function getStats(): array
    {
       $orderData = Trend::model(CreditSale::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $creditSales = $this->getPageTableQuery()->withOnly('creditSalePayments')->get();

        $orderItems = $this->getPageTableQuery()->withOnly('order.orderItems')->get();

        // dd($orderItems);

        return [
            Stat::make('Credit Sales', $this->getPageTableQuery()->count())
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Total Paid', 
                    number_format(
                        $creditSales->reduce(fn($total, $item) => 
                            $total + $item->creditSalePayments->reduce(fn($total2, $item2) => $total2 + $item2->paid, 0)
                        , 0) 
                    )
               ),
            Stat::make('Total Depts', number_format(
                $orderItems->reduce(
                    fn($total, $item) => $total + $item->order->orderItems->reduce(
                        fn($total2, $item2) => $total2 + $item2->price * $item2->quantity, 0), 0) - 
                    $creditSales->reduce(fn($total, $item) => 
                            $total + $item->creditSalePayments->reduce(fn($total2, $item2) => $total2 + $item2->paid, 0)
                        , 0)
                   )
                ),
        ];
    }
}
