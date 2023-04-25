<?php

namespace App\Http\Controllers\Api\Mod_Performance\OPCR_Rating;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class OPCRrateController extends Controller
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

    public function GetDept()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        $data['form'] = db::table($this->prfrmnce_db . '.opcr_entry')
            ->where('opcr_entry.id', $id)
            ->get();

        $formx = db::select("call " . $this->prfrmnce_db . ".opcrRating_modify(?)", [$id]);

        $final = array();
        foreach ($formx as $key => $value) {
            $dumAccountable = db::table($this->prfrmnce_db . '.opcr_entry_accountable')
                ->where("rating_id", $value->rating_id)
                ->get();
            $arrayAccountable = array();
            foreach ($dumAccountable as $keyC => $valueC) {
                array_push( $arrayAccountable, $valueC->accountable_id);
            }

            $dum = array(
                'function_type' =>$value->function_type,
                'sorting' => $value->sorting,
                'description' => $value->description,
                'MFO_dscrptn' => $value->MFO_dscrptn,
                'mfo_id' => $value->id,
                'success_indicators' => $value->success_indicators,
                'alloted' => $value->alloted,
                'actual_acmplshmnt_expense' => $value->actual_acmplshmnt_expense,
                'r_quality' => $value->r_quality,
                'r_efficiency' => $value->r_efficiency,
                'r_timeliness' => $value->r_timeliness,

                'quality' => db::table($this->prfrmnce_db . '.setup_ratings_quality')
                    ->select(
                        "*",
                        db::raw('CONCAT(qty," - ",description) AS description')
                    )
                    ->where("rating_id", $value->matrix_id)
                    ->get(),

                'efficiency' => db::table($this->prfrmnce_db . '.setup_ratings_efficiency')->select(
                    "*",
                    db::raw('CONCAT(qty," - ",description) AS description')
                )
                    ->where("rating_id", $value->matrix_id)
                    ->get(),

                'timeliness' => db::table($this->prfrmnce_db . '.setup_ratings_timeliness')
                    ->select(
                        "*",
                        db::raw('CONCAT(qty," - ",description) AS description')
                    )->where("rating_id", $value->matrix_id)
                    ->get(),

                'remarks' => $value->remarks,
                'matrix_id' => $value->matrix_id,
                'r_accountable' => $arrayAccountable,

            );
            array_push($final, $dum);
        }
        $data['formx'] = $final;

        $formz = db::table($this->prfrmnce_db . '.opcr_entry_reviewed')
            ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry_reviewed.reviewed_by) as NAME'))
            ->where('opcr_entry_reviewed.opcr_entry_id', $id)
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
    public function GetName($id)
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->where('DEPID', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetPeriod($id)
    {
        $list = DB::table($this->prfrmnce_db . '.setup_opcr')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'setup_opcr.period_id')
            ->select('*', db::raw('CONCAT(date_from," - ",date_to) AS period'), 'date_from', 'date_to')
            ->where('setup_opcr.status', 0)
            ->where('evaluation_period.status', 0)
            ->where('setup_opcr.dept_id', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function showMFO_byEmp(Request $request)
    {
        // $_empID = $request->emp_id;
        $_deptID = $request->dept_id;
        $_periodID = $request->period_id;
        $list = db::select("call " . $this->prfrmnce_db . ".MFO_OPCR(?,?)", [$_deptID, $_periodID]);
        $final = array();
        foreach ($list as $key => $value) {
            $dumAccountable = db::table($this->prfrmnce_db . '.setup_opcr_accountable')
                ->where("rating_id", $value->rating_id)
                ->get();
            $arrayAccountable = array();
            foreach ($dumAccountable as $keyC => $valueC) {
                array_push( $arrayAccountable, $valueC->accountable_id);
            }

            $dum = array(
                'function_type' => $value->function_type,
                'sorting' => $value->sorting,
                'description' => $value->description,
                'MFO_dscrptn' => $value->MFO_dscrptn,
                'mfo_id' => $value->id,
                'alloted' => $value->alloted_budget,
                'success_indicators' => $value->success_indicators,
                'r_quality' => '',
                'r_efficiency' => '',
                'r_timeliness' => '',
                'quality' => db::table($this->prfrmnce_db . '.setup_ratings_quality')
                    ->select(
                        "*",
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
                'remarks' => '',
                'r_accountable' => $arrayAccountable,
                'actual_acmplshmnt_expense' => '',
                'matrix_id' => $value->matrix_id,
            );
            array_push($final, $dum);
        }
        return response()->json(new JsonResponse($final));
    }

    public function store(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $formz = $request->formz;
        // $formb = $request->formb;

        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->prfrmnce_db . '.opcr_entry')
                ->where('id', $id)
                ->update($form);

            db::table($this->prfrmnce_db . '.opcr_entry_rating')
                ->where("opcr_entry_id", $id)
                ->delete();

            foreach ($formx as $key => $value) {
                $formxData = array(
                    'opcr_entry_id' => $id,
                    'mfo_id' => $value['mfo_id'],
                    'function_type' => $value['function_type'],
                    'sorting' => $value['sorting'],
                    'actual_acmplshmnt_expense' => $value['actual_acmplshmnt_expense'],
                    'r_quality' => $value['r_quality'],
                    'r_efficiency' => $value['r_efficiency'],
                    'r_timeliness' => $value['r_timeliness'],
                    'alloted' => $value['alloted'],
                    'remarks' => $value['remarks'],
                );
                db::table($this->prfrmnce_db . '.opcr_entry_rating')->insert($formxData);
                $rating_id = DB::getPdo()->LastInsertId();

                foreach ($value['r_accountable'] as $keyx => $valuex) {
                    $formRData = array(
                        'rating_id' => $rating_id,
                        'accountable_id' => $valuex
                    );
                    db::table($this->prfrmnce_db . '.opcr_entry_accountable')->insert($formRData);
                }
            }
            db::table($this->prfrmnce_db . '.opcr_entry_reviewed')
                ->where("opcr_entry_id", $id)
                ->delete();
            foreach ($formz as $key => $valuex) {
                $formzData = array(
                    'opcr_entry_id' => $id,
                    'date' => $valuex['date'],
                    'reviewed_by' => $valuex['reviewed_by'],
                );
                db::table($this->prfrmnce_db . '.opcr_entry_reviewed')->insert($formzData);
            }
        } else {
            $chk = db::table($this->prfrmnce_db . '.opcr_entry')
            // ->where("empName", $form['empName'])
            ->where("dept_id", $form['dept_id'])
            ->where("period_id", $form['period_id'])
            ->where("opcr_entry.status", 0)
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

                DB::table($this->prfrmnce_db . '.opcr_entry')->insert($form);
                $id = DB::getPdo()->LastInsertId();
                
                foreach ($formx as $key => $value) {
                    $formxData = array(
                        'opcr_entry_id' => $id,
                        'mfo_id' => $value['mfo_id'],
                        'function_type' => $value['function_type'],
                        'sorting' => $value['sorting'],
                        'actual_acmplshmnt_expense' => $value['actual_acmplshmnt_expense'],
                        'alloted' => $value['alloted'],
                        'r_quality' => $value['r_quality'],
                        'r_efficiency' => $value['r_efficiency'],
                        'r_timeliness' => $value['r_timeliness'],
                        'remarks' => $value['remarks'],
                    );
                    db::table($this->prfrmnce_db . '.opcr_entry_rating')->insert($formxData);
                    $rating_id = DB::getPdo()->LastInsertId();

                    foreach ($value['r_accountable'] as $keyx => $valuex) {
                        $formRData = array(

                            'rating_id' =>  $rating_id,
                            'accountable_id' => $valuex,
                        );
                        db::table($this->prfrmnce_db . '.opcr_entry_accountable')->insert($formRData);
                    }
                }
                foreach ($formz as $key => $valuex) {
                    $formzData = array(
                        'opcr_entry_id' => $id,
                        'date' => $valuex['date'],
                        'reviewed_by' => $valuex['reviewed_by'],
                    );
                    db::table($this->prfrmnce_db . '.opcr_entry_reviewed')->insert($formzData);
                }
            }
        }
        return  $this->G->success();
    }
    public function getEmpList($id)
    {
        $list = DB::table($this->prfrmnce_db . '.opcr_entry')
            ->orderBy('opcr_entry.id','ASC')
            ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'opcr_entry.dept_id')
            ->leftJoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'opcr_entry.prepared_by')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'opcr_entry.period_id')
            ->select(
                "*",
                DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.approved_by) as approved_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.prepared_by) as prepared_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.final_rating_by) as final_rating_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.assessed_by) as assessed_by'),
                db::raw('CONCAT(date_from," - ",date_to) AS period'),
                'date_from',
                'date_to',
                'Name_Dept',
                'evaluation_period.id',
                'opcr_entry.id'
            )
            ->where('opcr_entry.status', 0)
            ->where('opcr_entry.dept_id', $id)
            // ->where('emp_id',Auth::user()->Employee_id)
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }
    public function cancel($id)
    {
        db::table($this->prfrmnce_db . '.opcr_entry')
            ->where('id', $id)
            ->update(['opcr_entry.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function print(Request $request)
    {
        try {
            $id = $request->id;
            $form = $request->itm;
            $opcr = DB::table($this->prfrmnce_db . '.opcr_entry')
                ->join($this->hr_db . '.employee_information', 'employee_information.DEPID', 'opcr_entry.dept_id')
                ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'opcr_entry.period_id')
                ->leftJoin($this->hr_db . '.employees', 'employees.SysPK_Empl', 'employee_information.headId')
                ->select(
                    '*',
                    DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.assessed_by) as assess'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.prepared_by) as prepared_by'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.approved_by) as approved_by'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.final_rating_by) as final'),
                    db::raw('CONCAT(date_from," to ",date_to) AS period'),
                    'date_from',
                    'date_to',
                    'evaluation_period.id'
                )
                ->where('opcr_entry.id', $form['id'])
                ->get();
            $opcrData = "";
            foreach ($opcr as $key => $value) {
                $opcrData = $value;
            };

            $mfo = db::select("call " . $this->prfrmnce_db . ".OPCR_Rating1(?)", [$form['id']]);
            $mfoData = "";

            $avg = 0;
            $coreRows = 0;
            $totalAvrg = 0;
            $description="";
            foreach ($mfo as $key => $value) {
                $avgVal = 0;
                $coreRows = $coreRows + 1;
                $totalAvrg += $value->avg;
                 if ($description !==  $value->description) {
                    $mfoData .= '<tr>
                        <td width="100%" style="font-size:8pt"><b>' . $value->description. '</b>
                    </td>
                </tr>';
                 }
                $mfoData .= '<tr>
                    <td width="4%" align="center" style="font-size:8pt">' . $value->sorting . '
                    </td>
                    <td width="19%" style="font-size:8pt">' . $value->MFO_dscrptn . '
                    </td>
                    <td width="19%" style="font-size:8pt"> ' . $value->success_indicators . '
                    </td>
                    <td width="7%" align="right" style="font-size:8pt">' . number_format($value->alloted, 2) . '
                    </td>
                    <td width="12%" style="font-size:8pt">'.$value->accountable.'
                    </td>
                    <td width="16%" style="font-size:8pt">' . $value->actual_acmplshmnt_expense . '
                    </td>
                    <td width="3.5%" align="center" style="font-size:8pt">' . $value->quality . '</td>
                    <td width="3.5%" align="center" style="font-size:8pt">' . $value->efficiency . '</td>
                    <td width="3.5%" align="center" style="font-size:8pt">' . $value->timeliness . '</td>
                    <td width="4.5%" align="center" style="font-size:8pt">' . number_format($value->avg, 2) . '</td>
                    <td width="8%" align="center" style="font-size:8pt">' . $value->remarks . '</td>

                </tr>';
                $description = $value->description;
            };

            $supF = db::select("call " . $this->prfrmnce_db . ".OPCR_Support(?)", [$form['id']]);
            $supportFunction = "";

            $avg = 0;
            $supportRows = 0;
            $totalAvrgSupport = 0;
            // $supNull = "";
            $function_type="";
            $description="";
            foreach ($supF as $key => $value) {
                if ($value->avg > 0) {
                    $supportRows = $supportRows + 1;
                }
                $totalAvrgSupport += $value->avg;

                if ($function_type !== $value->function_type) {
                    $supportFunction .= '<tr>
                    <td width="100%" style="background-color:#ECF87F;"><b><i>Support Functions</i></b></td>
                </tr>';
                }

                if ($description !==  $value->description) {
                    $supportFunction .= '<tr>
                        <td width="100%" style="font-size:8pt"><b>' . $value->description. '</b>
                    </td>
                </tr>';
                 }
                $supportFunction .= '<tr>
                <td width="4%" align="center" style="font-size:8pt">' . $value->sorting . '
                </td>
                <td width="19%" style="font-size:8pt">' . $value->MFO_dscrptn . '
                </td>
                <td width="19%" style="font-size:8pt"> ' . $value->success_indicators . '
                </td>
                <td width="7%" align="right" style="font-size:8pt">' . number_format($value->alloted, 2) . '
                </td>
                <td width="12%" style="font-size:8pt">'.$value->accountable.'
                </td>
                <td width="16%" style="font-size:8pt">' . $value->actual_acmplshmnt_expense . '
                </td>
                <td width="3.5%" align="center" style="font-size:8pt">' . $value->quality . '</td>
                <td width="3.5%" align="center" style="font-size:8pt">' . $value->efficiency . '</td>
                <td width="3.5%" align="center" style="font-size:8pt">' . $value->timeliness . '</td>
                <td width="4.5%" align="center" style="font-size:8pt">' . number_format($value->avg, 2) . '</td>
                <td width="8%" align="center" style="font-size:8pt">' . $value->remarks . '</td>
            </tr>';
            
            $description = $value->description;
            $function_type = $value->function_type;
            };

            $sumCore = 0;
            $sumSup = 0;
            if ($totalAvrgSupport > 0) {
                $sumSup = ($totalAvrgSupport / $supportRows) * 0.1;
            }
            $review = DB::table($this->prfrmnce_db . ".opcr_entry_reviewed")
                ->where('opcr_entry_reviewed.opcr_entry_id', $form['id'])
                ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry_reviewed.reviewed_by) as reviewed_by'),)
                ->get();
            $reviewData = "";
            $reviewDate = "";
            foreach ($review as $key => $valueR) {
                $reviewData .= '
                    <tr>
                        <td align="center" style="font-size:8pt;"><b>' . $valueR->reviewed_by . '</b></td>
                    </tr> ';
                $reviewDate .= '
                <tr>
                    <td align="center" style="font-size:8pt;">' . (date_format(date_create($valueR->date), "m/d/Y")) . '</td>
                </tr>
                ';
            }
            $ave = db::select("SELECT * FROM " . $this->prfrmnce_db . ".rating_table WHERE " . number_format((($totalAvrg / $coreRows) * 0.9) + ($sumSup), 2) . " BETWEEN `from_` AND `to_` ");

            $final = "0.00";
            $finalDescription = "";
            foreach ($ave  as $key => $value) {
                $final = $value->grade;
                $finalDescription = $value->description;
            }
            $Template = '<table width="100%">
                <tr>
                    <td width="100%" align="center" style="font-size:10pt"><b>OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)</b></td>
                </tr>
                <tr><td></td></tr>
                <tr>
                    <td width="100%" style="font-size:8pt;"><b>I, <u>' . $opcrData->Name_Empl . '</u>, Head of <u>' . $opcrData->DEPARTMENT . '</u> of the City  of NAGA, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for the period
                    <u>' . (!empty($opcrData->date_from) ? (date_format(date_create($opcrData->date_from), "M. d, Y")) : "") . ' to ' . (!empty($opcrData->date_to) ? (date_format(date_create($opcrData->date_to), "M. d, Y")) : "") . '</u>.</b></td>
                </tr>
                <tr><td></td></tr>
                <table width="100%" cellpadding="2" >
                <tr>
                    <td style="background-color:#EEEEEE; border-left: 0.3px solid black;border-bottom: 0.3px solid black;border-top: 0.3px solid black;border-top: 0.3px solid black;border-right: 0.3px solid black"><b>Prepared by: </b></td>
                    <td align="center" style="background-color:#EEEEEE; border-top:0.3px solid black;border-right:0.3px solid black"></td>
                    <td style="background-color:#EEEEEE; border-top:0.3px solid black;border-bottom:0.3px solid black;border-right:0.3px solid black"><b>Approved by: </b></td>
                    <td align="center" style="background-color:#EEEEEE; border-top:0.3px solid black;border-right:0.3px solid black"></td>
                </tr>
                <tr>
                    <td style="background-color:#EEEEEE; border-left:0.3px solid black;border-top:0.3px solid black;border-top:0.3px solid black;border-right:0.3px solid black"></td>
                    <td align="center" style="background-color:#EEEEEE; border-right:0.3px solid black"><b>Date</b></td>
                    <td style="background-color:#EEEEEE; border-top:0.3px solid black;border-right:0.3px solid black"></td>
                    <td align="center" style="background-color:#EEEEEE; border-right:0.3px solid black"><b>Date</b></td>
                </tr>
                <tr>
                    <td align="center" style="background-color:#EEEEEE; border-left:0.3px solid black;border-right:0.3px solid black"><b>' . $opcrData->Name_Empl . '</b></td>
                    <td align="center" style="background-color:#EEEEEE; border-right:0.3px solid black"></td>
                    <td align="center" style="background-color:#EEEEEE; border-right:0.3px solid black"><b>' . $opcrData->approved_by . '</b></td>
                    <td align="center" style="background-color:#EEEEEE; border-right:0.3px solid black"></td>
                </tr>
                <tr>
                    <td align="center" style="background-color:#EEEEEE; border-left:0.3px solid black;border-bottom:0.3px solid black;border-right:0.3px solid black"><b>Department Head</b></td>
                    <td align="center" style="background-color:#EEEEEE; border-bottom:0.3px solid black; border-right:0.3px solid black">' . (!empty($opcrData->prepared_date) ? (date_format(date_create($opcrData->prepared_date), "d-M-y")) : "") . '</td>
                    <td align="center" style="background-color:#EEEEEE; border-right:0.3px solid black;border-bottom:0.3px solid black"><b>City Mayor</b></td>
                    <td align="center" style="background-color:#EEEEEE; border-right:0.3px solid black;border-bottom:0.3px solid black">' . (!empty($opcrData->approved_date) ? (date_format(date_create($opcrData->approved_date), "d-M-y")) : "") . ' </td>
                </tr>
                    <tr>
                        <td ></td>
                        <td>  </td>
                    </tr>
                </table>
              
                <table width="100%">
                    <tr>
                        <td width="50%"> </td>
                        <td width="6%" style="font-size:7pt">RATING</td>
                        <td width="15%" style="font-size:7pt;border-left:1px solid black;border-top:1px solid black;border-right:1px solid black">&nbsp;<b> 5 - Outstanding</b></td>
                    </tr>
                    <tr>
                    <td width="50%"> </td>
                    <td width="6%" style="font-size:7pt">SCALE</td>
                        <td width="15%" style="font-size:7pt;border-left:1px solid black;border-right:1px solid black">&nbsp;<b> 4 - Very Satisfactory</b></td>
                    </tr>
                    <tr>
                        <td width="56%"> </td>
                        <td width="15%" style="font-size:7pt;border-left:1px solid black;border-right:1px solid black">&nbsp;<b> 3 - Satisfactory</b></td>
                    </tr>
                <tr>
                     <td width="56%"> </td>
                    <td width="15%" style="font-size:7pt;border-left:1px solid black;border-right:1px solid black">&nbsp;<b> 2 - Unsatisfactory</b></td>
                </tr>
                <tr>
                    <td width="56%"> </td>
                    <td width="15%" style="font-size:7pt;border-left:1px solid black;border-bottom:1px solid black;border-right:1px solid black">&nbsp;<b> 1 - Poor</b></td>
                </tr>
                </table>
                <tr><td></td></tr>
                <table width="100%" border="0.3" cellpadding="2">
                    <tr>
                        <td align="center" rowspan="2" width="4%" style="background-color:#EEEEEE; font-size:8pt"><br/><br/> <b>NO.</b>  </td>
                        <td align="center" rowspan="2" width="19%" style="background-color:#EEEEEE; font-size:8pt"><br/><br/> <b>MFO/PAP</b>  </td>
                        <td align="center" rowspan="2" width="19%" style="background-color:#EEEEEE; font-size:8pt"> <br/><br/> <b>SUCCESS INDICATORS (TARGET + MEASURES)</b>  </td>
                        <td align="center" rowspan="2" width="7%" style="background-color:#EEEEEE; font-size:8pt"> <b> Alloted budget for 2023 </b><br/> (whole year)  </td>
                        <td align="center" rowspan="2" width="12%" style="background-color:#EEEEEE; font-size:8pt"> <b>  INDIVIDUAL/S or Divisions Accountable </b></td>
                        <td align="center" rowspan="2" width="16%" style="background-color:#EEEEEE; font-size:8pt"><b> Actual Accomplishments / Expenses </b></td>
                        <td align="center" width="15%" colspan="4" style="background-color:#EEEEEE; font-size:8pt"><b> Rating </b></td>
                        <td align="center" rowspan="2" width="8%" style="background-color:#EEEEEE; font-size:8pt"><b> Remarks </b></td>
                    </tr>
                    <tr>
                        <td align="center" width="3.5%"><b>Q</b></td>
                        <td align="center" width="3.5%"><b>E</b></td>
                        <td align="center" width="3.5%" ><b>T</b></td>
                        <td align="center" width="4.5%"><b>A</b></td>
                    </tr>
                    <tr>
                        <td width="100%" style="background-color:#ECF87F;"><b><i>Core Functions</i></b></td>
                    </tr>
                   ' . $mfoData . '
                    </table>    
                <table width="100%" border="0.3" cellpadding="2">
                    ' . $supportFunction . '
                </table>
                <table width="100%" border="0.3" cellpadding="2">
                    <tr>
                        <td align="left"  width="49%" style="font-size:8pt"><b>SUMMARY  OF RATING</b></td>
                        <td align="center"  width="12%" style="font-size:8pt">TOTAL</td>
                        <td align="center"  width="19.5%" style="font-size:8pt">Final Numerical Rating</td>
                        <td align="center"  width="19.5%" style="font-size:8pt">Final Adjectival Rating</td>
                    </tr>

                    <tr>
                        <td align="center"  width="15%" style="font-size:8pt"><b>' . number_format((($totalAvrg / $coreRows) * 0.9), 2) . '</b></td>
                        <td align="center"   width="34%" style="font-size:8pt">Formula: (total of all average ratings / no. of entries) x 90%	</td>
                        <td align="center"  rowspan="2" width="12%" style="font-size:8pt">&nbsp;<br /><b>' . number_format((($totalAvrg / $coreRows) * 0.9) + ($sumSup), 2) . '</b></td>
                        <td align="center" rowspan="2" width="19.5%" style="font-size:8pt">&nbsp;<br /><b>' . $final . '</b></td>
                        <td align="center" rowspan="2" width="19.5%" style="font-size:8pt">&nbsp;<br /><b>' . $finalDescription . '</b></td>
                    </tr>
                    <tr>
                        <td align="center"  width="15%" style="font-size:8pt"><b>' . number_format($sumSup, 2) . '</b></td>
                        <td align="center"   width="34%" style="font-size:8pt">Formula: (total of all average ratings / no. of entries) x 10%	</td>
                    </tr>

                    <tr>
                        <td height="30px"  width="100%" style="font-size:8pt"><b>Comments and Recommendation for Development Purposes:</b> <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>'.$opcrData->comments.'</b></td>
                    </tr>
                    <tr>
                        <td width="20%" align="left" style="background-color:#EEEEEE; font-size:8pt"><b>Assessed by:</b></td>
                        <td width="20%" align="center" style="background-color:#EEEEEE; font-size:8pt"><b>Date</b></td>
                        <td width="18%" align="center" style="background-color:#EEEEEE; font-size:8pt"><b>Reviewed by:</b></td>
                        <td width="17%" align="left" style="background-color:#EEEEEE; font-size:8pt"><b>Date</b></td>
                        <td width="15%" align="left" style="background-color:#EEEEEE; font-size:8pt"><b>Final Rating by</b></td>
                        <td width="10%" align="center" style="background-color:#EEEEEE; font-size:8pt"><b>Date</b></td>
                    </tr>
                    </table>
                    <tr>
                    <td width="20%" style="font-size:8pt;border-right:1px solid black;border-left:1px solid black;border-bottom:1px solid black;">
                        <table width="100%">
                                
                            <tr>
                                <td align="center" style="font-size:8pt"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt"><b>' . $opcrData->assess . '</b></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt"></td>
                            </tr>
                            
                        </table>
                    </td>
                    <td width="20%" style="font-size:8pt;border-right:1px solid black;border-bottom:1px solid black;">
                        <table width="100%">
                                
                            <tr>
                                <td align="center" style="font-size:8pt"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt">' . (!empty($opcrData->assessed_date) ? (date_format(date_create($opcrData->assessed_date), "d-M-y")) : "") . '</td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                        
                        </table>
                    </td>
                    <td width="18%" style="font-size:8pt;border-right:1px solid black;border-bottom:1px solid black;">
                                <table width="100%">
                                    <tr>
                                        <td></td>
                                    </tr>
                                    '. $reviewData.'

                                    
                                </table>
                    </td>

                    <td width="17%" style="font-size:8pt;border-right:1px solid black;border-bottom:1px solid black;">
                        <table width="100%">
                        <tr>
                            <td></td>
                        </tr>
                            '.$reviewDate.'
                        </table>
                    </td>
                    <td width="15%" style="font-size:8pt;border-right:1px solid black;border-bottom:1px solid black;">
                        <table width="100%">
                        
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"><b>' . $opcrData->final . '</b></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                            
                        </table>
                </td>
                <td width="10%" style="font-size:8pt;border-right:1px solid black;border-bottom:1px solid black;">
                    <table width="100%">
                        <tr>
                            <td align="center" style="font-size:8pt;"></td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:8pt;">' . (!empty($opcrData->final_rating_date) ? (date_format(date_create($opcrData->final_rating_date), "d-M-y")) : "") . '</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:8pt;"></td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:8pt;"></td>
                        </tr>
                        
                    </table>
            </td>
                </tr>
                <tr>
                        <td width="20%" align="center" style="background-color:#EEEEEE; font-size:8pt;border-bottom:0.3px solid black;border-left:0.3px solid black;border-right:0.3px solid black"><b>Planning Office</b></td>
                        <td width="20%" align="center" style="background-color:#EEEEEE; font-size:8pt;border-bottom:0.3px solid black;border-right:0.3px solid black"></td>
                        <td width="18%" align="center" style="background-color:#EEEEEE; font-size:8pt;border-bottom:0.3px solid black;border-right:0.3px solid black"><b>PMT</b></td>
                        <td width="17%" align="center" style="background-color:#EEEEEE; font-size:8pt;border-bottom:0.3px solid black;border-right:0.3px solid black"></td>
                        <td width="15%" align="center" style="background-color:#EEEEEE; font-size:8pt;border-bottom:0.3px solid black;border-right:0.3px solid black"><b>Head of Agency</b></td>
                        <td width="10%" align="center" style="background-color:#EEEEEE; font-size:8pt;border-bottom:0.3px solid black;border-right:0.3px solid black"></td>
                    </tr>
                    <tr>
                        <td width="100%"><i>*Legend: Q-Quality E-Effificiency (Quantity)  T-Timeliness  A-Average</i></td>
                    </tr>
                    <tr>
                        <td width="45%"></td>
                        <td width="30%">RANGE OF OVERALL PTS</td>
                        <td width="6%">NR</td>
                        <td width="6%">AR</td>
                    </tr>
                    <tr>
                        <td width="40%"></td>
                        <td width="31%">1.00</td>
                        <td width="4%">1.50</td>
                        <td width="5%">1</td>
                        <td width="6%">Poor</td>
                    </tr>
                    <tr>
                        <td width="40%"></td>
                        <td width="31%">1.51</td>
                        <td width="4%">2.50</td>
                        <td width="5%">2</td>
                        <td width="10%">Unsatisfactory</td>
                    </tr>
                    <tr>
                        <td width="40%"></td>
                        <td width="31%">2.51</td>
                        <td width="4%">3.50</td>
                        <td width="5%">3</td>
                        <td width="10%">Satisfactory</td>
                    </tr>
                    <tr>
                        <td width="40%"></td>
                        <td width="31%">3.51</td>
                        <td width="4%">4.50</td>
                        <td width="5%">4</td>
                        <td width="20%">Very Satisfactory</td>
                    </tr>
                    <tr>
                        <td width="40%"></td>
                        <td width="31%">4.51</td>
                        <td width="4%">5.50</td>
                        <td width="5%">5</td>
                        <td width="20%">Outstanding</td>
                    </tr>

            </table>';

            PDF::SetTitle('Office Performance Commitment and Review (OPCR) Accomplishment');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('P', array(215.9, 355.6));
            PDF::lastPage();
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
