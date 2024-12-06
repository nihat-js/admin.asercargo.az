@extends('backend.app')
@section('title')
    Exchange rates
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_exchange_rate")}}')"><span
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
                    <th class="columns" onclick="sort_by('exchange_rate.id')">No</th>
                    <th class="columns" onclick="sort_by('exchange_rate.rate')">Rate</th>
                    <th class="columns" onclick="sort_by('from_cur.name')">From Currency</th>
                    <th class="columns" onclick="sort_by('to_cur.name')">To Currency</th>
                    <th class="columns" onclick="sort_by('exchange_rate.country')">From Date</th>
                    <th class="columns" onclick="sort_by('exchange_rate.country')">To Date</th>
                    <th class="columns" onclick="sort_by('exchange_rate.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($exchange_rates as $exchange_rate)
                    <tr class="rows" id="row_{{$exchange_rate->id}}" onclick="select_row({{$exchange_rate->id}})">
                        <td>{{$exchange_rate->id}}</td>
                        <td id="rate_{{$exchange_rate->id}}">{{$exchange_rate->rate}}</td>
                        <td id="from_currency_id_{{$exchange_rate->id}}" from_currency_id="{{$exchange_rate->from_currency_id}}">{{$exchange_rate->from_currency}}</td>
                        <td id="to_currency_id_{{$exchange_rate->id}}" to_currency_id="{{$exchange_rate->to_currency_id}}">{{$exchange_rate->to_currency}}</td>
                        <td id="from_date_{{$exchange_rate->id}}">{{$exchange_rate->from_date}}</td>
                        <td id="to_date_{{$exchange_rate->id}}">{{$exchange_rate->to_date}}</td>
                        <td>{{$exchange_rate->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $exchange_rates->links(); !!}
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
                        <span class="masha_index masha_index1" rel="1"></span><span class="modal-title">Add exchange_rate</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="/exchange_rates/add" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="rate">Rate: <font color="red">*</font></label>
                                    <input type="number" step="0.001" name="rate" id="rate" required=""  maxlength="50">
                                </p>
                                <p class="sec">
                                    <label for="from_currency_id">From currency: <font color="red">*</font></label>
                                    <select name="from_currency_id" id="from_currency_id" required>
                                        <option value="">From</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{$currency->id}}">{{$currency->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="sec">
                                    <label for="to_currency_id">To currency: <font color="red">*</font></label>
                                    <select name="to_currency_id" id="to_currency_id" required>
                                        <option value="">To</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{$currency->id}}">{{$currency->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="name">
                                    <label for="from_date">From date: <font color="red">*</font></label>
                                    <input type="date" name="from_date" id="from_date" required="">
                                </p>
                                <p class="name">
                                    <label for="to_date">To date: <font color="red">*</font></label>
                                    <input type="date" name="to_date" id="to_date" required="">
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
            $(".add_or_update_form").prop("action", "{{route("add_exchange_rate")}}");
            $('.modal-title').html('Add exchange_rate');

            $("#rate").val(0);
            $("#from_currency_id").val("");
            $("#to_currency_id").val("");
            $("#from_date").val("");
            $("#to_date").val("");

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
            $(".add_or_update_form").prop("action", "{{route("update_exchange_rate")}}");
            $('.modal-title').html('Update exchange_rate');

            $("#rate").val($("#rate_" + row_id).text());
            $("#from_currency_id").val($("#from_currency_id_" + row_id).attr("from_currency_id"));
            $("#to_currency_id").val($("#to_currency_id_" + row_id).attr("to_currency_id"));
            $("#from_date").val($("#from_date_" + row_id).text());
            $("#to_date").val($("#to_date_" + row_id).text());

            $('#add-modal').modal('show');
        }
    </script>
@endsection