@extends('backend.app')
@section('title')
    Packages
@endsection
@section('actions')
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Actions <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li>
                <a onclick="status_history_for_package('{{route("admin_show_status_history_for_package")}}');" class="action-btn">Status history</a>
            </li>
            <li>
                <a onclick="open_change_branch_modal();" class="action-btn">Change branch</a>
            </li>
            <li>
                <a onclick="change_status_for_package_modal();" class="action-btn">Change status</a>
            </li>
            <li>
                <a onclick="change_weight_for_package_modal();" class="action-btn">Change weight</a>
            </li>
            <li>
                <a onclick="change_client_for_package_modal('{{route("change_client_for_package")}}');"
                   class="action-btn">Change client</a>
            </li>
            <li>
                <a class="action-btn" onclick="del('{{ route('delete_from_customs') }}')">Delete from Customs</a>
            </li>
            <li>
                <a class="action-btn" onclick="del('{{route("delete_package")}}')">Delete</a>
            </li>
        </ul>
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
                    <input type="text" class="form-control search-input" id="search_values" column_name="number"
                           placeholder="Track number" value="{{$search_arr['number']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="client"
                           placeholder="Client" value="{{$search_arr['client']}}">
                    <select class="form-control search-input" id="search_values" column_name="seller">
                        <option value="">Seller</option>
                        @foreach($sellers as $seller)
                            @if($seller->id == $search_arr['seller'])
                                <option selected value="{{$seller->id}}">{{$seller->name}}</option>
                            @else
                                <option value="{{$seller->id}}">{{$seller->name}}</option>
                            @endif
                        @endforeach
                    </select>
                    <select class="form-control search-input" id="search_values" column_name="status">
                        <option value="">Status</option>
                        @foreach($statuses as $status)
                            @if($status->id == $search_arr['status'])
                                <option selected value="{{$status->id}}">{{$status->status}}</option>
                            @else
                                <option value="{{$status->id}}">{{$status->status}}</option>
                            @endif
                        @endforeach
                    </select>
                    <select class="form-control search-input" id="search_values" column_name="location">
                        <option value="">Current location</option>
                        <option value="container">In container</option>
                        @foreach($locations as $location)
                            @if($location->id == $search_arr['location'])
                                <option selected value="{{$location->id}}">{{$location->name}}</option>
                            @else
                                <option value="{{$location->id}}">{{$location->name}}</option>
                            @endif
                        @endforeach
                    </select>
                    <select class="form-control search-input" id="search_values" column_name="departure">
                        <option value="">Departure</option>
                        @foreach($locations as $location)
                            @if($location->id == $search_arr['departure'])
                                <option selected value="{{$location->id}}">{{$location->name}}</option>
                            @else
                                <option value="{{$location->id}}">{{$location->name}}</option>
                            @endif
                        @endforeach
                    </select>
                    <select class="form-control search-input" id="search_values" column_name="destination">
                        <option value="">Destination</option>
                        @foreach($locations as $location)
                            @if($location->id == $search_arr['destination'])
                                <option selected value="{{$location->id}}">{{$location->name}}</option>
                            @else
                                <option value="{{$location->id}}">{{$location->name}}</option>
                            @endif
                        @endforeach
                    </select>
{{--                    <select class="form-control search-input invoice_status" id="search_values"--}}
{{--                            column_name="invoice_status">--}}
{{--                        <option value="">Invoice status</option>--}}
{{--                        <option value="no_invoice">No invoice</option>--}}
{{--                        <option value="not_confirmed">Not confirmed</option>--}}
{{--                        <option value="confirmed">Confirmed</option>--}}
{{--                    </select>--}}
                    
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('package.number')">Track number</th>
                    <th class="columns" onclick="sort_by('st.status')">Status</th>
                    <th class="columns" onclick="sort_by('l.name')">Location/Position</th>
                    <th class="columns" onclick="sort_by('dep.name')">Departure</th>
                    <th class="columns">Branch office</th>
                    {{-- <th class="columns" onclick="sort_by('des.name')">Destination</th> --}}
                    <th class="columns" onclick="sort_by('item.price')">Invoice</th>
{{--                    <th class="columns">Doc.</th>--}}
                    <th class="columns">#</th>
                    <th class="columns" onclick="sort_by('package.height')">Height</th>
                    <th class="columns" onclick="sort_by('package.width')">Width</th>
                    <th class="columns" onclick="sort_by('package.length')">Length</th>
                    <th class="columns" onclick="sort_by('package.gross_weight')">Gross weight</th>
                    <th class="columns" onclick="sort_by('package.total_charge_value')">Amount</th>
                    <th class="columns" onclick="sort_by('package.paid')">Paid</th>
                    <th class="columns" onclick="sort_by('c.name')">Client</th>
                    <th class="columns" onclick="sort_by('s.name')">Seller</th>
                    <th class="columns">Carrier Status</th>
                    <th class="columns">Carrier RegNumber</th>
                    <th class="columns" onclick="sort_by('package.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($packages as $package)
                    @if($package->position != null)
                        @php($location = $package->location . ' / ' . $package->position)
                    @elseif(!empty($package->container))
                        @php($location = 'CONTAINER' . $package->container)
                    @else
                        @php($location = '---')
                    @endif
                    @if($package->invoice_confirmed == 1)
                        @php($invoice_color = 'green')
                    @else
                        @php($invoice_color = 'red')
                    @endif
                    @if($package->paid_status == 1)
                        @php($paid_color = 'green')
                    @else
                        @php($paid_color = 'red')
                    @endif
                    @php($seller = $package->seller_id == 0 || $package->seller_id == null ? $package->other_seller : $package->seller)
                    <tr class="rows" id="row_{{$package->id}}" onclick="select_row({{$package->id}})">
                        <td>{{$package->number}}</td>
                        <td style="color: {{$package->status_color}};">{{$package->status}}</td>
                        <td>{{$location}}</td>
                        <td>{{$package->departure}}</td>
                         <td>{{$package->branch}}</td>
                        <td style="color: {{$invoice_color}};">{{$package->price}} {{$package->invoice_currency}}</td>
                        <td>
                            @if ($package->invoice_doc != null && $package->invoice_doc != '' && $package->last_status_id == 6)
                                <button onclick="set_declared_status('{{route("set_package_declared_status")}}', {{$package->id}});"
                                        class="btn btn-success btn-xs">Declared
                                </button>
                            @else
                                -
                            @endif
                        </td>
                        <td id="height_{{$package->id}}">{{$package->height}}</td>
                        <td id="width_{{$package->id}}">{{$package->width}}</td>
                        <td id="length_{{$package->id}}">{{$package->length}}</td>
                        <td id="gross_weight_{{$package->id}}">{{$package->gross_weight}}</td>
                        <td style="color: {{$paid_color}};">{{$package->total_charge_value}} {{$package->currency}}</td>
                        <td>{{$package->paid}} {{$package->currency}}</td>
                        <td><a style="color: #1f314c;"
                               href="{{route("show_clients")}}?search=1&suite={{$package->client_id}}">{{$package->client_name}} {{$package->client_surname}}</a>
                        </td>
                        <td>{{$seller}}</td>
                        @if($package->carrier_status_id == 0)
                            <td>Not Send</td>
                        @elseif($package->carrier_status_id == 4)
                            <td>Posted to Customs</td>
                        @elseif($package->carrier_status_id == 1 or $package->carrier_status_id == 2 or $package->carrier_status_id == 3  )
                            <td>Declared : {{ $package->carrier_status_id }}</td>
                        @elseif($package->carrier_status_id == 7)
                            <td>Added To Boxes</td>
                        @elseif($package->carrier_status_id == 8)
                            <td>Depesh</td>
                        @elseif($package->carrier_status_id == 10)
                            <td>Commercial</td>
                        @else
                            <td>unknown</td>
                        @endif
                        <td>{{ $package->carrier_registration_number }}</td>
                        <td>{{$package->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                @if(Auth::user()->role() != 9)
                {!! $packages->links() !!}
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="weight-modal" tabindex="-1" option="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" option="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title">Change weight</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="{{route("change_weight_for_package")}}"
                      method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <input type="hidden" id="package_id" name="package_id" value="">
                            <div class="col-md-6">
                                <p class="option">
                                    <label for="length">Length (cm):</label>
                                    <input type="number" name="length" id="length">
                                </p>
                                <p class="option">
                                    <label for="width">Width (cm):</label>
                                    <input type="number" name="width" id="width">
                                </p>
                                <p class="option">
                                    <label for="height">Height (cm):</label>
                                    <input type="number" name="height" id="height">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="option">
                                    <label for="gross_weight">Gross weight: <font color="red">*</font></label>
                                    <input type="number" name="gross_weight" id="gross_weight" required step="0.001">
                                </p>
                                <p class="sec">
                                    <label for="chargeable_weight">Chargeable:</label>
                                    <select name="chargeable_weight" id="chargeable_weight" required>
                                        <option value="1">Default</option>
                                        <option value="2">Gross weight</option>
                                        <option value="3">Volume weight</option>
                                    </select>
                                </p>
                                <p class="sec">
                                    <label for="tariff_type_id">Type:</label>
                                    <select name="tariff_type_id" id="tariff_type_id" required>
                                        <option value="0">Default</option>
                                        @foreach($tariff_types as $tariff_type)
                                            <option value="{{$tariff_type->id}}">{{$tariff_type->name}}</option>
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

    <div class="modal fade" id="status-modal" tabindex="-1" option="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" option="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title">Change status</span>
                    </div>
                </div>
                <form id="form" action="{{route("admin_change_status_for_single_package")}}" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <input type="hidden" id="package_id_for_status" name="package_id" value="">
                            <div class="col-md-12">
                                <p class="sec">
                                    <label for="status_id">Status:</label>
                                    <select name="status_id" id="status_id" required oninput="package_change_status_event(this);">
                                        <option value="">Select</option>
                                        @if(Auth::id() == 138869)
                                            <option value="2">Paid</option>
                                        @endif
                                        <option value="10">Unpaid</option>
                                        <option value="14">On the way</option>
                                        <option value="15">In baku</option>
                                        <option value="3">Delivered</option>
                                        <option value="37">Not Declared</option>
                                        <option value="29">Detained at customs</option>
                                        <option value="42">Out of delivery</option>
                                        <option value="43">Hold by Customer</option>
                                    {{-- <option value="38">Declared. Duty not paid</option> --}}
                {{--					<option value="39">Declared. Duty paid</option>--}}
                {{--					<option value="40">Declared</option>--}}
                                    </select>
                                </p>
                            </div>
                            <div class="col-md-12" id="paid_back_div" style="display: none;">
                                <p class="sec">
                                    <label for="back_paid">Send the payment back to the balance of the client?</label>
                                    <select name="back_paid" id="back_paid">
                                        <option value="2">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                            </div>
                            <div class="col-md-12" id="paid_from_balance_div" style="display: none;">
                                <p class="sec">
                                    <label for="from_balance">Should the amount be deducted from the client's balance?</label>
                                    <select name="from_balance" id="from_balance">
                                        <option value="1">Yes</option>
                                        <option value="2">No</option>
                                    </select>
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


    <div class="modal fade" id="branch-modal" tabindex="-1" option="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" option="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title">Change Branch</span>
                    </div>
                </div>
                <form id="form" action="{{route("change_branch_for_package")}}" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <input type="hidden"  name="package_id" value="">
                            <div class="col-md-12">
                                <p class="sec">
                                    <label for="branch_id">Filial:</label>
                                    <select name="branch_id" id="branch_id" required oninput="">
                                        <option value="">Seçin</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{$branch->id}}" > {{$branch->name}} </option>
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

    {{--Status history--}}
    <div class="modal fade" id="status-history-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="width: 80%;">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span>Status history
                    </div>
                </div>
                <div class="modal-body">
                    <div style="overflow: auto;">
                        <table class="references-table">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>Date</th>
                            </thead>
                            <tbody id="status-history-body">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            let invoice_status = '{{$search_arr['invoice_status']}}';
            $(".invoice_status").val(invoice_status);
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
                    if (response.case === 'success') {
                        $("#package_id").val('');
                        $("#height").val('');
                        $("#width").val('');
                        $("#length").val('');
                        $("#gross_weight").val('');
                    }
                    form_submit_message(response);
                }
            });
        });

        function package_change_status_event(e) {
            let status = $(e).val();

            if (status == 10) {
                $("#paid_back_div").css('display', 'block');
                $("#paid_from_balance_div").css('display', 'none');
            } else if (status == 2) {
                $("#paid_from_balance_div").css('display', 'block');
                $("#paid_back_div").css('display', 'none');
            } else {
                $("#paid_from_balance_div").css('display', 'none');
                $("#paid_back_div").css('display', 'none');
            }
        }
    </script>
@endsection
