@extends('layouts.app')

@section('content')

{{--    subcatagory--}}

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Add SubCategory') }}</div>

                    <div class="card-body">
                        <form method="POST" action="/subcategory/{{$data->id}}">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label  text-md-right" for="Category">Catagory</label>
                                <select class="custom-select col-md-6 @error('category') is-invalid @enderror " id="Category" name = "category">
                                    <option selected  value="{{$data->category_id}}">{{$data->category->category}}</option>
                                    @foreach($cat as $d)
                                        @if($data->category_id!= $d->id)
                                    <option value="{{$d->id}}">{{$d->category}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('category')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group row">
                                <label for="subcategory" class="col-md-4 col-form-label text-md-right">{{ __('SubCategory') }}</label>

                                <div class="col-md-6">
                                    <input id="subcategory" type="text"
                                           class="form-control @error('subcategory') is-invalid @enderror"
                                           name="subcategory" value="{{ $data->subcategory }}"  autocomplete="subcategory">

                                    @error('subcategory')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Submit') }}
                                    </button>
                                </div>
                            </div>



                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
