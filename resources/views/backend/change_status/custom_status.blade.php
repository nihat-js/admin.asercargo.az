@extends('backend.app')
@section('title')
    Change status | In Baku
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
                    <form action="{{route("admin_post_custom_status")}}" method="post">
                        @csrf
                        <select class="form-control search-input" id="status" name="status" required>
                            <option value="">Status</option>
                            @foreach($statuses as $status)
                                <option value="{{$status->id}}">{{$status->status_en}}</option>
                            @endforeach
                        </select>
                        <select class="form-control search-input" id="flight" name="flight" required>
                            <option value="">Flights</option>
                            @foreach($flights as $flight)
                                <option value="{{$flight->id}}">{{$flight->name}}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary search-input">Submit</button>
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