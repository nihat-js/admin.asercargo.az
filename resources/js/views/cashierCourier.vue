<template>
	<v-container fluid>
		<v-row no-gutters>
			<v-col cols="12">
				<v-row>
					<v-col cols="4">
						<v-menu v-model="menu1" :close-on-content-click="false" :nudge-right="40" transition="scale-transition" offset-y min-width="290px">
							<template v-slot:activator="{ on }">
								<v-text-field v-model="date" label="Date" prepend-icon="event" readonly v-on="on"></v-text-field>
							</template>
							<v-date-picker @change="getPackages" v-model="date" @input="menu1 = false"></v-date-picker>
						</v-menu>
					</v-col>
					<v-col cols="8">
						<v-text-field hide-details @input="setPackage" :disabled="!date" label="Receipt" v-model="receipt" outlined></v-text-field>
					</v-col>
				</v-row>
			</v-col>
			<v-col cols="12">
				<v-row>
					<v-col cols="12">
						<!--<v-text-field
										v-model="paid_amount"
										label="Paid Amount"
										outlined
										readonly
										shaped
										hide-details
						></v-text-field>-->
						<v-alert
										color="rgba(16, 69, 140, 1)"
										dark
										icon="mdi-account-cash-outline"
										dense
						>
							Paid Amount : {{paid_amount}}
						
						</v-alert>
					</v-col>
					<v-col cols="6">
						<v-row no-gutters>
							<v-col cols="12">
								<v-alert
												dense
												type="info"
								>
									{{totalNotPaid}}
								</v-alert>
								<!--<v-text-field
												v-model="totalNotPaid"
												label="Total Amount"
												outlined
												readonly
												shaped
								></v-text-field>-->
							</v-col>
							<v-col cols="12">
								<v-data-table :headers="headers" :items="notChecked" disable-sort disable-filtering class="elevation-1" loading-text="Loading... Please wait" :loading=isLoading hide-default-footer disable-pagination>
									<template v-slot:item.client="{ item }">
										{{item.client_name + ' ' + item.client_surname}}
									</template>
								</v-data-table>
							</v-col>
						</v-row>
					</v-col>
					<v-col cols="6">
						<v-row no-gutters>
							<v-col cols="12">
								<v-alert
												dense
												type="success"
								>
									{{totalPaid}}
								</v-alert>
								<!--<v-text-field
												v-model="totalPaid"
												label="Total Amount"
												outlined
												readonly
												shaped
								></v-text-field>-->
							</v-col>
							<v-col cols="12">
								<v-data-table :headers="headers" :items="checked" disable-sort disable-filtering class="elevation-1" loading-text="Loading... Please wait" :loading=isLoading hide-default-footer disable-pagination>
									<template v-slot:item.client="{ item }">
										{{item.client_name + ' ' + item.client_surname}}
									</template>
								</v-data-table>
							</v-col>
						</v-row>
					</v-col>
				</v-row>
			</v-col>
		</v-row>
	</v-container>
</template>

