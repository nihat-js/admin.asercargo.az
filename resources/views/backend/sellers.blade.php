@extends('backend.app')
@section('title')
    Sellers
@endsection
@section('actions')
    <li>
        <a onclick="show_add_modal();" class="action-btn"><span class="glyphicon glyphicon-plus-sign"></span> Add</a>
    </li>
    <li>
        <a onclick="show_update_modal();" class="action-btn"><span class="glyphicon glyphicon-edit"></span> Edit</a>
    </li>
    <li>
        <a class="action-btn" onclick="del('{{route("delete_seller")}}')"><span
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
                           placeholder="Seller name" value="{{$search_arr['name']}}">
                    <button type="button" class="btn btn-primary search-input" onclick="search_data();">Search</button>
                </div>
            </div>
        </div>
        <div class="references-in">
            <table class="references-table">
                <thead>
                <tr>
                    <th class="columns" onclick="sort_by('id')">No</th>
                    <th class="columns" onclick="sort_by('name')">Title</th>
                    <th class="columns" onclick="sort_by('name')">Name</th>
                    <th class="columns" onclick="sort_by('url')">URL</th>
                    <th class="columns" onclick="sort_by('url')">Category</th>
                    <th>Icon</th>
                    <th class="columns" onclick="sort_by('created_at')">Created date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sellers as $seller)
                    <tr class="rows" id="row_{{$seller->id}}" onclick="select_row({{$seller->id}})">
                        <td>{{$seller->id}}</td>
                        <td id="title_{{$seller->id}}">{{$seller->title}}</td>
                        <td id="name_{{$seller->id}}">{{$seller->name}}</td>
                        <td id="url_{{$seller->id}}"><a target="_blank" href="{{$seller->url}}">{{$seller->url}}</a></td>
                        <td id="category_id_{{$seller->id}}" category_id="{{$seller->category_id}}">{{$seller->category}}</td>
                        <td style="width: 30px !important;" id="image_td_{{$seller->id}}">
                            @if(!empty($seller->img))
                                <span class="btn btn-danger btn-xs"
                                      onclick="show_image_from_url('{{$seller->img}}', '{{$seller->id}}', 'sellers');"><i
                                            class="glyphicon glyphicon-picture"></i></span>
                            @else
                                <span class="btn btn-warning btn-xs" disabled><i
                                            class="glyphicon glyphicon-picture"></i></span>
                            @endif
                        </td>
                        <td>{{$seller->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                {!! $sellers->links(); !!}
            </div>
        </div>
    </div>

    <!-- start add modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" data-backdrop="static"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title">Add seller</span>
                    </div>
                </div>
                <form id="form" class="add_or_update_form" action="/sellers/add" method="post" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div id="form_item_id"></div>
                    <div class="modal-body">
                        <div class="form row">
                            <div class="col-md-6">
                                <p class="title">
                                    <label for="title">Title: <font color="red">*</font></label>
                                    <input type="text" name="title" id="title" required="" maxlength="255" oninput="create_seller_name_with_title(this);">
                                </p>
                                <p class="name">
                                    <label for="name">Name: <font color="red">*</font></label>
                                    <input type="text" name="name" id="name" required="" maxlength="50">
                                </p>
                                <p class="url">
                                    <label for="url">URL: <font color="red">*</font></label>
                                    <input type="url" name="url" id="url" required="" maxlength="255">
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="sec">
                                    <label for="category_id">Default category:</label>
                                    <select name="category_id" id="category_id">
                                        <option value="">Select</option>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                </p>
                                <p class="img">
                                    <label for="img">Icon:</label>
                                    <input type="file" id="img" name="icon"
                                           accept=".jpeg,.png,.jpg,.gif,.svg'"/>
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

    <!-- Image modal -->
    <div class="modal fade" id="image-modal" tabindex="-1" role="dialog" data-backdrop="static"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div style="clear: both;"></div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-heading">
                        <span class="masha_index masha_index1" rel="1"></span><span
                                class="modal-title">Show image</span>
                    </div>
                </div>
                {{csrf_field()}}
                <div id="form_item_id"></div>
                <div class="modal-body">
                    <div id="image-modal-body"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')

@endsection

@section('js')
    <script>
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

        function show_add_modal() {
            $('#form_item_id').html("");
            $(".add_or_update_form").prop("action", "{{route("add_seller")}}");
            $('.modal-title').html('Add seller');

            $("#title").val("");
            $("#name").val("");
            $("#url").val("");
            $("#img").val("");
            $("#category_id").val("");

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
            $(".add_or_update_form").prop("action", "{{route("update_seller")}}");
            $('.modal-title').html('Update seller');

            $("#title").val($("#title_" + row_id).text());
            $("#name").val($("#name_" + row_id).text());
            $("#url").val($("#url_" + row_id).text());
            $("#category_id").val($("#category_id_" + row_id).attr("category_id"));
            $("#img").val("");

            $('#add-modal').modal('show');
        }
    </script>
@endsection
