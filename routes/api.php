<?php

use App\Http\Controllers\CaixaController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\TipoDocumentoController;
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

// routes unidades
Route::get('/unidade', [UnidadeController::class, 'index'])->name('unidade.show');
Route::get('/unidade/{id}', [UnidadeController::class, 'show'])->name('unidade.detalhes');
Route::put('/unidade/{id}', [UnidadeController::class, 'update'])->name('unidade.update');
Route::delete('/unidade/{id}', [UnidadeController::class, 'destroy'])->name('unidade.destroy');
Route::post('/unidade', [UnidadeController::class, 'store'])->name('unidade.store');

// routes endereços
Route::get('/endereco', [EnderecoController::class, 'index'])->name('endereco.show');
Route::get('/endereco/{id}', [EnderecoController::class, 'show'])->name('endereco.detalhes');
Route::put('/endereco/{id}', [EnderecoController::class, 'update'])->name('endereco.update');
Route::delete('/endereco/{id}', [EnderecoController::class, 'destroy'])->name('endereco.destroy');
Route::post('/endereco', [EnderecoController::class, 'store'])->name('endereco.store');

// routes tipoDocumento
Route::get('/tipo-documento', [TipoDocumentoController::class, 'index'])->name('tipo-documento.show');
Route::get('/tipo-documento/{id}', [TipoDocumentoController::class, 'show'])->name('tipo-documento.detalhes');
Route::put('/tipo-documento/{id}', [TipoDocumentoController::class, 'update'])->name('tipo-documento.update');
Route::delete('/tipo-documento/{id}', [TipoDocumentoController::class, 'destroy'])->name('tipo-documento.destroy');
Route::post('/tipo-documento', [TipoDocumentoController::class, 'store'])->name('tipo-documento.store');

// routes caixas
Route::get('/caixa', [CaixaController::class, 'index'])->name('caixa.show');
Route::get('/caixa/{id}', [CaixaController::class, 'show'])->name('caixa.detalhes');
Route::put('/caixa/{id}', [CaixaController::class, 'update'])->name('caixa.update');
Route::delete('/caixa/{id}', [CaixaController::class, 'destroy'])->name('caixa.destroy');
Route::post('/caixa', [CaixaController::class, 'store'])->name('caixa.store');
