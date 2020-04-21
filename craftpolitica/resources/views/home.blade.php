@extends('layouts.layout')

@section('container')


    <div class="">
        <div class="container   pt-2 ">
            <div class="row ">
                <div class="col-lg-8  p-0">


                    <div class="m-1 bg-white p-2 rounded shadow-sm ">
                        <!--                <div class="text-secondary h4 border-bottom-2">TRENDING</div>-->
                        <div class="card">

                            <div class=" card-header h5"><a href="{{route('front.type',2)}}">TRENDING</a></div>
                        </div>

                        <?php  use Vinelab\Rss\Rss;$banners = \App\Post::where('type', 2)->take(8)->latest()->get();$i = 0  ?>
                        <div id="demo" class="carousel slide " data-ride="carousel">
                            <ul class="carousel-indicators">

                                @foreach($banners as $key=> $b)
                                    <li data-target="#demo" data-slide-to="{{$i++}}"
                                        @if($key ==0) class="active" @endif ></li>
                                @endforeach
                            </ul>
                            <div class="carousel-inner">


                                @foreach($banners as $key=>$b)
                                    <div class="carousel-item @if($key ==0) active @endif ">
                                        <img src="{{'http://'.request()->getHttpHost().'/'.$b->imgUrl}}" class="rounded"
                                             alt="{{$b->title}}}">
                                        <div class="carousel-caption">
                                            <a href="{{route('front.post_url',$b->post_url)}}" class="">

                                                <h3 class="bg-crousel text-black"> {{$b->title}}</h3>
                                            </a>
                                        </div>
                                    </div>

                                @endforeach

                                <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </a>
                                <a class="carousel-control-next" href="#demo" data-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--        <div  class="col-lg-1"></div>-->

                </div>

                <div class="col-lg-4    p-0 "  >

                    <div class="card mt-1">
                        <h4 class="card-header h5">TOP NEWS</h4>

                    <div class=" m-1 p-2   overflow-auto bg-white" style="height:360px;">



                    <?php

                    try {
                        $rss = new Rss();
                        $feed = $rss->feed('http://feeds.reuters.com/reuters/INtopNews');
                        $info = $feed->info();
                        $articles = $feed->articles();
                        $content = file_get_contents("https://www.straitstimes.com/news/asia/rss.xml");

                        $i=0;
                        $strait = new SimpleXmlElement($content);


                    } catch (Exception $exception) {

                        echo "No Feeds Available ";

                    }


                        ?>


                            @foreach($articles as $key=>$article)
                            <div class="card">
                                <div class="  d-flex p-1" >
                                    <img class="align-self-center" src="{{json_decode(json_encode($info))->image->url}}"
                                         style="height: {{ json_decode(json_encode($info))->image->height }}px;width: {{json_decode(json_encode($info))->image->width}}px">
                                    <a style ="cursor:pointer;" onclick="event.preventDefault();

                                                 document.getElementById({{$key}}).submit()" class="nav-link card-link ">
                                        {{$article->title}}</a>
                                </div>

                            </div>
                                <form method="POST" action="{{route('feed.show')}}" id="{{$key}}">
                                    @csrf
                                    <input type="text" name ="url" value="{{$article->link}}" hidden>
{{--                        <div class="nav-link btn-secondary btn"  type="submit" name ="url" value="{{$article->link}}"> Read More...</div>--}}

                                </form>
                            @endforeach

                        @foreach($strait->channel->item as  $d)


                            <div class="card">
                                <div class=" card-body d-flex">
                                    <img class="align-self-center" src="{{$strait->channel->image->url}}"  style="height: 40px;width: 35px">
                                    <a style ="cursor:pointer;" onclick="event.preventDefault();

                                            document.getElementById({{$i}}).submit()" class="nav-link card-link">
                                        {{$d->title}}</a>
                                </div>

                            </div>
                            <form method="POST" action="{{route('feed.show')}}" id="{{$i}}">
                                @csrf
                                <input type="text" name ="url" value="{{$d->link}}" hidden>
{{--                                                        <div class="nav-link btn-secondary btn"  type="submit" name ="url" value="{{$article->link}}"> Read More...</div>--}}

                            </form>
                            <?php $i++ ?>
                        @endforeach




                    </div>
                </div>

            </div>
            </div>
            <!--     oceana news-->


            <div class="row ">
                <div class="col-lg-8   p-0">
                    <div class="m-1 bg-white rounded shadow-sm ">


                        @foreach($category as $cat)
                            <div class="card">
                                <a class=" card-header h5"
                                   href="{{route('front.category',$cat->id)}}">{{$cat->category}} </a>
                            </div>

                            <div class="row m-2">

                                @foreach($cat->posts as $post)
                                    <div class="col-sm-12 col-md-4 m-0 p-0  ">

                                        <div class="my-img-container mydiv">
                                            <a href="{{route('front.post_url',$post->post_url)}}">
                                                <img src="{{request()->getSchemeAndHttpHost().'/'.$post->imgUrl}}"
                                                     style="width: 100%;height:150px">
                                                <div class="text-white lebel  rounded  p-1 text-truncate d-inline-block lead"
                                                     style="max-width: 90%">{{$post->title}}</div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach

                                <div class=" btn btn-dark mydiv col-sm-12 col-md-4 m-0 p-0">
                                    <a href="{{route('front.category',$cat->id)}}">
                                        <!--                                  //  <img src="img/chicago.jpg"  style="width: 100%;height:150px">-->
                                        <div class="p-5 text-center h4 text-white">Click for More...</div>
                                    </a>
                                </div>


                            </div>
                        @endforeach
                    </div>
                </div>


                <div class="col-lg-4    p-0 ">

                    <div class=" m-1">

                        <div class="card bg-light mb-0">
                            <a href="{{route('front.type',3)}}">
                                <h4 class="card-header h5">EXCLUSIVE</h4>
                            </a>





                        @foreach($exclusive as $ex)

                        @if(!isset($ex))
                            <div class="card  mb-0">
                                <div class="card-body">
                                    <a href="{{route('front.post_url',$ex->post_url)}}"
                                       class="nav-link stretched-link"><h6 class="card-title">{{$ex->title}}</h6></a>
                                </div>
                            </div>

                        @else

                                <div class="card">

                                        <div class="media position-relative">
                                            <img src="{{request()->getSchemeAndHttpHost().'/'.$ex->imgUrl}}"
                                                 class="mr-3" alt="{{$ex->title}}" style="height: 100px;width: 100px;">
                                            <div class="media-body">
                                                <p >{{$ex->title}}</p>
                                                <a href="{{route('front.post_url',$ex->post_url)}}" class="stretched-link">Read more...</a>
                                            </div>


                                    </div>
                                </div>

                            @endif
                        @endforeach


                        <div class="card-link text-right card-footer"><a href="{{route('front.type',3)}}"> More...</a></div>

                        </div>
                    </div>
                </div>

            </div>


            <div class="card text-center">
                <div class="card-footer"> Copyright 2019</div>
            </div>


        </div>
    </div>
@endsection
