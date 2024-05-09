<?php

namespace App\Filament\Widgets;

use App\Models\CreditSalePayment;
use App\Models\ExpenseItem;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Illuminate\Contracts\Database\Eloquent\Builder;

class CashOverview extends BaseWidget
{
    use InteractsWithPageFilters;

     protected static ?string $pollingInterval = null;
    protected static ?int $sort = 10;
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
                    
        $sales = OrderItem::query()
            ->when(!$startDate && !$endDate, function (Builder $query) {
                return $query->whereRelation('order','team_id', Filament::getTenant()->id)->whereDate('created_at', now());
            })
            ->when(
            $startDate, fn (Builder $query) => $query->whereRelation('order','team_id', Filament::getTenant()->id)->whereDate('created_at', '>=', $startDate)
            )
            ->when(
            $endDate, fn (Builder $query) => $query->whereRelation('order','team_id', Filament::getTenant()->id)->whereDate('created_at', '<=', $endDate)
            )
            ->get()->reduce(
                    fn($total, $item) => $total + $item->total_price, 0);

        $profit = OrderItem::query()
            ->when(!$startDate && !$endDate, function (Builder $query) {
                return $query->whereRelation('order', 'team_id', Filament::getTenant()->id)->whereDate('created_at', now());
            })
            ->when($startDate, function (Builder $query) use($startDate) {
                return $query->whereRelation('order', 'team_id', Filament::getTenant()->id)->whereDate('created_at', '>=', $startDate);
            }) 
            ->when($endDate, function (Builder $query) use($endDate) {
                return $query->whereRelation('order', 'team_id', Filament::getTenant()->id)->whereDate('created_at', '<=', $endDate);
            })
            ->get()->reduce(
                    fn($total, $item) => $total + $item->profit, 0);

        $expenses = ExpenseItem::query()
            ->when(!$startDate && !$endDate, function (Builder $query) {
                return $query->whereRelation('expense', 'team_id', Filament::getTenant()->id)->whereDate('created_at', now());
            })
            ->when($startDate, function (Builder $query) use($startDate) {
                return $query->whereRelation('expense', 'team_id', Filament::getTenant()->id)->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function (Builder $query) use($endDate) {
                return $query->whereRelation('expense', 'team_id', Filament::getTenant()->id)->whereDate('created_at', '<=', $endDate);
            })
            ->get()->reduce(
            fn($total, $item) => $total + $item->cost, 0
        );

        $credits = CreditSalePayment::query()
            ->when(!$startDate && !$endDate, function (Builder $query) {
                return $query->whereRelation('creditSale', 'team_id', Filament::getTenant()->id)->whereDate('created_at', now());
            })
            ->when($startDate, function (Builder $query) use($startDate) {
                return $query->whereRelation('creditSale', 'team_id', Filament::getTenant()->id)->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function (Builder $query) use($endDate) {
                return $query->whereRelation('creditSale', 'team_id', Filament::getTenant()->id)->whereDate('created_at', '<=', $endDate);
            })
            ->get()->reduce(fn ($total, $item) => $total + $item->paid, 0);
        
        return [
            Stat::make('Credit Received', number_format($credits)),
            Stat::make('Total Revenues', number_format(($sales - $expenses) + $credits)),
            Stat::make('Net Profit', number_format($profit - $expenses))
        ];
    }


    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
