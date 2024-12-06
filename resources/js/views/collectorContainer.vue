<template>
  <v-app>
    <div class="wrap container">
      <div class="top">
        <v-text-field
            class="scan_input"
            autofocus
            label="Scan"
            placeholder="Scan1"
            outlined
            v-model="scan_input"
            @change="scanner()"
        ></v-text-field>
        <v-text-field
            autofocus
            label="Track"
            placeholder="Track No"
            outlined
            v-model="track"
            disabled
        ></v-text-field>
        <v-text-field
            label="Container"
            placeholder="Container"
            v-model="position"
            outlined
            disabled
        ></v-text-field>
      </div>
      <div class="bottom">
        <template>
          <v-data-table
              :headers="headers"
              :items="log"
              item-key=""
              disable-pagination
              hide-default-footer
              class="elevation-1"
          >
          </v-data-table>
        </template>
      </div>
    </div>
    <div id="waybill_area">
      <div class="container" id="waybill_content" style="display: none;">
        <div class="row" style="border:2px solid black;">
          <div class="col-md-5 col-xs-5">
            <div class="">
              <table>
                <caption class="caption text-center">WAYBILL</caption>
                <tr class="">
                  <td class="col-md-1 col-xs-1 borderh">1</td>
                  <td class="col-md-1 col-xs-1 borderh"></td>
                  <td class="col-md-10 col-xs-10 borderh" colspan="3">Payer account number</td>
                </tr>
              </table>
              <table border="1">
                <tr>
                  <td rowspan="2" class="col-md-7 col-xs-7 text-center"
                      style="border-top:0px solid !important;" id="waybill-suite"></td>
                  <td class="col-md-1 col-xs-1" id="waybill_charge_collect">x</td>
                  <td class="col-md-4 col-xs-4" style="border-bottom:0px solid !important;">Charge
                    Collect
                  </td>
                </tr>
                <tr>
                  <td class="col-md-1 col-xs-1" style="border-top:0px solid !important;"
                      id="waybill_prepaid"></td>
                  <td class="col-md-12 col-xs-4" rowspan="2" style="border-top:0px solid !important;">
                    Prepaid
                  </td>
                </tr>
              </table>
              <table border="1">
                <tr>
                  <td class="col-md-1 col-xs-1">2</td>
                  <td class="col-md-1 col-xs-1" style="border-top: none!important;"></td>
                  <td class="col-md-4 col-xs-4">From</td>
                  <td class="col-md-6 col-xs-6">Shipper</td>
                </tr>
              </table>
              <table border="1">
                <tr style="border-bottom: 0px solid white !important;">

                  <td class="col-md-3 col-md-offset-3  col-xs-3 col-xs-offset-3 text-center"
                      style="padding-top:18px;" id="waybill_seller"></td>
                  <td class="col-md-3 col-md-offset-3  col-xs-3 col-xs-offset-3 text-center waybill_client"
                      style="padding-top: 18px;"></td>
                </tr>
              </table>
              <table border="1" class="col-md-12 col-xs-12  text-center" style="height:70px;">
                <tr>
                  <td>{{ userLocation }}</td>
                </tr>
              </table>
              <table class="col-md-12 col-xs-12 lrborder" style="height: 80px;">
                <tr class="row">
                  <td class="col-md-5 col-xs-5 "
                      style=" padding-right: 0px !important; padding-left: 10px !important;">Postcode /
                    ZIP Code
                  </td>
                  <td class="col-md-7 col-xs-7"
                      style=" padding-right: 0px !important; padding-left: 10px !important;">Phone, Fax or
                    Email
                    (required)
                  </td>
                </tr>
                <tr class="row">
                  <td class="col-md-5 col-xs-5 "
                      style=" padding-right: 0px !important; padding-left: 10px !important;"></td>
                  <td class="col-md-7 col-xs-7"
                      style=" padding-right: 0px !important; padding-left: 10px !important;"></td>
                </tr>
              </table>
              <table>
                <tr class="">
                  <td class="col-md-1  col-md-offset-1 col-xs-1 borderh">3</td>
                  <td class=" col-md-0 col-xs-1 bordertb"></td>
                  <td class="col-md-10 col-xs-10 borderh" colspan="3">To (Consignee)</td>
                </tr>
              </table>
              <table class="lrborder">
                <tr class="">
                  <td class="col-md-0 col-xs-0 ">Name</td>
                  <td class="col-md-12 col-xs-12" align="right">Personal ID No</td>
                </tr>
              </table>
              <table class="col-md-12 col-xs-12 lrborder">
                <tr class="col-md-8 col-md-offset-2  col-xs-8 col-xs-offset-2">
                  <td class="text-center waybill_client"></td>
                </tr>
                <tr class="col-md-8  col-md-offset-2  col-xs-8 col-xs-offset-2">
                  <td class=" text-center" style="padding-top: 3px; padding-bottom: 3px;"
                      id="waybill_client_phone"></td>
                </tr>
              </table>
              <table border="1" class="col-md-12 col-xs-12" style="height: 70px; ">
                <tr>
                  <td style="border-bottom: none;padding-bottom: 5px; padding-left: 10px;">Delivery
                    Address
                  </td>
                </tr>
                <tr>
                  <td style=" border-top: none;padding-left: 10px;" id="waybill_client_address"></td>
                </tr>
              </table>

              <table class="col-md-12 col-xs-12 lrborder">
                <tr>
                  <td class="text-center" style="padding-top: 5px;">
                    <div id="waybill_internal_id_barcode"></div>
                  </td>
                </tr>
                <tr>
                  <td class="text-center" style="font-weight: bold;" id="waybill_internal_id"></td>
                </tr>
              </table>
              <table>
                <tr>
                  <td class="col-md-6 col-xs-6 borderh" style="padding-bottom: 20px; padding-left: 10px;">
                    Postcode/ZIP Code
                  </td>
                  <td class="col-md-4 col-xs-4" style="padding-bottom: 20px;">Country Azerbaijan</td>
                </tr>
                <tr>
                  <td class="col-md-12 col-xs-12 borderh" colspan="3" style="padding-top: 2px;">Contact
                    Person
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <div class="col-md-7 col-xs-7">
            <div class="row" style="border: 1px grey;">
              <table border="1" class="col-md-12 col-xs-12">
                <tr>
                  <td rowspan="3" class="col-md-6 col-xs-6 colibimg" style="">
                    <img
                      src="/uploads/files/static/logo.png" height="98"
                      width="240"
                    />
                  </td>
                  <td class="col-md-3 col-xs-2" colspan="1">CDN</td>
                  <td class="col-md-3 col-xs-4" id="waybill_cdn"></td>

                </tr>

                <tr>
                  <td class="col-md-3 col-xs-2"></td>
                  <td class="col-md-3 col-xs-2 qrcode" id="qr"></td>
                </tr>
                <tr>


                  <td class="col-md-3 col-xs-3" id="waybill_departure"></td>

                  <td class="col-md-3 col-xs-3" id="waybill_destination"></td>
                </tr>
                <tr>
                  <td colspan="3" class="col-md-12 col-xs-12 text-center" id="waybill_date"></td>
                </tr>
              </table>
              <table class="col-md-12 col-xs-12">
                <tr>
                  <td class="col-md-1 col-xs-1 borderh four">4</td>
                  <td class="col-md-1 col-xs-1"></td>
                  <td class="col-md-10 col-xs-10 borderh" colspan="3">Shipment details</td>
                </tr>
              </table>
              <table border="1" align="center">
                <tr>
                  <td class="col-md-3 col-xs-3 text-center">Total number of packages</td>
                  <td class="col-md-3  col-xs-3 text-center">Total Gross weight (kg)</td>
                  <td class="col-md-3  col-xs-3 text-center">Chargeable Volume Weight (kg)</td>
                  <td class="col-md-3 col-xs-3 ">Shipping Price</td>
                </tr>
                <tr>
                  <td class="col-md-3 col-xs-3 text-center" id="waybill_quantity"></td>
                  <td class="col-md-3  col-xs-3 text-center" id="waybill_gross_weight"></td>
                  <td class="col-md-3 col-xs-3 text-center" id="waybill_volume_weight"></td>
                  <td class="text-center col-md-3 col-xs-3" id="waybill_amount"></td>
                </tr>
                <tr>
                  <td class="col-md-3 col-xs-3">Transportation mode</td>
                  <td colspan="2" class="text-center">By Air</td>
                </tr>
              </table>
              <table border="" class="col-md-12 col-xs-12" style="height: 70px;">
                <tr style="border-bottom: none;">
                  <td class="col-md-6  col-xs-6 text-center ">MAWB</td>
                  <td class="col-md-6 col-xs-6 text-center">Aser Cargo Express FLIGHT #</td>
                </tr>
                <tr style="border-top: none;">
                  <td class="col-md-6  col-xs-6 text-center " style="border-top: none;"></td>
                  <td class="col-md-6 col-xs-6 text-center " style="border-top: none;" id="waybill_flight_name"></td>
                </tr>
              </table>
              <table class="col-md-12 col-xs-12">
                <tr>
                  <td class="col-md-1 col-xs-1 borderh">5</td>
                  <td class="col-md-1 col-xs-1"></td>
                  <td class="col-md-10 col-xs-10 borderh" colspan="3">Full Description of contents &
                    remarks
                  </td>
                </tr>
              </table>
              <table border="1" class="col-md-12 bos">
                <tr>
                  <td id="waybill_description"></td>
                </tr>
              </table>
              <table class="col-md-12 col-xs-12" border="1">
                <tr>
                  <td class="col-md-4  col-xs-4 text-center " style="padding-bottom: 30px;">Category</td>
                  <td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;">Declared Value
                    for Customs
                  </td>
                  <td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;">Total Price
                  </td>
                </tr>
                <tr>
                  <td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
                      id="waybill_category"></td>
                  <td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
                      id="waybill_invoice_price"></td>
                  <td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
                      id="total_waybill_invoice_price"></td>
                </tr>
              </table>
              <table border="1" class="col-md-12 col-xs-12">
                <tr>
                  <td style="padding-bottom: 20px; padding-left: 15px; padding-top: 5px;">Information on goods filled in by Consignee or by Aser Cargo Express on behalf of Shipper
                  </td>
                </tr>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>

  </v-app>

