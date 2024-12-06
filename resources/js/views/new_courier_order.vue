<template>
	<v-container>
		<v-row>
			<v-col cols="12">
				<h2>Name : {{client_name}} , Surname : {{client_surname}}</h2>
			</v-col>
			<v-col cols="4">
				<v-text-field v-model="client_id" type="number" label="Suite" hide-details :disabled="disabled" @change="sendSuite"></v-text-field>
			</v-col>
			<v-col cols="4">
				<v-select v-model="metroStation" :items="metroStations" item-text="name" item-value="id" label="Metro" hide-details clearable :disabled="disabled"></v-select>
			</v-col>
			<v-col cols="4">
				<v-select v-model="area" :items="areas" :disabled="disabled" item-text="name" item-value="id" label="Area" hide-details clearable @change="selectArea"></v-select>
			</v-col>
			<v-col cols="4">
				<v-text-field v-model="address" label="Address" hide-details :disabled="disabled"></v-text-field>
			</v-col>
			<v-col cols="4">
				<v-text-field v-model="phone" label="Phone" hide-details :disabled="disabled"></v-text-field>
			</v-col>
			<v-col cols="4">
				<datepicker :minimumView="'day'" :maximumView="'day'" input-class="dateClass" :disabledDates=" disabledDates" placeholder="Date" v-model="date" :disabled="disabled"></datepicker>
				<!-- <v-text-field v-model="date" type="date" label="Date" hide-details :disabled="disabled"></v-text-field> -->
			</v-col>
			<!-- <v-col cols="4">
				<v-textarea
					v-model="description"
					label="Description"
					hide-details
					:disabled="disabled"
				></v-textarea>
			</v-col> -->
			<v-col cols="4">
				<v-select @change="getDeliveryPaymentType" v-model="payment_type" :items="payment_types" :disabled="!area || disabled" item-text="name" item-value="id" label="Courier Payment Types" hide-details clearable></v-select>
			</v-col>
			<v-col cols="4">
				<v-select v-model="delivery_payment_type" :items="delivery_payment_types" :disabled="!payment_type || !area || disabled" item-text="name" item-value="id" label="Delivery Payment Types" hide-details clearable></v-select>
			</v-col>
			<v-col cols="4">
				<template>
					<div class="text-center">
						<v-dialog v-model="dialog" width="700" :disabled="disabled || !client_id">
							<template v-slot:activator="{ on }">
								<v-btn color="red lighten-2" dark v-on="on">
									Choose packages
								</v-btn>
							</template>
							
							<v-card>
								<v-card-title class="headline grey lighten-2" primary-title>
									Packages
								</v-card-title>
								
								<v-card-text>
									<v-checkbox
											v-if="showReferrals"
											v-model="showReferralsPackages"
											label="Show referrals packages"
									></v-checkbox>
									<template>
										<v-data-table v-model="selected" :headers="headers" :items="desserts" item-key="id" show-select class="elevation-1">
											<template v-slot:item.client="{ item }">
												{{(item.client_name || '') + ' ' + (item.client_surname || '')}}
											</template>
										</v-data-table>
									</template>
								</v-card-text>
								
								<v-divider></v-divider>
								
								<v-card-actions>
									<v-spacer></v-spacer>
									<v-btn color="primary" text @click="dialog = false">
										I accept
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
					</div>
				</template>
			</v-col>
			<v-col cols="6">
				<v-checkbox v-model="urgent_order" label="Urgent Order"></v-checkbox>
			</v-col>
			<v-col cols="6">
				<v-btn color="primary" @click="save" :disabled="!payment_type || !delivery_payment_type || disabled || !selected.length">
					Save
				</v-btn>
			</v-col>
			<v-col cols="6">
				<v-data-table
						:headers="headersSelected"
						:items="selected"
						disable-pagination
						disable-filtering
						hide-default-footer
						class="elevation-1"
				></v-data-table>
			</v-col>
			<v-col cols="6">
				<v-simple-table>
					<template v-slot:default>
						<thead>
						<tr>
							<th class="text-left">Xaricdən çatdırılma</th>
							<th class="text-left">Kuryer çatdırılması</th>
							<th class="text-left">Ümumi məbləğ</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>{{ deliveryAmount }}</td>
							<td>{{ courierAmount }}</td>
							<td>{{ totalAmount }}</td>
						</tr>
						</tbody>
					</template>
				</v-simple-table>
			</v-col>
		</v-row>
	</v-container>
</template>

