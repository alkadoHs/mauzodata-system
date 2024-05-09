<?php

namespace App\Livewire;

use App\Models\Product;
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

class OutOfStock extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Product::whereColumn('stock', '<', 'stock_alert')
                                ->where('team_id', Filament::getTenant()->id)
                                // ->orderBy('order_items_count', 'desc')
                )
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('stock')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('buy_price')
                    ->label(__('Buying price'))
                    ->numeric(),
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
