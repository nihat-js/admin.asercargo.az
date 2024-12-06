@extends('backend.app')
@section('title')
    Flights
@endsection
@section('actions')
    {{--    <li>--}}
    {{--        <a onclick="set_fact_take_off();" class="action-btn"><span class="glyphicon glyphicon-time"></span> Set fact take off</a>--}}
    {{--    </li>--}}
    {{--    <li>--}}
    {{--        <a onclick="set_fact_arrival();" class="action-btn"><span class="glyphicon glyphicon-time"></span> Set fact arrival</a>--}}
    {{--    </li>--}}
    @if(Auth::user()->role() != 11)
        <li>
            <a onclick="close_flight('{{route("close_flight")}}');" class="action-btn"><span
                        class="glyphicon glyphicon-time"></span> Close flight</a>
        </li>
    @endif

    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_flight")}}')"><span
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
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('id')">No</th>
                    <th class="columns" onclick="sort_by('name')">Name</th>
                    <th class="columns" onclick="sort_by('carrier')">Carrier</th>
                    <th class="columns" onclick="sort_by('flight_number')">Flight</th>
                    <th class="columns" onclick="sort_by('flight_number')">AWB</th>
                    <th class="columns" onclick="sort_by('departure')">Departure</th>
                    <th class="columns" onclick="sort_by('destination')">Destination</th>
                    <th>Packages count</th>
                    <th>Total weight</th>
                    <th class="columns" onclick="sort_by('created_at')">Closed date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($flights as $flight)
                    <tr class="rows" id="row_{{$flight->id}}" onclick="select_row({{$flight->id}})"
                        ondblclick="get_containers_by_flight('{{route("show_containers") . "?search=1&flight=" . $flight->id}}')">
                        <td>{{$flight->id}}</td>
                        <td>{{$flight->name}}</td>
                        <td id="carrier_{{$flight->id}}">{{$flight->carrier}}</td>
                        <td id="flight_number_{{$flight->id}}">{{$flight->flight_number}}</td>
                        <td id="awb_{{$flight->id}}">{{$flight->awb}}</td>
                        <td id="departure_{{$flight->id}}">{{$flight->departure}}</td>
                        <td id="destination_{{$flight->id}}">{{$flight->destination}}</td>
                        <td>{{$flight->packages_count}}</td>
                        <td>{{$flight->total_weight}}</td>
                        <td id="closed_at_{{$flight->id}}">{{substr($flight->closed_at, 0, 16)}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $flights->links(); !!}
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
                        <span class="masha_index masha_index1" rel="1"></span><span
                            class="modal-title">Add flight</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="{{route("add_flight")}}" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="carrier">
                                    <label for="carrier">Carrier: <font color="red">*</font></label>
                                    <input type="text" name="carrier" id="carrier" required="" maxlength="3" value="">
                                </p>
                                <p class="flight_number">
                                    <label for="flight_number">Flight: <font color="red">*</font></label>
                                    <input type="date" name="flight_number" id="flight_number" required>
                                </p>
                                <p class="awb">
                                    <label for="awb">AWB: <font color="red">*</font></label>
                                    <input type="text" name="awb" id="awb" maxlength="15" required="required">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="departure">
                                    <label for="departure">Departure: <font color="red">*</font></label>
                                    <input type="text" name="departure" id="departure" required="" maxlength="50">
                                </p>
                                <p class="destination">
                                    <label for="destination">Destination: <font color="red">*</font></label>
                                    <input type="text" name="destination" id="destination" required="" maxlength="50" value="GYD" readonly>
                                </p>
{{--                                <p class="plan_take_off">--}}
{{--                                    <label for="plan_take_off">Date: <font color="red">*</font></label>--}}
{{--                                    <input size="16" type="text" value="2012-06-15 14:45" readonly class="form_datetime"--}}
{{--                                           name="plan_take_off" id="plan_take_off" required="">--}}
{{--                                </p>--}}
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

    {{--    <!-- start set fact take off modal-->--}}
    {{--    <div class="modal fade" id="set-fact-take-off-modal" tabindex="-1" role="dialog" data-backdrop="static"--}}
    {{--         aria-labelledby="exampleModalLabel"--}}
    {{--         aria-hidden="true">--}}
    {{--        <div class="modal-dialog modal-lg" role="document">--}}
    {{--            <div class="modal-content">--}}
    {{--                <div style="clear: both;"></div>--}}
    {{--                <div class="modal-header">--}}
    {{--                    <button type="button" class="close" data-dismiss="modal">&times;</button>--}}
    {{--                    <div class="modal-heading">--}}
    {{--                        <span class="masha_index masha_index1" rel="1"></span>Set fact take off--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--                <form id="form" action="{{route("set_fact_take_off")}}" method="post">--}}
    {{--                    {{csrf_field()}}--}}
    {{--                    <div id="id_for_fact_take_off"></div>--}}
    {{--                    <div class="modal-body">--}}
    {{--                        <div class="form row">--}}
    {{--                            <div class="col-md-6">--}}
    {{--                                <p class="plan_take_off">--}}
    {{--                                    <label for="fact_take_off">Fact take off: <font color="red">*</font></label>--}}
    {{--                                    <input size="16" type="text" value="" readonly class="form_datetime fact-date-input" name="fact_take_off" id="fact_take_off" required="">--}}
    {{--                                </p>--}}
    {{--                            </div>--}}
    {{--                        </div>--}}
    {{--                    </div>--}}
    {{--                    <div style="clear: both;"></div>--}}
    {{--                    <div class="modal-footer">--}}
    {{--                        <p class="submit">--}}
    {{--                            <input type="reset" data-dismiss="modal" value="Cancel">--}}
    {{--                            <input type="submit" value="Save" style=" margin-right: 25px;">--}}
    {{--                        </p>--}}
    {{--                    </div>--}}
    {{--                </form>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--    <!-- /.end set fact take off modal-->--}}

    {{--    <!-- start set fact arrival modal-->--}}
    {{--    <div class="modal fade" id="set-fact-arrival-modal" tabindex="-1" role="dialog" data-backdrop="static"--}}
    {{--         aria-labelledby="exampleModalLabel"--}}
    {{--         aria-hidden="true">--}}
    {{--        <div class="modal-dialog modal-lg" role="document">--}}
    {{--            <div class="modal-content">--}}
    {{--                <div style="clear: both;"></div>--}}
    {{--                <div class="modal-header">--}}
    {{--                    <button type="button" class="close" data-dismiss="modal">&times;</button>--}}
    {{--                    <div class="modal-heading">--}}
    {{--                        <span class="masha_index masha_index1" rel="1"></span>Set fact arrival--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--                <form id="form" action="{{route("set_fact_arrival")}}" method="post">--}}
    {{--                    {{csrf_field()}}--}}
    {{--                    <div id="id_for_fact_arrival"></div>--}}
    {{--                    <div class="modal-body">--}}
    {{--                        <div class="form row">--}}
    {{--                            <div class="col-md-6">--}}
    {{--                                <p class="plan_take_off">--}}
    {{--                                    <label for="fact_arrival">Fact arrival: <font color="red">*</font></label>--}}
    {{--                                    <input size="16" type="text" value="" readonly class="form_datetime fact-date-input" name="fact_arrival" id="fact_arrival" required="">--}}
    {{--                                </p>--}}
    {{--                            </div>--}}
    {{--                        </div>--}}
    {{--                    </div>--}}
    {{--                    <div style="clear: both;"></div>--}}
    {{--                    <div class="modal-footer">--}}
    {{--                        <p class="submit">--}}
    {{--                            <input type="reset" data-dismiss="modal" value="Cancel">--}}
    {{--                            <input type="submit" value="Save" style=" margin-right: 25px;">--}}
    {{--                        </p>--}}
    {{--                    </div>--}}
    {{--                </form>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--    <!-- /.end set fact arrival modal-->--}}
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset("css/bootstrap-datetimepicker.min.css")}}">
@endsection

