<?php

namespace App\Filament\Clusters\Reports\Pages;

use App\Filament\Clusters\Reports;
use Filament\Pages\Page;

class OutOfStock extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.reports.pages.out-of-stock';

    protected static ?string $cluster = Reports::class;
}
