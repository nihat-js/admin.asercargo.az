@extends('backend.app')
@section('title')
    Excel
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
            <!-- resources/views/excel.blade.php -->
            <form action="{{ route('upload_courier_excel') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".xlsx, .xls">
                <button type="submit">Excel Yükle</button>
            </form>

    </div>

@endsection

@section('css')

@endsection

@section('js')

@endsection
