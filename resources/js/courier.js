require('./bootstrap')
window.toastr = require('toastr')
import Vue     from 'vue'
import Vuetify from 'vuetify'

import courier from '@/js/views/courier.vue'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({ // eslint-disable-line no-new
  el        : '#courier',
  vuetify   : new Vuetify(),
  data () {
    return {
      printer: ''
    }
  },
  components: {
    'my-courier': courier
  }
})
