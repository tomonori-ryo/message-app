<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 'home' という名前のビューファイルを表示しなさい、という指示
        return view('home');
    } 
}