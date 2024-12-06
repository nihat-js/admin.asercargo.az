(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[3],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/FAQ.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/babel-loader/lib??ref--5-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/moderator/views/FAQ.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_0__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  $_veeValidate: {
    validator: 'new'
  },
  components: {},
  data: function data() {
    return {
      tab: null,
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
      search: '',
      dialog: false,
      headers: [{
        text: 'ID',
        align: 'left',
        value: 'id'
      }, {
        text: 'Question',
        value: 'question_az'
      }, {
        text: 'Actions',
        value: 'action',
        sortable: false
      }],
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
      }
    };
  },
  computed: {
    formTitle: function formTitle() {
      return this.editedIndex === -1 ? 'New FAQ' : 'Edit FAQ';
    }
  },
  watch: {
    dialog: function dialog(val) {
      val || this.close();
    }
  },
  created: function created() {
    this.initialize();
  },
  methods: {
    initialize: function initialize() {
      var _this = this;

      _this.isLoading = true;
      axios.get('/moderatorAPI/showFAQ' + '?page=' + this.pagination.current).then(function (resp) {
        _this.desserts = resp.data.data;
        _this.pagination.current = resp.data.current_page;
        _this.pagination.total = resp.data.last_page;
      })["catch"](function (resp) {
        sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
          type: 'error',
          title: 'Oops...',
          text: 'Something went wrong!',
          footer: '<a href>Why do I have this issue?</a>'
        });
      })["finally"](function () {
        _this.isLoading = false;
      });
    },
    initializePage: function initializePage() {
      var _this = this;

      axios.get('/moderatorAPI/showFAQ' + '?page=' + this.pagination.current).then(function (resp) {
        _this.pagination.current = resp.data.current_page;
        _this.pagination.total = resp.data.last_page;
      })["catch"](function (resp) {
        sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
          type: 'error',
          title: 'Oops...',
          text: 'Something went wrong!'
        });
      });
    },
    onPageChange: function onPageChange() {
      this.initialize();
    },
    editItem: function editItem(item) {
      this.editedIndex = this.desserts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    close: function close() {
      var _this2 = this;

      this.dialog = false;
      this.error = false;
      setTimeout(function () {
        _this2.editedItem = Object.assign({}, _this2.defaultItem);
        _this2.editedIndex = -1;
      }, 300);
    },
    save: function save() {
      var _this3 = this;

      var _this = this;

      this.$validator.validateAll().then(function (responses) {
        if (responses) {
          if (_this3.editedIndex > -1) {
            var _this4 = _this3;
            var newFAQ = _this4.editedItem;
            var formData = new FormData();
            formData.append('id', newFAQ.id);
            formData.append('answer_az', newFAQ.answer_az);
            formData.append('answer_ru', newFAQ.answer_ru);
            formData.append('answer_en', newFAQ.answer_en);
            formData.append('question_az', newFAQ.question_az);
            formData.append('question_ru', newFAQ.question_ru);
            formData.append('question_en', newFAQ.question_en);
            axios.post('/moderatorAPI/updateFAQ', formData).then(function (resp) {
              if (resp.data["case"] === 'success') {
                _this4.initialize();

                _this4.close();
              } else {
                sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
                  type: 'error',
                  title: resp.data.title,
                  text: resp.data.content
                });
              }
            })["catch"](function (resp) {
              sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
                type: 'error',
                title: resp.data.title,
                text: resp.data.content
              });
            });
          } else {
            var _this5 = _this3;
            var _newFAQ = _this5.editedItem;

            var _formData = new FormData();

            _formData.append('answer_az', _newFAQ.answer_az);

            _formData.append('answer_ru', _newFAQ.answer_ru);

            _formData.append('answer_en', _newFAQ.answer_en);

            _formData.append('question_az', _newFAQ.question_az);

            _formData.append('question_ru', _newFAQ.question_ru);

            _formData.append('question_en', _newFAQ.question_en);

            axios.post('/moderatorAPI/createFAQ', _formData).then(function (resp) {
              if (resp.data["case"] === 'success') {
                _this5.initialize();

                _this5.close();
              } else {
                sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
                  type: 'error',
                  title: resp.data.title,
                  text: resp.data.content
                });
              }
            })["catch"](function (resp) {
              sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
                type: 'error',
                title: 'Oops...',
                text: resp.content
              });
            });
          }
        } else {
          _this3.error = true;
        }
      });
    },
    deleteItem: function deleteItem(item) {
      var index = this.desserts.indexOf(item);
      var app = this;
      sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to revert this!',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then(function (result) {
        if (result.value) {
          axios["delete"]('/moderatorAPI/deleteFAQ/' + item.id).then(function (resp) {
            if (resp.data["case"] === 'success') {
              var old = app.pagination.current;
              app.initializePage();
              sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire('Deleted!', 'Your file has been deleted.', 'success')["finally"](function () {
                if (old >= app.pagination.total) {
                  app.pagination.current = app.pagination.total;
                } else {
                  app.pagination.current = old;
                }

                app.initialize();
              });
            } else {
              sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
                type: 'error',
                title: resp.data.title,
                text: resp.data.content
              });
            }
          })["catch"](function (resp) {
            sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
              type: 'error',
              title: 'Oops...',
              text: resp
            });
          });
        }
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/FAQ.vue?vue&type=template&id=199e88bd&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/moderator/views/FAQ.vue?vue&type=template&id=199e88bd& ***!
  \***********************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("v-app", [
    _c(
      "div",
      { staticClass: "container" },
      [
        _c("h1", [_vm._v("FAQ")]),
        _vm._v(" "),
        _c("v-data-table", {
          staticClass: "elevation-1",
          attrs: {
            headers: _vm.headers,
            items: _vm.desserts,
            search: _vm.search,
            "disable-sort": "",
            "disable-filtering": "",
            "loading-text": "Loading... Please wait",
            loading: _vm.isLoading,
            "hide-default-footer": true,
            dark: ""
          },
          scopedSlots: _vm._u([
            {
              key: "top",
              fn: function() {
                return [
                  _c(
                    "v-toolbar",
                    { attrs: { flat: "" } },
                    [
                      _c("div", { staticClass: "flex-grow-1" }),
                      _vm._v(" "),
                      _c(
                        "v-dialog",
                        {
                          staticClass: "zIndexModal",
                          attrs: { "max-width": "800px" },
                          scopedSlots: _vm._u([
                            {
                              key: "activator",
                              fn: function(ref) {
                                var on = ref.on
                                return [
                                  _c(
                                    "v-btn",
                                    _vm._g(
                                      {
                                        staticClass: "mb-2",
                                        attrs: { color: "primary", dark: "" }
                                      },
                                      on
                                    ),
                                    [_vm._v("New FAQ")]
                                  )
                                ]
                              }
                            }
                          ]),
                          model: {
                            value: _vm.dialog,
                            callback: function($$v) {
                              _vm.dialog = $$v
                            },
                            expression: "dialog"
                          }
                        },
                        [
                          _vm._v(" "),
                          _c(
                            "v-card",
                            [
                              _c("v-card-title", [
                                _c("span", { staticClass: "headline" }, [
                                  _vm._v(_vm._s(_vm.formTitle))
                                ])
                              ]),
                              _vm._v(" "),
                              _c(
                                "v-card-text",
                                [
                                  _c(
                                    "v-container",
                                    [
                                      _c(
                                        "v-row",
                                        [
                                          _vm.error
                                            ? _c(
                                                "v-col",
                                                { attrs: { cols: "12" } },
                                                [
                                                  _c(
                                                    "v-alert",
                                                    {
                                                      attrs: { type: "error" }
                                                    },
                                                    [
                                                      _vm._v(
                                                        "\n\t\t\t\t\t\t\t\t\t\t\t\tPlease fill correctly all inputs !\n\t\t\t\t\t\t\t\t\t\t\t"
                                                      )
                                                    ]
                                                  )
                                                ],
                                                1
                                              )
                                            : _vm._e(),
                                          _vm._v(" "),
                                          _c(
                                            "v-col",
                                            {
                                              attrs: {
                                                cols: "12",
                                                sm: "6",
                                                md: "6"
                                              }
                                            },
                                            [
                                              _c("v-text-field", {
                                                attrs: {
                                                  disabled: "",
                                                  label: "ID"
                                                },
                                                model: {
                                                  value: _vm.editedItem.id,
                                                  callback: function($$v) {
                                                    _vm.$set(
                                                      _vm.editedItem,
                                                      "id",
                                                      $$v
                                                    )
                                                  },
                                                  expression: "editedItem.id"
                                                }
                                              })
                                            ],
                                            1
                                          ),
                                          _vm._v(" "),
                                          [
                                            _c(
                                              "v-tabs",
                                              {
                                                staticClass: "elevation-2",
                                                attrs: {
                                                  "background-color":
                                                    "deep-purple accent-4",
                                                  dark: "",
                                                  centered: true,
                                                  grow: true
                                                },
                                                model: {
                                                  value: _vm.tab,
                                                  callback: function($$v) {
                                                    _vm.tab = $$v
                                                  },
                                                  expression: "tab"
                                                }
                                              },
                                              [
                                                _c("v-tabs-slider"),
                                                _vm._v(" "),
                                                _c(
                                                  "v-tab",
                                                  {
                                                    key: "az",
                                                    attrs: { href: "#tab-az" }
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n\t\t\t\t\t\t\t\t\t\t\t\t\tAZ\n\t\t\t\t\t\t\t\t\t\t\t\t"
                                                    )
                                                  ]
                                                ),
                                                _vm._v(" "),
                                                _c(
                                                  "v-tab",
                                                  {
                                                    key: "ru",
                                                    attrs: { href: "#tab-ru" }
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n\t\t\t\t\t\t\t\t\t\t\t\t\tRU\n\t\t\t\t\t\t\t\t\t\t\t\t"
                                                    )
                                                  ]
                                                ),
                                                _vm._v(" "),
                                                _c(
                                                  "v-tab",
                                                  {
                                                    key: "en",
                                                    attrs: { href: "#tab-en" }
                                                  },
                                                  [
                                                    _vm._v(
                                                      "\n\t\t\t\t\t\t\t\t\t\t\t\t\tEN\n\t\t\t\t\t\t\t\t\t\t\t\t"
                                                    )
                                                  ]
                                                ),
                                                _vm._v(" "),
                                                _c(
                                                  "v-tab-item",
                                                  {
                                                    key: "az",
                                                    attrs: { value: "tab-az" }
                                                  },
                                                  [
                                                    _c(
                                                      "v-card",
                                                      {
                                                        attrs: {
                                                          flat: "",
                                                          tile: ""
                                                        }
                                                      },
                                                      [
                                                        _c(
                                                          "v-card-text",
                                                          [
                                                            _c("v-text-field", {
                                                              directives: [
                                                                {
                                                                  name:
                                                                    "validate",
                                                                  rawName:
                                                                    "v-validate",
                                                                  value:
                                                                    "required",
                                                                  expression:
                                                                    "'required'"
                                                                }
                                                              ],
                                                              attrs: {
                                                                "error-messages": _vm.errors.collect(
                                                                  "question_az"
                                                                ),
                                                                "data-vv-name":
                                                                  "question_az",
                                                                label:
                                                                  "Question AZ"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .question_az,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "question_az",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.question_az"
                                                              }
                                                            }),
                                                            _vm._v(" "),
                                                            _c("ckeditor", {
                                                              directives: [
                                                                {
                                                                  name:
                                                                    "validate",
                                                                  rawName:
                                                                    "v-validate",
                                                                  value:
                                                                    "required",
                                                                  expression:
                                                                    "'required'"
                                                                }
                                                              ],
                                                              attrs: {
                                                                "error-messages": _vm.errors.collect(
                                                                  "answer_az"
                                                                ),
                                                                "data-vv-name":
                                                                  "answer_az"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .answer_az,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "answer_az",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.answer_az"
                                                              }
                                                            })
                                                          ],
                                                          1
                                                        )
                                                      ],
                                                      1
                                                    )
                                                  ],
                                                  1
                                                ),
                                                _vm._v(" "),
                                                _c(
                                                  "v-tab-item",
                                                  {
                                                    key: "ru",
                                                    attrs: { value: "tab-ru" }
                                                  },
                                                  [
                                                    _c(
                                                      "v-card",
                                                      {
                                                        attrs: {
                                                          flat: "",
                                                          tile: ""
                                                        }
                                                      },
                                                      [
                                                        _c(
                                                          "v-card-text",
                                                          [
                                                            _c("v-text-field", {
                                                              directives: [
                                                                {
                                                                  name:
                                                                    "validate",
                                                                  rawName:
                                                                    "v-validate",
                                                                  value:
                                                                    "required",
                                                                  expression:
                                                                    "'required'"
                                                                }
                                                              ],
                                                              attrs: {
                                                                "error-messages": _vm.errors.collect(
                                                                  "question_az"
                                                                ),
                                                                "data-vv-name":
                                                                  "question_ru",
                                                                label:
                                                                  "Question RU"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .question_ru,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "question_ru",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.question_ru"
                                                              }
                                                            }),
                                                            _vm._v(" "),
                                                            _c("ckeditor", {
                                                              directives: [
                                                                {
                                                                  name:
                                                                    "validate",
                                                                  rawName:
                                                                    "v-validate",
                                                                  value:
                                                                    "required",
                                                                  expression:
                                                                    "'required'"
                                                                }
                                                              ],
                                                              attrs: {
                                                                "error-messages": _vm.errors.collect(
                                                                  "answer_ru"
                                                                ),
                                                                "data-vv-name":
                                                                  "answer_ru"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .answer_ru,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "answer_ru",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.answer_ru"
                                                              }
                                                            })
                                                          ],
                                                          1
                                                        )
                                                      ],
                                                      1
                                                    )
                                                  ],
                                                  1
                                                ),
                                                _vm._v(" "),
                                                _c(
                                                  "v-tab-item",
                                                  {
                                                    key: "en",
                                                    attrs: { value: "tab-en" }
                                                  },
                                                  [
                                                    _c(
                                                      "v-card",
                                                      {
                                                        attrs: {
                                                          flat: "",
                                                          tile: ""
                                                        }
                                                      },
                                                      [
                                                        _c(
                                                          "v-card-text",
                                                          [
                                                            _c("v-text-field", {
                                                              directives: [
                                                                {
                                                                  name:
                                                                    "validate",
                                                                  rawName:
                                                                    "v-validate",
                                                                  value:
                                                                    "required",
                                                                  expression:
                                                                    "'required'"
                                                                }
                                                              ],
                                                              attrs: {
                                                                "error-messages": _vm.errors.collect(
                                                                  "question_en"
                                                                ),
                                                                "data-vv-name":
                                                                  "question_en",
                                                                label:
                                                                  "Question EN"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .question_en,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "question_en",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.question_en"
                                                              }
                                                            }),
                                                            _vm._v(" "),
                                                            _c("ckeditor", {
                                                              directives: [
                                                                {
                                                                  name:
                                                                    "validate",
                                                                  rawName:
                                                                    "v-validate",
                                                                  value:
                                                                    "required",
                                                                  expression:
                                                                    "'required'"
                                                                }
                                                              ],
                                                              attrs: {
                                                                "error-messages": _vm.errors.collect(
                                                                  "answer_en"
                                                                ),
                                                                "data-vv-name":
                                                                  "answer_en"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .answer_en,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "answer_en",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.answer_en"
                                                              }
                                                            })
                                                          ],
                                                          1
                                                        )
                                                      ],
                                                      1
                                                    )
                                                  ],
                                                  1
                                                )
                                              ],
                                              1
                                            )
                                          ]
                                        ],
                                        2
                                      )
                                    ],
                                    1
                                  )
                                ],
                                1
                              ),
                              _vm._v(" "),
                              _c(
                                "v-card-actions",
                                [
                                  _c("div", { staticClass: "flex-grow-1" }),
                                  _vm._v(" "),
                                  _c(
                                    "v-btn",
                                    {
                                      attrs: {
                                        color: "blue darken-1",
                                        text: ""
                                      },
                                      on: { click: _vm.close }
                                    },
                                    [_vm._v("Cancel")]
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "v-btn",
                                    {
                                      attrs: {
                                        color: "blue darken-1",
                                        text: "",
                                        disabled:
                                          !_vm.editedItem.answer_az ||
                                          !_vm.editedItem.answer_ru ||
                                          !_vm.editedItem.answer_en
                                      },
                                      on: { click: _vm.save }
                                    },
                                    [_vm._v("Save\n\t\t\t\t\t\t\t\t")]
                                  )
                                ],
                                1
                              )
                            ],
                            1
                          )
                        ],
                        1
                      )
                    ],
                    1
                  )
                ]
              },
              proxy: true
            },
            {
              key: "item.action",
              fn: function(ref) {
                var item = ref.item
                return [
                  _c(
                    "v-icon",
                    {
                      staticClass: "mr-2",
                      attrs: { small: "" },
                      on: {
                        click: function($event) {
                          return _vm.editItem(item)
                        }
                      }
                    },
                    [_vm._v("\n\t\t\t\t\tedit\n\t\t\t\t")]
                  ),
                  _vm._v(" "),
                  _c(
                    "v-icon",
                    {
                      attrs: { small: "" },
                      on: {
                        click: function($event) {
                          return _vm.deleteItem(item)
                        }
                      }
                    },
                    [_vm._v("\n\t\t\t\t\tdelete\n\t\t\t\t")]
                  )
                ]
              }
            },
            {
              key: "no-data",
              fn: function() {
                return [
                  _c(
                    "v-btn",
                    {
                      attrs: { color: "primary" },
                      on: { click: _vm.initialize }
                    },
                    [_vm._v("Reset")]
                  )
                ]
              },
              proxy: true
            }
          ])
        }),
        _vm._v(" "),
        [
          _c(
            "div",
            { staticClass: "text-center" },
            [
              _c("v-pagination", {
                attrs: {
                  length: _vm.pagination.total,
                  circle: _vm.circle,
                  "next-icon": _vm.nextIcon,
                  "prev-icon": _vm.prevIcon,
                  "total-visible": _vm.totalVisible
                },
                on: { input: _vm.onPageChange },
                model: {
                  value: _vm.pagination.current,
                  callback: function($$v) {
                    _vm.$set(_vm.pagination, "current", $$v)
                  },
                  expression: "pagination.current"
                }
              })
            ],
            1
          )
        ]
      ],
      2
    )
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/moderator/views/FAQ.vue":
/*!**********************************************!*\
  !*** ./resources/js/moderator/views/FAQ.vue ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _FAQ_vue_vue_type_template_id_199e88bd___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./FAQ.vue?vue&type=template&id=199e88bd& */ "./resources/js/moderator/views/FAQ.vue?vue&type=template&id=199e88bd&");
