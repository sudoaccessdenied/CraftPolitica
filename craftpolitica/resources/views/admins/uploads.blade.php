@extends('layouts.app')

@section('content')

    <script>
        function loadSubCategory(data) {
            var myNode = document.getElementById("subcategory");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }


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
    </script>

    <div class="card">
        <div class="card-header"> Upload By Links</div>
        @if (session('status'))
            <div class="alert alert-success">
                <?php echo session('status'); ?>
            </div>
        @endif

        <div class="card-body">

            <form method="POST" action = "{{route('posts.store')}}">
                @csrf
                <div class="form-group">
                    <label class="mr-sm-2" for="Catagory">Catagory</label>
                    <select class="custom-select mr-sm-2 @error('catagory') is-invalid @enderror" id="Catagory" name = "catagory" onchange="loadSubCategory(this)">
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
                <div class="form-group">
                    <label class="mr-sm-2" for="subcategory">Sub Catagory</label>
                    <select class="custom-select mr-sm-2 @error('subcatagory') is-invalid @enderror" id="subcategory" name ="subcatagory">
                        <option selected disabled>Choose...</option>
                    </select>
                    @error('subcatagory')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>

                <div class="form-group">
                    <label class="mr-sm-2" for="type">Sub Catagory</label>
                    <select class="custom-select mr-sm-2 @error('type') is-invalid @enderror" id="type" name ="type">
                        <option selected value="1" >Post</option>
                        <option value="2">Banner</option>
                    </select>
                    @error('type')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>


                <div class="form-group ">
                    <label for="link" class=" col-form-label ">{{ __('Links') }}</label>


                    <textarea id="link" type="textArea" rows =3 class="form-control @error('link') is-invalid @enderror" name="link" value="{{ old('link') }}"  autocomplete="link" autofocus></textarea>

                    @error('link')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <div class="card-footer alert-info"> Seprate link by commas and press Enter</div>
    </div>




@endsection
