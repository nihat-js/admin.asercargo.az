<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{asset("backend/css/bootstrap.css")}}">
    <link rel="stylesheet" href="{{asset("css/sweetalert2.min.css")}}">
    <link rel="stylesheet" href="{{asset("backend/css/main.css?ver=0.0.3")}}">
    <link rel="stylesheet" href="{{asset("backend/css/waybill-bootstrap.css")}}"  rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{asset("backend/css/waybill.css")}}"  rel="stylesheet" type="text/css">

    @yield('css')
</head>
<body>

<div id="waybill_area">
    <div class="container" id="waybill_content" style="display: block;">
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
                                style="border-top:0px solid !important;" id="waybill-suite">AS{{$package->user_id}}</td>
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
                                style="padding-top:18px;" id="waybill_seller">{{$package->seller_name}}</td>
                            <td class="col-md-3 col-md-offset-3  col-xs-3 col-xs-offset-3 text-center waybill_client"
                                style="padding-top: 18px;">{{$package->full_name}}  AS{{$package->user_id}}</td>
                        </tr>
                    </table>
                    <table border="1" class="col-md-12 col-xs-12  text-center" style="height:70px;">
                        <tr>
                            <td>{{$package->location_address}}</td>
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
                            <td class="text-center waybill_client">{{$package->full_name}} AS{{$package->user_id}}</td>
                        </tr>
                        <tr class="col-md-8  col-md-offset-2  col-xs-8 col-xs-offset-2">
                            <td class=" text-center" style="padding-top: 3px; padding-bottom: 3px;"
                                id="waybill_client_phone">{{$package->user_phone}}</td>
                        </tr>
                    </table>
                    <table border="1" class="col-md-12 col-xs-12" style="height: 70px; ">
                        <tr>
                            <td style="border-bottom: none;padding-bottom: 5px; padding-left: 10px;">Delivery
                                Address
                            </td>
                        </tr>
                        <tr>
                            <td style=" border-top: none;padding-left: 10px;" id="waybill_client_address">{{$package->user_address}}</td>
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
                            <td rowspan="3" class="col-md-6 col-xs-6" style="">
                                <img
                                        src="/backend/img/aserExpressLogo.png" height="98"
                                        width="240"
                                />
                            </td>
                            <td class="col-md-3 col-xs-2" colspan="1">CDN</td>
                            <td class="col-md-3 col-xs-4" id="waybill_cdn">{{$package->reg_number}}</td>

                        </tr>

                        <tr>
                            <td class="col-md-3 col-xs-2"></td>
                            <td class="col-md-3 col-xs-2 qrcode" id="qr"></td>
                        </tr>
                        <tr>


                            <td class="col-md-3 col-xs-3" id="waybill_departure">{{$package->departure}}</td>

                            <td class="col-md-3 col-xs-3" id="waybill_destination">{{$package->destination}}</td>
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
                            <td class="col-md-2 col-xs-2 text-center">Number of packages</td>
                            <td class="col-md-3  col-xs-2 text-center">Total Gross weight (kg)</td>
                            <td class="col-md-2  col-xs-2 text-center">Chargeable Volume Weight (kg)</td>
                            <td class="col-md-5 col-xs-4 ">Shipping Price</td>
                        </tr>
                        <tr>
                            <td class="col-md-2 col-xs-2 text-center" id="waybill_quantity">{{$package->quantity}}</td>
                            <td class="col-md-3  col-xs-2 text-center" id="waybill_gross_weight">{{$package->gross_weight}}</td>
                            <td class="col-md-2 col-xs-2 text-center" id="waybill_volume_weight">{{$package->volume_weight}}</td>
                            <td class="text-center col-md-5 col-xs-4" style="text-align: left" id="waybill_amount">{{$package->amount_usd}} USD</td>
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
                            <td class="col-md-6  col-xs-6 text-center " style="border-top: none;">{{$package->awb}}</td>
                            <td class="col-md-6 col-xs-6 text-center " style="border-top: none;" id="waybill_flight_name">{{$package->flight_name}}</td>
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
                                id="waybill_category">{{$package->cat_name}}</td>
                            <td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
                                id="waybill_invoice_price">{{$package->price_usd}}</td>
                            <td class="col-md-4  col-xs-4 text-center" style="padding-bottom: 30px;"
                                id="total_waybill_invoice_price">{{$package->price_usd + $package->amount_usd}} USD</td>
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
<script src="{{asset("backend/js/jquery-3.4.1.js")}}"></script>
<script src="{{asset("backend/js/bootstrap.min.js")}}"></script>
<script src="{{asset("js/jquery.form.min.js")}}"></script>
<script src="{{asset("js/sweetalert2.min.js")}}"></script>
<script src="{{asset("backend/js/variables.js")}}"></script>
<script src="{{asset("backend/js/main.js?ver=0.2.9")}}"></script>
<script src="{{asset("backend/js/ajax.js?ver=1.3.1")}}"></script>
<script src="{{asset("backend/js/jsbarcode.min.js")}}" defer></script>
<script src="{{asset("backend/js/jquery-barcode.js")}}" defer></script>
<script src="{{asset("backend/js/jquery.classyqr.min.js")}}" defer></script>
<script>


    document.addEventListener("DOMContentLoaded", function() {
        create_internal_barcode_for_waybill();
        generate_qr_code();
        window.print();

        // window.onafterprint = function() {
        //     window.close();
        // };
    });


    function generate_qr_code() {
        $('#qr').html("");

        var qr = "{{ $package->invoice_doc }}" != "" ? "https://asercargo.az" + "{{ $package->invoice_doc }}" : "https://asercargo.az";
        var qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x75&data=' + encodeURIComponent(qr + '?read_only=1');

        $('#qr').html('<img src="' + qrCodeUrl + '" alt="QR Code">');
    }

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

        $("#waybill_internal_id").html("{{ $package->internal_id }}");
        $("#waybill_internal_id_barcode").barcode(
            "{{ $package->internal_id }}",
            "code128",
            settings
        );
    }


</script>

</body>
</html>