<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

Route::namespace('Api')->group(function () {
    // start of routes
    // Route::post('auth/login', 'AuthController@login');

    Route::post('/login', 'LoginController@login');
    Route::post('/getToken', 'LoginController@login');
    // Route::post('/register', 'RegisterController@register');
    // Route::post('auth/login', 'AuthController@login');
    Route::post('auth/google', 'LoginController@redirectToGoogle');
    Route::get('auth/google/callback', 'LoginController@handleGoogleCallback');

    Route::post('register', 'AuthController@create');
    Route::get('email/verify/{id}/{hash}', 'VerificationApiController@verify')->name('verification.verify');
    Route::post('storeRegister', 'AuthController@create');
    Route::post('sendToken', 'AuthController@sendToken');
    Route::post('tokenValidate', 'AuthController@tokenValidate');
    Route::post('resetPassword', 'AuthController@resetPassword');
    Route::post('billSched', 'Admin/MeterController@billSched');



    // create or update a subscription for a user
    Route::post('subscription', 'SubscriptionController@store');

    // delete a subscription for a user
    Route::post('subscription/delete', 'SubscriptionController@destroy');


    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('ChangePassword', 'AuthController@ChangePassword');
        Route::post('generateACCOunt', 'AuthController@generateACCOunt');
        Route::get('printPriority/{id}', 'GlobalController@printPriority');
        Route::get('printPriority1/{id}/{category}', 'GlobalController@printPriority1');
        Route::get('StaledChecks', 'GlobalController@StaledChecks');
        Route::get('printPriorityBlank', 'GlobalController@printPriorityBlank');
        Route::prefix('queuing')->group(function () {
            Route::get('/getFacility', 'QueuingController@getFacility');
            Route::get('/getVaccinator', 'QueuingController@getVaccinator');
            Route::get('/getDailyVaccinated/{date}', 'QueuingController@getDailyVaccinated');
            Route::post('/store', 'QueuingController@store');
            Route::get('/show/{id}', 'QueuingController@show');
            Route::post('/updateTarget', 'QueuingController@updateTarget');
            Route::get('/getAllPriority', 'QueuingController@getAllPriority');
        });
        Route::prefix('Global')->group(function () {

            Route::get('/getEmployee', 'GlobalController@getEmployee');
            Route::get('/getAddFees', 'GlobalController@getAddFees');
            Route::get('/getAccountForBill', 'GlobalController@getAccountForBill');
            Route::post('/insertFees', 'GlobalController@insertFees');
            Route::get('/getAddFeesDelete/{id}', 'GlobalController@getAddFeesDelete');
            Route::get('/getORPosting', 'GlobalController@getORPosting');
            Route::post('/upload', 'GlobalController@upload');
            Route::get('/uploaded', 'GlobalController@uploaded');
            Route::get('/documentView/{id}', 'GlobalController@documentView');
            Route::get('/uploadRemove/{id}', 'GlobalController@uploadRemove');
            Route::get('/getListPerDepartment', 'GlobalController@getListPerDepartment');
            Route::get('/getProjectRegister', 'GlobalController@getProjectRegister');
        });
        Route::prefix('Reader')->group(function () {
            Route::get('/show/{id}', 'Qr\readerController@show');
            Route::post('/validated', 'Qr\readerController@validated');
            Route::post('/validatedBag', 'Qr\readerController@validatedBag');
            Route::get('/history', 'Qr\readerController@history');
            Route::get('/details/{id}', 'Qr\readerController@details');
            Route::get('/dashboard', 'Qr\readerController@dashboard');
            Route::get('/dashboardEcobag', 'Qr\readerController@dashboardEcobag');
            Route::get('/barangay', 'Qr\readerController@barangay');
            Route::get('/NamePerBarangay/{id}', 'Qr\readerController@NamePerBarangay');
            Route::get('/Print/{id}', 'Qr\readerController@printSample');
            Route::get('/LeaderPerBarangay', 'Qr\readerController@LeaderPerBarangay');
            Route::get('/printSampleIndividual/{id}', 'Qr\readerController@printSampleIndividual');
            Route::get('/PrintMultiple', 'Qr\readerController@PrintMultiple');
            Route::get('/getPurok/{id}', 'Qr\readerController@getPurok');
            Route::post('/saveMember', 'Qr\readerController@saveMember');
            Route::get('/getMember/{id}', 'Qr\readerController@getMember');
            Route::get('/GetRelationship', 'Qr\readerController@GetRelationship');
            Route::get('/getRef/{id}', 'Qr\readerController@getRef');
            Route::post('/UpdateMember', 'Qr\readerController@UpdateMember');
            Route::get('/removeMember/{id}', 'Qr\readerController@removeMember');
            Route::get('/checkID/{id}', 'Qr\readerController@checkID');
            Route::get('/checkingname', 'Qr\readerController@checkingname');
            Route::get('/Departments', 'Qr\readerController@Departments');
        });
        Route::prefix('Dashboard')->group(function () {
            Route::get('/population', 'Qr\dashboardController@showTotal');
            Route::get('/showPopulationCount', 'Qr\dashboardController@showPopulationCount');
            Route::get('/showPopulationList', 'Qr\dashboardController@showPopulationList');
        });
        Route::prefix('Address')->group(function () {
            Route::get('/province', 'GlobalController@province');
            Route::get('/city/{id}', 'GlobalController@city');
            Route::get('/barangay/{id}', 'GlobalController@barangay');
        });

        Route::prefix('Reader')->group(function () {
            Route::post('/saveMember', 'Qr\readerController@saveMember');
            Route::post('/UpdateMember', 'Qr\readerController@UpdateMember');
            Route::get('/removeMember/{id}', 'Qr\readerController@removeMember');
        });

        Route::prefix('Access')->group(function () {
            Route::post('insertform', 'UserAccessController@insertform');
            Route::get('formList/{profile_id}', 'UserAccessController@formList');
            Route::get('formProfile', 'UserAccessController@formProfile');
            Route::post('storeProfile', 'UserAccessController@storeProfile');
            Route::post('userProfile', 'UserAccessController@userProfile');
            Route::post('profileStore', 'UserAccessController@profileStore');
            Route::get('userProfileAccess/{id}', 'UserAccessController@userProfileAccess');
            Route::get('departmentList', 'UserAccessController@departmentList');
            Route::post('DepartmentApproval', 'UserAccessController@DepartmentApproval');
        });
        Route::prefix('budgetContrl')->group(function () {

            Route::post('store', 'budgetcontrController@store');
            Route::get('getBudgetContrl', 'budgetcontrController@getBudgetContrl');
            Route::put('removing/{id}', 'budgetcontrController@removing');
            Route::get('getDepartment', 'budgetcontrController@getDepartment');
            Route::get('getEmpRequest', 'budgetcontrController@getEmpRequest');
        });

        Route::prefix('budgetContrlJO')->group(function () {

            Route::post('store', 'budgetcontrJoController@store');
            Route::get('getBudgetContrl', 'budgetcontrJoController@getBudgetContrl');
            Route::put('removing/{id}', 'budgetcontrJoController@removing');
            Route::get('getDepartment', 'budgetcontrJoController@getDepartment');
            Route::get('getEmpRequest', 'budgetcontrJoController@getEmpRequest');
        });
        Route::prefix('MayorApprvd')->group(function () {

            Route::post('store', 'mayorApprovalController@store');
            Route::get('getlist', 'mayorApprovalController@getlist');
            Route::put('removing/{id}', 'mayorApprovalController@removing');
            Route::get('getDepartment', 'mayorApprovalController@getDepartment');
            Route::get('getEmpRequest', 'mayorApprovalController@getEmpRequest');
        });

        Route::prefix('BusinessOnline')->group(function () {
            Route::get('/getBusinessList', 'Mod_online\onlineController@getBusinessList');
            Route::post('/approvedBusiness', 'Mod_online\onlineController@approvedBusiness');
            Route::post('/insertBusiness', 'Mod_online\onlineController@insertBusiness');
            Route::get('/getAssesment', 'Mod_online\onlineController@getAssesment');
            Route::post('/disapproved', 'Mod_online\onlineController@disapproved');
            Route::get('/getMasterList/{id}', 'Mod_online\onlineController@getMasterList');
            Route::get('/getApplicant', 'Mod_online\onlineController@getApplicant');
            Route::post('/setScheduleDate', 'Mod_online\onlineController@setScheduleDate');
            Route::get('/getScheduleDate', 'Mod_online\onlineController@getScheduleDate');
        });
        Route::prefix('BusinessList')->group(function () {
            Route::post('store', 'Business\NewBusinessController@storeBusinesslist');
        });
        Route::get('user', 'AuthController@user');
        Route::post('auth/logout', 'AuthController@logout');
        Route::get('auth/check', 'AuthController@check');
        Route::prefix('AnnualInsp')->group(function () {
            Route::post('/store', 'OBO\AnnualInspController@store');
            Route::get('/show', 'OBO\AnnualInspController@show');
            Route::post('/prints', 'OBO\AnnualInspController@prints');
            Route::post('/print', 'OBO\AnnualInspController@print');
            Route::get('/edit/{id}', 'OBO\AnnualInspController@edit');
            Route::get('/ref', 'OBO\AnnualInspController@ref');
            Route::get('/char_occupancy', 'OBO\AnnualInspController@getchar_of_occupancy');
            Route::get('/occu_class/{id}', 'OBO\AnnualInspController@getoccu_class');
            Route::get('/employeeIssuance/{id}', 'OBO\AnnualInspController@employeeIssuance');
            Route::get('/delete/{id}', 'OBO\AnnualInspController@delete');
            Route::post('/local', 'OBO\AnnualInspController@local');
        });
        Route::prefix('Occupancy')->group(function () {
            Route::post('/store', 'OBO\OccupancyController@store');
            Route::get('/show', 'OBO\OccupancyController@show');
            Route::post('/prints', 'OBO\OccupancyController@prints');
            Route::post('/print', 'OBO\OccupancyController@print');
            Route::get('/edit/{id}', 'OBO\OccupancyController@edit');
            Route::get('/ref', 'OBO\OccupancyController@ref');
            Route::get('/char_occupancy', 'OBO\OccupancyController@getchar_of_occupancy');
            Route::get('/occu_class/{id}', 'OBO\OccupancyController@getoccu_class');
            Route::get('/employeeIssuance/{id}', 'OBO\OccupancyController@employeeIssuance');
            Route::get('/delete/{id}', 'OBO\OccupancyController@delete');
            Route::post('/local', 'OBO\OccupancyController@local');
        });
        Route::prefix('ranz')->group(function () {
            Route::post('/store', 'sample\sampleController@store');
            Route::get('/show', 'sample\sampleController@show');
            Route::post('/print', 'sample\sampleController@print');
            Route::get('/edit/{id}', 'sample\sampleController@edit');
        });
        Route::prefix('Medical')->group(function () {
            Route::post('store', 'Medical\medicalController@store');
            Route::post('updateExamination', 'Medical\medicalController@updateExamination');
            Route::get('editData/{id}', 'Medical\medicalController@editData');
            Route::post('/delete', 'Medical\medicalController@delete');
            Route::get('/edit/{id}', 'Medical\medicalController@edit');
            Route::get('/ref', 'Medical\medicalController@ref');
            Route::get('displayData', 'Medical\medicalController@displayData');
            Route::get('filterData', 'Medical\medicalController@filterData');
            Route::post('printList', 'Medical\medicalController@printList');
            Route::post('printDtl', 'Medical\medicalController@printDtl');
        });
        Route::prefix('Cadaver')->group(function () {
            Route::post('store', 'Cadaver\cadaverController@store');
            Route::get('occurenceName', 'Cadaver\cadaverController@occurenceName');
            Route::post('occurenceStore', 'Cadaver\cadaverController@occurenceStore');
            Route::get('/occurenceCancel/{id}', 'Cadaver\cadaverController@occurenceCancel');
            Route::post('updateInspection', 'Cadaver\cadaverController@updateInspection');
            Route::get('editData/{id}', 'Cadaver\cadaverController@editData');
            Route::post('/delete', 'Cadaver\cadaverController@delete');
            Route::get('/edit/{id}', 'Cadaver\cadaverController@edit');

            Route::get('/ref', 'Cadaver\cadaverController@ref');
            Route::get('displayData', 'Cadaver\cadaverController@displayData');
            Route::get('filterData', 'Cadaver\cadaverController@filterData');
            Route::post('printList', 'Cadaver\cadaverController@printList');
            Route::post('printDtl', 'Cadaver\cadaverController@printDtl');
        });
        Route::prefix('Exhumation')->group(function () {
            Route::post('store', 'Exhumation\exhumationController@store');
            Route::get('occurenceName', 'Exhumation\exhumationController@occurenceName');
            Route::get('editData/{id}', 'Exhumation\exhumationController@editData');
            Route::post('/delete', 'Exhumation\exhumationController@delete');
            Route::get('/edit/{id}', 'Exhumation\exhumationController@edit');
            Route::get('/ref', 'Exhumation\exhumationController@ref');
            Route::get('displayData', 'Exhumation\exhumationController@displayData');
            Route::get('filterData', 'Exhumation\exhumationController@filterData');
            Route::post('printList', 'Exhumation\exhumationController@printList');
            Route::post('printDtl', 'Exhumation\exhumationController@printDtl');
        });
        Route::prefix('Death')->group(function () {
            Route::post('store', 'Death\deathController@store');
            Route::get('editData/{id}', 'Death\deathController@editData');
            Route::post('/delete', 'Death\deathController@delete');
            Route::get('/edit/{id}', 'Death\deathController@edit');
            Route::get('/ref', 'Death\deathController@ref');
            Route::get('filterData', 'Death\deathController@filterData');
            Route::post('printList', 'Death\deathController@printList');
            Route::post('printDtl', 'Death\deathController@printDtl');
        });
        Route::prefix('MedicoLegal')->group(function () {
            Route::post('store', 'MedicoLegal\medicolegalController@store');
            Route::post('updateExamination', 'MedicoLegal\medicolegalController@updateExamination');
            Route::get('editData/{id}', 'MedicoLegal\medicolegalController@editData');
            Route::post('/delete', 'MedicoLegal\medicolegalController@delete');
            Route::get('/edit/{id}', 'MedicoLegal\medicolegalController@edit');
            Route::get('/ref', 'MedicoLegal\medicolegalController@ref');
            Route::get('filterData', 'MedicoLegal\medicolegalController@filterData');
            Route::post('printList', 'MedicoLegal\medicolegalController@printList');
            Route::post('printDtl', 'MedicoLegal\medicolegalController@printDtl');
        });
        Route::prefix('WaterPotability')->group(function () {
            Route::post('store', 'WaterPotability\waterpotabilityController@store');
            Route::post('updateInspection', 'WaterPotability\waterpotabilityController@updateInspection');
            Route::get('Activity', 'WaterPotability\waterpotabilityController@Activity');
            Route::get('getWaterSourceList', 'WaterPotability\waterpotabilityController@getWaterSourceList');
            Route::get('editData/{id}', 'WaterPotability\waterpotabilityController@editData');
            Route::post('/delete', 'WaterPotability\waterpotabilityController@delete');
            Route::get('/edit/{id}', 'WaterPotability\waterpotabilityController@edit');
            Route::get('/ref', 'WaterPotability\waterpotabilityController@ref');
            Route::get('filterData', 'WaterPotability\waterpotabilityController@filterData');
            Route::post('printList', 'WaterPotability\waterpotabilityController@printList');
            Route::post('printDtl', 'WaterPotability\waterpotabilityController@printDtl');
        });
        Route::prefix('MissionOrder')->group(function () {
            Route::post('store', 'MissionOrder\missionorderController@store');
            Route::get('purposeDescription', 'MissionOrder\missionorderController@purposeDescription');
            Route::get('editData/{id}', 'MissionOrder\missionorderController@editData');
            Route::post('/delete', 'MissionOrder\missionorderController@delete');
            Route::get('/edit/{id}', 'MissionOrder\missionorderController@edit');
            Route::get('/ref', 'MissionOrder\missionorderController@ref');
            Route::get('filterData', 'MissionOrder\missionorderController@filterData');
            Route::get('displayDetails/{id}', 'MissionOrder\missionorderController@displayDetails');
            Route::get('getBusMasterlist', 'MissionOrder\missionorderController@getBusMasterlist');
            Route::post('printList', 'MissionOrder\missionorderController@printList');
            Route::post('printForm', 'MissionOrder\missionorderController@printForm');
        });
        ///GLOBAL
        Route::get('/getDepartment', 'GlobalController@getDepartment');
        Route::get('/getSOF', 'GlobalController@getSOF');
        Route::get('/getAllDepatmentEmployee', 'GlobalController@getAllDepatmentEmployee');
        Route::get('/globalVariable', 'GlobalController@globalVariable');
        Route::get('/getItem', 'GlobalController@getItem');
        Route::get('/getBusinessForAssessment', 'GlobalController@getBusinessForAssessment');
        Route::get('/getBusinessForInspection', 'GlobalController@getBusinessForInspection');

        Route::get('/getBusinessList', 'GlobalController@getBusinessList');
        Route::get('/getPersonProfileList', 'GlobalController@getPersonProfileList');
        Route::post('/addPersonProfileList', 'GlobalController@addPersonProfileList');

        Route::post('/profileUpdate', 'GlobalController@profileUpdate');
        Route::get('/getProfile/{id}', 'GlobalController@getProfile');
        Route::post('/porfileUpload', 'GlobalController@porfileUpload');
        Route::get('/displaybillingfees', 'GlobalController@displaybillingfees');
        Route::get('/displaybillingfeesCode', 'GlobalController@displaybillingfeesCode');
        Route::get('/businessLocation', 'GlobalController@businessLocation');
        Route::post('/insertReason', 'GlobalController@insertReason');
        Route::post('/formIssuance', 'GlobalController@formIssuance');
        Route::post('/updateLocation', 'GlobalController@updateLocation');
        Route::get('/displaybusinessList', 'GlobalController@displaybusinessList');
        Route::get('/displaypersonList', 'GlobalController@displaypersonList');
        Route::get('/displaytaxDecListOwner', 'GlobalController@displaytaxDecListOwner');
        Route::get('/displayRPTexempt', 'GlobalController@displayRPTexempt');
        Route::get('/displayCertTrueCopy', 'GlobalController@displayCertTrueCopy');
        Route::get('/displaytaxdeclist', 'GlobalController@displaytaxdeclist');
        Route::get('/displaybrgylist', 'GlobalController@displaybrgylist');
        Route::get('/displaycadastrallot', 'GlobalController@displaycadastrallot');
        Route::get('/getAZCol', 'GlobalController@getAZCol');
        Route::get('/getclearancespermits', 'GlobalController@getclearancespermits');
        Route::get('/getkindbusiness', 'GlobalController@getkindbusiness');
        Route::get('/getclassificationfilter', 'GlobalController@getclassification');
        Route::get('/getbusstat', 'GlobalController@getbusstat');
        Route::get('/getApplicationType', 'GlobalController@getApplicationType');
        Route::get('/getStreet', 'GlobalController@getStreet');
        Route::get('/signatoryReport', 'GlobalController@signatoryReport');

        // Profile/Permits
        Route::get('/displayOrdinance', 'GlobalController@displayOrdinance');
        Route::get('/displayCC', 'GlobalController@displayCC');
        Route::get('/displayOrganization', 'GlobalController@displayOrganization');
        Route::get('/displayDepartment', 'GlobalController@displayDepartment');
        Route::get('/getBuildingList', 'GlobalController@getBuildingList');





        Route::apiResource('users', 'UserController')->middleware('permission:' . \App\Laravue\Acl::PERMISSION_USER_MANAGE);
        Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
        Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
        Route::apiResource('roles', 'RoleController')->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
        Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);
        Route::apiResource('permissions', 'PermissionController')->middleware('permission:' . \App\Laravue\Acl::PERMISSION_PERMISSION_MANAGE);

        Route::prefix('Setup')->group(function () {
            Route::get('/displayData', 'Setup\setupController@displayData');
            Route::get('/filterData', 'Setup\setupController@filterData');
            Route::post('/print', 'Setup\setupController@print');
            Route::post('/printMain', 'Setup\setupController@printMain');
            Route::post('/printDtl', 'Setup\setupController@printDtl');
            Route::get('/groupData', 'Setup\setupController@groupData');
            Route::get('/customData', 'Setup\setupController@customData');
            Route::post('/save', 'Setup\setupController@save');
            Route::get('/maxNum', 'Setup\setupController@maxNum');
            Route::get('/editData/{id}', 'Setup\setupController@editData');
            Route::post('/update', 'Setup\setupController@update');
            Route::get('/cancel/{id}', 'Setup\setupController@cancel');
            Route::get('/viewData/{id}', 'Setup\setupController@viewData');
            Route::post('/modify', 'Setup\setupController@modify');
            Route::post('/store', 'Setup\setupController@store');
        });
        // Environmental
        Route::prefix('Environmental')->group(function () {
            Route::get('/displayData', 'Environmental\enviController@displayData');
            Route::get('/filterData', 'Environmental\enviController@filterData');
            Route::post('/printMain', 'Environmental\enviController@printMain');
            Route::get('/businessList', 'Environmental\enviController@businessList');
            Route::post('/save', 'Environmental\enviController@save');
            Route::get('/viewData/{id}', 'Environmental\enviController@viewData');
            Route::get('/editData/{id}', 'Environmental\enviController@editData');
            Route::post('/update', 'Environmental\enviController@update');
            Route::get('/enviCertPrint/{id}', 'Environmental\enviController@enviCertPrint');
            Route::post('/store', 'Environmental\enviController@store');
            Route::get('/cancelData/{id}', 'Environmental\enviController@cancelData');
        });
        // Assessor
        Route::prefix('Assessor')->group(function () {
            Route::get('/displayData', 'Assessor\assessorCertController@displayData');
            Route::get('/filterData', 'Assessor\assessorCertController@filterData');
            Route::post('/printMain', 'Assessor\assessorCertController@printMain');
            Route::post('/save', 'Assessor\assessorCertController@save');
            Route::get('/viewData/{id}', 'Assessor\assessorCertController@viewData');
            Route::get('/editData/{id}', 'Assessor\assessorCertController@editData');
            Route::post('/update', 'Assessor\assessorCertController@update');
            Route::get('/businessList', 'Assessor\assessorCertController@businessList');
            Route::get('/personList', 'Assessor\assessorCertController@personList');
            Route::get('/transNo', 'Assessor\assessorCertController@transNo');
            Route::get('/noLandholdingCertPrint/{id}', 'Assessor\assessorCertController@noLandholdingCertPrint');
            Route::post('/store', 'Assessor\assessorCertController@store');
            Route::get('/cancelData/{id}', 'Assessor\assessorCertController@cancelData');
            //Newly Assessed
            Route::get('/displayDataNewlyAssessed', 'Assessor\assessorCertController@displayDataNewlyAssessed');
            Route::get('/filterDataNewlyAssessed', 'Assessor\assessorCertController@filterDataNewlyAssessed');
            Route::post('/printMainNewlyAssessed', 'Assessor\assessorCertController@printMainNewlyAssessed');
            Route::get('/viewDataNewlyAssessed/{id}', 'Assessor\assessorCertController@viewDataNewlyAssessed');
            Route::get('/editDataNewlyAssessed/{id}', 'Assessor\assessorCertController@editDataNewlyAssessed');
            Route::get('/newlyAssessedCertPrint/{id}', 'Assessor\assessorCertController@newlyAssessedCertPrint');
            // No Revision
            Route::get('/displayDataNoRevision', 'Assessor\assessorCertController@displayDataNoRevision');
            Route::get('/filterDataNoRevision', 'Assessor\assessorCertController@filterDataNoRevision');
            Route::post('/printMainNoRevision', 'Assessor\assessorCertController@printMainNoRevision');
            Route::get('/viewDataNoRevision/{id}', 'Assessor\assessorCertController@viewDataNoRevision');
            Route::get('/editDataNoRevision/{id}', 'Assessor\assessorCertController@editDataNoRevision');
            Route::get('/noRevisionCertPrint/{id}', 'Assessor\assessorCertController@noRevisionCertPrint');
            // Land History
            Route::get('/displayDataLandHistory', 'Assessor\assessorCertController@displayDataLandHistory');
            Route::get('/filterDataLandHistory', 'Assessor\assessorCertController@filterDataLandHistory');
            Route::post('/printMainLandHistory', 'Assessor\assessorCertController@printMainLandHistory');
            Route::get('/viewDataLandHistory/{id}', 'Assessor\assessorCertController@viewDataLandHistory');
            Route::get('/editDataLandHistory/{id}', 'Assessor\assessorCertController@editDataLandHistory');
            Route::get('/landHistoryCertPrint/{id}', 'Assessor\assessorCertController@landHistoryCertPrint');
            // Exempt Property
            Route::get('/displayDataExemptProperty', 'Assessor\assessorCertController@displayDataExemptProperty');
            Route::get('/filterDataExemptProperty', 'Assessor\assessorCertController@filterDataExemptProperty');
            Route::post('/printMainExemptProperty', 'Assessor\assessorCertController@printMainExemptProperty');
            Route::get('/viewDataExemptProperty/{id}', 'Assessor\assessorCertController@viewDataExemptProperty');
            Route::get('/editDataExemptProperty/{id}', 'Assessor\assessorCertController@editDataExemptProperty');
            Route::get('/exemptPropertyCertPrint/{id}', 'Assessor\assessorCertController@exemptPropertyCertPrint');
            // Real Property Certification
            Route::get('/displayDataRealPropertyCert', 'Assessor\assessorCertController@displayDataRealPropertyCert');
            Route::get('/filterDataRealPropertyCert', 'Assessor\assessorCertController@filterDataRealPropertyCert');
            Route::post('/printMainRealPropertyCert', 'Assessor\assessorCertController@printMainRealPropertyCert');
            Route::get('/viewDataRealPropertyCert/{id}', 'Assessor\assessorCertController@viewDataRealPropertyCert');
            Route::get('/editDataRealPropertyCert/{id}', 'Assessor\assessorCertController@editDataRealPropertyCert');
            Route::get('/realPropertyCertPrint/{id}', 'Assessor\assessorCertController@realPropertyCertPrint');
            // Zero Assessment
            Route::get('/displayDataZeroAssessment', 'Assessor\assessorCertController@displayDataZeroAssessment');
            Route::get('/filterDataZeroAssessment', 'Assessor\assessorCertController@filterDataZeroAssessment');
            Route::post('/printMainZeroAssessment', 'Assessor\assessorCertController@printMainZeroAssessment');
            Route::get('/viewDataZeroAssessment/{id}', 'Assessor\assessorCertController@viewDataZeroAssessment');
            Route::get('/editDataZeroAssessment/{id}', 'Assessor\assessorCertController@editDataZeroAssessment');
            Route::get('/zeroAssessmentCertPrint/{id}', 'Assessor\assessorCertController@zeroAssessmentCertPrint');
            Route::post('/storeAdd', 'Assessor\assessorCertController@storeAdd');
            Route::post('/saveAdd', 'Assessor\assessorCertController@saveAdd');
            Route::post('/updateAdd', 'Assessor\assessorCertController@updateAdd');
            // Property Holdings
            Route::get('/displayDataPropertyHoldings', 'Assessor\assessorCertController@displayDataPropertyHoldings');
            Route::get('/filterDataPropertyHoldings', 'Assessor\assessorCertController@filterDataPropertyHoldings');
            Route::post('/printMainPropertyHoldings', 'Assessor\assessorCertController@printMainPropertyHoldings');
            Route::get('/viewDataPropertyHoldings/{id}', 'Assessor\assessorCertController@viewDataPropertyHoldings');
            Route::get('/editDataPropertyHoldings/{id}', 'Assessor\assessorCertController@editDataPropertyHoldings');
            Route::get('/propertyHoldingsCertPrint/{id}', 'Assessor\assessorCertController@propertyHoldingsCertPrint');
            // No Improvement
            Route::get('/displayDataNoImprovement', 'Assessor\assessorCertController@displayDataNoImprovement');
            Route::get('/filterDataNoImprovement', 'Assessor\assessorCertController@filterDataNoImprovement');
            Route::post('/printMainNoImprovement', 'Assessor\assessorCertController@printMainNoImprovement');
            Route::get('/viewDataNoImprovement/{id}', 'Assessor\assessorCertController@viewDataNoImprovement');
            Route::get('/editDataNoImprovement/{id}', 'Assessor\assessorCertController@editDataNoImprovement');
            Route::get('/noImprovementCertPrint/{id}', 'Assessor\assessorCertController@noImprovementCertPrint');
            //FAAS DiscoveryNew
            Route::get('/displayFAASLandData', 'Assessor\assessorFAAScontroller@displayFAASLandData');
            Route::get('/filterFAASLandData', 'Assessor\assessorFAAScontroller@filterFAASLandData');
            Route::post('/printMainFAASLand', 'Assessor\assessorFAAScontroller@printMainFAASLand');
            Route::get('/getcitymundisnew', 'Assessor\assessorFAAScontroller@getcitymundisnew');
            Route::get('/displayClassification', 'Assessor\assessorFAAScontroller@displayClassification');
            Route::get('/displaySubClassification/{classid}', 'Assessor\assessorFAAScontroller@displaySubClassification');
            Route::get('/displayKind', 'Assessor\assessorFAAScontroller@displayKind');
            Route::get('/displayYear/{kindid}', 'Assessor\assessorFAAScontroller@displayYear');
            Route::get('/displaySignatory', 'Assessor\assessorFAAScontroller@displaySignatory');
            Route::get('/displaySignatoryEmp', 'Assessor\assessorFAAScontroller@displaySignatoryEmp');
            // Route::get('/assessorDiscoveryNew', 'Assessor\assessorFAAScontroller@assessorDiscoveryNew');
            // Route::post('/save', 'Assessor\assessorFAAScontroller@save');
            // Route::get('/viewData/{id}', 'Assessor\assessorFAAScontroller@viewData');
            // Route::get('/editData/{id}', 'Assessor\assessorFAAScontroller@editData');
            // Route::post('/update', 'Assessor\assessorFAAScontroller@update');
            // Route::get('/businessList', 'Assessor\assessorFAAScontroller@businessList');
            // Route::get('/personList', 'Assessor\assessorFAAScontroller@personList');
            // Route::get('/transNo', 'Assessor\assessorFAAScontroller@transNo');
            // Route::get('/noLandholdingCertPrint/{id}', 'Assessor\assessorFAAScontroller@noLandholdingCertPrint');
            // Route::post('/store', 'Assessor\assessorFAAScontroller@store');
            // Route::get('/cancelData/{id}', 'Assessor\assessorFAAScontroller@cancelData');
        });
        //New Business Application
        Route::prefix('Business')->group(function () {
            Route::get('/businessApplied', 'Business\NewBusinessController@businessApplied');
            Route::get('/businessDocs', 'Business\NewBusinessController@businessDocs');
            Route::get('/applied_per_brgy', 'Business\NewBusinessController@applied_per_brgy');

            Route::get('/getOrganizationType', 'Business\NewBusinessController@getOrganizationType');
            Route::get('/getBSPType', 'Business\NewBusinessController@getBSPType');
            Route::get('/getOccupationalFees', 'Business\NewBusinessController@getOccupationalFees');
            Route::get('/getBusinessKind', 'Business\NewBusinessController@getBusinessKind');
            Route::get('/getBarangaylist', 'Business\NewBusinessController@getBarangaylist');
            Route::get('/getDocumentList', 'Business\NewBusinessController@getDocumentList');
            Route::get('/getbusinessList', 'Business\NewBusinessController@getbusinessList');
            Route::get('/permitNoNew', 'Business\NewBusinessController@permitNoNew');
            Route::get('/businessNoNew', 'Business\NewBusinessController@businessNoNew');
            Route::get('/businessAccountNoNew', 'Business\NewBusinessController@businessAccountNoNew');
            Route::post('store', 'Business\NewBusinessController@store');
            Route::get('edit/{id}', 'Business\NewBusinessController@edit');
            Route::post('/delete', 'Business\NewBusinessController@delete');
            Route::get('printBusinessApplicationForm/{id}', 'Business\NewBusinessController@printBusinessApplicationForm');
            Route::get('getBusinessListforrenew', 'Business\NewBusinessController@getBusinessListforrenew');
            Route::get('retrievedata/{id}', 'Business\NewBusinessController@retrievedata');
            Route::get('getBusinessMasterlist', 'Business\NewBusinessController@getBusinessMasterlist');
            Route::post('printBusinessMasterlist', 'Business\NewBusinessController@printBusinessMasterlist');
        });
        //Building Permit
        Route::prefix('BuildingPermit')->group(function () {
            Route::get('/getBarangaylist', 'BuildingPermit\buildingPermitController@getBarangaylist');
            Route::get('/displayProjectList', 'BuildingPermit\buildingPermitController@displayProjectList');
            Route::get('/buildingpermitNoNew', 'BuildingPermit\buildingPermitController@buildingpermitNoNew');
            Route::get('getbuildingPermitList', 'BuildingPermit\buildingPermitController@getbuildingPermitList');
            Route::get('/getDocumentList', 'BuildingPermit\buildingPermitController@getDocumentList');
            Route::post('store', 'BuildingPermit\buildingPermitController@store');
            Route::get('edit/{id}', 'BuildingPermit\buildingPermitController@edit');
            Route::post('/delete', 'BuildingPermit\buildingPermitController@delete');
            Route::get('printBuildingPermitForm/{id}', 'BuildingPermit\buildingPermitController@printBuildingPermitForm');
            Route::post('printBuildingPermitList', 'BuildingPermit\buildingPermitController@printBuildingPermitList');
            Route::get('printQPass', 'BuildingPermit\buildingPermitController@printQPass');
            // Route::get('retrievedata/{id}', 'BuildingPermit\buildingPermitController@retrievedata');
            // Route::get('getBusinessMasterlist', 'BuildingPermit\buildingPermitController@getBusinessMasterlist');
            // Route::post('printBusinessMasterlist', 'BuildingPermit\buildingPermitController@printBusinessMasterlist');
        });

        //Civil/Structural Permit
        Route::prefix('CivilStructuralPermit')->group(function () {
            Route::get('/getBarangaylist', 'CivilStructuralPermit\civilStructuralController@getBarangaylist');
            Route::get('/displayProjectList', 'CivilStructuralPermit\civilStructuralController@displayProjectList');
            Route::get('/displayBuildingList', 'CivilStructuralPermit\civilStructuralController@displayBuildingList');
            Route::get('/civilStructuralPermitNoNew', 'CivilStructuralPermit\civilStructuralController@civilStructuralPermitNoNew');
            Route::get('getcivilStructuralList', 'CivilStructuralPermit\civilStructuralController@getcivilStructuralList');
            Route::get('/getDocumentList', 'CivilStructuralPermit\civilStructuralController@getDocumentList');
            Route::post('store', 'CivilStructuralPermit\civilStructuralController@store');
            Route::get('edit/{id}', 'CivilStructuralPermit\civilStructuralController@edit');
            Route::post('/delete', 'CivilStructuralPermit\civilStructuralController@delete');
            Route::get('printBuildingPermitForm/{id}', 'CivilStructuralPermit\civilStructuralController@printBuildingPermitForm');
            Route::post('printcivilStructurePermitList', 'CivilStructuralPermit\civilStructuralController@printcivilStructurePermitList');
            // Route::get('retrievedata/{id}', 'CivilStructuralPermit\civilStructuralController@retrievedata');
            // Route::get('getBusinessMasterlist', 'CivilStructuralPermit\civilStructuralController@getBusinessMasterlist');
            // Route::post('printBusinessMasterlist', 'CivilStructuralPermit\civilStructuralController@printBusinessMasterlist');
        });



        //Health Card
        Route::prefix('HealthCard')->group(function () {
            Route::post('store', 'HealthCard\healthCardController@store');
            Route::post('store2', 'HealthCard\healthCardController@store2');
            Route::get('edit/{id}', 'HealthCard\healthCardController@edit');
            Route::get('edit2/{id}', 'HealthCard\healthCardController@edit2');
            Route::get('/getHealthCardList', 'HealthCard\healthCardController@getHealthCardList');
            Route::get('/getHealthCertList', 'HealthCard\healthCardController@getHealthCertList');
            Route::get('/checking/{id}', 'HealthCard\healthCardController@checking');
            Route::post('/printHealthCardList', 'HealthCard\healthCardController@printHealthCardList');
            Route::get('/transNo', 'HealthCard\healthCardController@transNo');
            Route::get('/healthNo', 'HealthCard\healthCardController@healthNo');
            Route::get('printHealthCertificate/{id}', 'HealthCard\healthCardController@printHealthCertificate');
            Route::get('printHealthCard/{id}', 'HealthCard\healthCardController@printHealthCard');
            Route::get('printCardFedIn/{id}', 'HealthCard\healthCardController@printCardFedIn');
            Route::get('printCardFedIn2/{id}', 'HealthCard\healthCardController@printCardFedIn2');
            Route::get('getHistoryPhysical/{id}', 'HealthCard\healthCardController@getHistoryPhysical');
            Route::get('getHistoryXray/{id}', 'HealthCard\healthCardController@getHistoryXray');
            Route::get('getHistoryImmunization/{id}', 'HealthCard\healthCardController@getHistoryImmunization');
            Route::get('getHistoryRectal/{id}', 'HealthCard\healthCardController@getHistoryRectal');
            Route::post('/delete', 'HealthCard\healthCardController@delete');
            Route::get('/delete2', 'HealthCard\healthCardController@delete2');
            Route::post('postOR', 'HealthCard\healthCardController@postOR');
        });
        //Sanitary Permit
        Route::prefix('Sanitary')->group(function () {
            Route::get('/show/{id}', 'SanitaryPermit\sanitaryController@edit');
            Route::get('/edit/{id}', 'SanitaryPermit\sanitaryController@edit');
            Route::post('/store', 'SanitaryPermit\sanitaryController@store');
            Route::post('/delete', 'SanitaryPermit\sanitaryController@delete');
            Route::get('/save', 'SanitaryPermit\sanitaryController@save');
            Route::get('/update', 'SanitaryPermit\sanitaryController@update');
            Route::get('/businessList', 'SanitaryPermit\sanitaryController@businessList');
            Route::get('/getCategory', 'SanitaryPermit\sanitaryController@getCategory');
            Route::get('/sanitaryList', 'SanitaryPermit\sanitaryController@sanitaryList');
            Route::post('/printSanitaryList', 'SanitaryPermit\sanitaryController@printSanitaryList');
            Route::post('/printSanitaryCertificate', 'SanitaryPermit\sanitaryController@printSanitaryCertificate');
            Route::get('/employeeIssuance/{id}', 'SanitaryPermit\sanitaryController@employeeIssuance');
            Route::get('/ref', 'SanitaryPermit\sanitaryController@ref');
            Route::get('/businesschecking/{year}', 'SanitaryPermit\sanitaryController@businesschecking');
            Route::get('/checkInspection/{id}', 'SanitaryPermit\sanitaryController@checkInspection');
            Route::get('/getInspection', 'SanitaryPermit\sanitaryController@getInspection');
            Route::get('/getInspector', 'SanitaryPermit\sanitaryController@getInspector');
            Route::post('/storeInspection', 'SanitaryPermit\sanitaryController@storeInspection');
        });
        //Tricycle Permit
        Route::prefix('Tricycle')->group(function () {
            // PRINT
            Route::get('/tricyclelistdisplay', 'TricyclePermit\tricycleController@tricyclelistdisplay');
            Route::post('/printtricyclemasterlist', 'TricyclePermit\tricycleController@printtricyclemasterlist');
            Route::post('/printsummarycount', 'TricyclePermit\tricycleController@printsummarycount');
            Route::post('/printTricyclePermit', 'TricyclePermit\tricycleController@printTricyclePermit');
            // CRUD
            Route::get('/ref', 'TricyclePermit\tricycleController@ref');
            Route::get('/getApplicantType', 'TricyclePermit\tricycleController@getApplicantType');
            Route::get('/getRequirements', 'TricyclePermit\tricycleController@getRequirements');
            Route::post('/delete', 'TricyclePermit\tricycleController@delete');
            Route::post('/store', 'TricyclePermit\tricycleController@store');
            Route::get('/save', 'TricyclePermit\tricycleController@save');
            Route::get('/update', 'TricyclePermit\tricycleController@update');
            Route::get('/edit/{id}', 'TricyclePermit\tricycleController@edit');
        });
        //Trisikad Permit
        Route::prefix('Trisikad')->group(function () {
            // PRINT
            Route::get('/trisikadlistdisplay', 'TrisikadPermit\trisikadController@trisikadlistdisplay');
            Route::post('/printTrisikadmasterlist', 'TrisikadPermit\trisikadController@printTrisikadmasterlist');
            Route::post('/printsummarycount', 'TrisikadPermit\trisikadController@printsummarycount');
            Route::post('/printTrisikadPermit', 'TrisikadPermit\trisikadController@printTrisikadPermit');
            // CRUD
            Route::get('/ref', 'TrisikadPermit\trisikadController@ref');
            Route::get('/getApplicantType', 'TrisikadPermit\trisikadController@getApplicantType');
            Route::get('/getRequirements', 'TrisikadPermit\trisikadController@getRequirements');
            Route::post('/delete', 'TrisikadPermit\trisikadController@delete');
            Route::post('/store', 'TrisikadPermit\trisikadController@store');
            Route::post('/delete', 'TrisikadPermit\trisikadController@delete');
            Route::post('/store', 'TrisikadPermit\trisikadController@store');
            Route::get('/save', 'TrisikadPermit\trisikadController@save');
            Route::get('/update', 'TrisikadPermit\trisikadController@update');
            Route::get('/edit/{id}', 'TrisikadPermit\trisikadController@edit');
        });
        //Bicycle Permit
        Route::prefix('Bicycle')->group(function () {
            // PRINT
            Route::get('/bicyclelistdisplay', 'BicyclePermit\bicycleController@bicyclelistdisplay');
            Route::post('/printBicyclemasterlist', 'BicyclePermit\bicycleController@printBicyclemasterlist');
            Route::post('/printsummarycount', 'BicyclePermit\bicycleController@printsummarycount');
            Route::post('/printBicyclePermit', 'BicyclePermit\bicycleController@printBicyclePermit');
            // CRUD
            Route::get('/ref', 'BicyclePermit\bicycleController@ref');
            Route::get('/getApplicantType', 'BicyclePermit\bicycleController@getApplicantType');
            Route::get('/getRequirements', 'BicyclePermit\bicycleController@getRequirements');
            Route::post('/delete', 'BicyclePermit\bicycleController@delete');
            Route::post('/store', 'BicyclePermit\bicycleController@store');
            Route::post('/delete', 'BicyclePermit\bicycleController@delete');
            Route::post('/store', 'BicyclePermit\bicycleController@store');
            Route::get('/save', 'BicyclePermit\bicycleController@save');
            Route::get('/update', 'BicyclePermit\bicycleController@update');
            Route::get('/edit/{id}', 'BicyclePermit\bicycleController@edit');
        });

        //Mayor`s Clearance

        Route::prefix('Mayors')->group(function () {

            //PRINT
            Route::get('/masterList', 'Mayors\MayorsClearance@masterList');
            Route::post('/printList', 'Mayors\MayorsClearance@printList');
            Route::post('/printCount', 'Mayors\MayorsClearance@printCount');
            Route::get('/printclearance/{id}', 'Mayors\MayorsClearance@printclearance');
            Route::get('/printRecommendationcert/{id}', 'Mayors\MayorsClearance@printRecommendationcert');
            Route::get('/printCertification/{id}', 'Mayors\MayorsClearance@printCertification');
            Route::get('/printMayorEmployment/{id}', 'Mayors\MayorsClearance@printMayorEmployment');
            // CRUD
            Route::get('/ref', 'Mayors\MayorsClearance@ref');
            Route::get('/getApplicantType', 'Mayors\MayorsClearance@getApplicantType');
            Route::get('/getRequirements', 'Mayors\MayorsClearance@getRequirements');
            Route::post('/delete', 'Mayors\MayorsClearance@delete');
            Route::post('/store', 'Mayors\MayorsClearance@store');
            Route::get('/save', 'Mayors\MayorsClearance@save');
            Route::get('/update', 'Mayors\MayorsClearance@update');
            Route::get('/edit/{id}', 'Mayors\MayorsClearance@edit');
        });

        //Profile 3

        Route::prefix('Profile3')->group(function () {

            //PRINT
            Route::get('/listdisplay', 'Mayors\profile3controller@listdisplay');
            Route::post('/printmasterlist', 'Mayors\profile3controller@printmasterlist');
            Route::post('/printsummarycount', 'Mayors\profile3controller@printsummarycount');
            Route::get('/printPermit/{id}', 'Mayors\profile3controller@printPermit');
            Route::get('/printRecommendationcert/{id}', 'Mayors\profile3controller@printRecommendationcert');
            Route::get('/printCertification/{id}', 'Mayors\profile3controller@printCertification');
            // CRUD
            Route::get('/ref', 'Mayors\profile3controller@ref');
            Route::get('/getApplicantType', 'Mayors\profile3controller@getApplicantType');
            Route::get('/getRequirements', 'Mayors\profile3controller@getRequirements');
            Route::post('/delete', 'Mayors\profile3controller@delete');
            Route::post('/store', 'Mayors\profile3controller@store');
            Route::get('/save', 'Mayors\profile3controller@save');
            Route::get('/update', 'Mayors\profile3controller@update');
            Route::get('/edit/{id}', 'Mayors\profile3controller@edit');
        });

        // //Mayor`s Recommendation

        // Route::prefix('Recommendation')->group(function () {

        //     //PRINT
        //     Route::get('/mayorsRecommendationList', 'Mayors\mayorsRecommendation@mayorsRecommendationList');
        //     Route::post('/mayorsRecommendationmasterlist', 'Mayors\mayorsRecommendation@mayorsRecommendationmasterlist');
        //     Route::post('/printsummarycount', 'Mayors\mayorsRecommendation@printsummarycount');
        //     Route::post('/printRecommendationCert', 'Mayors\mayorsRecommendation@printRecommendationCert');
        //     // CRUD
        //     Route::get('/ref', 'Mayors\mayorsRecommendation@ref');
        //     Route::get('/getApplicantType', 'Mayors\mayorsRecommendation@getApplicantType');
        //     Route::get('/getRequirements', 'Mayors\mayorsRecommendation@getRequirements');
        //     Route::post('/delete', 'Mayors\mayorsRecommendation@delete');
        //     Route::post('/store', 'Mayors\mayorsRecommendation@store');
        //     Route::get('/save', 'Mayors\mayorsRecommendation@save');
        //     Route::get('/update', 'Mayors\mayorsRecommendation@update');
        //     Route::get('/edit/{id}', 'Mayors\mayorsRecommendation@edit');
        // });

        // OBO - ENGINEERING
        //Architectural Permit
        Route::prefix('Architectural')->group(function () {
            // PRINT
            Route::get('/architecturalList', 'OBOPermit\architecturalController@architecturalList');
            Route::get('/getPermitStatus', 'OBOPermit\architecturalController@getPermitStatus');
            Route::post('/printArchitecturalPermitList', 'OBOPermit\architecturalController@printArchitecturalPermitList');
            Route::post('/printArchitecturalPermit', 'OBOPermit\architecturalController@printArchitecturalPermit');
            Route::post('/issuance', 'OBOPermit\architecturalController@issuance');
            Route::get('/issuanceRef', 'OBOPermit\architecturalController@issuanceRef');

            // CRUD
            Route::get('/ref', 'OBOPermit\architecturalController@ref');
            Route::get('/getOccupancy', 'OBOPermit\architecturalController@getOccupancy');
            Route::post('/delete', 'OBOPermit\architecturalController@delete');
            Route::get('/edit/{id}', 'OBOPermit\architecturalController@edit');
        });

        //Locational Clearance
        Route::prefix('Locational')->group(function () {
            Route::get('/display', 'Locational\locationalcontroller@display');
            Route::get('/ref', 'Locational\locationalcontroller@ref');
            Route::get('/displaylist', 'Locational\locationalcontroller@displaylist');
            Route::get('/displayprojnature', 'Locational\locationalcontroller@displayprojnature');
            Route::get('/displayclassification', 'Locational\locationalcontroller@displayclassification');
            Route::get('/displaycategory', 'Locational\locationalcontroller@displaycategory');
            Route::post('/store', 'Locational\locationalcontroller@store');
            Route::get('/editlocational/{id}', 'Locational\locationalcontroller@editlocational');
            Route::get('/update', 'Locational\locationalcontroller@update');
            Route::get('/printLocCert/{id}', 'Locational\locationalcontroller@printLocCert');
            Route::post('/delete', 'Locational\locationalcontroller@delete');
            Route::post('/printlocationallist', 'Locational\locationalcontroller@printlocationallist');
            Route::get('/printevaluatereport/{id}', 'Locational\locationalcontroller@printevaluatereport');
            Route::get('/decisionzoning/{id}', 'Locational\locationalcontroller@decisionzoning');
        });

        //Lot Zoning
        Route::prefix('LotzoningCert')->group(function () {
            Route::get('/displaylist', 'LotZoning\lotzoning@displaylist');
            Route::get('/displayzpurpose', 'LotZoning\lotzoning@displayzpurpose');
            Route::get('/displayclassification', 'LotZoning\lotzoning@displayclassification');
            Route::post('/store', 'LotZoning\lotzoning@store');
            Route::get('/editlotzoning/{id}', 'LotZoning\lotzoning@editlotzoning');
            Route::post('/delete', 'LotZoning\lotzoning@delete');
            Route::get('/printLotCert/{id}', 'LotZoning\lotzoning@printLotCert');
            Route::post('/printLotzoninglist', 'LotZoning\lotzoning@printLotzoninglist');
            Route::get('/displaytaxdeclist', 'LotZoning\lotzoning@displaytaxdeclist');
        });

        //Lot Zoning variance
        Route::prefix('LotzoningCertVariance')->group(function () {
            Route::get('/displaylist', 'LotZoning\lotzoningvariance@displaylist');
            Route::get('/displayzpurpose', 'LotZoning\lotzoningvariance@displayzpurpose');
            Route::get('/displayclassification', 'LotZoning\lotzoningvariance@displayclassification');
            Route::post('/store', 'LotZoning\lotzoningvariance@store');
            Route::get('/editlotzoning/{id}', 'LotZoning\lotzoningvariance@editlotzoning');
            Route::post('/delete', 'LotZoning\lotzoningvariance@delete');
            Route::get('/printLotCert/{id}', 'LotZoning\lotzoningvariance@printLotCert');
            Route::post('/printLotzoninglist', 'LotZoning\lotzoningvariance@printLotzoninglist');
            Route::get('/displaytaxdeclist', 'LotZoning\lotzoningvariance@displaytaxdeclist');
        });
        //Lot Zoning Calcination
        Route::prefix('LotzoningCalc')->group(function () {
            Route::get('/displaylist', 'LotZoning\lotcalcination@displaylist');
            Route::get('/displayzpurpose', 'LotZoning\lotcalcination@displayzpurpose');
            Route::get('/displayclassification', 'LotZoning\lotcalcination@displayclassification');
            Route::post('/store', 'LotZoning\lotcalcination@store');
            Route::get('/editlotzoning/{id}', 'LotZoning\lotcalcination@editlotzoning');
            Route::post('/delete', 'LotZoning\lotcalcination@delete');
            Route::get('/printLotCert/{id}', 'LotZoning\lotcalcination@printLotCert');
            Route::post('/printLotzoninglist', 'LotZoning\lotcalcination@printLotzoninglist');
            Route::get('/displaytaxdeclist', 'LotZoning\lotcalcination@displaytaxdeclist');
        });

        Route::prefix('Calendar')->group(function () {
            Route::get('/display', 'FullCalendarController@displayCalendar');
            Route::post('/store', 'FullCalendarController@store');
        });

        Route::prefix('User')->group(function () {
            Route::get('/list', 'Admin\UsersController@getEmployee');
            Route::post('/store', 'Admin\UsersController@store');
            Route::post('/storeInside', 'Admin\UsersController@storeInside');
            Route::post('/storeRegister', 'Auth\RegisterController@store');
            Route::get('/show', 'Admin\UsersController@show');
            Route::post('/update', 'Admin\UsersController@update');
            Route::get('/cancel/{id}', 'Admin\UsersController@cancel');
            Route::get('/display', 'Admin\UsersController@display');
            Route::post('/sendMessage', 'Admin\UsersController@sendMessage');
            Route::get('/showMessage', 'Admin\UsersController@showMessage');
            Route::get('/edit/{id}', 'Admin\UsersController@edit');
            Route::get('/getMessageNotification', 'Admin\UsersController@getMessageNotification');
        });

        Route::prefix('Employee')->group(function () {
            Route::get('/list', 'Admin\EmployeeController@getEmployee');
            Route::get('/getEmployeeList', 'Admin\EmployeeController@getEmployeeList');
            Route::get('/getEmployeeListTable', 'Admin\EmployeeController@getEmployeeListTable');
        });

        Route::prefix('Group')->group(function () {
            Route::get('/', 'Scheduler\GroupController@index');
            Route::get('/show', 'Scheduler\GroupController@show');
            Route::get('/edit/{id}', 'Scheduler\GroupController@edit');
            Route::get('/cancel/{id}', 'Scheduler\GroupController@cancel');
            Route::get('/list', 'Scheduler\GroupController@list');
            Route::post('/store', 'Scheduler\GroupController@store');
            Route::get('/Display_Name', 'Scheduler\GroupController@Display_Name');
        });

        //General Controller
        Route::prefix('General')->group(function () {
            Route::prefix('Business')->group(function () {
                Route::get('/getBusinessStatus', 'General\GeneralController@getBusinessStatus');
                Route::get('/getBusinessType', 'General\GeneralController@getBusinessType');
                Route::get('/getBusinessKind', 'General\GeneralController@getBusinessKind');
                Route::get('/getofficeType', 'General\GeneralController@getofficeType');
                Route::get('/getBSPType', 'General\GeneralController@getBSPType');
                Route::get('/getClassification', 'General\GeneralController@getClassification');
            });
            Route::prefix('Market')->group(function () {
                Route::get('/getMarketBillType', 'General\GeneralController@getMarketBillType');
                Route::get('/getBuildingList', 'General\GeneralController@getBuildingList');
                Route::get('/getFloorBlock/{id}', 'General\GeneralController@getFloorBlock');
                Route::get('/getBldgOwner/{id}', 'General\GeneralController@getBldgOwner');
            });
            Route::prefix('Others')->group(function () {
                Route::get('/getQuarter', 'General\GeneralController@getQuarter');
            });
        });


        // //BPLO Controller
        // Route::prefix('Business')->group(function () {
        //     Route::prefix('BusinessReport')->group(function () {
        //         Route::get('/businessPermitStatus', 'Business\BusinessReport@businessPermitStatus');
        //         Route::post('/businessPermitStatusPrint', 'Business\BusinessReport@businessPermitStatusPrint');
        //         Route::get('/businessPaymentStatus', 'Business\BusinessReport@businessPaymentStatus');
        //         Route::post('/businessPaymentStatusPrint', 'Business\BusinessReport@businessPaymentStatusPrint');
        //         Route::get('/taxPayerReport', 'Business\BusinessReport@taxPayerReport');
        //         Route::post('/taxPayerReportPrint', 'Business\BusinessReport@taxPayerReportPrint');
        //         Route::get('/businessEnterprise', 'Business\BusinessReport@businessEnterprise');
        //         Route::post('/businessEnterprisePrint', 'Business\BusinessReport@businessEnterprisePrint');
        //         Route::get('/businessDTIReport', 'Business\BusinessReport@businessDTIReport');
        //         Route::post('/businessDTIReportPrint', 'Business\BusinessReport@businessDTIReportPrint');
        //         Route::post('/businessDTIReportPrintMEI', 'Business\BusinessReport@businessDTIReportPrintMEI');
        //         Route::get('/businessBMBE', 'Business\BusinessReport@businessBMBE');
        //         Route::post('/businessBMBEPrint', 'Business\BusinessReport@businessBMBEPrint');
        //         Route::get('/businessBSP', 'Business\BusinessReport@businessBSP');
        //         Route::post('/businessBSPPrint', 'Business\BusinessReport@businessBSPPrint');
        //         Route::post('/businessBSPReport', 'Business\BusinessReport@businessBSPReport');
        //     });
        // });
        //Treasury Controller
        // Route::prefix('Treasury')->group(function () {
        //     Route::prefix('RealPropertyTax')->group(function () {
        //     Route::get('/RPTTaxMasterList', 'Treasury\RealPropertyTax@RPTTaxMasterList');
        //     Route::post('/RPTTaxMasterListPrint', 'Treasury\RealPropertyTax@RPTTaxMasterListPrint');
        //     Route::get('/getrptTaxClearance/{id}', 'Treasury\RealPropertyTax@getrptTaxClearance');
        //     Route::post('/RPTTaxClearancePrint', 'Treasury\RealPropertyTax@RPTTaxClearancePrint');
        //     });
        // });

        // ***LGU MODULES***
        Route::prefix('LGUMain')->group(function () {
            Route::prefix('business')->group(function () {
                Route::get('/businessTaxLedger', 'Treasury\Business@businessTaxLedger');
                Route::get('/businessTaxDeliquency', 'Treasury\BusinessDel@businessTaxDeliquency');
                Route::get('/getAZCol', 'Treasury\BusinessDel@getAZCol');
                Route::get('/getQuarter', 'Treasury\BusinessDel@getQuarter');
                Route::get('/getStreet', 'Treasury\BusinessDel@getStreet');
                Route::get('/getClassification', 'Treasury\BusinessDel@getClassification');
                Route::get('/getBustype', 'Treasury\BusinessDel@getBusinessType');
                Route::get('/getBusstatus', 'Treasury\BusinessDel@getBusinessStatus');
                Route::get('/getBuskind', 'Treasury\BusinessDel@getKindofBusiness');
                Route::get('businessTaxSubsidiaryLedgerPrint/{id}', 'Treasury\Business@businessTaxSubsidiaryLedgerPrint');
                Route::get('printPhistory/{id}', 'Treasury\Business@printPhistory');
                Route::get('ref', 'Treasury\BusinessZoning@ref');
                Route::post('printmain', 'Treasury\Business@printmasterlist');
                Route::post('/printNot', 'Treasury\BusinessDel@printNot');
                Route::post('/printAllNot', 'Treasury\BusinessDel@printAllNot');
                Route::post('/printMaster', 'Treasury\BusinessDel@printMaster');
                Route::post('/printBarangay', 'Treasury\BusinessDel@printBarangay');
                Route::post('/printTaxCertificate', 'Treasury\BusinessDel@printTaxCertificate');
                Route::post('/businessTaxMaster', 'Treasury\BusinessDel@businessTaxMaster');
                Route::get('/getBusinessStatus', 'Treasury\BusinessDel@getBusinessStatus');
                Route::post('/printBusinessList', 'Treasury\BusinessDel@printBusinessList');
                Route::post('/printClosure', 'Treasury\BusinessDel@printClosure');
                Route::get('/businessTaxLedger_history/{id}', 'Treasury\Business@businessTaxLedger_history');
                Route::get('/businessPaymentStatus', 'Treasury\Business@businessPaymentStatus');
                Route::get('/businessPermitStatus', 'Treasury\Business@businessPermitStatus');
                Route::post('/businessPermitPrint', 'Treasury\Business@businessPermitPrint');
                Route::post('/businessPaymentPrint', 'Treasury\BusinessPaymentStatus@businessPaymentPrint');
                Route::get('/businessEnterprise', 'Treasury\Business@businessEnterprise');
                Route::post('/businessEnterprisePrint', 'Treasury\Business@businessEnterprisePrint');
                Route::get('/businessBMBE', 'Treasury\Business@businessBMBE');
                Route::post('/businessBMBEPrint', 'Treasury\Business@businessBMBEPrint');
                Route::get('/businessBSP', 'Treasury\Business@businessBSP');
                Route::post('/businessBSPPrint', 'Treasury\Business@businessBSPPrint');
                Route::post('/businessBSPReport', 'Treasury\Business@businessBSPReport');
                Route::get('/officeType', 'Treasury\Business@officeType');
                Route::get('/BSPType', 'Treasury\Business@BSPType');

                // DTI Report
                Route::get('getBustype', 'Treasury\Dtirep@getBusinessType');
                Route::get('getBusinessStatus', 'Treasury\Dtirep@getBusinessStatus');
                Route::get('getBusinesskind', 'Treasury\Dtirep@getBusinesskind');
                Route::get('displaydtireport', 'Treasury\Dtirep@displaydtireport');
                Route::post('/printDTIList', 'Treasury\Dtirep@printDTIList');
                Route::post('/printMEI', 'Treasury\Dtirep@printMEI');

                //Business Zoning
                Route::get('displayzoningcertlist', 'Treasury\BusinessZoning@display');
                Route::get('displaybusinesslist', 'Treasury\BusinessZoning@displaybusinesslist');
                Route::get('displaybrgylist', 'Treasury\BusinessZoning@displaybrgylist');
                Route::get('displaycadastrallot', 'Treasury\BusinessZoning@displaycadastrallot');
                Route::get('displaytaxdec', 'Treasury\BusinessZoning@displaytaxdec');
                Route::get('displayclassification', 'Treasury\BusinessZoning@displayclassification');
                Route::get('displaybillingfees', 'Treasury\BusinessZoning@displaybillingfees');
                Route::post('save', 'Treasury\BusinessZoning@save');
                Route::get('/editzoning/{id}', 'Treasury\BusinessZoning@editzoning');
                Route::get('/printCERT/{id}', 'Treasury\BusinessZoning@printCERT');
                Route::post('/printbussinesszoninglist', 'Treasury\BusinessZoning@printbussinesszoninglist');
                Route::post('/update', 'Treasury\BusinessZoning@update');
                Route::post('/store', 'Treasury\BusinessZoning@store');
                Route::post('displaybrgydata', 'Treasury\BusinessZoning@displaybrgylist');
                Route::post('/delete', 'Treasury\BusinessZoning@delete');
            });

            Route::prefix('rpt')->group(function () {
                Route::get('/getdelinquency', 'Treasury\RPTDelinquency@getdelinquency');
                Route::post('/printLists', 'Treasury\RPTDelinquency@printLists');
                Route::get('/getAZCol', 'Treasury\RPTDelinquency@getAZCol');
                Route::get('/generateBillComputation/{username}', 'Treasury\RPTDelinquency@generateBillComputation');
                Route::get('/getCollectionAbtract', 'Treasury\RPTCollectionAbstract@getCollectionAbtract');
                Route::post('/printrptabstract', 'Treasury\RPTCollectionAbstract@printrptabstract');
                Route::get('/getRPTMasterlist', 'Treasury\RPTMasterlistControl@getRPTMasterlist');
                Route::get('/getRPTTaxDueandPayment/{id}', 'Treasury\RPTMasterlistControl@getRPTTaxDueandPayment');
                Route::get('/printform/{id}', 'Treasury\RPTMasterlistControl@printform');
                Route::post('/printList', 'Treasury\RPTMasterlistControl@printList');
                Route::get('/certificationList', 'Treasury\RPTMasterlistControl@certificationList');
                Route::get('/getRPTTaxClearance/{id}', 'Treasury\RPTMasterlistControl@getRPTTaxClearance');
                Route::post('/printCertification', 'Treasury\RPTMasterlistControl@printCertification');
            });
            Route::get('tbl_market_bill', 'Market\marketController@show');

            Route::prefix('Market')->group(function () {
                Route::get('display/{id}', 'Market\marketController@display');
                Route::get('marketDelinquency', 'Market\marketController@marketDelinquency');
                Route::post('marketDelinquencyPrint', 'Market\marketController@marketDelinquencyPrint');
                Route::get('building', 'Market\marketController@building');
                Route::get('subBuilding/{id}', 'Market\marketController@subBuilding');
                Route::get('marketMasterlist', 'Market\marketController@marketMasterlist');
                Route::post('marketMasterlistPrint', 'Market\marketController@marketMasterlistPrint');
            });

            Route::prefix('taxPayerReport')->group(function () {
                Route::get('kindofBusiness', 'TaxPayerReport\taxPayerController@kindofBusiness');
                Route::get('TaxPayerReport', 'TaxPayerReport\taxPayerController@TaxPayerReport');
                Route::post('taxpayerReportPrint', 'TaxPayerReport\taxPayerController@taxpayerReportPrint');
            });
        });
        Route::prefix('userMonitoring')->group(function () {
            Route::get('/users', 'UsermonitoringController@users');
            Route::get('/userdeparments', 'UsermonitoringController@userdeparments');
            Route::get('/logout/{id}', 'UsermonitoringController@logout');
        });
        Route::prefix('UserSettings')->group(function () {
            Route::get('/GetUserList', 'Admin\UserSettingsController@GetUserList');
            Route::get('/GetUserRole', 'Admin\UserSettingsController@GetUserRole');
            Route::post('/StoreUserRole', 'Admin\UserSettingsController@StoreUserRole');
            Route::post('/VerifyUser', 'Admin\UserSettingsController@VerifyUser');
            Route::get('/CheckPermission', 'Admin\UserSettingsController@CheckPermission');
        });
    });
});
