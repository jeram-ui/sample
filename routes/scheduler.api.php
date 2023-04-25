<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        //Treasury Controller
        Route::prefix('Scheduler')->group(function () {
            Route::prefix('Position')->group(function () {
                Route::post('save', 'Scheduler\positionController@save');
                Route::get('displayData', 'Scheduler\positionController@displayData');
                Route::get('filterData', 'Scheduler\positionController@filterData');
                Route::get('customData', 'Scheduler\positionController@customData');
                Route::post('printList', 'Scheduler\positionController@printList');
                Route::get('cancel/{id}', 'Scheduler\positionController@cancel');
            });

            Route::prefix('Officers')->group(function () {
                Route::get('organization', 'Scheduler\officersController@organization');
                Route::get('member/{id}', 'Scheduler\officersController@member');
                Route::get('position', 'Scheduler\officersController@position');
                Route::post('save', 'Scheduler\officersController@save');
                Route::get('editData/{id}', 'Scheduler\officersController@editData');
                Route::post('updateData', 'Scheduler\officersController@updateData');
                Route::get('cancel/{id}', 'Scheduler\officersController@cancel');
            });
            Route::prefix('Officer')->group(function () {
                Route::get('orgArr', 'Scheduler\officerController@orgArr');
                Route::get('officerList', 'Scheduler\officerController@officerList');
                Route::get('officerOrg/{id}', 'Scheduler\officerController@officerOrg');
                Route::post('officerPrint', 'Scheduler\officerController@officerPrint');
                Route::post('printOfficers', 'Scheduler\officerController@printOfficers');
                Route::get('getTerm', 'Scheduler\officerController@getTerm');
            });

            Route::prefix('Member')->group(function () {
                Route::get('memberList', 'Scheduler\MemberController@memberList');
                Route::get('printMember/{id}', 'Scheduler\MemberController@printMember');
                Route::post('printMemberProfileList', 'Scheduler\MemberController@printMemberProfileList');
                Route::get('getOrganization', 'Scheduler\MemberController@getOrganization');
                Route::get('organizationList', 'Scheduler\MemberController@organizationList');
                Route::get('personList', 'Scheduler\MemberController@personList');
                Route::get('sectorList', 'Scheduler\MemberController@sectorList');
                Route::get('relationList', 'Scheduler\MemberController@relationList');
                Route::get('educationList', 'Scheduler\MemberController@educationList');
                Route::get('graduationList', 'Scheduler\MemberController@graduationList');
                Route::get('positionList', 'Scheduler\MemberController@positionList');
                Route::get('rememberList', 'Scheduler\MemberController@rememberList');
                Route::post('store', 'Scheduler\MemberController@store');
                Route::get('edit/{id}', 'Scheduler\MemberController@edit');
                Route::get('transNo', 'Scheduler\MemberController@transNo');
                Route::get('cancel/{id}', 'Scheduler\MemberController@cancel');
            });
            Route::prefix('Organization')->group(function () {
                Route::get('getSetupData', 'Scheduler\OrganizationController@getSetupData');
                Route::post('store', 'Scheduler\OrganizationController@store');
                Route::get('edit/{id}', 'Scheduler\OrganizationController@edit');
                Route::get('cancel/{id}', 'Scheduler\OrganizationController@cancel');

                Route::get('getListOrganization', 'Scheduler\OrganizationController@getListOrganization');
                Route::get('getDetails/{id}', 'Scheduler\OrganizationController@getDetails');
                Route::post('printdetail', 'Scheduler\OrganizationController@printdetail');
                Route::get('getOrganizationByMember/{id}', 'Scheduler\OrganizationController@getOrganizationByMember');
                Route::get('members/{id}', 'Scheduler\OrganizationController@members');
            });
            Route::prefix('marriage')->group(function () {
                Route::post('store', 'Scheduler\MarriageController@store');
                Route::get('printMarriageSlip/{id}', 'Scheduler\MarriageController@printMarriageSlip');
                Route::get('displayCalendar', 'Scheduler\MarriageController@displayCalendar');
                Route::get('edit/{id}', 'Scheduler\MarriageController@edit');
            });

            Route::prefix('facility')->group(function () {
                Route::post('store', 'Scheduler\FacilityController@store');
                Route::post('storeResources', 'Scheduler\FacilityController@storeResources');
                Route::post('storeFacility', 'Scheduler\FacilityController@storeFacility');
                Route::put('cancelResources/{id}', 'Scheduler\FacilityController@cancelResources');
                Route::put('cancelFacility/{id}', 'Scheduler\FacilityController@cancelFacility');
                Route::get('printFacilitySlip/{id}', 'Scheduler\FacilityController@printFacilitySlip');
                Route::get('displayCalendar', 'Scheduler\FacilityController@displayCalendar');
                Route::get('facilityFilter/{id}', 'Scheduler\FacilityController@facilityFilter');
                Route::get('facilityList', 'Scheduler\FacilityController@facilityList');
                Route::get('resourceList', 'Scheduler\FacilityController@resourceList');
                Route::get('departmentList', 'Scheduler\FacilityController@departmentList');
            });

            Route::prefix('equipment')->group(function () {
                Route::post('store', 'Scheduler\VehicleController@store');
                Route::get('printVehicleSlip/{id}', 'Scheduler\VehicleController@printVehicleSlip');
                Route::get('displayCalendar', 'Scheduler\VehicleController@displayCalendar');
            });
            Route::prefix('memorial')->group(function () {
                Route::post('store', 'Scheduler\MemorialController@store');
                Route::get('edit/{id}', 'Scheduler\MemorialController@edit');
                Route::get('displayCalendarfive', 'Scheduler\MemorialController@displayCalendarfive');
                Route::get('displayCalendarten', 'Scheduler\MemorialController@displayCalendarten');
                Route::get('printMemorialSlip/{id}', 'Scheduler\MemorialController@printMemorialSlip');
                Route::get('displayCalendar', 'Scheduler\MemorialController@displayCalendar');
            });
            Route::prefix('bonechamber')->group(function () {
                Route::post('store', 'Scheduler\BoneChanberController@store');
                Route::get('edit/{id}', 'Scheduler\BoneChanberController@edit');
                Route::get('printMemorialSlip/{id}', 'Scheduler\BoneChanberController@printMemorialSlip');
                Route::get('displayCalendar', 'Scheduler\BoneChanberController@displayCalendar');
            });
            Route::prefix('mayor')->group(function () {
                Route::post('store', 'Scheduler\MayorsController@store');
                Route::get('printMayorSlip/{id}', 'Scheduler\MayorsController@printMayorSlip');
                Route::get('displayCalendar', 'Scheduler\MayorsController@displayCalendar');
            });
            Route::prefix('training')->group(function () {
                Route::post('store', 'Scheduler\trainingController@store');
                Route::get('printMayorSlip/{id}', 'Scheduler\trainingController@printMayorSlip');
                Route::get('displayCalendar', 'Scheduler\trainingController@displayCalendar');
                Route::get('displayCalendarAll', 'Scheduler\trainingController@displayCalendarAll');
            });

            Route::prefix('Organization')->group(function () {
                Route::get('getSetupData', 'Scheduler\OrganizationController@getSetupData');
                Route::post('store', 'Scheduler\OrganizationController@store');
                Route::get('edit/{id}', 'Scheduler\OrganizationController@edit');
                Route::get('getListOrganization', 'Scheduler\OrganizationController@getListOrganization');
                Route::get('getDetails/{id}', 'Scheduler\OrganizationController@getDetails');
                Route::post('printdetail', 'Scheduler\OrganizationController@printdetail');
                Route::get('getOrganizationByMember/{id}', 'Scheduler\OrganizationController@getOrganizationByMember');
            });
            Route::prefix('MemorialListing')->group(function () {
                Route::get('show', 'Scheduler\MemorialListingController@show');
            });
            Route::prefix('Agenda')->group(function () {
                Route::post('store', 'Scheduler\AgendaController@store');
                Route::get('show', 'Scheduler\AgendaController@show');
                Route::get('edit/{id}', 'Scheduler\AgendaController@edit');
                Route::get('cancel/{id}', 'Scheduler\AgendaController@cancel');
                Route::get('displayIncomming', 'Scheduler\AgendaController@displayIncomming');
                Route::get('displayDone', 'Scheduler\AgendaController@displayDone');
                Route::post('attend', 'Scheduler\AgendaController@attend');
                Route::post('disregard', 'Scheduler\AgendaController@disregard');
                Route::get('getMinutesData/{id}', 'Scheduler\AgendaController@minutes');
                Route::get('getRef/{date}', 'Scheduler\AgendaController@getRef');
                Route::get('getMemberPerOrg/{id}', 'Scheduler\AgendaController@getMemberPerOrg');
                Route::post('present', 'Scheduler\AgendaController@present');
                Route::post('uploadFile', 'Scheduler\AgendaController@uploadFile');
                Route::get('uploaded/{id}', 'Scheduler\AgendaController@uploaded');
                Route::get('getAttendance/{id}', 'Scheduler\AgendaController@getAttendance');
                Route::get('uploadedDelete/{id}', 'Scheduler\AgendaController@uploadedDelete');
                Route::post('saveMinutes', 'Scheduler\AgendaController@saveMinutes');
                Route::post('updateDocs', 'Scheduler\AgendaController@updateDocs');
                Route::post('uploadFileResolution', 'Scheduler\AgendaController@uploadFileResolution');
                Route::get('uploadedOrdinance/{id}', 'Scheduler\AgendaController@uploadedOrdinance');
                Route::get('uploadedResolution/{id}', 'Scheduler\AgendaController@uploadedResolution');
                Route::get('uploadedDeleteResolution/{id}', 'Scheduler\AgendaController@uploadedDeleteResolution');
                Route::get('getResolutionData', 'Scheduler\AgendaController@getResolutionData');
                Route::get('getRefResolution/{date}', 'Scheduler\AgendaController@getRefResolution');
                Route::get('getRefOrdinance/{date}', 'Scheduler\AgendaController@getRefOrdinance');
                Route::post('saveResolution', 'Scheduler\AgendaController@saveResolution');
                Route::get('getAttendanceResolution', 'Scheduler\AgendaController@getAttendanceResolution');
            });
        });
    });
});
