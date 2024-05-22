<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*recordar eliminar la carpeta original para poder volver a regenerarlo*/
Route::get('/symlink-artisan', function(){
    Artisan::call('storage:link');
    return 'Symlink created successfully.';
});

Route::get('/clear-cache-laravel', function(){
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('cache:clear');
    Artisan::call('clear-compiled');
    
    //Artisan::call('optimize');
    
    //Cache::flush();

    return 'Cache refreshed successfully.';
});