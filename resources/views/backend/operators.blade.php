@extends('backend.app')
@section('title')
    Operators
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_operator")}}')"><span
                    class="glyphicon glyphicon-trash"></span> Delete</a>
    </li>
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
                    <input type="text" class="form-control search-input" id="search_values" column_name="name" placeholder="Name" value="{{$search_arr['name']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="surname" placeholder="Surname" value="{{$search_arr['surname']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="email" placeholder="E-mail" value="{{$search_arr['email']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="phone" placeholder="Phone" value="{{$search_arr['phone']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="username" placeholder="Username" value="{{$search_arr['username']}}">
                    <select class="form-control search-input" id="search_values" column_name="role">
                        <option value="">Role</option>
                        @foreach($roles as $role)
                            @if($role->id == $search_arr['role'])
                                <option selected value="{{$role->id}}">{{$role->role}}</option>
                            @else
                                <option value="{{$role->id}}">{{$role->role}}</option>
                            @endif
                        @endforeach
                    </select>
                    <select class="form-control search-input" id="search_values" column_name="location">
                        <option value="">Location</option>
                        @foreach($locations as $location)
                            @if($location->id == $search_arr['location'])
                                <option selected value="{{$location->id}}">{{$location->name}}</option>
                            @else
                                <option value="{{$location->id}}">{{$location->name}}</option>
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
                    <th class="columns" onclick="sort_by('users.id')">No</th>
                    <th class="columns" onclick="sort_by('users.username')">Username</th>
                    <th>First password</th>
                    <th class="columns" onclick="sort_by('users.name')">Name</th>
                    <th class="columns" onclick="sort_by('users.surname')">Surname</th>
                    <th class="columns" onclick="sort_by('users.email')">E-mail</th>
                    <th class="columns" onclick="sort_by('users.phone1')">Phone</th>
                    <th class="columns" onclick="sort_by('l.name')">Location</th>
                    <th class="columns" onclick="sort_by('r.role')">Role</th>
                    <th class="columns" onclick="sort_by('users.branch_id')">Branch office</th>
                    <th class="columns" onclick="sort_by('users.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($operators as $operator)
                    <tr class="rows" id="row_{{$operator->id}}" onclick="select_row({{$operator->id}})">
                        <td>{{$operator->id}}</td>
                        <td id="username_{{$operator->id}}">{{$operator->username}}</td>
                        <td id="first_password_{{$operator->id}}"><span class="btn btn-default btn-xs"
                                                                      onclick="show_password('{{$operator->first_pass}}', {{$operator->id}});">show password</span>
                        </td>
                        <td id="name_{{$operator->id}}">{{$operator->name}}</td>
                        <td id="surname_{{$operator->id}}">{{$operator->surname}}</td>
                        <td id="email_{{$operator->id}}">{{$operator->email}}</td>
                        <td id="phone_{{$operator->id}}">{{$operator->phone1}}</td>
                        <td id="destination_id_{{$operator->id}}" destination_id="{{$operator->destination_id}}">{{$operator->location}}</td>
                        <td id="role_id_{{$operator->id}}" role_id="{{$operator->role_id}}">{{$operator->role}}</td>
                        <td id="branch_id_{{$operator->id}}" branch_id="{{$operator->branch_id}}">{{$operator->branch_name}}</td>
                        <td>{{$operator->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $operators->links(); !!}
            </div>
        </div>
    </div>

    <!-- start add modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" data-backdrop="static"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span class="modal-title">Add operator</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="/operators/add" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="name">Name: <font color="red">*</font></label>
                                    <input type="text" name="name" id="name" required=""  maxlength="255">
                                </p>
                                <p class="surname">
                                    <label for="surname">Surname: <font color="red">*</font></label>
                                    <input type="text" name="surname" id="surname" required=""  maxlength="255">
                                </p>
                                <p class="email">
                                    <label for="email">E-mail: <font color="red">*</font></label>
                                    <input type="email" name="email" id="email" required="" maxlength="255">
                                </p>
                                <p class="phone1">
                                    <label for="phone1">Phone: <font color="red">*</font></label>
                                    <input type="text" name="phone1" id="phone1" required="" maxlength="30">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="sec packing_service_id">
                                    <label for="destination_id">Location: <font color="red">*</font></label>
                                    <select name="destination_id" id="destination_id" required>
                                        <option value="">Select</option>
                                        @foreach($locations as $location)
                                            <option value="{{$location->id}}">{{$location->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="sec packing_service_id">
                                    <label for="role_id">Role: <font color="red">*</font></label>
                                    <select name="role_id" id="role_id" required>
                                        <option value="">Select</option>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}">{{$role->role}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="sec branch_id">
                                    <label for="branch_id">Branch office:</label>
                                    <select name="branch_id" id="branch_id">
                                        <option value="">Select</option>
                                        @foreach($branchs as $branch)
                                            <option value="{{$branch->id}}">{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="username">
                                    <label for="username">Username: <font color="red">*</font></label>
                                    <input type="text" name="username" id="username" required="" maxlength="255">
                                </p>
                                <p class="password">
                                    <label for="password">Password:</label>
                                    <input type="text" name="password" id="password" required="" minlength="6">
                                </p>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                    <div class="modal-footer">
                        <p class="submit">
                            <input type="reset" data-dismiss="modal" value="Cancel">
                            <input type="submit" value="Save" style=" margin-right: 25px;">
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.end add modal-->
@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('form').ajaxForm({
                beforeSubmit:function () {
                    //loading
                    swal ({
                        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                        text: 'Loading, please wait...',
                        showConfirmButton: false
                    });
                },
                success:function (response) {
                    form_submit_message(response);
                }
            });
        });

        function show_add_modal() {
            $('#form_item_id').html("");
            $(".add_or_update_form").prop("action", "{{route("add_operator")}}");
            $('.modal-title').html('Add operator');

            $("#name").val("");
            $("#surname").val("");
            $("#phone1").val("");
            $("#email").val("");
            $("#destination_id").val('');
            $("#role_id").val('');
            $("#username").val("");
            $("#branch_id").val("");
            $("#password").val("").prop("required", true);

            $('#add-modal').modal('show');
        }

        function show_update_modal() {
            let id = 0;
            id = row_id;
            if (id === 0) {
                swal(
                    'Warning',
                    'Please select item!',
                    'warning'
                );
                return false;
            }

            let id_input = '<input type="hidden" name="id" value="' + row_id + '">';

            $('#form_item_id').html(id_input);
            $(".add_or_update_form").prop("action", "{{route("update_operator")}}");
            $('.modal-title').html('Update operator');

            $("#name").val($("#name_" + row_id).text());
            $("#surname").val($("#surname_" + row_id).text());
            $("#phone1").val($("#phone_" + row_id).text());
            $("#email").val($("#email_" + row_id).text());
            $("#destination_id").val($("#destination_id_" + row_id).attr("destination_id"));
            $("#role_id").val($("#role_id_" + row_id).attr("role_id"));
            $("#username").val($("#username_" + row_id).text());
            $("#branch_id").val($("#branch_id_" + row_id).text());
            $("#password").val("").prop("required", false);

            $('#add-modal').modal('show');
        }
    </script>
@endsection
