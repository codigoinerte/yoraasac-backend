<?php

namespace App\Http\Controllers;

use App\Models\tipoDocumento;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = tipoDocumento::all();

        return response()->json([

            'data' => $data

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
     * @param  \App\Models\tipoDocumento  $tipoDocumento
     * @return \Illuminate\Http\Response
     */
    public function show(tipoDocumento $tipoDocumento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\tipoDocumento  $tipoDocumento
     * @return \Illuminate\Http\Response
     */
    public function edit(tipoDocumento $tipoDocumento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\tipoDocumento  $tipoDocumento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, tipoDocumento $tipoDocumento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\tipoDocumento  $tipoDocumento
     * @return \Illuminate\Http\Response
     */
    public function destroy(tipoDocumento $tipoDocumento)
    {
        //
    }
}
