<template>
	<v-container fluid class="ma-2">
		<v-row>
			<v-col cols="3" class="left">
				<v-text-field
						autofocus
						label="Scan Queue"
						placeholder="Scan Queue"
						outlined
						v-model="scan"
						@input="scanner()"
						maxlength="8"
						clearable
				></v-text-field>
				<v-text-field
						label="Full Name"
						placeholder="Full Name"
						v-model="client.name"
						outlined
						disabled
				></v-text-field>
				<v-text-field label="Debt" placeholder="Debt" :value="commonDebt + ' TL'" outlined disabled></v-text-field>
				<v-text-field label="Suite" placeholder="Suite" outlined v-model="client.suite" disabled></v-text-field>
				<v-text-field
						label="Passport No"
						placeholder="Passport No"
						outlined
						v-model="client.passport_series"
						disabled
				></v-text-field>
				<v-text-field
						label="FIN code"
						placeholder="FIN code"
						outlined
						v-model="client.passport_fin"
						disabled
				></v-text-field>
				<v-text-field label="Phone" placeholder="Phone" outlined v-model="client.phone" disabled></v-text-field>
				<!--<v-textarea
									outlined
									name="Address"
									label="Address"
									v-model="client.address"
									disabled
									no-resize
				></v-textarea>-->
			</v-col>
			<v-col cols="9" class="right">
				<v-row>
					<v-col cols="12">
						<template>
							<v-data-table
									v-model="selected"
									:headers="headers"
									:items="packages"
									item-key="number"
									default-sort="client:desc"
                  show-select
									disable-pagination
									hide-default-footer
							>
								<!--<template v-slot:item.data-table-select="{ isSelected, select, item }">
											<v-simple-checkbox :value="isSelected" @input="select($event)"></v-simple-checkbox>
								</template>-->
								<template v-slot:item="{ isSelected, select, item }">
									<tr
											v-if="item.cargo_debt>0 || item.common_debt>0 || item.currency==='' || item.currency===null || item.currency==='null' "
											class="not_allowed"
									>
										<td>
											<!--                                    <v-simple-checkbox :value="isSelected" @input="select($event)"></v-simple-checkbox>-->
										</td>
										<td class="text-xs-left">{{ item.number }}</td>
										<td class="text-xs-left">{{ item.internal_id }}</td>
										<td class="text-xs-left">{{ item.amount }}</td>
										<td class="text-xs-left">{{ item.paid }}</td>
										<td class="text-xs-left">{{ item.currency }}</td>
                    <td class="text-xs-left">{{ item.combinedDebts }}</td>
                    <!--<td class="text-xs-left">{{ item.common_debt }}</td>
                    <td class="text-xs-left">{{ item.cargo_debt }}</td>-->
										<td class="text-xs-left">{{ item.client_name + ' ' +item.client_surname }}</td>
									</tr>
									<tr v-else-if="item.paid_status===1" class="paid">
										<td>
											<v-simple-checkbox dark :value="isSelected" @input="select($event)"></v-simple-checkbox>
										</td>
										<td class="text-xs-left">{{ item.number }}</td>
										<td class="text-xs-left">{{ item.internal_id }}</td>
										<td class="text-xs-left">{{ item.amount }}</td>
										<td class="text-xs-left">{{ item.paid }}</td>
										<td class="text-xs-left">{{ item.currency }}</td>
										<td class="text-xs-left">{{ item.combinedDebts }}</td>
										<!--<td class="text-xs-left">{{ item.common_debt }}</td>
										<td class="text-xs-left">{{ item.cargo_debt }}</td>-->
										<td class="text-xs-left">{{ item.client_name + ' ' +item.client_surname }}</td>
									</tr>
									<tr v-else-if="item.paid_status!==1">
										<td>
											<v-simple-checkbox :value="isSelected" @input="select($event)"></v-simple-checkbox>
										</td>
										<td class="text-xs-left">{{ item.number }}</td>
										<td class="text-xs-left">{{ item.internal_id }}</td>
										<td class="text-xs-left">{{ item.amount }}</td>
										<td class="text-xs-left">{{ item.paid }}</td>
										<td class="text-xs-left">{{ item.currency }}</td>
                    <td class="text-xs-left">{{ item.combinedDebts }}</td>
                    <!--<td class="text-xs-left">{{ item.common_debt }}</td>
                    <td class="text-xs-left">{{ item.cargo_debt }}</td>-->
										<td class="text-xs-left">{{ item.client_name + ' ' +item.client_surname }}</td>
									</tr>
								</template>
							</v-data-table>
						</template>
					</v-col>
					<v-col cols="12">
						<v-row class="ma-2">
							<v-col class="color_orange ma-2" cols="12">
								<v-row>
									<v-col cols="2">
										<v-text-field
												label="Balance"
												placeholder="Balance"
												:value="client.balance"
												outlined
												disabled
										></v-text-field>
									</v-col>
									<v-col cols="2">
										<v-text-field
												label="Shipping (AZN)"
												placeholder="Shipping (AZN)"
												outlined
												disabled
												v-model="shipping"
										></v-text-field>
									</v-col>
									<v-col cols="2">
										<v-text-field
												label="Shipping (USD)"
												placeholder="Shipping (USD)"
												outlined
												disabled
												v-model="shippingUSD"
										></v-text-field>
									</v-col>
									<v-col cols="2">
										<v-select
												:items="promoCodesGroups"
												label="Promocode"
												item-text="name"
												item-value="id"
												outlined
												v-model="promocodeId"
												@change="getDiscount"
												clearable
										></v-select>
									</v-col>
									<v-col cols="2">
										<v-text-field
												label="Discount"
												placeholder="Discount"
												:value="`${discount}% - ${totalValue}`"
												outlined
												disabled
										></v-text-field>
									</v-col>
									<v-col cols="2">
										<v-text-field
												type="number"
												step="0.1"
												label="Paid Amount (AZN)"
												placeholder="Paid Amount (AZN)"
												outlined
												v-model="totalValueWithPromocode"
										></v-text-field>
									</v-col>
								</v-row>
							</v-col>
							<v-col class="color_orange ma-2" cols="12">
								<v-row>
									<v-col cols="3">
										<v-btn
												:disabled="disabled_button || totalValueWithPromocode<0 || disabled_button_minimum"
												color="#03A9F4"
												@click="pay(1)"
										>Cash Pay
										</v-btn>
									</v-col>
									<v-col cols="3">
										<v-btn
												:disabled="disabled_button || totalValueWithPromocode<0 || disabled_button_minimum"
												color="#F44336"
												@click="pay(2)"
										>POS Pay
										</v-btn>
									</v-col>
									<v-col cols="3">
										<v-btn
												:disabled="disabled_button || totalValueWithPromocode<0 || disabled_button_minimum || parseFloat(shipping)>parseFloat(client.balance)"
												color="#2E7D32"
												@click="pay(3)"
												class="button_cash"
										>Balance Pay
										</v-btn>
									</v-col>
									<v-col cols="3">
										<v-btn
												color="rgb(75, 80, 76)"
												@click="printAgain"
												class="button_print"
										>Print Bill
										</v-btn>
									</v-col>
                  <v-col cols="3">
                    <v-btn
                        color="rgb(75, 80, 76)"
                        @click="printQmatic"
                        class="button_print"
                    >Print Qmatic Receipt
                    </v-btn>
                  </v-col>
								</v-row>
							</v-col>
						</v-row>
					</v-col>
				</v-row>
			</v-col>
		</v-row>
	</v-container>
