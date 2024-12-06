function del(url) {
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

    swal({
        title: 'Do you approve the deletion?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "delete",
                url: url,
                data: {
                    'id': id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        $('#row_' + response.id).remove();
                        swal({
                            position: 'top-end',
                            type: response.case,
                            title: response.title,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function go_to_client_account(url) {
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

    url = url.replace('client_id', id);

    swal({
        title: 'Are you sure you want to login to the client\'s account?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "get",
                url: url,
                data: {
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        location.href = response.url;
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function delete_image(url, id) {
    let real_url = "/" + url + "/delete/image";
    swal({
        title: 'Do you approve the deletion document?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "delete",
                url: real_url,
                data: {
                    'id': id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        $("#image_td_" + id).html('<span class="btn btn-warning btn-xs" disabled><i class="glyphicon glyphicon-picture"></i></span>');
                        $("#image-modal-body").html("");
                        $("#image-modal").modal('hide');
                        swal({
                            position: 'top-end',
                            type: response.case,
                            title: response.title,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function get_default_category_for_seller(url, seller, type= true, url_for_new_seller = "") {
    if (type === false) {
        //entered from input
        seller = $(seller).val();
    }

    if (seller === 'new') {
        show_add_new_seller_modal(url_for_new_seller);
        return false;
    }

    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'seller': seller,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let default_category = response.category;
                if (default_category !== null) {
                    $("#category").val(default_category);
                } else {
                    $("#category").val('');
                }
            } else {
                show_alert_message("danger", response.title, response.content);
            }
        }
    });
}

function set_to_default_contract(url) {
    let id = 0;
    id = row_id;
    if (id === 0) {
        swal(
            'Warning',
            'Please select contract!',
            'warning'
        );
        return false;
    }

    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'contract_id': id,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                $(".rows").removeClass("default_contract");
                $("#row_" + id).addClass("default_contract");
                // $(".detail-rows").removeClass("default_contract");
                // $("#detail_row_" + id).addClass("default_contract");

                swal({
                    position: 'top-end',
                    type: response.case,
                    title: response.title,
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                swal(
                    response.title,
                    response.content,
                    response.case
                );
            }
        }
    });
}

function show_contract_details(e, url) {
    detail_id = 0;
    let id = 0;
    id = row_id;
    if (id === 0) {
        swal(
            'Warning',
            'Please select contract!',
            'warning'
        );
        return false;
    }

    let country_id_for_show_detail = $(e).val();

    if (country_id_for_show_detail === 0 || country_id_for_show_detail === "") {
        swal(
            'Warning',
            'Please select country!',
            'warning'
        );
        return false;
    }

    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "get",
        url: url,
        data: {
            'contract_id': id,
            'country_id': country_id_for_show_detail,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let details = response.details;
                let detail;
                let tr;
                let table = '';
                let i;
                let weight_control = '';
                let def_class = '';
                let start_date = '';
                let end_date = '';
                let switch_url = '/contracts/details/volume-consider';
                let switch_checked;

                for (i = 0; i < details.length; i++) {
                    detail = details[i];
                    let detail_id = detail['id'];

                    if (detail['weight_control'] === 1) {
                        weight_control = 'Active';
                    } else {
                        weight_control = 'Disable';
                    }

                    // if (detail['default_option'] === 1) {
                    //     def_class = 'default_contract';
                    //     start_date = '<td id="detail_start_date_' + detail_id + '" style="color: red;">' + detail['start_date'] + '</td>';
                    //     end_date = '<td id="detail_end_date_' + detail_id + '" style="color: red;">' + detail['end_date'] + '</td>';
                    // } else {
                    //     def_class = '';
                    //     start_date = '<td id="detail_start_date_' + detail_id + '">' + detail['start_date'] + '</td>';
                    //     end_date = '<td id="detail_end_date_' + detail_id + '">' + detail['end_date'] + '</td>';
                    // }

                    start_date = '<td id="detail_start_date_' + detail_id + '">' + detail['start_date'] + '</td>';
                    end_date = '<td id="detail_end_date_' + detail_id + '">' + detail['end_date'] + '</td>';

                    switch_checked = '';
                    if (detail['weight_control'] === 1) {
                        switch_checked = 'checked';
                    }

                    let switch_tr = '<label class="switch">' +
                        '<input type="checkbox" ' + switch_checked + ' oninput="change_volume_switch(this, ' + detail_id + ', \'' + switch_url + '\');">' +
                        '<span class="slider round"></span>' +
                        '</label>';

                    tr = '<tr class="detail-rows ' + def_class + '" id="detail_row_' + detail['id'] + '" onclick="select_contract_detail(' + detail['id'] + ');">';
                    tr += '<td>' + detail['id'] + '</td>';
                    tr += '<td id="detail_type_id_' + detail_id + '" type_id="' + detail['type_id'] + '">' + detail['type'] + '</td>';
                    tr += '<td id="detail_service_name_' + detail_id + '">' + detail['service_name'] + '</td>';
                    tr += '<td id="detail_title_' + detail_id + '" az="' + detail['title_az'] + '" en="' + detail['title_en'] + '" ru="' + detail['title_ru'] + '">' + detail['title_en'] + '</td>';
                    tr += '<td id="detail_description_' + detail_id + '" az="' + detail['description_az'] + '" en="' + detail['description_en'] + '" ru="' + detail['description_ru'] + '">' + detail['description_en'] + '</td>';
                    tr += '<td id="detail_country_id_' + detail_id + '" country_id="' + detail['country_id'] + '">' + detail['country'] + '</td>';
                    tr += '<td id="detail_departure_id_' + detail_id + '" departure_id="' + detail['departure_id'] + '">' + detail['departure'] + '</td>';
                    tr += '<td id="detail_destination_id_' + detail_id + '" destination_id="' + detail['destination_id'] + '">' + detail['destination'] + '</td>';
                    tr += '<td id="detail_seller_id_' + detail_id + '" seller_id="' + detail['seller_id'] + '">' + detail['seller'] + '</td>';
                    tr += '<td id="detail_category_id_' + detail_id + '" category_id="' + detail['category_id'] + '">' + detail['category'] + '</td>';
                    tr += '<td id="detail_from_weight_' + detail_id + '">' + detail['from_weight'] + '</td>';
                    tr += '<td id="detail_to_weight_' + detail_id + '">' + detail['to_weight'] + '</td>';
                    tr += '<td id="detail_weight_control_' + detail_id + '" detail_weight_control="' + detail['weight_control'] + '">' + switch_tr + '</td>';
                    tr += '<td id="detail_rate_' + detail_id + '">' + detail['rate'] + '</td>';
                    tr += '<td id="detail_charge_' + detail_id + '">' + detail['charge'] + '</td>';
                    tr += '<td id="detail_currency_id_' + detail_id + '" currency_id="' + detail['currency_id'] + '">' + detail['currency'] + '</td>';
                    tr += start_date;
                    tr += end_date;
                    tr += '<td>' + detail['created_at'] + '</td>';

                    tr += '</tr>';
                    table += tr;
                }

                $("#details-body").html(table);

                $("#details-modal").modal("show");
            } else {
                swal(
                    response.title,
                    response.content,
                    response.case
                );
            }
        }
    });
}

function delete_contract_detail(url, d_id) {
    swal({
        title: 'Do you approve the deletion?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "delete",
                url: url,
                data: {
                    'id': d_id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        $('#detail_row_' + response.id).remove();
                        swal({
                            position: 'top-end',
                            type: response.case,
                            title: response.title,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function change_volume_switch(e, id, url) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    let switch_val = 0;
    if ($(e).is(':checked')) {
        switch_val = 1;
    } else {
        switch_val = 2;
    }

    $.ajax({
        type: "post",
        url: url,
        data: {
            'id': id,
            'switch': switch_val,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                swal({
                    position: 'top-end',
                    type: response.case,
                    title: response.title,
                    showConfirmButton: false,
                    timer: 1000
                });
            } else {
                show_alert_message("danger", response.title, response.content);
            }
        }
    });
}

function check_package_collector(url, number, clear = false) {
    $("#container_details_name").html('');
    $("#container_details_count").html('');
    $("#container_details_weight").html('');
    $("#container_details_area").css('display', 'none');
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'number': number,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            hide_alert_message();
            clear_values();
            if (response.case === 'success') {
                update_values(response, number); //collector.js

                let client_packages = response['client_packages'];
                if (client_packages !== null) {
                    show_client_other_packages(client_packages);
                }
            } else {
                show_alert_message("danger", response.title, response.content);
            }
            if (clear === true) {
                clear_values(false);
            }
        }
    });
}

function save_collector(url) {
    category = $("#category").val();
    hide_alert_message();
    let client_val = $("#client").val();
    if (client_val.length > 1) {
        client_id = parseInt(client_val.substr(2, client_val.length));
    } else {
        show_alert_message('warning', 'Oops!', "Client format is wrong!");
        return false;
    }
    if (client_id === 0) {
        if (client_name_surname === null || client_name_surname === "") {
            show_alert_message('warning', 'Oops!', "Client name and surname must be entered for unknown package!");
            return false;
        }
    }
    if (package_status == 36 && client_id !== 0) {
        show_alert_message('warning', 'Oops!', "Unknown client status can only be selected for unknown packages!");
        return false;
    }
    if ((container === null || container === "") && (position === null || position === "")) {
        show_alert_message('warning', 'Oops!', 'No container or position selected!');
        return false;
    }
    if (tracking_number === null || tracking_number === "") {
        show_alert_message('warning', 'Oops!', "Tracking number can't be empty!");
        return false;
    }
    if (destination === null || destination === "") {
        show_alert_message('warning', 'Oops!', "Destination can't be empty!");
        return false;
    }
    // if (length === null || length === "" || length === 0) {
    //     show_alert_message('warning', 'Oops!', "Length can't be empty!");
    //     return false;
    // }
    // if (height === null || height === "" || height === 0) {
    //     show_alert_message('warning', 'Oops!', "Height can't be empty!");
    //     return false;
    // }
    // if (width === null || width === "" || width === 0) {
    //     show_alert_message('warning', 'Oops!', "Width can't be empty!");
    //     return false;
    // }
    if (client_id === null) {
        show_alert_message('warning', 'Oops!', "Client can't be empty!");
        return false;
    }
    if (package_status === null || package_status === "" || package_status === 0) {
        show_alert_message('warning', 'Oops!', "Status can't be empty!");
        return false;
    }
    if (tariff_type === null || tariff_type === "" || tariff_type === 0) {
        show_alert_message('warning', 'Oops!', "Type can't be empty!");
        return false;
    }
    if (gross_weight === null || gross_weight === "" || gross_weight == 0) {
        show_alert_message('warning', 'Oops!', "Gross weight can't be empty!");
        return false;
    }
    // if ((category === null || category === "") && package_status != 6 && package_status != 9) {
    //     show_alert_message('warning', 'Oops!', 'Category cannot be empty if status is not "no invoice" or "incorrect invoice"!');
    //     return false;
    // }
    // if ((seller === null || seller === "") && package_status != 6 && package_status != 9) {
    //     show_alert_message('warning', 'Oops!', 'Seller cannot be empty if status is not "no invoice" or "incorrect invoice"!');
    //     return false;
    // }
    // if ((invoice === null || invoice === "") && package_status == 5) {
    //     show_alert_message('warning', 'Oops!', 'Invoice cannot be empty if status is "ready for carriage"!');
    //     return false;
    // }
    if (quantity === null || quantity === "" || quantity === 0) {
        show_alert_message('warning', 'Oops!', "Quantity can't be empty!");
        return false;
    }

    if (container !== null && container !== '' && client_id === 0) {
        show_alert_message('warning', 'Oops!', 'Unknown packages cannot be placed in the container!');
        return false;
    }

    if (container !== null && container !== '' && package_status != 5) {
        show_alert_message('warning', 'Oops!', 'Packages which status is "Ready for carriage" can be placed in the container!');
        return false;
    }

    if (position !== null && position !== '' && package_status == 5) {
        show_alert_message('warning', 'Oops!', 'Packages which status is "Ready for carriage" cannot be placed in the position!');
        return false;
    }

    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    let formData = new FormData();

    let total_images = $("#images")[0].files.length;
    let images_for_ajax = $("#images")[0];

    for (let i = 0; i < total_images; i++) {
        formData.append("images" + i, images_for_ajax.files[i]);
    }
    formData.append("total_images", total_images);

    formData.append('number', tracking_number);
    formData.append('length', length);
    formData.append('height', height);
    formData.append('width', width);
    formData.append('client_id', client_id);
    formData.append('client_name_surname', client_name_surname);
    formData.append('seller', seller);
    formData.append('destination', destination);
    formData.append('gross_weight', gross_weight);
    formData.append('currency', currency);
    formData.append('category', category);
    formData.append('invoice', invoice);
    formData.append('quantity', quantity);
    formData.append('container_id', container);
    formData.append('position', position);
    formData.append('tracking_internal_same', tracking_internal_same);
    formData.append('status_id', package_status);
    formData.append('description', package_description);
    formData.append('tariff_type_id', tariff_type);
    if(legality == 1 && client_id != 0){
        formData.append('is_legal_entity', 'on');
    }else{
        formData.append('is_legal_entity', is_legal_entity);
    }
    // formData.append('invoice_status', invoice_status);
    formData.append('invoice_status',$('#invoice_status').val())
    formData.append('title', product_title);
    formData.append('subCat', subCat);
    let settings = {headers: {'content-type': 'multipart/form-data',processData: false}};
    $.ajax({
        type: "post",
        url: url,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        processData: false,
        contentType: false,
        data: formData,
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let flight_details = response.flight_details;
                if (flight_details !== false) {
                    flight_departure = flight_details['departure'];
                    flight_destination = flight_details['destination'];
                    flight_date = flight_details['plan_take_off'];
                }
                let amount = response['amount_response']['amount'] + ' ' + response['amount_response']['currency'];
                package_amount = amount;
                let internal_id = response['internal_id'];
                package_internal_id = internal_id;
                item_add_to_table(amount, internal_id);
                check_package_collector(check_package_url, tracking_number, true);
                generate_waybill_content_for_print();
                clear_values();
                show_alert_message(response.case, response.title, response.content);
                $("#waybill_doc").removeClass("btn-danger").addClass("btn-success");
                waybill_print_access = true;

                let has_container_details = response.has_container_details;
                let container_details = response.container_details;
                if (has_container_details) {
                    let container_name = container_details['container'];
                    let container_package_count = container_details['count'];
                    let container_total_weight = container_details['weight'];

                    $("#container_details_name").html(container_name);
                    $("#container_details_count").html(container_package_count);
                    $("#container_details_weight").html(container_total_weight);
                    $("#container_details_area").css('display', 'block');
                }
            } else {
                let message_type = 'warning';
                if (response.case === 'error') {
                    message_type = 'danger';
                }
                if (response.type === 'validation') {
                    let content = response.content;
                    let validation_message = '';
                    $.each(content, function (index, value) {
                        if (value.length !== 0) {
                            for (let i = 0; i < value.length; i++) {
                                validation_message += value[i] + '\n';
                            }
                        }
                    });
                    show_alert_message(message_type, response.title, validation_message);
                } else {
                    show_alert_message(message_type, response.title, response.content);
                }
                $("#waybill_doc").removeClass("btn-success").addClass("btn-danger");
                waybill_print_access = false;
            }
        }
    });
}

function select_flight(e, url) {
    let flight_id = $(e).val();

    clear_values();
    container = "";
    position = "";
    $("#cont_or_pos").val("");
    $("#container").addClass("active-sec");
    $("#position").removeClass("active-sec");

    $("#container_select").html('<option value="">Containers</option>').val("").prop("disabled", true);

    if (flight_id === 0 || flight_id === '' || flight_id === null) {
        show_alert_message("warning", 'Oops!', 'Flight not selected!');
        return false;
    }

    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "get",
        url: url,
        data: {
            'flight_id': flight_id,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let containers = response.containers;
                let i;
                let container;
                let options = '<option value="">Containers</option>';
                let option;

                options += '<option value="CNNEWCN_' + flight_id + '">NEW</option>';

                for (i = 0; i < containers.length; i++) {
                    container = containers[i];

                    option = '<option value="CN' + container['id'] + '">CN' + container['id'] + '</option>';
                    options += option;
                }

                $("#container_select").html(options).val('').prop("disabled", false);
            } else {
                show_alert_message("danger", response.title, response.content);
            }
        }
    });
}

function generate_internal_id(url) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                clear_values();

                let internal_id = response.internal_id;

                tracking_number = internal_id;
                $("#number").val(internal_id);

                $("#internal_id").val(internal_id);
                $(".internal_id").css('display', 'block');
                $("#invoice").val(" ");
                tracking_internal_same = 1;
                tracking_number_control = true;
            } else {
                show_alert_message("danger", response.title, response.content);
            }
        }
    });
}

