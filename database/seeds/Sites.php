<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class Sites extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		//Create new Site for fetch
        DB::table('sites')->insert([
	        'site' => 'http://prokatavtofeodosia.ru/',
	        'login' => 'admin',
	        'password' => 'Yc(nQZjN5TgVftnhMp',
	        'site_to_fetch' => 'http://futureprocurement.net',
	        'fetch_settings' => 'rss'
        ]);

		//Create new Site for fetch
        DB::table('sites')->insert([
	        'site' => 'http://prokatavtofeodosia.ru/',
	        'login' => 'admin',
	        'password' => 'Yc(nQZjN5TgVftnhMp',
	        'site_to_fetch' => 'http://homegrownandhealthy.com',
	        'fetch_settings' => 'rss'
        ]);

        DB::table('sites')->insert([
	        'site' => 'http://prokatavtofeodosia.ru/',
	        'login' => 'admin',
	        'password' => 'Yc(nQZjN5TgVftnhMp',
	        'site_to_fetch' => 'http://nstacommunities.org/blog',
	        'fetch_settings' => 'rss'
        ]);

        DB::table('sites')->insert([
	        'site' => 'http://prokatavtofeodosia.ru/',
	        'login' => 'admin',
	        'password' => 'Yc(nQZjN5TgVftnhMp',
	        'site_to_fetch' => 'http://www.teampriorityhealth.com',
	        'fetch_settings' => 'rss'
        ]);
    }
}
