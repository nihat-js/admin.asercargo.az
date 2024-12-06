<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="csrf-token" content="{{csrf_token()}}">
	<title>@yield('title')</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="{{asset("backend/css/bootstrap.css")}}">
	<link rel="stylesheet" href="{{asset("css/sweetalert2.min.css")}}">
	<link rel="stylesheet" href="{{asset("backend/css/main.css?ver=0.0.3")}}">
	@yield('css')
</head>
<body>
<nav class="navbar navbar-fixed-top navbar-color">
	<div class="container-fluid mycontainer">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div class="collapse navbar-collapse" id="myNavbar">
			<ul class="nav navbar-nav" id="menu-links">
				{{--                <li><a href="{{route("home")}}">Home</a></li>--}}

				@if(Auth::user()->role() == 11)
					<li><a href="{{route("show_flights_collector")}}">Flights</a></li>
					<li><a href="{{route("show_containers_collector")}}">Containers</a></li>
					<li><a href="{{route("get_collector")}}">Add Package</a></li>
					<li><a href="{{route("add_container_page")}}">Add Flight</a></li>
					<li><a href="{{route("collector_packages")}}">Declared Packages</a></li>
				@endif
				@if(Auth::user()->role() == 3)
						<li><a href="{{route("show_flights_collector")}}">Flights</a></li>
						<li><a href="{{route("show_containers_collector")}}">Containers</a></li>
						<li><a href="{{route("get_collector")}}">Add Package</a></li>
						<li><a href="{{route("add_container_page")}}">Add Flight</a></li>
						<li><a href="{{route("manifest_collector")}}">Flight Report</a></li>
						<li><a target="_blank" href="{{route("collector_get_anonymous_page")}}">Unknow packages</a></li>
						<li>
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">Packages <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="{{route("collector_search_packages")}}">Search</a></li>
								<li><a href="{{route("collector_packages")}}">Declared Packages</a></li>
							</ul>
						</li>
						<li class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">Reports <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="{{route("collector_reports_page", "manifest")}}">Manifest</a></li>
								<li><a href="{{route("collector_reports_page", "no_invoice")}}">No invoice</a></li>
								<li><a href="{{route("collector_reports_page", "incorrect_invoice")}}">Incorrect invoice</a></li>
								<li><a href="{{route("collector_reports_page", "prohibited")}}">Prohibited</a></li>
								<li><a href="{{route("collector_reports_page", "damaged")}}">Damaged</a></li>
								<li><a href="{{route("collector_reports_page", "all_packages")}}">All packages</a></li>
							</ul>
						</li>
				
				@elseif(Auth::user()->role() == 9)
					<li><a href="{{route('show_packages_manager')}}">Packages</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Users <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route('show_clients_manager')}}">Clients</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Reports <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route("reports_get_warehouse_page")}}">Warehouse Report</a></li>
							<li><a href="{{route('get_declaration_page_manager')}}">Declaration</a></li>
							<li><a href="{{route("get_partner_reports")}}">Partner Export</a></li>
						</ul>
					</li>

					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Dictionary <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route('show_flights')}}">Flights</a></li>
							<li><a href="{{route('show_exchange_rates_manager')}}">Exchange rates</a></li>
							<li><a href="{{route("waybill_page")}}">Waybill</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Courier <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route("admin_courier_settings_page")}}">Settings</a></li>
							<li><a href="{{route("admin_courier_daily_limits_page")}}">Daily limits</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Links <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a target="_blank" href="{{route('operator_page')}}">Operator</a></li>
							<li><a target="_blank" href="{{route('courier_courier_page')}}">Courier</a></li>
						</ul>
					</li>
				@elseif(Auth::user()->role() == 1)
					<li><a href="{{route('show_dashboard')}}">Dashboard</a></li>
					<li><a href="{{route('show_packages')}}">Packages</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Views <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route("show_queues")}}">Queues</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Users <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route("show_operators")}}">Employees</a></li>
							<li><a href="{{route("show_clients")}}">Clients</a></li>
							<li><a href="{{route("admin_users_logs")}}">Logs</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Reports <span class="caret"></span></a>
						<ul class="dropdown-menu">
							@if(Auth::id() == 1 || Auth::id() == 131536)
								<li><a href="{{route("reports_clients_phones")}}">Phones list</a></li>
								<li><a href="{{route("clients_emails_with_parent_id")}}">Emails with Parent id</a></li>
								<li><a href="{{route("custom_index")}}">Gomruk</a></li>
							@endif

							<li><a href="{{route("reports_get_cashier_page")}}">Cashier Report</a></li>
							<li><a href="{{route("reports_get_payments_page")}}">Payments Report</a></li>
							<li><a href="{{route("get_declaration_page")}}">Declaration</a></li>
							<li><a href="{{route("get_admin_manifest_page")}}">Admin manifest</a></li>
							<li><a href="{{route("get_flight_depesh")}}">Flight Depesh</a></li>
							<li><a href="{{route("get_no_invoice")}}">No invoice packages</a></li>
							<li><a href="{{route("reports_get_warehouse_page")}}">Warehouse Report</a></li>
							<li><a href="{{route("reports_in_baku_page")}}">In Baku</a></li>
							<li><a href="{{route("reports_get_inbound_packages_page")}}">Inbound packages</a></li>
							<li><a href="{{route("reports_get_delivered_packages_page")}}">Delivered packages</a></li>
							<li><a href="{{route("reports_get_courier_orders_page")}}">Courier orders</a></li>
							<li><a href="{{route("reports_get_courier_orders_packages_page")}}">Courier orders packages</a></li>
							<li><a href="{{route("get_partner_reports")}}">Partner Export</a></li>
								@if(Auth::user()->full_access == 1)
									<li><a href="{{route("get_payment_task_reports")}}">Online Payment Report</a></li>
									<li><a href="{{route("get_partner_payment_reports")}}">External Partner Payment Report</a></li>

								@endif
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Change status <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route("admin_get_packages_in_baku_page")}}">In Baku</a></li>
							<li><a href="{{route("admin_get_custom_status")}}">Custom status</a></li>
						</ul>
					</li>
{{--					<li class="dropdown">--}}
{{--						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Send SMS <span class="caret"></span></a>--}}
{{--						<ul class="dropdown-menu">--}}
{{--							<li><a href="{{route("get_send_sms_for_no_invoice_package_page")}}">No invoice</a></li>--}}
{{--						</ul>--}}
{{--					</li>--}}
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Dictionary <span class="caret"></span></a>
						<ul class="dropdown-menu">
							@if(Auth::id() == 138869 || Auth::id() == 131536)
								<li><a href="{{route("show_contracts")}}">Contracts</a></li>
							@endif
							<li><a href="{{route("show_flights")}}">Flights</a></li>
							<li><a href="{{route("show_containers")}}">Containers</a></li>
							<li><a href="{{route("show_sellers")}}">Sellers</a></li>
							<li><a href="{{route("show_categories")}}">Categories</a></li>
							<li><a href="{{route("show_exchange_rates")}}">Exchange rates</a></li>
							<li><a href="{{route("show_currencies")}}">Currencies</a></li>
							<li><a href="{{route("show_options")}}">Options</a></li>
							<li><a href="{{route("show_locations")}}">Locations</a></li>
							<li><a href="{{route("show_positions")}}">Positions</a></li>
							<li><a href="{{route("show_roles")}}">Roles</a></li>
							<li><a href="{{route("show_warehouse_debt")}}">Warehouse Debt</a></li>
							<li><a href="{{route("show_branch")}}">Branchs</a></li>
							<li><a href="{{route("show_news")}}">News</a></li>
							<li><a href="{{route("waybill_page")}}">Waybill</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Promo codes <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route("show_promo_codes_groups")}}">Groups</a></li>
							<li><a href="{{route("show_promo_codes")}}">Promo codes</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Courier <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route("admin_courier_settings_page")}}">Settings</a></li>
							<li><a href="{{route("admin_courier_daily_limits_page")}}">Daily limits</a></li>
							<li><a href="{{route("admin_courier_zones_page")}}">Zones</a></li>
							<li><a href="{{route("admin_courier_areas_page")}}">Areas</a></li>
							<li><a href="{{route("admin_courier_metro_stations_page")}}">Metro stations</a></li>
							<li><a href="{{route("admin_courier_payment_types_page")}}">Payment types</a></li>
							<li><a href="{{route("admin_courier_region_page")}}">Regions</a></li>
							<li><a href="{{route("admin_courier_show_region_tariff")}}">Region tariffs</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Links <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a target="_blank" href="{{route("moderator_page")}}">Moderator</a></li>
							<li><a target="_blank" href="{{route("operator_page")}}">Operator</a></li>
							<li><a target="_blank" href="{{route("warehouse_page")}}">Warehouse</a></li>
							<li><a target="_blank" href="{{route("cashier_page")}}">Cashier</a></li>
							<li><a target="_blank" href="{{route("courier_courier_page")}}">Courier</a></li>
						</ul>
					</li>

				@endif
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">{{Auth::user()->username}} <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a><i>{{Auth::user()->username}} ({{Auth::user()->location_name()}})</i></a></li>
						<li><a href="{{route("logout")}}"><span class="glyphicon glyphicon-log-out"></span> Log out</a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				@yield('actions')
				{{-- <li><a onclick="location.reload();" style="cursor: pointer;"><span class="glyphicon glyphicon-refresh"></span> Refresh</a></li>
				<li><a><i>{{Auth::user()->username}} ({{Auth::user()->location_name()}})</i></a></li>
				<li><a href="{{route("logout")}}"><span class="glyphicon glyphicon-log-out"></span> Log out</a></li> --}}
			</ul>
		</div>
	</div>
</nav>

<section class="references-main">
	@yield('content')
</section>

<script src="{{asset("backend/js/jquery-3.4.1.js")}}"></script>
<script src="{{asset("backend/js/bootstrap.min.js")}}"></script>
<script src="{{asset("js/jquery.form.min.js")}}"></script>
<script src="{{asset("js/sweetalert2.min.js")}}"></script>
<script src="{{asset("backend/js/variables.js")}}"></script>
<script src="{{asset("backend/js/main.js?ver=0.2.9")}}"></script>
<script src="{{asset("backend/js/ajax.js?ver=1.3.1")}}"></script>
@yield('js')
</body>
</html>
