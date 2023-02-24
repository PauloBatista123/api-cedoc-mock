<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DocumentoStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'documento' => 'required|max:191|unique:documentos',
            'tipo_documento_id' => 'required|exists:App\Models\TipoDocumento,id',
            'nome' => 'required|max:191',
            'cpf' => 'required|max:14|min:11',
        ];
    }

    public function messages()
    {
        return [
            'documento.required' => 'o campo :attribute é obrigatório',
            'documento.max' => 'o campo :attribute pode conter no máximo :max caracteres',
            'documento.unique' => 'O dossiê já existe',
            'tipo_documento_id.required' => 'o campo :attribute é obrigatório',
            'tipo_documento_id.exists' => 'Não encontramos o tipo informado',
            'nome_co.required' => 'o campo :attribute é obrigatório',
            'cpf_co.required' => 'o campo :attribute é obrigatório',
        ];
    }

    public function withValidator($validator){

        if($validator->fails()){
            throw new HttpResponseException(response()->json([
                'msg' => 'Algum erro na validação dos campos!',
                'status' => false,
                'errors' => $validator->errors(),
                'url' => route('documento.store')
            ], 403));
        }

    }
}
