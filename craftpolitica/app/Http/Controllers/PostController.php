<?php

namespace App\Http\Controllers;


use App\Category;
use App\Post;
use Illuminate\Http\Request;
use Goutte\Client;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    //


    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $data = Post::orderBy('created_at', 'desc')->simplepaginate(15);
//        $data = Post::all();

        return view('admins.posts', compact('data'));

    }

    public function search()
    {

        $categories = Category::all();
        return view('admins.searchposts', compact('categories'));

    }

    public function result()
    {


//        dd(request()->all());
        $categories = Category::all();

//        $data = \request()->validate([
//            'category' => '',
//            'subcategory' => '',
//            'type' => '',
//            'fromDate' => '',
//
//            'toDate' => '',
//            'keyword' => '',
//        ]);
//        dd(request()->category );
        $data = false;
        if (request()->category != 0) {

            $data = Post::where(
                'category', '=', \request()->category
            )->
            orderBy('created_at', 'desc')->simplepaginate(15);
        } elseif (\request()->type != 0) {

            $data = Post::where(
                'type', '=', \request()->type
            )->
            orderBy('created_at', 'desc')->simplepaginate(15);

        } elseif (strlen(\request()->keyword) >= 4) {

            $data = Post::where('title', 'like', '%' . \request()->keyword . '%')->
            orderBy('created_at', 'desc')->simplepaginate(15);

        } elseif (isset(request()->fromDate) && isset(request()->toDate)) {
            $fromDate = date(request()->fromDate);
            $toDate = date(request()->toDate);
            $data = Post::whereBetween('created_at', [$fromDate, $toDate])->
            orderBy('created_at', 'desc')->simplepaginate(15);

        }


//
//        dd(array_merge(
//
//            ['title', 'like', '%' . \request()->keyword . '%'], $catArray ?? []
//
//        ));

//        dd($data);
        return view('admins.searchposts', compact('data', 'categories'));

    }

    public function destroy(Request $request)
    {
        $posts = Post::find($request->post);

        try {
            unlink($posts->imgUrl);
        } catch (\Exception $exception) {

        }
        $posts->delete();
        return redirect()->route('posts.index')->with('status', 'Post Id :' . $posts->id . '  Deleted Successfully ');


    }

    public function edit(Request $request)
    {
        $post = Post::find($request->post);
//        dd($post);
        $categories = Category::all();
        return view('admins.editpost', compact('post', 'categories'));
    }

    public function update(Request $request)
    {


//        dd($request->post);
        $data = \request()->validate([

            'title' => 'required',
            'description' => '',
            'image' => 'image',
            'catagory' => 'required',
            'subcatagory' => 'required',
            'article' => 'required',
            'type' => 'required',
            'caption' => ''

        ]);
        if (\request('image')) {

            $imagePath = 'storage/' . request('image')->store('post_images', 'public');
            $imageArray = ['imgUrl' => $imagePath];
            $posts = Post::find($request->post);
            unlink($posts->imgUrl);
        }

        $dataArray = ['title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['catagory'],
            'sub_category' => $data['subcatagory'],
            'caption' => $data['caption'],
            'article' => $data['article'],
            'type' => $data['type'],
        ];


        $posts = Post::where('id', $request->post)->update(
            array_merge($dataArray, $imageArray ?? [])
        );

//       // return view('front.details', compact('posts'));
        return redirect()->route('posts.index')->with('status', 'Article Updated  Successfully ');
    }

    public function create()
    {
        $categories = Category::all();

        return view('admins.uploads', compact('categories'));
    }


    public function store()
    {
        $data = request()->validate(
            [
                'catagory' => 'required|not_in:0 ',
                'subcatagory' => 'required|not_in:0',
                'link' => 'required',
                'type' => 'required'

            ]
        );

        $message = '';
        $arrayoflinks = explode(",\r\n", $data['link']);


        $client = new Client();
        foreach ($arrayoflinks as $link) {


            if (strpos($link, 'hindustantimes.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    try {

                        $h1 = $crawler->filter('article.story-article>div.story-highlight>h1')->extract("_text")[0];

                    } catch (\Exception $exception  ) {

                        $h1 = "No Heading found";
                    }


                    $h2 = $crawler->filter('.story-article  > .story-highlight >  h2')->extract("_text")[0];
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    $imagePathToDownload = $crawler->filter('.story-lg-img>.thumbnail>img')->extract('src')[0];
                    $caption = trim($crawler->filter('.img-captionh2')->extract('_text')[0],"\t\n\r\0\x0B");


                    $storyDetails = $crawler->filter('.story-details  ')->html();



                    $storyDetails = substr($storyDetails, 0, strpos($storyDetails, "First Published:"));


                    $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                    $name = "post_images/" . $name;
                    file_put_contents($name, file_get_contents($imagePathToDownload));

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => "Hindustan Times",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//                return view('front.details', compact('posts'));


            } else if (strpos($link, 'northeasttoday.in')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.content-article-title >  h1')->text();
                    $h2 = $crawler->filter('span.breadcrumb_last')->text();
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                    $imagePathToDownload =   $crawler->filter('.main-content-left >.article-photo >span> img  ')->extract('src')[0];
                    $imagePathToDownload = $crawler->filter('img')->each(function ($node) {
                        return $node->attr('src');
                    })[1];
//                    dd($imagePathToDownload);

                    $caption = preg_replace('[\t]', '', $crawler->filter('span.breadcrumb_last')->html());


                    $storyDetails = $crawler->filter('.shortcode-content > p ')->each(function ($node) {

                        return $node->html();
                    });

                    $crawlFrom = "<p>News Source: northeasttoday.in</p>";
                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;
//                    dd(implode("<p>",$storyDetails));

                    $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                    $name = "post_images/" . $name;

                    file_put_contents($name, file_get_contents($imagePathToDownload));

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'nenow.in')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.jeg_post_title')->text();
                    $h2 = $crawler->filter('.jeg_post_subtitle')->text();
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                    $imagePathToDownload =   $crawler->filter('.wp-post-image ')->extract('src');
                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                        return $node->attr('data-src');
                    })[0];
