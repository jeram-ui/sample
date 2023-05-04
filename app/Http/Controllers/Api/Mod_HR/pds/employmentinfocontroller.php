<?php

namespace App\Http\Controllers\Api\Mod_HR\pds;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class employmentinfocontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
   
   
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->pds_dum = $this->G->getPDSDummyDB();
    }
    // public function employmentinformation(Request $request)
    // {
    //   $list = DB::table($this->hr_db . '.employees')
    //   ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
    //   ->join($this->hr_db .'.employees_timeshift','employees.shift_code','employees_timeshift.shiftcode') 
    //   ->where('SysPK_Empl',Auth::user()->Employee_id)
    //   ->get();
    //   return response()->json(new JsonResponse($list));
    // }

    public function employmentinformation(Request $request)
    {
        $chk = DB::table($this->hr_db . '.employees')
        ->where('SysPK_Empl',Auth::user()->Employee_id)
        ->count();
        if( $chk > 0 ){
            $list = DB::table($this->pds_dum . '.employees')
            ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
            ->join($this->hr_db .'.employees_timeshift','employees.shift_code','employees_timeshift.shiftcode')
              ->where('SysPK_Empl',Auth::user()->Employee_id)
              ->where('Status_Empl', 'Active')
              ->orderby('idx', 'DESC')
              ->get();
            return response()->json(new JsonResponse($list));
        }else{
            $list = DB::table($this->hr_db . '.employees')
            ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
            ->join($this->hr_db .'.employees_timeshift','employees.shift_code','employees_timeshift.shiftcode')
              ->where('SysPK_Empl',Auth::user()->Employee_id)
              ->where('Status_Empl', 'Active')
              ->get();
            return response()->json(new JsonResponse($list));
        }



    }

    public function storeEmp_info(Request $request)
    {
        $form = $request->form;

                $form = array(
                    'SysPK_Empl' => $form['SysPK_Empl'],
                    'old_personId' => $form['old_personId'],
                    'person_id' =>$form['person_id'],
                    'status_update' => $form['status_update'],
                    'priority' => $form['priority'],
                    'AccountNo_Empl' => $form['AccountNo_Empl'],
                    'Emp_no' => $form['Emp_no'],
                    'trans_date' => $form['trans_date'],
                    'Name_Empl' => $form['Name_Empl'],
                    'FirstName_Empl' => $form['FirstName_Empl'],
                    'MiddleName_Empl' => $form['MiddleName_Empl'],
                    'LastName_Empl' => $form['LastName_Empl'],
                    'SuffixName_Empl' => $form['SuffixName_Empl'],
                    'PrefixName_Empl' => $form['PrefixName_Empl'],
                    'nickname' => $form['nickname'],
                    'Address_Empl' => $form['Address_Empl'],
                    'empl_contactno' => $form['empl_contactno'],
                    'email_address' => $form['email_address'],
                    'BirthDate_Empl' => $form['BirthDate_Empl'],
                    'weight' => $form['weight'],
                    'height' => $form['height'],
                    'gender' => $form['gender'],
                    'civilStatus' => $form['civilStatus'],
                    'GSIS_Empl' => $form['GSIS_Empl'],
                    'SSS_Empl' => $form['SSS_Empl'],
                    'TIN_Empl' => $form['TIN_Empl'],
                    'philhealth_no' => $form['philhealth_no'],
                    'pagibig_no' => $form['pagibig_no'],
                    'with_atm' => $form['with_atm'],
                    'bankname_Empl' => $form['bankname_Empl'],
                    'bankaccount_Empl' => $form['bankaccount_Empl'],
                    'BloodType_Empl' => $form['BloodType_Empl'],
                    'EmergencyName_Empl' => $form['EmergencyName_Empl'],
                    'EmergencyAdd_Empl' => $form['EmergencyAdd_Empl'],
                    'EmergencyTelNo_Empl' => $form['EmergencyTelNo_Empl'],
                    'Position_Empl' => $form['Position_Empl'],
                    'count' => $form['count'],
                    'Department_Empl' => $form['Department_Empl'],
                    'shift_code' => $form['shift_code'],
                    'RateBasis' => $form['RateBasis'],
                    'RatePerDay_Empls' => $form['RatePerDay_Empls'],
                    'BasicSalary_Empls' => $form['BasicSalary_Empls'],
                    'DateHired_Empl' => $form['DateHired_Empl'],
                    'DateEffectivity_Empl' => $form['DateEffectivity_Empl'],
                    'DatTerminated_Empl' => $form['DatTerminated_Empl'],
                    'DateRetired' => $form['DateRetired'],
                    'Status_Empl' => $form['Status_Empl'],
                    'Type_Empl' => $form['Type_Empl'],
                    'office_location' => $form['office_location'],
                    'payroll_group' => $form['payroll_group'],
                    'with_bio' => $form['with_bio'],
                    'with_contract' => $form['with_contract'],
                    'pertrip' => $form['pertrip'],
                    'title_id' => $form['title_id'],
                    'branch_id' => $form['branch_id'],
                    'time_stamp' => $form['time_stamp'],
                    'RHouse_No' => $form['RHouse_No'],
                    'RSubd_Village' => $form['RSubd_Village'],
                    'RCity_Mun' => $form['RCity_Mun'],
                    'RZipcode' => $form['RZipcode'],
                    'RStreet' => $form['RStreet'],
                    'brgyid' => $form['brgyid'],
                    'RBrgy' => $form['RBrgy'],
                    'RProvince' => $form['RProvince'],
                    'PHouse_No' => $form['PHouse_No'],
                    'PSubd_Village' => $form['PSubd_Village'],
                    'PCity_Mun' => $form['PCity_Mun'],
                    'PZipcode' => $form['PZipcode'],
                    'PStreet' => $form['PStreet'],
                    'PBrgy' => $form['PBrgy'],
                    'PProvince' => $form['PProvince'],
                    'sal_grade_id' => $form['sal_grade_id'],
                    'recruitment_id' => $form['recruitment_id'],
                    'birthplace' => $form['birthplace'],
                    'government_ID' => $form['government_ID'],
                    'ID_License_Passport' => $form['ID_License_Passport'],
                    'Date_Place_Issuance' => $form['Date_Place_Issuance'],
                    'privilege' => $form['privilege'],
                    'Appoint_Status' => $form['Appoint_Status'],
                    'jo_group_no' => $form['jo_group_no'],
                    'with_pds' => $form['with_pds'],
                    'level' => $form['level'],
                    'indigenous_group' => $form['indigenous_group'],
                    'person_disablity' => $form['person_disablity'],
                    'solo_parent' => $form['solo_parent'],
                    'bp_number' => $form['bp_number'],
                    'designate_dept' => $form['designate_dept'],
                    'designate_pos' => $form['designate_pos'],
                    'designate_count' => $form['designate_count'],
                    'item_no' => $form['item_no'],
                    'authorized' => $form['authorized'],
                    'date_promotion' => $form['date_promotion'],
                    'level_csc' => $form['level_csc'],
                    'inactive_date' => $form['inactive_date'],
                    'shift_type' => $form['shift_type'],
                    'payrollType' => $form['payrollType'],
                    'group_pm' => $form['group_pm'],
                    'lc_earn_start_date_casual' => $form['lc_earn_start_date_casual'],
                    'lc_earn_start_date' => $form['lc_earn_start_date'],
                    'jo_wtax' => $form['jo_wtax'],
                    'citizen' => $form['citizen'],
                    'agency_no' => $form['agency_no'],
                    'period_from' => $form['period_from'],
                    'period_to' => $form['period_to'],
                    'old_emp_id' => $form['old_emp_id'],

                );
                db::table($this->pds_dum . ".employees")->insert($form);

    }

  
}