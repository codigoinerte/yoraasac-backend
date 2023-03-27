<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    public function __construct()
    {
        $this->response = new ResponseController();
    }

    public function index()
    {
        $data = Departamento::all();

        return $this->response->success($data);
    }

    public function show(Request $request, $idpais)
    {
        $data = Departamento::where("id", $idpais)->orderBy("departamento","asc")->first();

        return $this->response->success($data);
    }
}
