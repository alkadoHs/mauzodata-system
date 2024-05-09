<?php

namespace App\Filament\Widgets;

use App\Models\CreditSalePayment;
use App\Models\ExpenseItem;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SellerOverview extends BaseWidget
{
    protected static ?int $sort = 10;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $sales = OrderItem::whereRelation('order', 'user_id', auth()->id())->whereRelation('order', 'status', '=', 'paid')->whereDate('created_at', now())->get()->reduce(
            fn ($total, $item) => $total + $item->total_price, 0);

        $expenses = ExpenseItem::whereRelation('expense', 'user_id', auth()->id())->whereDate('created_at', now())->get()->reduce(
            fn ($total, $item) => $total + $item->cost, 0);

        $credits = CreditSalePayment::where('user_id', auth()->id())->whereDate('created_at', now())->get()->reduce(
            fn ($total, $item) => $total + $item->paid, 0
        );

        $netSales = number_format(($sales - $expenses) + $credits);

        return [
            Stat::make('Sales', number_format($sales)),
            Stat::make('Expenses', number_format($expenses)),
            Stat::make('Credit Received', number_format($credits)),
            Stat::make('Today Revenue', $netSales),
        ];
    }


    public static function canView(): bool
    {
        return auth()->user()->role !== 'admin';
    }
}
