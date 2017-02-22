<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Setting;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function settings()
    {
      $settings = Setting::all();
      dd($settings);
    }

    public function store($request)
    {
      dd($request);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sites.index');
    }
}
