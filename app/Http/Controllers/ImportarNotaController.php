<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ImportarNota;
use Illuminate\Http\Request;
use App\Models\nota_heladero;
use App\Imports\MultiTableImport;
use App\Models\NotaHeladeroDetalle;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\NotaHeladeroController;

class ImportarNotaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->response = new ResponseController();
        $this->stock = new StockHeladosController();
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
    /* store excel */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // Crea una instancia de la clase de importación
        $import = new MultiTableImport;

        // Realiza la importación (esto carga los datos en la propiedad $rows)
        Excel::import($import, $request->file('file'));

        // Accede a los datos importados
        $data = $import->rows;

        $array_notas = [];

        $NotaHeladeroController = new NotaHeladeroController();
        $productos = $NotaHeladeroController->listPublicProducts();
        $productos = $productos->original["data"] ?? [];

        // Ahora puedes manipular los datos como desees
        foreach ($data as $key => $row) {
            
            if($key == 0){
                continue;
            }

            $codigo_usuario = $row[0]??null;
            $codigo_producto = $row[1]??null;
            $cantidad = $row[2]??null;

            $user_id = User::where("documento", $codigo_usuario)->value("id");
            $count_nota = nota_heladero::where('user_id', $user_id)->count();

            if($count_nota == 0)
            {
                if(!isset($array_notas[$codigo_usuario]["detalle"]) && !empty($codigo_usuario)){
                    $array_notas[$codigo_usuario]["detalle"] = [];
                }
                if(!empty($codigo_usuario)){
                    $array_notas[$codigo_usuario]["detalle"][$codigo_producto] = $cantidad;                
                }
            }
        }

        if(count($array_notas) > 0){
            foreach($array_notas as $k => $item){
                $codigo_usuario = $k;
                $detalle = $item["detalle"] ?? [];

                if(count($productos) > 0){
                    foreach($productos as $producto){

                        $codigo_producto = $producto->codigo ?? null;
                        $found = false;

                        if(count($detalle) > 0){
                            foreach($detalle as $codigo => $cantidad){
                                if($codigo === $codigo_producto){
                                    $found = true;
                                    break;
                                }                                
                            }
                        }

                        if($found == true) continue;

                        $array_notas[$k]["detalle"][$codigo_producto] = 0;

                    }
                }
            }
        }
        
        /*
        return response()->json([
            "data" => $array_notas
        ], 200);
        */

        if(count($array_notas) > 0){

            $monedaController = new MonedaController();        
            $moneda_id = $monedaController->getMonedaPrincipal()->id ?? 1;

            $auth = Auth::user();
            $sucursal_id = $auth->sucursals_id ?? 1;
            $user_id = $auth->id;
            $array_cod_documentos = [];
            
            foreach($array_notas as $codigo_usuario => $item){

                /* creacion de nota */
                $nota_heladero = new nota_heladero();

                $heladero_id = User::where("documento", $codigo_usuario)->value("id");

                $nota_heladero->user_id         = $heladero_id;
                $nota_heladero->estado          = 4;
                $nota_heladero->moneda_id       = $moneda_id;
                $nota_heladero->id_sucursal     = $sucursal_id;
                $nota_heladero->id_usuario      = $user_id;
                $nota_heladero->parent_id       = 0;
                $nota_heladero->cucharas        = 0;
                $nota_heladero->conos           = 0;
                $nota_heladero->save();

                /* creacion de detalle de nota*/
                $detalle = $item["detalle"] ?? [];

                if(count($detalle) > 0){
                    foreach($detalle as $codigo => $devolucion){
                        
                        $nota_detalle = new NotaHeladeroDetalle();
        
                        $nota_detalle->devolucion = $devolucion;
                        $nota_detalle->pedido = 0;
                        $nota_detalle->codigo = $codigo;
                        $nota_detalle->vendido = 0;
                        $nota_detalle->importe = 0;
                        $nota_detalle->nota_heladeros_id = $nota_heladero->id;
        
                        $nota_detalle->save();
                    }
                }

                $iddoc = $nota_heladero->id;
                $nota_heladero = nota_heladero::find($iddoc);

                
                $numero_documento = str_pad("$iddoc$heladero_id", 12, "0", STR_PAD_LEFT);
                $nota_heladero->codigo = $numero_documento;
                $nota_heladero->save();
                
                array_push($array_cod_documentos, $numero_documento);
            }

            return $this->response->success($array_cod_documentos, "El archivo fue importado con exito");
        }
        else
        {
            return $this->response->success([], "No hubo registros para importar");
        }        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImportarNota  $importarNota
     * @return \Illuminate\Http\Response
     */
    public function show(ImportarNota $importarNota)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ImportarNota  $importarNota
     * @return \Illuminate\Http\Response
     */
    public function edit(ImportarNota $importarNota)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ImportarNota  $importarNota
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ImportarNota $importarNota)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImportarNota  $importarNota
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImportarNota $importarNota)
    {
        //
    }
}
