<template>
	<v-app-bar height="auto" color="rgba(16, 69, 140, 1)">
		<v-toolbar-title>Aser Express</v-toolbar-title>
		<v-spacer>auth</v-spacer>
		<h2></h2>
		<v-spacer></v-spacer>
		<template>
			<v-row justify="center">
				<v-dialog v-model="dialog2" persistent max-width="800px">
					<template v-slot:activator="{ on }">
						<v-btn color="primary" dark v-on="on">Change balance</v-btn>
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
												<v-menu
														ref="menu"
														v-model="menu"
														:close-on-content-click="false"
														:return-value.sync="fromDateReport"
														transition="scale-transition"
														offset-y
														min-width="290px"
												>
													<template v-slot:activator="{ on }">
														<v-text-field
																v-model="fromDateReport"
																label="From Date"
																prepend-icon="event"
																readonly
																v-on="on"
																name="from_date"
														></v-text-field>
													</template>
													<v-date-picker :min="dateStart" :max="dateEnd"
													               v-model="fromDateReport" no-title
													               scrollable>
														<v-spacer></v-spacer>
														<v-btn text color="primary" @click="menu = false">
															Cancel
														</v-btn>
														<v-btn text color="primary"
														       @click="$refs.menu.save(fromDateReport)">OK
														</v-btn>
													</v-date-picker>
												</v-menu>
												{{--
												<v-date-picker v-model="fromDateReport"></v-date-picker>
												--}}
											</v-row>
										</v-col>
										<v-col cols="12" sm="12" md="6">
											<v-row justify="center">
												<v-menu
														ref="menu1"
														v-model="menu1"
														:close-on-content-click="false"
														:return-value.sync="toDateReport"
														transition="scale-transition"
														offset-y
														min-width="290px"
												>
													<template v-slot:activator="{ on }">
														<v-text-field
																v-model="toDateReport"
																label="To Date"
																prepend-icon="event"
																readonly
																v-on="on"
																name="to_date"
														></v-text-field>
													</template>
													<v-date-picker :disabled="!fromDateReport"
													               :min="fromDateReport" :max="dateEnd"
													               v-model="toDateReport" no-title
													               scrollable>
														<v-spacer></v-spacer>
														<v-btn text color="primary" @click="menu1 = false">
															Cancel
														</v-btn>
														<v-btn text color="primary"
														       @click="$refs.menu1.save(toDateReport)">OK
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
								<v-btn color="blue darken-1" text @click="dialog2 = false">Close</v-btn>
								<v-btn color="blue darken-1" text @click="printReport">Export</v-btn>
							</v-card-actions>
						</form>
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
												<v-menu
														ref="menu"
														v-model="menu"
														:close-on-content-click="false"
														:return-value.sync="fromDateReport"
														transition="scale-transition"
														offset-y
														min-width="290px"
												>
													<template v-slot:activator="{ on }">
														<v-text-field
																v-model="fromDateReport"
																label="From Date"
																prepend-icon="event"
																readonly
																v-on="on"
																name="from_date"
														></v-text-field>
													</template>
													<v-date-picker :min="dateStart" :max="dateEnd"
													               v-model="fromDateReport" no-title
													               scrollable>
														<v-spacer></v-spacer>
														<v-btn text color="primary" @click="menu = false">
															Cancel
														</v-btn>
														<v-btn text color="primary"
														       @click="$refs.menu.save(fromDateReport)">OK
														</v-btn>
													</v-date-picker>
												</v-menu>
												{{--
												<v-date-picker v-model="fromDateReport"></v-date-picker>
												--}}
											</v-row>
										</v-col>
										<v-col cols="12" sm="12" md="6">
											<v-row justify="center">
												<v-menu
														ref="menu1"
														v-model="menu1"
														:close-on-content-click="false"
														:return-value.sync="toDateReport"
														transition="scale-transition"
														offset-y
														min-width="290px"
												>
													<template v-slot:activator="{ on }">
														<v-text-field
																v-model="toDateReport"
																label="To Date"
																prepend-icon="event"
																readonly
																v-on="on"
																name="to_date"
														></v-text-field>
													</template>
													<v-date-picker :disabled="!fromDateReport"
													               :min="fromDateReport" :max="dateEnd"
													               v-model="toDateReport" no-title
													               scrollable>
														<v-spacer></v-spacer>
														<v-btn text color="primary" @click="menu1 = false">
															Cancel
														</v-btn>
														<v-btn text color="primary"
														       @click="$refs.menu1.save(toDateReport)">OK
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
		<my-station class="row justify-center" my-route="myRoute">
		</my-station>
		<v-spacer></v-spacer>
		<v-btn color="blue" href="{{route('cashier_get_courier_page')}}">
			Courier
		</v-btn>
		<v-spacer></v-spacer>
		<a href="{{route('logout')}}">
			<svg style="width:35px;height:35px" viewBox="0 0 24 24">
				<path fill="currentColor"
				      d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />
			
			</svg>
			<p>Log Out</p>
		</a>
	</v-app-bar>
</template>

<script>
  export default {
    name : 'cashierHeader',
    props: {
      'myRoute': {
        type: String
      },
      'auth'   : {
        type: String
      }
    },
  }
</script>
