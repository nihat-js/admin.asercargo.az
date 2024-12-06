<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Aser | Courier</title>

	<!-- Fonts -->
	<link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/new_courier_order.css?ver=0.0.5') }}" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Alegreya&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.0.15/vuetify.min.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css">--}}
	<script>
    if (navigator.userAgent.toLowerCase()
                 .indexOf('firefox') > -1) {
      var root = document.getElementsByTagName('html')[0]
      root.setAttribute('class', 'ff')
    }
	</script>
</head>

<body>

<div id="new_courier_order">
	<v-app>
		<v-app-bar app color="#f0682a">
			<v-toolbar-title>Aser Express</v-toolbar-title>
			<v-spacer></v-spacer>
			<h3>{{Auth::user()->name . ' ' . Auth::user()->surname }}</h3>
			<v-spacer></v-spacer>
			@if(Auth::user()->role() == 1)
				<v-btn text rounded href="{{route('admin_get_courier_page')}}">
					Go Back
				</v-btn>
			@else
				<v-btn text rounded href="{{route('operator_get_courier_page')}}">
					Go Back
				</v-btn>
			@endif
			<v-btn text rounded href="{{route('logout')}}">
				<svg style="width:35px;height:35px" viewBox="0 0 24 24">
					<path fill="currentColor" d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />

				</svg>
				Log Out
			</v-btn>
		</v-app-bar>
		<v-content>
			@if(Auth::user()->role() == 1)
				<my-new_courier_order
								my-route="{{route('admin_show_courier_orders')}}"
								:areas="{{ $areas }}"
								:metro-stations="{{ $metro_stations }}"
								on-zone-change="{{ route('admin_get_courier_payment_types') }}"
								on-type-change="{{ route('admin_get_delivery_payment_types') }}"
								amount-for-urgent='{{$amount_for_urgent}}'
								on-suite-change="{{ route('admin_get_client_details') }}"
								create-order="{{ route('admin_create_courier_order') }}"
								admin="{{ Auth::user()->username }}"
								min-date="{{$min_date}}"
				></my-new_courier_order>
			@else
				<my-new_courier_order
								my-route="{{route('operator_show_courier_orders')}}"
								:areas="{{ $areas }}"
								:metro-stations="{{ $metro_stations }}"
								on-zone-change="{{ route('operator_get_courier_payment_types') }}"
								on-type-change="{{ route('operator_get_delivery_payment_types') }}"
								amount-for-urgent='{{$amount_for_urgent}}'
								on-suite-change="{{ route('operator_get_client_details') }}"
								create-order="{{ route('operator_create_courier_order') }}"
								admin="{{ Auth::user()->username }}"
								min-date="{{$min_date}}"
				></my-new_courier_order>
			@endif
		</v-content>
		<v-footer padless dark color="primary" app>
			<v-col class="text-center" cols="12">
				{{ date("Y") }} — <strong>EDI</strong>
			</v-col>
		</v-footer>
	</v-app>
</div>

<script src="{{ asset('js/new_courier_order.js?ver=0.0.5') }}"></script>
</body>

</html>