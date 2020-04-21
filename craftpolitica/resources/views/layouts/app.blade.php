
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CraftPolitica') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'CraftPolitica') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    <main class="py-4">

        <div class="container-fluid">
            <div class="row">
                @auth
                <div class="col-3 mynav">

                    <!--Sidebar -->
                    <div class="nav flex-column " aria-orientation="vertical">
                        <a class="nav-link "  href="{{route('uploads')}}"   >Create Post</a>
                        <a class="nav-link"   href="{{route('exclusive.index')}}"  >Exclusive Uploads</a>

                        <a class="nav-link dropdown-toggle text-info" data-toggle="collapse" data-target="#demo">Category</a>

                        <div id="demo" class="collapse card">

                            <a class="nav-link"  href="{{route('category.create')}}"   >Add Category</a>
                            <a class="nav-link"  href="{{route('subcategory.create')}}"   >Add SubCategory</a>
                            <a class="nav-link"  href="{{route('editcategory.index')}}"   >Edit Category</a>
                            <a class="nav-link"  href="{{route('subcategory.index')}}"   >Edit SubCategory</a>
                        </div>

                        <a class="nav-link dropdown-toggle text-info" data-toggle="collapse" data-target="#edit">Edit Post</a>

                        <div id="edit" class="collapse card">

                            <a class="nav-link"  href="{{route('posts.index')}}"  >All post</a>
                            <a class="nav-link"  href="{{route('posts.search')}}" >Search Post</a>
                        </div>
                        <a class="nav-link"  href="#v-pills-comments"   >Comments</a>
                        <a class="nav-link"  href="#v-pills-banneredit"   >Banner</a>

                    </div>

                </div>
                    @else
                    <div  class="offset-2">
                    </div>

                @endauth
                <div class="col-9">
                    @yield('content')
                </div>
            </div>
        </div>


    </main>
</div>

{{--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>--}}
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-lite.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-lite.js"></script>
</body>
</html>
