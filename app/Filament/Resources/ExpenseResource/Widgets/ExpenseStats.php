<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use App\Models\Expense;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ExpenseStats extends BaseWidget
{
    use InteractsWithPageTable;


    protected function getTablePage(): string
    {
        return ListExpenses::class;
    }


    protected function getStats(): array
    {
        $orderData = Trend::model(Expense::class)
            ->between(
                start: now()->subMonth(),
                end: now(),
            )
            ->perDay()
            ->count();

        $expenses = $this->getPageTableQuery()->get();
        return [
            Stat::make('Count', $this->getPageTableQuery()->count())
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Total Expenses', number_format(
                $expenses->reduce(
                    fn($total, $item) => $total + $item->expenseItems->reduce(
                        fn($total2, $item2) => $total2 + $item2->cost, 0
                    ), 0
                )
            ))
        ];
    }
}
