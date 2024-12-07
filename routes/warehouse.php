<?php

Route::group(['prefix' => '/warehouse', 'middleware' => 'Delivery'], function () {
    Route::get('/', 'WareHouseController@index')->name("warehouse_page");

    Route::group(['prefix' => '/delivery'], function () {
        Route::get('/', 'DeliveryController@index')->name("delivery_page");
        Route::post('/packages', 'DeliveryController@get_packages')->name("delivery_get_packages");
        Route::post('/delivered', 'DeliveryController@set_delivered')->name("set_delivered");
    });

    Route::group(['prefix' => '/distributor'], function () {
        Route::get('/', 'DistributorController@index')->name("distributor_page");
        Route::get('/detained-at-customs', 'DistributorController@detained_at_customs_page')->name("warehouse_detained_at_customs_page");
        //Route::get('/report', 'DistributorController@get_report_page')->name("report_page");
        //Route::post('/report', 'ReportsController@post_warehouse')->name("post_report_for_warehouse");
        //Route::post('/report/show', 'DistributorController@get_report')->name("distributor_report");
        Route::post('/change-position', 'DistributorController@change_position')->name("distributor_change_position");
        Route::post('/change-status/in-baku', 'ChangeStatusController@post_packages_in_baku')->name("warehouse_post_packages_in_baku");
        Route::get('/detained-at-customs', 'DistributorController@detained_at_customs_page')->name("warehouse_detained_at_customs_page");
        Route::get('/detained-at-customs-show-packages', 'DistributorController@detained_at_customs')->name("warehouse_detained_at_customs");
        Route::post('/detained-at-customs-notification', 'DistributorController@detained_at_customs_notification')->name("warehouse_detained_at_customs_notification");
        Route::get("/change-branch", "DistributorController@changePackageBranchView")->name("warehouse_change_package_branch_view");
        Route::post("/change-branch", "DistributorController@changePackageBranch")->name("warehouse_change_package_branch");

        Route::get("/change-in-baku", "DistributorController@changePackageInBakuView")->name("warehouse_change_package_in_baku_view");
        Route::post("/change-in-baku", "DistributorController@changePackageInBaku")->name("warehouse_change_package_in_baku");
    });

    Route::group(['prefix' => '/partner'], function (){
        Route::get('/partner-page', 'WareHouseController@get_partner_page')->name("get_partner_page");

        Route::group(['prefix' => '/canada-shop'], function (){
            Route::get('/', 'AllPartnerController@get_canadashop')->name("get_canadashop");
            Route::post('/set', 'AllPartnerController@set_canadashop_flight_package')->name("set_canadashop_flight_package");
        });

        Route::group(['prefix' => '/hepsiglobal'], function (){
            Route::get('/', 'DistributorController@get_partner_package')->name("get_partner_package");
            Route::post('/set', 'DistributorController@partner_change_position')->name("partner_change_position");
        });


    });

    Route::group(['prefix' => '/report'], function () {
        Route::get('/', 'DistributorController@get_report_page')->name("report_page");
        Route::get('/in-baku', 'ReportsController@reports_in_baku_page')->name("warehouse_reports_in_baku");
        Route::get('/inbound-packages', 'ReportsController@warehouse_inbound_packages')->name("warehouse_reports_inbound_packages");
        Route::get('/delivered-packages', 'ReportsController@warehouse_delivered_packages')->name("warehouse_reports_delivered_packages");
    });

    Route::group(['prefix' => '/courier'], function () {
        Route::get('/', 'CourierWarehouseController@get_courier_page')->name("warehouse_courier_page");
        Route::get('/show-orders', 'CourierWarehouseController@show_courier_orders')->name("warehouse_show_courier_orders");
        Route::post('/choose-courier', 'CourierWarehouseController@choose_courier_for_order')->name("warehouse_choose_courier_for_order");
        Route::post('/print-receipt', 'CourierWarehouseController@print_courier_receipt')->name("warehouse_print_courier_receipt");
        Route::post('/print-receipt-log', 'CashierController@add_print_receipt_log')->name("warehouse_print_receipt_log");
        Route::get('/export-courier-orders', 'CourierWarehouseController@export_courier_orders')->name("warehouse_export_courier_orders");
        Route::post('/delivered-to-the-courier', 'CourierWarehouseController@delivered_to_the_courier')->name("warehouse_delivered_to_the_courier");
        
        Route::post('/update-date', 'CourierWarehouseController@update_date')->name('warhouse_update_date');
        Route::post('/set-azerpost', 'CourierWarehouseController@set_azerpost')->name("warehouse_set_azerpost");

    });
});
