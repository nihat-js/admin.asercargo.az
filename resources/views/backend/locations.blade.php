@extends('backend.app')
@section('title')
    Locations
@endsection
@section('actions')
    <li>
        <a onclick="show_positions();" class="action-btn"><span class="glyphicon glyphicon-list"></span> Show positions</a>
    </li>
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_location")}}')"><span
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
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('id')">No</th>
                    <th class="columns" onclick="sort_by('name')">Location</th>
                    <th class="columns" onclick="sort_by('city')">City</th>
                    <th class="columns" onclick="sort_by('country')">Country</th>
{{--                    <th class="columns" onclick="sort_by('is_volume')">Volume consider</th>--}}
                    <th class="columns" onclick="sort_by('created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($locations as $location)
                    <tr class="rows" id="row_{{$location->id}}" onclick="select_row({{$location->id}});">
                        <td>{{$location->id}}</td>
                        <td id="name_{{$location->id}}">{{$location->name}}</td>
                        <td id="city_{{$location->id}}">{{$location->city}}</td>
                        <td id="country_{{$location->id}}" country_id="{{$location->country_id}}">{{$location->country}}</td>
{{--                        <td>--}}
{{--                            @php($switch_checked = '')--}}
{{--                            @if($location->is_volume == 1)--}}
{{--                                @php($switch_checked = 'checked')--}}
{{--                            @endif--}}
{{--                            <label class="switch">--}}
{{--                                <input type="checkbox" {{$switch_checked}} oninput="change_volume_switch(this, {{$location->id}}, '{{route("change_volume_consider")}}');">--}}
{{--                                <span class="slider round"></span>--}}
{{--                            </label>--}}
{{--                        </td>--}}
                        <td>{{$location->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $locations->links(); !!}
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
                        <span class="masha_index masha_index1" rel="1"></span><span class="modal-title">Add location</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="/locations/add" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-12">
                                <p class="name">
                                    <label for="name">Name: <font color="red">*</font></label>
                                    <input type="text" name="name" id="name" required=""  maxlength="50">
                                </p>
                                <p class="surname">
                                    <label for="city">City: <font color="red">*</font></label>
                                    <input type="text" name="city" id="city" required=""  maxlength="50">
                                </p>
                                <p class="sec">
                                    <label for="country_id">Country: <font color="red">*</font></label>
                                    <select name="country_id" id="country_id" required>
                                        <option value="">Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{$country->id}}">{{$country->name}}</option>
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
            $(".add_or_update_form").prop("action", "{{route("add_location")}}");
            $('.modal-title').html('Add location');

            $("#name").val("");
            $("#city").val("");
            $("#country_id").val("");

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
            $(".add_or_update_form").prop("action", "{{route("update_location")}}");
            $('.modal-title').html('Update location');

            $("#name").val($("#name_" + row_id).text());
            $("#city").val($("#city_" + row_id).text());
            $("#country_id").val($("#country_" + row_id).attr("country_id"));

            $('#add-modal').modal('show');
        }

        function show_positions() {
            let id = 0;
            id = row_id;
            if (id === 0) {
                swal(
                    'Warning',
                    'Please select location!',
                    'warning'
                );
                return false;
            }

            get_positions(id);
        }

        function get_positions(location_id) {
            let url;
            url = "{{route("show_positions")}}" + "?search=1&location=" + location_id;
            location.href = url;
        }
    </script>
@endsection
