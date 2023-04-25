<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        //BPLO Controller
        Route::prefix('Business')->group(function () {
            Route::prefix('BusinessReport')->group(function () {
                Route::get('/businessPermitStatus', 'Business\BusinessReport@businessPermitStatus');
                Route::post('/businessPermitStatusPrint', 'Business\BusinessReport@businessPermitStatusPrint');
                Route::get('/businessPaymentStatus', 'Business\BusinessReport@businessPaymentStatus');
                Route::post('/businessPaymentStatusPrint', 'Business\BusinessReport@businessPaymentStatusPrint');
                Route::get('/taxPayerReport', 'Business\BusinessReport@taxPayerReport');
                Route::post('/taxPayerReportPrint', 'Business\BusinessReport@taxPayerReportPrint');
                Route::get('/businessEnterprise', 'Business\BusinessReport@businessEnterprise');
                Route::post('/businessEnterprisePrint', 'Business\BusinessReport@businessEnterprisePrint');
                Route::get('/businessDTIReport', 'Business\BusinessReport@businessDTIReport');
                Route::post('/businessDTIReportPrint', 'Business\BusinessReport@businessDTIReportPrint');
                Route::post('/businessDTIReportPrintMEI', 'Business\BusinessReport@businessDTIReportPrintMEI');
                Route::get('/businessBMBE', 'Business\BusinessReport@businessBMBE');
                Route::post('/businessBMBEPrint', 'Business\BusinessReport@businessBMBEPrint');
                Route::get('/businessBSP', 'Business\BusinessReport@businessBSP');
                Route::post('/businessBSPPrint', 'Business\BusinessReport@businessBSPPrint');
                Route::post('/businessBSPReport', 'Business\BusinessReport@businessBSPReport');
            });

            Route::prefix('BusinessInquiry')->group(function () {                
                Route::post('/businessInfo', 'Business\BusinessInquiry@businessInfo');
                Route::post('/transInquiry', 'Business\BusinessInquiry@transInquiry');
                Route::post('/ledgerHistory', 'Business\BusinessInquiry@ledgerHistory');
                Route::post('/inspectorate', 'Business\BusinessInquiry@inspectorate');
            });

            Route::prefix('Dashboard')->group(function () {                
                Route::get('/getNewRenewBusiness', 'Dashboard\DashboadController@getNewRenewBusiness');           
            });
        });
    });
});
