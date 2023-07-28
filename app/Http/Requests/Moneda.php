<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Moneda extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'moneda' => 'required',
            'simbolo'=> 'required',
            'codigo'=> 'required',
            'principal' => 'required|integer|between:0,1'
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
