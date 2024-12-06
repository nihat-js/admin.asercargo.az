import Vue            from 'vue'

require('./bootstrap')
import Vuetify        from 'vuetify'
import { Popconfirm } from 'element-ui'

Vue.use(Popconfirm)
import 'element-ui/lib/theme-chalk/index.css'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

// @ts-ignore
import sub_accounts from '@/js/views/sub_accounts.vue'
//import station from '@/js/components/station.vue'

new Vue({
  el        : '#sub_accounts',
  vuetify   : new Vuetify(),
  components: {
    'my-sub_accounts': sub_accounts
  }
})
/*new Vue({
  el: '#header',
  vuetify: new Vuetify(),
  data:{
    roleParent:''
  },
  components: {
    'my-station':station
  },
})*/

