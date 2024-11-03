<?php

use App\Http\Controllers\avalanchecontroller;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/avalanche', [avalanchecontroller::class, 'avalanche'])->name('avalanche');
