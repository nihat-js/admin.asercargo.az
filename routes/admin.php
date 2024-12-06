<?php

Route::group(['prefix' => '/reports', 'middleware' => 'Admin'], function () {
	Route::get('/declaration', 'ReportsController@get_declaration_page')->name("get_declaration_page");
	Route::post('/declaration', 'ReportsController@get_declaration')->name("get_declaration");
	Route::get('/manifest', 'ReportsController@get_admin_manifest_page')->name("get_admin_manifest_page");
	Route::post('/manifest', 'ReportsController@admin_manifest')->name("admin_manifest");
	Route::get('/depesh', 'ReportsController@get_flight_depesh')->name("get_flight_depesh");
	Route::post('/depesh', 'ReportsController@flight_depesh')->name("flight_depesh");
	Route::get('/no-invoice', 'ReportsController@get_no_invoice')->name("get_no_invoice");
	Route::get('/cashier', 'ReportsController@get_cashier_page')->name("reports_get_cashier_page");
	Route::post('/cashier', 'ReportsController@post_cashier')->name("reports_post_cashier");
	Route::get('/payments', 'ReportsController@get_payments_page')->name("reports_get_payments_page");
	Route::post('/payments', 'ReportsController@post_payments')->name("reports_post_payments");
	Route::get('/warehouse', 'ReportsController@get_warehouse_page')->name("reports_get_warehouse_page");
	Route::post('/warehouse', 'ReportsController@post_warehouse')->name("reports_post_warehouse");
	Route::get('/in-baku', 'ReportsController@reports_in_baku_page')->name("reports_in_baku_page");
	Route::get('/phones', 'ReportsController@clients_phones')->name("reports_clients_phones");
	Route::get('/emails', 'ReportsController@clients_emails_with_parent_id')->name("clients_emails_with_parent_id");
	Route::get('/inbound-packages', 'ReportsController@get_inbound_packages_page')->name("reports_get_inbound_packages_page");
	Route::post('/inbound-packages', 'ReportsController@post_inbound_packages')->name("reports_post_inbound_packages");
	Route::get('/delivered-packages', 'ReportsController@get_delivered_packages_page')->name("reports_get_delivered_packages_page");
	Route::post('/delivered-packages', 'ReportsController@post_delivered_packages')->name("reports_post_delivered_packages");
	Route::get('/courier-orders', 'ReportsController@get_courier_orders_page')->name("reports_get_courier_orders_page");
	Route::post('/courier-orders', 'ReportsController@post_courier_orders')->name("reports_post_courier_orders");
	Route::get('/courier-orders-packages', 'ReportsController@get_courier_orders_packages_page')->name("reports_get_courier_orders_packages_page");
	Route::post('/courier-orders-packages', 'ReportsController@post_courier_orders_packages')->name("reports_post_courier_orders_packages");

	Route::get('/partner', 'ReportsController@get_partner_reports')->name("get_partner_reports");
	Route::post('/partner', 'ReportsController@partner_reports')->name("post_partner_reports");

    Route::get('/payment-task', 'ReportsController@get_payment_task_reports')->name("get_payment_task_reports");
    Route::post('/payment-task', 'ReportsController@payment_task_reports')->name("post_payment_task_reports");

    Route::get('/partner-payment', 'ReportsController@get_partner_payment_reports')->name("get_partner_payment_reports");
    Route::post('/partner-payment', 'ReportsController@partner_payment_reports')->name("post_partner_payment_reports");


	Route::get('/custom', 'CustomController@index')->name("custom_index");
	Route::post('/custom/send', 'CustomController@post_custom_response')->name("post_custom_response");
	Route::post('/custom/declaration', 'CustomController@post_declaration')->name("post_declaration");
	Route::post('/custom/deleted', 'CustomController@post_custom_deleted')->name("post_custom_deleted");
	Route::post('/custom/awb', 'CustomController@post_awb')->name("post_awb");
	Route::post('/custom/putAirWay', 'CustomController@putAirWay')->name("putAirWay");
	Route::post('/custom/checkPack', 'CustomController@checkPack')->name("checkPack");
	Route::post('/custom/updatePackage', 'CustomController@updatePackage')->name("updatePackage");
});

