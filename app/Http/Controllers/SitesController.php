<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sites;
use Validator;
use App\Libs\Scrape;
use App\Libs\Wp\WpAPI;
//use Andreskrey\Readability\HTMLParser;

class SitesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $sites = Sites::all();
      return view('sites.index', [ 'sites' => $sites ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
      return view('sites.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
      $this->validate($request, [
        'site' => 'unique:sites,site'
        ]);
      $validator = Validator::make($request->all(), [
        'site' => 'required|unique:sites,site|max:255',
        'site_to_fetch' => 'required',
        'login' => 'required',
        'password' => 'required'
        ]);
      if ($validator->fails()) {
        return redirect('sites/create')
        ->withErrors($validator)
        ->withInput();
      }
      $site = filter_var($request["site"], FILTER_VALIDATE_URL);
      $site_to_fetch = filter_var($request["site_to_fetch"], FILTER_VALIDATE_URL);
      $sites = new Sites();
      $sites->site = $site;
      $sites->site_to_fetch = $site_to_fetch;
      $sites->login = $request["login"];
      $sites->password = $request["password"];

      $sites->save();
      return redirect('sites');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $site = Sites::find($id);
      $scrape = new Scrape\Scraper($site->site_to_fetch);

      return view('sites.show', [
        "site" => $site,
        "RSS_DISPLAY" => $scrape->result["html"],
      ]);
    }

    public function sendToWp($id)
    {
      $site = Sites::find($id);
      $scrape = new Scrape\Scraper($site->site_to_fetch);

      $auth    = array(
        "login"    =>   $site->login,
        "password" =>   $site->password
      );

      $wp_post = array(
        "title"   => $scrape->result["title"],
        "content" => $scrape->result["html"]
      );

      $wp_api = new WpAPI($site->site, $auth);

      $wp_api->newPost($wp_post);

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
        $site = Sites::find($id);
        $site->delete();
        return redirect('sites');
    }
  }
