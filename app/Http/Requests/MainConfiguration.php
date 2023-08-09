<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MainConfiguration extends FormRequest
{
    public function rules()
    {
        return [
            'ruc' => 'required',
            'razon_social'=> 'required',
            'razon_comercial'=> 'required',
            'pagina_web'=> 'required',
            'email_empresa'=> 'required',
            'celular'=> 'required'            
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));

    }
}
