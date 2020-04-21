<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Craft Politica') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <script src="https://kit.fontawesome.com/d5c70bd5aa.js"></script>


    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/customadmin.css') }}" rel="stylesheet">
    <script src="{{ asset('js/share.js') }}"></script>


{{--    //Meta tag for facebook--}}

    <meta property="og:url"           content="{{request()->getUri()}}" />
    <meta property="og:type"          content="website" />
    <meta property="og:title"         content="Craft Politica" />
    <meta property="og:description"   content="Your description" />
    <meta property="og:image"         content="{{request()->getSchemeAndHttpHost()}}/img/twitter.png" />


</head>
<body>
    <div id="app">

        <!-- --------------------------------------------Initial navigation links for facebooks etc-->
        <div class="d-none d-sm-block bg-black " >
            <div class="nav d-flex  container">
                <!--        <div class="  text-light "><a href="#" class="nav-link text-light">HOME </a></div>-->
                <div class="  text-light  "><a href="#" class="nav-link text-light ">ABOUT </a></div>
                <div class="  text-light "><a href="#" class="nav-link text-light">CONTACT US </a></div>
                <div class="  text-light "><a href="#" class="nav-link text-light">WRITE FOR US </a></div>

                <div class="  text-light  ml-auto"><a href="#" class="nav-link text-light ">
                        <i class="fab fa-facebook-square"></i> </a></div>
                <div class="  text-light  "><a href="#" class="nav-link text-light "><i class="fab fa-twitter-square"></i> </a>
                </div>
                <div class="  text-light  "><a href="#" class="nav-link text-light "><i class="fab fa-youtube"></i> </a></div>

            </div>
        </div>


        <!--  ------------------------------------------------------------------------your header part -->
        <div   class="container d-none d-md-block  ">
            <div   class="row">
                <div class="col-md-3 p-3 ">
                    <span class=" h1  " style="color:#ff5722;" >Craft Politica</span>
                    <div class="text-secondary text-right  small lead font-weight-bold ">...Unfolding the Indo-Pacific</div>
                </div>
                <div class="col-md-9 p-3  bg-white h1 text-center  ">Advertisement Zone</div>

            </div>
        </div>
        <!-- -----------------------------------------------------end-->
        <nav class=" navbar navbar-expand-md justify-content-around navbar-light shadow-sm border-top">
            <!-- Brand -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand d-md-none d-sm-block font-weight-bold text-secondary" href="#">Craft Politica</a>


            <!--    <form class="form-inline">-->
            <!--        <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">-->
            <!--&lt;!&ndash;        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>&ndash;&gt;-->
            <!--    </form>-->

            <!--    <a   class="navbar-brand d-md-none d-sm-block " href="#"  >-->
            <!--        <i class="fas fa-search"></i>-->
            <!--    </a>-->

            <!-- Toggler/collapsibe Button -->


            <!-- Navbar links -->
            <div class="collapse navbar-collapse  justify-content-around    font-weight-bold " id="collapsibleNavbar">
                <ul class="navbar-nav ">
                    <li class="nav-item">
                        <a class="nav-link " href="/">
                            <i class="fas fa-home"></i>
                            Home
                        </a>
                    </li>
                    <?php $categories = \App\Category::all() ?>
                    @foreach($categories as $category)
{{--                            {{$category->subcategorys[0]->subcategory}}--}}

                        @if($category->category == $category->subcategorys[0]->subcategory)

                            <li class="nav-item">
                                <a class="nav-link" href="{{route('front.category',$category->id)}}">{{$category->category}}</a>
                            </li>

                        @else

                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="" id="navbardrop" data-toggle="dropdown">
                            {{$category->category}}
                        </a>
                        <div class="dropdown-menu ">

                            @foreach($category->subcategorys as $subcategory)
                            <a class="dropdown-item text-secondary  " href="{{route('front.subcategory',$subcategory->id)}}">
                                {{$subcategory->subcategory}}</a>
                            @endforeach

                        </div>
                    </li>


                        @endif
                      @endforeach
                </ul>
                <form class="form-inline " action="{{route('front.search')}} " method="GET">
                    <input class="form-control mr-sm-2 " name ="search" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

        </nav>

        <main>
            @yield('container')
        </main>











    </div>
</body>
</html>
