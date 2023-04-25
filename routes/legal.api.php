<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        //Treasury Controller
        Route::prefix('mod_legal')->group(function () {
            Route::prefix('Court')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\courtController@edit');
                Route::get('show', 'Mod_legal\courtController@show');
                Route::get('showCourtTypeList', 'Mod_legal\courtController@showCourtTypeList');
                Route::post('store', 'Mod_legal\courtController@store');
                Route::post('storeCourtType', 'Mod_legal\courtController@storeCourtType');
                Route::post('storeCourtCity', 'Mod_legal\courtController@storeCourtCity');

                Route::post('storeCompany', 'Mod_legal\courtController@storeCompany');
                Route::get('cancel/{id}', 'Mod_legal\courtController@cancel');
                Route::get('print', 'Mod_legal\courtController@print');
                Route::get('getType', 'Mod_legal\courtController@getType');
                Route::get('getCity', 'Mod_legal\courtController@getCity');
                Route::get('cancelCityType/{id}', 'Mod_legal\courtController@cancelCityType');
                Route::get('cancelCourtType/{id}', 'Mod_legal\courtController@cancelCourtType');
            });
            Route::prefix('client')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\clientController@edit');
                Route::get('show', 'Mod_legal\clientController@show');
                Route::get('showOpponent', 'Mod_legal\clientController@showOpponent');
                Route::post('store', 'Mod_legal\clientController@store');
                Route::post('storeCompany', 'Mod_legal\clientController@storeCompany');
                Route::get('cancel/{id}', 'Mod_legal\clientController@cancel');
                Route::get('print', 'Mod_legal\clientController@print');
            });
            Route::prefix('opponent')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\opponentController@edit');
                Route::get('show', 'Mod_legal\opponentController@show');
                Route::get('showOpponent', 'Mod_legal\opponentController@showOpponent');
                Route::post('store', 'Mod_legal\opponentController@store');
                Route::post('storeCompany', 'Mod_legal\opponentController@storeCompany');
                Route::get('cancel/{id}', 'Mod_legal\opponentController@cancel');
                Route::get('print', 'Mod_legal\opponentController@print');
            });
            Route::prefix('lawyer')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\lawyerController@edit');
                Route::get('show', 'Mod_legal\lawyerController@show');
                Route::post('store', 'Mod_legal\lawyerController@store');
                Route::get('cancel/{id}', 'Mod_legal\lawyerController@cancel');
                Route::get('print', 'Mod_legal\lawyerController@print');
            });
            Route::prefix('judge')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\judgeController@edit');
                Route::get('show', 'Mod_legal\judgeController@show');
                Route::post('store', 'Mod_legal\judgeController@store');
                Route::get('cancel/{id}', 'Mod_legal\judgeController@cancel');
                Route::get('print', 'Mod_legal\judgeController@print');
            });
            Route::prefix('law')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\lawController@edit');
                Route::get('show', 'Mod_legal\lawController@show');
                Route::post('store', 'Mod_legal\lawController@store');
                Route::post('storeCaseType', 'Mod_legal\lawController@storeCaseType');
                Route::get('cancel/{id}', 'Mod_legal\lawController@cancel');
                Route::get('print', 'Mod_legal\lawController@print');
                Route::get('getType', 'Mod_legal\lawController@getType');
            });
            Route::prefix('Call')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\callController@edit');
                Route::get('show', 'Mod_legal\callController@show');
                Route::post('store', 'Mod_legal\callController@store');
                Route::get('cancel/{id}', 'Mod_legal\callController@cancel');
                Route::get('print', 'Mod_legal\callController@print');
            });
            Route::prefix('Consultation')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\consultationController@edit');
                Route::get('show', 'Mod_legal\consultationController@show');
                Route::get('getRef', 'Mod_legal\consultationController@getRef');
                Route::post('store', 'Mod_legal\consultationController@store');
                Route::get('cancel/{id}', 'Mod_legal\consultationController@cancel');
                Route::get('printform/{id}', 'Mod_legal\consultationController@printform');
                Route::post('printlist', 'Mod_legal\consultationController@printlist');
                Route::get('printlist', 'Mod_legal\consultationController@printlist');
                Route::get('printlist', 'Mod_legal\consultationController@printlist');
                Route::post('upload', 'Mod_legal\consultationController@upload');
                Route::get('uploaded/{id}', 'Mod_legal\consultationController@uploaded');
                Route::get('documentView/{id}', 'Mod_legal\consultationController@documentView');
                Route::get('uploadRemove/{id}', 'Mod_legal\consultationController@uploadRemove');
                Route::get('updateCase/{id}', 'Mod_legal\consultationController@updateCase');
                Route::get('showForCase', 'Mod_legal\consultationController@showForCase');
            });
            Route::prefix('Investigation')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\InvestigationController@edit');
                Route::get('show', 'Mod_legal\InvestigationController@show');
                Route::get('getRef', 'Mod_legal\InvestigationController@getRef');
                Route::post('store', 'Mod_legal\InvestigationController@store');
                Route::get('cancel/{id}', 'Mod_legal\InvestigationController@cancel');
                Route::post('upload', 'Mod_legal\InvestigationController@upload');
                Route::get('uploaded/{id}', 'Mod_legal\InvestigationController@uploaded');
                Route::get('documentView/{id}', 'Mod_legal\InvestigationController@documentView');
                Route::get('uploadRemove/{id}', 'Mod_legal\InvestigationController@uploadRemove');
            });
            Route::prefix('Enforcement')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\lawEnforcementController@edit');
                Route::get('showOffender', 'Mod_legal\lawEnforcementController@showOffender');
                Route::post('notes', 'Mod_legal\lawEnforcementController@notes');
                Route::get('getRef', 'Mod_legal\lawEnforcementController@getRef');
                Route::post('store', 'Mod_legal\lawEnforcementController@store');
                Route::get('cancel/{id}', 'Mod_legal\lawEnforcementController@cancel');
                Route::post('upload', 'Mod_legal\lawEnforcementController@upload');
                Route::get('uploaded/{id}', 'Mod_legal\lawEnforcementController@uploaded');
                Route::get('documentView/{id}', 'Mod_legal\lawEnforcementController@documentView');
                Route::get('uploadRemove/{id}', 'Mod_legal\lawEnforcementController@uploadRemove');
                Route::get('showlist', 'Mod_legal\lawEnforcementController@showlist');
                Route::get('getUpdate/{id}', 'Mod_legal\lawEnforcementController@getUpdate');
                Route::post('postUpdate', 'Mod_legal\lawEnforcementController@postUpdate');
                Route::get('deleteUpdate/{id}', 'Mod_legal\lawEnforcementController@deleteUpdate');
                // Route::get('storeUpload', 'Mod_legal\lawEnforcementController@storeUpload');
            });

            Route::prefix('Opinion')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\legalOpinionController@edit');
                Route::get('show', 'Mod_legal\legalOpinionController@show');
                Route::get('getRef', 'Mod_legal\legalOpinionController@getRef');
                Route::post('store', 'Mod_legal\legalOpinionController@store');
                Route::get('cancel/{id}', 'Mod_legal\legalOpinionController@cancel');
                Route::get('printform/{id}', 'Mod_legal\legalOpinionController@printform');
                Route::post('printlist', 'Mod_legal\legalOpinionController@printlist');
                Route::get('printlist', 'Mod_legal\legalOpinionController@printlist');
                Route::get('printlist', 'Mod_legal\legalOpinionController@printlist');
                Route::post('upload', 'Mod_legal\legalOpinionController@upload');
                Route::get('uploaded/{id}', 'Mod_legal\legalOpinionController@uploaded');
                Route::get('documentView/{id}', 'Mod_legal\legalOpinionController@documentView');
                Route::get('uploadRemove/{id}', 'Mod_legal\legalOpinionController@uploadRemove');
            });
            Route::prefix('Review')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\forReviewController@edit');
                Route::get('show', 'Mod_legal\forReviewController@show');
                Route::get('getRef', 'Mod_legal\forReviewController@getRef');
                Route::post('store', 'Mod_legal\forReviewController@store');
                Route::get('cancel/{id}', 'Mod_legal\forReviewController@cancel');
                Route::get('print', 'Mod_legal\forReviewController@print');
                Route::post('updateAction', 'Mod_legal\forReviewController@updateAction');

                Route::post('storeUpload', 'Mod_legal\forReviewController@storeUpload');
                Route::get('showStat/{id}', 'Mod_legal\forReviewController@showStat');
                Route::post('updateStat', 'Mod_legal\forReviewController@updateStat');
            });
            Route::prefix('Appointments')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\appointmentController@edit');
                Route::get('show', 'Mod_legal\appointmentController@show');
                Route::get('getRef', 'Mod_legal\appointmentController@getRef');
                Route::post('store', 'Mod_legal\appointmentController@store');
                Route::get('cancel/{id}', 'Mod_legal\appointmentController@cancel');
                Route::get('print', 'Mod_legal\appointmentController@print');
                Route::post('updateAction', 'Mod_legal\appointmentController@updateAction');
                Route::get('showCalendar', 'Mod_legal\appointmentController@showCalendar');
                Route::get('showCalendar_data/{id}', 'Mod_legal\appointmentController@showCalendar_data');
            });
            Route::prefix('Contract')->group(function () {
                Route::get('getType', 'Mod_legal\contractController@getType');
                Route::post('storeDocType', 'Mod_legal\contractController@storeDocType');

                Route::get('edit/{id}', 'Mod_legal\contractController@edit');
                Route::get('show', 'Mod_legal\contractController@show');
                Route::get('getRef', 'Mod_legal\contractController@getRef');
                Route::post('store', 'Mod_legal\contractController@store');
                Route::get('cancel/{id}', 'Mod_legal\contractController@cancel');
                Route::get('print', 'Mod_legal\contractController@print');
                Route::post('storeDocumentUpdate', 'Mod_legal\contractController@storeDocumentUpdate');
                Route::get('getDocs/{id}', 'Mod_legal\contractController@getDocs');
                Route::get('documentView/{id}', 'Mod_legal\contractController@documentView');
                Route::get('uploadRemove/{id}', 'Mod_legal\contractController@uploadRemove');
            });
            Route::prefix('Case')->group(function () {
                Route::get('getCaseType', 'Mod_legal\caseController@getCaseType');
                Route::get('edit/{id}', 'Mod_legal\caseController@edit');
                Route::get('show', 'Mod_legal\caseController@show');
                Route::get('getRef', 'Mod_legal\caseController@getRef');
                Route::post('store', 'Mod_legal\caseController@store');
                Route::post('storeHearing', 'Mod_legal\caseController@storeHearing');
                Route::post('storeWitness', 'Mod_legal\caseController@storeWitness');
                Route::get('cancel/{id}', 'Mod_legal\caseController@cancel');
                Route::get('print', 'Mod_legal\caseController@print');
                Route::get('showHearing/{id}', 'Mod_legal\caseController@showHearing');
                Route::get('editHearing/{id}', 'Mod_legal\caseController@editHearing');
                Route::get('editWitness/{id}', 'Mod_legal\caseController@editWitness');
                Route::get('showWitness/{id}', 'Mod_legal\caseController@showWitness');
                Route::post('witnessUpload', 'Mod_legal\caseController@witnessUpload');
                Route::post('witnessUpload1', 'Mod_legal\caseController@witnessUpload1');
                Route::post('witnessUploaded', 'Mod_legal\caseController@witnessUploaded');

                Route::get('witnessUploadRemove/{id}', 'Mod_legal\caseController@witnessUploadRemove');
                Route::post('storeMeeting', 'Mod_legal\caseController@storeMeeting');
                Route::get('showMeeting/{id}', 'Mod_legal\caseController@showMeeting');
                Route::get('editMeeting/{id}', 'Mod_legal\caseController@editMeeting');

                Route::get('documentType', 'Mod_legal\caseController@documentType');
                Route::post('storeDocument', 'Mod_legal\caseController@storeDocument');
                Route::get('editdocument/{id}', 'Mod_legal\caseController@editdocument');

                Route::get('showDocument/{id}', 'Mod_legal\caseController@showDocument');
                Route::get('getDocs/{id}', 'Mod_legal\caseController@getDocs');
                Route::get('documentView/{id}', 'Mod_legal\caseController@documentView');
                Route::post('storeDocumentUpdate', 'Mod_legal\caseController@storeDocumentUpdate');
                Route::get('uploadRemoveDoc/{id}', 'Mod_legal\caseController@uploadRemoveDoc');

                Route::get('getUpdate/{id}', 'Mod_legal\caseController@getUpdate');
                Route::post('postUpdate', 'Mod_legal\caseController@postUpdate');
                Route::get('deleteUpdate/{id}', 'Mod_legal\caseController@deleteUpdate');
            });
            Route::prefix('lawenforcecontroller')->group(function () {
                Route::get('edit/{id}', 'Mod_legal\lawenforcecontroller@edit');
                Route::get('show', 'Mod_legal\lawenforcecontroller@show');
                Route::get('getRef', 'Mod_legal\lawenforcecontroller@getRef');
                Route::post('store', 'Mod_legal\lawenforcecontroller@store');
                Route::get('cancel/{id}', 'Mod_legal\lawenforcecontroller@cancel');
                Route::post('updateAction', 'Mod_legal\lawenforcecontroller@updateAction');

                Route::post('storeUpload', 'Mod_legal\lawenforcecontroller@storeUpload');
                Route::get('showStat/{id}', 'Mod_legal\lawenforcecontroller@showStat');
                Route::post('updateStat', 'Mod_legal\lawenforcecontroller@updateStat');
            });
        });
    });
});
