<?php

namespace App\Http\Services;

use App\Models\Documento;
use App\Models\Unidade;
use App\Models\Caixa;
use App\Http\Services\CaixaService;
use App\Services\RastreabilidadeService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentoService {


    public function __construct(
        protected CaixaService $caixaService,
        protected RastreabilidadeService $rastreabilidadeService
    ) {}

    public function proximoEndereco($espaco_ocupado)
    {
        //ultima caixa lançada no sistema por ordem de numero (número é unico e ordem descrescente)
        $ultima_caixa = $this->caixaService->ultimaCaixa();
        $espaco_predio = $this->espacoDisponivelPredio($ultima_caixa);

        //validação do proximo endereço
        $proximo_endereco = (object) array('caixa_id' => '', 'predio_id' => '', 'andar_id' => '', 'ordem' => '');

        if((int) $espaco_predio->espaco_disponivel_total == 0 && $espaco_predio->total_caixas === 63 && $ultima_caixa->espaco_disponivel < $espaco_ocupado){
            //não existe espaço no predio e total de caixas já atingiu o máximo
            $proximo_endereco->predio_id = $espaco_predio->predio_id + 1;
            $proximo_endereco->caixa_id = $ultima_caixa->id + 1;
            $proximo_endereco->andar_id = ++$ultima_caixa->andar_id > 9 ? 1 : $ultima_caixa->andar_id;
            $proximo_endereco->ordem = Documento::ordem($ultima_caixa->id + 1);

        }else if((int) $espaco_predio->espaco_disponivel_total == 0 && $espaco_predio->total_caixas < 63 && $ultima_caixa->espaco_disponivel == 0 || $ultima_caixa->espaco_disponivel < $espaco_ocupado){
            //não existe espaço no predio, não atingiu o total de caixas, no entanto, a ultimoa caixa não possui espaço
            $proximo_endereco->predio_id = $espaco_predio->predio_id;
            $proximo_endereco->caixa_id = $ultima_caixa->id + 1;
            $proximo_endereco->andar_id = Unidade::localizacaoAndar(++$espaco_predio->total_caixas);
            $proximo_endereco->ordem = Documento::ordem($ultima_caixa->id + 1);
        }else{
            //o prédio não atingiu o total de caixas e possui espaço na última caixa
            $proximo_endereco->predio_id = $espaco_predio->predio_id;
            $proximo_endereco->caixa_id = $ultima_caixa->id;
            $proximo_endereco->andar_id = Unidade::localizacaoAndar($espaco_predio->total_caixas);
            $proximo_endereco->ordem = Documento::ordem($ultima_caixa->id);
        }

        return $proximo_endereco;
    }

    public function espacoDisponivelPredio($ultima_caixa)
    {
        //total de caixas por predio - considerando ultima caixa recomendada pelo sistema
        $espaco_predio = DB::select(
        'SELECT

        COUNT(c.id) as total_caixas, SUM(c.espaco_disponivel) as espaco_disponivel_total, p.id as predio_id

        from predios p

        join caixas c on c.predio_id = p.id

        where p.id = :predio_id

        order by c.id desc',
        [
            ':predio_id' => $ultima_caixa->predio_id
        ])[0];

        return $espaco_predio;
    }

    public function getDocumento($tipo_documento_id, $cpf_cooperado, $numero, $page)
    {
        $documentos = Documento::with(['tipoDocumento', 'usuario', 'predio'])
        ->when($tipo_documento_id, function ($query) use ($tipo_documento_id) {
            $query->where('tipo_documento_id', '=', $tipo_documento_id);
        })->when($cpf_cooperado, function ($query) use ($cpf_cooperado) {
            $query->where('cpf_cooperado', '=', $cpf_cooperado);
        })->when($numero, function ($query) use ($numero) {
            $query->where('documento', '=', $numero);
        })
        ->where('status', 'alocar')
        ->paginate(10, ['*'], 'page', $page);

        return $documentos;
    }

    public function prediosDisponiveis()
    {
        return DB::select(
            'SELECT
                predios.id as predio_id
            FROM predios

            JOIN caixas on caixas.predio_id = predios.id

            WHERE caixas.espaco_disponivel > 0

            GROUP BY predio_id'
        );
    }

    public function enderecar(
        $caixaId,
        $documento,
        $espaco_ocupado,
        $observacao,
        $ordem,
        $predio_id,
        $andar_id
    ) : Documento
    {


        if($documento->status === 'arquivado'){
            throw new \Error('O documento já está endereçado', 404);
        }

        //verifica se a caixa existe / se não cria uma caixa nova
        $caixa = $this->caixaService->alterar_conteudo_caixa(
            $caixaId,
            $espaco_ocupado,
            $predio_id,
            $andar_id,
            'entrada'
        );

        $documento->update([
            'espaco_ocupado' => $espaco_ocupado,
            'status' => 'arquivado',
            'caixa_id' => $caixa->id,
            'predio_id' => $predio_id,
            'observacao' => $observacao,
            'ordem' => $ordem
        ]);

        $this->rastreabilidadeService->create(
            'arquivar',
            $documento->id,
            Auth()->user()->id,
            'Registro manual de arquivamento'
        );

        return $documento;
    }

    public function create(
        string $documento,
        int    $tipo_documento_id,
        string $nome,
        string $cpf,
        string|null $vencimento,
        string|null $valor,
        string $user_id
    ): Documento
    {
        try{

            if(Documento::where(
                [
                    ['documento', '=', $documento],
                    ['tipo_documento_id', '=', $tipo_documento_id],
                    ['cpf_cooperado', '=', $cpf]
                ],
            )->count()){
                throw new \Error('O documento já existe no sistema', 404);
            }

            $documento = Documento::create([
                'documento' => $documento,
                'tipo_documento_id' => $tipo_documento_id,
                'nome_cooperado' => $nome,
                'cpf_cooperado' => $cpf,
                'vencimento_operacao' => Carbon::parse($vencimento) ?? null,
                'valor_operacao' => $valor ?? null,
                'user_id' => $user_id,
            ]);

            return $documento;

        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Busca o documento de acordo com o id.
     *
     * @param  int  $id
     * @return Documento
     */

    public function findById(int $id): Documento
    {
        return Documento::find($id);
    }

    /**
     * Alterar o espaço ocupado de um documento individual
     *
     * @param  Documento  $documento
     * @param  mixed  $espaco_ocupado
     * @return bool
     */

    public function alterar_espaco_ocupado(
        Documento $documento,
        mixed $espaco_ocupado
    )
    {
        return $documento->update([
            'espaco_ocupado' => $espaco_ocupado
        ]);
    }

}
