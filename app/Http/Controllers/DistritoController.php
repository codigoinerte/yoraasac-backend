<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use Illuminate\Http\Request;
use App\Http\Controllers\ResponseController;

class DistritoController extends Controller
{
    public function __construct()
    {
        $this->response = new ResponseController();
    }

    public function index(Request $request)
    {
        $data = Distrito::all();

        return $this->response->success($data);
    }

    public function show(Request $request, $id)
    {
        $data = Distrito::where("id", $id)->orderBy("distrito","asc")->first();

        return $this->response->success($data);
    }

    public function showbyprovincia(Request $request, $id)
    {
        if($id == "" || $id == null) return $this->response->error();

        $data = Distrito::where("provincia_id", $id)->orderBy("distrito","asc")->get();

        return $this->response->success($data);
    }

    
}
