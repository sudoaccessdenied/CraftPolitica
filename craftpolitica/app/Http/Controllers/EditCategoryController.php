<?php

namespace App\Http\Controllers;

use App\Category;
use App\SubCategory;
use Illuminate\Http\Request;

class EditCategoryController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        $data = Category::all();
        return view('admins.viewcategory',compact('data'));
    }

    public function edit(Category $data)
    {

        return view('admins.editcategory', compact('data'));

    }

    public function update(Request $request)
    {
        $requested = request()->validate([

        'category'=>'required'
        ]
        );

        $mydata = Category::find($request->data);

        $mydata->category= $requested['category'];

        $mydata->save();


        return redirect()->route('editcategory.index');



    }

    public function destroy(Request $request)
    {
        $deletedRows = SubCategory::where('category_id', $request->data)->delete();
        $mydata = Category::find($request->data);
        $mydata->delete();

        return redirect()->route('editcategory.index')->with('status',"Category Deleted Successfully!");



    }



}