<script>
  import Swal       from 'sweetalert2'
  import Datepicker from 'vuejs-datepicker'

  function addWorkDaysIncludingSaturday (startDate, days) {
    if (isNaN(days)) {
      console.log('Value provided for "days" was not a number')
      return
    }
    if (!(startDate instanceof Date)) {
      console.log('Value provided for "startDate" was not a Date object')
      return
    }
    // Get the day of the week as a number (0 = Sunday, 1 = Monday, .... 6 = Saturday)
    const dow     = startDate.getDay()
    let daysToAdd = parseInt(days)
    // If the current day is Sunday add one day
    if (dow === 0) { daysToAdd++ }
    // If the start date plus the additional days falls on or after the closest Saturday calculate weekends
    if (dow + daysToAdd > 6) {
      // Subtract days in current working week from work days
      const remainingWorkDays = daysToAdd - (6 - dow)
      // Add current working week's weekend
      daysToAdd += 1
      if (remainingWorkDays > 6) {
        // Add two days for each working week by calculating how many weeks are included
        daysToAdd += Math.floor(remainingWorkDays / 6)
        // Exclude final weekend if remainingWorkDays resolves to an exact number of weeks
        if (remainingWorkDays % 6 === 0) { daysToAdd -= 1 }
      }
    }
    startDate.setDate(startDate.getDate() + daysToAdd)
    return startDate
  }

  export default {
    inheritAttrs: false,
    components  : {
      Datepicker
    },
    props       : {
      onZoneChange   : {
        type    : String,
        required: true
      },
      createOrder    : {
        type    : String,
        required: true
      },
      onSuiteChange  : {
        type    : String,
        required: true
      },
      areas          : {
        type    : Array,
        required: true
      },
      metroStations  : {
        type    : Array,
        required: true
      },
      onTypeChange   : {
        type    : String,
        required: true
      },
      amountForUrgent: {
        type    : String,
        required: false
      },
      minDate        : {
        type    : String,
        required: true
      }
    },
    data () {
      return {
        disabled              : false,
        dialog                : false,
        selected              : [],
        dateNow               : new Date(this.minDate),
        headers               : [
          { text: 'Id', value: 'id' },
          { text: 'Track', value: 'track', sortable: false },
          { text: 'Gross Weight', value: 'gross_weight', sortable: false },
          { text: 'Amount', value: 'amount', sortable: false },
          { text: 'Client', value: 'client', sortable: true },
          { text: 'Payment Type', value: 'payment_type', sortable: false }
        ],
        headersSelected       : [
          { text: 'Track', value: 'track' },
          { text: 'Amount', value: 'amount' }
        ],
        desserts              : [],
        referralsPackages     : [],
        ownPackages           : [],
        client_name           : '',
        client_surname        : '',
        client_id             : '',
        metroStation          : '',
        payment_types         : [],
        delivery_payment_types: [],
        payment_type          : '',
        delivery_payment_type : '',
        address               : '',
        phone                 : '',
        zone                  : '',
        area                  : '',
        date                  : '',
        description           : '',
        urgent_order          : false,
        tariff                : '',
        showReferrals         : false,
        showReferralsPackages : false
      }
    },
    methods     : {
      clear () {
        this.client_id      = ''
        this.client_name    = ''
        this.client_surname = ''
        this.address        = ''
        this.phone          = ''
        this.selected       = []
        this.desserts       = []
        this.urgent_order   = false
        this.description    = ''
        this.date           = ''
        this.area           = ''
        this.zone           = ''
        this.payment_type   = ''
      },
      close () {
        this.dialog            = false
        this.error             = false
        this.commonOrderNumber = ''
        setTimeout(() => {
          this.editedItem  = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        }, 300)
      },

      selectArea () {
        if (this.area) {
          axios
            .post(this.onZoneChange, {
              area_id: this.area
            })
            .then(resp => {
              if (resp.data.case === 'success') {
                this.payment_types = resp.data.payment_types
                this.tariff        = resp.data.tariff
              } else {
                Swal.fire({
                  type : resp.data.case,
                  title: resp.data.title,
                  text : resp.data.content
                })
              }
            })
            .catch(resp => {
              Swal.fire({
                type : resp.data.case,
                title: resp.data.title,
                text : resp.data.content
              })
            })
        }
      },

      sendSuite () {
        if (this.client_id.length === 6) {
          axios
            .post(this.onSuiteChange, {
              client_id: this.client_id
            })
            .then(resp => {
              if (resp.data.case === 'success') {
                this.client_name       = resp.data.client.name
                this.client_surname    = resp.data.client.surname
                this.phone             = this.phone || resp.data.client.phone
                this.address           = this.address || resp.data.client.address
                this.showReferrals     = resp.data.has_referral_packages && resp.data.has_referrals
                this.desserts          = this.ownPackages = resp.data.packages.filter((item) => item.client_id === resp.data.client.id)
                this.referralsPackages = resp.data.packages.filter((item) => item.client_id !== resp.data.client.id)
              } else {
                Swal.fire({
                  type : resp.data.case,
                  title: resp.data.title,
                  text : resp.data.content
                })
                this.clear()
              }
            })
            .catch(resp => {
              this.clear()
              Swal.fire({
                type : resp.data.case,
                title: resp.data.title,
                text : resp.data.content
              })
            })
        }
      },

      showPackages () {
        axios
          .post(this.getPackages, {
            area        : this.area,
            payment_type: this.payment_type,
            client_id   : this.client_id
          })
          .then(resp => {
            if (resp.data.case === 'success') {
              this.desserts = resp.data.packages
            } else {
              this.dialog = false
              Swal.fire({
                type : resp.data.case,
                title: resp.data.title,
                text : resp.data.content
              })
            }
          })
          .catch(resp => {
            this.dialog = false
            Swal.fire({
              type : resp.data.case,
              title: resp.data.title,
              text : resp.data.content
            })
          })
      },

      save () {
        let ids = ''
        this.selected.forEach(el => {
          ids += ',' + el.id
        })
        const d          = this.date
        const datestring = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate()
        axios
          .post(this.createOrder, {
            client_id               : this.client_id,
            // zone_id: this.zone,
            area_id                 : this.area,
            address                 : this.address,
            phone                   : this.phone,
            date                    : datestring,
            courier_payment_type_id : this.payment_type,
            delivery_payment_type_id: this.delivery_payment_type,
            urgent_order            : this.urgent_order,
            // remark: this.description,
            packages_list           : ids.substring(1),
            metro_station_id        : this.metroStation
          })
          .then(resp => {
            if (resp.data.case === 'success') {
              this.clear()
              Swal.fire({
                type : resp.data.case,
                title: resp.data.title,
                text : resp.data.content
              })
            } else {
              Swal.fire({
                type : resp.data.case,
                title: resp.data.title,
                text : resp.data.content
              })
            }
          })
          .catch(resp => {
            Swal.fire({
              type : resp.data.case,
              title: resp.data.title,
              text : resp.data.content
            })
          })
      },

      getDeliveryPaymentType () {
        if (this.payment_type) {
          axios.post(this.onTypeChange, {
            area_id             : this.area,
            courier_payment_type: this.payment_type
          })
               .then(resp => {
                 if (resp.data.case === 'success') {
                   this.delivery_payment_types = resp.data.payment_types
                   this.delivery_payment_type  = ''
                 } else {
                   Swal.fire({
                     type : resp.data.case,
                     title: resp.data.title,
                     text : resp.data.content
                   })
                 }
               })
               .catch(resp => {
                 Swal.fire({
                   type : resp.data.case,
                   title: resp.data.title,
                   text : resp.data.content
                 })
               })
        }
      }
    },
    watch       : {
      showReferralsPackages (val) {
        if (val) {
          this.desserts = [...this.ownPackages, ...this.referralsPackages]
        } else {
          this.desserts = [...this.ownPackages]
        }
      }
    },
    computed    : {
      deliveryAmount () {
        let v = 0
        for (const must of this.selected) {
          v += parseFloat(must.amount)
        }
        return v.toFixed(2)
      },
      courierAmount () {
        return (parseFloat(this.tariff || 0) + parseFloat(this.urgent_order ? this.amountForUrgent : 0)).toFixed(2)
      },
      totalAmount () {
        return (parseFloat(this.deliveryAmount) + parseFloat(this.courierAmount)).toFixed(2)
      },

      disabledDates () {
        const from = new Date(this.minDate)
        return {
          // to: new Date(2016, 0, 5), // Disable all dates up to specific date
          // from: new Date(2016, 0, 26), // Disable all dates after specific date
          days  : [7, 0], // Disable Sunday's
          // daysOfMonth: [29, 30, 31], // Disable 29th, 30th and 31st of each month
          // dates: [ // Disable an array of dates
          //   new Date(2016, 9, 16),
          //   new Date(2016, 9, 17),
          //   new Date(2016, 9, 18)
          // ],
          ranges: [{ // Disable dates in given ranges (exclusive).
            from: new Date(1000, 1, 1),
            to  : this.dateNow.setDate(this.dateNow.getDate())
          }, {
            from: addWorkDaysIncludingSaturday(from, 3),
            to  : new Date(9999, 12, 31)
          }]
          // a custom function that returns true if the date is disabled
          // this can be used for wiring you own logic to disable a date if none
          // of the above conditions serve your purpose
          // this function should accept a date and return true if is disabled
          // customPredictor: function (date) {
          //   // disables the date if it is a multiple of 5
          //   if (date.getDate() % 5 == 0) {
          //     return true
          //   }
          // }
        }
      }
    }/*,
    watch       : {
      date: function (d) {
        console.log(d)
        let datestring = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate()
        console.log(datestring)
      }
    } */
  }
</script>
