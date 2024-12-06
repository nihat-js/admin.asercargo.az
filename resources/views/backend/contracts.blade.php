@extends('backend.app')
@section('title')
	Contracts
@endsection
@section('actions')
	<li>
		<a onclick="show_contract_details('{{route("show_contract_details")}}');" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Show details</a>
	</li>
	<li>
		<a onclick="set_to_default_contract('{{route("set_to_default_contract")}}');" class="action-btn"><span class="glyphicon glyphicon-check"></span> Set to default</a>
	</li>
	<li>
		<a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
	</li>
	<li>
		<a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
	</li>
	<li>
		<a class="action-btn" onclick="del('{{route("delete_contract")}}')"><span
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
					<input type="text" class="form-control search-input" id="search_values" column_name="system"
					       placeholder="Name" value="{{$search_arr['system']}}">
					<input placeholder="Start date" type="text" onfocus="(this.type='date')"
					       class="form-control search-input" id="search_values" column_name="start_date"
					       value="{{$search_arr['start_date']}}">
					<input placeholder="End date" type="text" onfocus="(this.type='date')"
					       class="form-control search-input" id="search_values" column_name="end_date"
					       value="{{$search_arr['end_date']}}">
					<button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
				</div>
			</div>
		</div>
		<div class="references-in">
			<table class="references-table">
				<thead>
				<tr>
					<th class="columns" onclick="sort_by('id')">No</th>
					<th class="columns" onclick="sort_by('system')">Contract</th>
					<th class="columns" onclick="sort_by('start_date')">Start date</th>
					<th class="columns" onclick="sort_by('end_date')">End date</th>
					<th class="columns" onclick="sort_by('created_at')">Created date</th>
				</tr>
				</thead>
				<tbody>
				@foreach($contracts as $contract)
					@php($class = "")
					@if($contract->default_option == 1)
						@php($class = 'default_contract')
					@endif
					<tr class="rows {{$class}}" id="row_{{$contract->id}}" onclick="select_row({{$contract->id}})" ondblclick="show_contract_details_modal();">
						<td>{{$contract->id}}</td>
						<td id="system_{{$contract->id}}" title="{{$contract->description}}">{{$contract->system}}</td>
						<td id="start_date_{{$contract->id}}">{{$contract->start_date}}</td>
						<td id="end_date_{{$contract->id}}">{{$contract->end_date}}</td>
						<td>{{$contract->created_at}}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			<div>
				{!! $contracts->links(); !!}
			</div>
		</div>
	</div>

	<!-- start add modal-->
	<div style="z-index: 9999;" class="modal fade" id="add-modal" tabindex="-1" role="dialog" data-backdrop="static"
	     aria-labelledby="exampleModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div style="clear: both;"></div>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<div class="modal-heading">
						<span class="masha_index masha_index1" rel="1"></span><span
										class="modal-title">Add contract</span>
					</div>
				</div>
				<form id="form" class="add_or_update_form" action="/contracts/add" method="post">
					{{csrf_field()}}
					<div id="form_item_id"></div>
					<div class="modal-body">
						<div class="form row">
							<div class="col-md-6">
								<p class="system">
									<label for="system">Contract name: <font color="red">*</font></label>
									<input type="text" name="system" id="system" required="" maxlength="50">
								</p>
								<p class="sec">
									<label for="description">Description:</label>
									<textarea name="description" id="description" cols="30" rows="2"></textarea>
								</p>
							</div>
							<div class="col-md-6">
								<p class="start_date">
									<label for="start_date">Start date: <font color="red">*</font></label>
									<input type="date" name="start_date" id="start_date" required="">
								</p>
								<p class="end_date">
									<label for="end_date">End date: <font color="red">*</font></label>
									<input type="date" name="end_date" id="end_date" required="">
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

	<!-- details modal-->
	<div class="modal fade" id="details-modal" tabindex="-1" role="dialog" data-backdrop="static"
	     aria-labelledby="exampleModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document" style="width: 80%;">
			<div class="modal-content">
				<div style="clear: both;"></div>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<div class="modal-heading">
						<span class="masha_index masha_index1" rel="1"></span>Contract details
						<div style="float: right;">
							{{--                            <a onclick="set_to_default_contract_detail('{{route("set_to_default_contract_detail")}}');" class="details-action-btn"><span class="glyphicon glyphicon-check"></span> Set to default</a>--}}
							<a onclick="show_detail_add_modal();" class="details-action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
							<a onclick="show_detail_update_modal();" class="details-action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Update</a>
							<a class="details-action-btn" onclick="del_detail()"><span class="glyphicon glyphicon-trash"></span> Delete</a>
						</div>
					</div>
				</div>
				<div class="modal-body">
					<select class="form-control" id="country_id_for_show_detail" style="margin-bottom: 10px;" oninput="show_contract_details(this, '{{route("show_contract_details")}}');">
						<option value="">Country</option>
						@foreach($countries as $country)
							<option value="{{$country->id}}">{{$country->name}}</option>
						@endforeach
					</select>
					<div style="overflow: auto;">
						<table class="references-table">
							<thead>
							<tr>
								<th>No</th>
								<th>Type</th>
								<th>Name</th>
								<th title="For site">Title</th>
								<th>Description</th>
								<th>Country</th>
								<th>Departure</th>
								<th>Destination</th>
								<th>Seller</th>
								<th>Category</th>
								<th>From weight</th>
								<th>To weight</th>
								<th>Volume control</th>
								<th>Rate</th>
								<th>Charge</th>
								<th>Currency</th>
								<th>Start date</th>
								<th>End date</th>
								<th>Created date</th>
							</thead>
							<tbody id="details-body">

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- start detail add modal-->
	<div style="z-index: 9999;" class="modal fade" id="detail-add-modal" tabindex="-1" role="dialog" data-backdrop="static"
	     aria-labelledby="exampleModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div style="clear: both;"></div>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<div class="modal-heading">
						<span class="masha_index masha_index1" rel="1"></span><span
										class="modal-title">Add contract detail</span>
					</div>
				</div>
				<form id="detail-form" class="detail_add_or_update_form" action="{{route("add_contract_detail")}}" method="post">
					{{csrf_field()}}
					<div id="form_detail_id"></div>
					<div id="detail_contract_id"></div>
					<div class="modal-body">
						<div class="form row">
							<div class="col-md-6">
								<p class="sec">
									<label for="detail_type_id">Type:</label>
									<select name="type_id" id="detail_type_id">
										@foreach($types as $type)
											@if($type->id == 1)
												<option selected value="{{$type->id}}">{{$type->name}}</option>
											@else
												<option value="{{$type->id}}">{{$type->name}}</option>
											@endif
										@endforeach
									</select>
								</p>
								<p class="service_name">
									<label for="detail_service_name">Service name: <font color="red">*</font></label>
									<input type="text" name="service_name" id="detail_service_name" required="" maxlength="50">
								</p>
								<p class="service_name">
									<label for="detail_title_az">Title (AZ): <font color="red">*</font></label>
									<input type="text" name="title_az" id="detail_title_az" required="" maxlength="255">
								</p>
								<p class="service_name">
									<label for="detail_title_en">Title (EN): <font color="red">*</font></label>
									<input type="text" name="title_en" id="detail_title_en" required="" maxlength="255">
								</p>
								<p class="service_name">
									<label for="detail_title_ru">Title (RU): <font color="red">*</font></label>
									<input type="text" name="title_ru" id="detail_title_ru" required="" maxlength="255">
								</p>
								<p class="contract_description">
									<label for="detail_description_az">Description (AZ): <font color="red">*</font></label>
									<input type="text" name="description_az" id="detail_description_az" required="" maxlength="150">
								</p>
								<p class="contract_description">
									<label for="detail_description_en">Description (EN): <font color="red">*</font></label>
									<input type="text" name="description_en" id="detail_description_en" required="" maxlength="150">
								</p>
								<p class="contract_description">
									<label for="detail_description_ru">Description (RU): <font color="red">*</font></label>
									<input type="text" name="description_ru" id="detail_description_ru" required="" maxlength="150">
								</p>
								<p class="sec">
									<label for="detail_seller_id">Seller:</label>
									<select name="seller_id" id="detail_seller_id">
										<option value="">Select</option>
										@foreach($sellers as $seller)
											<option value="{{$seller->id}}">{{$seller->name}}</option>
										@endforeach
									</select>
								</p>
								<p class="sec">
									<label for="detail_category_id">Category:</label>
									<select name="category_id" id="detail_category_id">
										<option value="">Select</option>
										@foreach($categories as $category)
											<option value="{{$category->id}}">{{$category->name}}</option>
										@endforeach
									</select>
								</p>
								<p class="sec">
									<label for="detail_country_id">Country:</label>
									<select name="country_id" id="detail_country_id">
										<option value="">Select</option>
										@foreach($countries as $country)
											<option value="{{$country->id}}">{{$country->name}}</option>
										@endforeach
									</select>
								</p>
								<p class="sec">
									<label for="detail_weight_control">Volume control:</label>
									<select name="weight_control" id="detail_weight_control">
										<option value="2" selected>Disable</option>
										<option value="1">Active</option>
									</select>
								</p>
							</div>
							<div class="col-md-6">
								<p class="from_weight">
									<label for="detail_from_weight">From weight: <font color="red">*</font></label>
									<input type="number" name="from_weight" id="detail_from_weight" required="" step="0.001" min="0">
								</p>
								<p class="to_weight">
									<label for="detail_to_weight">To weight: <font color="red">*</font></label>
									<input type="number" name="to_weight" id="detail_to_weight" required="" step="0.001" min="0">
								</p>
								<p class="rate">
									<label for="detail_rate">Rate: <font color="red">*</font></label>
									<input type="number" name="rate" id="detail_rate" required="" step="0.001" min="0">
								</p>
								<p class="charge">
									<label for="detail_charge">Charge: <font color="red">*</font></label>
									<input type="number" name="charge" id="detail_charge" required="" step="0.001" min="0" value="0">
								</p>
								<p class="sec">
									<label for="detail_currency_id">Currency: <font color="red">*</font></label>
									<select name="currency_id" id="detail_currency_id" required>
										<option value="">Select</option>
										@foreach($currencies as $currency)
											<option value="{{$currency->id}}">{{$currency->name}}</option>
										@endforeach
									</select>
								</p>
								<p class="sec">
									<label for="detail_departure_id">Departure: <font color="red">*</font></label>
									<select name="departure_id" id="detail_departure_id" required>
										<option value="">Select</option>
										@foreach($locations as $location)
											<option value="{{$location->id}}">{{$location->name}}</option>
										@endforeach
									</select>
								</p>
								<p class="sec">
									<label for="detail_destination_id">Destination: <font color="red">*</font></label>
									<select name="destination_id" id="detail_destination_id" required>
										<option value="">Select</option>
										@foreach($locations as $location)
											<option value="{{$location->id}}">{{$location->name}}</option>
										@endforeach
									</select>
								</p>
								<p class="start_date">
									<label for="detail_start_date">Start date: <font color="red">*</font></label>
									<input type="date" name="start_date" id="detail_start_date" required="">
								</p>
								<p class="end_date">
									<label for="detail_end_date">End date: <font color="red">*</font></label>
									<input type="date" name="end_date" id="detail_end_date" required="">
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
    $(document)
      .ready(function () {
        $('#form')
          .ajaxForm({
            beforeSubmit: function () {
              //loading
              swal({
                title            : '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text             : 'Loading, please wait...',
                showConfirmButton: false
              })
            },
            success     : function (response) {
              form_submit_message(response)
            }
          })

        $('#detail-form')
          .ajaxForm({
            beforeSubmit: function () {
              //loading
              swal({
                title            : '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text             : 'Loading, please wait...',
                showConfirmButton: false
              })
            },
            success     : function (response) {
              form_submit_message(response, false)
              if (response.case === 'success') {
                $('#detail-add-modal')
                  .modal('hide')
                show_contract_details($('#country_id_for_show_detail'), '{{route("show_contract_details")}}')
              }
            }
          })
      })

    function show_contract_details_modal () {
      $('#country_id_for_show_detail')
        .val('')
      $('#details-body')
        .html('')
      $('#details-modal')
        .modal('show')
    }

    function show_add_modal () {
      $('#form_item_id')
        .html('')
      $('.add_or_update_form')
        .prop('action', "{{route("add_contract")}}")
      $('.modal-title')
        .html('Add contract')

      $('#system')
        .val('')
      $('#description')
        .val('')
      $('#start_date')
        .val(get_current_date())
      $('#end_date')
        .val(get_current_date())

      $('#add-modal')
        .modal('show')
    }

    function show_update_modal () {
      let id = 0
      id     = row_id
      if (id === 0) {
        swal(
          'Warning',
          'Please select item!',
          'warning'
        )
        return false
      }

      let id_input = '<input type="hidden" name="id" value="' + row_id + '">'

      $('#form_item_id')
        .html(id_input)
      $('.add_or_update_form')
        .prop('action', "{{route("update_contract")}}")
      $('.modal-title')
        .html('Update contract')

      $('#system')
        .val($('#system_' + row_id)
          .text())
      $('#description')
        .val($('#system_' + row_id)
          .attr('title'))
      $('#start_date')
        .val($('#start_date_' + row_id)
          .text())
      $('#end_date')
        .val($('#end_date_' + row_id)
          .text())

      $('#add-modal')
        .modal('show')
    }

    function show_detail_add_modal () {
      $('#detail_contract_id')
        .html('<input name="contract_id" type="hidden" value="' + row_id + '">')
      $('#form_detail_id')
        .html('')
      $('.detail_add_or_update_form')
        .prop('action', "{{route("add_contract_detail")}}")
      $('.modal-title')
        .html('Add contract detail')

      $('#detail_type_id')
        .val(1)
      $('#detail_service_name')
        .val('')
      $('#detail_title_az')
        .val('')
      $('#detail_title_en')
        .val('')
      $('#detail_title_ru')
        .val('')
      $('#detail_description_az')
        .val('')
      $('#detail_description_en')
        .val('')
      $('#detail_description_ru')
        .val('')
      $('#detail_country_id')
        .val('')
      $('#detail_seller_id')
        .val('')
      $('#detail_category_id')
        .val('')
      $('#detail_from_weight')
        .val('')
      $('#detail_to_weight')
        .val('')
      $('#detail_weight_control')
        .val(2)
      $('#detail_rate')
        .val('')
      $('#detail_charge')
        .val(0)
      $('#detail_currency_id')
        .val('')
      $('#detail_departure_id')
        .val('')
      $('#detail_destination_id')
        .val('')
      $('#detail_start_date')
        .val(get_current_date())
      $('#detail_end_date')
        .val(get_current_date())

      $('#detail-add-modal')
        .modal('show')
    }

    function show_detail_update_modal () {
      let id = 0
      id     = detail_id
      if (id === 0) {
        swal(
          'Warning',
          'Please select item!',
          'warning'
        )
        return false
      }

      let id_input = '<input type="hidden" name="id" value="' + detail_id + '">'
      $('#detail_contract_id')
        .html('<input name="contract_id" type="hidden" value="' + row_id + '">')
      $('#form_detail_id')
        .html(id_input)
      $('.detail_add_or_update_form')
        .prop('action', "{{route("update_contract_detail")}}")
      $('.modal-title')
        .html('Update contract detail')

      $('#detail_type_id')
        .val($('#detail_type_id_' + detail_id)
          .attr('type_id'))
      $('#detail_service_name')
        .val($('#detail_service_name_' + detail_id)
          .text())
      $('#detail_title_az')
        .val($('#detail_title_' + detail_id)
          .attr('az'))
      $('#detail_title_en')
        .val($('#detail_title_' + detail_id)
          .attr('en'))
      $('#detail_title_ru')
        .val($('#detail_title_' + detail_id)
          .attr('ru'))
      $('#detail_description_az')
        .val($('#detail_description_' + detail_id)
          .attr('az'))
      $('#detail_description_en')
        .val($('#detail_description_' + detail_id)
          .attr('en'))
      $('#detail_description_ru')
        .val($('#detail_description_' + detail_id)
          .attr('ru'))
      $('#detail_country_id')
        .val($('#detail_country_id_' + detail_id)
          .attr('country_id'))
      $('#detail_seller_id')
        .val($('#detail_seller_id_' + detail_id)
          .attr('seller_id'))
      $('#detail_category_id')
        .val($('#detail_category_id_' + detail_id)
          .attr('category_id'))
      $('#detail_from_weight')
        .val($('#detail_from_weight_' + detail_id)
          .text())
      $('#detail_to_weight')
        .val($('#detail_to_weight_' + detail_id)
          .text())
      $('#detail_weight_control')
        .val($('#detail_weight_control_' + detail_id)
          .attr('detail_weight_control'))
      $('#detail_rate')
        .val($('#detail_rate_' + detail_id)
          .text())
      $('#detail_charge')
        .val($('#detail_charge_' + detail_id)
          .text())
      $('#detail_currency_id')
        .val($('#detail_currency_id_' + detail_id)
          .attr('currency_id'))
      $('#detail_destination_id')
        .val($('#detail_destination_id_' + detail_id)
          .attr('destination_id'))
      $('#detail_departure_id')
        .val($('#detail_departure_id_' + detail_id)
          .attr('departure_id'))
      $('#detail_start_date')
        .val($('#detail_start_date_' + detail_id)
          .text())
      $('#detail_end_date')
        .val($('#detail_end_date_' + detail_id)
          .text())

      $('#detail-add-modal')
        .modal('show')
    }

    function del_detail () {
      let id = 0
      id     = detail_id
      if (id === 0) {
        swal(
          'Warning',
          'Please select item!',
          'warning'
        )
        return false
      }

      let url = '{{route("delete_contract_detail")}}'

      delete_contract_detail(url, id)
    }
	</script>
@endsection
