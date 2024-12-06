require('./bootstrap')
window.toastr = require('toastr')

import Vue     from 'vue'
import Vuetify from 'vuetify'

import customs from '@/js/views/customs.vue'
import Swal    from 'sweetalert2'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({ // eslint-disable-line no-new
  el        : '#customs',
  vuetify   : new Vuetify(),
  components: {
    'my-customs': customs
  }
})

new Vue({ // eslint-disable-line no-new
  el     : '#header',
  vuetify: new Vuetify(),
  data   : {
    dialog       : false,
    flight_id    : '',
    disableButton: false,
    loadingButton: false
  },
  methods: {
    close () {
      this.dialog = false
    }
  }
})
