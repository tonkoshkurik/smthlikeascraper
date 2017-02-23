<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // new
    protected $fillable = ['proxy', 'bulkapi'];
}
