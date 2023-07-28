<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\Moneda as RequestMoneda;

class MonedaController extends Controller
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
        $data = Moneda::all();

        return response()->json([

            'data' => $data

        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestMoneda $request)
    {
        $nombre = $request->input("moneda") ?? '';
        $simbolo = $request->input("simbolo") ?? '';
        $codigo = $request->input("codigo") ?? '';
        $digitos = $request->input("digitos") ?? 2;
        $decimales = $request->input("decimales") ?? '';
        $miles = $request->input("miles") ?? '';
        $principal = $request->input("principal") ?? 0;


        if($principal == 1){
            Moneda::where('principal', '1')->update(['principal' => '0']);
        }

        $moneda = new Moneda();

        $moneda->moneda = $nombre;
        $moneda->simbolo = $simbolo;
        $moneda->codigo = $codigo;
        $moneda->digitos = $digitos;
        $moneda->sep_decimales = $decimales;
        $moneda->sep_miles = $miles;
        $moneda->principal = $principal;

        $moneda->save();

        return response()->json([

            'data' => $moneda

        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Moneda  $moneda
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $moneda = Moneda::find($id);

        if($moneda){
            
            return $this->response->success($moneda, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Moneda  $moneda
     * @return \Illuminate\Http\Response
     */
    public function edit(Moneda $moneda)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Moneda  $moneda
     * @return \Illuminate\Http\Response
     */
    public function update(RequestMoneda $request, $id)
    {
        $nombre = $request->input("moneda") ?? '';
        $simbolo = $request->input("simbolo") ?? '';
        $codigo = $request->input("codigo") ?? '';
        $digitos = $request->input("digitos") ?? 2;
        $decimales = $request->input("decimales") ?? '';
        $miles = $request->input("miles") ?? '';
        $principal = $request->input("principal") ?? 0;

        if($principal == 1){
            Moneda::where('principal', '1')->update(['principal' => '0']);
        }

        $moneda = Moneda::find($id);

        $moneda->moneda = $nombre;
        $moneda->simbolo = $simbolo;
        $moneda->codigo = $codigo;
        $moneda->digitos = $digitos;
        $moneda->sep_decimales = $decimales;
        $moneda->sep_miles = $miles;
        $moneda->principal = $principal;

        $moneda->save();

        return response()->json([

            'data' => $moneda

        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Moneda  $moneda
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $moneda = Moneda::find($id);

        if(empty($moneda)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $moneda->delete();

        return $this->response->success($moneda, "El registro fue eliminado correctamente");
    }

    public function getMonedaPrincipal()
    {
        $moneda = Moneda::query()
                    ->where("principal", "=", 1)
                    ->first();
        
        return $moneda;
    }
}
