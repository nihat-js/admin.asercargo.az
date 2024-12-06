<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Aser | Cashier</title>

	<!-- Fonts -->
	<link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/cashier.css?v=0.0.3') }}" type="text/css">
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
@if(session('display') == 'block')
	<div class="alert alert-{{session('class')}}" role="alert">
		{{session('message')}}
	</div>
@endif
<div id="cashier">
	<v-app>
		<v-app-bar height="auto" color="#f0682a">
			<v-toolbar-title>Aser Express</v-toolbar-title>
			<v-spacer></v-spacer>
			<h3>{{Auth::user()->name . ' ' . Auth::user()->surname }}</h3>
			<v-spacer></v-spacer>
			<template>
				<v-row justify="center">
					<v-dialog v-model="dialog2" persistent max-width="800px">
						<template v-slot:activator="{ on }">
							<v-btn color="primary" dark v-on="on">Change balance</v-btn>
						</template>
						<v-card>
							{{--							<form name="myForm" action="{{route('cashier_report')}}" method="POST">--}}
							@csrf
							<v-card-title>
								<span class="headline">Export Report</span>
							</v-card-title>
							<v-card-text>
								<v-container>
									<v-row>
										<v-col cols="12" sm="12" md="4">
											<v-text-field type="number" label="Suite" outlined v-model="suite" @input="sendSuite"></v-text-field>
										</v-col>
										<v-col cols="12" sm="12" md="4">
											<v-text-field label="Client" outlined :value="client" disabled></v-text-field>
										</v-col>
										<v-col cols="12" sm="12" md="2">
											<v-text-field type="number" label="Balance AZN" outlined :value="balanceAZN" disabled></v-text-field>
										</v-col>
										<v-col cols="12" sm="12" md="2">
											<v-text-field type="number" label="Balance USD" outlined :value="balanceUSD" disabled></v-text-field>
										</v-col>
										<v-col cols="12" sm="12" md="6">
											<v-select :items="[{name:'USD',id:1},{name:'AZN',id:3}]" item-text="name" item-value="id" label="Currency" v-model="currency" outlined></v-select>
										</v-col>
										<v-col cols="12" sm="12" md="6">
											<v-text-field type="number" label="Add/Subtract from balance" outlined v-model="valueToBalance"></v-text-field>
										</v-col>
									</v-row>
								</v-container>
							</v-card-text>
							<v-card-actions>
								<v-spacer></v-spacer>
								<v-btn color="blue darken-1" text @click="dialog2 = false">Close
								</v-btn>
								<v-btn color="blue darken-1" :disabled="!valueToBalance || !currency" text @click="updateBalance">Update</v-btn>
							</v-card-actions>
							{{--							</form>--}}
						</v-card>
					</v-dialog>
				</v-row>
			</template>
			<v-spacer></v-spacer>
			<template>
				<v-row justify="center">
					<v-dialog v-model="dialog" persistent max-width="800px">
						<template v-slot:activator="{ on }">
							<v-btn color="primary" dark v-on="on">Report</v-btn>
						</template>
						<v-card>
							<form name="myForm" action="{{route('cashier_report')}}" method="POST">
								@csrf
								<v-card-title>
									<span class="headline">Export Report</span>
								</v-card-title>
								<v-card-text>
									<v-container>
										<v-row>
											<v-col cols="12" sm="12" md="6">
												<v-row justify="center">
													<v-menu ref="menu" v-model="menu" :close-on-content-click="false" :return-value.sync="fromDateReport" transition="scale-transition" offset-y min-width="290px">
														<template v-slot:activator="{ on }">
															<v-text-field v-model="fromDateReport" label="From Date" prepend-icon="event" readonly v-on="on" name="from_date"></v-text-field>
														</template>
														<v-date-picker :min="dateStart" :max="dateEnd" v-model="fromDateReport" no-title scrollable>
															<v-spacer></v-spacer>
															<v-btn text color="primary" @click="menu = false">
																Cancel
															</v-btn>
															<v-btn text color="primary" @click="$refs.menu.save(fromDateReport)">OK
															</v-btn>
														</v-date-picker>
													</v-menu>
													{{--                                                            <v-date-picker v-model="fromDateReport"></v-date-picker>--}}
												</v-row>
											</v-col>
											<v-col cols="12" sm="12" md="6">
												<v-row justify="center">
													<v-menu ref="menu1" v-model="menu1" :close-on-content-click="false" :return-value.sync="toDateReport" transition="scale-transition" offset-y min-width="290px">
														<template v-slot:activator="{ on }">
															<v-text-field v-model="toDateReport" label="To Date" prepend-icon="event" readonly v-on="on" name="to_date"></v-text-field>
														</template>
														<v-date-picker :disabled="!fromDateReport" :min="fromDateReport" :max="dateEnd" v-model="toDateReport" no-title scrollable>
															<v-spacer></v-spacer>
															<v-btn text color="primary" @click="menu1 = false">
																Cancel
															</v-btn>
															<v-btn text color="primary" @click="$refs.menu1.save(toDateReport)">OK
															</v-btn>
														</v-date-picker>
													</v-menu>
												</v-row>
											</v-col>
										</v-row>
									</v-container>
								</v-card-text>
								<v-card-actions>
									<v-spacer></v-spacer>
									<v-btn color="blue darken-1" text @click="dialog = false">Close</v-btn>
									<v-btn color="blue darken-1" text @click="printReport">Export</v-btn>
								</v-card-actions>
							</form>
						</v-card>
					</v-dialog>
				</v-row>
			</template>
			<v-spacer></v-spacer>
			{{-- <my-station class="row justify-center" my-route="{{route('call_next_client')}}"> --}}
			</my-station>
			<v-spacer></v-spacer>
			<v-spacer></v-spacer>
			<my-waybill class="row justify-center" my-route="{{route('waybill_page')}}">
			</my-waybill>
			<v-spacer></v-spacer>
			<v-btn color="blue" href="{{route('cashier_get_courier_page')}}">
				Courier
			</v-btn>
			<v-spacer></v-spacer>
			<v-btn color="blue" href="{{route('operator_page')}}">
				Operator
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
			<my-cashier my-pay-log="{{route('print_receipt_log')}}" get-promocode-route="{{route('cashier_get_promo_code')}}" my-currency="{{$rates}}" :promo-codes-groups="{{$promo_codes_groups }}" my-route="{{route('cashier_get_packages')}}" cashier-pay-route="{{route('cashier_pay')}}" cashier-qmatic-route="{{route('qmatic_print')}}" admin="{{Auth::user()->username }}"></my-cashier>
		</v-content>
		<v-footer padless dark color="primary" app>
			<v-col class="text-center" cols="12">
				{{ date("Y") }} — <strong>EDI</strong>
			</v-col>
		</v-footer>
	</v-app>
</div>

<script src="{{ asset('js/cashier.js?v=0.1.1') }}"></script>
<script>
  window.getRoute = "{{route('cashier_get_client_balance')}}"
  window.setRoute = "{{route('cashier_set_client_balance')}}"
</script>
</body>

</html>
