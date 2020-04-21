<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use Illuminate\Http\Request;

class ExclusiveController extends Controller

{
    //

    public function index()
    {
        $categories = Category::all();
        return view('admins.exclusive',compact('categories'));
    }

    public function store()
    {
        $data = \request()->validate([

            'title' => 'required',
            'description' => '',
            'image'=>'image',
            'catagory' => 'required|not_in:0 ',
            'subcatagory' => 'required|not_in:0',
            'article' => 'required',
            'type' => 'required',
            'caption'=>''

        ]);



        if (\request('image')) {

            $imagePath = 'storage/'.request('image')->store('post_images', 'public');
        }

        $path = "news-" . md5($data['title']);


        $posts = Post::create(
            ['title' => $data['title'],
                'description' => $data['description'],
                'user_id' => auth()->user()->id,
                'category' => $data['catagory'],
                'sub_category' => $data['subcatagory'],
                'caption' => $data['caption'],
                'article' => $data['article'],
                'type' => $data['type'],
                'imgUrl' => $imagePath ?? '',
                'post_url' => $path,
                'author' => "Craft Politica :".auth()->user()->name,
            ]

        );


//        return view('front.details', compact('posts'));
        return redirect()->route('exclusive.index')->with('status', 'Article Successfully Uploaded');
    }
}
