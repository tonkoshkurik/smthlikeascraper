<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScrapedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scrapeds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->string('link');
            $table->string('title');
            $table->integer('saved')->unsigned()->nullable();
            $table->integer('bulka')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('site_id')->unsigned()
              ->references('id')->on('sites')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scrapeds');
    }
}
