<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoDocumento\TipoDocumentoStoreRequest;
use App\Http\Requests\TipoDocumento\TipoDocumentoUpdateRequest;
use App\Http\Resources\TipoDocumento\TipoDocumentoCollectionResource;
use App\Http\Resources\TipoDocumento\TipoDocumentoResource;
use App\Models\TipoDocumento;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller
{
    private $tipoDocumento;

    public function __construct(TipoDocumento $tipoDocumento)
    {
        $this->tipoDocumento = $tipoDocumento;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $tipoDocumentos = $this->tipoDocumento->orderBy('descricao')->get();

            return new TipoDocumentoCollectionResource($tipoDocumentos);

        } catch (\Throwable|Exception $e) {

            return ResponseService::exception('tipo-documento.show', null, $e);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TipoDocumentoStoreRequest $request)
    {
        try {

            $tipoDocumento = $this->tipoDocumento->create([
                'descricao' => $request->descricao,
                'temporalidade' => $request->temporalidade,
            ]);

            return new TipoDocumentoResource($tipoDocumento, ['route' => 'tipo-documento.store', 'type' => 'store']);

        }catch(\Throwable|\Exception $e) {

            return ResponseService::exception('tipo-documento.store', null, $e);
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

            $tipoDocumento = $this->tipoDocumento->find($id);

            if(!$tipoDocumento){
                throw new Exception('Nenhum registro encontrado.');
            }

            return new TipoDocumentoResource($tipoDocumento, ['route' => 'tipo-documento.detalhes', 'type' => 'detalhes']);

        }catch(\Throwable|\Exception $e) {
            return ResponseService::exception('tipo-documento.show', $id, $e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TipoDocumentoUpdateRequest $request, $id)
    {
        try {
            $tipoDocumento = $this->tipoDocumento->find($id);

            if(!$tipoDocumento){
                throw new Exception('Nenhum registro encontrado.');
            }

            $tipoDocumento->update([
                'descricao' => $request->descricao,
                'temporalidade' => $request->temporalidade,
            ]);

            return new TipoDocumentoResource($tipoDocumento, ['route' => 'tipo-documento.update', 'type' => 'update']);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('tipo-documento.update', $id, $e);
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
            $tipoDocumento = $this->tipoDocumento->find($id);

            if(!$tipoDocumento){
                throw new Exception('Nenhum registro encontrado.');
            }

            $tipoDocumento->delete();

            return ResponseService::default(['route' => 'tipo-documento.destroy', 'type' => 'destroy'], $id);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('tipo-documento.destroy', $id, $e);
        }
    }
}
