/* eslint-disable no-new */
import Vue from 'vue'

import Vuetify from 'vuetify'

import delivery from '@/js/views/delivery.vue'
import station  from '@/js/components/station.vue'

require('./bootstrap')
window.toastr = require('toastr')

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({
  el:         '#delivery',
  vuetify:    new Vuetify(),
  components: {
    'my-delivery': delivery,
    'my-station':  station
  }
})/*
new Vue({
  el:         '#header',
  vuetify:    new Vuetify(),
  components: {
    'my-station': station
  }
}) */