//                    dd($imagePathToDownload);


                    $caption = preg_replace('[\t]', '', $crawler->filter('.wp-caption-text')->html());


                    $storyDetails = $crawler->filter('.content-inner > p ')->each(function ($node) {

                        return $node->html();
                    });

                    $crawlFrom = "<p>News Source: nenow.in</p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;
//                    dd(implode("<p>",$storyDetails));

//                    $storyDetails = substr($storyDetails, 0, strpos($storyDetails,"First Published:"));

                    $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                    $name = "post_images/" . $name;

                    file_put_contents($name, file_get_contents($imagePathToDownload));

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'northeastindia24.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-header>.entry-title')->text();

                    $h2 = $crawler->filter('.entry-content >p>span')->text();
//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    $imagePathToDownload = $crawler->filter('.featured-image > a')->extract('href')[0];
//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];
//                   dd($imagePathToDownload);


                    $caption = preg_replace('[\t]', '', $crawler->filter('.entry-content >p>span')->text());

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();
                    $article = $crawler->filter('.entry-content > p ')->each(function ($node) {
                        return $node->html();
                    });

                    for ($i = 1; $i < count($article); $i++) {
                        $storyDetails[$i - 1] = $article[$i];
                    }


                    $crawlFrom = "<p><cite>News Source: northeastindia24.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;
//
//                    dd($storyDetails);

                    $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                    $name = "post_images/" . $name;

                    file_put_contents($name, file_get_contents($imagePathToDownload));

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

            } else if (strpos($link, 'theshillongtimes.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.single-post-title > .post-title ')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('.single-post-content > h4')->text();

                    } catch (\Exception $exception) {
                        $h2 = $crawler->filter('.single-post-title > .post-title ')->text();

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {


                        $imagePathToDownload = $crawler->filter('.post-thumbnail ')->extract('href')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }


//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '', $crawler->filter('figcaption.wp-caption-text')->text());

//                            dd($caption);
                    } catch (\Exception $e) {
                        $caption = preg_replace('[\t]', '', $crawler->filter('.single-post-title > .post-title ')->text());

                    }


//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();
                    $article = $crawler->filter('.single-post-content> p')->each(function ($node) {
                        return $node->html();
                    });

                    for ($i = 1; $i < count($article); $i++) {
                        $storyDetails[$i - 1] = $article[$i];
                    }


                    $crawlFrom = "<p><cite>News Source: theshillongtimes.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//                   // return view('front.details', compact('posts'));


            } else if (strpos($link, 'indianexpress.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.m-story-header__title')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('[itemprop=\'articleBody\'] p, h2')->text();
//                            dd($h2);
                    } catch (\Exception $exception) {
                        $h2 = $h1;
                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {


                        $imagePathToDownload = $crawler->filter('.custom-caption > .wp-caption  > img ')
                            ->extract('data-src')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }


//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('.wp-caption-text')->text());

//                            dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }


//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();
                    $storyDetails = $crawler->filter('[itemprop=\'articleBody\'] p')->each(function ($node) {
                        return $node->html();
                    });

//
//                        dd($article);
//                        for ($i = 1;$i<count($article);$i++) {
//                            $storyDetails[$i - 1] = $article[$i];
//                        }
//


                    $crawlFrom = "<p><cite>News Source: indianexpress.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


            } else if (strpos($link, 'livemint.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('h1')->text();
//                        dd($h1);

                    try {
                        $h2 = "<ul>" . $crawler->filter('.highlights ')->html() . "</ul>";

                    } catch (\Exception $exception) {
                        $h2 = $h1;
                    }

//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {


                        $imagePathToDownload = $crawler->filter('figure >img')
                            ->extract('data-src')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }


//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('.figcaption')->text());

                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h1;

                    }


//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();
                    $storyDetails = $crawler->filter('.mainArea > p')->each(function ($node) {
                        return $node->html();
                    });

//
//                    dd($storyDetails);
//                        for ($i = 1;$i<count($article);$i++) {
//                            $storyDetails[$i - 1] = $article[$i];
//                        }
//


                    $crawlFrom = "<p><cite>News Source: livemint.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'thehindu.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('h1.title ')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('h2.intro ')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = $h1;
                    }

//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {


                        $imagePathToDownload = $crawler->filter('picture > source')
                            ->extract('srcset')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name . "jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }


//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('.lead-img-caption >p')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h1;

                    }


//                    $storyDetails = $crawler->filter('.row>.col-xs-12>.article>div')->html();
                    $storyDetails = $crawler->filter('  .row>.col-xs-12>.article>div>div>p')->each(function ($node) {
                        return $node->html();
                    });


//                    dd($storyDetails);
//                        for ($i = 1;$i<count($article);$i++) {
//                            $storyDetails[$i - 1] = $article[$i];
//                        }
//


                    $crawlFrom = "<p><cite>News Source:thehindu.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'ptinews.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.fulstoryheading')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('h2.intro ')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }

//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $urlConcat . $crawler->filter('.fullstorydivaudiovideo >img ')
                                ->extract('src')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name . "jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }


//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('.lead-img-caption >p')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


                    $storyDetails = $crawler->filter('.fulstorytext')->html();
//                    $storyDetails = $crawler->filter('  .row>.col-xs-12>.article>div>div>p')->each(function ($node) {
//                        return $node->html();
//                    });


//                    dd($storyDetails);
//                        for ($i = 1;$i<count($article);$i++) {
//                            $storyDetails[$i - 1] = $article[$i];
//                        }
//


                    $crawlFrom = "<p><cite>News Source: ptinews.com</cite></p>";

//                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'aninews.in')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('h1.title')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('h2.intro ')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }

//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $crawler->filter('header>div.img-container > img ')
                            ->extract('src')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name . "jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }


//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('header > small')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
//                    $storyDetails = $crawler->filter('  .row>.col-xs-12>.article>div>div>p')->each(function ($node) {
//                        return $node->html();
//                    });


