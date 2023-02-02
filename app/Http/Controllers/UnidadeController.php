<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUnidadeRequest;
use App\Http\Requests\Unidade\UnidadeStoreRequest;
use App\Http\Requests\Unidade\UnidadeUpdateRequest;
use App\Http\Resources\Unidade\UnidadeCollectionResource;
use App\Http\Resources\Unidade\UnidadeResource;
use App\Models\Andar;
use App\Models\Unidade;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function index(Request $request)
    {
        try {

            $query = $this->unidade
                    ->when($request->get('nome'), function ($query) use ($request) {
                        $query->where('nome', 'like', '%'.$request->get('nome').'%');
                    })
                    ->when($request->get('status'), function ($query) use ($request) {
                        $query->where('status', '=', $request->get('status'));
                    })
                    ->when($request->get('ordem'), function($query) use ($request) {
                    $query->orderBy($request->get('ordem'));
                    }, function ($query){
                    $query->orderBy('id');
                    })->when($request->get('page'), function ($query) use($request){
                    if($request->get('page') < 0){
                        return $query->get();
                    }
                    return $query->paginate(10);
                    });


            return new UnidadeCollectionResource($query);

        } catch (\Throwable|Exception $e) {

            return ResponseService::exception('unidade.show', null, $e);
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

            //inicia uma trasanção para o banco de dados (caso ocorra algum erro será realizado um rollback)
            DB::beginTransaction();

            $firstPredio = Unidade::orderBy('id', 'desc')->first();

            $predio = Unidade::create([
                'numero' => !is_null($firstPredio) ? (int) $firstPredio->numero + 1 : 1,
                'observacao' => 'Predio criado automático pelo sistema',
                'status' => 'ativo'
            ]);

            for($i = 1; $i <= 9; $i++){
                Andar::create([
                    'numero' => $i,
                    'predio_id' => $predio->id,
                ]);
            }


            DB::commit();

            return new UnidadeResource($predio, ['route' => 'unidade.store', 'type' => 'store']);

        }catch(\Throwable|\Exception $e) {

            DB::rollback();

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

            $unidade = $this->unidade->with(['andares.caixas.documentos'])->find($id);

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

            if(count($unidade->enderecos)){
                throw new Exception('Não podemos excluir a unidade pois possui endereços vinculados...');
            }

            $unidade->delete();

            return ResponseService::default(['route' => 'unidade.destroy', 'type' => 'destroy'], $id);

        } catch (\Throwable|\Exception $e) {

            return ResponseService::exception('unidade.destroy', $id, $e);
        }
    }
}
