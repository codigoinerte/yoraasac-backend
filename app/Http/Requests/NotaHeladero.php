<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class NotaHeladero extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'user_id' => 'required|exists:users,id',
            'estado'=> 'required|integer|exists:nota_heladero_estados,id',
            'id_sucursal'=> 'required|integer',
            'fecha_operacion' => 'required|date',
            'productos' => 'required|array'
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
