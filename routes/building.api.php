<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

Route::namespace('Api')->group(function () {

    Route::group(['middleware' => 'auth:api'], function () {
        //Treasury Controller
        Route::prefix('Building')->group(function () {    
            Route::prefix('Zoning')->group(function () {
                Route::get('/getLocationalClearance', 'Treasury\BusinessZoning@getLocationalClearance'); 
            });  
        }); 
    });
});