function add_new_seller(url, seller) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'seller': seller,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let new_seller = response.seller;

                $("#seller").append('<option class="seller_for_only_collector" value="' + new_seller + '">' + new_seller + '</option>').val(new_seller);
            } else {
                show_alert_message("danger", response.title, response.content, response.type);
            }
        }
    });
}

function add_new_category(url, category) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'category': category,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let new_category = response.category;

                $("#category").append('<option value="' + new_category + '">' + new_category + '</option>>').val(new_category);
            } else {
                show_alert_message("danger", response.title, response.content, response.type);
            }
        }
    });
}

function close_flight(url) {
    let id = 0;
    id = row_id;
    if (id === 0) {
        swal(
            'Warning',
            'Please select flight!',
            'warning'
        );
        return false;
    }

    swal({
        title: 'Do you approve the flight closing?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "post",
                url: url,
                data: {
                    'id': id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        $("#closed_at_" + id).html(response.closed_at);
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function show_images_gallery_in_collector(show_url, delete_url) {
    if (package_id === 0 || package_id === '' || package_id === undefined) {
        show_alert_message("warning", "Oops!", "Package not found!");
        return false;
    }
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "get",
        url: show_url,
        data: {
            'package': package_id,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let images = response.images;
                let image;
                let gallery = '';
                let single_image;

                if (images.length === 0) {
                    show_alert_message("warning", "No images!", "No images!");
                    return false;
                }

                for (let i = 0; i < images.length; i++) {
                    image = images[i];
                    single_image = '<div class="responsive" id="gallery_image_' + image['id'] + '">';
                    single_image += '<div class="gallery">';
                    single_image += '<a target="_blank" href="' + image['url'] + '">';
                    single_image += '<img src="' + image['domain'] + image['url'] + '" alt="' + image['name'] + '" width="600" height="400">';
                    single_image += '</a>';
                    single_image += '<div class="delete_image"><span onclick="delete_image_in_collector(\'' + delete_url + '\', ' + image['id'] + ');" style="color: red;" ><i class="glyphicon glyphicon-trash"></i> Delete</span></div>';
                    single_image += '</div>';
                    single_image += '</div>';

                    gallery += single_image;
                }

                $("#images_gallery").html(gallery);
                $("#images-modal").modal("show");
            } else {
                show_alert_message("danger", response.title, response.content, response.type);
            }
        }
    });
}

function delete_image_in_collector(url, id) {
    if (id === 0 || id === '' || id === undefined) {
        show_alert_message("warning", "Oops!", "Image not found!");
        return false;
    }
    swal({
        title: 'Do you approve the deletion image?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "delete",
                url: url,
                data: {
                    'image': id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        $("#gallery_image_" + id).remove();
                    } else {
                        show_alert_message("danger", response.title, response.content, response.type);
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function create_new_container(flight_id) {
    if (flight_id === 0 || flight_id === '' || flight_id === null) {
        show_alert_message("danger", "Oops!", "Flight not found!");
        return false;
    }
    if (add_new_container_in_collector_url === '' || add_new_container_in_collector_url === null) {
        show_alert_message("danger", "Oops!", "URL not found!");
        return false;
    }
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: add_new_container_in_collector_url,
        data: {
            'flight': flight_id,
            '_token': CSRF_TOKEN
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let container = 'CN' + response.container;
                $("#container_select").append('<option value="' + container + '">' + container + '</option>');
                select_container(container, true);
            } else {
                show_alert_message("danger", response.title, response.content);
            }
        }
    });
}

function get_packages_for_distributor(url) {
  let flight = $("#flight").val();
  if (flight === '' || flight === 0 || flight === null) {
    swal(
      'Oops!',
      'Flight not found!',
      'warning'
    );
    return false;
  }
  swal({
    title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
    text: 'Loading, please wait...',
    showConfirmButton: false
  });
  let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  $.ajax({
    type: "get",
    url: url,
    data: {
      'flight': flight,
      '_token': CSRF_TOKEN,
    },
    success: function (response) {
      swal.close();
      if (response.case === 'success') {
        let packages = response.packages;
        let package;
        let total = 0;
        let body = '';
        let tr;

        for (let i = 0; i < packages.length; i++) {
          total++;
          package = packages[i];

          tr = '<tr id="' + package['track'] + '">';
          tr += '<td>' + package['track'] + '</td>';
          tr += '<td>' + package['internal_id'] + '</td>';
          tr += '<td>' + package['client_name'] + ' ' + package['client_surname'] + '</td>';
          tr += '<td>' + package['gross_weight'] + '</td>';

          body += tr;
        }

        $("#packages_list").html(body);
        $("#track_no").focus();
      } else {
        form_submit_message(response, false);
      }
    }
  });
}

function change_position_package(e, url) {
  let barcode = $(e).val();
  if (barcode === '' || barcode === null || barcode.length < 3) {
    swal(
      'Oops!',
      'Barcode not found!',
      'warning'
    );
    return false;
  }

  if(barcode.substr(0, 2).toUpperCase() === 'PS') {
    //position
    distributor_position = barcode.substr(2, barcode.length);
  } else {
    //package
    distributor_package = barcode;
    let len = distributor_package.length;
    if (distributor_package.substr(0, 8) === '42019801') {
      distributor_package = distributor_package.substr(8, len - 1);
    }
  }

  $(e).val("");

  console.log(distributor_position);
  console.log(distributor_package);

  if (distributor_package === '' || distributor_position === '') {
    return false;
  }

  swal({
    title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
    text: 'Loading, please wait...',
    showConfirmButton: false
  });
  let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  $.ajax({
    type: "post",
    url: url,
    data: {
      'position': distributor_position,
      'package': distributor_package,
      '_token': CSRF_TOKEN,
    },
    success: function (response) {
      distributor_package = '';
      distributor_position = '';
      swal.close();
      if (response.case === 'success') {
        $("#" + response.track).remove();
        swal({
          position: 'top-end',
          type: 'success',
          title: 'Success',
          showConfirmButton: false,
          timer: 1000
        });
      } else {
        form_submit_message(response, false);
      }
    }
  });
}

function set_declared_status(url, package_id) {
    if (package_id === 0) {
        swal(
            'Warning',
            'Please select item!',
            'warning'
        );
        return false;
    }

    swal({
        title: 'Do you approve change status?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "post",
                url: url,
                data: {
                    'id': package_id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        location.reload();
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function create_item_for_package(url, package_id) {
    if (package_id === 0) {
        swal(
            'Warning',
            'Please select item!',
            'warning'
        );
        return false;
    }

    swal({
        title: 'Do you approve create item for this package?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "post",
                url: url,
                data: {
                    'id': package_id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        location.reload();
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function disable_special_order_for_client(id, url) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'id': id,
            '_token': CSRF_TOKEN
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                form_submit_message(response, true);
            } else {
                form_submit_message(response, false);
            }
        }
    });
}

function enable_special_order_for_client(id, url) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'id': id,
            '_token': CSRF_TOKEN
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                form_submit_message(response, true);
            } else {
                form_submit_message(response, false);
            }
        }
    });
}

