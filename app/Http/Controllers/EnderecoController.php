<?php

namespace App\Http\Controllers;

use App\Http\Requests\Endereco\EnderecoStoreRequest;
use App\Http\Requests\Endereco\EnderecoUpdateRequest;
use App\Http\Resources\Endereco\EnderecoCollectionResource;
use App\Http\Resources\Endereco\EnderecoResource;
use App\Models\Endereco;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;

class EnderecoController extends Controller
{
    private $endereco;

    public function __construct(Endereco $endereco)
    {
        $this->endereco = $endereco;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $enderecos = $this->endereco->with(['unidade'])->orderBy('rua')->paginate(10);

            return new EnderecoCollectionResource($enderecos);

        } catch (\Throwable|Exception $e) {

            return ResponseService::exception('endereco.show', null, $e);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EnderecoStoreRequest $request)
    {
        try {

            $endereco = $this->endereco->create([
                'rua' => $request->rua,
                'avenida' => $request->avenida,
                'andar' => $request->andar,
                'unidade_id' => $request->unidade_id,
            ]);

            return new EnderecoResource($endereco, ['route' => 'endereco.store', 'type' => 'store']);

        }catch(\Throwable|\Exception $e) {

            return ResponseService::exception('endereco.store', null, $e);
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

            $endereco = $this->endereco->find($id);

            if(!$endereco){
                throw new Exception('Nenhum registro encontrado.');
            }

            return new EnderecoResource($endereco, ['route' => 'endereco.detalhes', 'type' => 'detalhes']);

        }catch(\Throwable|\Exception $e) {
            return ResponseService::exception('endereco.show', $id, $e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EnderecoUpdateRequest $request, $id)
    {
        try {
            $endereco = $this->endereco->find($id);

            if(!$endereco){
                throw new Exception('Nenhum registro encontrado.');
            }

            $endereco->update([
                'nome' => $request->nome,
                'status' => $request->status,
            ]);

            return new EnderecoResource($endereco, ['route' => 'endereco.update', 'type' => 'update']);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('endereco.update', $id, $e);
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
            $endereco = $this->endereco->find($id);

            if(!$endereco){
                throw new Exception('Nenhum registro encontrado.');
            }

            $endereco->delete();

            return ResponseService::default(['route' => 'endereco.destroy', 'type' => 'destroy'], $id);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('endereco.destroy', $id, $e);
        }
    }

}
