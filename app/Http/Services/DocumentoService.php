<?php

namespace App\Http\Services;

use App\Models\Documento;
use App\Models\Unidade;
use Illuminate\Support\Facades\DB;

class DocumentoService {


    public function proximoEndereco($espaco_predio, $ultima_caixa, $espaco_ocupado)
    {
        //validação do proximo endereço
        $proximo_endereco = (object) array('caixa_id' => '', 'predio_id' => '', 'andar_id' => '', 'ordem' => '');

        if($espaco_predio->espaco_disponivel_total == 0 || $espaco_predio->total_caixas === 63 && $ultima_caixa->espaco_disponivel < $espaco_ocupado){
            //não existe espaço no predio e total de caixas já atingiu o máximo
            $proximo_endereco->predio_id = $espaco_predio->predio_id + 1;
            $proximo_endereco->caixa_id = $ultima_caixa->id + 1;
            $proximo_endereco->andar_id = ++$ultima_caixa->andar_id > 9 ? 1 : $ultima_caixa->andar_id;
            $proximo_endereco->ordem = Documento::ordem($ultima_caixa->id + 1);
        }else if($espaco_predio->espaco_disponivel_total == 0 && $espaco_predio->total_caixas < 63 && $ultima_caixa->espaco_disponivel == 0 || $ultima_caixa->espaco_disponivel < $espaco_ocupado){
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

}
