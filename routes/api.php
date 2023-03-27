<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IgvController;
use App\Http\Controllers\PaisController;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\MarcasController;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\UnspscController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ProvinciaController;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\DepartamentoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::resource('persona', PersonasController::class);

    Route::resource('producto', ProductosController::class);

    Route::resource('unspsc', UnspscController::class);
    Route::resource('estado', EstadosController::class);
    Route::resource('marca', MarcasController::class);
    Route::resource('unidad', UnidadController::class);
    Route::resource('moneda', MonedaController::class);
    Route::resource('igv', IgvController::class);
    
    Route::get('/pais',[ PaisController::class, 'index']);

    Route::get('/departamento',[ DepartamentoController::class, 'index']);
    Route::get('/departamento/{id}',[ DepartamentoController::class, 'show'])->where('id', '[0-9]+');
    
    Route::get('/provincia',[ ProvinciaController::class, 'index']);
    Route::get('/provincia/{id}',[ ProvinciaController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/provincia/filtro/departamento/{id}',[ ProvinciaController::class, 'showbydepartamento'])->where('id', '[0-9]+');
    
    Route::get('/distritos',[ DistritoController::class, 'index']);
    Route::get('/distritos/{id}',[ DistritoController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/distritos/filtro/provincia/{id}',[ DistritoController::class, 'showbyprovincia'])->where('id', '[0-9]+');
});


Route::post('/login', [LoginController::class, 'login']);
Route::post('/renew-token', [LoginController::class, 'validateLogin']);
Route::post('/logout', [LoginController::class, 'logout']);