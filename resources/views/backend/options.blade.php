@extends('backend.app')
@section('title')
    Options
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_option")}}')"><span
                    class="glyphicon glyphicon-trash"></span> Delete</a>
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
                    <th class="columns" onclick="sort_by('device1')">Device 1</th>
                    <th class="columns" onclick="sort_by('device1')">Device 2</th>
                    <th class="columns" onclick="sort_by('l.name')">Location</th>
                    <th class="columns" onclick="sort_by('created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($options as $option)
                    <tr class="rows" id="row_{{$option->id}}" onclick="select_row({{$option->id}})">
                        <td>{{$option->id}}</td>
                        <td id="title_{{$option->id}}">{{$option->title}}</td>
                        <td id="device1_{{$option->id}}">{{$option->device1}}</td>
                        <td id="device2_{{$option->id}}">{{$option->device2}}</td>
                        <td id="location_{{$option->id}}" location_id="{{$option->location_id}}">{{$option->location}}</td>
                        <td>{{$option->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $options->links(); !!}
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
                <form id="form" class="add_or_update_form" action="/options/add" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-12">
                                <p class="option">
                                    <label for="title">Title: <font color="red">*</font></label>
                                    <input type="text" name="title" id="title" required="" maxlength="50">
                                </p>
                                <p class="option">
                                    <label for="device1">Device 1: <font color="red">*</font></label>
                                    <input type="text" name="device1" id="device1" required="" maxlength="50">
                                </p>
                                <p class="option">
                                    <label for="device2">Device 2: <font color="red">*</font></label>
                                    <input type="text" name="device2" id="device2" required="" maxlength="50">
                                </p>
                                <p class="sec">
                                    <label for="location_id">Location:</label>
                                    <select name="location_id" id="location_id">
                                        <option value="">Select</option>
                                        @foreach($locations as $location)
                                            <option value="{{$location->id}}">{{$location->name}}</option>
                                        @endforeach
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
            $(".add_or_update_form").prop("action", "{{route("add_option")}}");
            $('.modal-title').html('Add option');

            $("#title").val("");
            $("#device1").val("");
            $("#device2").val("");
            $("#location_id").val("");

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
            $(".add_or_update_form").prop("action", "{{route("update_option")}}");
            $('.modal-title').html('Update option');

            $("#title").val($("#title_" + row_id).text());
            $("#device1").val($("#device1_" + row_id).text());
            $("#device2").val($("#device2_" + row_id).text());
            $("#location_id").val($("#location_" + row_id).attr("location_id"));

            $('#add-modal').modal('show');
        }
    </script>
@endsection
