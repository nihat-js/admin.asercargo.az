<?php

// for admin
Route::group(['prefix' => '/courier', 'middleware' => 'Admin'], function () {
    Route::group(['prefix' => '/settings'], function () {
        Route::get('/', 'CourierController@show_settings')->name("admin_courier_settings_page");
        Route::post('/update', 'CourierController@update_settings')->name("admin_courier_settings_update");
        Route::get('/show-log', 'CourierController@show_settings_log')->name("admin_courier_show_settings_log");
    });
    Route::group(['prefix' => '/daily-limits'], function () {
        Route::get('/', 'CourierController@show_daily_limits')->name("admin_courier_daily_limits_page");
        Route::post('/add', 'CourierController@add_daily_limit')->name("admin_courier_daily_limit_add");
        Route::post('/update', 'CourierController@update_daily_limit')->name("admin_courier_daily_limit_update");
        Route::delete('/delete', 'CourierController@delete_daily_limit')->name("admin_courier_daily_limit_delete");
    });
    Route::group(['prefix' => '/payment-types'], function () {
        Route::get('/', 'CourierController@show_payment_types')->name("admin_courier_payment_types_page");
        Route::post('/add', 'CourierController@add_payment_type')->name("admin_courier_payment_type_add");
        Route::post('/update', 'CourierController@update_payment_type')->name("admin_courier_payment_type_update");
        Route::delete('/delete', 'CourierController@delete_payment_type')->name("admin_courier_payment_type_delete");
    });
    Route::group(['prefix' => '/zones'], function () {
        Route::get('/', 'CourierController@show_zones')->name("admin_courier_zones_page");
        Route::post('/add', 'CourierController@add_zone')->name("admin_courier_zone_add");
        Route::post('/update', 'CourierController@update_zone')->name("admin_courier_zone_update");
        Route::delete('/delete', 'CourierController@delete_zone')->name("admin_courier_zone_delete");
        Route::group(['prefix' => '/payment-types'], function () {
            Route::get('/', 'CourierController@get_payment_types_for_zones')->name("admin_get_payment_types_for_zones");
            Route::post('/add', 'CourierController@add_payment_type_for_zones')->name("admin_courier_payment_type_for_zones_add");
            Route::delete('/delete', 'CourierController@delete_payment_type_for_zones')->name("admin_payment_type_for_zones_delete");
        });
    });
    Route::group(['prefix' => '/areas'], function () {
        Route::get('/', 'CourierController@show_areas')->name("admin_courier_areas_page");
        Route::post('/add', 'CourierController@add_area')->name("admin_courier_area_add");
        Route::post('/update', 'CourierController@update_area')->name("admin_courier_area_update");
        Route::delete('/delete', 'CourierController@delete_area')->name("admin_courier_area_delete");
        Route::post('/active', 'CourierController@active_area')->name("admin_courier_area_active");
    });
    Route::group(['prefix' => '/metro-stations'], function () {
        Route::get('/', 'CourierController@show_metro_stations')->name("admin_courier_metro_stations_page");
        Route::post('/add', 'CourierController@add_metro_station')->name("admin_courier_metro_station_add");
        Route::post('/update', 'CourierController@update_metro_station')->name("admin_courier_metro_station_update");
        Route::delete('/delete', 'CourierController@delete_metro_station')->name("admin_courier_metro_station_delete");
    });

    Route::group(['prefix' => '/regions'], function () {
        Route::get('/', 'CourierController@show_region')->name("admin_courier_region_page");
        Route::post('/add', 'CourierController@add_region')->name("admin_courier_region_add");
        Route::post('/update', 'CourierController@update_region')->name("admin_courier_region_update");
        Route::delete('/delete', 'CourierController@delete_region')->name("admin_courier_region_delete");
    });


    Route::group(['prefix' => '/region-tariffs'], function () {
        Route::get('/', 'CourierController@show_region_tariff')->name("admin_courier_show_region_tariff");
        Route::post('/add', 'CourierController@add_region_tariff')->name("admin_add_region_tariff");
        Route::post('/update', 'CourierController@update_region_tariff')->name("admin_update_region_tariff");
        Route::delete('/delete', 'CourierController@delete_region_tariff')->name("admin_delete_region_tariff");
    });

});