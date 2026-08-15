<?php

use App\Http\Controllers\CentroPublicoController;
use App\Http\Controllers\InscripcionPublicaController;
use App\Livewire\ActualizacionRapida;
use Illuminate\Support\Facades\Route;

/*
 * Vista publica. Sin auth, sin sesion obligatoria: cualquier friccion
 * en el lado del donante mata el uso. El panel vive en /admin (Filament).
 */
Route::get('/', [CentroPublicoController::class, 'index'])->name('publico.index');
Route::get('/centro/{centro}', [CentroPublicoController::class, 'show'])->name('publico.centro');

/*
 * Voluntarios. Sin cuenta, como el resto de la vista publica.
 */
Route::get('/turno/{turno}', [InscripcionPublicaController::class, 'create'])->name('publico.turno');

// El unico endpoint publico que escribe en la base y recibe datos
// personales. Sin cuenta ni captcha, el limite por IP es la unica defensa
// contra que alguien lo inunde de inscripciones falsas.
Route::post('/turno/{turno}', [InscripcionPublicaController::class, 'store'])
    ->middleware('throttle:6,60')
    ->name('publico.turno.anotar');

Route::view('/privacidad', 'publico.privacidad')->name('publico.privacidad');

/*
 * Actualizacion rapida: la usa el coordinador desde el celular, parado en
 * la bodega. Va detras de auth porque escribe sobre el inventario.
 */
Route::get('/rapido/{centro}', ActualizacionRapida::class)
    ->middleware('auth')
    ->name('rapido');
