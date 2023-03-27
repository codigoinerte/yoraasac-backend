<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use Illuminate\Http\Request;

class ProvinciaController extends Controller
{
    public function __construct()
    {
        $this->response = new ResponseController();
    }

    public function index(Request $request)
    {
        $data = Provincia::all();

        return $this->response->success($data);
    }

    public function show(Request $request, $id)
    {
        if($id == "" || $id == null) return $this->response->error();
        
        $data = Provincia::where("id", $id)->orderBy("provincia","asc")->first();

        return $this->response->success($data);
    }

    public function showbydepartamento(Request $request, $id)
    {
        if($id == "" || $id == null) return $this->response->error();

        $data = Provincia::where("departamento_id", $id)->orderBy("provincia","asc")->get();

        return $this->response->success($data);
    }
}
