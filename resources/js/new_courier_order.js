require('./bootstrap')
import Vue from 'vue'
import Vuetify from 'vuetify'
import newCourierOrder from '@/js/views/new_courier_order.vue'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({ // eslint-disable-line no-new
  el: '#new_courier_order',
  vuetify: new Vuetify(),
  components: {
    'my-new_courier_order': newCourierOrder
  }
})
