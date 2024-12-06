require('./bootstrap')

import Vue from 'vue'

import Vuetify from 'vuetify'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

import courier from '@/js/views/operatorCourier.vue'

new Vue({
  el        : '#operatorCourier',
  vuetify   : new Vuetify(),
  components: {
    'my-courier': courier
  }
})