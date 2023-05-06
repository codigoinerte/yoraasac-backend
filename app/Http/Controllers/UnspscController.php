<?php

namespace App\Http\Controllers;

use App\Models\Unspsc;
use Illuminate\Http\Request;

class UnspscController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $buscar = $request->input("buscar")??'';
        $type = $request->input("type")??'';

        $data = [];

        if($buscar != '')
        {
            $query = Unspsc::query();

            if($type == 'codigo')
            {
                $query->Where('id', 'LIKE', "%$buscar%");

            }
            else
            {
                $query->where('descripcion', 'LIKE', "%$buscar%");
            }
            $query->orderBy('descripcion','desc');

            $data = $query->get();
                
        }

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
     * @param  \App\Models\Unspsc  $unspsc
     * @return \Illuminate\Http\Response
     */
    public function show(Unspsc $unspsc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Unspsc  $unspsc
     * @return \Illuminate\Http\Response
     */
    public function edit(Unspsc $unspsc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Unspsc  $unspsc
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Unspsc $unspsc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Unspsc  $unspsc
     * @return \Illuminate\Http\Response
     */
    public function destroy(Unspsc $unspsc)
    {
        //
    }
}
