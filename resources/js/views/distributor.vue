<template>
  <v-app>
    <div class="wrap container">
      <div class="top">
        <v-text-field
            class="scan_input"
            autofocus
            label="Scan "
            placeholder="Scan"
            outlined
            v-model="scan_input"
            @change="scanner()"
        ></v-text-field>
        <v-text-field
            autofocus
            label="Track"
            placeholder="Track No"
            outlined
            v-model="track"
            disabled
        ></v-text-field>
        <v-text-field
            label="Position"
            placeholder="Position"
            v-model="position"
            outlined
            disabled
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
  name   : 'distributor',
  props  : {
    'myRoute': {
      type: String,
    },
  },
  data () {
    return {
      id        : 1,
      position  : '',
      track     : '',
      scan_input: '',
      headers   : [
        { text: 'Id', align: 'left', sortable: false, value: 'id' },
        { text: 'track', align: 'left', value: 'track' },
        { text: 'Position', align: 'left', value: 'position' },
      ],
      log       : [],
    }
  },
  methods: {
    scanner () {
      let self    = this
      let barcode = this.scan_input
      if (barcode.substr(0, 2)
                 .toUpperCase() === 'PS') {
        //position
        self.position = barcode.substr(2, barcode.length)
      } else {
        //package
        let packageTrack = barcode
        if (packageTrack.startsWith('42019801')) {
          packageTrack = packageTrack.slice(8)
        }
        self.track = packageTrack
      }
      self.scan_input = ''
      if (this.position && this.track) {
        let formData = new FormData
        formData.append('track', this.track)
        formData.append('position', this.position)
        axios.post(this.myRoute, formData)
             .then(function (resp) {
               if (resp.data.case === 'success') {
                 toastr.success(resp.data.content)
                  // new Audio('../assets/sucess.wav').play()
                 setTimeout(() => {
                   new Audio('../assets/sucess.wav').play()
                 }, 500)
                 /*if (self.log.length===9 ){
               self.log.splice(-1,1)
             }*/
                 let item = self.log.find(el => el.track === self.track)
                 if (item) {
                   let index       = self.log.indexOf(item)
                   item.position   = self.position
                   self.log[index] = item
                 } else {
                   self.log.unshift({
                     id      : self.id++,
                     track   : self.track,
                     position: self.position,
                   })
                 }
               }else if (resp.data.case === 'branch') {
                 toastr.error(resp.data.content)
                // new Audio('../assets/negativeBeep.wav').play()
                 setTimeout(() => {
                   new Audio('../assets/negativeBeep.wav').play()
                 }, 500)
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

