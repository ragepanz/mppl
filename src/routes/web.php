<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use App\Http\Controllers\InvoiceController;


/*
|--------------------------------------------------------------------------
| Livewire Asset Routes (Do Not Remove)
|--------------------------------------------------------------------------
| Digunakan jika aplikasi menggunakan subfolder atau domain khusus.
*/
Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});

/*
|--------------------------------------------------------------------------
| Public Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Client Panel Routes (Harus login)
|--------------------------------------------------------------------------
*/

    // 🔁 Riwayat Pesanan
  
    // 📄 Invoice: Tampilkan & Download PDF
    Route::get('/invoice/{order}', [InvoiceController::class, 'show'])->name('client.invoice.show');
    Route::get('/invoice/{order}/download', [InvoiceController::class, 'download'])->name('client.invoice.download');

    // 🖨️ Cetak Pesanan

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Harus login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // 📄 Invoice: Tampilkan dari sisi admin
    Route::get('/invoice/{order}', [InvoiceController::class, 'show'])->name('admin.invoice.show');
    Route::get('/invoice/{order}/download', [InvoiceController::class, 'download'])->name('admin.invoice.download');
});
