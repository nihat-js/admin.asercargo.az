<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/sahib-test-secret', 'ApiController@test');

Route::group(['prefix' => '/', 'middleware' => 'Login'], function () {
    Route::get('/', 'HomeController@index')->name("home");
    Route::get('/home', 'HomeController@index');
    Route::get('/index', 'HomeController@index');
    Route::get('/access-denied', 'HomeController@access_denied')->name("access_denied");

    Route::group(['prefix' => '/sahib'], function () {
        Route::get('/add-positions', 'HomeController@add_positions')->name("add_positions");
        Route::get('/calculate-amounts', 'HomeController@calculate_amounts')->name("calculate_amounts");
        Route::get('/create-item-for-packages', 'HomeController@create_item_for_package')->name("create_item_for_package");
    });

    include('admin.php');

    include('moderator.php');

    include('collector.php');

    include('cashier.php');

    include('warehouse.php');

    include('operator.php');

    include('courier.php');

    include('manager.php');


    Route::post('/call-next-queue', 'QueueController@call_next_client')->name("call_next_client");
    Route::get('/waybill', 'WaybillController@waybill_page')->name("waybill_page");
    Route::get('/waybill/{tack}', 'WaybillController@waybill')->name("waybill");
});

Auth::routes(['register' => false, 'reset' => false]);
Route::get('/logout', 'Auth\LoginController@logout')->name("logout");
