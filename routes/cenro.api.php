<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::prefix('mod_cenro')->group(function () {
            Route::prefix('instrument')->group(function () {
                Route::get('getInstrument', 'Mod_Cenro\cenrocontroller@getInstrument');          
                Route::post('store', 'Mod_Cenro\cenrocontroller@store');
                Route::get('getInstrument_id','Mod_Cenro\cenrocontroller@getInstrument_id');
                Route::get('show','Mod_Cenro\cenrocontroller@show');
                Route::post('edit','Mod_Cenro\cenrocontroller@edit');
                Route::post('cancel','Mod_Cenro\cenrocontroller@cancel');
                Route::get('getInstrument_id_name/{id}','Mod_Cenro\cenrocontroller@getInstrument_id');
                Route::post('getRTSbyID/{id}','Mod_Cenro\cenrocontroller@getRTSbyID');
                Route::get('showMonitoring','Mod_Cenro\cenrocontroller@showMonitoring');
            });
        });
    });
});
