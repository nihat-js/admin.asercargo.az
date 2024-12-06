import Swal from 'sweetalert2'

require('./bootstrap')
window.toastr = require('toastr')
window.Swal   = require('sweetalert2')

import Vue     from 'vue'
import Vuetify from 'vuetify'

import cashier from '@/js/views/cashier.vue'
import station from '@/js/components/station.vue'
import waybill from '@/js/components/waybill.vue'

Vue.use(Vuetify)
export default new Vuetify({
  icons: {
    iconfont: 'mdi'
  }
})

new Vue({ // eslint-disable-line no-new
  el        : '#cashier',
  vuetify   : new Vuetify(),
  components: {
    'my-station': station,
    'my-waybill': waybill,
    'my-cashier': cashier
  },
  data () {
    return {
      dialog        : false,
      dialog2       : false,
      fromDateReport: '',
      toDateReport  : '',
      menu          : false,
      menu1         : false,
      date          : new Date(),
      suite         : '',
      client        : '',
      balanceUSD    : '0',
      balanceAZN    : '0',
      valueToBalance: '',
      currency      : '',
    }
  },
  methods   : {
    printReport () {
      if (this.fromDateReport && this.toDateReport) {
        document.myForm.submit()
        console.log('suc')
        this.dialog = false
      }
    },
    clear () {
      this.client         = ''
      this.suite          = ''
      this.valueToBalance = ''
      this.currency       = null
      this.balanceUSD     = ''
      this.balanceAZN     = ''
    },
    updateBalance () {
      axios.post(window.setRoute, {
        'suite'   : this.suite,
        'amount'  : this.valueToBalance,
        'currency': this.currency,
      })
           .then((resp) => {
             if (resp.data.case === 'success') {
               Swal.fire({
                 type : resp.data.case,
                 title: resp.data.title,
                 text : resp.data.content
               })
               this.clear()
             } else {
               Swal.fire({
                 type : resp.data.case,
                 title: resp.data.title,
                 text : resp.data.content
               })
             }
           })
           .catch((e) => {
             Swal.fire({
               type : e.data.case,
               title: e.data.title,
               text : e.data.content
             })
           })
    },
    sendSuite (route) {
      if (this.suite.length === 6) {
        axios.post(window.getRoute, { 'suite': this.suite })
             .then((resp) => {
               if (resp.data.case === 'success') {
                 this.balanceAZN = resp.data.balance_azn
                 this.balanceUSD = resp.data.balance_usd
                 this.client     = resp.data.client
               } else {
                 Swal.fire({
                   type : resp.data.case,
                   title: resp.data.title,
                   text : resp.data.content
                 })
               }
             })
             .catch((resp) => {
               Swal.fire({
                 type : resp.data.case,
                 title: resp.data.title,
                 text : resp.data.content
               })
             })
      }
    }
  },
  computed  : {
    dateEnd () {
      const dt = new Date()
      dt.setDate(dt.getDate())
      return dt.toISOString()
               .substr(0, 10)
    },
    dateStart () {
      const dt = new Date()
      dt.setDate(dt.getDate() - 2)
      return dt.toISOString()
               .substr(0, 10)
    }
  }
})
