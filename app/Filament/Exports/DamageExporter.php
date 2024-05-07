<?php

namespace App\Filament\Exports;

use App\Models\Damage;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DamageExporter extends Exporter
{
    protected static ?string $model = Damage::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('product.title')
                ->label(__('PRODUCT')),
            ExportColumn::make('stock')
                ->label(__('STOCK')),
            ExportColumn::make('created_at')
                ->label(__('DATE')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your damage export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
