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
<style>
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .card-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 43px;
        padding: 30px;
    }

    .card {
        background-color: #fafafa;
        padding: 20px;
        border-radius: 8px;
        text-align: left;
        transition: transform 0.2s ease-in-out;
        cursor: pointer;
        height: 22rem;
    }

    .card i {
        font-size: 28px;
        margin-bottom: 10px;
    }

    .card h3 {
        font-size: 19px;
        margin: 3px;
    }

    .card p {
        font-size: 15px;
    }



    h1 {
        text-align: center;
    }
</style>
<body>

@php($count = 0)
@php($address = null)
<div class="card-grid">
@foreach($orders as $order)
    @php($count++)
    @php($address = ($order->region == null && $order->area == null) ? $order->address : (($order->region == null) ? $order->area : $order->region))
        <div class="card">
            <i class="fas fa-star"></i>
            <h3>Alıcı</h3>
            <h3>{{$order->client_name}} {{$order->client_surname}}</h3>
            <p>{{$address}} - {{$order->post_zip}}</p>
            <p>Tel: {{$order->phone}}</p>

        </div>

@endforeach
</div>


</body>

<script src="{{asset("backend/js/jquery-3.4.1.js")}}"></script>
<script>
    $(document).ready(function () {
        window.print();
    });
</script>

</html>