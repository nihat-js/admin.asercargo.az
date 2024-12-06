$(document).ready(function(){
    var url = window.location.href;
    var url_arr = url.split('search');
    var where_url = 'search' + url_arr[1];

    if (url_arr.length > 1) {
        $('.pagination').each(function(){
            $(this).find('a').each(function(){
                var current = $(this);
                var old_url = current.attr('href');
                var new_url = old_url + '&' + where_url;
                current.prop('href', new_url);
            });
        });
    }

    var short_arr = url.split('shortType');
    if (short_arr.length > 1) {
        short_type = short_arr[1].substr(1, 1);
    }

    var short_arr_for_link = url.split('shortBy');
    var short_url = 'shortBy' + short_arr_for_link[1];

    if (short_arr_for_link.length > 1) {
        $('.pagination').each(function(){
            $(this).find('a').each(function(){
                var current = $(this);
                var old_url_for_short = current.attr('href');
                var new_url_for_short = old_url_for_short + '&' + short_url;
                current.prop('href', new_url_for_short);
            });
        });
    }
});

//select row
function select_row(row) {
    $('.rows').removeClass("selected_row");
    $('#row_' + row).addClass("selected_row");
    row_id = row;
    $(".action-btn-disable").css('display', 'block');
}

function get_current_date() {
    let currentDate;

    let now = new Date();
    let year = now.getFullYear();
    let month = now.getMonth() + 1;
    let day = now.getDay() + 1;

    if (month.toString().length < 2) {
        month = '0' + month;
    }
    if (day.toString().length < 2) {
        day = '0' + day;
    }

    currentDate = year + '-' + month + '-' + day;

    return currentDate;
}

function get_current_time() {
    let now = new Date();

    let hour = now.getHours();
    let minute = now.getMinutes();

    if (hour.toString().length < 2) {
        hour = '0' + hour;
    }
    if (minute.toString().length < 2) {
        minute = '0' + minute;
    }

    return hour + ":" + minute;
}

//show date area for search
function date_area() {
    if (show_date_area) {
        show_date_area = false;
        $('#search-date-area').css('display', 'none');
    } else {
        show_date_area = true;
        $('#search-date-area').css('display', 'block');
    }
}

function today_for_date_area() {
    show_date_area = true;
    $('#search-date-area').css('display', 'block');
    $("#date_search").prop('checked', true);

    let currentDate = get_current_date();

    $(".start_date_search").val(currentDate);
    $(".end_date_search").val(currentDate);
}

//short by
function sort_by(column, first_search_column="search") {
    var url_for_short = window.location.href;
    var url_arr_for_short = url_for_short.split(first_search_column);
    var link_for_short = '';

    if (url_arr_for_short.length > 1) {
        var new_url_for_short = url_arr_for_short[1].split('shortBy');
        if (new_url_for_short.length > 1) {
            link_for_short = '?' + first_search_column + new_url_for_short[0];
        } else {
            link_for_short = '?' + first_search_column + new_url_for_short[0] + '&';
        }

    } else {
        link_for_short = '?';
    }

    var shortType = '';
    if (short_type == 1) {
        shortType = '2';
    } else {
        shortType = '1';
    }
    link_for_short += 'shortBy=' + column + '&shortType=' + shortType;

    location.href = link_for_short;
}

function search_data() {
    var link = '?search=1';

    $('[id=search_values]').each(function() {
        var column = $(this).attr("column_name");
        var value = $(this).val();

        link += '&' + column + '=' + value;
    });

    location.href = link;
}

function show_password(password, id) {
    if(id == 138869){
        $("#first_password_" + id).html('');
    }else{
        $("#first_password_" + id).html(password);
    }
    
}

function form_submit_message(response, reload= true, modal_close = true) {
    if (response.case === 'success') {
        if (modal_close) {
            $('#add-modal').modal('hide');
        }
        swal({
            position: 'top-end',
            type: response.case,
            title: response.title,
            showConfirmButton: false,
            timer: 1500
        });
        if (reload) {
            location.reload();
        }
    }
    else {
        if (response.type === 'validation') {
            let content = response.content;
            let validation_message = '';
            $.each(content, function(index, value)
            {
                if (value.length !== 0)
                {
                    for (let i = 0; i < value.length; i++) {
                        validation_message += value[i] + '\n';
                    }
                }
            });
            swal(
                'Validation error!',
                validation_message,
                'warning'
            );
        } else {
            swal(
                response.title,
                response.content,
                response.case
            );
        }
    }
}

function show_image_from_url(url, id, delete_url) {
    if (url === '' || url === null) {
        swal(
            'Oops!',
            'Image not found!',
            'warning'
        );
        return false;
    }

    let body = '';
    body += "<img src='" + url + "'  width='200' height='200'>" + "<br><br>";
    body += "<span class='btn btn-danger btn-xs' onclick='delete_image(\"" + delete_url + "\"," + id + ")'>Delete</span>";

    $("#image-modal-body").html(body);
    $("#image-modal").modal('show');
}

let timer_status;
let focus_control = true;
function start_timer() {
    clock();
    // if (focus_control) {
    //     focus_active();
    // }

    timer_status = setTimeout(start_timer, 100);
}

function stop_timer() {
    clearTimeout(timer_status);
}

function focus_disable() {
    focus_control = false;
}

function focus_active() {
    focus_control = true;
    $("#barcode_input").focus();
}

