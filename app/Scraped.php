<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scraped extends Model
{
	//
	protected $fillable = ['site_id', 'link', 'title', 'saved'];
	// protected $table = 'scrapeds';
}
