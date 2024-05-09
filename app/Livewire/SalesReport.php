<?php

namespace App\Livewire;

use App\Filament\Exports\ExpenseItemExporter;
use App\Filament\Exports\OrderItemExporter;
use App\Models\ExpenseItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class SalesReport extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderItem::query())
            ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('order', 'team_id', Filament::getTenant()->id))
            ->defaultGroup('order.user.name')
            ->groups([
                Group::make('order.user.name')
                    ->collapsible()
            ])
            ->columns([
                TextColumn::make('order.user.name')
                    ->searchable()
                    ->label(__('User')),
                TextColumn::make('order.invoice_number')
                    ->searchable()
                    ->label(__('INV NO')),
                TextColumn::make('product.title')
                    ->searchable(),
                TextColumn::make('price')
                    ->sortable()
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->sortable()
                    ->toggleable()
                    ->numeric(),
                TextColumn::make('total_price')
                    ->sortable()
                    ->toggleable()
                    ->numeric(),
                TextColumn::make('profit')
                    ->sortable()
                    ->toggleable()
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->date('d/m/Y')
                    ->toggleable()
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')
                            ->default(date('Y-m-d')),
                        DatePicker::make('created_until')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Sales from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Sales until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                DeleteAction::make()
                    ->label('cancel')
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(OrderItemExporter::class)
            ]);
    }
    public function render()
    {
        return view('livewire.sellers-report');
    }
}
