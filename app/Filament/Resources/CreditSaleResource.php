<?php

namespace App\Filament\Resources;

use App\Filament\Exports\CreditSaleExporter;
use App\Filament\Resources\CreditSaleResource\Pages;
use App\Filament\Resources\CreditSaleResource\RelationManagers;
use App\Filament\Resources\CreditSaleResource\Widgets\CreditSaleStats;
use App\Models\CreditSale;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreditSaleResource extends Resource
{
    protected static ?string $model = CreditSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 4;

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             //
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                 fn (Builder $query) => auth()->user()->role !== 'admin' ? $query->where('user_id', auth()->id())->latest() : $query->latest()
                )
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('user.name')
                    ->label('Seller')
                    ->visible(auth()->user()->role === 'admin')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('order.invoice_number')
                    ->label('Inv. No')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order.customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): ?string => match ($state) {
                        'completed' => 'success',
                        'onprogress' => 'warning'
                    })
                    ->icon(fn (?string $state) => match ($state) {
                        'completed' => 'heroicon-m-check-circle',
                        'onprogress' => 'heroicon-m-clock',
                    })
                    ->toggleable(),
                TextColumn::make('total_order')
                    ->state(
                        fn (CreditSale $creditSale) => $creditSale->order->orderItems->reduce(
                            fn ($acc, $item) => $acc + $item->price * $item->quantity, 0)
                        )
                        ->numeric()
                        ->toggleable(),
                        
                TextColumn::make('Paid')
                    ->state(
                        fn (CreditSale $creditSale) => $creditSale->creditSalePayments->reduce(
                            fn ($acc, $item) => $acc + $item->paid, 0)
                        )
                        ->numeric()
                        ->toggleable(),

                TextColumn::make('Dept')
                    ->state(fn (CreditSale $creditSale) => 
                            $creditSale->order->orderItems->reduce(
                                fn ($acc, $item) => $acc + ($item->price * $item->quantity), 0
                            ) 
                            - 
                            $creditSale->creditSalePayments->reduce(
                                fn ($acc, $item) => $acc + $item->paid, 0
                            )

                        )
                        ->numeric(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Filter by seller')
                    ->visible(auth()->user()->role === 'admin')
                    ->options(fn () => Filament::getTenant()->users()->get()->pluck('name', 'id'))
                    ->searchable(),
                // Tables\Filters\SelectFilter::make('status')
                //         ->options([
                //             'paid' => 'Paid sales',
                //             'credit' => 'Credit sales',
                //         ])
                //         ->native(false),
                // Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->native(false)
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->native(false)
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
                    ->visible(auth()->user()->role == 'admin'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Pay'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->role === 'admin'),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(CreditSaleExporter::class)
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CreditSalePaymentsRelationManager::class
        ];
    }


    public static function getWidgets(): array
    {
        return [
            CreditSaleStats::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditSales::route('/'),
            // 'create' => Pages\CreateCreditSale::route('/create'),
            'view' => Pages\ViewCreditSale::route('/{record}'),
            'edit' => Pages\EditCreditSale::route('/{record}/edit'),
        ];
    }
}
