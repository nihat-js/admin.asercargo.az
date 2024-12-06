let scan_mode = true;
let tracking_number_control = false;
let tracking_number = "";
let length = 0;
let height = 0;
let width = 0;
let category = "";
let seller = "";
let seller_title = "";
let waybill_cdn = "";
let destination = "";
let gross_weight = "";
let volume_weight = "";
let container = "";
let position = "";
let invoice = "0";
let invoice_usd = "0";
let quantity = 1;
let currency = currency_by_user;
let client_id = 0;
let client_name_surname = '';
let client_name = "";
let client_phone = "";
let client_address = "";
let suite = '';
let url = "";
let last_invoice_doc = '';
let last_return_label = '';
let last_invoice_confirmed = 0;
let last_waybill_doc = '';
let waybill_print_access = false;
let paid_status = 0;
let flight_departure = "";
let flight_destination = "";
let flight_date = "";
let flight_name = "";
let package_amount = "";
let check_package_url = '';
let package_status = 37; //no invoice
let invoice_status = 0;
let status_arr = [];
let package_description = '';
let tariff_type = 1;
let is_legal_entity = 0;
let client_comment = "";
let success_invoice_statuses = [3, 4];
let success_customs_statuses = [1, 2];
let current_status = 0;
let product_title = "";
let subCat = "";
let legality = 0;
start_timer();

$(document).ready(function () {
    // manual mode
    manually_control = true;
    // focus_disable();
    $("#save-btn").css("display", "inline-block");
    $("#barcode_input").css("display", "none");
    $("#manually-btn").html("Only scan").removeClass("btn-warning").addClass("btn-success");
    $(".collector-track-input").prop("readonly", false).prop('disabled', false);
    let legal_entity_selector = $("#is_legal_entity");
    current_status = $("#status").val();
    invoice_status = parseInt($("#invoice_status :selected").val());
    is_legal_entity = legal_entity_selector.is(':checked');
    legal_entity_selector.change(function () {
        is_legal_entity = (this.checked);
        if (is_legal_entity) {
            $("#status").val(41);
            $('#invoice_status').val(1);
        }
        refresh_ok_to_send();
    });
    // $(".collector-inputs").prop("readonly", false);
    $("#invoice_status").change(function (status) {
        invoice_status = parseInt(status.target.value);
        refresh_ok_to_send();
    })
});

