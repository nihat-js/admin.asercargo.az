<template>
	<v-app>
		<div class="container">
			<h1>Mail/SMS Template</h1>
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
							<!--<template v-slot:activator="{ on }">
								<v-btn color="primary" dark class="mb-2" v-on="on">New FAQ</v-btn>
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
											<v-col cols="12" sm="6" md="6">
												<v-text-field disabled v-model="editedItem.id" label="ID"></v-text-field>
											</v-col>
											<v-col cols="12" sm="6" md="6">
												<v-text-field disabled v-model="editedItem.type" label="Type"></v-text-field>
											</v-col>
											
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
																<v-text-field v-model="editedItem.title_az" v-validate="'required'"
																              :error-messages="errors.collect('title_az')"
																              data-vv-name="title_az"
																              label="Title"></v-text-field>
																<v-text-field v-model="editedItem.subject_az" v-validate="'required'"
																              :error-messages="errors.collect('subject_az')"
																              data-vv-name="subject_az"
																              label="Subject"></v-text-field>
																<v-text-field v-model="editedItem.button_name_az" v-validate="''"
																              :error-messages="errors.collect('button_name_az')"
																              data-vv-name="button_name_az"
																              label="Button Name"></v-text-field>
																<v-text-field v-model="editedItem.sms_az" v-validate="''"
																              :error-messages="errors.collect('sms_az')"
																              data-vv-name="sms_az"
																              label="SMS"></v-text-field>
																<h3>Content</h3>
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('content_az')"
																          data-vv-name="content_az"
																          v-model="editedItem.content_az"
																          ></ckeditor>
																<h3>Content Bottom</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('content_bottom_az')"
																          data-vv-name="content_bottom_az"
																          v-model="editedItem.content_bottom_az"
																          ></ckeditor>
																<h3>List Inside</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('list_inside_az')"
																          data-vv-name="list_inside_az"
																          v-model="editedItem.list_inside_az"
																          ></ckeditor>
																<h3>Push Content</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('push_content_az')"
																          data-vv-name="push_content_az"
																          v-model="editedItem.push_content_az"
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
																<v-text-field v-model="editedItem.title_ru" v-validate="'required'"
																              :error-messages="errors.collect('title_ru')"
																              data-vv-name="title_ru"
																              label="Title"></v-text-field>
																<v-text-field v-model="editedItem.subject_ru" v-validate="'required'"
																              :error-messages="errors.collect('subject_ru')"
																              data-vv-name="subject_ru"
																              label="Subject"></v-text-field>
																<v-text-field v-model="editedItem.button_name_ru" v-validate="''"
																              :error-messages="errors.collect('button_name_ru')"
																              data-vv-name="button_name_ru"
																              label="Button Name"></v-text-field>
																<v-text-field v-model="editedItem.sms_ru" v-validate="''"
																              :error-messages="errors.collect('sms_ru')"
																              data-vv-name="sms_ru"
																              label="SMS"></v-text-field>
																<h3>Content</h3>
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('content_ru')"
																          data-vv-name="content_ru"
																          v-model="editedItem.content_ru"
																          ></ckeditor>
																<h3>Content Bottom</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('content_bottom_ru')"
																          data-vv-name="content_bottom_ru"
																          v-model="editedItem.content_bottom_ru"
																          ></ckeditor>
																<h3>List Inside</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('list_inside_ru')"
																          data-vv-name="list_inside_ru"
																          v-model="editedItem.list_inside_ru"
																          ></ckeditor>
																<h3>Push Content</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('push_content_ru')"
																          data-vv-name="push_content_ru"
																          v-model="editedItem.push_content_ru"
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
																<v-text-field v-model="editedItem.title_en" v-validate="'required'"
																              :error-messages="errors.collect('title_en')"
																              data-vv-name="title_en"
																              label="Title"></v-text-field>
																<v-text-field v-model="editedItem.subject_en" v-validate="'required'"
																              :error-messages="errors.collect('subject_en')"
																              data-vv-name="subject_en"
																              label="Subject"></v-text-field>
																<v-text-field v-model="editedItem.button_name_en" v-validate="''"
																              :error-messages="errors.collect('button_name_en')"
																              data-vv-name="button_name_en"
																              label="Button Name"></v-text-field>
																<v-text-field v-model="editedItem.sms_en" v-validate="''"
																              :error-messages="errors.collect('sms_en')"
																              data-vv-name="sms_en"
																              label="SMS"></v-text-field>
																<h3>Content</h3>
																<ckeditor v-validate="'required'"
																          :error-messages="errors.collect('content_en')"
																          data-vv-name="content_en"
																          v-model="editedItem.content_en"
																          ></ckeditor>
																<h3>Content Bottom</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('content_bottom_en')"
																          data-vv-name="content_bottom_en"
																          v-model="editedItem.content_bottom_en"
																          ></ckeditor>
																<h3>Push Content</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('push_content_en')"
																          data-vv-name="push_content_en"
																          v-model="editedItem.push_content_en"
																          ></ckeditor>
																<h3>List Inside</h3>
																<ckeditor v-validate="''"
																          :error-messages="errors.collect('list_inside_en')"
																          data-vv-name="list_inside_en"
																          v-model="editedItem.list_inside_en"
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
									       :disabled="disableSave">Save
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
					<!--<v-icon
							  small
							  @click="deleteItem(item)"
					>
						delete
					</v-icon>-->
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
    name:'MailSMSTemplate',
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
        { text: 'Type', value: 'type' },
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
        return this.editedIndex === -1 ? 'New Mail/SMS Template' : 'Edit Mail/SMS Template'
      },
      disableSave(){
        return !(!!this.editedItem.title_az && !!this.editedItem.title_ru && !!this.editedItem.title_en && !!this.editedItem.subject_az && !!this.editedItem.subject_ru && !!this.editedItem.subject_en && !!this.editedItem.content_az && !!this.editedItem.content_ru && !!this.editedItem.content_en)
      }
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
        axios.get('/moderatorAPI/showMailSMSTemplate' + '?page=' + this.pagination.current).then(function (resp) {
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
        console.log(this.editedItem)
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
              formData.append('title_az', newFAQ.title_az)
              formData.append('title_ru', newFAQ.title_ru)
              formData.append('title_en', newFAQ.title_en)
              formData.append('subject_az', newFAQ.subject_az)
              formData.append('subject_ru', newFAQ.subject_ru)
              formData.append('subject_en', newFAQ.subject_en)
              formData.append('content_az', newFAQ.content_az)
              formData.append('content_ru', newFAQ.content_ru)
              formData.append('content_en', newFAQ.content_en)
              formData.append('list_inside_az', newFAQ.list_inside_az)
              formData.append('list_inside_ru', newFAQ.list_inside_ru)
              formData.append('list_inside_en', newFAQ.list_inside_en)
              formData.append('content_bottom_az', newFAQ.content_bottom_az)
              formData.append('content_bottom_ru', newFAQ.content_bottom_ru)
              formData.append('content_bottom_en', newFAQ.content_bottom_en)
              formData.append('button_name_az', newFAQ.button_name_az)
              formData.append('button_name_ru', newFAQ.button_name_ru)
              formData.append('button_name_en', newFAQ.button_name_en)
              formData.append('sms_az', newFAQ.sms_az)
              formData.append('sms_ru', newFAQ.sms_ru)
              formData.append('sms_en', newFAQ.sms_en)
			  formData.append('push_content_az', newFAQ.push_content_az)
			  formData.append('push_content_ru', newFAQ.push_content_ru)
			  formData.append('push_content_en', newFAQ.push_content_en)

              axios.post('/moderatorAPI/updateMailSMSTemplate', formData).then(function (resp) {
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


<style scoped>

</style>