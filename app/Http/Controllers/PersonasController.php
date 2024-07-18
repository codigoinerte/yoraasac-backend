<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\uploadImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
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
        $auth = Auth::user();
        $authId = $auth->id ?? 0;

        $tipo = $request->input('tipo') ?? 4;
        $page = $request->input('page') ?? 1;

        $documento = $request->input('documento') ?? '';
        $nombres = $request->input('nombres') ?? '';
        $fecha = $request->input('fecha') ?? '';

        $query = User::query();
        
        

        if (!empty($tipo) && $tipo !="") {
            
            if($tipo == 6){
                $query->where('id', '<>' , $authId);
                $query->where('id', '<>' , 1);
                $query->where(function($query) {
                    $query->where('usuario_tipo', 6)
                            ->orWhere('usuario_tipo', 1)
                            ->orWhere('usuario_tipo', 2)
                            ->orWhere('usuario_tipo', 3);
                });
                        
            }
            else{
                $query->where('usuario_tipo', "$tipo");
            }
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
        
        $data = $query->orderBy('created_at','desc')->get();
        
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

        $foto_frontal = $request->input("foto_frontal");
        $foto_posterior = $request->input("foto_posterior");

        $persona = new User();

        $persona->documento = $documento;
        $persona->documento_tipo = $documento_tipo;
        $persona->name = $nombres;
        $persona->apellidos = $apellidos;
        $persona->idpais = $pais;
        $persona->iddepartamento = $departamento;
        $persona->idprovincia = $provincia;
        $persona->iddistrito = $distrito;
        
        if($usuario_tipo == 1 || $usuario_tipo == 2 || $usuario_tipo == 3){
            if($email!=null) $persona->email = $email;
            if($password!="") $persona->password = Hash::make($password);
        }else{
            $persona->email = null;
            $persona->password = '';
        }
        #$persona->email = ($usuario_tipo == 1 || $usuario_tipo == 2 || $usuario_tipo == 3) ? $email : null;
        #$persona->password = ($usuario_tipo == 1 || $usuario_tipo == 2 || $usuario_tipo == 3) ? Hash::make($password) : ''; 
        $persona->celular = $celular;
        $persona->direccion = $direccion;
        $persona->usuario_tipo = $usuario_tipo;

        if($foto_frontal !='' ) $persona->foto_frontal = $foto_frontal;
        if($foto_posterior!='') $persona->foto_posterior = $foto_posterior;

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

        $foto_frontal = $request->input("foto_frontal");
        $foto_posterior = $request->input("foto_posterior");

        $persona->documento = $documento;
        $persona->documento_tipo = $documento_tipo;
        $persona->name = $nombres;
        $persona->apellidos = $apellidos;
        $persona->idpais = $pais;
        $persona->iddepartamento = $departamento;
        $persona->idprovincia = $provincia;
        $persona->iddistrito = $distrito;

        if($foto_frontal !='' ) $persona->foto_frontal = $foto_frontal;
        if($foto_posterior!='') $persona->foto_posterior = $foto_posterior;

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

    public function updateDeleteImagen(Request $request, $id)
    {
        $persona = User::find($id);

        if(empty($persona)){
            return $this->response->error("No se envio un id valido");
        }

        $foto_frontal = $request->input("foto_frontal");
        
        $foto_posterior = $request->input("foto_posterior");

        if($foto_frontal !='' && $persona->foto_frontal == $foto_frontal){

            $respuesta = $this->deleteImage($foto_frontal);

            if($respuesta == false)
            {
                return $this->response->error("La foto enviada no existe");
            }

            $persona->foto_frontal = "";

        };

        if($foto_posterior!=''  && $persona->foto_posterior == $foto_posterior) {

            $respuesta = $this->deleteImage($foto_posterior);

            if($respuesta == false)
            {
                return $this->response->error("La foto enviada no existe");
            }

            $persona->foto_posterior = "";
        };

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

    public function uploadImage(uploadImage $request)
    {        
        $fotoArray = $request->file('foto');
        
        $fotoNombreArray = [];     
        
        for($key = 0; $key<count($fotoArray); $key++)
        {
            $foto = $fotoArray[$key]??'';
            
            if($foto == '' || $foto==null){
                array_push($fotoNombreArray, '');                
            }
            else
            {

                $name = (time() + $key).'.'.$foto->getClientOriginalExtension();
                
                $destinationPath = public_path('/fotos');
        
                $data = getimagesize($foto);
                
                $width = $data[0]??0;
                
                $height = $data[1]??0;
        
                $tamanioMaximo = 3 * 1024 * 1024;
        
                $foto = Image::make($foto)->encode('jpg', 75);
                
                $ruta = storage_path('app/public/fotos').'/'.$name;
                
                $anchoMaximo = 1000;
        
                $altoMaximo = 1000;
                
                if ($width > $anchoMaximo || $height > $altoMaximo) {
        
                    $foto->resize($anchoMaximo, $altoMaximo, function ($constraint) {
                        $constraint->aspectRatio();
                    });
        
                }
                
                $foto->save($ruta);
    
                array_push($fotoNombreArray, $name);             
    
                unset($foto, $name);            
            }
            
        }


        return response()->json(['ruta' => $fotoNombreArray], 200);        
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

    public function reporteAsistencia(Request $request){
       
        $fecha_anio = date("Y");
        $fecha_mes = date("m");
        $fecha_dia = date("d");

        $query_string = "
            SELECT 
            users.id,
            users.documento,
            CONCAT(users.name,' ',users.apellidos )as heladero_nombre,
            (
                SELECT count(*)
                FROM  asistencias as asistencias
                WHERE   MONTH(asistencias.fecha) = $fecha_mes
                    AND YEAR(asistencias.fecha) = $fecha_anio
                    AND DAY(asistencias.fecha) = $fecha_dia
                    AND asistencias.user_id = users.id
            ) as asistio
        FROM users
        WHERE users.usuario_tipo = 7
        ORDER BY heladero_nombre;
        ";

        $data = \DB::select($query_string);

        return response()->json([
            'data' => $data,
        ], 200);
    }
}