// $('#client').keypress(function (e) {
//     enter_press(e);
// });

function enter_press(e) {
    let key = e.which;
    if(key === 13)  // the enter key code
    {
        focus_active();
    }
}

function clear_value(e) {
    $(e).val("");
}

function clock() {
    let now = new Date(),
        months = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"];

    let hour = now.getHours();
    let minute = now.getMinutes();
    let second = now.getSeconds();

    if (hour.toString().length < 2) {
        hour = '0' + hour;
    }
    if (minute.toString().length < 2) {
        minute = '0' + minute;
    }
    if (second.toString().length < 2) {
        second = '0' + second;
    }

    let time = hour + ':' + minute + ':' + second;

    let day = now.getDate();
    let month = now.getMonth();
    let year = now.getFullYear();

    if (day.toString().length < 2) {
        day = '0' + day;
    }

    let date = [day,
        months[month],
        year].join(' ');

    document.getElementById('time').innerHTML = [date, time].join(' | ');
}

function show_alert_message(type, title, content, error_type = 'warning') {
    let content_message = '';
    if (error_type === 'validation') {
        let validation_message = '';
        $.each(content, function(index, value)
        {
            if (value.length !== 0)
            {
                for (let i = 0; i < value.length; i++) {
                    validation_message += value[i] + '\n';
                }
            }
        });
        content_message = validation_message;
    } else {
        content_message = content;
    }

    let alert = '<div class="alert alert-' + type + '"><b>' + title + '</b><br>' + content_message + '</div>';
    $("#response_alert_message").html(alert).css('display', 'block');
}

function hide_alert_message() {
    $("#response_alert_message").css('display', 'none');
}

function select_contract_detail(d_id) {
    detail_id = d_id;
    $('.detail-rows').removeClass("selected_row");
    $('#detail_row_' + detail_id).addClass("selected_row");
    $(".detail-action-btn-disable").css('display', 'block');
}

// function change_track_number(e, url) {
//     if (manually_control) {
//         if ($(e).val().length > 5) {
//             check_package_collector(url, $(e).val());
//             $(".collector-inputs").prop("readonly", false).prop('disabled', false);
//         } else {
//             show_alert_message('warning', 'Stop!', "Please enter the track number first!");
//             $(".collector-inputs").prop("readonly", true).prop('disabled', true);
//             clear_values();
//         }
//     }
// }

function create_seller_name_with_title(e) {
    let title = $(e).val();
    let name = '';

    name = title.split('.')[0];
    name = name.replace(/\ /g, '_');

    if (name.length > 50) {
        name = name.substr(0, 50);
    }

    name = name.toLowerCase();

    $("#name").val(name);
}

// choose menu links
let pathname = window.location.origin + window.location.pathname;
$('#menu-links').each(function(){
    $(this).find('li').each(function(){
        if (pathname.substr(pathname.length - 1) === '/') {
            pathname = pathname.substr(0, pathname.length - 1);
        }
        let current = $(this);
        let menu_link = current.find('a').attr('href');
        if (menu_link.substr(menu_link.length - 1) === '/') {
            menu_link = menu_link.substr(0, menu_link.length - 1);
        }
        if (menu_link === pathname) {
            current.addClass('active');
            let parent = current.parent();
            let parent_class = parent[0]['className'];
            if (parent_class === 'dropdown-menu') {
                parent.parent().addClass('active-parent');
            }
        }
    });
});

function get_containers_by_flight(url) {
    location.href = url;
}

function change_client_for_package_modal(url) {
    swal({
        title: 'Client ID (Suite)',
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Save',
        showLoaderOnConfirm: true,
        html:
            '<input type="number" id="swal-input-client-id" class="swal2-input" placeholder="ID" style="max-width: 100% !important;">' +
            '<input type="text" id="swal-input-remark" class="swal2-input" placeholder="Remark" maxlength="500">',
        preConfirm: function () {
            let client_id = $('#swal-input-client-id').val();
            let remark = $('#swal-input-remark').val();
            change_client_for_package(client_id, remark, url);
        },
        onOpen: function () {
            $('#swal-input-client-id').focus()
        }
    }).catch(swal.noop)
}

//for admin
function change_weight_for_package_modal() {
    package_id = row_id;

    if (package_id === 0 || package_id === '') {
        swal(
            'Warning',
            'Please select item!',
            'warning'
        );
        return false;
    }

    $("#package_id").val(package_id);
    $("#height").val($("#height_" + package_id).text());
    $("#width").val($("#width_" + package_id).text());
    $("#length").val($("#length_" + package_id).text());
    $("#gross_weight").val($("#gross_weight_" + package_id).text());

    $('#weight-modal').modal('show');
}

function change_status_for_package_modal() {
    package_id = row_id;

    if (package_id === 0 || package_id === '') {
        swal(
            'Warning',
            'Please select item!',
            'warning'
        );
        return false;
    }

    $("#package_id_for_status").val(package_id);

    $('#status-modal').modal('show');
}


function open_change_branch_modal() {
    package_id = row_id;

    // console.log({package_id,row_id})

    if (package_id === 0 || package_id === '') {
        swal(
            'Warning',
            'Please select item!',
            'warning'
        );
        return false;
    }

    $("#branch-modal [name='package_id']").val(package_id);

    $('#branch-modal').modal('show');
}

