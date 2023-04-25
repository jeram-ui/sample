<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
  Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('Mod_IT')->group(function () {

      Route::prefix('equipment')->group(function () {
        Route::post('store', 'Mod_IT\equipmentController@store');
        Route::post('storeSpecification', 'Mod_IT\equipmentController@storeSpecification');
        Route::get('getEquipment', 'Mod_IT\equipmentController@getEquipment');
        Route::get('getEquiptList', 'Mod_IT\equipmentController@getEquiptList');
        Route::get('getEquipmentsList', 'Mod_IT\equipmentController@getEquipmentsList');
        Route::get('getSpecification/{id}', 'Mod_IT\equipmentController@getSpecification');
        Route::put('cancel/{id}', 'Mod_IT\equipmentController@cancel');
        Route::put('CancelEquipment/{id}', 'Mod_IT\equipmentController@CancelEquipment');
        Route::put('Edit/{id}', 'Mod_IT\equipmentController@Edit');
        Route::put('removing/{id}', 'Mod_IT\equipmentController@removing');
        Route::put('removingEquip/{id}', 'Mod_IT\equipmentController@removingEquip');
      });

      Route::prefix('repairForm')->group(function () {
        Route::get('show', 'Mod_IT\repairController@show');
        Route::get('GetEmpName', 'Mod_IT\repairController@GetEmpName');
        Route::get('getrecommend', 'Mod_IT\repairController@getrecommend');
        Route::post('store', 'Mod_IT\repairController@store');
        Route::post('inspectStore', 'Mod_IT\repairController@inspectStore');
        Route::post('billStore', 'Mod_IT\repairController@billStore');
        Route::get('getReqName', 'Mod_IT\repairController@getReqName');
        Route::get('getRepairSetup', 'Mod_IT\repairController@getRepairSetup');
        Route::put('cancel/{id}', 'Mod_IT\repairController@cancel');
        Route::put('Edit/{id}', 'Mod_IT\repairController@Edit');
        Route::post('print', 'Mod_IT\repairController@print');
        Route::get('getEmpRequest', 'Mod_IT\repairController@getEmpRequest');
        Route::get('reference', 'Mod_IT\repairController@reference');
      });

      Route::prefix('preventive')->group(function () {
        Route::get('GetOffice', 'Mod_IT\preventiveController@GetOffice');
        Route::get('GetAreOwner', 'Mod_IT\preventiveController@GetAreOwner');
        Route::get('GetUser', 'Mod_IT\preventiveController@GetUser');
        Route::get('GetInspected', 'Mod_IT\preventiveController@GetInspected');
        Route::get('GetVerified', 'Mod_IT\preventiveController@GetVerified');
        Route::get('GetEquipment', 'Mod_IT\preventiveController@GetEquipment');
        Route::get('GetSpecification/{id}', 'Mod_IT\preventiveController@GetSpecification');
        Route::post('store', 'Mod_IT\preventiveController@store');
        Route::get('GetList', 'Mod_IT\preventiveController@GetList');
        Route::put('Edit/{id}', 'Mod_IT\preventiveController@Edit');
        Route::put('cancel/{id}', 'Mod_IT\preventiveController@cancel');
        Route::post('ITC', 'Mod_IT\preventiveController@ITC');
        Route::post('p_Printer', 'Mod_IT\preventiveController@p_Printer');
        Route::get('getFunctions', 'Mod_IT\preventiveController@getFunctions');

      });
    });
  });
});
