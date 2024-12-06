@extends('backend.app')
@section('title')
	Collector
@endsection
@section('actions')

@endsection
@section('content')
	<div class="col-md-12">
		<div class="references-in">
			<div class="forms">
				<div class="col-md-12" style="text-align: center;">
					<h3 id="time" style="text-align: center; color: #1f314c;"></h3>
				</div>
				<div class="form_files" style="margin-bottom: 20px">
					<div class="form_files_button">

                            <span style="padding: 9px 12px; margin-bottom: 2px;" id="manually-btn"
								  class="btn btn-warning btn-xs"
								  onclick="manually();">Only scan</span>
						<span class="btn btn-basic" id="ok_to_send">Ok to send
							</span>
					</div>
					<div class="form_files_file">
						<h4 style="display: inline-block;">Files:</h4>
						<a href="#" target="_self" class="btn btn-danger btn-xs" id="invoice_doc">Invoice</a>
						@if(Auth::user()->role() == 11)
							<span onclick="print_barcode();" class="btn btn-danger btn-xs"
								  id="print_barcode">Print Barcode</span>
							<span
						@endif
						<span onclick="print_waybill();" class="btn btn-danger btn-xs"
							  id="waybill_doc">Waybill</span>
						<span
								onclick="show_images_gallery_in_collector('{{route("show_images_in_collector")}}', '{{route("delete_image_in_collector")}}');"
								class="btn btn-warning btn-xs"
								id="show_images_btn">Images</span>
						<a href="#" target="_self" class="btn btn-danger btn-xs" id="return_label">Return label</a>
					</div>
					<a href="#" target="_self" class="btn btn-basic btn-xs" id="customs_permission">Smart Customs Permission</a>

					<a href="#" target="_self" class="btn btn-basic btn-xs" id="legal_customer">Legal Entity</a>
					<a href="#" target="_self" class="btn btn-basic btn-xs" id="legal_customer_send_pack">Send all package</a>

					<span class="generate_tracking btn btn-warning btn-xs"
						  onclick="generate_internal_id('{{route("generate_internal_id")}}');">Generate tracking</span>
					<span class="btt btn-basic btn-xs" id="invoice_status_indicator">
							Invoice
						</span>
				</div>

				<div class="alerts">
					<div style="display: block; height: 72px;">
						<div id="response_alert_message" style="display: none;">

						</div>
					</div>
				</div>

				<div class="form_files_selects">

					<div class="form_tracking_suite">
						<div class="form_selects">
							<p class="sec">
								{{--                                <label for="flight_id">Flight:</label>--}}
								<select id="flight_id"
								        oninput="select_flight(this, '{{route("get_containers_by_flight")}}');"
								        class="collector-inputs">
									<option value="">Flights</option>
									@foreach($flights as $flight)
										<option
														value="{{$flight->id}}">{{$flight->name}}</option>
									@endforeach
								</select>
							</p>
							<p class="sec">
								{{--                                <label for="container_select">Container:</label>--}}
								<select id="container_select" disabled oninput="select_container(this);"
								        class="collector-inputs">
									<option value="">Containers</option>
								</select>
							</p>
							<p class="sec">
								{{--                                <label for="position_select">Position:</label>--}}
								<select id="position_select" oninput="select_position(this);" class="collector-inputs">
									<option value="">Positions</option>
									@foreach($positions as $position)
										<option value="{{$position->name}}">{{$position->name}}</option>
									@endforeach
								</select>
							</p>
						</div>
						<div class="form_tracking_suite_2">
							<div class="form_tracking">
								<p class="name">
									<label for="number">Tracking №:</label>
									<input type="text" id="number" maxlength="255" class="collector-track-input"
									       onchange="change_track_number(this, '{{route("check_package_collector")}}');">
								</p>
								<p class="internal_id" style="display: none;">
									<label for="internal_id">Internal ID:</label>
									<input type="text" readonly id="internal_id" maxlength="255" disabled>
								</p>
							</div>
							<div class="form_suite">
								<p class="name">
									<label for="client">Client ID:</label>
									<input type="text" name="client" id="client" required maxlength="50"
									       {{--                                       onfocus="focus_disable();" onblur="focus_active();"--}}
									       onchange="check_client(this, '{{route("check_client")}}');" value="AS"
									       class="collector-inputs">
								</p>
								<p class="client_name" style="display: none;">
									<label for="client_name">N/S:</label>
									<input type="text" readonly id="client_name" maxlength="255" disabled
									       oninput="generate_client_name_and_surname(this);">
								</p>
							</div>
						</div>
					</div>

				</div>
				<div class="form_inputs">
					<p class="gross_weight">
						<label for="gross_weight">Gross w.:</label>
						<input type="number" id="gross_weight" placeholder="kg"
							   class="collector-inputs">
					</p>

					<p class="quantity">
						<label for="quantity">Quantity:</label>
						<input type="number" id="quantity" value="1" class="collector-inputs">
					</p>
					<p class="volume_weight">
						<label for="volume_weight">Volume w.:</label>
						<input readonly type="number" id="volume_weight" placeholder="kg" disabled>
					</p>
					<p class="length">
						<label for="length">Length:</label>
						<input type="number" id="length" placeholder="cm" class="collector-inputs"
						       oninput="calculate_volume();">
					</p>
					<p class="width">
						<label for="width">Width:</label>
						<input type="number" id="width" placeholder="cm" class="collector-inputs"
						       oninput="calculate_volume();">
					</p>
					<p class="height">
						<label for="height">Height:</label>
						<input type="number" id="height" placeholder="cm" class="collector-inputs"
						       oninput="calculate_volume();">
					</p>

					<p class="sec">
						<label for="tariff_type">Type:</label>
						<select id="tariff_type" class="collector-inputs">
							@foreach($types as $type)
								@if($type->id == 1)
									<option selected value="{{$type->id}}">{{$type->name}}</option>
								@else
									<option value="{{$type->id}}">{{$type->name}}</option>
								@endif
							@endforeach
						</select>
					</p>
					<p class="sec">
					
						<label for="category">Category:</label>
						@if(Auth::user()->destination_id == 10)
							<select id="category" class="collector-inputs" onchange="writeValue()"
							
									oninput="select_category('{{route("add_new_category")}}', this)" 
							
							>
								<option value="">Categories</option>
								
								<option value="new">NEW</option>   
					
								@foreach($catHong as $category)
									<option value="{{mb_strtolower($category->name)}}">{{$category->name}}</option>
								@endforeach
							</select>
						@else

						<select id="category" class="collector-inputs" onchange="writeValue()"
							>
								<option value="">Categories</option>
								@foreach($categories as $category)
									<option value="{{mb_strtolower($category->name)}}">{{$category->name}}</option>
								@endforeach
						</select>
					@endif
					</p>
					<p class="sec">
						<label for="seller">Seller:</label>
						<select id="seller" class="collector-inputs"
						        @if(Auth::user()->has_access_for_add_new_seller() == 1)
						        oninput="select_seller('{{route("add_new_seller")}}', this)"
										@endif
										{{--                                                                        oninput="get_default_category_for_seller('{{route("get_category_for_seller")}}', this, false, '{{route("add_new_seller")}}')"--}}
						required="required">
							<option value="">Sellers</option>
							@if(Auth::user()->has_access_for_add_new_seller() == 1)
								<option value="new">NEW</option>
							@endif
							@foreach($sellers as $seller)
								<option value="{{mb_strtolower($seller->name)}}">{{$seller->name}}</option>
							@endforeach
						</select>
					</p>
					@if(Auth::user()->location() == 6)
						<p class="description" id="subCats">
							<label for="other_seller">Sub category:</label>
							<input type="text" id="subCat" name="subCat" required>
						</p>
					@endif
					<p class="description" id="other_seller_area" style="display: none;">
						<label for="other_seller">Other seller:</label>
						<input type="text" id="other_seller" readonly disabled>
					</p>
					<p>
						<input type="hidden" name="is_legal_entity" id="is_legal_entity" class="legal_entity">
						{{-- <label for="is_legal_entity">legal entity</label> --}}
					</p>

					<p class="description collector-inputs">
                                                <label for="title">Title:</label>
                                                <input type="text" id="title" name="title" required>
                                        </p>

					<p class="description">
						<label for="description">Description:</label>
						<input type="text" id="description" class="collector-inputs">
					</p>
					<p class="sec">
						<label for="status">Status:</label>
						<select id="status" class="collector-inputs" oninput="select_status(this);">
							@php($status_arr = '')
							@foreach($statuses as $status)
								@php($status_arr .= $status->id . ',')
								@if($status->id == 37)
									<option value="{{$status->id}}">{{$status->status}}</option>
								@else
									<option value="{{$status->id}}">{{$status->status}}</option>
								@endif
							@endforeach
						</select>
					</p>
					<p class="sec">
						<label for="invoice_status">Invoice: </label>
						<select name="invoice_status" id="invoice_status" class="collector-inputs">
							<option value="1" selected>No invoice</option>
							<option value="2">Incorrect invoice</option>
							<option value="3">Invoice available</option>
							<option value="4">Invoice uploaded</option>
						</select>
					</p>
					<p class="invoice">
						<label for="invoice">Invoice:</label>
						<input type="number" 
							id="invoice" 
							class="collector-inputs"  
							oninput="change_invoice(this);" 
							value=""
							placeholder="0"
						>
					</p>
					<p class="invoice_usd" hidden>
						<label for="invoice_usd">Invoice USD:</label>
						<input type="number" 
							id="invoice_usd" 
							class="collector-inputs"  
							value=""
						>
					</p>
					<p class="sec">
						<label for="currency">Currency:</label>
						<select id="currency" class="collector-inputs">
							@foreach($currencies as $currency)
								@if($currency->name == Auth::user()->local_currency())
									<option selected
									        value="{{Str::upper($currency->name)}}">{{$currency->name}}</option>
								@else
									<option
													value="{{Str::upper($currency->name)}}">{{$currency->name}}</option>
								@endif
							@endforeach
						</select>
						{{--<input type="text" readonly id="currency" maxlength="50" value="USD" class="collector-inputs">--}}
					</p>
					<p class="name">
						<label for="cont_or_pos"><span id="container" class="active-sec">Con</span> /
							<span
											id="position">Pos</span>:</label>
						<input type="text" id="cont_or_pos" maxlength="255" onchange="cont_or_pos(this);"
						       readonly disabled>
					</p>
					<p class="destination">
						<label for="destination">Dest.:</label>
						<input type="text" id="destination" value="Baku" class="collector-inputs" readonly>
					</p>
					<p class="images" style="display: none;">
						<label for="images">Images:</label>
						<input type="file" id="images" value="Baku" class="collector-inputs" multiple
						       accept="image/.jpeg,.png,.jpg,.jpeg,.gif,.svg">
					</p>

				</div>
				<span style="padding: 9px 12px; margin-bottom: 2px; " class="btn btn-primary"
					  id="save-btn" onclick="save_collector_manually('{{route("add_collector")}}');">Save</span>
			</div>


		</div>
		<div class="tables">

			<div id="container_details_area" style="display: none;">
				<table class="references-table">
					<thead>
					<tr>
						<th id="container_details_name"></th>
						<th id="container_details_count"></th>
						<th id="container_details_weight"></th>
					</tr>
					</thead>
				</table>
			</div>

			<div class="table" id="tables">

				@if(session('display') == 'block')
					<div class="alert alert-{{session('class')}}" role="alert">
						{{session('message')}}
					</div>
				@endif

				<h3>Last changes</h3>
				<table class="references-table">
					<thead>
					<tr>
						<th>Tracking №</th>
						{{--                        <th>Internal ID</th>--}}
						<th>C/P</th>
						<th>Category</th>
						<th>Invoice</th>
						<th>Weight</th>
						<th>Client</th>
						<th>Amount</th>
						{{--                        <th>Seller</th>--}}
						<th>QTY</th>
						<th>Time</th>
					</tr>
					</thead>
					<tbody id="item-list">
					</tbody>
				</table>

			</div>
			<div class="table" id="client_packages_table" style="display: none;">
				<h3>Client's other packages</h3>
				<table class="references-table">
					<thead>
					<tr>
						<th>Tracking №</th>
						<th>Internal ID</th>
						<th>Destination</th>
						<th>Seller</th>
						<th>Amount</th>
						<th>Chargeable weight</th>
						<th>Status</th>
					</tr>
					</thead>
					<tbody id="client_packages_body">
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div id="waybill_area">
		<div class="container" id="waybill_content" style="display: none;">
			<div class="row" style="border:2px solid black;">
				<div class="col-md-5 col-xs-5">
					<div class="">
						<table>
							<caption class="caption text-center">WAYBILL</caption>
							<tr class="">
								<td class="col-md-1 col-xs-1 borderh">1</td>
								<td class="col-md-1 col-xs-1 borderh"></td>
								<td class="col-md-10 col-xs-10 borderh" colspan="3">Payer account number</td>
							</tr>
						</table>
						<table border="1">
							<tr>
								<td rowspan="2" class="col-md-7 col-xs-7 text-center"
								    style="border-top:0px solid !important;" id="waybill-suite"></td>
								<td class="col-md-1 col-xs-1" id="waybill_charge_collect">x</td>
								<td class="col-md-4 col-xs-4" style="border-bottom:0px solid !important;">Charge Collect
								</td>
							</tr>
							<tr>
								<td class="col-md-1 col-xs-1" style="border-top:0px solid !important;"
								    id="waybill_prepaid"></td>
								<td class="col-md-12 col-xs-4" rowspan="2" style="border-top:0px solid !important;">
									Prepaid
								</td>
							</tr>
						</table>
						<table border="1">
							<tr>
								<td class="col-md-1 col-xs-1">2</td>
								<td class="col-md-1 col-xs-1" style="border-top: none!important;"></td>
								<td class="col-md-4 col-xs-4">From</td>
								<td class="col-md-6 col-xs-6">Shipper</td>
							</tr>
						</table>
						<table border="1">
							<tr style="border-bottom: 0px solid white !important;">

								<td class="col-md-3 col-md-offset-3  col-xs-3 col-xs-offset-3 text-center"
								    style="padding-top:18px;" id="waybill_seller"></td>
								<td class="col-md-3 col-md-offset-3  col-xs-3 col-xs-offset-3 text-center waybill_client"
								    style="padding-top: 18px;"></td>
							</tr>
						</table>
						<table border="1" class="col-md-12 col-xs-12  text-center" style="height:70px;">
							<tr>
								<td>{{Auth::user()->location_address()}}</td>
							</tr>
						</table>
						<table class="col-md-12 col-xs-12 lrborder" style="height: 80px;">
							<tr class="row">
								<td class="col-md-5 col-xs-5 "
								    style=" padding-right: 0px !important; padding-left: 10px !important;">Postcode /ZIP Code
								</td>
								<td class="col-md-7 col-xs-7"
								    style=" padding-right: 0px !important; padding-left: 10px !important;">Phone, Fax or Email (required)
								</td>
							</tr>
							<tr class="row">
								<td class="col-md-5 col-xs-5 "
								    style=" padding-right: 0px !important; padding-left: 10px !important;"></td>
								<td class="col-md-7 col-xs-7"
								    style=" padding-right: 0px !important; padding-left: 10px !important;"></td>
							</tr>
						</table>
						<table>
							<tr class="">
								<td class="col-md-1  col-md-offset-1 col-xs-1 borderh">3</td>
								<td class=" col-md-0 col-xs-1 bordertb"></td>
								<td class="col-md-10 col-xs-10 borderh" colspan="3">To (Consignee)</td>
							</tr>
						</table>
						<table class="lrborder">
							<tr class="">
								<td class="col-md-0 col-xs-0 ">Name</td>
								<td class="col-md-12 col-xs-12" align="right">Personal ID No</td>
							</tr>
						</table>
						<table class="col-md-12 col-xs-12 lrborder">
							<tr class="col-md-8 col-md-offset-2  col-xs-8 col-xs-offset-2">
								<td class="text-center waybill_client"></td>
							</tr>
							<tr class="col-md-8  col-md-offset-2  col-xs-8 col-xs-offset-2">
								<td class=" text-center" style="padding-top: 3px; padding-bottom: 3px;"
								    id="waybill_client_phone"></td>
							</tr>
						</table>
						<table border="1" class="col-md-12 col-xs-12" style="height: 70px; ">
							<tr>
								<td style="border-bottom: none;padding-bottom: 5px; padding-left: 10px;">Delivery Address
								</td>
							</tr>
							<tr>
								<td style=" border-top: none;padding-left: 10px;" id="waybill_client_address"></td>
							</tr>
						</table>

						<table class="col-md-12 col-xs-12 lrborder">
							<tr>
								<td class="text-center" style="padding-top: 5px;">
									<div id="waybill_internal_id_barcode"></div>
								</td>
							</tr>
							<tr>
								<td class="text-center" style="font-weight: bold;" id="waybill_internal_id"></td>
							</tr>
						</table>
						<table>
							<tr>
								<td class="col-md-6 col-xs-6 borderh" style="padding-bottom: 20px; padding-left: 10px;">
									Postcode/ZIP Code
								</td>
								<td class="col-md-4 col-xs-4" style="padding-bottom: 20px;">Country Azerbaijan</td>
							</tr>
							<tr>
								<td class="col-md-12 col-xs-12 borderh" colspan="3" style="padding-top: 2px;">Contact
								                                                                              Person
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-md-7 col-xs-7">
					<div class="row" style="border: 1px grey;">
						<table border="1" class="col-md-12 col-xs-12">
							<tr>
								<td rowspan="3" class="col-md-6 col-xs-6 colibimg" style=""><img
													src="{{asset("uploads/files/static/logo.png")}}" height="98"
													width="240" /></td>
								<td class="col-md-3 col-xs-2" colspan="1">CDN</td>
								<td class="col-md-3 col-xs-4" id="waybill_cdn"></td>
							</tr>
							<tr>
								<td class="col-md-3 col-xs-3">Origin</td>
								<td class="col-md-3 col-xs-3" id="waybill_departure"></td>
							</tr>
							<tr>
								<td class="col-md-3 col-xs-3"> Destination</td>
								<td class="col-md-3 col-xs-3" id="waybill_destination"></td>
							</tr>
							<tr>
								<td colspan="3" class="col-md-12 col-xs-12 text-center" id="waybill_date"></td>
							</tr>
						</table>
						<table class="col-md-12 col-xs-12">
							<tr>
								<td class="col-md-1 col-xs-1 borderh four">4</td>
								<td class="col-md-1 col-xs-1"></td>
								<td class="col-md-10 col-xs-10 borderh" colspan="3">Shipment details</td>
							</tr>
						</table>
						<table border="1" align="center">
							<tr>
								<td class="col-md-3 col-xs-3 text-center">Total number of packages</td>
								<td class="col-md-3  col-xs-3 text-center">Total Gross weight (kg)</td>
								<td class="col-md-3  col-xs-3 text-center">Chargeable Volume Weight (kg)</td>
								<td class="col-md-3 col-xs-3 ">Shipping Price</td>
							</tr>
							<tr>
								<td class="col-md-3 col-xs-3 text-center" id="waybill_quantity"></td>
								<td class="col-md-3  col-xs-3 text-center" id="waybill_gross_weight"></td>
								<td class="col-md-3 col-xs-3 text-center" id="waybill_volume_weight"></td>
								<td class="text-center col-md-3 col-xs-3" id="waybill_amount"></td>
							</tr>
							<tr>
								<td class="col-md-3 col-xs-3">Transportation mode</td>
								<td colspan="2" class="text-center">By Air</td>
							</tr>
						</table>
						<table border="" class="col-md-12 col-xs-12" style="height: 70px;">
							<tr style="border-bottom: none;">
								<td class="col-md-6  col-xs-6 text-center ">MAWB</td>
								<td class="col-md-6 col-xs-6 text-center">Aser Cargo Express FLIGHT #</td>
							</tr>
							<tr style="border-top: none;">
								<td class="col-md-6  col-xs-6 text-center " style="border-top: none;"></td>
								<td class="col-md-6 col-xs-6 text-center " style="border-top: none;" id="waybill_flight_name"></td>
							</tr>
						</table>
						<table class="col-md-12 col-xs-12">
							<tr>
								<td class="col-md-1 col-xs-1 borderh">5</td>
								<td class="col-md-1 col-xs-1"></td>
								<td class="col-md-10 col-xs-10 borderh" colspan="3">Full Description of contents & remarks
								</td>
							</tr>
						</table>
						<table border="1" class="col-md-12 bos">
							<tr>
								<td id="waybill_description"></td>
							</tr>
						</table>
						<table class="col-md-12 col-xs-12" border="1">
							<tr>
								<td class="col-md-4  col-xs-4 text-center " style="padding-bottom: 30px;">Category</td>
								<td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;">Declared Value for Customs
								</td>
								<td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;">Total Price
								</td>
							</tr>
							<tr>
								<td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
								    id="waybill_category"></td>
								<td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
								    id="waybill_invoice_price"></td>
									<td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
								id="total_waybill_invoice_price"></td>
							</tr>
						</table>
						<table border="1" class="col-md-12 col-xs-12">
							<tr>
								<td style="padding-bottom: 20px; padding-left: 15px; padding-top: 5px;">Information on goods filled in by Consignee or by Aser Cargo Express on behalf of Shipper
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div id="barcode_area">
		<div class="container" id="barcode_content" style="display: none;">
			<div class="row">

				<div class="col-md-3 col-xs-3"></div>
				<div class="col-md-6 col-xs-6">


						<table class="col-md-12 col-xs-12">
							<tr>
								<td class="text-center" style="padding-top: 5px;">
									<div id="barcode_internal_id_barcode"></div>
								</td>
							</tr>
							<tr>
								<td class="text-center" style="font-weight: bold;padding-top: 15px;" id="barcode_internal_id"></td>
							</tr>
						</table>

				</div>


			</div>
		</div>
	</div>

	{{--  Images modal  --}}
	<div class="modal fade" id="images-modal" tabindex="-1" option="dialog" aria-labelledby="exampleModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-lg" option="document">
			<div class="modal-content">
				<div class="modal-body" id="images_gallery">

				</div>
				<div style="clear: both;"></div>
				<div class="modal-footer">
					<p class="submit">
						<input type="reset" data-dismiss="modal" value="Close">
					</p>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('css')
	<link rel="stylesheet" href="{{asset("backend/css/collector.css")}}">
	<link rel="stylesheet" href="{{asset("backend/css/waybill-bootstrap.css")}}">
	<link rel="stylesheet" href="{{asset("backend/css/waybill.css")}}">
