<?php

namespace App\Http\Controllers;

use App\Http\Resources\Documento\DocumentoCollectionResource;
use App\Http\Resources\Documento\DocumentoResource;
use App\Models\Caixa;
use App\Models\Documento;
use App\Models\HistoricoArquivo;
use App\Models\Unidade;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentoController extends Controller
{

    private $documento;

    public function __construct(Documento $documento)
    {
        $this->documento = $documento;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $query = $this->documento->with(['tipoDocumento', 'caixa.predio'])
            ->when($request->get('descricao'), function ($query) use ($request) {
                return $query->where('descricao', 'like', '%' . $request->get('descricao') . '%');
            })
            ->when($request->get('status'), function ($query) use ($request) {
                return $query->where('status', '=', $request->get('status'));
            }, function ($query){
                return $query->where('status', 'aguardando');
            })->when($request->get('predio_id'), function ($query) use ($request) {
                return $query->where('predio_id', '=', $request->get('predio_id'));
            })->when($request->get('tipo_documento_id'), function ($query) use ($request) {
                return $query->where('tipo_documento_id', '=', $request->get('tipo_documento_id'));
            })->when($request->get('ordenar_campo'), function ($query) use ($request) {
                return $query->orderBy(
                    $request->get('ordenar_campo'),
                    $request->get('ordenar_direcao') ?? 'asc'
                );
            })
            ->when($request->get('page'), function ($query) {
                return $query->paginate(12);
            }, function($query){
                return $query->get();
            });

            return new DocumentoCollectionResource($query);

        } catch (\Throwable|Exception $e) {

            return ResponseService::exception('documento.show', null, $e);
        }
    }

    /**
     * Buscar espaço disponivel para endereçamento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function buscar_enderecamento(Request $request)
    {
        try{
            $espaco_ocupado = $request->get('espaco_ocupado');
            $numero = $request->get('numero');

            //pegar informações do documento a ser endereçado
            $documento = $this->documento->where('documento', $numero)->first();

            if(!$documento){
                throw new \Error('O documento informado não foi localizado', 404);
            }else if($documento->status === 'arquivado'){
                throw new \Error('O documento já está endereçado', 404);
            }
            //ultima caixa lançada no sistema por ordem de numero (número é unico e ordem descrescente)
            $ultima_caixa = Caixa::
            orderByDesc('caixas.id')
            ->first();

            //total de caixas por predio - considerando ultima caixa recomendada pelo sistema
            $espaco_predio = DB::select('CALL P_ESPACO_DISPONIVEL(:predio_id)', [
                ':predio_id' => $ultima_caixa->predio_id
            ])[0];


            //caixas que possuem espaço disponivel para ser armazenado
            $caixas = Caixa::
            with(['predio','documentos'])
            ->espacoDisponivel($espaco_ocupado)
            ->when($request->get('predio_id'), function ($query) use ($request) {
                $query->where('predio_id', $request->get('predio_id'));
            })
            ->whereNot('id', $ultima_caixa->id)
            ->orderBy('id', 'desc')
            ->paginate(6);

            $predios_disponiveis = DB::select(
                'SELECT
                    predios.id as predio_id
                FROM predios

                JOIN caixas on caixas.predio_id = predios.id

                WHERE caixas.espaco_disponivel > 0

                GROUP BY predio_id'
            );

            //validação do proximo endereço
            $proximo_endereco = (object) array('caixa_id' => '', 'predio_id' => '', 'andar_id' => '');

            if($espaco_predio->espaco_disponivel_total == 0 || $espaco_predio->total_caixas === 63 && $ultima_caixa->espaco_disponivel < $espaco_ocupado){
                //não existe espaço no predio e total de caixas já atingiu o máximo
                $proximo_endereco->predio_id = $espaco_predio->predio_id + 1;
                $proximo_endereco->caixa_id = $ultima_caixa->id + 1;
                $proximo_endereco->andar_id = ++$ultima_caixa->andar_id > 9 ? 1 : $ultima_caixa->andar_id;
            }else if($espaco_predio->espaco_disponivel_total == 0 && $espaco_predio->total_caixas < 63 && $ultima_caixa->espaco_disponivel == 0 || $ultima_caixa->espaco_disponivel < $espaco_ocupado){
                //não existe espaço no predio, não atingiu o total de caixas, no entanto, a ultimoa caixa não possui espaço
                $proximo_endereco->predio_id = $espaco_predio->predio_id;
                $proximo_endereco->caixa_id = $ultima_caixa->id + 1;
                $proximo_endereco->andar_id = Unidade::localizacaoAndar(++$espaco_predio->total_caixas);
            }else{
                //o prédio não atingiu o total de caixas e possui espaço na última caixa
                $proximo_endereco->predio_id = $espaco_predio->predio_id;
                $proximo_endereco->caixa_id = $ultima_caixa->id;
                $proximo_endereco->andar_id = Unidade::localizacaoAndar($espaco_predio->total_caixas);
            }

            return response()->json([
                'documento' => $documento,
                'proximo_endereco' => $proximo_endereco,
                'ultima_caixa' => $ultima_caixa,
                'predio' => $espaco_predio,
                'caixas' => $caixas,
                'espaco_ocupado' => $espaco_ocupado,
                'predios_disponiveis' => $predios_disponiveis,
            ]);

        } catch (\Throwable|Exception $e) {

            return ResponseService::exception('documento.espaco_disponivel', null, $e);

        }
    }

    /**
     * Salvar endereço em um documento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function salvar_enderecamento(Request $request)
    {
        try {
            //iniciar um transação de dados
            DB::beginTransaction();

            $numero_documento = $request->get('numero');
            $espaco_ocupado = floatval($request->get('espaco_ocupado'));
            $numero_caixa = $request->get('numero_caixa');
            $andar_id = (int) $request->get('andar_id');
            $predio_id = Unidade::getIdPredio($request->get('predio_id'));

            //pegar informações do documento a ser endereçado
            $documento = $this->documento->where('documento', $numero_documento)->first();

            if($documento->status === 'arquivado'){
                throw new \Error('O documento já está endereçado', 404);
            }

            //verifica se a caixa existe / se não cria uma caixa nova
            $caixa = Caixa::where('id', $numero_caixa)->first();

            if($caixa){
                //alterar caixa
                $caixa->update([
                    'espaco_ocupado' => $espaco_ocupado + $caixa->espaco_ocupado,
                    'espaco_disponivel' => $caixa->espaco_disponivel - $espaco_ocupado,
                    'status' => ($caixa->espaco_disponivel - $espaco_ocupado) == 0 ? 'ocupado' : 'disponivel',
                    'predio_id' => $predio_id,
                    'andar_id' => $andar_id,
                ]);
            }else{
                //criar caixa
                $caixa = Caixa::create(
                    [
                        'numero' => $numero_caixa,
                        'espaco_total' => 80,
                        'espaco_ocupado' => $espaco_ocupado,
                        'espaco_disponivel' => 80 - $espaco_ocupado,
                        'predio_id' => $predio_id,
                        'andar_id' => $andar_id
                    ]
                );
            }

            $documento->update([
                'espaco_ocupado' => $espaco_ocupado,
                'status' => 'arquivado',
                'caixa_id' => $caixa->id,
                'predio_id' => $predio_id,
            ]);

            return ResponseService::default(['type' => 'update', 'route' => 'documento.espaco_disponivel']);

            //comit de trsanações
            DB::commit();

        } catch (\Throwable|Exception $e) {

            //estorna as trnsações temporarias
            DB::rollBack();

            return ResponseService::exception('documento.espaco_disponivel', null, $e);

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

            $documento = $this->documento
                        ->with(['tipoDocumento', 'caixa.predio' , 'caixa'])
                        ->find($id);

            return new DocumentoResource($documento, ['type' => 'detalhes', 'route' => 'documento.detalhes', 'id' => $id]);

        } catch (\Throwable|Exception $e) {
            return ResponseService::exception('documento.detalhes', $id, $e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
