<?php

namespace App\Http\Requests\Endereco;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EnderecoStoreRequest extends FormRequest
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
            'rua' => 'required|min:3|max:191',
            'avenida' => 'required|min:3|max:191',
            'andar' => 'required|min:3|max:191',
            'unidade_id' => 'required|exists:App\Models\Unidade,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */

     public function messages()
     {
        return [
            'rua.required' => 'o campo :attribute é obrigatório',
            'rua.min' => 'o campo :attribute deve conter no minimo :min caracteres',
            'rua.max' => 'o campo :attribute deve conter no maximo :max caracteres',
            'andar.required' => 'o campo :attribute é obrigatório',
            'andar.min' => 'o campo :attribute deve conter no minimo :min caracteres',
            'andar.max' => 'o campo :attribute deve conter no maximo :max caracteres',
            'unidade_id.required' => 'o campo :attribute é obrigatório',
            'unidade_id.exists' => 'o relacionamento não existe para o id informado',
        ];
     }

     public function withValidator($validator){

        if($validator->fails()){
            throw new HttpResponseException(response()->json([
                'msg' => 'Ops! Algum campo não foi preenchido corretamente!',
                'status' => false,
                'errors' => $validator->errors(),
                'url' => route('unidade.store')
            ], 403));
        }

    }


}
