<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        //Treasury Controller
        Route::prefix('Treasury')->group(function () {
            Route::prefix('RealPropertyTax')->group(function () {
                Route::get('/getrptTaxMasterList', 'Treasury\RealPropertyTax@getrptTaxMasterList');
                Route::post('/rptTaxMasterListPrint', 'Treasury\RealPropertyTax@rptTaxMasterListPrint');
                Route::get('/getrptTaxClearance/{id}', 'Treasury\RealPropertyTax@getrptTaxClearance');
                Route::post('/rptTaxClearancePrint', 'Treasury\RealPropertyTax@rptTaxClearancePrint');
                Route::post('/getrptTaxDelinquency', 'Treasury\RealPropertyTax@getrptTaxDelinquency');
                Route::post('/rptTaxDelinquencyPrint', 'Treasury\RealPropertyTax@rptTaxDelinquencyPrint');
                Route::get('/getrptCollectionAbtract', 'Treasury\RealPropertyTax@getrptCollectionAbtract');
                Route::post('/rptCollectionAbtractPrint', 'Treasury\RealPropertyTax@rptCollectionAbtractPrint');
                Route::get('/getrptTaxDueandPayment/{id}', 'Treasury\RealPropertyTax@getrptTaxDueandPayment');
                Route::post('/getrptTaxDueDisplayList', 'Treasury\RealPropertyTax@getrptTaxDueDisplayList');
                Route::post('/getrptTaxDueandPaymentPrint', 'Treasury\RealPropertyTax@getrptTaxDueandPaymentPrint');
                Route::get('/getrptTaxComputation/{id}', 'Treasury\RealPropertyTax@getrptTaxComputation');
                Route::post('/rptTaxDelinquencyNoticePrint', 'Treasury\RealPropertyTax@rptTaxDelinquencyNoticePrint');
                Route::post('/rptTaxDelinquencyAllNoticePrint', 'Treasury\RealPropertyTax@rptTaxDelinquencyAllNoticePrint');
            });
            Route::prefix('TaxOnBusiness')->group(function () {
                Route::post('/getbusinessTaxMasterList', 'Treasury\TaxOnBusiness@getbusinessTaxMasterList');
                Route::post('/businessTaxMasterListPrint', 'Treasury\TaxOnBusiness@businessTaxMasterListPrint');
                Route::post('/businessTaxCertificatePrint', 'Treasury\TaxOnBusiness@businessTaxCertificatePrint');
                Route::post('/businessTaxOnClosurePrint', 'Treasury\TaxOnBusiness@businessTaxOnClosurePrint');
                Route::get('/businessTaxDeliquencyList', 'Treasury\TaxOnBusiness@businessTaxDeliquencyList');
                Route::post('/businessTaxDeliquencyListPrint', 'Treasury\TaxOnBusiness@businessTaxDeliquencyListPrint');
                Route::post('/businessTaxNoticeOfDelinquency', 'Treasury\TaxOnBusiness@businessTaxNoticeOfDelinquency');
                Route::post('/businessTaxNoticeOfDelinquencyAll', 'Treasury\TaxOnBusiness@businessTaxNoticeOfDelinquencyAll');
                Route::post('/businessTaxNoticeOfDelinquencyBrgy', 'Treasury\TaxOnBusiness@businessTaxNoticeOfDelinquencyBrgy');
                Route::get('/getbusinessTaxLedgerList', 'Treasury\TaxOnBusiness@getbusinessTaxLedgerList');
                Route::get('/getbusinessTaxLedgerHistory/{id}', 'Treasury\TaxOnBusiness@getbusinessTaxLedgerHistory');
                Route::post('/businessTaxLedgerListPrint', 'Treasury\TaxOnBusiness@businessTaxLedgerListPrint');
                Route::get('/businessTaxSubsidiaryLedgerPrint/{id}', 'Treasury\TaxOnBusiness@businessTaxSubsidiaryLedgerPrint');
                Route::get('/businessTaxLedgerHistoryPrint/{id}', 'Treasury\TaxOnBusiness@businessTaxLedgerHistoryPrint');
                Route::get('/getbusinessCollectionReport', 'Treasury\TaxOnBusiness@getbusinessCollectionReport');
                Route::get('/getbusinessCollectionDetails/{id}', 'Treasury\TaxOnBusiness@getbusinessCollectionDetails');
                Route::post('/businessCollectionReportPrint', 'Treasury\TaxOnBusiness@businessCollectionReportPrint');
                Route::get('/businessCollectionDetailsPrint/{id}', 'Treasury\TaxOnBusiness@businessCollectionDetailsPrint');
                Route::get('/getbusinessComparativeReport', 'Treasury\TaxOnBusiness@getbusinessComparativeReport');
                Route::post('/businessComparativeReportPrint', 'Treasury\TaxOnBusiness@businessComparativeReportPrint');
            });

            Route::prefix('OtherTaxes')->group(function () {
                Route::get('/getmarketDelinquency', 'Treasury\OtherTaxes@getmarketDelinquency');
                Route::post('/marketDelinquencyPrint', 'Treasury\OtherTaxes@marketDelinquencyPrint');
                Route::get('/getmarketMasterlist', 'Treasury\OtherTaxes@getmarketMasterlist');
                Route::post('/marketMasterlistPrint', 'Treasury\OtherTaxes@marketMasterlistPrint');
            });
            Route::prefix('Assessment')->group(function () {
                Route::get('/generateRef', 'Treasury\BusinessAssessment@generateRef');
                Route::get('/checkExist', 'Treasury\BusinessAssessment@checkExist');

                Route::post('/store', 'Treasury\BusinessAssessment@store');

                Route::get('/getPreloadEntryForm', 'Treasury\BusinessAssessment@getPreloadEntryForm');
                Route::get('/getEntryFormList', 'Treasury\BusinessAssessment@getEntryFormList');

                Route::get('/getPreloadLineBusiness', 'Treasury\BusinessAssessment@getPreloadLineBusiness');
                Route::get('/getLineBusinessList', 'Treasury\BusinessAssessment@getLineBusinessList');
                Route::post('/computeBusinessTaxGraduated', 'Treasury\BusinessAssessment@computeBusinessTaxGraduated');

                Route::get('/getPreloadGarabageFees', 'Treasury\BusinessAssessment@getPreloadGarabageFees');
                Route::get('/getGarabageList', 'Treasury\BusinessAssessment@getGarabageList');
                Route::post('/computeGarabageGraduated', 'Treasury\BusinessAssessment@computeGarabageGraduated');
           
                Route::get('/getPreloadCombustibleFees', 'Treasury\BusinessAssessment@getPreloadCombustibleFees');
                Route::get('/getCombustibleList', 'Treasury\BusinessAssessment@getCombustibleList');
                Route::post('/computeCombustibleGraduated', 'Treasury\BusinessAssessment@computeCombustibleGraduated');
           
                Route::get('/getPreloadOccupationalFees', 'Treasury\BusinessAssessment@getPreloadOccupationalFees');
                Route::get('/getOccupationalFeesList', 'Treasury\BusinessAssessment@getOccupationalFeesList');

                Route::get('/getPreloadOtherFees', 'Treasury\BusinessAssessment@getPreloadOtherFees');
                Route::get('/getOtherFeesList', 'Treasury\BusinessAssessment@getOtherFeesList');
                Route::get('/getOtherFeesDefault', 'Treasury\BusinessAssessment@getOtherFeesDefault');

                Route::get('/getList', 'Treasury\BusinessAssessment@getList');
                Route::get('/getDetails', 'Treasury\BusinessAssessment@getDetails');
                Route::post('/printList', 'Treasury\BusinessAssessment@printList');
            });
        });
    });
});
