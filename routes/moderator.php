<?php

Route::group(['prefix' => '/moderator', 'middleware' => 'Admin'], function () {
    Route::get('/{any}', 'ModeratorController@index')->where('any', '.*');
    Route::get('/', 'ModeratorController@index')->name('moderator_page');
});
Route::group(['prefix' => '/moderatorAPI', 'middleware' => 'Admin'], function () {
    Route::get('/showFAQ', 'ModeratorController@showFAQ')->name('moderator_showFAQ');
    Route::post('/updateFAQ', 'ModeratorController@updateFAQ')->name('moderator_updateFAQ');
    Route::post('/createFAQ', 'ModeratorController@createFAQ')->name('moderator_createFAQ');
    Route::delete('/deleteFAQ/{faq}', 'ModeratorController@deleteFAQ')->name('moderator_deleteFAQ');


    Route::get('/showProhibitedItem', 'ModeratorController@showProhibitedItem')->name('moderator_showProhibitedItem');
    Route::post('/updateProhibitedItem', 'ModeratorController@updateProhibitedItem')->name('moderator_updateProhibitedItem');
    Route::post('/createProhibitedItem', 'ModeratorController@createProhibitedItem')->name('moderator_createProhibitedItem');
    Route::delete('/deleteProhibitedItem/{item}', 'ModeratorController@deleteProhibitedItem')->name('moderator_deleteProhibitedItem');


    Route::get('/showStore', 'ModeratorController@showStore')->name('moderator_showStore');
    Route::post('/updateStore', 'ModeratorController@updateStore')->name('moderator_updateStore');
    Route::post('/createStore', 'ModeratorController@createStore')->name('moderator_createStore');
    Route::delete('/deleteStore/{item}', 'ModeratorController@deleteStore')->name('moderator_deleteStore');
    Route::post('/changeCheck/{item}', 'ModeratorController@changeCheck')->name('moderator_changeCheck');

    Route::get('/getCategories', 'ModeratorController@getCategories')->name('moderator_getCategories');
    Route::get('/getCountries', 'ModeratorController@getCountries')->name('moderator_getCountries');

    Route::get('/showStoreCategory', 'ModeratorController@showStoreCategory')->name('moderator_showStoreCategory');
    Route::post('/updateStoreCategory', 'ModeratorController@updateStoreCategory')->name('moderator_updateStoreCategory');
    Route::post('/createStoreCategory', 'ModeratorController@createStoreCategory')->name('moderator_createStoreCategory');
    Route::delete('/deleteStoreCategory/{item}', 'ModeratorController@deleteStoreCategory')->name('moderator_deleteStoreCategory');

    Route::get('/showCountryDetails', 'ModeratorController@showCountryDetails')->name('moderator_showCountryDetails');
    Route::get('/selectCountryDetails/{id}', 'ModeratorController@selectCountryDetails')->name('moderator_selectCountryDetails');
    Route::post('/updateCountryDetails', 'ModeratorController@updateCountryDetails')->name('moderator_updateCountryDetails');
    Route::post('/createCountryDetails', 'ModeratorController@createCountryDetails')->name('moderator_createCountryDetails');
    Route::delete('/deleteCountryDetails/{item}', 'ModeratorController@deleteCountryDetails')->name('moderator_deleteCountryDetails');

    Route::get('/showMailSMSTemplate', 'ModeratorController@showMailSMSTemplate')->name('moderator_showMailSMSTemplate');
    Route::post('/updateMailSMSTemplate', 'ModeratorController@updateMailSMSTemplate')->name('moderator_updateMailSMSTemplate');
    Route::post('/createMailSMSTemplate', 'ModeratorController@createMailSMSTemplate')->name('moderator_createMailSMSTemplate');

    Route::get('/showCategory', 'ModeratorController@showCategory')->name('moderator_showCategory');
    Route::post('/updateCategory', 'ModeratorController@updateCategory')->name('moderator_updateCategory');
    Route::post('/createCategory', 'ModeratorController@createCategory')->name('moderator_createCategory');
    Route::post('/mergeCategory', 'ModeratorController@mergeCategory')->name('moderator_mergeCategory');
    Route::delete('/deleteCategory/{item}', 'ModeratorController@deleteCategory')->name('moderator_deleteCategory');

    Route::post('/updateLanguageMenu', 'ModeratorController@updateLanguageMenu')->name('moderator_updateLanguageMenu');
    Route::get('/showLanguageSidebar', 'ModeratorController@showLanguageSidebar')->name('moderator_showLanguageSidebar');
    Route::get('/showLanguageTemplate', 'ModeratorController@showLanguageTemplate')->name('moderator_showLanguageTemplate');

    Route::get('/showStatus', 'ModeratorController@showStatus')->name('moderator_showStatus');
    Route::post('/updateStatus', 'ModeratorController@updateStatus')->name('moderator_updateStatus');
});
