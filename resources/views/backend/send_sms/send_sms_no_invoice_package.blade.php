@extends('backend.app')
@section('title')
    SMS | No invoice
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
                    <form action="{{route("send_sms_for_no_invoice_package")}}" method="post">
                        @csrf
                        <select class="form-control search-input" id="location" name="location" required>
                            <option value="">Location</option>
                            @foreach($locations as $location)
                                <option value="{{$location->id}}">{{$location->name}}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary search-input">Send</button>
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
