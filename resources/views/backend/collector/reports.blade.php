@extends('backend.app')
@section('title')
    Reports | {{$title}}
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
                    <form action="{{route("collector_post_reports", $type)}}" method="post">
                        @csrf
                        @if($type == 'manifest')
                            <select class="form-control search-input" id="flight" name="flight" required>
                                <option value="">Flights</option>
                                @if($flights != false)
                                    @foreach($flights as $flight)
                                        <option value="{{$flight->id}}">{{$flight->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        @else
                            <input type="date" name="from_date" class="form-control search-input" required>
                            <input type="date" name="to_date" class="form-control search-input" required>
                        @endif
                        <button type="submit" class="btn btn-primary search-input">Export</button>
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
