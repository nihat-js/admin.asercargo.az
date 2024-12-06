<?php
use App\Http\Controllers\PackageController;
Route::group(['prefix' => '/manager', 'middleware' => 'Manager'], function () {
    Route::group(['prefix' => '/packages'], function () {
        Route::get('/', 'PackageController@manager_show_package')->name("show_packages_manager");
        Route::post('/change-weight', 'PackageController@change_weight')->name("change_weight_for_package");
        Route::post('/change-client', 'PackageController@change_client')->name("change_client_for_package");
        Route::post('/change-status', 'ChangeStatusController@change_status_for_single_package')->name("admin_change_status_for_single_package");
        Route::get('/show-status-history', 'PackageController@show_status_history_for_package')->name("admin_show_status_history_for_package");
        Route::delete('/delete-from-customs', 'PackageController@delete_from_customs')->name('delete_from_customs');
        Route::delete('/delete', 'PackageController@delete')->name("delete_package");
    });


    Route::group(['prefix' => '/clients'], function () {
        Route::get('/login-client-account/{client_id}', 'OperatorUserController@login_client_account')->name("admin_login_client_account");
        Route::get('/', 'ClientController@manager_client_show')->name("show_clients_manager");
        Route::post('/add', 'ClientController@add')->name("add_client");
        Route::post('/update', 'ClientController@update')->name("update_client");
        Route::delete('/delete', 'ClientController@delete')->name("delete_client");
        Route::post('/export-packages', 'ClientController@admin_export_packages')->name("admin_export_client_packages");
    
        Route::group(['prefix' => '/balance-operations'], function () {
            Route::post('/set', 'BalanceOperationsController@set_client_balance')->name("admin_set_client_balance");
        });
    });


    Route::group(['prefix' => '/reports'], function () {
        Route::get('/declaration', 'ReportsController@get_declaration_page')->name("get_declaration_page_manager");
        Route::post('/declaration', 'ReportsController@get_declaration')->name("get_declaration");
        Route::get('/partner', 'ReportsController@get_partner_reports')->name("get_partner_reports")->middleware('ManagerUser');
        Route::post('/partner', 'ReportsController@partner_reports')->name("post_partner_reports")->middleware('ManagerUser');
        Route::get('/warehouse', 'ReportsController@get_warehouse_page')->name("reports_get_warehouse_page");
        Route::post('/warehouse', 'ReportsController@post_warehouse')->name("reports_post_warehouse");
    });


    Route::group(['prefix' => '/exchange-rates'], function () {
        Route::get('/', 'ExchangeRateController@show')->name("show_exchange_rates_manager");
        Route::post('/add', 'ExchangeRateController@add')->name("add_exchange_rate");
        Route::post('/update', 'ExchangeRateController@update')->name("update_exchange_rate");
        Route::delete('/delete', 'ExchangeRateController@delete')->name("delete_exchange_rate");
    });

    Route::group(['prefix' => '/flights'], function () {
        Route::get('/', 'FlightController@show')->name("show_flights");
        Route::post('/add', 'FlightController@add')->name("add_flight");
        Route::post('/update', 'FlightController@update')->name("update_flight");
        Route::post('/close', 'FlightController@closeNew')->name("close_flight");
        Route::delete('/delete', 'FlightController@delete')->name("delete_flight");
    });

    Route::group(['prefix' => '/courier'], function () {
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
    });
    
});
