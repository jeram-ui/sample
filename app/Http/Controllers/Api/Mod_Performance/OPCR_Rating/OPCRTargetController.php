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

class OPCRTargetController extends Controller
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

    // public function GetDept()
    // {
    //     $list = DB::table($this->hr_db . '.department')
    //         ->select("*", 'SysPK_Dept', 'Name_Dept')
    //         ->where('department.status', 'Active')
    //         ->get();

    //     return response()->json(new JsonResponse($list));
    // }

    public function Edit($id)
    {
        $data['form'] = db::table($this->prfrmnce_db . '.setup_opcr')
            ->where('setup_opcr.id', $id)
            ->get();

        // $setupDtl = db::table($this->prfrmnce_db . '.setup_opcr_details')
        //     // ->join($this->prfrmnce_db . '.setup_opcr', 'setup_opcr.id', 'setup_opcr_details.setup_ipcr_id')
        //     // ->select('setup_opcr_details.*', 'setup_opcr.dept_id')
        //     ->where('setup_ipcr_id', $id);

        // $dummyDesc = DB::table($this->prfrmnce_db . '.setup_mfopap')
        //     ->leftJoin($this->prfrmnce_db . '.rating_matrix_setup', 'rating_matrix_setup.mfo_pap', 'setup_mfopap.id')
        //     ->leftJoinSub($setupDtl, 'setupDtl', function ($join) {
        //         $join->on('setupDtl.martix_id', '=', 'rating_matrix_setup.id');
        //     })
        //     ->select("setup_mfopap.*",'setupDtl.id as rating_id','setupDtl.mfo_id','rating_matrix_setup.id as martix_id', "rating_matrix_setup.success_indicators", "setupDtl.selectx", "setupDtl.function_type", "setupDtl.sorting", "setupDtl.alloted_budget"
            
        //     )
        //     ->orderBy('setupDtl.function_type')
        //     ->orderBy('rating_matrix_setup.function_type')
        //     ->orderBy('setup_mfopap.MFO_dscrptn')
        //     ->where("setup_mfopap.status",0)
        //     ->where("rating_matrix_setup.status",0)
        //     // ->where('rating_matrix_setup.dept_name', $dept)
        //     ->get();

        $dummyDesc = db::select("call " . $this->prfrmnce_db . ".OPCRTarget_modify(?)", [$id]);

        $detail = array();
        foreach ($dummyDesc as $key => $value) {
            $dumAccountable = db::table($this->prfrmnce_db . '.setup_opcr_accountable')
                ->where("rating_id", $value->rating_id)
                ->get();
            $arrayAccountable = array();
            foreach ($dumAccountable as $keyC => $valueC) {
                array_push( $arrayAccountable, $valueC->accountable_id);
            }

            $descData = array(
                'rating_id' => $value->rating_id,
                'id' => $value->mfo_id,
                'selectx' => $value->selectx === 'true' ? 'true' : 'false',
                'sorting' => $value->sorting,
                'description' => $value->description,
                'alloted_budget' => $value->alloted_budget,
                'function_type' => $value->function_type,
                'MFO_dscrptn' => $value->MFO_dscrptn,
                'success_indicators' => $value->success_indicators,
                'martix_id' => $value->martix_id,
                'r_accountable' => $arrayAccountable,
            );
            array_push($detail, $descData);
        }
        $data['formx'] = $detail;

        $formz = db::table($this->prfrmnce_db . '.setup_opcr_reviewed')
            ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr_reviewed.reviewed_by) as NAME'))
            ->where('setup_opcr_reviewed.ipcr_entry_id', $id)
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
    public function EditCopy($id) 
    {
        $data['form'] = db::table($this->prfrmnce_db . '.setup_opcr')
            ->where('setup_opcr.id', $id)
            ->get();

        $dummyDesc = db::select("call " . $this->prfrmnce_db . ".OPCRTarget_modify(?)", [$id]);

        $detail = array();
        foreach ($dummyDesc as $key => $value) {
            $dumAccountable = db::table($this->prfrmnce_db . '.setup_opcr_accountable')
                ->where("rating_id", $value->rating_id)
                ->get();
            $arrayAccountable = array();
            foreach ($dumAccountable as $keyC => $valueC) {
                array_push( $arrayAccountable, $valueC->accountable_id);
            }

            $descData = array(
                'rating_id' => $value->rating_id,
                'id' => $value->mfo_id,
                'selectx' => $value->selectx === 'true' ? 'true' : 'false',
                'sorting' => $value->sorting,
                'description' => $value->description,
                'alloted_budget' => $value->alloted_budget,
                'function_type' => $value->function_type,
                'MFO_dscrptn' => $value->MFO_dscrptn,
                'success_indicators' => $value->success_indicators,
                'martix_id' => $value->martix_id,
                'r_accountable' => $arrayAccountable,
            );
            array_push($detail, $descData);
        }
        $data['formx'] = $detail;

        $formz = db::table($this->prfrmnce_db . '.setup_opcr_reviewed')
            ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr_reviewed.reviewed_by) as NAME'))
            ->where('setup_opcr_reviewed.ipcr_entry_id', $id)
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
    // public function GetName($id)
    // {
    //     $list = DB::table($this->hr_db . '.employee_information')
    //         ->where('DEPID', $id)
    //         ->get();
    //     return response()->json(new JsonResponse($list));
    // }
    // public function GetPeriod()
    // {
    //     $list = DB::table($this->prfrmnce_db . '.evaluation_period')
    //         ->select('*', db::raw('CONCAT(date_from," - ",date_to) AS period'), 'date_from', 'date_to')
    //         ->where('evaluation_period.status', 0)
    //         ->get();
    //     return response()->json(new JsonResponse($list));
    // }
    public function getList(Request $request)
    {
        $list = DB::table($this->prfrmnce_db . '.setup_opcr')
            // ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'setup_opcr.emp_id')
            ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'setup_opcr.period_id')
            ->select(
                "*",
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.approved_by) as approved_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.prepared_by) as prepared_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.final_rating_by) as final_rating_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.assessed_by) as assessed_by'),
                db::raw($this->hr_db . '.getEmployeeDept(setup_opcr.dept_id) AS department'),
                db::raw('CONCAT(date_from," - ",date_to) AS period'),
                'date_from',
                'date_to',
                'evaluation_period.id',
                'setup_opcr.id'
            )
            ->where('setup_opcr.status', 0)
            // ->where('emp_id',Auth::user()->Employee_id)
            ->orderBy('setup_opcr.id')
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }
    public function showMFObyDept($id)
    {
        $list = db::select("call " . $this->prfrmnce_db . ".showMFO_byDEPT(?)", [$id]);
        $final = array();
        foreach ($list as $key => $value) {

            $dum = array(
                'id'=> $value->id,
                'function_type' => $value->function_type,
                'selectx' => $value->selectx,
                'sorting' => $value->sorting,
                'description' => $value->description,
                'MFO_dscrptn' => $value->MFO_dscrptn,
                // 'mfo_id' => $value->id,
                'success_indicators' => $value->success_indicators,
                'alloted_budget' =>  $value->alloted_budget,
                'r_accountable' => [],
                'matrix_id' => $value->matrix_id,
            );
            array_push($final, $dum);
        }
        return response()->json(new JsonResponse($final));
    }
    public function storeCopyDat(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $formz = $request->formz;
            $chk = db::table($this->prfrmnce_db . '.setup_opcr')
            // ->where("empName", $form['empName'])
            ->where("dept_id", $form['dept_id'])
            ->where("period_id", $form['period_id'])
            ->where("setup_opcr.status", 0)
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
                    // 'emp_id' => $form['emp_id'],
                    // 'empName' => $form['empName'],
                    'period_id' => $form['period_id'],
                    'prepared_date' => $form['prepared_date'],
                    'prepared_by' => $form['prepared_by'],
                    'approved_by' => $form['approved_by'],
                    'approved_date'=> $form['approved_date'],
                    'final_rating_by'=> $form['final_rating_by'],
                    'final_rating_date'=> $form['final_rating_date'],
                    'assessed_by'=> $form['assessed_by'],
                    'assessed_date'=> $form['assessed_date'],
                    // 'job_desc' => $form['job_desc'],

                );
                DB::table($this->prfrmnce_db . '.setup_opcr')->insert($datax);
                $id = DB::getPdo()->LastInsertId();

                foreach ($formx as $key => $value) {
                    $formxData = array(
                        'setup_ipcr_id' => $id,
                        'function_type' => $value['function_type'],
                        'sorting' => $value['sorting'],
                        'mfo_id' => $value['id'],
                        'selectx' => $value['selectx'],
                        'martix_id' => $value['martix_id'],
                        'alloted_budget' => $value['alloted_budget'],
                    );
                    db::table($this->prfrmnce_db . '.setup_opcr_details')->insert($formxData);
                    $rating_id = DB::getPdo()->LastInsertId();

                    foreach ($value['r_accountable'] as $keyx => $valuex) {
                        $formRData = array(

                            'rating_id' =>  $rating_id,
                            'accountable_id' => $valuex,
                        );
                        db::table($this->prfrmnce_db . '.setup_opcr_accountable')->insert($formRData);
                    }
                }
                foreach ($formz as $key => $valuex) {
                    $formzData = array(
                        'ipcr_entry_id' => $id,
                        'date' => $valuex['date'],
                        'reviewed_by' => $valuex['reviewed_by'],
                    );
                    db::table($this->prfrmnce_db . '.setup_opcr_reviewed')->insert($formzData);
                }
            }
        // }
        return  $this->G->success();
    }
    public function store(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $formz = $request->formz;
        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->prfrmnce_db . '.setup_opcr')
            ->where('id', $id)
            ->update($form);

            // db::table($this->prfrmnce_db . '.setup_opcr_details')
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
                    'alloted_budget' => $value['alloted_budget'],

                );
                // if ($value['selectx'] === 'true') {
                //     if ($value['id'] > 0) {
                        db::table($this->prfrmnce_db . '.setup_opcr_details')
                        ->where("martix_id",$value['martix_id'])
                        ->where("setup_ipcr_id",$id)
                        ->update($formxData);
                        
                //     }else{
                //         db::table($this->prfrmnce_db . '.setup_opcr_details')->insert($formxData);
                //         $rating_id = DB::getPdo()->LastInsertId();
                //     }
            
                // }
                // $rating_id = DB::getPdo()->LastInsertId();

                foreach ($value['r_accountable'] as $keyx => $valuex) {
                    $formRData = array(
                        'rating_id' => $value['rating_id'],
                        'accountable_id' => $valuex
                    );
                    db::table($this->prfrmnce_db . '.setup_opcr_accountable')->insert($formRData);
                }
            }
            db::table($this->prfrmnce_db . '.setup_opcr_reviewed')
            ->where("ipcr_entry_id", $id)
            ->delete();
            foreach ($formz as $key => $valuex) {
                $formzData = array(
                    'ipcr_entry_id' => $id,
                    'date' => $valuex['date'],
                    'reviewed_by' => $valuex['reviewed_by'],
                );
                db::table($this->prfrmnce_db . '.setup_opcr_reviewed')->insert($formzData);
            }
        } else {
            $chk = db::table($this->prfrmnce_db . '.setup_opcr')
            // ->where("empName", $form['empName'])
            ->where("dept_id", $form['dept_id'])
            ->where("period_id", $form['period_id'])
            ->where("setup_opcr.status", 0)
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

                DB::table($this->prfrmnce_db . '.setup_opcr')->insert($form);
                $id = DB::getPdo()->LastInsertId();
                
                foreach ($formx as $key => $value) {
                    $formxData = array(
                        'setup_ipcr_id' => $id,
                        'mfo_id' => $value['id'],
                        'function_type' => $value['function_type'],
                        'sorting' => $value['sorting'],
                        'selectx' => $value['selectx'],
                        'martix_id' => $value['matrix_id'],
                        'alloted_budget' => $value['alloted_budget'],
                    );
                    db::table($this->prfrmnce_db . '.setup_opcr_details')->insert($formxData);
                    $rating_id = DB::getPdo()->LastInsertId();

                    foreach ($value['r_accountable'] as $keyx => $valuex) {
                        $formRData = array(

                            'rating_id' =>  $rating_id,
                            'accountable_id' => $valuex,
                        );
                        db::table($this->prfrmnce_db . '.setup_opcr_accountable')->insert($formRData);
                    }
                }
                foreach ($formz as $key => $valuex) {
                    $formzData = array(
                        'ipcr_entry_id' => $id,
                        'date' => $valuex['date'],
                        'reviewed_by' => $valuex['reviewed_by'],
                    );
                    db::table($this->prfrmnce_db . '.setup_opcr_reviewed')->insert($formzData);
                }
            }
        }
        return  $this->G->success();
    }
    // public function getEmpList($id)
    // {
    //     $list = DB::table($this->prfrmnce_db . '.opcr_entry')
    //         ->orderBy('opcr_entry.id','ASC')
    //         ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'opcr_entry.dept_id')
    //         ->leftJoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'opcr_entry.prepared_by')
    //         ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'opcr_entry.period_id')
    //         ->select(
    //             "*",
    //             DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.approved_by) as approved_by'),
    //             DB::raw($this->hr_db . '.jay_getEmployeeName(opcr_entry.prepared_by) as prepared_by'),
    //             db::raw('CONCAT(date_from," - ",date_to) AS period'),
    //             'date_from',
    //             'date_to',
    //             'Name_Dept',
    //             'evaluation_period.id',
    //             'opcr_entry.id'
    //         )
    //         ->where('opcr_entry.status', 0)
    //         ->where('opcr_entry.dept_id', $id)
    //         // ->where('emp_id',Auth::user()->Employee_id)
    //         ->get();
    //     // $list="";
    //     return response()->json(new JsonResponse($list));
    // }
    public function cancel($id)
    {
        db::table($this->prfrmnce_db . '.setup_opcr')
            ->where('id', $id)
            ->update(['setup_opcr.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function print(Request $request)
    {
        try {
            $id = $request->id;
            $form = $request->itm;
            $opcr = DB::table($this->prfrmnce_db . '.setup_opcr')
                ->join($this->hr_db . '.employee_information', 'employee_information.DEPID', 'setup_opcr.dept_id')
                ->join($this->prfrmnce_db . '.evaluation_period', 'evaluation_period.id', 'setup_opcr.period_id')
                ->leftJoin($this->hr_db . '.employees', 'employees.SysPK_Empl', 'employee_information.headId')
                ->select(
                    '*',
                    DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.assessed_by) as assessed_by'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.prepared_by) as prepared_by'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.approved_by) as approved_by'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr.final_rating_by) as final_rating_by'),
                    db::raw('CONCAT(date_from," to ",date_to) AS period'),
                    'date_from',
                    'date_to',
                    'evaluation_period.id'
                )
                ->where('setup_opcr.id', $form['id'])
                ->get();
            $opcrData = "";
            foreach ($opcr as $key => $value) {
                $opcrData = $value;
            };
            log::debug(1);
            $mfo = db::select("call " . $this->prfrmnce_db . ".OPCR_PrintCore(?)", [$form['id']]);
            $mfoData = "";

            // $avg = 0;
            // $coreRows = 0;
            // $totalAvrg = 0;
            $function_type = "";
            $description="";
            foreach ($mfo as $key => $value) {
                // $avgVal = 0;
                // $coreRows = $coreRows + 1;
                // $totalAvrg += $value->avg;
                if ($function_type !== $value->function_type) {
                    $mfoData .= '<tr>
                    <td width="100%" style="background-color:#ECF87F;"><b><i>Core Functions</i></b></td>
                </tr>';
                }
                 if ($description !==  $value->description) {
                    $mfoData .= '<tr>
                        <td width="100%" style="font-size:8pt"><b>' . $value->description. '</b>
                    </td>
                </tr>';
                }
                $mfoData .= '<tr>
                    <td align="center" width="4%" style="font-size:8pt">' . $value->sorting . '
                    </td>
                    <td width="19%" style="font-size:8pt">' . $value->MFO_dscrptn . '
                    </td>
                    <td width="19%" style="font-size:8pt"> ' . $value->success_indicators . '
                    </td>
                    <td width="7%" align="right" style="font-size:8pt">' . number_format($value->alloted_budget, 2) . '
                    </td>
                    <td width="12%" style="font-size:8pt">'.$value->accountable.'
                    </td>
                    <td width="16%" style="font-size:8pt">
                    </td>
                    <td width="3.5%" align="center" style="font-size:8pt">-</td>
                    <td width="3.5%" align="center" style="font-size:8pt"></td>
                    <td width="3.5%" align="center" style="font-size:8pt"></td>
                    <td width="4.5%" align="center" style="font-size:8pt">0</td>
                    <td width="8%" align="center" style="font-size:8pt"></td>

                </tr>';
                 
                $description = $value->description;
                $function_type = $value->function_type;
            };

            $supF = db::select("call " . $this->prfrmnce_db . ".OPCR_TargetPrintSupport(?)", [$form['id']]);
            $supportFunction = "";

            // $avg = 0;
            // $supportRows = 0;
            // $totalAvrgSupport = 0;
            // $supNull = "";
            $function_type="";
            $description="";
            foreach ($supF as $key => $value) {
                // if ($value->avg > 0) {
                //     $supportRows = $supportRows + 1;
                // }
                // $totalAvrgSupport += $value->avg;

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
                $supportFunction .= '<tr>
                    <td align="center" width="4%" style="font-size:8pt">' . $value->sorting . '
                    </td>
                    <td width="19%" style="font-size:8pt">' . $value->MFO_dscrptn . '
                    </td>
                    <td width="19%" style="font-size:8pt"> ' . $value->success_indicators . '
                    </td>
                    <td width="7%" align="right" style="font-size:8pt">' . number_format($value->alloted_budget, 2) . '
                    </td>
                    <td width="12%" style="font-size:8pt">'.$value->accountable.'
                    </td>
                    <td width="16%" style="font-size:8pt">
                    </td>
                    <td width="3.5%" align="center" style="font-size:8pt">-</td>
                    <td width="3.5%" align="center" style="font-size:8pt"></td>
                    <td width="3.5%" align="center" style="font-size:8pt"></td>
                    <td width="4.5%" align="center" style="font-size:8pt">0</td>
                    <td width="8%" align="center" style="font-size:8pt"></td>
                </tr>';
                 } else {
                $supportFunction .= '<tr>
                    <td align="center" width="4%" style="font-size:8pt">' . $value->sorting . '
                    </td>
                    <td width="19%" style="font-size:8pt">' . $value->MFO_dscrptn . '
                    </td>
                    <td width="19%" style="font-size:8pt"> ' . $value->success_indicators . '
                    </td>
                    <td width="7%" align="right" style="font-size:8pt">' . number_format($value->alloted_budget, 2) . '
                    </td>
                    <td width="12%" style="font-size:8pt">'.$value->accountable.'
                    </td>
                    <td width="16%" style="font-size:8pt">
                    </td>
                    <td width="3.5%" align="center" style="font-size:8pt">-</td>
                    <td width="3.5%" align="center" style="font-size:8pt"></td>
                    <td width="3.5%" align="center" style="font-size:8pt"></td>
                    <td width="4.5%" align="center" style="font-size:8pt">0</td>
                    <td width="8%" align="center" style="font-size:8pt"></td>
                </tr>';
            }
            $description = $value->description;
            $function_type = $value->function_type;
            };

            // $sumCore = 0;
            // $sumSup = 0;
            // if ($totalAvrgSupport > 0) {
            //     $sumSup = ($totalAvrgSupport / $supportRows) * 0.1;
            // }
            $review = DB::table($this->prfrmnce_db . ".setup_opcr_reviewed")
                ->where('setup_opcr_reviewed.ipcr_entry_id', $form['id'])
                ->select("*", DB::raw($this->hr_db . '.jay_getEmployeeName(setup_opcr_reviewed.reviewed_by) as reviewed_by'),)
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
                    <td align="center" style="font-size:8pt;">' . (date_format(date_create($valueR->date), "d-M-y")) . '</td>
                </tr>
                ';
            }
            // $ave = db::select("SELECT * FROM " . $this->prfrmnce_db . ".rating_table WHERE " . number_format((($totalAvrg / $coreRows) * 0.9) + ($sumSup), 2) . " BETWEEN `from_` AND `to_` ");

            // $final = "0.00";
            // $finalDescription = "";
            // foreach ($ave  as $key => $value) {
            //     $final = $value->grade;
            //     $finalDescription = $value->description;
            // }
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
                        <td style="background-color:#EEEEEE; border-left: 0.3px solid black;border-bottom: 0.3px solid black;border-top: 0.3px solid black;border-top: 0.3px solid black;border-right: 0.3px solid black"><b>Prepared by:</b> </td>
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
                        <td align="center" style="background-color:#EEEEEE; border-left:0.3px solid black;border-right:0.3px solid black"><b>' . $opcrData->prepared_by . '</b></td>
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
                <tr><td></td></tr>
                <table width="100%">
                    <tr>
                        <td width="50%"> </td>
                        <td width="6%" style="font-size:7pt">RATING</td>
                        <td width="15%" style="font-size:7pt;border-left:0.3px solid black;border-top:0.3px solid black;border-right:0.3px solid black">&nbsp;<b> 5 - Outstanding</b></td>
                    </tr>
                    <tr>
                    <td width="50%"> </td>
                    <td width="6%" style="font-size:7pt">SCALE</td>
                        <td width="15%" style="font-size:7pt;border-left:0.3px solid black;border-right:0.3px solid black">&nbsp;<b> 4 - Very Satisfactory</b></td>
                    </tr>
                    <tr>
                        <td width="56%"> </td>
                        <td width="15%" style="font-size:7pt;border-left:0.3px solid black;border-right:0.3px solid black">&nbsp;<b> 3 - Satisfactory</b></td>
                    </tr>
                <tr>
                     <td width="56%"> </td>
                    <td width="15%" style="font-size:7pt;border-left:0.3px solid black;border-right: 0.3px solid black">&nbsp;<b> 2 - Unsatisfactory</b></td>
                </tr>
                <tr>
                    <td width="56%"> </td>
                    <td width="15%" style="font-size:7pt;border-left: 0.3px solid black;border-bottom: 0.3px solid black;border-right:0.3px solid black">&nbsp;<b> 1 - Poor</b></td>
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
                        <td align="center" width="15%" colspan="4" style="background-color:#EEEEEE; font-size:8pt"><b> Rating* </b></td>
                        <td align="center" rowspan="2" width="8%" style="background-color:#EEEEEE; font-size:8pt"><b> Remarks </b></td>
                    </tr>
                    <tr>
                        <td align="center" width="3.5%" style="background-color:#EEEEEE"><b>Q</b></td>
                        <td align="center" width="3.5%" style="background-color:#EEEEEE"><b>E</b></td>
                        <td align="center" width="3.5%" style="background-color:#EEEEEE"><b>T</b></td>
                        <td align="center" width="4.5%" style="background-color:#EEEEEE"><b>A</b></td>
                    </tr>
                    
                    '. $mfoData.'
                    '. $supportFunction.'
                    </table>    
                <table width="100%" border="0.3" cellpadding="2">
                    

                </table>
                <table width="100%" border="0.3" cellpadding="2">
                    <tr>
                        <td align="left"  width="49%" style="font-size:8pt"><b>SUMMARY  OF RATING</b></td>
                        <td align="center"  width="12%" style="font-size:8pt">TOTAL</td>
                        <td align="center"  width="19.5%" style="font-size:8pt">Final Numerical Rating</td>
                        <td align="center"  width="19.5%" style="font-size:8pt">Final Adjectival Rating</td>
                    </tr>

                    <tr>
                        <td align="center"  width="15%" style="font-size:8pt"><b>0</b></td>
                        <td align="center"   width="34%" style="font-size:8pt">Formula: (total of all average ratings / no. of entries) x 90%	</td>
                        <td align="center"  rowspan="2" width="12%" style="font-size:8pt">&nbsp;<br /><b>0</b></td>
                        <td align="center" rowspan="2" width="19.5%" style="font-size:8pt"><b></b></td>
                        <td align="center" rowspan="2" width="19.5%" style="font-size:8pt"><b></b></td>
                    </tr>
                    <tr>
                        <td align="center"  width="15%" style="font-size:8pt"><b>0</b></td>
                        <td align="center"   width="34%" style="font-size:8pt">Formula: (total of all average ratings / no. of entries) x 10%	</td>
                    </tr>

                    <tr>
                        <td height="20px"  width="100%" style="font-size:8pt"><b>Comments and Recommendation for Development Purposes:</b></td>
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
                    <td width="20%" style="font-size:8pt;border-right:0.3px solid black;border-left:0.3px solid black;border-bottom:0.3px solid black">
                        <table width="100%">
                            
                            <tr>
                                <td align="center" style="font-size:8pt"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt"><b>' . $opcrData->assessed_by . '</b></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt"></td>
                            </tr>
                            
                        </table>
                    </td>
                    <td width="20%" style="font-size:8pt;border-right:0.3px solid black;border-bottom:0.3px solid black;">
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
                    <td width="18%" style="font-size:8pt;border-right:0.3px solid black;border-bottom:0.3px solid black;">
                            <table width="100%">
                            <tr>
                                <td></td>
                            </tr>
                                '. $reviewData.'

                                
                            </table>
                        </td>

                    <td width="17%" style="font-size:8pt;border-right:0.3px solid black;border-bottom:0.3px solid black;">
                        <table width="100%">
                            '.$reviewDate.'
                        </table>
                    </td>
                    <td width="15%" style="font-size:8pt;border-right:0.3px solid black;border-bottom:0.3px solid black;">
                        <table width="100%">
                        
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"><b>' . $opcrData->final_rating_by . '</b></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:8pt;"></td>
                            </tr>
                            
                        </table>
                    </td>
                    <td width="10%" style="font-size:8pt;border-right:0.3px solid black;border-bottom:0.3px solid black;">
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
            </table>';

            PDF::SetTitle('Office Performance Commitment and Review (OPCR) Target');
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
