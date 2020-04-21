@extends('layouts.app')

@section('content')
    <script type="text/javascript">
        function loadType(data) {


            if(data.value ==1)
            {
                document.getElementById("Title").classList.remove('d-none');
                document.getElementById("Category").classList.add('d-none');
                document.getElementById("postType").classList.add('d-none');
                document.getElementById("inBetweenDate").classList.add('d-none');

            }else if (data.value ==2) {
                document.getElementById("Category").classList.remove('d-none');
                document.getElementById("Title").classList.add('d-none');
                document.getElementById("postType").classList.add('d-none');
                document.getElementById("inBetweenDate").classList.add('d-none');

            }else if (data.value ==3) {
                document.getElementById("postType").classList.remove('d-none');
                document.getElementById("Title").classList.add('d-none');
                document.getElementById("Category").classList.add( 'd-none');
                document.getElementById("inBetweenDate").classList.add('d-none');

            }else if (data.value == 4){
                document.getElementById("inBetweenDate").classList.remove('d-none');
                document.getElementById("Title").classList.add('d-none');
                document.getElementById("Category").classList.add( 'd-none');
                document.getElementById("postType").classList.add( 'd-none');

            }
            // while (myNode.firstChild) {
            //     myNode.removeChild(myNode.firstChild);
            // }
            //
            // $.get("/subcategory/" + data.value, function (response, status) {
            //     // alert("Data: " + response[0].id + "\nStatus: " + status);
            //
            //     for(var i = 0;i<response.length;i++)
            //     {
            //
            //         var x = document.createElement("OPTION");
            //         x.setAttribute("value", response[i].id);
            //         var t = document.createTextNode(response[i].subcategory);
            //         x.appendChild(t);
            //         document.getElementById("subcategory").appendChild(x);
            //     }
            // });

        }
       </script>


    <div class="card">
        <div class="header">

            <form method="GET" action = "{{__('/posts/searchitem')}}" >
                @csrf
                <div class="form-group row p-3">
                    <label class="mx-sm-4" for="SearchBy">Search By</label>
                    <select class="custom-select mx-sm-4 @error('searchby') is-invalid @enderror" id="SearchBy" name = "searchby" onchange="loadType(this)">
{{--                        @foreach($categories as $category)--}}

                            <option value="1"  selected >Title</option>
                            <option value="2" >Category</option>
                            <option value="3" >Type</option>
                            <option value="4" >Date</option>
{{--                        @endforeach--}}
                    </select>
                    @error('searchby')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror

                </div>


                <div class="d-none" id="Category">
                    <div class="form-group row">
                        <label class="mx-sm-4" for="Category">Category</label>
                        <select class="custom-select mx-sm-4 @error('category') is-invalid @enderror" id="Category" name = "category" >
                            <option value="0" selected>Choose...</option>
                            @if(isset($categories))
                            @foreach($categories as $category)

                                <option value="{{$category->id}}" >{{$category->category}}</option>
                            @endforeach
                                @endif
                        </select>
                        @error('category')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror

                    </div>
                </div>


                <div class="d-none" id="postType">

                    <div class="form-group row">
                        <label class="mx-sm-4" for="Type">Type</label>
                        <select class="custom-select mx-sm-4 @error('type') is-invalid @enderror" id="Type" name = "type" >

                                <option value="0"   selected>Choose...</option>
                                <option value="1"   >Post</option>
                                <option value="2"   >Banner</option>
                                <option value="3"   >Exclusive</option>
                        </select>
                        @error('type')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror

                    </div>
                </div>

                <div class="d-none" id="inBetweenDate">
                    <div class="d-flex">


                    <div class="form-group ">
                        <label class="mx-sm-4" for="fromDate">Starting Date</label>
                        <input type="date" class=" mx-sm-4 @error('fromDate') is-invalid @enderror" id="fromDate" name = "fromDate">

                        @error('fromDate')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror

                    </div>




                    <div class="form-group ">
                        <label class="mx-sm-4" for="toDate">End Date</label>
                        <input type="date" class=" mx-sm-4 @error('toDate') is-invalid @enderror" id="toDate" name = "toDate">

                        @error('toDate')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror

                    </div>
                </div>
                </div>



                <div class="" id="Title">


                    <div class="form-group d-flex "  >
                        <label for="keyword" class="col-form-label m-3 ">{{ __('Title') }}</label>

                        <input id="keyword" type="text" class="form-control m-3 @error('keyword') is-invalid @enderror" name="keyword" value="{{ old('keyword') }}"  autocomplete="keyword" autofocus>
                        @error('keyword')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror


                    </div>
                </div>

                <div> <button type="submit" name="submit" class="btn btn-outline-primary m-3 float-right"> Search</button></div>
            </form>

        </div>
    </div>


    @if(isset($data)&& !is_bool($data))
<div class="card">

    <div class="card-header d-flex justify-content-between">
        <h3>Search Result</h3>
        <div>{{$data->links()}}</div>
    </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
    <div class="card-body">
        <table class="table table-bordered ">
            <thead>
            <tr>
                <th scope="col">#Post Id</th>
                <th scope="col">Title</th>
                <th scope="col">Type</th>
                <th scope="col">Category</th>
                <th scope="col">SubCategory</th>
                <th scope="col">Date</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $d)
                <tr>
                    <th scope="row">{{$d->id}}</th>
                    <td>{{$d->title}}</td>
                    <td>{{$d->type}}</td>
                    <td>{{$d->categorys->category}}</td>
                    <td>{{$d->subcategorys->subcategory}}</td>
                    <td>{{$d->created_at}}</td>
                    <td class="d-flex justify-content-around">
                        <a  class="btn btn-primary" href="/posts/{{$d->id}}/edit">Edit</a>
                        &nbsp
                        <a  class="btn btn-danger" href="{{route('posts.destroy',$d->id)}}"
                            onclick="event.preventDefault();document.getElementById('mydelete-form{{$d->id}}').submit();"
                            >Delete</a>
                    </td>

                    <form id="mydelete-form{{$d->id}}" action="{{route('posts.destroy',$d->id)}}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="card-footer d-flex justify-content-end"> {{$data->links()}}</div>
    </div>

</div>



@endif

@endsection