Route::group(['prefix' => '/send-sms', 'middleware' => 'Admin'], function () {
	Route::get('/no-invoice', 'SendSMSController@get_send_sms_for_no_invoice_package_page')->name("get_send_sms_for_no_invoice_package_page");
	Route::post('/no-invoice', 'SendSMSController@send_sms_for_no_invoice_package')->name("send_sms_for_no_invoice_package");
});

Route::group(['prefix' => '/change-status', 'middleware' => 'Admin'], function () {
	Route::get('/in-baku', 'ChangeStatusController@get_packages_in_baku_page')->name("admin_get_packages_in_baku_page");
	Route::post('/in-baku', 'ChangeStatusController@post_packages_in_baku')->name("admin_post_packages_in_baku");

    Route::get('/custom-status', 'ChangeStatusController@get_packages_custom_status_page')->name("admin_get_custom_status");
    Route::post('/custom-status', 'ChangeStatusController@post_packages_custom_status')->name("admin_post_custom_status");
});

Route::group(['prefix' => '/orders', 'middleware' => 'Admin'], function () {
	Route::get('/all', 'OrderController@get_all_orders')->name("get_all_orders");
	Route::group(['prefix' => '/specials'], function () {
		Route::get('/', 'OrderController@get_special_orders')->name("show_special_orders");
		Route::get('/update/{order_id}', 'OrderController@get_update_special_order')->name("get_update_special_order");
		Route::post('/update/{order_id}', 'OrderController@post_update_special_order')->name("post_update_special_order");
		Route::post('/declare/{order_id}', 'OrderController@declare_special_order')->name("declare_special_order");
		Route::post('/disable/{order_id}', 'OrderController@disable_order_for_client')->name("disable_order_for_client");
		Route::post('/enable/{order_id}', 'OrderController@enable_order_for_client')->name("enable_order_for_client");
	});
});

Route::group(['prefix' => '/clients', 'middleware' => 'Admin'], function () {
	Route::get('/login-client-account/{client_id}', 'OperatorUserController@login_client_account')->name("admin_login_client_account");
	Route::get('/', 'ClientController@show')->name("show_clients");
	Route::post('/add', 'ClientController@add')->name("add_client");
	Route::post('/update', 'ClientController@update')->name("update_client");
	Route::delete('/delete', 'ClientController@delete')->name("delete_client");
	Route::post('/export-packages', 'ClientController@admin_export_packages')->name("admin_export_client_packages");
	Route::post('/client-log', 'ClientController@client_log')->name("client_log");

	Route::group(['prefix' => '/balance-operations'], function () {
		Route::post('/set', 'BalanceOperationsController@set_client_balance')->name("admin_set_client_balance");
	});
});

Route::group(['prefix' => '/users-log'], function () {
	Route::get('/set', 'UsersLogController@get_logs')->name("admin_users_logs");
});

Route::group(['prefix' => '/packages', 'middleware' => 'Admin'], function () {
	Route::get('/', 'PackageController@show')->name("show_packages");
	Route::delete('/delete', 'PackageController@delete')->name("delete_package");
	Route::post('/set-declared-status', 'PackageController@set_package_declared_status')->name("set_package_declared_status");
	Route::post('/create-item', 'PackageController@create_item_for_package')->name("create_item_for_package");
	Route::post('/change-client', 'PackageController@change_client')->name("change_client_for_package");
	Route::post('/change-branch', 'PackageController@change_branch')->name("change_branch_for_package");

	Route::post('/change-weight', 'PackageController@change_weight')->name("change_weight_for_package");
	Route::post('/change-status', 'ChangeStatusController@change_status_for_single_package')->name("admin_change_status_for_single_package");
	Route::get('/show-status-history', 'PackageController@show_status_history_for_package')->name("admin_show_status_history_for_package");
	Route::delete('/delete-from-customs', 'PackageController@delete_from_customs')->name('delete_from_customs');
});

Route::group(['prefix' => '/queues', 'middleware' => 'Admin'], function () {
	Route::get('/', 'QueueController@show_queues')->name("show_queues");
});

Route::group(['prefix' => '/locations', 'middleware' => 'Admin'], function () {
	Route::get('/', 'LocationController@show')->name("show_locations");
//        Route::post('/volume-consider', 'LocationController@change_volume_consider')->name("change_volume_consider");
	Route::post('/add', 'LocationController@add')->name("add_location");
	Route::post('/update', 'LocationController@update')->name("update_location");
	Route::delete('/delete', 'LocationController@delete')->name("delete_location");
});

