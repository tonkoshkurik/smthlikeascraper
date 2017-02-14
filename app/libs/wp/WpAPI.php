<?php
namespace App\Libs\Wp;
use Ixudra\Curl\Facades\Curl;

class WpAPI
{
  public $response;
  public $auth;
  private $wpClient;

  public function  __construct($remote, $auth)
  {
    $url_array = parse_url($remote);
    $url  = $url_array["scheme"] ? $url_array["scheme"] : "http";
    $url .= "://";
    $url .= $url_array["host"];
    $url .= "/xmlrpc.php";
    # Create client instance
    $this->wpClient = new \HieuLe\WordpressXmlrpcClient\WordpressClient;
    # Set the credentials for the next requests
    $this->wpClient->setCredentials($url, $auth["login"],  $auth["password"]);
  }

  public function newPost($wp_post){
    $response = $this->wpClient->newPost($wp_post["title"], $wp_post["content"]);
    return $response;
  }
  public function getPost($id){
    return $this->wpClient->getPost($id);
  }


  private function curl($url, $header, $body)
  {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,             $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,  1    );
    curl_setopt($ch, CURLOPT_POST,            1    );
    curl_setopt($ch, CURLOPT_POSTFIELDS,      $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER,      array($header, 'Content-Type: multipart/form-data'));

    $result=curl_exec ($ch);

    return $result;
  }

}