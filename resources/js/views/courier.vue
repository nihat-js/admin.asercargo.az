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
                    <v-text-field v-model="search.name" label="Name" single-line hide-details></v-text-field>
                  </v-col>
                  <v-col cols="4">
                    <v-text-field v-model="search.surname" label="Surname" single-line hide-details></v-text-field>
                  </v-col>
                  <v-col cols="4">
                    <v-text-field v-model="search.suite" label="Suite" single-line hide-details></v-text-field>
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

                    <v-col cols="4">
                    <v-select
                      v-model="search.regions"
                      :items="regions"
                      item-text="name"
                      item-value="id"
                      label="Regions"
                      hide-details
                      clearable
                    ></v-select>
                  </v-col>
                  <!-- <v-col cols="4">
                    <v-select
                      v-model="search.courier_payment_type"
                      :items="payment_types"
                      item-text="name"
                      item-value="id"
                      label="Payment Types"
                      hide-details
                      clearable
                    ></v-select>
                  </v-col>
                  <v-col cols="4">
                    <v-select
                      v-model="search.delivery_payment_type"
                      :items="payment_types"
                      item-text="name"
                      item-value="id"
                      label="Payment Types"
                      hide-details
                      clearable
                    ></v-select>
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
                      <v-date-picker v-model="search.date" @input="menu1 = false"></v-date-picker>
                    </v-menu>
                  </v-col>
                  <v-col cols="2">
                    <v-checkbox v-model="old_orders" label="Show old orders"></v-checkbox>
                  </v-col>
                  <v-col cols="2">
                    <v-btn class="ma-3 pa-2" color="blue" @click="initialize()">Search</v-btn>
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
                        '&areas=' +
                        (search.areas || null) +
                        '&regions=' +
                        (search.regions || null) +
                        '&courier_payment_type=' +
                        (search.courier_payment_type || null) +
                        '&delivery_payment_type=' +
                        (search.delivery_payment_type || null) +
                        '&date=' +
                        (search.date || null) +
                        '&type=1'
                      "
                    >Export PDF</v-btn>
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
                        '&areas=' +
                        (search.areas || null) +
                        '&regions=' +
                        (search.regions || null) +
                        '&courier_payment_type=' +
                        (search.courier_payment_type || null) +
                        '&delivery_payment_type=' +
                        (search.delivery_payment_type || null) +
                        '&date=' +
                        (search.date || null) +
                        '&type=2'
                      "
                    >Export Excel</v-btn>
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
                        '&areas=' +
                        (search.areas || null) +
                        '&regions=' +
                        (search.regions || null) +
                        '&courier_payment_type=' +
                        (search.courier_payment_type || null) +
                        '&delivery_payment_type=' +
                        (search.delivery_payment_type || null) +
                        '&date=' +
                        (search.date || null) +
                        '&type=3'
                      "
                    >Export Azerpost</v-btn>
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
                >{{ openSearch ? 'Close Search' : 'Open Search' }}</v-btn>

                <v-btn class="ma-3 pa-2" color="blue" @click="allPackages">All Packages</v-btn>
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
                  <v-icon>mdi-package-variant-closed</v-icon>Set Courier to Selected Orders
                </v-btn>
                <v-btn
                  class="ma-3"
                  :loading="loading"
                  color="blue"
                  :disabled="!selected.length || !printer"
                  @click="printCourierToSelected"
                >
                  <v-icon>mdi-printer</v-icon>Print Selected Orders
                </v-btn>
              </v-row>
            </v-col>
            <v-col cols="12">
              <v-row justify="center">
                <v-btn
                    class="ma-3"
                    color="blue"
                    :disabled="!selected.length"
                    @click="setAzerpostToSelected"
                >
                  <v-icon>mdi-package-variant-closed</v-icon>Set Azerpost to Selected Orders
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
          disable-sort
          show-select
          v-model="selected"
          disable-filtering
          class="elevation-1"
          loading-text="Loading... Please wait"
          :loading="isLoading"
          hide-default-footer
          disable-pagination
        >
          <template v-slot:item="{ isSelected, select, item }">
            <tr :class="item.urgent ? 'urgent' : ''">
              <td>
                <v-simple-checkbox :value="isSelected" @input="select($event)"></v-simple-checkbox>
              </td>
              <td>{{ item.no }}</td>
              <td>{{ item.id }}</td>
              <td>
                <v-switch
                  @change="set_at_courier(item.id, $event)"
                  :input-value="!!item.has_courier"
                ></v-switch>
              </td>
              <td>{{ item.suite }}</td>
              <td>{{ item.client_name + ' ' + item.client_surname }}</td>
              <td>{{ item.passport_number }}</td>
              <td>{{ item.phone }}</td>
              <td>{{ item.area }}</td>
              <td>{{ item.region }}</td>
              <td>{{ item.post_zip }}</td>
              <td :class="getCellClass(item)">{{ item.azerpost_track }}</td>
              <td>{{ item.order_weight }}</td>
              <td>{{ item.metro_station }}</td>
              <td>{{ item.address }}</td>
              <td>{{ item.date }}</td>
              <td>{{ item.courier_payment_type }}</td>
              <td>{{ item.delivery_payment_type }}</td>
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
                <div class="action">
                  <div>
                    <v-icon small @click="showPackages(item)">mdi-eye</v-icon>
                  </div>
                  <div>
                    <v-icon small @click="showCourier(item)">mdi-package-variant-closed</v-icon>
                  </div>
                  <div>
                    <v-icon small @click="updateDate({id: item.id, date: item.date, track: item.azerpost_track, weight: item.order_weight})">mdi-pen</v-icon>
                  </div>
                  <div>
                    <v-icon small :disabled="!printer" @click="printBill(item)">mdi-printer</v-icon>
                  </div>
                </div>
              </td>
            </tr>
          </template>
          <!-- <template v-slot:item.client="{ item }">{{item.client_name + ' ' + item.client_surname}}</template>
          <template
            v-slot:item.courier="{ item }"
          >{{(item.courier_name || '') + ' ' + (item.courier_surname || '')}}</template>
          <template v-slot:item.actions="{ item }">
            <td>
              <v-icon @click="showPackages(item)">mdi-eye</v-icon>
            </td>
            <td>
              <v-icon @click="showCourier(item)">mdi-package-variant-closed</v-icon>
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
              <v-btn color="green darken-1" text @click="dialog = false">close</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-row>
    </template>
    <template>
      <v-row justify="center">
        <v-dialog v-model="dialogUpdate" max-width="700">
          <v-card>
            <v-card-title class="headline">Date</v-card-title>
            <v-card-text>
              <v-simple-table>
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th class="text-left">Edit date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <input type="date" v-model="update.date">
                      </td>
                    </tr>
                  </tbody>
                  <thead>
                  <tr>
                    <th class="text-left">Add track</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td>
                      <v-text-field v-model="update.track" label="Track" single-line hide-details></v-text-field>
                    </td>
                  </tr>
                  </tbody>
                  <thead>
                  <tr>
                    <th class="text-left">Add weight</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td>
                      <v-text-field type="number" v-model="update.weight" label="Weight" single-line hide-details></v-text-field>

                    </td>
                  </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="green darken-1" text @click="dialogUpdate = false">close</v-btn>
              <v-btn
                color="green darken-1"
                :loading="loading"
                text
                @click="dateUpdate(update)"
              >Save</v-btn>
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
                label="Couriers"
                hide-details
                clearable
              ></v-select>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="green darken-1" text @click="dialogCourier = false">close</v-btn>
              <v-btn
                color="green darken-1"
                :loading="loading"
                :disabled="!setCourierData.courier_id"
                text
                @click="sendCourier()"
              >Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-row>
    </template>
    <v-dialog v-model="preloader" fullscreen>
      <v-container fluid fill-height style="background-color: rgba(255, 69, 0, 0.9);">
        <v-layout justify-center align-center>
          <v-progress-circular indeterminate color="primary"></v-progress-circular>
        </v-layout>
      </v-container>
    </v-dialog>
    <template>
      <v-row justify="center">
        <v-dialog v-model="setAzerpostModal" max-width="700">
          <v-card>
            <v-card-title class="headline">Set Azerpost</v-card-title>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="green darken-1" text @click="setAzerpostModal = false">close</v-btn>
              <v-btn
                  color="green darken-1"
                  :loading="loading"
                  text
                  @click="setToAzerpost()"
              >Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-row>
    </template>
  </v-container>
