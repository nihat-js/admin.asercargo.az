<template>
	<div class="wrap">
		<div class="top">
			<v-text-field
					min=1
					type="number"
					autofocus
					label="Suite"
					placeholder="Suite"
					outlined
					v-model="search.suite"
					clearable
					@keyup.enter="searchButton"
			></v-text-field>
			<v-text-field
					label="Name"
					placeholder="Name"
					outlined
					v-model="search.name"
					clearable
					@keyup.enter="searchButton"
			></v-text-field>
			<v-text-field
					label="Surname"
					placeholder="Surname"
					outlined
					v-model="search.surname"
					clearable
					@keyup.enter="searchButton"
			></v-text-field>
			<v-text-field
					label="Passport"
					placeholder="Passport"
					outlined
					v-model="search.passport"
					@keyup.enter="searchButton"
					clearable
			></v-text-field>
			<v-text-field
					label="FIN"
					placeholder="FIN"
					outlined
					v-model="search.fin"
					@keyup.enter="searchButton"
					clearable
			></v-text-field>
			<v-text-field
					label="Phone"
					placeholder="Phone"
					outlined
					v-model="search.phone"
					clearable
					@keyup.enter="searchButton"
			></v-text-field>
			<v-text-field
					label="Email"
					placeholder="Email"
					outlined
					v-model="search.email"
					clearable
					@keyup.enter="searchButton"
			></v-text-field>
			<div class="buttons">
				<v-btn
						@click="searchButton"
						color="#2E7D32"
				>Search
				</v-btn>
				<v-btn
						@click="clearButton"
						color="#ec1b1b"
				>Clear
				</v-btn>
			</div>
		</div>
		<div class="bottom">
			<div class="left">
				<div class="accountButton">
					<!-- <v-btn
							small
							@click="clientAccount"
							color="#2b74e1"
							:disabled="!client.id"
					>Go to Client`s Account
					</v-btn>
					<v-btn
							v-show="hasReferral"
							small
							color="#ec1b1b"
							:disabled="!client.id"
							target="_blank"
							:href="`${myReferral+'/'+client.id}`"
					>Show referral`s packages
					</v-btn> -->
					<v-btn
							small
							color="green"
							v-if="client.email_verified_at"
					>{{client.email_verified_at}}
					</v-btn>
					<template v-if="!client.email_verified_at && client.id">
						<v-row justify="center">
							<v-dialog
									v-model="dialog"
									persistent
									max-width="290"
							>
								<template v-slot:activator="{ on }">
									<v-btn
											color="green"
											dark
											v-on="on"
									>Confirm account
									</v-btn>
								</template>
								<v-card>
									<v-card-title class="headline">Are you sure?</v-card-title>
									<v-card-text>Confirm account</v-card-text>
									<v-card-actions>
										<v-spacer></v-spacer>
										<v-btn
												color="green darken-1"
												text
												@click="dialog = false"
										>Disagree
										</v-btn>
										<v-btn
												color="green darken-1"
												text
												@click="confirmAccount"
										>Agree
										</v-btn>
									</v-card-actions>
								</v-card>
							</v-dialog>
						</v-row>
					</template>
					<!--<template>
						<v-row justify="center">
							<v-dialog v-model="dialog" persistent max-width="600px">
								<template v-slot:activator="{ on }">
									<v-btn color="green" :disabled="!client.id" v-on="on">Change Password</v-btn>
								</template>
								<v-card>
									<v-card-title>
										<span class="headline">Change Password</span>
									</v-card-title>
									<v-card-text>
										<v-container>
											<v-row>
												&lt;!&ndash;<v-col cols="12" sm="6" md="4">
													<v-text-field label="Legal first name*" name="client_id" required></v-text-field>
												</v-col>&ndash;&gt;
												<v-col cols="12" sm="6" md="4">
													<v-text-field label="New Password"
																				hint="Enter the new password"></v-text-field>
												</v-col>
											</v-row>
										</v-container>
										<small>*indicates required field</small>
									</v-card-text>
									<v-card-actions>
										<v-spacer></v-spacer>
										<v-btn color="blue darken-1" text @click="dialog = false">Close</v-btn>
										<v-btn color="blue darken-1" text @click="dialog = false">Save</v-btn>
									</v-card-actions>
								</v-card>
							</v-dialog>
						</v-row>
					</template>-->
				</div>
				<v-text-field
						disabled
						label="Suite"
						placeholder="Suite"
						outlined
						:value="((client.suite+client.id)|| '')"
				></v-text-field>
				<v-text-field
						disabled
						label="Name"
						placeholder="Name"
						outlined
						v-model="client.name"
				></v-text-field>
				<v-text-field
						disabled
						label="Surname"
						placeholder="Surname"
						outlined
						v-model="client.surname"
				></v-text-field>
				<v-text-field
						disabled
						label="Balance"
						placeholder="Balance"
						outlined
						v-model="client.balance"
				></v-text-field>
				<v-text-field
						disabled
						label="Common Debt"
						placeholder="Common Debt"
						outlined
						v-model="client.common_debt"
				></v-text-field>
				<v-text-field
						disabled
						label="Cargo Debt"
						placeholder="Cargo Debt"
						outlined
						v-model="client.cargo_debt"
				></v-text-field>
				<v-text-field
						disabled
						label="Passport Number"
						placeholder="Passport Number"
						outlined
						v-model="client.passport_number"
				></v-text-field>
				<v-text-field
						disabled
						label="Passport Fin"
						placeholder="Passport Fin"
						outlined
						v-model="client.passport_fin"
				></v-text-field>
				<v-text-field
						disabled
						label="Email"
						placeholder="Email"
						outlined
						v-model="client.email"
				></v-text-field>
				<v-text-field
						disabled
						label="Address"
						placeholder="Address"
						outlined
						v-model="client.address1"
				></v-text-field>
				<v-text-field
						disabled
						label="Phone"
						placeholder="Phone"
						outlined
						v-model="client.phone1"
				></v-text-field>
				<v-text-field
						disabled
						label="Birthday"
						placeholder="Birthday"
						outlined
						v-model="client.birthday"
				></v-text-field>
				<v-text-field
						disabled
						label="Language"
						placeholder="Language"
						outlined
						v-model="client.language"
				></v-text-field>
			</div>
			<div class="right">
				<template v-if="!client.id">
					<v-data-table
							:headers="headers"
							:items="clients"
							item-key="id"
							disable-pagination
							hide-default-footer
							:loading="isLoading"
							loading-text="Loading... Please wait"
							class="elevation-1"
					>
						<template v-slot:item="{ item }">
							<tr
									@dblclick="setClient(item)"
									class="clients"
							>
								<td class="text-xs-left">{{ item.suite + item.id}}</td>
								<!--                                <td class="text-xs-left">{{ item.suite }}</td>-->
								<td class="text-xs-left">{{ item.name }}</td>
								<td class="text-xs-left">{{ item.surname }}</td>
								<td class="text-xs-left">{{ item.passport }}</td>
								<td class="text-xs-left">{{ item.passport_fin }}</td>
								<td class="text-xs-left">{{ item.email }}</td>
								<td class="text-xs-left">{{ item.address1 }}</td>
								<td class="text-xs-left">{{ item.phone1 }}</td>
								<td class="text-xs-left">{{ item.phone2 }}</td>
								<td class="text-xs-left">{{ item.common_debt }}</td>
								<td class="text-xs-left">{{ item.cargo_debt }}</td>
								<td class="text-xs-left">{{ item.balance }}</td>
								<td class="text-xs-left">{{ item.birthday }}</td>
								<td class="text-xs-left">{{ item.language }}</td>
							</tr>
						</template>
					</v-data-table>
				</template>
				<template v-if="client.id">
					<VBtnToggle
							active-class="active_status"
							mandatory
							v-model="search.delivered"
							@change="selectUser(client)"
							class="statuses"
					>
						<v-btn
								color="#2196f3"
								value="2"
						>Not Delivered
						</v-btn>
						<v-btn
								color="#2196f3"
								value="1"
						>Delivered
						</v-btn>
						<v-btn
								color="#2196f3"
								value="0"
						>All
						</v-btn>
					</VBtnToggle>
					<v-data-table
							:headers="headersPackages"
							:items="packages"
							item-key="id"
							disable-pagination
							hide-default-footer
							:loading="isLoading"
							loading-text="Loading... Please wait"
							class="elevation-1"
					>
						<template v-slot:item="{ item }">
							<tr class="packages" :class="item.paid_status?'green':'pink'">
								<td class="text-xs-left">{{ item.number }}</td>
								<td class="text-xs-left">{{ item.internal_id }}</td>
								<td class="text-xs-left">{{ item.flight }}</td>
								<td class="text-xs-left">{{ item.invoice + ' ' + item.invoice_currency }}</td>
								<td class="text-xs-left invoice_width">
									<v-row>
										<v-col cols="8">
											<a
													v-if="item.invoice_doc"
													target="_blank"
													:href=" 'https://asercargo.az/' +item.invoice_doc"
											>Invoice</a>
											<span v-if="!item.invoice_doc">--_--</span>
										</v-col>
										<!-- <v-col cols="4" v-if="item.invoice_doc">
											<el-popconfirm
													confirmButtonText='Yes'
													cancelButtonText='No, Thanks'
													icon="el-icon-info"
													iconColor="red"
													title="Are you sure to delete?"
													@onConfirm="deleteInvoiceDoc(item.id)"
											>
												<v-icon slot="reference" class="icon_cursor" small>mdi-delete</v-icon>
											</el-popconfirm>
										</v-col> -->

										<v-col cols="4" v-if="item.invoice_doc">
											<button @click="deleteInvoiceDoc(item.id)"><v-icon slot="reference" class="icon_cursor" small>mdi-delete</v-icon></button>
										</v-col>
									</v-row>
								</td>
								<td class="text-xs-left">
									<v-btn :loading="loading" color="primary" @click="getInvoiceStatus(item.id)">Invoice Log</v-btn>
								</td>
								<td class="text-xs-left">
									<v-btn :loading="loading" color="primary" @click="getStatus(item.id)">{{item.status || '-_-'}}</v-btn>
								</td>
								<td
										class="text-xs-left"
										:class="{'green':item.chargeable_weight === 2 }"
								>
                    <span
		                    :data-tooltip="'Gross weight - '+ item.gross_weight + '/ Volume weight - ' + item.volume_weight +'\n Length - ' + item.length + ' / Width - ' + item.width + ' / Height - ' + item.height"
		                    data-tooltip-position="top"
                    >{{item.chargeable_weight===1?item.gross_weight:item.volume_weight}}</span>
								</td>
								<!--								<td-->
								<!--										class="text-xs-left"-->
								<!--										:class="{'green':item.chargeable_weight === 1 }"-->
								<!--								>{{-->
								<!--								 item.gross_weight-->
								<!--								 }}-->
								<!--								</td>-->
								<td class="text-xs-left">{{ item.amount }}</td>
								<td class="text-xs-left">{{ item.currency }}</td>
								<td class="text-xs-left">{{ item.paid }}</td>
								<!--								<td class="text-xs-left">{{ item.paid_status }}</td>-->
								
								
								<td class="text-xs-left">{{ item.position }}</td>
								<td class="text-xs-left">{{ item.location }}</td>
								
								<td class="text-xs-left">{{ item.seller }}</td>
								<td class="text-xs-left">{{ item.departure }}</td>
								<td class="text-xs-left">{{ item.destination }}</td>
								<td class="text-xs-left">
									<span
											:data-tooltip="'Receipt Date - \n' + item.payment_receipt_date"
											data-tooltip-position="top"
									>{{ item.payment_receipt }}</span>
								</td>
								<!--								<td class="text-xs-left">{{ item.payment_receipt_date }}</td>-->
							</tr>
						</template>
					</v-data-table>
				</template>
			</div>
			<v-row justify="center">
				<v-dialog v-model="dialog2" persistent max-width="400px">
					<v-card>
						<v-card-title>
							<span class="headline">Status</span>
						</v-card-title>
						<v-card-text>
							<v-container>
								<v-row>
									<v-simple-table>
										<template v-slot:default>
											<thead>
											<tr>
												<th class="text-left">#</th>
												<th class="text-left">Status</th>
												<th class="text-left">Tarix</th>
												<th class="text-left">User</th>
											</tr>
											</thead>
											<tbody>
											<tr v-for="item in statuses" :key="item.name">
												<td>{{ item.no }}</td>
												<td>{{ item.status }}</td>
												<td>{{ item.date }}</td>
												<td>{{ item.user_name + ' ' + item.user_surname }}</td>
											</tr>
											</tbody>
										</template>
									</v-simple-table>
								</v-row>
							</v-container>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn color="blue darken-1" text @click="dialog2 = false">Close
							</v-btn>
							<!--													<v-btn color="blue darken-1" :disabled="!valueToBalance || !currency" text @click="updateBalance">Update</v-btn>-->
						</v-card-actions>
					</v-card>
				</v-dialog>
			</v-row>

			<v-row justify="center">
				<v-dialog v-model="dialog_invoice" persistent max-width="400px">
					<v-card>
						<v-card-title>
							<span class="headline">Status</span>
						</v-card-title>
						<v-card-text>
							<v-container>
								<v-row>
									<v-simple-table>
										<template v-slot:default>
											<thead>
											<tr>
												<th class="text-left">#</th>
												<th class="text-left">Status</th>
												<th class="text-left">Tarix</th>
												<th class="text-left">User</th>
											</tr>
											</thead>
											<tbody>
											<tr v-for="item in statuses" :key="item.name">
												<td>{{ item.no }}</td>
												<td>{{ item.inv_status }}</td>
												<td>{{ item.date }}</td>
												<td>{{ item.user_name + ' ' + item.user_surname }}</td>
											</tr>
											</tbody>
										</template>
									</v-simple-table>
								</v-row>
							</v-container>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn color="blue darken-1" text @click="dialog_invoice = false">Close
							</v-btn>
							<!--													<v-btn color="blue darken-1" :disabled="!valueToBalance || !currency" text @click="updateBalance">Update</v-btn>-->
						</v-card-actions>
					</v-card>
				</v-dialog>
			</v-row>
		</div>
	</div>