//                    dd($storyDetails);
//                        for ($i = 1;$i<count($article);$i++) {
//                            $storyDetails[$i - 1] = $article[$i];
//                        }
//


                    $crawlFrom = "<p><cite>News Source: aninews.in</cite></p>";

//                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'sentinelassam.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-title.h1')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('h2.intro ')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }

//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $crawler->filter('.herald-post-thumbnail-single>span>img')
                            ->extract('data-src')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }

//                  dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('header > small')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $article = $crawler->filter('  .entry-content>p')->each(function ($node) {
                        return $node->html();
                    });


                    for ($i = 1; $i < count($article); $i++) {
                        $storyDetails[$i - 1] = $article[$i];
                    }
//                    dd($storyDetails);
//


                    $crawlFrom = "<p><cite>News Source: sentinelassam.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'thejakartapost.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.title-large')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('h2.intro ')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }

//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $crawler->filter('.bannerHeadSingle >img')
                            ->extract('src')[0];

//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }

//                  dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('.created')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('  .show-define-text>p')->each(function ($node) {
                        return $node->html();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);
//


                    $crawlFrom = "<p><cite>News Source:thejakartapost.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'khmertimeskh.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-title')->text();
//                        dd($h1);

                    try {
                        $h2 = $crawler->filter('h2.intro ')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $crawler->filter('.entry-thumb >a.ci-lightbox')
                            ->extract('href')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }

//                  dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('.ccfic>span.ccfic-text')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.entry-content>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);
//


                    $crawlFrom = "<p><cite>News Source:khmertimeskh.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'phnompenhpost.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.section.section-width-sidebar.single-article-header>h2')->text();
                    $h1 = trim($h1, "\n\t\0\x0B ");


//                    dd($h1);

                    try {
                        $h2 = $crawler->filter('h2.intro ')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $crawler->filter('.node-image.align-right.node-main-image
                        >a.shadow-box-item')
                            ->extract('href')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {
                        $name = "img/la.jpg";

                    }

//                  dd($name);
//                        article-headlinediv.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('.node-main-image-caption-inner')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.paragraph-style>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);
//


                    $crawlFrom = "<p><cite>News Source:phnompenhpost.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'bangkokpost.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('article>div.article-headline>h2')->text();
                    $h1 = trim($h1, "\n\t\0\x0B ");


//                    dd($h1);

                    try {
                        $h2 = $crawler->filter('article>div.article-headline>p')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $crawler->filter('div.box-img>figure>img')
                            ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }

//                  dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('figcaption')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('div.articl-content >p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);
//


                    $crawlFrom = "<p><cite>News Source:bangkokpost.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'malaymail.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('section>div>div>div>h1')->text();
                    $h1 = trim($h1, "\n\t\0\x0B ");


//                    dd($h1);

                    try {
                        $h2 = $crawler->filter('article>div.article-headline>p')->text();
                        $h2 = trim($h2, "\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "http://www.ptinews.com/";
                        $imagePathToDownload = $crawler->filter('article>figure.figure.float-left>img')
                            ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }

//                  dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('figcaption.figure-caption')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('article >p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);
//


                    $crawlFrom = "<p><cite>News Source:malaymail.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'thestar.com.my')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.headline.story-pg>h1')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");


//                    dd($h1);

                    try {
                        $h2 = $crawler->filter('adfafda')->text();
                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

                        $urlConcat = "https://www.thestar.com.my";
                        $imagePathToDownload = $urlConcat . $crawler->filter('.story-image>img')
                                ->extract('data-thumb-img')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, 'online'), strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));

                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('figcaption.figure-caption')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.story.relative >p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:thestar.com.my</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


//               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'straitstimes.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('h1.headline.node-title')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");


//                    dd($h1);

                    try {
                        $h2 = null;
//                        $h2 = $crawler->filter('adfafda')->text();
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "https://www.straitstimes.com";
                        $imagePathToDownload = $crawler->filter('figure>picture>img')
                            ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));

                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption = preg_replace('[\t]', '',
                            $crawler->filter('figcaption.group-image-caption.field-group-html-element>span.caption-text')->text());

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('[itemprop="articleBody"]>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:straitstimes.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'vnanet.vn')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.control-list-item>a.buyTopNews')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");


//                    dd($h1);

                    try {
                        $h2 = null;
//                        $h2 = $crawler->filter('adfafda')->text();
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

                        $urlConcat = "https://www.vnanet.vn";
                        $imagePathToDownload = $urlConcat . $crawler->filter('.thumb-slider.thumb-img>img')
                                ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));

                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));
//
                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption =
                            $crawler->filter('.caption-image-grl')->text();

                        $caption = trim($caption, "\r\n\t\0\x0B ");

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.sample-grl.scrollbar>div ')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:vnanet.vn</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'japantimes.co.jp')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('header>hgroup>h1')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");


//                    dd($h1);

                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('.article__sub-title>span.ezstring-field')->text();
                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

//                        $urlConcat = "https://www.japantimes.co.jp";
                        $imagePathToDownload = $crawler->filter('.gallery >figure>img')
                            ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));

                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));
//
                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption =
                            $crawler->filter('figcaption.padding_block')->text();

                        $caption = trim($caption, "\r\n\t\0\x0B ");

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.padding_block>.entry>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:japantimes.co.jp</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'theaseanpost.com')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('header.single-post-heading>h1>div.title-desc.title-sumary')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");


//                    dd($h1);

                    try {
                        $h2 = null;
//                        $h2 = $crawler->filter('.article__sub-title>span.ezstring-field')->text();
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {

                        $urlConcat = "https://theaseanpost.com";
                        $imagePathToDownload = $urlConcat . $crawler->filter('figure.border-img>img')
                                ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));
