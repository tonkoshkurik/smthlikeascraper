<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Sites;
use \App\Scraped;
use App\Libs\Scrape;
use App\Libs\Wp\WpAPI;
use App\FetchError;

class ScrapeController extends Controller
{
  public function all()
  {
    $sites = Sites::all();
    $result = array();

    $index = 0;
    foreach ($sites as $site) {
      $scraped = array();
      $result["id"]        = $site->id;
      $result["fetched"]   = $site->site_to_fetch;
      $scrape = new Scrape\Scraper($site->site_to_fetch);
      $scrape->getRssArray();

      if(isset($scrape->rss_array["ILINK"])){
        $scraped[$index]["links"] = $scrape->rss_array["ILINK"];
        $scraped[$index]["title"] = $scrape->rss_array["ITITLE"];
        for($i=0; $i<count($scraped[$index]["links"]); $i++){
          Scraped::firstOrCreate([
            'site_id' =>  $result["id"],
            'link'    =>  $scraped[$index]["links"][$i],
            'title'   =>  $scraped[$index]["title"][$i],
            'saved'   => 0
          ]);
        }
        $index++;
      } else {
        FetchError::updateOrCreate([
          'site_id'   =>  $result["id"],
          'rss_url'   =>  $scrape->rss_url
        ]);
      }
    }

    return view('scrape.index', [
      "result" => $sites
    ]);

  }
  public function saveToWp($id){
    $news = Scraped::where('saved', 0)->get();
    foreach ($news as $new)
    {
      $scrape = new Scrape\Scraper($new->link);

//      $site = Sites::find($id);
//      $auth    = array(
//        "login"    =>   $site->login,
//        "password" =>   $site->password
//      );
//      $wp_post = array(
//        "title"   => $scrape->result["title"],
//        "content" => $scrape->result["html"]
//      );
//
//      $wp_api = new WpAPI($site->site, $auth);
//
//      $wp_api->newPost($wp_post);
    }
  }

  public function sendToWp($id)
  {
    $site = Sites::find($id);
    $scrape = new Scrape\Scraper($site->site_to_fetch);
    $scrape->getRssArray();

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
}
