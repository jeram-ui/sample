<?php

namespace App\Http\Controllers\Api\Mod_Performance\IPCR_Rating;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class ipcrRatingController extends Controller
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
    public function basicinfo(Request $request)
    {
        $list = DB::table($this->hr_db . '.employees')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'employees.SysPK_Empl')
            ->where('SysPK_Empl', Auth::user()->Employee_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetDept()
    {
        $list = DB::table($this->hr_db . '.employee_information')
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
    public function GetAssessed()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetReviewed()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetFinal()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetPeriod()
    {
        $list = DB::table($this->prfrmnce_db . '.evaluation_period')
            ->select('*', db::raw('CONCAT(date_from," - ",date_to) AS period'), 'date_from', 'date_to')
            ->where('evaluation_period.status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetPeriodTarget(Request $request)
    {
        $emp_id = $request->emp_id;
        $dept_id = $request->dept_id;
        
        $list = DB::table($this->prfrmnce_db . '.setup_ipcr')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'setup_ipcr.period_id')
            ->select('*', db::raw('CONCAT(date_from," - ",date_to) AS period'), 'date_from', 'date_to')
            ->where('evaluation_period.status', 0)
            ->where('setup_ipcr.status', 0)
            ->where('setup_ipcr.dept_id', $dept_id)
            ->where('setup_ipcr.emp_id', $emp_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function showMFO_byEmp(Request $request)
    {
        $_empID = $request->emp_id;
        $_deptID = $request->dept_id;
        $_periodID = $request->period_id;
        $list = db::select("call " . $this->prfrmnce_db . ".mfo_byEmp(?,?,?)", [$_empID, $_deptID, $_periodID]);

        $final = array();
        foreach ($list as $key => $value) {
        $dum = array(
            'function_type' =>$value->function_type,
            'sorting' => $value->sorting,
            'description'=>$value->description,
            'MFO_dscrptn'=>$value->MFO_dscrptn,
            'mfo_id'=>$value->id,
            'success_indicators'=>$value->success_indicators,
            'r_quality'=>'',
            'r_quality_value'=>'',
            'r_efficiency'=>'',
            'r_efficiency_value'=>'',
            'r_timeliness'=>'',
            'r_timeliness_value'=>'',
            'quality'=>db::table($this->prfrmnce_db .'.setup_ratings_quality')
                    ->select("*",
                    db::raw('CONCAT(qty," - ",description) AS description')
                    )
                    ->where("rating_id", $value->matrix_id)
                    ->get(),

            'efficiency'=>db::table($this->prfrmnce_db .'.setup_ratings_efficiency') ->select("*",
            db::raw('CONCAT(qty," - ",description) AS description')
            )->where("rating_id",$value->matrix_id)->get(),
            'timeliness'=>db::table($this->prfrmnce_db .'.setup_ratings_timeliness') ->select("*",
            db::raw('CONCAT(qty," - ",description) AS description')
            )->where("rating_id",$value->matrix_id)->get(),
            'remarks'=>'',
            'actual_acmplshmnt_expense'=>'',
            'matrix_id'=>$value->matrix_id,
        );
        array_push( $final,$dum );
       }
        return response()->json(new JsonResponse($final));
    }
    public function Edit($id)
    {
        $data['form'] = db::table($this->prfrmnce_db . '.ipcr_entry')
            ->where('ipcr_entry.id', $id)
            ->get();
        $formx = db::select("call " . $this->prfrmnce_db . ".ipcrRating_modify(?)", [$id]);
        $final = array();
        foreach ($formx as $key => $value) {
        $dum = array(
            'MFO_dscrptn'=>$value->MFO_dscrptn,
            'function_type' =>$value->function_type,
            'sorting' => $value->sorting,
            'description' => $value->description,
            'mfo_id'=>$value->id,
            'success_indicators'=>$value->success_indicators,
            'r_quality'=>$value->r_quality,
            'r_efficiency'=>$value->r_efficiency,
            'r_timeliness'=>$value->r_timeliness,
            'quality'=>db::table($this->prfrmnce_db .'.setup_ratings_quality')
                    ->select("*",
                    db::raw('CONCAT(qty," - ",description) AS description')
                    )
                    ->where("rating_id", $value->matrix_id)
                    ->get(),

                'efficiency' => db::table($this->prfrmnce_db . '.setup_ratings_efficiency')->select(
                    "*",
                    db::raw('CONCAT(qty," - ",description) AS description')
                )->where("rating_id", $value->matrix_id)->get(),
                'timeliness' => db::table($this->prfrmnce_db . '.setup_ratings_timeliness')->select(
                    "*",
                    db::raw('CONCAT(qty," - ",description) AS description')
                )->where("rating_id", $value->matrix_id)->get(),
                'remarks' => $value->remarks,
                'actual_acmplshmnt_expense' => $value->actual_acmplshmnt_expense,
                'matrix_id' => $value->matrix_id,
            );
            array_push($final, $dum);
        }
        $data['formx'] = $final;

        $formz = db::table($this->prfrmnce_db . '.ipcr_entry_reviewed')
            // ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'ipcr_entry_reviewed.reviewed_by' )
            ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(ipcr_entry_reviewed.reviewed_by) as NAME'))
            ->where('ipcr_entry_reviewed.ipcr_entry_id', $id)
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
        $data['formz'] = $review;
        return response()->json(new JsonResponse($data));
    }
    public function store(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $formz = $request->formz;
        $id = $form['id'];
        if ($id > 0) {
            DB::table($this->prfrmnce_db . '.ipcr_entry')
                ->where('id', $id)
                ->update($form);

            db::table($this->prfrmnce_db . '.ipcr_entry_rating')
                ->where("ipcr_entry_id", $id)
                ->delete();
            foreach ($formx as $key => $value) {
                $formxData = array(
                    'ipcr_entry_id' => $id,
                    'function_type' =>$value['function_type'],
                    'sorting' => $value['sorting'],
                    'mfo_id' => $value['mfo_id'],
                    'matrix_id' => $value['matrix_id'],
                    'actual_acmplshmnt_expense' => $value['actual_acmplshmnt_expense'],
                    'r_quality' => $value['r_quality'],
                    'r_efficiency' => $value['r_efficiency'],
                    'r_timeliness' => $value['r_timeliness'],
                    'remarks' => $value['remarks']
                );
                db::table($this->prfrmnce_db . '.ipcr_entry_rating')->insert($formxData);
            }

            db::table($this->prfrmnce_db . '.ipcr_entry_reviewed')
                ->where("ipcr_entry_id", $id)
                ->delete();
            foreach ($formz as $key => $valuex) {
                $formzData = array(
                    'ipcr_entry_id' => $id,
                    'date' => $valuex['date'],
                    'reviewed_by' => $valuex['reviewed_by'],
                );
                db::table($this->prfrmnce_db . '.ipcr_entry_reviewed')->insert($formzData);
            }
        } else {
            $chk = db::table($this->prfrmnce_db . '.ipcr_entry')
                ->where("empName", $form['empName'])
                ->where("dept_id", $form['dept_id'])
                ->where("period_id", $form['period_id'])
                ->where("ipcr_entry.status", 0)
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

                DB::table($this->prfrmnce_db . '.ipcr_entry')->insert($form);
                $id = DB::getPdo()->LastInsertId();

                foreach ($formx as $key => $value) {
                    $formxData = array(
                        'ipcr_entry_id' => $id,
                        'function_type' =>$value['function_type'],
                        'sorting' => $value['sorting'],
                        'mfo_id' => $value['mfo_id'],
                        'matrix_id' => $value['matrix_id'],
                        'actual_acmplshmnt_expense' => $value['actual_acmplshmnt_expense'],
                        'r_quality' => $value['r_quality'],
                        'r_efficiency' => $value['r_efficiency'],
                        'r_timeliness' => $value['r_timeliness'],
                        'remarks' => $value['remarks']

                        );
                        log::debug($formxData);
                        db::table($this->prfrmnce_db . '.ipcr_entry_rating')->insert($formxData);
                }
                foreach ($formz as $key => $valuex) {
                    $formzData = array(
                        'ipcr_entry_id' => $id,
                        'date' => $valuex['date'],
                        'reviewed_by' => $valuex['reviewed_by'],
                    );
                    db::table($this->prfrmnce_db . '.ipcr_entry_reviewed')->insert($formzData);
                }
            }
        }
        return  $this->G->success();
    }
    public function getEmpList()
    {
        $list = DB::table($this->prfrmnce_db . '.ipcr_entry')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'ipcr_entry.emp_id')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'ipcr_entry.period_id')
            ->select(
                "*",
                db::raw($this->hr_db . '.getEmployeeDept(ipcr_entry.dept_id) AS department'),
                db::raw('CONCAT(date_from," - ",date_to) AS period'),
                'date_from',
                'date_to',
                'evaluation_period.id',
                'ipcr_entry.id'
            )
            ->where('ipcr_entry.status', 0)
            ->where('emp_id', Auth::user()->Employee_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getRatingList($id)
    {
        $list = DB::table($this->prfrmnce_db . '.ipcr_entry')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'ipcr_entry.emp_id')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'ipcr_entry.period_id')
            ->select(
                "*",
                db::raw($this->hr_db . '.getEmployeeDept(ipcr_entry.dept_id) AS department'),
                db::raw('CONCAT(date_from," - ",date_to) AS period'),
                'date_from',
                'date_to',
                'evaluation_period.id',
                'ipcr_entry.id'
            )
            ->where('ipcr_entry.status', 0)
            ->where('ipcr_entry.dept_id', $id)
            // ->where('emp_id',Auth::user()->Employee_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function cancel($id)
    {
        db::table($this->prfrmnce_db . '.ipcr_entry')
            ->where('id', $id)
            ->update(['ipcr_entry.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function print(Request $request)
    {
        try {
            $form = $request->itm;
            $main = DB::table($this->prfrmnce_db . '.ipcr_entry')
                ->join($this->hr_db . '.employee_information', 'employee_information.DEPID', 'ipcr_entry.dept_id')
                ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'ipcr_entry.period_id')
                ->leftJoin($this->hr_db . '.employees', 'employees.SysPK_Empl', 'employee_information.headId')
                ->select(
                    '*',
                    DB::raw($this->hr_db . '.jay_getEmployeeName(ipcr_entry.approved_by) as approved'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(ipcr_entry.assessed_by) as assess'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(ipcr_entry.final_rating_by) as final'),
                    db::raw('CONCAT(date_from," - ",date_to) AS period'),
                    'date_from',
                    'date_to',
                    'evaluation_period.id'
                    // db::raw('IF(IFNULL(ipcr_entry.comments,'')='','', AS period'),
                )
                ->where('ipcr_entry.id', $form['id'])
                ->get();
            $mainData = "";

            foreach ($main as $key => $value) {
                $mainData = $value;
            }

            $rating = db::select("call " . $this->prfrmnce_db . ".ipcr_EntryRatingCore(?)", [$form['id']]);
            $ratingData = "";

            $avg = 0;
            $coreRows = 0;
            $totalAvrg = 0;
            $function_type = "";
            $description = "";
            foreach ($rating as $key => $value) {
                // log::debug($key);
                if ($value->avg > 0) {
                    $coreRows = $coreRows + 1;
                }
                $avgVal = 0;

                $totalAvrg += $value->avg;

                if ($function_type !== $value->function_type) {
                    $ratingData .= '<tr>
                    <td width="100%" style="font-size:8pt; background-color:#ECF87F; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b><i>Strategic Objectives / Core Functions</i></b></td>
                </tr>';
                }
                if ($description !==  $value->description) {
                    $ratingData .= '<tr>
                        <td width="100%" style="font-size:8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black"><b>' . $value->description. '</b>
                    </td>
                </tr>';
                 }
                $ratingData .= '
                <tr>
                <td width="4%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->sorting . '</td>
                <td width="23%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->MFO_dscrptn . '</td>
                <td width="25%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->success_indicators . '</td>
                <td width="18%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->actual_acmplshmnt_expense . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->quality . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->efficiency . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->timeliness . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . number_format($value->avg, 2) . '</td>
                <td width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->remarks . '</td>
                </tr>
                ';
                log::debug($totalAvrg);

                $description = $value->description;
                $function_type = $value->function_type;
            }
            // log::debug(count($rating));
            if (count($rating) < 2) {
                for ($i = count($rating); $i < 2; $i++) {
                    $ratingData .= ' <tr>
                    <td width="4%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="23%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="25%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="18%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    </tr>';
                }
            }
            // log::debug("oks");
            $support = db::select("call " . $this->prfrmnce_db . ".ipcr_EntryRatingSupport(?)", [$form['id']]);
            $supportData = "";

            $avg = 0;
            $supportRows = 0;
            $totalAvrgSupport = 0;
            $functionType = "";
            $description = "";
            foreach ($support as $key => $value) {
                if ($value->avg > 0) {
                    $supportRows = $supportRows + 1;
                }
                $totalAvrgSupport += $value->avg;
                if ($functionType !== $value->functionType) {
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
                <td width="25%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->success_indicators . '</td>
                <td width="18%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->actual_acmplshmnt_expense . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->quality . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->efficiency . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->timeliness . '</td>
                <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . number_format($value->avg, 2) . '</td>
                <td width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . $value->remarks . '</td>
                </tr>';

                $description = $value->description;
                $functionType = $value->functionType;
            }
            // log::debug(2);
            if (count($support) < 1) {
                for ($i = count($support); $i < 1; $i++) {
                    $supportData .= ' <tr>
                    <td width="4%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="23%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="25%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="18%" align="left" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="5%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    <td width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                    </tr>';
                }
            }
            $sumCore = 0;
            $sumSup = 0;
            if ($totalAvrgSupport > 0) {
                $sumSup = ($totalAvrgSupport / $supportRows) * 0.1;
            }
            $review = DB::table($this->prfrmnce_db . ".ipcr_entry_reviewed")
                ->where('ipcr_entry_reviewed.ipcr_entry_id', $form['id'])
                ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(ipcr_entry_reviewed.reviewed_by) as reviewed_by'))
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
            $ave = db::select("SELECT * FROM " . $this->prfrmnce_db . ".rating_table WHERE " . number_format((($totalAvrg / $coreRows) * 0.9) + ($sumSup), 2) . " BETWEEN `from_` AND `to_` ");

            // log::debug( $ave);
            $final = "0.00";
            $finalDescription = "";
            foreach ($ave  as $key => $value) {
                $final = $value->grade;
                $finalDescription = $value->description;
            }
            $Template = '<table cellpadding="2">
            <tr>
                <td align="center" style="font-size: 9pt"><b>INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)</b></td>
                <br />
            </tr>
            <tr>
            <br />
                <td width="100%" style="font-size: 8pt; text-align: justify"><p>I, <u><b>' . $mainData->empName . '</b></u>, of the <u><b>' . $mainData->DEPARTMENT . '</b></u> of the City of Naga, commit to deliver and
                agree to be rated on the attainment of the following targets in accordance with the indicated measures for the period <u><b>' . (!empty($mainData->date_from) ? (date_format(date_create($mainData->date_from), "M. d, Y")) : "") . ' to ' . (!empty($mainData->date_to) ? (date_format(date_create($mainData->date_to), "M. d, Y")) : "") . '.</b></u></p></td>
            </tr>
            <tr>
                <td width="100%" align="center" style="font-size: 2pt"></td>
            </tr>
            <tr>
                <td width="60%" align="center" style="font-size: 8pt"></td>
                <td width="35%" align="center" style="font-size: 8pt; border-bottom: 0.5px solid black"><b>' . $mainData->empName . '</b></td>
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
                <td width="19%" align="center" style="font-size: 8pt; border-bottom: 0.5px solid black">' . (!empty($mainData->date) ? (date_format(date_create($mainData->date), "M. d, Y")) : "") . '</td>
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
                <td width="32%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black"><b>' . $mainData->Name_Empl . '</b></td>
                <td width="17%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black">' . (!empty($mainData->date) ? (date_format(date_create($mainData->date), "M. d, Y")) : "") . '</td>
                <td width="34%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black"><b>' . $mainData->approved . '</b></td>
                <td width="17%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black">' . (!empty($mainData->approved_date) ? (date_format(date_create($mainData->approved_date), "M. d, Y")) : "") . '</td>
            </tr>
            <tr>
                <td width="32%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black">Immediate Superior/ Dept. Head</td>
                <td width="17%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"></td>
                <td width="34%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black">City Mayor</td>
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
                <td width="25%" align="center" style="font-size: 8pt"></td>
                <td width="20%" align="left" style="font-size: 8pt"></td>
                <td width="35%" align="center" style="font-size: 8pt">RATING</td>
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
                <td rowspan="2" width="4%" align="center" style="background-color:#EEEEEE; font-size: 9; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>NO.</b></td>
                <td rowspan="2" width="23%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>MFO/PAP</b></td>
                <td rowspan="2" width="25%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>SUCCESS INDICATORS <br />(TARGETS + MEASURES)</b></td>
                <td rowspan="2" width="18%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>Actual Accomplishments / Expenses</b></td>
                <td colspan="4" width="20%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>Rating*</b></td>
                <td rowspan="2" width="10%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><b>Remarks</b></td>
            </tr>
            <tr>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>Q</b></td>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>E</b></td>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>T
                </b></td>
                <td width="5%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>A</b></td>
            </tr>
            ' . $ratingData . '

            ' . $supportData . '
        <tr>
            <td width="50%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">SUMMARY OF RATING</td>
            <td width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">TOTAL</td>
            <td width="20%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Final Numerical Rating</td>
            <td width="20%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Final Adjectival Rating</td>
        </tr>
        <tr>
            <td width="25%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . number_format((($totalAvrg / $coreRows) * 0.9), 2) . '</td>
            <td width="25%" align="center" style="font-size: 6pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Formula: (total of all average ratings / no. of entries) x 90%</td>
            <td rowspan="2" width="10%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><br/><br/>' . number_format((($totalAvrg / $coreRows) * 0.9) + ($sumSup), 2) . '</td>
            <td rowspan="2" width="20%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><br/><br/>' . $final . '</td>
            <td rowspan="2" width="20%" align="center" style="font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black"><br/><br/>' . $finalDescription . '</td>
        </tr>
        <tr>
            <td width="25%" align="center" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . number_format($sumSup, 2) . '</td>
            <td width="25%" align="center" style="font-size: 6pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Formula: (total of all average ratings / no. of entries) x 10%</td>
        </tr>
        <tr>
            <td width="100%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-top: 0.5px solid black"><b>Comments and Recommendation for Development Purposes:</b></td>
        </tr>
        <tr>
            <td width="100%" align="left" style="font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black">&nbsp;' . $mainData->comments . ' <br /></td>
        </tr>
        <tr>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Discussed:</td>
            <td width="10%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . (!empty($mainData->date) ? (date_format(date_create($mainData->date), "m/d/Y")) : "") . '</td>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Assessed by:</td>
            <td width="10%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">' . (!empty($mainData->date) ? (date_format(date_create($mainData->date), "m/d/Y")) : "") . '</td>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Reviewed:</td>
            <td width="10%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Date:</td>
            <td width="15%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-top: 0.5px solid black; border-bottom: 0.5px solid black; border-right: 0.5px solid black">Final Rating by:</td>
            <td width="10%" align="left" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-top: 0.5px solid black; border-bottom: 0.5px solid black">Date:</td>
        </tr>
        <table width="100%">
            <tr>
            <td width="25%" style="border-left:  0.5px solid black; border-right:  0.5px solid black; border-bottom:  0.5px solid black">
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
            <td width="25%" style="border-right:  0.5px solid black; border-bottom:  0.5px solid black">
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
                    <td width="100%" align="center" style="font-size: 9pt"><b></b></td>
                </tr>
                ' . $reviewData . '
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
                <td width="100%" align="center" style="font-size: 8pt"><b>' . $mainData->final . '</b></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
                </tr>
                <tr>
                <td width="100%" align="left" style="font-size: 8pt"></td>
            </tr>
            </table>
            </td>
            <td width="10%" style="border-right:  0.5px solid black; border-bottom:  0.5px solid black">
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
                <td width="100%" align="center" style="font-size: 9pt"><b>' . (!empty($mainData->final_rating_date) ? (date_format(date_create($mainData->final_rating_date), "m/d/Y")) : "") . '</b></td>
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
            <td width="25%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-left: 0.5px solid black; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>Ratee</b></td>
            <td width="25%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>Supervisor</b></td>
            <td width="25%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>PMT</b></td>
            <td width="15%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"><b>City Mayor</b></td>
            <td width="10%" align="center" style="background-color:#EEEEEE; font-size: 8pt; border-right: 0.5px solid black; border-bottom: 0.5px solid black"></td>
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

            PDF::SetTitle('IPCR Accomplishment');
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
