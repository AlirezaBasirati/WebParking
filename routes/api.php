<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RequestLogger;

Route::middleware([RequestLogger::class])->group(function () {
    Route::post('sales-invoices', [InvoiceController::class, 'create'])->name('sales-invoices');
});

Route::get('invoice', [InvoiceController::class, 'index'])->name('invoice');
Route::get('log', [InvoiceController::class, 'invoiceWithLog'])->name('log');
