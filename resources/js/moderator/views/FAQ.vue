<template>
	<v-app>
		<div class="container">
			<h1>FAQ</h1>
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
						<!--<v-text-field
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
								<v-btn color="primary" dark class="mb-2" v-on="on">New FAQ</v-btn>
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
											<!--<v-col cols="12" sm="6" md="6">
												<v-text-field v-model="editedItem.question" v-validate="'required'"
																  :error-messages="errors.collect('question')" data-vv-name="question"
																  label="Question"></v-text-field>
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
																<v-text-field v-model="editedItem.question_az" v-validate="'required'"
																              :error-messages="errors.collect('question_az')"
																              data-vv-name="question_az"
																              label="Question AZ"></v-text-field>
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('answer_az')"
																          data-vv-name="answer_az"
																          v-model="editedItem.answer_az"
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
																<v-text-field v-model="editedItem.question_ru" v-validate="'required'"
																              :error-messages="errors.collect('question_az')"
																              data-vv-name="question_ru"
																              label="Question RU"></v-text-field>
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('answer_ru')"
																          data-vv-name="answer_ru"
																          v-model="editedItem.answer_ru"
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
																<v-text-field v-model="editedItem.question_en" v-validate="'required'"
																              :error-messages="errors.collect('question_en')"
																              data-vv-name="question_en"
																              label="Question EN"></v-text-field>
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('answer_en')"
																          data-vv-name="answer_en"
																          v-model="editedItem.answer_en"
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
									<v-btn color="blue darken-1" text @click="save"
									       :disabled="!editedItem.answer_az || !editedItem.answer_ru || !editedItem.answer_en">Save
									</v-btn>
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
        { text: 'Question', value: 'question_az' },
        { text: 'Actions', value: 'action', sortable: false },
      ],
      desserts: [],
      editedIndex: -1,
      editedItem: {
        id: 0,
        answer: '',
        question: '',
      },
      defaultItem: {
        id: 0,
        answer: '',
        question: '',
      },
    }),

    computed: {
      formTitle () {
        return this.editedIndex === -1 ? 'New FAQ' : 'Edit FAQ'
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
        axios.get('/moderatorAPI/showFAQ' + '?page=' + this.pagination.current).then(function (resp) {
          _this.desserts           = resp.data.data
          _this.pagination.current = resp.data.current_page
          _this.pagination.total   = resp.data.last_page
        }).catch(function (resp) {
          Swal.fire({
            type: 'error',
            title: 'Oops...',
            text: 'Something went wrong!',
            footer: '<a href>Why do I have this issue?</a>',
          })
        }).finally(() => {
          _this.isLoading = false
        })
      },
      initializePage () {
        let _this = this
        axios.get('/moderatorAPI/showFAQ' + '?page=' + this.pagination.current).
          then(function (resp) {
            _this.pagination.current = resp.data.current_page
            _this.pagination.total   = resp.data.last_page
          }).
          catch(function (resp) {
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

      editItem (item) {
        this.editedIndex = this.desserts.indexOf(item)
        this.editedItem  = Object.assign({}, item)
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
        this.$validator.validateAll().then(responses => {
          if (responses) {
            if (this.editedIndex > -1) {
              const _this = this

              let newFAQ = _this.editedItem

              let formData = new FormData()
              formData.append('id', newFAQ.id)
              formData.append('answer_az', newFAQ.answer_az)
              formData.append('answer_ru', newFAQ.answer_ru)
              formData.append('answer_en', newFAQ.answer_en)
              formData.append('question_az', newFAQ.question_az)
              formData.append('question_ru', newFAQ.question_ru)
              formData.append('question_en', newFAQ.question_en)

              axios.post('/moderatorAPI/updateFAQ', formData).then(function (resp) {
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
              const _this  = this
              let newFAQ   = _this.editedItem
              let formData = new FormData()
              formData.append('answer_az', newFAQ.answer_az)
              formData.append('answer_ru', newFAQ.answer_ru)
              formData.append('answer_en', newFAQ.answer_en)
              formData.append('question_az', newFAQ.question_az)
              formData.append('question_ru', newFAQ.question_ru)
              formData.append('question_en', newFAQ.question_en)

              axios.post('/moderatorAPI/createFAQ', formData).then((resp) => {
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
            axios.delete('/moderatorAPI/deleteFAQ/' + item.id).then(function (resp) {

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
  }
</script>
