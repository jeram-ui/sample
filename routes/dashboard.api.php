<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::namespace('Api')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        //Treasury Controller
        Route::prefix('mod_dashboard')->group(function () {
            Route::prefix('bplo')->group(function () {
                Route::get('businessStatus', 'Mod_Dashboard\bploController@businessStatus');
                Route::get('businessCollection', 'Mod_Dashboard\bploController@businessCollection');
                Route::get('businessCollectionCount', 'Mod_Dashboard\bploController@businessCollectionCount');
                Route::get('businessCollectionType', 'Mod_Dashboard\bploController@businessCollectionType');
                Route::get('get_bud_total', 'Mod_Dashboard\bploController@get_bud_total');
                Route::get('get_project_status', 'Mod_Dashboard\bploController@get_project_status');




                Route::get('hr_employee_type', 'Mod_Dashboard\bploController@hr_employee_type');
                Route::get('hr_employee_gender', 'Mod_Dashboard\bploController@hr_employee_gender');
                Route::get('hr_Department', 'Mod_Dashboard\bploController@hr_Department');
                Route::get('hr_age_gap', 'Mod_Dashboard\bploController@hr_age_gap');
                Route::get('ass_deliquency', 'Mod_Dashboard\bploController@ass_deliquency');
                Route::get('ass_app_type', 'Mod_Dashboard\bploController@ass_app_type');
                Route::get('ass_building_count', 'Mod_Dashboard\bploController@ass_building_count');
                Route::get('ass_land_count', 'Mod_Dashboard\bploController@ass_land_count');
                Route::get('get_bud_saao', 'Mod_Dashboard\bploController@get_bud_saao');
                Route::get('get_bud_project', 'Mod_Dashboard\bploController@get_bud_project');
                Route::get('getDailyCollection', 'Mod_Dashboard\bploController@getDailyCollection');
                Route::get('getDailyApplied', 'Mod_Dashboard\bploController@getDailyApplied');
                Route::get('getGraph', 'Mod_Dashboard\bploController@getGraph');
                Route::get('getGraphYearly', 'Mod_Dashboard\bploController@getGraphYearly');
                Route::post('updatePermitStatus', 'Mod_Dashboard\bploController@updatePermitStatus');
                Route::get('getpermitstatus', 'Mod_Dashboard\bploController@getpermitstatus');
                Route::get('getBusinessSize', 'Mod_Dashboard\bploController@getBusinessSize');
                Route::get('businessAppliedStatus', 'Mod_Dashboard\bploController@businessAppliedStatus');
                Route::get('businessAppliedLacking', 'Mod_Dashboard\bploController@businessAppliedLacking');
                Route::get('businessAppliedHold', 'Mod_Dashboard\bploController@businessAppliedHold');
                Route::get('getbusinessAssessmentPaid', 'Mod_Dashboard\bploController@getbusinessAssessmentPaid');
                Route::get('getbusinessAppliedComparative', 'Mod_Dashboard\bploController@getbusinessAppliedComparative');
                Route::get('businessCollectionRunning', 'Mod_Dashboard\bploController@businessCollectionRunning');
                Route::get('getapplicationStatus', 'Mod_Dashboard\bploController@getapplicationStatus');
                Route::get('getapplicationStatusDetails/{id}', 'Mod_Dashboard\bploController@getapplicationStatusDetails');

                Route::get('releasedaging', 'Mod_Dashboard\bploController@releasedaging');
                Route::get('assessmentaging', 'Mod_Dashboard\bploController@assessmentaging');
                Route::get('releasedaging1', 'Mod_Dashboard\bploController@releasedaging1');
                Route::get('topTaxPayer', 'Mod_Dashboard\bploController@topTaxPayer');

            });
            Route::prefix('Treasury')->group(function () {
                Route::get('brgyShare', 'Mod_Dashboard\TreasuryController@brgy_share');
                Route::get('proceeds', 'Mod_Dashboard\TreasuryController@proceeds');

            });
            Route::prefix('Cenro')->group(function () {
                Route::get('getRTS', 'Mod_Dashboard\CENROController@getRTS');

            });
            Route::prefix('Dengue_monitoring')->group(function () {
                Route::get('showmonitoring', 'Mod_Dashboard\denguemonitoringcontroller@showmonitoring');
                Route::get('showbrgy', 'Mod_Dashboard\denguemonitoringcontroller@showbrgy');
                Route::get('showyear', 'Mod_Dashboard\denguemonitoringcontroller@showyear');

            });
            Route::prefix('HR_bloodtype')->group(function () {
                Route::get('hr_employee_bloodtype', 'Mod_Dashboard\HRbloodtypecontroller@hr_employee_bloodtype');
            });
            Route::prefix('HR_empaddress')->group(function () {
                Route::get('hr_employee_barangay', 'Mod_Dashboard\HRbloodtypecontroller@hr_employee_barangay');
            });
            Route::prefix('HR_empaddress')->group(function () {
                Route::get('hr_employee_yearsinservice', 'Mod_Dashboard\HRbloodtypecontroller@hr_employee_yearsinservice');
            });
            Route::prefix('LiveBirth')->group(function () {
                Route::get('livebirth_monitoring', 'Mod_Dashboard\LCR_Dashboardcontroller@livebirth_monitoring');
            });

            Route::prefix('LiveBirth')->group(function () {
                Route::get('livebirth_monitoring_perbarangay', 'Mod_Dashboard\LCR_Dashboardcontroller@livebirth_monitoring_perbarangay');
            });

            Route::prefix('DeathMonitoring')->group(function () {
                Route::get('death_monitoring', 'Mod_Dashboard\LCR_Dashboardcontroller@death_monitoring');
            });

            Route::prefix('DeathMonitoring')->group(function () {
                Route::get('death_monitoring_monthly', 'Mod_Dashboard\LCR_Dashboardcontroller@death_monitoring_monthly');
            });
            Route::prefix('DeathMonitoring')->group(function () {
                Route::get('death_monitoring_perbarangay', 'Mod_Dashboard\LCR_Dashboardcontroller@death_monitoring_perbarangay');
            });

            Route::prefix('MarriageMonitoring')->group(function () {
                Route::get('marriage_monitoring_monthly', 'Mod_Dashboard\LCR_Dashboardcontroller@marriage_monitoring_monthly');
            });

            Route::prefix('MarriageMonitoring')->group(function () {
                Route::get('type_marriage_monitoring_monthly', 'Mod_Dashboard\LCR_Dashboardcontroller@type_marriage_monitoring_monthly');
            });

            Route::prefix('SanitaryMonitoring')->group(function () {
                Route::get('sanitary_businessmonitoring', 'Mod_Dashboard\Sanitary_Dashboardcontroller@sanitary_businessmonitoring');
                Route::get('sanitary_business_monthlymonitoring', 'Mod_Dashboard\Sanitary_Dashboardcontroller@sanitary_business_monthlymonitoring');
            });

            Route::prefix('projectMonitoring')->group(function () {
                Route::get('show_project', 'Mod_Dashboard\projectmonitoringcontroller@show_project');
                Route::get('show_proj', 'Mod_Dashboard\projectmonitoringcontroller@show_proj');
                Route::get('show_Attach/{id}', 'Mod_Dashboard\projectmonitoringcontroller@show_Attach');
            });
            Route::prefix('statusbudget')->group(function () {
                Route::get('show_statusBudget', 'Mod_Dashboard\statusBudget@show_statusBudget');
                Route::get('show_statusAppropriateByClass', 'Mod_Dashboard\statusBudget@show_statusAppropriateByClass');
                Route::get('show_proj', 'Mod_Dashboard\projectmonitoringcontroller@show_proj');
                Route::get('show_Attach/{id}', 'Mod_Dashboard\projectmonitoringcontroller@show_Attach');
            });
            Route::prefix('statusbudget_byClass')->group(function () {
                Route::get('show_statusByClass', 'Mod_Dashboard\statusbudget_byClass@show_statusByClass');
                Route::get('show_statusByActivity', 'Mod_Dashboard\statusbudget_byClass@show_statusByActivity');
                Route::get('show_statusByDepartment', 'Mod_Dashboard\statusbudget_byClass@show_statusByDepartment');

            });
            Route::prefix('rating')->group(function () {
                Route::get('showRating', 'Mod_Dashboard\RatingController@showRating');
                Route::get('GetRatings', 'Mod_Dashboard\RatingController@GetRatings');
                Route::get('getDepartment', 'Mod_Dashboard\RatingController@getDepartment');
                Route::post('store', 'Mod_Dashboard\RatingController@store');
                Route::get('getAssistedName', 'Mod_Dashboard\RatingController@getAssistedName');


            });
        });
    });
});
