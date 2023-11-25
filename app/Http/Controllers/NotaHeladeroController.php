<?php

namespace App\Http\Controllers;

use App\Models\productos;
use Illuminate\Http\Request;
use App\Models\nota_heladero;
use App\Models\NotaHeladeroDetalle;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\NotaHeladero as NotaHeladeroRequest;

class NotaHeladeroController extends Controller
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
    
    public function index(Request $request)
    {
        $page = $request->input('page') ?? 1;
        $documento = $request->input("documento") ?? '';
        $nombre = $request->input("nombre") ?? '';
        $estado = $request->input("estado") ?? 0;

        $query = nota_heladero::query()
                    ->leftJoin('users as heladero', 'nota_heladeros.user_id', '=', 'heladero.id')
                    ->leftJoin('users as creador', 'nota_heladeros.id_usuario', '=', 'creador.id')
                    ->leftJoin('moneda', 'nota_heladeros.moneda_id', '=', 'moneda.id')
                    ->leftJoin('sucursals', 'nota_heladeros.id_sucursal', '=', 'sucursals.id')
                    ->leftJoin('nota_heladero_estados as lestado', 'nota_heladeros.estado', '=', 'lestado.id')
                    ->select(
                        "nota_heladeros.id",
                        "nota_heladeros.user_id",
                        "nota_heladeros.moneda_id",
                        "nota_heladeros.id_sucursal",
                        "nota_heladeros.monto",
                        "nota_heladeros.pago",
                        "nota_heladeros.debe",
                        "nota_heladeros.ahorro",
                        "nota_heladeros.cucharas",
                        "nota_heladeros.conos",
                        "nota_heladeros.placas_entregas",
                        "nota_heladeros.placas_devueltas",
                        "nota_heladeros.fecha_guardado",
                        "nota_heladeros.fecha_apertura",
                        "nota_heladeros.fecha_cierre",
                        "nota_heladeros.id_usuario",
                        "nota_heladeros.created_at",
                        "nota_heladeros.updated_at",
                        "heladero.documento as heladero_documento",
                        "heladero.name as heladero_nombre",
                        "creador.name",
                        "sucursals.nombre",
                        "lestado.nombre as estado"
                    );

        if (!empty($documento) && $documento !="") {

            $query->where('heladero.documento', $documento);
        }
        
        if (!empty($nombre) && $nombre !="") {
            
            $query->where('heladero.name', 'LIKE', "%$nombre%");
        }        
        
        if(!empty($estado) && $estado != 0){
            $query->where('nota_heladeros.estado', '=', "$estado");
        }

        // if (!empty($fecha) && $fecha !="") {
        //     $query->whereDate('nota_heladeros.created_at', $fecha);
        // }

        $nota_heladero = $query->orderBy('nota_heladeros.created_at','desc')->paginate(10, ['*'], 'page', $page);

        $nextPageUrl = $nota_heladero->nextPageUrl();
        $previousPageUrl = $nota_heladero->previousPageUrl();
            
        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        $data = $nota_heladero->toArray()["data"] ?? [];

        $n=0;
        foreach($data as $item)
        {
            $created_at = $item["created_at"]??'';

            $fecha = str_replace("/", "-", $created_at);
            $newDate = date("d-m-Y", strtotime($fecha));		    

            $data[$n]["created_at"] = $newDate;
            $n++;
        }
        
        return response()->json([

            'data' => $data,
            'next_page' => isset($nextPageQueryParams['page']) ? $nextPageQueryParams['page'] : null,
            'previous_page' => isset($previousPageQueryParams['page']) ? $previousPageQueryParams['page'] : null,

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
    public function store(NotaHeladeroRequest $request)
    {
        $heladero_id = $request->input("user_id") ?? 0;        
        $estado_id = $request->input("estado") ?? 0;        
        $fecha_operacion = $request->input("fecha_operacion") ?? '';
        $productos = $request->input("productos")??[];

        $monedaController = new MonedaController();        
        $moneda_id = $monedaController->getMonedaPrincipal()->id ?? 1;

        $auth = Auth::user();
        $sucursal_id = $auth->sucursals_id ?? 1;
        $user_id = $auth->id;
        
        $nota_heladero = new nota_heladero();

        $nota_heladero->user_id         = $heladero_id;
        $nota_heladero->estado          = $estado_id;
        $nota_heladero->moneda_id       = $moneda_id;
        $nota_heladero->id_sucursal     = $sucursal_id;
        $nota_heladero->id_usuario      = $user_id;
        //$nota_heladero->fecha_guardado  = $fecha_operacion;
        if($estado_id == 2) //re apertura
        {
            $nota_heladero->fecha_apertura  = $fecha_operacion;
        }
        else if($estado_id == 3){ // guardado
            $nota_heladero->fecha_guardado  = $fecha_operacion;
        }
        else if($estado_id == 1){ // cierre
            $nota_heladero->fecha_cierre = $fecha_operacion;
        }
        
        $nota_heladero->cucharas        = 0;
        $nota_heladero->conos           = 0;

        $nota_heladero->save();

        $detalle = [];

        if(count($productos) > 0)
        {
            foreach($productos as $item)
            {
                $devolucion = $item["devolucion"]??0;
                $pedido = $item["pedido"]??0;
                $codigo = $item["codigo"]??0;
                $vendido = $item["vendido"]??0;
                $importe = $item["importe"]??0;
                
                $nota_detalle = new NotaHeladeroDetalle();

                $nota_detalle->devolucion = $devolucion;
                $nota_detalle->pedido = $pedido;
                $nota_detalle->codigo = $codigo;
                $nota_detalle->vendido = $vendido;
                $nota_detalle->importe = $importe;
                $nota_detalle->nota_heladeros_id = $nota_heladero->id;

                $nota_detalle->save();

                array_push($detalle, $nota_detalle);                
            }
        }

        $nota_heladero["detalle"] = $detalle;

        $array_detalle = [];

        if(count($productos) > 0)
        {
            foreach($productos as $item)
            {
                $devolucion = $item["devolucion"]??0;
                $pedido = $item["pedido"]??0;
                $codigo = $item["codigo"]??0;
                $vendido = $item["vendido"]??0;
                $importe = $item["importe"]??0;
                
                if($estado_id == 2){
                    /* re apertura :  salida */
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $devolucion + $pedido
                    ]);
                    
                }else if($estado_id == 3){
                    /* guardado:  ingreso */
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $devolucion
                    ]);
                }else if($estado_id == 1){
                    /*salida*/
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $vendido
                    ]);
                }
            }
        }

        $iddoc = $nota_heladero->id;
        $nota_heladero = nota_heladero::find($iddoc);

        
        $numero_documento = str_pad("$iddoc$heladero_id", 12, "0", STR_PAD_LEFT);

        $nota_heladero->codigo = $numero_documento;
        $nota_heladero->save();
        
        $this->stock->createMovimientoStock("nota", $estado_id, $nota_heladero->id, $heladero_id, $array_detalle, 2, $numero_documento);
        
        return $this->response->success($nota_heladero);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\nota_heladero  $nota_heladero
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$nota_heladero = nota_heladero::find($id);
        $nota_heladero = nota_heladero::query()
                    ->leftJoin('users as heladero', 'nota_heladeros.user_id', '=', 'heladero.id')
                    ->leftJoin('users as creador', 'nota_heladeros.id_usuario', '=', 'creador.id')
                    ->leftJoin('moneda', 'nota_heladeros.moneda_id', '=', 'moneda.id')
                    ->leftJoin('sucursals', 'nota_heladeros.id_sucursal', '=', 'sucursals.id')
                    ->leftJoin('nota_heladero_estados as lestado', 'nota_heladeros.estado', '=', 'lestado.id')
                    ->select(
                        "nota_heladeros.id",
                        "nota_heladeros.user_id",
                        "nota_heladeros.moneda_id",
                        "nota_heladeros.id_sucursal",
                        "nota_heladeros.monto",
                        "nota_heladeros.pago",
                        "nota_heladeros.debe",
                        "nota_heladeros.ahorro",
                        "nota_heladeros.cucharas",
                        "nota_heladeros.conos",
                        "nota_heladeros.placas_entregas",
                        "nota_heladeros.placas_devueltas",
                        "nota_heladeros.fecha_guardado",
                        "nota_heladeros.fecha_apertura",
                        "nota_heladeros.fecha_cierre",
                        "nota_heladeros.id_usuario",
                        "nota_heladeros.created_at",
                        "nota_heladeros.updated_at",
                        "heladero.documento as heladero_documento",
                        "heladero.name as heladero_nombre",
                        "creador.name",
                        "sucursals.nombre",
                        "nota_heladeros.estado",
                        "lestado.nombre as estado_nombre",
                        "moneda.moneda"
                    )
                    ->where('nota_heladeros.id', $id)
                    ->first();

        if($nota_heladero)
        {
            $detalle = NotaHeladeroDetalle::query()                                
                                ->leftJoin('productos',  'productos.codigo', '=', 'nota_heladero_detalle.codigo')
                                ->select(
                                    "nota_heladero_detalle.id",
                                    "nota_heladero_detalle.devolucion",
                                    "nota_heladero_detalle.pedido",
                                    "nota_heladero_detalle.vendido",
                                    "nota_heladero_detalle.importe",
                                    "nota_heladero_detalle.nota_heladeros_id",
                                    "nota_heladero_detalle.created_at",
                                    "nota_heladero_detalle.updated_at",
                                    "nota_heladero_detalle.codigo",
                                    "productos.nombre as producto",
                                    "productos.heladero_precio_venta",
                                    "productos.heladero_descuento"
                                )
                                ->where('nota_heladero_detalle.nota_heladeros_id', $id)
                                ->orderBy('nota_heladero_detalle.created_at','desc')
                                ->get();

            foreach($detalle as $key=>$item){
                $heladero_precio_venta = $item["heladero_precio_venta"] ?? '';
                $heladero_descuento = $item["heladero_descuento"] ?? '';
                $precio_operacion = $heladero_precio_venta - ($heladero_precio_venta * ($heladero_descuento / 100));
                
                $detalle[$key]["precio_operacion"] = $precio_operacion;
            }
                            /*
                            ->where("nota_heladeros_id","=", $id)
                            ->orderBy('created_at','desc')
                            ->get();
                            */            
            $nota_heladero["detalle"] = $detalle;

            return $this->response->success($nota_heladero, "El registro fue encontrado");
        }
        else{
            return $this->response->error("El registro no fue encontrado");
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\nota_heladero  $nota_heladero
     * @return \Illuminate\Http\Response
     */
    public function edit(NotaHeladeroRequest $request, $id)
    {           
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\nota_heladero  $nota_heladero
     * @return \Illuminate\Http\Response
     */
    public function update(NotaHeladeroRequest $request, $id)
    {
        $nota_heladero = nota_heladero::find($id);

        if(empty($nota_heladero)){
            return $this->response->error("No se envio un id valido");
        }

        $heladero_id = $request->input("user_id") ?? 0;        
        $estado_id = $request->input("estado") ?? 0;        
        $fecha_operacion = $request->input("fecha_operacion") ?? '';
        $productos = $request->input("productos")??[];

        $nota_heladero->estado = $estado_id;
        /*
        1:Cierre - fecha_cierre
        2:re-apertura - fecha_apertura
        3:guardado - fecha_guardado
        */
        
        if($estado_id == 2)
        {
            $nota_heladero->fecha_apertura  = $fecha_operacion;
        }
        else if($estado_id == 3){ // guardado
            $nota_heladero->fecha_guardado  = $fecha_operacion;
        }
        else if($estado_id == 1){
            $nota_heladero->fecha_cierre = $fecha_operacion;
        }

        $nota_heladero->cucharas = 0;
        $nota_heladero->conos = 0;

        $nota_heladero->save();

        $detalle = [];

        if(count($productos) > 0)
        {
            foreach($productos as $item)
            {
                $idDetalle  = $item["id"]??0;
                $devolucion = $item["devolucion"]??0;
                $pedido     = $item["pedido"]??0;
                $codigo     = $item["codigo"]??0;
                $vendido    = $item["vendido"]??0;
                $importe    = $item["importe"]??0;

                if($idDetalle > 0)
                {
                    $nota_detalle = NotaHeladeroDetalle::find($idDetalle);
    
                    if(!empty($nota_detalle)){
    
                        $nota_detalle->devolucion = $devolucion;
                        $nota_detalle->pedido = $pedido;
                        $nota_detalle->codigo = $codigo;
                        $nota_detalle->vendido = $vendido;
                        $nota_detalle->importe = $importe;
                        $nota_detalle->nota_heladeros_id = $nota_heladero->id;
        
                        $nota_detalle->save();
                    }
                    
                    array_push($detalle, $nota_detalle);
                }
                else
                {
                        $nota_detalle = new NotaHeladeroDetalle();

                        $nota_detalle->devolucion = $devolucion;
                        $nota_detalle->pedido = $pedido;
                        $nota_detalle->codigo = $codigo;
                        $nota_detalle->vendido = $vendido;
                        $nota_detalle->importe = $importe;
                        $nota_detalle->nota_heladeros_id = $nota_heladero->id;
        
                        $nota_detalle->save();

                        array_push($detalle, $nota_detalle);
                }
            }
        }

        $nota_heladero["detalle"] = $detalle;

        $array_detalle = [];

        if(count($productos) > 0)
        {
            foreach($productos as $item)
            {
                $devolucion = $item["devolucion"]??0;
                $pedido = $item["pedido"]??0;
                $codigo = $item["codigo"]??0;
                $vendido = $item["vendido"]??0;
                $importe = $item["importe"]??0;
                
                if($estado_id == 2){
                    /* re apertura :  salida */
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $devolucion + $pedido
                    ]);
                    
                }else if($estado_id == 3){
                    /* guardado:  ingreso */
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $devolucion
                    ]);
                }else if($estado_id == 1){
                    /*salida*/
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $vendido
                    ]);
                }
            }
        }

        
        $this->stock->createMovimientoStock("nota", $estado_id, $nota_heladero->id, $heladero_id, $array_detalle, 2, $nota_heladero->codigo);
        
        return $this->response->success($nota_heladero);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\nota_heladero  $nota_heladero
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stock = nota_heladero::find($id);

        if(empty($stock)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $stock->delete();

        return $this->response->success($stock, "El registro fue eliminado correctamente");
    }

    public function findNotaGuardada(Request $request)
    {

        $idusuario = $request->input("idusuario") ?? 0;

        if($idusuario == 0){
            return $this->response->error("No se envio un id valido");
        }

        $nota_heladero = nota_heladero::query()
        ->whereRaw('user_id = ? AND (estado = ? OR estado = ?) ', [$idusuario, 3, 2])                            
        ->orderBy('created_at','desc')
        ->first();
        
        if($nota_heladero)
        {
            $id = $nota_heladero->id;

            /*
            $detalle = NotaHeladeroDetalle::query()
                            ->where("nota_heladeros_id","=", $id)
                            ->orderBy('created_at','desc')
                            ->get();
            */

            $detalle = NotaHeladeroDetalle::query()
                                ->leftJoin('productos',  'productos.codigo', '=', 'nota_heladero_detalle.codigo')
                                ->select(
                                    "nota_heladero_detalle.id",
                                    "nota_heladero_detalle.devolucion",
                                    "nota_heladero_detalle.pedido",
                                    "nota_heladero_detalle.vendido",
                                    "nota_heladero_detalle.importe",
                                    "nota_heladero_detalle.nota_heladeros_id",
                                    "nota_heladero_detalle.created_at",
                                    "nota_heladero_detalle.updated_at",
                                    "nota_heladero_detalle.codigo",
                                    "productos.nombre as producto",
                                    "productos.heladero_precio_venta",
                                    "productos.heladero_descuento"                                    
                                )
                                ->where('nota_heladero_detalle.nota_heladeros_id', $id)
                                ->orderBy('nota_heladero_detalle.created_at','desc')
                                ->get();

            foreach($detalle as $key=>$item){
                $heladero_precio_venta = $item["heladero_precio_venta"] ?? '';
                $heladero_descuento = $item["heladero_descuento"] ?? '';
                $precio_operacion = $heladero_precio_venta - ($heladero_precio_venta * ($heladero_descuento / 100));
                
                $detalle[$key]["precio_operacion"] = $precio_operacion;
            }
                            /*
                            ->where("nota_heladeros_id","=", $id)
                            ->orderBy('created_at','desc')
                            ->get();
                            */


            $nota_heladero["detalle"] = $detalle;

            return $this->response->success($nota_heladero, "El registro fue encontrado");
        }
        else{
            return $this->response->success([], "El registro no fue encontrado");            
        }

    }

    public function listPublicProducts(Request $request)
    {
        $producto = Productos::query()
                    ->where("estados_id", "=", 1)
                    ->orderBy('nombre','asc')
                    ->get();
                    
        return $this->response->success($producto);
    }

    public function reporte(Request $request)
    {
        $documento = $request->input("documento") ?? '';
        $nombre = $request->input("nombre") ?? '';
        $estado = $request->input("estado") ?? 0;
        
        $fecha_inicio = $request->input("fecha_inicio") ?? "";
        $fecha_fin = $request->input("fecha_fin") ?? date("Y-m-d");
        
        $query = nota_heladero::query()
                    ->leftJoin('users as heladero', 'nota_heladeros.user_id', '=', 'heladero.id')
                    ->leftJoin('users as creador', 'nota_heladeros.id_usuario', '=', 'creador.id')
                    ->leftJoin('moneda', 'nota_heladeros.moneda_id', '=', 'moneda.id')
                    ->leftJoin('sucursals', 'nota_heladeros.id_sucursal', '=', 'sucursals.id')
                    ->leftJoin('nota_heladero_estados as lestado', 'nota_heladeros.estado', '=', 'lestado.id')
                    ->select(
                        "nota_heladeros.id",
                        "nota_heladeros.user_id",
                        "nota_heladeros.moneda_id",
                        "nota_heladeros.id_sucursal",
                        "nota_heladeros.monto",
                        "nota_heladeros.pago",
                        "nota_heladeros.debe",
                        "nota_heladeros.ahorro",
                        "nota_heladeros.cucharas",
                        "nota_heladeros.conos",
                        "nota_heladeros.placas_entregas",
                        "nota_heladeros.placas_devueltas",
                        "nota_heladeros.fecha_guardado",
                        "nota_heladeros.fecha_apertura",
                        "nota_heladeros.fecha_cierre",
                        "nota_heladeros.id_usuario",
                        "nota_heladeros.created_at",
                        "nota_heladeros.updated_at",
                        "heladero.documento as heladero_documento",
                        "heladero.name as heladero_nombre",
                        "creador.name",
                        "sucursals.nombre",
                        "lestado.nombre as estado"
                    );

        if (!empty($documento) && $documento !="") {

            $query->where('heladero.documento', $documento);
        }
        
        if (!empty($nombre) && $nombre !="") {
            
            $query->where('heladero.name', 'LIKE', "%$nombre%");
        }        
        
        if(!empty($estado) && $estado != 0){
            $query->where('nota_heladeros.estado', '=', "$estado");
        }

        if((!empty($fecha_inicio) && $fecha_inicio != 0) &&
            (!empty($fecha_fin) && $fecha_fin != 0) ){
            $query->whereBetween('nota_heladeros.created_at', [$fecha_inicio, $fecha_fin]);
        }

        $data = $query->orderBy('nota_heladeros.created_at','desc')->get();

        // dd($data);

        $data = $data->toArray() ?? [];

        function convertDate($fecha = ''){

            if($fecha == '') return '';

            $fecha = str_replace("/", "-", $fecha);
            return date("d-m-Y h:i a", strtotime($fecha));		    
        }

        $n=0;
        foreach($data as $key=>$item)
        {
            $created_at = $item["created_at"]??'';
            $fecha_guardado = $item["fecha_guardado"]??'';
            $fecha_apertura = $item["fecha_apertura"]??'';
            $fecha_cierre = $item["fecha_cierre"]??'';

            $fecha = str_replace("/", "-", $created_at);
            $created_at = date("d-m-Y", strtotime($fecha));		    

            $data[$key]["created_at"] = $created_at;
            $data[$key]["fecha_guardado"] = convertDate($fecha_guardado);
            $data[$key]["fecha_apertura"] = convertDate($fecha_apertura);
            $data[$key]["fecha_cierre"] = convertDate($fecha_cierre);

            unset($created_at , $fecha_guardado, $fecha_apertura, $fecha_cierre);
            $n++;
        }
        
        return response()->json([

            'data' => $data

        ], 200);        
    }
}
