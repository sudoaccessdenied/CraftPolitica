@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Edit Category') }}</div>

                    <div class="card-body">
                        <form method="POST" action="/editcategory/{{$data->id}}">
                            @csrf
                            @method('PUT')


                            <div class="form-group row">
                                <label for="category" class="col-md-4 col-form-label text-md-right">{{ __('Category') }}</label>

                                <div class="col-md-6">
                                    <input id="category" type="text" class="form-control @error('category') is-invalid @enderror" name="category" value="{{ $data->category }}"  autocomplete="category" autofocus>

                                    @error('category')
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



{{--    subcatagory--}}

{{--    <div class="container">--}}
{{--        <div class="row justify-content-center">--}}
{{--            <div class="col-md-8">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-header">{{ __('Add SubCategory') }}</div>--}}

{{--                    <div class="card-body">--}}
{{--                        <form method="POST" action="{{route('addcategory.store')}}">--}}
{{--                            @csrf--}}
{{--                            <div class="form-group">--}}
{{--                                <label class="mr-sm-2" for="Catagory">Catagory</label>--}}
{{--                                <select class="custom-select mr-sm-2" id="Catagory" name = "catagory">--}}
{{--                                    <option selected>Choose...</option>--}}
{{--                                    <option value="one">One</option>--}}
{{--                                    <option value="two">Two</option>--}}
{{--                                    <option value="three">Three</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}




{{--                            <div class="form-group row">--}}
{{--                                <label for="subcategory" class="col-md-4 col-form-label text-md-right">{{ __('SubCategory') }}</label>--}}

{{--                                <div class="col-md-6">--}}
{{--                                    <input id="subcategory" type="text" class="form-control @error('subcategory') is-invalid @enderror" name="subcategory" value="{{ old('subcategory') }}"  autocomplete="subcategory">--}}

{{--                                    @error('subcategory')--}}
{{--                                    <span class="invalid-feedback" role="alert">--}}
{{--                                        <strong>{{ $message }}</strong>--}}
{{--                                    </span>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="form-group row mb-0">--}}
{{--                                <div class="col-md-6 offset-md-4">--}}
{{--                                    <button type="submit" class="btn btn-primary">--}}
{{--                                        {{ __('Add') }}--}}
{{--                                    </button>--}}
{{--                                </div>--}}
{{--                            </div>--}}



{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

@endsection
