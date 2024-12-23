require('../bootstrap')
import Vue         from 'vue'
import VeeValidate from 'vee-validate'
import App         from '@/js/moderator/App.vue'
import router      from '@/js/moderator/router.js'
import Vuetify     from 'vuetify'
import CKEditor    from 'ckeditor4-vue'

Vue.use(CKEditor)

Vue.use(VeeValidate)

Vue.config.productionTip = false
Vue.use(Vuetify)

export default new Vuetify({
  icons: {
    iconfont: 'mdi',
  },
})

window.onload = function () {
  new Vue({
    el: '#moderator',
    router: router,
    vuetify: new Vuetify(),
    components: {
      'my-app': App,
    },
    // render: h => h(App),
  })
}

document.addEventListener('DOMContentLoaded', function (event) {
  document.getElementById('preloader').style.display = 'none'
})

