<?php

namespace App\Http\Controllers;

use App\Models\factura;
use Illuminate\Http\Request;
use App\Models\FacturaDetalle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\FacturaRequest;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\StockHeladosController;
use App\Http\Controllers\SucursalesDocumentosSerieController;

class FacturaController extends Controller
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

        $documento = $request->input('documento') ?? '';
        $nombres = $request->input('nombre') ?? '';
        $fecha = $request->input('fecha') ?? '';

        $query = Factura::query()
                    ->leftJoin('users as usuario', 'facturas.user_id', '=', 'usuario.id')
                    ->leftJoin('moneda', 'facturas.id_moneda', '=', 'moneda.id')
                    ->leftJoin('factura_estados as festado', 'facturas.id_estado', '=', 'festado.id')
                    ->leftJoin('tipo_documentos as nomdoc', 'facturas.tipo', '=', 'nomdoc.id')                 
                    ->selectRaw('
                        
                            facturas.id,
                            facturas.codigo,
                            facturas.serie,
                            facturas.correlativo,
                            facturas.user_id,
                            facturas.tipo,
                            facturas.fecha_pago,
                            facturas.id_usuario,
                            facturas.created_at,
                            facturas.updated_at,
                            facturas.sucursals_id,
                            facturas.fecha_emision,
                            facturas.tipo_transaccion,
                            facturas.id_estado,
                            facturas.id_moneda,
                            facturas.subtotal,
                            facturas.descuento,
                            facturas.igv,
                            facturas.total,
                            usuario.documento as usuario_documento,
                            usuario.name as usuario_nombre,
                            festado.estado as estado,
                            nomdoc.documento as documento,
                            moneda.moneda as moneda                          
                        ');
                    //->selectRaw('SUM(factura_detalle.cantidad * factura_detalle.precio * (1 - (factura_detalle.descuento/100))) as total');
                    
            
        if (!empty($documento) && $documento !="") {
            
            $query->whereRaw("CONCAT(facturas.serie,'-',facturas.correlativo) = ? ", [$documento]);
        }

        if (!empty($nombres) && $nombres !="") {
            
            $query->where('usuario.name', 'LIKE', "%$nombres%");
        }                
        
        if (!empty($fecha) && $fecha !="") {
            $query->whereDate('facturas.created_at', $fecha);
        }
        
        $users = $query
                    ->orderBy('facturas.created_at','desc')
                    ->paginate(10, ['*'], 'page', $page);

        $nextPageUrl = $users->nextPageUrl();
        $previousPageUrl = $users->previousPageUrl();

        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        $data = $users->toArray()["data"] ?? [];

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
    public function store(FacturaRequest $request)
    {
        
        $tipo = $request->input("tipo") ?? 0;        
        $tipo_transaccion = $request->input("tipo_transaccion") ?? 0;        
        $cliente = $request->input("user_id") ?? 0;        
        $id_estado = $request->input("estado") ?? 0;

        $precio_tipo = $request->input("precio_tipo") ?? 0;
        
        $fecha_emision = $request->input("fecha_emision") ?? '';
        $fecha_pago = $request->input("fecha_pago") ?? '';

        $subtotal = $request->input("subtotal") ?? 0;
        $descuento = $request->input("descuento") ?? 0;
        $igv = $request->input("igv") ?? 0;
        $total = $request->input("total") ?? 0;

        $productos = $request->input("productos")??[];

        $monedaController = new MonedaController();        
        $moneda_id = $monedaController->getMonedaPrincipal()->id ?? 1;

        $auth = Auth::user();
        $sucursal_id = $auth->sucursals_id ?? 1;
        $user_creador = $auth->id;
        
        $sucursales_series = new SucursalesDocumentosSerieController();
        $respuesta = $sucursales_series->generate_next_document($tipo,$sucursal_id);
        
        $serie = $respuesta["serie"]??'';
        $correlativo = $respuesta["correlativo"]??'';

        $factura = new Factura();

        $factura->serie           = $serie;
        $factura->correlativo     = $correlativo;
        $factura->user_id         = $cliente;
        $factura->tipo            = $tipo;
        $factura->fecha_pago      = $fecha_pago;
        $factura->id_usuario      = $user_creador;
        $factura->sucursals_id    = $sucursal_id;
        $factura->fecha_emision   = $fecha_emision;
        $factura->tipo_transaccion= $tipo_transaccion;
        $factura->id_estado       = $id_estado;
        $factura->precio_tipo       = $precio_tipo;
        $factura->id_moneda       = $moneda_id;
        
        $factura->subtotal        = $subtotal;
        $factura->descuento       = $descuento;
        $factura->igv             = $igv;
        $factura->total           = $total;


        
        $factura->save();

        $array_salida = [];
        $array_ingreso = [];

        $detalle = [];

        if(count($productos) > 0)
        {
            foreach($productos as $item)
            {
                $codigo = $item["codigo"]??"";
                $precio = $item["precio"]??0;
                $descuento = $item["descuento"]??0;
                $cantidad = $item["cantidad"]??0;
                
                $factura_detalle = new FacturaDetalle();

                $factura_detalle->codigo = $codigo;
                $factura_detalle->precio = $precio;
                $factura_detalle->descuento = $descuento;
                $factura_detalle->cantidad = $cantidad;
                $factura_detalle->facturas_id = $factura->id;
                $factura_detalle->save();

                unset($factura_detalle->facturas_id);
                unset($factura_detalle->updated_at);
                unset($factura_detalle->created_at);

                array_push($detalle, $factura_detalle);
                
                array_push($array_salida, [
                    "codigo" => $codigo,
                    "cantidad" => $cantidad
                ]);
            }
        }

        $factura["detalle"] = $detalle;

        $stock_movimiento_tipo = $tipo == 1 ? 'boleta_venta' : 'factura_venta';
        $stock_estado = 0;
        $stock_id = 0;
        $stock_movimiento = 0;
        $heladero_id = $user_creador;
        $numero_documento = $serie."-".$correlativo;

        $this->stock->createMovimientoStock($stock_movimiento_tipo, $stock_estado, $stock_id, $heladero_id, $array_salida, $stock_movimiento, $numero_documento);

        $response = $this->getFacturaQuery($factura->id);
        
        return $this->response->success($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->getFacturaQuery($id);
        if(empty($response)){
            return $this->response->error("El registro no fue encontrado");
        }else{
            return $this->response->success($response, "El registro fue encontrado");
        }

        /*
        $nota_heladero = Factura::find($id);

        if($nota_heladero)
        {
            $detalle = FacturaDetalle::query()                                
                                ->leftJoin('productos',  'productos.codigo', '=', 'factura_detalle.codigo')
                                ->select(
                                    "factura_detalle.id",
                                    "factura_detalle.codigo",
                                    "factura_detalle.precio",
                                    "factura_detalle.descuento",
                                    "factura_detalle.cantidad",
                                    "factura_detalle.facturas_id",
                                    "factura_detalle.created_at",
                                    "factura_detalle.updated_at",
                                    
                                    "productos.nombre as producto"
                                )
                                ->where('factura_detalle.facturas_id', $id)
                                ->orderBy('factura_detalle.created_at','desc')
                                ->get();
            
            $nota_heladero["detalle"] = $detalle;

            return $this->response->success($nota_heladero, "El registro fue encontrado");
        }
        else{
            return $this->response->error("El registro no fue encontrado");
        }
        */
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function edit(factura $factura)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function update(FacturaRequest $request, $id)
    {
        $factura = Factura::find($id);

        if(empty($factura)){
            return $this->response->error("No se envio un id valido");
        }

        $tipo = $request->input("tipo") ?? 0;        
        $tipo_transaccion = $request->input("tipo_transaccion") ?? 0;        
        $cliente = $request->input("user_id") ?? 0;        
        $id_estado = $request->input("estado") ?? 0;
        $precio_tipo = $request->input("precio_tipo") ?? 0;
        
        $fecha_emision = $request->input("fecha_emision") ?? '';
        $fecha_pago = $request->input("fecha_pago") ?? '';
        $productos = $request->input("productos")??[];
        
        $subtotal = $request->input("subtotal") ?? 0;
        $descuento = $request->input("descuento") ?? 0;
        $igv = $request->input("igv") ?? 0;
        $total = $request->input("total") ?? 0;

        $factura->user_id         = $cliente;
        #$factura->tipo            = $tipo;
        $factura->fecha_pago      = $fecha_pago;
        $factura->fecha_emision   = $fecha_emision;
        $factura->tipo_transaccion= $tipo_transaccion;
        $factura->id_estado       = $id_estado;
        $factura->precio_tipo       = $precio_tipo;

        $factura->subtotal        = $subtotal;
        $factura->descuento       = $descuento;
        $factura->igv             = $igv;
        $factura->total           = $total;

        $factura->save();

        $detalle = [];

        $array_salida = [];
        $array_ingreso = [];

        $factura_actuales = FacturaDetalle::query()
                            ->where("facturas_id", "=", $id)
                            ->get()
                            ->toArray();

        $array_id_eliminar = [];

        if(count($factura_actuales) > 0)
        {
            
            foreach($productos as $ritem){
                $rid = $ritem["id"]??'';

                foreach($factura_actuales as $lkey => $litem){
                    $lid = $litem["id"]??'';

                    if($rid == $lid && $lid!='' && $rid !=''){
                        
                        unset($factura_actuales[$lkey]);

                    }

                }

            }
            
            if(count($factura_actuales)){

                foreach($factura_actuales as $eitem){
                    $id = $eitem??0;
                    if($id !=0){

                        $factura_detalle_find = FacturaDetalle::find($id)->first();
                        
                        $_codigo = $factura_detalle_find->codigo;
                        $_cantidad = $factura_detalle_find->cantidad;

                        array_push($array_ingreso, [
                            "codigo" => $_codigo,
                            "cantidad" => $_cantidad
                        ]);

                        unset($_codigo);
                        unset($_cantidad);

                        $factura_detalle_find->delete();                        
                    }
                }

            }
        }

        if(count($productos) > 0)
        {
            foreach($productos as $item)
            {
                $id = $item["id"]??0;
                $codigo = $item["codigo"]??"";
                $precio = $item["precio"]??0;
                $descuento = $item["descuento"]??0;
                $cantidad = $item["cantidad"]??0;
                
                $factura_detalle = FacturaDetalle::find($id);

                if($factura_detalle){

                    $factura_detalle->codigo = $codigo;
                    $factura_detalle->precio = $precio;
                    $factura_detalle->descuento = $descuento;
                    $factura_detalle->cantidad = $cantidad;

                    array_push($array_salida, [
                        "codigo" => $codigo,
                        "cantidad" => $cantidad
                    ]);
                    
                    $factura_detalle->save();

                    unset($factura_detalle->facturas_id);
                    unset($factura_detalle->updated_at);
                    unset($factura_detalle->created_at);
    
                    array_push($detalle, $factura_detalle);                
                }
                else if($id == 0 && $codigo !="")
                {

                    $factura_detalle = new FacturaDetalle();

                    $factura_detalle->codigo = $codigo;
                    $factura_detalle->precio = $precio;
                    $factura_detalle->descuento = $descuento;
                    $factura_detalle->cantidad = $cantidad;
                    $factura_detalle->facturas_id = $factura->id;

                    array_push($array_salida, [
                        "codigo" => $codigo,
                        "cantidad" => $cantidad
                    ]);

                    $factura_detalle->save();

                    unset($factura_detalle->facturas_id);
                    unset($factura_detalle->updated_at);
                    unset($factura_detalle->created_at);

                    array_push($detalle, $factura_detalle); 
                }

            }
        }

        $factura["detalle"] = $detalle;

        $numero_documento = $factura->serie."-".$factura->correlativo;
        
        $this->stock->updateMovimientoStockFactura($array_salida, $array_ingreso, $numero_documento);

        $response = $this->getFacturaQuery($factura->id);
        
        return $this->response->success($response);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $factura = Factura::find($id);

        if(empty($factura)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $this->stock->eliminarStockByNumeroDocumento($factura->serie."-".$factura->correlativo);
        
        $factura->delete();

        return $this->response->success($factura, "El registro fue eliminado correctamente");
    }

    public function getSeriesDoc() {
        
    }

    public function reporte(Request $request){
     
        $documento = $request->input("documento") ?? '';
        $nombre = $request->input("nombre") ?? '';
        $estado = $request->input("estado") ?? 0;
        $tipo = $request->input("tipo") ?? 0;
        
        $fecha_inicio = $request->input("fecha_inicio") ?? "";
        $fecha_fin = $request->input("fecha_fin") ?? date("Y-m-d");
        
        $query = Factura::query()
                ->leftJoin('factura_detalle', 'facturas.id', '=', 'factura_detalle.facturas_id')
                ->leftJoin('users as usuario', 'facturas.user_id', '=', 'usuario.id')
                ->leftJoin('moneda', 'facturas.id_moneda', '=', 'moneda.id')
                ->leftJoin('factura_estados as festado', 'facturas.id_estado', '=', 'festado.id')
                ->leftJoin('tipo_documentos as nomdoc', 'facturas.tipo', '=', 'nomdoc.id')                    
                ->select(

                    "facturas.id",
                    "facturas.codigo",
                    "facturas.serie",
                    "facturas.correlativo",
                    "facturas.user_id",
                    "facturas.tipo",
                    "facturas.fecha_pago",
                    "facturas.id_usuario",
                    "facturas.created_at",
                    "facturas.updated_at",
                    "facturas.sucursals_id",
                    "facturas.fecha_emision",
                    "facturas.tipo_transaccion",
                    "facturas.id_estado",
                    "facturas.id_moneda",
                    
                    "usuario.documento as usuario_documento",
                    "usuario.name as usuario_nombre",
                    "festado.estado as estado",
                    "nomdoc.documento as documento",
                    "moneda.moneda as moneda"
                )                    
                ->selectRaw('SUM(factura_detalle.cantidad * factura_detalle.precio * (1 - (factura_detalle.descuento/100))) as total');

        if (!empty($tipo) && $tipo !="") {
    
            $query->where('facturas.tipo', '=', "$tipo");
        }

        if (!empty($documento) && $documento !="") {
    
            $query->whereRaw("CONCAT(facturas.serie,'-',facturas.correlativo) = ? ", [$documento]);
        }

        if (!empty($nombres) && $nombres !="") {
            
            $query->where('usuario.name', 'LIKE', "%$nombres%");
        }                
        
        if(!empty($estado) && $estado != 0){
            $query->where('facturas.id_estado', '=', "$estado");
        }

        if((!empty($fecha_inicio) && $fecha_inicio != 0) &&
            (!empty($fecha_fin) && $fecha_fin != 0) ){

                if($fecha_inicio == $fecha_fin){
                    $query->whereDate('facturas.created_at', $fecha_inicio);
                }else{
                    $query->whereBetween('facturas.created_at', [$fecha_inicio, $fecha_fin]);
                }
        }else if(!empty($fecha_inicio) && empty($fecha_fin)){
            $query->whereDate('facturas.created_at', $fecha_inicio);
        }else if(empty($fecha_inicio) && !empty($fecha_fin)){
            $query->whereDate('facturas.created_at', $fecha_fin);
        }

        $data = $query
                    ->orderBy('facturas.created_at','desc')
                    ->groupBy('facturas.id')
                    ->get();

        // dd($data);

        $data = $data->toArray() ?? [];

        function convertDate($fecha = ''){

            if($fecha == '') return '';

            $fecha = str_replace("/", "-", $fecha);
            return date("d-m-Y h:i a", strtotime($fecha));		    
        }
        
        foreach($data as $key=>$item)
        {
            $fecha_pago = $item["fecha_pago"]??'';
            $created_at = $item["created_at"]??'';
            $updated_at = $item["updated_at"]??'';

            $fecha_pago = str_replace("/", "-", $fecha_pago);
            $fecha_pago = date("d-m-Y", strtotime($fecha_pago));		    		    

            $data[$key]["created_at"] = convertDate($created_at);
            $data[$key]["updated_at"] = convertDate($updated_at);
            $data[$key]["fecha_pago"] = $fecha_pago;

            unset($fecha_pago , $created_at, $updated_at);            
        }
        
        return response()->json([

            'data' => $data

        ], 200);        
    }

    public function getFacturaQuery ($id) {

        if(empty($id)) return null;

        $factura = Factura::query()
                    ->leftJoin('users as cliente', 'facturas.user_id', '=', 'cliente.id')
                    ->leftJoin('users as creador', 'facturas.id_usuario', '=', 'creador.id')
                    ->leftJoin('moneda', 'facturas.id_moneda', '=', 'moneda.id')
                    ->leftJoin('sucursals', 'facturas.sucursals_id', '=', 'sucursals.id')
                    ->leftJoin('factura_estados as estados', 'facturas.id_estado', '=', 'estados.id')
                    ->leftJoin('tipo_documentos', 'tipo_documentos.id', '=', 'facturas.tipo')
                    ->select(
                        
                        "facturas.id",
                        "facturas.codigo",
                        "facturas.serie",
                        "facturas.correlativo",
                        "facturas.user_id",
                        "facturas.tipo",
                        "facturas.fecha_pago",
                        "facturas.id_usuario",
                        "facturas.created_at",
                        "facturas.updated_at",
                        "facturas.sucursals_id",
                        "facturas.fecha_emision",
                        "facturas.tipo_transaccion",
                        "facturas.id_estado",
                        "facturas.id_moneda",
                        "facturas.subtotal",
                        "facturas.descuento",
                        "facturas.igv",
                        "facturas.total",
                        "facturas.precio_tipo",
                        "estados.estado",
                        "tipo_documentos.documento as documento_tipo",

                        "cliente.documento as usuario_documento",

                        "creador.name as creador",
                        "sucursals.nombre as sucursal",
                        
                        "moneda.moneda"
                    )
                    ->addSelect(DB::raw("getFacturaTransaction(facturas.tipo_transaccion) as transaccion"))
                    ->addSelect(DB::raw("CONCAT(cliente.name, ' ', cliente.apellidos) as usuario_nombre"))
                    ->addSelect(DB::raw("CONCAT(facturas.codigo, '-', facturas.serie) as documento"))
                    ->addSelect(DB::raw("getFacturaMonto(facturas.id) as total_monto"))
                    ->addSelect(DB::raw("getFacturaTotalDescuento(facturas.id) as total_descuento"))                    
                    ->addSelect(DB::raw("getFacturaTotal(facturas.id) as total"))
                    ->where('facturas.id', $id)
                    ->first();
                    //! FALTA EL WHERE

        if(empty($factura)) return null;

        $detalle = FacturaDetalle::query()                                
                    ->leftJoin('productos',  'productos.codigo', '=', 'factura_detalle.codigo')
                    ->select(
                        "factura_detalle.id",
                        "factura_detalle.codigo",
                        "factura_detalle.precio",
                        "factura_detalle.descuento",
                        "factura_detalle.cantidad",
                        "factura_detalle.facturas_id",
                        "factura_detalle.created_at",
                        "factura_detalle.updated_at",                        
                        "productos.nombre as producto"
                    )
                    ->addSelect(DB::raw("((factura_detalle.precio * (1 - (factura_detalle.descuento/100))) * factura_detalle.cantidad) as total"))
                    ->where('factura_detalle.facturas_id', $id)
                    ->orderBy('factura_detalle.created_at','desc')
        // ->toSql();
                    ->get();
        
        $factura["detalle"] = $detalle;

        return $factura;
    }
}
