require('./bootstrap')
window.toastr = require('toastr')

import Vue         from 'vue'
import VeeValidate from 'vee-validate'
import Vuetify     from 'vuetify'

import anonymous from '@/js/views/anonymous.vue'

Vue.use(VeeValidate)

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({ // eslint-disable-line no-new
  el        : '#anonymous',
  vuetify   : new Vuetify(),
  components: {
    'my-anonymous': anonymous
  }
})
