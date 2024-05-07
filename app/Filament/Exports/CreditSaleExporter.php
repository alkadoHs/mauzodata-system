<?php

namespace App\Filament\Exports;

use App\Models\CreditSale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CreditSaleExporter extends Exporter
{
    protected static ?string $model = CreditSale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')
                ->label(__('SELLER')),
            ExportColumn::make('order.invoice_number')
                ->label(__('INVOICE. NO')),
            ExportColumn::make('order.customer.name')
                ->label(__('CUSTOMER')),
            ExportColumn::make('total_price')
                ->label(__('TOTAL PRICE'))
                ->state(
                    fn (CreditSale $creditSale) => $creditSale->order->orderItems->reduce(
                        fn ($acc, $item) => $acc + $item->price * $item->quantity, 0)
                    ),
            ExportColumn::make('paid')
                    ->label(__('PAID'))
                    ->state(
                        fn (CreditSale $creditSale) => $creditSale->creditSalePayments->reduce(
                            fn ($acc, $item) => $acc + $item->paid, 0)
                        ),
            ExportColumn::make('order')
                ->label(__('DEPT'))
                ->state(
                    fn (CreditSale $creditSale) => 
                            $creditSale->order->orderItems->reduce(
                                fn ($acc, $item) => $acc + ($item->price * $item->quantity), 0
                            ) 
                            - 
                            $creditSale->creditSalePayments->reduce(
                                fn ($acc, $item) => $acc + $item->paid, 0
                            )

                    ),
            ExportColumn::make('created_at')
                ->label(__('DATE')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your credit sale export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
