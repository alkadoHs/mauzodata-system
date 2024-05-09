<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Reports;
use Filament\Pages\Page;

class SellerReport extends Page
{
    protected static ?string $cluster = Reports::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    protected static ?string $title = "Monthly Sellers Report";

    protected static string $view = 'filament.pages.seller-report';

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'admin';
    }

}
