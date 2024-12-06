require('./bootstrap')
window.toastr = require('toastr')

import Vue         from 'vue'
import VeeValidate from 'vee-validate'
import Vuetify     from 'vuetify'

import makeOrder from '@/js/views/makeOrder.vue'

Vue.use(VeeValidate)

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({ // eslint-disable-line no-new
  el        : '#makeOrder',
  vuetify   : new Vuetify(),
  components: {
    'my-makeorder': makeOrder
  }
})
