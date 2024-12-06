<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Aser | Delivery</title>

	<!-- Fonts -->
	<link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/collectorContainer.css?ver=0.0.1') }}"
	      type="text/css">
	<link rel="stylesheet" href="{{asset("backend/css/waybill-bootstrap.css")}}">
	<link rel="stylesheet" href="{{asset("backend/css/waybill.css")}}">
	<link rel="stylesheet" href="{{asset("backend/css/bootstrap.css")}}">
	<link rel="stylesheet" href="{{asset("css/sweetalert2.min.css")}}">
	<link href="https://fonts.googleapis.com/css?family=Alegreya&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.0.15/vuetify.min.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
	      rel="stylesheet">
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
<header id="header">
	<v-app>
		<div class="header container">
			<div class="logo">
				<h2>Aser Express</h2>
			</div>
			<div class="user">
				<h2>{{Auth::user()->name . ' ' . Auth::user()->surname }}</h2>
			</div>
			
			<div class="back">
				<a href="{{route('get_collector')}}">
					<p>Go Back</p>
				</a>
			</div>
			<!-- <div class="logout">
				<a href="{{route('logout')}}">
					<svg style="width:35px;height:35px" viewBox="0 0 24 24">
						<path fill="currentColor"
						      d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />

					</svg>
					<p>Log Out</p>
				</a>
			</div> -->
		</div>
	</v-app>
</header>
<div id="collector">

	<my-collector my-route="{{route('collector_change_position')}}" waybill-route="{{route('check_package_collector')}}" user-location="{{Auth::user()->location_address()}}"
	         admin="{{Auth::user()->username }}"></my-collector>
</div>
<script src="{{asset("backend/js/jquery-3.4.1.js")}}" defer></script>
<script src="{{asset("backend/js/jsbarcode.min.js")}}" defer></script>
<script src="{{ asset('js/collectorContainer.js?ver=0.0.1') }}" defer></script>
<script src="{{asset("backend/js/jquery-barcode.js")}}" defer></script>
<script src="{{asset("backend/js/jquery.classyqr.min.js")}}" defer></script>
<script src="{{asset("backend/js/collector.js?ver=1.0")}}" defer></script>
<script src="{{asset("backend/js/bootstrap.min.js")}}" defer></script>
<script src="{{asset("js/jquery.form.min.js")}}" defer></script>
<script src="{{asset("js/sweetalert2.min.js")}}" defer></script>
<script src="{{asset("backend/js/ajax.js?ver=1.3.1")}}" defer></script>
</body>

</html>