function update_values(response, number) {
    let package_exist = response.package_exist;
    let package;
    let cont_or_pos = '';
    let update_category;
    let update_seller;
    let update_currency;

    if (package_exist) {
        package = response.package;

        if (package['container_id'] !== null) {
            cont_or_pos = 'CN' + package['container_id'];
            container = package['container_id'];
            position = "";
            $("#container").addClass("active-sec");
            $("#position").removeClass("active-sec");
        } else if (package['position'] !== null) {
            cont_or_pos = package['position'];
            position = package['position'];
            container = "";
            $("#position").addClass("active-sec");
            $("#container").removeClass("active-sec");
        }
        // else {
        //     cont_or_pos = '';
        //     container = "";
        //     position = "";
        //     $("#container").addClass("active-sec");
        //     $("#position").removeClass("active-sec");
        // }

        if (package['category'] !== '' && package['category'] !== null) {
            update_category = package['category'];
        }

        if (package['title'] !== '' && package['title'] !== null) {
            product_title = package['title'];
        } else {
            product_title = '';
        }

        if (package['subCat'] !== '' && package['subCat'] !== null) {
            subCat = package['subCat'];
        } else {
            subCat = '';
        }

        if (package['seller'] !== '' && package['seller'] !== null) {
            update_seller = package['seller'];
        }

        if (package['currency'] !== '' && package['currency'] !== null) {
            update_currency = package['currency'];
        }

        $("#number").val(package['track']);
        $("#internal_id").val(package['internal_id']);
        $(".internal_id").css('display', 'block');
        $("#client").val(package['client']);
        if (package['client'] === 'C0') {
            client_name_surname = package['client_name'];
            $("#client_name").val(package['client_name']).prop("readonly", false).prop('disabled', false).attr("name", "client_name_surname");
        } else {
            client_name_surname = '';
            $("#client_name").val(package['client_name']).prop("readonly", true).prop('disabled', true).removeAttr("name");
        }
        $(".client_name").css('display', 'block');
        $("#length").val(package['length']);
	$("#title").val(package['title']);
        $("#height").val(package['height']);
        $("#width").val(package['width']);
        $("#gross_weight").val(package['gross_weight']);
        $("#volume_weight").val(package['volume_weight']);
        $("#destination").val(package['destination']);
        $("#is_legal_entity").prop('checked', package['customer_type_id'] === 2)
        if (package['seller_id'] === 0) {
            $("#other_seller").val(package['other_seller']);
            $("#seller").val("");
            $("#other_seller_area").css('display', 'block');
        } else {
            if (package['seller_only_collector'] == 1) {
                $("#seller").append('<option class="seller_for_only_collector" value="' + update_seller + '">' + update_seller + '</option>');
            }
            $("#seller").val(update_seller);
            $("#other_seller").val("");
            $("#other_seller_area").css('display', 'none');
        }
        $("#category").val(update_category);
        $("#invoice").val(package['invoice']);
        $("#invoice_usd").val(package['invoice_usd']);
        $("#currency").val(update_currency);
        $("#quantity").val(package['quantity']);
        $("#cont_or_pos").val(cont_or_pos);
        $("#description").val(package['description']);
        $("#tariff_type").val(package['tariff_type_id']);
        $("#client_comment").val(package['client_comment']);
        $("#title").val(package['title']);
        $("#subCat").val(package['subCat']);

        package_id = package['id'];
        package_status = package['status_id'];
        invoice_status = package['invoice_status'];
        length = package['length'];
        height = package['height'];
        width = package['width'];
        category = package['category'];
        seller = package['seller'];
        seller_title = package['seller_title'];
        destination = package['destination'];
        gross_weight = package['gross_weight'];
        volume_weight = package['volume_weight'];
        carrier_status_id = package['carrier_status_id']
        invoice = package['invoice'];
        invoice_usd = package['invoice_usd'];
        quantity = package['quantity'];
        currency = package['currency'];
        client_id = package['client_id'];
        client_name = package['client_name'];
        client_phone = package['client_phone'];
        client_address = package['client_address'];
        client_legality = package['client_legality'];
        send_legality = package['send_legality'];
        suite = package['client'];
        last_invoice_doc = package['invoice_doc'];
        last_return_label = package['return_label_doc'];
        last_invoice_confirmed = package['invoice_confirmed'];
        paid_status = package['paid_status'];
        package_internal_id = package['internal_id'];
        flight_departure = package['flight_departure'];
        flight_destination = package['flight_destination'];
        flight_date = package['flight_date'];
        flight_name = package['flight_name'];
        package_amount = package['amount'];
        waybill_cdn = package['carrier_registration_number'];
        package_description = package['description'];
        tariff_type = package['tariff_type_id'];
        client_comment = package['client_comment'];
        is_legal_entity = (package['customer_type_id'] === 2 ? true : false);
        product_title = package['title'] ? package['title'] : "";
        subCat = package['subCat'] ? package['subCat'] : "";
        if (last_invoice_doc === null) {
            $("#invoice_doc").removeClass("btn-success").removeClass("btn-warning").addClass("btn-danger")
                .prop("href", "#").prop("target", "_self");
        } else {
            last_invoice_doc = 'https://asercargo.az/' + last_invoice_doc;
            if (last_invoice_confirmed === 1) {
                $("#invoice_doc").removeClass("btn-danger").removeClass("btn-warning").addClass("btn-success")
                    .prop("href", last_invoice_doc).prop("target", "_blank");
            } else {
                $("#invoice_doc").removeClass("btn-success").removeClass("btn-danger").addClass("btn-warning")
                    .prop("href", last_invoice_doc).prop("target", "_blank");
            }
        }

        if (carrier_status_id === 0) {
            $("#customs_permission").removeClass("btn-success").removeClass('btn-danger').addClass("btn-basic");
        } else if(carrier_status_id === 3) {
            $('#customs_permission').removeClass("btn-basic").removeClass("btn-success").addClass("btn-danger")
        } else if ((carrier_status_id === 2) || (carrier_status_id === 1) || (carrier_status_id >= 7 && carrier_status_id < 9)) {
            $("#customs_permission").removeClass("btn-basic").removeClass(('btn-danger')).addClass("btn-success");
        } else {
            $("#customs_permission").removeClass("btn-success").removeClass(('btn-danger')).addClass("btn-basic");
        }

        if (client_legality === 1) {
            $("#legal_customer").removeClass("btn-basic").removeClass(('btn-danger')).addClass("btn-success");
        } else {
            $("#legal_customer").removeClass("btn-success").removeClass(('btn-danger')).addClass("btn-basic");
        }

        if (send_legality === 1) {
            $("#legal_customer_send_pack").removeClass("btn-basic").removeClass(('btn-danger')).addClass("btn-success");
        } else {
            $("#legal_customer_send_pack").removeClass("btn-success").removeClass(('btn-danger')).addClass("btn-basic");
        }

        if (invoice_status === 1 || invoice_status === 2) {
            $("#invoice_status_indicator").removeClass("btn-success").removeClass("btn-basic").addClass("btn-danger");
        } else if (invoice_status === 3) {
            $("#invoice_status_indicator").removeClass("btn-basic").removeClass("btn-danger").addClass("btn-success");
        } else if (invoice_status === 4) {
            $("#invoice_status_indicator").removeClass("btn-basic").removeClass("btn-danger").removeClass("btn-success").addClass("btn-warning");
        }

        if (last_return_label === null) {
            $("#return_label").removeClass("btn-success").addClass("btn-danger")
                .prop("href", "#").prop("target", "_self");
        } else {
            $("#return_label").removeClass("btn-danger").addClass("btn-success")
                .prop("href", last_return_label).prop("target", "_blank");
        }

        if (jQuery.inArray(package_status, status_arr) === -1) {
            // other status
            package_status = 5; // collected
        }
        $("#status").val(package_status);
        $("#invoice_status").val(invoice_status);

        if (package_status === 7 || package_status === 8) {
            $(".images").css("display", "block");
        } else {
            $(".images").css("display", "none");
        }

        generate_waybill_content_for_print();
        $("#waybill_doc").removeClass("btn-danger").addClass("btn-success");
        waybill_print_access = true;
        tracking_number_control = true;
        refresh_ok_to_send();
    } else {
        if (response.package !== null) {
            clear_values();
            show_alert_message("warning", response.package, 'If you think this is a problem, please let the administrator know.');
        }
        tracking_number = number;
        $("#number").val(number);
    }
}

