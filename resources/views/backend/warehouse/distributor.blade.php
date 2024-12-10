<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Aser | Delivery</title>

	<!-- Fonts -->
	<link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/distributor.css?ver=0.0.1') }}"
	      type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Alegreya&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.0.15/vuetify.min.css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
	      rel="stylesheet">
	{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css">--}}


	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


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
			<div class="report">
				<template>
					<div class="text-center">
						<v-dialog
										v-model="dialog"
										width="500"
						>
							<template v-slot:activator="{ on }">
								<v-btn
												color="#6779fb"
												v-on="on"
												dark
								>
									Change Status
								</v-btn>
							</template>

							<v-card>
								<v-card-title
												class="headline grey lighten-2"
												primary-title
								>
									Flight
								</v-card-title>
								<v-card-text>
									<v-select
													{{-- v-validate="'required'"
													 :error-messages="errors.collect('country')" data-vv-name="country"--}}
													:items="{{$flights}}"
													item-text="name"
													item-value="id"
													v-model="flight_id"
													label="Flight"
									></v-select>
									<v-select
										:items="{{$branches}}"
										item-text="name"
										item-value="id"
										v-model="branch_id"
										label="Branch"
										>
									</v-select>
								</v-card-text>

								<v-divider></v-divider>

								<v-card-actions>
									<v-spacer></v-spacer>
									<v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
									<v-btn
													:loading="loadingButton"
													raised
													color="primary"
													dark
													@click="changeStatus(`{{route('warehouse_post_packages_in_baku')}}`)"
									>
										Change Status
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
					</div>
				</template>
			</div>
			<div class="new">
				<v-btn
								color="#6779fb"
								href="{{route('warehouse_detained_at_customs_page')}}"
								dark
				>
				
					Detained at Customs
				</v-btn>
				<v-btn
								color="#6779fb"
								href="{{route('warehouse_change_package_branch_view')}}"
								dark
				>
					Change Package Branch
				</v-btn>

				<v-btn
								color="#6779fb"
								href="{{route('warehouse_change_package_in_baku_view')}}"
								dark
				>
					Change Package in Baku
				</v-btn>

			</div>
			<div class="back">
				<a href="{{url()->previous()}}">
					<p>Go Back</p>
				</a>
			</div>
			<div class="logout">
				<a href="{{route('logout')}}">
					<svg style="width:35px;height:35px" viewBox="0 0 24 24">
						<path fill="currentColor"
						      d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />

					</svg>
					<p>Log Out</p>
				</a>
			</div>
		</div>
	</v-app>
</header>
<div id="distributor" class="mt-3">


<div class="container mb-5">
	<button data-toggle="modal" 
		data-target="#changeFlightStatusByWarehouseModal"
		class="btn btn-info ml">  
			Change flight Status by warehouse 
	</button>
</div>

	<my-distributor my-route="{{route('distributor_change_position')}}" admin="{{Auth::user()->username }}"></my-distributor>

	


<div class="modal" id="changeFlightStatusByWarehouseModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Change Flight Status</h2>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="flight">Flight</label>
                <select name="flight" id="flight" class="form-control">
										@foreach($flights as $key => $flight)
										<option value="{{$flight->id}}"  {{ $key == "0" ? "selected" : "" }}  >{{$flight->name}}</option>
										@endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="branch">Branch</label>
                <select name="branch" id="branch" class="form-control">
									@foreach($branches as $key => $branch)
										<option value="{{$branch->id}}"  {{ $key == "0" ? "selected" : "" }}   >{{$branch->name}}</option>
									@endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
					<button class="btn btn-danger" data-dismiss="modal"   >Cancel</button>
					<button class="btn btn-primary" id="save">Change Status</button>
        </div>
    </div>
</div>




</div>




<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script src="{{ asset('js/distributor.js?ver=1.0') }}"> </script>

<script>
	document.getElementById('save').addEventListener('click', function () {
		// console.log("ee")
		let selectedFlight = document.querySelector(".modal [name='flight']").value
		let selectedBranch = document.querySelector(".modal [name='branch']").value
		axios.post('{{ route('warehouse_post_packages_in_baku') }}', { 'flight': selectedFlight, "branch" : selectedBranch })
		.then(function (response) {
		// console.log("waay")
			if (response.data.case === 'success') {
				location.reload()
			} else {
				// _this.loadingButton = false
				Swal.fire({
					type : 'error',
					title: "Something went wrong (13)",
					text : response.data.content,
				})
			}
		})
		.catch(function (resp) {
			// _this.loadingButton = false
			Swal.fire({
				type : 'error',
				title: "Something went wrong (14)",
				text : resp.data.content,
			})
		})
	})
	 

</script>

</body>

</html>
