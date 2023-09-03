<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SucursalesDocumentosSerie;
use App\Http\Controllers\ResponseController;

class SucursalesDocumentosSerieController extends Controller
{   //$tipo, $sucursal
    public function __construct(){
        $this->response = new ResponseController();
    }
    public function index(Request $request){

        $tipo = $request->input("tipo") ??'';
        $sucursal = $request->input("sucursal") ??'';

        if($tipo == "" || $sucursal == ""){
            return $this->response->error("Faltan algunos parametros");
        }

        $series = SucursalesDocumentosSerie::query()
        ->where("idsucursal",'=', $sucursal)
        ->where("tipo", '=', $tipo)
        ->where("estado", '=', 1)
        ->where("principal", '=', 1)
        ->get()
        ->toArray();
        
        if(empty($series)){
            return null;
        }

        $serie = $series[0]["serie"]??'';
        $correlativo = $series[0]["correlativo"]??'';

        $return = [
            "serie"=> $serie,
            "correlativo"=> $correlativo,
        ];

        return response()->json($return, 200);
    }
    public function generate_next_document($tipo = 0, $sucursal = 0){

        if($tipo == 0 && $sucursal == 0){
            throw new Exception("Error Processing Request", 1);            
        }

        $series = SucursalesDocumentosSerie::query()
                    ->where("idsucursal",'=', $sucursal)
                    ->where("tipo", '=', $tipo)
                    ->where("estado", '=', 1)
                    ->where("principal", '=', 1)
                    ->get()
                    ->toArray();

        if(empty($series)){
            return [];
        }

        $id = $series[0]["id"]??0;
        $serie = $series[0]["serie"]??'';
        $correlativo = $series[0]["correlativo"]??0;

        $return = [
            "serie"=> $serie,
            "correlativo"=> $correlativo,
        ];
                
        $series = SucursalesDocumentosSerie::find($id);

        $correlativo = (int)$correlativo+1;

        $series->correlativo = "$correlativo";

        $series->save();

        return $return;
    }
}
