<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        //Treasury Controller
        Route::prefix('mod_bac')->group(function () {
            Route::prefix('resolution')->group(function () {
                Route::get('getPR', 'Mod_Bac\resolutionController@getPR');
                Route::post('getPendingWorks', 'Mod_Bac\resolutionController@getPendingWorks');
                Route::post('getPendingFilter', 'Mod_Bac\resolutionController@getPendingFilter');
                Route::post('getPR_byYearCount', 'Mod_Bac\resolutionController@getPR_byYearCount');
                Route::post('getPRCount', 'Mod_Bac\resolutionController@getPRCount');
                Route::get('PRListing/{id}', 'Mod_Bac\resolutionController@PRListing');
                Route::post('officialstore', 'Mod_Bac\resolutionController@officialstore');
                Route::post('store', 'Mod_Bac\resolutionController@store');
                Route::get('show', 'Mod_Bac\resolutionController@show');
                Route::get('getofficials', 'Mod_Bac\resolutionController@getofficials');
                Route::get('cancel/{id}', 'Mod_Bac\resolutionController@cancel');
                Route::get('getRef/{id}', 'Mod_Bac\resolutionController@getRef');
                Route::get('getNOAApproval', 'Mod_Bac\resolutionController@getNOAApproval');
                Route::post('getNOAApprovalEntry', 'Mod_Bac\resolutionController@getNOAApprovalEntry');
                Route::post('getNOAapprovalCount', 'Mod_Bac\resolutionController@getNOAapprovalCount');
                Route::post('updateForNOAApproval', 'Mod_Bac\resolutionController@updateForNOAApproval');
                Route::get('getNOAApprovalList', 'Mod_Bac\resolutionController@getNOAApprovalList');
                Route::post('print', 'Mod_Bac\resolutionController@print');
                Route::post('printofficial', 'Mod_Bac\resolutionController@printofficial');
                Route::post('printProposal', 'Mod_Bac\resolutionController@printProposal');
                Route::post('storeTitle', 'Mod_Bac\resolutionController@storeTitle');
                Route::put('Edit/{id}', 'Mod_Bac\resolutionController@Edit');
                Route::post('storePR', 'Mod_Bac\resolutionController@storePR');
                Route::put('cancelPR/{id}', 'Mod_Bac\resolutionController@cancelPR');
                Route::post('viewDocAll', 'Mod_Bac\resolutionController@viewDocAll');
                Route::get('addPR', 'Mod_Bac\resolutionController@addPR');
            });
            Route::prefix('eligibility')->group(function () {
                Route::get('getPR', 'Mod_Bac\eligibilityController@getPR');
                Route::post('store', 'Mod_Bac\eligibilityController@store');
                Route::get('show', 'Mod_Bac\eligibilityController@show');
                Route::get('showfilter_year', 'Mod_Bac\eligibilityController@showfilter_year');
                Route::get('edit/{id}', 'Mod_Bac\eligibilityController@edit');
                Route::get('cancel/{id}', 'Mod_Bac\eligibilityController@cancel');
                Route::get('getEligibility', 'Mod_Bac\eligibilityController@getEligibility');
                Route::post('getForEligibility', 'Mod_Bac\eligibilityController@getForEligibility');
                Route::post('updateForEligibility', 'Mod_Bac\eligibilityController@updateForEligibility');
                Route::get('getDocu', 'Mod_Bac\eligibilityController@getDocu');
                Route::post('save', 'Mod_Bac\eligibilityController@save');
                Route::get('getDocsList/{id}', 'Mod_Bac\eligibilityController@getDocsList');
                Route::put('removeDocu/{id}', 'Mod_Bac\eligibilityController@removeDocu');
                Route::post('print', 'Mod_Bac\eligibilityController@print');
            });

            Route::prefix('NonEligibility')->group(function () {
                Route::get('getPR', 'Mod_Bac\nonElegibilityController@getPR');
                Route::post('store', 'Mod_Bac\nonElegibilityController@store');
                Route::get('show', 'Mod_Bac\nonElegibilityController@show');
                Route::get('showfilter_year', 'Mod_Bac\nonElegibilityController@showfilter_year');
                Route::get('edit/{id}', 'Mod_Bac\nonElegibilityController@edit');
                Route::get('cancel/{id}', 'Mod_Bac\nonElegibilityController@cancel');
                Route::get('getEligibility', 'Mod_Bac\nonElegibilityController@getEligibility');
                Route::post('getForEligibility', 'Mod_Bac\nonElegibilityController@getForEligibility');
                Route::post('updateForEligibility', 'Mod_Bac\nonElegibilityController@updateForEligibility');
                Route::get('getDocu', 'Mod_Bac\nonElegibilityController@getDocu');
                Route::post('save', 'Mod_Bac\nonElegibilityController@save');
                Route::get('getDocsList/{id}', 'Mod_Bac\nonElegibilityController@getDocsList');
                Route::put('removeDocu/{id}', 'Mod_Bac\nonElegibilityController@removeDocu');
                Route::post('print', 'Mod_Bac\nonElegibilityController@print');
            });
            Route::prefix('Motion')->group(function () {
                Route::post('store_motion', 'Mod_Bac\motioncontroller@store_motion');
                Route::get('show_motion', 'Mod_Bac\motioncontroller@show_motion');
            });
            Route::prefix('Alternative')->group(function () {
                Route::get('getAlternative', 'Mod_Bac\alternativeController@getAlternative');
                Route::post('getAlternativeMode', 'Mod_Bac\alternativeController@getAlternativeMode');
                Route::post('getAlternativeCount', 'Mod_Bac\alternativeController@getAlternativeCount');
                Route::post('print', 'Mod_Bac\alternativeController@print');
            });
            Route::prefix('project')->group(function () {
                Route::post('printAbstractBid', 'Mod_Bac\projectController@printAbstractBid');
                Route::post('storeDeclaration', 'Mod_Bac\projectController@storeDeclaration');
                Route::get('getGSDocs/{id}', 'Mod_Bac\projectController@getGSDocs');
                Route::get('itbGetActivities/{id}', 'Mod_Bac\projectController@itbGetActivities');
                Route::post('showITBINFRA', 'Mod_Bac\projectController@showITBINFRA');

                Route::get('getDocs', 'Mod_Bac\projectController@getDocs');
                Route::get('getMOP', 'Mod_Bac\projectController@getMOP');
                Route::get('getDocsPrebid_bulletin', 'Mod_Bac\projectController@getDocsPrebid_bulletin');
                Route::get('dones/{id}', 'Mod_Bac\projectController@dones');

                Route::post('upload', 'Mod_Bac\projectController@upload');
                Route::post('uploadPreBID', 'Mod_Bac\projectController@uploadPreBID');
                Route::post('uploadBIDOpining', 'Mod_Bac\projectController@uploadBIDOpining');

                Route::get('getAttach', 'Mod_Bac\projectController@getAttach');
                Route::get('getAttachPreBID', 'Mod_Bac\projectController@getAttachPreBID');
                Route::get('getAttachBIDOpening', 'Mod_Bac\projectController@getAttachBIDOpening');

                Route::get('getAttachBull', 'Mod_Bac\projectController@getAttachBull');

                Route::get('documentView/{type}', 'Mod_Bac\projectController@documentView');
                Route::get('documentViewPreBID/{type}', 'Mod_Bac\projectController@documentViewPreBID');
                Route::get('documentViewBIDOpining/{type}', 'Mod_Bac\projectController@documentViewBIDOpining');

                Route::get('uploadRemove/{id}', 'Mod_Bac\projectController@uploadRemove');
                Route::get('uploadRemovePreBID/{id}', 'Mod_Bac\projectController@uploadRemovePreBID');
                Route::get('uploadRemoveBIDOpening/{id}', 'Mod_Bac\projectController@uploadRemoveBIDOpening');

                ### setup
                Route::get('getBacMembers', 'Mod_Bac\projectController@getBacMembers');
                Route::get('getBacMembersForOpeningBID', 'Mod_Bac\projectController@getBacMembersForOpeningBID');
                ###
                Route::get('edit/{id}', 'Mod_Bac\projectController@edit');
                Route::get('show', 'Mod_Bac\projectController@show');
                Route::get('showFilter', 'Mod_Bac\projectController@showFilter');
                Route::get('showprocProject_fltr', 'Mod_Bac\projectController@showprocProject_fltr');
                Route::get('showEntry', 'Mod_Bac\projectController@showEntry');

                Route::get('showBidout', 'Mod_Bac\projectController@showBidout');

                Route::post('store', 'Mod_Bac\projectController@store');
                Route::get('cancel/{id}', 'Mod_Bac\projectController@cancel');
                ## 1 start
                Route::get('getProject', 'Mod_Bac\projectController@getProject');
                Route::post('storePreProc', 'Mod_Bac\projectController@storePreProc');
                ## 1 end
                Route::get('getRef', 'Mod_Bac\projectController@getRef');
                Route::get('get_1pre', 'Mod_Bac\projectController@get_1pre');
                Route::get('get_1preSelected/{id}', 'Mod_Bac\projectController@get_1preSelected');
                ## 2
                Route::get('get_2invitation', 'Mod_Bac\projectController@get_2invitation');
                Route::get('get_3invitation/{id}', 'Mod_Bac\projectController@get_3invitation');
                Route::post('storeInvitationToBid', 'Mod_Bac\projectController@storeInvitationToBid');
                ## 2
                Route::get('get_7suplemental', 'Mod_Bac\projectController@get_7suplemental');
                Route::get('get_15reso', 'Mod_Bac\projectController@get_15reso');
                Route::get('bacc_16noa', 'Mod_Bac\projectController@bacc_16noa');
                ## 3
                Route::get('get_3invitation_prebid', 'Mod_Bac\projectController@get_3invitation_prebid');
                Route::post('store3invitation_to_observer_prebid', 'Mod_Bac\projectController@store3invitation_to_observer_prebid');
                ###4 prebid
                Route::post('store4_prebid', 'Mod_Bac\projectController@store4_prebid');
                Route::get('get_4prebid_conference', 'Mod_Bac\projectController@get_4prebid_conference');
                Route::post('removedocs', 'Mod_Bac\projectController@removedocs');
                ###5
                Route::post('store5invitation_to_bid_opening', 'Mod_Bac\projectController@store5invitation_to_bid_opening');
                Route::get('get5invitation_to_bid_opening', 'Mod_Bac\projectController@get5invitation_to_bid_opening');
                Route::post('getBacMembersForOpeningBIDSupplierStore', 'Mod_Bac\projectController@getBacMembersForOpeningBIDSupplierStore');
                Route::post('getBacMembersForOpeningBIDSupplierRemove', 'Mod_Bac\projectController@getBacMembersForOpeningBIDSupplierRemove');
                ###6
                Route::get('getSupplierInvited', 'Mod_Bac\projectController@getSupplierInvited');
                Route::post('getSupplierInvitedRemove', 'Mod_Bac\projectController@getSupplierInvitedRemove');

                Route::post('store6bid_opening', 'Mod_Bac\projectController@store6bid_opening');
                Route::get('getBIDList', 'Mod_Bac\projectController@getBIDList');
                ###7
                Route::post('store7postqua', 'Mod_Bac\projectController@store7postqua');
                Route::get('getPOSTQUAList', 'Mod_Bac\projectController@getPOSTQUAList');

                ### 8
                Route::get('getNoaWinner', 'Mod_Bac\projectController@getNoaWinner');
                Route::post('store8NOA', 'Mod_Bac\projectController@store8NOA');
                #### 9
                Route::get('getContract', 'Mod_Bac\projectController@getContract');
                Route::post('store9Contract', 'Mod_Bac\projectController@store9Contract');
                Route::get('bacc_ContractList', 'Mod_Bac\projectController@bacc_ContractList');

                ### 10
                Route::post('store10NTP', 'Mod_Bac\projectController@store10NTP');
                Route::get('getNTP', 'Mod_Bac\projectController@getNTP');
                Route::get('bacc_NPTList', 'Mod_Bac\projectController@bacc_NPTList');

                ###calendar
                Route::get('displayCalendar', 'Mod_Bac\projectController@displayCalendar');
                Route::post('printBidOut', 'Mod_Bac\projectController@printBidOut');
                Route::get('displayInfra', 'Mod_Bac\projectController@displayInfra');
                Route::get('displayGoods', 'Mod_Bac\projectController@displayGoods');


                Route::post('storeInvitationBID', 'Mod_Bac\projectController@storeInvitationBID');
                Route::get('printLetter/{id}', 'Mod_Bac\projectController@printLetter');

                Route::get('getBIDinvitationList', 'Mod_Bac\projectController@getBIDinvitationList');

                Route::get('getOpeningProjectByDate/{date}/{type}', 'Mod_Bac\projectController@getOpeningProjectByDate');
                Route::get('displayCalendarPerdate', 'Mod_Bac\projectController@displayCalendarPerdate');
            });
        });
    });
});