function refresh_ok_to_send() {
    let selector = $("#ok_to_send");
    selector.removeClass("btn-success").addClass("btn-basic");
    let statusSelector = $("#status");

    if (success_invoice_statuses.includes(invoice_status)) {
        if (success_customs_statuses.includes(carrier_status_id) || is_legal_entity) {
            selector.removeClass("btn-basic").addClass("btn-success");
            statusSelector.val(5);
        }
    }
}

function cont_or_pos(e) {
    let cont_or_pos = $(e).val();
    if (cont_or_pos.substr(0, 9).toUpperCase() === 'CN') {
        $("#container").addClass("active-sec");
        $("#position").removeClass("active-sec");
        container = cont_or_pos.substr(9);
        position = null;
    } else {
        $("#position").addClass("active-sec");
        $("#container").removeClass("active-sec");
        container = null;
        position = cont_or_pos;
    }
}

function save_collector_manually(url) {
    if (manually_control) {
        tracking_number = $("#number").val();
        length = $("#length").val();
        height = $("#height").val();
        width = $("#width").val();
        seller = $("#seller").val();
        destination = $("#destination").val();
        gross_weight = $("#gross_weight").val();
        currency = $("#currency").val();
        category = $("#category").val();
        invoice = $("#invoice").val();
        quantity = $("#quantity").val();
        package_status = $("#status").val();
        package_description = $("#description").val();
        tariff_type = $("#tariff_type").val();
        is_legal_entity = (is_legal_entity === true ? "on" : "off");
        product_title = $("#title").val();
        subCat = $("#subCat").val();

        save_collector(url);
    } else {
        show_alert_message('warning', 'Stop!', "You are in scan mode!");
    }
}

