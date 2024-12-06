@extends('backend.app')
@section('title')
    Special orders
@endsection
@section('actions')

@endsection
@section('content')
    <div class="col-md-12">
        @if(session('display') == 'block')
            <div class="alert alert-{{session('class')}}" role="alert">
                {{session('message')}}
            </div>
        @endif
{{--        <div class="panel panel-default">--}}
{{--            <div class="panel-body">--}}
{{--                <div id="search-inputs-area" class="search-areas">--}}
{{--                    <input type="text" class="form-control search-input" id="search_values" column_name="no" placeholder="No" value="{{$search_arr['no']}}">--}}
{{--                    <input type="text" class="form-control search-input" id="search_values" column_name="number" placeholder="Track number" value="{{$search_arr['number']}}">--}}
{{--                    <input type="text" class="form-control search-input" id="search_values" column_name="client" placeholder="Client" value="{{$search_arr['client']}}">--}}
{{--                    <select  class="form-control search-input" id="search_values" column_name="seller">--}}
{{--                        <option value="">Seller</option>--}}
{{--                        @foreach($sellers as $seller)--}}
{{--                            @if($seller->id == $search_arr['seller'])--}}
{{--                                <option selected value="{{$seller->id}}">{{$seller->name}}</option>--}}
{{--                            @else--}}
{{--                                <option value="{{$seller->id}}">{{$seller->name}}</option>--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                    <select  class="form-control search-input" id="search_values" column_name="status">--}}
{{--                        <option value="">Status</option>--}}
{{--                        @foreach($statuses as $status)--}}
{{--                            @if($status->id == $search_arr['status'])--}}
{{--                                <option selected value="{{$status->id}}">{{$status->status}}</option>--}}
{{--                            @else--}}
{{--                                <option value="{{$status->id}}">{{$status->status}}</option>--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('special_orders.id')">#</th>
                    <th class="columns" onclick="sort_by('client.id')">Suite</th>
                    <th class="columns" onclick="sort_by('client.name')">Client</th>
                    <th class="columns" onclick="sort_by('c.name')">Country</th>
                    <th class="columns" onclick="sort_by('s.status')">Status</th>
                    <th class="columns" onclick="sort_by('operator.name')">Operator</th>
                    <th class="columns" onclick="sort_by('special_orders.price')">Price</th>
                    <th class="columns" onclick="sort_by('c.declarated_at')">Declaration</th>
                    <th class="columns" onclick="sort_by('c.created_at')">Created date</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    @php($suite = $order->suite)
                    @php($suite_len = strlen($order->suite))
                    @if($suite_len < 6)
                        @for($i = 0; $i < 6 - $suite_len; $i++)
                            @php($suite = '0' . $suite)
                        @endfor
                    @endif
                    <tr class="rows" id="row_{{$order->id}}" onclick="select_row({{$order->id}})">
                        <td>{{$order->pay_id}}</td>
                        <td>{{$suite}}</td>
                        <td>{{$order->name}} {{$order->surname}}</td>
                        <td>{{$order->country}}</td>
                        <td>{{$order->status}}</td>
                        <td>{{$order->operator_name}} {{$order->operator_surname}}</td>
                        <td><span style="display: block;">{{$order->price}} {{$order->currency}}</span> <small>({{$order->price_azn}} AZN)</small></td>
                        <td>
                            @if($order->declarated_at == null && $order->canceled_by == null && $order->is_paid == 1 && $order->placed_by != null)
                                <button id="declare_btn_{{$order->id}}" class="btn btn-success btn-xs" onclick="declare_special_order_modal('{{route("declare_special_order", $order->id)}}', {{$order->price}}, {{$order->quantity}}, '{{$order->description}}');">Declare</button>
                            @else
                                <button disabled class="btn btn-warning btn-xs">Declared</button>
                            @endif
                        </td>
                        <td>{{$order->created_at}}</td>
                        <td>
                            <a href="{{route("get_update_special_order", $order->id)}}" style="cursor: pointer;"><i class="glyphicon glyphicon-pencil"></i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $orders->links(); !!}
            </div>
        </div>
    </div>

    <div class="modal fade" id="declare-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                            class="modal-title">Declare</span>
                    </div>
                </div>
                <form id="declare_special_order_form" action="#" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="track">
                                    <label for="track">Track: <font color="red">*</font></label>
                                    <input type="text" name="track" id="track" required maxlength="255">
                                </p>
                                <p class="sec">
                                    <label for="seller_id">Seller: <font color="red">*</font></label>
                                    <select name="seller_id" id="seller_id" required>
                                        <option value="">Select</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{$seller->id}}">{{$seller->title}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="sec">
                                    <label for="category_id">Category: <font color="red">*</font></label>
                                    <select name="category_id" id="category_id" required>
                                        <option value="">Select</option>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="title">
                                    <label for="title">Title:</label>
                                    <input type="text" name="title" id="title" maxlength="255">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="quantity">
                                    <label for="track">Quantity: <font color="red">*</font></label>
                                    <input type="number" name="quantity" id="quantity" required min="0">
                                </p>
                                <p class="price">
                                    <label for="track">Price: <font color="red">*</font></label>
                                    <input type="number" name="price" id="price" required min="0" step="0.01">
                                </p>
                                <p class="description">
                                    <label for="remark">Description:</label>
                                    <input type="text" name="remark" id="remark" maxlength="5000">
                                </p>
                                <p class="invoice">
                                    <label for="invoice">Invoice: <font color="red">*</font></label>
                                    <input type="file" id="invoice" name="invoice" required>
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

@endsection

@section('css')

@endsection

@section('js')
    <script>
        function declare_special_order_modal(url, price, quantity, remark) {
            $("#price").val(price);
            $("#quantity").val(quantity);
            $("#remark").val(remark);

            $("#declare_special_order_form").prop("action", url);
            $('#declare-modal').modal('show');
        }

        $(document).ready(function () {
            $('#declare_special_order_form').ajaxForm({
                beforeSubmit: function () {
                    //loading
                    swal({
                        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                        text: 'Loading, please wait...',
                        showConfirmButton: false
                    });
                },
                success: function (response) {
                    if (response.case === 'success') {
                        $("#declare_btn_" + response.id).removeClass("btn-success").addClass("btn-warning").removeAttr("onclick");
                    }
                    $('#declare-modal').modal('hide');
                    form_submit_message(response, false);
                }
            });
        });
    </script>
@endsection
