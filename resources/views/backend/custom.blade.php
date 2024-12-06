@extends('backend.app')
@section('title')
	Customs
@endsection

@section('content')

<style>

section{
    margin-top: 100px !important;
}

[data-tab-content] {
  display: none;
}

.active[data-tab-content] {
  display: block;
}


.tabs {
  display: flex;
  justify-content: space-around;
  list-style-type: none;
  margin: 0;
  padding: 0;
  border-bottom: 1px solid black;
}

.tab {
  cursor: pointer;
  padding: 10px;
}

.tab.active {
  background-color: #CCC;
}

.tab:hover {
  background-color: #AAA;
}

.tab-content {
  margin-left: 20px;
  margin-right: 20px;
}

.myForm{
    display: flex;
    flex-direction: column;
    margin-top: 50px;
}

</style>

    <ul class="tabs">
        <li data-tab-target="#home" class="active tab">Send Custom</li>
        <li data-tab-target="#pricing" class="tab">Declaration</li>
        <li data-tab-target="#about" class="tab">Delete Package</li>
        <li data-tab-target="#news" class="tab">AirwallBill</li>
        <li data-tab-target="#pack" class="tab">Put AWB</li>
        <li data-tab-target="#checkPackage" class="tab">Paketin yoxlanmasi</li>
        <li data-tab-target="#updatePackage" class="tab">Paketin yenilenmesi</li>
    </ul>

    <div class="tab-content">
        <div id="home" data-tab-content class="active">
            <form method="POST" action="{{ route('post_custom_response') }}">
                @csrf

                <div class="form-group row myForm">
                    <label for="ASR" class="col-md-4 col-form-label text-md-right">ASR</label>

                    <div class="row">
                        <div class="col-md-6">
                            <input id="sendCustom" type="text" class="form-control" name="sendCustom" value="" required autocomplete="sendCustom" autofocus>
                            
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Search" style=" margin-right: 25px;">
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <div id="pricing" data-tab-content>
            <form method="POST" action="{{ route('post_declaration') }}">
                @csrf

                <div class="form-group row myForm">
                    <label for="ASR" class="col-md-4 col-form-label text-md-right">ASR</label>

                    <div class="row">
                        <div class="col-md-6">
                            <input id="declaration" type="text" class="form-control" name="declaration" value="" required autocomplete="declaration" autofocus>
                            
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Search" style=" margin-right: 25px;">
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <div id="about" data-tab-content>
            <form method="POST" action="{{ route('post_custom_deleted') }}">
                @csrf

                <div class="form-group row myForm">
                    <label for="ASR" class="col-md-4 col-form-label text-md-right">ASR</label>

                    <div class="row">
                        <div class="col-md-6">
                            <input id="delete" type="text" class="form-control" name="delete" value="" required autocomplete="delete" autofocus>
                            
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Search" style=" margin-right: 25px;">
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <div id="news" data-tab-content>
            <form method="POST" action="{{ route('post_awb') }}">
                @csrf

                <div class="form-group row myForm">
                    <label for="ASR" class="col-md-4 col-form-label text-md-right">ASR</label>

                    <div class="row">
                        <div class="col-md-6">
                            <input id="awb" type="text" class="form-control" name="awb" value="" required autocomplete="awb" autofocus>
                            <input id="depeshNumber" type="text" class="form-control" name="depeshNumber" value="" required autocomplete="depeshNumber" autofocus>
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Search" style=" margin-right: 25px;">
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <div id="pack" data-tab-content>
            <form method="POST" action="{{ route('putAirWay') }}">
                @csrf

                <div class="form-group row myForm">
                    <label for="ASR" class="col-md-4 col-form-label text-md-right">ASR</label>

                    <div class="row">
                        <div class="col-md-6">
                            <input id="awb" type="text" class="form-control" name="awb" value="" required autocomplete="awb" autofocus>
                            <input id="ccn" type="text" class="form-control" name="ccn" value="" required autocomplete="ccn" autofocus>
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Search" style=" margin-right: 25px;">
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <div id="checkPackage" data-tab-content>
            <form method="POST" action="{{ route('checkPack') }}">
                @csrf

                <div class="form-group row myForm">
                    <label for="ASR" class="col-md-4 col-form-label text-md-right">ASR</label>

                    <div class="row">
                        <div class="col-md-6">
                            <input id="track" type="text" class="form-control" name="track" value="" required autocomplete="track" autofocus>
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Search" style=" margin-right: 25px;">
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <div id="updatePackage" data-tab-content>
            <form method="POST" action="{{ route('updatePackage') }}">
                @csrf

                <div class="form-group row myForm">
                    <div class="row">
                        <div class="col-md-6">
                        <input type="radio" name="type" id="carrier" value="1">
                            <label for="carrier">carrier</label><br>
                            <input type="radio" name="type" id="container" value="2">
                            <label for="container">container</label><br>
                            <input type="radio" name="type" id="last_container" value="3">
                            <label for="last_container">last_container</label><br>
                            <input type="radio" name="type" id="pos" value="4">
                            <label for="pos">pos</label><br>
                            <input type="radio" name="type" id="custom_id" value="5">
                            <label for="custom_id">Custom Type</label><br>
                            <input type="radio" name="type" id="title" value="6">
                            <label for="title">Title</label><br>
                            <input type="radio" name="type" id="las_status_id" value="7">
                            <label for="las_status_id">Last status id</label><br>


                            <label for="ASR" class="col-md-4 col-form-label text-md-right">ASR</label>
                            <input id="track" type="text" class="form-control" name="track" value="" required autocomplete="track" autofocus>
                            
                            
                            
                            <label for="value" class="col-md-4 col-form-label text-md-right">value</label>
                            <input id="value" type="text" class="form-control" name="value" value=""  autocomplete="value" autofocus>
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Search" style=" margin-right: 25px;">
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>

@endsection

@section('css')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
@endsection

@section('js')
<script>
    const tabs = document.querySelectorAll('[data-tab-target]')
    const tabContents = document.querySelectorAll('[data-tab-content]')

    tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const target = document.querySelector(tab.dataset.tabTarget)
        tabContents.forEach(tabContent => {
        tabContent.classList.remove('active')
        })
        tabs.forEach(tab => {
        tab.classList.remove('active')
        })
        tab.classList.add('active')
        target.classList.add('active')
    })
    })
</script>
@endsection