//
                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {

                        $caption =
                            $crawler->filter('figcaption>.char_Caption')->text();

                        $caption = trim($caption, "\r\n\t\0\x0B ");

//                        dd($caption);
                    } catch (\Exception $e) {
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.single-post-content>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:theaseanpost.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'mizzima.com')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.news-details>.news-details-title')->extract('_text')[0];
//                    dd($h1);
                    $h1 = trim($h1, "\r\n\t\0\x0B ");


                    try {
                        $h2 = null;
//                        $h2 = $crawler->filter('.article__sub-title>span.ezstring-field')->text();
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {


                        $imagePathToDownload = $crawler->filter('.image-style-news-category-large-image')
                            ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));
//
                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
////
//                        $caption =
//                            $crawler->filter('.caption p')->text();
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
                        $caption = null;
//                        dd($caption);

                    } catch (\Exception $e) {
                        dd($e);
                        $caption = null;

                    }


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.field-item.even>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:mizzima.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'dhakatribune.com')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.report-left.report-mainhead>h1')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('.highlighted-content>p')->text();
                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {
                        $imagePathToDownload = $crawler->filter('.reports-big-img.details-reports-big-img>img')
                            ->extract('src')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
////
                        $caption =
                            $crawler->filter('.media-caption')->extract('_text')[0];
                        $caption = trim($caption, "\r\n\t\0\x0B ");
//                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.report-content.fr-view>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:dhakatribune.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'rappler.com')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('h1.select-headline')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('p.select-metadesc')->text();
                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {
                        $imagePathToDownload = $crawler->filter('div.storypage-divider.desktop>p>img')
                            ->extract('data-original')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {
                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
////
                        $caption =
                            $crawler->filter('.storypage-divider.desktop p.caption')->extract('_text')[0];
                        $caption = trim($caption, "\r\n\t\0\x0B ");
//                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.storypage-divider.desktop p.p1')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:rappler.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'smh.com.au')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('[itemprop="headline"]')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
                        $h2 = null;
//                        $h2 = $crawler->filter('')->text();
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {
                        $imagePathToDownload = $crawler->filter('figure>a>div>picture>img')
                            ->extract('img')[0];
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

//                        throw new \Exception("No Image");

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
//////
//                        $caption =
//                            $crawler->filter('asdfasfd')->extract('_text')[0];
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('article>section')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:smh.com.au</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'postcourier.com.pg')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('#hero-single-content>h1')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('.hide-on-mobile-only')->text();
                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {
                        $imagePathToDownload = $crawler->filter('.hero')
                            ->extract('style')[0];
                        $imagePathToDownload = substr($imagePathToDownload,
                            strpos($imagePathToDownload, '(') + 1, strlen($imagePathToDownload) - 24);
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

//                        throw new \Exception("No Image");

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
//////
//                        $caption =
//                            $crawler->filter('asdfasfd')->extract('_text')[0];
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('section.entry-content.cf>p')->each(function ($node) {
                        return $node->html();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:postcourier.com.pg</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'jamestown.org')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('header.entry-header>h1')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('header.entry-header>h2')->text();
                        $h2 = trim($h2, "\r\n\t\0\x0B ");
                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);

                    try {
                        $urlConcat = "https://jamestown.org";
                        $imagePathToDownload = $urlConcat . $crawler->filter('.entry-image>img')
                                ->extract('src')[0];
//                        $imagePathToDownload = substr($imagePathToDownload,
//                            strpos($imagePathToDownload, '(')+1, strlen($imagePathToDownload)-24);
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

//                        throw new \Exception("No Image");

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
//////
//                        $caption =
//                            $crawler->filter('asdfasfd')->extract('_text')[0];
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.entry-content>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:jamestown.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'forbes.com')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.fs-headline.speakable-headline.font-base')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('.wp-caption-text')->extract('_text')[0];
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
//                        dd($h2);
//                        $h2 =[$h2[0],$h2[1]];


                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);


                    try {
//                        $urlConcat = "https://scmp.com";
                        $imagePathToDownload = $crawler->filter('.article-body-image>progressive-image')
                            ->extract('src')[0];
//                        $imagePathToDownload = substr($imagePathToDownload,
//                            strpos($imagePathToDownload, '(')+1, strlen($imagePathToDownload)-24);
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

//                        throw new \Exception("No Image");

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
//////
//                        $caption =
//                            $crawler->filter('asdfasfd')->extract('_text')[0];
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.article-container.color-body.font-body>div>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:forbes.com/</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'thescoop.co')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-title-primary')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('.entry-subtitle')->extract('_text')[0];
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
//                        dd($h2);
//                        $h2 =[$h2[0],$h2[1]];


                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);


                    try {
//                        $urlConcat = "https://scmp.com";
                        $imagePathToDownload = $crawler->filter('.post-image>img')
                            ->extract('src')[0];
//                        $imagePathToDownload = substr($imagePathToDownload,
//                            strpos($imagePathToDownload, '(')+1, strlen($imagePathToDownload)-24);
//                        dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

//                        throw new \Exception("No Image");

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
//////
                        $caption =
                            $crawler->filter('.wp-caption-text')->extract('_text')[0];
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
//                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('.post-content >p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 1;$i<count($article);$i++) {
//                        $storyDetails[$i - 1] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:thescoop.co/</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'janes.com')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('#main-column>#article>div>h1')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('.entry-subtitle')->extract('_text')[0];
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
//                        dd($h2);
//                        $h2 =[$h2[0],$h2[1]];


                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);


                    try {
                        $urlConcat = "https://www.janes.com";
                        $imagePathToDownload = $urlConcat . $crawler->filter('.image-container>img')
                                ->extract('src')[0];
//                        $imagePathToDownload = substr($imagePathToDownload,
//                            strpos($imagePathToDownload, '(')+1, strlen($imagePathToDownload)-24);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        dd($imagePathToDownload);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

//                        throw new \Exception("No Image");

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
//////
                        $caption =
                            $crawler->filter('.image-caption')->extract('_text')[0];
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
//                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $article = $crawler->filter('.tweet-it>p')->each(function ($node) {
                        return $node->text();
                    });


                    for ($i = 0; $i < count($article) - 1; $i++) {
                        $storyDetails[$i] = $article[$i];
                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:janes.com/</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'inquirer.net')) {

                try {
                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-title')->extract('_text')[0];
                    $h1 = trim($h1, "\r\n\t\0\x0B ");
//                    dd($h1);


                    try {
//                        $h2 = null;
                        $h2 = $crawler->filter('.entry-subtitle')->extract('_text')[0];
//                        $h2 = trim($h2, "\r\n\t\0\x0B ");
//                        dd($h2);
//                        $h2 =[$h2[0],$h2[1]];


                    } catch (\Exception $exception) {
                        $h2 = null;
                    }


//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
//                        dd($h2);


                    try {
//                        $urlConcat = "https://www.janes.com";
                        $imagePathToDownload = $crawler->filter('.wp-caption>img')
                            ->extract('src')[0];
//                        $imagePathToDownload = substr($imagePathToDownload,
//                            strpos($imagePathToDownload, '(')+1, strlen($imagePathToDownload)-24);
                        $name = substr($imagePathToDownload, 0, strrpos($imagePathToDownload, '/') + 1);
//                        dd($imagePathToDownload);
//                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . md5($name) . ".jpg";
                        file_put_contents($name, file_get_contents($imagePathToDownload));

//                        throw new \Exception("No Image");

                    } catch (\Exception $e) {
//                        dd($e);
                        $name = "img/la.jpg";

                    }

//                    dd($name);
//                        dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
//////
                        $caption =
                            $crawler->filter('.wp-caption-text')->extract('_text')[0];
//                        $caption = trim($caption, "\r\n\t\0\x0B ");
//                        $caption = null;

                    } catch (\Exception $e) {
//                        dd($e);
                        $caption = null;

                    }
//                    dd($caption);


//                    $storyDetails = $crawler->filter('div.content[itemprop] > p')->html();
                    $storyDetails = $crawler->filter('#article_content>div>p')->each(function ($node) {
                        return $node->text();
                    });


//                    for ($i = 0;$i<count($article)-1;$i++) {
//                        $storyDetails[$i ] = $article[$i];
//                    }
//                    dd($storyDetails);


                    $crawlFrom = "<p><cite>News Source:inquirer.net/</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

//                    dd($storyDetails);

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => $data['type'],
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica :" . auth()->user()->name,
                        ]
                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }

