<template>
  <v-app>
    <div class="container">
      <h1>{{ $route.params.group }}</h1>
      <v-data-table
          :headers="headers"
          :items="desserts"
          disable-sort
          disable-filtering
          class="elevation-1"
          loading-text="Loading... Please wait"
          :loading=isLoading
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
            <v-divider
                class="mx-4"
                inset
                vertical
            ></v-divider>
            <!-- <div class="flex-grow-1"></div> -->

            <v-dialog v-model="dialog" max-width="700px">
              <!--<template v-slot:activator="{ on }">
                <v-btn color="primary" dark class="mb-2" v-on="on">New Store Category</v-btn>
              </template>-->
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
                        <v-text-field disabled v-model="editedItem.id" label="ID"></v-text-field>
                      </v-col>
                      <!--<v-col cols="12" sm="6" md="4">
                        <v-text-field required v-validate="'required'"
                                  :error-messages="errors.collect('name')" data-vv-name="name"
                                  v-model="editedItem.name" label="Name"></v-text-field>
                      </v-col>-->
                      <template>
                        <v-tabs
                            v-model="tab"
                            background-color="deep-purple accent-4"
                            class="elevation-2"
                            dark
                            :centered="true"
                            :grow="true"
                        >
                          <v-tabs-slider></v-tabs-slider>

                          <v-tab
                              key="az"
                              :href="`#tab-az`"
                          >
                            AZ
                          </v-tab>
                          <v-tab
                              key="ru"
                              :href="`#tab-ru`"
                          >
                            RU
                          </v-tab>
                          <v-tab
                              key="en"
                              :href="`#tab-en`"
                          >
                            EN
                          </v-tab>

                          <v-tab-item
                              key="az"
                              :value="'tab-az'"
                          >
                            <v-card
                                flat
                                tile
                            >
                              <v-card-text>
                                <ckeditor required v-validate="'required'"
                                          :error-messages="errors.collect('name_az')" data-vv-name="name_az"
                                          v-model="lang.az" label="Name"></ckeditor>
                              </v-card-text>
                            </v-card>
                          </v-tab-item>
                          <v-tab-item
                              key="ru"
                              :value="'tab-ru'"
                          >
                            <v-card
                                flat
                                tile
                            >
                              <v-card-text>
                                <ckeditor required v-validate="'required'"
                                          :error-messages="errors.collect('name_ru')" data-vv-name="name_ru"
                                          v-model="lang.ru" label="Name"></ckeditor>
                              </v-card-text>
                            </v-card>
                          </v-tab-item>
                          <v-tab-item
                              key="en"
                              :value="'tab-en'"
                          >
                            <v-card
                                flat
                                tile
                            >
                              <v-card-text>
                                <ckeditor required v-validate="'required'"
                                          :error-messages="errors.collect('name_en')" data-vv-name="name_en"
                                          v-model="lang.en" label="Name"></ckeditor>
                              </v-card-text>
                            </v-card>
                          </v-tab-item>
                        </v-tabs>
                      </template>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <div class="flex-grow-1"></div>
                  <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                  <v-btn color="blue darken-1" text @click="save">Save</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-toolbar>

        </template>
        <template v-slot:item.action="{ item }">
          <v-icon
              small
              class="mr-2"
              @click="editItem(item)"
          >
            edit
          </v-icon>
          <!--<v-icon
                small
                @click="deleteItem(item)"
          >
            delete
          </v-icon>-->
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
  name         : 'LanguageMenu',
  $_veeValidate: {
    validator: 'new',
  },
  data         : () => ({
    lang        : [],
    tabs        : 3,
    tab         : null,
    pagination  : {
      current: 1,
      total  : 0,
    },
    circle      : true,
    nextIcon    : 'navigate_next',
    prevIcon    : 'navigate_before',
    totalVisible: 5,
    error       : false,
    isLoading   : true,
    search      : { name: '' },
    dialog      : false,
    headers     : [
      {
        text : 'ID',
        align: 'left',
        value: 'id',
      },
      { text: 'Key', value: 'key' },
      { text: 'Actions', value: 'action', sortable: false },
    ],
    desserts    : [],
    editedIndex : -1,
    editedItem  : {},
    defaultItem : {},
  }),

  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'New Store Category' : 'Edit Store Category'
    },
  },

  watch: {
    dialog (val) {
      val || this.close()
    },
    /*'$route.params.group': function (id) {
      this.initialize()
    }*/
  },

  mounted () {
    this.initialize()
  },

  methods: {
    initialize () {
      let _this       = this
      _this.isLoading = true
      axios.get('/moderatorAPI/showLanguageTemplate' + '?name=' + this.search.name + '&lang=' + this.$route.params.group + '&page=' + this.pagination.current)
           .then((resp) => {
             _this.desserts           = resp.data.data
             _this.pagination.current = resp.data.current_page
             _this.pagination.total   = resp.data.last_page
           })
           .catch((resp) => {
             Swal.fire({
               type  : 'error',
               title : 'Oops...',
               text  : 'Something went wrong!',
               footer: '<a href>Why do I have this issue?</a>',
             })
           })
           .finally(() => {
             _this.isLoading = false
           })
    },
    initializePage () {
      let _this = this
      axios.get('/moderatorAPI/showFAQ' + '?page=' + this.pagination.current)
           .then((resp) => {
             _this.pagination.current = resp.data.current_page
             _this.pagination.total   = resp.data.last_page
           })
           .catch((resp) => {
             Swal.fire({
               type : 'error',
               title: 'Oops...',
               text : 'Something went wrong!',
             })
           })
    },
    onPageChange () {
      this.initialize()
    },
    searchStore () {
      this.pagination.current = 1
      this.initialize()
    },

    editItem (item) {
      let _this        = this
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem  = Object.assign({}, item)
      this.lang        = this.editedItem.text
      this.dialog      = true
    },

    close () {
      this.dialog = false
      this.error  = false
      setTimeout(() => {
        this.editedItem  = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      }, 300)
    },

    save () {
      let _this = this
      this.$validator.validateAll()
          .then(responses => {
            if (responses) {
              if (this.editedIndex > -1) {
                const _this = this

                let updatedLang = _this.editedItem

                let formData = new FormData()
                formData.append('group', updatedLang.group)
                formData.append('key', updatedLang.key)
                formData.append('lang', JSON.stringify(this.lang))

                axios.post('/moderatorAPI/updateLanguageMenu', formData)
                     .then((resp) => {
                       if (resp.data.case === 'success') {
                         _this.initialize()
                         _this.close()
                       } else {
                         Swal.fire({
                           type : 'error',
                           title: resp.data.title,
                           text : resp.data.content,
                         })
                       }
                     })
                     .catch((resp) => {
                       Swal.fire({
                         type : 'error',
                         title: resp.data.title,
                         text : resp.data.content,
                       })
                     })
              } else {
                const _this          = this
                let newStoreCategory = _this.editedItem

                let formData = new FormData()
                formData.append('name_az', newStoreCategory.name_az)
                formData.append('name_ru', newStoreCategory.name_ru)
                formData.append('name_en', newStoreCategory.name_en)

                axios.post('/moderatorAPI/createStoreCategory', formData)
                     .then((resp) => {
                       if (resp.data.case === 'success') {
                         _this.initialize()
                         _this.close()
                       } else {
                         Swal.fire({
                           type : 'error',
                           title: resp.data.title,
                           text : resp.data.content,
                         })
                       }
                     })
                     .catch((resp) => {
                       Swal.fire({
                         type : 'error',
                         title: 'Oops...',
                         text : resp.content,
                       })
                     })
              }
            } else {
              this.error = true
            }
          })
    },

    deleteItem (item) {
      const index = this.desserts.indexOf(item)
      let _this   = this
      Swal.fire({
        title             : 'Are you sure?',
        text              : 'You won\'t be able to revert this!',
        type              : 'warning',
        showCancelButton  : true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor : '#d33',
        confirmButtonText : 'Yes, delete it!',
      })
          .then((result) => {
            if (result.value) {
              axios.delete('/moderatorAPI/deleteStoreCategory/' + item.id)
                   .then((resp) => {

                     if (resp.data.case === 'success') {
                       let old = _this.pagination.current
                       _this.initializePage()
                       Swal.fire(
                           'Deleted!',
                           'Your file has been deleted.',
                           'success',
                       )
                           .finally(() => {
                             if (old >= _this.pagination.total) {
                               _this.pagination.current = _this.pagination.total
                             } else {
                               _this.pagination.current = old
                             }
                             _this.initialize()
                           })
                     } else {
                       Swal.fire({
                         type : 'error',
                         title: resp.data.title,
                         text : resp.data.content,
                       })
                     }

                   })
                   .catch((resp) => {
                     Swal.fire({
                       type : 'error',
                       title: 'Oops...',
                       text : resp,
                     })
                   })

            }
          })
    },
  },
}
</script>
