@extends('backend.app')
@section('title')
    Dashboard page
@endsection
@section('content')

    <div class="container">
        <div class="row">

            <div class="col-md-4 cols">
                <div class="col-md-12">
                    <h5 class="head-title">In baku statusunda olan paketlər</h5>
                </div>
                <div class="col-md-6">
                    <span class="head-title">Ümumi say</span>
                    <h5 class="home-title">{{$results->package_count}}</h5>
                </div>
                <div class="col-md-6">
                    <span class="head-title">Ümumi çəki</span>
                    <h5 class="home-title">{{$results->total_weight}}</h5>
                </div>


            </div>
            <div class="col-md-6 cols">
                <div class="col-md-12">
                    <span class="head-title">Users registrations</span>
                </div>
                <div class="col-md-4">
                    <span class="head-title">24 hours</span>
                    <h5 class="home-title">{{$last24Hours}}</h5>
                </div>
                <div class="col-md-4">
                    <span class="head-title">Last 3 day</span>
                    <h5 class="home-title">{{$last3Days}}</h5>
                </div>
                <div class="col-md-4">
                    <span class="head-title">Last month</span>
                    <h5 class="home-title">{{$thisMonth}}</h5>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>

    </div>




@endsection

@section('css')
    <style>
        .home-title {
            text-align: center;
            box-shadow: 5px 5px 5px 5px #1f314c;
            padding: 15px;
            height: auto;
        }

        .head-title{
            text-align: center;
            color: #284257;
            padding: 15px;
            font-size: 20px;
            height: auto;
        }
        .cols{
            text-align: center;
            box-shadow: 5px 5px 5px 5px #1f314c;
            padding: 15px;
            height: auto;
            margin: 10px;
        }

        .references-main{
            margin-top: 180px !important;
            height: auto !important;
        }
    </style>
@endsection

@section('js')

@endsection