Route::group(['prefix' => '/positions', 'middleware' => 'Admin'], function () {
	Route::get('/', 'PositionController@show')->name("show_positions");
	Route::post('/add', 'PositionController@add')->name("add_position");
	Route::post('/update', 'PositionController@update')->name("update_position");
	Route::delete('/delete', 'PositionController@delete')->name("delete_position");
});

Route::group(['prefix' => '/sellers', 'middleware' => 'Admin'], function () {
	Route::get('/', 'SellerController@show')->name("show_sellers");
	Route::post('/add', 'SellerController@add')->name("add_seller");
	Route::post('/update', 'SellerController@update')->name("update_seller");
	Route::delete('/delete', 'SellerController@delete')->name("delete_seller");
	Route::delete('/delete/image', 'SellerController@delete_icon')->name("delete_seller_icon");
});

Route::group(['prefix' => '/categories', 'middleware' => 'Admin'], function () {
	Route::get('/', 'CategoryController@show')->name("show_categories");
	Route::post('/add', 'CategoryController@add')->name("add_category");
	Route::post('/update', 'CategoryController@update')->name("update_category");
	Route::delete('/delete', 'CategoryController@delete')->name("delete_category");
});

Route::group(['prefix' => '/exchange-rates', 'middleware' => 'Admin'], function () {
	Route::get('/', 'ExchangeRateController@show')->name("show_exchange_rates");
	Route::post('/add', 'ExchangeRateController@add')->name("add_exchange_rate");
	Route::post('/update', 'ExchangeRateController@update')->name("update_exchange_rate");
	Route::delete('/delete', 'ExchangeRateController@delete')->name("delete_exchange_rate");
});

Route::group(['prefix' => '/currencies', 'middleware' => 'Admin'], function () {
	Route::get('/', 'CurrencyController@show')->name("show_currencies");
	Route::post('/add', 'CurrencyController@add')->name("add_currency");
	Route::post('/update', 'CurrencyController@update')->name("update_currency");
	Route::delete('/delete', 'CurrencyController@delete')->name("delete_currency");
});

Route::group(['prefix' => '/operators', 'middleware' => 'Admin'], function () {
	Route::get('/', 'OperatorController@show')->name("show_operators");
	Route::post('/add', 'OperatorController@add')->name("add_operator");
	Route::post('/update', 'OperatorController@update')->name("update_operator");
	Route::delete('/delete', 'OperatorController@delete')->name("delete_operator");
});

Route::group(['prefix' => '/roles', 'middleware' => 'Admin'], function () {
	Route::get('/', 'RoleController@show')->name("show_roles");
	Route::post('/add', 'RoleController@add')->name("add_role");
	Route::post('/update', 'RoleController@update')->name("update_role");
	Route::delete('/delete', 'RoleController@delete')->name("delete_role");
});

Route::group(['prefix' => '/contracts', 'middleware' => 'Admin'], function () {
	Route::get('/', 'ContractController@show')->name("show_contracts");
	Route::post('/set-to-default', 'ContractController@set_to_default_contract')->name("set_to_default_contract");
	Route::post('/add', 'ContractController@add')->name("add_contract");
	Route::post('/update', 'ContractController@update')->name("update_contract");
	Route::delete('/delete', 'ContractController@delete')->name("delete_contract");

	Route::group(['prefix' => '/details'], function () {
		Route::get('/', 'ContractDetailsController@show')->name("show_contract_details");
		Route::post('/volume-consider', 'ContractDetailsController@change_volume_consider')->name("change_volume_consider_contract_detail");
//            Route::post('/set-to-default', 'ContractDetailsController@set_to_default_contract_detail')->name("set_to_default_contract_detail");
		Route::post('/add', 'ContractDetailsController@add')->name("add_contract_detail");
		Route::post('/update', 'ContractDetailsController@update')->name("update_contract_detail");
		Route::delete('/delete', 'ContractDetailsController@delete')->name("delete_contract_detail");
	});
});

Route::group(['prefix' => '/containers', 'middleware' => 'Admin'], function () {
	Route::get('/', 'ContainerController@show')->name("show_containers");
	Route::post('/add', 'ContainerController@add')->name("add_container");
	Route::delete('/delete', 'ContainerController@delete')->name("delete_container");
});