function get_declaration(url) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });

    let flight = $("#flight").val();
    let location = $("#location").val();
    let currency_type = $("#location_" + location).attr("currency_type");
    let goods_fr = $("#location_" + location).attr("goods_fr");
    let goods_to = $("#location_" + location).attr("goods_to");
    let address = $("#location_" + location).attr("address");

    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'flight': flight,
            'location': location,
            '_token': CSRF_TOKEN
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let packages  = response.packages;
                let package;
                let body = '';
                let tr;
                // let total_invoice = 0;
                // let rate = response['rate'];

                for (let i = 0; i < packages.length; i++) {
                    package = packages[i];
                    // total_invoice = package['invoice'].toFixed(2) + package['amount'].toFixed(2) * rate;
                    // total_invoice = total_invoice.toFixed(2);
                    tr = '<tr>';
                    tr += '<td>' + package['cbr'] + '</td>';
                    tr += '<td>1</td>';
                    tr += '<td>1</td>';
                    tr += '<td>' + package['gross_weight'].replace('.', ',') + '</td>';
                    tr += '<td>' + package['total_price'].replace('.', ',') + '</td>';
                    tr += '<td>' + currency_type + '</td>';
                    tr += '<td>' + package['category'] + '</td>';
                    tr += '<td>' + package['client_name'] + ' ' + package['client_surname'] + '</td>';
                    tr += '<td>' + package['client_address'] + '</td>';
                    tr += '<td>' + package['seller'] + '</td>';
                    tr += '<td>' + address + '</td>';
                    tr += '<td>' + goods_fr + '</td>';
                    tr += '<td>' + goods_to + '</td>';
                    tr += '<td>' + package['cbr'] + '</td>';
                    tr += '<td>' + package['track'] + '</td>';
                    tr += '<td>' + package['client_fin'] + '</td>';
                    tr += '<td>' + package['client_phone'] + '</td>';
                    tr += '<td>' + package['invoice'].replace('.', ',') + '</td>';
                    tr += '<td>' + package['amount'].replace('.', ',') + '</td>';
                    tr += '</tr>';

                    body += tr;
                }

                $("#declaration_table").html(body);
            } else {
                form_submit_message(response, false);
            }
        }
    });
}

