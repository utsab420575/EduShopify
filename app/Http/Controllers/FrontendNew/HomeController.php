<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('frontend_new.home.index');
    }
}
