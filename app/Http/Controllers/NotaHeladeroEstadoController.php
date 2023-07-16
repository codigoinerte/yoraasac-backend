<?php

namespace App\Http\Controllers;

use App\Models\NotaHeladeroEstado;
use Illuminate\Http\Request;

class NotaHeladeroEstadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $estado = NotaHeladeroEstado::all();

        return response()->json([

        'data' => $estado

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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NotaHeladeroEstado  $notaHeladeroEstado
     * @return \Illuminate\Http\Response
     */
    public function show(NotaHeladeroEstado $notaHeladeroEstado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NotaHeladeroEstado  $notaHeladeroEstado
     * @return \Illuminate\Http\Response
     */
    public function edit(NotaHeladeroEstado $notaHeladeroEstado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NotaHeladeroEstado  $notaHeladeroEstado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NotaHeladeroEstado $notaHeladeroEstado)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NotaHeladeroEstado  $notaHeladeroEstado
     * @return \Illuminate\Http\Response
     */
    public function destroy(NotaHeladeroEstado $notaHeladeroEstado)
    {
        //
    }
}
