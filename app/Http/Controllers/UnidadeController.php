<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUnidadeRequest;
use App\Http\Requests\Unidade\UnidadeStoreRequest;
use App\Http\Requests\Unidade\UnidadeUpdateRequest;
use App\Http\Resources\Unidade\UnidadeCollectionResource;
use App\Http\Resources\Unidade\UnidadeResource;
use App\Models\Unidade;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{

    private $unidade;

    public function __construct(Unidade $unidade)
    {
        $this->unidade = $unidade;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $unidades = $this->unidade->orderBy('nome')->get();

            return new UnidadeCollectionResource($unidades);

        } catch (\Throwable|Exception $e) {

            return ResponseService::exception('unidade.index', null, $e);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UnidadeStoreRequest $request)
    {
        try {

            $unidade = $this->unidade->create([
                'nome' => $request->nome,
                'status' => 'ativo'
            ]);

            return new UnidadeResource($unidade, ['route' => 'unidade.store', 'type' => 'store']);

        }catch(\Throwable|\Exception $e) {

            return ResponseService::exception('unidade.store', null, $e);
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

            $unidade = $this->unidade->find($id);

            if(!$unidade){
                throw new Exception('Nenhum registro encontrado.');
            }

            return new UnidadeResource($unidade, ['route' => 'unidade.detalhes', 'type' => 'detalhes']);

        }catch(\Throwable|\Exception $e) {
            return ResponseService::exception('unidade.show', $id, $e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UnidadeUpdateRequest $request, $id)
    {
        try {
            $unidade = $this->unidade->find($id);

            if(!$unidade){
                throw new Exception('Nenhum registro encontrado.');
            }

            $unidade->update([
                'nome' => $request->nome,
                'status' => $request->status,
            ]);

            return new UnidadeResource($unidade, ['route' => 'unidade.update', 'type' => 'update']);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('unidade.update', $id, $e);
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
            $unidade = $this->unidade->find($id);

            if(!$unidade){
                throw new Exception('Nenhum registro encontrado.');
            }

            $unidade->delete();

            return ResponseService::default(['route' => 'unidade.destroy', 'type' => 'destroy'], $id);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('unidade.destroy', $id, $e);
        }
    }
}
