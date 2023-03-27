<?php

namespace App\Http\Controllers;

use App\Models\Igv;
use Illuminate\Http\Request;

class IgvController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Igv::all();

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
     * @param  \App\Models\Igv  $igv
     * @return \Illuminate\Http\Response
     */
    public function show(Igv $igv)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Igv  $igv
     * @return \Illuminate\Http\Response
     */
    public function edit(Igv $igv)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Igv  $igv
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Igv $igv)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Igv  $igv
     * @return \Illuminate\Http\Response
     */
    public function destroy(Igv $igv)
    {
        //
    }
}
