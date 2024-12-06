@extends('backend.app')
@section('title')
    Courier | Daily limits
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("admin_courier_daily_limit_delete")}}')"><span
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
                    <input type="date" class="form-control search-input" id="search_values" column_name="date"
                           value="{{$search_arr['date']}}">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('id')">#</th>
                    <th class="columns" onclick="sort_by('date')">Date</th>
                    <th class="columns" onclick="sort_by('count')">Count</th>
                    <th class="columns" onclick="sort_by('used')">Used</th>
                    <th class="columns" onclick="sort_by('created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($limits as $limit)
                    <tr class="rows" id="row_{{$limit->id}}" onclick="select_row({{$limit->id}})">
                        <td>{{$limit->id}}</td>
                        <td id="date_{{$limit->id}}" >{{$limit->date}}</td>
                        <td id="count_{{$limit->id}}" >{{$limit->count}}</td>
                        <td>{{$limit->used}}</td>
                        <td>{{$limit->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $limits->links(); !!}
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
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="date">Date: <font color="red">*</font></label>
                                    <input type="date" name="date" id="date" required>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="count">Count: <font color="red">*</font></label>
                                    <input type="number" name="count" id="count" required min="0">
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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_daily_limit_add")}}");
            $('.modal-title').html('Add daily limit');

            $("#date").val("");
            $("#count").val(0);

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
            $(".add_or_update_form").prop("action", "{{route("admin_courier_daily_limit_update")}}");
            $('.modal-title').html('Update daily limit');

            $("#date").val($("#date_" + row_id).text());
            $("#count").val($("#count_" + row_id).text());

            $('#add-modal').modal('show');
        }
    </script>
@endsection