/* harmony import */ var _FAQ_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./FAQ.vue?vue&type=script&lang=js& */ "./resources/js/moderator/views/FAQ.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _FAQ_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _FAQ_vue_vue_type_template_id_199e88bd___WEBPACK_IMPORTED_MODULE_0__["render"],
  _FAQ_vue_vue_type_template_id_199e88bd___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/moderator/views/FAQ.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/moderator/views/FAQ.vue?vue&type=script&lang=js&":
/*!***********************************************************************!*\
  !*** ./resources/js/moderator/views/FAQ.vue?vue&type=script&lang=js& ***!
  \***********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_babel_loader_lib_index_js_ref_5_0_node_modules_vue_loader_lib_index_js_vue_loader_options_FAQ_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/babel-loader/lib??ref--5-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./FAQ.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/FAQ.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_babel_loader_lib_index_js_ref_5_0_node_modules_vue_loader_lib_index_js_vue_loader_options_FAQ_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/moderator/views/FAQ.vue?vue&type=template&id=199e88bd&":
/*!*****************************************************************************!*\
  !*** ./resources/js/moderator/views/FAQ.vue?vue&type=template&id=199e88bd& ***!
  \*****************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_FAQ_vue_vue_type_template_id_199e88bd___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./FAQ.vue?vue&type=template&id=199e88bd& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/FAQ.vue?vue&type=template&id=199e88bd&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_FAQ_vue_vue_type_template_id_199e88bd___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_FAQ_vue_vue_type_template_id_199e88bd___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);