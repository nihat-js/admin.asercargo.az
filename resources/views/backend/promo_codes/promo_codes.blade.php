@extends('backend.app')
@section('title')
    Promo codes | Codes
@endsection
@section('actions')
    <li>
        <a class="action-btn" onclick="del('{{route("delete_promo_code")}}')"><span
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
        <div class="panel panel-default">
            <div class="panel-body">
                <div id="search-inputs-area" class="search-areas">
                    <select  class="form-control search-input used-type" id="search_values" column_name="used_type">
                        <option value="">All</option>
                        <option value="used">Used</option>
                        <option value="not_used">Not used</option>
                    </select>
                    <input type="text" class="form-control search-input" id="search_values" column_name="group"
                           value="{{$search_arr['group']}}" placeholder="Group">
                    <input type="text" class="form-control search-input" id="search_values" column_name="code"
                           value="{{$search_arr['code']}}" placeholder="Promo code">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('promo_codes.id')">#</th>
                    <th class="columns" onclick="sort_by('promo_codes.code')">Code</th>
                    <th class="columns" onclick="sort_by('promo_codes_groups.name')">Group</th>
                    <th class="columns" onclick="sort_by('promo_codes.percent')">Percent</th>
                    <th class="columns" onclick="sort_by('promo_codes.reserved_at')">Reserved date</th>
                    <th class="columns" onclick="sort_by('client.name')">Client</th>
                    <th class="columns" onclick="sort_by('promo_codes.real_price')">Real price</th>
                    <th class="columns" onclick="sort_by('promo_codes.discount')">Discount</th>
                    <th class="columns" onclick="sort_by('promo_codes.discounted_price')">Discounted price</th>
                    <th class="columns" onclick="sort_by('currency.name')">Currency</th>
                    <th class="columns" onclick="sort_by('promo_codes.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @php($no = 0)
                @foreach($codes as $code)
                    @php($no++)
                    @if($code->client_id != null)
                        @php($style = 'style="background-color: greenyellow;"')
                    @else
                        @php($style = '')
                    @endif
                    <tr class="rows" id="row_{{$code->id}}" onclick="select_row({{$code->id}})" {!! $style !!}>
                        <td>{{$no}}</td>
                        <td>{{$code->code}}</td>
                        <td>{{$code->group}}</td>
                        <td>{{$code->percent}}</td>
                        <td>{{$code->reserved_at}}</td>
                        <td><a target="_blank" href="{{route("show_clients")}}?suite={{$code->client_id}}">{{$code->client_name}} {{$code->client_surname}}</a></td>
                        <td>{{$code->real_price}}</td>
                        <td>{{$code->discount}}</td>
                        <td>{{$code->discounted_price}}</td>
                        <td>{{$code->currency}}</td>
                        <td>{{$code->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $codes->links(); !!}
            </div>
        </div>
    </div>
@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function(){
            let used_type = '{{$search_arr['used_type']}}';
            $(".used-type").val(used_type);
        });
    </script>
@endsection