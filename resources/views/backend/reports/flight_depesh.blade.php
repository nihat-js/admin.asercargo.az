@extends('backend.app')
@section('title')
    Reports | Manifest
@endsection
@section('actions')

@endsection
@section('content')
    <div class="col-md-12">
        @if(session('display') == 'block')
            <div class="alert alert-{{session('class')}}" role="alert">
                {{session('message')}}
            </div>
        @endif
        <div class="panel panel-default">
            <div class="panel-body">
                <div id="search-inputs-area" class="search-areas">
                    <form action="{{route("flight_depesh")}}" method="post">
                        @csrf
                        <select class="form-control search-input" id="flight" name="flight" required>
                            <option value="">Flights</option>
                            @foreach($flights as $flight)
                                <option value="{{$flight->id}}">{{$flight->name}}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary search-input">Export manifest</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')

@endsection

@section('js')

@endsection