</template>

<script>
  import Swal from 'sweetalert2'

  export default {
    name    : 'operator',
    props   : {
      myRoute              : {
        type: String
      },
      getStatusRoute       : {
        type: String
      },
	   getInvoiceStatusRoute       : {
        type: String
      },
      deleteInvoiceDocRoute: {
        type: String
      },
      admin                : {
        type: String
      },
      getPackages          : {
        type: String
      },
      myReferral           : {
        type: String
      },
      myClientAccount      : {
        type: String
      },
      myConfirmAccount     : {
        type: String
      }
    },
    data () {
      return {
        scan             : '',
        loading          : false,
        track_no         : '',
        selectedPackage  : [],
        headers          : [
          // {
          //     text: 'Id',
          //     align: 'left',
          //     sortable: false,
          //     value: 'id'
          // },
          { text: 'Suite', align: 'left', value: 'suite' },
          { text: 'Name', align: 'left', value: 'name' },
          { text: 'Surname', align: 'left', value: 'surname' },
          { text: 'Passport', align: 'left', value: 'passport' },
          { text: 'FIN', align: 'left', value: 'passport_fin' },
          { text: 'Email', align: 'left', value: 'email' },
          { text: 'Address1', align: 'left', value: 'address1' },
          { text: 'Phone1', align: 'left', value: 'phone1' },
          { text: 'Phone2', align: 'left', value: 'phone2' },
          { text: 'Common Debt', align: 'left', value: 'common_debt' },
          { text: 'Cargo debt', align: 'left', value: 'cargo_debt' },
          { text: 'Balance', align: 'left', value: 'balance' },
          { text: 'Birthday', align: 'left', value: 'birthday' },
          { text: 'Language', align: 'left', value: 'language' }
        ],
        headersPackages  : [
          { text: 'Track No', align: 'left', sortable: false, value: 'number' },
          { text: 'Internal Id', align: 'left', value: 'internal_id' },
          { text: 'Flight', align: 'left', value: 'flight' },
          { text: 'Invoice', align: 'left', value: 'invoice' },
          { text: 'Invoice Doc', align: 'left', value: 'invoice_doc' },
		  { text: 'Invoice Status', align: 'left', value: 'invoice_status' },
		  { text: 'Status', align: 'left', value: 'status' },
          { text: 'Weight', align: 'left', value: 'weight' },
          // { text: 'Gross Weight', align: 'left', value: 'gross_weight' },
          { text: 'Amount', align: 'left', value: 'amount' },
          { text: 'Currency', align: 'left', value: 'currency' },
          { text: 'Paid', align: 'left', value: 'paid' },
          // { text: 'Paid Status', align: 'left', value: 'paid_status' },
    
          { text: 'Position', align: 'left', value: 'position' },
          { text: 'Location', align: 'left', value: 'location' },
          { text: 'Seller', align: 'left', value: 'seller' },
          { text: 'Departure', align: 'left', value: 'departure' },
          { text: 'Destination', align: 'left', value: 'destination' },
          { text: 'Payment Receipt', align: 'left', value: 'payment_receipt' },
          // {
          //   text : 'Payment Receipt Date',
          //   align: 'left',
          //   value: 'payment_receipt_date'
          // },
        ],
        packages         : [],
        isLoading        : false,
        deliveredPackages: [],
        search           : {
          suite    : '',
          name     : '',
          surname  : '',
          passport : '',
          fin      : '',
          phone    : '',
          email    : '',
          delivered: 2
        },
        client           : [],
        clients          : [],
        dialog           : false,
        dialog2          : false,
		dialog_invoice          : false,
        hasReferral      : false,
        statuses         : [],
      }
    },
    methods : {
      searchButton () {
        const _this    = this
        const formData = new FormData()
        this.client    = []
        this.isLoading = true
        Object.keys(_this.search)
              .map(function (key) {
                return formData.append(key, _this.search[key] || '')
              })
        axios
          .post(this.myRoute, formData)
          .then(function (resp) {
            if (resp.data.case === 'success') {
              _this.clients = resp.data.clients
            } else {
              _this.client   = []
              _this.packages = []
              _this.clients  = []
              _this.search   = []
              toastr.error(resp.data.content)
            }
          })
          .catch(function (resp) {
            Swal.fire({
              type : 'error',
              title: 'Oops...',
              text : 'Something went wrong while getting data!'
            })
          })
          .finally(() => {
            this.isLoading = false
          })
      },
      selectUser (item) {
        const _this     = this
        _this.isLoading = true
        const formData  = new FormData()
        formData.append('client', item.id)
        formData.append('delivered', _this.search.delivered ?? 2)

        axios
          .post(this.getPackages, formData)
          .then(function (resp) {
            if (resp.data.case === 'success') {
              _this.packages    = resp.data.packages
              _this.hasReferral = resp.data.has_referral
            } else {
              toastr.error(resp.data.content)
            }
          })
          .catch(function (resp) {
            alert('Error scanner')
          })
          .finally(() => {
            _this.isLoading = false
          })
      },
      setClient (item) {
        this.clearButton()
        this.client = item
      },
      clearButton () {
        this.client      = []
        this.packages    = []
        this.clients     = []
        this.search      = []
        this.hasReferral = false
      },
      clientAccount () {
        axios
          .get(this.myClientAccount + '/' + this.client.id)
          .then(resp => {
            if (resp.data.case === 'success') {
              window.open(resp.data.url)
            } else {
              toastr.error(resp.data.content)
            }
          })
          .catch(resp => {
            toastr.error(resp.data.content)
          })
      },
      confirmAccount () {
        const _this = this
        axios
          .post(this.myConfirmAccount, { client: this.client.id })
          .then(function (resp) {
            if (resp.data.case === 'success') {
              _this.client.email_verified_at = new Date().toLocaleString()
            } else {
              alert('Something went wrong while getting data!')
            }
          })
          .catch(function (resp) {
            alert('Something went wrong while getting data!')
          })
          .finally(() => {
            this.dialog = false
          })
      },
      getStatus (id) {
        this.loading = true
        axios.post(this.getStatusRoute, { package_id: id })
             .then((resp) => {
               if (resp.data.case === 'success') {
                 this.statuses = resp.data.events
                 this.dialog2  = true
               } else {
                 Swal.fire({
                   type : resp.data.case,
                   title: resp.data.title,
                   text : resp.data.content,
                 })
               }
             })
             .catch((resp) => {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : resp.data.content,
               })
             })
             .finally(() => {
               this.loading = false
             })
      },
	    getInvoiceStatus (id) {
        this.loading = true
        axios.post(this.getInvoiceStatusRoute, { package_id: id })
             .then((resp) => {
               if (resp.data.case === 'success') {
                 this.statuses = resp.data.events
                 this.dialog_invoice  = true
               } else {
                 Swal.fire({
                   type : resp.data.case,
                   title: resp.data.title,
                   text : resp.data.content,
                 })
               }
             })
             .catch((resp) => {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : resp.data.content,
               })
             })
             .finally(() => {
               this.loading = false
             })
      },
      deleteInvoiceDoc (id) {
        axios.delete(this.deleteInvoiceDocRoute, { data: { package_id: id } })
             .then((resp) => {
               this.selectUser(this.client)
             })
             .catch((e) => {

             })
      }
    },
    computed: {},
    mounted () {
      toastr.options = {
        closeButton      : false,
        debug            : false,
        newestOnTop      : false,
        progressBar      : false,
        positionClass    : 'toast-bottom-right',
        preventDuplicates: false,
        onclick          : null,
        showDuration     : '300',
        hideDuration     : '1000',
        timeOut          : '5000',
        extendedTimeOut  : '1000',
        showEasing       : 'swing',
        hideEasing       : 'linear',
        showMethod       : 'fadeIn',
        hideMethod       : 'fadeOut'
      }
    }
  }
</script>