function change_client_for_package(client_id_for_change_client, remark, url) {
    if (client_id_for_change_client === null) {
        swal(
            'Oops!',
            'Client id not found!',
            'warning'
        );
        return false;
    }
    package_id_for_change_client = row_id;
    if (package_id_for_change_client === 0 || package_id_for_change_client === null || package_id_for_change_client === '') {
        swal(
            'Oops!',
            'Package id not found!',
            'warning'
        );
        return false;
    }
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "post",
        url: url,
        data: {
            'client_id': client_id_for_change_client,
            'package_id': package_id_for_change_client,
            'remark': remark,
            '_token': CSRF_TOKEN
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                form_submit_message(response, true);
            } else {
                form_submit_message(response, false);
            }
        }
    });
}

// courier
function show_payment_types(url, zone_id, delete_url) {
    $("#zone_id_for_payment_types").val(zone_id);
    $("#delivery_payment_type_id").val("");
    $("#courier_payment_type_id").val("");

    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "get",
        url: url,
        data: {
            'zone_id': zone_id,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let types = response.payment_types;
                let i;
                let type;
                let table = '';
                let tr;
                let id;

                for (i = 0; i < types.length; i++) {
                    type = types[i];
                    id = type['id'];

                    tr = '<tr class="courier_payment_type_rows" id="courier_payment_type_row_' + id + '">';
                    tr += '<td>' + type['delivery'] + '</td>';
                    tr += '<td>' + type['courier'] + '</td>';
                    tr += '<td><span class="btn btn-danger btn-xs" onclick="delete_courier_payment_type(\'' + delete_url + '\', ' + id + ')"><i class="glyphicon glyphicon-trash"></i></span></td>';
                    tr += '</tr>';

                    table += tr;
                }

                $("#payment_types_table").html(table);

                $('#payment-types-modal').modal('show');
            } else {
                form_submit_message(response, false, false);
            }
        }
    });
}

