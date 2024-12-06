<?php

Route::group(['prefix' => '/collector', 'middleware' => 'Collector'], function () {
    Route::get('/', 'CollectorController@get_collector')->name("get_collector");
    Route::get('/get-containers', 'CollectorController@get_containers')->name("get_containers_by_flight");
    Route::post('/check-client', 'CollectorController@check_client')->name("check_client");
    Route::post('/get-category-for-seller', 'CollectorController@get_default_category_for_seller')->name("get_category_for_seller");
    Route::post('/add', 'CollectorController@add_collector')->name("add_collector");
    Route::post('/check-package', 'CollectorController@check_package')->name("check_package_collector");
    Route::post('/generate-internal', 'CollectorController@get_internal_id')->name("generate_internal_id");
    Route::post('/add-new-seller', 'CollectorController@add_new_seller_in_collector')->name("add_new_seller");
    Route::post('/add-new-category', 'CollectorController@add_new_category_in_collector')->name("add_new_category");
    Route::post('/add-new-container', 'ContainerController@create_single_container')->name("create_single_container");

    Route::group(['prefix' => '/images'], function () {
        Route::get('/show', 'CollectorController@show_images')->name("show_images_in_collector");
        Route::delete('/delete', 'CollectorController@delete_image')->name("delete_image_in_collector");
    });

    Route::group(['prefix' => '/flights'], function () {
        Route::get('/', 'FlightController@show')->name("show_flights_collector");
        Route::post('/add', 'FlightController@add')->name("add_flight_collector");
        Route::post('/update', 'FlightController@update')->name("update_flight_collector");
        Route::post('/close', 'FlightController@closeNew')->name("close_flight_collector");
        Route::delete('/delete', 'FlightController@delete')->name("delete_flight_collector");
    });

    Route::group(['prefix' => '/containers'], function () {
        Route::get('/', 'ContainerController@show')->name("show_containers_collector");
        Route::post('/add', 'ContainerController@add')->name("add_container_collector");
        Route::delete('/delete', 'ContainerController@delete')->name("delete_container_collector");
    });

    Route::group(['prefix' => '/packages'], function () {
        Route::get('/', 'PackageController@manifest_collector')->name("manifest_collector");
        Route::get('/search', 'PackageController@collector_search_packages')->name("collector_search_packages");
        Route::delete('/delete', 'PackageController@collector_delete_package')->name("collector_delete_package");
        Route::get('/collector-package', 'PackageController@collector_packages')->name("collector_packages");
    });

    Route::group(['prefix' => '/anonymous'], function () {
        Route::get('/', 'OperatorUserController@get_anonymous_page')->name("collector_get_anonymous_page");
        Route::get('/show', 'OperatorUserController@show_anonymous_orders')->name("collector_show_anonymous_orders");
        Route::post('/control', 'OperatorUserController@client_control')->name("collector_anonymous_client_control");
        Route::post('/merge', 'OperatorUserController@merge_client_and_package')->name("collector_merge_client_and_package");
    });

    Route::group(['prefix' => '/reports'], function () {
        Route::get('/{type}', 'CollectorReportsController@get_reports_page')->name("collector_reports_page");
        Route::post('/{type}', 'CollectorReportsController@post_reports')->name("collector_post_reports");
    });

    Route::group(['prefix' => '/couriers'], function () {
        Route::get('/scanner', 'CollectorController@foreign_courier_companies')->name("foreign_courier_companies");
    });

    
  
});

Route::group(['prefix' => '/collect'], function () {
    Route::post('/way', 'CollectorController@get_waybill_data')->name('get_waybill_data');
    Route::get('/', 'CollectorController@add_container_page')->name("add_container_page");
    Route::post('/change-position', 'CollectorController@change_position')->name("collector_change_position");
});
