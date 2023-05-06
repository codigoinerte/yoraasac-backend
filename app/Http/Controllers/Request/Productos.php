<?php

namespace App\Http\Controllers\Request;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Productos extends FormRequest
{
    public function rules(Request $request)
    {
        return [

            'codigo' => 'required',            
            'nombre' => 'required',
            'precio_venta' => 'required',
            'estados_id'=> 'required|integer|exists:estados,id',            
            'unidad_id'=> 'required|integer|exists:unidad,id',
            'moneda_id'=> 'required|integer|exists:moneda,id',
            'igv_id'=> 'required|integer|exists:igv,id'
            
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
