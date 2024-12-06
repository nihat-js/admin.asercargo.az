@extends('backend.app')
@section('title')
    Reports | Payments
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
                    <form action="{{route("reports_post_payments")}}" method="post">
                        @csrf
                        <input type="date" name="from_date" class="form-control search-input" required>
                        <input type="date" name="to_date" class="form-control search-input" required>
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