//               // return view('front.details', compact('posts'));
            } else if (strpos($link, 'raajje.mv')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.article-heading ')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.mb-1')->text();

                    } catch (\Exception $exception) {
                        $h2 = $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.article-image > img')->extract('src')[0];


//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '', $crawler->filter('.text-muted')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    //dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.article-content-english  ')->each(function ($node) {

                        return $node->html();
                    });

                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.raajje.mv</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                //// return view('front.details', compact('posts'));


            } else if (strpos($link, 'colombopage.com/')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.headB ')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.mb-1')->text();

                    } catch (\Exception $exception) {
                        $h2 = $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('[border="1"]')->extract('src')[0];


                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);
                    // dd($imagePathToDownload);
//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '', $crawler->filter('.text-muted')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    //dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.newsbody >p ')->each(function ($node) {

                        return $node->html();
                    });

                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.colombopage.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                //// return view('front.details', compact('posts'));


            } else if (strpos($link, 'slguardian.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.single-post-title ')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.post-body > h3')->text();

                    } catch (\Exception $exception) {
                        $h2 = $h1;

                    }
                    // dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');

                    try {

                        $imagePathToDownload = $crawler->filter(' .separator>a>img')->extract('src');

                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '', $crawler->filter('.text-muted')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    //dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.post-body >div')->each(function ($node) {

                        return $node->html();
                    });

                    // dd($article);
                    // for ($i = 1;$i<count($article);$i++) {
                    //     $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.slguardian.org</cite></p>";

                    $storyDetails = "<p>" . implode("", $storyDetails) . "</p>" . $crawlFrom;
                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'colombotelegraph.com')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-title ')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.mb-1')->text();

                    } catch (\Exception $exception) {
                        $h2 = $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $image = $crawler->filter('a>img')->extract('src');
                        $imagePathToDownload = $image[3];


                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '', $crawler->filter('.text-muted')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    //dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.pf-content >p ')->each(function ($node) {

                        return $node->html();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.colombotelegraph.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                //// return view('front.details', compact('posts'));


            }   else if (strpos($link, 'seychellesnewsagency.com')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('article >h1')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.mb-1')->text();

                    } catch (\Exception $exception) {
                        $h2 = $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.article_photo>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($image);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.article_photo >p')->each(function ($node) {
                            return $node->text();
                        })[1];


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    //dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('#textsize >p ')->each(function ($node) {

                        return $node->text();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.seychellesnewsagency.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                ////return view('front.details', compact('posts'));

            } else if (strpos($link, 'thediliweekly.com')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.article-header>.module-title>h1')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.item-page>p')->text();

                    } catch (\Exception $exception) {
                        $h2 = $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = "http://thediliweekly.com" . $crawler->filter('.rt-block >.title6 >#rt-mainbody>.component-content>.item-page>.pull-left>img')->extract('src')[0];
                        // dd($imagePathToDownload);
                        // $imagePathToDownload=$image[3];

                        // $imagePathToDownload = $crawler->filter('.rt-block >.title6 >#rt-mainbody>.component-content>.item-page>.pull-left>img')->each(function ($node) {
                        // return  $node->html();
                        // });

                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;


                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.pull-left >figcaption')->text();
                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $article = $crawler->filter('.item-page >p ')->each(function ($node) {

                        return $node->text();
                    });
//
                    // dd($storyDetails);
                    for ($i = 1; $i < count($article); $i++) {
                        $storyDetails[$i - 1] = $article[$i];
                    }


                    $crawlFrom = "<p><cite>News Source: www.thediliweekly.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                //// return view('front.details', compact('posts'));


            } else if (strpos($link, 'thedailystar.net')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('[itemprop=headline]')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.panel-pane.pane-top>h2.h5.margin-bottom-zero>em')->text();

                    } catch (\Exception $exception) {
                        $h2 = $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.featured-image.lg-gallery>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.featured-image.lg-gallery.margin-bottom-big>.caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.field-body >p ')->each(function ($node) {

                        return $node->text();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.thedailystar.net</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                //// return view('front.details', compact('posts'));

            } else if (strpos($link, 'sputniknews.com')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.b-article__header-title>h1')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.b-article__lead>p')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.b-article__header>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        //$name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.featured-image.lg-gallery.margin-bottom-big>.caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.b-article__text >p ')->each(function ($node) {

                        return $node->text();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.sputniknews.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                ////return view('front.details', compact('posts'));

            } else if (strpos($link, 'asiatimes.com')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.heading.less-space')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.sub-heading>p')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.image-holder.p-t-15>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        //$name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.photo-credit-container.below>.photo-credit>p')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.article-text>p ')->each(function ($node) {

                        return $node->text();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.asiatimes.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                //// return view('front.details', compact('posts'));

            } else if (strpos($link, 'worldpoliticsreview.com')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.header-news>h2')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.sub-heading>p')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.pull-right.image>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        //$name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.styles-text>p ')->each(function ($node) {

                        return $node->html();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: worldpoliticsreview.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


                //// return view('front.details', compact('posts'));

            } else if (strpos($link, 'futuredirections.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.innerpage-head')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.author-name')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.pull-right.image>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        //$name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.single-rightpubpost.pub-singlelist>p ')->each(function ($node) {

                        return $node->html();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: futuredirections.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'aspistrategist.org.au')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-title')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.author-name')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.entry-content>figure>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        //$name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.entry-content>p ')->each(function ($node) {

                        return $node->html();
                    });
