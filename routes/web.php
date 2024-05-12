<?php

use App\Http\Controllers\InvoiceController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return redirect('https://home.mauzodata.com');
// });

Route::get('/invoices/{order}', [InvoiceController::class, 'show'])->name('invoices.index')->middleware('auth');
