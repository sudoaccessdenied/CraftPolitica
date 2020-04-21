<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('title' ,191);
            $table->string('description',500)->nullable();
            $table->longText('article');
            $table->unsignedBigInteger('category');
            $table->unsignedInteger('sub_category');
            $table->string('author',50);
            $table->string('caption',500)->nullable();
            $table->string('post_url',500);
            $table->string('imgUrl',500);
            $table->integer('type');
            $table->timestamps();
//            $table->foreign('category')->references('id')->on('categories');


            $table->index(['title','id','category']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
