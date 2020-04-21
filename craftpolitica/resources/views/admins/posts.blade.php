@extends('layouts.app')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between">
        <h3>All Post</h3>
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
                <th scope="col">Created At</th>
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





@endsection
