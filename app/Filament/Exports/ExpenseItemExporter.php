<?php

namespace App\Filament\Exports;

use App\Models\ExpenseItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ExpenseItemExporter extends Exporter
{
    protected static ?string $model = ExpenseItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('expense.user.name')
                ->label('USER'),
            ExportColumn::make('item')
                ->label('ITEM'),
            ExportColumn::make('cost')
                ->label('COST'),
            ExportColumn::make('created_at')
                ->label('DATE'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your expense item export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
