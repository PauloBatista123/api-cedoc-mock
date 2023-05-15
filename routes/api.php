<?php

use App\Http\Controllers\CaixaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\LoginSecurityController;
use App\Http\Controllers\RepactuacaoController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\PredioController;
use App\Http\Controllers\UserController;
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


Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('2fa')->group(function () {

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

        // routes documento
        Route::get('/documento', [DocumentoController::class, 'index'])->name('documento.show');
        Route::get('/documento/espaco-disponivel', [DocumentoController::class, 'buscar_enderecamento'])->name('documento.espaco_disponivel');
        Route::get('/documento/proximo-endereco', [DocumentoController::class, 'proximo_endereco'])->name('documento.proximo_endereco');
        Route::post('/documento/enderecar', [DocumentoController::class, 'salvar_enderecamento'])->name('documento.salvar_enderecamento');
        Route::get('/documento/enderecar/filtro', [DocumentoController::class, 'filtro'])->name('documento.filtro');
        Route::get('/documento/{id}', [DocumentoController::class, 'show'])->name('documento.detalhes');
        Route::put('/documento/{id}', [DocumentoController::class, 'update'])->name('documento.update');
        Route::delete('/documento/{id}', [DocumentoController::class, 'destroy'])->name('documento.destroy');
        Route::post('/documento', [DocumentoController::class, 'store'])->name('documento.store');
        Route::post('/documento/importar', [DocumentoController::class, 'importar'])->name('documento.importar');
        Route::post('/documento/importar/novos', [DocumentoController::class, 'importar_novos'])->name('documento.importar_novos');
        Route::get('/documento/importar/progress', [DocumentoController::class, 'progress_batch'])->name('documento.importar.progress');
        Route::get('/documento/importar/progress/{id}', [DocumentoController::class, 'buscar_progress_batch'])->name('documento.importar.progress.buscar');
        Route::get('/documento/importar/now/{id}', [DocumentoController::class, 'buscar_progress_now'])->name('documento.importar.progress.now');
        Route::post('/documento/espaco-ocupado/{id}', [DocumentoController::class, 'espaco_ocupado'])->name('documento.editar.espaco_ocupado');

        // routes caixas
        Route::get('/caixa', [CaixaController::class, 'index'])->name('caixa.show');
        Route::get('/caixa/{id}', [CaixaController::class, 'show'])->name('caixa.detalhes');
        Route::put('/caixa/{id}', [CaixaController::class, 'update'])->name('caixa.update');
        Route::delete('/caixa/{id}', [CaixaController::class, 'destroy'])->name('caixa.destroy');
        Route::post('/caixa', [CaixaController::class, 'store'])->name('caixa.store');

        // routes para repactuação
        Route::put('/repactuar/fila/{id}', [RepactuacaoController::class, 'salvar_fila_repactuacao'])->name('repactuacao.salvar_fila_repactuacao');
        Route::get('/repactuar/fila', [RepactuacaoController::class, 'fila'])->name('repactuacao.fila');
        Route::post('/repactuar/enderecar', [RepactuacaoController::class, 'enderecar'])->name('repactuacao.enderecar');
        Route::get('/repactuar/lista', [RepactuacaoController::class, 'lista'])->name('repactuacao.lista');
        Route::put('/repactuar/fila/deletar/{id}', [RepactuacaoController::class, 'deletar_fila_repactuacao'])->name('repactuacao.deletar_fila_repactuacao');

        // ruoutes predios
        Route::get('/predios/disponiveis', [PredioController::class, 'disponiveis'])->name('predios.disponiveis');


    });

    Route::prefix('2fa')->group(function(){
        Route::get('/', [LoginSecurityController::class, 'show2faForm'])->name('2fa');
        Route::post('/generateSecret',[LoginSecurityController::class, 'generate2faSecret'])->name('generate2faSecret');
        Route::post('/enable2fa',[LoginSecurityController::class, 'enable2fa'])->name('enable2fa');
        Route::post('/disable2fa',[LoginSecurityController::class, 'disable2fa'])->name('disable2fa');

        Route::post('/2faVerify', [LoginSecurityController::class, 'verify2fa'])->name('2faVerify');
    });

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

});

// rotas de login e tokens
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

