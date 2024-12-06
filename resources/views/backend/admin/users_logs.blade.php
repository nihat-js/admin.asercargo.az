@extends('backend.app')
@section('title')
    Login / Logout logs
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
                    <select  class="form-control search-input log-users-type" id="search_values" column_name="users_type">
                        <option value="">All</option>
                        <option value="staff">Only staff</option>
                        <option value="client">Only client</option>
                    </select>
                    <select  class="form-control search-input log-type" id="search_values" column_name="type">
                        <option value="">Type</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                    </select>
                    <input type="number" class="form-control search-input" id="search_values" column_name="user" placeholder="User ID" value="{{$search_arr['user']}}">
                    <select  class="form-control search-input" id="search_values" column_name="role">
                        <option value="">Role</option>
                        @foreach($roles as $role)
                            @if($role->id == $search_arr['role'])
                                <option selected value="{{$role->id}}">{{$role->role}}</option>
                            @else
                                <option value="{{$role->id}}">{{$role->role}}</option>
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
                    <th class="columns" onclick="sort_by('login_log.id')">#</th>
                    <th class="columns" onclick="sort_by('login_log.user_id')">User ID</th>
                    <th class="columns" onclick="sort_by('users.username')">Username</th>
                    <th class="columns" onclick="sort_by('roles.role')">Role</th>
                    <th class="columns" onclick="sort_by('login_log.ip')">IP</th>
                    <th class="columns" onclick="sort_by('login_log.type')">Type</th>
                    <th class="columns" onclick="sort_by('login_log.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr class="rows" id="row_{{$log->id}}" onclick="select_row({{$log->id}})">
                        <td>{{$log->id}}</td>
                        <td><a target="_blank" style="color: #1f314c;" href="{{route("show_clients")}}?search=1&suite={{$log->user_id}}">{{$log->user_id}}</a></td>
                        <td><a target="_blank" style="color: #1f314c;" href="{{route("show_clients")}}?search=1&suite={{$log->user_id}}">{{$log->username}}</a></td>
                        <td>{{$log->role}}</td>
                        <td>{{$log->ip}}</td>
                        <td>{{$log->type}}</td>
                        <td>{{$log->created_at}}</td>
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
    <script>
        $(document).ready(function(){
            let type = '{{$search_arr['type']}}';
            $(".log-type").val(type);

            let users_type = '{{$search_arr['users_type']}}';
            $(".log-users-type").val(users_type);
        });
    </script>
@endsection
