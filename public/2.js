(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[2],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/CountryDetails.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/babel-loader/lib??ref--5-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/moderator/views/CountryDetails.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************************************************/
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

/* harmony default export */ __webpack_exports__["default"] = ({
  $_veeValidate: {
    validator: 'new'
  },
  components: {},
  data: function data() {
    return {
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
      search: {
        name: ''
      },
      dialog: false,
      headers: [{
        text: 'country id',
        value: 'country.id'
      }, {
        text: 'country name',
        value: 'country.name_az'
      }, {
        text: 'Actions',
        value: 'action',
        sortable: false
      }],
      desserts: [],
      editedIndex: -1,
      editedItem: {},
      defaultItem: {},
      selectedCountryDetails: [],
      csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };
  },
  computed: {
    formTitle: function formTitle() {
      return this.editedIndex === -1 ? 'New Country Details' : 'Edit Country Details';
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
      axios.get('/moderatorAPI/showCountryDetails' + '?name=' + this.search.name + '&page=' + this.pagination.current).then(function (resp) {
        _this.desserts = resp.data.data;
        _this.pagination.current = resp.data.current_page;
        _this.pagination.total = resp.data.last_page;
      })["catch"](function (resp) {
        sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
          type: 'error',
          title: 'Oops...',
          text: 'Something went wrong!'
        });
      })["finally"](function () {
        _this.isLoading = false;
      });
    },
    selectCountryDetails: function selectCountryDetails(item) {
      var _this = this;

      _this.isLoading = true;
      axios.get('/moderatorAPI/selectCountryDetails/' + item.id).then(function (resp) {
        _this.selectedCountryDetails = resp.data;
      })["catch"](function (resp) {
        sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
          type: 'error',
          title: 'Oops...',
          text: 'Something went wrong!'
        });
      })["finally"](function () {
        _this.isLoading = false;
      });
    },
    newCountryDetails: function newCountryDetails() {
      this.selectedCountryDetails.push({
        country_id: this.selectedCountryDetails[0].country_id
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
    searchStore: function searchStore() {
      this.pagination.current = 1;
      this.initialize();
    },
    editItem: function editItem(item) {
      var _this = this;

      this.editedIndex = this.desserts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.selectCountryDetails(item.country);
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
          var _this4 = _this3;
          var updatedStoreCategory = _this4.editedItem;
          var data = _this4.selectedCountryDetails;
          /* formData.append('id', updatedStoreCategory.id)
          formData.append('name', updatedStoreCategory.name) */

          axios.post('/moderatorAPI/updateCountryDetails', data).then(function (resp) {
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
          _this3.error = true;
        }
      });
    },
    deleteItem: function deleteItem(item) {
      var _this = this;

      sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then(function (result) {
        if (result.value) {
          axios["delete"]('/moderatorAPI/deleteCountryDetails/' + item.id).then(function (resp) {
            if (resp.data["case"] === 'success') {
              sweetalert2__WEBPACK_IMPORTED_MODULE_0___default.a.fire('Deleted!', 'Your file has been deleted.', 'success')["finally"](function () {
                _this.close();
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

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/CountryDetails.vue?vue&type=template&id=8abf9e16&":
/*!**********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/moderator/views/CountryDetails.vue?vue&type=template&id=8abf9e16& ***!
  \**********************************************************************************************************************************************************************************************************************/
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
        _c("h1", [_vm._v("Country Details")]),
        _vm._v(" "),
        _c("v-data-table", {
          staticClass: "elevation-1",
          attrs: {
            headers: _vm.headers,
            items: _vm.desserts,
            "disable-sort": "",
            "disable-filtering": "",
            "loading-text": "Loading... Please wait",
            loading: _vm.isLoading,
            "hide-default-footer": "",
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
                      _c("v-text-field", {
                        attrs: {
                          label: "Name",
                          "single-line": "",
                          "hide-details": ""
                        },
                        on: {
                          keyup: function($event) {
                            if (
                              !$event.type.indexOf("key") &&
                              _vm._k(
                                $event.keyCode,
                                "enter",
                                13,
                                $event.key,
                                "Enter"
                              )
                            ) {
                              return null
                            }
                            return _vm.searchStore()
                          }
                        },
                        model: {
                          value: _vm.search.name,
                          callback: function($$v) {
                            _vm.$set(_vm.search, "name", $$v)
                          },
                          expression: "search.name"
                        }
                      }),
                      _vm._v(" "),
                      _c("v-divider", {
                        staticClass: "mx-4",
                        attrs: { inset: "", vertical: "" }
                      }),
                      _vm._v(" "),
                      _c(
                        "v-dialog",
                        {
                          attrs: { "max-width": "800px" },
                          model: {
                            value: _vm.dialog,
                            callback: function($$v) {
                              _vm.dialog = $$v
                            },
                            expression: "dialog"
                          }
                        },
                        [
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
                                                        "\n                        Please fill correctly all inputs !\n                      "
                                                      )
                                                    ]
                                                  )
                                                ],
                                                1
                                              )
                                            : _vm._e(),
                                          _vm._v(" "),
                                          _vm._l(
                                            _vm.selectedCountryDetails,
                                            function(item) {
                                              return [
                                                _c("input", {
                                                  attrs: {
                                                    type: "hidden",
                                                    "v-model": item.id
                                                  }
                                                }),
                                                _vm._v(" "),
                                                _c("input", {
                                                  attrs: {
                                                    type: "hidden",
                                                    "v-model": item.country_id
                                                  }
                                                }),
                                                _vm._v(" "),
                                                _c(
                                                  "v-col",
                                                  {
                                                    attrs: {
                                                      cols: "12",
                                                      sm: "5",
                                                      md: "5"
                                                    }
                                                  },
                                                  [
                                                    _c("v-text-field", {
                                                      directives: [
                                                        {
                                                          name: "validate",
                                                          rawName: "v-validate",
                                                          value: "required",
                                                          expression:
                                                            "'required'"
                                                        }
                                                      ],
                                                      attrs: {
                                                        required: "",
                                                        "error-messages": _vm.errors.collect(
                                                          "title"
                                                        ),
                                                        "data-vv-name": "title",
                                                        label: "Title"
                                                      },
                                                      model: {
                                                        value: item.title,
                                                        callback: function(
                                                          $$v
                                                        ) {
                                                          _vm.$set(
                                                            item,
                                                            "title",
                                                            $$v
                                                          )
                                                        },
                                                        expression: "item.title"
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
                                                      sm: "5",
                                                      md: "5"
                                                    }
                                                  },
                                                  [
                                                    _c("v-text-field", {
                                                      directives: [
                                                        {
                                                          name: "validate",
                                                          rawName: "v-validate",
                                                          value: "required",
                                                          expression:
                                                            "'required'"
                                                        }
                                                      ],
                                                      attrs: {
                                                        required: "",
                                                        "error-messages": _vm.errors.collect(
                                                          "information"
                                                        ),
                                                        "data-vv-name":
                                                          "information",
                                                        label: "Information"
                                                      },
                                                      model: {
                                                        value: item.information,
                                                        callback: function(
                                                          $$v
                                                        ) {
                                                          _vm.$set(
                                                            item,
                                                            "information",
                                                            $$v
                                                          )
                                                        },
                                                        expression:
                                                          "item.information"
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
                                                      sm: "2",
                                                      md: "2"
                                                    }
                                                  },
                                                  [
                                                    _c(
                                                      "v-icon",
                                                      {
                                                        attrs: { small: "" },
                                                        on: {
                                                          click: function(
                                                            $event
                                                          ) {
                                                            return _vm.deleteItem(
                                                              item
                                                            )
                                                          }
                                                        }
                                                      },
                                                      [
                                                        _vm._v(
                                                          "\n                          delete\n                        "
                                                        )
                                                      ]
                                                    )
                                                  ],
                                                  1
                                                )
                                              ]
                                            }
                                          ),
                                          _vm._v(" "),
                                          _c(
                                            "v-col",
                                            {
                                              attrs: {
                                                cols: "12",
                                                sm: "12",
                                                md: "12"
                                              }
                                            },
                                            [
                                              _c(
                                                "v-btn",
                                                {
                                                  on: {
                                                    click: _vm.newCountryDetails
                                                  }
                                                },
                                                [_vm._v("New")]
                                              )
                                            ],
                                            1
                                          )
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
                    [_vm._v("\n          edit\n        ")]
                  )
                ]
              }
            },
            {
              key: "no-data",
              fn: function() {
                return [
                  _c("h2", [_vm._v("There is nothing!")]),
                  _vm._v(" "),
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

/***/ "./resources/js/moderator/views/CountryDetails.vue":
/*!*********************************************************!*\
  !*** ./resources/js/moderator/views/CountryDetails.vue ***!
  \*********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _CountryDetails_vue_vue_type_template_id_8abf9e16___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CountryDetails.vue?vue&type=template&id=8abf9e16& */ "./resources/js/moderator/views/CountryDetails.vue?vue&type=template&id=8abf9e16&");
/* harmony import */ var _CountryDetails_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CountryDetails.vue?vue&type=script&lang=js& */ "./resources/js/moderator/views/CountryDetails.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _CountryDetails_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _CountryDetails_vue_vue_type_template_id_8abf9e16___WEBPACK_IMPORTED_MODULE_0__["render"],
  _CountryDetails_vue_vue_type_template_id_8abf9e16___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/moderator/views/CountryDetails.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/moderator/views/CountryDetails.vue?vue&type=script&lang=js&":
/*!**********************************************************************************!*\
  !*** ./resources/js/moderator/views/CountryDetails.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_babel_loader_lib_index_js_ref_5_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CountryDetails_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/babel-loader/lib??ref--5-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./CountryDetails.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/CountryDetails.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_babel_loader_lib_index_js_ref_5_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CountryDetails_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/moderator/views/CountryDetails.vue?vue&type=template&id=8abf9e16&":
/*!****************************************************************************************!*\
  !*** ./resources/js/moderator/views/CountryDetails.vue?vue&type=template&id=8abf9e16& ***!
  \****************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CountryDetails_vue_vue_type_template_id_8abf9e16___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./CountryDetails.vue?vue&type=template&id=8abf9e16& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/moderator/views/CountryDetails.vue?vue&type=template&id=8abf9e16&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CountryDetails_vue_vue_type_template_id_8abf9e16___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CountryDetails_vue_vue_type_template_id_8abf9e16___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);