let tr_count = 0;

function item_add_to_table(amount, internal_id) {
    tr_count++;
    if (tr_count > 5) {
        $('#item-list tr:last').remove();
    }
    let client_name = $("#client").val();
    let cont_or_pos = $("#cont_or_pos").val();
    let item_tr = '<tr ondblclick="check_package_collector(\'' + check_package_url + '\', \'' + tracking_number + '\')">';
    item_tr += '<td>' + tracking_number + '</td>';
    // item_tr += '<td>' + internal_id + '</td>';
    item_tr += '<td>' + cont_or_pos + '</td>';
    item_tr += '<td>' + category + '</td>';
    item_tr += '<td>' + invoice + ' ' + currency + '</td>';
    item_tr += '<td>' + gross_weight + '</td>';
    item_tr += '<td>' + client_name + '</td>';
    item_tr += '<td>' + amount + '</td>';
    // item_tr += '<td>' + seller + '</td>';
    item_tr += '<td>' + quantity + '</td>';
    item_tr += '<td>' + get_current_time() + '</td>';
    item_tr += '</tr>';

    $("#item-list").prepend(item_tr);
}

function clear_values(waybill_clear = true) {
    tracking_internal_same = 0;
    scan_mode = true;
    tracking_number_control = false;
    tracking_number = "";
    length = 0;
    height = 0;
    width = 0;
    category = "";
    seller = "";
    seller_title = "";
    destination = "Baku";
    gross_weight = "";
    volume_weight = "";
    // container = "";
    // position = "";
    invoice = "0";
    quantity = 1;
    currency = currency_by_user;
    client_id = 0;
    client_name_surname = '';
    client_name = "";
    client_phone = "";
    client_address = "";
    suite = '';
    url = "";
    last_invoice_doc = '';
    last_return_label = '';
    last_invoice_confirmed = 0;
    last_waybill_doc = '';
    paid_status = 0;
    flight_departure = "";
    flight_destination = "";
    flight_date = "";
    package_status = 37; // no invoice
    package_id = 0;
    package_description = '';
    tariff_type = 1;
    is_legal_entity = 0;
    client_comment = "";
    product_title = "";
    subCat = "";


    $("#number").val("");
    $("#description").val("");
    $("#client_comment").val("");
    $("#tariff_type").val(1);
    $("#status").val(37); // no invoice
    $("#length").val("");
    $("#height").val("");
    $("#width").val("");
    $("#category").val("");
    $("#seller").val("");
    $("#other_seller").val("");
    $("#destination").val("Baku").prop("readonly", true);
    $("#gross_weight").val("");
    $("#volume_weight").val("");
    $("#invoice_status").val("1");
    // $("#cont_or_pos").val("");
    $("#invoice").val(0);
    $("#quantity").val(1);
    $("#currency").val(currency_by_user);
    $("#client").val("AS");
    $("#client_name").val("").prop("readonly", true).prop('disabled', true).removeAttr("name");
    $("#title").val("");
    $("subCat").val("");

    $(".seller_for_only_collector").remove();

    // $("#container").addClass("active-sec");
    // $("#position").removeClass("active-sec");

    $(".internal_id").css('display', 'none');
    $(".client_name").css('display', 'none');

    $("#client_packages_table").css('display', 'none');
    $("#client_packages_body").html("");

    // $(".collector-inputs").prop("readonly", true);

    $("#invoice_doc").removeClass("btn-success").removeClass("btn-warning").addClass("btn-danger")
        .prop("href", "#").prop("target", "_self");

    $("#return_label").removeClass("btn-success").addClass("btn-danger")
        .prop("href", "#").prop("target", "_self");

    if (waybill_clear === true) {
        $("#waybill_doc").removeClass("btn-success").addClass("btn-danger");
        waybill_print_access = false;
    }

    $(".images").css("display", "none");
    $("#images").val("");

    $("#images_gallery").html("");

    $("#cont_or_pos").prop("readonly", true).prop('disabled', true);
    $("#other_seller_area").css('display', 'none');

    $('#customs_permission').removeClass('btn-danger').removeClass('btn-success').addClass('btn-basic');
    $('#invoice_status_indicator').removeClass('btn-danger').removeClass('btn-success').addClass('btn-basic');
    $('#is_legal_entity').prop('checked', false);
}

