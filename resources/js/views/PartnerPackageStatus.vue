<template>
  <v-app>
    <div class="wrap container">
      <div class="top">
        <v-select
            :items="statuses"
            item-text="status_en"
            item-value="id"
            label="Status"
            outlined
            v-model="status"
        ></v-select>
        <v-text-field
            class="scan_input"
            autofocus
            label="Scan"
            placeholder="Scan1"
            outlined
            v-model="scan_input"
            @change="scanner()"
        ></v-text-field>

      </div>
      <div class="bottom">
        <template>
          <v-data-table
              :headers="headers"
              :items="log"
              item-key=""
              disable-pagination
              hide-default-footer
              class="elevation-1"
          >
          </v-data-table>
        </template>
      </div>
    </div>

  </v-app>

</template>

<script>
export default {
  name   : 'partnerpackage',
  props  : {
    'myRoute': {
      type: String,
    },
    'userLocation': {
      type: String,
    },
    'baseUrl': {
      type: String,
    },
    'statuses'  : {
      type: Array,
    },
  },
  data () {
    return {
      id        : 1,
      status  : '',
      scan_input: '',
      track     : '',
      headers   : [
        { text: 'Id', align: 'left', sortable: false, value: 'id' },
        { text: 'status', align: 'left', value: 'status' },
        { text: 'track', align: 'left', value: 'track' },
      ],
      log       : [],

    }
  },
  methods: {
    scanner () {
      let self    = this
      let barcode = this.scan_input

        //pack
        let packageTrack = barcode

        if (packageTrack.startsWith('42019801')) {
          packageTrack = packageTrack.slice(8)
        }
        self.track = packageTrack

      self.scan_input = ''
      if (this.status && this.track) {
        let formData = new FormData
        formData.append('track', this.track)
        formData.append('status', this.status)
        axios.post(this.myRoute, formData)
             .then((resp) => {
               if (resp.data.case === 'success') {

                 toastr.success(resp.data.content)
                 setTimeout(() => {
                   new Audio('../assets/sucess.wav').play()
                 }, 500);


                 let item = self.log.find(el => el.track === self.track)
                 if (item) {
                   let index       = self.log.indexOf(item)
                   item.status   = self.status
                   self.log[index] = item
                 } else {
                   self.log.unshift({
                     id      : self.id++,
                     track   : self.track,
                     status: self.status,
                   })
                 }
               }
               else {
                 toastr.error(resp.data.content)
                 new Audio('../assets/error.wav').play()
                 setTimeout(() => {
                   new Audio('../assets/error.wav').play()
                 }, 500)
               }
             })
             .catch(function (resp) {
               console.log(resp);
               alert('Error scanner')
             })
             .finally(() => {
               self.track = ''
               // self.position = ''
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