</template>

<script>
import Swal from 'sweetalert2'

function timeout (ms) {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

export default {
  inheritAttrs: false,
  props:        {
    myRoute: {
      type:     String,
      required: true
    },
    exportTable: {
      type:     String,
      required: true
    },
    printReceiptRoute: {
      type:     String,
      required: true
    },
    logReceiptRoute: {
      type:     String,
      required: true
    },
    setCourier: {
      type:     String,
      required: true
    },
    statuses: {
      type:     Array,
      required: true
    },
    couriers: {
      type:     Array,
      required: true
    },
    printer: {
      type:    String,
      default: null
    },
    areas: {
      type:     Array,
      required: true
    },
    regions: {
      type:     Array,
      required: true
    },
    payment_types: {
      type:     Array,
      required: true
    },
    setAtCourierRoute: {
      type:     String,
      required: true
    },
    setAzerpost: {
      type:     String,
      required: true
    }
  },
  data () {
    return {
      preloader:     false,
      ifSelected:    false,
      loading:       false,
      selected:      [],
      openSearch:    false,
      menu1:         false,
      dialog:        false,
      dialogCourier: false,
      setAzerpostModal: false,
      dialogUpdate:  false,
      headers:       [
        { text: 'No', value: 'no' },
        { text: 'Order Number ', value: 'id' },
        { text: 'At the Courier', value: 'at_the_courier' },
        { text: 'Suite', value: 'suite' },
        { text: 'Client Name ', value: 'client' },
        { text: 'Passport Number ', value: 'passport_number' },
        { text: 'Phone', value: 'phone' },
        { text: 'Area', value: 'area' },
        { text: 'Region', value: 'region' },
        { text: 'Post Index', value: 'post_zip' },
        { text: 'Azerpost Track', value: 'azerpost_track' },
        { text: 'Ceki', value: 'order_weight' },
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
        { text: 'Actions', value: 'actions', sortable: false, align: 'center' }
      ],
      desserts:   [],
      pagination: {
        current: 1,
        total:   0,
        perPage: 30
      },
      circle:       true,
      nextIcon:     'navigate_next',
      prevIcon:     'navigate_before',
      totalVisible: 5,
      isLoading:    false,
      search:       {
        status: ''
      },
      setCourierData: {
        order_id:   '',
        courier_id: ''
      },
      packages_object: '',
      update: '',
      old_orders:      false,
      setAzerpostData: {
        order_id:   [],
      }
    }
  },
  methods: {

    initialize () {
      const _this = this
      _this.isLoading = true
      this.desserts = []
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
            '&no=' +
            (this.search.no ?? null) +
            '&status=' +
            (this.search.status ?? null) +
            '&areas=' +
            (this.search.areas ?? null) +
            '&regions=' +
            (this.search.regions ?? null) +
            '&courier_payment_type=' +
            (this.search.courier_payment_type ?? null) +
            '&delivery_payment_type=' +
            (this.search.delivery_payment_type ?? null) +
            '&date=' +
            (this.search.date ?? null) +
            '&old_orders=' +
            (+this.old_orders ?? 0) +
            '&page=' +
            this.pagination.current
        )
        .then((resp) => {
          if (resp.data.case === 'success') {
            console.log(resp.data.orders.data);
            _this.desserts = resp.data.orders.data
            _this.pagination.current = resp.data.orders.current_page
            _this.pagination.total = resp.data.orders.last_page
            let i = 1
            _this.desserts.forEach(
              (item) =>
                (item.no =
                  (this.pagination.current - 1) * this.pagination.perPage + i++)
            )
          }
        })
        .catch((resp) => {
          Swal.fire({
            type:  'error',
            title: 'Oops...',
            text:  resp.data.content
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
        .then((resp) => {
          if (resp.data.case === 'success') {
            _this.pagination.current = resp.data.current_page
            _this.pagination.total = resp.data.last_page
          }
        })
        .catch((resp) => {
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

    close () {
      this.dialog = false
      this.error = false
      this.commonOrderNumber = ''
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      }, 300)
    },

    showPackages (item) {
      this.packages_object = item.packages_object
      this.dialog = true
    },

    updateDate (item) {
      this.update = item
        console.log(this.update)
      this.dialogUpdate = true
    },

     dateUpdate(item){
      
        const res = axios.post('/warehouse/courier/update-date', item)

        .then((resp) => {
          this.initialize()
          Swal.fire({
            type:  resp.data.case,
            title: resp.data.title,
            text:  resp.data.content
          })
          this.dialogUpdate = false
          this.initialize()
            //console.log(resp)
            // location.reload()
          })
        .catch((resp) => {

          Swal.fire({
            type:  'error',
            title: 'Oops...',
            text: 'Has been an error'
          })
       

      });
    },

    searchSS () {
      this.initialize()
    },

    showCourier (item) {
      this.ifSelected = false
      this.setCourierData.order_id = item.id
      this.dialogCourier = true
    },
    async sendCourier () {
      this.loading = true
      if (!this.ifSelected) {
        axios
          .post(this.setCourier, {
            order_id:   this.setCourierData.order_id,
            courier_id: this.setCourierData.courier_id
          })
          .then((resp) => {
            this.initialize()
            Swal.fire({
              type:  resp.data.case,
              title: resp.data.title,
              text:  resp.data.content
            })
          })
          .catch((resp) => {
            Swal.fire({
              type:  'error',
              title: 'Oops...',
              text:  resp.data.content
            })
          })
          .finally(() => {
            this.dialogCourier = false
            this.setCourierData.order_id = ''
            this.setCourierData.courier_id = ''
            this.loading = false
          })
      } else if (this.ifSelected) {
        let iS = 0
        const eI = []
        for (const item of this.selected) {
          await axios
            .post(this.setCourier, {
              order_id:   item.id,
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
          type:  'success',
          title: 'Success',
          text:  `${iS} of ${
            this.selected.length
          }  orders were successful set( Ids of unsettled orders : ${
            eI || 'none'
          })`
        })
        this.setCourierData.courier_id = ''
        this.selected = []
        this.loading = false
      }
    },
    setCourierToSelected () {

      this.ifSelected = true
      this.dialogCourier = true
    },

    async setToAzerpost () {
      this.loading = true
      const selectedIds = this.selected.map(item => item.id);
      if (!this.ifSelected) {
        Swal.fire({
          type:  'error',
          title: 'Oops...',
          text:  'Not selected item'
        })
        this.setAzerpostModal = false
        this.setAzerpost.order_id = ''
        this.loading = false
      } else if (this.ifSelected) {
        let iS = 0
        const eI = []

        await axios
            .post(this.setAzerpost, {
              order_id:   selectedIds,
            })
            .then((resp) => {
              if (resp.data.case === 'success') {
                console.log(resp.data)
                this.initialize()
                Swal.fire({
                  type:  resp.data.case,
                  title: resp.data.title,
                  text:  resp.data.content
                })
              } else {
                eI.push(item.id)
              }
            })
            .catch((resp) => {
              Swal.fire({
                type:  'error',
                title: 'Oops...',
                text:  resp.data.content
              })
            })
        this.setAzerpostModal = false
        this.initialize()
        await Swal.fire({
          type:  'success',
          title: 'Success',
          text:  `${iS} of ${
              this.selected.length
          }  orders were successful set( Ids of unsettled orders : ${
              eI || 'none'
          })`
        })
        this.selected = []
        this.loading = false
      }
    },
    async setToAzerpostFor () {
      this.loading = true
      if (!this.ifSelected) {
        axios
            .post(this.setAzerpost, {
              order_id:   this.setAzerpostData.order_id
            })
            .then((resp) => {
              this.initialize()
              Swal.fire({
                type:  resp.data.case,
                title: resp.data.title,
                text:  resp.data.content
              })
            })
            .catch((resp) => {
              Swal.fire({
                type:  'error',
                title: 'Oops...',
                text:  resp.data.content
              })
            })
            .finally(() => {
              this.setAzerpostModal = false
              this.setAzerpost.order_id = ''
              this.loading = false
            })
      } else if (this.ifSelected) {
        let iS = 0
        const eI = []
        const selectedIds = this.selected.map(item => item.id);
        for (const item of this.selected) {
          await axios
              .post(this.setAzerpost, {
                order_id:   item.id,
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
        this.setAzerpostModal = false
        this.initialize()
        await Swal.fire({
          type:  'success',
          title: 'Success',
          text:  `${iS} of ${
              this.selected.length
          }  orders were successful set( Ids of unsettled orders : ${
              eI || 'none'
          })`
        })
        this.setAzerpostData.courier_id = ''
        this.selected = []
        this.loading = false
      }
    },
    setAzerpostToSelected () {
      const selectedIds = this.selected.map(item => item.id);
      console.log(selectedIds)
      this.ifSelected = true
      this.setAzerpostModal = true
    },

    getCellClass(item) {
      if (item.is_send_azerpost === 1) {
        return 'is_send_azerpost';
      } else if (item.is_set_azerpost === 1 && item.is_send_azerpost === 0 && item.is_error === 0) {
        return 'is_set_azerpost';
      } else if (item.is_error === 1) {
        return 'is_error';
      } else {
        return '';
      }
    },

    async printCourierToSelected () {
      this.loading = true

      let iS = 0
      const eI = []
      for (const item of this.selected) {
        await axios
          .post(this.printReceiptRoute, { id: item.id, ip: this.printer })
          .then((resp) => {
            if (resp.data.case === 'success') {
              iS++
              const letter = resp.data.response

              let txt = JSON.stringify(letter)
              txt = txt.replace(/[{]/g, '&')
              txt = txt.replace(/[}]/g, '|')
              txt = txt.replace(/["']/g, '')

              // setTimeout(()=>{},iS*3)
              this.download('EDI-Team-2019-Aser-Courier.txt', txt)

              const log = new FormData()
              log.append('status', 1)
              log.append('text', txt)
              axios.post(this.logReceiptRoute, log).then((resp) => {})
            } else {
              eI.push(item.id)
            }
          })
          .catch((resp) => {
            eI.push(item.id)
          })
        await timeout(1000)
      }
      this.initialize()
      await Swal.fire({
        type:  'success',
        title: 'Success',
        text:  `${iS} of ${
          this.selected.length
        }  orders were successful set( Ids of unsettled orders : ${
          eI || 'none'
        })`
      })
      this.selected = []
      this.loading = false
    },

    download (filename, text) {
      const element = document.createElement('a')
      element.setAttribute(
        'href',
        'data:text/plain;charset=utf-8,' + encodeURIComponent(text)
      )
      element.setAttribute('download', filename)

      element.style.display = 'none'
      document.body.appendChild(element)

      element.click()

      document.body.removeChild(element)
    },
    printBill (item) {
      axios
        .post(this.printReceiptRoute, { id: item.id, ip: this.printer })
        .then((resp) => {
          if (resp.data.case === 'success') {
            const letter = resp.data.response

            let txt = JSON.stringify(letter)
            txt = txt.replace(/[{]/g, '&')
            txt = txt.replace(/[}]/g, '|')
            txt = txt.replace(/["']/g, '')

            this.download('EDI-Team-2019-Aser-Courier.txt', txt)

            Swal.fire({
              position:          'top-end',
              icon:              'success',
              title:             'Success',
              showConfirmButton: false,
              timer:             1500
            })
            const log = new FormData()
            log.append('status', 1)
            log.append('text', txt)
            axios.post(this.logReceiptRoute, log).then((resp) => {})
          } else {
            Swal.fire({
              position:          'top-end',
              icon:              'error',
              title:             resp.data.content,
              showConfirmButton: false,
              timer:             1500
            })
          }
        })
        .catch((resp) => {
          Swal.fire({
            position:          'top-end',
            icon:              'error',
            title:             resp.data.content,
            showConfirmButton: false,
            timer:             1500
          })
        })
    },
    allPackages () {
      // for (let member in this.search) delete this.search[member]
      this.search = {}
      this.old_orders = true
      this.initialize()
    },
    set_at_courier (id, e) {
      this.preloader = true
      axios
        .post(this.setAtCourierRoute, {
          order_id:    id,
          has_courier: +e
        })
        .then((resp) => {
          if (resp.data.case !== 'success') {
            this.initialize()
            Swal.fire({
              type:  resp.data.case || 'Oops ...',
              title: resp.data.title || 'No title',
              text:  resp.data.content || 'Something goes wrong'
            })
          }
          // Swal.fire({
          //   type: resp.data.case,
          //   title: resp.data.title,
          //   text: resp.data.content
          // })
        })
        .catch((resp) => {
          this.initialize()
          Swal.fire({
            type:  'error',
            title: 'Oops...',
            text:  'Something goes wrong ..'
          })
        })
        .finally(() => {
          this.preloader = false
        })
    }
  },
  created () {
    this.couriers.unshift({ id: 1907, name: 'Canceled' })
  }
}
</script>