function calculate_volume() {
    let height = $("#height").val();
    let width = $("#width").val();
    let length = $("#length").val();

    let volume = (length * width * height) / 6000;
    volume_weight = volume;
    $("#volume_weight").val(volume);
}

function select_container(e, val = false) {
    let new_container;
    if (val === true) {
        new_container = e;
    } else {
        new_container = $(e).val();
    }
    if (new_container.length < 3) {
        show_alert_message("danger", "Wrong format!", "Container format is not correct.");
        return false;
    }

    if (new_container.substr(0, 7) === 'CNNEWCN') {
        let flight_id = new_container.split('_')[1];
        let conf = confirm("Əminsinizmi?");
        if(conf == true)
            create_new_container(flight_id);
        return false;
    }

    $("#cont_or_pos").val(new_container);
    position = "";
    container = parseInt(new_container.substr(2, new_container.length));
    $("#container").addClass("active-sec");
    $("#position").removeClass("active-sec");

    $("#position_select").val("");
}

function select_position(e) {
    let new_position = $(e).val();

    $("#cont_or_pos").val(new_position);
    position = new_position;
    container = "";
    $("#position").addClass("active-sec");
    $("#container").removeClass("active-sec");

    $("#container_select").val("");
}

function select_category(url, e) {
    let category = $(e).val();

    if (category === 'new') {
        show_add_new_category_modal(url);
    }
}

function select_seller(url, e) {
    let seller = $(e).val();

    if (seller === 'new') {
        show_add_new_seller_modal(url);
    }
}

function show_add_new_seller_modal(url) {
    $("#seller").val("");
    swal({
        title: 'New seller',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Save and choose',
        showLoaderOnConfirm: true,
        preConfirm: (new_seller) => {
            add_new_seller(url, new_seller);
        }
    });
}

function add_new_seller_by_other_seller(url) {
    let other_seller = $("#other_seller").val();
    if (other_seller.length > 1) {
        add_new_seller(url, other_seller);
    } else {
        show_alert_message('warning', 'Oops!', 'Seller format is wrong!');
    }
}

function show_add_new_category_modal(url) {
    $("#category").val("");
    swal({
        title: 'New category',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Save and choose',
        showLoaderOnConfirm: true,
        preConfirm: (new_category) => {
            add_new_category(url, new_category);
        }
    });
}

function create_internal_barcode_for_barcode() {
    let settings = {
        barWidth: 2,
        barHeight: 50,
        moduleSize: 30,
        showHRI: true,
        addQuietZone: true,
        marginHRI: 5,
        bgColor: "#FFFFFF",
        color: "#000000",
        fontSize: 0,
        output: "bmp",
        posX: 0,
        posY: 0
    };

    $("#barcode_internal_id").html(package_internal_id);
    $("#barcode_internal_id_barcode").barcode(
        package_internal_id, // Value barcode (dependent on the type of barcode)
        "code128", // type (string)
        settings
    );
}

function create_internal_barcode_for_waybill() {
    let settings = {
        barWidth: 2,
        barHeight: 50,
        moduleSize: 30,
        showHRI: true,
        addQuietZone: true,
        marginHRI: 5,
        bgColor: "#FFFFFF",
        color: "#000000",
        fontSize: 0,
        output: "bmp",
        posX: 0,
        posY: 0
    };

    $("#waybill_internal_id").html(package_internal_id);
    $("#waybill_internal_id_barcode").barcode(
        package_internal_id, // Value barcode (dependent on the type of barcode)
        "code128", // type (string)
        settings
    );
}

