<template>
  <v-app>
    <div class="container">
      <h1>Store</h1>
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

            <v-dialog v-model="dialog" max-width="700px">
              <template v-slot:activator="{ on }">
                <v-btn color="primary" dark class="mb-2" v-on="on"
                  >New Store</v-btn
                >
              </template>
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
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          disabled
                          v-model="editedItem.id"
                          label="ID"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-textarea
                          rows="1"
                          auto-grow
                          clearable
                          v-model="editedItem.name"
                          v-validate="'required'"
                          :error-messages="errors.collect('name')"
                          data-vv-name="name"
                          label="Name"
                        ></v-textarea>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          required
                          v-validate="'required'"
                          :error-messages="errors.collect('title')"
                          data-vv-name="title"
                          v-model="editedItem.title"
                          label="Title"
                        ></v-text-field>
                      </v-col>

                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          v-model="editedItem.url"
                          label="Url"
                          v-validate="''"
                          :error-messages="errors.collect('url')"
                          data-vv-name="url"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-file-input id="file" label="Img_src"></v-file-input>
                      </v-col>

                      <v-col cols="12" sm="6" md="6">
                        <v-select
                          v-validate="'required'"
                          :error-messages="errors.collect('category')"
                          data-vv-name="category"
                          v-model="editedItem.category"
                          :items="category"
                          item-text="name_az"
                          item-value="id"
                          label="Category"
                          multiple
                          chips
                          deletable-chips
                        >
                        </v-select>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-select
                          v-validate="'required'"
                          :error-messages="errors.collect('country')"
                          data-vv-name="country"
                          v-model="editedItem.country"
                          :items="country"
                          item-text="name_az"
                          item-value="id"
                          label="Country"
                          multiple
                          chips
                          deletable-chips
                        >
                        </v-select>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <div class="flex-grow-1"></div>
                  <v-btn color="blue darken-1" text @click="close"
                    >Cancel</v-btn
                  >
                  <v-btn
                    color="blue darken-1"
                    :loading="loadingButton"
                    text
                    @click="save"
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-toolbar>
        </template>
        <template v-slot:item.img="{ item }">
          <td class="td_image">
            <img
              alt="image"
              :src="item.img"
              @click="show_image(item.img)"
              style="width: 50px; height: 50px;"
            />
          </td>
        </template>

        <template v-slot:item.country="{ item }">
          <span v-for="country in item.country" :key="country.name">
            {{ country.name }}
            <hr
          /></span>
        </template>
        <template v-slot:item.category="{ item }">
          <span v-for="category in item.category" :key="category.name">
            {{ category.name }}
            <hr
          /></span>
        </template>
        <template v-slot:item.action="{ item }">
          <v-icon small class="mr-2" @click="editItem(item)">
            edit
          </v-icon>
          <v-icon small @click="deleteItem(item)">
            delete
          </v-icon>
        </template>
        <template v-slot:item.check="{ item }">
          <v-switch
            @change="changeCheck(item.id, !item.has_site)"
            :input-value="!!item.has_site"
          ></v-switch>
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
      <template v-if="image">
        <div class="overlay"></div>
        <i class="material-icons image_close" @click="close_image">
          close
        </i>
        <v-row class="image_popup" align="center" justify="center">
          <v-img
            :src="image_src"
            :lazy-src="image_src"
            aspect-ratio="1"
            max-width="80%"
            max-height="80%"
          ></v-img>
        </v-row>
      </template>
    </div>
    <v-dialog v-model="loading" fullscreen>
      <v-container
        fluid
        fill-height
        style="background-color: rgba(255, 69, 0, 0.9);"
      >
        <v-layout justify-center align-center>
          <v-progress-circular indeterminate color="primary">
          </v-progress-circular>
        </v-layout>
      </v-container>
    </v-dialog>
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
    loading: false,
    loadingButton: false,
    search: { name: '' },
    dialog: false,
    headers: [
      {
        text: 'ID',
        align: 'left',
        value: 'id'
      },
      { text: 'Name', value: 'name' },
      { text: 'Title', value: 'title' },
      { text: 'Url', value: 'url' },
      { text: 'Img', value: 'img', align: 'center' },
      { text: 'Category', value: 'category' },
      { text: 'Country', value: 'country' },
      { text: 'Has Site', value: 'check' },
      { text: 'Actions', value: 'action', sortable: false }
    ],
    desserts: [],
    editedIndex: -1,
    editedItem: {
      id: 0,
      answer: '',
      question: ''
    },
    defaultItem: {
      id: 0,
      answer: '',
      question: ''
    },
    image: false,
    image_src: '',
    file: [],
    country: [],
    category: [],
    editedCountryID: [],
    editedCategoryID: []
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'New Store' : 'Edit Store'
    }
  },

  watch: {
    dialog(val) {
      val || this.close()
    }
  },

  created() {
    this.initialize()
    this.getCategory()
    this.getCountry()
  },

  methods: {
    initialize() {
      const _this = this
      _this.isLoading = true
      axios
        .get(
          '/moderatorAPI/showStore' +
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
    initializePage() {
      const _this = this
      axios
        .get('/moderatorAPI/showStore' + '?page=' + this.pagination.current)
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

      _this.editedCountryID = []
      for (const country of _this.editedItem.country) {
        if (!country.id) {
          continue
        }
        _this.editedCountryID.push(country.id)
      }
      _this.editedCategoryID = []
      for (const category of _this.editedItem.category) {
        if (!category.id) {
          continue
        }
        _this.editedCategoryID.push(category.id)
      }

      this.dialog = true
    },

    close() {
      this.dialog = false
      this.error = false
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      }, 300)
      document.getElementById('file').value = ''
    },

    save() {
      this.loadingButton = true
      this.$validator.validateAll().then((responses) => {
        if (responses) {
          if (this.editedIndex > -1) {
            const _this = this

            const updatedStore = _this.editedItem

            let category = updatedStore.category
            if (typeof category[0] === 'object') {
              category = _this.editedCategoryID
            }
            let country = updatedStore.country
            if (typeof country[0] === 'object') {
              country = _this.editedCountryID
            }

            const formData = new FormData()
            formData.append('file', document.getElementById('file').files[0])
            formData.append('id', updatedStore.id)
            formData.append('name', updatedStore.name)
            formData.append('title', updatedStore.title)
            formData.append('url', updatedStore.url)
            formData.append('country_id', JSON.stringify(country))
            formData.append('category_id', JSON.stringify(category))

            axios
              .post('/moderatorAPI/updateStore', formData)
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
              .finally(() => {
                this.loadingButton = false
              })
          } else {
            const _this = this
            const newStore = _this.editedItem

            const formData = new FormData()
            formData.append(
              'file',
              document.getElementById('file').files[0] ?? null
            )
            formData.append('name', newStore.name)
            formData.append('title', newStore.title)
            formData.append('url', newStore.url)
            formData.append('country_id', JSON.stringify(newStore.country))
            formData.append('category_id', JSON.stringify(newStore.category))

            axios
              .post('/moderatorAPI/createStore', formData)
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
                  title: 'Oops...',
                  text: resp.content
                })
              })
              .finally(() => {
                this.loadingButton = false
              })
          }
        } else {
          this.error = true
          this.loadingButton = false
        }
      })
    },

    getCategory() {
      const _this = this
      axios
        .get('/moderatorAPI/getCategories')
        .then(function (resp) {
          _this.category = resp.data
        })
        .catch(function (resp) {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!'
          })
        })
    },

    getCountry() {
      const _this = this
      axios
        .get('/moderatorAPI/getCountries')
        .then(function (resp) {
          _this.country = resp.data
        })
        .catch(function (resp) {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!'
          })
        })
    },

    deleteItem(item) {
      // const index = this.desserts.indexOf(item)
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
            .delete('/moderatorAPI/deleteStore/' + item.id)
            .then((resp) => {
              if (resp.data.case === 'success') {
                const old = _this.pagination.current
                _this.initializePage()
                Swal.fire(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
                ).finally(() => {
                  if (old >= _this.pagination.total) {
                    _this.pagination.current = _this.pagination.total
                  } else {
                    _this.pagination.current = old
                  }

                  _this.initialize()
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
    },
    show_image(src) {
      this.image_src = src
      this.image = true
    },
    close_image() {
      this.image = false
    },
    changeCheck(id, val) {
      this.loading = true
      axios
        .post('/moderatorAPI/changeCheck/' + id, { val: val })
        .then((resp) => {
          if (resp.data.case === 'success') {
            this.initialize()
          } else {
            console.error(resp)
          }
        })
        .catch((resp) => {
          console.error(resp)
        })
        .finally(() => {
          this.loading = false
        })
    }
  }
}
</script>
