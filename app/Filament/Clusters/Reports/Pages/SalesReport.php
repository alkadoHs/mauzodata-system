<?php

namespace App\Filament\Clusters\Reports\Pages;

use App\Filament\Clusters\Reports;
use Filament\Pages\Page;

class SalesReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.reports.pages.sales-report';

    protected static ?string $cluster = Reports::class;

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
