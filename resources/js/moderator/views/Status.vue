<template>
	<v-app>
		<div class="container">
			<h1>Statuses</h1>
			<v-data-table
					  :headers="headers"
					  :items="desserts"
					  class="elevation-1"
					  loading-text="Loading... Please wait"
					  :loading=isLoading
					  hide-default-footer
					  disable-sort
					  disable-filtering
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
																<v-text-field required v-validate="'required'"
																              :error-messages="errors.collect('status_az')" data-vv-name="status_az"
																              v-model="editedItem.status_az" label="Name"></v-text-field>
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
																<v-text-field required v-validate="'required'"
																              :error-messages="errors.collect('status_ru')" data-vv-name="status_ru"
																              v-model="editedItem.status_ru" label="Name"></v-text-field>
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
																<v-text-field required v-validate="'required'"
																              :error-messages="errors.collect('status_en')" data-vv-name="status_en"
																              v-model="editedItem.status_en" label="Name"></v-text-field>
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
									<v-btn v-if="!dialogMerge" color="blue darken-1" text @click="save">Save</v-btn>
									<v-btn v-if="dialogMerge" color="blue darken-1" text @click="merge">Merge</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
						<v-dialog v-model="dialogMerge" max-width="700px">
							<v-card>
								<v-card-title>
									<span class="headline">Merge</span>
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
											<v-col cols="12" sm="6" md="4">
												<v-text-field disabled v-model="editedItem.status_az" label="ID"></v-text-field>
											</v-col>
											<v-col cols="12" sm="6" md="6">
												<v-select
														  v-model="editedItem.category"
														  :items="desserts"
														  item-text="status_az"
														  item-value="id"
														  label="Category"
												>
												</v-select>
											</v-col>
											<!--<v-col cols="12" sm="6" md="4">
												<v-text-field required v-validate="'required'"
																  :error-messages="errors.collect('name')" data-vv-name="name"
																  v-model="editedItem.name" label="Name"></v-text-field>
											</v-col>-->
										</v-row>
									</v-container>
								</v-card-text>
								
								<v-card-actions>
									<div class="flex-grow-1"></div>
									<v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
									<v-btn color="blue darken-1" text @click="merge">Merge</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
						<!--<v-dialog v-model="dialogMerge" max-width="700px">
							<v-card>
								<v-card-title>
									<span class="headline">Merge</span>
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
											&lt;!&ndash;<v-col cols="12" sm="6" md="4">
												<v-text-field required v-validate="'required'"
																  :error-messages="errors.collect('name')" data-vv-name="name"
																  v-model="editedItem.name" label="Name"></v-text-field>
											</v-col>&ndash;&gt;
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
																<v-text-field required v-validate="'required'"
																              :error-messages="errors.collect('status_az')" data-vv-name="status_az"
																              v-model="editedItem.status_az" label="Name"></v-text-field>
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
																<v-text-field required v-validate="'required'"
																              :error-messages="errors.collect('status_ru')" data-vv-name="status_ru"
																              v-model="editedItem.status_ru" label="Name"></v-text-field>
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
																<v-text-field required v-validate="'required'"
																              :error-messages="errors.collect('status_en')" data-vv-name="status_en"
																              v-model="editedItem.status_en" label="Name"></v-text-field>
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
						</v-dialog>-->
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
      validator: 'new',
    },
    name         : 'Status',
    data         : () => ({
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
      search      : '',
      dialog      : false,
      dialogMerge : false,
      headers     : [
        {
          text : 'ID',
          align: 'left',
          value: 'id',
        },
        { text: 'Status', value: 'status_en' },
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
    },

    created () {
      this.initialize()
    },

    methods: {
      initialize () {
        let _this       = this
        _this.isLoading = true
        axios.get('/moderatorAPI/showStatus' + '?name=' + this.search.name + '&page=' + this.pagination.current).then((resp) => {
          _this.desserts           = resp.data.data
          _this.pagination.current = resp.data.current_page
          _this.pagination.total   = resp.data.last_page
        }).catch((resp) => {
          Swal.fire({
            type  : 'error',
            title : 'Oops...',
            text  : 'Something went wrong!',
          })
        }).finally(() => {
          _this.isLoading = false
        })
      },
      initializePage () {
        let _this = this
        axios.get('/moderatorAPI/showFAQ' + '?page=' + this.pagination.current).then((resp) => {
          _this.pagination.current = resp.data.current_page
          _this.pagination.total   = resp.data.last_page
        }).catch((resp) => {
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
        this.dialog      = true

      },
      mergeItem (item) {
        let _this        = this
        this.editedIndex = this.desserts.indexOf(item)
        this.editedItem  = Object.assign({}, item)
        this.dialogMerge = true
      },

      close () {
        this.dialog = false
        this.dialogMerge = false
        this.error  = false
        setTimeout(() => {
          this.editedItem  = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        }, 300)
      },

      save () {
        let _this = this
        this.$validator.validateAll().then(responses => {
          if (responses) {
            if (this.editedIndex > -1) {
              const _this = this

              let updatedStoreCategory = _this.editedItem

              let formData = new FormData()
              formData.append('id', updatedStoreCategory.id)
              formData.append('status_az', updatedStoreCategory.status_az)
              formData.append('status_ru', updatedStoreCategory.status_ru)
              formData.append('status_en', updatedStoreCategory.status_en)

              axios.post('/moderatorAPI/updateStatus', formData).then((resp) => {
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
              }).catch((resp) => {
                Swal.fire({
                  type : 'error',
                  title: resp.data.title,
                  text : resp.data.content,
                })
              })
            } /*else {
              const _this          = this
              let newStoreCategory = _this.editedItem

              let formData = new FormData()
              formData.append('status_az', newStoreCategory.status_az)
              formData.append('status_ru', newStoreCategory.status_ru)
              formData.append('status_en', newStoreCategory.status_en)

              axios.post('/moderatorAPI/createCategory', formData).then((resp) => {
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
              }).catch((resp) => {
                Swal.fire({
                  type : 'error',
                  title: 'Oops...',
                  text : resp.content,
                })
              })
            }*/
          } else {
            this.error = true
          }
        })
      },

      merge(){
        let _this = this
        if (this.editedItem.id && this.editedItem.category) {
          const _this          = this
          let newStoreCategory = _this.editedItem

          let formData = new FormData()
          formData.append('id', newStoreCategory.id)
          formData.append('category_id', newStoreCategory.category)

          axios.post('/moderatorAPI/mergeCategory', formData).then((resp) => {
            if (resp.data.case === 'success') {
              _this.initialize()
              _this.close()
              Swal.fire({
                type : 'Success',
                title: 'Merged/Deleted',
                text : resp.data.content,
              })
            } else {
              Swal.fire({
                type : 'error',
                title: resp.data.title,
                text : resp.data.content,
              })
            }
          }).catch((resp) => {
            Swal.fire({
              type : 'error',
              title: 'Oops...',
              text : resp.content,
            })
          })
        } else {
          this.error = true
        }
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
        }).then((result) => {
          if (result.value) {
            axios.delete('/moderatorAPI/deleteCategory/' + item.id).then((resp) => {

              if (resp.data.case === 'success') {
                let old = _this.pagination.current
                _this.initializePage()
                Swal.fire(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success',
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
                  type : 'error',
                  title: resp.data.title,
                  text : resp.data.content,
                })
              }

            }).catch((resp) => {
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
