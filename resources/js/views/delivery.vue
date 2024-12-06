<template>
  <v-app>
    <!--<div class="wrap container">
      <div class="left">
        <v-text-field
                autofocus
                label="Payment code"
                placeholder="Payment code"
                outlined
                v-model="scan"
                @change="scanner()"
                maxlength="8"
                clearable
                ref="payment"
        ></v-text-field>
        <template>
          <v-data-table
                  :headers="headers"
                  :items="packages"
                  item-key="number"
                  disable-pagination
                  hide-default-footer
                  class="elevation-1"
          >
          </v-data-table>
        </template>
      </div>
      <div class="right">
        <v-text-field
                label="Track No"
                placeholder="Track No"
                outlined
                ref="track"
                v-model="track_no"
                @change="setDelivered()"
                minlength="6"
                clearable
        ></v-text-field>
        <template>
          <v-data-table
                  :headers="headers"
                  :items="deliveredPackages"
                  item-key="number"
                  disable-pagination
                  hide-default-footer
                  class="elevation-1"
          >
          </v-data-table>
        </template>

      </div>
    </div>-->
    <v-container fluid>
      <v-row>
        <v-col v-if="courierOrder" cols="12">
          <v-alert
              dense
              type="info"
          >
            Courier Order
          </v-alert>
        </v-col>
        <v-col cols="6">
          <v-text-field
              autofocus
              label="Payment code"
              placeholder="Payment code"
              outlined
              v-model="scan"
              @change="scanner()"
              maxlength="8"
              clearable
              ref="payment"
          ></v-text-field>
          <template>
            <v-data-table
                :headers="headers"
                :items="packages"
                item-key="number"
                disable-pagination
                hide-default-footer
                class="elevation-1"
            >
            </v-data-table>
          </template>
        </v-col>
        <v-col cols="6">
          <v-text-field
              label="Track No"
              placeholder="Track No"
              outlined
              ref="track"
              v-model="track_no"
              @change="setDelivered()"
              minlength="6"
              clearable
          ></v-text-field>
          <template>
            <v-data-table
                :headers="headers"
                :items="deliveredPackages"
                item-key="number"
                disable-pagination
                hide-default-footer
                class="elevation-1"
            >
            </v-data-table>
          </template>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script>
import Swal from 'sweetalert2'

export default {
  props  : {
    'myRoute'          : {
      type: String,
    },
    'admin'            : {
      type: String,
    },
    'setDeliveredRoute': {
      type: String,
    },
  },
  data () {
    return {
      scan             : '',
      track_no         : '',
      selectedPackage  : [],
      headers          : [
        { text: 'Track No', align: 'left', sortable: false, value: 'number' },
        { text: 'Internal Id', align: 'left', value: 'internal_id' },
        { text: 'paid', align: 'left', value: 'paid' },
        { text: 'currency', align: 'left', value: 'currency' },
      ],
      packages         : [],
      deliveredPackages: [],
      courierOrder     : false,
    }
  },
  methods: {
    scanner () {
      let self               = this
      self.deliveredPackages = []
      if ((this.scan?.length || 0) === 8) {
        let formData = new FormData
        formData.append('receipt', this.scan)
        axios.post(this.myRoute, formData)
             .then(function (resp) {
               if (resp.data.case === 'success') {
                 self.packages     = resp.data['packages']
                 self.courierOrder = resp.data.is_courier_order
                 self.$refs.track.focus()
               } else {
                 self.packages = []
                 self.scan     = ''
                 self.client   = []
                 toastr.error(resp.data.content)
                 new Audio('../assets/error.wav').play()
                 setTimeout(() => {
                   new Audio('../assets/error.wav').play()
                 }, 500)
               }
             })
             .catch(function (resp) {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : 'Something went wrong!',
               })
             })
      } else {
        self.packages = []
      }
    },
    setDelivered () {
      let self = this
      if ((this.track_no?.length || 0) >= 6) {
        /*this.selectedPackage = this.packages.filter(obj => {
        return obj?.number === self.track_no.toString()
     })*/

        let formData = new FormData
        formData.append('receipt', this.scan)
        // formData.append('package', this.selectedPackage[0]?.number)
        let packageTrack = this.track_no
        if (packageTrack.startsWith('42019801')) {
          packageTrack = packageTrack.slice(8)
        }
        formData.append('package', packageTrack)
        axios.post(this.setDeliveredRoute, formData)
             .then(function (resp) {
               if (resp.data.case === 'success') {
                 let selectedPackage = self.packages.filter(obj => {
                   return obj?.number === resp.data.package.toString()
                 })
                 self.packages       = self.packages.filter(obj => {
                   return obj?.number !== resp.data.package
                 })
                 self.deliveredPackages.push(selectedPackage[0])
                 if (!self.packages.length) {
                   self.scan = ''
                   self.$refs.payment.focus()
                 }
                 self.track_no = ''
               } else {
                 self.track_no = ''
                 toastr.error(resp.data.content)
                 new Audio('../assets/error.wav').play()
                 setTimeout(() => {
                   new Audio('../assets/error.wav').play()
                 }, 500)
               }
             })
             .catch(function (resp) {
               Swal.fire({
                 type : 'error',
                 title: 'Oops...',
                 text : 'Something went wrong!',
               })
             })
      } else {
      }
    },
  },
  mounted () {
    toastr.options = {
      'closeButton'      : false,
      'debug'            : false,
      'newestOnTop'      : false,
      'progressBar'      : false,
      'positionClass'    : 'toast-bottom-full-width',
      'preventDuplicates': false,
      'onclick'          : null,
      'showDuration'     : '300',
      'hideDuration'     : '1000',
      'timeOut'          : '10000',
      'extendedTimeOut'  : '1000',
      'showEasing'       : 'swing',
      'hideEasing'       : 'linear',
      'showMethod'       : 'fadeIn',
      'hideMethod'       : 'fadeOut',
    }
  },
}
</script>
