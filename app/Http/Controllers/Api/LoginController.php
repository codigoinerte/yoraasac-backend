<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
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
            'token' => $token, 
            'name'=> $user->name , 
            'surname'=> $user->apellidos, 
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
            'name'=> $user->name , 
            'surname'=> $user->apellidos, 
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
}
