@extends('backend.app')
@section('title')
    Courier | Settings | Log
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
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>User</th>
                    <th>Daily limit</th>
                    <th>Closing time</th>
                    <th>Amount for urgent</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr class="rows" id="row_{{$log->id}}" onclick="select_row({{$log->id}})">
                        <td>{{$log->id}}</td>
                        <td>{{$log->date}}</td>
                        <td>{{$log->user}}</td>
                        <td>{{$log->daily_limit}}</td>
                        <td>{{substr($log->closing_time, 0, 5)}}</td>
                        <td>{{$log->amount_for_urgent}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $logs->links(); !!}
            </div>
        </div>
    </div>
@endsection

@section('css')

@endsection

@section('js')

@endsection