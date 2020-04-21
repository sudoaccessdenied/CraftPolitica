<?php

namespace App\Http\Controllers;

use App\Category;
use App\SubCategory;
use Illuminate\Http\Request;

class SubCategoriesController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $data = SubCategory::all();


        return view('admins.viewsubcategory', compact('data'));

    }

    public function edit(SubCategory $data)
    {

        $cat = Category::all();
        return view('admins.editsubcategory', compact('data','cat'));

    }

    public function update(Request $request)
    {
        $requested = request()->validate([

                'category' => 'required',

                'subcategory'=>'required'
            ]
        );



        $mydata = SubCategory::find($request->data);

        $mydata->category_id= $requested['category'];
        $mydata->subcategory= $requested['subcategory'];

        $mydata->save();


        return redirect()->route('subcategory.index');



    }

    public function destroy(Request $request)
    {
        $mydata = SubCategory::find($request->data);
        $mydata->delete();

        return redirect()->route('subcategory.index')->with('deletesubstatus','SubCategory Successfully Deleted');


    }


    public function create()
    {

        $datas = Category::all();
        return view('admins.subcategory' ,compact('datas'));
    }

    public function show(Request $request)
    {
//        dd($request->data);
//        $data = SubCategory::find()
        $model = SubCategory::where('category_id', '=', $request->data)->get();

//        dd($model);
         return response()->json($model);;
    }


    public function store()
    {

        $data = request()->validate(
            [
                'subcategory' => 'required',
                'category' => 'required|not_in:0',
            ]
        );

//        dd($data);ss
        try{

            $done = SubCategory::create([
                'subcategory'=>$data['subcategory'],
                'category_id'=>$data['category']
            ]);

        }catch (\Exception $e){

            return view('admins.erros', compact('e')
            );

        }

        return redirect()->route('subcategory.create')->with('addsubcategorystatus','SubCategory Added Successfully');

    }


}

