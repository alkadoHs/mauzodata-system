<?php

namespace App\Filament\Exports;

use App\Models\OrderItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderItemExporter extends Exporter
{
    protected static ?string $model = OrderItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order.user.name')
                ->label(__('SELLER')),
            ExportColumn::make('product.title')
                ->label(__('PRODUCT')),
            ExportColumn::make('price')
                ->label(__('PRICE')),
            ExportColumn::make('quantity')
                ->label(__('QUANTITY')),
            ExportColumn::make('total_price')
                ->label(__('TOTAL PRICE')),
            ExportColumn::make('profit')
                ->label(__('PROFIT')),
            ExportColumn::make('created_at')
                ->label('DATE'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order item export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
