<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sites;
use App\Scraped;
use Validator;
use \App\Libs\Scrape;
use \App\Libs\Wp\WpAPI;
//use Andreskrey\Readability\HTMLParser;

class SitesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $scraper;

    public function index(){
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

    public function __construct()
    {
      $this->scraper =  new Scrape\Scraper();
    }

  /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
      // HERE WE FETCH RSS FEEDS
      // checked is actually array with rss url for feeds
      $cheked = $this->checkAuth($request);

      if(count($cheked["feed"])){

        $site = filter_var($request["site"], FILTER_VALIDATE_URL);
        // $site_to_fetch = $request["site_to_fetch"];

        // lets take here rss array of url, it's will make our scraper more faster
        $rss_array_string = '';

        foreach ($cheked["feed"] as $rss) {
             $rss_array_string .= $rss . "\n";
        }

        $sites = new Sites();

        $sites->site = $site;
        $sites->site_to_fetch = $request["site_to_fetch"];
        $sites->rss_feeds = $rss_array_string;
        $sites->login = $request["login"];
        $sites->password = $request["password"];

        $sites->save();

        $site_id = $sites->id;

        foreach ($cheked["links"] as $v)
        {
          Scraped::firstOrCreate([
            'site_id' =>  $site_id,
            'link'    =>  $v["link"],
            'title'   =>  $v["title"],
            'saved'   => 0
          ]);
        }

        return redirect('sites');
      } else {
        // why you return $cheked?
        return $cheked;
      }

    }

    private function checkAuth($request)
    {
      $site = filter_var($request["site"], FILTER_VALIDATE_URL);

      if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $site)) {
        return "Site URL is invalid, url should have full path with protocol: http://example.com";
      }

      $f_array = array();
      $fetching_array = explode(PHP_EOL, $request["site_to_fetch"]);
      
      foreach ($fetching_array as $site_to_fetch) {
        # code...
        $site_to_fetch = trim(preg_replace('/\s\s+/', ' ', $site_to_fetch));
        // dd($site_to_fetch);
        $site_to_fetch = filter_var($site_to_fetch, FILTER_VALIDATE_URL);
        $f_array[] = $site_to_fetch;
        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $site_to_fetch)) {
          return "Fetching site URL is invalid, url should have full path with protocol: http://example.com";
        }
      }

      $auth    = array(
        "login"    =>   $request["login"],
        "password" =>   $request["password"]
      );

      if(substr($site, -1) == '/') {
        $site = substr($site, 0, -1);
      }

      $url_array = parse_url($site);
      $url  = isset($url_array["scheme"]) ? $url_array["scheme"] : "http";
      $url .= "://";
      $url .= isset($url_array["host"]) ? $url_array["host"] : $site;
      $url .= "/xmlrpc.php";

      $wp_api =  new \HieuLe\WordpressXmlrpcClient\WordpressClient;

      # Set the credentials for the next requests
      $wp_api->setCredentials($url, $auth["login"],  $auth["password"]);

      try {
        $wp_api->getPosts();
      } catch(\Exception $exception) {
        dd('invalid wordpress login');
      }

      // get url's for rss feeds
      $rss_array = $this->returnRssArray($f_array);
      $rss["feed"] = $rss_array;

      // here we should get all $rss_arrays with links to add it to cache to avoid first scraping
      $rss_arrays = $this->scraper->getRssArray($rss_array);

      $links = [];
      foreach ($rss_arrays as $scrape) {
        if(isset($scrape["ILINK"])){
          $scraped["links"] = $scrape["ILINK"];
          $scraped["title"] = $scrape["ITITLE"];
          for($i=0; $i<count($scraped["links"]); $i++){
            $links[] = [
              'link'    =>  $scraped["links"][$i],
              'title'   =>  $scraped["title"][$i]
            ];
          }
        }
      }
      $rss["links"] = $links;
      return $rss;
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
      // $scrape = new Scrape\Scraper($site->site_to_fetch);
      $fetch = Scraped::where('site_id', $id)->orderBy('id', 'desc')->get();

      // dd($fetch);

      return view('sites.show', [
        "site" => $site,
        "fetch" => $fetch
        // "RSS_DISPLAY" => $scrape->result["html"],
      ]);
    }

    public function getPost()
    {
      $site_id = request('site_id');
      $post_id = request('post_id');
      $site = Sites::find($site_id);
      // $scrape = new Scrape\Scraper($site->site_to_fetch);

      $auth    = array(
        "login"    =>   $site->login,
        "password" =>   $site->password
      );


      $wp_api = new WpAPI($site->site, $auth);

      $post_data = $wp_api->getPost($post_id);

      $post_html = <<<EOL
          <input type="hidden" name="site_id" value="$site_id">
          <input type="hidden" name="post_id" value="$post_id">
          <div class="form-group">
            <input type="text" name="post_title" class="form-control" value="$post_data[post_title]" >
          </div>
          <div class="form-group">
            <textarea class="form-control" name="post_content">$post_data[post_content]</textarea>
          </div>
          <input type="submit" value="Update Post">
EOL;
      return $post_html;
    }

    public function editpost()
    {
      $site_id = request('site_id');
      $post_id = request('post_id');
      $site = Sites::find($site_id);
      $auth    = array(
        "login"    =>   $site->login,
        "password" =>   $site->password
      );
      $content = array(
        'post_title' => request('post_title'),
        'post_content' => request('post_content')
        );
      $wp_api = new WpAPI($site->site, $auth);
      $response = $wp_api->editpost($post_id, $content);
      $response ? $response = 'Success' : $response = 'Failed';
      return $response;
    }



    // rss array at first adding 
    private function returnRssArray($links)
    {
      if(is_array($links)){
        $rss_array = array();
        foreach ($links as $urla) {
          $html = $this->scraper->getHtml($urla);
          try {
            $rss_url = $this->getRSSLocation($html, $urla);
            $rss_feed = $this->scraper->getHtml($rss_url);
            if($rss_feed){
              $rss_feed = download_parse_rss($rss_feed);
              if(count($rss_feed["ILINK"])){
                $rss_array[] = $rss_url;
              }
            }
          } catch (\Exception $ex)
          {
            echo 'No feeds were found for this URL: ' . $urla .'<br>';
          }
        }
        return $rss_array;
      } else {
        return false;
      }
    }

    public function getRSSLocation($html, $location)
    {
    if(!$html or !$location){
      return false;
    } else {
      #search through the HTML, save all <link> tags
      # and store each link's attributes in an associative array
      preg_match_all('/<link\s+(.*?)\s*\/?>/si', $html, $matches);
      $links = $matches[1];
      $final_links = array();
      $link_count = count($links);
      for($n=0; $n<$link_count; $n++){
        $attributes = preg_split('/\s+/s', $links[$n]);
        foreach($attributes as $attribute){
          $att = preg_split('/\s*=\s*/s', $attribute, 2);
          if(isset($att[1])){
            $att[1] = preg_replace('/([\'"]?)(.*)\1/', '$2', $att[1]);
            $final_link[strtolower($att[0])] = $att[1];
          }
        }
        $final_links[$n] = $final_link;
      }
      #now figure out which one points to the RSS file
      for($n=0; $n<$link_count; $n++){
        if(strtolower($final_links[$n]['rel']) == 'alternate'){
          if(strtolower($final_links[$n]['type']) == 'application/rss+xml'){
            $href = $final_links[$n]['href'];
          }
          if(!$href and strtolower($final_links[$n]['type']) == 'text/xml'){
            #kludge to make the first version of this still work
            $href = $final_links[$n]['href'];
          }
          if($href){
            if( (strstr($href, "http://") !== false ) OR (strstr($href, "https://") !== false)){ #if it's absolute
              $full_url = $href;
            }else{ #otherwise, 'absolutize' it
              $url_parts = parse_url($location);
              #only made it work for http:// links. Any problem with this?
              $full_url = "http://$url_parts[host]";
              if(isset($url_parts['port'])){
                $full_url .= ":$url_parts[port]";
              }
              if($href{0} != '/'){ #it's a relative link on the domain
                $full_url .= dirname($url_parts['path']);
                if(substr($full_url, -1) != '/'){
                  #if the last character isn't a '/', add it
                  $full_url .= '/';
                }
              }
              $full_url .= $href;
            }
            return $full_url;
          }
        }
      }
      return false;
    }
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
