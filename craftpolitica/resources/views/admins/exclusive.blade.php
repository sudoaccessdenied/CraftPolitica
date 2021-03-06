@extends('layouts.app')

@section('content')
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script type="text/javascript">
        function loadSubCategory(data) {


            $.get("/subcategory/" + data.value, function (response, status) {
                // alert("Data: " + response[0].id + "\nStatus: " + status);

               for(var i = 0;i<response.length;i++)
               {

                   var x = document.createElement("OPTION");
                   x.setAttribute("value", response[i].id);
                   var t = document.createTextNode(response[i].subcategory);
                   x.appendChild(t);
                   document.getElementById("subcategory").appendChild(x);
               }
            });

        }

        $(document).ready(function() {
            $('.summernote').summernote(
                {
                    placeholder: 'Type Your Article Here...',
                    tabsize: 10,
                    height: 300
                }
            );

        });
    </script>

    <div class="card">
        <div class="card-header"> Exclusive uploads</div>
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="card-body">

            <form method="POST" action = "{{route('exclusive.store')}}"  enctype="multipart/form-data">
                @csrf

                <div class="form-group row">
                    <label for="title" class=" col-form-label mx-sm-4 ">{{ __('Title') }}</label>


                        <input id="title" type="text" class="form-control mx-sm-4 @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}"  autocomplete="title" autofocus>

                        @error('title')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror

                </div>


                <div class="form-group row">
                    <label for="description" class=" col-form-label mx-sm-4">{{ __('Description') }}</label>


                        <input id="description" type="text" class="form-control  mx-sm-4 @error('description') is-invalid @enderror" name="description" value="{{ old('description') }}"  autocomplete="description" autofocus>

                        @error('description')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror

                </div>

                <div class="form-group row">
                    <label for="image" class=" col-form-label mx-sm-4">{{ __('Post Image') }}</label>

                        <input id="image" type="file" class="form-control mx-sm-4 @error('image') is-invalid @enderror" name="image"  autocomplete="image" accept="image/*" autofocus>

                        @error('image')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror

                </div>

                <div class="form-group row">
                    <label for="caption" class=" col-form-label mx-sm-4">{{ __('Caption') }}</label>


                    <input id="caption" type="text" class="form-control  mx-sm-4 @error('caption') is-invalid @enderror" name="caption" value="{{ old('caption') }}"  autocomplete="caption" autofocus>

                    @error('caption')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>


                <div class="form-group row">
                    <label class="mx-sm-4" for="Catagory">Catagory</label>
                    <select class="custom-select mx-sm-4 @error('catagory') is-invalid @enderror" id="Catagory" name = "catagory" onchange="loadSubCategory(this)">
                        <option selected disabled >Choose...</option>
                        @foreach($categories as $category)
                        <option value="{{$category->id}}">{{$category->category}}</option>
                            @endforeach
                    </select>
                    @error('catagory')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>


                <div class="form-group row">
                    <label class="mx-sm-4" for="subcategory">Sub Catagory</label>
                    <select class="custom-select mx-sm-4 @error('subcatagory') is-invalid @enderror" id="subcategory" name ="subcatagory">
                        <option selected disabled>Choose...</option>
                    </select>
                    @error('subcatagory')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>

                <div class="form-group row">
                    <label class="mx-sm-4" for="type">Type</label>
                    <select class="custom-select mx-sm-4 @error('type') is-invalid @enderror" id="type" name ="type">
                        <option  value="1" >Post</option>
                        <option value="2">Banner</option>
                        <option selected value="3" >Exclusive</option>
                    </select>
                    @error('type')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>


                <div class="form-group  ">
                    <label for="article" class=" col-form-label ">{{ __('Article') }}</label>


                    <textarea id="article" type="textArea" rows =10 class="form-control summernote @error('article') is-invalid @enderror" name="article" value="{{ old('article') }}"  autocomplete="article" autofocus></textarea>

                    @error('article')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

              </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

    </div>




@endsection
