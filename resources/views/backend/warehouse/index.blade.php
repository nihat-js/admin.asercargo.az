<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Operator</title>
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
            margin-top: 15px;
        }
        .delivery{
            background-image: url(/backend/img/static/delivery1.png);
            background-size: 400px 300px;
            width: 400px;
            height: 370px;
            position: relative;
            background-repeat: no-repeat;
        }
        .distributor{
            background-image: url(/backend/img/static/del.svg);
            background-size: 400px 300px;
            width: 400px;
            height: 370px;
            position: relative;
            background-repeat: no-repeat;
        }
        .report{
            background-image: url(/backend/img/static/report.png);
            background-size: 400px 300px;
            width: 400px;
            height: 370px;
            position: relative;
            background-repeat: no-repeat;
        }
        .courier{
            background-image: url(/backend/img/static/courier.png);
            background-size: 400px 300px;
            width: 400px;
            height: 370px;
            position: relative;
            background-repeat: no-repeat;
        }
        .partner{
            background-image: url(/backend/img/static/partner.png);
            background-size: 290px 310px;
            width: 335px;
            height: 370px;
            position: relative;
            background-repeat: no-repeat;
        }
        .delivery>a,.distributor>a,.report>a,.courier>a, .partner>a{
            text-align: center;
            width: 100%;
            position: absolute;
            margin: -25px;
            text-decoration: none;
            height: 100%;
        }
    </style>
</head>
<body>

<div class="pages">
    <div class="delivery">
        <a href="{{route("delivery_page")}}">Delivery</a>
    </div>
    <div class="distributor">
        <a href="{{route("distributor_page")}}">Distribute</a>
    </div>
    <div class="report">
        <a href="{{route("report_page")}}">Report</a>
    </div>
    <div class="courier">
        <a href="{{route("warehouse_courier_page")}}">Courier</a>
    </div>
    <div class="partner">
        <a href="{{route("get_partner_page")}}">Partner</a>
    </div>
</div>

</body>
</html>
