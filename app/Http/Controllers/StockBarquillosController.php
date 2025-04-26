<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBarquillos;
use Illuminate\Support\Facades\Auth;
use App\Models\StockBarquillosDetail;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\StockBarquillos as StockBarquillosRequest;

class StockBarquillosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->response = new ResponseController();
    }
    
    public function index(Request $request)
    {
        $page = $request->input('page') ?? 1;
        $codigo = $request->input('codigo') ?? '';
        $movimiento = $request->input('movimiento') ?? '';
        $fecha = $request->input('fecha') ?? '';

        $query = StockBarquillos::query()
                    ->leftJoin('movimientos', 'stock_barquillos.movimientos_id', '=', 'movimientos.id')
                    ->leftJoin('tipo_documentos', 'stock_barquillos.tipo_documento_id', '=', 'tipo_documentos.id')
                    ->select(
                        "stock_barquillos.id",
                        "stock_barquillos.codigo_movimiento",
                        "stock_barquillos.numero_documento",
                        "stock_barquillos.fecha_movimiento",                        
                        "movimientos.movimiento",
                        "tipo_documentos.documento",
                        "stock_barquillos.created_at",
                        "stock_barquillos.updated_at",
                    );

        if (!empty($codigo) && $codigo !="") {

            $query->where('stock_barquillos.codigo_movimiento', $codigo);
        }
        
        if (!empty($movimiento) && $movimiento !="") {
            
            $query->where('movimientos.movimiento', 'LIKE', "%$movimiento%");
        }        
        
        if (!empty($fecha) && $fecha !="") {
            $query->whereDate('stock_barquillos.created_at', $fecha);
        }

                
        $stock_barquillos = $query->orderBy('stock_barquillos.created_at','desc')->paginate(10, ['*'], 'page', $page);

        $nextPageUrl = $stock_barquillos->nextPageUrl();
        $previousPageUrl = $stock_barquillos->previousPageUrl();
            
        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        $data = $stock_barquillos->toArray()["data"] ?? [];

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
    public function create(StockBarquillosRequest $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StockBarquillosRequest $request)
    {
        $codigo = '';
        $unidades = $request->input("unidades") ?? 0;
        $movimiento_id = $request->input("movimientos_id") ?? 0;
        $tipo_documento_id = $request->input("tipo_documento_id") ?? 0;
        $fecha_movimiento = $request->input("fecha_movimiento") ?? '';
        $numero_documento = $request->input("numero_documento") ?? '';
        $image_file = $request->input("image_file") ?? '';

        $array_detalle = $request->input("detalle")??[];

        $auth = Auth::user();
        $user_creador = $auth->id;

        $codigo = '';

        $stock = new StockBarquillos();

        $stock->unidades = $unidades;
        $stock->codigo_movimiento = $codigo;
        $stock->movimientos_id = $movimiento_id;
        $stock->tipo_documento_id = $tipo_documento_id;
        $stock->numero_documento = $numero_documento;
        $stock->fecha_movimiento = $fecha_movimiento;  
        $stock->imagen = $image_file;
        $stock->user_id = $user_creador;
        
        $stock->save();

        $idStock = $stock->id;

        /* añadir codigo */
        $codigo = str_pad($idStock, 7, "0", STR_PAD_LEFT);        
        $stock->codigo_movimiento = "stq-".$codigo;

        $stock->save();     
        /* añadir codigo */

        if(count($array_detalle) > 0)
        {
            foreach($array_detalle as $item)
            {
                $codigo = $item["codigo"]??'';                
                $cantidad = $item["cantidad"]??0;
                $caja = $item["caja"]??0;
                $caja_cantidad = $item["caja_cantidad"]??0;
                $id_importado = $item["id_importado"]??0;
                $is_litro = $item["is_litro"]??0;
                $min_cantidad = $item["min_cantidad"]??0;

                $newDetail = new StockBarquillosDetail();

                $newDetail->codigo = $codigo;
                $newDetail->stock_barquillos_id = $idStock;
                $newDetail->cantidad = $cantidad;
                $newDetail->caja = $caja;
                $newDetail->caja_cantidad = $caja_cantidad;
                if($is_litro == true)
                    $newDetail->cant_litro_devuelta = $min_cantidad;

                $newDetail->save();

                /* eliminar cantidad de stock detalle */
                if($id_importado > 0){
                    $nota_detalle = StockBarquillosDetail::find($id_importado);
                    if(!empty($nota_detalle)){
                        $nota_detalle->devolucion = ($is_litro == true) ? ($nota_detalle->devolucion - $min_cantidad) : ($nota_detalle->devolucion - $cantidad);
                        $nota_detalle->save();
                    }
                }

            }
        }

        $stock = $this->getDataDetailFromId($idStock);

        return $this->response->success($stock, "El stock fue creado con exito");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockBarquillos  $stockBarquillos
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stock = $this->getDataDetailFromId($id);
        
        if($stock){
    
            return $this->response->success($stock, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockBarquillos  $stockBarquillos
     * @return \Illuminate\Http\Response
     */
    public function edit(StockBarquillos $stockBarquillos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockBarquillos  $stockBarquillos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $stock = StockBarquillos::find($id);

        if(empty($stock)){
            return $this->response->error("No se envio un id valido");
        }

        $idStock = $id;
        $unidades = $request->input("unidades") ?? 0;
        $movimiento_id = $request->input("movimientos_id") ?? 0;
        $tipo_documento_id = $request->input("tipo_documento_id") ?? 0;
        $fecha_movimiento = $request->input("fecha_movimiento") ?? '';
        $numero_documento = $request->input("numero_documento") ?? '';
        $image_file = $request->input("image_file") ?? '';

        $array_detalle = $request->input("detalle")??[];

        $unidades_originales = $stock->unidades;
        $stock->unidades = $unidades;
        $stock->movimientos_id = $movimiento_id;
        $stock->tipo_documento_id = $tipo_documento_id;
        $stock->numero_documento = $numero_documento;
        $stock->fecha_movimiento = $fecha_movimiento;
        $stock->imagen = $image_file;
        
        $stock->save();   

        if(count($array_detalle) > 0)
        {
            foreach($array_detalle as $item)
            {
                $idDetail = $item["id"]??null;
                $codigo = $item["codigo"]??'';                
                $cantidad = $item["cantidad"]??0;
                $caja = $item["caja"]??0;
                $caja_cantidad = $item["caja_cantidad"]??0;
                $min_cantidad = $item["min_cantidad"]??0;
                $id_importado = $item["id_importado"]??0;
                $is_litro = $item["is_litro"]??0;


                if(empty($idDetail))
                {
                    //si hay algun producto nuevo
                    $newDetail = new StockBarquillosDetail();

                    $newDetail->codigo = $codigo;
                    $newDetail->stock_barquillos_id = $idStock;
                    $newDetail->cantidad = $cantidad;
                    $newDetail->caja = $caja;
                    $newDetail->caja_cantidad = $caja_cantidad;
                    if($is_litro == true)
                        $newDetail->cant_litro_devuelta = $min_cantidad;

                    $newDetail->save();

                    /* eliminar cantidad de stock detalle */
                    if($id_importado > 0){
                        $nota_detalle = NotaHeladeroDetalle::find($id_importado);
                        if(!empty($nota_detalle)){
                            $nota_detalle->devolucion = ($is_litro == true) ? ($nota_detalle->devolucion - $min_cantidad) : ($nota_detalle->devolucion - $cantidad);
                            $nota_detalle->save();
                        }
                    }

                }
                else
                {
                    //si el producto ya existia
                    $newDetail = StockBarquillosDetail::find($idDetail);
                    
                    $newDetail->codigo = $codigo;
                    $newDetail->stock_barquillos_id = $id;
                    $newDetail->cantidad = $cantidad;
                    $newDetail->caja_cantidad = $caja_cantidad;
                    if($unidades_originales == 2 && $unidades == 1){
                        $newDetail->caja = 0;
                    }else{
                        $newDetail->caja = $caja;
                    }
    
                    $newDetail->save();
                }
            }
        }

        $stock = $this->getDataDetailFromId($id);     

        return $this->response->success($stock);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockBarquillos  $stockBarquillos
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->eliminar_stock($id);
    }

    public function eliminar_stock($id){
        $stock = StockBarquillos::find($id);      
        if(empty($stock)){
            return $this->response->error("No se envio un id valido");
        }

        $tipo_documento_id = $stock->tipo_documento_id;

        /* detectar si hay un proceso de devolucion, regresarlo a la cuenta orignal si se elimina */
        if($tipo_documento_id == 5){
            $documento = $stock->numero_documento ?? null;
            if(!empty($documento)){
                $idNota = nota_heladero::where("codigo", $documento)->value("id") ?? null;
                if(!empty($idNota)){
                    $stockDetalle = StockHeladosDetail::where("stock_barquillos_id", $id)->get();
                    if(count($stockDetalle) > 0){
                        foreach($stockDetalle as $item) {
                            
                            $codigo_helado = $item->codigo??null;
                            $cantidad = $item->cantidad??0;
                            $min_cantidad = $item->cant_litro_devuelta??0;

                            if(!empty($codigo_helado)){

                                $nota_detalle = NotaHeladeroDetalle::where("codigo", $codigo_helado)
                                                        ->where("nota_heladeros_id", $idNota)
                                                        ->first();
                                if(!empty($nota_detalle)){
                                    $nota_detalle->devolucion = $min_cantidad > 0 ? $min_cantidad : ($nota_detalle->devolucion + $cantidad);
                                    $nota_detalle->save();
                                }

                            }
                        }
                    }
                }
            }
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $stock->delete();

        return $this->response->success($stock, "El registro fue eliminado correctamente");
    }

    public function createMovimientoStock($tipo, $estado, $iddoc, $iduser, $array_detalle = [], $movimiento = 2, $numero_documento, $isReturn = true){

        $movimientos_id = $movimiento;
        if($tipo == "nota"){

            if($estado == 2 || $estado == 4){
                /* reapertura los helados seran agregados al usuario y saldran de almacen : salida*/
                $tipo_documento_id = 4;
                $movimientos_id = 2;
            }
            else if($estado == 3){
                /* guardado de helados del heladero vuelven al almacen y al congelador para el dia siguiente: entrada*/
                $tipo_documento_id = 5;
                $movimientos_id = 1;
            }else if($estado == 1){
                /* cierre de nota, se hace el calculo de la venta: salida - final*/
                $tipo_documento_id = 5;
                $movimientos_id = 2;
            }
            
        }else if($tipo == "factura_venta"){
            $tipo_documento_id = 2;
            $movimientos_id = 2;
        }else if($tipo == "boleta_venta"){
            $tipo_documento_id = 1;
            $movimientos_id = 2;
        }else if($tipo == "factura_compra"){
            $tipo_documento_id = 3;
            $movimientos_id = 1;
        }else if($tipo == "nota_venta"){
            $tipo_documento_id = 7;
            $movimientos_id = 2;
        }else if($tipo == "reajuste_ingreso"){
            $tipo_documento_id = 6;
            $movimientos_id = 1;
        }else if($tipo == "reajuste_salida"){
            $tipo_documento_id = 6;
            $movimientos_id = 2;
        }else{            
            $tipo_documento_id = 6;
        }

        $fecha_movimiento = date("Y-m-d");

        $codigo = '';

        $auth = Auth::user();
        $user_creador = $auth->id;

        $stock = new StockBarquillos();

        $stock->codigo_movimiento = $codigo;
        $stock->movimientos_id = $movimientos_id;
        $stock->tipo_documento_id = $tipo_documento_id;
        $stock->numero_documento = $numero_documento;
        $stock->fecha_movimiento = $fecha_movimiento;
        $stock->user_id = $user_creador;
        
        $stock->save();

        $idStock = $stock->id;

        /* añadir codigo */
        $codigo = str_pad($idStock, 7, "0", STR_PAD_LEFT);        
        $stock->codigo_movimiento = "sth-".$codigo;

        $stock->save();     
        /* añadir codigo */

        $detalle = [];

        if(count($array_detalle) > 0)
        {
            foreach($array_detalle as $item)
            {
                $codigo = $item["codigo"]??'';                
                $cantidad = $item["cantidad"]??0;

                $newDetail = new StockBarquillosDetail();

                $newDetail->codigo = $codigo;
                $newDetail->stock_barquillos_id = $idStock;
                $newDetail->cantidad = $cantidad;

                $newDetail->save();

                array_push($detalle, $newDetail);
            }
        }

        $stock["detalle"] = $detalle;        

        return $isReturn ? $this->response->success($stock) : $stock;
    }

    public function updateMovimientoStock($array_detalle = [], $id = null, $movimiento = null){
        
        if($id == null && !empty($movimiento)){
            $id = StockBarquillos::where("codigo_movimiento", $movimiento)
                ->value("id");
        }

        if(empty($id)) return null;

        foreach($array_detalle as $item){
            $codigo = $item["codigo"]??'';                
            $cantidad = $item["cantidad"]??0;

            $detail = StockBarquillosDetail::where("codigo", $codigo)
                                            ->where("stock_barquillos_id", $id)
                                            ->first();
            if(empty($detail)) continue;

            $detail->cantidad = $cantidad;
            $detail->save();
        }
    }

    public function getIdFromDocumento($numero_documento, $tipo_documento_id = null, $movimientos_id = null){
        $query = StockBarquillos::where("numero_documento", $numero_documento);

        if(!empty($tipo_documento_id)){
            $query->where("tipo_documento_id", $tipo_documento_id);
        }

        if(!empty($movimientos_id)){
            $query->where("movimientos_id", $movimientos_id);
        }

        $id = $query->value("id");

        return $id;
    }
    public function getStockHeladosByCodigo($codigo = null){
        if(empty($codigo)) return null;

        return StockBarquillos::where("codigo_movimiento", $codigo)->first();
    }

    public function deleteImage($imagen = ""){
        
        $imagen = request()->input("imagen") ?? $imagen;

        if($imagen == '') return false;
        
        $image_path = storage_path('app/public/fotos/').$imagen;
        
        if(\File::exists($image_path)){
            \File::delete($image_path);
            return true;
          }else{
            return false;
          }
    }

    public function updateDeleteImagen(Request $request, $id)
    {
        $stock = StockBarquillos::find($id);

        if(empty($stock)){
            return $this->response->error("No se envio un id valido");
        }

        $imagen = $request->input("imagen");

        if($imagen !='' && $stock->imagen == $imagen){

            $respuesta = $this->deleteImage($imagen);

            if($respuesta == false) return $this->response->error("La foto enviada no existe");
            
            $stock->imagen = "";

        };

        $stock->save();

        return $this->response->success($stock, "El registro fue actualizado correctamente");
    }

    public function getDataDetailFromId($id){
        $stock = StockBarquillos::find($id);        
        if($stock){
            $stock_detalle = StockBarquillosDetail::query()
                        ->leftJoin('productos', 'stock_barquillos_detail.codigo', '=', 'productos.codigo')                        
                        ->select(
                            "stock_barquillos_detail.id",
                            "stock_barquillos_detail.codigo",
                            "stock_barquillos_detail.stock_barquillos_id",
                            "stock_barquillos_detail.cantidad",
                            "stock_barquillos_detail.updated_at",
                            "stock_barquillos_detail.created_at",
                            "stock_barquillos_detail.caja",
                            "stock_barquillos_detail.caja_cantidad",                            
                            "productos.nombre as producto"
                        )
                        ->where('stock_barquillos_detail.stock_barquillos_id', $id)
                        ->orderBy('stock_barquillos_detail.created_at','asc')
                        ->orderBy('stock_barquillos_detail.codigo','asc')
                        ->get();

            $stock["detalle"] = $stock_detalle;
            
            return $stock;
        }else{
            return null;
        }
    }

    public function updateMovimientoStockFactura($array_salida, $array_entrada, $numero_documento = null){
        
        if(empty($numero_documento)) return null;

        $id = StockBarquillos::where("numero_documento", $numero_documento)->value("id");


        foreach($array_salida as $item){
            $codigo = $item["codigo"]??'';                
            $cantidad = $item["cantidad"]??0;

            $detail = StockBarquillosDetail::where("codigo", $codigo)
                                            ->where("stock_helados_id", $id)
                                            ->first();
            if(empty($detail)) continue;

            $detail->cantidad = $cantidad;
            $detail->save();
        }

        foreach($array_entrada as $item){
            $codigo = $item["codigo"]??'';                
            $cantidad = $item["cantidad"]??0;

            $detail = StockBarquillosDetail::where("codigo", $codigo)
                                            ->where("stock_helados_id", $id)
                                            ->first();
            if(empty($detail)) continue;

            $detail->delete();
        }

    }

    public function eliminarStockByCodigo($codigo_documento, $codigo_producto){
        $stock = StockBarquillos::where("numero_documento", $codigo_documento)->first();
        if(empty($stock)) return null;

        $idStock = $stock->id;

        $stock_detalle = StockBarquillosDetail::where("codigo", $codigo_producto)
                                                ->where("stock_barquillos_id", $idStock)
                                                ->first();
        if(empty($stock_detalle)) return null;

        $stock_detalle->delete();

        return $this->response->success($stock_detalle, "El registro fue eliminado correctamente");
    }
}
