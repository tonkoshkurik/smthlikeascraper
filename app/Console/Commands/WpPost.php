<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Sites;

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

    $url_array = parse_url("http://prokatavtofeodosia.ru/");
    $url  = $url_array["scheme"] ? $url_array["scheme"] : "http";
    $url .= "://";
    $url .= $url_array["host"];
    $url .= "/xmlrpc.php";

    # Create client instance
    $wpClient = new \HieuLe\WordpressXmlrpcClient\WordpressClient();

    # Set the credentials for the next requests
    $wpClient->setCredentials($url, 'admin',  'Yc(nQZjN5TgVftnhMp');

    $wpClient->newPost('New post from api', 'Hi there! <br> <h2>Yo-yo-yo!</h2>');
  }

}