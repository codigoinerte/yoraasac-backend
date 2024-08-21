<?php

namespace App\Http\Controllers;

use App\Models\StockHelados;
use Illuminate\Http\Request;
use App\Models\StockHeladosDetail;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\Request\StockHelados as StockHeladosRequest;

class StockHeladosController extends Controller
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

        $query = StockHelados::query()
                    ->leftJoin('movimientos', 'stock_helados.movimientos_id', '=', 'movimientos.id')
                    ->leftJoin('tipo_documentos', 'stock_helados.tipo_documento_id', '=', 'tipo_documentos.id')
                    ->select(
                        "stock_helados.id",
                        "stock_helados.codigo_movimiento",
                        "stock_helados.numero_documento",
                        "stock_helados.fecha_movimiento",
                        "movimientos.movimiento",
                        "tipo_documentos.documento",
                        "stock_helados.created_at",
                        "stock_helados.updated_at",
                    );

        if (!empty($codigo) && $codigo !="") {

            $query->where('stock_helados.codigo_movimiento', $codigo);
        }
        
        if (!empty($movimiento) && $movimiento !="") {
            
            $query->where('movimientos.movimiento', 'LIKE', "%$movimiento%");
        }        
        
        if (!empty($fecha) && $fecha !="") {
            $query->whereDate('stock_helados.created_at', $fecha);
        }

                
        $stock_helados = $query->orderBy('stock_helados.created_at','desc')->paginate(10, ['*'], 'page', $page);

        $nextPageUrl = $stock_helados->nextPageUrl();
        $previousPageUrl = $stock_helados->previousPageUrl();

        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        $data = $stock_helados->toArray()["data"] ?? [];

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
    public function store(StockHeladosRequest $request)
    {
        $movimientos_id = $request->input("movimientos_id") ?? 0;
        $tipo_documento_id = $request->input("tipo_documento_id") ?? 0;
        $fecha_movimiento = $request->input("fecha_movimiento") ?? '';
        $numero_documento = $request->input("numero_documento") ?? '';
        $image_file = $request->input("image_file") ?? '';

        $array_detalle = $request->input("detalle")??[];

        $auth = Auth::user();
        $user_creador = $auth->id;

        $codigo = '';

        $stock = new StockHelados();

        $stock->codigo_movimiento = $codigo;
        $stock->movimientos_id = $movimientos_id;
        $stock->tipo_documento_id = $tipo_documento_id;
        $stock->numero_documento = $numero_documento;
        $stock->fecha_movimiento = $fecha_movimiento;
        $stock->imagen = $image_file;
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
                $caja = $item["caja"]??0;
                $caja_cantidad = $item["caja_cantidad"]??0;

                $newDetail = new StockHeladosDetail();

                $newDetail->codigo = $codigo;
                $newDetail->stock_helados_id = $idStock;
                $newDetail->cantidad = $cantidad;
                $newDetail->caja = $caja;
                $newDetail->caja_cantidad = $caja_cantidad;

                $newDetail->save();

                array_push($detalle, $newDetail);
            }
        }

        $stock["detalle"] = $detalle;        

        return $this->response->success($stock);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockHelados  $stockHelados
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stock = StockHelados::find($id);
        
        if($stock){
    
            // $stock_detalle = StockHeladosDetail::where('stock_helados_id', $id)->get();
    
            $stock_detalle = StockHeladosDetail::query()
                        ->leftJoin('productos', 'stock_helados_detail.codigo', '=', 'productos.codigo')                        
                        ->select(
                            "stock_helados_detail.id",
                            "stock_helados_detail.codigo",
                            "stock_helados_detail.stock_helados_id",
                            "stock_helados_detail.cantidad",
                            "stock_helados_detail.updated_at",
                            "stock_helados_detail.created_at",
                            "stock_helados_detail.caja",
                            "stock_helados_detail.caja_cantidad",
                            "productos.nombre as producto"
                        )
                        ->where('stock_helados_detail.stock_helados_id', $id)
                        ->orderBy('stock_helados_detail.created_at','desc')
                        ->get();

            $stock["detalle"] = $stock_detalle;
            
            return $this->response->success($stock, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockHelados  $stockHelados
     * @return \Illuminate\Http\Response
     */
    public function edit(StockHelados $stockHelados)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockHelados  $stockHelados
     * @return \Illuminate\Http\Response
     */
    public function update(StockHeladosRequest $request, $id)
    {
        $stock = StockHelados::find($id);

        if(empty($stock)){
            return $this->response->error("No se envio un id valido");
        }

        $movimientos_id = $request->input("movimientos_id") ?? 0;
        $tipo_documento_id = $request->input("tipo_documento_id") ?? 0;
        $fecha_movimiento = $request->input("fecha_movimiento") ?? '';
        $numero_documento = $request->input("numero_documento") ?? '';
        $image_file = $request->input("image_file") ?? '';

        $array_detalle = $request->input("detalle")??[];

        $stock->movimientos_id = $movimientos_id;
        $stock->tipo_documento_id = $tipo_documento_id;
        $stock->numero_documento = $numero_documento;
        $stock->fecha_movimiento = $fecha_movimiento;        
        $stock->imagen = $image_file;        
        
        $stock->save();        

        $detalle = [];

        //StockHeladosDetail::where('stock_helados_id', $id)->delete();

        if(count($array_detalle) > 0)
        {
            foreach($array_detalle as $item)
            {
                $idDetail = $item["id"]??null;
                $codigo = $item["codigo"]??'';                
                $cantidad = $item["cantidad"]??0;
                $caja = $item["caja"]??0;
                $caja_cantidad = $item["caja_cantidad"]??0;

                if(empty($idDetail)) break;

                $newDetail = StockHeladosDetail::find($idDetail);
                
                $newDetail->codigo = $codigo;
                $newDetail->stock_helados_id = $id;
                $newDetail->cantidad = $cantidad;
                $newDetail->caja = $caja;
                $newDetail->caja_cantidad = $caja_cantidad;

                $newDetail->save();

                array_push($detalle, $newDetail);
            }
        }

        $stock["detalle"] = $detalle;        

        return $this->response->success($stock);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockHelados  $stockHelados
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->eliminar_stock($id);
    }

    public function eliminar_stock($id){
        $stock = StockHelados::find($id);      
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

        $stock = new StockHelados();

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

                $newDetail = new StockHeladosDetail();

                $newDetail->codigo = $codigo;
                $newDetail->stock_helados_id = $idStock;
                $newDetail->cantidad = $cantidad;

                $newDetail->save();

                array_push($detalle, $newDetail);
            }
        }

        $stock["detalle"] = $detalle;        

        return $isReturn ? $this->response->success($stock) : $stock;
    }

    public function updateMovimientoStock($array_detalle = [], $id = 0, $movimiento = null){
        if($id == 0 && $movimiento == null) return null;
        
        if(empty($movimiento)) return;

        $id = StockHelados::where("codigo_movimiento", $movimiento)
            ->value("id");

        if(empty($id)) return null;
        
        foreach($array_detalle as $item){
            $codigo = $item["codigo"]??'';                
            $cantidad = $item["cantidad"]??0;

            $detail = StockHeladosDetail::where("codigo", $codigo)
                                            ->where("stock_helados_id", $id)
                                            ->first();
            if(empty($detail)) continue;

            $detail->cantidad = $cantidad;
            $detail->save();
        }
    }

    public function getIdFromDocumento($numero_documento, $tipo_documento_id = null, $movimientos_id = null){
        $query = StockHelados::where("numero_documento", $numero_documento);

        if(!empty($tipo_documento_id)){
            $query->where("tipo_documento_id", $tipo_documento_id);
        }

        if(!empty($movimientos_id)){
            $query->where("movimientos_id", $movimientos_id);
        }

        $id = $query->value("id");
    }

    public function getStockHeladosByCodigo($codigo = null){
        if(empty($codigo)) return null;

        return StockHelados::where("codigo_movimiento", $codigo)->first();
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
        $stock = StockHelados::find($id);

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
}