@section('js')
    <script src="{{asset("js/bootstrap-datetimepicker.min.js")}}"></script>

    <script>
        $(".form_datetime").datetimepicker({
            format: "yyyy-mm-dd hh:ii",
            autoclose: true,
            todayBtn: true,
            pickerPosition: "bottom-left"
        });

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
            $(".add_or_update_form").prop("action", "{{route("add_flight")}}");
            $('.modal-title').html('Add flight');

            $("#carrier").val("");
            $("#flight_number").val("");
            $("#awb").val("");
            $("#departure").val("");
            $("#destination").val("GYD");
            //$("#plan_take_off").val("");
            // $("#plan_arrival").val("");

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
            $(".add_or_update_form").prop("action", "{{route("update_flight")}}");
            $('.modal-title').html('Update flight');

            $("#carrier").val($("#carrier_" + row_id).text());
            $("#flight_number").val($("#flight_number_" + row_id).text());
            $("#awb").val($("#awb_" + row_id).text());
            $("#departure").val($("#departure_" + row_id).text());
            $("#destination").val($("#destination_" + row_id).text());
            //$("#plan_take_off").val($("#plan_take_off_" + row_id).text());
            // $("#plan_arrival").val($("#plan_arrival_" + row_id).text());

            $('#add-modal').modal('show');
        }

        function set_fact_take_off() {
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

            let input = '<input type="hidden" name="id" value="' + row_id + '">';
            $("#id_for_fact_take_off").html(input);
            $(".fact-date-input").val("");
            $("#set-fact-take-off-modal").modal("show");
        }

        function set_fact_arrival() {
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

            let input = '<input type="hidden" name="id" value="' + row_id + '">';
            $("#id_for_fact_arrival").html(input);
            $(".fact-date-input").val("");
            $("#set-fact-arrival-modal").modal("show");
        }
    </script>
@endsection
