require('./bootstrap')
window.toastr = require('toastr')

import Vue from 'vue'

import Vuetify from 'vuetify'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi',
  },
})

import CanadaShopPackageStatus from '@/js/views/CanadaShopPackageStatus.vue'
import Swal        from 'sweetalert2'

new Vue({
  el        : '#canadaShopPackage',
  vuetify   : new Vuetify(),
  components: {
    'my-canadashoppackagestatus': CanadaShopPackageStatus,
  },
})

new Vue({
  el     : '#header',
  vuetify: new Vuetify(),
  data   : {
    dialog       : false,
    dialog2      : false,
    flight_id    : '',
    disableButton: false,
    loadingButton: false
  },
  methods: {
    showGomruk () {
      console.log('asd')
    },
    close () {
      this.dialog  = false
      this.dialog2 = false
    },
  },
})