</template>

<script>
export default {
  name   : 'collector',
  props  : {
    'myRoute': {
      type: String,
    },
    'waybillRoute': {
      type: String,
    },
    'userLocation': {
      type: String,
    },
    'baseUrl': {
      type: String,
    }
  },
  data () {
    return {
      id        : 1,
      position  : '',
      track     : '',
      scan_input: '',
      headers   : [
        { text: 'Id', align: 'left', sortable: false, value: 'id' },
        { text: 'track', align: 'left', value: 'track' },
        { text: 'Position', align: 'left', value: 'position' },
      ],
      log       : [],

    }
  },
  methods: {
    scanner () {
      let self    = this
      let barcode = this.scan_input
      if (barcode.substr(0, 2)
                 .toUpperCase() === 'CN') {
        //position
        self.position = barcode.substr(2, barcode.length)
      } else {
        //pack
        let packageTrack = barcode
        console.log(packageTrack);
        if (packageTrack.startsWith('42019801')) {
          packageTrack = packageTrack.slice(8)
        }
        self.track = packageTrack
      }
      self.scan_input = ''
      if (this.position && this.track) {
        let formData = new FormData
        formData.append('track', this.track)
        formData.append('position', this.position)
        axios.post(this.myRoute, formData)
             .then((resp) => {
               if (resp.data.case === 'success') {
                 toastr.success(resp.data.content)
                  // new Audio('../assets/sucess.wav').play()
                 setTimeout(() => {
                   new Audio('../assets/sucess.wav').play()
                 }, 500);
                 //get_package_with_waybill();

                 axios.post(this.waybillRoute, {number:this.track}).then(res => {
                   update_values_for_collect(res.data, this.track);
                   print_waybill();
                 });

                 /*if (self.log.length===9 ){
               self.log.splice(-1,1)
             }*/
                 let item = self.log.find(el => el.track === self.track)
                 if (item) {
                   let index       = self.log.indexOf(item)
                   item.position   = self.position
                   self.log[index] = item
                 } else {
                   self.log.unshift({
                     id      : self.id++,
                     track   : self.track,
                     position: self.position,
                   })
                 }
               } else {
                 toastr.error(resp.data.content)
                 new Audio('../assets/error.wav').play()
                 setTimeout(() => {
                   new Audio('../assets/error.wav').play()
                 }, 500)
               }
             })
             .catch(function (resp) {
               console.log(resp);
               alert('Error scanner')
             })
             .finally(() => {
               self.track = ''
               // self.position = ''
             })
      } else {
      }
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

function print_waybill () {
  console.log('test');

    $('#waybill_content')
        .css('display', 'block')
    let disp_setting  = 'toolbar=no,location=no,directories=no,menubar=no,'
    disp_setting += 'scrollbars=no,left=0,top=0,resizable=yes,width=900, height=650,'
    let content_value = document.getElementById('waybill_area').outerHTML
    let docprint      = window.open('', '', disp_setting)
    docprint.document.open()
    docprint.document.write('<html><head><title></title>')
    docprint.document.write('<link rel="stylesheet" href="/backend/css/waybill-bootstrap.css"  rel="stylesheet" type="text/css">')
    docprint.document.write('<link rel="stylesheet" href="/backend/css/waybill.css"  rel="stylesheet" type="text/css">')
    docprint.document.write('</head><body onLoad="self.print();window.close();">')
    docprint.document.write(content_value)
    docprint.document.write('</body></html>')
    docprint.document.close()
    docprint.focus()
    $('#waybill_content')
        .css('display', 'none')


}

let scan_mode = true;
let tracking_number_control = false;
let tracking_number = "";
let length = 0;
let height = 0;
let width = 0;
let category = "";
let seller = "";
let seller_title = "";
let waybill_cdn = "";
let destination = "";
let gross_weight = "";
let volume_weight = "";
let container = "";
let position = "";
let invoice = "0";
let invoice_usd = "0";
let quantity = 1;
let currency = "";
let client_id = 0;
let client_name_surname = '';
let client_name = "";
let client_phone = "";
let client_address = "";
let suite = '';
let url = "";
let last_invoice_doc = '';
let last_return_label = '';
let last_invoice_confirmed = 0;
let last_waybill_doc = '';
let waybill_print_access = false;
let paid_status = 0;
let flight_departure = "";
let flight_destination = "";
let flight_date = "";
let flight_name = "";
let package_amount = "";
let check_package_url = '';
let package_status = 37; //no invoice
let invoice_status = 0;
let status_arr = [];
let package_description = '';
let tariff_type = 1;
let is_legal_entity = 0;
let client_comment = "";
let success_invoice_statuses = [3, 4];
let success_customs_statuses = [1, 2];
let current_status = 0;
let product_title = "";
let subCat = "";
let legality = 0;
let package_internal_id = "";
let invoice_doc = "";

function create_internal_barcode_for_waybill() {
  let settings = {
    barWidth: 2,
    barHeight: 50,
    moduleSize: 30,
    showHRI: true,
    addQuietZone: true,
    marginHRI: 5,
    bgColor: "#FFFFFF",
    color: "#000000",
    fontSize: 0,
    output: "bmp",
    posX: 0,
    posY: 0
  };

  $("#waybill_internal_id").html(package_internal_id);
  $("#waybill_internal_id_barcode").barcode(
      package_internal_id,
      "code128",
      settings
  );
}

function generate_waybill_content_for_print() {
  create_internal_barcode_for_waybill();
  $("#waybill-suite").html("AS" + suite);
  if (invoice !== null && invoice != 0) {
    $("#waybill_charge_collect").html("x");
  } else {
    $("#waybill_charge_collect").html("");
  }
  if (paid_status === 1) {
    $("#waybill_prepaid").html("x");
  } else {
    $("#waybill_prepaid").html("");
  }
  $("#waybill_seller").html(seller_title);
  $(".waybill_client").html(client_name + " " + "AS" + suite);
  $("#waybill_client_phone").html("(" + client_phone + ")");
  $("#waybill_client_address").html("(" + client_address + ")");
  $("#waybill_departure").html(flight_departure);
  $("#waybill_cdn").html(waybill_cdn)
  $("#waybill_destination").html(flight_destination);
  // if (flight_date !== null && flight_date.length > 10) {
  //   flight_date = flight_date.substr(0, 10);
  // }
  $("#waybill_flight_name").html(flight_name);
  $("#waybill_date").html(flight_date);
  $("#waybill_quantity").html(quantity);
  $("#waybill_gross_weight").html(gross_weight);
  $("#waybill_volume_weight").html(volume_weight);
  $("#waybill_invoice_price").html(invoice + ' ' + currency);

  let invoice_usd_waybill = Number(invoice_usd);
  let payment_amount_way_bill = Number(package_amount.slice(0, -3));
  let total = (invoice_usd_waybill + payment_amount_way_bill).toFixed(2);

  $("#total_waybill_invoice_price").html(total + ' ' + 'USD');
  $("#waybill_category").html(category);
  $("#waybill_amount").html(package_amount);
  $("#waybill_description").html(client_comment);


}

const update_values_for_collect = (res) =>{
  console.log(res);
  gross_weight = res.package.gross_weight;
  volume_weight = res.package.volume_weight;
  category = res.package.category;
  package_amount = res.package.amount;
  client_comment = res.package.client_comment;
  quantity = res.package.quantity;
  flight_date = res.package.flight;
  flight_name = res.package.flight_name;
  invoice_usd = res.package.invoice_usd;
  flight_departure = res.package.flight_departure;
  flight_destination = res.package.flight_destination;
  paid_status = res.package.paid_status;
  invoice = res.package.invoice;
  suite = res.package.client_id;
  seller_title = res.package.seller_title;
  client_phone = res.package.client_phone;
  client_address = res.package.client_address;
  client_name = res.package.client_name;
  waybill_cdn = res.package.carrier_registration_number;
  currency = res.package.currency;

  package_internal_id = res.package.internal_id;
  //invoice_doc = res.package.invoice_doc;


  /*$('#qr').html("");
  $('#qr').ClassyQR({
    create: true, // signals the library to create the image tag inside the container div.
    type: 'text', // text/url/sms/email/call/locatithe text to encode in the QR. on/wifi/contact, default is TEXT
    text: res.package.invoice_doc != null ? "https://asercargo.az" + res.package.invoice_doc : "https://asercargo.az"
  });*/

  $('#qr').html("");
  var qr = res.package.invoice_doc != null ? "https://asercargo.az" + res.package.invoice_doc : "https://asercargo.az";
  var qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x75&data=' + encodeURIComponent(qr + '?read_only=1');
  $('#qr').html('<img src="' + qrCodeUrl + '" alt="QR Code">');




  generate_waybill_content_for_print();
}

</script>

