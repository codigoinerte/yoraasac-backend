<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IgvController;
use App\Http\Controllers\PaisController;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\StockController;
use App\Http\Controllers\MarcasController;
use App\Http\Controllers\MonedaController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\UnspscController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\MainConfiguration;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\ReajusteController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\DestacadosController;
use App\Http\Controllers\StockHistorialHelado;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\MovimientosController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\ImportarNotaController;
use App\Http\Controllers\NotaHeladeroController;
use App\Http\Controllers\StockHeladosController;
use App\Http\Controllers\BuscarUsuarioController;
use App\Http\Controllers\DestacadoMenuController;
use App\Http\Controllers\FacturaEstadoController;
use App\Http\Controllers\LocalesSeriesController;
use App\Http\Controllers\StockBateriasController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\BuscarProductoController;
use App\Http\Controllers\StockBarquillosController;
use App\Http\Controllers\NotaHeladeroEstadoController;
use App\Http\Controllers\SucursalesDocumentosSerieController;

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
Route::middleware(['auth:sanctum'])->group(function () {

    Route::resource('destacados', DestacadoMenuController::class);

    Route::resource('locales-series', LocalesSeriesController::class);

    Route::resource('contacto', ContactoController::class);

    Route::resource('configuracion', MainConfiguration::class);

    Route::get('reporte-factura', [FacturaController::class, 'reporte']);

    Route::get('reporte-nota', [NotaHeladeroController::class, 'reporte']);

    Route::post('guardar-foto', [PersonasController::class, 'uploadImage']);

    Route::delete('eliminar-foto', [PersonasController::class, 'deleteImage']);

    Route::post('eliminar-foto-persona/{id}', [PersonasController::class, 'updateDeleteImagen']);

    Route::get('nota-heladero-productos', [NotaHeladeroController::class, 'listPublicProducts']);

    Route::get('nota-heladero-buscar', [NotaHeladeroController::class, 'findNotaGuardada']);
    
    Route::resource('factura', FacturaController::class);

    Route::resource('nota-heladero', NotaHeladeroController::class);

    Route::resource('notas-estado', NotaHeladeroEstadoController::class);

    Route::resource('buscar-usuario', BuscarUsuarioController::class);
     
    Route::resource('buscar-producto', BuscarProductoController::class);

    Route::resource('persona', PersonasController::class);

    Route::get('reporte-heladero-asistencia', [PersonasController::class, 'reporteAsistencia']);

    Route::resource('producto', ProductosController::class);

    Route::get('stock', [StockController::class,'index']);

    Route::get('stock-historial-helado', [StockHistorialHelado::class,'index']);

    Route::resource('stock-helado', StockHeladosController::class);

    Route::post('eliminar-foto-nota/{id}', [StockHeladosController::class, 'updateDeleteImagen']);
    
    Route::resource('stock-bateria', StockBateriasController::class);
    
    Route::resource('stock-barquillos', StockBarquillosController::class);

    Route::post('eliminar-foto-stock-barquillo/{id}', [StockBarquillosController::class, 'updateDeleteImagen']);
    
    Route::resource('reajuste', ReajusteController::class);

    Route::resource('unspsc', UnspscController::class);
    Route::resource('estado', EstadosController::class);
    Route::resource('estado-factura', FacturaEstadoController::class);
    Route::resource('marca', MarcasController::class);
    Route::resource('unidad', UnidadController::class);
    Route::resource('moneda', MonedaController::class);
    Route::resource('igv', IgvController::class);
    Route::resource('movimiento', MovimientosController::class);
    Route::resource('tipo-documento',TipoDocumentoController::class);
    Route::resource('importar-nota',ImportarNotaController::class);
    Route::get('doc-series', [SucursalesDocumentosSerieController::class, 'index']);
    
    Route::get('/pais',[ PaisController::class, 'index']);

    Route::get('/departamento',[ DepartamentoController::class, 'index']);
    Route::get('/departamento/{id}',[ DepartamentoController::class, 'show'])->where('id', '[0-9]+');
    
    Route::get('/provincia',[ ProvinciaController::class, 'index']);
    Route::get('/provincia/{id}',[ ProvinciaController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/provincia/filtro/departamento/{id}',[ ProvinciaController::class, 'showbydepartamento'])->where('id', '[0-9]+');
    
    Route::get('/distritos',[ DistritoController::class, 'index']);
    Route::get('/distritos/{id}',[ DistritoController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/distritos/filtro/provincia/{id}',[ DistritoController::class, 'showbyprovincia'])->where('id', '[0-9]+');

    Route::put('/account-update', [LoginController::class, 'accountUpdate']);

    Route::post('/hota-heladero-fecha-operacion/{id}', [NotaHeladeroController::class, 'saveDateOperation']);

    Route::post('/reset-nota-heladero/{id}', [NotaHeladeroController::class, 'resetNotaHeladero']);

    Route::get('/buscar-nota-incompleta', [NotaHeladeroController::class, 'searchNotaIncomplete']);
});


Route::post('/login', [LoginController::class, 'login']);
Route::post('/renew-token', [LoginController::class, 'validateLogin']);
Route::post('/logout', [LoginController::class, 'logout']);