<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Aser | Courier</title>

	<!-- Fonts -->
	<link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/cashierCourier.css?ver=0.0.3') }}" type="text/css">
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

	<div id="cashierCourier">
		<v-app>
			<v-app-bar app color="#f0682a">
				<v-toolbar-title>Aser Express</v-toolbar-title>
				<v-spacer></v-spacer>
				<h3>{{Auth::user()->name . ' ' . Auth::user()->surname }}</h3>
				<v-spacer></v-spacer>
				<v-btn text rounded href="{{route('cashier_page')}}">
					Go Back
				</v-btn>
				{{--<v-btn text rounded href="{{route('operator_get_new_courier_order_page')}}">
				New Courier Order
				</v-btn>--}}
				<v-btn text rounded href="{{route('logout')}}">
					<svg style="width:35px;height:35px" viewBox="0 0 24 24">
						<path fill="currentColor" d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />

					</svg>
					Log Out
				</v-btn>
			</v-app-bar>
			<v-content>
				<my-cashier-courier my-route="{{route('cashier_get_courier_orders')}}" set-route="{{route('cashier_set_to_paid_and_delivered')}}"></my-cashier-courier>
			</v-content>
			<v-footer padless dark color="primary" app>
				<v-col class="text-center" cols="12">
					{{ date("Y") }} — <strong>EDI</strong>
				</v-col>
			</v-footer>
		</v-app>
	</div>

	<script src="{{ asset('js/cashierCourier.js?ver=0.0.5') }}"></script>
</body>

</html>