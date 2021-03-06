<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use \App\Sites;
use \App\Scraped;
use \App\Libs\Scrape;
use \App\Libs\Wp\WpAPI;
use \App\FetchError;
use Mockery\CountValidator\Exception;

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
    $time_start = microtime(true); 
    $sites = Sites::all();
    $result = array();
    $index = 0;
    foreach ($sites as $site) {
      $scraped = array();
      $result["id"]        = $site->id;
      $result["fetched"]   = $site->site_to_fetch;

      $fetching_array = explode("\n", $site->rss_feeds);
      $scrape = new Scrape\Scraper($fetching_array);
      $rss_array = $scrape->getRssArray($fetching_array);
      $time_end = microtime(true);



      foreach ($rss_array as $scrape) {
        if(isset($scrape["ILINK"]))  {
          $scraped[$index]["links"] = $scrape["ILINK"];
          $scraped[$index]["title"] = $scrape["ITITLE"];
          for($i=0; $i<count($scraped[$index]["links"]); $i++) {

            // validation 
            $validation = Validator::make(
              array(
                'link' => $scraped[$index]["links"][$i],
                'title' => $scraped[$index]["title"][$i],
                ),
              array(
                'link' => array( 'required', 'unique:scrapeds' ),
                'title' => array( 'required', 'unique:scrapeds' ),
                )
              );

            if ( $validation->fails() ) {
                $errors = $validation->messages();
                // Log::error($errors);
                echo "\n ============ \n";
                echo $errors;
                echo "\n ============ \n"; 
            } else {
              Scraped::firstOrCreate([
              'site_id' =>  $result["id"],
              'link'    =>  str_limit(strtok($scraped[$index]["links"][$i], '?'), 250),
              'title'   =>  str_limit($scraped[$index]["title"][$i], 250),
              ]);
            $message = "have scraped: " . $scraped[$index]["links"][$i];
            echo "\n ============ \n";
            echo $message;
            echo "\n ============ \n"; 
            }
          }
          $index++;
        } else {
//          echo "Error with: \n";
//          var_dump($scrape);
//          FetchError::updateOrCreate([
//            'site_id'   =>  $result["id"],
//            'rss_url'   =>  $scrape->rss_url
//            ]);
        }
      }
    }

    // Send all new posts to wp
    $newScraped = Scraped::whereNull('saved')->get();

    foreach ($newScraped as $scraped) {
      $post = new Scrape\Scraper($scraped->link);
      $p_content = '';
      try {
        $p_content = $post->getContent();
      } catch (Exception $ex){
        var_dump($ex);
      }
      if($p_content !== ''){
        $site = Sites::find($scraped->site_id);
        $auth    = array(
         "login"    =>   $site->login,
         "password" =>   $site->password
         );

        $wp_post = array(
         "title"   => $scraped->title,
         "content" => $p_content["html"]
         );

        $wp_api = new WpAPI($site->site, $auth);

        try{
          $post_id = $wp_api->newPost($wp_post);

          echo "The post is: $post_id \n";

          if($post_id){

            echo "\n Saved post with $post_id and $scraped->title";

            Scraped::where('title', $scraped->title)
              ->where('link', $scraped->link)
              ->update(['saved' => $post_id]);
          } else {
            echo "update failed with post id: <br>";
            var_dump($post_id);
            echo "<br>";
          }
        } catch (Exception $ex)
        {
          // nothing
        }

      }
    }
    $saved = Scraped::whereNotNull('saved')->where('saved', '!=' , 0)->whereNull('bulka')->get();
    $links = array();

    foreach ($saved as $v) {
      $site = Sites::find($v->site_id);
      $auth    = array(
         "login"    =>   $site->login,
         "password" =>   $site->password
         );
      $post_id = $v->saved;
      $wp_api = new WpAPI($site->site, $auth);
      $post = $wp_api->getpost($post_id);
      $link = $post["link"];
      if($link){
        $scraped_id = $v->id;
        $links[] = $link;
        Scraped::where('id', $scraped_id)->update(['bulka' => 1]);
      }
    }
    
    if(count($links)){
       $bulkaSender = new Scrape\Scraper();
       $bulkaSender->sendLinkTo($links);
    }

    //dividing with 60 will give the execution time in minutes other wise seconds
    $execution_time = ($time_end - $time_start)/60;

    //execution time of the script
    echo '<b>Total Execution Time:</b> '.$execution_time. " Mins \n ";
  }
}