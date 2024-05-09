<?php

namespace App\Filament\Exports;

use App\Models\CreditSalePayment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CreditSalePaymentExporter extends Exporter
{
    protected static ?string $model = CreditSalePayment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')
                ->label('PAID TO'),
            ExportColumn::make('creditSale.customer.name')
                ->label('CUSTOMER'),
            ExportColumn::make('paid')
                ->label('PAID AMOUNT'),
            ExportColumn::make('paymentMethod.name')
                ->label('PAYMENT METHOD'),
            ExportColumn::make('created_at')
                ->label('DATE'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your credit sale payment export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
