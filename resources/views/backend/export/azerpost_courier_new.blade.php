<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <style>
        p {
            line-height: 8px;
        }

        #user {
            font-size: 18px;
        }

        * {
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }



        .page {
            width: 10cm;

        }

        @media print {
            body {
                color: #000;
                background-color: #fff;
            }

            @page {
                size: 10cm 10cm;
                margin: 1mm;
            }

            .page {
                width: 10cm;

            }
        }

        .logo_div{
            height: 7rem;
            margin-bottom: 15px;
            border-bottom: 1px dashed;
        }

        .table{
            border: 1px solid;
        }

        tbody > tr > td {
            border-bottom: 1px dashed;
        }

        .left_row{
            border-right: 1px dashed;
            width: 65px;
        }

        .address{
            height: 60px;
        }

        .right_row{
            font-size: 10px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>

<body>
@php($count = 0)
@php($address = null)
@php($maxCharacters = 60)
@php($trimmedAddress = null)


@foreach($orders as $order)
<div class="row m-2 p-1 page" style="border: 2px solid; border-radius: 10px;">

    <div class="logo_div">
        <img src="{{asset("uploads/files/static/aserLogo.svg")}}" style="position: absolute;
      width: 130px;" alt="Aser-logo" />
    </div>


        @php($count++)
        @php($address = ($order->region == null && $order->area == null) ? $order->address : (($order->region == null) ? $order->area : $order->region))
        @php( $trimmedAddress = mb_substr($address, 0, $maxCharacters))
        <div class="container">
            <table class="table">
                <tbody>
                <tr>
                    <td class="left_row">Müştəri</td>
                    <td class="right_row">{{$order->client_name}} {{$order->client_surname}}</td>
                </tr>
                <tr>
                    <td class="left_row address" >Adress</td>
                    <td class="right_row">{{$trimmedAddress}}</td>
                </tr>
                <tr>
                    <td class="left_row" >Post kodu</td>
                    <td class="right_row">{{$order->post_zip}}</td>
                </tr>
                <tr>
                    <td class="left_row">Əlaqə</td>
                    <td class="right_row">{{$order->phone}}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div  class="col-12 text-center pt-1" id="qrCode-{{$loop->iteration}}">
            <canvas id="barcode-{{$loop->iteration}}"></canvas>
        </div>




</div>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.3/dist/barcodes/JsBarcode.code128.min.js"></script>

<script>
    const qrCode{{$loop->iteration}} = document.getElementById('qrCode-{{$loop->iteration}}')
    qrCode{{$loop->iteration}}.src = 'https://api.qrserver.com/v1/create-qr-code/?size=75x75&data=hdhdhdh?read_only=1'
    JsBarcode("#barcode-{{$loop->iteration}}", '{{$order->azerpost_track}}', {
        width: 2,
        fontSize: 12,
        height: 60,
        text: '{{$order->azerpost_track}}'
    });


</script>
@endforeach

</body>

</html>