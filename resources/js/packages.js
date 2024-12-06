require('./bootstrap')
window.toastr = require('toastr')

import Vue     from 'vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

import packages from '@/js/views/packages.vue'

new Vue({
  el        : '#packages',
  vuetify   : new Vuetify(),
  components: {
    'my-packages': packages
  }
})

