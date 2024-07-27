<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;

class RecentlyOrders extends BaseWidget
{
    protected string |array|int $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 14;


    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
            )
            ->modifyQueryUsing(
                fn (Builder $query) => auth()->user()->role !== 'admin' ? $query->whereDate('created_at', now())->latest()->limit(10): $query->where('user_id', auth()->user()->id)->latest()->limit(10)
                )
            ->emptyStateHeading('No orders today.')
            ->emptyStateDescription('Once orders are available, will appear here!')
            ->paginated([10])
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('Seller'))
                    ->numeric()
                    ->visible(auth()->user()->role === 'admin'),
                TextColumn::make('invoice_number')
                    ->alignCenter(),
                TextColumn::make('price')
                    ->numeric()
                    ->state(fn (Order $order) => $order->orderItems->reduce(fn ($acc, $item) => $acc + $item->total_price, 0)),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): ?string => match ($state) {
                        'paid' => 'success',
                        'credit' => 'warning'
                    })
                    ->icon(fn (?string $state) => match ($state) {
                        'paid' => 'heroicon-m-check-circle',
                        'credit' => 'heroicon-m-clock',
                    }),
                TextColumn::make('paymentMethod.name')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:m'),
            ]);
    }

    
}
