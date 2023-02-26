<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;

class PersonasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tipo = $request->input('tipo') ?? 4;
        $page = $request->input('page') ?? 1;

        $documento = $request->input('documento') ?? '';
        $nombres = $request->input('nombres') ?? '';
        $fecha = $request->input('fecha') ?? '';

        $query = User::query();

        if (!empty($tipo) && $tipo !="") {
            
            $query->where('usuario_tipo', "$tipo");
        }
        
        if (!empty($nombres) && $nombres !="") {
            
            $query->where('name', 'LIKE', "%$nombres%");
        }
        
        if (!empty($documento) && $documento !="") {
            
            $query->where('documento', $documento);
        }
        
        if (!empty($fecha) && $fecha !="") {
            $query->whereDate('created_at', $fecha);
        }
        
        $users = $query->paginate(10, ['*'], 'page', $page);

        // $users = User::where('usuario_tipo', $tipo)  
        // ->paginate(10, ['*'], 'page', $page);

        // ->get()      
        // ->map(function($registro) {
            
        //     $fecha = str_replace("/", "-", $registro->created_at);			
        //     $registro->created_at = date("d-m-Y", strtotime($fecha));		

        //     return $registro;
        // })   

        $nextPageUrl = $users->nextPageUrl();
        $previousPageUrl = $users->previousPageUrl();

        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $nextPageQueryParams);
        parse_str(parse_url($previousPageUrl, PHP_URL_QUERY), $previousPageQueryParams);

        $data = $users->toArray()["data"] ?? [];

        $n=0;
        foreach($data as $item)
        {
            $created_at = $item["created_at"]??'';

            $fecha = str_replace("/", "-", $created_at);
            $newDate = date("d-m-Y", strtotime($fecha));		    

            $data[$n]["created_at"] = $newDate;
            $n++;
        }
        
        return response()->json([

            'data' => $data,
            'next_page' => isset($nextPageQueryParams['page']) ? $nextPageQueryParams['page'] : null,
            'previous_page' => isset($previousPageQueryParams['page']) ? $previousPageQueryParams['page'] : null,

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
