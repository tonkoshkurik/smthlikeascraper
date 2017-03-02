<?php
namespace App\Libs\Scrape;

use andreskrey\Readability\HTMLParser;
use Ixudra\Curl\Facades\Curl;

class Scraper
{
  public $rss_array;
  public $rss_url;
  public $result = array();
  public $root_url;
  protected $settings;

  # Include libraries
  public function __construct($target='') // or any other method
  {
    $this->root_url = $target;
    require_once("LIB_http.php");
    require_once("LIB_parse.php");
    require_once("LIB_rss.php");

    $this->settings = \App\Setting::find(1);
    //    $html = file_get_contents($target);
    //    $this->rss_url = $this->getRSSLocation($html, $target);
    //    if(!$this->rss_url) $this->rss_url = $target . '?feed=rss2';
    //    $this->rss_array = download_parse_rss($this->rss_url);
    // $this->rss_display = display_rss_array($this->rss_array);
    // $html = file_get_contents($this->rss_array["ILINK"][0]);
  }

  public function getHtml($url){
    // $settings = \App\Settings::find(1);
     $proxies = explode(PHP_EOL, $this->settings->proxy);
     $proxy = $proxies[array_rand($proxies)];
     return Curl::to($url)
      ->allowRedirect()
      ->withOption('PROXY', trim($proxy))
      ->get();
  }

  public function getRssArray($url=''){
    if( !$url == '') {
      $this->root_url = $url;
      // dd('hi');
    }
    
    if(is_array($this->root_url)){
      foreach ($this->root_url as $url) {
        $html = $this->getHtml($url);
        try {
          $this->rss_url = $this->getRSSLocation($html, $url);
        } catch (\Exception $ex)
        {
          echo('No feeds were found for this URL');
        }
//        if(!$this->rss_url) $this->rss_url = $this->root_url . '?feed=rss2';
        $rss_feed = $this->getHtml($this->rss_url);
        $this->rss_array[] = download_parse_rss($rss_feed);
      }
      return $this->rss_array;
    } else {
      $html = $this->getHtml($url);
      try {
        $this->rss_url = $this->getRSSLocation($html, $url);
      } catch (\Exception $ex)
      {
        dd('No feeds were found for this URL');
      }
      if(!$this->rss_url) $this->rss_url = $this->root_url . '?feed=rss2';
      $rss_feed = $this->getHtml($this->rss_url);
      $this->rss_array = download_parse_rss($rss_feed);
      return $this->rss_array;
    }
  }

  public function getContent($link=''){
    if($link==='') $link = $this->root_url;
    $html = $this->getHtml($link);
    $opts = [
      'maxTopCandidates' => 3, // Max amount of top level candidates
      'articleByLine' => false,
      'stripUnlikelyCandidates' => true,
      'cleanConditionally' => true,
      'weightClasses' => true,
      'removeReadabilityTags' => true,
      'fixRelativeURLs' => true,
      'originalURL' => $this->root_url
    ];

    $readability = new HTMLParser($opts);
    return $readability->parse($html);
  }

  public function sendLinkTo($urls)
  {
    $apikey = $this->settings->bulkapi;
    return $this->send_to_bulkaddurl($apikey, $urls);
  }

  private function send_to_bulkaddurl($apikey, $urls) {

  $url_api = 'http://bulkaddurl.com/api';

  $urls = implode(PHP_EOL, $urls);

  $fields = array(
      'urls'=>$urls,
      'apikey'=>$apikey,
      'name'=>'Adding task with scraper api',
    );

  $fields_string = http_build_query($fields);

  //open connection
  $ch = curl_init();

  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL,$url_api);
  curl_setopt($ch,CURLOPT_POST, count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //execute post
  $result = curl_exec($ch);

  $result = json_decode($result, true);
  return $result;
  } 

  /**
   * @link http://keithdevens.com/weblog/archive/2002/Jun/03/RSSAuto-DiscoveryPHP
   */
  
  public function getRSSLocation($html, $location){
    if(!$html or !$location){
      return false;
    }else{
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
            if(strstr($href, "http://") !== false){ #if it's absolute
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


}
