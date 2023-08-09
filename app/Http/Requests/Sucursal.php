<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Sucursal extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'codigo' => 'required',
            'codigo_sunat' => 'required',
            'nombre' => 'required',
            'ubigeo' => 'required',
            'moneda' => 'required',
            
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
