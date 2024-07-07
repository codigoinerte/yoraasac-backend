<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\productos;
use Illuminate\Http\Request;
use App\Models\nota_heladero;
use Illuminate\Support\Facades\DB;
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
                        "nota_heladeros.codigo",
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
        $parent_id = $request->input("parent_id")??0;

        $monto = $request->input("monto")??0;
        $pago = $request->input("pago")??0;
        $debe = $request->input("debe")??0;
        $ahorro = $request->input("ahorro")??0;

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
        $nota_heladero->parent_id       = $parent_id;

        if($estado_id == 1){
            $nota_heladero->monto           = $monto;
            $nota_heladero->pago            = $pago;
            $nota_heladero->debe            = $debe;
            $nota_heladero->ahorro          = $ahorro;
        }

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
        
        //return $this->response->success($nota_heladero);

        return $this->show($nota_heladero->id);
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
                        "nota_heladeros.codigo",
                        "nota_heladeros.id",
                        "nota_heladeros.parent_id",
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
                                    "productos.heladero_descuento",
                                    "productos.is_litro"
                                )
                                ->addSelect(DB::raw('(  
                                        SELECT nd.devolucion 
                                        FROM nota_heladero_detalle as nd 
                                        LEFT JOIN nota_heladeros nh ON nh.id = nd.nota_heladeros_id
                                        WHERE nd.codigo = nota_heladero_detalle.codigo AND nh.parent_id = nota_heladero_detalle.nota_heladeros_id
                                        ) as devolucion_today'))
                                ->where('nota_heladero_detalle.nota_heladeros_id', $id)
                                ->orderBy('nota_heladero_detalle.codigo','asc')
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
        $parent_id = $request->input("parent_id")??0;

        $monto = $request->input("monto")??0;
        $pago = $request->input("pago")??0;
        $debe = $request->input("debe")??0;
        $ahorro = $request->input("ahorro")??0;

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

        $nota_heladero->monto       = $monto;
        $nota_heladero->pago        = $pago;
        $nota_heladero->debe        = $debe;
        $nota_heladero->ahorro      = $ahorro;
        $nota_heladero->cucharas    = 0;
        $nota_heladero->conos       = 0;
        if($estado_id == 3){
            $nota_heladero->parent_id   = $parent_id;
        }

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
        
        return $this->show($id);
        //return $this->response->success($nota_heladero);
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
        ->whereRaw('user_id = ? AND (estado = ? OR estado = ? OR estado = ?) ', [$idusuario, 3, 2 , 4])
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
                                    "productos.heladero_descuento",
                                    "productos.is_litro"
                                )
                                ->addSelect(DB::raw('(  
                                    SELECT nd.devolucion 
                                    FROM nota_heladero_detalle as nd 
                                    LEFT JOIN nota_heladeros nh ON nh.id = nd.nota_heladeros_id
                                    WHERE nd.codigo = nota_heladero_detalle.codigo AND nh.parent_id = nota_heladero_detalle.nota_heladeros_id
                                    ) as devolucion_today'))
                                ->where('nota_heladero_detalle.nota_heladeros_id', $id)
                                ->orderBy('nota_heladero_detalle.codigo','asc')
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
                    ->orderBy('codigo','asc')
                    ->get();
                    
        return $this->response->success($producto);
    }

    public function reporte(Request $request)
    {
        $user_id = $request->input("user_id") ?? '';
        $estado = $request->input("estado") ?? 0;
        
        $fecha_inicio = $request->input("fecha_inicio") ?? "";
        $fecha_fin = $request->input("fecha_fin") ?? date("Y-m-d");
  

        $queryExtra = "";
        if (!empty($user_id) && $user_id !="") {            
            $queryExtra.=" AND nota_heladeros.user_id = '$user_id' ";
        }     
        
        if(!empty($estado) && $estado != 0){
            $queryExtra.=" AND nota_heladeros.estado = '$estado' ";
        }

        $fecha1 = Carbon::parse($fecha_inicio);
        $fecha2 = Carbon::parse($fecha_fin);
        $fecha2->setTime(23, 59, 59);

        switch ($estado) {
            case 1: //cierre
                $fecha_column = "nota_heladeros.fecha_cierre";
            break;
            
            case 2: //reapertura
                $fecha_column = "nota_heladeros.apertura";
            break;
            
            case 3: //guardado
                $fecha_column = "nota_heladeros.guardado";
            break;
            
            default:
                $fecha_column = "nota_heladeros.created_at";
            break;
        }

        if((!empty($fecha_inicio) && $fecha_inicio != 0) &&
            (!empty($fecha_fin) && $fecha_fin != 0) &&
            !$fecha1->eq($fecha2)){
            $queryExtra.=" AND $fecha_column BETWEEN '$fecha1' AND '$fecha2' ";
        }else{
            $fecha = Carbon::parse($fecha_inicio);

            $queryExtra.=" AND DAY($fecha_column) = $fecha->day 
                           AND MONTH($fecha_column) = $fecha->month 
                           AND YEAR($fecha_column) = $fecha->year 
                        ";
        }
        
        $query_string = "
            SELECT
                users.id,
                users.documento as heladero_documento, 
                CONCAT(users.name,' ',users.apellidos )as heladero_nombre,
                (
                    SELECT SUM(nota_heladeros.monto)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as monto,
                (
                    SELECT SUM(nota_heladeros.pago)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as pago,
                (
                    SELECT SUM(nota_heladeros.debe)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as debe,
                (
                    SELECT SUM(nota_heladeros.ahorro)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as ahorro,
                (
                    SELECT SUM(nota_heladeros.monto+nota_heladeros.ahorro-nota_heladeros.pago)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as deuda_total
            FROM users
            WHERE users.usuario_tipo = 7
        ";

        $data = DB::select($query_string);

        function convertDate($fecha = ''){

            if($fecha == '') return '';

            $fecha = str_replace("/", "-", $fecha);
            return date("d-m-Y h:i a", strtotime($fecha));		    
        }
        
        return response()->json([

            'data' => $data

        ], 200);

    }

    public function saveDateOperation(Request $request, $id) {
        $type = $request->input("estado") ?? '';
        $date = $request->input("fecha_operacion") ?? '';
        
        if(empty($id))
            return $this->response->error("No se envio un id valido");
        
        $nota_heladero = nota_heladero::find($id);

        if(empty($nota_heladero))
            return $this->response->error("No se envio un id valido");
        
        
        if($type == 2){ //re apertura        
            $nota_heladero->fecha_apertura = $date;
        }
        else if($type == 3){ // guardado
            $nota_heladero->fecha_guardado = $date;
            $nota_heladero->estado = 3;
        }
        else if($type == 1){ // cierre
            $nota_heladero->fecha_cierre = $date;
        }

        $nota_heladero->save();

        return $this->show($id);
    }
}
