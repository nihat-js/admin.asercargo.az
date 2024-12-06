<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Aser | Operator</title>

	<!-- Fonts -->
	<link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/operator.css?ver=0.0.2') }}" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Alegreya&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.0.15/vuetify.min.css">
	{{--<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
				rel="stylesheet">--}}
	{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css">--}}
	<script>
    if (navigator.userAgent.toLowerCase()
                 .indexOf('firefox') > -1) {
      var root = document.getElementsByTagName('html')[0]
      root.setAttribute('class', 'ff')
    }
	</script>
</head>
<style>
	.v-sheet, .v-toolbar, .v-app-bar {
		background-color: #b3d4fc !important;
		box-shadow: #b3d4fc !important;
	}
</style>

<body>


<div id="operator">
	<v-app>
		<v-app-bar height="auto" color="#f0682a" >
			<v-toolbar-title>Aser Express</v-toolbar-title>
			<v-spacer></v-spacer>
			<h3>{{Auth::user()->name . ' ' . Auth::user()->surname }}</h3>
			<v-spacer></v-spacer>

			{{-- <my-station class="row align-center justify-center" :role="roleParent" my-route="{{route('call_next_client')}}">
				<template #role="{items,role}">
					<v-select hide-details :items='items' label="Online/Information" outlined v-model="roleParent"></v-select>
				</template>
			</my-station> --}}
			<v-spacer></v-spacer>
			<v-btn color="blue" href="{{route('cashier_get_courier_page')}}">
				Courier
			</v-btn>
			<v-spacer></v-spacer>
			<v-btn color="grey" href="{{route('operator_page')}}">
				Go Back
			</v-btn>
			<v-spacer></v-spacer>
			<a href="{{route('logout')}}">
				<svg style="width:35px;height:35px" viewBox="0 0 24 24">
					<path fill="currentColor" d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />

				</svg>
				<p>Log Out</p>
			</a>
		</v-app-bar>

		<v-content>
			<my-operator delete-invoice-doc-route="{{route('operator_package_delete_invoice_file')}}" get-status-route="{{route('operator_show_package_events')}}" get-invoice-status-route="{{route('operator_show_package_invoice_events')}}" my-route="{{route('operator_get_client')}}" my-confirm-account="{{route('operator_verify_client_account')}}" get-packages="{{route('operator_get_packages')}}" my-client-account="{{route('login_client_account','')}}" my-referral="{{route('get_sub_accounts_page','')}}" admin="{{Auth::user()->username }}"></my-operator>
		</v-content>
		<v-footer padless dark color="primary" app>
			<v-col class="text-center" cols="12">
				{{ date("Y") }} — <strong>EDI</strong>
			</v-col>
		</v-footer>
	</v-app>
</div>


<script src="{{ asset('js/operator.js?ver=1.0.0') }}"></script>
</body>

</html>
