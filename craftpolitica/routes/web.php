<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'FrontController@index');
Route::post('/feeds/show/', 'FeedController@show')->name('feed.show');
Route::get('/feed', 'FeedController@fetchFeed');


//----------------Front End Routes------------------

Route::get('/front/category/{category}', 'FrontController@byCategory')->name('front.category');
Route::get('/front/subcategory/{subcategory}', 'FrontController@bySubCategory')->name('front.subcategory');
Route::get('/front/post_url/{post_url}', 'FrontController@byPostUrl')->name('front.post_url');
Route::get('/front/search', 'FrontController@search')->name('front.search');
Route::get('/front/type/{type}', 'FrontController@byType')->name('front.type');







//------------------------------------------Admin routes-----------
Auth::routes();
//Post Controller
Route::post('/posts', 'PostController@store')->name('posts.store');
Route::get('/uploads', 'PostController@create')->name('uploads');
Route::get('/posts', 'PostController@index')->name('posts.index');
Route::get('/posts/{post}/edit', 'PostController@edit')->name('posts.edit');
Route::get('/posts/search', 'PostController@search')->name('posts.search');
Route::get('/posts/searchitem', 'PostController@result')->name('posts.result');
Route::put('/posts/{post}', 'PostController@update')->name('posts.update');
Route::delete('/posts/{post}', 'PostController@destroy')->name('posts.destroy');

//Exclusive Articles
Route::get('/exclusive', 'ExclusiveController@index')->name('exclusive.index');
Route::post('/exclusive', 'ExclusiveController@store')->name('exclusive.store');


//CategoryController
Route::get('/category/create', 'CategorysController@index')->name('category.create');
Route::post('/addcategory', 'CategorysController@store')->name('addcategory.store');
//\App\SubCategory::
Route::get('/subcategory/create', 'SubCategoriesController@create')->name('subcategory.create');
Route::get('/subcategory/{data}', 'SubCategoriesController@show')->name('subcategory.show');


Route::post('/addsubcategory', 'SubCategoriesController@store')->name('addsubcategory.store');
//Edit subcategory
Route::get('/subcategory', 'SubCategoriesController@index')->name('subcategory.index');
Route::get('/subcategory/{data}/edit', 'SubCategoriesController@edit');
Route::put('/subcategory/{data}', 'SubCategoriesController@update');
Route::delete('/subcategory/{data}', 'SubCategoriesController@destroy');

//Edit category
Route::get('/editcategory', 'EditCategoryController@index')->name('editcategory.index');
Route::get('/editcategory/{data}/edit', 'EditCategoryController@edit');
Route::put('/editcategory/{data}', 'EditCategoryController@update');
Route::delete('/editcategory/{data}', 'EditCategoryController@destroy');



//homeroute
Route::get('/home', 'PostController@index')->name('home');


//add category home controller
//Route::post('/addcategory', 'HomeController@store')->name('addcategory.store');
