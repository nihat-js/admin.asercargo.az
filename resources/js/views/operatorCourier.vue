<template>
	<v-container fluid>
		<v-row>
			<v-col cols="12">
				<v-toolbar height="prominent">
					<v-row no-gutters>
						<transition name="fade">
							<v-col v-show="openSearch" cols="12 pa-0" no-gutters>
								<v-row class="pa-4">
									<!-- <v-col cols="4">
																																										<v-text-field type='number' v-model="search.no" label="Order Number" single-line hide-details></v-text-field>
									</v-col>-->
									<v-col cols="4">
										<v-text-field
												v-model="search.name"
												label="Name"
												single-line
												hide-details
										></v-text-field>
									</v-col>
									<v-col cols="4">
										<v-text-field
												v-model="search.surname"
												label="Surname"
												single-line
												hide-details
										></v-text-field>
									</v-col>
									<v-col cols="4">
										<v-text-field
												v-model="search.suite"
												label="Suite"
												single-line
												hide-details
										></v-text-field>
									</v-col>
									<v-col cols="4">
										<v-select
												v-model="search.status"
												:items="statuses"
												item-text="name"
												item-value="id"
												label="Status"
												hide-details
												clearable
										></v-select>
									</v-col>
									<v-col cols="4">
										<v-select
												v-model="search.courier"
												:items="couriers"
												item-text="name"
												item-value="id"
												label="Couriers"
												hide-details
												clearable
										></v-select>
									</v-col>
									<v-col cols="4">
										<v-select
												v-model="search.areas"
												:items="areas"
												item-text="name"
												item-value="id"
												label="Areas"
												hide-details
												clearable
										></v-select>
									</v-col>
									<!-- <v-col cols="4">
																																										<v-select v-model="search.courier_payment_type" :items="payment_types" item-text="name" item-value="id" label="Payment Types" hide-details clearable></v-select>
																																									</v-col>
																																									<v-col cols="4">
																																										<v-select v-model="search.delivery_payment_type" :items="payment_types" item-text="name" item-value="id" label="Payment Types" hide-details clearable></v-select>
									</v-col>-->
									<v-col cols="4">
										<v-menu
												v-model="menu1"
												:close-on-content-click="false"
												:nudge-right="40"
												transition="scale-transition"
												offset-y
												min-width="290px"
										>
											<template v-slot:activator="{ on }">
												<v-text-field
														v-model="search.date"
														label="Date"
														prepend-icon="event"
														readonly
														v-on="on"
												></v-text-field>
											</template>
											<v-date-picker
													v-model="search.date"
													@input="menu1 = false"
											></v-date-picker>
										</v-menu>
									</v-col>
									<v-col cols="2">
										<v-checkbox
												v-model="old_orders"
												label="Show old orders"
										></v-checkbox>
									</v-col>
									<v-col cols="2">
										<v-btn class="ma-3 pa-2" color="blue" @click="initialize()"
										>Search
										</v-btn
										>
									</v-col>
									<v-col cols="2">
										<v-btn
												class="ma-3 pa-2"
												:disabled="!search.date"
												target="_blank"
												color="blue"
												:href="
                        exportTable +
                        '/' +
                        '?name=' +
                        search.name +
                        '&surname=' +
                        search.surname +
                        '&suite=' +
                        search.suite +
                        '&courier=' +
                        (search.courier || null) +
                        '&no=' +
                        (search.no || null) +
                        '&status=' +
                        (search.status || null) +
                        '&area=' +
                        (search.area || null) +
                        '&courier_payment_type=' +
                        (search.courier_payment_type || null) +
                        '&delivery_payment_type=' +
                        (search.delivery_payment_type || null) +
                        '&date=' +
                        (search.date || null) +
                        '&type=1'
                      "
										>Export PDF
										</v-btn
										>
									</v-col>
									<v-col cols="2">
										<v-btn
												class="ma-3 pa-2"
												:disabled="!search.date"
												color="blue"
												:href="
                        exportTable +
                        '/' +
                        '?name=' +
                        search.name +
                        '&surname=' +
                        search.surname +
                        '&suite=' +
                        search.suite +
                        '&courier=' +
                        (search.courier || null) +
                        '&no=' +
                        (search.no || null) +
                        '&status=' +
                        (search.status || null) +
                        '&area=' +
                        (search.area || null) +
                        '&courier_payment_type=' +
                        (search.courier_payment_type || null) +
                        '&delivery_payment_type=' +
                        (search.delivery_payment_type || null) +
                        '&date=' +
                        (search.date || null) +
                        '&type=2'
                      "
										>Export Excel
										</v-btn
										>
									</v-col>
								</v-row>
							</v-col>
						</transition>
						<v-col cols="12">
							<v-row justify="center">
								<v-btn
										class="ma-3 pa-2"
										color="blue"
										@click="openSearch = !openSearch"
								>{{
								 openSearch ? 'Close Search Panel' : 'Open Search Panel'
								 }}
								</v-btn
								>
								<v-btn class="ma-3 pa-2" color="blue" @click="allPackages"
								>All Packages
								</v-btn
								>
								<v-btn class="ma-3 pa-2" color="blue" :href="newCourierPage"
								>New Courier Order
								</v-btn
								>
							</v-row>
						</v-col>
						<v-col cols="12">
							<v-row justify="center">
								<v-btn
										class="ma-3"
										color="blue"
										:disabled="!selected.length"
										@click="setCourierToSelected"
								>
									<v-icon>mdi-package-variant-closed</v-icon>
									Set Courier to
									Selected Orders
								</v-btn>
							</v-row>
						</v-col>
					</v-row>
				</v-toolbar>
			</v-col>
			<v-col cols="12">
				<v-data-table
						:headers="headers"
						:items="desserts"
						show-select
						v-model="selected"
						disable-sort
						disable-filtering
						class="elevation-1"
						loading-text="Loading... Please wait"
						:loading="isLoading"
						hide-default-footer
						disable-pagination
						row-class="urgent"
				>
					<template v-slot:item="{ isSelected, select, item }">
						<tr :class="item.urgent ? 'urgent' : ''">
							<td>
								<v-simple-checkbox
										:value="isSelected"
										@input="select($event)"
								></v-simple-checkbox>
							</td>
							<td>{{ item.no }}</td>
							<td>{{ item.id }}</td>
							<td>{{ item.suite }}</td>
							<td>{{ item.client_name + ' ' + item.client_surname }}</td>
							<td>{{ item.passport_number }}</td>
							<td>{{ item.phone }}</td>
							<td>{{ item.area }}</td>
              <td>{{ item.region }}</td>
              <td>{{ item.post_zip }}</td>
              <td :class="item.is_send_azerpost ? 'is_send_azerpost' : ''">{{ item.azerpost_track }}</td>
							<td>{{ item.metro_station }}</td>
							<td>{{ item.address }}</td>
							<td>{{ item.date }}</td>
							<td>{{ item.courier_payment_type }}</td>
							<td>{{ item.delivery_payment_type }}</td>
							<!-- <td>{{ item.courier_id }}</td> -->
							<td>
								{{
								(item.courier_name || '') + ' ' + (item.courier_surname || '')
								}}
							</td>
							<td>{{ item.delivery_amount }}</td>
							<td>{{ item.shipping_amount }}</td>
							<td>{{ item.summary_amount }}</td>
							<td>{{ item.status }}</td>
							<td>{{ item.created_at }}</td>
							<td>
								<div>
							<td>
								<v-icon small @click="showPackages(item)">mdi-eye</v-icon>
							</td>
							<td>
								<v-icon small @click="showCourier(item)"
								>mdi-package-variant-closed
								</v-icon
								>
							</td>
							<td>
								<v-icon small @click="showStatus(item)">mdi-pencil</v-icon>
							</td>
							<td>
								<v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
							</td>
							</div>
							</td>
						</tr>
					</template>
					<!-- <template v-slot:item.client="{ item }">
				{{item.client_name + ' ' + item.client_surname}}
			</template>
			<template v-slot:item.courier="{ item }">
				{{(item.courier_name || '') + ' ' + (item.courier_surname || '')}}
			</template>
			<template v-slot:item.actions="{ item }">
				<td>
				<v-icon
					small
					@click="showPackages(item)"
				>
					mdi-eye
				</v-icon>
				</td>
				<td>
				<v-icon
					small
					@click="showCourier(item)"
				>
					mdi-package-variant-closed
				</v-icon>
				</td>
				<td>
				<v-icon
					small
					@click="showStatus(item)"
				>
					mdi-pencil
				</v-icon>
				</td>

					</template>-->
				</v-data-table>
			</v-col>
		</v-row>
		<template>
			<div class="text-center">
				<v-pagination
						v-model="pagination.current"
						:length="pagination.total"
						@input="onPageChange"
						:circle="circle"
						:next-icon="nextIcon"
						:prev-icon="prevIcon"
						:total-visible="totalVisible"
				></v-pagination>
			</div>
		</template>
		<template>
			<v-row justify="center">
				<v-dialog v-model="dialog" max-width="700">
					<v-card>
						<v-card-title class="headline">Packages</v-card-title>
						<v-card-text>
							<v-simple-table>
								<template v-slot:default>
									<thead>
									<tr>
										<th class="text-left">ID</th>
										<th class="text-left">Number</th>
										<th class="text-left">Internal Id</th>
										<th class="text-left">Amount</th>
										<th class="text-left">Paid Status</th>
										<th class="text-left">Client</th>
									</tr>
									</thead>
									<tbody>
									<tr v-for="item in packages_object" :key="item.id">
										<td>{{ item.id }}</td>
										<td>{{ item.number }}</td>
										<td>{{ item.internal_id }}</td>
										<td>{{ item.amount }}</td>
										<td>{{ item.paid_status }}</td>
										<td>
											{{
											(item.client_name || '') +
											' ' +
											(item.client_surname || '')
											}}
										</td>
									</tr>
									</tbody>
								</template>
							</v-simple-table>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn color="green darken-1" text @click="dialog = false"
							>close
							</v-btn
							>
						</v-card-actions>
					</v-card>
				</v-dialog>
			</v-row>
		</template>
		<template>
			<v-row justify="center">
				<v-dialog v-model="dialogCourier" max-width="700">
					<v-card>
						<v-card-title class="headline">Set Courier</v-card-title>
						<v-card-text>
							<v-select
									v-model="setCourierData.courier_id"
									:items="couriers"
									item-text="name"
									item-value="id"
									label="Courier"
									hide-details
									clearable
							></v-select>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn
									color="green darken-1"
									text
									@click="
                  ;(dialogCourier = false), (setCourierData.courier_id = '')
                "
							>close
							</v-btn
							>
							<v-btn
									color="green darken-1"
									:loading="loading"
									:disabled="!setCourierData.courier_id"
									text
									@click="sendCourier()"
							>Save
							</v-btn
							>
						</v-card-actions>
					</v-card>
				</v-dialog>
			</v-row>
		</template>
		<template>
			<v-row justify="center">
				<v-dialog v-model="dialogStatus" max-width="700">
					<v-card>
						<v-card-title class="headline">Set Status</v-card-title>
						<v-card-text>
							<v-select
									v-model="setStatusData.status"
									:items="setStatuses"
									item-text="name"
									item-value="id"
									label="Status"
									hide-details
									clearable
							></v-select>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn color="green darken-1" text @click="dialogStatus = false"
							>close
							</v-btn
							>
							<v-btn
									color="green darken-1"
									:disabled="!setStatusData.status"
									text
									@click="sendStatus()"
							>Save
							</v-btn
							>
						</v-card-actions>
					</v-card>
				</v-dialog>
			</v-row>
		</template>
	</v-container>
