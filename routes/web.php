<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;



Route::get('/invoices/{order}', [InvoiceController::class, 'show'])->name('invoices.index')->middleware('auth');


require __DIR__.'/auth.php';
