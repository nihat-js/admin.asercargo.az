<?php

Route::group(['prefix' => '/cashier', 'middleware' => 'Cashier'], function () {
    Route::get('/', 'CashierController@index')->name("cashier_page");
    Route::post('/packages', 'CashierController@get_packages')->name("cashier_get_packages");
    Route::post('/pay', 'CashierController@set_to_paid')->name("cashier_pay");
    Route::post('/qmatic', 'CashierController@qmatic_print')->name("qmatic_print");
    Route::post('/print-receipt-log', 'CashierController@add_print_receipt_log')->name("print_receipt_log");
    Route::post('/report', 'ReportsController@post_cashier')->name("cashier_report");

    Route::post('/get-promo-code', 'CashierController@get_promo_code')->name("cashier_get_promo_code");

    Route::group(['prefix' => '/courier'], function () {
        Route::get('/', 'CourierCashierController@get_courier_page')->name("cashier_get_courier_page");
        Route::post('/get-orders', 'CourierCashierController@get_courier_orders')->name("cashier_get_courier_orders");
        Route::post('/set-to-paid-and-delivered', 'CourierCashierController@set_to_paid_and_delivered')->name("cashier_set_to_paid_and_delivered");
    });

    Route::group(['prefix' => '/balance-operations'], function () {
        Route::post('/get', 'BalanceOperationsController@get_client_balance')->name("cashier_get_client_balance");
        Route::post('/set', 'BalanceOperationsController@set_client_balance')->name("cashier_set_client_balance");
    });
});