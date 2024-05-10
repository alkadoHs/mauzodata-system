<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
 
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;
    
    protected function filtersForm(Form $form): Form
    {
         return $form
                ->schema([
                    Section::make()
                        ->schema([
                            DatePicker::make('startDate')
                            ->native(false),
                            DatePicker::make('endDate')
                            ->native(false),
                        ])
                        ->columns(2),
                ]);
    }
}