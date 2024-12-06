(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[5],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/ProhibitedItems.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/babel-loader/lib??ref--5-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/moderator/views/ProhibitedItems.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************************************************/
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
  props: {
    'country': {
      type: Array
    }
  },
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
        text: 'Country',
        value: 'country.name_az'
      }, {
        text: 'Actions',
        value: 'action',
        sortable: false
      }],
      desserts: [],
      editedIndex: -1,
      editedItem: {
        id: 0,
        answer_az: '',
        answer_ru: '',
        answer_en: '',
        question_az: '',
        question_ru: '',
        question_en: ''
      },
      defaultItem: {
        id: 0,
        answer_az: '',
        answer_ru: '',
        answer_en: '',
        question_az: '',
        question_ru: '',
        question_en: ''
      }
    };
  },
  computed: {
    formTitle: function formTitle() {
      return this.editedIndex === -1 ? 'New Prohibited Item' : 'Edit Prohibited Item';
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
      var app = this;
      app.isLoading = true;
      axios.get('/moderatorAPI/showProhibitedItem' + '?page=' + this.pagination.current).then(function (resp) {
        app.desserts = resp.data.data;
        app.pagination.current = resp.data.current_page;
        app.pagination.total = resp.data.last_page;
      })["catch"](function (resp) {
        sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
          type: 'error',
          title: 'Oops...',
          text: 'Something went wrong!'
        });
      })["finally"](function () {
        app.isLoading = false;
      });
    },
    initializePage: function initializePage() {
      var app = this;
      axios.get('/moderatorAPI/showProhibitedItem' + '?page=' + this.pagination.current).then(function (resp) {
        app.pagination.current = resp.data.current_page;
        app.pagination.total = resp.data.last_page;
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
    close: function close() {
      var _this2 = this;

      this.dialog = false;
      this.error = false;
      setTimeout(function () {
        _this2.editedItem = Object.assign({}, _this2.defaultItem);
        _this2.editedIndex = -1;
      }, 300);
    },
    editItem: function editItem(item) {
      this.editedIndex = this.desserts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    save: function save() {
      var _this3 = this;

      var _this = this;

      this.$validator.validateAll().then(function (responses) {
        if (responses) {
          if (_this3.editedIndex > -1) {
            var _this4 = _this3;
            var newProhibitedItem = _this4.editedItem;
            var formData = new FormData();
            formData.append('id', newProhibitedItem.id);
            formData.append('country_id', newProhibitedItem.country_id);
            formData.append('item_az', newProhibitedItem.item_az.replace(/<ul[^>]*>|<ol[^>]*>/g, '<al>').replace(/<\/ul>|<\/ol>/g, '</al>'));
            formData.append('item_ru', newProhibitedItem.item_ru.replace(/<ul[^>]*>|<ol[^>]*>/g, '<al>').replace(/<\/ul>|<\/ol>/g, '</al>'));
            formData.append('item_en', newProhibitedItem.item_en.replace(/<ul[^>]*>|<ol[^>]*>/g, '<al>').replace(/<\/ul>|<\/ol>/g, '</al>'));
            axios.post('/moderatorAPI/updateProhibitedItem', formData).then(function (resp) {
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
            var _newProhibitedItem = _this5.editedItem;

            var _formData = new FormData();

            _formData.append('country_id', _newProhibitedItem.country_id);

            _formData.append('item_az', _newProhibitedItem.item_az.replace(/<ul[^>]*>|<ol[^>]*>/g, '<al>').replace(/<\/ul>|<\/ol>/g, '</al>'));

            _formData.append('item_ru', _newProhibitedItem.item_ru.replace(/<ul[^>]*>|<ol[^>]*>/g, '<al>').replace(/<\/ul>|<\/ol>/g, '</al>'));

            _formData.append('item_en', _newProhibitedItem.item_en.replace(/<ul[^>]*>|<ol[^>]*>/g, '<al>').replace(/<\/ul>|<\/ol>/g, '</al>'));

            axios.post('/moderatorAPI/createProhibitedItem', _formData).then(function (resp) {
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
          axios["delete"]('/moderatorAPI/deleteProhibitedItem/' + item.id).then(function (resp) {
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
  },
  mounted: function mounted() {}
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/ProhibitedItems.vue?vue&type=template&id=e602166e&":
/*!***********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/moderator/views/ProhibitedItems.vue?vue&type=template&id=e602166e& ***!
  \***********************************************************************************************************************************************************************************************************************/
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
        _c("h1", [_vm._v("Prohibited Items")]),
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
                                    [_vm._v("New Prohibited Item")]
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
                                              _c("v-select", {
                                                directives: [
                                                  {
                                                    name: "validate",
                                                    rawName: "v-validate",
                                                    value: "required",
                                                    expression: "'required'"
                                                  }
                                                ],
                                                attrs: {
                                                  "error-messages": _vm.errors.collect(
                                                    "country"
                                                  ),
                                                  "data-vv-name": "country",
                                                  items: _vm.country,
                                                  "item-text": "name_az",
                                                  "item-value": "id",
                                                  label: "country"
                                                },
                                                model: {
                                                  value:
                                                    _vm.editedItem.country_id,
                                                  callback: function($$v) {
                                                    _vm.$set(
                                                      _vm.editedItem,
                                                      "country_id",
                                                      $$v
                                                    )
                                                  },
                                                  expression:
                                                    "editedItem.country_id"
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
                                                                  "item_az"
                                                                ),
                                                                "data-vv-name":
                                                                  "item_az"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .item_az,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "item_az",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.item_az"
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
                                                                  "item_ru"
                                                                ),
                                                                "data-vv-name":
                                                                  "item_ru"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .item_ru,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "item_ru",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.item_ru"
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
                                                                  "item_en"
                                                                ),
                                                                "data-vv-name":
                                                                  "item_en"
                                                              },
                                                              model: {
                                                                value:
                                                                  _vm.editedItem
                                                                    .item_en,
                                                                callback: function(
                                                                  $$v
                                                                ) {
                                                                  _vm.$set(
                                                                    _vm.editedItem,
                                                                    "item_en",
                                                                    $$v
                                                                  )
                                                                },
                                                                expression:
                                                                  "editedItem.item_en"
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
                                        text: ""
                                      },
                                      on: { click: _vm.save }
                                    },
                                    [_vm._v("Save")]
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

/***/ "./resources/js/moderator/views/ProhibitedItems.vue":
/*!**********************************************************!*\
  !*** ./resources/js/moderator/views/ProhibitedItems.vue ***!
  \**********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ProhibitedItems_vue_vue_type_template_id_e602166e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ProhibitedItems.vue?vue&type=template&id=e602166e& */ "./resources/js/moderator/views/ProhibitedItems.vue?vue&type=template&id=e602166e&");
/* harmony import */ var _ProhibitedItems_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ProhibitedItems.vue?vue&type=script&lang=js& */ "./resources/js/moderator/views/ProhibitedItems.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _ProhibitedItems_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _ProhibitedItems_vue_vue_type_template_id_e602166e___WEBPACK_IMPORTED_MODULE_0__["render"],
  _ProhibitedItems_vue_vue_type_template_id_e602166e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/moderator/views/ProhibitedItems.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/moderator/views/ProhibitedItems.vue?vue&type=script&lang=js&":
/*!***********************************************************************************!*\
  !*** ./resources/js/moderator/views/ProhibitedItems.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_babel_loader_lib_index_js_ref_5_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ProhibitedItems_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/babel-loader/lib??ref--5-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./ProhibitedItems.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/ProhibitedItems.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_babel_loader_lib_index_js_ref_5_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ProhibitedItems_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/moderator/views/ProhibitedItems.vue?vue&type=template&id=e602166e&":
/*!*****************************************************************************************!*\
  !*** ./resources/js/moderator/views/ProhibitedItems.vue?vue&type=template&id=e602166e& ***!
  \*****************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ProhibitedItems_vue_vue_type_template_id_e602166e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./ProhibitedItems.vue?vue&type=template&id=e602166e& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/ProhibitedItems.vue?vue&type=template&id=e602166e&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ProhibitedItems_vue_vue_type_template_id_e602166e___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ProhibitedItems_vue_vue_type_template_id_e602166e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);