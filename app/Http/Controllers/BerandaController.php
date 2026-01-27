<?php

namespace App\Http\Controllers;

class BerandaController extends Controller
{
    public function index()
    {
        return view('Users.beranda');
    }

    public function features()
    {
        return view('Users.features');
    }

    public function about()
    {
        return view('Users.about');
    }

    public function contact()
    {
        return view('Users.contact');
    }
}
