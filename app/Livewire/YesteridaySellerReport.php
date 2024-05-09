<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class YesteridaySellerReport extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => User::with([
                                    'orderItems' => function (Builder $query) {
                                        $query->whereRelation('order', 'orders.status', 'paid')->whereDate('order_items.created_at', now()->subDay());
                                    },
                                    'expenseItems' => function (Builder $query) {
                                        $query->whereDate('expense_items.created_at', now()->subDay());
                                    }, 
                                    'creditSalePayments' => function (Builder $query) {
                                        $query->whereDate('credit_sale_payments.created_at', now()->subDay());
                                    },
                                ])
                                ->whereRelation('teams', 'teams.id', Filament::getTenant()->id)
                )
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
            ])
            ->filters([
                // Filter::make('date')
                //     ->form([
                //         DatePicker::make('created_from'),
                //         DatePicker::make('created_until')
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['created_from'],
                //                 fn (Builder $query, $date): Builder => $query->whereRelation('orderItems', 'order_items.created_at', '>=', $date)->whereRelation('expenseItems', 'expense_items.created_at', '>=', $date)
                //             )
                //             ->when(
                //                 $data['created_until'],
                //                 fn (Builder $query, $date): Builder => $query->whereRelation('orderItems', 'order_items.created_at', '<=', $date)
                //             );
                //     })
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
    public function render()
    {
        return view('livewire.sellers-report');
    }
}
