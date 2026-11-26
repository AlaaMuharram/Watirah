<?php

use App\Http\Controllers\WatiraApiController;
use Illuminate\Support\Facades\Route;

Route::get('/all', [WatiraApiController::class, 'all']);
Route::put('/tables/{name}', [WatiraApiController::class, 'save']);
Route::delete('/all', [WatiraApiController::class, 'clear']);
