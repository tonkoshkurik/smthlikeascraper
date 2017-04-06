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
    if(substr($remote, -1) == '/') {
      $remote = substr($remote, 0, -1);
    }
    $url_array = parse_url($remote);
    $url  = isset($url_array["scheme"]) ? $url_array["scheme"] : "http";
    $url .= "://";
    $url .= isset($url_array["host"]) ? $url_array["host"] : $remote;
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

  public function editPost($id, $content){
    return $this->wpClient->editPost($id, $content);
  }
  public function getPosts(){
    return $this->wpClient->getPosts();
  }
  public function getAuth(){
    return $this->wpClient->getAuth();
  }

}