@extends('backend.app')
@section('title')
    Home page
@endsection
@section('content')
    <h1 class="home-title">Aser - Courier Management System</h1>

    <div class="col-md-12">
        @if(session('display') == 'block')
            <div class="alert alert-{{session('class')}}" role="alert">
                {{session('message')}}
            </div>
        @endif
    </div>
@endsection

@section('css')
    <style>
        .home-title {
            text-align: center;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            position: absolute;
            box-shadow: 5px 5px 5px 5px #10458c;
            padding: 15px;
            color: #10458c;
        }
    </style>
@endsection

@section('js')

@endsection
