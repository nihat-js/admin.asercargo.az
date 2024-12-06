<template>
	<v-app class="wrap">
		<div class="search_buttons">
			<VBtnToggle v-model="search.status" @change="searchSS" mandatory>
				<v-btn color="primary" value="1">Paid ({{myStatusCount.paid}})</v-btn>
				<v-btn color="primary" value="2">Ordered ({{myStatusCount.ordered}})</v-btn>
				<v-btn color="primary" value="3">Declined ({{myStatusCount.declined}})</v-btn>
				<v-btn color="primary" value="0">ALL ({{myStatusCount.all}})</v-btn>
				<v-btn color="primary" value="4">Old ({{myStatusCount.old}})</v-btn>
			</VBtnToggle>
		</div>
		<v-data-table
				:headers="headers"
				:items="desserts"
				disable-sort
				disable-filtering
				class="elevation-1"
				loading-text="Loading... Please wait"
				:loading=isLoading
				hide-default-footer
				disable-pagination
		>
			<template v-slot:top>
				<v-toolbar flat>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.order"
							label="Order ID"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-divider
							class="mx-4"
							inset
							vertical
					></v-divider>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.code"
							label="Payment ID"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-divider
							class="mx-4"
							inset
							vertical
					></v-divider>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.suite"
							label="Suite"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-divider
							class="mx-4"
							inset
							vertical
					></v-divider>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.name"
							label="Name"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-divider
							class="mx-4"
							inset
							vertical
					></v-divider>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.surname"
							label="Surname"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-dialog v-model="dialog" width="80%">
						<v-card>
							
							<v-card-text>
								<v-container>
									<template>
										<template>
											<div class="client_table">
												<v-switch v-if="access"
												          :error="!clientDisable"
												          :success="clientDisable"
												          v-model="clientDisable"
												          color="blue"
												          :inset="false"
												          @change="disableEnable"
												></v-switch>
												<tr>
													<th class="text-left">Order Id</th>
													<td>{{ details.order_id }}</td>
												</tr>
												<tr>
													<th class="text-left">Suite</th>
													<td>{{ details.suite }}</td>
												</tr>
												<tr>
													<th class="text-left">Client</th>
													<td>{{ details.name + ' ' + details.surname }}</td>
												</tr>
												<tr>
													<th class="text-left">Phone</th>
													<td>{{ details.phone }}</td>
												</tr>
												<tr>
													<th class="text-left">Email</th>
													<td>{{ details.email }}</td>
												</tr>
												<tr>
													<th class="text-left">country</th>
													<td>{{ details.country }}</td>
												</tr>
												<tr>
													<th class="text-left">language</th>
													<td>{{ details.language }}</td>
												</tr>
											</div>
										</template>
									</template>
									<v-row style="margin-left: 250px;min-height:600px;">
										<v-col cols="12" v-if="error">
											<v-alert type="error">
												Please fill correctly all inputs !
											</v-alert>
										</v-col>
										
										
										<div class="singleOrder" v-for="item in editedItem"
										     :class="{'disableOrder':(item.last_status_id === 12 || item.last_status_id===13 || item.last_status_id===21 || item.last_status_id===22 || item.last_status_id===23 || item.last_status_id===24 || item.last_status_id===25 ),}">
											
											<v-col cols="12" sm="12" md="12">
												<h1>{{item.num}}</h1>
											</v-col>
											<v-col cols="12" sm="12" md="12">
												<a target="_blank" :href="item.url">
													<v-text-field disabled required v-validate="''" outlined
													              :error-messages="errors.collect('url')"
													              data-vv-name="url"
													              :value="item.base_url" label="url"></v-text-field>
												</a>
											</v-col>
											
											<v-col cols="12" sm="2" md="2">
												<v-text-field disabled required v-validate="''"
												              outlined
												              :error-messages="errors.collect(`size${item.id}`)"
												              :data-vv-name="'size'+item.id"
												              v-model="item.size" label="Size"></v-text-field>
											</v-col>
											<v-col cols="12" sm="4" md="4">
												<v-text-field disabled required v-validate="''"
												              outlined
												              :error-messages="errors.collect(`color${item.id}`)"
												              :data-vv-name="'color'+item.id"
												              v-model="item.color" label="Color"></v-text-field>
											</v-col>
											<v-col cols="12" sm="3" md="3">
												<v-text-field required v-validate="'required'"
												              outlined
												              :error-messages="errors.collect(`quantity${item.id}`)"
												              :data-vv-name="'quantity'+item.id"
												              v-model="item.quantity" label="quantity"></v-text-field>
											</v-col>
											<v-col cols="12" sm="3" md="3">
												<v-text-field disabled required v-validate="''"
												              outlined
												              :error-messages="errors.collect(`single_price${item.id}`)"
												              :data-vv-name="'single_price'+item.id"
												              v-model="item.single_price" label="Single price"></v-text-field>
											</v-col>
											
											<v-col cols="12" sm="12" md="12">
												<v-textarea disabled rows="1" auto-grow clearable v-model="item.description"
												            outlined
												            v-validate="''"
												            :error-messages="errors.collect(`description${item.id}`)"
												            :data-vv-name="'description'+item.id" label="Description"></v-textarea>
											</v-col>
											<v-col cols="12" sm="6" md="4">
												<v-select
														v-validate="'required'" :error-messages="errors.collect(`Select${item.id}`)"
														:data-vv-name="'Select'+item.id"
														v-model="item.last_status_id"
														:items="status"
														item-text="status"
														item-value="id"
														label="Status"
														@change="setStatus(item.no)"
												></v-select>
											</v-col>
											
											<v-col cols="12" sm="6" md="6"
											       v-if="item.last_status_id===13 || item.last_status_id===21 ||item.last_status_id===22">
												<v-text-field required v-validate="'required'"
												              :error-messages="errors.collect(`order_number${item.id}`)"
												              :data-vv-name="'order_number'+item.id"
												              v-model="item.order_number" label="Order number"></v-text-field>
											</v-col>
											<br>
										</div>
										<v-col cols="12" sm="4" md="4">
											<v-text-field disabled required v-validate="''"
											              :error-messages="errors.collect('price')"
											              data-vv-name="price"
											              v-model="details.single_price"
											              label="Price"></v-text-field>
										</v-col>
										<v-col cols="12" sm="4" md="4">
											<v-text-field disabled v-validate="''"
											              :error-messages="errors.collect('percent')"
											              data-vv-name="percent"
											              :value="(details.single_price*7/100).toFixed(2)"
											              label="7%"></v-text-field>
										</v-col>
										<v-col cols="12" sm="4" md="4">
											<v-text-field disabled v-validate="''"
											              :error-messages="errors.collect('total')"
											              data-vv-name="total"
											              v-model="details.price"
											              label="Total"></v-text-field>
										</v-col>
										
										<v-col cols="12" sm="6" md="4">
											<v-text-field v-validate="''"
											              type="number"
											              :error-messages="errors.collect('common_debt')"
											              data-vv-name="common_debt"
											              v-model="details.common_debt" label="common debt"></v-text-field>
										</v-col>
										
										<v-col cols="12" sm="6" md="4">
											<v-text-field type="number" v-validate="''"
											              :error-messages="errors.collect('cargo_debt')" data-vv-name="cargo_debt"
											              v-model="details.cargo_debt" label="cargo debt"></v-text-field>
										</v-col>
										<v-col cols="12" sm="6" md="4">
											<v-select
													v-validate="'required'" :error-messages="errors.collect('Select')"
													data-vv-name="Select"
													v-model="details.last_status_id"
													:items="status"
													item-text="status"
													item-value="id"
													label="Status"
											></v-select>
										</v-col>
										<v-col cols="12" sm="6" md="6">
											<v-text-field required
											              v-model="commonOrderNumber" @change="setCommonOrderNumber"
											              label="Common Order number"></v-text-field>
										</v-col>
										<v-col cols="12" sm="6" md="6">
											<v-checkbox
													v-model="details.sms_status"
													label="Send SMS"
											></v-checkbox>
										</v-col>
									</v-row>
								</v-container>
							</v-card-text>
							
							<v-card-actions>
								<div class="flex-grow-1"></div>
								<v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
								<v-btn color="blue darken-1" text @click="save">Save</v-btn>
							</v-card-actions>
						</v-card>
					</v-dialog>
				</v-toolbar>
				<!-- <div class="flex-grow-1"></div> -->
			
			</template>
			<template v-slot:item.client="{ item }">
				<td>{{item.name + ' ' + item.surname}}</td>
			</template>
			<template v-slot:item.operator="{ item }">
				<td>{{item.operator_name + ' ' + item.operator_surname}}</td>
			</template>
			<template v-slot:item.debtCommon="{ item }">
				<td :class="{ 'debt': parseFloat(item.common_debt)>0 }">{{item.common_debt}}</td>
			</template>
			<template v-slot:item.debtCargo="{ item }">
				<td :class="{ 'debt': parseFloat(item.cargo_debt)>0 }">{{item.cargo_debt}}</td>
			</template>
			<template v-slot:item.action="{ item }">
				<v-icon
						small
						class="mr-2"
						@click="editItem(item)"
				>
					edit
				</v-icon>
			</template>
			<template v-slot:no-data>
				<h2>There is nothing!</h2>
				<v-btn color="primary" @click="initialize">Reset</v-btn>
			</template>
		</v-data-table>
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
	</v-app>