function generate_waybill_content_for_print() {
    create_internal_barcode_for_waybill();
    create_internal_barcode_for_barcode();
    $("#waybill-suite").html(suite);
    if (invoice !== null && invoice != 0) {
        $("#waybill_charge_collect").html("x");
    } else {
        $("#waybill_charge_collect").html("");
    }
    if (paid_status === 1) {
        $("#waybill_prepaid").html("x");
    } else {
        $("#waybill_prepaid").html("");
    }
    $("#waybill_seller").html(seller_title);
    $(".waybill_client").html(client_name + " " + suite);
    $("#waybill_client_phone").html("(" + client_phone + ")");
    $("#waybill_client_address").html("(" + client_address + ")");
    $("#waybill_departure").html(flight_departure);
    $("#waybill_cdn").html(waybill_cdn)
    $("#waybill_destination").html(flight_destination);
    if (flight_date !== null && flight_date.length > 10) {
        flight_date = flight_date.substr(0, 10);
    }
    $("#waybill_flight_name").html(flight_name);
    $("#waybill_date").html(flight_date);
    $("#waybill_quantity").html(quantity);
    $("#waybill_gross_weight").html(gross_weight);
    $("#waybill_volume_weight").html(volume_weight);
    $("#waybill_invoice_price").html(invoice + ' ' + currency);

    let invoice_usd_waybill = Number(invoice_usd);
    let payment_amount_way_bill = Number(package_amount.slice(0, -3));
    let total = (invoice_usd_waybill + payment_amount_way_bill).toFixed(2);

    $("#total_waybill_invoice_price").html(total + ' ' + 'USD');
    $("#waybill_category").html(category);
    $("#waybill_amount").html(package_amount);
    $("#waybill_description").html(client_comment);
}

function show_client_other_packages(packages) {
    if (!packages) {
        return false;
    }

    let table = '';
    let i = 0;
    let package;
    let tr;
    let weight;
    let len = packages.length;

    for (i = 0; i < len; i++) {
        package = packages[i];
        tr = '<tr ondblclick="check_package_collector(\'' + check_package_url + '\', \'' + package['number'] + '\')">';

        if (package['chargeable_weight'] === 1) {
            weight = package['gross_weight'];
        } else {
            weight = package['volume_weight'];
        }

        tr += '<td>' + package['number'] + '</td>';
        tr += '<td>' + package['internal_id'] + '</td>';
        tr += '<td>' + package['destination'] + '</td>';
        tr += '<td>' + package['seller'] + '</td>';
        tr += '<td>' + package['amount'] + ' ' + package['currency'] + '</td>';
        tr += '<td>' + weight + '</td>';
        tr += '<td>' + package['status'] + '</td>';
        // tr += '<td style="color: ' + package['status_color'] + ';">' + package['status'] + '</td>';

        tr += '</tr>';

        table += tr;
    }

    $("#client_packages_body").html(table);
    $("#client_packages_table").css('display', 'block');
}

function manually() {
    // clear_values();
    if (manually_control) {
        // scan mode
        manually_control = false;
        // focus_active();
        $("#save-btn").css("display", "none");
        $("#barcode_input").css("display", "inline-block");
        $("#manually-btn").html("Scan or manually").removeClass("btn-success").addClass("btn-warning");
        $(".collector-track-input").prop("readonly", true).prop('disabled', true);
        $(".collector-inputs").prop("readonly", true).prop('disabled', true);
    } else {
        // scan or manual mode
        manually_control = true;
        // focus_disable();
        $("#save-btn").css("display", "inline-block");
        $("#barcode_input").css("display", "none");
        $("#manually-btn").html("Only scan").removeClass("btn-warning").addClass("btn-success");
        $(".collector-track-input").prop("readonly", false).prop('disabled', false);
        if ($("#number").val().length > 5) {
            $(".collector-inputs").prop("readonly", false).prop('disabled', false);
        } else {
            // show_alert_message('warning', 'Stop!', "You are in manual mode. Please enter the track number first!");
            $(".collector-inputs").prop("readonly", true).prop('disabled', true);
            // clear_values();
        }
    }
}

function generate_client_name_and_surname(e) {
    client_name_surname = $(e).val();
}

