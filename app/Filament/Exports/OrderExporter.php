<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')
                ->label('SELLER'),
            ExportColumn::make('invoice_number')
                ->label('INVOICE NUMBER'),
            ExportColumn::make('customer.name')
                ->label('CUSTOMER'),
            ExportColumn::make('Price')
                ->state(fn (Order $order) => $order->orderItems->reduce(fn ($acc, $item) => $acc + $item->price * $item->quantity, 0))
                ->label('PRICE'),
            ExportColumn::make('paymentMethod.name')
                ->label('PAYMENT METHOD'),
            ExportColumn::make('status')
                ->label('STATUS'),
            ExportColumn::make('created_at')
                ->label('DATE'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
