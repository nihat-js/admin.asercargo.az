<template>
  <v-app>
    <div class="container">
      <h1>Country Details</h1>
      <v-data-table
        :headers="headers"
        :items="desserts"
        disable-sort
        disable-filtering
        class="elevation-1"
        loading-text="Loading... Please wait"
        :loading="isLoading"
        hide-default-footer
        dark
      >
        <template v-slot:top>
          <v-toolbar flat>
            <v-text-field
              v-model="search.name"
              label="Name"
              single-line
              hide-details
              @keyup.enter="searchStore()"
            ></v-text-field>
            <v-divider class="mx-4" inset vertical></v-divider>
            <!-- <div class="flex-grow-1"></div> -->

            <v-dialog v-model="dialog" max-width="800px">
              <v-card>
                <v-card-title>
                  <span class="headline">{{ formTitle }}</span>
                </v-card-title>

                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="12" v-if="error">
                        <v-alert type="error">
                          Please fill correctly all inputs !
                        </v-alert>
                      </v-col>
                      <template v-for="item in selectedCountryDetails">
                        <input type="hidden" :v-model="item.id" />
                        <input type="hidden" :v-model="item.country_id" />

                        <v-col cols="12" sm="5" md="5">
                          <v-text-field
                            required
                            v-validate="'required'"
                            :error-messages="errors.collect('title')"
                            data-vv-name="title"
                            v-model="item.title"
                            label="Title"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="5" md="5">
                          <v-text-field
                            required
                            v-validate="'required'"
                            :error-messages="errors.collect('information')"
                            data-vv-name="information"
                            v-model="item.information"
                            label="Information"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="2" md="2">
                          <v-icon small @click="deleteItem(item)">
                            delete
                          </v-icon>
                        </v-col>
                      </template>
                      <v-col cols="12" sm="12" md="12">
                        <v-btn @click="newCountryDetails">New</v-btn>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <div class="flex-grow-1"></div>
                  <v-btn color="blue darken-1" text @click="close"
                    >Cancel</v-btn
                  >
                  <v-btn color="blue darken-1" text @click="save">Save</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-toolbar>
        </template>
        <template v-slot:item.action="{ item }">
          <v-icon small class="mr-2" @click="editItem(item)">
            edit
          </v-icon>
        </template>
        <template v-slot:no-data>
          <h2>There is nothing!</h2>
          <v-btn color="primary" @click="initialize">Reset</v-btn>
        </template>
      </v-data-table>
      <template>
        <div class="text-center">
          <v-pagination
            v-model="pagination.current"
            :length="pagination.total"
            @input="onPageChange"
            :circle="circle"
            :next-icon="nextIcon"
            :prev-icon="prevIcon"
            :total-visible="totalVisible"
          ></v-pagination>
        </div>
      </template>
    </div>
  </v-app>
</template>

<script>
import Swal from 'sweetalert2'

export default {
  $_veeValidate: {
    validator: 'new'
  },
  components: {},
  data: () => ({
    pagination: {
      current: 1,
      total: 0
    },
    circle: true,
    nextIcon: 'navigate_next',
    prevIcon: 'navigate_before',
    totalVisible: 5,
    error: false,
    isLoading: true,
    search: { name: '' },
    dialog: false,
    headers: [
      { text: 'country id', value: 'country.id' },
      { text: 'country name', value: 'country.name_az' },
      { text: 'Actions', value: 'action', sortable: false }
    ],
    desserts: [],
    editedIndex: -1,
    editedItem: {},
    defaultItem: {},
    selectedCountryDetails: [],
    csrf: document
      .querySelector('meta[name="csrf-token"]')
      .getAttribute('content')
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? 'New Country Details'
        : 'Edit Country Details'
    }
  },

  watch: {
    dialog(val) {
      val || this.close()
    }
  },

  created() {
    this.initialize()
  },

  methods: {
    initialize() {
      const _this = this
      _this.isLoading = true
      axios
        .get(
          '/moderatorAPI/showCountryDetails' +
            '?name=' +
            this.search.name +
            '&page=' +
            this.pagination.current
        )
        .then((resp) => {
          _this.desserts = resp.data.data
          _this.pagination.current = resp.data.current_page
          _this.pagination.total = resp.data.last_page
        })
        .catch((resp) => {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!'
          })
        })
        .finally(() => {
          _this.isLoading = false
        })
    },
    selectCountryDetails(item) {
      const _this = this
      _this.isLoading = true
      axios
        .get('/moderatorAPI/selectCountryDetails/' + item.id)
        .then((resp) => {
          _this.selectedCountryDetails = resp.data
        })
        .catch((resp) => {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!'
          })
        })
        .finally(() => {
          _this.isLoading = false
        })
    },
    newCountryDetails() {
      this.selectedCountryDetails.push({
        country_id: this.selectedCountryDetails[0].country_id
      })
    },
    initializePage() {
      const _this = this
      axios
        .get('/moderatorAPI/showFAQ' + '?page=' + this.pagination.current)
        .then((resp) => {
          _this.pagination.current = resp.data.current_page
          _this.pagination.total = resp.data.last_page
        })
        .catch((resp) => {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!'
          })
        })
    },
    onPageChange() {
      this.initialize()
    },
    searchStore() {
      this.pagination.current = 1
      this.initialize()
    },

    editItem(item) {
      const _this = this
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.selectCountryDetails(item.country)
      this.dialog = true
    },

    close() {
      this.dialog = false
      this.error = false
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      }, 300)
    },

    save() {
      const _this = this
      this.$validator.validateAll().then((responses) => {
        if (responses) {
          const _this = this

          const updatedStoreCategory = _this.editedItem

          const data = _this.selectedCountryDetails
          /* formData.append('id', updatedStoreCategory.id)
				formData.append('name', updatedStoreCategory.name) */

          axios
            .post('/moderatorAPI/updateCountryDetails', data)
            .then((resp) => {
              if (resp.data.case === 'success') {
                _this.initialize()
                _this.close()
              } else {
                Swal.fire({
                  type: 'error',
                  title: resp.data.title,
                  text: resp.data.content
                })
              }
            })
            .catch((resp) => {
              Swal.fire({
                type: 'error',
                title: resp.data.title,
                text: resp.data.content
              })
            })
        } else {
          this.error = true
        }
      })
    },

    deleteItem(item) {
      const _this = this
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.value) {
          axios
            .delete('/moderatorAPI/deleteCountryDetails/' + item.id)
            .then((resp) => {
              if (resp.data.case === 'success') {
                Swal.fire(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
                ).finally(() => {
                  _this.close()
                })
              } else {
                Swal.fire({
                  type: 'error',
                  title: resp.data.title,
                  text: resp.data.content
                })
              }
            })
            .catch((resp) => {
              Swal.fire({
                type: 'error',
                title: 'Oops...',
                text: resp
              })
            })
        }
      })
    }
  }
}
</script>
