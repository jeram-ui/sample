<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
  Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('mod_hr')->group(function () {
      Route::prefix('dtr')->group(function () {
        Route::get('getDTR', 'Mod_HR\hrcontroller@getDTRList');
        Route::get('getDTRListPrint', 'Mod_HR\hrcontroller@getDTRListPrint');
        Route::get('getPslip', 'Mod_HR\hrcontroller@getPslip');
        Route::post('printDtr', 'Mod_HR\hrcontroller@printDtr');
        Route::get('getLogs/{date}', 'Mod_HR\hrcontroller@getLogs');
        Route::get('showData', 'Mod_HR\hrcontroller@showData');
        Route::get('showList', 'Mod_HR\hrcontroller@showList');
        Route::get('showShifts', 'Mod_HR\hrcontroller@showShifts');
        Route::post('storeSlip', 'Mod_HR\hrcontroller@storeSlip');
      });
      Route::prefix('dtrMaker')->group(function () {
        Route::get('getDTR', 'Mod_HR\DTRMakerController@getDTRList');
        Route::get('getDTRListPrint', 'Mod_HR\DTRMakerController@getDTRListPrint');
        Route::get('getPslip', 'Mod_HR\DTRMakerController@getPslip');
        Route::post('printDtr', 'Mod_HR\DTRMakerController@printDtr');
        Route::get('getLogs/{date}', 'Mod_HR\DTRMakerController@getLogs');
        Route::get('showData', 'Mod_HR\DTRMakerController@showData');
        Route::get('showList', 'Mod_HR\DTRMakerController@showList');
        Route::get('showShifts', 'Mod_HR\DTRMakerController@showShifts');
        Route::post('storeSlip', 'Mod_HR\DTRMakerController@storeSlip');
      });

      Route::prefix('certOfAppearance')->group(function () {
        Route::post('store', 'Mod_HR\certOfAppearanceController@store');
        Route::get('certList', 'Mod_HR\certOfAppearanceController@certList');
        Route::get('getPslip', 'Mod_HR\hrcontroller@getPslip');
        Route::post('print', 'Mod_HR\certOfAppearanceController@print');
        Route::put('cancel/{id}', 'Mod_HR\certOfAppearanceController@cancel');
        Route::put('Edit/{id}', 'Mod_HR\certOfAppearanceController@Edit');
      });
      Route::prefix('Leave Entry')->group(function () {
        Route::get('showleave', 'Mod_HR\leaveentrycontroller@showleave');
        Route::post('store', 'Mod_HR\leaveentrycontroller@store');
        Route::get('getref', 'Mod_HR\leaveentrycontroller@getref');
        Route::get('showlist', 'Mod_HR\leaveentrycontroller@showlist');
        Route::get('cancel/{leave_id}', 'Mod_HR\leaveentrycontroller@cancel');
        Route::get('getForApproval', 'Mod_HR\leaveentrycontroller@getForApproval');
        Route::post('Approved', 'Mod_HR\leaveentrycontroller@Approved');
        Route::post('ApprovedMayor', 'Mod_HR\leaveentrycontroller@ApprovedMayor');
        Route::get('chekingLimit', 'Mod_HR\leaveentrycontroller@chekingLimit');
        Route::get('getForApprovalMayor', 'Mod_HR\leaveentrycontroller@getForApprovalMayor');
        Route::post('print', 'Mod_HR\leaveentrycontroller@print');
        // Route::get('getPaySlip', 'Mod_HR\leaveentrycontroller@getPaySlip');

      });
      Route::prefix('Payslip')->group(function () {
        Route::post('printpayslip', 'Mod_HR\payslipController@printpayslip');
        Route::get('getPaySlip', 'Mod_HR\payslipController@getPaySlip');
        Route::get('getAllPaySlip/{id}', 'Mod_HR\payslipController@getAllPaySlip');


        Route::get('getEmployee', 'Mod_HR\payslipController@getEmployee');

        // Route::get('getAllPaySlip', 'Mod_HR\payslipController@getAllPaySlip');

      });
      Route::prefix('RaffleNames')->group(function () {
        Route::post('store', 'Mod_HR\raffleNameController@store');
        Route::get('getRaffleList', 'Mod_HR\raffleNameController@getRaffleList');
        Route::get('getDepartment', 'Mod_HR\raffleNameController@getDepartment');
        Route::put('Edit/{id}', 'Mod_HR\raffleNameController@Edit');
        Route::put('cancel/{id}', 'Mod_HR\raffleNameController@cancel');

      });
      Route::prefix('TravelOrders')->group(function () {
        Route::post('store', 'Mod_HR\TravelOrdersController@store');
        Route::get('show', 'Mod_HR\TravelOrdersController@show');
        Route::put('Edit/{id}', 'Mod_HR\TravelOrdersController@Edit');
        Route::put('cancel/{id}', 'Mod_HR\TravelOrdersController@cancel');
        Route::post('print', 'Mod_HR\TravelOrdersController@print');

      });
      Route::prefix('Force Leave')->group(function () {
        Route::get('showleave', 'Mod_HR\Forceleaveentrycontroller@showleave');
        Route::post('store', 'Mod_HR\Forceleaveentrycontroller@store');
        Route::get('getref', 'Mod_HR\Forceleaveentrycontroller@getref');
        Route::get('showlist', 'Mod_HR\Forceleaveentrycontroller@showlist');
        Route::get('cancel/{leave_id}', 'Mod_HR\Forceleaveentrycontroller@cancel');
        Route::get('getForApproval', 'Mod_HR\Forceleaveentrycontroller@getForApproval');
        Route::post('Approved', 'Mod_HR\Forceleaveentrycontroller@Approved');
        Route::post('print', 'Mod_HR\Forceleaveentrycontroller@print');
      });
      Route::prefix('Time Schedule')->group(function () {
        Route::get('Timeschedule', 'Mod_HR\Timesched\Timeschedcontroller@Timeschedule');
        Route::get('showShifts', 'Mod_HR\Timesched\Timeschedcontroller@showShifts');
        Route::post('storeTime', 'Mod_HR\Timesched\Timeschedcontroller@storeTime');
        Route::put('timeCancel/{id}', 'Mod_HR\Timesched\Timeschedcontroller@timeCancel');
        Route::put('timeEdit/{id}', 'Mod_HR\Timesched\Timeschedcontroller@timeEdit');
      });

      Route::prefix('Leave Entry')->group(function () {
        Route::get('showleave', 'Mod_HR\leaveentrycontroller@showleave');
        Route::post('store', 'Mod_HR\leaveentrycontroller@store');
        Route::get('getref', 'Mod_HR\leaveentrycontroller@getref');
        Route::get('showlist', 'Mod_HR\leaveentrycontroller@showlist');
        Route::get('cancel/{leave_id}', 'Mod_HR\leaveentrycontroller@cancel');
        Route::get('getForApproval', 'Mod_HR\leaveentrycontroller@getForApproval');
        Route::post('Approved', 'Mod_HR\leaveentrycontroller@Approved');
        Route::post('ApprovedMayor', 'Mod_HR\leaveentrycontroller@ApprovedMayor');
        Route::get('chekingLimit', 'Mod_HR\leaveentrycontroller@chekingLimit');
        Route::get('chekingLeaveDate/{date}', 'Mod_HR\leaveentrycontroller@chekingLeaveDate');
        Route::get('getForApprovalMayor', 'Mod_HR\leaveentrycontroller@getForApprovalMayor');
        Route::post('print', 'Mod_HR\leaveentrycontroller@print');
      });
      Route::prefix('Force Leave')->group(function () {
        Route::get('showleave', 'Mod_HR\Forceleaveentrycontroller@showleave');
        Route::post('store', 'Mod_HR\Forceleaveentrycontroller@store');
        Route::get('getref', 'Mod_HR\Forceleaveentrycontroller@getref');
        Route::get('showlist', 'Mod_HR\Forceleaveentrycontroller@showlist');
        Route::get('cancel/{leave_id}', 'Mod_HR\Forceleaveentrycontroller@cancel');
        Route::get('getForApproval', 'Mod_HR\Forceleaveentrycontroller@getForApproval');
        Route::post('Approved', 'Mod_HR\Forceleaveentrycontroller@Approved');
        Route::post('print', 'Mod_HR\Forceleaveentrycontroller@print');
      });
      Route::prefix('Time Schedule')->group(function () {
        Route::get('Timeschedule', 'Mod_HR\Timesched\Timeschedcontroller@Timeschedule');
        Route::get('showShifts', 'Mod_HR\Timesched\Timeschedcontroller@showShifts');
        Route::post('storeTime', 'Mod_HR\Timesched\Timeschedcontroller@storeTime');
        Route::put('timeCancel/{id}', 'Mod_HR\Timesched\Timeschedcontroller@timeCancel');
        Route::put('timeEdit/{id}', 'Mod_HR\Timesched\Timeschedcontroller@timeEdit');
      });


      Route::prefix('Leave Entry')->group(function () {
        Route::get('showleave', 'Mod_HR\leaveentrycontroller@showleave');
        Route::post('store', 'Mod_HR\leaveentrycontroller@store');
        Route::get('getref', 'Mod_HR\leaveentrycontroller@getref');
        Route::get('showlist', 'Mod_HR\leaveentrycontroller@showlist');
        Route::get('cancel/{leave_id}', 'Mod_HR\leaveentrycontroller@cancel');
        Route::get('getForApproval', 'Mod_HR\leaveentrycontroller@getForApproval');
        Route::get('getRecommendedList', 'Mod_HR\leaveentrycontroller@getRecommendedList');

        Route::post('Approved', 'Mod_HR\leaveentrycontroller@Approved');
        Route::post('ApprovedMayor', 'Mod_HR\leaveentrycontroller@ApprovedMayor');
        Route::get('chekingLimit', 'Mod_HR\leaveentrycontroller@chekingLimit');
        Route::get('getForApprovalMayor', 'Mod_HR\leaveentrycontroller@getForApprovalMayor');
        Route::get('getApprovedList', 'Mod_HR\leaveentrycontroller@getApprovedList');
        Route::post('print', 'Mod_HR\leaveentrycontroller@print');
        Route::get('getLeaveLedger', 'Mod_HR\leaveentrycontroller@getLeaveLedger');
        Route::get('updateForApproval/{id}', 'Mod_HR\leaveentrycontroller@updateForApproval');

      });
      Route::prefix('Force Leave')->group(function () {
        Route::get('showleave', 'Mod_HR\Forceleaveentrycontroller@showleave');
        Route::post('store', 'Mod_HR\Forceleaveentrycontroller@store');
        Route::get('getref', 'Mod_HR\Forceleaveentrycontroller@getref');
        Route::get('showlist', 'Mod_HR\Forceleaveentrycontroller@showlist');
        Route::get('cancel/{leave_id}', 'Mod_HR\Forceleaveentrycontroller@cancel');
        Route::get('getForApproval', 'Mod_HR\Forceleaveentrycontroller@getForApproval');
        Route::post('Approved', 'Mod_HR\Forceleaveentrycontroller@Approved');
        Route::post('print', 'Mod_HR\Forceleaveentrycontroller@print');
      });
      Route::prefix('Time Schedule')->group(function () {
        Route::get('Timeschedule', 'Mod_HR\Timesched\Timeschedcontroller@Timeschedule');
        Route::get('showShifts', 'Mod_HR\Timesched\Timeschedcontroller@showShifts');
        Route::post('storeTime', 'Mod_HR\Timesched\Timeschedcontroller@storeTime');
        Route::put('timeCancel/{id}', 'Mod_HR\Timesched\Timeschedcontroller@timeCancel');
        Route::put('timeEdit/{id}', 'Mod_HR\Timesched\Timeschedcontroller@timeEdit');
      });

      Route::prefix('TimeSChedulePay')->group(function () {
        Route::get('Timeschedule/{id}', 'Mod_HR\Timesched\TimeSchedPayController@Timeschedule');
        Route::get('showShifts', 'Mod_HR\Timesched\TimeSchedPayController@showShifts');
        Route::post('storeTime', 'Mod_HR\Timesched\TimeSchedPayController@storeTime');
        Route::put('timeCancel/{id}', 'Mod_HR\Timesched\TimeSchedPayController@timeCancel');
        Route::put('timeEdit/{id}', 'Mod_HR\Timesched\TimeSchedPayController@timeEdit');
        Route::get('getpayrollMakerList', 'Mod_HR\Timesched\TimeSchedPayController@getpayrollMakerList');
      });
      Route::prefix('sworn')->group(function () {
        Route::get('basicinfo', 'Mod_HR\SSALNW\SwornController@basicinfo');
        Route::post('Swornstore', 'Mod_HR\SSALNW\SwornController@Swornstore');
        Route::get('getsworn', 'Mod_HR\SSALNW\SwornController@getsworn');
        Route::put('sworncancel/{id}', 'Mod_HR\SSALNW\SwornController@sworncancel');
        Route::put('Edit/{id}', 'Mod_HR\SSALNW\SwornController@Edit');
        Route::post('print', 'Mod_HR\SSALNW\SwornController@print');
      });
      Route::prefix('individual')->group(function () {
        Route::get('show', 'Mod_HR\individual\individualController@show');
        Route::get('GetEmpName', 'Mod_HR\individual\individualController@GetEmpName');
        Route::post('store', 'Mod_HR\individual\individualController@store');
        Route::get('getReqName', 'Mod_HR\individual\individualController@getReqName');
        Route::put('cancel/{id}', 'Mod_HR\individual\individualController@cancel');
        Route::put('Edit/{id}', 'Mod_HR\individual\individualController@Edit');
        Route::post('print', 'Mod_HR\individual\individualController@print');
        Route::post('printpayslip', 'Mod_HR\individual\individualController@printpayslip');
        Route::post('ITC', 'Mod_HR\individual\individualController@ITC');
        Route::get('reference', 'Mod_HR\individual\individualController@reference');
        Route::get('HeadList', 'Mod_HR\individual\individualController@HeadList');
        Route::get('HeadListApproved', 'Mod_HR\individual\individualController@HeadListApproved');
        Route::post('HeadApprovalApproved', 'Mod_HR\individual\individualController@HeadApprovalApproved');
        Route::post('HeadApprovalDisApproved', 'Mod_HR\individual\individualController@HeadApprovalDisApproved');
      });
      Route::prefix('Overtime')->group(function () {
        Route::get('getDepartment', 'Mod_HR\Overtime\overtimeAppController@getDepartment');
        // Route::get('GetEmpName/{id}', 'Mod_HR\Overtime\overtimeAppController@GetEmpName');
        // Route::get('getEmpRequest/{id}', 'Mod_HR\Overtime\overtimeAppController@getEmpRequest');
        Route::get('getref', 'Mod_HR\Overtime\overtimeAppController@getref');
        Route::get('getEmpPreparedby', 'Mod_HR\Overtime\overtimeAppController@getEmpPreparedby');
        Route::get('getEmpRequest', 'Mod_HR\Overtime\overtimeAppController@getEmpRequest');
        Route::get('GetEmpName', 'Mod_HR\Overtime\overtimeAppController@GetEmpName');
        Route::post('memoStore', 'Mod_HR\Overtime\overtimeAppController@memoStore');
        Route::post('OverStore', 'Mod_HR\Overtime\overtimeAppController@OverStore');
        Route::get('getOvertime', 'Mod_HR\Overtime\overtimeAppController@getOvertime');
        Route::get('getCert', 'Mod_HR\Overtime\overtimeAppController@getCert');
        Route::put('OverTimeCancel/{id}', 'Mod_HR\Overtime\overtimeAppController@OverTimeCancel');
        Route::put('Edit/{id}', 'Mod_HR\Overtime\overtimeAppController@Edit');
        Route::post('print', 'Mod_HR\Overtime\overtimeAppController@print');
        Route::post('printOvertime', 'Mod_HR\Overtime\overtimeAppController@printOvertime');


        Route::get('OvertimeMayorApproval', 'Mod_HR\Overtime\overtimeAppController@OvertimeMayorApproval');
        Route::get('OvertimeHeadList', 'Mod_HR\Overtime\overtimeAppController@OvertimeHeadList');
        Route::get('BudgetControlApproval', 'Mod_HR\Overtime\overtimeAppController@BudgetControlApproval');
        Route::get('OvertimeHeadApproval', 'Mod_HR\Overtime\overtimeAppController@OvertimeHeadApproval');
        Route::get('OvertimeRecommended', 'Mod_HR\Overtime\overtimeAppController@OvertimeRecommended');
        Route::get('OvertimeHeadfundapproval', 'Mod_HR\Overtime\overtimeAppController@OvertimeHeadfundapproval');
        Route::get('OvertimeHeadListApproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeHeadListApproved');
        Route::post('OvertimeHeadApprovalApproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeHeadApprovalApproved');
        Route::post('OvertimeHeadApprovalDisapproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeHeadApprovalDisapproved');
        Route::post('OvertimeHeadApprovalNoted', 'Mod_HR\Overtime\overtimeAppController@OvertimeHeadApprovalNoted');
        Route::post('OvertimeforHeadNotedDisapproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeforHeadNotedDisapproved');
        Route::post('OvertimeforMayor', 'Mod_HR\Overtime\overtimeAppController@OvertimeforMayor');
        Route::get('OvertimeMayorApprovedList', 'Mod_HR\Overtime\overtimeAppController@OvertimeMayorApprovedList');
        Route::post('OvertimeforMayorDisapproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeforMayorDisapproved');
        Route::post('OvertimeAppropriation', 'Mod_HR\Overtime\overtimeAppController@OvertimeAppropriation');
        Route::post('OvertimeBudgetCntrlApproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeBudgetCntrlApproved');
        Route::post('OvertimeRecommendedApproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeRecommendedApproved');
        Route::post('OvertimefundApproved', 'Mod_HR\Overtime\overtimeAppController@OvertimefundApproved');
        Route::get('OvertimeAppropriationApprovedList', 'Mod_HR\Overtime\overtimeAppController@OvertimeAppropriationApprovedList');
        Route::get('budgetControlApprovedList', 'Mod_HR\Overtime\overtimeAppController@budgetControlApprovedList');
        Route::get('OvertimeAsFundApprovedList', 'Mod_HR\Overtime\overtimeAppController@OvertimeAsFundApprovedList');
        Route::get('OvertimeRecommendApprovedList', 'Mod_HR\Overtime\overtimeAppController@OvertimeRecommendApprovedList');
        Route::post('OvertimebudgetCntrlDisapproved', 'Mod_HR\Overtime\overtimeAppController@OvertimebudgetCntrlDisapproved');
        Route::post('OvertimeAppropriationDisapproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeAppropriationDisapproved');
        Route::post('OvertimeRecommendedDisapproved', 'Mod_HR\Overtime\overtimeAppController@OvertimeRecommendedDisapproved');
        Route::post('OvertimefundDisapproved', 'Mod_HR\Overtime\overtimeAppController@OvertimefundDisapproved');



      });
      Route::prefix('Training')->group(function () {
        Route::get('getDepartment', 'Mod_HR\training_SchedController@getDepartment');
        Route::get('GetEmpName', 'Mod_HR\training_SchedController@GetEmpName');
        Route::post('Store', 'Mod_HR\training_SchedController@Store');
        Route::post('StoreForced', 'Mod_HR\training_SchedController@StoreForced');
        Route::get('list', 'Mod_HR\training_SchedController@list');
        Route::get('listForcedLeave', 'Mod_HR\training_SchedController@listForcedLeave');
        Route::get('getCert', 'Mod_HR\training_SchedController@getCert');
        Route::put('OverTimeCancel/{id}', 'Mod_HR\training_SchedController@OverTimeCancel');
        Route::put('Edit/{id}', 'Mod_HR\training_SchedController@Edit');
        Route::get('getRef/{id}', 'Mod_HR\training_SchedController@getRef');
        Route::post('print', 'Mod_HR\training_SchedController@print');
        Route::get('checkDateApply', 'Mod_HR\training_SchedController@checkDateApply');
        Route::put('cancel/{id}', 'Mod_HR\training_SchedController@cancel');
      });
      Route::prefix('OvertimeCert')->group(function () {
        Route::get('getDepartment', 'Mod_HR\OvertimeCert\overtimeCertController@getDepartment');
        Route::get('GetName', 'Mod_HR\OvertimeCert\overtimeCertController@GetName');
        Route::post('OvertimeStore', 'Mod_HR\OvertimeCert\overtimeCertController@OvertimeStore');
        Route::get('getovertCert', 'Mod_HR\OvertimeCert\overtimeCertController@getovertCert');
        Route::put('OverTimeCancel/{id}', 'Mod_HR\OvertimeCert\overtimeCertController@OverTimeCancel');
        Route::put('Edit/{id}', 'Mod_HR\OvertimeCert\overtimeCertController@Edit');
        Route::post('print', 'Mod_HR\OvertimeCert\overtimeCertController@print');
        Route::get('getCert', 'Mod_HR\OvertimeCert\overtimeCertController@getCert');
        Route::get('getRef', 'Mod_HR\OvertimeCert\overtimeCertController@getRef');
      });

      Route::prefix('Travel')->group(function () {
        Route::get('getDepartment', 'Mod_HR\Travel\travelOrderController@getDepartment');
        Route::get('GetName', 'Mod_HR\Travel\travelOrderController@GetName');
        Route::get('GetPurpose', 'Mod_HR\Travel\travelOrderController@GetPurpose');
        Route::get('getDept', 'Mod_HR\Travel\travelOrderController@getDept');
        Route::post('store', 'Mod_HR\Travel\travelOrderController@store');
        Route::get('getovertCert', 'Mod_HR\Travel\travelOrderController@getovertCert');
        Route::put('cancel/{id}', 'Mod_HR\Travel\travelOrderController@cancel');
        Route::put('Edit/{id}', 'Mod_HR\Travel\travelOrderController@Edit');
        Route::post('print_TO', 'Mod_HR\Travel\travelOrderController@print_TO');
        Route::get('reference', 'Mod_HR\Travel\travelOrderController@reference');

        Route::get('getTravel', 'Mod_HR\Travel\travelOrderController@getTravel');
      });

      Route::prefix('Time Schedule')->group(function () {
        Route::get('showShifts', 'Mod_HR\Timeschedcontroller@showShifts');
        Route::get('Timeschedule', 'Mod_HR\Timeschedcontroller@Timeschedule');
        Route::post('storeTime', 'Mod_HR\Timeschedcontroller@storeTime');
        Route::put('timeCancel/{id}', 'Mod_HR\Timeschedcontroller@timeCancel');
        Route::put('timeEdit/{id}', 'Mod_HR\Timeschedcontroller@timeEdit');
        Route::get('getpayrollMakerList', 'Mod_HR\Timeschedcontroller@getpayrollMakerList');
      });
      //    Route::prefix('TimeSchedPay')->group(function () {
      //     Route::get('showShifts', 'Mod_HR\TimeSchedPayController@showShifts');
      //     Route::get('Timeschedule/{id}', 'Mod_HR\TimeSchedPayController@Timeschedule');
      //     Route::post('storeTime', 'Mod_HR\TimeSchedPayController@storeTime');
      //     Route::put('timeCancel/{id}', 'Mod_HR\TimeSchedPayController@timeCancel');
      //     Route::put('timeEdit/{id}', 'Mod_HR\TimeSchedPayController@timeEdit');
      //     Route::get('getpayrollMakerList', 'Mod_HR\TimeSchedPayController@getpayrollMakerList');

      //   });

      Route::prefix('Force Leave')->group(function () {
        Route::get('showleave', 'Mod_HR\Forceleaveentrycontroller@showleave');
        Route::get('getref', 'Mod_HR\Forceleaveentrycontroller@getref');
        Route::get('show', 'Mod_HR\Forceleaveentrycontroller@show');
        Route::post('store', 'Mod_HR\Forceleaveentrycontroller@store');
        Route::get('showlist', 'Mod_HR\Forceleaveentrycontroller@showlist');
        Route::post('print', 'Mod_HR\Forceleaveentrycontroller@print');
        Route::get('cancel/{leave_id}', 'Mod_HR\Forceleaveentrycontroller@cancel');
      });

      Route::prefix('IncidentReport')->group(function () {
        Route::get('incidentHeadList', 'Mod_HR\IncidentReport\IncidentReportController@incidentHeadList');
        Route::get('incidentForHeadApproval', 'Mod_HR\IncidentReport\IncidentReportController@incidentForHeadApproval');
        Route::get('incidentHeadListApproved', 'Mod_HR\IncidentReport\IncidentReportController@incidentHeadListApproved');

        Route::post('incidentForHeadApprovalApproved', 'Mod_HR\IncidentReport\IncidentReportController@incidentForHeadApprovalApproved');
        Route::post('incidentForHeadApprovalDisapproved', 'Mod_HR\IncidentReport\IncidentReportController@incidentForHeadApprovalDisapproved');
        Route::post('incidentForHeadApprovalNoted', 'Mod_HR\IncidentReport\IncidentReportController@incidentForHeadApprovalNoted');
        Route::post('incidentForHeadNotedDisapproved', 'Mod_HR\IncidentReport\IncidentReportController@incidentForHeadNotedDisapproved');
        Route::post('incidentForMayor', 'Mod_HR\IncidentReport\IncidentReportController@incidentForMayor');
        Route::get('incidentMayorApprovedList', 'Mod_HR\IncidentReport\IncidentReportController@incidentMayorApprovedList');
        Route::post('incidentForMayorDisapproved', 'Mod_HR\IncidentReport\IncidentReportController@incidentForMayorDisapproved');
        Route::post('incidentAction', 'Mod_HR\IncidentReport\IncidentReportController@incidentAction');
        Route::get('incidentActionApprovedList', 'Mod_HR\IncidentReport\IncidentReportController@incidentActionApprovedList');

        Route::post('incidentActionDisapproved', 'Mod_HR\IncidentReport\IncidentReportController@incidentActionDisapproved');
        Route::get('incidentReports', 'Mod_HR\IncidentReport\IncidentReportController@incidentReports');
        Route::post('storeIncident', 'Mod_HR\IncidentReport\IncidentReportController@storeIncident');
        Route::put('cancelReport/{ir_id}', 'Mod_HR\IncidentReport\IncidentReportController@cancelReport');
        Route::post('print', 'Mod_HR\IncidentReport\IncidentReportController@print');
        // Route::get('reference/{ir_id}', 'Mod_HR\IncidentReport\IncidentReportController@reference');
        Route::get('reference', 'Mod_HR\IncidentReport\IncidentReportController@reference');
        Route::get('remarks', 'Mod_HR\IncidentReport\IncidentReportController@remarks');
        Route::get('getScheduleLog/{date}', 'Mod_HR\IncidentReport\IncidentReportController@getScheduleLog');

        Route::get('overtimeReports', 'Mod_HR\IncidentReport\IncidentReportController@overtimeReports');
        Route::post('storeOvertime', 'Mod_HR\IncidentReport\IncidentReportController@storeOvertime');
        Route::put('cancelOvertime/{ir_id}', 'Mod_HR\IncidentReport\IncidentReportController@cancelOvertime');
      });


      Route::prefix('Employee_PDS')->group(function () {
        Route::post('print', 'Mod_HR\pds\basicinfocontroller@print');
        Route::get('basicinfo', 'Mod_HR\pds\basicinfocontroller@basicinfo');
        Route::get('approvedList', 'Mod_HR\pds\basicinfocontroller@approvedList');
        Route::get('getEdited', 'Mod_HR\pds\basicinfocontroller@getEdited');
        Route::post('disapproveData', 'Mod_HR\pds\basicinfocontroller@disapproveData');
        Route::post('store', 'Mod_HR\pds\basicinfocontroller@store');
        Route::post('approveData', 'Mod_HR\pds\basicinfocontroller@approveData');
        Route::get('familybackground', 'Mod_HR\pds\familybackgroundcontroller@familybackground');
        Route::post('storeFamily', 'Mod_HR\pds\familybackgroundcontroller@storeFamily');
        //   Route::post('print','Mod_HR\pds\familybackgroundcontroller@print');




        Route::get('employmentinformation', 'Mod_HR\pds\employmentinfocontroller@employmentinformation');
        Route::post('storeEmp_info', 'Mod_HR\pds\employmentinfocontroller@storeEmp_info');

        Route::get('showDependent', 'Mod_HR\pds\dependentController@showDependent');
        Route::post('dependentStore', 'Mod_HR\pds\dependentController@dependentStore');
        Route::put('dependCancel/{id}', 'Mod_HR\pds\dependentController@dependCancel');

        Route::get('educationbackground', 'Mod_HR\pds\educationbackgroundcontroller@educationbackground');
        Route::post('storeEduc', 'Mod_HR\pds\educationbackgroundcontroller@storeEduc');
        Route::put('EducCancel/{id}', 'Mod_HR\pds\educationbackgroundcontroller@EducCancel');

        Route::get('civilserviceeligibility', 'Mod_HR\pds\civilservicecontroller@civilserviceeligibility');
        Route::post('storeCivil', 'Mod_HR\pds\civilservicecontroller@storeCivil');
        Route::put('civilCancel/{id}', 'Mod_HR\pds\civilservicecontroller@civilCancel');



        Route::get('workexperience', 'Mod_HR\pds\workexperiencecontroller@workexperience');
        Route::post('storeWorkexp', 'Mod_HR\pds\workexperiencecontroller@storeWorkexp');
        Route::put('workexperienceCancel/{id}', 'Mod_HR\pds\workexperiencecontroller@workexperienceCancel');

        Route::get('voluntarywork', 'Mod_HR\pds\voluntaryworkcontroller@voluntarywork');


        Route::get('trainingprogram', 'Mod_HR\pds\trainingcontroller@trainingprogram');
        Route::post('storePrograms', 'Mod_HR\pds\trainingcontroller@storePrograms');
        Route::put('trainingCancel/{id}', 'Mod_HR\pds\trainingcontroller@trainingCancel');



        Route::get('skillshobbies', 'Mod_HR\pds\skillshobbiescontroller@skillshobbies');
        Route::get('reference', 'Mod_HR\pds\referencecontroller@reference');
        Route::get('salariescontribution', 'Mod_HR\pds\salariescontroller@salariescontribution');
         Route::post('storeSalary', 'Mod_HR\pds\salariescontroller@storeSalary');
        Route::post('storeVol', 'Mod_HR\pds\voluntaryworkcontroller@storeVol');
        Route::put('voluntaryworkCancel/{id}', 'Mod_HR\pds\voluntaryworkcontroller@voluntaryworkCancel');
      });
    });
  });
});
