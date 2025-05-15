<?php

namespace App\Http\Controllers;

use App\Models\productos;
use Illuminate\Http\Request;
use App\Models\BuscarProducto;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ResponseController;

class BuscarProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->response = new ResponseController();
    }
    public function index(Request $request)
    {
        $producto = $request->input('producto') ?? '';
        $type = $request->input('type') ?? 0;

        if($producto == ""){
            return $this->response->success($producto, "No se envio un nombre valido");
        }
        
        $query = productos::query()
                ->leftJoin('estados', 'productos.estados_id', '=', 'estados.id')
                ->leftJoin('moneda', 'productos.moneda_id', '=', 'moneda.id')
                ->select(
                            "productos.id",
                            "productos.codigo",
                            "productos.nombre",
                            "productos.orden",
                            "productos.stock_alerta",
                            "productos.destacado",
                            "productos.estados_id",
                            "productos.unspsc_id",
                            "productos.marcas_id",
                            "productos.unidad_id",
                            "productos.moneda_id",
                            "productos.igv_id",
                            "productos.cantidad_caja",
                            "productos.is_barquillo",
                            "productos.is_litro",
                            "estados.estado",
                            "moneda.moneda",
                            "productos.created_at",
                            "productos.updated_at",
        );

        /*
        type 1: precio por mayor
        type 2: precio heladero
        type any/0: precio normal 
        */
        if($type == 1){            
            $query
                ->addSelect(DB::raw('productos.precio_venta_mayor as precio_venta'))
                ->addSelect(DB::raw('productos.descuento_venta_mayor as descuento'))

                ->addSelect(DB::raw('productos.precio_venta_mayor_cajas as precio_venta_cajas'))
                ->addSelect(DB::raw('productos.descuento_venta_mayor_cajas as descuento_cajas'))
                
                ->addSelect(DB::raw('1 as is_unit'));
        }else if($type == 2){            
            $query
                ->addSelect(DB::raw('productos.heladero_precio_venta as precio_venta'))
                ->addSelect(DB::raw('productos.heladero_descuento as descuento'))

                ->addSelect(DB::raw('productos.precio_venta_mayor_cajas as precio_venta_cajas'))
                ->addSelect(DB::raw('productos.descuento_venta_mayor_cajas as descuento_cajas'))
                
                ->addSelect(DB::raw('1 as is_unit'));
        }else{
            $query
                ->addSelect(DB::raw('productos.precio_venta as precio_venta'))
                ->addSelect(DB::raw('productos.descuento as descuento'))

                ->addSelect(DB::raw('productos.precio_venta_mayor_cajas as precio_venta_cajas'))
                ->addSelect(DB::raw('productos.descuento_venta_mayor_cajas as descuento_cajas'))
                
                ->addSelect(DB::raw('1 as is_unit'));
        }
                           
        $query->where('productos.codigo', 'like',"%$producto%")
                ->orWhere('productos.nombre', 'like',"%$producto%");
    
       
        $productos = $query->orderBy('productos.created_at','desc')->get();
        
        return response()->json([

            'data' => $productos

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BuscarProducto  $buscarProducto
     * @return \Illuminate\Http\Response
     */
    public function show(BuscarProducto $buscarProducto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BuscarProducto  $buscarProducto
     * @return \Illuminate\Http\Response
     */
    public function edit(BuscarProducto $buscarProducto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BuscarProducto  $buscarProducto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BuscarProducto $buscarProducto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BuscarProducto  $buscarProducto
     * @return \Illuminate\Http\Response
     */
    public function destroy(BuscarProducto $buscarProducto)
    {
        //
    }
}
