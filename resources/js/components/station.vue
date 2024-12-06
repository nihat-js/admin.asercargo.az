<template>
  <div>
    <v-text-field
        min=1
        type="number"
        label="Station Number"
        placeholder="Station Number"
        hide-details
        outlined
        v-model="table"
        clearable
    ></v-text-field>
    <v-spacer></v-spacer>
    <slot name="role" :items="items" :role="role"></slot>
  <v-btn :disabled="!table" @click="callClient" color="#ec1b1b">Call</v-btn>
  </div>
</template>

<script>
export default {
  props: {
    myRoute: {
      type: String
    },
    role: {
      type: String
    }
  },
  data: () => ({
    table: '',
    items: ['online', 'information']
  }),
  methods: {
    callClient () {
      const formData = new FormData()
      formData.append('station', this.table)
      if (this.role) { formData.append('role', this.role) }
      axios.post(this.myRoute, formData)
        .then(function (resp) {
          if (resp.data.case === 'success') {
            toastr.success(resp.data.content)
          } else {
            toastr.error(resp.data.content)
          }
        })
        .catch(function (resp) {
          console.log(resp)
        })
    }
  },
  mounted () {
    toastr.options = {
      closeButton:       false,
      debug:             false,
      newestOnTop:       false,
      progressBar:       false,
      positionClass:     'toast-bottom-right',
      preventDuplicates: false,
      onclick:           null,
      showDuration:      '300',
      hideDuration:      '1000',
      timeOut:           '5000',
      extendedTimeOut:   '1000',
      showEasing:        'swing',
      hideEasing:        'linear',
      showMethod:        'fadeIn',
      hideMethod:        'fadeOut'
    }
  }
}
</script>
<style lang='scss'>
.station{
  grid-template-columns: 2fr 2fr 1fr;
    display: grid;
    grid-gap: 5px;
}
</style>
