<?php

namespace App\Http\Controllers;


use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Models\TipoDocumento;
use Illuminate\Support\Facades\Auth;
use App\Models\SucursalesDocumentosSerie;
use App\Http\Controllers\ResponseController;

class LocalesSeriesController extends Controller
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

        $auth = Auth::user();
        $sucursal_id = $auth->sucursals_id ?? 1;
        
        $sucursals = Sucursal::all();

        $series = TipoDocumento::query()
                    ->leftJoin('sucursales_documentos_series', 'sucursales_documentos_series.tipo', '=', 'tipo_documentos.id')
                    ->select(
                        "tipo_documentos.id as idtipo", 
                        "tipo_documentos.documento",
                        "sucursales_documentos_series.id",
                        "sucursales_documentos_series.serie",
                        "sucursales_documentos_series.correlativo",                        
                    )                 
                    ->where("sucursales_documentos_series.idsucursal", "=", $sucursal_id)  
                    ->orWhere("sucursales_documentos_series.idsucursal", "=", null)
                    ->where("tipo_documentos.id", "<>", 6)
                    ->get();

        return response()->json([
            "sucursales" => $sucursals,
            "series" => $series,

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request)
    {

        $auth = Auth::user();
        $sucursal_id = $auth->sucursals_id ?? 1;
        
        $id_sucursal = $request->input("id")??'';
        $codigo = $request->input("codigo")??'';
        $nombre = $request->input("nombre")??'';
        $codigo_sunat = $request->input("codigo_sunat")??'';
        $ubigeo = $request->input("ubigeo")??'';
        $departamento = $request->input("departamento")??0;
        $provincia = $request->input("provincia")??0;
        $distrito = $request->input("distrito")??0;
        $direccion = $request->input("direccion")??'';

        $sucursal = Sucursal::find($id_sucursal);

        if(empty($sucursal)){

            if($nombre !="" && $codigo!=""){

                $sucursal = new Sucursal();
    
                $sucursal->codigo = $codigo;
                $sucursal->codigo_sunat = $codigo_sunat;
                $sucursal->nombre = $nombre;
                $sucursal->ubigeo = $ubigeo;
                $sucursal->departamento = $departamento;
                $sucursal->provincia = $provincia;
                $sucursal->distrito = $distrito;
                $sucursal->direccion = $direccion;
                
                $sucursal->save();
            }

        }else{

            $sucursal->codigo = $codigo;
            $sucursal->codigo_sunat = $codigo_sunat;
            $sucursal->nombre = $nombre;
            $sucursal->ubigeo = $ubigeo;
            $sucursal->departamento = $departamento;
            $sucursal->provincia = $provincia;
            $sucursal->distrito = $distrito;
            $sucursal->direccion = $direccion;
            
            $sucursal->save();
        }


        $series = $request->input("series")??[];

        if(count($series) > 0)
        {
            foreach($series as $item)
            {
                $i_id = $item["id"]??0;
                $i_tipo = $item["idtipo"]??0;
                $i_serie = $item["serie"]??"";
                $i_correlativo = $item["correlativo"]??"";
                $i_estado = $item["estado"]??"";
                $i_principal = $item["principal"]??"";
                
                if($i_tipo !=0 && $i_tipo!=null && $i_id!=0)
                {
                    $sucursales_serie = SucursalesDocumentosSerie::find($i_id);

                    if(!empty($sucursales_serie)){

                        $sucursales_serie->idsucursal = $sucursal_id;
                        $sucursales_serie->tipo = $i_tipo;
                        $sucursales_serie->serie = $i_serie;
                        $sucursales_serie->correlativo = $i_correlativo;
                        $sucursales_serie->estado = 1;
                        $sucursales_serie->principal = 1;
        
                        $sucursales_serie->save();
                    }
    
                }else{
                    
                    $sucursales_serie = SucursalesDocumentosSerie::where("tipo", $i_tipo)
                                                                    ->where("idsucursal", $sucursal_id)
                                                                    ->first();
                    if(empty($sucursales_serie)){

                        $sucursales_serie = new SucursalesDocumentosSerie();
                        
                        $sucursales_serie->idsucursal = $sucursal_id;
                        $sucursales_serie->tipo = $i_tipo;
                        $sucursales_serie->serie = $i_serie;
                        $sucursales_serie->correlativo = $i_correlativo;
                        $sucursales_serie->estado = 1;
                        $sucursales_serie->principal = 1;
        
                        $sucursales_serie->save();
                    }
                    

                }
            }
        }

        
        $sucursals = Sucursal::all();

        $series = TipoDocumento::query()
                    ->leftJoin('sucursales_documentos_series', 'sucursales_documentos_series.tipo', '=', 'tipo_documentos.id')
                    ->select(
                        "tipo_documentos.id as idtipo",
                        "tipo_documentos.documento",
                        "sucursales_documentos_series.id",
                        "sucursales_documentos_series.serie",
                        "sucursales_documentos_series.correlativo",                        
                    )                 
                    ->where("sucursales_documentos_series.idsucursal", "=", $sucursal_id)  
                    ->orWhere("sucursales_documentos_series.idsucursal", "=", null)
                    ->where("tipo_documentos.id", "<>", 6)
                    ->get();

        return $this->response->success([
            "sucursales" => $sucursals,
            "series" => $series,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sucursal = Sucursal::find($id);

        if(empty($sucursal)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $sucursal->delete();

        return $this->response->success($sucursal, "El registro fue eliminado correctamente");
    }
}
