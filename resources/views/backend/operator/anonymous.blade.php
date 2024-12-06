<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Aser | Anonymous packages</title>

    <!-- Fonts -->
    <link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{ asset('css/anonymous.css?ver=0.0.1') }}"
          type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Alegreya&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.0.15/vuetify.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
          rel="stylesheet">
{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css">--}}
    <script>
			if ( navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ) {
				var root = document.getElementsByTagName('html')[0]
				root.setAttribute('class', 'ff')
			}
    </script>
</head>

<body>
<header id="header">
    <nav>
        <div class="header container">
            <div class="logo">
                <h2>Aser Express</h2>
            </div>
            <div class="user">
                <h2>{{Auth::user()->name . ' ' . Auth::user()->surname }}</h2>
            </div>
            <div class="back">
                @if(Auth::user()->role() == 3)
                    {{--for collector user--}}
                    <a href="{{route("get_collector")}}">
                        <p>Go Back</p>
                    </a>
                @else
                    <a href="{{route("operator_page")}}">
                        <p>Go Back</p>
                    </a>
                @endif
            </div>
            <div class="logout">
                <a href="{{route('logout')}}">
                    <svg style="width:35px;height:35px" viewBox="0 0 24 24">
                        <path fill="currentColor"
                              d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z"/>

                    </svg>
                    <p>Log Out</p>
                </a>
            </div>
        </div>
    </nav>
</header>
<div id="anonymous">
    @if(Auth::user()->role() == 3)
        {{--for collector user--}}
        <my-anonymous my-package="{{route('collector_show_anonymous_orders')}}" my-client="{{route('collector_anonymous_client_control')}}"  my-merge="{{route('collector_merge_client_and_package')}}" admin="{{Auth::user()->username }}"></my-anonymous>
    @else
        <my-anonymous my-package="{{route('show_anonymous_orders')}}" my-client="{{route('anonymous_client_control')}}"  my-merge="{{route('merge_client_and_package')}}" admin="{{Auth::user()->username }}"></my-anonymous>
    @endif
</div>

<script src="{{ asset('js/anonymous.js?ver=0.0.1') }}"></script>
</body>

</html>
