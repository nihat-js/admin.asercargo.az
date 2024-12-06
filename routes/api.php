<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/login', 'Api\LoginController@login');

//collector
Route::group(['prefix' => 'collector', 'middleware' => 'ApiCollector'], function () {
    Route::get('/check-package', 'Api\CollectorController@check_package');
    Route::get('/check-client', 'Api\CollectorController@client_control');
    Route::get('/get-default-category-for-seller', 'Api\CollectorController@get_default_category_for_seller');
    Route::post('/save-package', 'Api\CollectorController@save_package');
    Route::get('/receive-package', 'Api\CollectorController@receive_package');
    Route::post('/create-batch', 'Api\CollectorController@create_batch');
});

//distributor
Route::group(['prefix' => 'distributor', 'middleware'=>'ApiDistributor'], function () {
    Route::post('/change-position', 'Api\DistributorController@change_position');
});

//queue
Route::group(['prefix' => 'queue'], function () {
    Route::get('/get-queue-table', 'Api\QueueController@show_queue_table'); // location (location_id)
    Route::get('/get-user-details', 'Api\QueueController@get_user_details'); // passport (user_id)
    Route::get('/get-user-packages', 'Api\QueueController@get_user_packages'); // user (user_id), location (location_id)
    Route::post('/create-queue', 'Api\QueueController@get_queue'); // user (user_id), type (c->cashier, d->delivery, o->operator, i->information), location (location_id)
    Route::get('/payment-receipt', 'Api\QueueController@create_payment_receipt'); // user (user_id), tracks (track_id1,track_id2....etc), location (location_id)
    Route::post('/packages-for-delivery', 'Api\QueueController@packages_for_delivery'); // user (user_id), tracks (track_id1,track_id2....etc), location (location_id)
    Route::post('/pay-from-balance', 'Api\QueueController@pay_from_balance'); // tracks (track_id1,track_id2....etc), client (client_id)
});

//delivery
Route::group(['prefix' => 'delivery', 'middleware'=>'ApiDeliveryManager'], function () {
    Route::get('/get-packages', 'Api\DeliveryController@get_packages');
    Route::post('/set-delivered', 'Api\DeliveryController@set_delivered');
});

//cashier
Route::group(['prefix' => 'cashier', 'middleware'=>'ApiCashier'], function () {
    Route::get('/get-packages', 'Api\CashierController@get_packages');
    Route::post('/set-to-paid', 'Api\CashierController@set_to_paid');
});

//operator
Route::group(['prefix' => 'operator', 'middleware'=>'ApiOperator'], function () {
    Route::get('/get-client', 'Api\OperatorController@get_client');
    Route::get('/get-packages', 'Api\OperatorController@get_packages');
});

//options
Route::group(['prefix' => 'options'], function () {
    Route::get('/get-device-address', 'Api\OptionController@get_device_address');
});

//bon.az (cash back)
Route::group(['prefix' => 'cash-back', 'middleware'=>'BonAz'], function () {
    Route::post('/control', 'Api\CashBackController@control_special_order');
});

//YIGIM payment system
Route::group(['prefix' => 'yigim'], function () {
    Route::post('/login', 'Api\YigimController@login');
    Route::post('/client-details', 'Api\YigimController@get_client_details')->middleware('Yigim');
    Route::post('/pay', 'Api\YigimController@pay_balance')->middleware('Yigim');
    Route::post('/payment-control', 'Api\YigimController@pay_control')->middleware('Yigim');
});

//Colibri IT
Route::group(['prefix' => 'colibri-it'], function () {
    Route::post('/login', 'Api\PhonesController@login');
    Route::post('/get-client-details', 'Api\PhonesController@get_client_details')->middleware('ColibriIT');
    //Route::post('/get-clients', 'Api\PhonesController@get_clients')->middleware('ColibriIT');
});


Route::group(['prefix' => 'aser', 'middleware' => 'ApiAserCollector'], function () {
    Route::post('/add-collector', 'Api\AserController@add_collector');
    Route::get('/check-user', 'Api\AserController@check_client');
    Route::get('/check-package', 'Api\AserController@check_package');
    Route::get('/position', 'Api\AserController@position');
    Route::post('/position/add', 'Api\AserController@AddPosition');
    Route::put('/position/update/{id}', 'Api\AserController@UpdatePosition');
    Route::get('/categories', 'Api\AserController@categories');
    Route::get('/sellers', 'Api\AserController@seller');
    Route::get('/types', 'Api\AserController@types');
    Route::get('/statuses', 'Api\AserController@statuses');
    Route::get('/currencies', 'Api\AserController@currencies');
    Route::get('/generate-internal', 'Api\AserController@get_internal_id');
    Route::get('/package-statuses', 'Api\AserController@check_package_status');
    Route::post('/change-package-position', 'Api\AserController@change_package_position');

    Route::post('/package-add-container', 'Api\AserController@PackageAddContainer');
    Route::get('/search', 'Api\AserController@CollectorSearchPackage');
    Route::get('/waybill', 'Api\AserController@waybill');

    Route::group(['prefix' => '/flights'], function () {
        Route::get('/', 'Api\AserController@GetFlights')->name("show_flights_collector");
        Route::get('/select-flights', 'Api\AserController@SelectFlights');
        Route::get('/{id}', 'Api\AserController@GetSingleFlight');
        Route::post('/add', 'Api\AserController@CreateFlight')->name("add_flight_collector");
        Route::put('/update/{id}', 'Api\AserController@UpdateFlight')->name("update_flight_collector");
        Route::post('/close', 'Api\AserController@close')->name("close_flight_collector");
    });

    Route::group(['prefix' => '/containers'], function () {
        Route::get('/', 'Api\AserController@GetContainers');
        Route::post('/add', 'Api\AserController@createContainer');
    });

    Route::group(['prefix' => '/reports'], function () {
        Route::post('/{type}', 'Api\AserController@ReportAllPackage');
    });
    
    Route::group(['prefix' => '/otp'], function () {
        Route::get('/', 'Api\AserController@check_otp_code');
    });

});

//CourierPanel
Route::group(['prefix' => 'courier', 'middleware'=>'CourierPanel'], function () {
    Route::get('/get-orders', 'Api\CourierController@get_orders');
    Route::get('/get-packages', 'Api\CourierController@get_packages');
    Route::get('/get-area', 'Api\CourierController@GetArea');
    Route::get('/get-region', 'Api\CourierController@GetRegion');
    Route::get('/get-status', 'Api\CourierController@GetStatus');
    Route::get('/courier-user', 'Api\CourierController@get_courier_user');
    Route::put('/edit-status/{order}', 'Api\CourierController@update_status');
    Route::put('/set-courier/{order}', 'Api\CourierController@set_courier');
});

Route::group(['prefix' => 'azerpost', 'middleware'=>'Azerpost'], function () {
    Route::post('/webhook/status', 'Api\AzerpostController@update_status');
});

Route::put('/odeme', 'Api\CalcPartnerAmountController@calculate_amount_platforms');
