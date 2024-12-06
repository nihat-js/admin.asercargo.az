@extends('backend.app')
@section('title')
    Courier | Areas
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("admin_courier_area_delete")}}')"><span
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
                    <select class="form-control search-input" id="search_values" column_name="zone">
                        <option value="">Zone</option>
                        @foreach($zones as $zone)
                            @if($zone->id == $search_arr['zone'])
                                <option selected value="{{$zone->id}}">{{$zone->name}}</option>
                            @else
                                <option value="{{$zone->id}}">{{$zone->name}}</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="text" class="form-control search-input" id="search_values" column_name="name"
                           placeholder="Area" value="{{$search_arr['name']}}">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('courier_areas.id')">#</th>
                    <th class="columns" onclick="sort_by('zone.name_en')">Zone</th>
                    <th class="columns" onclick="sort_by('courier_areas.name_en')">Name</th>
                    <th class="columns" onclick="sort_by('courier_areas.tariff')">Tariff</th>
                    <th class="columns" onclick="sort_by('courier_areas.active')">Active</th>
                    <th class="columns" onclick="sort_by('courier_areas.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($areas as $area)
                    @if($area->active == 1)
                        @php($switch_checked = 'checked')
                    @else
                        @php($switch_checked = '')
                    @endif
                    <tr class="rows" id="row_{{$area->id}}" onclick="select_row({{$area->id}})">
                        <td>{{$area->id}}</td>
                        <td id="zone_{{$area->id}}" zone_id="{{$area->zone_id}}">{{$area->zone}}</td>
                        <td id="name_{{$area->id}}" name_en="{{$area->name_en}}" name_az="{{$area->name_az}}"
                            name_ru="{{$area->name_ru}}">{{$area->name_en}}</td>
                        <td id="tariff_{{$area->id}}">{{$area->tariff}}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox"
                                       {{$switch_checked}} oninput="change_active_area(this, {{$area->id}}, '{{route("admin_courier_area_active")}}');">
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>{{$area->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $areas->links(); !!}
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
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="sec">
                                    <label for="zone_id">Zone: <font color="red">*</font></label>
                                    <select name="zone_id" id="zone_id" onchange="change_tariff_for_area(this);">
                                        <option value="">Select</option>
                                        @foreach($zones as $zone)
                                            <option value="{{$zone->id}}" id="zone_for_tariff_{{$zone->id}}"
                                                    default_tariff="{{$zone->default_tariff}}">{{$zone->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="tariff">Tariff: <font color="red">*</font></label>
                                    <input type="number" name="tariff" id="tariff" required="" min="0" step="0.01">
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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_area_add")}}");
            $('.modal-title').html('Add area');

            $("#name_en").val("");
            $("#name_az").val("");
            $("#name_ru").val("");
            $("#zone_id").val("");
            $("#tariff").val(0);

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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_area_update")}}");
            $('.modal-title').html('Update area');

            $("#name_en").val($("#name_" + row_id).attr("name_en"));
            $("#name_az").val($("#name_" + row_id).attr("name_az"));
            $("#name_ru").val($("#name_" + row_id).attr("name_ru"));
            $("#zone_id").val($("#zone_" + row_id).attr("zone_id"));
            $("#tariff").val($("#tariff_" + row_id).text());

            $('#add-modal').modal('show');
        }

        function change_tariff_for_area(e) {
            let zone_id = $(e).val();
            let default_tariff = $("#zone_for_tariff_" + zone_id).attr("default_tariff");

            $("#tariff").val(default_tariff);
        }
    </script>
@endsection