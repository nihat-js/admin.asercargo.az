@extends('backend.app')
@section('title')
    Courier | Zones
@endsection
@section('actions')
    <li>
        <a onclick="show_payment_types_modal();" class="action-btn"><span class="glyphicon glyphicon-list"></span> Payment types</a>
    </li>
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("admin_courier_zone_delete")}}')"><span
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
                           placeholder="Zone" value="{{$search_arr['name']}}">
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
                    <th class="columns" onclick="sort_by('default_tariff')">Default tariff</th>
                    <th class="columns" onclick="sort_by('created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($zones as $zone)
                    <tr class="rows" id="row_{{$zone->id}}" onclick="select_row({{$zone->id}})">
                        <td>{{$zone->id}}</td>
                        <td id="name_{{$zone->id}}" name_en="{{$zone->name_en}}" name_az="{{$zone->name_az}}" name_ru="{{$zone->name_ru}}">{{$zone->name_en}}</td>
                        <td id="default_tariff_{{$zone->id}}">{{$zone->default_tariff}}</td>
                        <td>{{$zone->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $zones->links(); !!}
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
                            <div class="col-md-12">
                                <p class="name">
                                    <label for="default_tariff">Default tariff: <font color="red">*</font></label>
                                    <input type="number" name="default_tariff" id="default_tariff" required="" min="0" step="0.01">
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

    <!-- start payment types modal-->
    <div class="modal fade" id="payment-types-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1">Payment types</span>
                    </div>
                </div>
                <form id="payment_type_form" action="{{route("admin_courier_payment_type_for_zones_add")}}" method="post">
                    {{csrf_field()}}
                    <input type="hidden" id="zone_id_for_payment_types" name="zone_id" value="null">
                    <div class="modal-body">
                        <div class="form row">
                            <div class="references-in">
                                <table class="references-table" style="width: 70%; margin: 0 13% 10px;">
                                    <thead>
                                    <tr>
                                        <th>Delivery</th>
                                        <th>Courier</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="payment_types_table">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="sec">
                                    <label for="delivery_payment_type_id">Delivery: <font color="red">*</font></label>
                                    <select name="delivery_payment_type_id" id="delivery_payment_type_id">
                                        <option value="">Select</option>
                                        @foreach($payment_types as $payment_type)
                                            <option value="{{$payment_type->id}}">{{$payment_type->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="sec">
                                    <label for="courier_payment_type_id">Courier: <font color="red">*</font></label>
                                    <select name="courier_payment_type_id" id="courier_payment_type_id">
                                        <option value="">Select</option>
                                        @foreach($payment_types as $payment_type)
                                            <option value="{{$payment_type->id}}">{{$payment_type->name}}</option>
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
    <!-- /.end payment types modal-->
@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#form').ajaxForm({
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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_zone_add")}}");
            $('.modal-title').html('Add zone');

            $("#name_en").val("");
            $("#name_az").val("");
            $("#name_ru").val("");
            $("#default_tariff").val(0);

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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_zone_update")}}");
            $('.modal-title').html('Update zone');

            $("#name_en").val($("#name_" + row_id).attr("name_en"));
            $("#name_az").val($("#name_" + row_id).attr("name_az"));
            $("#name_ru").val($("#name_" + row_id).attr("name_ru"));
            $("#default_tariff").val($("#default_tariff_" + row_id).text());

            $('#add-modal').modal('show');
        }

        function show_payment_types_modal() {
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

            show_payment_types('{{route("admin_get_payment_types_for_zones")}}', id, '{{route("admin_payment_type_for_zones_delete")}}');
        }

        $(document).ready(function () {
            $('#payment_type_form').ajaxForm({
                beforeSubmit: function () {
                    //loading
                    swal({
                        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                        text: 'Loading, please wait...',
                        showConfirmButton: false
                    });
                },
                success: function (response) {
                    form_submit_message(response, false, false);
                    if (response.case === 'success') {
                        show_payment_types('{{route("admin_get_payment_types_for_zones")}}', row_id, '{{route("admin_payment_type_for_zones_delete")}}');
                    }
                }
            });
        });
    </script>
@endsection