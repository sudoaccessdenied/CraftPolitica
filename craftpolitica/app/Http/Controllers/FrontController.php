<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use App\SubCategory;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    //


    public function index()
    {
        $category = Category::all();
        $exclusive=Post::where('type',3)->latest()->take(10)->get();
//        dd($exclusive);
        return view('home' ,compact('category','exclusive'));
    }

    public function byType()
    {
        $data=Post::where('type',\request()->type)->latest()->simplepaginate(18);


        if (\request()->type == 1) {
            $cat = 'Posts';
        } elseif (\request()->type == 2) {
            $cat = 'Banner';
        }else
            $cat = 'Exclusive';

        return view('front.card_category', compact('data','cat'));
    }

    public function byCategory(Request $request)
    {

        $data = Post::where('category', $request->category)->orderBy('created_at','desc')->simplepaginate(18);
        $cat = Category::findorFail($request->category)->category;
        return view('front.card_category', compact('data','cat'));
    }


    public function bySubCategory(Request $request)
    {

        $data = Post::where('sub_category', $request->subcategory)->orderBy('created_at','desc')->simplepaginate(18);
        $cat = SubCategory::findorFail($request->subcategory)->subcategory;
        return view('front.card_category', compact('data','cat'));
    }


    public function byPostUrl(Request $request)
    {
        $posts = Post::where('post_url', $request->post_url)->get()[0];

//        dd($posts);
        return view('front.details', compact('posts'));
    }

    public function search(Request $request)
    {
        $data = Post::where('title','like','%'.$request->search.'%')->orderBy('created_at','desc')->simplepaginate(15);
        $cat=$request->search;
        return view('front.card_category', compact('data','cat'));
    }
}