</template>

<script>
  import Swal     from 'sweetalert2'
  import debounce from 'debounce'

  export default {
    $_veeValidate: {
      validator: 'new',
    },
    props        : {
      'myPackage'    : {
        type: String,
      },
      'myStatus'     : {
        type: String,
      },
      'myGet'        : {
        type: String,
      },
      'myUpdate'     : {
        type: String,
      },
      'myDisable'    : {
        type: String,
      },
      'myStatusCount': {
        type: Object
      }
    },
    data         : () => ({
      access           : false,
      clientDisable    : false,
      pagination       : {
        current: 1,
        total  : 0,
      },
      circle           : true,
      nextIcon         : 'navigate_next',
      prevIcon         : 'navigate_before',
      totalVisible     : 5,
      status           : [],
      error            : false,
      isLoading        : true,
      search           : {
        order  : '',
        name   : '',
        surname: '',
        suite  : '',
        code   : '',
        status : '',
      },
      dialog           : false,
      image            : false,
      headers          : [
        { text: 'No', value: 'no' },
        {
          text : 'Payment ID',
          align: 'left',
          value: 'payment_key',
        },
        { text: 'Order ID', value: 'id' },
        { text: 'Suite', value: 'suite' },
        { text: 'Client', value: 'client' },
        { text: 'Country', value: 'country' },
        { text: 'Status', value: 'status' },
        { text: 'Operator', value: 'operator' },
        { text: 'Price', value: 'price' },
        { text: 'Cargo Debt', value: 'debtCargo' },
        { text: 'Common Debt', value: 'debtCommon' },
        { text: 'Created date', value: 'created_at' },
        { text: 'Paid date', value: 'paid_at' },
        { text: 'Actions', value: 'action', sortable: false },
      ],
      desserts         : [],
      editedIndex      : -1,
      editedStaffID    : [],
      editedItem       : {},
      defaultItem      : {
        id         : 0,
        title      : '',
        description: '',
        category_id: '',
        link       : '',
        img_src    : '',
        video_src  : '',
      },
      details          : [],
      commonOrderNumber: '',
    }),

    computed: {
      formTitle () {
        return 'Make Order'
      },
      disableClient () {
        return this.editedItem.disable ? 'Enabled' : 'Disabled'
      },
      commonStatus () {
        return this.details.last_status_id
      },
    },

    watch: {
      dialog (val) {
        val || this.close()
      },
      commonStatus (val) {
        this.editedItem.forEach((item) => {
          if (item.status_changed === false) {
            item.last_status_id = val
          }
          // if (!item.last_status_id || item.last_status_id === 13) {
          //   item.last_status_id = val
          // }
        })
      },
    },

    created () {
      this.getStatus()
      /*this.searchSS = debounce(this.searchSS, 1000)*/
    },

    methods: {
      initialize () {
        let app       = this
        app.isLoading = true
        axios.get(
          this.myPackage + '?code=' + this.search.code + '&order=' + this.search.order + '&suite=' + this.search.suite + '&name=' + this.search.name + '&surname=' + this.search.surname + '&status=' + (this.search.status ?? null) + '&page=' + this.pagination.current)
             .then((resp) => {
               if (resp.data.case === 'success') {
                 app.desserts           = resp.data.orders.data
                 app.pagination.current = resp.data.orders.current_page
                 app.pagination.total   = resp.data.orders.last_page
               }
             })
             .catch((resp) => {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : 'Something went wrong!',
               })
             })
             .finally(() => {
               app.isLoading = false
             })
      },

      initializePage () {
        let app = this
        axios.get(
          this.myPackage + '?code=' + this.search.code + '&suite=' + this.search.suite + '&name=' + this.search.name + '&surname=' + this.search.surname + '&status=' + (this.search.status ?? null) + '&page=' + this.pagination.current)
             .then((resp) => {
               if (resp.data.case === 'success') {
                 app.pagination.current = resp.data.current_page
                 app.pagination.total   = resp.data.last_page
               }
             })
             .catch((resp) => {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : 'Something went wrong!',
               })
             })
      },

      onPageChange () {
        this.initialize()
      },

      getStatus () {
        let app = this
        axios.get(this.myStatus)
             .then((resp) => {
               if (resp.data.case === 'success') {
                 app.status = resp.data.statuses
               }
             })
             .catch((resp) => {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : 'Something went wrong!',
               })
             })
      },

      editItem (item) {
        let app = this
        axios.get(this.myGet + '/' + item.group_code)
             .then((resp) => {
               if (resp.data.case === 'success') {
                 app.editedItem    = resp.data.orders
                 app.details       = resp.data.details
                 app.clientDisable = !!app.details.disable
                 app.access        = !(app.details.placed_by !== null || app.details.canceled_by !== null)
               } else {
                 console.log(resp)
               }
             })
             .catch((resp) => {
               console.log(resp)
             })
        this.dialog = true
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

      save () {
        let app = this
        this.$validator.validateAll()
            .then(responses => {
              if (responses) {

                const app = this

                let newWork = app.editedItem
                /*let formData = new FormData()
								formData.append('status_id', app.editedItem.last_status_id)
								formData.append('quantity', app.editedItem.quantity)
								formData.append('common_debt', app.editedItem.common_debt)
								formData.append('cargo_debt', app.editedItem.cargo_debt)
								formData.append('title', app.editedItem.title)
								formData.append('order_number', app.editedItem.order_number??null)*/

                let details = this.details
                let orders  = this.editedItem
                if (this.details.last_status_id !== 18 && this.details.last_status_id !== 21 && this.details.last_status_id !== 22 && this.details.last_status_id !== 26 && this.details.last_status_id !== 27) {
                  this.details.sms_status = false
                }

                axios.post(this.myUpdate + '/' + this.details.group_code, { details, orders })
                     .then((resp) => {
                       if (resp.data.case === 'success') {
                         Swal.fire({
                           type : 'success',
                           title: 'Saved',
                         })
                         app.initialize()
                         app.close()
                       } else {
                         app.error = true
                         Swal.fire({
                           type : 'error',
                           title: 'Wrong input data!',
                           text : resp.content,
                         })
                       }
                     })
                     .catch((resp) => {
                       Swal.fire({
                         type : 'error',
                         title: 'Oops...',
                         text : resp.content,
                       })
                     })

              } else {
                this.error = true
              }
            })
      },

      searchSS () {
        this.initialize()
      },

      disableEnable () {
        let dis      = this.clientDisable ? 1 : 0
        let _this    = this
        let formData = new FormData()
        formData.append('type', dis.toString())
        formData.append('id', this.editedItem.id)
        axios.post(this.myDisable, formData)
             .then((resp) => {
               if (resp.data.case === 'success') {
               }
             })
             .catch(() => {
             })
      },
      setCommonOrderNumber () {
        this.editedItem.forEach((item) => {
          if (!item.order_number) {
            item.order_number = this.commonOrderNumber
          }
        })
      },
      setStatus (item_no) {
        this.editedItem[item_no].status_changed = true
      },

      test () {
        console.log(this.search)
      },
    },
  }
</script>

