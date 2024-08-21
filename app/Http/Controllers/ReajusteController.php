<?php

namespace App\Http\Controllers;

use App\Models\reajuste;
use Illuminate\Http\Request;
use App\Models\reajustes_detail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReajusteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->stock = new StockHeladosController();
        $this->response = new ResponseController();
    }

    public function index()
    {
        $data = reajuste::all();

        return response()->json([
            "data" => $data
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
        $fecha_reajuste = request()->input("fecha_reajuste");
        $detalle = request()->input("detalle");

        $auth = Auth::user();
        $sucursal_id = $auth->sucursals_id ?? 1;
        $user_id = $auth->id;

        $reajuste = new reajuste();

        $reajuste->user_id = $user_id;
        $reajuste->fecha_reajuste = $fecha_reajuste;

        $reajuste->save();
        $detail = $array_ingreso = $array_salida = [];

        if(count($detalle) > 0)
        {
            foreach($detalle as $item){
                $codigo = $item["codigo"] ?? '';
                $cantidad_ingreso = $item["cantidad_ingreso"] ?? 0;
                $cantidad_salida = $item["cantidad_salida"] ?? 0;
                $reajuste_id = $reajuste->id;

                $new_reajustes_detail = new reajustes_detail();

                $new_reajustes_detail->codigo = $codigo;
                $new_reajustes_detail->cantidad_ingreso = $cantidad_ingreso;
                $new_reajustes_detail->cantidad_salida = $cantidad_salida;
                $new_reajustes_detail->reajuste_id = $reajuste_id;

                $new_reajustes_detail->save();

                if($cantidad_ingreso > 0)
                    array_push($array_ingreso , [
                        "codigo" => $codigo,
                        "cantidad" => $cantidad_ingreso
                    ]);

                if($cantidad_salida > 0)
                    array_push($array_salida , [
                        "codigo" => $codigo,
                        "cantidad" => $cantidad_salida
                    ]);
                

                array_push($detail, $new_reajustes_detail);
            }
        }

        /* añadir codigo */
        $codigo = str_pad($reajuste->id, 7, "0", STR_PAD_LEFT);        
        $reajuste->codigo = "rs-".$codigo;
        $reajuste->save();
        /* añadir codigo */


        if(count($array_ingreso) > 0){
            $stock_ingreso = $this->stock->createMovimientoStock("reajuste_ingreso", 0, $reajuste->id, $user_id, $array_ingreso, 0, $reajuste->codigo, false);            
            $reajuste->codigo_ingreso = $stock_ingreso->codigo_movimiento;
        }

        if(count($array_salida) > 0){
            $stock_salida = $this->stock->createMovimientoStock("reajuste_salida", 0, $reajuste->id, $user_id, $array_salida, 0, $reajuste->codigo, false);    
            $reajuste->codigo_salida = $stock_salida->codigo_movimiento;
        }

        $reajuste->save();
        
        /* reajuste detail */
        $reajuste = reajuste::find($reajuste->id);

        $detalle = $this->getReajustesDetail($reajuste->id);

        $reajuste->detalle = $detalle;

        return $this->response->success($reajuste, "El registro fue encontrado");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\reajuste  $reajuste
     * @return \Illuminate\Http\Response
     */
    public function show(reajuste $reajuste)
    {
        $reajuste = reajuste::find($reajuste->id);

        $detalle = $this->getReajustesDetail($reajuste->id);

        $reajuste->detalle = $detalle;

        return $this->response->success($reajuste, "El registro fue encontrado");

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\reajuste  $reajuste
     * @return \Illuminate\Http\Response
     */
    public function edit(reajuste $reajuste)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\reajuste  $reajuste
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, reajuste $reajuste)
    {
        $fecha_reajuste = request()->input("fecha_reajuste");
        $detalle = request()->input("detalle");

        $reajuste = reajuste::find($reajuste->id);
        $reajuste->fecha_reajuste = $fecha_reajuste;
        $reajuste->save();

        $detail = $array_ingreso = $array_salida = [];

        if(count($detalle) > 0)
        {
            foreach($detalle as $item){
                $idDetail = $item["id"] ?? 0;
                $codigo = $item["codigo"] ?? '';
                $cantidad_ingreso = $item["cantidad_ingreso"] ?? 0;
                $cantidad_salida = $item["cantidad_salida"] ?? 0;
                $reajuste_id = $reajuste->id;

                $new_reajustes_detail = reajustes_detail::find($idDetail);
                
                if(empty($new_reajustes_detail)) continue;

                $new_reajustes_detail->codigo = $codigo;
                $new_reajustes_detail->cantidad_ingreso = $cantidad_ingreso;
                $new_reajustes_detail->cantidad_salida = $cantidad_salida;

                $new_reajustes_detail->save();

                if($cantidad_ingreso > 0)
                    array_push($array_ingreso , [
                        "codigo" => $codigo,
                        "cantidad" => $cantidad_ingreso
                    ]);

                if($cantidad_salida > 0)
                    array_push($array_salida , [
                        "codigo" => $codigo,
                        "cantidad" => $cantidad_salida
                    ]);
                

                array_push($detail, $new_reajustes_detail);
            }
        }

        $reajuste->detail = $detail;

        $codigo_ingreso	= $reajuste->codigo_ingreso??'';
        $codigo_salida	= $reajuste->codigo_salida??'';

        /*
        detail = [
            "codigo",
            "cantidad"
        ];
        */

        $auth = Auth::user();
        $user_id = $auth->id;
        
        if(!empty($codigo_ingreso) && count($array_ingreso) > 0){
            $this->stock->updateMovimientoStock($array_ingreso, 0, $codigo_ingreso);
        }else if(empty($codigo_ingreso) && count($array_ingreso) > 0){
            $stock_ingreso = $this->stock->createMovimientoStock("reajuste_ingreso", 0, $reajuste->id, $user_id, $array_ingreso, 0, $reajuste->codigo, false);            
            $reajuste->codigo_ingreso = $stock_ingreso->codigo_movimiento;
        }
        if(!empty($codigo_salida) && count($array_salida) > 0){
            $this->stock->updateMovimientoStock($array_salida, 0, $codigo_salida);
        }else if(empty($codigo_salida) && count($array_salida) > 0){
            $stock_salida = $this->stock->createMovimientoStock("reajuste_salida", 0, $reajuste->id, $user_id, $array_salida, 0, $reajuste->codigo, false);    
            $reajuste->codigo_salida = $stock_salida->codigo_movimiento;
        }

        
        /* reajuste detail */
        $reajuste = reajuste::find($reajuste->id);

        $detalle = $this->getReajustesDetail($reajuste->id);

        $reajuste->detalle = $detalle;

        return $this->response->success($reajuste, "El registro fue encontrado");;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\reajuste  $reajuste
     * @return \Illuminate\Http\Response
     */
    public function destroy(reajuste $reajuste)
    {
        $id = $reajuste->id;

        $reajuste = reajuste::find($id);

        if(empty($reajuste)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }
        
        $codigo_ingreso = $reajuste->codigo_ingreso ?? null;
        $codigo_salida = $reajuste->codigo_salida ?? null;
        
        if(!empty($codigo_ingreso)){
            $idStock_ingreso = $this->stock->getStockHeladosByCodigo($codigo_ingreso);
            $this->stock->eliminar_stock($idStock_ingreso->id);
        }

        if(!empty($codigo_salida)){
            $idStock_salida = $this->stock->getStockHeladosByCodigo($codigo_salida);
            $this->stock->eliminar_stock($idStock_salida->id);
        }

        $reajuste->delete();

        reajustes_detail::query()->where("reajuste_id", $id)->delete();        

        return $this->response->success($reajuste, "El registro fue eliminado correctamente");
    }

    public function getReajustesDetail($id){
        return reajustes_detail::query()                                
        ->leftJoin('productos',  'productos.codigo', '=', 'reajustes_detail.codigo')
        ->select(                                    
            "reajustes_detail.id",
            "reajustes_detail.codigo",
            "reajustes_detail.cantidad_ingreso",
            "reajustes_detail.cantidad_salida",
            "reajustes_detail.reajuste_id",
            "reajustes_detail.created_at",
            "reajustes_detail.updated_at",

            "productos.nombre as producto",            
        )
        ->addSelect(DB::raw("getStock(productos.codigo COLLATE utf8mb4_unicode_ci) as stock"))
        ->where('reajustes_detail.reajuste_id', $id)
        ->orderBy('reajustes_detail.codigo','asc')
        ->get();
    }
}
