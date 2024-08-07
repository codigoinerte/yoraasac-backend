<?php

namespace App\Http\Controllers;

use App\Models\Igv;
use App\Models\sistema;
use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\MainConfiguration as MainConfigurationRequest;

class MainConfiguration extends Controller
{
    public function __construct()
    {
        $this->response = new ResponseController();
    }
    
    public function index(Request $request){
        
        $sistemas = sistema::find(1);

        $contactos = Contacto::all();

        $igvs = Igv::all();

        $id = $sistemas->id ?? '';
        $ruc = $sistemas->ruc ?? '';
        $razon_social = $sistemas->razon_social ?? '';
        $razon_comercial = $sistemas->razon_comercial ?? '';
        $pagina_web = $sistemas->pagina_web ?? '';
        $email_empresa = $sistemas->email_empresa ?? '';
        $celular = $sistemas->celular ?? '';    
        $igv = $sistemas->igv ?? 1;
        $logo = $sistemas->logo ?? '';
        $cargo_baterias = $sistemas->cargo_baterias ?? 0;

        $response = [
            "ruc" => $ruc,
            "razon_social" => $razon_social,
            "razon_comercial" => $razon_comercial,
            "pagina_web" => $pagina_web,
            "email_empresa" => $email_empresa,
            "celular" => $celular,
            "igv" => $igv,
            "contactos"=> $contactos,
            "igvs"=> $igvs,
            "logo"=> $logo,
            "cargo_baterias" => $cargo_baterias
        ];

        
      
        return response()->json([

            'data' => $response

        ], 200);
    }
    /*
    public function store(MainConfigurationRequest $request){

        $ruc = $request->input("ruc") ?? '';
        $razon_social = $request->input("razon_social") ?? '';
        $razon_comercial = $request->input("razon_comercial") ?? '';
        $pagina_web = $request->input("pagina_web") ?? '';
        $email_empresa = $request->input("email_empresa") ?? '';
        $celular = $request->input("celular") ?? '';
        $igv = $request->input("igv") ?? '';

        $sistema = new sistema();
        $sistema->ruc = $ruc;
        $sistema->razon_social = $razon_social;
        $sistema->razon_comercial = $razon_comercial;
        $sistema->pagina_web = $pagina_web;
        $sistema->email_empresa = $email_empresa;
        $sistema->celular = $celular;
        $sistema->igv = $igv;

        $sistema->save();

        return response()->json([

            'data' => $sistema

        ], 200);

    }
    */
    public function show($id){

        $sistema = sistema::find($id);

        if($sistema){
            
            return $this->response->success($sistema, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    public function update(MainConfigurationRequest $request, $id){

        $ruc = $request->input("ruc") ?? '';
        $razon_social = $request->input("razon_social") ?? '';
        $razon_comercial = $request->input("razon_comercial") ?? '';
        $pagina_web = $request->input("pagina_web") ?? '';
        $email_empresa = $request->input("email_empresa") ?? '';
        $celular = $request->input("celular") ?? '';
        $igv = $request->input("igv") ?? '';
        $logo = $request->input("logo") ?? '';
        $cargo_baterias = $request->input("cargo_baterias") ?? '';
        $contactos = $request->input("contactos") ?? [];

        $sistema = sistema::find($id);

        if(empty($sistema)){
            return $this->response->error("No se envio un id valido");
        }

        $sistema->ruc = $ruc;
        $sistema->razon_social = $razon_social;
        $sistema->razon_comercial = $razon_comercial;
        $sistema->pagina_web = $pagina_web;
        $sistema->email_empresa = $email_empresa;
        $sistema->celular = $celular;
        $sistema->igv = $igv;
        $sistema->cargo_baterias = $cargo_baterias;
        $sistema->logo = $logo;

        $sistema->save();

        foreach($contactos as $contacto){
            $i_id = $contacto["id"]??0;
            $i_nombre = $contacto["nombre"]??'';
            $i_email = $contacto["email"]??'';
            $i_celular = $contacto["celular"]??'';
            $i_principal = $contacto["principal"]??0;

            if($i_principal == 1){
                Contacto::where('principal', '1')->update(['principal' => '0']);
            }

            $contacto = Contacto::find($i_id);

            if(empty($contacto) || $i_id == 0){
                $contacto = new Contacto();                

                $contacto->nombre = $i_nombre;
                $contacto->email = $i_email;
                $contacto->celular = $i_celular;
                $contacto->principal = $i_principal;
                $contacto->sistemas_id = $i_id;

                $contacto->save();
            }
            else
            {
                $contacto->nombre = $i_nombre;
                $contacto->email = $i_email;
                $contacto->celular = $i_celular;
                $contacto->principal = $i_principal;
                $contacto->sistemas_id = $i_id;
                
                $contacto->save();
            }
            

        }

        $contactos = Contacto::all();

        $igvs = Igv::all();

        $response = [
            "ruc" => $ruc,
            "razon_social" => $razon_social,
            "razon_comercial" => $razon_comercial,
            "pagina_web" => $pagina_web,
            "email_empresa" => $email_empresa,
            "celular" => $celular,
            "igv" => $igv,
            "contactos"=> $contactos,
            "igvs"=> $igvs,
            "logo"=> $logo,
            "cargo_baterias"=> $cargo_baterias,
        ];

        return response()->json([

            'data' => $response

        ], 200);

    }

    /*
     public function destroy(){
        $sistema = sistema::find($id);

        if(empty($sistema)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $sistema->delete();

        return $this->response->success($sistema, "El registro fue eliminado correctamente");        
    }
    */
}
