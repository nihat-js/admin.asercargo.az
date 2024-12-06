<template>
  <div>
    <v-text-field
        min="1"
        type="text"
        label="Waybill track"
        placeholder="Waybill track"
        hide-details
        outlined
        v-model="table"
        clearable
    ></v-text-field>
    <v-spacer></v-spacer>
    <slot name="role" :items="items" :role="role"></slot>
    <v-btn :disabled="!table" @click="openNewTab" color="#ec1b1b">Waybill</v-btn>
  </div>
</template>

<script>
export default {
  data: () => ({
    table: '',
    items: ['online', 'information']
  }),
  methods: {
    openNewTab() {
      var searchValue = this.table;
      var url = "https://manager.asercargo.az/waybill/" + encodeURIComponent(searchValue);

      window.open(url, '_blank');
    }
  }
}
</script>

<style lang='scss'>
.station {
  grid-template-columns: 2fr 2fr 1fr;
  display: grid;
  grid-gap: 5px;
}
</style>