@endsection

@section('js')
	<script src="{{asset("backend/js/jquery.scannerdetection.js")}}"></script>
	<script src="{{asset("backend/js/jquery-barcode.js")}}"></script>
	<script src="{{asset("backend/js/collector.js?ver=1.0")}}"></script>
	<script>
    check_package_url                  = '{{route("check_package_collector")}}'
    add_new_container_in_collector_url = '{{route("create_single_container")}}'
    currency_by_user                   = '{{Auth::user()->local_currency()}}'

    let status_str = '{{$status_arr}}'
    if (status_str.length > 2) {
      status_str = status_str.substr(0, status_str.length - 1)
    }
    status_arr = $.map(status_str.split(','), function (value) {
      return parseInt(value, 10)
    })

	async function get_fetch(){
		let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
		const invoice = document.getElementById('invoice');
		const currency = document.getElementById('currency');
		const res = await fetch('/collector/exchange-rate', { 
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Accept' : 'application/json',
		},
		body: JSON.stringify({
			'_token': CSRF_TOKEN,
			amount: Number(invoice.value),
			currency: currency.value
		})
	  });
	   const body = await res.json();

	  return body;
	}

	function print_waybill () {
      if (waybill_print_access) {
        $('#waybill_content')
          .css('display', 'block')
        let disp_setting  = 'toolbar=no,location=no,directories=no,menubar=no,'
        disp_setting += 'scrollbars=no,left=0,top=0,resizable=yes,width=900, height=650,'
        let content_value = document.getElementById('waybill_area').outerHTML
        let docprint      = window.open('', '', disp_setting)
        docprint.document.open()
        docprint.document.write('<html><head><title></title>')
        docprint.document.write('<link rel="stylesheet" href="{{asset("backend/css/waybill-bootstrap.css")}}"  rel="stylesheet" type="text/css">')
        docprint.document.write('<link rel="stylesheet" href="{{asset("backend/css/waybill.css")}}"  rel="stylesheet" type="text/css">')
        docprint.document.write('</head><body onLoad="self.print();window.close();">')
        docprint.document.write(content_value)
        docprint.document.write('</body></html>')
        docprint.document.close()
        docprint.focus()
        $('#waybill_content')
          .css('display', 'none')
      }

    }

    let is_scanner = false
    $(document)
      .scannerDetection({
        timeBeforeScanTest: 30, // wait for the next character for upto 30ms
        avgTimeByChar     : 30, // it's not a barcode if a character takes longer than 30ms
        onComplete        : function (barcode, qty) {
          is_scanner = true
          string_detection(barcode, '1')
        },
        onError           : function (string, qty) {
          is_scanner = false
          string_detection(string, '2')
        }
      })

    function string_detection (val, type) {
      if (scan_mode === false) {
        return false
      }

      hide_alert_message()

      if (val.length > 1) {
        switch (val.substr(0, 2)) {
          case 'DS': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            destination = val.substr(2, val.length)
            $('#destination')
              .val(destination)
          }
            break
          case 'LN': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            length = val.substr(2, val.length)
            $('#length')
              .val(length)
            volume_weight = (length * width * height) / 6000
            $('#volume_weight')
              .val(volume_weight)
          }
            break
          case 'HG': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            height = val.substr(2, val.length)
            $('#height')
              .val(height)
            volume_weight = (length * width * height) / 6000
            $('#volume_weight')
              .val(volume_weight)
          }
            break
          case 'WD': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            width = val.substr(2, val.length)
            $('#width')
              .val(width)
            volume_weight = (length * width * height) / 6000
            $('#volume_weight')
              .val(volume_weight)
          }
            break
          case 'CT': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            category = val.substr(2, val.length)
            $('#category')
              .val(category)
          }
            break
          case 'SL': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            seller = val.substr(2, val.length)
            $('#seller')
              .val(seller)
            url = '{{route("get_category_for_seller")}}'
            get_default_category_for_seller(url, seller, true)
          }
            break
          case 'GW': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            gross_weight = val.substr(2, val.length)
            $('#gross_weight')
              .val(gross_weight)
          }
            break
          case 'IN': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            invoice = val.substr(3, val.length)
            $('#invoice')
              .val(invoice)
          }
            break
          case 'QN': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            quantity = val.substr(2, val.length)
            $('#quantity')
              .val(quantity)
          }
            break
          case 'US': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            currency = val.substr(2, val.length)
            $('#currency')
              .val(currency)
          }
            break
          case 'CN': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            container = val.substr(2, val.length)
            position  = null
            $('#cont_or_pos')
              .val('CN' + container)
            $('#container')
              .addClass('active-sec')
            $('#position')
              .removeClass('active-sec')
          }
            break
          case 'PS': {
            if (tracking_number_control === false) {
              show_alert_message('warning', 'Stop!', 'Please enter the tracking number first.')
              break
            }
            position  = val.substr(2, val.length)
            container = null
            $('#cont_or_pos')
              .val(position)
            $('#position')
              .addClass('active-sec')
            $('#container')
              .removeClass('active-sec')
          }
            break
          case 'SV': {
            save_collector('{{route("add_collector")}}')
          }
            break
          case 'CL': {
            clear_values()
          }
            break
          default: {
            clear_values()
            tracking_number_control = true
            let tracking_number     = val
            let len                 = tracking_number.length
            tracking_number         = tracking_number.trim()
            tracking_number         = tracking_number.replace(/ /g, '')
            tracking_number         = tracking_number.replace(//g, '')
            // console.warn(tracking_number)
            if (tracking_number.substr(0, 8) === '42019801') {
              tracking_number = tracking_number.slice(-22)
            }
            // alert(tracking_number)
            $('#number')
              .val(tracking_number)
            check_package_collector('{{route("check_package_collector")}}', tracking_number)
          }
        }
      } else {
        if (is_scanner) {
          show_alert_message('warning', 'Oops!', 'Wrong format!')
        }
      }
    }

	function change_invoice(e) {
		let inv = parseInt($(e).val());
		let sts = $("#invoice_status").val();

		if (inv > 0) { //status is no invoice
			$("#invoice_status").val(3); //collected
		}
		else{
			$("#invoice_status").val(1);
		}
	}

    function call_save_collector () {
      save_collector('{{route("add_collector")}}')
    }

	function print_barcode () {
		if (waybill_print_access) {
			$('#barcode_content')
					.css('display', 'block')
			let disp_setting  = 'toolbar=no,location=no,directories=no,menubar=no,'
			disp_setting += 'scrollbars=no,left=0,top=0,resizable=yes,width=700, height=500,'
			let content_value = document.getElementById('barcode_area').outerHTML
			let docprint      = window.open('', '', disp_setting)
			docprint.document.open()
			docprint.document.write('<html><head><title></title>')
			docprint.document.write('<link rel="stylesheet" href="{{asset("backend/css/waybill-bootstrap.css")}}"  rel="stylesheet" type="text/css">')
			docprint.document.write('<link rel="stylesheet" href="{{asset("backend/css/waybill.css")}}"  rel="stylesheet" type="text/css">')
			docprint.document.write('</head><body onLoad="self.print();window.close();">')
			docprint.document.write(content_value)
			docprint.document.write('</body></html>')
			docprint.document.close()
			docprint.focus()
			$('#barcode_content')
					.css('display', 'none')
		}

	}

	function writeValue() {
		var selectElement = document.getElementById("category");
		var inputElement = document.getElementById("title");
		var selectedOption = selectElement.options[selectElement.selectedIndex];
		inputElement.value = selectedOption.innerHTML;
		//inputElement.value = selectElement.value;
	}
	</script>
@endsection
