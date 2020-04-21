<?php

namespace App\Http\Controllers;

use App\Post;
use Goutte\Client;
use Illuminate\Http\Request;
use Vinelab\Rss\Rss;

class FeedController extends Controller
{
    //


    public function fetchFeed()
    {






    }


    public function show( Request $request)
    {
        $client = new Client();

        if(strpos($request->url,'reuters.com')) {


            try {


                $crawler = $client->request('GET', htmlspecialchars($request->url));
                $h1 = $crawler->filter('.ArticleHeader_headline')->text();
                // dd($h1);

                try {
                    $h2 = $crawler->filter('.article-heading-des')->text();

                } catch (\Exception $exception) {
                    $h2 = $h1;

                }
//                    $author =   $crawler->filter('.para-txt > span ')->extract('_text');
                // dd($h2);

                try {
                    // $constant="https://www.aljazeera.com";
                    $imagePathToDownload = $crawler->filter('.Image_container>figure>div.LazyImage_container.LazyImage_dark img')->extract('src')[0];
                    // $imagePathToDownload =   $constant.$crawler->filter('.LazyImage_container > img')->extract('src')[0];
                    // dd($imagePathToDownload);
                    // $name=substr($name, 0,strpos($name, '?'));

                    try {

                        $name = substr($imagePathToDownload, strrpos($imagePathToDownload, '/') + 1);


                        $name = "post_images/" . md5($name) . ".jpg";

                        file_put_contents($name, file_get_contents(
                            "http:" . $imagePathToDownload . "00"));

                    } catch (Exception $e) {
                        dd($e);
                    }


                    // dd($name);
                } catch (\Exception $e) {

//                    dd($e);
                    $name = "img/la.jpg";

                }
                // dd($name);

//                    $imagePathToDownload = $crawler->filter('div.animate-lazy > img')->each(function ($node) {
                //    return  $node->attr('data-src');
                //})[0];

                try {
                    $caption = preg_replace('[\t]', '', $crawler->filter('.Image_caption >span')->text());


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

                $storyDetails = "<p>" . implode("</p><p> ", $storyDetails) . "</p>" . $crawlFrom;

                // dd($storyDetails);


                $path = "news-" . md5(substr(htmlspecialchars($request->url), strrpos(htmlspecialchars($request->url), '/') + 1));


                $posts =
                    ['title' => $h1,
                        'description' => $h2,
//                        'user_id'=>auth()->user()->id,
//                        'category'=>$data['catagory'],
//                        'sub_category'=>$data['subcatagory'],
                    'caption' => $caption,
                    'article' => $storyDetails,
//                        'type' =>1,
                    'imgUrl' => $name,
                    'post_url' => $path,
                    'author' => "By Reuters",
                ];


            } catch (\Exception $e) {

                return view('home');
            }
            return view('front.details', compact('posts'));

        }else if (strpos($request->url, 'straitstimes.com')) {
            try {

                $crawler = $client->request('GET', $request->url);
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

//                $path = "news-" . md5(substr($request->url, strrpos($request->url, '/') + 1));


                $posts =
                    ['title' => $h1,
                        'description' => $h2,
//                        'user_id' => auth()->user()->id,
//                        'category' => $data['catagory'],
//                        'sub_category' => $data['subcatagory'],
                        'caption' => $caption,
                        'article' => $storyDetails,
//                        'type' => $data['type'],
                        'imgUrl' => $name,
//                        'post_url' => $path,
                        'author' => " By StraitStimes.com " ,
                    ];


            } catch (\Exception $e) {

                return view('home');
            }



            return view('front.details', compact('posts'));
        }



    }





}


