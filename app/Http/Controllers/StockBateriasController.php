<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBaterias;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StockBaterias as StockBateriasRequest;

class StockBateriasController extends Controller
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

        $query = StockBaterias::query()
                    ->leftJoin('movimientos', 'stock_baterias.movimientos_id', '=', 'movimientos.id')
                    ->leftJoin('tipo_documentos', 'stock_baterias.tipo_documento_id', '=', 'tipo_documentos.id')
                    ->select(
                        "stock_baterias.id",
                        "stock_baterias.codigo_movimiento",
                        "stock_baterias.numero_documento",
                        "stock_baterias.fecha_movimiento",
                        "stock_baterias.cantidad",
                        "movimientos.movimiento",
                        "tipo_documentos.documento",
                        "stock_baterias.created_at",
                        "stock_baterias.updated_at",
                    );

        if (!empty($codigo) && $codigo !="") {

            $query->where('stock_baterias.codigo_movimiento', $codigo);
        }
        
        if (!empty($movimiento) && $movimiento !="") {
            
            $query->where('movimientos.movimiento', 'LIKE', "%$movimiento%");
        }        
        
        if (!empty($fecha) && $fecha !="") {
            $query->whereDate('stock_baterias.created_at', $fecha);
        }

                
        $stock_baterias = $query->orderBy('stock_baterias.created_at','desc')->paginate(10, ['*'], 'page', $page);

        $nextPageUrl = $stock_baterias->nextPageUrl();
        $previousPageUrl = $stock_baterias->previousPageUrl();
            
        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        $data = $stock_baterias->toArray()["data"] ?? [];

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
    public function store(StockBateriasRequest $request)
    {
        $movimiento_id = $request->input("movimiento_id") ?? 0;
        $tipo_documento_id = $request->input("tipo_documento_id") ?? 0;
        $cantidad = $request->input("cantidad") ?? 0;
        $fecha_movimiento = $request->input("fecha_movimiento") ?? '';
        $numero_documento = $request->input("numero_documento") ?? '';

        $codigo = '';

        $stock = new StockBaterias();

        $stock->codigo_movimiento = $codigo;
        $stock->movimientos_id = $movimiento_id;
        $stock->tipo_documento_id = $tipo_documento_id;
        $stock->numero_documento = $numero_documento;
        $stock->fecha_movimiento = $fecha_movimiento;  
        $stock->cantidad = $cantidad;
        
        $stock->save();


        $idStock = $stock->id;

        /* añadir codigo */
        $codigo = str_pad($idStock, 7, "0", STR_PAD_LEFT);        
        $stock->codigo_movimiento = "stb-".$codigo;

        $stock->save();     
        /* añadir codigo */

        return $this->response->success($stock);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockBaterias  $stockBaterias
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stock = StockBaterias::find($id);
        
        if($stock){
    
            return $this->response->success($stock, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockBaterias  $stockBaterias
     * @return \Illuminate\Http\Response
     */
    public function edit(StockBaterias $stockBaterias)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockBaterias  $stockBaterias
     * @return \Illuminate\Http\Response
     */
    public function update(StockBateriasRequest $request, $id)
    {
        $stock = StockBaterias::find($id);

        if(empty($stock)){
            return $this->response->error("No se envio un id valido");
        }

        $movimiento_id = $request->input("movimiento_id") ?? 0;
        $tipo_documento_id = $request->input("tipo_documento_id") ?? 0;
        $cantidad = $request->input("cantidad") ?? 0;
        $fecha_movimiento = $request->input("fecha_movimiento") ?? '';
        $numero_documento = $request->input("numero_documento") ?? '';

        $stock->movimientos_id = $movimiento_id;
        $stock->tipo_documento_id = $tipo_documento_id;
        $stock->numero_documento = $numero_documento;
        $stock->fecha_movimiento = $fecha_movimiento;
        $stock->cantidad = $cantidad;
        
        $stock->save();        

        return $this->response->success($stock);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockBaterias  $stockBaterias
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stock = StockBaterias::find($id);

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
}
