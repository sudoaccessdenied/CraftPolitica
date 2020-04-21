@extends('layouts.app')

@section('content')

{{--    subcatagory--}}

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Add SubCategory') }}</div>

                    @if (session('addsubcategorystatus'))
                        <div class="alert alert-success">
                            {{ session('addsubcategorystatus') }}
                        </div>
                    @endif
                    <div class="card-body">
                        <form method="POST" action="{{route('addsubcategory.store')}}">
                            @csrf
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label  text-md-right" for="Category">Catagory</label>
                                <select class="custom-select col-md-6 @error('subcategory') is-invalid @enderror " id="Category" name = "category">
                                    <option selected disabled>Choose...</option>
                                    @foreach($datas as $data)
                                    <option value="{{$data->id}}">{{$data->category}}</option>
                                    @endforeach
                                </select>
                                @error('subcategory')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group row">
                                <label for="subcategory" class="col-md-4 col-form-label text-md-right">{{ __('SubCategory') }}</label>

                                <div class="col-md-6">
                                    <input id="subcategory" type="text" class="form-control @error('subcategory') is-invalid @enderror" name="subcategory" value="{{ old('subcategory') }}"  autocomplete="subcategory">

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
                                        {{ __('Add') }}
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
