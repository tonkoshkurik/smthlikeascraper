<?php
namespace App\Libs\Scrape;
use andreskrey\Readability\HTMLParser;

class Scraper
{
  public $rss_array;
  public $rss_url;
  public $result = array();
  public $root_url;
  # Include libraries
  public function __construct($target='') // or any other method
  {
    $this->root_url = $target;
    require_once("LIB_http.php");
    require_once("LIB_parse.php");
    require_once("LIB_rss.php");
//    $html = file_get_contents($target);
//    $this->rss_url = $this->getRSSLocation($html, $target);
//    if(!$this->rss_url) $this->rss_url = $target . '?feed=rss2';
//    $this->rss_array = download_parse_rss($this->rss_url);
    // $this->rss_display = display_rss_array($this->rss_array);
    // $html = file_get_contents($this->rss_array["ILINK"][0]);

  }

  public function getHtml($url){
    return Curl::to($url)
      ->get();
  }

  public function getRssArray(){
    $html = file_get_contents($this->root_url );
    $this->rss_url = $this->getRSSLocation($html, $target);
    if(!$this->rss_url) $this->rss_url = $target . '?feed=rss2';
    $this->rss_array = download_parse_rss($this->rss_url);

  }

  public function getContent($html){
    $this->rss_array = download_parse_rss($this->rss_url);

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
