@extends('backend.app')
@section('title')
    All orders
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
                    <input type="text" class="form-control search-input" id="search_values" column_name="no" placeholder="No" value="{{$search_arr['no']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="number" placeholder="Track number" value="{{$search_arr['number']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="client" placeholder="Client" value="{{$search_arr['client']}}">
                    <select  class="form-control search-input" id="search_values" column_name="seller">
                        <option value="">Seller</option>
                        @foreach($sellers as $seller)
                            @if($seller->id == $search_arr['seller'])
                                <option selected value="{{$seller->id}}">{{$seller->name}}</option>
                            @else
                                <option value="{{$seller->id}}">{{$seller->name}}</option>
                            @endif
                        @endforeach
                    </select>
                    <select  class="form-control search-input" id="search_values" column_name="status">
                        <option value="">Status</option>
                        @foreach($statuses as $status)
                            @if($status->id == $search_arr['status'])
                                <option selected value="{{$status->id}}">{{$status->status}}</option>
                            @else
                                <option value="{{$status->id}}">{{$status->status}}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('package.id')">#</th>
                    <th class="columns" onclick="sort_by('c.name')">Client</th>
                    <th class="columns" onclick="sort_by('package.number')">Track number</th>
                    <th class="columns" onclick="sort_by('seller.name')">Seller</th>
                    <th class="columns" onclick="sort_by('status.status')">Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr class="rows" id="row_{{$order->id}}" onclick="select_row({{$order->id}})">
                        <td>{{$order->id}}</td>
                        <td>{{$order->number}}</td>
                        <td>{{$order->name}} {{$order->surname}}</td>
                        <td>{{$order->seller}}</td>
                        <td>{{$order->status}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $orders->links(); !!}
            </div>
        </div>
    </div>

@endsection

@section('css')

@endsection

@section('js')

@endsection
