<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //

    protected $guarded = [];

    public function subcategorys()
    {
        return $this->hasMany('\App\SubCategory');
    }

    public function posts()
    {
        return $this->hasMany('\App\Post','category','id')->latest()->take(5);
    }
}
