<?php

namespace App\Http\Controllers;

use App\Category;
use App\Post;
use Illuminate\Http\Request;

class CategorysController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function  index()
    {


        return view('admins.category');
    }

    public function store()
    {
//        dd("Hello");
        $data = request()->validate(
            [
                'category' => 'required',
            ]
        );


            try{

                $done = Category::create($data);

//                dd($done);
            }catch (\Exception $e){

                return view('admins.erros', compact('e')
                );

        }


        return redirect()->route('category.create')->with('addcategorystatus', 'Category added Successfully!');

    }

}
