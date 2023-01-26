<?php

namespace App\Http\Controllers;

use App\Http\Requests\Caixa\CaixaStoreRequest;
use App\Http\Requests\Caixa\CaixaUpdateRequest;
use App\Http\Resources\Caixa\CaixaCollectionResource;
use App\Http\Resources\Caixa\CaixaResource;
use App\Models\Caixa;
use App\Models\Unidade;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;

class CaixaController extends Controller
{
    private $caixa;

    public function __construct(Caixa $caixa)
    {
        $this->caixa = $caixa;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $caixas = $this->caixa->with(['unidade', 'endereco'])->orderBy('numero')->get();

            return new CaixaCollectionResource($caixas);

        } catch (\Throwable|Exception $e) {

            return ResponseService::exception('caixa.show', null, $e);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CaixaStoreRequest $request)
    {
        try {

            $caixa = $this->caixa->create([
                'numero' => $request->numero,
                'espaco_total' => $request->espaco_total,
                'espaco_ocupado' => $request->espaco_ocupado,
                'espaco_disponivel' => $request->espaco_disponivel,
                'unidade_id' => $request->unidade_id,
                'endereco_id' => $request->endereco_id,
            ]);

            return new CaixaResource($caixa, ['route' => 'caixa.store', 'type' => 'store']);

        }catch(\Throwable|\Exception $e) {

            return ResponseService::exception('caixa.store', null, $e);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            if($id === null) {
                throw new Exception('Informe o id do registro.');
            }

            $caixa = $this->caixa->find($id);

            if(!$caixa){
                throw new Exception('Nenhum registro encontrado.');
            }

            return new CaixaResource($caixa, ['route' => 'caixa.detalhes', 'type' => 'detalhes']);

        }catch(\Throwable|\Exception $e) {
            return ResponseService::exception('caixa.show', $id, $e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CaixaUpdateRequest $request, $id)
    {
        try {
            $caixa = $this->caixa->find($id);

            if(!$caixa){
                throw new Exception('Nenhum registro encontrado.');
            }

            $caixa->update([
                'numero' => $request->numero,
                'espaco_total' => $request->espaco_total,
                'espaco_ocupado' => $request->espaco_ocupado,
                'espaco_disponivel' => $request->espaco_disponivel,
                'unidade_id' => $request->unidade_id,
                'endereco_id' => $request->endereco_id,
            ]);

            return new CaixaResource($caixa, ['route' => 'caixa.update', 'type' => 'update']);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('caixa.update', $id, $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $caixa = $this->caixa->find($id);

            if(!$caixa){
                throw new Exception('Nenhum registro encontrado.');
            }

            $caixa->delete();

            return ResponseService::default(['route' => 'caixa.destroy', 'type' => 'destroy'], $id);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('caixa.destroy', $id, $e);
        }
    }
}
