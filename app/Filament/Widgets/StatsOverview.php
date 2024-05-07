<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class StatsOverview extends BaseWidget
{
     use InteractsWithPageFilters;

     protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $data = Trend::query(Order::query()
                            ->where('team_id', Filament::getTenant()->id)
                        )
                    ->between(
                        start: now()->subYear(),
                        end: now()
                    )
                    ->perMonth()
                    ->count();
                    
        $sales = Order::query()
            ->when(
            $startDate, fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id)->whereDate('created_at', '>=', $startDate)
            )
            ->when(
            $endDate, fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id)->whereDate('created_at', '<=', $endDate)
            )
            ->get()->reduce(
                    fn($total, $item) => $total + $item->orderItems->reduce(fn($total2, $item2) => $total2 + $item2->price * $item2->quantity, 0), 0);

        $profit = Order::query()
            ->when($startDate, fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id))->whereDate('created_at', '>=', $startDate)
            ->when($endDate, fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id))->whereDate('created_at', '<=', $endDate)
            ->get()->reduce(
                    fn($total, $item) => $total + $item->orderItems->reduce(fn($total2, $item2) => $total2 + ($item2->price - $item2->product->buy_price) * $item2->quantity, 0), 0);

        $expenses = Expense::query()
            ->when($startDate, fn(Builder $query) => $query->where('team_id', Filament::getTenant()->id))->whereDate('created_at', '>=', $startDate)
            ->when($endDate, fn(Builder $query) => $query->where('team_id', Filament::getTenant()->id))->whereDate('created_at', '<=', $endDate)
            ->get()->reduce(
            fn($total, $item) => $total + $item->expenseItems->reduce(fn($total2, $item2) => $total2 + $item2->cost, 0), 0
        );


        return [
            Stat::make('Sales', number_format($sales))
                ->chart(
                    $data
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Profit', number_format($profit)),
            Stat::make('Expenses', number_format($expenses)),
        ];
    }
}
