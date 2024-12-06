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
      {{--  <div class="panel panel-default">
            <div class="panel-body">
                <div id="search-inputs-area" class="search-areas">
                   <select class="form-control search-input" id="search_values" column_name="status">
                        <option value="">Status</option>
                        @foreach($statuses as $status)
                            @if($status->id == $status_check)
                                <option selected value="{{$status->id}}">{{$status->status_en}}</option>
                            @else
                                <option value="{{$status->id}}">{{$status->status_en}}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>

                </div>
            </div>
        </div> --}}
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
                    <th class="columns">Shop</th>
                    <th class="columns" onclick="sort_by('item.price')">Invoice</th>
                    <th class="columns" onclick="sort_by('cur.name')">Currency</th>
                    <th>Invoice file exists</th>
                    <th class="columns" onclick="sort_by('item.invoice_uploaded_date')">Invoice uploaded date</th>
                </tr>
                </thead>
                <tbody>
                @if($packages == false)
                    <tr>
                        <td colspan="12"><h3>Not Found</h3></td>
                    </tr>
                @else
                    @if(count($packages) > 0)
                        @php($no = 0)
                        @foreach($packages as $package)
                            @php($no++)
                            @if($package->container_id != null)
                                @php($storage = 'CONTAINER' . $package->container_id)
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
                                <td>{{$package->seller}}</td>
                                <td>{{$package->price}}</td>
                                <td>{{$package->currency}}</td>
                                <td>{{$invoice_file_exists}}</td>
                                <td>{{$package->invoice_uploaded_date}}</td>
                            </tr>
                        @endforeach
                    @else
                        <td colspan="12"><h3>No packages!</h3></td>
                    @endif
                @endif
                </tbody>
            </table>
            <div>
                {{$packages->links()}}
            </div>
        </div>
    </div>

@endsection

@section('css')

@endsection

@section('js')

@endsection
