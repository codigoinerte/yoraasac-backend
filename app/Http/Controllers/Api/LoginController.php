<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\accountUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\ResponseController;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->response = new ResponseController();
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (! $user || ! Hash::check($request->password, $user->password)) {
            $data = ["message"=>"Los campos no coinciden"];
            return response()->json($data, 400);
        }
    
        $token = $user->createToken('authToken')->plainTextToken;
    
        return response([
            'true' => true,
            'token' => $token,
            'name'=> $user->name,
            'surname'=> $user->apellidos,
            'email' => $user->email,
            'celular' => $user->celular,
            'pais' => $user->idpais,
            'departamento' => $user->iddepartamento,
            'provincia' => $user->idprovincia,
            'distrito' => $user->iddistrito,
            'direccion' => $user->direccion,
            'type'=> $user->usuario_tipo,
            'uid' => $user->id], 200);
    }

    public function validateLogin(Request $request)
    {
        $user = Auth::user();
        $tokenOriginal = $request->input('token');
        $token = PersonalAccessToken::findToken($tokenOriginal);
        
        
        if (!$token) {
    
            return response()->json([
                'message' => 'Invalid token'
            ], 400);
        } 

        $userId = $token->tokenable->id;
            
        $user = User::where('id', $userId)->first();
        
        $accessToken = $token->plainTextToken;

        return response()->json([
            'true' => true,
            'name'=> $user->name,
            'surname'=> $user->apellidos,
            'email' => $user->email,
            'celular' => $user->celular,
            'pais' => $user->idpais,
            'departamento' => $user->iddepartamento,
            'provincia' => $user->idprovincia,
            'distrito' => $user->iddistrito,
            'direccion' => $user->direccion,
            'type'=> $user->usuario_tipo,
            'uid' => $user->id
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $tokenOriginal = $request->input('token');
        $token = PersonalAccessToken::findToken($tokenOriginal);

        if ($token) $token->delete();
    }

    public function accountUpdate(accountUser $request){

        $name = $request->input("name") ?? '';
        $surname = $request->input("surname") ?? '';
        $email = $request->input("email") ?? '';
        $celular = $request->input("celular") ?? '';
        $idpais = $request->input("idpais") ?? 0;
        $iddepartamento = $request->input("departamento") ?? 0;
        $idprovincia = $request->input("provincia") ?? 0;
        $iddistrito = $request->input("distrito") ?? 0;
        $direccion = $request->input("direccion") ?? '';
        $password = $request->input("password") ?? '';

        $authInfo = Auth::user();
        $user_id = $authInfo->id;

        $auth = User::find($user_id);

        $auth->name = $name;
        $auth->apellidos = $surname;
        $auth->email = $email;
        $auth->celular = $celular;
        $auth->idpais = $idpais;
        $auth->iddepartamento = $iddepartamento;
        $auth->idprovincia = $idprovincia;
        $auth->iddistrito = $iddistrito;
        $auth->direccion = $direccion;

        if($idpais == 0){
            $auth->idpais = 179;
        }
        if($password != ''){
            $auth->password = Hash::make($password);
        }
        
        $auth->save();

        return $this->response->success($auth);
    }
}
