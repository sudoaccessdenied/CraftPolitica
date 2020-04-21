<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function categorys()
    {
        return $this->belongsTo(Category::class,'category','id');
    }

    public function subcategorys()
    {
        return $this->belongsTo(SubCategory::class,'sub_category','id');
    }


}
