@extends('backend.app')
@section('title')
    Client's packages
@endsection
@section('actions')

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
                    <button style="float: right;" type="button" class="btn btn-warning search-input" onclick="print_declareds();">Print</button>

                </div>
            </div>
        </div>
        <div class="references-in" id="print-div">
            <table class="references-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th class="columns" onclick="sort_by('package.number')">Track</th>
                    <th class="columns" onclick="sort_by('package.internal_id')">ASR</th>
                    <th class="columns" onclick="sort_by('status.status_en')">Status</th>
                    <th>Storage</th>
                    <th class="columns" onclick="sort_by('package.gross_weight')">Weight</th>
                    <th class="columns" onclick="sort_by('item.price')">Invoice</th>
                    <th>Invoice file exists</th>
                </tr>
                </thead>
                <tbody>
                @if($packages == false)
                    <tr>
                        <td colspan="12"><h3>Enter Client ID or Track number (or ASR)</h3></td>
                    </tr>
                @else
                    @if(count($packages) > 0)
                        @php($no = 0)
                        @foreach($packages as $package)
                            @php($no++)
                            @if($package->container_id != null)
                                @php($storage = 'CN' . $package->container_id)
                            @elseif($package->position_id != null)
                                @php($storage = $package->position)
                            @else
                                @php($storage = '---')
                            @endif
                            @if($package->invoice_doc == null)
                                @php($invoice_file_exists = 'NO')
                            @else
                                @php($invoice_file_exists = 'YES')
                            @endif

                            <tr class="rows" id="row_{{$package->id}}" onclick="select_row({{$package->id}})">
                                <td>{{$no}}</td>
                                <td>{{$package->number}}</td>
                                <td>{{$package->internal_id}}</td>
                                @if($package->carrier_status_id == 2)
                                    <td style="background-color: green;">{{$package->status}}</td>
                                @else
                                    <td>{{$package->status}}</td>
                                @endif
                                <td>{{$storage}}</td>
                                <td>{{$package->gross_weight}}</td>
                                <td>{{$package->price}}</td>
                                <td>{{$invoice_file_exists}}</td>
                            </tr>
                        @endforeach
                    @else
                        <td colspan="12"><h3>No packages!</h3></td>
                    @endif
                @endif
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('css')
    <style>
        .flight-print {
            margin-right: 5%;
        }

        .awb-print {
            margin-left: 5%;
        }
    </style>
@endsection

@section('js')
    <script>
        function print_declareds() {
            let disp_setting = "toolbar=no,location=no,directories=no,menubar=no,";
            disp_setting += "scrollbars=no,left=0,top=0,resizable=yes,width=900, height=650,";
            let content_vlue = document.getElementById('print-div').outerHTML;
            let docprint = window.open("", "", disp_setting);
            docprint.document.open();
            docprint.document.write('<html><head><title></title>');
            docprint.document.write('<link rel="stylesheet" href="{{asset("backend/css/manifest.css")}}"  rel="stylesheet" type="text/css">');
            docprint.document.write('</head><body onLoad="self.print();window.close();">');
            docprint.document.write(content_vlue);
            docprint.document.write("</body></html>");
            docprint.document.close();
            docprint.focus();
        }
    </script>
@endsection
