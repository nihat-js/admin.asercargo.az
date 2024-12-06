<template>
	<v-container fluid>
		<v-row align="center" justify="center">
			<v-col cols="12">
				<v-row align="center" justify="center">
					<h1>Reports</h1>
				</v-row>
			</v-col>
			<v-col cols="4" class="box_shadow ma-3 pa-6">
				<v-row align="center" justify="center">
					<v-btn color="blue" :href="exportInbaku">In Baku</v-btn>
				</v-row>
			</v-col>
			<v-col cols="4" class="box_shadow ma-3 pa-6">
				<v-row align="center" justify="center">
					<v-select
									:items="flights"
									item-text="name"
									item-value="id"
									label="Flight"
									outlined
									v-model="flight"
					></v-select>
				</v-row>
				<v-row align="center" justify="center">
					<v-btn color="red" :disabled="!flight" :href="exportInbound + '/' + '?flight=' + flight">Inbound</v-btn>
				</v-row>
			</v-col>
			<v-col cols="4" class="box_shadow ma-3 pa-6">
				<v-row class="ma-3 " align="center" justify="center">
					<v-menu
									v-model="menu"
									:close-on-content-click="false"
									:nudge-right="40"
									transition="scale-transition"
									offset-y
									min-width="290px"
					>
						<template v-slot:activator="{ on }">
							<v-text-field
											v-model="date"
											label="From"
											prepend-icon="event"
											readonly
											v-on="on"
							></v-text-field>
						</template>
						<v-date-picker :min="dateStart" :max="date2" v-model="date" @input="menu = false"></v-date-picker>
					</v-menu>
				</v-row>
				<v-row class="ma-3" align="center" justify="center">
					<v-menu
									v-model="menu2"
									:close-on-content-click="false"
									:nudge-right="40"
									transition="scale-transition"
									offset-y
									min-width="290px"
					>
						<template v-slot:activator="{ on }">
							<v-text-field
											v-model="date2"
											label="To"
											prepend-icon="event"
											readonly
											v-on="on"
							></v-text-field>
						</template>
						<v-date-picker :min="dateStart" :max="dateEnd" v-model="date2" @input="menu2 = false"></v-date-picker>
					</v-menu>
				</v-row>
				<v-row align="center" justify="center">
					<v-btn color="green" :disabled="!date || !date2" :href="exportDelivered + '/' + '?from_date=' + date + '&to_date=' + date2">Delivered</v-btn>
				</v-row>
			</v-col>
		</v-row>
	</v-container>
</template>

<script>
  export default {
    name    : 'report',
    props   : {
      'exportInbaku'   : {
        type: String,
      },
      'exportInbound'  : {
        type: String,
      },
      'exportDelivered': {
        type: String,
      },
      'flights'        : {
        type: Array,
      },
    },
    data () {
      return {
        // status         : '',
        flight: '',
        /*headers        : [
          { text: 'Track', align: 'left', value: 'track' },
          { text: 'Internal_id', align: 'left', value: 'internal_id' },
          { text: 'Suite', align: 'left', value: 'suite' },
          { text: 'Client name', align: 'left', value: 'client_name' },
          { text: 'Client surname', align: 'left', value: 'client_surname' },
          { text: 'Gross Weight', align: 'left', value: 'gross_weight' },
        ],*/
        /*statuses_search: [
          { value: 1, text: 'Hamisi' },
          { value: 2, text: 'Qebul Edilenler' },
          { value: 3, text: 'Qebul Edilmiyenler' },
        ],
        desserts       : [],
        dialog         : false,*/
        /*fromDateReport : '',
        toDateReport   : '',*/
        //status_report  : '',
        csrf  : document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content'),
        /*exportStatus   : [
          { value: 'all', text: 'All' },
          { value: 'yes', text: 'Delivered' },
          { value: 'no', text: 'Not Delivered' }
        ],*/
        date  : new Date().toISOString()
                          .substr(0, 10),
        date2 : new Date().toISOString()
                          .substr(0, 10),
        menu  : false,
        menu2 : false,
      }
    },
    methods : {
      search () {
        let self     = this
        let formData = new FormData
        formData.append('flight', this.flight)
        formData.append('status', this.status)
        axios.post(this.myRoute, formData)
             .then(function (resp) {
               self.packges = resp.data.packages
               if (resp.data.case === 'success') {
                 self.desserts = resp.data.packages
                 self.desserts.forEach((el) => {
                   el.suite = (el.client_suite + (el.client_id)?.toString()
                                                               .padStart(6, '0')) || ''
                   delete el['client_suite']
                   delete el['client_id']
                 })
               } else {
                 toastr.error(resp.data.content)
               }
             })
             .catch(function (resp) {
               console.log(resp)
             })
      },
      printReport () {
        if (this.fromDateReport && this.toDateReport) {
          document.myForm.submit()
          this.dialog = false
        }
      },
    },
    computed: {
      dateEnd () {
        let dt = new Date()
        dt.setDate(dt.getDate())
        return dt.toISOString()
                 .substr(0, 10)
      },
      dateStart () {
        let dt = new Date()
        dt.setDate(dt.getDate() - 9)
        return dt.toISOString()
                 .substr(0, 10)
      },
    },
    mounted () {
    },
  }
</script>