function change_invoice(e) {
    let inv = $(e).val();
    let sts = $("#status").val();

    if (inv > 0 && sts == 6) { //status is no invoice
        $("#status").val(5); //collected
        package_status = 5;
    }
}

function select_status(e) {
    let status = parseInt($(e).val());

    hide_alert_message();
    if (isNaN(status)) {
        show_alert_message('warning', 'Oops!', 'Status can not be empty!');
        $("#status").val("");
        return false;
    }

    // unknown client control
    if (status === 5 && client_id === 0) {
        show_alert_message('warning', 'Oops!', 'Packages which status is "Ready for carriage" cannot be unknown client!');
        status = 36;
        $("#status").val(status);
        return false;
    }

    // show image input
    $("#images").val("");
    if (status === 7 || status === 8) {
        $(".images").css("display", "block");
    } else {
        $(".images").css("display", "none");
    }
}

// scan mode enable or disable
$(".collector-inputs").focus(function () {
    // scan mode disable
    scan_mode = false;
});

$(".collector-inputs").blur(function () {
    // scan mode enable
    scan_mode = true;
});

$(".collector-track-input").focus(function () {
    // scan mode disable
    scan_mode = false;
});

$(".collector-track-input").blur(function () {
    // scan mode enable
    scan_mode = true;
});

function change_track_number(e, url) {
    if ($(e).val().length > 5) {
        check_package_collector(url, $(e).val());
        $(".collector-inputs").prop("readonly", false).prop('disabled', false);
    } else {
        show_alert_message('warning', 'Stop!', "Please enter the track number first!");
        $(".collector-inputs").prop("readonly", true).prop('disabled', true);
        clear_values();
    }
}

function check_client(e, url) {
    client_name_surname = '';
    $(".client_name").css('display', 'none');
    $("#client_name").val("").prop("readonly", true).prop('disabled', true).removeAttr("name");
    $("#client_packages_table").css('display', 'none');
    $("#client_packages_body").html("");
    let client = $(e).val();
    console.log(client);
    client_id = 0;
    if (client.length > 2) {
        client_id = parseInt(client.substr(2, client.length));
    } else {
        show_alert_message('warning', "Oops!", 'Wrong format!');
        $("#client").val("AS");
        return false;
    }

    if (client_id === 0) {
        $("#status").val(36); // unknown client

        show_alert_message('warning', "Attention!", 'You are saving this package as an unknown package.</br>Unknown packages can only be placed in the position!');
        $(".client_name").css('display', 'block');
        $("#client_name").val("").prop("readonly", false).prop('disabled', false).attr("name", "client_name_surname");
        $("#status").attr("disabled", true);
        return false;
    }else {
        $("#status").attr("disabled", false);
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
            'client_id': client_id,
            '_token': CSRF_TOKEN,
        },
        success: function (response) {
            swal.close();
            if (response.case === 'success') {
                if (!response.client) {
                    $("#client").val("AS");
                    $("#client_name").val("");
                    $(".client_name").css('display', 'none');
                    show_alert_message('warning', 'Oops!', 'Client not exist!');
                } else {
                    let user_arr = response.client;
                    let suite = user_arr['suite'];
                    let client = user_arr['name'];
                    legality = user_arr['is_legality'];
                    // console.log(legality);
                    $("#client").val(suite);
                    $("#client_name").val(client);
                    $(".client_name").css('display', 'block');

                    if (legality === 1) {
                        $("#legal_customer").removeClass("btn-basic").removeClass(('btn-danger')).addClass("btn-success");
                        $('#is_legal_entity').prop('checked', true);
                        let legal_entity_selector = $("#is_legal_entity");
                        is_legal_entity = legal_entity_selector.is(':checked');                           
                        $("#status").val(41);
                    } else {
                        $("#legal_customer").removeClass("btn-success").removeClass(('btn-danger')).addClass("btn-basic");
                    }

                    let client_packages = response['client_packages'];
                    if (client_packages !== null) {
                        show_client_other_packages(client_packages);
                    }

                    hide_alert_message();
                }
            } else {
                $("#client").val("AS");
                show_alert_message('danger', 'Oops!', 'Sorry, something went wrong!');
            }
        }
    });
}
