<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::prefix('mod_mayors')->group(function () {
            Route::prefix('Memorandum')->group(function () {
                Route::get('ref', 'Mayors\memorandumController@ref');
                Route::post('store', 'Mayors\memorandumController@store');
                Route::get('show', 'Mayors\memorandumController@show');
                Route::get('edit/{id}', 'Mayors\memorandumController@edit');
                Route::get('cancel/{id}', 'Mayors\memorandumController@cancel');
                Route::post('upload', 'Mayors\memorandumController@upload');
                Route::get('uploaded/{id}', 'Mayors\memorandumController@uploaded');
                Route::get('documentView/{id}', 'Mayors\memorandumController@documentView');
                Route::get('uploadRemove/{id}', 'Mayors\memorandumController@uploadRemove');
                Route::get('doneMemo/{id}', 'Mayors\memorandumController@doneMemo');

            });
            Route::prefix('petty')->group(function () {
                Route::get('getDepartment', 'Mayors\pettycashController@getDepartment');
                // Route::get('GetEmpName/{id}', 'Mod_HR\Overtime\overtimeAppController@GetEmpName');
                Route::get('getEmpRequest/{id}', 'Mod_HR\Overtime\overtimeAppController@getEmpRequest');
                Route::get('getref', 'Mayors\pettycashController@getref');
                // Route::get('getEmpRequest', 'Mayors\pettycashController@getEmpRequest');
                Route::post('store', 'Mayors\pettycashController@store');
                Route::get('show', 'Mayors\pettycashController@show');
                Route::put('cancel/{id}', 'Mayors\pettycashController@cancel');
                Route::put('Edit/{id}', 'Mayors\pettycashController@Edit');
                Route::post('print', 'Mayors\pettycashController@print');
                Route::post('printPettyCash', 'Mayors\pettycashController@printPettyCash');

                Route::get('pettyCashHeadList', 'Mayors\pettycashController@pettyCashHeadList');
                Route::get('PettyDisbursingApproval', 'Mayors\pettycashController@PettyDisbursingApproval');
                Route::get('pettyCashallotmentApproval', 'Mayors\pettycashController@pettyCashallotmentApproval');
                Route::get('PettyCashHeadListApproved', 'Mayors\pettycashController@PettyCashHeadListApproved');
                Route::post('PettyCashHeadApprovalApproved', 'Mayors\pettycashController@PettyCashHeadApprovalApproved');
                Route::post('PettyCashHeadApprovalDisapproved', 'Mayors\pettycashController@PettyCashHeadApprovalDisapproved');
                Route::get('PettyCashAppropriationApproval', 'Mayors\pettycashController@PettyCashAppropriationApproval');
                Route::get('PettyCashAppropriationApprovedList', 'Mayors\pettycashController@PettyCashAppropriationApprovedList');
                Route::post('PettyCashDisbursingApproved', 'Mayors\pettycashController@PettyCashDisbursingApproved');
                Route::post('PettyCashAppropriationApproved', 'Mayors\pettycashController@PettyCashAppropriationApproved');
                Route::post('PettyCashAllotmentApproved', 'Mayors\pettycashController@PettyCashAllotmentApproved');
                Route::get('PettyCashDisbursingApprovedList', 'Mayors\pettycashController@PettyCashDisbursingApprovedList');
                Route::get('pettyCashallotmentApprovalList', 'Mayors\pettycashController@pettyCashallotmentApprovalList');
                Route::post('PettyCashDisbursingDisapproved', 'Mayors\pettycashController@PettyCashDisbursingDisapproved');
                Route::post('PettyCashAppropriationDisapproved', 'Mayors\pettycashController@PettyCashAppropriationDisapproved');
                Route::post('PettyCashAllotmentDisapproved', 'Mayors\pettycashController@PettyCashAllotmentDisapproved');
            });
            Route::prefix('referral')->group(function () {
                Route::post('store', 'Mayors\referralController@store');
                Route::get('show', 'Mayors\referralController@show');
                Route::put('cancel/{id}', 'Mayors\referralController@cancel');
                Route::put('Edit/{id}', 'Mayors\referralController@Edit');
                Route::post('print', 'Mayors\referralController@print');
            });
            Route::prefix('ExecutiveOrder')->group(function () {
                Route::get('ref', 'Mayors\executiveOrderController@ref');
                Route::post('store', 'Mayors\executiveOrderController@store');
                Route::get('show', 'Mayors\executiveOrderController@show');
                Route::get('edit/{id}', 'Mayors\executiveOrderController@edit');
                Route::get('cancel/{id}', 'Mayors\executiveOrderController@cancel');
                Route::post('upload', 'Mayors\executiveOrderController@upload');
                Route::get('uploaded/{id}', 'Mayors\executiveOrderController@uploaded');
                Route::get('documentView/{id}', 'Mayors\executiveOrderController@documentView');
                Route::get('uploadRemove/{id}', 'Mayors\executiveOrderController@uploadRemove');
                Route::get('doneMemo/{id}', 'Mayors\memorandumController@doneMemo');

            });
            Route::prefix('SeekMedical')->group(function () {
                Route::get('ref', 'Mayors\medicalController@ref');
                Route::post('store', 'Mayors\medicalController@store');
                Route::get('show', 'Mayors\medicalController@show');
                Route::get('showsummary', 'Mayors\medicalController@showsummary');
                Route::get('edit/{id}', 'Mayors\medicalController@edit');
                Route::get('cancel/{id}', 'Mayors\medicalController@cancel');
                Route::post('upload', 'Mayors\medicalController@upload');
                Route::get('uploaded/{id}', 'Mayors\medicalController@uploaded');
                Route::get('documentView/{id}', 'Mayors\medicalController@documentView');
                Route::get('uploadRemove/{id}', 'Mayors\medicalController@uploadRemove');
            });
            Route::prefix('Burial')->group(function () {
                Route::get('ref', 'Mayors\burialController@ref');
                Route::post('store', 'Mayors\burialController@store');
                Route::get('show', 'Mayors\burialController@show');
                Route::get('showsummary', 'Mayors\burialController@showsummary');
                Route::get('edit/{id}', 'Mayors\burialController@edit');
                Route::get('cancel/{id}', 'Mayors\burialController@cancel');
                Route::post('printform', 'Mayors\burialController@printform');
            });
            Route::prefix('VecoIndigencyCert')->group(function () {
                Route::get('ref', 'Mayors\vecoIndigency@ref');
                Route::post('store', 'Mayors\vecoIndigency@store');
                Route::get('show', 'Mayors\vecoIndigency@show');
                Route::get('showsummary', 'Mayors\vecoIndigency@showsummary');
                Route::get('edit/{id}', 'Mayors\vecoIndigency@edit');
                Route::get('cancel/{id}', 'Mayors\vecoIndigency@cancel');
                Route::get('printform/{id}', 'Mayors\vecoIndigency@printform');
            });


            Route::prefix('EscCertificate')->group(function () {
                Route::get('ref', 'Mayors\EscCertcontroller@ref');
                Route::post('store', 'Mayors\EscCertcontroller@store');
                Route::get('show', 'Mayors\EscCertcontroller@show');
                Route::get('showsummary', 'Mayors\EscCertcontroller@showsummary');
                Route::get('edit/{id}', 'Mayors\EscCertcontroller@edit');
                Route::get('cancel/{id}', 'Mayors\EscCertcontroller@cancel');
                Route::get('printform/{id}', 'Mayors\EscCertcontroller@printform');
            });
            Route::prefix('Burialvigil')->group(function () {
                Route::get('getCemetery', 'Mayors\burialvigilController@getCemetery');
                Route::get('ref', 'Mayors\burialvigilController@ref');
                Route::post('store', 'Mayors\burialvigilController@store');
                Route::get('show', 'Mayors\burialvigilController@show');
                Route::get('showsummary', 'Mayors\burialvigilController@showsummary');
                Route::get('edit/{id}', 'Mayors\burialvigilController@edit');
                Route::get('cancel/{id}', 'Mayors\burialvigilController@cancel');
                // Route::post('printform', 'Mayors\burialvigilController@printform');
                Route::get('printform/{id}', 'Mayors\burialvigilController@printform');
            });
            Route::prefix('Assistance')->group(function () {
                Route::get('getRef', 'Mod_Mayors\AssistanceController@ref');
                Route::post('store', 'Mod_Mayors\AssistanceController@store');
                Route::get('show', 'Mod_Mayors\AssistanceController@show');
                Route::get('showsummary', 'Mod_Mayors\AssistanceController@showsummary');
                Route::get('edit/{id}', 'Mod_Mayors\AssistanceController@edit');
                Route::get('cancel/{id}', 'Mod_Mayors\AssistanceController@cancel');
                Route::post('survey1', 'Mod_Mayors\AssistanceController@survey1');
                Route::get('getRating/{id}', 'Mod_Mayors\AssistanceController@getRating');
                Route::get('SurveyList', 'Mod_Mayors\AssistanceController@SurveyList');
                Route::post('printList', 'Mod_Mayors\AssistanceController@printList');
                Route::post('getGenderCount', 'Mod_Mayors\AssistanceController@getGenderCount');
                Route::get('getAssistedName', 'Mod_Mayors\AssistanceController@getAssistedName');
                Route::get('purpose', 'Mod_Mayors\AssistanceController@purpose');
            });

            Route::prefix('SPResolution')->group(function () {

                Route::get('getRef', 'Mod_SP\spResolutionController@getRef');
                Route::get('getSector', 'Mod_SP\spResolutionController@getSector');
                Route::post('store', 'Mod_SP\spResolutionController@store');
                Route::get('show', 'Mod_SP\spResolutionController@show');
                Route::get('edit/{id}', 'Mod_SP\spResolutionController@edit');
                Route::get('cancel/{id}', 'Mod_SP\spResolutionController@cancel');
                Route::get('getChairman', 'Mod_SP\spResolutionController@getChairman');

                Route::get('getUpdate/{id}', 'Mod_SP\spResolutionController@getUpdate');
                Route::post('postUpdate', 'Mod_SP\spResolutionController@postUpdate');
                Route::get('deleteUpdate/{id}', 'Mod_SP\spResolutionController@deleteUpdate');


                Route::post('storeSector', 'Mod_SP\spResolutionController@storeSector');
            });

            Route::prefix('accomplishment')->group(function () {

                Route::get('getRef', 'Mod_Mayors\AccomplishmentController@getRef');
                Route::get('getSector', 'Mod_Mayors\AccomplishmentController@getSector');
                Route::post('store', 'Mod_Mayors\AccomplishmentController@store');
                Route::get('show', 'Mod_Mayors\AccomplishmentController@show');
                Route::get('edit/{id}', 'Mod_Mayors\AccomplishmentController@edit');
                Route::get('cancel/{id}', 'Mod_Mayors\AccomplishmentController@cancel');
                Route::post('print', 'Mod_Mayors\AccomplishmentController@print');
                Route::get('getAccom', 'Mod_Mayors\AccomplishmentController@getAccom');
            });

            Route::prefix('problemSolving')->group(function () {
                Route::get('getRef', 'Mod_Mayors\problemSolvingController@getRef');
                Route::get('getSector', 'Mod_Mayors\problemSolvingController@getSector');
                Route::post('store', 'Mod_Mayors\problemSolvingController@store');
                Route::get('show', 'Mod_Mayors\problemSolvingController@show');
                Route::get('edit/{id}', 'Mod_Mayors\problemSolvingController@edit');
                Route::get('cancel/{id}', 'Mod_Mayors\problemSolvingController@cancel');
                Route::post('printform', 'Mod_Mayors\problemSolvingController@printform');
                Route::get('getAccom', 'Mod_Mayors\problemSolvingController@getAccom');
            });
            Route::prefix('hospitaldischarge')->group(function () {
                //Route::get('getCemetery', 'Mayors\rofcontroller@getCemetery');
                Route::get('ref', 'Mayors\hospitaldischarge@ref');
                Route::post('store', 'Mayors\hospitaldischarge@store');
                Route::get('show', 'Mayors\hospitaldischarge@show');
                Route::get('showsummary', 'Mayors\hospitaldischarge@showsummary');
                Route::get('edit/{id}', 'Mayors\hospitaldischarge@edit');
                Route::get('cancel/{id}', 'Mayors\hospitaldischarge@cancel');
                Route::get('printform/{id}', 'Mayors\hospitaldischarge@printform');
            });
            Route::prefix('otherscontroller')->group(function () {
                Route::get('ref', 'Mayors\otherscontroller@ref');
                Route::post('store', 'Mayors\otherscontroller@store');
                Route::get('show', 'Mayors\otherscontroller@show');
                Route::get('showsummary', 'Mayors\otherscontroller@showsummary');
                Route::get('edit/{id}', 'Mayors\otherscontroller@edit');
                Route::get('cancel/{id}', 'Mayors\otherscontroller@cancel');
                Route::get('printform/{id}', 'Mayors\otherscontroller@printform');
            });
            Route::prefix('returningresidence')->group(function () {
                //Route::get('getCemetery', 'Mayors\rofcontroller@getCemetery');
                Route::get('ref', 'Mayors\returningresidence@ref');
                Route::post('store', 'Mayors\returningresidence@store');
                Route::get('show', 'Mayors\returningresidence@show');
                Route::get('showsummary', 'Mayors\returningresidence@showsummary');
                Route::get('edit/{id}', 'Mayors\returningresidence@edit');
                Route::get('cancel/{id}', 'Mayors\returningresidence@cancel');
                Route::get('printform/{id}', 'Mayors\returningresidence@printform');
            });
            Route::prefix('rof')->group(function () {
                //Route::get('getCemetery', 'Mayors\rofcontroller@getCemetery');
                Route::get('ref', 'Mayors\rofcontroller@ref');
                Route::post('store', 'Mayors\rofcontroller@store');
                Route::get('show', 'Mayors\rofcontroller@show');
                Route::get('showsummary', 'Mayors\rofcontroller@showsummary');
                Route::get('edit/{id}', 'Mayors\rofcontroller@edit');
                Route::get('cancel/{id}', 'Mayors\rofcontroller@cancel');
                Route::get('printform/{id}', 'Mayors\rofcontroller@printform');
            });
        });
    });
});
