import Vue from 'vue'
import Vuetify from 'vuetify'

import courierUser from '@/js/views/courier_user.vue'
require('./bootstrap')
window.toastr = require('toastr')

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

// eslint-disable-next-line no-new
new Vue({
  el: '#courier_user',
  vuetify: new Vuetify(),
  data() {
    return {
      printer: ''
    }
  },
  components: {
    'my-courier_user': courierUser
  }
})
