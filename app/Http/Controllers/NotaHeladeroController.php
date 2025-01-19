<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\sistema;
use App\Models\productos;
use App\Models\StockHelados;
use Illuminate\Http\Request;
use App\Models\nota_heladero;
use Illuminate\Support\Facades\DB;
use App\Models\NotaHeladeroDetalle;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\StockHeladosController;
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
                        "nota_heladeros.cargo_baterias",
                        "heladero.documento as heladero_documento",
                        "creador.name",
                        "sucursals.nombre",
                        "lestado.nombre as estado",
                        "nota_heladeros.estado as idestado",
                    )
                    ->addSelect(DB::raw("CONCAT(heladero.name, ' ', heladero.apellidos) as heladero_nombre"))
                    ->addSelect(DB::raw("(SELECT nota_children.id
                                            FROM nota_heladeros as nota_children
                                            WHERE nota_children.parent_id = nota_heladeros.id) as id_children"))
                    ->addSelect(DB::raw("avaibleDelete(nota_heladeros.estado, nota_heladeros.parent_id) as avaibleDelete"))
                    ->addSelect(DB::raw("avaibleDeleteMessage(nota_heladeros.estado, nota_heladeros.parent_id) as avaibleDeleteMessage"));

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

        $deuda_anterior = $request->input("deuda_anterior")??0;
        $cargo_baterias = $request->input("cargo_baterias")??0;
        $monto = $request->input("monto")??0;
        $pago = $request->input("pago")??0;
        $debe = $request->input("debe")??0;
        $ahorro = $request->input("ahorro")??0;
        $yape = $request->input("yape")??0;
        $efectivo = $request->input("efectivo")??0;

        $closeNota = $request->input("closeNota")??false;

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
            $nota_heladero->yape            = $yape;
            $nota_heladero->efectivo        = $efectivo;
            $nota_heladero->deuda_anterior  = $deuda_anterior;
            $nota_heladero->cargo_baterias  = $cargo_baterias;
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
                $vendido_cantidad = $item["vendido_cantidad"]??0;
                $importe = $item["importe"]??0;
                
                $nota_detalle = new NotaHeladeroDetalle();

                $nota_detalle->devolucion = $devolucion;
                $nota_detalle->pedido = $pedido;
                $nota_detalle->codigo = $codigo;
                $nota_detalle->vendido = $vendido;
                $nota_detalle->vendido_cantidad = $vendido_cantidad;
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
                $vendido_cantidad = $item["vendido_cantidad"]??0;
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
                }else if($estado_id == 4){
                    /*salida*/
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $pedido
                    ]);
                }
            }
        }

        $iddoc = $nota_heladero->id;
        $nota_heladero = nota_heladero::find($iddoc);

        
        $numero_documento = str_pad("$iddoc$heladero_id", 12, "0", STR_PAD_LEFT);

        $nota_heladero->codigo = $numero_documento;
        $nota_heladero->save();
        
        if($estado_id  == 2)
        $this->stock->createMovimientoStock("nota", $estado_id, $nota_heladero->id, $heladero_id, $array_detalle, 2, $numero_documento);
        
        // si existe el id parent y esta activa closeNota, se debe cerrar la nota parent
        if($parent_id > 0 && $closeNota === true){
            $nota_heladero_parent = nota_heladero::find($parent_id);

            $sistema = sistema::find(1);

            $cargo_baterias = $sistema->cargo_baterias ?? 0;

            $nota_heladero_parent->cargo_baterias = $cargo_baterias;
            $nota_heladero_parent->debe = $cargo_baterias;
            $nota_heladero_parent->estado = 1;
            $nota_heladero_parent->fecha_cierre = date("Y-m-d H:i:s");
            $nota_heladero_parent->save();
           
        }

        $message = ($parent_id > 0) ? "Las cantidades guardadas han sido registradas" : "El registro fue guardado con exito";
        if($estado_id == 2){
            $message = "El Pedido se ha registrado"; // reapertura
        }

        return $this->show($nota_heladero->id, $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\nota_heladero  $nota_heladero
     * @return \Illuminate\Http\Response
     */
    public function show($id, $message = "El registro fue encontrado")
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
                        "nota_heladeros.deuda_anterior",                        
                        "nota_heladeros.monto",
                        "nota_heladeros.pago",
                        "nota_heladeros.debe",
                        "nota_heladeros.ahorro",
                        "nota_heladeros.cucharas",
                        "nota_heladeros.conos",
                        "nota_heladeros.yape",
                        "nota_heladeros.efectivo",
                        "nota_heladeros.placas_entregas",
                        "nota_heladeros.placas_devueltas",
                        "nota_heladeros.fecha_guardado",
                        "nota_heladeros.fecha_apertura",
                        "nota_heladeros.fecha_cierre",
                        "nota_heladeros.id_usuario",
                        "nota_heladeros.created_at",
                        "nota_heladeros.updated_at",
                        "nota_heladeros.observaciones",
                        "heladero.documento as heladero_documento",                        
                        "creador.name",
                        "sucursals.nombre",
                        "nota_heladeros.estado",
                        "lestado.nombre as estado_nombre",
                        "moneda.moneda"
                    )
                    ->addSelect(DB::raw("CONCAT(heladero.name, ' ', heladero.apellidos) as heladero_nombre"))
                    ->addSelect(DB::raw("(SELECT nota_children.id
                                            FROM nota_heladeros as nota_children
                                            WHERE nota_children.parent_id = nota_heladeros.id) as id_children"))
                    ->addSelect(DB::raw("IF(nota_heladeros.cargo_baterias = 0, (
                        SELECT sistemas.cargo_baterias
                        FROM sistemas as sistemas
                        WHERE id = 1
                    ), nota_heladeros.cargo_baterias) as cargo_baterias"))
                    ->where('nota_heladeros.id', $id)
                    ->first();

        if($nota_heladero)
        {
            $detalle = NotaHeladeroDetalle::query()                                
                                ->leftJoin('productos',  'productos.codigo', '=', 'nota_heladero_detalle.codigo')
                                ->leftJoin('nota_heladeros',  'nota_heladeros.id', '=', 'nota_heladero_detalle.nota_heladeros_id')
                                ->select(
                                    "nota_heladero_detalle.id",
                                    "nota_heladero_detalle.pedido",
                                    "nota_heladero_detalle.devolucion",
                                    "nota_heladero_detalle.vendido_cantidad",
                                    "nota_heladero_detalle.nota_heladeros_id",
                                    "nota_heladero_detalle.created_at",
                                    "nota_heladero_detalle.updated_at",
                                    "nota_heladero_detalle.codigo",
                                    "productos.nombre as producto",
                                    "productos.heladero_precio_venta",
                                    "productos.heladero_descuento",
                                    "productos.is_litro"
                                ) //ROUND(nota_heladero_detalle.vendido, 0)
                                ->addSelect(DB::raw('IF(nota_heladeros.estado = 1, nota_heladero_detalle.importe, "") as importe'))
                                ->addSelect(DB::raw('IF(nota_heladeros.estado = 1, 
                                    IF(productos.is_litro, FORMAT(nota_heladero_detalle.vendido,2), nota_heladero_detalle.vendido)
                                , "") as vendido'))
                                //nota_heladero_detalle.vendido

                                // ->addSelect(DB::raw('
                                //     IF(nota_heladeros.estado = 4, "", (
                                //         IF(nota_heladero_detalle.vendido = 0,
                                //         IF(productos.is_litro, "0.00", "0"),
                                //         IF(productos.is_litro, nota_heladero_detalle.vendido, ROUND(nota_heladero_detalle.vendido, 0)  ))
                                //     ) ) as vendido
                                // ')) 
                                ->addSelect(DB::raw("CheckStock(nota_heladero_detalle.codigo COLLATE utf8mb4_unicode_ci, productos.stock_alerta) as stock_alert_input"))
                                ->addSelect(DB::raw("getStock(nota_heladero_detalle.codigo COLLATE utf8mb4_unicode_ci) as stock"))
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

            return $this->response->success($nota_heladero, $message);
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

        $deuda_anterior = $request->input("deuda_anterior")??0;
        $cargo_baterias = $request->input("cargo_baterias")??0;
        $monto = $request->input("monto")??0;
        $pago = $request->input("pago")??0;
        $debe = $request->input("debe")??0;
        $ahorro = $request->input("ahorro")??0;
        $yape = $request->input("yape")??0;
        $efectivo = $request->input("efectivo")??0;
        $observaciones = $request->input("observaciones")??'';

        $previousEstado = $nota_heladero->estado ?? 0;
        $previousFechaPago = $nota_heladero->fecha_pago ?? null;

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
            if(!empty($nota_heladero->fecha_cierre)){
                $nota_heladero->fecha_pago = date("Y-m-d H:i:s");
            }            
            $nota_heladero->fecha_cierre = $fecha_operacion;
        }

        $nota_heladero->deuda_anterior  = $deuda_anterior;
        $nota_heladero->cargo_baterias  = $cargo_baterias;
        $nota_heladero->monto           = $monto;
        $nota_heladero->pago            = $pago;
        $nota_heladero->debe            = $debe;
        $nota_heladero->ahorro          = $ahorro;
        $nota_heladero->yape            = $yape;
        $nota_heladero->efectivo        = $efectivo;
        $nota_heladero->observaciones   = $observaciones;
        $nota_heladero->cucharas        = 0;
        $nota_heladero->conos           = 0;
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
                $vendido_cantidad = $item["vendido_cantidad"]??0;
                $importe    = $item["importe"]??0;

                if($idDetalle > 0)
                {
                    $nota_detalle = NotaHeladeroDetalle::find($idDetalle);
    
                    if(!empty($nota_detalle)){
    
                        $nota_detalle->devolucion = $devolucion;
                        $nota_detalle->pedido = $pedido;
                        $nota_detalle->codigo = $codigo;
                        $nota_detalle->vendido = $vendido;
                        $nota_detalle->vendido_cantidad = $vendido_cantidad;
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
                        $nota_detalle->vendido_cantidad = $vendido_cantidad;
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
                        "cantidad" => $pedido
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
                }else if($estado_id == 4){
                    /*ingreso*/
                    array_push($array_detalle, [
                        "codigo" => $codigo,
                        "cantidad" => $pedido
                    ]);
                }
            }
        }

        if($estado_id == 2 && $previousEstado == 4){
            $this->stock->createMovimientoStock("nota", $estado_id, $nota_heladero->id, $heladero_id, $array_detalle, 2, $nota_heladero->codigo);
        }else if($estado_id == 2 && $previousEstado == 2){
            $idStock = $this->stock->getIdFromDocumento($nota_heladero->codigo, 4, 2);
            $response = $this->stock->updateMovimientoStock($array_detalle, $idStock);            
        }

        if($estado_id == 3){
            $idStock = $this->stock->getIdFromDocumento($nota_heladero->codigo, 4, 2);
            $this->stock->updateMovimientoStock($array_detalle, $idStock);
        }
        
        $message = "El registro fue actualizado con exito";
        if(($previousEstado == 4 && $estado_id == 2) || $estado_id == 2){
            $message = "El Pedido se ha registrado";                            // reapertura
        }else if(($previousEstado == 2 && $estado_id == 3) || $estado_id == 3 ){
            $message = "Las cantidades guardadas han sido registradas";         // guardado
        }else if($previousEstado == 3 && $estado_id == 1){
            $message = "La cuenta ha sido registrada, ahora registre el pago";  // 1er cierre
        }else if(($previousEstado == 1 && $estado_id == 1) && empty($previousFechaPago) && !empty($nota_heladero->fecha_pago)){
            $message = "El pago ha sido registrado";                            // 2do cierre - pago
        }else if(($previousEstado == 1 && $estado_id == 1) && !empty($previousFechaPago) && !empty($nota_heladero->fecha_pago)){
            $message = "El pago ha sido actualizado";                           // subsiguientes cierres despues del pago
        }

        return $this->show($id, $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\nota_heladero  $nota_heladero
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $nota = nota_heladero::find($id);
        $numero_documento = $nota->codigo ?? null;
        $parent_id = $nota->parent_id ?? null;
        $estado = $nota->estado ?? 0;

        if(empty($nota)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        /* ingresar logica para devolver a almacen cuando la nota no sea cerrada */
        $stockController = new StockHeladosController();
        $stockList = null;

        if(!empty($numero_documento)){
            $stockList = StockHelados::where('numero_documento', $numero_documento)->get();
            if(count($stockList) > 0){
                foreach($stockList as $item){
                    $_id = $item->id ?? null;

                    if(empty($_id)) continue;
                    
                    $stockController->eliminar_stock($_id);
                }
            }
        }   
                
        if(!empty($parent_id)){
            $stock_parent = nota_heladero::find($parent_id);
            if(!empty($stock_parent)){
                $stock_parent->estado = 2;
                $stock_parent->fecha_guardado = null;
                $stock_parent->save();
            }
        }

        /* ingresar logica para devolver a almacen cuando la nota no sea cerrada */
        $nota->delete();

        return $this->response->success($nota, "El registro fue eliminado correctamente");
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
                                ->leftJoin('nota_heladeros',  'nota_heladeros.id', '=', 'nota_heladero_detalle.nota_heladeros_id')
                                ->select(
                                    "nota_heladero_detalle.id",
                                    "nota_heladero_detalle.pedido",
                                    "nota_heladero_detalle.nota_heladeros_id",
                                    "nota_heladero_detalle.created_at",
                                    "nota_heladero_detalle.updated_at",
                                    "nota_heladero_detalle.codigo",
                                    "productos.nombre as producto",
                                    "productos.heladero_precio_venta",
                                    "productos.heladero_descuento",
                                    "productos.is_litro"
                                )
                                //nota_heladero_detalle.vendido_cantidad
                                ->addSelect(DB::raw('
                                    IF(nota_heladeros.estado = 4, "", (
                                        IF(nota_heladero_detalle.vendido_cantidad = 0,
                                        IF(productos.is_litro, "0.00", "0"),
                                        nota_heladero_detalle.vendido_cantidad )
                                    ) ) as vendido_cantidad
                                ')) // nota_heladero_detalle.vendido
                                ->addSelect(DB::raw('
                                    IF(nota_heladeros.estado = 4, "", (
                                        IF(nota_heladero_detalle.vendido = 0,
                                        IF(productos.is_litro, "0.00", "0"),
                                        IF(productos.is_litro, nota_heladero_detalle.vendido, ROUND(nota_heladero_detalle.vendido, 0))
                                        )
                                    ) ) as vendido
                                '))         
                                ->addSelect(DB::raw('
                                    IF(nota_heladeros.estado = 4, "", nota_heladero_detalle.importe ) as importe
                                '))         
                                ->addSelect(DB::raw('
                                    IF(nota_heladero_detalle.devolucion = 0, 
                                    IF(productos.is_litro, "0.00", "0"), 
                                    nota_heladero_detalle.devolucion ) as devolucion
                                '))
                                ->addSelect(DB::raw('(  
                                    SELECT nd.devolucion 
                                    FROM nota_heladero_detalle as nd 
                                    LEFT JOIN nota_heladeros nh ON nh.id = nd.nota_heladeros_id
                                    WHERE nd.codigo = nota_heladero_detalle.codigo AND nh.parent_id = nota_heladero_detalle.nota_heladeros_id
                                    ) as devolucion_today'))
                                ->addSelect(DB::raw("CheckStock(productos.codigo COLLATE utf8mb4_unicode_ci, productos.stock_alerta) as stock_alert_input"))                                
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

    public function listPublicProducts()
    {

        $query_string = "SELECT 
                                prod.id, 
                                prod.codigo, 
                                prod.nombre, 
                                prod.orden, 
                                prod.stock_alerta, 
                                prod.precio_venta, 
                                prod.descuento, 
                                prod.destacado, 
                                prod.estados_id, 
                                prod.unspsc_id, 
                                prod.marcas_id, 
                                prod.unidad_id, 
                                prod.moneda_id, 
                                prod.igv_id, 
                                prod.created_at, 
                                prod.updated_at, 
                                prod.heladero_precio_venta, 
                                prod.heladero_descuento, 
                                prod.cantidad_caja, 
                                prod.proveedor_precio, 
                                prod.is_litro,
                                '' as vendido_cantidad,
                                '' as vendido,
                                '' as importe,
                                IF(prod.is_litro = 1, '0.00', '0') as devolucion,
                                CheckStock(prod.codigo COLLATE utf8mb4_unicode_ci, prod.stock_alerta) as stock_alert_input,
                                getStock(prod.codigo COLLATE utf8mb4_unicode_ci) as stock
                         FROM productos as prod
                         WHERE estados_id = 1 AND prod.is_barquillo = 0
                         ORDER BY codigo ASC";

        $data = DB::select($query_string);
                    
        return $this->response->success($data);
        /*
            0: alerta minima hay stock
            1: alerta maxima no hay stock 
            2: alerta media el stock aun es existente pero en baja cantidad
        */
    }

    public function queryPago($extraString = ""){
        return "(
                    SELECT SUM(nota_heladeros.pago)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $extraString
                )";
    }
    public function queryCuenta($extraString = ""){
        return "(
                    SELECT SUM(nota_heladeros.monto+nota_heladeros.cargo_baterias)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $extraString
                )";
    }
    public function queryAhorro($extraString = ""){
        return "(
                    SELECT SUM(nota_heladeros.ahorro)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $extraString
                )";
    }

    public function reporte(Request $request)
    {
        $user_id = $request->input("user_id") ?? '';
        $estado = $request->input("estado") ?? 0;
        
        $fecha_inicio = $request->input("fecha_inicio") ?? "";
        $fecha_fin = $request->input("fecha_fin") ?? date("Y-m-d");
  

        $queryExtra = $queryExtraAsistencia = $queryExtraUserId = "";
        if (!empty($user_id) && $user_id !="") {            
            $queryExtra.=" AND nota_heladeros.user_id = '$user_id' ";
            $queryExtraUserId.=" AND users.id = '$user_id' ";
        }     
        
        if(!empty($estado) && $estado != 0){
            $queryExtra.=" AND nota_heladeros.estado = '$estado' ";
        }

        $fecha1 = Carbon::parse($fecha_inicio);
        $fecha2 = Carbon::parse($fecha_fin);        

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

        $days = 1;

        if((!empty($fecha_inicio) && $fecha_inicio != 0) &&
            (!empty($fecha_fin) && $fecha_fin != 0) &&
            !$fecha1->eq($fecha2)){

            $fecha2->setTime(23, 59, 59);

            $queryExtra.=" AND $fecha_column BETWEEN '$fecha1' AND '$fecha2' ";
            $queryExtraAsistencia.=" AND asistencias.fecha BETWEEN '$fecha1' AND '$fecha2' ";

            $days = $fecha1->diffInDays($fecha2) + 1;            
        }else{
            $fecha = Carbon::parse($fecha_inicio);

            $queryExtra.=" AND DAY($fecha_column) = $fecha->day 
                           AND MONTH($fecha_column) = $fecha->month 
                           AND YEAR($fecha_column) = $fecha->year 
                        ";

            $queryExtraAsistencia.="AND DAY(asistencias.fecha) = $fecha->day 
                                    AND MONTH(asistencias.fecha) = $fecha->month 
                                    AND YEAR(asistencias.fecha) = $fecha->year ";
        }
        
        $queryAsistencia = "(
            SELECT count(*)
            FROM  asistencia_apertura as asistencias
            WHERE asistencias.user_id = users.id $queryExtraAsistencia
        )";
        //Resumen_a_fecha_fin = Pago_acumulado - Cuenta_acumulada + Ahorro_acumulado

        $pago = $this->queryPago();
        $cuenta = $this->queryCuenta();
        $ahorro = $this->queryAhorro();

        $resumen = "$pago - ($cuenta + $ahorro)";

        $query_string = "
            SELECT
                users.id,
                users.documento as heladero_documento, 
                CONCAT(users.name,' ',users.apellidos )as heladero_nombre,
                (
                    SELECT GROUP_CONCAT(CONCAT('Nota ', codigo,': ', observaciones) SEPARATOR '----')
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as observaciones,
                ".($this->queryPago($queryExtra))." as vendido,
                (
                    SELECT SUM(nota_heladeros.deuda_anterior)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as deuda_pagada,
                (
                    SELECT SUM(nota_heladeros.monto+nota_heladeros.cargo_baterias+nota_heladeros.deuda_anterior)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as total_pagar,
                ".($this->queryCuenta($queryExtra))." as pago,
                (
                    SELECT SUM(nota_heladeros.debe)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as debe,
                (
                    SELECT SUM(nota_heladeros.efectivo)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as efectivo,
                ".($this->queryAhorro($queryExtra))." as ahorro,
                (
                    SELECT SUM(nota_heladeros.monto+nota_heladeros.ahorro-nota_heladeros.pago)
                    FROM nota_heladeros
                    WHERE nota_heladeros.user_id = users.id
                    $queryExtra
                ) as deuda_total,
                (
                    SELECT count(*)
                    FROM  asistencia_apertura as asistencias
                    WHERE asistencias.user_id = users.id $queryExtraAsistencia
                ) as dias_asistidos,
                ROUND( (( $queryAsistencia  * 100) / $days) , 2) as porcentaje_asistencia,
                resumenReporteNota(users.id) as resumen
            FROM users
            WHERE users.usuario_tipo = 7 $queryExtraUserId
        ";
        //return response()->json([ 'query' => $query_string ]);
        $data = DB::select($query_string);

        function convertDate($fecha = ''){

            if($fecha == '') return '';

            $fecha = str_replace("/", "-", $fecha);
            return date("d-m-Y h:i a", strtotime($fecha));		    
        }
        
        return response()->json([

            'data' => $data,          

        ], 200);

    }

    public function saveDateOperation(Request $request, $id) {
        $type = $request->input("estado") ?? '';
        $date = $request->input("fecha_operacion") ?? '';
        $closeNota = $request->input("closeNota") ?? false;
        
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

        if($closeNota == false) return $this->show($id);


    }

    public function resetNotaHeladero(Request $request, $id){

        $state_case = $request->input("state") ?? 0;

        try {
            
            if($state_case == 2){

                $nota_heladero_children = nota_heladero::where('parent_id', $id)->first();
                $estado_children = $nota_heladero_children->estado ?? null;
                $id_children = $nota_heladero_children->id ?? null;
                            
                if(!empty($nota_heladero_children)){
                    if($estado_children == 4){
                        $nota = nota_heladero::find($id_children);
                        $nota->delete();
                    }else{
                        $message = "No se puede reiniciar la nota, porque hay una nota hija ya inicializada";
                        return $this->response->error($message);
                    }
                }
            
                $nota_heladero = nota_heladero::find($id);
    
                if(empty($nota_heladero))
                return $this->response->error("No se envio un id valido");
    
                $nota_heladero->monto           = 0;
                $nota_heladero->pago            = 0;
                $nota_heladero->debe            = 0;
                $nota_heladero->ahorro          = 0;
                $nota_heladero->yape            = 0;
                $nota_heladero->efectivo        = 0;
                $nota_heladero->deuda_anterior  = 0;
                $nota_heladero->cargo_baterias  = 0;
                $nota_heladero->estado          = 4;
                $nota_heladero->observaciones   = null;
                $nota_heladero->fecha_apertura  = null;
                $nota_heladero->fecha_guardado  = null;
                $nota_heladero->fecha_cierre    = null;
                $nota_heladero->fecha_pago      = null;
                $nota_heladero->save();
        
                $notas_heladero_detalle = NotaHeladeroDetalle::where('nota_heladeros_id', $id)->get();
                foreach($notas_heladero_detalle as $item){
                    $idchildren = $item->id ?? null;
    
                    if(empty($idchildren)) continue;
    
                    $nota_heladero_detalle = NotaHeladeroDetalle::find($idchildren);
                    $nota_heladero_detalle->pedido           = 0;
                    $nota_heladero_detalle->vendido          = 0;
                    $nota_heladero_detalle->vendido_cantidad = 0;
                    $nota_heladero_detalle->importe          = 0;
                    $nota_heladero_detalle->save();
                }
    
                $message = "El registro fue reiniciado con exito";
                return $this->response->success([], $message);
            }else if($state_case == 3){
                $nota_heladero = nota_heladero::find($id);
    
                if(empty($nota_heladero))
                return $this->response->error("No se envio un id valido");
    
                $nota_heladero->monto           = 0;
                $nota_heladero->pago            = 0;
                $nota_heladero->debe            = 0;
                $nota_heladero->ahorro          = 0;
                $nota_heladero->yape            = 0;
                $nota_heladero->efectivo        = 0;
                $nota_heladero->deuda_anterior  = 0;
                $nota_heladero->cargo_baterias  = 0;
                $nota_heladero->estado          = 3;
                $nota_heladero->observaciones   = null;
                $nota_heladero->fecha_cierre    = null;
                $nota_heladero->fecha_pago      = null;
                $nota_heladero->save();
        
                $notas_heladero_detalle = NotaHeladeroDetalle::where('nota_heladeros_id', $id)->get();
                foreach($notas_heladero_detalle as $item){
                    $idchildren = $item->id ?? null;
    
                    if(empty($idchildren)) continue;
    
                    $nota_heladero_detalle = NotaHeladeroDetalle::find($idchildren);
                    $nota_heladero_detalle->vendido          = 0;
                    $nota_heladero_detalle->vendido_cantidad = 0;
                    $nota_heladero_detalle->importe          = 0;
                    $nota_heladero_detalle->save();
                }
    
                $message = "El registro fue reiniciado con exito";
                return $this->response->success([], $message);
            }

       
        } catch (\Throwable $th) {
            //throw $th;
            $message = "Hubo un error al reiniciar el registro, ($th)";
            return $this->response->error($message);
        }

    }

    public function searchNotaIncomplete(){

        $documento = request()->input("documento") ?? null;

        $nota_found = nota_heladero::query()
                            ->where("codigo", $documento)
                            ->get();

        $string_documento = (!empty($documento) && count($nota_found) > 0) ? " OR nota_heladeros.codigo = '$documento' " : '';
        

        $nota_heladero = nota_heladero::query()
                        ->whereRaw('estado = ? ', [4])
                        ->whereRaw("(
                                SELECT SUM(nota_heladero_detalle.devolucion)
                                FROM nota_heladero_detalle
                                WHERE nota_heladeros_id = nota_heladeros.id
                            ) > 0 $string_documento")
                        ->orderBy('created_at','desc')
                        ->get();
        
        if(count($nota_heladero) > 0)
        {
            foreach($nota_heladero as $key=>$item){

                $id = $item->id;
    
                $detalle = NotaHeladeroDetalle::query()
                                    ->leftJoin('productos',  'productos.codigo', '=', 'nota_heladero_detalle.codigo')
                                    ->select(
                                        "nota_heladero_detalle.id",
                                        "nota_heladero_detalle.devolucion",
                                        "nota_heladero_detalle.pedido",
                                        "nota_heladero_detalle.vendido",
                                        "nota_heladero_detalle.vendido_cantidad",
                                        "nota_heladero_detalle.importe",
                                        "nota_heladero_detalle.nota_heladeros_id",
                                        "nota_heladero_detalle.created_at",
                                        "nota_heladero_detalle.updated_at",
                                        "nota_heladero_detalle.codigo",
                                        "productos.nombre as producto",
                                        "productos.heladero_precio_venta",
                                        "productos.heladero_descuento",
                                        "productos.is_litro",
                                        "productos.cantidad_caja",
                                    )
                                    ->addSelect(DB::raw('(  
                                        SELECT nd.devolucion 
                                        FROM nota_heladero_detalle as nd 
                                        LEFT JOIN nota_heladeros nh ON nh.id = nd.nota_heladeros_id
                                        WHERE nd.codigo = nota_heladero_detalle.codigo AND nh.parent_id = nota_heladero_detalle.nota_heladeros_id
                                        ) as devolucion_today'))
                                    ->addSelect(DB::raw("CheckStock(productos.codigo COLLATE utf8mb4_unicode_ci, productos.stock_alerta) as stock_alert_input"))
                                    ->where('nota_heladero_detalle.nota_heladeros_id', $id)
                                    ->orderBy('nota_heladero_detalle.codigo','asc')
                                    ->get();
    
                $nota_heladero[$key]["detalle"] = $detalle;
            }
        }

        return response()->json([

            'data' => $nota_heladero

        ], 200);

    }
}
