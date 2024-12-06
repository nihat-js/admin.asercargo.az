@extends('backend.app')
@section('title')
    Courier | tariffs
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("admin_delete_region_tariff")}}')"><span
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
                    <input type="text" class="form-control search-input" id="search_values" column_name="name"
                           placeholder="tariff" value="{{$search_arr['name']}}">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('id')">#</th>
                    <th class="columns" onclick="sort_by('name')">Name</th>
                    <th class="columns" onclick="sort_by('from_weight')">From weight</th>
                    <th class="columns" onclick="sort_by('to_weight')">To weight</th>
                    <th class="columns" onclick="sort_by('static_price')">Static price</th>
                    <th class="columns" onclick="sort_by('dynamic_price')">Dynamic price</th>
                    <th class="columns" onclick="sort_by('created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tariffs as $tariff)
                    <tr class="rows" id="row_{{$tariff->id}}" onclick="select_row({{$tariff->id}})">
                        <td>{{$tariff->id}}</td>
                        <td id="name_{{$tariff->id}}" name="{{$tariff->name}}">{{$tariff->name}}</td>
                        <td id="from_weight_{{$tariff->id}}" name="{{$tariff->from_weight}}">{{$tariff->from_weight}}</td>
                        <td id="to_weight_{{$tariff->id}}" name="{{$tariff->to_weight}}">{{$tariff->to_weight}}</td>
                        <td id="static_price_{{$tariff->id}}" name="{{$tariff->static_price}}">{{$tariff->static_price}}</td>
                        <td id="dynamic_price_{{$tariff->id}}" name="{{$tariff->dynamic_price}}">{{$tariff->dynamic_price}}</td>
                        <td>{{$tariff->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $tariffs->links(); !!}
            </div>
        </div>
    </div>

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
                                class="modal-title"></span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="" method="post">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-12">
                                <div class="tab-content">
                                    <div id="english" class="tab-pane fade in active">
                                        <p class="name">
                                            <label for="name_en">Name: <font color="red">*</font></label>
                                            <input type="text" name="name" id="name" required="" maxlength="255">
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <p class="name">
                                    <label for="default_tariff">From weight: <font color="red">*</font></label>
                                    <input type="number" name="from_weight" id="from_weight" required="" step="0.001" min="0">
                                </p>
                            </div>
                            <div class="col-md-12">
                                <p class="name">
                                    <label for="default_tariff">To weight: <font color="red">*</font></label>
                                    <input type="number" name="to_weight" id="to_weight" required="" step="0.001" min="0">
                                </p>
                            </div>
                            <div class="col-md-12">
                                <p class="name">
                                    <label for="default_tariff">Static price: <font color="red">*</font></label>
                                    <input type="number" name="static_price" id="static_price" required="" step="0.001" min="0">
                                </p>
                            </div>
                            <div class="col-md-12">
                                <p class="name">
                                    <label for="default_tariff">Dynamic price: <font color="red">*</font></label>
                                    <input type="number" name="dynamic_price" id="dynamic_price" required="" step="0.001" min="0">
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

    <!-- start payment types modal-->
  
    <!-- /.end payment types modal-->
@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#form').ajaxForm({
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

        function show_add_modal() {
            $('#form_item_id').html("");
            $(".add_or_update_form").prop("action", "{{route('admin_add_region_tariff')}}");
            $('.modal-title').html('Add tariff');

            $("#name").val("");
            $("#from_weight").val('');
            $("#to_weight").val('');
            $("#static_price").val('');
            $("#dynamic_price").val('');

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

            let id_input = '<input type="hidden" name="id" value="' + row_id + '">';

            $('#form_item_id').html(id_input);
            $(".add_or_update_form").prop("action", "{{route('admin_update_region_tariff')}}");
            $('.modal-title').html('Update tariff');

            $("#name").val($("#name_" + row_id).attr("name"));
            $("#from_weight").val($("#from_weight_" + row_id).text());
            $("#to_weight").val($("#to_weight_" + row_id).text());
            $("#static_price").val($("#static_price_" + row_id).text());
            $("#dynamic_price").val($("#dynamic_price_" + row_id).text());


            $('#add-modal').modal('show');
        }

    
    </script>
@endsection