@extends('backend.app')
@section('title')
    AWB
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_awb_collector")}}')"><span
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
                    <input type="text" class="form-control search-input" id="search_values" column_name="series"
                           placeholder="Series" value="{{$search_arr['series']}}">
                    <input type="number" class="form-control search-input" id="search_values" column_name="number"
                           placeholder="Number" value="{{$search_arr['number']}}">
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
                    <select  class="form-control search-input created" id="search_values" column_name="created">
                        <option value="">All</option>
                        <option value="byme">Only those created by me</option>
                    </select>
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('awb.id')">No</th>
                    <th class="columns" onclick="sort_by('awb.series')">Series</th>
                    <th class="columns" onclick="sort_by('awb.number')">Number</th>
                    <th class="columns" onclick="sort_by('l.name')">Warehouse</th>
                    <th class="columns" onclick="sort_by('awb.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($awb_list as $awb)
                    <tr class="rows" id="row_{{$awb->id}}" onclick="select_row({{$awb->id}})">
                        <td>{{$awb->id}}</td>
                        <td id="series_{{$awb->id}}">{{$awb->series}}</td>
                        <td id="number_{{$awb->id}}">{{$awb->number}}</td>
                        <td id="location_id_{{$awb->id}}"
                            location_id="{{$awb->location_id}}">{{$awb->warehouse}}</td>
                        <td>{{$awb->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $awb_list->links(); !!}
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
                                class="modal-title">Add AWB</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="/awb/add" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-12">
                                <p class="name">
                                    <label for="series">Series: <font color="red">*</font></label>
                                    <input type="text" name="series" id="series" required="" maxlength="50" value="501">
                                </p>
                                <p class="name">
                                    <label for="number">Number: <font color="red">*</font></label>
                                    <input type="text" name="number" id="number" required="">
                                </p>
                                <p class="sec">
                                    <label for="city">Location: <font color="red">*</font></label>
                                    <select name="location_id" id="location_id" required>
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
        $(document).ready(function(){
            let created = '{{$search_arr['created']}}';
            $(".created").val(created);
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
            $(".add_or_update_form").prop("action", "{{route("add_awb_collector")}}");
            $('.modal-title').html('Add AWB');

            $("#series").val("501");
            $("#number").val("");
            $("#location_id").val(2);

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
            $(".add_or_update_form").prop("action", "{{route("update_awb_collector")}}");
            $('.modal-title').html('Update AWB');

            $("#series").val($("#series_" + row_id).text());
            $("#number").val($("#number_" + row_id).text());
            $("#location_id").val($("#location_id_" + row_id).attr("location_id"));

            $('#add-modal').modal('show');
        }
    </script>
@endsection