//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: aspistrategist.org.au</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'lowyinstitute.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.article-title')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.article-summary')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload = $crawler->filter('.article-main-image>img')->extract('src')[0];
                        // $imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        //$name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.field-item >div> p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: lowyinstitute.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'nationalinterest.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.detail__title')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.detail__sub')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        $constant = "https://nationalinterest.org";
                        $imagePathToDownload = $constant . $crawler->filter('.detail-hero>img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.detail__content> p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: nationalinterest.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'warontherocks.com')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.wotr_title>h1')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.detail__sub')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://nationalinterest.org";
                        $imagePathToDownload = $crawler->filter('.wotr_lede_img > img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.wotr_content> p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: warontherocks.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }


               // return view('front.details', compact('posts'));


            } else if (strpos($link, 'amti.csis.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.entry-title')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.detail__sub')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://nationalinterest.org";
                        $imagePathToDownload = $crawler->filter('.image-container>img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.entry-content > p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: amti.csis.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }
               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'reconnectingasia.csis.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.featured')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.detail__sub')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://nationalinterest.org";
                        $imagePathToDownload = $crawler->filter('.featured_image>figure>img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.body> p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: reconnectingasia.csis.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }
                //// return view('front.details', compact('posts'));

            } else if (strpos($link, 'idsa.in')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.heading-bar ')->text();
                    // dd($h1);

                    try {
                        $h2 = $crawler->filter('.detail__sub')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        $constant = "https://idsa.in";
                        $imagePathToDownload = $constant . $crawler->filter('.idsacomment>img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    } // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.field-name-body>.field-items >.field-item>p')->html();


                    $storyDetails = $crawler->filter('.field-name-body>.field-items >.field-item>p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: idsa.in</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }
               // return view('front.details', compact('posts'));

            }  else if (strpos($link, 'cfr.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.article-header__title')->text();
                    // dd($h1);
                    try {
                        $h2 = $crawler->filter('.article-header__description')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://idsa.in";
                        $imagePathToDownload = $crawler->filter('.article-header__image.for-tablet-down>div>picture>img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents("http:" . $imagePathToDownload));

                    } catch (\Exception $e) {
                        // dd($e);
                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    // })[0];
                    // $name = "img/la.jpg";
                    try {
                        $caption = $crawler->filter('.article-header__image-caption')->text();
                    } catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.field-name-body>.field-items >.field-item>p')->html();


                    $storyDetails = $crawler->filter('.body-content')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: cfr.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }
               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'southasiaanalysis.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('[id="main"]>h1')->text();
                    // dd($h1);
                    try {
                        $h2 = $crawler->filter('.article-header__description')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://idsa.in";
                        $imagePathToDownload = $crawler->filter('.article-header__image.for-tablet-down>div>picture>img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents("http:" . $imagePathToDownload));

                    } catch (\Exception $e) {
                        // dd($e);
                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    // })[0];
                    // $name = "img/la.jpg";
                    try {
                        $caption = $crawler->filter('.article-header__image-caption')->text();
                    } catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.field-name-body>.field-items >.field-item>p')->html();


                    $storyDetails = $crawler->filter('.field-items>.field-item.even>p')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: southasiaanalysis.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }
               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'brookings.edu')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('.headline-wrapper>h1')->text();
                    // dd($h1);
                    try {
                        $h2 = $crawler->filter('.article-header__description')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://idsa.in";
                        $imagePathToDownload = $crawler->filter('.size-jumbotron-primary>img')->extract('srcset')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = substr($name, 0, strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {
                        // dd($e);
                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    // })[0];
                    // $name = "img/la.jpg";
                    try {
                        $caption = $crawler->filter('.article-header__image-caption')->text();
                    } catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.post-body.post-body-enhanced')->html();


                    $storyDetails = $crawler->filter('[itemprop="articleBody"]>p,blockquote')->each(function ($node) {

                        return $node->text();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: brookings.edu</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }
               // return view('front.details', compact('posts'));

            } else if (strpos($link, 'rand.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1 = $crawler->filter('[id="RANDTitleHeadingId"]')->text();
                    // dd($h1);
                    try {
                        $h2 = $crawler->filter('.subtitle')->text();

                    } catch (\Exception $exception) {
                        $h2 = null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://idsa.in";
                        $imagePathToDownload = $crawler->filter('.hero-title>img')->extract('src')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/" . $name;
                        file_put_contents($name, file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {
                        // dd($e);
                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    // })[0];
                    // $name = "img/la.jpg";
                    try {
                        $caption = $crawler->filter('.article-header__image-caption')->text();
                    } catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.post-body.post-body-enhanced')->html();


                    $storyDetails = $crawler->filter('.body-text>p')->each(function ($node) {

                        return $node->text();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: rand.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));


                    $posts = Post::create(
                        ['title' => $h1,
                            'description' => $h2,
                            'user_id' => auth()->user()->id,
                            'category' => $data['catagory'],
                            'sub_category' => $data['subcatagory'],
                            'caption' => $caption,
                            'article' => $storyDetails,
                            'type' => 1,
                            'imgUrl' => $name,
                            'post_url' => $path,
                            'author' => " By Craft Politica",
                        ]

                    );

                } catch (\Exception $e) {

                    return view('admins.erros', compact('e'));
                }
               // return view('front.details', compact('posts'));

            } else if (strpos( $link,'www.easternmirrornagaland.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.posttitle')->text();
                    //dd($h1);

                    try {
                        $h2=   $crawler->filter('.m-story-header__intro')->text();

                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    //dd($h2);

                    try {

                        $imagePathToDownload =   $crawler->filter('.wp-caption  > img')->extract('src')[0];


//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = "post_images/".$name;
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    //dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.wp-caption-text > span > em')->text());


//                            dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    //dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $article = $crawler->filter('.entry > p ')->each(function ($node) {

                        return $node->html();
                    });

                    for ($i = 1;$i<count($article);$i++) {
                        $storyDetails[$i - 1] = $article[$i];
                    }

                    //dd($storyDetails);

                    $crawlFrom = "<p><cite>News Source: www.easternmirrornagaland.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>" By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));



            }
            else if (strpos( $link,'economictimes.indiatimes.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('h1.title')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('h2.title2 ')->text();

                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload =   $crawler->filter('.articleImg > figure >img')->extract('src')[0];


//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = "post_images/".$name;
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.articleImg > figure >figcaption')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

                    $storyDetails = $crawler->filter('.artText > .section1 > .Normal ')->html();


                    // $storyDetails = $crawler->filter('.artText > .section1 > .Normal ')->each(function ($node) {
//
                    // return $node->html();
                    // });

                    // dd($storyDetails);
                    // for ($i = 1;$i<count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source:www.economictimes.indiatimes.com</cite></p>";
                    $storyDetails=$storyDetails.$crawlFrom;

                    // $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>" By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));



            } else if (strpos( $link,'reuters.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.ArticleHeader_headline')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.article-heading-des')->text();

                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://www.aljazeera.com";
                        $imagePathToDownload =   $crawler->filter('.Image_container>figure>div.LazyImage_container.LazyImage_dark img')->extract('src')[0];
                        // $imagePathToDownload =   $constant.$crawler->filter('.LazyImage_container > img')->extract('src')[0];
                        // dd($imagePathToDownload);
                        // $name=substr($name, 0,strpos($name, '?'));

                        try {

                            $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);


                            $name = "post_images/".md5($name).".jpg";

                            file_put_contents($name , file_get_contents(
                                "http:".$imagePathToDownload."00"));

                        } catch (Exception $e) {
                            dd($e);
                        }


                        // dd($name);
                    } catch (\Exception $e) {

                        dd($e);
                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.Image_caption >span')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('div.StandardArticleBody_body > p')->each(function ($node) {

                        return $node->text();
                    });

                    // for ($i = 1;$i<count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }

                    // dd($storyDetails);

                    $crawlFrom = "<p><cite>News Source:reuters.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>"By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));

            }


            else if (strpos( $link,'aljazeera.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.post-title')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.article-heading-des')->text();

                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        $constant="https://www.aljazeera.com";
                        $imagePathToDownload =   $constant.$crawler->filter('.main-article-media>img')->extract('src')[0];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name, 0,strpos($name, '?'));
                        $name = "post_images/".$name;
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.main-article-caption')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.article-p-wrapper > p ')->each(function ($node) {

                        return $node->html();
                    });

                    // for ($i = 1;$i<count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }

                    // dd($storyDetails);

                    $crawlFrom = "<p><cite>News Source:aljazeera.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>"By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));

            }

            else if (strpos( $link,'theguardian.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.content__headline ')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.content__standfirst')->text();

                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload =   $crawler->filter('.u-responsive-ratio>picture> img')->extract('src')[0];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name=substr($name, 0,strpos($name, '?'));
                        $name = "post_images/".$name;
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.label__link-wrapper')->text());


//                            dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.content__article-body  > p ')->each(function ($node) {

                        return $node->html();
                    });

                    // for ($i = 1;$i<count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }

                    // dd($storyDetails);

                    $crawlFrom = "<p><cite>News Source:theguardian.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>" By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));

            }





            else if (strpos( $link,'www.asianage.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.single-head-title')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.content ')->text();

                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {

                        $imagePathToDownload =   $crawler->filter('.single-view-banner > img')->extract('src')[0];


//                            dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name = "post_images/".$name;
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.sDetailCapt')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        // $caption = $h2;

                    }
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.entry-content > p ')->text();
                    //

                    $storyDetails = $crawler->filter('.storyBody > p ')->each(function ($node) {
//
                        return $node->html();
                    });

                    // dd($storyDetails);
                    // for ($i = 1;$i<count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: www.asianage.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>" By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));



            }








            else if (strpos( $link,'nagalandpost.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.newsheader >h3')->text();
//                        dd($h1);
                    $h2=   $crawler->filter('.newsheader >h3')->text();
//                    dd($h2);
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    $imagePathToDownload =   $crawler->filter('.text-center > img')->extract('src')[0];
//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];
//                   dd($imagePathToDownload);



                    $caption = preg_replace('[\t]', '',$crawler->filter('.newsheader >h3')->text());

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();
                    $article = $crawler->filter('.artfulstry ')->each(function ($node) {
                        return $node->html();
                    });

                    for ($i = 1;$i<count($article);$i++) {
                        $storyDetails[$i - 1] = $article[$i];
                    }


                    $crawlFrom = "<p><cite>News Source: nagalandpost.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;
//
//                    dd($storyDetails);

                    $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                    $name = "post_images/".$name;

                    file_put_contents($name , file_get_contents($imagePathToDownload));

                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>" By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));



            }else if (strpos( $link,'scmp.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.info__headline ')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.generic-article__summary--ul')->html();
                        $h2 = "<ul>" .  $h2 . "</ul>";
                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    dd($h2);

                    try {
                        // $constant="https://www.aljazeera.com";
                        $imagePathToDownload =$crawler->filter('.article__featured-media>.media--type-image > a')->extract('href')[0];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name=substr($name, 0,strpos($name, '?'));
                        $name = "post_images/".md5($name).".jpg";
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                        // dd($imagePathToDownload);



                    } catch (\Exception $e) {

                        dd($e);
                        $name = "img/la.jpg";

                    }
                    // dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.article__featured-media>.media--type-image > a>figcaption>div>figcaption')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.article__body >div> p')->each(function ($node) {

                        return $node->text();
                    });

                    // for ($i = 1;$i<count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }

                    // dd($storyDetails);

                    $crawlFrom = "<p><cite>News Source: scmp.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>"By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));

            }



            else if (strpos( $link,'voanews.com')) {

                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.page-header__title')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.article-heading-des')->text();

                    } catch (\Exception $exception) {
                        $h2=   $h1;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://www.aljazeera.com";
                        $imagePathToDownload =$crawler->filter('.article__featured-media>.media--type-image > a')->extract('href')[0];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        $name=substr($name, 0,strpos($name, '?'));
                        $name = "post_images/".md5($name).".jpg";
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                        // dd($imagePathToDownload);



                    } catch (\Exception $e) {

                        dd($e);
                        $name = "img/la.jpg";

                    }
                    // dd($imagePathToDownload);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    //})[0];

                    try {
                        $caption = preg_replace('[\t]', '',$crawler->filter('.article__featured-media>.media--type-image > a>figcaption>div>figcaption')->text());


                        // dd($caption);
                    } catch (\Exception $e) {
                        $caption = $h2;

                    }
                    // dd($caption);

