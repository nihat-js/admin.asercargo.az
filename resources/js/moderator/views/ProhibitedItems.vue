<template>
	<v-app>
		<div class="container">
			<h1>Prohibited Items</h1>
			<v-data-table
					:headers="headers"
					:items="desserts"
					:search="search"
					disable-sort
					disable-filtering
					class="elevation-1"
					loading-text="Loading... Please wait"
					:loading=isLoading
					:hide-default-footer="true"
					dark
			>
				<template v-slot:top>
					<v-toolbar flat>
						<!--	<v-text-field
									v-model="search"
									label="Search"
									single-line
									hide-details
							></v-text-field>
							<v-divider
									class="mx-4"
									inset
									vertical
							></v-divider>-->
						<div class="flex-grow-1"></div>

						<v-dialog class="zIndexModal" v-model="dialog" max-width="800px">
							<template v-slot:activator="{ on }">
								<v-btn color="primary" dark class="mb-2" v-on="on">New Prohibited Item</v-btn>
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
											<v-col cols="12" sm="6" md="6">
												<v-text-field disabled v-model="editedItem.id" label="ID"></v-text-field>
											</v-col>
											<v-col cols="12" sm="6" md="6">
												<!--<v-text-field v-model="editedItem.country" v-validate="'required'"
																  :error-messages="errors.collect('country')" data-vv-name="country"
																  label="Country"></v-text-field>-->
												<v-select
														v-validate="'required'"
														:error-messages="errors.collect('country')" data-vv-name="country"
														:items="country"
														item-text="name_az"
														item-value="id"
														v-model="editedItem.country_id"
														label="country"
												></v-select>
											</v-col>
											<!--<v-text-field required v-validate="'required'"
															  :error-messages="errors.collect('answer')" data-vv-name="answer"
															  v-model="editedItem.answer" label="Answer"></v-text-field>-->
											<!--<ckeditor v-model="editedItem.item"
														 ></ckeditor>-->
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
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('item_az')"
																          data-vv-name="item_az"
																          v-model="editedItem.item_az"
																          ></ckeditor>
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
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('item_ru')"
																          data-vv-name="item_ru"
																          v-model="editedItem.item_ru"
																          ></ckeditor>
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
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('item_en')"
																          data-vv-name="item_en"
																          v-model="editedItem.item_en"
																          ></ckeditor>
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
					<v-icon
							small
							@click="deleteItem(item)"
					>
						delete
					</v-icon>
				</template>
				<template v-slot:no-data>
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
  import Swal          from 'sweetalert2'

  export default {
    $_veeValidate: {
      validator: 'new',
    },
    components: {},
    props: {
      'country': {
        type: Array,
      },
    },
    data: () => ({
      tab: null,
      pagination: {
        current: 1,
        total: 0,
      },
      circle: true,
      nextIcon: 'navigate_next',
      prevIcon: 'navigate_before',
      totalVisible: 5,
      error: false,
      isLoading: true,
      search: '',
      dialog: false,
      headers: [
        {
          text: 'ID',
          align: 'left',
          value: 'id',
        },
        { text: 'Country', value: 'country.name_az' },
        { text: 'Actions', value: 'action', sortable: false },
      ],
      desserts: [],
      editedIndex: -1,
      editedItem: {
        id: 0,
        answer_az: '',
        answer_ru: '',
        answer_en: '',
        question_az: '',
        question_ru: '',
        question_en: '',
      },
      defaultItem: {
        id: 0,
        answer_az: '',
        answer_ru: '',
        answer_en: '',
        question_az: '',
        question_ru: '',
        question_en: '',
      },
    }),

    computed: {
      formTitle () {
        return this.editedIndex === -1 ? 'New Prohibited Item' : 'Edit Prohibited Item'
      },
    },

    watch: {
      dialog (val) {
        val || this.close()
      },
    },

    created () {
      this.initialize()
    },

    methods: {
      initialize () {
        let app       = this
        app.isLoading = true
        axios.get('/moderatorAPI/showProhibitedItem' + '?page=' + this.pagination.current).then((resp) => {
          app.desserts           = resp.data.data
          app.pagination.current = resp.data.current_page
          app.pagination.total   = resp.data.last_page
        }).catch((resp) => {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!',
          })
        }).finally(() => {
          app.isLoading = false
        })
      },
      initializePage () {
        let app = this
        axios.get('/moderatorAPI/showProhibitedItem' + '?page=' + this.pagination.current).
          then((resp) => {
            app.pagination.current = resp.data.current_page
            app.pagination.total   = resp.data.last_page
          }).
          catch((resp) => {
            Swal.fire({
              type: 'error',
              title: 'Oops...',
              text: 'Something went wrong!',
            })
          })
      },
      onPageChange () {
        this.initialize()
      },
      close () {
        this.dialog = false
        this.error  = false
        setTimeout(() => {
          this.editedItem  = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        }, 300)
      },

      editItem (item) {
        this.editedIndex = this.desserts.indexOf(item)
        this.editedItem  = Object.assign({}, item)
        this.dialog      = true
      },
      save () {
        let _this = this
        this.$validator.validateAll().then(responses => {
          if (responses) {
            if (this.editedIndex > -1) {
              const _this = this

              let newProhibitedItem = _this.editedItem

              let formData = new FormData()
              formData.append('id', newProhibitedItem.id)
              formData.append('country_id', newProhibitedItem.country_id)
              formData.append('item_az', (newProhibitedItem.item_az).replace(/<ul[^>]*>|<ol[^>]*>/g,'<al>').replace(/<\/ul>|<\/ol>/g,'</al>'))
              formData.append('item_ru', (newProhibitedItem.item_ru).replace(/<ul[^>]*>|<ol[^>]*>/g,'<al>').replace(/<\/ul>|<\/ol>/g,'</al>'))
              formData.append('item_en', (newProhibitedItem.item_en).replace(/<ul[^>]*>|<ol[^>]*>/g,'<al>').replace(/<\/ul>|<\/ol>/g,'</al>'))

              axios.post('/moderatorAPI/updateProhibitedItem', formData).then(function (resp) {
                if (resp.data.case === 'success') {
                  _this.initialize()
                  _this.close()
                }
                else {
                  Swal.fire({
                    type: 'error',
                    title: resp.data.title,
                    text: resp.data.content,
                  })
                }
              }).catch(function (resp) {
                Swal.fire({
                  type: 'error',
                  title: resp.data.title,
                  text: resp.data.content,
                })
              })
            }
            else {
              const _this           = this
              let newProhibitedItem = _this.editedItem
              let formData          = new FormData()
              formData.append('country_id', newProhibitedItem.country_id)
              formData.append('item_az', (newProhibitedItem.item_az).replace(/<ul[^>]*>|<ol[^>]*>/g,'<al>').replace(/<\/ul>|<\/ol>/g,'</al>'))
              formData.append('item_ru', (newProhibitedItem.item_ru).replace(/<ul[^>]*>|<ol[^>]*>/g,'<al>').replace(/<\/ul>|<\/ol>/g,'</al>'))
              formData.append('item_en', (newProhibitedItem.item_en).replace(/<ul[^>]*>|<ol[^>]*>/g,'<al>').replace(/<\/ul>|<\/ol>/g,'</al>'))

              axios.post('/moderatorAPI/createProhibitedItem', formData).then((resp) => {
                if (resp.data.case === 'success') {
                  _this.initialize()
                  _this.close()
                }
                else {
                  Swal.fire({
                    type: 'error',
                    title: resp.data.title,
                    text: resp.data.content,
                  })
                }
              }).catch(function (resp) {
                Swal.fire({
                  type: 'error',
                  title: 'Oops...',
                  text: resp.content,
                })
              })
            }
          }
          else {
            this.error = true
          }
        })
      },
      deleteItem (item) {
        const index = this.desserts.indexOf(item)
        let app     = this
        Swal.fire({
          title: 'Are you sure?',
          text: 'You won\'t be able to revert this!',
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
          if (result.value) {
            axios.delete('/moderatorAPI/deleteProhibitedItem/' + item.id).then(function (resp) {

              if (resp.data.case === 'success') {
                let old = app.pagination.current
                app.initializePage()
                Swal.fire(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success',
                ).finally(() => {
                  if (old >= app.pagination.total) {
                    app.pagination.current = app.pagination.total
                  }
                  else {
                    app.pagination.current = old
                  }
                  app.initialize()
                })
              }
              else {
                Swal.fire({
                  type: 'error',
                  title: resp.data.title,
                  text: resp.data.content,
                })
              }

            }).catch(function (resp) {
              Swal.fire({
                type: 'error',
                title: 'Oops...',
                text: resp,
              })
            })

          }
        })
      },
    },
    mounted () {
    },
  }
</script>
