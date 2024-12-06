@extends('backend.app')
@section('title')
    Promo codes | Groups
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="create_promo_codes_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Generate promo codes</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_promo_codes_group")}}')"><span
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
                           value="{{$search_arr['name']}}" placeholder="Name">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('promo_codes_groups.id')">#</th>
                    <th class="columns" onclick="sort_by('promo_codes_groups.name')">Name</th>
                    <th class="columns" onclick="sort_by('promo_codes_groups.percent')">Percent</th>
                    <th class="columns" onclick="sort_by('promo_codes_groups.count')">Count</th>
                    <th class="columns" onclick="sort_by('promo_codes_groups.used_count')">Used count</th>
                    <th class="columns" onclick="sort_by('promo_codes_groups.created_at')">Created date</th>
                    <th class="columns" onclick="sort_by('users.name')">Created by</th>
                </tr>
                </thead>
                <tbody>
                @php($no = 0)
                @foreach($groups as $group)
                    @php($no++)
                    <tr class="rows" id="row_{{$group->id}}" onclick="select_row({{$group->id}})" ondblclick="show_promo_codes({{$group->id}});">
                        <td>{{$no}}</td>
                        <td>{{$group->name}}</td>
                        <td>{{$group->percent}}</td>
                        <td>{{$group->count}}</td>
                        <td>{{$group->used_count}}</td>
                        <td>{{$group->created_at}}</td>
                        <td>{{$group->user_name}} {{$group->user_surname}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $groups->links(); !!}
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
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="name">Name: <font color="red">*</font></label>
                                    <input type="text" name="name" id="name" required maxlength="100">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="percent">Percent: <font color="red">*</font></label>
                                    <input type="number" name="percent" id="percent" required min="0" max="100">
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

    <!-- start create promo codes modal-->
    <div class="modal fade" id="promo-code-modal" tabindex="-1" role="dialog"
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
                <form id="form" class="promo_codes_form" action="" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="count">Count: <font color="red">*</font></label>
                                    <input type="number" name="count" id="count" required>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="sec">
                                    <label for="type">Type: <font color="red">*</font></label>
                                    <select name="type" id="type" required oninput="change_promo_code_type(this);">
                                        <option value="random">Random</option>
                                        <option value="manually">Manually</option>
                                    </select>
                                </p>
                            </div>
                            <div class="col-md-6" id="promo_code_text" style="display: none;">
                                <p class="code">
                                    <label for="code">Promo code: <font color="red">*</font></label>
                                    <input type="text" name="code" id="code" maxlength="15">
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
    <!-- /.end create promo codes modal-->
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
            $(".add_or_update_form").prop("action", "{{route("create_promo_codes_group")}}");
            $('.modal-title').html('Add group');

            $("#name").val("");
            $("#percent").val(0);

            $('#add-modal').modal('show');
        }

        function create_promo_codes_modal() {
            let id = 0;
            id = row_id;
            if (id === 0) {
                swal(
                    'Warning',
                    'Please select group!',
                    'warning'
                );
                return false;
            }

            let id_input = '<input type="hidden" name="id" value="' + row_id + '">';

            $('#form_item_id').html(id_input);
            $(".promo_codes_form").prop("action", "{{route("create_promo_codes")}}");
            $('.modal-title').html('Create promo codes');

            $("#promo_code_text").css('display', 'none');
            $("#count").val(0);
            $("#type").val("random");
            $("#code").val("");

            $('#promo-code-modal').modal('show');
        }

        function change_promo_code_type(e) {
            let type = $(e).val();

            if (type === 'manually') {
                $("#promo_code_text").css('display', 'block');
            } else {
                $("#promo_code_text").css('display', 'none');
            }
        }

        function show_promo_codes(group_id) {
            location.href = '{{route("show_promo_codes")}}?group_id=' + group_id;
        }
    </script>
@endsection