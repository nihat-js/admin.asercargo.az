<template>
	<v-app class="wrap">
		<v-data-table
				:headers="headers"
				:items="desserts"
				disable-sort
				disable-filtering
				dark
				class="elevation-1"
				loading-text="Loading... Please wait"
				:loading=isLoading
				hide-default-footer
				disable-pagination
		>
			<template v-slot:top>
				<v-toolbar flat>
					<!-- <div class="flex-grow-1"></div> -->
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.number"
							label="Number"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.name"
							label="Name"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.surname"
							label="Surname"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-text-field
							@keyup.enter="searchSS"
							v-model="search.suite"
							label="Suite"
							single-line
							hide-details
							clearable
					></v-text-field>
					<v-select
							:items="mySeller"
							item-value="id"
							item-text="name"
							v-model="search.seller"
							@change="searchSS"
							label="Seller"
							outlined
							clearable
					></v-select>
					<v-select
							:items="myStatus"
							item-value="id"
							item-text="status"
							v-model="search.status"
							@change="searchSS"
							label="Status"
							outlined
							clearable
					></v-select>
					<v-select
							:items="currentLocation"
							item-value="id"
							item-text="name"
							v-model="search.location"
							@change="searchSS"
							label="Current location"
							outlined
							clearable
					></v-select>
					<v-select
							:items="myLocation"
							item-value="id"
							item-text="name"
							v-model="search.departure"
							@change="searchSS"
							label="Departure"
							outlined
							clearable
					></v-select>
<!--					<v-select
							:items="myLocation"
							item-value="id"
							item-text="name"
							v-model="search.destination"
							label="Destination"
							outlined
					></v-select>-->
					<v-select
							:items="invoiceStatus"
							item-value="id"
							item-text="name"
							v-model="search.status"
							@change="searchSS"
							label="Invoice status"
							outlined
							clearable
					></v-select>

				</v-toolbar>

			</template>
			<template v-slot:item.location_position="{ item }">
				<td>{{ item.position !==null ?(item.location + ' / ' + item.position):((
				    item.container?('CONTAINER'+item.container):'---')) }}
				</td>
			</template>
			<template v-slot:item.invoiceCurrency="{ item }">
				<td>{{(item.price || '') + ' ' + (item.invoice_currency || '')}}</td>
			</template>
			<template v-slot:item.grossWeight="{ item }">
				<td>{{(item.gross_weight || '') + ' ' + (item.unit || '')}}</td>
			</template>
			<template v-slot:item.mustPaid="{ item }">
				<td>{{(item.total_charge_value || '') + ' ' + (item.currency || '')}}</td>
			</template>
			<template v-slot:item.client="{ item }">
				<td>{{(item.client_name || '') + ' ' + (item.client_surname || '')}}</td>
			</template>
			<template v-slot:item.invoiceDocument="{ item }">
				<td v-if="item.invoice_doc"><a target="_blank" :href="'https://asercargo.az'+item.invoice_doc">Invoice</a>
				</td>
				<td v-else>---</td>
			</template>
      <template v-slot:item.package_img="{ item }">
        <td v-if="item.package_img"><a target="_blank" :href="item.package_img">Image</a>
        </td>
        <td v-else>---</td>
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
    props: {
      'myPackage': {
        type: String,
      },
      'myLocation': {
        type: Array,
      },
      'myStatus': {
        type: Array,
      },
      'mySeller': {
        type: Array,
      }
    },
    data: () => ({
      pagination: {
        current: 1,
        total: 0,
      },
      circle: true,
      nextIcon: 'navigate_next',
      prevIcon: 'navigate_before',
      totalVisible: 5,
      error: false,
      isLoading: true,
      like: '',
      search: {
        number: '',
        suite: '',
        name: '',
        surname: '',
        seller: '',
        status: '',
        location: '',
        departure: '',
        destination: '',
        invoice_status: '',
      },
      dialog: false,
      headers: [
        { text: 'Truck number', align: 'left', value: 'number', },
        { text: 'Status', value: 'status' },
        { text: 'Location/position', value: 'location_position' },
        { text: 'Departure', value: 'departure' },
        { text: 'Destination', value: 'destination' },
        { text: 'Invoice', value: 'invoiceCurrency' },
        { text: 'Document', value: 'invoiceDocument' },
        { text: 'Package image', value: 'package_img' },
        { text: 'Height', value: 'height' },
        { text: 'Length', value: 'length' },
        { text: 'Gross weight', value: 'grossWeight' },
        { text: 'To be paid', value: 'mustPaid' },
        { text: 'Suite', value: 'suite' },
        { text: 'Client', value: 'client' },
        { text: 'Seller', value: 'seller' },
        { text: 'Created date', value: 'created_at' }
      ],
      desserts: [],
      checkId: '',
      package: '',
      client: '',
      currentLocation: [],
      invoiceStatus:[]
    }),

    computed: {
      formTitle () {
        return 'Merge Order'
      },
      disableClient () {
        return this.editedItem.disable ? 'Enabled' : 'Disabled'
      },
    },

    watch: {
      dialog (val) {
        val || this.close()
      },
    },

    created () {
      this.initialize()
      this.currentLocation.push(...this.myLocation)
      this.currentLocation.unshift({id:'container',name:'in container'})
      this.invoiceStatus.push({id:'no_invoice',name:'No invoice'},{id:'not_confirmed',name:'Not confirmed'},{id:'confirmed',name:'Confirmed'})
    },

    methods: {
      initialize () {
        let app       = this
        app.isLoading = true
        axios.get(
          this.myPackage + '?number=' + this.search.number + '&location=' + (this.search.location??('')) + '&suite=' + this.search.suite + '&name=' + this.search.name + '&surname=' + this.search.surname + '&seller=' + (this.search.seller??('')) + '&status=' + (this.search.status??('')) + '&departure=' + (this.search.departure??('')) + '&destination=' + (this.search.destination??(''))  + '&page=' + this.pagination.current).
          then((resp) => {
            console.log(resp)
            if (resp.data.case === 'success') {
              app.desserts           = resp.data.packages.data
              app.pagination.current = resp.data.packages.current_page
              app.pagination.total   = resp.data.packages.last_page
            }
          }).
          catch((resp) => {
            console.log(resp)
            Swal.fire({
              type: 'error',
              title: 'Oops...',
              text: 'Something went wrong!',
            })
          }).
          finally(() => {
            app.isLoading = false
          })
      },

      initializePage () {
        let app = this
        axios.get('/api/admin/showWork?search=' + this.like + '&page=' + this.pagination.current).then((resp) => {
          app.pagination.current = resp.data.current_page
          app.pagination.total   = resp.data.last_page
        }).catch((resp) => {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!',
          })
        })
      },

      onPageChange () {
        this.initialize()
      },

      searchSS () {
        this.initialize()
      },
    },
  }
</script>

