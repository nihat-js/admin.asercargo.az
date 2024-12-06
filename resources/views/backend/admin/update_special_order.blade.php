@extends('backend.app')
@section('title')
    All orders
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
        @php($suite = $order->suite)
        @php($suite_len = strlen($order->suite))
        @if($suite_len < 6)
            @for($i = 0; $i < 6 - $suite_len; $i++)
                @php($suite = '0' . $suite)
            @endfor
        @endif
        @php($rate = ($order->single_price * 5) / 100)
        {{--        @php($total_price = $order->price + $rate)--}}
            @if($order->placed_by == null && $order->canceled_by == null)
            <form id="update_special_order_form" action="{{route("post_update_special_order", $order->id)}}"
                  method="post">
                @csrf
                @endif
                <div class="references-in">
                    <table class="references-table">
                        <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Suite</th>
                            <th>Phone</th>
                            <th>E-mail</th>
                            <th>Language</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>#{{$order->id}}</td>
                            <td>{{$suite}}</td>
                            <td>{{$order->phone}}</td>
                            <td>{{$order->email}}</td>
                            <td>{{$order->language}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    <div class="col-md-12" id="disable_or_enable_order" style="margin-bottom: 10px;">
                        @if($order->disable == 0)
                            <span
                                onclick="disable_special_order_for_client({{$order->id}}, '{{route("disable_order_for_client", $order->id)}}');"
                                class="btn btn-danger">Disable order for client</span>
                        @else
                            <span
                                onclick="enable_special_order_for_client({{$order->id}}, '{{route("enable_order_for_client", $order->id)}}');"
                                class="btn btn-success">Enable order for client</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <input type="text" disabled class="form-control" value="{{$order->name}} {{$order->surname}}">
                    </div>
                    <div class="col-md-6">
                        <input type="text" disabled class="form-control" value="{{$order->country}}">
                    </div>
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    <div class="col-md-8">
                        <input type="text" disabled class="form-control" value="{{$order->url}}">
                    </div>
                    <div class="col-md-4">
                        <a href="{{$order->url}}" class="btn btn-warning"><i class="glyphicon glyphicon-link"></i> Go to
                            order</a>
                    </div>
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    <input type="text" class="form-control" name="title" value="{{$order->title}}" placeholder="Title">
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="quantity" value="{{$order->quantity}}" min="0"
                               required>
                    </div>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="price" value="{{$order->single_price}}" min="0"
                               step="0.01" required>
                    </div>
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    <div class="col-md-6">
                        <input type="text" class="form-control" value="{{$order->color}}" placeholder="Color">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" value="{{$order->size}}" placeholder="Size">
                    </div>
                </div>

                <div class="col-md-12" style="margin: 15px 0;">
                <textarea class="form-control" name="description" placeholder="Description">{{$order->description}}</textarea>
                </div>

                <div class="references-in">
                    <table class="references-table">
                        <thead>
                        <tr>
                            <th>Price</th>
                            <th>5%</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{$order->single_price * $order->quantity}} {{$order->currency}}</td>
                            <td>{{$rate}} {{$order->currency}}</td>
                            <td>{{$order->price}} {{$order->currency}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="common_debt" value="{{$order->common_debt}}"
                               min="0"
                               step="0.01">
                    </div>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="cargo_debt" value="{{$order->cargo_debt}}"
                               min="0"
                               step="0.01">
                    </div>
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    <select class="form-control" name="status_id" required>
                        @foreach($statuses as $status)
                            @if($status->id == $order->last_status_id)
                                <option selected value="{{$status->id}}">{{$status->status}}</option>
                            @else
                                <option value="{{$status->id}}">{{$status->status}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12" style="margin-top: 15px;">
                    {{--                <button type="button" class="btn btn-default">Order placed</button>--}}
                    {{--                <button type="button" class="btn btn-default">Status canceled</button>--}}
                    @if($order->placed_by == null && $order->canceled_by == null)
                        <button type="submit" class="btn btn-primary">Update</button>
                    @endif
                </div>
                @if($order->placed_by == null && $order->canceled_by == null)
            </form>
        @endif
    </div>

@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#update_special_order_form').ajaxForm({
                beforeSubmit: function () {
                    //loading
                    swal({
                        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                        text: 'Loading, please wait...',
                        showConfirmButton: false
                    });
                },
                success: function (response) {
                    form_submit_message(response, false);
                }
            });
        });
    </script>
@endsection
