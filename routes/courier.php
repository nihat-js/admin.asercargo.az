<?php

// for courier role
Route::group(['prefix' => '/courier-user', 'middleware' => 'Courier'], function () {
    Route::group(['prefix' => '/courier'], function () {
        Route::get('/', 'CourierWarehouseController@get_courier_page_for_courier_user')->name("courier_courier_page");
        Route::get('/show-orders', 'CourierWarehouseController@show_courier_orders')->name("courier_show_courier_orders");
        Route::post('/choose-courier', 'CourierWarehouseController@choose_courier_for_order')->name("courier_choose_courier_for_order");
        Route::get('/export-courier-orders', 'CourierWarehouseController@export_courier_orders')->name("courier_export_courier_orders");
        Route::post('/delivered-to-the-courier', 'CourierWarehouseController@delivered_to_the_courier')->name("courier_delivered_to_the_courier");
    });
});