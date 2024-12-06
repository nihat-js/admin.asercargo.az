<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Aser | Delivery</title>

    <!-- Fonts -->
    <link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/report.css') }}"
          type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Alegreya&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.0.15/vuetify.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
          rel="stylesheet">
    {{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css">--}}
    <script>
        if (navigator.userAgent.toLowerCase()
            .indexOf('firefox') > -1) {
            var root = document.getElementsByTagName('html')[0]
            root.setAttribute('class', 'ff')
        }
    </script>
</head>
<style>
    .v-sheet, .v-toolbar, .v-app-bar {
        background-color: #b3d4fc !important;
        box-shadow: #b3d4fc !important;
    }
</style>
<body>
@if(session('display') == 'block')
    <div class="col col-12" style="margin-top: 60px;">
        <div role="alert" class="v-alert v-sheet theme--dark v-alert--dense info">
            <div class="v-alert__wrapper"><i aria-hidden="true"
                                             class="v-icon notranslate v-alert__icon mdi mdi-information theme--dark"></i>
                <div class="v-alert__content">
                    {{session('message')}}
                </div>
            </div>
        </div>
    </div>
@endif
<div id="report">
    <v-app>
        <v-app-bar app color="#f0682a">
            <v-toolbar-title>Aser Express</v-toolbar-title>
            <v-spacer></v-spacer>
            <h3>{{Auth::user()->name . ' ' . Auth::user()->surname }}</h3>
            <v-spacer></v-spacer>

            <v-btn text rounded href="{{route('warehouse_page')}}">
                Go Back
            </v-btn>
            <v-btn text rounded href="{{route('logout')}}">
                <svg style="width:35px;height:35px" viewBox="0 0 24 24">
                    <path fill="currentColor"
                          d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z"/>

                </svg>
                Log Out
            </v-btn>
        </v-app-bar>
        <v-content>
            <my-report export-inbaku="{{route('warehouse_reports_in_baku')}}"
                       export-inbound="{{route('warehouse_reports_inbound_packages')}}"
                       export-delivered="{{route('warehouse_reports_delivered_packages')}}"
                       :flights="{{$flights}}"
                       admin="{{Auth::user()->username }}"
            ></my-report>
        </v-content>
        <v-footer padless dark color="primary" app>
            <v-col class="text-center" cols="12">
                {{ date("Y") }} — <strong>EDI</strong>
            </v-col>
        </v-footer>
    </v-app>
</div>


<script src="{{ asset('js/report.js') }}"></script>
</body>

</html>
