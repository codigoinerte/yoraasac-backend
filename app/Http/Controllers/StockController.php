<?php

namespace App\Http\Controllers;

use App\Models\stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        #ingresos excepto nota de helado de regreso
        $query_entrada = "(SELECT SUM(shd.cantidad)
                        FROM stock_helados_detail shd
                        LEFT JOIN stock_helados sh ON sh.id = shd.stock_helados_id
                        WHERE sh.movimientos_id = 1 AND shd.codigo = prod.codigo AND sh.tipo_documento_id <> 5)";

        $query_salida = "(SELECT SUM(shd.cantidad)
                        FROM stock_helados_detail shd
                        LEFT JOIN stock_helados sh ON sh.id = shd.stock_helados_id
                        WHERE sh.movimientos_id = 2 AND shd.codigo = prod.codigo)";

        $query =   "SELECT prod.id, prod.codigo, prod.nombre, prod.stock_alerta, prod.precio_venta, prod.descuento, prod.destacado, prod.estados_id, prod.unspsc_id, prod.marcas_id, prod.unidad_id, 
                    prod.moneda_id, prod.igv_id, prod.heladero_precio_venta, prod.heladero_descuento, prod.cantidad_caja, $query_entrada as entrantes, $query_salida as salientes
                    FROM productos prod
                    ORDER BY prod.nombre DESC";
            $data = DB::select(DB::raw($query));

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function show(stock $stock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function edit(stock $stock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, stock $stock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function destroy(stock $stock)
    {
        //
    }
}
