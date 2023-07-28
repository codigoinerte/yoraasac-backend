<?php

namespace App\Http\Controllers;

use App\Models\Marcas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\Marcas as MarcasRequest;

class MarcasController extends Controller
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
    
    public function index()
    {
        $data = Marcas::all();

        return response()->json([

            'data' => $data

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
    public function store(MarcasRequest $request)
    {
        $nombre = $request->input("nombre") ?? '';

        $marca = new Marcas();

        $marca->nombre = $nombre;

        $marca->save();

        return response()->json([

            'data' => $marca

        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Marcas  $marcas
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = Marcas::find($id);

        if($marca){
            
            return $this->response->success($marca, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marcas  $marcas
     * @return \Illuminate\Http\Response
     */
    public function edit(Marcas $marcas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Marcas  $marcas
     * @return \Illuminate\Http\Response
     */
    public function update(MarcasRequest $request, $id)
    {
        $nombre = $request->input("nombre") ?? '';
        
        $marca = Marcas::find($id);

        $marca->nombre = $nombre;

        $marca->save();

        return response()->json([

            'data' => $marca

        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marcas  $marcas
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marcas = Marcas::find($id);

        if(empty($marcas)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $marcas->delete();

        return $this->response->success($marcas, "El registro fue eliminado correctamente");
    }
}
