<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBarquillos;
use Illuminate\Support\Facades\Auth;
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
                        "stock_barquillos.cantidad",
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
        $movimiento_id = $request->input("movimientos_id") ?? 0;
        $tipo_documento_id = $request->input("tipo_documento_id") ?? 0;
        $cantidad = $request->input("cantidad") ?? 0;
        $fecha_movimiento = $request->input("fecha_movimiento") ?? '';
        $numero_documento = $request->input("numero_documento") ?? '';

        $codigo = '';

        $stock = new StockBarquillos();

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
        $stock->codigo_movimiento = "stq-".$codigo;

        $stock->save();     
        /* añadir codigo */

        return $this->response->success($stock);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockBarquillos  $stockBarquillos
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stock = StockBarquillos::find($id);
        
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

        $movimiento_id = $request->input("movimientos_id") ?? 0;
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
     * @param  \App\Models\StockBarquillos  $stockBarquillos
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stock = StockBarquillos::find($id);

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
