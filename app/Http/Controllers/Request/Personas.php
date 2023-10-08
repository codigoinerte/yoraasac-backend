<?php

namespace App\Http\Controllers\Request;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Personas extends FormRequest
{
    //
    public function rules(Request $request)
    {           
        /*
        Rule::unique('users')->when(in_array($request['usuario_tipo'], [1, 2, 3]), function ($query) {
            return $query->whereNotNull('email');
            
        })   
        function ($attribute, $value, $fail) use ($request) {
            if (in_array($request->usuario_tipo, [1, 2, 3]) && is_null($value)) {
                $fail('El campo email es obligatorio para los usuarios de tipo 1, 2 o 3');
            }
        }
        */  


        return [

            'documento' => 'required',
            'documento_tipo' => 'required|integer|between:1,7',
            'nombres' => 'required',
            'apellidos' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->usuario_tipo, [1, 2, 3, 4, 6, 7]) && is_null($value)) {
                        $fail('El campo apellidos es obligatorio');
                    }
                }
            ],
            'usuario_tipo'=> 'required|integer|between:1,7',            
            'email'=> [
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->usuario_tipo, [1, 2, 3]) && is_null($value)) {
                        $fail('El campo email es obligatorio para los usuarios');
                    }
                },  
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->usuario_tipo, [1, 2, 3]) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('El campo email debe ser una dirección de correo electrónico válida.');
                    }
                },
                function ($attribute, $value, $fail) use ($request) {                    
                    
                    $split = explode("/",$request->getRequestUri()) ?? [];
                    $id = $split[3]??0;
                    
                    $respuesta = User::query()
                                ->where('email', $request->email)
                                ->where('id', '<>', $id)
                                ->count();

                    if (in_array($request->usuario_tipo, [1, 2, 3]) && $respuesta) {
                        
                        $fail('El campo email debe ser unico.');
                    }
                },                
                'max:255',
            ]
            
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

?>