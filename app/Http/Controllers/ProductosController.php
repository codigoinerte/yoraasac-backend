<?php

namespace App\Http\Controllers;

use App\Models\productos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\Request\Productos as ProductosRequest;

class ProductosController extends Controller
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
        $page = $request->input('page') ?? 1;
        $codigo = $request->input('codigo') ?? '';
        $producto = $request->input('producto') ?? '';
        $fecha = $request->input('fecha') ?? '';

        $query = productos::query()
                ->leftJoin('estados', 'productos.estados_id', '=', 'estados.id')
                ->leftJoin('moneda', 'productos.moneda_id', '=', 'moneda.id')
                ->select(
                            "productos.id",
                            "productos.codigo",
                            "productos.nombre",
                            "productos.orden",
                            "productos.stock_alerta",
                            "productos.precio_venta",
                            "productos.descuento",
                            "productos.destacado",
                            "productos.estados_id",
                            "productos.unspsc_id",
                            "productos.marcas_id",
                            "productos.unidad_id",
                            "productos.moneda_id",
                            "productos.igv_id",
                            "estados.estado",
                            "moneda.moneda",
                            "productos.created_at",
                            "productos.updated_at",
        )               ;
        
        if (!empty($codigo) && $codigo !="") {
            
            $query->where('productos.codigo', $codigo);
        }
        
        if (!empty($producto) && $producto !="") {
            
            $query->where('productos.nombre', 'LIKE', "%$producto%");
        }        
        
        if (!empty($fecha) && $fecha !="") {
            $query->whereDate('productos.created_at', $fecha);
        }
        
        $data = $query->orderBy('productos.codigo','asc')->get(); //->paginate(10, ['*'], 'page', $page);

        //$nextPageUrl = $productos->nextPageUrl();
        //$previousPageUrl = $productos->previousPageUrl();

        //parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        //parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        //$data = $productos->toArray()["data"] ?? [];

        $n=0;
        foreach($data as $item)
        {
            $created_at = $item["created_at"]??'';

            $fecha = str_replace("/", "-", $created_at);
            $newDate = date("d-m-Y", strtotime($fecha));		    

            $data[$n]["created_at_spanish"] = $newDate;
            $n++;
        }
        
        return response()->json([

            'data' => $data,
            'next_page' => null,
            'previous_page' => null,

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
    public function store(ProductosRequest $request)
    {
        $codigo = $request->input("codigo") ?? '';
        $nombre = $request->input("nombre") ?? '';
        $orden = $request->input("orden") ?? 0;
        $stock_alerta = $request->input("stock_alerta") ?? 0;
        $precio_venta = $request->input("precio_venta") ?? 0;
        $descuento = $request->input("descuento") ?? 0;
        $destacado = $request->input("destacado") ?? 0;
        $estados_id = $request->input("estados_id") ?? 0;
        $unspsc_id = $request->input("unspsc_id") ?? 0;
        $marcas_id = $request->input("marcas_id") ?? 0;
        $unidad_id = $request->input("unidad_id") ?? 0;
        $moneda_id = $request->input("moneda_id") ?? 0;
        $is_litro = $request->input("is_litro") ?? 0;
        $is_barquillo = $request->input("is_barquillo") ?? 0;
        $heladero_precio_venta = $request->input("heladero_precio_venta") ?? 0;
        $heladero_descuento = $request->input("heladero_descuento") ?? 0;
        $igv_id = $request->input("igv_id") ?? 0;
        $cantidad_caja = $request->input("cantidad_caja") ?? 0;
        $proveedor_precio = $request->input("proveedor_precio") ?? 0;
        
        $precio_venta_mayor = $request->input("precio_venta_mayor") ?? 0;
        $descuento_venta_mayor = $request->input("descuento_venta_mayor") ?? 0;

        $precio_venta_mayor_cajas = $request->input("precio_venta_mayor_cajas") ?? 0;
        $descuento_venta_mayor_cajas = $request->input("descuento_venta_mayor_cajas") ?? 0;

        $producto = new Productos();

        $producto->codigo = $codigo;
        $producto->nombre = $nombre;
        $producto->orden = $orden;
        $producto->stock_alerta = $stock_alerta;
        $producto->	precio_venta = $precio_venta;
        $producto->descuento = $descuento;
        $producto->destacado = $destacado;
        $producto->estados_id = $estados_id;
        $producto->unspsc_id = $unspsc_id;
        $producto->marcas_id = $marcas_id;
        $producto->unidad_id = $unidad_id;
        $producto->moneda_id = $moneda_id;
        $producto->heladero_precio_venta = $heladero_precio_venta;
        $producto->heladero_descuento = $heladero_descuento;
        $producto->precio_venta_mayor = $precio_venta_mayor;
        $producto->descuento_venta_mayor = $descuento_venta_mayor;
        $producto->precio_venta_mayor_cajas = $precio_venta_mayor_cajas;
        $producto->descuento_venta_mayor_cajas = $descuento_venta_mayor_cajas;
        $producto->igv_id = $igv_id;
        $producto->cantidad_caja = $cantidad_caja;
        $producto->proveedor_precio = $proveedor_precio;
        $producto->is_litro = $is_barquillo === true ? 0 : $is_litro;
        $producto->is_barquillo = $is_barquillo;

        $producto->save();

        return $this->response->success($producto);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\productos  $productos
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $producto = Productos::find($id);

        if($producto){
            
            return $this->response->success($producto, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\productos  $productos
     * @return \Illuminate\Http\Response
     */
    public function edit(productos $productos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\productos  $productos
     * @return \Illuminate\Http\Response
     */
    public function update(ProductosRequest $request, $id)
    {
        $producto = Productos::find($id);

        if(empty($producto)){
            return $this->response->error("No se envio un id valido");
        }

        $codigo = $request->input("codigo") ?? '';
        $nombre = $request->input("nombre") ?? '';
        $orden = $request->input("orden") ?? 0;
        $stock_alerta = $request->input("stock_alerta") ?? 0;
        $precio_venta = $request->input("precio_venta") ?? 0;
        $descuento = $request->input("descuento") ?? 0;
        $destacado = $request->input("destacado") ?? 0;
        $estados_id = $request->input("estados_id") ?? 0;
        $unspsc_id = $request->input("unspsc_id") ?? 0;
        $marcas_id = $request->input("marcas_id") ?? 0;
        $unidad_id = $request->input("unidad_id") ?? 0;
        $moneda_id = $request->input("moneda_id") ?? 0;
        $heladero_precio_venta = $request->input("heladero_precio_venta") ?? 0;
        $heladero_descuento = $request->input("heladero_descuento") ?? 0;
        $igv_id = $request->input("igv_id") ?? 0;
        $cantidad_caja = $request->input("cantidad_caja") ?? 0;
        $proveedor_precio = $request->input("proveedor_precio") ?? 0;
        $is_litro = $request->input("is_litro") ?? 0;
        $is_barquillo = $request->input("is_barquillo") ?? 0;

        $precio_venta_mayor = $request->input("precio_venta_mayor") ?? 0;
        $descuento_venta_mayor = $request->input("descuento_venta_mayor") ?? 0;

        $precio_venta_mayor_cajas = $request->input("precio_venta_mayor_cajas") ?? 0;
        $descuento_venta_mayor_cajas = $request->input("descuento_venta_mayor_cajas") ?? 0;

        $producto->codigo = $codigo;
        $producto->nombre = $nombre;
        $producto->orden = $orden;
        $producto->stock_alerta = $stock_alerta;
        $producto->	precio_venta = $precio_venta;
        $producto->descuento = $descuento;
        $producto->destacado = $destacado;
        $producto->estados_id = $estados_id;
        $producto->unspsc_id = $unspsc_id;
        $producto->marcas_id = $marcas_id;
        $producto->unidad_id = $unidad_id;
        $producto->moneda_id = $moneda_id;
        $producto->heladero_precio_venta = $heladero_precio_venta;
        $producto->heladero_descuento = $heladero_descuento;
        $producto->igv_id = $igv_id;
        $producto->cantidad_caja = $cantidad_caja;
        $producto->proveedor_precio = $proveedor_precio;
        $producto->is_litro = $is_barquillo === true ? 0 : $is_litro;
        $producto->is_barquillo =  $is_barquillo;
        $producto->precio_venta_mayor = $precio_venta_mayor;
        $producto->descuento_venta_mayor = $descuento_venta_mayor;
        $producto->precio_venta_mayor_cajas = $precio_venta_mayor_cajas;
        $producto->descuento_venta_mayor_cajas = $descuento_venta_mayor_cajas;

        $producto->save();

        return $this->response->success($producto, "El registro fue actualizado correctamente");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\productos  $productos
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $producto = Productos::find($id);

        if(empty($producto)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $producto->delete();

        return $this->response->success($producto, "El registro fue eliminado correctamente");
    }
}
