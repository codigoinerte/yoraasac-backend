<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;

class PaisController extends Controller
{
    public function index(Request $request)
    {
        $data = Pais::all();

        return response()->json([

            'data' => $data            

        ], 200);
    }

    public function show(Request $request)
    {
        
    }
}
