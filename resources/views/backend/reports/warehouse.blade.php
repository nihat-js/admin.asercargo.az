@extends('backend.app')
@section('title')
    Reports | Warehouse
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
                    <form action="{{route("reports_post_warehouse")}}" method="post">
                        @csrf
                        <input type="date" name="from_date" class="form-control search-input" required>
                        <input type="date" name="to_date" class="form-control search-input" required>
                        <select name="country" class="form-control search-input">
                            <option value="">Country</option>
                            @foreach($countries as $country)
                                <option value="{{$country->id}}">{{$country->name}}</option>
                            @endforeach
                        </select>
                        <select name="flight" class="form-control search-input">
                            <option value="">Flight</option>
                            @foreach($flights as $flight)
                                <option value="{{$flight->id}}">{{$flight->name}}</option>
                            @endforeach
                        </select>
                        <select name="warehouse" class="form-control search-input">
                            <option value="all">Warehouse status (All)</option>
                            <option value="declared">Declared</option>
                            <option value="external">External warehouse</option>
                            <option value="way">On the way</option>
                            <option value="internal">Internal warehouse</option>
                            <option value="delivered">Delivered</option>
                        </select>
                        <select name="status" class="form-control search-input">
                            <option value="">Status</option>
                            @foreach($statuses as $status)
                                <option value="{{$status->id}}">{{$status->status}}</option>
                            @endforeach
                        </select>
                        <select name="paid" class="form-control search-input">
                            <option value="all">Paid status (All)</option>
                            <option value="yes">Paid</option>
                            <option value="no">Not paid</option>
                        </select>
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
