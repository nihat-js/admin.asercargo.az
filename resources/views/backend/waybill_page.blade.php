@extends('backend.app')
@section('title')
    Waybill
@endsection
@section('content')
    <div class="col-md-12">
        @if(session('display') == 'block')
            <div class="alert alert-{{session('class')}}" role="alert">
                {{session('message')}}
            </div>
        @endif
            <div class="panel panel-default">
                <div class="panel-body">
                    <div id="search-inputs-area" class="search-areas">
                        <input type="text" class="form-control search-input" id="search_values" column_name="name" placeholder="track" value="">
                        <button type="button" class="btn btn-primary search-input" onclick="sendRequest()">Waybill</button>
                    </div>
                </div>
            </div>

    </div>

@endsection

@section('css')

@endsection
<script>
    function sendRequest() {
        var searchValue = document.getElementById('search_values').value;

        var url = "https://manager.asercargo.az/waybill" + "/" + encodeURIComponent(searchValue);
        window.open(url, '_blank');
    }
</script>
@section('js')

@endsection
