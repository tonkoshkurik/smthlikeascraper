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

      $settings = Setting::find(1);
      return view('settings', $settings);
    }

    public function store(Request $request)
    {
      $s = Setting::find(1);

      $s->update([
        'proxy'   => $request["proxy"],
        'bulkapi' => $request["bulkapi"]
      ]);

      return redirect('settings');
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
