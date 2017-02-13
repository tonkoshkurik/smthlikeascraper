<?php

Route::get('/', function () {
  return view('login');
});

Auth::routes();

// Route::get('/home', 'HomeController@index');

Route::group(['middleware' => 'auth'], function () {
  // Route::get('/home', 'HomeController@index');
  Route::get('/home', 'SitesController@index');

  Route::get('/dashboard', 'SitesController@index');

  Route::post('/sites/create', 'SitesController@store')->name("addsite");

  Route::get('/sites', 'SitesController@index')->name("sites");

  Route::get('/sites/create', 'SitesController@create');
  Route::get('/sites/delete/{id}', 'SitesController@destroy')->name('siteDelete');

  Route::get('/sites/show/{id}', 'SitesController@show')->name('showFetchResult');
  
  Route::get('/sites/connect/{id}', 'SitesController@connectToWp')->name('connectToWp');
  Route::get('/callback', 'SitesController@callback');

  Route::get('/scrape/all', 'ScrapeController@all');
  Route::get('/scrape/save', 'ScrapeController@saveToWp');

  Route::get('/scrape/sendToWp', 'ScrapeController@sendToWp')->name('sendToWp');

});
