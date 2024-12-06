@extends('backend.app')
@section('title')
    Queues
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
                    <input type="number" class="form-control search-input" id="search_values" column_name="user" placeholder="User suite" value="{{$search_arr['user']}}">
                    <select  class="form-control search-input queue-status" id="search_values" column_name="status">
                        <option value="">All</option>
                        <option value="used">Used</option>
                        <option value="not_used">Not used</option>
                    </select>
                    <select  class="form-control search-input queue-type" id="search_values" column_name="type">
                        <option value="">Type</option>
                        <option value="c">Cashier</option>
                        <option value="d">Delivery</option>
                        <option value="i">Information</option>
                    </select>
                    <select  class="form-control search-input" id="search_values" column_name="location">
                        <option value="">Location</option>
                        @foreach($locations as $location)
                            @if($location->id == $search_arr['location'])
                                <option selected value="{{$location->id}}">{{$location->name}}</option>
                            @else
                                <option value="{{$location->id}}">{{$location->name}}</option>
                            @endif
                        @endforeach
                    </select>
                    <input placeholder="Start date" type="text" onfocus="(this.type='date')"
                           class="form-control search-input start_date_search" id="search_values" column_name="start_date"
                           value="{{$search_arr['start_date']}}">
                    <input placeholder="End date" type="text" onfocus="(this.type='date')"
                           class="form-control search-input end_date_search" id="search_values" column_name="end_date"
                           value="{{$search_arr['end_date']}}">
                    <button type="button" class="btn btn-warning btn-xs search-input" onclick="today_for_date_area();">Today</button>
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('queue.date')">Date</th>
                    <th class="columns" onclick="sort_by('queue.no')">No</th>
                    <th class="columns" onclick="sort_by('queue.type')">Type</th>
                    <th class="columns" onclick="sort_by('l.name')">Location</th>
                    <th class="columns" onclick="sort_by('queue.user_id')">User</th>
                    <th class="columns" onclick="sort_by('queue.created_at')">Created date</th>
                    <th class="columns" onclick="sort_by('queue.used_at')">Used date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($queues as $queue)
                    @php($suite = $queue->user_id)
                    @php($user_id_len = strlen($suite))
                    @if($user_id_len < 6)
                        @for($i = 0; $i < 6 - $user_id_len; $i++)
                            @php($suite = '0' . $suite)
                        @endfor
                    @endif
                    @switch($queue->type)
                        @case('c')
                        @php($type = 'Cashier')
                        @break
                        @case('d')
                        @php($type = 'Delivery')
                        @break
                        @case('i')
                        @php($type = 'Information')
                        @break
                        @default
                        @php($type = '---s')
                    @endswitch
                    <tr class="rows" id="row_{{$queue->id}}" onclick="select_row({{$queue->id}})">
                        <td>{{$queue->date}}</td>
                        <td>{{$queue->no}}</td>
                        <td>{{$type}}</td>
                        <td>{{$queue->location}}</td>
                        <td><a style="color: #1f314c;" href="{{route("show_clients")}}?search=1&suite={{$queue->user_id}}">{{$queue->suite}}{{$suite}}</a></td>
                        <td>{{$queue->created_at}}</td>
                        <td>{{$queue->used_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $queues->links(); !!}
            </div>
        </div>
    </div>

@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function(){
           let status = '{{$search_arr['status']}}';
           $(".queue-status").val(status);

            let type = '{{$search_arr['type']}}';
            $(".queue-type").val(type);
        });
    </script>
@endsection
