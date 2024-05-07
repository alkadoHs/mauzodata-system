<?php

namespace App\Filament\Imports;

use App\Models\NewStock;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class NewStockImporter extends Importer
{
    protected static ?string $model = NewStock::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('team')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('product')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('stock')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): ?NewStock
    {
        // return NewStock::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new NewStock();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your new stock import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
