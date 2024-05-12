<?php

namespace App\Http\Controllers\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class home extends Controller
{
    function homeView(Request $request) {
        return view('test');
    }
}
