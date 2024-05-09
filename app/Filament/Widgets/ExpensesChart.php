<?php

namespace App\Filament\Widgets;

use App\Models\ExpenseItem;
use App\Models\OrderItem;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ExpensesChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    
    protected static ?string $heading = 'Monthly Expenses';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::query(ExpenseItem::query()
                            ->whereRelation('expense', 'team_id', '=', Filament::getTenant()->id)
                        )
                    ->between(
                        start: now()->subYear(),
                        end: now()
                    )
                    ->perMonth()
                    ->sum('cost');
        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
