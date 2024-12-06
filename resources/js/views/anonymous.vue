<template>
  <v-app class="wrap">
    <v-data-table :headers="headers" :items="desserts" disable-sort disable-filtering dark class="elevation-1" loading-text="Loading... Please wait" :loading="isLoading" hide-default-footer disable-pagination style="background: #10458c">
      <template v-slot:top>
        <v-toolbar flat style="background: #10458c">
          <!-- <div class="flex-grow-1"></div> -->
          <v-text-field @keyup.enter="searchSS" v-model="search.code" label="Number" single-line hide-details clearable></v-text-field>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-text-field @keyup.enter="searchSS" v-model="search.internal_id" label="Internal ID" single-line hide-details clearable></v-text-field>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-text-field @keyup.enter="searchSS" v-model="search.name_surname" label="Client" single-line hide-details clearable></v-text-field>

          <v-dialog v-model="dialog">
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" v-if="error">
                      <v-alert type="error">Please fill correctly all inputs !</v-alert>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <v-text-field disabled v-validate="''" :error-messages="errors.collect('number')" data-vv-name="number" :value="package.number" label="Number"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <v-text-field disabled v-validate="''" :error-messages="errors.collect('client_name_surname')" data-vv-name="client_name_surname" :value="package.client_name_surname" label="Client"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="4">
                      <v-text-field required v-validate="'required'" :error-messages="errors.collect('checkId')" data-vv-name="checkId" v-model="checkId" label="Client Id"></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <p>{{ client }}</p>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <div class="flex-grow-1"></div>
                <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                <v-btn color="blue darken-1" text @click="check">Check</v-btn>
                <v-btn color="blue darken-1" text @click="save">Assigne</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.amountCurrency="{ item }">
        <td>{{ (item.amount || '') + ' ' + (item.amount_currency || '') }}</td>
      </template>
      <template v-slot:item.invoiceCurrency="{ item }">
        <td>
          {{ (item.invoice || '') + ' ' + (item.invoice_currency || '') }}
        </td>
      </template>
      <template v-slot:item.action="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)">edit</v-icon>
      </template>
      <template v-slot:no-data>
        <h2>There is nothing!</h2>
        <v-btn color="primary" @click="initialize">Reset</v-btn>
      </template>
    </v-data-table>
    <template>
      <div class="text-center">
        <v-pagination v-model="pagination.current" :length="pagination.total" @input="onPageChange" :circle="circle" :next-icon="nextIcon" :prev-icon="prevIcon" :total-visible="totalVisible"></v-pagination>
      </div>
    </template>
  </v-app>
</template>

<script>
import Swal from 'sweetalert2'
import debounce from 'debounce'

export default {
  $_veeValidate: {
    validator: 'new'
  },
  props: {
    myPackage: {
      type: String
    },
    myClient: {
      type: String
    },
    myMerge: {
      type: String
    }
  },
  data: () => ({
    pagination: {
      current: 1,
      total:   0
    },
    circle:       true,
    nextIcon:     'navigate_next',
    prevIcon:     'navigate_before',
    totalVisible: 5,
    error:        false,
    isLoading:    true,
    like:         '',
    search:       {
      name_surname: '',
      internal_id:  '',
      code:         '',
      status:       ''
    },
    dialog:  false,
    headers: [
      {
        text:  'Number',
        align: 'left',
        value: 'number'
      },
      { text: 'Internal Id', value: 'internal_id' },
      { text: 'Client', value: 'client_name_surname' },
      { text: 'Gross Weight', value: 'gross_weight' },
      { text: 'Amount', value: 'amountCurrency' },
      { text: 'Invoice', value: 'invoiceCurrency' },
      { text: 'Seller', value: 'seller' },
      { text: 'Category', value: 'category' },
      { text: 'Status', value: 'status' },
      { text: 'Created date', value: 'created_at' },
      { text: 'Actions', value: 'action', sortable: false }
    ],
    desserts: [],
    checkId:  '',
    package:  '',
    client:   ''
  }),

  computed: {
    formTitle () {
      return 'Assigne to User'
    },
    disableClient () {
      return this.editedItem.disable ? 'Enabled' : 'Disabled'
    }
  },

  watch: {
    dialog (val) {
      val || this.close()
    }
  },

  created () {
    this.initialize()
    this.searchSS = debounce(this.searchSS, 1000)
  },

  methods: {
    initialize () {
      const app = this
      app.isLoading = true
      axios
        .get(
          this.myPackage +
            '?code=' +
            this.search.code +
            '&internal_id=' +
            this.search.internal_id +
            '&client=' +
            this.search.name_surname +
            '&page=' +
            this.pagination.current
        )
        .then(resp => {
          if (resp.data.case === 'success') {
            app.desserts = resp.data.orders.data
            app.pagination.current = resp.data.orders.current_page
            app.pagination.total = resp.data.orders.last_page
          }
        })
        .catch(resp => {
          Swal.fire({
            type:  'error',
            title: 'Oops...',
            text:  'Something went wrong!'
          })
        })
        .finally(() => {
          app.isLoading = false
        })
    },

    initializePage () {
      const app = this
      axios
        .get(
          '/api/admin/showWork?search=' +
            this.like +
            '&page=' +
            this.pagination.current
        )
        .then(resp => {
          app.pagination.current = resp.data.current_page
          app.pagination.total = resp.data.last_page
        })
        .catch(resp => {
          Swal.fire({
            type:  'error',
            title: 'Oops...',
            text:  'Something went wrong!'
          })
        })
    },

    onPageChange () {
      this.initialize()
    },

    editItem (item) {
      const app = this
      this.package = item
      this.dialog = true
      this.client = ''
      this.checkId = ''
    },

    close () {
      this.dialog = false
      this.error = false
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      }, 300)
    },

    save () {
      const app = this
      this.$validator.validateAll().then(responses => {
        if (responses) {
          const app = this
          const newWork = app.editedItem
          const formData = new FormData()
          formData.append('package_id', app.package.id)
          formData.append('client_id', app.checkId)

          axios
            .post(this.myMerge, formData)
            .then(resp => {
              if (resp.data.case === 'success') {
                Swal.fire({
                  type:  'success',
                  title: 'Merged'
                })
                app.initialize()
                app.close()
              }
            })
            .catch(resp => {
              Swal.fire({
                type:  'error',
                title: 'Oops...',
                text:  'Something went wrong!'
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

    check () {
      const _this = this
      _this.client = ''
      const formData = new FormData()
      formData.append('client_id', this.checkId)
      axios
        .post(this.myClient, formData)
        .then(resp => {
          if (resp.data.case === 'success') {
            _this.client = resp.data.client
          }
        })
        .catch(() => { })
    }
  }
}
</script>
