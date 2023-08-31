<?php

namespace App\Http\Controllers;

use App\Models\destacados;
use Illuminate\Http\Request;
use App\Models\destacadoMenu;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResponseController;

class DestacadoMenuController extends Controller
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
        $menuResponse = $this->menus();

        $auth = Auth::user();
        $sucursal_id = $auth->sucursals_id ?? 1;
        $user_creador = $auth->id ?? null;

        $destacados = destacadoMenu::query()
                        ->leftJoin('destacados', 'destacados.id', '=', 'destacado_menus.idmenu')
                        ->where("destacado_menus.idusuario", "=", $user_creador)
                        ->orderBy('destacado_menus.orden','asc')
                        ->orderBy('destacado_menus.id','asc')
                        ->get();
        
        return $this->response->success([
            "menu" => [...$menuResponse],
            "destacado" => $destacados
        ], "Registro encontrado");
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
        $menuResponse = $this->menus();

        $menus_destacados = $request->input("destacados") ?? [];

        $auth = Auth::user();
        $sucursal_id = $auth->sucursals_id ?? 1;
        $user_creador = $auth->id ?? null;

        if(empty($user_creador)){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $query = destacadoMenu::query()
                    ->where("idusuario", "=", $user_creador)
                    ->delete();                    
        
        if(count($menus_destacados) > 0){
            foreach($menus_destacados as $item){
                
                $idusuario = $user_creador;
                $idmenu = $item["idmenu"] ?? 0;
                $orden = $item["orden"] ?? 0;
                $icono = $item["icono"] ?? "";

                $nuevo_menu = new destacadoMenu();

                $nuevo_menu->idusuario = $idusuario;
                $nuevo_menu->idmenu = $idmenu;
                $nuevo_menu->icono = $icono;
                $nuevo_menu->orden = $orden;

                $nuevo_menu->save();                
            }
        }

        $destacados = destacadoMenu::query()
                    ->where("idusuario", "=", $user_creador)
                    ->orderBy('orden','asc')
                    ->orderBy('id','asc')
                    ->get();

        return $this->response->success(
            [
                "menu" => [...$menuResponse],
                "destacado" => $destacados
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\destacadoMenu  $destacadoMenu
     * @return \Illuminate\Http\Response
     */
    public function show(destacadoMenu $destacadoMenu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\destacadoMenu  $destacadoMenu
     * @return \Illuminate\Http\Response
     */
    public function edit(destacadoMenu $destacadoMenu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\destacadoMenu  $destacadoMenu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, destacadoMenu $destacadoMenu)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\destacadoMenu  $destacadoMenu
     * @return \Illuminate\Http\Response
     */
    public function destroy(destacadoMenu $destacadoMenu)
    {
        //
    }

    public function menus (){
        $menus  = destacados::all();

        $menuResponse = [];

        foreach($menus as $item){

            $id = $item["id"]??0;
            $nombre = $item["nombre"]??"";
            $alias = $item["alias"]??"";
            $icono = $item["icono"]??"";
            $idparent = $item["idparent"]??0;

            $nuevo_array = [
                "id" => $id,
                "nombre" => $nombre,
                "alias" => $alias,
                "icono" => $icono,
                "idparent" => $idparent,
            ];

            if($idparent == 0){
                $menuResponse[$id] = $nuevo_array;
            }else{

                
                $array_parent  = $menuResponse[$idparent] ?? [];
                
                $array_related = $menuResponse[$idparent]["children"] ?? [];

                if(count($array_related) == 0){

                    $menuResponse[$idparent]["children"] = [$nuevo_array];
                }else{
                    array_push($menuResponse[$idparent]["children"], $nuevo_array);
                }
            }            
        }

        return $menuResponse;
    }
}
