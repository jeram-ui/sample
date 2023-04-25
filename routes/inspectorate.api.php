<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::prefix('mod_Ins')->group(function () {
            
            Route::prefix('centro')->group(function () {
                Route::get('show', 'Mod_Inspectorate\cenroControler@show');
                Route::get('getReq', 'Mod_Inspectorate\cenroControler@getReq');
                Route::get('getRef', 'Mod_Inspectorate\cenroControler@ref');
                Route::post('store', 'Mod_Inspectorate\cenroControler@store');
                Route::get('update/{id}', 'Mod_Inspectorate\cenroControler@update');
                Route::get('cancel/{id}', 'Mod_Inspectorate\cenroControler@cancel');
                Route::post('upload', 'Mod_Inspectorate\cenroControler@upload');
                Route::get('uploaded/{id}', 'Mod_Inspectorate\cenroControler@uploaded');
                Route::get('documentView/{id}', 'Mod_Inspectorate\cenroControler@documentView');
                Route::get('uploadRemove/{id}', 'Mod_Inspectorate\cenroControler@uploadRemove');
            });
            Route::prefix('obo')->group(function () {
                Route::get('getBuilding', 'Mod_Inspectorate\oboControler@getBuilding');
                Route::get('show', 'Mod_Inspectorate\oboControler@show');
                Route::get('getReq', 'Mod_Inspectorate\oboControler@getReq');
                Route::get('getRef', 'Mod_Inspectorate\oboControler@ref');
                Route::post('store', 'Mod_Inspectorate\oboControler@store');
                Route::get('update/{id}', 'Mod_Inspectorate\oboControler@update');
                Route::get('cancel/{id}', 'Mod_Inspectorate\oboControler@cancel');
                Route::post('upload', 'Mod_Inspectorate\oboControler@upload');
                Route::get('uploaded/{id}', 'Mod_Inspectorate\oboControler@uploaded');
                Route::get('documentView/{id}', 'Mod_Inspectorate\oboControler@documentView');
                Route::get('uploadRemove/{id}', 'Mod_Inspectorate\oboControler@uploadRemove');
            });
            Route::prefix('health')->group(function () {
                Route::get('show', 'Mod_Inspectorate\HealthControler@show');
                Route::get('getReq', 'Mod_Inspectorate\HealthControler@getReq');
                Route::get('getRef', 'Mod_Inspectorate\HealthControler@ref');
                Route::post('store', 'Mod_Inspectorate\HealthControler@store');
                Route::get('update/{id}', 'Mod_Inspectorate\HealthControler@update');
                Route::get('cancel/{id}', 'Mod_Inspectorate\HealthControler@cancel');
                Route::post('upload', 'Mod_Inspectorate\HealthControler@upload');
                Route::get('uploaded/{id}', 'Mod_Inspectorate\HealthControler@uploaded');
                Route::get('documentView/{id}', 'Mod_Inspectorate\HealthControler@documentView');
                Route::get('uploadRemove/{id}', 'Mod_Inspectorate\HealthControler@uploadRemove');
            });
            Route::prefix('treasurer')->group(function () {
                Route::get('show', 'Mod_Inspectorate\treasurerControler@show');
                Route::get('getReq', 'Mod_Inspectorate\treasurerControler@getReq');
                Route::get('getRef', 'Mod_Inspectorate\treasurerControler@ref');
                Route::post('store', 'Mod_Inspectorate\treasurerControler@store');
                Route::get('update/{id}', 'Mod_Inspectorate\treasurerControler@update');
                Route::get('cancel/{id}', 'Mod_Inspectorate\treasurerControler@cancel');
                Route::post('upload', 'Mod_Inspectorate\treasurerControler@upload');
                Route::get('uploaded/{id}', 'Mod_Inspectorate\treasurerControler@uploaded');
                Route::get('documentView/{id}', 'Mod_Inspectorate\treasurerControler@documentView');
                Route::get('uploadRemove/{id}', 'Mod_Inspectorate\treasurerControler@uploadRemove');
            });
            Route::prefix('assessors')->group(function () {
                Route::get('show', 'Mod_Inspectorate\assessorsControler@show');
                Route::get('getReq', 'Mod_Inspectorate\assessorsControler@getReq');
                Route::get('getRef', 'Mod_Inspectorate\assessorsControler@ref');
                Route::post('store', 'Mod_Inspectorate\assessorsControler@store');
                Route::get('update/{id}', 'Mod_Inspectorate\assessorsControler@update');
                Route::get('cancel/{id}', 'Mod_Inspectorate\assessorsControler@cancel');
                Route::post('upload', 'Mod_Inspectorate\assessorsControler@upload');
                Route::get('uploaded/{id}', 'Mod_Inspectorate\assessorsControler@uploaded');
                Route::get('documentView/{id}', 'Mod_Inspectorate\assessorsControler@documentView');
                Route::get('uploadRemove/{id}', 'Mod_Inspectorate\assessorsControler@uploadRemove');
            });
            Route::prefix('bplo')->group(function () {
                Route::get('show', 'Mod_Inspectorate\bploControler@show');
                Route::get('getReq', 'Mod_Inspectorate\bploControler@getReq');
                Route::get('getRef', 'Mod_Inspectorate\bploControler@ref');
                Route::post('store', 'Mod_Inspectorate\bploControler@store');
                Route::get('update/{id}', 'Mod_Inspectorate\bploControler@update');
                Route::get('cancel/{id}', 'Mod_Inspectorate\bploControler@cancel');
                Route::post('upload', 'Mod_Inspectorate\bploControler@upload');
                Route::get('uploaded/{id}', 'Mod_Inspectorate\bploControler@uploaded');
                Route::get('documentView/{id}', 'Mod_Inspectorate\bploControler@documentView');
                Route::get('uploadRemove/{id}', 'Mod_Inspectorate\bploControler@uploadRemove');
            });
        });
    });
});
