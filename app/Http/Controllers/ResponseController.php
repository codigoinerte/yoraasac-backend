<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function error($mensaje)
    {
        return response()->json([
            'success'=> false,
            "message"=> $mensaje
        ], 400);
    }

    public function success($data, $mensaje = "El registro fue guardado exitosamente")
    {
        return response()->json([
            'success'   => true,
            'message'   => $mensaje,
            'data'=> $data,            
        ], 200);
    }
}

?>