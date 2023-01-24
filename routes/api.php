<?php

use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\UnidadeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/unidade', [UnidadeController::class, 'index'])->name('unidade.show');
Route::get('/unidade/{id}', [UnidadeController::class, 'show'])->name('unidade.detalhes');
Route::put('/unidade/{id}', [UnidadeController::class, 'update'])->name('unidade.update');
Route::delete('/unidade/{id}', [UnidadeController::class, 'destroy'])->name('unidade.destroy');
Route::post('/unidade', [UnidadeController::class, 'store'])->name('unidade.store');

Route::get('/endereco', [EnderecoController::class, 'index'])->name('endereco.show');

