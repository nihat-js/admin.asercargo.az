@extends('backend.app')
@section('title')
    Clients
@endsection
@section('actions')
    <li>
        <a onclick="show_export_client_packages_modal();" class="action-btn"><span
                    class="glyphicon glyphicon-export"></span> Export packages</a>
    </li>
    <li>
        <a onclick="show_balance_modal();" class="action-btn"><span class="glyphicon glyphicon-usd"></span> Balance</a>
    </li>
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_log_modal();" class="action-btn"><span class="	glyphicon glyphicon-cloud"></span> Log</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_client")}}')"><span
                    class="glyphicon glyphicon-trash"></span> Delete</a>
    </li>
    @if(Auth::user()->role() != 9)
    <li>
        <a class="action-btn"
           onclick="go_to_client_account('{{route("admin_login_client_account", 'client_id')}}')"><span
                    class="glyphicon glyphicon-log-in"></span> Go to client's account</a>
    </li>
    @endif
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
                    <input type="number" class="form-control search-input" id="search_values" column_name="suite"
                           placeholder="Suite" value="{{$search_arr['suite']}}">
                    <input type="number" class="form-control search-input" id="search_values" column_name="parent"
                           placeholder="Parent ID" value="{{$search_arr['parent']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="name"
                           placeholder="Name" value="{{$search_arr['name']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="surname"
                           placeholder="Surname" value="{{$search_arr['surname']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="passport"
                           placeholder="Passport" value="{{$search_arr['passport']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="address"
                           placeholder="Address" value="{{$search_arr['address']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="email"
                           placeholder="E-mail" value="{{$search_arr['email']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="phone"
                           placeholder="Phone" value="{{$search_arr['phone']}}">
                    <input type="text" class="form-control search-input" id="search_values" column_name="username"
                           placeholder="Username" value="{{$search_arr['username']}}">
                    @if(Auth::user()->role() != 9)
                    <select class="form-control search-input" id="search_values" column_name="contract">
                        <option value="">Contract</option>
                        @foreach($contracts as $contract)
                            @if($contract->id == $search_arr['contract'])
                                <option selected value="{{$contract->id}}">{{$contract->description}}</option>
                            @else
                                <option value="{{$contract->id}}">{{$contract->description}}</option>
                            @endif
                        @endforeach
                    </select>
                    @endif
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('users.id')">Suite</th>
                    @if(Auth::user()->role() != 9)
                        <th class="columns" onclick="sort_by('users.parent_id')">Parent</th>
                    @elseif(Auth::user()->id == 138165)
                        <th class="columns" onclick="sort_by('users.parent_id')">Parent</th>
                    @endif
                    <th class="columns" onclick="sort_by('users.username')">Username</th>
                    <th>First password</th>
                    <th class="columns" onclick="sort_by('users.name')">Name</th>
                    <th class="columns" onclick="sort_by('users.surname')">Surname</th>
                    <th class="columns" onclick="sort_by('users.passport_series')">Passport Series</th>
                    <th class="columns" onclick="sort_by('users.passport_number')">Passport No</th>
                    <th class="columns" onclick="sort_by('users.passport_fin')">Passport Fin</th>
                    <th class="columns" onclick="sort_by('users.birthday')">Birth date</th>
                    <th class="columns" onclick="sort_by('users.gender')">Gender</th>
                    <th class="columns" onclick="sort_by('users.balance')">Balance</th>
                    <th class="columns" onclick="sort_by('users.language')">Language</th>
                    @if(Auth::user()->role() != 9)
                        <th class="columns" onclick="sort_by('c.description')">Contract</th>
                    @endif
                    <th class="columns" onclick="sort_by('users.email')">E-mail</th>
                    <th class="columns" onclick="sort_by('users.phone1')">Phone</th>
                    <th class="columns" onclick="sort_by('users.address1')">Address</th>
                    <th class="columns" onclick="sort_by('users.zip1')">Zip code</th>
                    <th class="columns" onclick="sort_by('users.is_legality')">Legality</th>
                    <th class="columns" onclick="sort_by('users.is_partner')">Partner</th>
                    <th class="columns" onclick="sort_by('users.branch_id')">Branch office</th>
                    <th class="columns" onclick="sort_by('users.created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($clients as $client)
                    @php($suite = $client->suite)
                    @if(strlen($client->id) < 6)
                        @for($i = 0; $i < 6 - strlen($client->id); $i++)
                            @php($suite .= '0')
                        @endfor
                    @endif

                    
                    @if($client->is_legality == 1)
                       @php($bool = "Legal entity")
                    @else
                        @php($bool = "Customer")
                    @endif

                    @if($client->is_partner == 1)
                       @php($partner = "Partner")
                    @else
                        @php($partner = "---")
                    @endif

                    @if($client->gender == 0)
                        @php($gender = 'Female')
                    @else
                        @php($gender = 'Male')
                    @endif
                    @php($suite .= $client->id)
                    @php($address = $client->address1)
                    {{--                    @if(strlen($client->address2) > 0)--}}
                    {{--                        @php($address .= ', ' . $client->address2)--}}
                    {{--                    @endif--}}
                    {{--                    @if(strlen($client->address3) > 0)--}}
                    {{--                        @php($address .= ', ' . $client->address3)--}}
                    {{--                    @endif--}}
                    @php($zip = $client->zip1)
                    {{--                    @if(strlen($client->zip2) > 0)--}}
                    {{--                        @php($zip .= ', ' . $client->zip2)--}}
                    {{--                    @endif--}}
                    {{--                    @if(strlen($client->zip3) > 0)--}}
                    {{--                        @php($zip .= ', ' . $client->zip3)--}}
                    {{--                    @endif--}}
                    @php($phone = $client->phone1)
                    @if(strlen($client->phone2) > 0)
                        @php($phone .= ', ' . $client->phone2)
                    @endif
                    {{--                    @if(strlen($client->phone3) > 0)--}}
                    {{--                        @php($phone .= ', ' . $client->phone3)--}}
                    {{--                    @endif--}}
                    <tr class="rows" id="row_{{$client->id}}" onclick="select_row({{$client->id}})">
                        <td id="suite_{{$client->id}}">{{$suite}}</td>
                        @if(Auth::user()->role() != 9)
                        <td id="parent_id_{{$client->id}}">{{$client->parent_id}}</td>
                        @elseif(Auth::user()->id == 138165)
                        <td id="parent_id_{{$client->id}}">{{$client->parent_id}}</td>
                        @endif
                        <td id="username_{{$client->id}}">{{$client->username}}</td>
                        <td id="first_password_{{$client->id}}"><span class="btn btn-default btn-xs"
                                                                      onclick="show_password('{{$client->first_pass}}', {{$client->id}});">show password</span>
                        </td>
                        <td id="name_{{$client->id}}">{{$client->name}}</td>
                        <td id="surname_{{$client->id}}">{{$client->surname}}</td>
                        <td id="passport_series_{{$client->id}}">{{$client->passport_series}}</td>
                        <td id="passport_number_{{$client->id}}">{{$client->passport_number}}</td>
                        <td id="passport_fin_{{$client->id}}">{{$client->passport_fin}}</td>
                        <td id="birthday_{{$client->id}}">{{$client->birthday}}</td>
                        <td id="gender_{{$client->id}}" gender="{{$client->gender}}">{{$gender}}</td>
                        <td id="balance_{{$client->id}}" balance_usd="{{$client->balance}}"
                            balance_azn="{{$client->balance_azn}}">{{$client->balance}} USD<br/>{{$client->balance_azn}}
                            AZN
                        </td>
                        
                        <td id="language_{{$client->id}}">{{$client->language}}</td>
                        @if(Auth::user()->role() != 9)
                        <td id="contract_id_{{$client->id}}"
                            contract_id="{{$client->contract_id}}">{{$client->contract}}</td>
                        @endif
                        <td id="email_{{$client->id}}">{{$client->email}}</td>
                        <td id="phone_{{$client->id}}" phone1="{{$client->phone1}}"
                            phone2="{{$client->phone2}}">{{$phone}}</td>
                        <td id="address_{{$client->id}}" address1="{{$client->address1}}">{{$address}}</td>
                        <td id="zip_{{$client->id}}" zip1="{{$client->zip1}}">{{$zip}}</td>
                        {{--                        <td id="console_limit_{{$client->id}}">{{$client->console_limit}}</td>--}}
                        {{--                        <td id="console_option_{{$client->id}}">{{$client->console_option}}</td>--}}
                        {{--                        <td id="packing_service_id_{{$client->id}}" packing_service_id="{{$client->packing_service_id}}">{{$client->packing_service}}</td>--}}
                       <td id="is_legality_{{$client->id}}" bool="{{$client->is_legality}}">{{$bool}}</td>
                       <td id="is_partner_{{$client->id}}" partner="{{$client->is_partner}}">{{$partner}}</td>
                       <td id="branch_id_{{$client->id}}" branch_id="{{$client->branch_id}}">{{$client->branch_name}}</td>
                       <td>{{$client->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                @if(Auth::user()->role() != 9)
                    {!! $clients->links(); !!}
                
                @endif
            </div>
        </div>
    </div>

    <!-- start expprt client packages modal-->
    <div class="modal fade" id="export-client-packages-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span>Export client's packages
                    </div>
                </div>
                <form action="{{route("admin_export_client_packages")}}" method="post">
                    {{csrf_field()}}
                    <input type="hidden" name="client_id" id="packages_export_client_id" value="">
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-12">
                                <p class="sec">
                                    <label for="referral_packages">Add referral's packages:</label>
                                    <select name="referral_packages" id="referral_packages" required>
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <p class="sec">
                                    <label for="delivered_packages">Add delivered packages:</label>
                                    <select name="delivered_packages" id="delivered_packages" required>
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                    <div class="modal-footer">
                        <p class="submit">
                            <input type="reset" data-dismiss="modal" value="Cancel">
                            <input type="submit" value="Export" style=" margin-right: 25px;">
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.end expprt client packages modal-->

    <!-- start balance modal-->
    <div class="modal fade" id="balance-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span>Balance operations
                    </div>
                </div>
                <form id="balance_form" action="{{route("admin_set_client_balance")}}" method="post">
                    {{csrf_field()}}
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="balance_client_id">
                                    <label for="balance_client_id">Suite:</label>
                                    <input type="number" name="suite" id="balance_client_id" readonly required>
                                </p>
                                <p class="balance_azn">
                                    <label for="balance_azn">Balance (AZN):</label>
                                    <input type="text" id="balance_azn" readonly disabled>
                                </p>
                                <p class="balance_usd">
                                    <label for="balance_usd">Balance (USD):</label>
                                    <input type="text" id="balance_usd" readonly disabled>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="sec">
                                    <label for="balance_currency">Currency:</label>
                                    <select name="currency" id="balance_currency" required>
                                        <option value="3">AZN</option>
                                        <option value="1">USD</option>
                                    </select>
                                </p>
                                <p class="balance_amount">
                                    <label for="balance_amount">Amount:</label>
                                    <input type="number" name="amount" id="balance_amount" step="0.01" required>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                    <div class="modal-footer">
                        <p class="submit">
                            <input type="reset" data-dismiss="modal" value="Cancel">
                            <input type="submit" value="Save" style=" margin-right: 25px;">
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.end balance modal-->

    <!-- start add modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title">Add client</span>
                    </div>
                </div>
                <form id="add_form" class="add_or_update_form" action="/clients/add" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="parent_id">
                                    <label for="parent_id">Parent account:</label>
                                    <input type="number" name="parent_id" id="parent_id">
                                </p>
                                <p class="name">
                                    <label for="name">Name: <font color="red">*</font></label>
                                    <input type="text" name="name" id="name" required="" maxlength="255">
                                </p>
                                <p class="surname">
                                    <label for="surname">Surname: <font color="red">*</font></label>
                                    <input type="text" name="surname" id="surname" required="" maxlength="255">
                                </p>
                                <p class="passport_series">
                                    <label for="passport_series">Passport Series: <font color="red">*</font></label>
                                    <input type="text" name="passport_series" id="passport_series" required="">
                                </p>
                                <p class="passport_number">
                                    <label for="passport_number">Passport No: <font color="red">*</font></label>
                                    <input type="text" name="passport_number" id="passport_number" required="">
                                </p>
                                <p class="passport_fin">
                                    <label for="passport_fin">Passport Fin: <font color="red">*</font></label>
                                    <input type="text" name="passport_fin" id="passport_fin" required="">
                                </p>
                                <p class="address1">
                                    <label for="address1">Address 1: <font color="red">*</font></label>
                                    <input type="text" name="address1" id="address1" required="" maxlength="100">
                                </p>
                                <p class="zip1">
                                    <label for="zip1">Zip code 1:</label>
                                    <input type="text" name="zip1" id="zip1" maxlength="30">
                                </p>
                                <p class="sec gender">
                                    <label for="gender">Gender: <font color="red">*</font></label>
                                    <select name="gender" id="gender" required>
                                        <option value="1">Male</option>
                                        <option value="0">Female</option>
                                    </select>
                                </p>
                                {{--                                <p class="address2">--}}
                                {{--                                    <label for="address2">Address 2:</label>--}}
                                {{--                                    <input type="text" name="address2" id="address2" maxlength="100">--}}
                                {{--                                </p>--}}
                                {{--                                <p class="zip2">--}}
                                {{--                                    <label for="zip2">Zip code 2:</label>--}}
                                {{--                                    <input type="text" name="zip2" id="zip2" maxlength="3030">--}}
                                {{--                                </p>--}}
                                {{--                                <p class="address3">--}}
                                {{--                                    <label for="address3">Address 3:</label>--}}
                                {{--                                    <input type="text" name="address3" id="address3" maxlength="100">--}}
                                {{--                                </p>--}}
                                {{--                                <p class="zip3">--}}
                                {{--                                    <label for="zip3">Zip code 3:</label>--}}
                                {{--                                    <input type="text" name="zip3" id="zip3" maxlength="30">--}}
                                {{--                                </p>--}}
                                {{--                                <p class="phone3">--}}
                                {{--                                    <label for="phone3">Phone 3:</label>--}}
                                {{--                                    <input type="text" name="phone3" id="phone3" maxlength="30">--}}
                                {{--                                </p>--}}
                            </div>
                            <div class="col-md-6">
                                <p class="phone1">
                                    <label for="phone1">Phone 1: <font color="red">*</font></label>
                                    <input type="text" name="phone1" id="phone1" required="" minlength="12" maxlength="15" placeholder="994XXXXXXXXX">
                                </p>
                                <p class="phone2">
                                    <label for="phone2">Phone 2:</label>
                                    <input type="text" name="phone2" id="phone2" maxlength="30">
                                </p>
                                <p class="birthday">
                                    <label for="birthday">Birth date: <font color="red">*</font></label>
                                    <input type="date" name="birthday" id="birthday" required="">
                                </p>
                                <p class="email">
                                    <label for="email">E-mail: <font color="red">*</font></label>
                                    <input type="email" name="email" id="email" required="" maxlength="255">
                                </p>
                                {{--                                <p class="sec suite">--}}
                                {{--                                    <label for="suite">Suite: <font color="red">*</font></label>--}}
                                {{--                                    <select name="suite" id="suite" required>--}}
                                {{--                                        <option value="AZE">AZE</option>--}}
                                {{--                                        <option value="USA">USA</option>--}}
                                {{--                                    </select>--}}
                                {{--                                </p>--}}
                                <p class="sec contract_id">
                                    <label for="contract_id">Contract:</label>
                                    <select name="contract_id" id="contract_id">
                                        <option value="">None</option>
                                        @foreach($contracts as $contract)
                                            <option value="{{$contract->id}}">{{$contract->description}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="sec branch_id">
                                    <label for="branch_id">Branch office: <font color="red">*</font></label>
                                    <select name="branch_id" id="branch_id" required>
                                        <option value="">None</option>
                                        @foreach($branchs as $branch)
                                            <option value="{{$branch->id}}">{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="sec language">
                                    <label for="language">Language: <font color="red">*</font></label>
                                    <select name="language" id="language" required>
                                        <option value="AZ">AZ</option>
                                        <option value="RU">RU</option>
                                        <option value="EN">EN</option>
                                    </select>
                                </p>
                                {{--                                <p class="sec console_option">--}}
                                {{--                                    <label for="console_option">Console service:</label>--}}
                                {{--                                    <select name="console_option" id="console_option">--}}
                                {{--                                        <option value="">No console</option>--}}
                                {{--                                        <option value="weight">By weight</option>--}}
                                {{--                                        <option value="quantity">By quantity</option>--}}
                                {{--                                        <option value="select">By select</option>--}}
                                {{--                                    </select>--}}
                                {{--                                </p>--}}
                                {{--                                <p class="sec packing_service_id">--}}
                                {{--                                    <label for="packing_service_id">Packing service: <font color="red">*</font></label>--}}
                                {{--                                    <select name="packing_service_id" id="packing_service_id" required>--}}
                                {{--                                        @foreach($packing_services as $packing_service)--}}
                                {{--                                            <option value="{{$packing_service->id}}">{{$packing_service->title}}</option>--}}
                                {{--                                        @endforeach--}}
                                {{--                                    </select>--}}
                                {{--                                </p>--}}
                                {{--                                <p class="console_limit">--}}
                                {{--                                    <label for="console_limit">Console limit:</label>--}}
                                {{--                                    <input type="number" name="console_limit" id="console_limit">--}}
                                {{--                                </p>--}}
                                <p class="username">
                                    <label for="username">Username: <font color="red">*</font></label>
                                    <input type="text" name="username" id="username" required="" maxlength="255">
                                </p>
                                <p class="password">
                                    <label for="password">Password:</label>
                                    <input type="text" name="password" id="password" required="" minlength="6">
                                </p>

                                <p class="is_legality">
                                    <label for="is_legality">Legality:</label>
                                    <input type="checkbox" name="is_legality" id="is_legality">
                                </p>

                                <p class="is_partner">
                                    <label for="is_partner">Partner:</label>
                                    <input type="checkbox" name="is_partner" id="is_partner">
                                </p>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                    <div class="modal-footer">
                        <p class="submit">
                            <input type="reset" data-dismiss="modal" value="Cancel">
                            <input type="submit" value="Save" style=" margin-right: 25px;">
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.end add modal-->


   

    <!-- Modal log-->
    <div class="modal fade" id="logModal" role="dialog">
        <div class="modal-dialog">
        
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Modal Header</h4>
                </div>
                <div class="modal-body">
                <table>
                    <thead>
                        <tr>
                            <th>Deyisiklik</th>
                            <th>Tarix</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody id="log_modal_tbody" class="table">
                        <tr>
                            <td>
                            
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                
            </div>
            
        </div>
        </div>

 
    <input type="hidden" id="userId" value="<?php echo Auth::user()->role()?>" />

@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#add_form').ajaxForm({
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

        $(document).ready(function () {
            $('#balance_form').ajaxForm({
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

        function show_export_client_packages_modal() {
            if (row_id === 0) {
                swal(
                    'Warning',
                    'Please select item!',
                    'warning'
                );
                return false;
            }

            $("#packages_export_client_id").val(row_id);
            $("#delivered_packages").val('no');
            $("#referral_packages").val('no');

            $('#export-client-packages-modal').modal('show');
        }

        function show_balance_modal() {
            if (row_id === 0) {
                swal(
                    'Warning',
                    'Please select item!',
                    'warning'
                );
                return false;
            }

            $("#balance_client_id").val(row_id);
            $("#balance_usd").val($("#balance_" + row_id).attr("balance_usd"));
            $("#balance_azn").val($("#balance_" + row_id).attr("balance_azn"));

            $('#balance-modal').modal('show');
        }

        function show_add_modal() {
            $('#form_item_id').html("");
            $(".add_or_update_form").prop("action", "{{route("add_client")}}");
            $('.modal-title').html('Add client');

            $("#parent_id").show();
            $("#contract_id").show();
            $("p[class='parent_id']").show();
            $("p[class='sec contract_id']").show();
            $("#parent_id").val("");
            $("#name").val("");
            $("#surname").val("");
            $("#passport_series").val("");
            $("#passport_number").val("");
            $("#passport_fin").val("");
            $("#address1").val("");
            $("#zip1").val("");
            // $("#address2").val("");
            // $("#zip2").val("");
            // $("#address3").val("");
            // $("#zip3").val("");
            $("#phone1").val("");
            $("#phone2").val("");
            // $("#phone3").val("");
            $("#gender").val(1);
            $("#birthday").val("");
            $("#email").val("");
            // $("#suite").val("C");
            $("#contract_id").val(1);
            $("#language").val("AZ");
            // $("#console_option").val("");
            // $("#packing_service_id").val(1);
            // $("#console_limit").val("");
            $("#username").val("");
            $("#branch_id").val("");
            $("#password").val("").prop("required", true);
          
            if($("#is_legality").is('checked')){
                $("#is_legality").val(false);
            }else{
                $("#is_legality").val(true);
            }

            if($("#is_partner").is('checked')){
                $("#is_partner").val(false);
            }else{
                $("#is_partner").val(true);
            }

            $('#add-modal').modal('show');
        }

        function show_update_modal() {
            let id = 0;
            id = row_id;
            if (id === 0) {
                swal(
                    'Warning',
                    'Please select item!',
                    'warning'
                );
                return false;
            }
            // console.log(row_id);
            let id_input = '<input type="hidden" name="id" value="' + row_id + '">';

            $('#form_item_id').html(id_input);
            $(".add_or_update_form").prop("action", "{{route("update_client")}}");
            $('.modal-title').html('Update client');

            let filter_user = $('#userId').val();
         
            if(filter_user == 9){
                $("#parent_id").hide();
                $("#contract_id").hide();
                $("p[class='parent_id']").hide();
                $("p[class='sec contract_id']").hide();
            }else{
                $("#parent_id").val($("#parent_id_" + row_id).text());
                $("#contract_id").val($("#contract_id_" + row_id).attr("contract_id"));
                //console.log('contact: ', $("#contract_id").val($("#contract_id_" + row_id).attr("contract_id")));
            }
            $("#name").val($("#name_" + row_id).text());
            $("#surname").val($("#surname_" + row_id).text());
            $("#passport_series").val($("#passport_series_" + row_id).text());
            $("#passport_number").val($("#passport_number_" + row_id).text());
            $("#passport_fin").val($("#passport_fin_" + row_id).text());
            $("#address1").val($("#address_" + row_id).attr("address1"));
            $("#zip1").val($("#zip_" + row_id).attr("zip1"));
            // $("#address2").val($("#address_" + row_id).attr("address2"));
            // $("#zip2").val($("#zip_" + row_id).attr("zip2"));
            // $("#address3").val($("#address_" + row_id).attr("address3"));
            // $("#zip3").val($("#zip_" + row_id).attr("zip3"));
            $("#phone1").val($("#phone_" + row_id).attr("phone1"));
            $("#phone2").val($("#phone_" + row_id).attr("phone2"));
            // $("#phone3").val($("#phone_" + row_id).attr("phone3"));
            $("#gender").val($("#gender_" + row_id).attr("gender"));
            $("#birthday").val($("#birthday_" + row_id).text());
            $("#email").val($("#email_" + row_id).text());
            $("#suite").val($("#suite_" + row_id).attr("suite"));
            
            $("#language").val($("#language_" + row_id).text());
            // $("#console_option").val($("#console_option_" + row_id).text());
            // $("#packing_service_id").val($("#packing_service_id_" + row_id).attr("packing_service_id"));
            // $("#console_limit").val($("#console_limit_" + row_id).text());
            $("#username").val($("#username_" + row_id).text());
            //$("#branch_id").val($("#branch_id_" + row_id).text());
            $("#branch_id").val($("#branch_id_" + row_id).attr("branch_id"));
            //console.log($("#branch_id").val($("#branch_id_" + row_id).attr("branch_id")));
            $("#password").val("").prop("required", false);
            
            if($("#is_legality_" +row_id).text() === 'Customer'){
                $("#is_legality").prop('checked', false);
            } else {
                $("#is_legality").prop('checked', true);
            }
        
            if($("#is_partner_" +row_id).text() === 'Partner'){
                $("#is_partner").prop('checked', true);
            } else {
                $("#is_partner").prop('checked', false);
            }

            $('#add-modal').modal('show');
        }

        function show_log_modal(){
            
            let id = 0;
            id = row_id;
            if (id === 0) {
                swal(
                    'Warning',
                    'Please select item!',
                    'warning'
                );
                return false;
            }
        
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            
			$.ajax({
				url:'/clients/client-log',
				type:'POST',
                data:{
                    id:row_id,
                    '_token': CSRF_TOKEN,
                },
				success:function(result){
                    $('#logModal').modal('show');
                    const tbody = $("<tbody>");
                    const maping = result.map((v) => {
                        const changes = Object.keys(v.Changes).map((keys) => keys + "<br/>").toString();
                        return ({
                            changes: changes,
                            date: v.Date,
                            user: v.User
                        })
                    });


                    maping.forEach((v) => {
                        const tr = $('<tr>');
                        Object.values(v).forEach((value) => {
                            tr.append($('<td>').html(value));
                        });
                        tbody.append(tr);
                    });

                    $("#log_modal_tbody").html(tbody.children());
                    
				}
			});
    

            // console.log(logModal);
        }
    </script>

    <script>
        const phoneInput = document.getElementById('phone1');

        // Sayfa yüklendiğinde input değeri '994' olarak başlasın
        phoneInput.value = '994';

        phoneInput.addEventListener('input', function (e) {
            // Sadece rakamlara izin ver
            this.value = this.value.replace(/[^0-9]/g, '');

            // Başlangıcı '994' olarak sabit tut
            if (!this.value.startsWith('994')) {
                this.value = '994';
            }

            // '994'ten sonra maksimum 9 rakam girilmesine izin ver
            if (this.value.length > 12) {
                this.value = this.value.slice(0, 12);
            }
        });
    </script>

@endsection
