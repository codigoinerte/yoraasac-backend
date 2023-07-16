<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacturaEstado;

class FacturaEstadoController extends Controller
{
    public function index()
    {
        $data = FacturaEstado::all();

        return response()->json([

            'data' => $data

        ], 200);
    }
}
