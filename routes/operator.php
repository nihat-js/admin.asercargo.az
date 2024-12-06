<?php

Route::group(['prefix' => '/operator', 'middleware' => 'Operator'], function () {
    Route::get('/', 'OperatorUserController@index')->name("operator_page");
    Route::get('/login-client-account/{client_id}', 'OperatorUserController@login_client_account')->name("login_client_account");
    Route::post('/verify-client-account', 'OperatorUserController@verify_client_account')->name("operator_verify_client_account");
    Route::group(['prefix' => '/information'], function () {
        Route::get('/', 'OperatorUserController@get_operator_page')->name("information_page");
        Route::post('/client', 'OperatorUserController@get_client')->name("operator_get_client");
        Route::post('/packages', 'OperatorUserController@get_packages')->name("operator_get_packages");
        Route::group(['prefix' => '/sub-accounts/{client_id}'], function () {
            Route::get('/', 'OperatorUserController@get_sub_accounts_and_their_packages')->name("get_sub_accounts_page");
        });
    });
    Route::group(['prefix' => '/make-orders'], function () {
        Route::get('/', 'OperatorUserController@get_make_orders_page')->name("get_make_orders_page");
        Route::get('/show', 'OperatorUserController@show_make_orders')->name("show_make_orders");
        Route::get('/show-statuses', 'OperatorUserController@get_statuses')->name("get_statuses_for_make_orders");
        Route::get('/update/{group_code}', 'OperatorUserController@get_update_make_order')->name("get_update_make_order");
        Route::post('/update/{group_code}', 'OperatorUserController@post_update_make_order')->name("post_update_make_order");
        Route::post('/disable-enable', 'OperatorUserController@disable_or_enable_make_order_for_client')->name("disable_or_enable_make_order_for_client");
    });
    Route::group(['prefix' => '/anonymous'], function () {
        Route::get('/', 'OperatorUserController@get_anonymous_page')->name("get_anonymous_page");
        Route::get('/show', 'OperatorUserController@show_anonymous_orders')->name("show_anonymous_orders");
        Route::post('/control', 'OperatorUserController@client_control')->name("anonymous_client_control");
        Route::post('/merge', 'OperatorUserController@merge_client_and_package')->name("merge_client_and_package");
    });
    Route::group(['prefix' => '/packages'], function () {
        Route::get('/', 'OperatorUserController@get_packages_page')->name("operator_get_packages_page");
        Route::get('/show', 'OperatorUserController@show_packages')->name("operator_show_packages");
        Route::post('/show-events', 'OperatorUserController@show_package_events')->name("operator_show_package_events");
        Route::post('/show-events-invoice', 'OperatorUserController@show_package_invoice_events')->name("operator_show_package_invoice_events");
        Route::delete('/delete-invoice', 'OperatorUserController@package_delete_invoice_file')->name("operator_package_delete_invoice_file");
    });
    Route::group(['prefix' => '/courier'], function () {
        Route::get('/', 'CourierOperatorController@get_courier_page')->name("operator_get_courier_page");
        Route::get('/show-orders', 'CourierWarehouseController@show_courier_orders')->name("operator_show_courier_orders");
        Route::delete('/delete', 'CourierOperatorController@delete_courier_order')->name("operator_delete_courier_order");
        Route::post('/set-status', 'CourierOperatorController@set_status_order')->name("operator_set_status_order");
        Route::post('/choose-courier', 'CourierWarehouseController@choose_courier_for_order')->name("operator_choose_courier_for_order");
        Route::get('/new-order', 'CourierOperatorController@get_new_courier_order_page')->name("operator_get_new_courier_order_page");
        Route::post('/get-client-details', 'CourierOperatorController@get_client_details')->name("operator_get_client_details");
        Route::post('/get-courier-payment-types', 'CourierOperatorController@get_courier_payment_types')->name("operator_get_courier_payment_types");
        Route::post('/get-delivery-payment-types', 'CourierOperatorController@get_delivery_payment_types')->name("operator_get_delivery_payment_types");
        Route::post('/create-order', 'CourierOperatorController@create_courier_order')->name("operator_create_courier_order");
        Route::get('/export-courier-orders', 'CourierWarehouseController@export_courier_orders')->name("operator_export_courier_orders");
    });
});
