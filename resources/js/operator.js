/* eslint-disable no-new */
import Vue            from 'vue'
import Vuetify        from 'vuetify'
import { Popconfirm } from 'element-ui'

Vue.use(Popconfirm)
import 'element-ui/lib/theme-chalk/index.css'
import operator       from '@/js/views/operator.vue'
import station        from '@/js/components/station.vue'

require('./bootstrap')
window.toastr = require('toastr')

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({
  el        : '#operator',
  vuetify   : new Vuetify(),
  data      : {
    roleParent: ''
  },
  components: {
    'my-operator': operator,
    'my-station' : station
  }
})
/* new Vue({
  el:      '#header',
  vuetify: new Vuetify(),
  data:    {
    roleParent: ''
  },
  components: {
    'my-station': station
  }
}) */