Route::group(['prefix' => '/flights', 'middleware' => 'Admin'], function () {
	Route::get('/', 'FlightController@show')->name("show_flights");
	Route::post('/add', 'FlightController@add')->name("add_flight");
	Route::post('/update', 'FlightController@update')->name("update_flight");
	Route::post('/close', 'FlightController@closeNew')->name("close_flight");
	Route::delete('/delete', 'FlightController@delete')->name("delete_flight");
//        Route::group(['prefix'=>'/flights'], function () {
//            Route::post('/fact-take-off', 'FlightController@set_fact_take_off')->name("set_fact_take_off");
//            Route::post('/fact-arrival', 'FlightController@set_fact_arrival')->name("set_fact_arrival");
//        });
});

Route::group(['prefix' => '/options', 'middleware' => 'Admin'], function () {
	Route::get('/', 'OptionController@show')->name("show_options");
	Route::post('/add', 'OptionController@add')->name("add_option");
	Route::post('/update', 'OptionController@update')->name("update_option");
	Route::delete('/delete', 'OptionController@delete')->name("delete_option");
});
    
Route::group(['prefix' => '/branch', 'middleware' => 'Admin'], function () {
        Route::get('/', 'BranchController@show')->name("show_branch");
        Route::post('/add', 'BranchController@add')->name("add_branch");
        Route::post('/update', 'BranchController@update')->name("update_branch");
});

Route::group(['prefix' => '/news'], function () {
        Route::get('/', 'NewsController@show_news')->name("show_news");
        Route::post('/add', 'NewsController@add_news')->name("add_news");
        Route::post('/update', 'NewsController@update_news')->name("update_news");
        Route::delete('/delete', 'NewsController@delete_news')->name("deleted_news");
});

Route::group(['prefix' => '/user/courier', 'middleware' => 'Admin'], function () {
	Route::get('/', 'CourierOperatorController@get_courier_page')->name("admin_get_courier_page");
	Route::get('/show-orders', 'CourierWarehouseController@show_courier_orders')->name("admin_show_courier_orders");
	Route::post('/set-status', 'CourierOperatorController@set_status_order')->name("admin_set_status_order");
	Route::post('/choose-courier', 'CourierWarehouseController@choose_courier_for_order')->name("admin_choose_courier_for_order");
	Route::get('/new-order', 'CourierOperatorController@get_new_courier_order_page')->name("admin_get_new_courier_order_page");
	Route::post('/get-client-details', 'CourierOperatorController@get_client_details')->name("admin_get_client_details");
	Route::post('/get-courier-payment-types', 'CourierOperatorController@get_courier_payment_types')->name("admin_get_courier_payment_types");
	Route::post('/get-delivery-payment-types', 'CourierOperatorController@get_delivery_payment_types')->name("admin_get_delivery_payment_types");
	Route::post('/create-order', 'CourierOperatorController@create_courier_order')->name("admin_create_courier_order");
});

Route::group(['prefix' => '/promo-codes', 'middleware' => 'Admin'], function () {
	Route::get('/', 'PromoCodesController@show_promo_codes')->name("show_promo_codes");
	Route::post('/create', 'PromoCodesController@create_promo_codes')->name("create_promo_codes");
	Route::delete('/delete', 'PromoCodesController@delete_promo_code')->name("delete_promo_code");
	Route::group(['prefix' => '/groups'], function () {
		Route::get('/', 'PromoCodesController@show_promo_codes_groups')->name("show_promo_codes_groups");
		Route::post('/create', 'PromoCodesController@create_promo_codes_group')->name("create_promo_codes_group");
		Route::delete('/delete', 'PromoCodesController@delete_promo_codes_group')->name("delete_promo_codes_group");
	});
});

Route::group(['prefix' => '/dashboard', 'middleware' => 'Admin'], function () {
    Route::get('/', 'DashboardController@show')->name("show_dashboard");
});
    
    Route::group(['prefix' => '/warehouse-debt', 'middleware' => 'Admin'], function () {
        Route::get('/', 'WarehouseDebtController@getDebt')->name("show_warehouse_debt");
        Route::post('/update', 'WarehouseDebtController@updateDebt')->name("update_warehouse_debt");
    });

Route::get('/excel', 'ExcelController@show')->name("show_excel");
Route::post('/upload-courier-excel', 'ExcelController@upload')->name('upload_courier_excel');

include('admin_courier.php');
