<?php

namespace App\Livewire;

use App\Filament\Exports\ExpenseItemExporter;
use App\Models\ExpenseItem;
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

class ExpensesReport extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // public User $user;

    public function table(Table $table): Table
    {
        return $table
            ->query(ExpenseItem::query())
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereRelation('expense', 'team_id', Filament::getTenant()->id)
              )
            ->defaultGroup('expense.user.name')
            ->groups([
                Group::make('expense.user.name')
                    ->collapsible()
            ])
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('expense.user.name')
                    ->label(__('User'))
                    ->searchable(),
                TextColumn::make('item')
                    ->searchable(),
                TextColumn::make('cost')
                    ->sortable()
                    ->numeric()
                    ->summarize(Sum::make()->label(__('Total'))),
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->date('d/m/Y')
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
                            $indicators['created_from'] = 'Expenses from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Expenses until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
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
                    ->exporter(ExpenseItemExporter::class)
            ]);
    }
    public function render()
    {
        return view('livewire.sellers-report');
    }
}
