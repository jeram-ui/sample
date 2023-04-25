<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
  Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('Mod_Performance')->group(function () {

      Route::prefix('performance')->group(function () {
        Route::post('store', 'Mod_Performance\Eval_period\evalPeriodController@store');
        Route::get('getEvaluation', 'Mod_Performance\Eval_period\evalPeriodController@getEvaluation');
        Route::put('removing/{id}', 'Mod_Performance\Eval_period\evalPeriodController@removing');
      });

      Route::prefix('MFO')->group(function () {
        Route::post('functiongroupstore', 'Mod_Performance\MFOPAP\MFOController@functiongroupstore');
        Route::post('store', 'Mod_Performance\MFOPAP\MFOController@store');
        Route::put('Edit/{id}', 'Mod_Performance\MFOPAP\MFOController@Edit');
        Route::get('getlist/{id}', 'Mod_Performance\MFOPAP\MFOController@getlist');
        Route::get('getEvaluation/{id}/{type}', 'Mod_Performance\MFOPAP\MFOController@getEvaluation');
        Route::get('getDepartment', 'Mod_Performance\MFOPAP\MFOController@getDepartment');
        Route::put('removing/{id}', 'Mod_Performance\MFOPAP\MFOController@removing');
        Route::get('showByDept/{id}', 'Mod_Performance\MFOPAP\MFOController@showByDept');
        Route::put('cancel/{id}', 'Mod_Performance\MFOPAP\MFOController@cancel');

      });

      Route::prefix('OPCR')->group(function () {
        Route::post('store', 'Mod_Performance\OPCR\opcrController@store');
        Route::get('getDepartment', 'Mod_Performance\OPCR\opcrController@getDepartment');
        Route::get('getMFO/{id}', 'Mod_Performance\OPCR\opcrController@getMFO');
        // Route::get('getMFO', 'Mod_Performance\OPCR\opcrController@getMFO');
        // Route::get('getFgroup/{id}', 'Mod_Performance\OPCR\opcrController@getFgroup');
        Route::get('getFgroup', 'Mod_Performance\OPCR\opcrController@getFgroup');
        Route::get('getRatingsQ', 'Mod_Performance\OPCR\opcrController@getRatingsQ');
        Route::get('getRatingsE', 'Mod_Performance\OPCR\opcrController@getRatingsE');
        Route::get('getRatingMatrix/{id}', 'Mod_Performance\OPCR\opcrController@getRatingMatrix');
        Route::get('showRating/{id}', 'Mod_Performance\OPCR\opcrController@showRating');
        Route::post('OverStore', 'Mod_HR\Overtime\overtimeAppController@OverStore');
        Route::get('getOvertime', 'Mod_HR\Overtime\overtimeAppController@getOvertime');
        Route::get('getCert', 'Mod_HR\Overtime\overtimeAppController@getCert');
        Route::put('cancel/{id}', 'Mod_Performance\OPCR\opcrController@cancel');
        Route::put('Edit/{id}', 'Mod_Performance\OPCR\opcrController@Edit');
        Route::post('print',  'Mod_Performance\OPCR\opcrController@print');
      });
      Route::prefix('empMFO')->group(function () {
        Route::get('GetDept', 'Mod_Performance\empMFO\empMFOController@GetDept');
        Route::get('GetName/{id}', 'Mod_Performance\empMFO\empMFOController@GetName');
        Route::get('showMFO/{id}', 'Mod_Performance\empMFO\empMFOController@showMFO');
        Route::post('store', 'Mod_Performance\empMFO\empMFOController@store');
        Route::post('storeCopyDat', 'Mod_Performance\empMFO\empMFOController@storeCopyDat');
        Route::get('getEmpList', 'Mod_Performance\empMFO\empMFOController@getEmpList');
        Route::put('cancel/{id}', 'Mod_Performance\empMFO\empMFOController@cancel');
        Route::get('GetPrepared', 'Mod_Performance\empMFO\empMFOController@GetPrepared');
        Route::get('GetApproved', 'Mod_Performance\empMFO\empMFOController@GetApproved');
        Route::put('Edit/{id}', 'Mod_Performance\empMFO\empMFOController@Edit');
        Route::put('EditCopy/{id}', 'Mod_Performance\empMFO\empMFOController@EditCopy');
        Route::get('getAuthName', 'Mod_Performance\empMFO\empMFOController@getAuthName');
        Route::get('getListing/{id}', 'Mod_Performance\empMFO\empMFOController@getListing');
        Route::post('print',  'Mod_Performance\empMFO\empMFOController@print');

      });
      Route::prefix('ipcrRating')->group(function () {
        Route::get('GetPeriod', 'Mod_Performance\IPCR_Rating\ipcrRatingController@GetPeriod');
        Route::get('GetPeriodTarget', 'Mod_Performance\IPCR_Rating\ipcrRatingController@GetPeriodTarget');
        Route::get('GetAssessed', 'Mod_Performance\IPCR_Rating\ipcrRatingController@GetAssessed');
        Route::get('GetName/{id}', 'Mod_Performance\IPCR_Rating\ipcrRatingController@GetName');
        Route::get('GetDept', 'Mod_Performance\IPCR_Rating\ipcrRatingController@GetDept');
        Route::get('basicinfo', 'Mod_Performance\IPCR_Rating\ipcrRatingController@basicinfo');
        Route::get('GetFinal', 'Mod_Performance\IPCR_Rating\ipcrRatingController@GetFinal');
        Route::get('showMFO_byEmp', 'Mod_Performance\IPCR_Rating\ipcrRatingController@showMFO_byEmp');
        Route::post('store', 'Mod_Performance\IPCR_Rating\ipcrRatingController@store');
        Route::get('getEmpList', 'Mod_Performance\IPCR_Rating\ipcrRatingController@getEmpList');
        Route::put('cancel/{id}', 'Mod_Performance\IPCR_Rating\ipcrRatingController@cancel');
        Route::post('print', 'Mod_Performance\IPCR_Rating\ipcrRatingController@print');
        Route::get('GetReviewed', 'Mod_Performance\IPCR_Rating\ipcrRatingController@GetReviewed');
        Route::put('Edit/{id}', 'Mod_Performance\IPCR_Rating\ipcrRatingController@Edit');
        Route::get('getRatingList/{id}', 'Mod_Performance\IPCR_Rating\ipcrRatingController@getRatingList');

      });
      Route::prefix('opcrRating')->group(function () {
        Route::get('GetPeriod/{id}', 'Mod_Performance\OPCR_Rating\OPCRrateController@GetPeriod');
        Route::get('GetDept', 'Mod_Performance\OPCR_Rating\OPCRrateController@GetDept');
        Route::get('getAccomplished', 'Mod_Performance\OPCR_Rating\OPCRrateController@getAccomplished');
        Route::get('GetName/{id}', 'Mod_Performance\OPCR_Rating\OPCRrateController@GetName');
        Route::get('showMFO', 'Mod_Performance\OPCR_Rating\OPCRrateController@showMFO');
        Route::get('showMFO_byEmp', 'Mod_Performance\OPCR_Rating\OPCRrateController@showMFO_byEmp');
        Route::post('store', 'Mod_Performance\OPCR_Rating\OPCRrateController@store');
        Route::get('getEmpList/{id}', 'Mod_Performance\OPCR_Rating\OPCRrateController@getEmpList');
        Route::put('cancel/{id}', 'Mod_Performance\OPCR_Rating\OPCRrateController@cancel');
        Route::post('print',  'Mod_Performance\OPCR_Rating\OPCRrateController@print');
        Route::put('Edit/{id}', 'Mod_Performance\OPCR_Rating\OPCRrateController@Edit');

      });
      Route::prefix('OPCRTarget')->group(function () {
        // Route::get('GetPeriod', 'Mod_Performance\OPCR_Rating\OPCRTargetController@GetPeriod');
        // Route::get('GetDept', 'Mod_Performance\OPCR_Rating\OPCRTargetController@GetDept');
        // Route::get('getAccomplished', 'Mod_Performance\OPCR_Rating\OPCRTargetController@getAccomplished');
        // Route::get('GetName/{id}', 'Mod_Performance\OPCR_Rating\OPCRTargetController@GetName');
        // Route::get('showMFO', 'Mod_Performance\OPCR_Rating\OPCRTargetController@showMFO');
        Route::get('showMFObyDept/{id}', 'Mod_Performance\OPCR_Rating\OPCRTargetController@showMFObyDept');
        Route::post('store', 'Mod_Performance\OPCR_Rating\OPCRTargetController@store');
        Route::post('storeCopyDat', 'Mod_Performance\OPCR_Rating\OPCRTargetController@storeCopyDat');
        Route::get('getList', 'Mod_Performance\OPCR_Rating\OPCRTargetController@getList');
        Route::put('cancel/{id}', 'Mod_Performance\OPCR_Rating\OPCRTargetController@cancel');
        Route::post('print',  'Mod_Performance\OPCR_Rating\OPCRTargetController@print');
        Route::put('Edit/{id}', 'Mod_Performance\OPCR_Rating\OPCRTargetController@Edit');
        Route::put('EditCopy/{id}', 'Mod_Performance\OPCR_Rating\OPCRTargetController@EditCopy');

      });
    });
  });
});
