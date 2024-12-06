<template>
	<v-app>
		<v-card>
			<v-toolbar>
				<v-text-field
								v-model="search.suite"
								label="suite"
								type="number"
								single-line
								hide-details
				></v-text-field>
				<v-divider
								class="mx-4"
								inset
								vertical
				></v-divider>
				<v-text-field
								v-model="search.track"
								label="track"
								single-line
								hide-details
				></v-text-field>
				<v-divider
								class="mx-4"
								inset
								vertical
				></v-divider>
				<v-select
								v-model="search.flight"
								:items="flights"
								item-text="name"
								item-value="id"
								label="Flight"
								hide-details
								clearable
				></v-select>
				<v-divider
								class="mx-4"
								inset
								vertical
				></v-divider>
				<v-btn
								color="blue"
								@click="initialize()"
				>Search
				</v-btn>
				<v-divider
								class="mx-4"
								inset
								vertical
				></v-divider>
				<v-btn
								color="green"
								@click="send()"
				>Change Status
				</v-btn>
			
			</v-toolbar>
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
			></v-data-table>
		</v-card>
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
  import Swal from 'sweetalert2'

  export default {
    props  : {
      'myPackage'   : {
        type: String,
      },
      'notification': {
        type: String,
      },
      'flights'     : {
        type: Array,
      },
    },
    data () {
      return {
        headers     : [
          { text: 'Flight ', value: 'flight' },
          { text: 'Track', value: 'track' },
          { text: 'Internal id ', value: 'internal_id' },
          { text: 'Country', value: 'country' },
          { text: 'Suite', value: 'suite' },
          { text: 'Client name', value: 'client_name' },
          { text: 'Client surname', value: 'client_surname' },
          { text: 'Gross weight', value: 'gross_weight' },
          { text: 'Seller', value: 'seller' },
          { text: 'Category', value: 'category' },
          { text: 'Date', value: 'date' },
        ],
        desserts    : [],
        pagination  : {
          current: 1,
          total  : 0,
        },
        circle      : true,
        nextIcon    : 'navigate_next',
        prevIcon    : 'navigate_before',
        totalVisible: 5,
        isLoading   : true,
        search      : {
          client: '',
          flight: '',
          track : ''
        },
      }
    },
    methods: {
      initialize () {
        let app       = this
        app.isLoading = true
        axios.get(this.myPackage + '?client=' + this.search.client + '&flight=' + this.search.flight + '&track=' + this.search.track + '&page=' + this.pagination.current)
             .then((resp) => {
               console.log(resp)
               if (resp.data.case === 'success') {
                 app.desserts           = resp.data.packages.data
                 app.pagination.current = resp.data.packages.current_page
                 app.pagination.total   = resp.data.packages.last_page
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

      close () {
        this.dialog            = false
        this.error             = false
        this.commonOrderNumber = ''
        setTimeout(() => {
          this.editedItem  = Object.assign({}, this.defaultItem)
          this.editedIndex = -1

        }, 300)
      },

      send () {
        axios.post(this.notification, {
          'flight': this.search.flight,
          'suite' : this.search.suite,
          'track' : this.search.track
        })
             .then((resp) => {
               Swal.fire({
                 type : resp.data.case,
                 title: resp.data.title,
                 text : resp.data.content,
               })
             })
             .catch((resp) => {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : resp.data.content,
               })
             })
      },

      searchSS () {
        this.initialize()
      },
    },
    created () {
      this.initialize()
    },
  }
</script>