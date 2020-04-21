@extends('layouts.layout')

@section('container')
    <div class="container ">

        <div class="card mt-4">
            <div class="card-header  d-flex justify-content-between">
                <div class="h4">{{$cat}}</div>
            <div class="card-link pagination"> {{$data->links()}}</div>
            </div>

            <div class="row">

                @foreach($data as $d)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card d-block m-1 shadow-sm m-1">
                        <img class="card-img-top" src="{{request()->getSchemeAndHttpHost().'/'.$d->imgUrl}}" alt="{{$d->title}}" style="width:100%;height:225px;">
                        <div class="card-body">
                            <h4 class="card-title">{{$d->title}}</h4>

                            <a href="{{route('front.post_url',$d->post_url)}}" class="card-link stretched-link ">Read More..</a>
                        </div>
                        <div class="card-footer text-muted">
                            {{date('d-M-Y h:i:sa',strtotime($d->created_at))}}
                        </div>
                    </div>
                </div>

                @endforeach



            </div>
            <div class="card-link card-footer pagination justify-content-end">

            {{$data->links()}}

        </div>
    </div>



@endsection
