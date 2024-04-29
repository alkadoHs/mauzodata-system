<?php

use App\Http\Controllers\InvoiceController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $company = "Mauzodata Store";
    dd(session()->all());
    return view('welcome', ['company' => $company]);
});

Route::get('/invoices/{order}', [InvoiceController::class, 'show'])->name('invoices.index')->middleware('auth');
