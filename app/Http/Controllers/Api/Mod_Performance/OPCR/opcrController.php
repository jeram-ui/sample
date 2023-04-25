<?php

namespace App\Http\Controllers\Api\Mod_Performance\OPCR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class opcrController extends Controller
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
        $this->prfrmnce_db = $this->G->getPerformance();
    }

    public function GetEmpName()
    {
        $list = DB::table($this->hr_db . '.employee_information')
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
    public function getFgroup()
    {
        $list = DB::table($this->prfrmnce_db . '.setup_fnctiongroup')
            ->select("*", 'id', 'description')
            // ->where('department', $id)
            ->where('setup_fnctiongroup.status', 0)
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function getMFO($id)
    {
        $list = DB::table($this->prfrmnce_db . '.setup_mfopap')
            ->select("*", 'id', 'MFO_dscrptn')
            ->where('fnctngroup_id', $id)
            ->where('setup_mfopap.status', 0)
            ->get();

        return response()->json(new JsonResponse($list));
    }
    // public function getMFO()
    // {
    //     $list = DB::table($this->prfrmnce_db . '.setup_mfopap')
    //         ->select("*", 'id', 'MFO_dscrptn')
    //         // ->where('fnctngroup_id', $id)
    //         ->where('setup_mfopap.status', 0)
    //         ->get();

    //     return response()->json(new JsonResponse($list));
    // }

    public function getRatingsQ()
    {
        $list = DB::table($this->prfrmnce_db . '.setup_ratings_quality')
            ->select("*", 'id', 'description')
            ->where('setup_ratings_quality.status', 0)
            ->get();

        return response()->json(new JsonResponse($list));
    }

    public function getRatingsE()
    {
        $list = DB::table($this->prfrmnce_db . '.setup_ratings_efficiency')
            ->select("*", 'id', 'description')
            ->where('setup_ratings_efficiency.status', 0)
            ->get();

        return response()->json(new JsonResponse($list));
    }

    public function store(Request $request)
    {
        $form = $request->form;
        $formz = $request->formz;
        $formx = $request->formx;
        $formc = $request->formc;


        $id = $form['id'];
        if ($id > 0) {
            db::table($this->prfrmnce_db . ".rating_matrix_setup")
                ->where('id', $id)
                ->update($form);

            // db::table($this->hr_db .".tbl_overtime_cert")
            // ->where('cert_id' ,$id)
            // ->update(['status' => 'Approved']);


            db::table($this->prfrmnce_db . ".setup_ratings_quality")
                ->where("rating_id", $id)
                ->delete();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'rating_id' => $id,
                    'description' => $value['description'],
                    'qty' => $value['qty'],

                );
                db::table($this->prfrmnce_db . ".setup_ratings_quality")->insert($datx);
            }


            db::table($this->prfrmnce_db . ".setup_ratings_efficiency")
                ->where("rating_id", $id)
                ->delete();

            foreach ($formz as $key => $value) {
                $datx = array(
                    'rating_id' => $id,
                    'description' => $value['description'],
                    'qty' => $value['qty'],
                );
                db::table($this->prfrmnce_db . ".setup_ratings_efficiency")->insert($datx);
            }


            db::table($this->prfrmnce_db . ".setup_ratings_timeliness")
                ->where("rating_id", $id)
                ->delete();

            foreach ($formc as $key => $value) {
                $datx = array(
                    'rating_id' => $id,
                    'description' => $value['description'],
                    'qty' => $value['qty'],
                );
                db::table($this->prfrmnce_db . ".setup_ratings_timeliness")->insert($datx);
            }
        } else {
            db::table($this->prfrmnce_db . ".rating_matrix_setup")->insert($form);
            $id = DB::getPdo()->LastInsertId();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'rating_id' => $id,
                    'description' => $value['description'],
                    'qty' => $value['qty'],

                );
                db::table($this->prfrmnce_db . ".setup_ratings_quality")->insert($datx);
            }

            foreach ($formz as $key => $value) {
                $datx = array(
                    'rating_id' => $id,
                    'description' => $value['description'],
                    'qty' => $value['qty'],
                );
                db::table($this->prfrmnce_db . ".setup_ratings_efficiency")->insert($datx);
            }

            foreach ($formc as $key => $value) {
                $datx = array(
                    'rating_id' => $id,
                    'description' => $value['description'],
                    'qty' => $value['qty'],
                );
                db::table($this->prfrmnce_db . ".setup_ratings_timeliness")->insert($datx);
            }
        }
    }
    public function getRatingMatrix()
    {
        $list = DB::table($this->prfrmnce_db . '.rating_matrix_setup')
            ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'rating_matrix_setup.dept_name')
            ->select('*', db::raw('SysPK_Dept', 'Name_Dept'), 'department.SysPK_Dept', 'rating_matrix_setup.id')
            ->leftjoin($this->prfrmnce_db . '.setup_fnctiongroup', 'setup_fnctiongroup.id', 'rating_matrix_setup.function_group')
            ->where('rating_matrix_setup.status', 0)

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
            ->where('tbl_overtime_cert.status', 'Active')
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        $data['main'] = db::table($this->prfrmnce_db . '.rating_matrix_setup')->where('id', $id)->get();
        $data['q_rating'] = db::table($this->prfrmnce_db . '.setup_ratings_quality')->where('rating_id', $id)->get();
        $data['e_rating'] = db::table($this->prfrmnce_db . '.setup_ratings_efficiency')->where('rating_id', $id)->get();
        $data['t_rating'] = db::table($this->prfrmnce_db . '.setup_ratings_timeliness')->where('rating_id', $id)->get();

        return response()->json(new JsonResponse($data));
    }

    public function showRating($id)
    {
        // $data = db::select("call " . $this->prfrmnce_db . ".opcr_rating(?)", [$id]);
        $data['main'] = db::table($this->prfrmnce_db . '.rating_matrix_setup')->where('id', $id)->get();
        $data['q_rating'] = db::table($this->prfrmnce_db . '.setup_ratings_quality')->where('rating_id', $id)->get();
        $data['e_rating'] = db::table($this->prfrmnce_db . '.setup_ratings_efficiency')->where('rating_id', $id)->get();
        $data['t_rating'] = db::table($this->prfrmnce_db . '.setup_ratings_timeliness')->where('rating_id', $id)->get();
        return response()->json(new JsonResponse($data));
    }

    public function cancel($id)
    {
        db::table($this->prfrmnce_db . '.rating_matrix_setup')
            ->where('id', $id)
            ->update(['status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function printData($id)
    {
       $core= db::table($this->prfrmnce_db . '.rating_matrix_setup')
            ->where('id', $id)
            ->update(['status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function print(Request $request)
    {
        try {
            $lists = $request->lists;
            $HRDRatings = "";
            $QdData = "";
            $function_group="";
            $function_type="";

            foreach ($lists as $key => $value) {
                if ($function_type !==  $value['function_type']) {
                $HRDRatings.='<tr>
                <td width-"45%" align="center" style="font-size:10pt"><b>'.strtoupper($value['function_type']).'</b></td>
                <td width="15%" align="center"></td>
                <td width="15%" align="center"></td>
                <td width="15%" align="center"></td>
                <td width="10%" align="center"></td>
            </tr>';
                }
                if ($function_group !==  $value['function_group']) {
                $HRDRatings.=' <tr>
                <td width="45%"><b>'.$value['function_group'].'</b></td>
                <td width="15%" align="center"></td>
                <td width="15%" align="center"></td>
                <td width="15%" align="center"></td>
                <td width="10%" align="center"></td>
            </tr>';
            }

                $HRDRatings.='<tr>
                <td width="15%"></td>
                <td width="30%" align="center">'.$value['MFO_dscrptn'].'</td>
                <td width="15%" align="center">'.$value['success_indicators'].'</td>
                <td width="15%" align="center">
                    <table>';
                foreach ($value['quality'] as $key => $valueq) {
                    $HRDRatings.='<tr>
                    <td height="20px" style="border-bottom:1px solid black"> '.$valueq['qty'] .' - '. $valueq['description'].' </td>

                    </tr>';

                }

                $HRDRatings.='
                    </table>
                </td>

                <td width="15%" align="center">
                    <table>';
                    foreach ($value['efficiency'] as $key => $valueq) {
                        $HRDRatings.='<tr>
                        <td height="20px" style="border-bottom:1px solid black"> '.$valueq['qty'] .' - '. $valueq['description'].' </td>

                        </tr>';

                    }

                    $HRDRatings.='
                    </table>
                    </td>
                <td width="10%" align="center">
                    <table>';
                    foreach ($value['timeliness'] as $key => $valueq) {
                        $HRDRatings.='<tr>
                        <td height="20px" style="border-bottom:1px solid black"> '.$valueq['qty'] .' - '. $valueq['description'].' </td>

                        </tr>';

                    }

                    $HRDRatings.='

                    </table>
                </td>
            </tr>';

            $function_group = $value['function_group'];
            $function_type = $value['function_type'];
            }




            $Template = '<table cellpadding="2">
                <tr>
                    <td width="100%" style="font-size:11pt" align="center"><b>HRD RATING MATRIX</b></td>
                </tr>
                <tr>
                    <td width="49%" align="right"><b>Effective</b></td>
                    <td width="51%">January 2020</td>
                </tr>
            </table>
            <table border="1" cellpadding="2">
                <tr>
                    <td width="45%" align="center">Major Final Output</td>
                    <td width="15%" align="center">Success Indicator</td>
                    <td width="15%" align="center">Description of Ratings for Quality</td>
                    <td width="15%" align="center">Description of Ratings for Efficiency</td>
                    <td width="10%" align="center">Description of Ratings for Timeliness</td>
                </tr>

               '.$HRDRatings.'
            </table>';



            PDF::SetTitle('Rating Matrix');
            PDF::SetFont('helvetica', '', 10);
            PDF::AddPage('L', array(215.9,330.2));
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
