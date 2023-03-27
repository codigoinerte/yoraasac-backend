<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Request\Personas;
use App\Http\Controllers\ResponseController;

class PersonasController extends Controller
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
        $tipo = $request->input('tipo') ?? 4;
        $page = $request->input('page') ?? 1;

        $documento = $request->input('documento') ?? '';
        $nombres = $request->input('nombres') ?? '';
        $fecha = $request->input('fecha') ?? '';

        $query = User::query();

        if (!empty($tipo) && $tipo !="") {
            
            $query->where('usuario_tipo', "$tipo");
        }
        
        if (!empty($nombres) && $nombres !="") {
            
            $query->where('name', 'LIKE', "%$nombres%");
        }
        
        if (!empty($documento) && $documento !="") {
            
            $query->where('documento', $documento);
        }
        
        if (!empty($fecha) && $fecha !="") {
            $query->whereDate('created_at', $fecha);
        }
        
        $users = $query->orderBy('created_at','desc')->paginate(10, ['*'], 'page', $page);

        // $users = User::where('usuario_tipo', $tipo)  
        // ->paginate(10, ['*'], 'page', $page);

        // ->get()      
        // ->map(function($registro) {
            
        //     $fecha = str_replace("/", "-", $registro->created_at);			
        //     $registro->created_at = date("d-m-Y", strtotime($fecha));		

        //     return $registro;
        // })   

        $nextPageUrl = $users->nextPageUrl();
        $previousPageUrl = $users->previousPageUrl();

        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        $data = $users->toArray()["data"] ?? [];

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
    public function store(Personas $request)
    {
        $documento = $request->input("documento") ?? '';
        $documento_tipo = $request->input("documento_tipo") ?? 0;
        $nombres = $request->input("nombres") ?? '';
        $apellidos = $request->input("apellidos") ?? '';        
        $pais = $request->input("pais") ?? 0;
        $departamento = $request->input("departamento") ?? 0;
        $provincia = $request->input("provincia") ?? 0;
        $distrito = $request->input("distrito") ?? 0;
        $direccion = $request->input("direccion") ?? '';
        $celular = $request->input("celular") ?? '';
        $email = $request->input("email") ?? null;
        $password = $request->input("password") ?? '';
        $usuario_tipo = $request->input("usuario_tipo") ?? 0;

        $persona = new User();

        $persona->documento = $documento;
        $persona->documento_tipo = $documento_tipo;
        $persona->name = $nombres;
        $persona->apellidos = $apellidos;
        $persona->idpais = $pais;
        $persona->iddepartamento = $departamento;
        $persona->idprovincia = $provincia;
        $persona->iddistrito = $distrito;
        $persona->email = ($usuario_tipo == 1 || $usuario_tipo == 2 || $usuario_tipo == 3) ? $email : null;
        $persona->password = ($usuario_tipo == 1 || $usuario_tipo == 2 || $usuario_tipo == 3) ? Hash::make($password) : ''; 
        $persona->celular = $celular;
        $persona->direccion = $direccion;
        $persona->usuario_tipo = $usuario_tipo;

        $persona->save();

        return $this->response->success($persona);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $persona = User::find($id);

        if($persona){
            
            return $this->response->success($persona, "El registro fue encontrado");
        }else{
            return $this->response->error("El registro no fue encontrado");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Personas $request, $id)
    {
        $persona = User::find($id);

        if(empty($persona)){
            return $this->response->error("No se envio un id valido");
        }

        $documento = $request->input("documento") ?? '';
        $documento_tipo = $request->input("documento_tipo") ?? 0;
        $nombres = $request->input("nombres") ?? '';
        $apellidos = $request->input("apellidos") ?? '';        
        $pais = $request->input("pais") ?? 0;
        $departamento = $request->input("departamento") ?? 0;
        $provincia = $request->input("provincia") ?? 0;
        $distrito = $request->input("distrito") ?? 0;
        $direccion = $request->input("direccion") ?? '';
        $celular = $request->input("celular") ?? '';
        $email = $request->input("email") ?? null;
        $password = $request->input("password") ?? '';
        $usuario_tipo = $request->input("usuario_tipo") ?? 0;

        $persona->documento = $documento;
        $persona->documento_tipo = $documento_tipo;
        $persona->name = $nombres;
        $persona->apellidos = $apellidos;
        $persona->idpais = $pais;
        $persona->iddepartamento = $departamento;
        $persona->idprovincia = $provincia;
        $persona->iddistrito = $distrito;
        $persona->email = ($usuario_tipo == 1 || $usuario_tipo == 2 || $usuario_tipo == 3 )? $email : null;

        if(($usuario_tipo == 1 || $usuario_tipo == 2 || $usuario_tipo == 3) && $password !="" ){

            $persona->password = Hash::make($password); 
        }

        $persona->celular = $celular;
        $persona->direccion = $direccion;
        $persona->usuario_tipo = $usuario_tipo;

        $persona->save();

        return $this->response->success($persona, "El registro fue actualizado correctamente");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $persona = User::find($id);

        if(empty($persona)){
            return $this->response->error("No se envio un id valido");
        }

        $auth_tipo = Auth::user()->usuario_tipo;
        if($auth_tipo !=  1 && $auth_tipo != 2){
            return $this->response->error("El usuario no esta autorizado para realizar esta accion");
        }

        $persona->delete();

        return $this->response->success($persona, "El registro fue eliminado correctamente");
    }
}
