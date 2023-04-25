<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        //Treasury Controller
        Route::prefix('mod_tracker')->group(function () {
            Route::get('/getRoutingSetup', 'GlobalController@getRoutingSetup');
            Route::prefix('DocRouting')->group(function () {
                Route::get('getRef', 'DocumentTrucker\RoutingController@getRef');
                Route::get('edit/{id}', 'DocumentTrucker\RoutingController@edit');
                Route::get('show', 'DocumentTrucker\RoutingController@show');
                Route::post('store', 'DocumentTrucker\RoutingController@store');
                Route::get('cancel/{id}', 'DocumentTrucker\RoutingController@cancel');
                Route::post('print', 'DocumentTrucker\RoutingController@print');
            });
            Route::prefix('Docreceived')->group(function () {
                Route::get('showIncoming', 'DocumentTrucker\DocumentReceivedController@showIncoming');
                Route::get('showAllDocs', 'DocumentTrucker\DocumentReceivedController@showAllDocs');

                Route::get('signatory', 'DocumentTrucker\DocumentReceivedController@signatory');
                Route::get('signatoryDocs', 'DocumentTrucker\DocumentReceivedController@signatoryDocs');

                Route::post('signatoryStore', 'DocumentTrucker\DocumentReceivedController@signatoryStore');
                Route::post('signatoryStoreView', 'DocumentTrucker\DocumentReceivedController@signatoryStoreView');
                Route::post('received', 'DocumentTrucker\DocumentReceivedController@received');
                Route::get('return/{id}', 'DocumentTrucker\DocumentReceivedController@return');
                Route::get('receivedList', 'DocumentTrucker\DocumentReceivedController@receivedList');
                Route::get('receivedListDone', 'DocumentTrucker\DocumentReceivedController@receivedListDone');
                Route::get('receivedListDoneRead/{id}', 'DocumentTrucker\DocumentReceivedController@receivedListDoneRead');

                Route::get('getDocs', 'DocumentTrucker\DocumentReceivedController@getDocs');
                Route::post('storeDocumentUpdate', 'DocumentTrucker\DocumentReceivedController@storeDocumentUpdate');
                Route::get('documentView/{id}', 'DocumentTrucker\DocumentReceivedController@documentView');
                Route::post('notes', 'DocumentTrucker\DocumentReceivedController@notes');
                Route::get('getNotes/{id}', 'DocumentTrucker\DocumentReceivedController@getNotes');
                Route::get('uploadRemove/{id}', 'DocumentTrucker\DocumentReceivedController@uploadRemove');
                Route::get('getDoneCount', 'DocumentTrucker\DocumentReceivedController@getDoneCount');

                Route::post('storeUpdates', 'DocumentTrucker\DocumentReceivedController@storeUpdates');
                Route::post('storeComments', 'DocumentTrucker\DocumentReceivedController@storeComments');
                Route::get('getComments/{id}', 'DocumentTrucker\DocumentReceivedController@getComments');

                Route::post('storeUpdatesAdditional', 'DocumentTrucker\DocumentReceivedController@storeUpdatesAdditional');


                Route::post('storeUpdatesReply', 'DocumentTrucker\DocumentReceivedController@storeUpdatesReply');

                Route::get('getUpdates', 'DocumentTrucker\DocumentReceivedController@getUpdates');

                Route::get('storeUpdatesCancel/{id}', 'DocumentTrucker\DocumentReceivedController@storeUpdatesCancel');
                Route::post('getPendingCorrespondence', 'DocumentTrucker\DocumentReceivedController@getPendingCorrespondence');
                Route::post('printCorrespondingList', 'DocumentTrucker\DocumentReceivedController@printCorrespondingList');
            });
            Route::prefix('Flow')->group(function () {
                Route::get('/', 'DocumentTrucker\FlowController@index');
                Route::get('show', 'DocumentTrucker\FlowController@show');
                Route::get('edit/{id}', 'DocumentTrucker\FlowController@edit');
                Route::get('cancel/{id}', 'DocumentTrucker\FlowController@cancel');
                Route::post('store', 'DocumentTrucker\FlowController@store');
                Route::post('updateSignatory', 'DocumentTrucker\FlowController@updateSignatory');

                Route::get('docType', 'DocumentTrucker\FlowController@docType');
                Route::get('getTagReferral/{id}', 'DocumentTrucker\FlowController@getTagReferral');
                Route::get('signatory/{id}', 'DocumentTrucker\FlowController@signatory');
            });
            Route::prefix('Doctracker')->group(function () {

                Route::get('/getAssistedName', 'DocumentTrucker\DocumentController@getAssistedName');
                Route::get('/documentstraker_category', 'DocumentTrucker\DocumentController@documentstraker_category');
                Route::get('/show', 'DocumentTrucker\DocumentController@show');
                Route::get('/getRef', 'DocumentTrucker\DocumentController@getRef');
                Route::get('/getFlowChart', 'DocumentTrucker\DocumentController@getFlowchart');
                Route::get('/edit/{id}', 'DocumentTrucker\DocumentController@edit');
                Route::get('/list', 'DocumentTrucker\DocumentController@list');
                Route::post('/store', 'DocumentTrucker\DocumentController@store');
                Route::post('/done', 'DocumentTrucker\DocumentController@done');
                Route::get('/getDepartMent', 'DocumentTrucker\DocumentController@getDepartMent');


                Route::get('/getSigantorySetup/{id}', 'DocumentTrucker\DocumentController@getSigantorySetup');
                Route::post('/storeEmail', 'DocumentTrucker\DocumentController@storeEmail');
                Route::get('/EmailList', 'DocumentTrucker\DocumentController@EmailList');

                Route::get('/Display_Name', 'DocumentTrucker\DocumentController@Display_Name');
                Route::get('/flowName', 'DocumentTrucker\DocumentController@getFLowName');
                Route::get('/docType', 'DocumentTrucker\DocumentController@docType');
                Route::get('/showPerType', 'DocumentTrucker\DocumentController@showPerType');
                Route::get('/showAll', 'DocumentTrucker\DocumentController@showAll');
                Route::get('/showStatus/{id}', 'DocumentTrucker\DocumentController@showStatus');
                Route::post('/showPerNumber', 'DocumentTrucker\DocumentController@showPerNumber');

                Route::get('/cancel/{id}', 'DocumentTrucker\DocumentController@cancel');
                Route::post('/uploadFile', 'DocumentTrucker\DocumentController@uploadFile');
                Route::get('/uploaded/{id}', 'DocumentTrucker\DocumentController@uploaded');
                Route::get('/uploadedRemove/{id}', 'DocumentTrucker\DocumentController@uploadedRemove');
                Route::post('/printqr', 'DocumentTrucker\DocumentController@printqr');
                Route::post('/printDoc', 'DocumentTrucker\DocumentController@printDoc');
                Route::post('/printProposal', 'DocumentTrucker\DocumentController@printProposal');
                Route::post('/print_TO', 'DocumentTrucker\DocumentController@print_TO');
                Route::post('/undone', 'DocumentTrucker\DocumentController@undone');

                Route::get('/allCommunications', 'DocumentTrucker\DocumentController@allCommunications');
            });
        });
    });
});
