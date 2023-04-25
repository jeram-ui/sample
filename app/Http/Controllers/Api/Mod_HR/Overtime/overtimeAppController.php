<?php

namespace App\Http\Controllers\Api\Mod_HR\Overtime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class overtimeAppController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $Proc_db;


    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->Proc_db = $this->G->getProcDb();
    }


    // public function GetEmpName($id)
    // {

    //     $list = DB::table($this->hr_db.'.employee_information')
    //     // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
    //     ->where('DEPID', $id)
    //       ->get();
    //     return response()->json(new JsonResponse($list));
    // }
    public function getEmpRequest()
    {

        $list = DB::table($this->hr_db . '.employee_information')
            // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
            // ->where('DEPID', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getEmpPreparedby()
    {

        $list = DB::table($this->hr_db . '.employee_information')
            // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
            // ->where('DEPID', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetEmpName()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
            // ->where('DEPID', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function getDepartment()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }

    public function getOvertime()
    {
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'tbl_overtime.office_id')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime.req_by')
            ->select(
                '*',
                db::raw('SysPK_Dept', 'Name_Dept'),
                db::raw('PPID', 'NAME'),
                db::raw('tbl_overtime.status as overtime_status'),
                'department.SysPK_Dept',
                'tbl_overtime.overtime_id'
            )
            // ->where('tbl_overtime.status', 'Active')
            ->where("tbl_overtime.status", "!=", "Cancelled")
            ->where(function ($q)  {
                $q->where('req_by', Auth::user()->Employee_id)
                ->orwhere('prpared_by', Auth::user()->Employee_id);
            })

          // ->orWhere('AssistantHead_Dept', Auth::user()->Employee_id);
            // ->where('emp_id',Auth::user()->Employee_id)
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }

    public function getCert()
    {

        $list = DB::table($this->hr_db . '.tbl_overtime_cert_dtl')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime_cert_dtl.emp_id')
            ->join($this->hr_db . '.tbl_overtime_cert', 'tbl_overtime_cert.id', 'tbl_overtime_cert_dtl.cert_id', 'tbl_overtime_cert_dtl.id')
            // ->select('*',db::raw('cert_id', 'emp_id','date' ),'tbl_overtime_cert_dtl.cert_id','tbl_overtime_cert.id')
            ->where('tbl_overtime_cert.status', 'Approved')
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        $data['office'] = db::table($this->hr_db . '.tbl_overtime')->where('overtime_id', $id)->get();
        $data['formA'] = db::table($this->hr_db . '.tbl_overtime_memo')->where('overtime_id', $id)->get();
        $data['Empname'] = db::table($this->hr_db . '.tbl_overtime_dtl')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
            ->select("*",
                    'NAME',
                    'Emp_status',
                    'date_overtime',
                    'date_to',
                    db::raw("TIME_FORMAT(overtime_from,'%H:%i:%s') as overtime_from"),
                    db::raw("TIME_FORMAT(overtime_to,'%H:%i:%s') as overtime_to"),
                    'overtime_charge',
                    'rate_per_hr',
                    'memo',
                    'app_hrs',
                    'is_wholed_day',
                    )
            ->where('overtime_id', $id)
            ->get();

        // $data['formz'] =db::table($this->hr_db .'.sworn_assets')->where('mainID', $id)->get();


        return response()->json(new JsonResponse($data));
    }

    public function OverStore(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;


        $id = $form['overtime_id'];
        if ($id > 0) {
            db::table($this->hr_db . ".tbl_overtime")
                ->where('overtime_id', $id)
                ->update($form);


            db::table($this->hr_db . ".tbl_overtime_dtl")
                ->where("overtime_id", $id)
                ->delete();


            foreach ($formx as $key => $value) {
                $datx = array(
                    'overtime_id' => $id,
                    'emp_id' => $value['emp_id'],
                    // 'date_overtime' => $value['date_overtime'],
                    'date_overtime' => $value['date_overtime'],
                    'date_to' => $value['date_to'],
                    'date_from' => $value['date_overtime'],
                    'overtime_charge' => $value['overtime_charge'],
                    // 'overtime_to' => $value['overtime_to'],
                    'overtime_to' => $value['date_to'].' '. $value['overtime_to'],
                    // 'overtime_from' => $value['overtime_from'],
                    'overtime_from' => $value['date_overtime'].' '. $value['overtime_from'],
                    'rate_per_hr' => $value['rate_per_hr'],
                    'memo' => $value['memo'],
                    'Emp_status' => $value['Emp_status'],
                    'app_hrs' => $value['app_hrs'],
                    'is_wholed_day' => $value['is_wholed_day'],

                );
                db::table($this->hr_db . ".tbl_overtime_dtl")->insert($datx);
            }
        } else {
            db::table($this->hr_db . ".tbl_overtime")->insert($form);
            $id = DB::getPdo()->LastInsertId();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'overtime_id' => $id,
                    'emp_id' => $value['emp_id'],
                    // 'date_overtime' => $value['date_overtime'],
                    'date_overtime' => $value['date_overtime'],
                    'date_from' => $value['date_overtime'],
                    'date_to' => $value['date_to'],
                    'overtime_charge' => $value['overtime_charge'],
                    // 'overtime_to' => $value['overtime_to'],
                    'overtime_to' => $value['date_to'].' '. $value['overtime_to'],
                    // 'overtime_from' => $value['overtime_from'],
                    'overtime_from' => $value['date_overtime'].' '. $value['overtime_from'],
                    'rate_per_hr' => $value['rate_per_hr'],
                    'memo' => $value['memo'],
                    'Emp_status' => $value['Emp_status'],
                    'app_hrs' => $value['app_hrs'],
                    'is_wholed_day' => $value['is_wholed_day'],

                );
                db::table($this->hr_db . ".tbl_overtime_dtl")->insert($datx);
            }
        }
    }
    public function memoStore(Request $request)
    {

        $idx = $request->idx;
        $formA = $request->formA;
        $id = $idx;

        if ($id > 0) {

            db::table($this->hr_db . ".tbl_overtime_memo")
                ->where("overtime_id", $id)
                ->delete();


                    $datx = array(
                        'overtime_id' => $id,
                        'memoto' => $formA['memoto'],
                        'memodate' => $formA['memodate'],
                        'Subject' => $formA['Subject'],
                    );
                    db::table($this->hr_db . ".tbl_overtime_memo")->insert($datx);

        } else {

                $datx = array(
                    'overtime_id' => $id,
                    'memoto' => $formA['memoto'],
                    'memodate' => $formA['memodate'],
                    'Subject' => $formA['Subject'],
                );
                db::table($this->hr_db . ".tbl_overtime_memo")->insert($datx);
        }
    }
    public function getRef(Request $request)
    {
        $query = DB::select("SELECT CONCAT(LPAD(COUNT(*)+1,4,0),'-',DATE_FORMAT(NOW(),'%Y'))as 'NOS' FROM " . $this->hr_db . ".tbl_overtime");
        return response()->json(new JsonResponse(['data' => $query]));
    }
    // public function getRef(Request $request)
    // {
    //   $query = DB::select("SELECT CONCAT(LPAD(COUNT(*)+1,4,0),'-',DATE_FORMAT(NOW(),'%Y'))as 'NOS' FROM " . $this->hr_db . ".tbl_overtime");
    //   return response()->json(new JsonResponse(['data' => $query]));
    // }

    public function OverTimeCancel($id)
    {
        db::table($this->hr_db . '.tbl_overtime')
            ->where('overtime_id', $id)
            ->update(['status' => 'Cancelled']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function OvertimeHeadApproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::select("SELECT * FROM (SELECT
        `tbl_overtime`.`overtime_id`
        ,`Name_Dept`
        ,project_name
        ,ov_application_no
        ,ov_application_date
        ,purpose
        ,SUM(CASE WHEN ".$this->hr_db.".tbl_overtime_dtl.`budgetCntrl_by` IS NOT NULL THEN 1 ELSE 0 END) AS 'app'
        ,COUNT(*) AS 'row'
        FROM ".$this->hr_db.".tbl_overtime
        INNER JOIN ".$this->hr_db.".department
        ON(".$this->hr_db.".tbl_overtime.`office_id` = ".$this->hr_db.".department.`SysPK_Dept`)
        INNER JOIN ".$this->hr_db.".tbl_overtime_dtl
        ON(".$this->hr_db.".tbl_overtime.`overtime_id` = ".$this->hr_db.".tbl_overtime_dtl.`overtime_id`)
        where tbl_overtime.status = 'HEAD APPROVED'
        GROUP BY ".$this->hr_db.".tbl_overtime.`overtime_id`)A WHERE A.app = A.row");


        // DB::table($this->hr_db . '.tbl_overtime')
        //     ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
        //     ->join($this->hr_db . ".tbl_overtime_dtl", 'tbl_overtime_dtl.overtime_id', 'tbl_overtime.overtime_id')
        //     ->select( "*", select())
        //     // ->whereNull('dept_app_by')
        //     // ->where('tbl_overtime.status', $stat)
        //     // ->where('tbl_overtime_dtl.budgetCntrl_status', $stat)
        //     ->whereNotNull('tbl_overtime_dtl.budgetCntrl_status')
        //     ->groupby('tbl_overtime.overtime_id')
        //     // ->whereNull('tbl_overtime_dtl.budgetCntrl_by')
        //     ->get();


        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),


                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")


                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )


                    ->where('overtime_id', $value->overtime_id)
                    ->where('tbl_overtime_dtl.budgetCntrl_status', 'Approved')
                    // ->is_null('tbl_overtime_dtl.budgetCntrl_status', "")
                    ->get()
            );
            array_push($overtime, $over);


        }
        return response()->json(new JsonResponse($overtime));
    }
    public function OvertimeMayorApproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            // ->whereNull('dept_app_by')
            ->where('tbl_overtime.status', $stat)
            ->get();

        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)->get()
            );
            array_push($overtime, $over);
        }
        return response()->json(new JsonResponse($overtime));
    }
    public function BudgetControlApproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".tbl_overtime_dtl", 'tbl_overtime_dtl.overtime_id', 'tbl_overtime.overtime_id')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            ->where('tbl_overtime.status', $stat)
            ->whereNull('tbl_overtime_dtl.budgetCntrl_by')
            ->groupby('tbl_overtime.overtime_id')
            ->get();
        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->leftjoin($this->hr_db . ".paygroup_setup", 'paygroup_setup.paygroup_id', 'employee_information.payroll_group')
                    ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'employee_information.designate_dept')
                    ->select(
                        'tbl_overtime_dtl.id',
                        db::raw('"false" as budgetCntrl_status'),
                        'NAME',
                        'Emp_status',
                        db::raw("CASE WHEN employee_information.payroll_group > 0 THEN paygroup_setup.paygroup_name ELSE Name_Dept end as 'Project Name'"),
                        'date_overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)
                    ->whereNull("tbl_overtime_dtl.budgetCntrl_status")
                    // ->Where(function ($query) {
                    //     $query->Where('Head_Dept', Auth::user()->Employee_id);
                    //     // ->orWhere('AssistantHead_Dept', Auth::user()->Employee_id);
                    // })

                    ->get()
            );
            array_push($overtime, $over);
        }

        // foreach ($list as $key => $value) {

        //     $over = array(
        //         'overtime_id' => $value->overtime_id,
        //         'Name_Dept' => $value->Name_Dept,
        //         'project_name' => $value->project_name,
        //         'ov_application_no' => $value->ov_application_no,
        //         'ov_application_date' => $value->ov_application_date,
        //         'purpose' => $value->purpose,
        //         // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

        //         'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
        //         ->where('overtime_id', $value->overtime_id)
        //          ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".OVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
        //          as Total"))
        //           ->get(),

        //         'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
        //             ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
        //             ->join($this->hr_db . ".tbl_overtime", 'tbl_overtime.overtime_id', 'tbl_overtime_dtl.overtime_id')
        //             ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
        //             ->join($this->hr_db . ".budget_controller", 'budget_controller.dept_id', 'tbl_overtime.office_id')
        //             ->select(
        //                 'tbl_overtime_dtl.id',
        //                 db::raw('"false" as budgetCntrl_status'),
        //                 'NAME',
        //                 'Emp_status',
        //                 'date_from',
        //                 'overtime_from',
        //                 'overtime_to',
        //                 'memo',
        //                 'app_hrs',
        //                 db::raw("format(`rate_per_hr`, 2) as PerHour"),

        //                 // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

        //                 db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".OVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
        //                 as Total")
        //         )
        //             ->where('tbl_overtime_dtl.overtime_id', $value->overtime_id)
        //             ->where('tbl_overtime_dtl.Emp_status', ['Permanent', 'Casual'])

        //             ->Where(function ($query) {
        //                 $query->Where('budget_controller.emp_id', Auth::user()->Employee_id);
        //                 // $query->Where('budget_controller.Fund', 'tbl_overtime.Fund');

        //                 // ->orWhere('AssistantHead_Dept', Auth::user()->Employee_id);
        //             })
        //             ->get()
        //     );
        //     array_push($overtime, $over);
        // }
        return response()->json(new JsonResponse($overtime));
    }
    public function OvertimeRecommended(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            // ->whereNull('dept_app_by')
            ->where('tbl_overtime.status', $stat)
            ->get();
        return response()->json(new JsonResponse($list));
    }

    // public function OvertimeMayorApproval(Request $request)
    // {
    //     $stat = $request->status;
    //     $list = DB::table($this->hr_db . '.tbl_overtime')
    //     ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
    //     // ->whereNull('dept_app_by')
    //         ->where('tbl_overtime.status', $stat)
    //         ->get();
    //     return response()->json(new JsonResponse($list));
    // }

    public function OvertimeHeadfundapproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            // ->whereNull('dept_app_by')
            ->where('tbl_overtime.status', $stat)
            ->get();

        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)->get()
            );
            array_push($overtime, $over);
        }
        return response()->json(new JsonResponse($overtime));
    }

    public function OvertimeHeadList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            // ->where("Head_Dept", Auth::user()->Employee_id)
            ->where('tbl_overtime.status', $stat)
            // ->orWhere('ir_head', Auth::user()->Employee_id)
            ->Where(function ($query) {
                $query->Where('tbl_overtime.req_by', Auth::user()->Employee_id);
                // ->orWhere('AssistantHead_Dept', Auth::user()->Employee_id);
            })
            ->get();
        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)->get()
            );
            array_push($overtime, $over);
        }

        return response()->json(new JsonResponse($overtime));
    }
    public function OvertimeHeadListApproved(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            ->where("recom_by", Auth::user()->Employee_id)
            ->orderBy("tbl_overtime.recom_date", "desc")
            ->limit(100)
            ->get();

        $overtime = array();
        foreach ($list as $key => $value) {
            // $total =  DB::table($this->hr_db . '.tbl_overtime_dtl')
            // ->where('overtime_id', $value->overtime_id)
            //  ->select(db::raw("format(SUM(CASE WHEN tbl_overtime_dtl.`Emp_status` IN ('Permanent','Casual')
            //  THEN (tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".OVERTIMEMULTIPLIER(overtime_from,Emp_status) ELSE tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs` END), 2)
            //   as Total"))
            //   ->get();
            //   $totalz = "";
            // foreach ($total as $key => $valuex) {
            //     $totalz = $valuex->Total;
            // }
            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                'total' =>  DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * " . $this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'date_overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        // db::raw("cast(format('rate_per_hr', 2)as decimal(26,2)) as Rate Per Hours"),

                        // 'rate_per_hr',
                        'app_hrs',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),
                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                        // db::raw("format(SUM(CASE WHEN tbl_overtime_dtl.`Emp_status` IN ('Permanent','Casual')
                        // THEN (tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * OVERTIMEMULTIPLIER(overtime_from,Emp_status) ELSE tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs` END), 2)
                        //  as Total")

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours")
                    )
                    ->where('overtime_id', $value->overtime_id)->get()
            );
            array_push($overtime, $over);
        }
        return response()->json(new JsonResponse($overtime));
    }


    public function OvertimeHeadApprovalApproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'HEAD APPROVED', 'recom_status' => 'APPROVED', 'recom_by' => Auth::user()->Employee_id, 'recom_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function OvertimeHeadApprovalDisapproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'DISAPPROVED', 'recom_status' => 'DISAPPROVED', 'recom_by' => Auth::user()->Employee_id, 'recom_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function OvertimeHeadApprovalNoted(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'HEAD NOTED', 'dept_app_status' => 'APPROVED', 'dept_not_by' => Auth::user()->Employee_id, 'dept_not_dateTime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function OvertimeforHeadNotedDisapproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'DISAPPROVED', 'dept_app_status' => 'DISAPPROVED', 'dept_not_by' => Auth::user()->Employee_id, 'dept_not_dateTime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function OvertimeMayorApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            ->where("approved_by", Auth::user()->Employee_id)
            ->orderBy("approved_date", "desc")
            ->limit(100)
            ->get();

        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)->get()
            );
            array_push($overtime, $over);
        }
        return response()->json(new JsonResponse($overtime));
    }
    public function OvertimeforMayor(Request $request)
    {
        $app = db::select("CALL " . $this->lgu_db . ".jay_display_lgu_signatory('%MUN CITY MAYOR%')");
        $MayorId = 0;
        foreach ($app as $key => $value) {
            $MayorId = $value->Signatory_PP_ID;
        }
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'APPROVED', 'approved_by' => $MayorId, 'mayor_app_status' => 'APPROVED', 'mayor_autho_by' => Auth::user()->Employee_id, 'approved_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function OvertimeforMayorDisapproved(Request $request)
    {
        $app = db::select("CALL " . $this->lgu_db . ".jay_display_lgu_signatory('%MUN CITY MAYOR%')");
        $MayorId = 0;
        foreach ($app as $key => $value) {
            $MayorId = $value->Signatory_PP_ID;
        }

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'DISAPPROVED', 'approved_by' => $MayorId, 'mayor_app_status' => 'DISAPPROVED', 'mayor_autho_by' => Auth::user()->Employee_id, 'approved_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function OvertimeAppropriation(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'Appropriated', 'as_app_status' => 'APPROVED', 'as_app' => Auth::user()->Employee_id, 'as_app_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function OvertimeBudgetCntrlApproved(Request $request)
    {
        try {
            DB::beginTransaction();
            $dtls = $request->dtls;
            foreach ($dtls as $key => $value) {

              if ($value['budgetCntrl_status'] === 'true') {
                // log::debug($dtls);

                db::table($this->hr_db . ".tbl_overtime_dtl")->where("id", $value['id'])
                  ->update(['status' => 'Approved', 'budgetCntrl_status' => 'Approved',
                            'budgetCntrl_date' => $this->G->serverdatetime(),
                            'budgetCntrl_by' => Auth::user()->Employee_id
                        ]);

              }
            //    else {
            //     db::table($this->hr_db . ".tbl_overtime_dtl")->where("id", $value['id'])
            //       ->update(['status' => 'Disapproved', 'budgetCntrl_status' => 'Disapproved',
            //                 'budgetCntrl_date' => $this->G->serverdatetime(),
            //                 'budgetCntrl_by' => Auth::user()->Employee_id]);
            //   }
            }
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
          } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $th, 'status' => 'error']));
          }
        // $list =  $request->list;
        // foreach ($list as $key => $value) {
        //     db::table($this->hr_db . '.tbl_overtime_dtl')
        //         ->where("overtime_id", $value['overtime_id'])
        //         ->update(['status' => 'Budget Controlled', 'budgetCntrl_status' => 'Approved', 'budgetCntrl_by' => Auth::user()->Employee_id, 'budgetCntrl_date' => $this->G->serverdatetime()]);
        // }
        // return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }


    public function OvertimeRecommendedApproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'Recommended', 'recom_status' => 'APPROVED', 'recom_by' => Auth::user()->Employee_id, 'recom_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function OvertimefundApproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'Funded', 'as_fund_status' => 'APPROVED', 'as_fund' => Auth::user()->Employee_id, 'as_fund_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function OvertimeAppropriationApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            ->where("as_app", Auth::user()->Employee_id)
            ->orderBy("as_app_date", "desc")
            ->limit(100)
            ->get();

        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)->get()
            );
            array_push($overtime, $over);
        }
        return response()->json(new JsonResponse($overtime));
    }
    public function OvertimeAsFundApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            ->where("as_fund", Auth::user()->Employee_id)
            ->orderBy("as_fund_date", "desc")
            ->limit(100)
            ->get();

        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)->get()
            );
            array_push($overtime, $over);
        }
        return response()->json(new JsonResponse($overtime));
    }
    // public function budgetControlApprovedList(Request $request)
    // {
    //     $stat = $request->status;
    //     $list = DB::table($this->hr_db . '.tbl_overtime')
    //     ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
    //         ->where("budgetCntrl_by", Auth::user()->Employee_id)
    //         ->orderBy("budgetCntrl_date", "desc")
    //         ->limit(100)
    //         ->get();

    //         $overtime = array() ;
    //         foreach ($list as $key => $value) {
    //            $over = array(
    //                'overtime_id'=> $value->overtime_id,
    //                'Name_Dept'=> $value->Name_Dept,
    //                'project_name'=> $value->project_name,
    //                'ov_application_no'=> $value->ov_application_no,
    //                'ov_application_date'=> $value->ov_application_date,
    //                'purpose'=> $value->purpose,
    //                'dtls'=>db::table($this->hr_db . ".tbl_overtime_dtl")
    //                ->join($this->hr_db . ".employee_information",'employee_information.PPID','tbl_overtime_dtl.emp_id')
    //             //    ->select("*", db::raw("TIMEDIFF(`overtime_to`, `overtime_from`), (TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"))
    //                ->select('NAME','date_overtime','overtime_from','overtime_to','memo','rate_per_hr',
    //                db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"))
    //                ->where('overtime_id',$value->overtime_id)->get()
    //            );
    //            array_push($overtime,$over);
    //         }
    //     return response()->json(new JsonResponse($overtime));
    // }
    public function budgetControlApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')

            ->where("budgetCntrl_by", Auth::user()->Employee_id)
            ->orderBy("budgetCntrl_date", "desc")
            ->limit(100)
            ->get();

        $overtime = array();
        foreach ($list as $key => $value) {

            $over = array(
                'overtime_id' => $value->overtime_id,
                'Name_Dept' => $value->Name_Dept,
                'project_name' => $value->project_name,
                'ov_application_no' => $value->ov_application_no,
                'ov_application_date' => $value->ov_application_date,
                'purpose' => $value->purpose,
                // 'total' => db::select("call " . $this->hr_db . ".rans_overtime_total(?)", [$value->overtime_id]),

                'total' => DB::table($this->hr_db . '.tbl_overtime_dtl')
                ->where('overtime_id', $value->overtime_id)
                 ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                 as Total"))
                  ->get(),

                'dtls' => db::table($this->hr_db . ".tbl_overtime_dtl")
                    ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                    ->select(
                        'NAME',
                        'Emp_status as Employee Status',
                        'date_overtime as Date Overtime',
                        db::raw("TIME_FORMAT(`overtime_from`, '%r') as 'Time From'"),
                        db::raw("TIME_FORMAT(`overtime_to`, '%r') as 'Time To'"),
                        'memo',
                        'app_hrs as Approved Hours',
                        db::raw("format(`rate_per_hr`, 2) as PerHour"),

                        // db::raw("(TIME_TO_SEC(TIMEDIFF(`overtime_to`, `overtime_from`))/60)/60 as no_hours"),

                        db::raw("format((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status), 2)
                        as Total")
                )
                    ->where('overtime_id', $value->overtime_id)
                    ->get()
            );
            array_push($overtime, $over);
        }
        return response()->json(new JsonResponse($overtime));
    }

    public function OvertimeRecommendApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'tbl_overtime.office_id')
            ->where("recom_by", Auth::user()->Employee_id)
            ->orderBy("recom_date", "desc")
            ->limit(100)
            ->get();
        return response()->json(new JsonResponse($list));
    }


    public function OvertimeAppropriationDisapproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'DISAPPROVED', 'as_app_status' => Auth::user()->Employee_id, 'as_app_status' => 'DISAPPROVED',  'as_app_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function OvertimebudgetCntrlDisapproved(Request $request)
    {
        try {
            DB::beginTransaction();
            $dtls = $request->dtls;
            foreach ($dtls as $key => $value) {

              if ($value['budgetCntrl_status'] === 'true') {
                // log::debug($dtls);

                db::table($this->hr_db . ".tbl_overtime_dtl")->where("id", $value['id'])
                      ->update(['status' => 'Disapproved', 'budgetCntrl_status' => 'Disapproved',
                                'budgetCntrl_date' => $this->G->serverdatetime(),
                                'budgetCntrl_by' => Auth::user()->Employee_id]);
              }
            //    else {
            //     db::table($this->hr_db . ".tbl_overtime_dtl")->where("id", $value['id'])
            //       ->update(['status' => 'Disapproved', 'budgetCntrl_status' => 'Disapproved',
            //                 'budgetCntrl_date' => $this->G->serverdatetime(),
            //                 'budgetCntrl_by' => Auth::user()->Employee_id]);
            //   }
            }
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
          } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $th, 'status' => 'error']));
          }
        }
    // {

    //     $list =  $request->list;
    //     foreach ($list as $key => $value) {
    //         db::table($this->hr_db . '.tbl_overtime')
    //             ->where("overtime_id", $value['overtime_id'])
    //             ->update(['status' => 'disapproved', 'budgetCntrl_status' => Auth::user()->Employee_id, 'budgetCntrl_status' => 'disapproved',  'budgetCntrl_date' => $this->G->serverdatetime()]);
    //     }
    //     return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    // }

    public function OvertimeRecommendedDisapproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'DISAPPROVED', 'recom_status' => Auth::user()->Employee_id, 'recom_status' => 'DISAPPROVED',  'recom_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function OvertimefundDisapproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_overtime')
                ->where("overtime_id", $value['overtime_id'])
                ->update(['status' => 'DISAPPROVED', 'as_fund_status' => Auth::user()->Employee_id, 'as_fund_status' => 'DISAPPROVED',  'as_fund_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function print(Request $request)
    {
        try {
            $form = 413;
            $main = db::table($this->Proc_db . '.tbl_pr_main')
                ->where('id', $form)
                ->get();
            // $main =db::select("CALL" . $this->Proc_db.".getPR_main()");

            $mainData = "";

            foreach ($main as $key => $value) {
                $mainData = $value;
            }

            $mainx = db::table($this->Proc_db . '.tbl_pr_detail')
                ->where('main_id', $form)
                ->get();
            $mainDatax = "";

            $totalx = 0;
            $x = 1;

            foreach ($mainx as $key => $value) {
                $z = $x++;
                $totalx = $totalx + $value->total_cost;
                $mainDatax .= '
                    <tr>
                <td width="7%" height="12px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black" align="center">' . $z . '</td>
                <td width="7%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . $value->qty . '</td>

                <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . $value->unit_measure . '</td>
                <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . $value->item_name . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . number_format($value->unit_cost, 2) . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . number_format($value->total_cost, 2) . '</td>
            </tr>';
            }

            // if(count($mainx)< 28){
            //         for($i = count($mainx); $i<28; $i++){
            //             $mainDatax .=' <tr>
            //             <td width="7%" height="12px" style="border-bottom:1px solid black; border-right:1px solid black;
            //             border-left:1px solid black" align="center"></td>
            // <td width="7%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>

            // <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // </tr>' ;
            //         }
            //     }



            $Template = '<table cellpadding="1">
                <tr>
                    <th width="35%" align="right">
                    <img src="' . public_path() . '/img/logo1.png"  height="30" width="30">
                    </th>
                    <th width="35%" style="font-size:9pt;  word-spacing:30px" align="center">
                            Republic of the Philippines
                    <br />
                            Province of Cebu
                    <br />

                        CIty of Naga
                    <br />
                    <br />
                        </th>

                    <th align="left">
                    <img src="' . public_path() . '/img/logo2.png"  height="35" width="60">
                    </th>
                 </tr>
                 <tr>
                 <td width="80%"></td>
                 <td width="7%"><b>TXN #:</b></td>
                 <td width="13%">' . $mainData->txn_num . '</td>
         </tr>
                </table >
        <table cellpadding="2">
            <tr>
            <td width="100%" height="18px" style="border-bottom:1px solid black; border-top:1px solid black;
                        border-right:1px solid black;  border-left:1px solid black; font-size:12pt" align="center">
                        <b>PURCHASE REQUEST</b></td>
            </tr>
            <tr>
                <td width="80%" height="13px" align="right" style="border-bottom:1px solid black; border-right:1px solid black;
                                border-left:1px solid black ">
                <b><i>Account Code: </i></b>
                </td>
                <td width="20%" style="border-bottom:1px solid black; border-right:1px solid black"></td>
            </tr>
            <tr>
                <td width="14%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Departmemt:</b></td>
                <td width="44%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->dept . '</td>
                <td width="8%" style="border-bottom:1px solid black; border-right:1px solid black"><b> PR No:</b></td>
                <td width="34%"  style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->pr_no . '</td>
            </tr>
            <tr>
                <td width="58%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"></td>
                <td width="8%" style="border-bottom:1px solid black; border-right:1px solid black"><b> PR Date:</b></td>
                <td width="34%"  style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->pr_date . '</td>
            </tr>
            <tr>
                <td width="58%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black"></td>
                <td width="21%"  style="border-bottom:1px solid black; border-right:1px solid black"></td>
            </tr>
            <tr>
                <td width="14%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Section:</b></td>
                <td width="44%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->section_name . '</td>
                <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black"><b> SAI No:</b></td>
                <td width="13%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->sai_no . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black"><b> Date:  ' . $mainData->sai_date . '</b></td>
            </tr>
             <tr>
                <th rowspan="1.5" width="7%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black; background-color:#bdbdbd  " align="center"><b>Item No.</b></th>
                <td width="7%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Qty</b></td>

                <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Unit of Measure</b></td>
                <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><br /><b>Item Description</b></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Estimated Unit Cost</b></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Estimated Cost</b></td>
            </tr>
          ' . $mainDatax . '

            <tr>
                <td width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black" ><b> Delivery Term:</b></td>
                <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black" >' . $mainData->terms . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black; font-size:10pt" align="center"><b>TOTAL</b></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"><b>' . number_format($totalx, 2) . '</b></td>
            </tr>
            <tr>
                <td width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Purpose:</b></td>
                <td width="78%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->pr_description . '</td>
            </tr>
            <tr>
                <td width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"></td>
                <td width="30%" style="border-bottom:1px solid black; border-right:1px solid black"><b> Requested By</b></td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black"><b> Cash Availability</b></td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black"><b> Approved</b></td>
            </tr>
            <tr>
                <td height="20px" width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Signature:</b></td>
                <td width="30%" style="border-bottom:1px solid black; border-right:1px solid black"></td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black"></td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black"></td>
            </tr>
            <tr>
                <td height="12px" width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Printed Name:</b></td>
                <td width="30%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">ROWENA REPOLLO ARNOZA</td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">ANNA MARIA BACON GABILAN</td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">ATTY. KRISTINE VANESSA TADIWAN CHIONG</td>
            </tr>
            <tr>
                <td height="12px" width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Designation:</b></td>
                <td width="30%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"><b>CGDH I (City Government Department Head) I</b></td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"><b>City Treasurer I</b></td>
                <td width="24%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"><b>City Mayor</b></td>
            </tr>
            <tr>
                <td height="12px" width="52%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> OK AS TO APPROPRIATION</b></td>
                <td width="48%" style="border-bottom:1px solid black; border-right:1px solid black"><b> OK AS TO ALLOTMENT</b></td>
            </tr>
            <tr>
                <td height="20px" width="52%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"></td>
                <td width="48%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            </tr>
            <tr>
                <td height="12px" width="52%" style="border-bottom:1px solid black; border-right:1px solid black;
                         border-left:1px solid black; font-size:7pt" align="center">CERTERIA VILLARICO BUENAVISTA</td>
                <td width="48%" style="border-bottom:1px solid black; border-right:1px solid black; font-size:7pt"
                         align="center">KELVIN RAY LAPINING ABABA</td>
                </tr>
            <tr>
                <td height="12px" width="52%" style="border-bottom:1px solid black; border-right:1px solid black;
                         border-left:1px solid black" align="center"><b>Budget Officer</b></td>
                <td width="48%" style="border-bottom:1px solid black; border-right:1px solid black"
                         align="center"><b>City Accountant</b></td>
            </tr>
        </table>
               ';
            PDF::SetTitle('Sworn Statement of Assets, Liabilities and Net Worth');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('P');
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }


    public function printOvertime(Request $request)
    {
        try {
            $form = $request->itm;
            $overtime_id = $form['overtime_id'];

            $Data = db::table($this->hr_db . '.tbl_overtime')
                ->join($this->hr_db . '.tbl_overtime_dtl', 'tbl_overtime_dtl.overtime_id', 'tbl_overtime.overtime_id')
                ->leftjoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                // ->select(db::raw("format(sum((tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)), 2)
                //  as Total_all"))
                //    ->select("*", db::raw($this->hr_db.'.GET_EmployeeName_by_ID(tbl_overtime.req_by) as requestName'))

                // ->select("*",db::raw('concat(lastname,", ",firstname," ",middlename,".") as Fullname'),
                //             db::raw('concat(purok," ",barangay," ",cityName,", ",provinceName) as Address'))
                ->where('tbl_overtime.overtime_id', $form['overtime_id'])
                ->get();
            $infoData = "";




            $datarow = db::select("call " . $this->hr_db . ".rans_display_tbl_overtime_report_new(?)", [$overtime_id]);
            $row = [];

            foreach ($datarow as $key => $value) {
                $row = $value;

            }


            $memorandum = "";
            if (count($Data) == 1) {
                foreach ($Data as $key => $value) {
                    $memorandum = $value->NAME;
                }
            } else {
                $count=0;
                $name="";
                foreach ($Data as $key => $value) {


                    if ($key>0) {
                        if ($value->NAME !==  $name) {
                            $count=$count+1;
                        }

                    }
                    $name=$value->NAME;
                }
                if ($count>0) {
                    foreach ($Data as $key => $value) {
                        if ($key == 0) {
                        $memorandum = $value->NAME . " et.al";
                    }
                 }

                }else{
                    foreach ($Data as $key => $value) {
                        if ($key == 0) {
                        $memorandum = $value->NAME;
                    }

                }
              }
           }

            foreach ($Data as $key => $value) {
                $infoData = $value;


            }




            $table = db::table($this->hr_db . '.tbl_overtime_dtl')
                ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime_dtl.emp_id')
                ->select("*",
                        db::raw("TIMEDIFF( `overtime_to`, `overtime_from`), (TIME_TO_SEC(TIMEDIFF( `overtime_to`, `overtime_from`))/60)/60 as no_hours"),
                        db::raw("(tbl_overtime_dtl.`rate_per_hr` * tbl_overtime_dtl.`app_hrs`) * ".$this->hr_db .".getOVERTIMEMULTIPLIER(overtime_from,Emp_status)
                        as Total")
                    )
                ->where('overtime_id', $form['overtime_id'])
                ->get();
            $tabledata = "";
            // log::debug($table);

            $totalAll = 0;

            foreach ($table as $key => $value) {


                $totalAll = $totalAll + $value->Total;

                // $height += 5;
                $key += 1;
                $tabledata .= '<tr>
                    <td width="3%" align="center">' . $key . '</td>
                    <td width="20%" align="center">' . $value->NAME . '</td>
                    <td width="7%" style="font-size:8pt" align="center">' . $value->Emp_status . '</td>
                    <td width="8%" align="center">' . number_format($value->rate_per_hr, 2) . '</td>
                    <td width="20%" align="center">' . $value->memo . '</td>
                    <td width="10%" align="center">' . $value->date_overtime . '</td>
                    <td width="18%" align="center">' . (!empty($value->overtime_from) ? (date_format(date_create($value->overtime_from), "h i A")) : "") . '  - ' . (!empty($value->overtime_to) ? (date_format(date_create($value->overtime_to), "h i A")) : "") . '</td>
                    <td width="6%" align="center">' . number_format($value->app_hrs, 2) . '</td>
                    <td width="8%" align="center">' .number_format($value->Total, 2) . '</td>
                </tr>
                ';
            }

            $Template = '<table cellpadding="1">
            <tr>
                <th width="32%" align="right">
                <img src="' . public_path() . '/img/logo1.png"  height="60" width="60">
                </th>
                <th width="38%" style="font-size:9pt;  word-spacing:30px" align="center">
                        Republic of the Philippines
                <br />
                        Province of Cebu
                <br />

                    CIty of Naga
                <br />
                <br />
                    </th>

                <th align="left">
                <img src="' . public_path() . '/img/logo2.png"  height="60" width="60">
                </th>
             </tr>
             <tr>
                <th width="100%" style="font-size:15pt" align="center"><b>' . $infoData->office . '</b>
                </th>
             </tr>
            </table >
            <br />
            <br />
            <br />
            <table cellpadding="1">
                <tr>
                    <td width="17%"><b>Date:</b></td>
                    <td width="30%" style="border-bottom:1px solid black">' . $infoData->ov_application_date . '</td>
                    <td width="21%"><b></b></td>
                    <td width="14%"><b>Application No.:</b></td>
                    <td width="18%" style="border-bottom:1px solid black">' . $infoData->ov_application_no . '</td>

                </tr>
                <tr>
                <td width="17%"><b>Memorandum To:</b></td>
                <td width="30%" style="border-bottom:1px solid black">' . $memorandum . '</td>
                </tr>
            <br />
            <tr>
                <td width="100%">' . $infoData->purpose . '
                </td>
            </tr>
            <br />

            <tr>
                <td width="100%">
                    <table border="1">

                        <tr>
                            <td width="3%" style="font-size:8pt" align="center"><b>No.</b></td>
                            <td width="20%" style="font-size:8pt" align="center"><b>Employee Name</b></td>
                            <td width="7%" style="font-size:8pt" align="center"><b>Employee Status</b></td>
                            <td width="8%" style="font-size:8pt" align="center"><b>Rate Per Hour</b></td>
                            <td width="20%" style="font-size:8pt" align="center"><b>Specific Duties & Responsibilities</b></td>
                            <td width="10%" style="font-size:8pt" align="center"><b>Date</b></td>
                            <td width="18%" style="font-size:8pt" align="center"><b>Time</b></td>
                            <td width="6%"  style="font-size:8pt" align="center">No. of Hours</td>
                            <td width="8%"  style="font-size:8pt" align="center">Total</td>

                        </tr>
                       ' . $tabledata . '
                    </table>
                </td>
            </tr>
            <br />
            <tr>
                <td width="85%"></td>
                <td width="5%" style="color:red"><b>Total:</b></td>
                <td width="10%" style="border-bottom:1px solid black" align="center"><b>'.number_format($totalAll, 2).'</b></td>
            </tr>

            <br/>
            <tr>
                <td width="5%"></td>
                <td width="95%"><b><i>You are entitled to collect pay pursuant to accounting rules and regulations.</i></b></td>
            </tr>
            <br />

            <tr>
                <td width="40%"><b>Requested By:</b></td>
                <td width="20%"></td>
                <td width="40%"><b>O.K as to Appropriation:</b></td>
            </tr>
            <br />
            <tr>

                <td width="20%" style="border-bottom:1px solid black" align="center">' . $row->{'req By'} . '</td>
                <td width="20%" style="border-bottom:1px solid black" align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'ReqSig'} . '"></td>
                <td width="20%"></td>
                <td width="20%" style="border-bottom:1px solid black; font-size: 8pt" align="center">' . $row->{'App By'} . '</td>
                <td width="20%" style="border-bottom:1px solid black" align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'appSig'} . '"></td>
            </tr>
            <tr>
                <td width="40%" align="center"><b>Section/Department Head</b></td>
                <td width="20%"></td>
                <td width="40%" align="center"><b>City Budget Officer</b></td>
            </tr>
            <br />
            <br />
            <tr>
                <td width="40%"></td>
                <td width="20%"></td>
                <td width="40%"><b>O.K as to Funds:</b></td>
            </tr>
            <br />
            <tr>
                <td width="20%" ></td>
                <td width="20%" ></td>
                <td width="20%"></td>
                <td width="20%" style="border-bottom:1px solid black" align="center">' . $row->{'Fund By'} . '</td>
                <td width="20%" style="border-bottom:1px solid black" align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'FundSig'} . '"></td>

            </tr>
            <tr>
                <td width="40%" align="center"></td>
                <td width="20%"></td>
                <td width="40%" align="center"><b>City Treasurer</b></td>
            </tr>
            <br />
            <br />
            <tr>
                <td width="100%" align="center"><b>Approved By:</b></td>
            </tr>
            <br/>
            <tr>
                <td width="25%"></td>
                <td width="25%" style="border-bottom:1px solid black" align="center">' . $row->{'Approved By'} . '</td>
                <td width="25%" style="border-bottom:1px solid black" align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'ApprvdSig'} . '"></td>
                <td width="25%"></td>
            </tr>
            <tr>
                <td width="100%" align="center"><b>City Mayor</b></td>
            </tr>

            </table>
            ';

            PDF::SetTitle('Overtime Application');
            // PDF::AddPage('P');

            if (count($table) > 20){
                PDF::SetFont('helvetica', '', 8);
                PDF::AddPage('P', array(215.9, 330.2 ));
            }else{
                PDF::SetFont('helvetica', '', 9);
                PDF::AddPage('P', array(215.9, 279.4 ));
            }

            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');

            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
