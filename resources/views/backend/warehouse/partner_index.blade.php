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

        .goBack{
            width: 10%;
            height: 30px;
            background: aqua;
            display: block;
            text-align: center;
            position: fixed;
            padding: 10px;
            text-decoration: none;
        }
        .pages {
            display: grid;
            grid-gap: 20px;
            justify-content: center;
            align-items: center;
            height: 100%;
            justify-items: center;
            font-size: 2rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 15px;
        }

        .hepsiglobal{
            background-image: url(/backend/img/static/hepsiglobal.png);
            background-repeat: no-repeat;
            background-position-y: 55px;
            background-position-x: 20px;
            background-size: 200px 200px;
            width: 320px;
            height: 370px;
            position: relative;
        }
        .canadashop{
            background-image: url(/backend/img/static/canadashop.jpg);
            background-position-y: 55px;
            background-position-x: 20px;
            background-size: 200px 200px;
            background-repeat: no-repeat;
            width: 320px;
            height: 370px;
            position: relative;
        }

        .hepsiglobal>a,.canadashop>a{
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
<a color="grey" style="background-color: #10458c !important; color: #fff" class="goBack" href="{{route('warehouse_page')}}">
    Go Back
</a>
<div class="pages">
    {{-- <div class="hepsiglobal">
        <a href="{{route("get_partner_package")}}">Change HepsiGlobal package status</a>
    </div> --}}
    <div class="canadashop">
        <a href="{{route("get_canadashop")}}">Change CanadaShop status and paid</a>
    </div>
</div>

</body>
</html>
