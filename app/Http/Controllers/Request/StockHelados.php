<?php

namespace App\Http\Controllers\Request;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StockHelados extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'movimientos_id'=> 'required|integer|exists:movimientos,id',
            'tipo_documento_id'=> 'required|integer|exists:tipo_documentos,id'
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
