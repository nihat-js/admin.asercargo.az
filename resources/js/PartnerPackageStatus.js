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

import PartnerPackageStatus from '@/js/views/PartnerPackageStatus.vue'
import Swal        from 'sweetalert2'

new Vue({
  el        : '#partnerPackage',
  vuetify   : new Vuetify(),
  components: {
    'my-partnerpackagestatus': PartnerPackageStatus,
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
    changeStatus (url) {
      let _this          = this
      this.loadingButton = true
      let flight         = this.flight_id
      axios.post(url, { 'flight': flight })
           .then(function (resp) {
             if (resp.data.case === 'success') {
               location.reload()
             } else {
               _this.loadingButton = false
               Swal.fire({
                 type : 'error',
                 title: resp.data.title,
                 text : resp.data.content,
               })
             }
           })
           .catch(function (resp) {
             _this.loadingButton = false
             Swal.fire({
               type : 'error',
               title: resp.data.title,
               text : resp.data.content,
             })
           })
    },
    showGomruk () {
      console.log('asd')
    },
    close () {
      this.dialog  = false
      this.dialog2 = false
    },
  },
})
