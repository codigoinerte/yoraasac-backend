<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sucursalesDocumentosSerie;
use App\Http\Controllers\ResponseController;

class SucursalesDocumentosSerieController extends Controller
{   //$tipo, $sucursal
    public function __construct(){
        $this->response = new ResponseController();
    }
    public function get_document($tipo, $sucursal){

        $series = sucursalesDocumentosSerie::query()
        ->where("idsucursal",'=', $sucursal)
        ->where("tipo", '=', $tipo)
        ->where("estado", '=', 1)
        ->where("principal", '=', 1)
        ->get();

        if(empty($series)){
            return null;
        }

        $return = [
            "serie"=> $serie,
            "correlativo"=> $correlativo,
        ];

        return $this->response->success($return);
    }
    public function generate_next_document($tipo = 0, $sucursal = 0){

        if($tipo == 0 && $sucursal == 0){
            throw new Exception("Error Processing Request", 1);            
        }

        $series = sucursalesDocumentosSerie::query()
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
                
        $series = sucursalesDocumentosSerie::find($id);

        $correlativo = (int)$correlativo+1;

        $series->correlativo = "$correlativo";

        $series->save();

        return $return;
    }
}
