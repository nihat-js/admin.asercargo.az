<template>
  <v-app>
    <div class="wrap container">
      <div class="top">
        <v-select
            :items="flights"
            item-text="name"
            item-value="id"
            label="Flights"
            outlined
            v-model="flight"
        ></v-select>
        <v-btn
            raised
            color="primary"
            dark
            @click="changeCanadaShopStatus()"
        >
          Change Status
        </v-btn>
      </div>

    </div>

  </v-app>

</template>

<script>
export default {
  name   : 'canadashoppackage',
  props  : {
    'myRoute': {
      type: String,
    },
    'userLocation': {
      type: String,
    },
    'baseUrl': {
      type: String,
    },
    'flights'  : {
      type: Array,
    },
  },
  data () {
    return {
      id        : 1,
      flight  : '',
      headers   : [
        { text: 'Id', align: 'left', sortable: false, value: 'id' },
        { text: 'flight', align: 'left', value: 'flight' },
      ],
      log       : [],

    }
  },
  methods: {
    changeCanadaShopStatus () {

      let url = "/warehouse/partner/canada-shop/set";
      let _this          = this
      this.loadingButton = true
      let formData = new FormData
      formData.append('flight', this.flight)
      axios.post(url, formData)
          .then(function (resp) {
            if (resp.data.case === 'success') {
              toastr.success(resp.data.content)
              setTimeout(() => {
                new Audio('../../assets/sucess.wav').play()
              }, 500);
            } else {
              toastr.error(resp.data.content)
              new Audio('../../assets/error.wav').play()
              setTimeout(() => {
                new Audio('../../assets/error.wav').play()
              }, 500)
            }
          })
          .catch(function (resp) {
            console.log(resp)
          })
    },

  },
  mounted () {
    toastr.options = {
      'closeButton'      : false,
      'debug'            : false,
      'newestOnTop'      : false,
      'progressBar'      : false,
      'positionClass'    : 'toast-bottom-full-width',
      'preventDuplicates': false,
      'onclick'          : null,
      'showDuration'     : '300',
      'hideDuration'     : '1000',
      'timeOut'          : '10000',
      'extendedTimeOut'  : '1000',
      'showEasing'       : 'swing',
      'hideEasing'       : 'linear',
      'showMethod'       : 'fadeIn',
      'hideMethod'       : 'fadeOut',
    }
  },
}


</script>

