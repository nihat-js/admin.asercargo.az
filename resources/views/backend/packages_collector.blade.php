@extends('backend.app')
@section('title')
    Packages
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
                    <select  class="form-control search-input" id="search_values" column_name="flight">
                        <option value="">Flights</option>
                        @foreach($flights as $flight)
                            @if($flight->id == $search_arr['flight'])
                                <option selected value="{{$flight->id}}">{{$flight->name}}</option>
                            @else
                                <option value="{{$flight->id}}">{{$flight->name}}</option>
                            @endif
                        @endforeach
                    </select>
{{--                    <select  class="form-control search-input" id="search_values" column_name="awb">--}}
{{--                        <option value="">AWB</option>--}}
{{--                        @php($selected_awb = 'is not selected')--}}
{{--                        @foreach($awbs as $awb)--}}
{{--                            @if($awb->id == $search_arr['awb'])--}}
{{--                                <option selected value="{{$awb->id}}">{{$awb->series}}-{{$awb->number}}</option>--}}
{{--                                @php($selected_awb = $awb->series . '-' . $awb->number)--}}
{{--                            @else--}}
{{--                                <option value="{{$awb->id}}">{{$awb->series}}-{{$awb->number}}</option>--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
                    <select  class="form-control search-input" id="search_values" column_name="container">
                        <option value="">Containers</option>
                        @foreach($containers as $container)
                            @if($container->id == $search_arr['container'])
                                <option selected value="{{$container->id}}">CONTAINER{{$container->id}}</option>
                            @else
                                <option value="{{$container->id}}">CONTAINER{{$container->id}}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                    <button style="float: right;" type="button" class="btn btn-warning search-input" onclick="print_manifest();">Print</button>
                </div>
            </div>
        </div>
        <div class="references-in" id="print-div">
            @if($selected_flight != false)
                <h3>
                    <span class="flight-print">Flight {{$selected_flight->carrier}}{{$selected_flight->flight_number}} {{substr($selected_flight->plan_take_off, 0, 10)}}</span>
                    <span class="location-print">{{$selected_flight->departure}}-{{$selected_flight->destination}}</span>
                    <span class="awb-print">{{$selected_awb}}</span>
                </h3>
            @endif
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('package.number')">Track</th>
                    <th class="columns" onclick="sort_by('c.suite')">Suite</th>
                    <th class="columns" onclick="sort_by('c.name')">Client</th>
                    <th class="columns" onclick="sort_by('c.address1')">Client Address</th>
                    <th class="columns" onclick="sort_by('package.gross_weight')">Weight</th>
                    <th class="columns" onclick="sort_by('s.name')">Shop</th>
                    <th class="columns" onclick="sort_by('item.price')">Invoice</th>
                    <th class="columns" onclick="sort_by('item.quantity')">Count</th>
                    <th class="columns" onclick="sort_by('cat.name')">Category</th>
                    @if(Auth::user()->location() == 6)
                    <th class="columns">Sub Categor</th>
                    <th class="columns">Title</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @if($packages == false)
                    <tr>
                        <td colspan="10"><h3>Please select Flight or Container</h3></td>
                    </tr>
                @else
                    @if(count($packages) > 0)
                        @foreach($packages as $package)
                            @php($suite = $package->suite)
                            @php($client = $package->client_id)
                            @php($len = strlen($client))
                            @if($len < 6)
                                @for($i = 0; $i < 6 - $len; $i++)
                                    @php($client = '0' . $client)
                                @endfor
                            @endif
                            @php($seller = $package->seller_id == 0 || $package->seller_id == null ? $package->other_seller : $package->seller)
                            <tr class="rows" id="row_{{$package->id}}">
                                <td>{{$package->number}}</td>
                                <td>{{$suite}}{{$client}}</td>
                                <td>{{$package->client_name}} {{$package->client_surname}}</td>
                                <td>{{$package->client_address}}</td>
                                <td>{{$package->gross_weight}}</td>
                                <td>{{$seller}}</td>
                                <td>{{$package->price}} {{$package->currency}}</td>
                                <td>{{$package->quantity}}</td>
                                <td>{{$package->category}}</td>
                                @if(Auth::user()->location() == 6)
                                    <td>{{$package->subCat}}</td>
                                    <td>{{$package->title}}</td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <td colspan="8"><h3>No packages!</h3></td>
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
        function print_manifest() {
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
