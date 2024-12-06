@extends('backend.app')
@section('title')
    Containers
@endsection
@section('actions')
    <li>
        <a class="action-btn" onclick="del('{{route("delete_container")}}')"><span
                class="glyphicon glyphicon-trash"></span> Delete</a>
    </li>
@endsection
@section('content')
    <div class="col-md-12">
        @if(session('display') == 'block')
            <div class="alert alert-{{session('class')}}" role="alert">
                {{session('message')}}
            </div>
        @endif
    </div>
    <div class="dol-md-12">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div id="search-inputs-area" class="search-areas">
                        <select class="form-control search-input" id="search_values" column_name="flight">
                            <option value="">Flight</option>
                            @foreach($flights as $flight)
                                @if($flight->id == $search_arr['flight'])
                                    <option selected
                                            value="{{$flight->id}}">{{$flight->carrier}} {{$flight->flight_number}}</option>
                                @else
                                    <option
                                        value="{{$flight->id}}">{{$flight->carrier}} {{$flight->flight_number}}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search
                        </button>
                    </div>
                </div>
            </div>
            <div class="references-in">
                <table class="references-table">
                    <thead>
                    <tr>
                        <th class="columns" onclick="sort_by('container.id')">Name</th>
                        <th class="columns" onclick="sort_by('flt.carrier')">Flight</th>
                        <th class="columns" onclick="sort_by('dep.name')">Departure</th>
                        <th class="columns" onclick="sort_by('des.name')">Destination</th>
                        <th class="columns" onclick="sort_by('awb.series')">AWB</th>
                        <th>Packages count</th>
                        <th>Total weight</th>
                        <th>Print</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($containers as $container)
                        @php($color = "#1f314c")
                        @if(strlen($container->close_date) > 0)
                            @php($color = "red")
                        @endif
                        <tr class="rows" id="row_{{$container->id}}" onclick="select_row({{$container->id}})">
                            <td style="color: {{$color}};">CONTAINER{{$container->id}}</td>
                            <td>{{$container->airline}} {{$container->flight_number}}</td>
                            <td>{{$container->departure}}</td>
                            <td>{{$container->destination}}</td>
                            <td>{{$container->awb}}</td>
                            <td>{{$container->packages_count}}</td>
                            <td>{{$container->total_weight}}</td>
                            <td>
                                <span class="btn btn-primary btn-xs" onclick="print_container('{{$container->id}}', '{{$container->awb}}', '{{$container->airline}} {{$container->flight_number}}', '{{$container->dep}}', '{{$container->des}}');"><i class="glyphicon glyphicon-print"></i></span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div>
                    {!! $containers->links(); !!}
                </div>
            </div>
        </div>
        <div class="col-md-4" style="background-color: #f8ffc2;">
            <form id="form" class="add_or_update_form" action="{{route("add_container")}}" method="post">
                {{csrf_field()}}
                <div class="form row">
                    <div class="col-md-6">
                        <p class="name">
                            <label for="count">Count: <font color="red">*</font></label>
                            <input type="number" name="count" id="count" required="" min="1" value="1">
                        </p>
                        <p class="sec">
                            <label for="flight_id">Flight: <font color="red">*</font></label>
                            <select name="flight_id" id="flight_id" required>
                                <option value="">Select</option>
                                @foreach($flights as $flight)
                                    <option
                                        value="{{$flight->id}}">{{$flight->carrier}} {{$flight->flight_number}}</option>
                                @endforeach
                            </select>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="sec">
                            <label for="departure_id">Departure: <font color="red">*</font></label>
                            <select name="departure_id" id="departure_id" required>
                                <option value="">Select</option>
                                @foreach($locations as $location)
                                    @if($location->id == 2)
                                        <option selected value="{{$location->id}}">{{$location->name}}</option>
                                    @else
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </p>
                        <p class="sec">
                            <label for="destination_id">Destination: <font color="red">*</font></label>
                            <select name="destination_id" id="destination_id" required>
                                <option value="">Select</option>
                                @foreach($locations as $location)
                                    @if($location->id == 1)
                                        <option selected value="{{$location->id}}">{{$location->name}}</option>
                                    @else
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </p>
                    </div>
                </div>
                <div class="form row">
                    <div class="col-md-12">
                        <p class="submit">
                            <input type="reset" value="Cancel">
                            <input type="submit" value="Save" style=" margin-right: 25px;">
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="test25" style="display: none;">
        <table style="width:90%;border: 1px solid black;" id="printTable">
            <thead>
            <tr>
                <td colspan="3" style="text-align: center;" id="awb-number"></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style="text-align: center;" id="flight-no"></td>
                <td style="text-align: center;" id="dep"></td>
                <td style="text-align: center;" id="des"></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">
                    <div id="barcode"></div>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;" id="barcode-text"></td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

@section('css')

@endsection

@section('js')
    <script src="{{asset("backend/js/jquery-barcode.js")}}"></script>

    <script>
        function print_container(container, awb, flight, dep, des) {
            create_barcode(container);
            $("#awb-number").html(awb);
            $("#flight-no").html(flight);
            $("#dep").html(dep);
            $("#des").html(des);

            let disp_setting = "toolbar=no,location=no,directories=no,menubar=no,";
            disp_setting += "scrollbars=no,left=0,top=0,resizable=yes,width=900, height=650,";
            let content_vlue = document.getElementById('test25').innerHTML;
            let docprint = window.open("", "", disp_setting);
            docprint.document.open();
            docprint.document.write('<html><head><title></title>');
            docprint.document.write('<link rel="stylesheet" href="{{asset("backend/css/container-print.css")}}"  rel="stylesheet" type="text/css">');
            docprint.document.write('</head><body onLoad="self.print();window.close();">');
            docprint.document.write(content_vlue);
            docprint.document.write("</body></html>");
            docprint.document.close();
            docprint.focus();
        }

        $(document).ready(function () {
            $('form').ajaxForm({
                beforeSubmit: function () {
                    //loading
                    swal({
                        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                        text: 'Loading, please wait...',
                        showConfirmButton: false
                    });
                },
                success: function (response) {
                    form_submit_message(response);
                }
            });
        });

        function create_barcode(code) {
            var settings = {
                barWidth: 1,
                barHeight: 50,
                moduleSize: 30,
                showHRI: true,
                addQuietZone: true,
                marginHRI: 5,
                bgColor: "#FFFFFF",
                color: "#000000",
                fontSize: 0,
                output: "css",
                posX: 0,
                posY: 0
            };

            $("#barcode-text").html("CONTAINER" + code);
            $("#barcode").barcode(
                "CN" + code, // Value barcode (dependent on the type of barcode)
                "code128", // type (string)
                settings
            );
        }
    </script>
@endsection
