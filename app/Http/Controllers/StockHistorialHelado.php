<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockHistorialHelado extends Controller
{
    public function index(Request $request)
    {
        $query_user_venta = "(SELECT us_name.name
                            FROM users us_name
                            LEFT JOIN facturas document on document.user_id = us_name.id
                            WHERE CONCAT(document.codigo,document.serie) = sh.numero_documento)";
        $query_user_compra = "(SELECT us_name.name
                            FROM users us_name
                            WHERE us_name.id = sh.user_id)";
        $query_user_nota = "(SELECT us_name.name 
                            FROM users us_name
                            LEFT JOIN nota_heladeros document ON document.user_id = us_name.id
                            WHERE document.codigo = sh.numero_documento)";


        $query =  "SELECT sh.id, sh.codigo_movimiento, sh.movimientos_id as id_tipo_movimiento, mv.movimiento, td.documento, sh.numero_documento, sh.fecha_movimiento, sh.created_at, sh.updated_at,
                   case  
                        when sh.tipo_documento_id = 1 then $query_user_venta
                        when sh.tipo_documento_id = 2 then $query_user_venta
                        when sh.tipo_documento_id = 7 then $query_user_venta

                        when sh.tipo_documento_id = 3 then $query_user_compra

                        when sh.tipo_documento_id = 4 then $query_user_nota
                        when sh.tipo_documento_id = 5 then $query_user_nota
                   end as user_related 
                  FROM stock_helados sh
                  LEFT JOIN movimientos mv ON mv.id = sh.movimientos_id
                  LEFT JOIN tipo_documentos td ON td.id = sh.tipo_documento_id 
                  ORDER BY sh.created_at DESC";
        $data = DB::select(DB::raw($query));
        
        if(!empty($data)){
            foreach($data as $key => $item){
                $idstock = $item->id ?? 0;
                $array_detalle = $this->detalle_stock_helado($idstock);
                $data[$key]->detalle = $array_detalle;
            }
        }

        return response()->json([
            "data" => $data
        ], 200);
    }

    public function detalle_stock_helado($idstockhelado = null){
        
        if($idstockhelado == 0 || $idstockhelado == null) return new stdClass();
        $query =  "SELECT shd.id, shd.codigo, pro.nombre, shd.cantidad, shd.caja, shd.caja_cantidad
                   FROM stock_helados_detail shd
                   LEFT JOIN productos pro ON pro.codigo = shd.codigo 
                   WHERE shd.stock_helados_id = $idstockhelado";
        $data = DB::select(DB::raw($query));

        return $data;
    }
}