</template>

<script>
  import Swal from 'sweetalert2'

  export default {
    inheritAttrs: false,
    props       : {
      myRoute       : {
        type    : String,
        required: true
      },
      newCourierPage: {
        type    : String,
        required: true
      },
      deleteRoute   : {
        type    : String,
        required: true
      },
      exportTable   : {
        type    : String,
        required: true
      },
      setCourier    : {
        type    : String,
        required: true
      },
      setStatus     : {
        type    : String,
        required: true
      },
      statuses      : {
        type    : Array,
        required: true
      },
      couriers      : {
        type    : Array,
        required: true
      },
      areas         : {
        type    : Array,
        required: true
      },
      payment_types : {
        type    : Array,
        required: true
      }
    },
    data () {
      return {
        ifSelected     : false,
        loading        : false,
        selected       : [],
        openSearch     : false,
        dialog         : false,
        menu1          : false,
        dialogCourier  : false,
        dialogStatus   : false,
        headers        : [
          { text: 'No', value: 'no' },
          { text: 'Order Number', value: 'id' },
          { text: 'Suite', value: 'suite' },
          { text: 'Client Name ', value: 'client' },
          { text: 'Passport Number ', value: 'passport_number' },
          { text: 'Phone', value: 'phone' },
          { text: 'Area', value: 'area' },
          { text: 'Region', value: 'region' },
          { text: 'Post Index', value: 'post_zip' },
          { text: 'Azerpost Track', value: 'azerpost_track' },
          { text: 'Metro Station', value: 'metro_station' },
          { text: 'Address', value: 'address' },
          { text: 'Date', value: 'date' },
          { text: 'Courier Payment Type', value: 'courier_payment_type' },
          { text: 'Delivery Payment Type', value: 'delivery_payment_type' },
          //  { text: 'Courier Id', value: 'courier_id' },
          { text: 'Courier', value: 'courier' },
          { text: 'Delivery Amount', value: 'delivery_amount' },
          { text: 'Shipping Amount', value: 'shipping_amount' },
          { text: 'Summary Amount', value: 'summary_amount' },
          { text: 'Status', value: 'status' },
          { text: 'Created At', value: 'created_at' },
          // { text: 'At the Courier', value: 'at_the_courier' },
          { text: 'Actions', value: 'actions', sortable: false, align: 'center' }
        ],
        desserts       : [],
        pagination     : {
          current: 1,
          total  : 0,
          perPage: 30
        },
        circle         : true,
        nextIcon       : 'navigate_next',
        prevIcon       : 'navigate_before',
        totalVisible   : 5,
        isLoading      : false,
        search         : {
          old_orders: false
        },
        setCourierData : {
          order_id  : '',
          courier_id: ''
        },
        setStatusData  : {
          status  : '',
          order_id: ''
        },
        packages_object: '',
        setStatuses    : [
          {
            id  : '3',
            name: 'Delivered'
          },
          {
            id  : '12',
            name: 'Canceled'
          },
          {
            id  : '13',
            name: 'Order Placed'
          }
        ],
        old_orders     : false
      }
    },
    methods     : {
      initialize () {
        const _this     = this
        _this.isLoading = true
        axios
          .get(
            this.myRoute +
            '?name=' +
            this.search.name +
            '&surname=' +
            this.search.surname +
            '&suite=' +
            this.search.suite +
            '&courier=' +
            (this.search.courier ?? null) +
            // '&no=' +
            // (this.search.no ?? null) +
            '&status=' +
            (this.search.status ?? null) +
            '&area=' +
            (this.search.area ?? null) +
            // '&courier_payment_type=' +
            // (this.search.courier_payment_type ?? null) +
            // '&delivery_payment_type=' +
            // (this.search.delivery_payment_type ?? null) +
            '&date=' +
            (this.search.date ?? null) +
            '&old_orders=' +
            (+this.old_orders ?? 0) +
            '&page=' +
            this.pagination.current
          )
          .then((resp) => {
            if (resp.data.case === 'success') {
              _this.desserts           = resp.data.orders.data
              _this.pagination.current = resp.data.orders.current_page
              _this.pagination.total   = resp.data.orders.last_page
              let i                    = 1
              _this.desserts.forEach(
                (item) =>
                  (item.no =
                    (this.pagination.current - 1) * this.pagination.perPage + i++)
              )
            }
          })
          .catch((resp) => {
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

      initializePage () {
        const _this = this
        axios
          .get(
            this.myRoute +
            '?name=' +
            this.search.name +
            '&surname=' +
            this.search.surname +
            '&suite=' +
            this.search.suite +
            '&courier=' +
            (this.search.courier ?? null) +
            // '&no=' +
            // (this.search.no ?? null) +
            '&status=' +
            (this.search.status ?? null) +
            '&area=' +
            (this.search.area ?? null) +
            // '&courier_payment_type=' +
            // (this.search.courier_payment_type ?? null) +
            // '&delivery_payment_type=' +
            // (this.search.delivery_payment_type ?? null) +
            '&date=' +
            (this.search.date ?? null) +
            '&page=' +
            this.pagination.current
          )
          .then((resp) => {
            if (resp.data.case === 'success') {
              _this.pagination.current = resp.data.current_page
              _this.pagination.total   = resp.data.last_page
            }
          })
          .catch((resp) => {
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
        this.setStatusData.order_id    = ''
        this.setStatusData.status      = ''
        this.setCourierData.order_id   = ''
        this.setCourierData.courier_id = ''
      },

      showCourier (item) {
        this.ifSelected              = false
        this.setCourierData.order_id = item.id
        this.dialogCourier           = true
      },
      async sendCourier () {
        this.loading = true
        if (!this.ifSelected) {
          axios
            .post(this.setCourier, {
              order_id  : this.setCourierData.order_id,
              courier_id: this.setCourierData.courier_id
            })
            .then((resp) => {
              this.initialize()
              Swal.fire({
                type : resp.data.case,
                title: resp.data.title,
                text : resp.data.content
              })
            })
            .catch((resp) => {
              Swal.fire({
                type : 'error',
                title: 'Oops...',
                text : resp.data.content
              })
            })
            .finally(() => {
              this.dialogCourier             = false
              this.setCourierData.order_id   = ''
              this.setCourierData.courier_id = ''
              this.loading                   = false
            })
        } else if (this.ifSelected) {
          let iS   = 0
          const eI = []
          for (const item of this.selected) {
            await axios
              .post(this.setCourier, {
                order_id  : item.id,
                courier_id: this.setCourierData.courier_id
              })
              .then((resp) => {
                if (resp.data.case === 'success') {
                  iS++
                } else {
                  eI.push(item.id)
                }
              })
              .catch((resp) => {
                eI.push(item.id)
              })
          }
          this.dialogCourier = false
          this.initialize()
          await Swal.fire({
            type : 'success',
            title: 'Success',
            text : `${iS} of ${
              this.selected.length
            }  orders were successful set( Ids of unsettled orders : ${
              eI || 'none'
            })`
          })
          this.setCourierData.courier_id = ''
          this.selected                  = []
          this.loading                   = false
        }
      },

      showStatus (item) {
        this.setStatusData.order_id = item.id
        this.dialogStatus           = true
      },
      setCourierToSelected () {
        this.ifSelected    = true
        this.dialogCourier = true
      },
      sendStatus () {
        axios
          .post(this.setStatus, {
            order_id : this.setStatusData.order_id,
            status_id: this.setStatusData.status
          })
          .then((resp) => {
            this.initialize()
            Swal.fire({
              type : resp.data.case,
              title: resp.data.title,
              text : resp.data.content
            })
          })
          .catch((resp) => {
            Swal.fire({
              type : 'error',
              title: 'Oops...',
              text : resp.data.content
            })
          })
          .finally(() => {
            this.dialogStatus           = false
            this.setStatusData.order_id = ''
            this.setStatusData.status   = ''
          })
      },

      showPackages (item) {
        this.packages_object = item.packages_object
        this.dialog          = true
      },
      allPackages () {
        // for (let member in this.search) delete this.search[member]
        this.search     = {}
        this.old_orders = true
        this.initialize()
      },
      searchSS () {
        this.initialize()
      },
      deleteItem (item) {
        const _this = this
        Swal.fire({
          title             : 'Are you sure?',
          text              : 'You won\'t be able to revert this!',
          type              : 'warning',
          showCancelButton  : true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor : '#d33',
          confirmButtonText : 'Yes, delete it!'
        })
            .then((result) => {
              if (result.value) {
                axios
                  .delete(this.deleteRoute + '?id=' + item.id)
                  .then(function (resp) {
                    if (resp.data.case === 'success') {
                      const old = _this.pagination.current
                      _this.initializePage()
                      Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                      )
                          .finally(() => {
                            if (old >= _this.pagination.total) {
                              _this.pagination.current = _this.pagination.total
                            } else {
                              _this.pagination.current = old
                            }
                            _this.initialize()
                          })
                    } else {
                      Swal.fire({
                        type : 'error',
                        title: 'Oops...',
                        text : resp.data.content
                      })
                    }
                  })
                  .catch(function (resp) {
                    Swal.fire({
                      type : 'error',
                      title: 'Oops...',
                      text : resp.data.content
                    })
                  })
              }
            })
      }
    }
  }
</script>
