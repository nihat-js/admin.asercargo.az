require('./bootstrap')

import Vue from 'vue'

import Vuetify from 'vuetify'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

import report from '@/js/views/report.vue'

new Vue({
  el        : '#report',
  vuetify   : new Vuetify(),
  components: {
    'my-report': report
  }
})

