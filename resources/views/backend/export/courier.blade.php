<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aser | Kuryer</title>
    <link rel="stylesheet" href="{{asset("backend/export/courier/css/bootstrap.css")}}">
    <link rel="stylesheet" href="{{asset("backend/export/courier/css/main.css")}}">
</head>
<body>
<div class="table-responsive container margin-top">
    <table>
        <tr>
            <td class="bold">Sifariş sayı:</td>
            <td><input type="text" class="border-bottom" value=" {{$orders_count}}" disabled></td>
        </tr>
        <tr>
            <td class="bold">Xaricdən çatdırılma:</td>
            <td><input type="text" class="border-bottom" value=" {{$total_amounts['shipping']}} AZN" disabled></td>
        </tr>
        <tr>
            <td class="bold">Kuryer çatdırılması:</td>
            <td><input type="text" class="border-bottom" value=" {{$total_amounts['delivery']}} AZN" disabled></td>
        </tr>
        <tr>
            <td class="bold">Ümumi məbləğ:</td>
            <td><input type="text" class="border-bottom" value=" {{$total_amounts['summary']}} AZN" disabled></td>
        </tr>
        <tr>
            <td class="bold">Çatdırılma tarixi:</td>
            <td><input type="text" class="border-bottom" value=" {{$date}}" disabled></td>
        </tr>
    </table>
    <table class="table  table-bordered margin-top text_center ">
        <tr class="th">
            <th rowspan="3"><span class="vertical">Opr</span></th>
            <th rowspan="3">№</th>
            <th rowspan="3" colspan="2"> İD Nömrə <br>Ad,Soyad</th>
            <th rowspan="3">Ərazi,<br>Metrostansiya<br>Poçt Zip kodu</th>
            <th rowspan="3">Çatdırılma <br>ünvanı</th>
            <th rowspan="3">Əlaqə <br>nömrəsi</th>
            <th rowspan="3">Ödəniş növü <br>Xaricdən <br>Kuryer</th>
            <th>Məbləğ</th>
            <th>Kuryer</th>
        </tr>
        <tr class="th">
            <th>Daşınma</th>
            <th rowspan="2" class="tehvil">Bağlamanı təhvil aldım<br>Qəbzi(pulu) təhvil verdim</th>
        </tr>
        <tr class="th">
            <th>Çatdırılma</th>
        </tr>
        <tbody>
        @php($count = 0)
        @foreach($orders as $order)
            @php($count++)
            <tr>
                <td rowspan="4"><span class="vertical">{{$order->courier_name}}</span></td>
                <td rowspan="4">{{$count}}</td>
                <td colspan="2">{{$order->suite}}</td>
                <td rowspan="3">
                    @if($order->post_zip == null)
                        {{$order->area}}, 
                        {{$order->metro_station}}
                    @else
                        {{$order->region}},
                        <br/>
                        {{$order->post_zip}}
                    @endif
                </td>
                <td rowspan="3" class="address-column">{{$order->address}}</td>
                <td rowspan="3">{{$order->phone}}</td>
                <td>{{$order->delivery_payment_type}}</td>
                <td>{{$order->shipping_amount}} ₼</td>
                <td rowspan="3"></td>
            </tr>
            <tr>
                <th>Ş/V</th>
                <td>{{$order->passport_number}}</td>
                <td>{{$order->courier_payment_type}}</td>
                <td>{{$order->courier_amount}} ₼</td>
            </tr>
            <tr>
                <td colspan="2">{{$order->client_name}} {{$order->client_surname}}</td>
                <td>Cəmi n.:</td>
                <td>{{$order->total_cash_amount}} ₼
                </td>
            </tr>
            <tr>
                <th colspan="2">Trek nömrə(lər):</th>
                <td colspan="6" class="text-left">{{$order->tracks}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>

<script src="{{asset("backend/js/jquery-3.4.1.js")}}"></script>
<script>
    $(document).ready(function () {
        window.print();
    });
</script>

</html>