@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-8 is-invalid alert-danger">
            <h1>
                Sorry Unable to Scrap because of the following error
            </h1>
            {{ $e->getMessage() }}

        </div>
    </div>

@endsection


<div>
    <img src="" alt="">
</div>