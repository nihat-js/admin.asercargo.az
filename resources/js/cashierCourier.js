import Vue     from 'vue'
import Vuetify from 'vuetify'

import cashierCourier from '@/js/views/cashierCourier.vue'

require('./bootstrap')
window.toastr = require('toastr')

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({ // eslint-disable-line no-new
  el:      '#cashierCourier',
  vuetify: new Vuetify(),
  data () {
    return {
      printer: ''
    }
  },
  components: {
    'my-cashier-courier': cashierCourier
  }
})
