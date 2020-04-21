@extends('layouts.app')

@section('content')

<div class="card">

    <div class="card-header"> All Category</div>
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    <div class="card-body">
        <table class="table table-bordered ">
            <thead>
            <tr>
                <th scope="col">#Category Id</th>
                <th scope="col">Category Name</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $d)
                <tr>
                    <th scope="row">{{$d->id}}</th>
                    <td>{{$d->category}}</td>
                    <td class="d-flex justify-content-around">
                        <a  class="btn btn-primary" href="/editcategory/{{$d->id}}/edit">Edit</a>


                        <a  class="btn btn-danger" href="/editcategory/{{$d->id}}"
                            onclick="event.preventDefault();document.getElementById('delete-form{{$d->id}}').submit()">Delete</a></td>

                    <form id="delete-form{{$d->id}}" action="/editcategory/{{$d->id}}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

    <div   class="card-footer alert-danger" >Deleting a Category will delete all it Subcategory</div>

</div>





@endsection
