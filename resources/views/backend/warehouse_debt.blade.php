@extends('backend.app')
@section('title')
    Positions
@endsection
@section('actions')
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
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
                    <th class="columns" onclick="sort_by('amount')">Amount</th>
                    <th class="columns" onclick="sort_by('day')">Day</th>
                    <th class="columns" onclick="sort_by('limitDay')">Limit Day</th>
                    <th class="columns" onclick="sort_by('type')">Type</th>
                </tr>
                </thead>
                <tbody>
                @foreach($debts as $debt)
                    <tr class="rows" id="row_{{$debt->id}}" onclick="select_row({{$debt->id}})">
                        <td>{{$debt->id}}</td>
                        <td id="amount_{{$debt->id}}">{{$debt->amount}}</td>
                        <td id="day_{{$debt->id}}">{{$debt->day}}</td>
                        <td id="limitDay_{{$debt->id}}">{{$debt->limitDay}}</td>
                        <td id="type_{{$debt->id}}">{{$debt->type}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
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
                                class="modal-title">Add position</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="/warehouse-debt/update" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="name">Type:</label>
                                    <input type="text" name="type" id="type" disabled maxlength="50">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="name">Amount: <font color="red">*</font></label>
                                    <input type="text" name="amount" id="amount">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="name">Day: <font color="red">*</font></label>
                                    <input type="number" name="day" id="day">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="name">Limit day: <font color="red">*</font></label>
                                    <input type="number" name="limitDay" id="limitDay" >
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
            $(".add_or_update_form").prop("action", "{{route("update_warehouse_debt")}}");
            $('.modal-title').html('Update debt');

            $("#amount").val($("#amount_" + row_id).text());
            $("#day").val($("#day_" + row_id).text());
            $("#limitDay").val($("#limitDay_" + row_id).text());
            $("#type").val($("#type_" + row_id).text());

            $('#add-modal').modal('show');
        }
    </script>
@endsection