//                    $storyDetails = $crawler->filter('.entry-content > p ')->text();


                    $storyDetails = $crawler->filter('.article__body >div> p')->each(function ($node) {

                        return $node->text();
                    });

                    // for ($i = 1;$i<count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }

                    // dd($storyDetails);

                    $crawlFrom = "<p><cite>News Source: voanews.com</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>"By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }




//                return view('front.details', compact('posts'));

            }else if (strpos( $link,'carnegieendowment.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.headline ')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.zone-title__summary')->text();

                    } catch (\Exception $exception) {
                        $h2=   null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://idsa.in";
                        $imagePathToDownload = $crawler->filter('[name="image_src"]')->extract('href')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/".$name;
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    // })[0];
                    $name = "img/la.jpg";
                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    }


                        // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.field-name-body>.field-items >.field-item>p')->html();


                    $storyDetails = $crawler->filter('.article-body>p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: carnegieendowment.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>" By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }
//                return view('front.details', compact('posts'));

            }else if (strpos( $link,'satp.org')) {
                try {

                    $crawler = $client->request('GET', $link);
                    $h1=   $crawler->filter('.headline ')->text();
                    // dd($h1);

                    try {
                        $h2=   $crawler->filter('.zone-title__summary')->text();

                    } catch (\Exception $exception) {
                        $h2=   null;

                    }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                    // dd($h2);

                    try {
                        // $constant="https://idsa.in";
                        $imagePathToDownload = $crawler->filter('[name="image_src"]')->extract('href')[0];
                        // $   imagePathToDownload=$image[3];


                        // dd($imagePathToDownload);
                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);
                        // $name=substr($name,0,strpos($name, "?"));
                        $name = "post_images/".$name;
                        file_put_contents($name , file_get_contents($imagePathToDownload));

                    } catch (\Exception $e) {

                        $name = "img/la.jpg";

                    }
                    // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                    //    return  $node->attr('data-src');
                    // })[0];
                    $name = "img/la.jpg";
                    try {
                        $caption = $crawler->filter('.article-main-image-caption')->text();
                    }


                        // dd($caption);
                    catch (\Exception $e) {
                        $caption = null;

                    }
                    // dd($caption);

                    // $storyDetails = $crawler->filter('.field-name-body>.field-items >.field-item>p')->html();


                    $storyDetails = $crawler->filter('.article-body>p ')->each(function ($node) {

                        return $node->html();
                    });

//
                    // dd($storyDetails);
                    // for ($i = 1;$i<?count($article);$i++) {
                    // $storyDetails[$i - 1] = $article[$i];
                    // }


                    $crawlFrom = "<p><cite>News Source: carnegieendowment.org</cite></p>";

                    $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>".$crawlFrom;

                    // dd($storyDetails);


                    $path = "news-" . md5(substr($link, strrpos($link, '/') + 1));




                    $posts =   Post::create(
                        ['title' =>$h1,
                            'description' =>$h2,
                            'user_id'=>auth()->user()->id,
                            'category'=>$data['catagory'],
                            'sub_category'=>$data['subcatagory'],
                            'caption' =>$caption,
                            'article'=>$storyDetails,
                            'type' =>1,
                            'imgUrl'=>$name,
                            'post_url' =>$path,
                            'author'=>" By Craft Politica",
                        ]

                    );

                } catch (\Exception $e ) {

                    return view('admins.erros', compact('e'));
                }
                return view('front.details', compact('posts'));

            }


            else {

                $message = "<p>".$link."   Crawling Not Available for this Website<p>".$message;


            }

        }

        $message = '<p>Crawling Done Successfully!</p>'.$message;
        return redirect()->route('uploads')->with('status', $message);


    }
}