@extends('backend.app')
@section('title')
    Branchs
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
@endsection
@section('content')
    <div class="col-md-12">
        @if(session('display') == 'block')
            <div class="alert alert-{{session('class')}}" option="alert">
                {{session('message')}}
            </div>
        @endif
        <div class="panel panel-default">
            <div class="panel-body">
                <div id="search-inputs-area" class="search-areas">
                    <input type="text" class="form-control search-input" id="search_values" column_name="title"
                           placeholder="Title" value="{{$search_arr['title']}}">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('id')">No</th>
                    <th class="columns" onclick="sort_by('title')">Title</th>
                    <th class="columns" onclick="sort_by('device1')">Longitude</th>
                    <th class="columns" onclick="sort_by('device1')">Latitude</th>
                    <th class="columns" onclick="sort_by('l.name')">Active status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($branchs as $option)
                    <tr class="rows" id="row_{{$option->id}}" onclick="select_row({{$option->id}})">
                        <td>{{$option->id}}</td>
                        <td id="title_{{$option->id}}">{{$option->name}}</td>
                        <td id="longitude_{{$option->id}}">{{$option->longitude}}</td>
                        <td id="latitude_{{$option->id}}">{{$option->latitude}}</td>
                        <td id="is_active_{{$option->id}}">{{$option->is_active == 1 ? 'Active' : 'Deactive'}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $branchs->links(); !!}
            </div>
        </div>
    </div>

    <!-- start add modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" option="dialog" data-backdrop="static"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" option="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title">Add option</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="/branch/add" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-12">
                                <p class="option">
                                    <label for="title">Title: <font color="red">*</font></label>
                                    <input type="text" name="name" id="title" required="" maxlength="50">
                                </p>
                                <p class="option">
                                    <label for="device1">Longitude: <font color="red">*</font></label>
                                    <input type="text" name="longitude" id="longitude" maxlength="50">
                                </p>
                                <p class="option">
                                    <label for="device2">Latitute: <font color="red">*</font></label>
                                    <input type="text" name="latitude" id="latitude" maxlength="50">
                                </p>
                                <p class="sec">
                                    <label for="is_active">Location:</label>
                                    <select name="is_active" id="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
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
                beforeSubmit: function () {
                    //loading
                    swal({
                        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                        text: 'Loading, please wait...',
                        showConfirmButton: false
                    });
                },
                success: function (response) {
                    form_submit_message(response);
                }
            });
        });

        function show_add_modal() {
            $('#form_item_id').html("");
            $(".add_or_update_form").prop("action", "{{route("add_branch")}}");
            $('.modal-title').html('Add branch');

            $("#title").val("");
            $("#longitude").val("");
            $("#latitude").val("");
            $("#is_active").val("");

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
            $(".add_or_update_form").prop("action", "{{route("update_branch")}}");
            $('.modal-title').html('Update branch');

            $("#title").val($("#title_" + row_id).text());
            $("#longitude").val($("longitude_" + row_id).text());
            $("#latitude").val($("#latitude_" + row_id).text());
            $("#is_active").val($("#is_active_" + row_id).text());

            $('#add-modal').modal('show');
        }
    </script>
@endsection
