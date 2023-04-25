<?php

namespace App\Http\Controllers\Api\Mod_Performance\empMFO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class empMFOController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $prfrmnce_db;


    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->prfrmnce_db = $this->G->getPerformance();
    }
    public function getAuthName(Request $request)
    {
        $list = DB::table($this->hr_db . '.employees')
        ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
          ->where('SysPK_Empl',Auth::user()->Employee_id)
          ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetDept()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function GetName($id)
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->where('DEPID', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetPrepared()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetApproved()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function showMFO($id)
    {
        $list = db::select("call " . $this->prfrmnce_db . ".show_mfo(?)", [$id]);
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $formz = $request->formz;
        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->prfrmnce_db . '.setup_ipcr')
                ->where('id', $id)
                ->update($form);

            // db::table($this->prfrmnce_db . '.setup_ipcr_details')
            //     ->where("setup_ipcr_id", $id)
            //     ->delete();
            foreach ($formx as $key => $value) {
                $formxData = array(
                    'setup_ipcr_id' => $id,
                    'function_type' => $value['function_type'],
                    'sorting' => $value['sorting'],
                    'mfo_id' => $value['id'],
                    'selectx' => $value['selectx'],
                    'martix_id' => $value['martix_id'],

                );
                // if ($value['selectx'] === 'true') {
                    // if ($value['id'] > 0) {
                        db::table($this->prfrmnce_db . '.setup_ipcr_details')
                        ->where("martix_id",$value['martix_id'])
                        ->where("setup_ipcr_id",$id)
                        ->update($formxData);
                    // }else{
                        // db::table($this->prfrmnce_db . '.setup_ipcr_details')->insert($formxData);
                    // }
              
                // }
            }
            db::table($this->prfrmnce_db . '.setup_ipcr_reviewed')
            ->where("ipcr_entry_id", $id)
            ->delete();
            foreach ($formz as $key => $valuex) {
                $formzData = array(
                    'ipcr_entry_id' => $id,
                    'date' => $valuex['date'],
                    'reviewed_by' => $valuex['reviewed_by'],
                );
                db::table($this->prfrmnce_db . '.setup_ipcr_reviewed')->insert($formzData);
            }
        } else {
            $chk = db::table($this->prfrmnce_db . '.setup_ipcr')
            ->where("empName", $form['empName'])
            ->where("dept_id", $form['dept_id'])
            ->where("period_id", $form['period_id'])
            ->where("setup_ipcr.status", 0)
            ->count();
            log::debug($chk);
            if ($chk > 0) {
                return response()->json(
                    new JsonResponse([
                        'Message' => 'Already Exist',
                        'status' => 'Error',
                        // 'errormsh' => $e,
                    ])
                );
            } else {
                
                DB::table($this->prfrmnce_db . '.setup_ipcr')->insert($form);
                $id = DB::getPdo()->LastInsertId();

                foreach ($formx as $key => $value) {
                    $formxData = array(
                        'setup_ipcr_id' => $id,
                        'function_type' => $value['function_type'],
                        'sorting' => $value['sorting'],
                        'mfo_id' => $value['id'],
                        'selectx' => $value['selectx'],
                        'martix_id' => $value['matrix_id'],
                    );
                    
                    log::debug($formxData);
                    db::table($this->prfrmnce_db . '.setup_ipcr_details')->insert($formxData);
                }
                foreach ($formz as $key => $valuex) {
                    $formzData = array(
                        'ipcr_entry_id' => $id,
                        'date' => $valuex['date'],
                        'reviewed_by' => $valuex['reviewed_by'],
                    );
                    db::table($this->prfrmnce_db . '.setup_ipcr_reviewed')->insert($formzData);
                }
            }
        }
        return  $this->G->success();
    }
    public function storeCopyDat(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $formz = $request->formz;
            $chk = db::table($this->prfrmnce_db . '.setup_ipcr')
            ->where("empName", $form['empName'])
            ->where("dept_id", $form['dept_id'])
            ->where("period_id", $form['period_id'])
            ->where("setup_ipcr.status", 0)
            ->count();
            log::debug($chk);
            if ($chk > 0) {
                return response()->json(
                    new JsonResponse([
                        'Message' => 'Already Exist',
                        'status' => 'Error',
                        // 'errormsh' => $e,
                    ])
                );
            } else {

                $datax = array(
                    'dept_id' => $form['dept_id'],
                    'emp_id' => $form['emp_id'],
                    'empName' => $form['empName'],
                    'period_id' => $form['period_id'],
                    'prepared_date' => $form['prepared_date'],
                    'prepared_by' => $form['prepared_by'],
                    'approved_by' => $form['approved_by'],
                    'approved_date'=> $form['approved_date'],
                    'final_rating_by'=> $form['final_rating_by'],
                    'final_rating_date'=> $form['final_rating_date'],
                    'reviewed_by'=> $form['reviewed_by'],
                    'reviewed_date'=> $form['reviewed_date'],
                    // 'job_desc' => $form['job_desc'],

                );
                DB::table($this->prfrmnce_db . '.setup_ipcr')->insert($datax);
                $id = DB::getPdo()->LastInsertId();

                foreach ($formx as $key => $value) {
                    $formxData = array(
                        'setup_ipcr_id' => $id,
                        'function_type' => $value['function_type'],
                        'sorting' => $value['sorting'],
                        'mfo_id' => $value['id'],
                        'selectx' => $value['selectx'],
                        'martix_id' => $value['martix_id'],
                    );

                    db::table($this->prfrmnce_db . '.setup_ipcr_details')->insert($formxData);
                }
                foreach ($formz as $key => $valuex) {
                    $formzData = array(
                        'ipcr_entry_id' => $id,
                        'date' => $valuex['date'],
                        'reviewed_by' => $valuex['reviewed_by'],
                    );
                    db::table($this->prfrmnce_db . '.setup_ipcr_reviewed')->insert($formzData);
                }
            }
        // }
        return  $this->G->success();
    }
    public function getEmpList(Request $request)
    {
        $list = DB::table($this->prfrmnce_db . '.setup_ipcr')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'setup_ipcr.emp_id')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'setup_ipcr.period_id')
            ->select(
                "*",
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.approved_by) as approved_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.prepared_by) as prepared_by'),
                db::raw($this->hr_db . '.getEmployeeDept(setup_ipcr.dept_id) AS department'),
                db::raw('CONCAT(date_from," - ",date_to) AS period'),
                'date_from',
                'date_to',
                'evaluation_period.id',
                'setup_ipcr.id'
            )
            ->where('setup_ipcr.status', 0)
            ->where('emp_id',Auth::user()->Employee_id)
            ->orderBy('setup_ipcr.id')
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }
    public function getListing($id)
    {
        $list = DB::table($this->prfrmnce_db . '.setup_ipcr')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'setup_ipcr.emp_id')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'setup_ipcr.period_id')
            ->select(
                "*",
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.approved_by) as approved_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.prepared_by) as prepared_by'),
                db::raw($this->hr_db . '.getEmployeeDept(setup_ipcr.dept_id) AS department'),
                db::raw('CONCAT(date_from," - ",date_to) AS period'),
                'date_from',
                'date_to',
                'evaluation_period.id',
                'setup_ipcr.id'
            )
            ->where('setup_ipcr.status', 0)
            ->where('setup_ipcr.dept_id', $id)
            // ->where('emp_id',Auth::user()->Employee_id)
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }
    public function cancel($id)
    {
        db::table($this->prfrmnce_db . '.setup_ipcr')
            ->where('id', $id)
            ->update(['setup_ipcr.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    function Edit($id)
    {
        $list['form'] = DB::table($this->prfrmnce_db . '.setup_ipcr')
            ->where("setup_ipcr.id", $id)
            ->get();

        // $setupDtl = db::table($this->prfrmnce_db . '.setup_ipcr_details')
        //     // ->leftJoin($this->prfrmnce_db . '.rating_matrix_setup', 'rating_matrix_setup.id', 'setup_ipcr_details.martix_id')

        //     ->where('setup_ipcr_id', $id);

        // $dummyDesc = DB::table($this->prfrmnce_db . '.setup_mfopap')
        //     ->leftJoin($this->prfrmnce_db . '.rating_matrix_setup', 'rating_matrix_setup.mfo_pap', 'setup_mfopap.id')
        //     ->leftJoinSub($setupDtl, 'setupDtl', function ($join) {
        //         $join->on('setupDtl.martix_id', '=', 'rating_matrix_setup.id');
        //     })
        //     ->select("setup_mfopap.*",'setupDtl.mfo_id','rating_matrix_setup.id as martix_id', "rating_matrix_setup.success_indicators", "setupDtl.selectx", "setupDtl.function_type", "setupDtl.sorting",
        //             // db::raw("setup_mfopap.id as id")
        //     )
        //     ->orderBy('setupDtl.function_type')
        //     ->orderBy('rating_matrix_setup.function_type')
        //     ->orderBy('setup_mfopap.MFO_dscrptn')
        //     ->where("setup_mfopap.status",0)
        //     ->get();
        $dummyDesc = db::select("call " . $this->prfrmnce_db . ".IPCRTarget_modify(?)", [$id]);

        $detail = array();
        foreach ($dummyDesc as $key => $value) {
            $descData = array(
                'id' => $value->mfo_id,
                'selectx' => $value->selectx === 'true' ? 'true' : 'false',
                'sorting' => $value->sorting,
                'description' => $value->description,
                'function_type' => $value->function_type,
                'MFO_dscrptn' => $value->MFO_dscrptn,
                'descrption' => $value->success_indicators,
                'martix_id' => $value->martix_id,
            );
            array_push($detail, $descData);
        }

        $list['formx'] = $detail;

        $formz = db::table($this->prfrmnce_db . '.setup_ipcr_reviewed')
        // ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'ipcr_entry_reviewed.reviewed_by' )
        ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr_reviewed.reviewed_by) as NAME'))
        ->where('setup_ipcr_reviewed.ipcr_entry_id', $id)
        ->get();

        $review = array();
        foreach ($formz as $key => $value) {
            $revData = array(
                'id' => $value->id,
                'date' => $value->date,
                'reviewed_by' => $value->reviewed_by,
                'NAME' => $value->NAME,
            );
            array_push($review, $revData);
        }
        $list['formz'] = $review;
        return response()->json(new JsonResponse($list));
        log::debug($id);
    }
    function EditCopy($id)
    {
        $list['form'] = DB::table($this->prfrmnce_db . '.setup_ipcr')
            ->where("setup_ipcr.id", $id)
            ->get();

        $dummyDesc = db::select("call " . $this->prfrmnce_db . ".IPCRTarget_modify(?)", [$id]);

        $detail = array();
        foreach ($dummyDesc as $key => $value) {
            $descData = array(
                'id' => $value->id,
                'selectx' => $value->selectx === 'true' ? 'true' : 'false',
                'sorting' => $value->sorting,
                'description' => $value->description,
                'function_type' => $value->function_type,
                'MFO_dscrptn' => $value->MFO_dscrptn,
                'descrption' => $value->success_indicators,
                'martix_id' => $value->martix_id,
            );
            array_push($detail, $descData);
        }

        $list['formx'] = $detail;

        $formz = db::table($this->prfrmnce_db . '.setup_ipcr_reviewed')
        // ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'ipcr_entry_reviewed.reviewed_by' )
        ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr_reviewed.reviewed_by) as NAME'))
        ->where('setup_ipcr_reviewed.ipcr_entry_id', $id)
        ->get();

        $review = array();
        foreach ($formz as $key => $value) {
            $revData = array(
                'id' => $value->id,
                'date' => $value->date,
                'reviewed_by' => $value->reviewed_by,
                'NAME' => $value->NAME,
            );
            array_push($review, $revData);
        }
        $list['formz'] = $review;

        return response()->json(new JsonResponse($list));
        log::debug($id);
    }
    public function print(Request $request)
    {
        try {
            $form = $request->itm;
            $main = DB::table($this->prfrmnce_db . '.setup_ipcr')
                    ->join($this->hr_db . '.employee_information', 'employee_information.DEPID', 'setup_ipcr.dept_id')
                    ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'setup_ipcr.period_id')
                    ->leftJoin($this->hr_db . '.employees', 'employees.SysPK_Empl', 'employee_information.headId')
                    ->select(
                        "*",
                        DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.approved_by) as approved_by'),
                        DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.prepared_by) as prepared_by'),
                        DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.reviewed_by) as reviewed_by'),
                        DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr.final_rating_by) as final_rating_by'),
                        db::raw($this->hr_db . '.getEmployeeDept(setup_ipcr.dept_id) AS department'),
                        db::raw('CONCAT(date_from," - ",date_to) AS period'),
                        'date_from',
                        'date_to',
                        'evaluation_period.id',
                        'setup_ipcr.id'
                    )
                    ->where('setup_ipcr.id', $form['id'])
                    ->get();
            $mainData = "";
            
            foreach ($main as $key => $value) {
                $summaryRating1 = 0;
                $summaryRating2 = 0;
                $total = 0;
                $mainData = $value;
            }

            $rating = db::select("call " . $this->prfrmnce_db . ".ipcr_TargetCore(?)", [$form['id']]);
            $ratingData = "";

            $avg = 0;
            // $coreRows = 0;
            // $totalAvrg = 0;
            $function_type = "";
            $description = "";
            foreach ($rating as $key => $value) {
                // log::debug($key);
                // if ($value->avg > 0) {
                //     $coreRows = $coreRows + 1;
                // }
                // $avgVal = 0;

                // $totalAvrg += $value->avg;
                $avg = 0;

                if ($function_type !== $value->function_type) {
                    $ratingData .= '<tr>
                    <td width="100%" style="font-size:8pt; background-color:#ECF87F; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b><i>Strategic Objectives / Core Functions</i></b></td>
                </tr>';
                }
                if ($description !==  $value->description) {
                    $ratingData .= '<tr>
                        <td width="100%" style="font-size:8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>' . $value->description. '</b>
                    </td>
                </tr>';
                 }
                $ratingData .= '
                <tr>
                <td width="4%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->sorting . '</td>
                <td width="23%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->MFO_dscrptn . '</td>
                <td width="24%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->descrption . '</td>
                <td width="20%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">-</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $avg . '</td>
                <td width="9%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                </tr>
                ';
            //     log::debug($totalAvrg);
                $description = $value->description;
                $function_type = $value->function_type;
            }
            // log::debug(count($rating));
            if (count($rating) < 2) {
                for ($i = count($rating); $i < 2; $i++) {
                    $ratingData .= ' <tr>
                    <td width="4%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="23%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="24%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="20%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="9%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    </tr>';
                }
            }
            // log::debug("oks");
            $support = db::select("call " . $this->prfrmnce_db . ".ipcr_TargetSupport(?)", [$form['id']]);
            $supportData = "";

            $avg = 0;
            // $supportRows = 0;
            // $totalAvrgSupport = 0;
            $functionType = "";
            $description = "";
            foreach ($support as $key => $value) {
                // if ($value->avg > 0) {
                //     $supportRows = $supportRows + 1;
                // }
                // $totalAvrgSupport += $value->avg;
                $avg = 0;
                if ($functionType !== $value->function_type) {
                    $supportData .= ' <tr>
                    <td width="100%" align="left" style="background-color:#ECF87F; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b><i>Support Functions</i></b></td>
                </tr>';
                }
                if ($description !==  $value->description) {
                    $supportData .= '<tr>
                        <td width="100%" style="font-size:8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>' . $value->description. '</b>
                    </td>
                </tr>';
                 }
                // $avg = ($value->quality + $value->efficiency + $value->timeliness) / 3;
                $supportData .= '<tr>
                <td width="4%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->sorting . '</td>
                <td width="23%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->MFO_dscrptn . '</td>
                <td width="24%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->descrption . '</td>
                <td width="20%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">-</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' .   $avg . '</td>
                <td width="9%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                </tr>';

                $description = $value->description;
                $functionType = $value->function_type;
            }
            // log::debug(2);
            if (count($support) < 1) {
                for ($i = count($support); $i < 1; $i++) {
                    $supportData .= ' <tr>
                    <td width="4%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="23%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="24%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="20%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="9%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    </tr>';
                }
            }
            // $sumCore = 0;
            // $sumSup = 0;
            // if ($totalAvrgSupport > 0) {
            //     $sumSup = ($totalAvrgSupport / $supportRows) * 0.1;
            // }
            $review = DB::table($this->prfrmnce_db . ".setup_ipcr_reviewed")
                ->where('setup_ipcr_reviewed.ipcr_entry_id', $form['id'])
                ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(setup_ipcr_reviewed.reviewed_by) as reviewed_by'))
                ->get();
            $reviewData = "";

            foreach ($review as $key => $value) {
                $reviewData .= '
                <tr>
                <td width="70%" align="left" style="font-size: 7pt"><b>'.$value->reviewed_by.'</b></td>
                <td width="30%" align="center" style="font-size: 7pt">' . (date_format(date_create($value->date), "m/d/Y")) . '</td>
            </tr>
                ';
            }
            // log::debug(3);
            // log::debug($totalAvrg);
            // log::debug($coreRows);
            // log::debug($sumSup);
            // log::debug("asd");
            // log::debug(($totalAvrg / $coreRows));
            // log::debug((($totalAvrg / $coreRows) * 0.9 ) +($sumSup));
            // $ave = db::select("SELECT * FROM " . $this->prfrmnce_db . ".rating_table WHERE " . number_format((($totalAvrg / $coreRows) * 0.9) + ($sumSup), 2) . " BETWEEN `from_` AND `to_` ");

            // // log::debug( $ave);
            // $final = "0.00";
            // $finalDescription = "";
            // foreach ($ave  as $key => $value) {
            //     $final = $value->grade;
            //     $finalDescription = $value->description;
            // }
            $Template = '<table cellpadding="2">
            <tr>
                <td align="center" style="font-size: 10pt"><b>INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)</b></td>
                <br />
            </tr>
            <tr>
            <br />
                <td width="100%" style="font-size: 9pt; text-align: justify"><p>I, <u><b>' . $mainData->empName . '</b></u>, of the <u><b>' . $mainData->department . '</b></u> of the City of <u>Naga, Cebu</u>, commit to deliver and
                agree to be rated on the attainment of the following targets in accordance with the indicated measures for the period <u><b>' . (!empty($mainData->date_from) ? (date_format(date_create($mainData->date_from), "F d, Y")) : "") . ' to ' . (!empty($mainData->date_to) ? (date_format(date_create($mainData->date_to), "F d, Y")) : "") . '.</b></u></p></td>
            </tr>
            <tr>
                <td width="100%" align="center" style="font-size: 2pt"></td>
            </tr>
            <tr>
                <td width="60%" align="center" style="font-size: 8pt"></td>
                <td width="35%" align="center" style="font-size: 9pt; border-bottom: 0.5px solid black"><b>' . $mainData->empName . '</b></td>
                <td width="5%" align="center" style="font-size: 8pt"></td>
            </tr>
            <tr>
                <td width="60%" align="center" style="font-size: 8pt"></td>
                <td width="35%" align="center" style="font-size: 8pt">Ratee</td>
                <td width="5%" align="center" style="font-size: 8pt"></td>
            </tr>
            <tr>
                <td width="70%" align="center" style="font-size: 8pt"></td>
                <td width="6%" align="center" style="font-size: 8pt">Date:</td>
                <td width="19%" align="center" style="font-size: 8pt; border-bottom: 0.5px solid black">' . (!empty($mainData->prepared_date) ? (date_format(date_create($mainData->prepared_date), "M. d, Y")) : "") . '</td>
                <td width="5%" align="center" style="font-size: 8pt"></td>
            </tr>
            <tr>
            <br />
                <td width="32%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-left: 0.5px solid black; border-right: 0.5px solid black">Reviewed by:</td>
                <td width="17%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-right: 0.5px solid black"></td>
                <td width="34%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-right: 0.5px solid black">Approved by:</td>
                <td width="17%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-right: 0.5px solid black"></td>
            </tr>
            <tr>
                <td width="32%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black"></td>
                <td width="17%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black">Date:</td>
                <td width="34%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black"></td>
                <td width="17%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black">Date:</td>
            </tr>
            <tr>
                <td width="32%" align="center" style="background-color:#EEEEEE; font-size: 9pt; border-left: 0.5px solid black; border-right: 0.5px solid black"><b>' . $mainData->Name_Empl . '</b></td>
                <td width="17%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black">' . (!empty($mainData->prepared_date) ? (date_format(date_create($mainData->prepared_date), "F d, Y")) : "") . '</td>
                <td width="34%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black"><b>' . $mainData->approved_by . '</b></td>
                <td width="17%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black">' . (!empty($mainData->approved_date) ? (date_format(date_create($mainData->approved_date), "M. d, Y")) : "") . '</td>
            </tr>
            <tr>
                <td width="32%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>Immediate Superior/ Dept. Head</b></td>
                <td width="17%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="34%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>City Mayor</b></td>
                <td width="17%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"></td>
            </tr>
            <tr>
                <td width="100%" align="center" style="font-size: 2pt"></td>
            </tr>
            <tr>
                <td width="25%" align="center" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 8pt"></td>
                <td width="35%" align="left" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 6pt; border-right: 0.5px solid black; border-left: 0.5px solid black; border-top: 0.5px solid black">&nbsp;<i><b>5 - Outstanding</b></i></td>
            </tr>
            <tr>
                <td width="25%" align="center" style="font-size: 9pt"></td>
                <td width="20%" align="left" style="font-size: 9pt"></td>
                <td width="35%" align="center" style="font-size: 9pt">RATING</td>
                <td width="20%" align="left" style="font-size: 6pt; border-right: 0.5px solid black; border-left: 0.5px solid black">&nbsp;<i><b>4 - Very Satisfactory</b></i></td>
            </tr>
            <tr>
                <td width="25%" align="center" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 8pt"></td>
                <td width="35%" align="center" style="font-size: 8pt">SCALE</td>
                <td width="20%" align="left" style="font-size: 6pt; border-right: 0.5px solid black; border-left: 0.5px solid black">&nbsp;<i><b>3 - Satisfactory</b></i></td>
            </tr>
            <tr>
                <td width="25%" align="center" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 8pt"></td>
                <td width="35%" align="center" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 6pt; border-right: 0.5px solid black; border-left: 0.5px solid black">&nbsp;<i><b>2 - Unsatisfactory</b></i></td>
            </tr>
            <tr>
                <td width="25%" align="center" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 8pt"></td>
                <td width="35%" align="center" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 6pt; border-right: 0.5px solid black; border-left: 0.5px solid black; border-bottom: 0.5px solid black">&nbsp;<i><b>1 - Poor</b></i></td>
            </tr>
            <tr>
                <td width="100%" align="center" style="font-size: 2pt"></td>
            </tr>
            <tr>
                <td rowspan="2" width="4%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>NO.</b></td>
                <td rowspan="2" width="23%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>MFO/PAP</b></td>
                <td rowspan="2" width="24%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>SUCCESS INDICATORS <br />(TARGETS + MEASURES)</b></td>
                <td rowspan="2" width="20%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>Actual Accomplishments / Expenses</b></td>
                <td colspan="4" width="20%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>Rating*</b></td>
                <td rowspan="2" width="9%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>Remarks</b></td>
            </tr>
            <tr>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>Q</b></td>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>E</b></td>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>T
                </b></td>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>A</b></td>
            </tr>
            '. $ratingData.'
            '. $supportData.'
        <tr>
            <td width="51%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">SUMMARY OF RATING</td>
            <td width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">TOTAL</td>
            <td width="20%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Final Numerical Rating</td>
            <td width="19%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Final Adjectival Rating</td>
        </tr>
        <tr>
            <td width="27%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>'.$summaryRating1.'</b></td>
            <td width="24%" align="center" style="font-size: 6pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Formula: (total of all average ratings / no. of entries) x 90%</td>
            <td rowspan="2" width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">&nbsp;<br/><b>'.number_format($total,2).'</b><br/></td>
            <td rowspan="2" width="20%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><br/><br/></td>
            <td rowspan="2" width="19%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><br/><br/></td>
        </tr>
        <tr>
            <td width="27%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>'.number_format($summaryRating2, 2).'</b></td>
            <td width="24%" align="center" style="font-size: 6pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Formula: (total of all average ratings / no. of entries) x 10%</td>
        </tr>
        <tr>
            <td width="100%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black"><b>Comments and Recommendation for Development Purposes:</b></td>
        </tr>
        <tr>
            <td width="100%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black">&nbsp;</td>
        </tr>
        <tr>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Discussed:</td>
            <td width="12%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . (!empty($mainData->prepared_date) ? (date_format(date_create($mainData->prepared_date), "m/d/Y")) : "") . '</td>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Assessed by:</td>
            <td width="9%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . (!empty($mainData->prepared_date) ? (date_format(date_create($mainData->prepared_date), "m/d/Y")) : "") . '</td>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Reviewed:</td>
            <td width="10%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Date:</td>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-bottom: 0.5px solid black; border-right: 0.5px solid black">Final Rating by:</td>
            <td width="9%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Date:</td>
        </tr>
        <table width="100%">
            <tr>
            <td width="27%" style="border-left:  0.5px solid black; border-right:  0.5px solid black; border-bottom:  0.5px solid black">
                <table width="100%">
                <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                    </tr>
                    <tr>
                    <td width="100%" align="center" style="font-size: 8pt"><b>' . $mainData->empName . '</b></td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                </table>
            </td>
            <td width="24%" style="border-right:  0.5px solid black; border-bottom:  0.5px solid black">
                <table width="100%">
                <tr>
                    <td width="100%" align="left" style="font-size: 6pt">I certify that I discussed my assessment of the <br />
                    performance with the employee:</td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                    </tr>
                    <tr>
                    <td width="100%" align="center" style="font-size: 8pt"><b>' . $mainData->Name_Empl . '</b></td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                    </tr>
                    <tr>
                    <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                </table>
            </td>
            <td width="25%" style="border-right:  0.5px solid black; border-bottom:  0.5px solid black">
                <table width="100%">
                <tr>
                    <td width="100%" align="center" style="font-size: 8pt"><b></b></td>
                </tr>
                '.$reviewData.'
                </table>
            </td>
            <td width="15%" style="border-right:  0.5px solid black; border-bottom:  0.5px solid black">
            <table width="100%">
            <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="center" style="font-size: 8pt"><b>' . $mainData->final_rating_by . '</b></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
            </tr>
            </table>
            </td>
            <td width="9%" style="border-right:  0.5px solid black; border-bottom:  0.5px solid black">
            <table width="100%">
            <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="center" style="font-size: 8pt"><b>' . (!empty($mainData->final_rating_date) ? (date_format(date_create($mainData->final_rating_date), "m/d/Y")) : "") . '</b></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
            </tr>
            </table>
            </td>
            </tr>
        </table>
        <tr>
            <td width="27%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>Ratee</b></td>
            <td width="24%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>Supervisor</b></td>
            <td width="25%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>PMT</b></td>
            <td width="15%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>City Mayor</b></td>
            <td width="9%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"></td>
        </tr>
        <tr>
            <td width="100%" align="left" style="font-size: 7pt"><i>*Legend: &nbsp; Q-Quality &nbsp; E-Efficiency (Quantity) &nbsp; T-Timeliness &nbsp; A-Average</i></td>
        </tr>
        <tr>
        <br />
            <td width="62%" align="right" style="font-size: 8pt">RANGE OF OVERALL PTS</td>
            <td width="13%" align="right" style="font-size: 8pt"></td>
            <td width="5%" align="center" style="font-size: 8pt">NR</td>
            <td width="5%" align="center" style="font-size: 8pt">AR</td>
            <td width="15%" align="center" style="font-size: 8pt"></td>
        </tr>
        <tr>
            <td width="59%" align="right" style="font-size: 8pt">1.00</td>
            <td width="16%" align="right" style="font-size: 8pt">1.50</td>
            <td width="5%" align="center" style="font-size: 8pt">1</td>
            <td width="20%" align="left" style="font-size: 8pt">Poor</td>
        </tr>
        <tr>
            <td width="59%" align="right" style="font-size: 8pt">1.51</td>
            <td width="16%" align="right" style="font-size: 8pt">2.50</td>
            <td width="5%" align="center" style="font-size: 8pt">2</td>
            <td width="20%" align="left" style="font-size: 8pt">Unsatisfactory</td>
        </tr>
        <tr>
            <td width="59%" align="right" style="font-size: 8pt">2.51</td>
            <td width="16%" align="right" style="font-size: 8pt">3.50</td>
            <td width="5%" align="center" style="font-size: 8pt">3</td>
            <td width="20%" align="left" style="font-size: 8pt">Satisfactory</td>
        </tr>
        <tr>
            <td width="59%" align="right" style="font-size: 8pt">3.51</td>
            <td width="16%" align="right" style="font-size: 8pt">4.50</td>
            <td width="5%" align="center" style="font-size: 8pt">4</td>
            <td width="20%" align="left" style="font-size: 8pt">Very Satisfactory</td>
        </tr>
        <tr>
            <td width="59%" align="right" style="font-size: 8pt">4.51</td>
            <td width="16%" align="right" style="font-size: 8pt">5.00</td>
            <td width="5%" align="center" style="font-size: 8pt">5</td>
            <td width="20%" align="left" style="font-size: 8pt">Outstanding</td>
        </tr>
        </table>
            ';

            PDF::SetTitle('IPCR Target');
            PDF::SetFont('helvetica', '', 10);
            PDF::AddPage('P', array(215.9, 355.6));
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
