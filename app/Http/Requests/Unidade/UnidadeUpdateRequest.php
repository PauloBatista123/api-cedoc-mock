<?php

namespace App\Http\Requests\Unidade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UnidadeUpdateRequest extends FormRequest
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
            'nome' => 'required|min:3|max:191',
            'status' => 'required',
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
            'nome.required' => 'O :attribute deve ser obrigatório.',
            'nome.min' => 'O :attribute deve ter pelo menos :min caracteres.',
            'nome.max' => 'O :attribute deve ter pelo menos :max caracteres.',
            'status.required' => 'O :attribute deve ser obrigatório.',
        ];
    }

    public function withValidator($validator){

        if($validator->fails()){
            throw new HttpResponseException(response()->json([
                'msg' => 'Ops! Algum campo não foi preenchido corretamente!',
                'status' => false,
                'errors' => $validator->errors(),
                'url' => route('unidade.update')
            ], 403));
        }

    }
}
