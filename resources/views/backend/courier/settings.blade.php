@extends('backend.app')
@section('title')
    Courier | Settings
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
                    <th>Daily limit</th>
                    <th>Closing time</th>
                    <th>Amount for urgent</th>
                    <th>Last update date</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{$settings->daily_limit}}</td>
                    <td>{{substr($settings->closing_time, 0, 5)}}</td>
                    <td>{{$settings->amount_for_urgent}}</td>
                    <td>{{$settings->updated_at}}</td>
                    <td>
                        <a class="btn btn-warning btn-xs" href="{{route("admin_courier_show_settings_log")}}">LOG</a>
                    </td>
                </tr>
                </tbody>
            </table>
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
                        <span class="masha_index masha_index1" rel="1"></span>Update settings
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="{{route("admin_courier_settings_update")}}" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="col-md-6">
                            <p class="name">
                                <label for="daily_limit">Daily limit: <font color="red">*</font></label>
                                <input type="number" name="daily_limit" id="daily_limit" required="" min="0" value="{{$settings->daily_limit}}">
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="name">
                                <label for="closing_time">Closing time: <font color="red">*</font></label>
                                <input type="time" name="closing_time" id="closing_time" required="" value="{{$settings->closing_time}}">
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="name">
                                <label for="amount_for_urgent">Amount for urgent: <font color="red">*</font></label>
                                <input type="number" name="amount_for_urgent" id="amount_for_urgent" required="" min="0" step="0.01" value="{{$settings->amount_for_urgent}}">
                            </p>
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
            $('#add-modal').modal('show');
        }
    </script>
@endsection