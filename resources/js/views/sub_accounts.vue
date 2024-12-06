<template>
	<v-app>
		<div class="wrap">
			<div class="top">
				<div class="buttons">
					<v-btn @click="check=false" color="#2E7D32">Show referrals</v-btn>
					<v-btn @click="check=true" color="#ec1b1b">Show referrals` packages</v-btn>
				</div>
			</div>
			<div class="bottom">
				<template v-if="!check">
					<v-data-table
							:headers="headers"
							:items="clients"
							item-key="id"
							disable-pagination
							hide-default-footer
							class="elevation-1"
					>
						<template v-slot:item.client_suite="{ item }">
							<td class="text-xs-left">{{ item.suite + item.id}}</td>
						</template>
					</v-data-table>
				</template>
				<template v-if="check">
					<v-data-table
							:headers="headersPackages"
							:items="packages"
							item-key="id"
							disable-pagination
							hide-default-footer
							class="elevation-1"
					>
						<template v-slot:item="{ item }">
							<tr class="packages" :class="item.paid_status?'pink':'green'">
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
										<v-col cols="4" v-if="item.invoice_doc">
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
										</v-col>
									</v-row>
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
								
								<td class="text-xs-left">
									<v-btn :loading="loading" v-model="dialog2" color="primary" @click="getStatus(item.id)">{{item.status || '-_-'}}</v-btn>
								</td>
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
	</v-app>
</template>

<script>
  import Swal from 'sweetalert2'

  export default {
    name   : 'operator',
    props  : {
      myRoute              : {
        type: String
      },
      admin                : {
        type: String
      },
      getStatusRoute       : {
        type: String
      },
      deleteInvoiceDocRoute: {
        type: String
      },
      mySub_accounts       : {
        type: Array
      },
      sub_accounts_packages: {
        type: Array
      }
    },
    data () {
      return {
        check            : false,
        scan             : '',
        track_no         : '',
        selectedPackage  : [],
        headers          : [
          // {
          //     text: 'Id',
          //     align: 'left',
          //     sortable: false,
          //     value: 'id'
          // },
          { text: 'Suite', align: 'left', value: 'client_suite' },
          { text: 'Name', align: 'left', value: 'name' },
          { text: 'Surname', align: 'left', value: 'surname' },
          { text: 'Passport', align: 'left', value: 'passport' },
          { text: 'Email', align: 'left', value: 'email' },
          { text: 'Address1', align: 'left', value: 'address1' },
          { text: 'Phone1', align: 'left', value: 'phone1' },
          { text: 'Phone2', align: 'left', value: 'phone2' },
          { text: 'Phone3', align: 'left', value: 'phone3' },
          { text: 'Birthday', align: 'left', value: 'birthday' },
          { text: 'Language', align: 'left', value: 'language' }
        ],
        headersPackages  : [
          { text: 'Track No', align: 'left', sortable: false, value: 'number' },
          { text: 'Internal Id', align: 'left', value: 'internal_id' },
          { text: 'Flight', align: 'left', value: 'flight' },
          { text: 'Invoice', align: 'left', value: 'invoice' },
          { text: 'Invoice Doc', align: 'left', value: 'invoice_doc' },
          { text: 'Weight', align: 'left', value: 'weight' },
          // { text: 'Gross Weight', align: 'left', value: 'gross_weight' },
          { text: 'Amount', align: 'left', value: 'amount' },
          { text: 'Currency', align: 'left', value: 'currency' },
          { text: 'Paid', align: 'left', value: 'paid' },
          // { text: 'Paid Status', align: 'left', value: 'paid_status' },
          { text: 'Status', align: 'left', value: 'status' },
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
        deliveredPackages: [],
        search           : {
          suite   : '',
          name    : '',
          surname : '',
          passport: '',
          phone   : '',
          email   : ''
        },
        user             : [],
        clients          : [],
        client           : [],
        dialog2          : false,
        statuses         : [],
        loading          : false
      }
    },
    methods: {
      searchButton () {
        let self     = this
        let formData = new FormData
        this.user    = []
        Object.keys(self.search)
              .map(function (key) {
                return (formData.append(key, self.search[key] || ''))
              })
        axios.post(this.myRoute, formData)
             .then(function (resp) {
               if (resp.data.case === 'success') {
                 self.clients = resp.data.clients

               } else {
                 self.user     = []
                 self.packages = []
                 self.clients  = []
                 self.search   = []
                 toastr.error(resp.data.content)
               }
             })
             .catch(function (resp) {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : 'Something went wrong!',
               })
             })
      },
      selectUser (item) {
        let self     = this
        let formData = new FormData
        self.client  = item
        formData.append('client', item.id)

        axios.post(this.getPackages, formData)
             .then(function (resp) {
               if (resp.data.case === 'success') {
                 self.user     = item
                 self.packages = resp.data.packages
               } else {
                 toastr.error(resp.data.content)
               }
             })
             .catch(function (resp) {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : 'Something went wrong!',
               })
             })
      },
      clearButton () {
        this.user     = []
        this.packages = []
        this.clients  = []
        this.search   = []
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
      deleteInvoiceDoc (id) {
        axios.delete(this.deleteInvoiceDocRoute, { data: { package_id: id } })
             .then((resp) => {
               this.selectUser(this.client)
             })
             .catch((e) => {

             })
      }
    },
    mounted () {
      this.packages = this.sub_accounts_packages
      this.clients  = this.mySub_accounts
    }
  }
</script>
