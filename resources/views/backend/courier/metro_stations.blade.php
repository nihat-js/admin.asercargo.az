@extends('backend.app')
@section('title')
    Courier | Metro stations
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("admin_courier_metro_station_delete")}}')"><span
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
                    <input type="text" class="form-control search-input" id="search_values" column_name="name"
                           placeholder="Metro station" value="{{$search_arr['name']}}">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('id')">#</th>
                    <th class="columns" onclick="sort_by('name_en')">Name</th>
                    <th class="columns" onclick="sort_by('created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($metro_stations as $metro_station)
                    <tr class="rows" id="row_{{$metro_station->id}}" onclick="select_row({{$metro_station->id}})">
                        <td>{{$metro_station->id}}</td>
                        <td id="name_{{$metro_station->id}}" name_en="{{$metro_station->name_en}}"
                            name_az="{{$metro_station->name_az}}"
                            name_ru="{{$metro_station->name_ru}}">{{$metro_station->name_en}}</td>
                        <td>{{$metro_station->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $metro_stations->links(); !!}
            </div>
        </div>
    </div>

    <!-- start add modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title"></span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-12">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#english">English</a></li>
                                    <li><a data-toggle="tab" href="#azerbaijan">Azerbaijan</a></li>
                                    <li><a data-toggle="tab" href="#russian">Russian</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div id="english" class="tab-pane fade in active">
                                        <p class="name">
                                            <label for="name_en">Name: <font color="red">*</font></label>
                                            <input type="text" name="name_en" id="name_en" required="" maxlength="255">
                                        </p>
                                    </div>
                                    <div id="azerbaijan" class="tab-pane fade">
                                        <p class="name">
                                            <label for="name_az">Name: <font color="red">*</font></label>
                                            <input type="text" name="name_az" id="name_az" required="" maxlength="255">
                                        </p>
                                    </div>
                                    <div id="russian" class="tab-pane fade">
                                        <p class="name">
                                            <label for="name_ru">Name: <font color="red">*</font></label>
                                            <input type="text" name="name_ru" id="name_ru" required="" maxlength="255">
                                        </p>
                                    </div>
                                </div>
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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_metro_station_add")}}");
            $('.modal-title').html('Add metro station');

            $("#name_en").val("");
            $("#name_az").val("");
            $("#name_ru").val("");

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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_metro_station_update")}}");
            $('.modal-title').html('Update metro station');

            $("#name_en").val($("#name_" + row_id).attr("name_en"));
            $("#name_az").val($("#name_" + row_id).attr("name_az"));
            $("#name_ru").val($("#name_" + row_id).attr("name_ru"));

            $('#add-modal').modal('show');
        }
    </script>
@endsection