<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BuscarUsuario;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ResponseController;

class BuscarUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->response = new ResponseController();
    }
    public function index(Request $request)
    {
        $buscar = $request->input('buscar') ?? '';
        $tipo = $request->input('tipo') ?? '7';
        $type = $request->input("type") ?? '';

        if($buscar == ""){
            return $this->response->success($buscar, "No se envio un nombre valido");
        }

        $buscar = strtolower($buscar);

        if($type == 'codigo')
        {
            $usuario = User::query()
                        ->Where('id', 'LIKE', "%$buscar%")
                        ->orderBy('created_at','desc')
                        ->get();

        }
        else
        {
            $usuario = User::query()
                        ->whereRaw('usuario_tipo = ? AND (name LIKE ? OR apellidos LIKE ? OR documento LIKE ?) ', [$tipo, "%$buscar%", "%$buscar%", "%$buscar%"])
                        ->orderBy('created_at','desc')
                        ->get();
        }

        return response()->json([

            'data' => $usuario

        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BuscarUsuario  $buscarUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(BuscarUsuario $buscarUsuario)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BuscarUsuario  $buscarUsuario
     * @return \Illuminate\Http\Response
     */
    public function edit(BuscarUsuario $buscarUsuario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BuscarUsuario  $buscarUsuario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BuscarUsuario $buscarUsuario)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BuscarUsuario  $buscarUsuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(BuscarUsuario $buscarUsuario)
    {
        //
    }
}
