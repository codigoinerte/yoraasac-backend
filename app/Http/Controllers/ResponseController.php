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

    public function success($data, $mensaje = "El registro fue guardado exitosamente", $icon = "success", $title = "Exito!")
    {
        return response()->json([
            'success'   => true,
            'icon' => $icon,
            'message'   => $mensaje,
            'title' => $title,
            'data'=> $data,            
        ], 200);
    }
}

?>