<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SellersReport extends BaseWidget
{
    use InteractsWithPageFilters;

     protected string |array|int $columnSpan = 'full';

     protected static ?int $sort = 13;

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        return $table
            ->query(User::query())
            ->paginated([10])
            ->modifyQueryUsing(function (Builder $query) use($startDate, $endDate) {
                return $query->with([
                                    'orderItems' => function (Builder $query) use($startDate, $endDate) {
                                         return $query->when(
                                            !$startDate && !$endDate, function (Builder $query) {
                                            return $query->whereRelation('order', 'orders.status', 'paid')->whereDate('order_items.created_at', now());
                                        })
                                        ->when(
                                        $startDate, fn (Builder $query) => $query->whereRelation('order', 'orders.status', 'paid')->whereDate('order_items.created_at', '>=', $startDate)
                                        )
                                        ->when(
                                        $endDate, fn (Builder $query) => $query->whereRelation('order', 'orders.status', 'paid')->whereDate('order_items.created_at', '<=', $endDate)
                                        );
                                    },
                                    'expenseItems' => function (Builder $query) use($startDate, $endDate) {
                                        return $query->when(
                                            !$startDate && !$endDate, function (Builder $query) {
                                            return $query->whereDate('expense_items.created_at', now());
                                        })
                                        ->when(
                                        $startDate, fn (Builder $query) => $query->whereDate('expense_items.created_at', '>=', $startDate)
                                        )
                                        ->when(
                                        $endDate, fn (Builder $query) => $query->whereDate('expense_items.created_at', '<=', $endDate)
                                        );
                                    }, 
                                    'creditSalePayments' => function (Builder $query) use($startDate, $endDate) {
                                        return $query->when(
                                            !$startDate && !$endDate, function (Builder $query) {
                                            return $query->whereDate('credit_sale_payments.created_at', now());
                                        })
                                        ->when(
                                        $startDate, fn (Builder $query) => $query->whereDate('credit_sale_payments.created_at', '>=', $startDate)
                                        )
                                        ->when(
                                        $endDate, fn (Builder $query) => $query->whereDate('credit_sale_payments.created_at', '<=', $endDate)
                                        );
                                    },
                                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('Total Sales')
                    ->state(function (User $user) {
                        return $user->orderItems->reduce(fn ($total, $item) => $total + $item->price * $item->quantity, 0);
                    })
                    ->numeric(),
                TextColumn::make('Profit')
                    ->state(function (User $user) {
                        return $user->orderItems->reduce(fn ($total, $item) => $total + ($item->price - $item->product->buy_price) * $item->quantity, 0);
                    })
                    ->numeric(),
                TextColumn::make('Expenses')
                    ->state(fn (User $user) => $user->expenseItems->reduce(fn ($total, $item) => $total + $item->cost, 0 ))
                    ->numeric(),
                TextColumn::make('Credit Received')
                    ->state(fn (User $user) => $user->creditSalePayments->reduce(fn ($total, $item) => $total + $item->paid, 0 ))
                    ->numeric(),
                TextColumn::make('Net Sales')
                    ->state(fn (User $user) => 
                        $user->orderItems->reduce(fn ($total, $item) => $total + $item->price * $item->quantity, 0) +
                        $user->creditSalePayments->reduce(fn ($total, $item) => $total + $item->paid, 0 ) -
                        $user->expenseItems->reduce(fn ($total, $item) => $total + $item->cost, 0 ) 
                    )
                    ->numeric(),
                TextColumn::make('Net Profit')
                    ->state(fn (User $user) => 
                        $user->orderItems->reduce(fn ($total, $item) => $total + ($item->price - $item->product->buy_price) * $item->quantity, 0) -
                        $user->expenseItems->reduce(fn ($total, $item) => $total + $item->cost, 0 ) 
                    )
                    ->numeric()
            ]);
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