</template>

<script>
  export default {
    props   : {
      'myRoute'          : {
        type: String,
      },
      'getPromocodeRoute': {
        type: String,
      },
      'admin'            : {
        type: String,
      },
      'cashierPayRoute'  : {
        type: String,
      },
      'cashierQmaticRoute'  : {
        type: String,
      },
      'myCurrency'       : {
        type: String,
      },
      'myPayLog'         : {
        type: String,
      },
      'promoCodesGroups' : {
        type: Array,
      },
    },
    data () {
      return {
        discount               : 0,
        totalValueWithPromocode: 0,
        promocodeId            : '',
        promocodeText          : '',
        bill                   : {},
        disabled_button        : true,
        disabled_button_minimum: true,
        scan                   : '',
        total_pay              : '',
        singleSelect           : false,
        selected               : [],
        headers                : [
          {
            text    : 'number',
            align   : 'left',
            sortable: false,
            value   : 'number',
          },
          { text: 'internal_id', align: 'left', value: 'internal_id' },
          { text: 'amount', align: 'left', value: 'amount' },
          { text: 'paid', align: 'left', value: 'paid' },
          { text: 'currency', align: 'left', value: 'currency' },
          { text: 'debt', align: 'left', value: 'debt' },
          /*{ text: 'Common Debt', align: 'left', value: 'common_debt' },
					{ text: 'Carg Debt', align: 'left', value: 'cargo_debt' },*/
          { text: 'Client', align: 'left', value: 'client_name' },
        ],
        packages               : [],
        client                 : [],
        totalValue             : '',
      }
    },
    watch   : {
      selected               : function (val) {
        let _this = this
        //_this.disabled_button = !val.length;
        if (val.length) {
          for (let sel of val) {
            if (sel.paid_status === 0) {
              _this.disabled_button = false
              return
            } else {
              _this.disabled_button = true
            }
          }
        } else {
          _this.disabled_button = true
        }
      },
      shipping               : function (val) {
        let _this   = this
        let balance = parseFloat(_this.client.balance)
        let debt    = parseFloat(val)
        /*  if (balance >= debt) {
					 // console.log(balance + '>' + debt)
					 _this.totalValue = 0
				 } else { */
        // console.log(balance + '<' + debt)
        // _this.totalValue = (debt - balance).toFixed(2)
        _this.totalValue = (debt - (debt * (this.discount / 100).toFixed(2)).toFixed(2)).toFixed(2)
        //  }

      },
      totalValue             : function (val) {
        if (+this.client.balance >= +val) {
          this.totalValueWithPromocode = 0
        } else {
          this.totalValueWithPromocode = (val - (parseFloat(this.client.balance)).toFixed(2)).toFixed(2)
        }

        let _this                     = this
        let balance                   = parseFloat(_this.client.balance)
        let paid                      = parseFloat(this.totalValueWithPromocode)
        let ship                      = parseFloat(_this.shipping)
        _this.disabled_button_minimum = (paid + balance).toFixed(2) < ship - (ship * this.discount / 100).toFixed(2)
      },
      totalValueWithPromocode: function (val) {
        let _this                     = this
        let balance                   = parseFloat(_this.client.balance)
        let paid                      = parseFloat(val)
        let ship                      = parseFloat(_this.shipping)
        _this.disabled_button_minimum = (paid + balance).toFixed(2) < ship - (ship * this.discount / 100).toFixed(2)

        // console.log((paid + balance).toFixed(2)+ '<'+ ship+ '-' + _this.disabled_button_minimum)
      },
    },
    methods : {
      scanner () {
        let _this          = this
        this.discount      = 0
        this.promocodeText = ''
        if ((this.scan?.length || 0) === 8) {
          this.discount    = 0
          this.promocodeId = ''
          let formData     = new FormData
          formData.append('receipt', this.scan)
          axios.post(this.myRoute, formData)
               .then(function (resp) {
                 if (resp.data.case === 'success') {
                   if (resp.data.courier === 'yes') {
                     _this.scan = ''
                     toastr.success(resp.data.content)
                   } else {
                     _this.packages               = resp.data['packages']
                     _this.client                 = resp.data['client']
                     // _this.client.balanceAZN      = (_this.client.balance * _this.checkCurrency(1, 3)).toFixed(2)
                     _this.client.suite           = 'AS' + _this.client.id.toString()
                                                               .padStart(6, '0')
                     _this.client.name            = (_this.client.name ?? '') + ' ' + (_this.client.surname ?? '')
                     _this.client.passport_series = (_this.client.passport_series ?? '') + ' ' + (_this.client.passport_number ?? '')
                     //_this.disabled_button = false;
                   }
                 } else {
                   _this.packages = []
                   toastr.error(resp.data.content)
                   _this.client = []
                   _this.scan   = ''
                 }
               })
               .catch(function (resp) {
               })
        } else {
          _this.packages      = []
          _this.client        = []
          _this.selected      = []
          _this.discount      = 0
          _this.promocodeId   = ''
          _this.promocodeText = ''
          //_this.shipping='';
          //_this.disabled_button = true;
        }
      },
      download (filename, text) {
        let element = document.createElement('a')
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text))
        element.setAttribute('download', filename)

        element.style.display = 'none'
        document.body.appendChild(element)

        element.click()

        document.body.removeChild(element)
      },
      pay (type) {
        let _this    = this
        let formData = new FormData
        formData.append('packages', JSON.stringify(this.selected))
        formData.append('client_id', _this.client.id)
        formData.append('type', type)
        formData.append('total_amount', _this.totalValueWithPromocode)
        formData.append('receipt', _this.scan)
        formData.append('promo_code', this.promocodeText || null)
        // let ship    = parseFloat(_this.shipping)
        // let paid    = parseFloat(_this.totalValue)
        let total   = parseFloat(_this.totalValue)
        let must    = parseFloat(_this.totalValueWithPromocode)
        let balance = parseFloat(_this.client.balance)

        if (must === total) {
          axios.post(this.cashierPayRoute, formData)
               .then(function (resp) {
                 if (resp.data.case === 'success') {
                   let letter = {
                     receipt     : _this.scan,
                     suite       : _this.client.suite,
                     client      : _this.client.name,
                     //total: _this.shipping + ' AZN',
                     //total: resp.data.total,
                     cashier     : _this.admin,
                     payment_type: resp.data.payment_type,
                     queue       : resp.data.queue.original.no ?? '---',
                     waiting     : resp.data.queue.original.waiting ?? '---',
                     time        : resp.data.queue.original.date ?? '---',
                     debt        : resp.data.debt + ' AZN',
                     paid        : resp.data.pay + ' AZN',
                     rest        : resp.data.rest + ' AZN',
                     packages    : resp.data.packages,
                   }
                   _this.bill = letter
                   let txt    = JSON.stringify(letter)
                   txt        = txt.replace(/[{]/g, '&')
                   txt        = txt.replace(/[}]/g, '|')
                   txt        = txt.replace(/["']/g, '')
                   _this.download('EDI-Team-2019-aser.txt', txt)
                   toastr.success('Successfully paid')
                   let log = new FormData()
                   log.append('status', 1)
                   log.append('text', txt)
                   axios.post(_this.myPayLog, log)
                        .then((resp) => {
                        })
                   _this.packages        = []
                   _this.client          = []
                   _this.scan            = ''
                   _this.selected        = []
                   _this.disabled_button = true
                 } else {
                   toastr.error(resp.data.content)
                 }
               })
               .catch(function (resp) {
                 toastr.error(resp.data.content)
               })
        } else if (total < must) {
          Swal.fire({
            title             : 'Are you sure?',
            text              : `${(must - total).toFixed(2)} will be transferred to the account `,
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor : '#d33',
            confirmButtonText : 'Accept',
          })
              .then((result) => {
                if (result.value) {
                  axios.post(this.cashierPayRoute, formData)
                       .then(function (resp) {
                         if (resp.data.case === 'success') {
                           let letter = {
                             receipt     : _this.scan,
                             suite       : _this.client.suite,
                             client      : _this.client.name,
                             //total: _this.shipping + ' AZN',
                             //total: resp.data.total,
                             cashier     : _this.admin,
                             payment_type: resp.data.payment_type,
                             queue       : resp.data.queue.original.no ?? '---',
                             waiting     : resp.data.queue.original.waiting ?? '---',
                             time        : resp.data.queue.original.date ?? '---',
                             debt        : resp.data.debt + ' AZN',
                             paid        : resp.data.pay + ' AZN',
                             rest        : resp.data.rest + ' AZN',
                             packages    : resp.data.packages,
                           }
                           _this.bill = letter
                           let txt    = JSON.stringify(letter)
                           txt        = txt.replace(/[{]/g, '&')
                           txt        = txt.replace(/[}]/g, '|')
                           txt        = txt.replace(/["']/g, '')
                           _this.download('EDI-Team-2019-Aser.txt', txt)
                           toastr.success('Successfully paid')
                           let log = new FormData()
                           log.append('status', 1)
                           log.append('text', txt)
                           axios.post(_this.myPayLog, log)
                                .then((resp) => {
                                  // console.log(resp.data)
                                })
                           _this.packages        = []
                           _this.client          = []
                           _this.scan            = ''
                           _this.selected        = []
                           _this.disabled_button = true
                         } else {
                           toastr.error(resp.data.content)
                         }
                       })
                       .catch(function (resp) {
                         toastr.error(resp.data.content)
                       })
                }
              })
        } else if (total > must && ((must + balance).toFixed(2) >= total)) {
          Swal.fire({
            title             : 'Are you sure?',
            text              : `${(total - must).toFixed(2)} will be taken out of the account `,
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor : '#d33',
            confirmButtonText : 'Accept',
          })
              .then((result) => {
                if (result.value) {
                  axios.post(this.cashierPayRoute, formData)
                       .then(function (resp) {
                         if (resp.data.case === 'success') {
                           let letter = {
                             receipt     : _this.scan,
                             suite       : _this.client.suite,
                             client      : _this.client.name,
                             //total: _this.shipping + ' AZN',
                             //total: resp.data.total,
                             cashier     : _this.admin,
                             payment_type: resp.data.payment_type,
                             queue       : resp.data.queue.original.no ?? '---',
                             waiting     : resp.data.queue.original.waiting ?? '---',
                             time        : resp.data.queue.original.date ?? '---',
                             debt        : resp.data.debt + ' AZN',
                             paid        : resp.data.pay + ' AZN',
                             rest        : resp.data.rest + ' AZN',
                             packages    : resp.data.packages,
                           }
                           _this.bill = letter
                           let txt    = JSON.stringify(letter)
                           txt        = txt.replace(/[{]/g, '&')
                           txt        = txt.replace(/[}]/g, '|')
                           txt        = txt.replace(/["']/g, '')
                           _this.download('EDI-Team-2019-Aser.txt', txt)
                           toastr.success('Successfully paid')
                           let log = new FormData()
                           log.append('status', 1)
                           log.append('text', txt)
                           axios.post(_this.myPayLog, log)
                                .then((resp) => {
                                  // console.log(resp.data)
                                })
                           _this.packages        = []
                           _this.client          = []
                           _this.scan            = ''
                           _this.selected        = []
                           _this.disabled_button = true
                         } else {
                           toastr.error(resp.data.content)
                         }
                       })
                       .catch(function (resp) {
                         toastr.error(resp.data.content)
                       })
                }
              })
        } else {
          _this.disabled_button_minimum = true
        }
      },
      /*checkCurrency (a, b) {
				let _this    = this
				let currency = JSON.parse(_this.myCurrency)
				for (let i = 0; i < currency.length; i++) {
					if (currency[i].from_currency_id === a && currency[i].to_currency_id === b) {
						return currency[i].rate
					}
				}
			},*/
      printAgain () {
        let _this = this
        let txt   = JSON.stringify(_this.bill)
        console.log(JSON.stringify(_this.bill));
        if (Object.entries(_this.bill).length !== 0 && _this.bill.constructor === Object) {
          _this.download('EDI-Team-2019-Aser.txt', txt)
        }
      },
      printQmatic () {
        let _this    = this
        let formData = new FormData
        formData.append('client_id', _this.client.id)
        axios.post(this.cashierQmaticRoute, formData)
            .then(function (resp) {
              if (resp.data.case === 'success') {
                let letter = {
                  receipt     : resp.data.receipt,
                  suite       : _this.client.suite,
                  client      : _this.client.name,
                  cashier     : _this.admin,
                  payment_type: resp.data.payment_type,
                  packages    : resp.data.packages,
                }
                _this.bill = letter
                let txt    = JSON.stringify(letter)
                txt        = txt.replace(/[{]/g, '&')
                txt        = txt.replace(/[}]/g, '|')
                txt        = txt.replace(/["']/g, '')
                _this.download('EDI-Team-2024-aser-anbar.txt', txt)
                toastr.success('Successfully paid')
                let log = new FormData()
                log.append('status', 1)
                log.append('text', txt)
                axios.post(_this.myPayLog, log)
                    .then((resp) => {
                    })
                _this.packages        = []
                _this.client          = []
                _this.scan            = ''
                _this.selected        = []
                _this.disabled_button = true
              } else {
                toastr.error(resp.data.content)
              }
            })
            .catch(function (resp) {
              toastr.error(resp.data.content)
            })

      },
      getDiscount () {
        if (this.promocodeId) {
          axios.post(this.getPromocodeRoute, { group_id: this.promocodeId })
               .then(res => {
                 console.log(res)

                 if (res.data.case !== 'success') {
                   console.error(res)

                 }
                 this.discount      = +res.data.percent
                 this.promocodeText = res.data.promo_code
                 this.totalValue    = (+this.shipping - (+this.shipping * this.discount / 100).toFixed(2)).toFixed(2)
                 //this.totalValueWithPromocode=(+this.totalValue - (+this.totalValue*this.discount/100).toFixed(2)).toFixed(2)

               })
               .catch(res => {
                 console.error(res)

               })
        } else {
          this.discount   = 0
          this.totalValue = this.shipping
        }

      }
    },
    computed: {
      shipping () {
        let _this = this
        let v     = 0
        for (let must of this.selected) {
          v += parseFloat(must.pay_azn)
        }
        return v.toFixed(2)
      },
      commonDebt () {
        /*let v = 0
				for (let must of this.selected) {
					v += parseFloat(must.common_debt) + parseFloat(must.cargo_debt)
				}
				return v.toFixed(2)*/
        return (parseFloat(this.client.common_debt ?? 0)) + (parseFloat(this.client.cargo_debt ?? 0))
      },
      shippingUSD () {
        let _this = this
        let v     = 0
        for (let must of this.selected) {
          v += parseFloat(must.pay_usd)
        }
        return v.toFixed(2)
      },
    },
    mounted () {
      toastr.options = {
        'closeButton'      : false,
        'debug'            : false,
        'newestOnTop'      : false,
        'progressBar'      : false,
        'positionClass'    : 'toast-bottom-full-width',
        'preventDuplicates': false,
        'onclick'          : null,
        'showDuration'     : '300',
        'hideDuration'     : '1000',
        'timeOut'          : '10000',
        'extendedTimeOut'  : '1000',
        'showEasing'       : 'swing',
        'hideEasing'       : 'linear',
        'showMethod'       : 'fadeIn',
        'hideMethod'       : 'fadeOut',
      }
    },
  }
</script>
