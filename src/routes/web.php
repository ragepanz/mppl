<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use App\Http\Controllers\InvoiceController;
use App\Providers\Filament\ClientPanelProvider;

/*
|--------------------------------------------------------------------------
| Livewire Asset Routes
|--------------------------------------------------------------------------
*/
Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/livewire/livewire.js', $handle);
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
| Client Panel Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('client')->group(function () {
    Route::get('/invoice/{order}', [InvoiceController::class, 'download'])
     ->name('invoice.download');
    
});

/*
|--------------------------------------------------------------------------
| Filament Panel Registration
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return redirect()->route('filament.client.pages.dashboard');
})->middleware(['auth']);