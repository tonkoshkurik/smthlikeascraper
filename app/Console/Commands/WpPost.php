<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use \App\Sites;
use \App\Scraped;
use \App\Libs\Scrape;
use \App\Libs\Wp\WpAPI;
use \App\FetchError;

class WpPost extends Command
{
  protected $signature = 'wp:post';

  protected $description = 'Send post to wp';

  public function handle()
  {
    self::wppost();
  }

  public static function wppost()
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
            'title'   =>  $scraped[$index]["title"][$i]
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

    // Send all new posts to wp
    $newScraped = Scraped::where('saved', 0)->get();
    foreach ($newScraped as $scraped) {
      $post = new Scrape\Scraper($scraped->link);
      $p_content = $post->getContent();
      $site = Sites::find($scraped->site_id);
      if(!$site->api_integraion){  // Need to change it after finish with integraion cheking
        $auth    = array(
         "login"    =>   $site->login,
         "password" =>   $site->password
         );
        $wp_post = array(
         "title"   => $scraped->title,
         "content" => $p_content["html"]
         );

        $wp_api = new WpAPI($site->site, $auth);
        $post_id = $wp_api->newPost($wp_post);
        if($post_id){
          Scraped::where('title', $scraped->title)
                  ->where('link', $scraped->link)
                  ->update(['saved' => $post_id]);
          var_dump($post_id);
          var_dump($wp_post);
        } else {
          echo "update failed with post id: <br>";
          var_dump($post_id);
          echo "<br>";
        }
      }
    }
  }

}