function delete_courier_payment_type(url, id) {
    if (id === 0) {
        swal(
            'Warning',
            'Please select item!',
            'warning'
        );
        return false;
    }

    swal({
        title: 'Do you approve the deletion?',
        type: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes!'
    }).then(function (result) {
        if (result.value) {
            swal({
                title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
                text: 'Loading, please wait...',
                showConfirmButton: false
            });
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "delete",
                url: url,
                data: {
                    'id': id,
                    '_token': CSRF_TOKEN,
                },
                success: function (response) {
                    swal.close();
                    if (response.case === 'success') {
                        $('#courier_payment_type_row_' + response.id).remove();
                        swal({
                            position: 'top-end',
                            type: response.case,
                            title: response.title,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        swal(
                            response.title,
                            response.content,
                            response.case
                        );
                    }
                }
            });
        } else {
            return false;
        }
    });
}

function change_active_area(e, id, url) {
    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    let switch_val = 0;
    if ($(e).is(':checked')) {
        switch_val = 1;
    } else {
        switch_val = 2;
    }

    $.ajax({
        type: "post",
        url: url,
        data: {
            'id': id,
            'switch': switch_val,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                swal({
                    position: 'top-end',
                    type: response.case,
                    title: response.title,
                    showConfirmButton: false,
                    timer: 1000
                });
            } else {
                show_alert_message("danger", response.title, response.content);
            }
        }
    });
}

function status_history_for_package(url) {
    package_id = row_id;

    if (package_id === 0 || package_id === '') {
        swal(
            'Warning',
            'Please select item!',
            'warning'
        );
        return false;
    }

    swal({
        title: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Please wait...</span>',
        text: 'Loading, please wait...',
        showConfirmButton: false
    });
    let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        type: "get",
        url: url,
        data: {
            'id': package_id,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                let statuses = response.statuses;
                let status;
                let table = '';
                let tr;
                let i;
                let no;

                for (i = 0; i < statuses.length; i++) {
                    status = statuses[i];
                    no = i + 1;

                    tr = '<tr>';
                    tr += '<td>' + no + '</td>';
                    tr += '<td>' + status['status'] + '</td>';
                    tr += '<td>' + status['suite'] + ' - ' + status['user_name'] + ' ' + status['user_surname'] + '</td>';
                    tr += '<td>' + status['date'] + '</td>';
                    tr+= '</tr>';

                    table += tr;
                }

                $("#status-history-body").html(table);

                $('#status-history-modal').modal('show');
            } else {
                swal(
                    response.title,
                    response.content,
                    response.case
                );
            }
        }
    });
}
