<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            background-color: #10458c69;
            display: grid;
            min-height: 95vh;
        }

        .pages {
            display: grid;
            grid-gap: 20px;
            justify-content: center;
            align-items: center;
            height: 100%;
            justify-items: center;
            font-size: 2rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .pages>div{
            background-size: 200px 200px;
            width: 200px;
            height: 200px;
            position: relative;
        }
        .operator{
            background-image: url(/backend/img/static/oper.png);
        }
        .makeOrder{
            background-image: url(/backend/img/static/order.png);
        }
        .unknown{
            background-image: url(/backend/img/static/unknown.png);
        }
        .packages{
            background-image: url(/backend/img/static/packages.png);
        }
        .courier{
            background-image: url(/backend/img/static/courier.png);
        }
        .operator>a,.makeOrder>a,.unknown>a,.packages>a,.courier>a{
            text-align: center;
            width: 100%;
            position: absolute;
            margin: -40px;
            text-decoration: none;
            height: 100%;
        }
    </style>
</head>
<body>
<div class="pages">
    <div class="operator">
        <a href="{{route("information_page")}}">Information</a>
    </div>
    <div class="makeOrder">
        <a href="{{route("get_make_orders_page")}}">Make orders</a>
    </div>
    <div class="unknown">
        <a href="{{route("get_anonymous_page")}}">Unknown</a>
    </div>
	<div class="packages">
		<a href="{{route("operator_get_packages_page")}}">All Packages</a>
	</div>
    <div class="courier">
		<a href="{{route("operator_get_courier_page")}}">Courier</a>
	</div>
</div>
</body>
</html>
