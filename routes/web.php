<?php

use App\Http\Controllers\CentroPublicoController;
use App\Livewire\ActualizacionRapida;
use Illuminate\Support\Facades\Route;

/*
 * Vista publica. Sin auth, sin sesion obligatoria: cualquier friccion
 * en el lado del donante mata el uso. El panel vive en /admin (Filament).
 */
Route::get('/', [CentroPublicoController::class, 'index'])->name('publico.index');
Route::get('/centro/{centro}', [CentroPublicoController::class, 'show'])->name('publico.centro');

/*
 * Actualizacion rapida: la usa el coordinador desde el celular, parado en
 * la bodega. Va detras de auth porque escribe sobre el inventario.
 */
Route::get('/rapido/{centro}', ActualizacionRapida::class)
    ->middleware('auth')
    ->name('rapido');