<script>
  import Swal       from 'sweetalert2'
  import Datepicker from 'vuejs-datepicker'

  export default {
    inheritAttrs: false,
    components  : {
      Datepicker,
    },
    props       : {
      myRoute : {
        type    : String,
        required: true
      },
      setRoute: {
        type    : String,
        required: true
      },
    },
    data () {
      return {
        dialog      : false,
        headers     : [
          { text: 'Order Number ', value: 'id' },
          { text: 'Suite', value: 'suite' },
          { text: 'Client ', value: 'client' },
          { text: 'Courier Payment Type', value: 'courier_payment_type' },
          { text: 'Delivery Payment Type', value: 'delivery_payment_type' },
          { text: 'Summary Amount', value: 'summary_amount' },
          { text: 'Courier', value: 'courier' },
        ],
        notChecked  : [],
        checked     : [],
        pagination  : {
          current: 1,
          total  : 0
        },
        circle      : true,
        nextIcon    : 'navigate_next',
        prevIcon    : 'navigate_before',
        totalVisible: 5,
        isLoading   : false,
        date        : '',
        menu1       : false,
        receipt     : '',
        paid_amount : 0
      }
    },
    methods     : {
      initialize () {
        let _this       = this
        _this.isLoading = true
        axios.get(
          this.myRoute +
          '?name=' +
          this.search.name +
          '&surname=' +
          this.search.surname +
          '&suite=' +
          this.search.suite +
          '&courier=' +
          (this.search.courier ?? null) +
          '&no=' +
          (this.search.no ?? null) +
          '&status=' +
          (this.search.status ?? null) +
          '&area=' +
          (this.search.area ?? null) +
          '&courier_payment_type=' +
          (this.search.courier_payment_type ?? null) +
          '&delivery_payment_type=' +
          (this.search.delivery_payment_type ?? null) +
          '&date=' +
          (this.search.date ?? null) +
          '&page=' +
          this.pagination.current
        )
             .then(resp => {
               if (resp.data.case === 'success') {
                 _this.desserts           = resp.data.orders.data
                 _this.pagination.current = resp.data.orders.current_page
                 _this.pagination.total   = resp.data.orders.last_page
                 let i                    = 1
                 _this.desserts.forEach(item => item.no = i++)
               }
             })
             .catch(resp => {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : resp.data.content
               })
             })
             .finally(() => {
               _this.isLoading = false
             })
      },

      getPackages () {
        axios.post(this.myRoute, { 'date': new Date(this.date) })
             .then((resp) => {
               if (resp.data.case === 'success') {
                 this.notChecked = resp.data.not_delivered_orders
                 this.checked    = resp.data.delivered_orders
               } else {
                 Swal.fire({
                   type : resp.data.case,
                   title: resp.data.title,
                   text : resp.data.content
                 })
               }
             })
             .catch((resp) => {
               Swal.fire({
                 type : resp.data.case,
                 title: resp.data.title,
                 text : resp.data.content
               })
             })
             .finally(() => {
               this.receipt = ''
             })
      },
      setPackage () {
        if (this.receipt.length === 8) {
          console.log(this.receipt)
          console.log(this.receipt.length)
          axios.post(this.setRoute, { 'receipt': this.receipt, 'date': new Date(this.date) })
               .then((resp) => {
                 if (resp.data.case === 'success') {
                   this.paid_amount += +resp.data.paid_amount
                   this.getPackages()
                   Swal.fire({
                     type : resp.data.case,
                     title: resp.data.case,
                     text : resp.data.content
                   })
                 } else {
                   new Audio('../assets/error.wav').play()
                   setTimeout(() => {
                     new Audio('../assets/error.wav').play()
                   }, 500)
                   Swal.fire({
                     type : resp.data.case,
                     title: resp.data.case,
                     text : resp.data.content
                   })
                 }
               })
               .catch((resp) => {
                 Swal.fire({
                   type : resp.data.case,
                   title: resp.data.title,
                   text : resp.data.content
                 })
               })
               .finally(() => {
                 this.receipt = ''
               })
        }
      },

      initializePage () {
        let _this = this
        axios
          .get(
            this.myPackage +
            '?code=' +
            this.search.code +
            '&suite=' +
            this.search.suite +
            '&name=' +
            this.search.name +
            '&surname=' +
            this.search.surname +
            '&status=' +
            (this.search.status ?? null) +
            '&page=' +
            this.pagination.current
          )
          .then(resp => {
            if (resp.data.case === 'success') {
              _this.pagination.current = resp.data.current_page
              _this.pagination.total   = resp.data.last_page
            }
          })
          .catch(resp => {
            Swal.fire({
              type : 'error',
              title: 'Oops...',
              text : 'Something went wrong!'
            })
          })
      },

      onPageChange () {
        this.initialize()
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
    },
    computed    : {
      totalNotPaid () {
        let v = 0
        for (let must of this.notChecked) {
          v += parseFloat(must.summary_amount)
        }
        return v.toFixed(2) + ' AZN'
      },
      totalPaid () {
        let v = 0
        for (let must of this.checked) {
          v += parseFloat(must.summary_amount)
        }
        return v.toFixed(2) + ' AZN'
      }
    },
    created () {
    }
  }
</script>