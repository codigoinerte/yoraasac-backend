<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\contacto as contactoRequest;

class ContactoController extends Controller
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
        //
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
    public function store(contactoRequest $request)
    {
        $nombre = $request->input("nombre") ?? '';
        $email = $request->input("email") ?? '';
        $celular = $request->input("celular") ?? '';
        $principal = $request->input("principal") ?? 0;

        if($principal == 1){
            Contacto::where('principal', '1')->update(['principal' => '0']);
        }

        $contacto = new Contacto();
        $contacto->nombre = $nombre;
        $contacto->email = $email;
        $contacto->celular = $celular;
        $contacto->principal = $principal;
        $contacto->sistemas_id = 1;
        
        $contacto->save();

        return response()->json([

            'data' => $contacto

        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contacto = Contacto::find($id);

        if($contacto){
            
            return $this->response->success($contacto, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(contactoRequest $request, $id)
    {
        $nombre = $request->input("nombre") ?? '';
        $email = $request->input("email") ?? '';
        $celular = $request->input("celular") ?? '';
        $principal = $request->input("principal") ?? 0;

        if($principal == 1){
            Contacto::where('principal', '1')->update(['principal' => '0']);
        }

        $contacto = Contacto::find($id);

        if(empty($contacto)){
            return $this->response->error("No se envio un id valido");
        }

        $contacto->nombre = $nombre;
        $contacto->email = $email;
        $contacto->celular = $celular;
        $contacto->principal = $principal;
        
        $contacto->save();

        return response()->json([

            'data' => $contacto

        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $moneda = Contacto::find($id);

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
}
