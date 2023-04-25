<?php

namespace App\Http\Controllers\Api\Mod_Performance\MFOPAP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class MFOController extends Controller
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

    public function functiongroupstore(Request $request)
    {
        $formx = $request->formx;
        $id = $formx['id'];
        if ($id > 0) {
            db::table($this->prfrmnce_db . ".setup_fnctiongroup")
                ->where('id', $id)
                ->update($formx);

        } else {
            db::table($this->prfrmnce_db . ".setup_fnctiongroup")->insert($formx);
            $id = DB::getPdo()->LastInsertId();

        }
    }
    public function showByDept($id)
    {
        $list = db::select("call " . $this->prfrmnce_db . ".mfo_byDept(?)",[$id]);
        $final = array();
       foreach ($list as $key => $value) {
        $dum = array(
            'id'=>$value->id,
            'function_type'=>$value->function_type,
            'function_group'=>$value->function_group,
            'MFO_dscrptn'=>$value->MFO_dscrptn,
            'success_indicators'=>$value->success_indicators,
            'quality'=>db::table($this->prfrmnce_db .'.setup_ratings_quality')->select("qty",'description')->where("rating_id",$value->id)->get(),
            'efficiency'=>db::table($this->prfrmnce_db .'.setup_ratings_efficiency')->select('qty','description')->where("rating_id",$value->id)->get(),
            'timeliness'=>db::table($this->prfrmnce_db .'.setup_ratings_timeliness')->select('qty','description')->where("rating_id",$value->id)->get(),

        );
        array_push( $final,$dum );
       }
        return response()->json(new JsonResponse($final));
    }
    public function store(Request $request)
    {
        // $formz = $request->formz;
        $form = $request->form;
        $id = $form['id'];
        $idx = $form['fnctngroup_id'];
        if ($id > 0) {

                $datx = array(
                    'fnctngroup_id' => $idx,
                    'MFO_dscrptn'=>$form['MFO_dscrptn'],
                );
                db::table($this->prfrmnce_db .".setup_mfopap")
                ->where("id",$id)
                ->update($datx);
        } else {
            $datx = array(
                'fnctngroup_id' => $idx,
                'MFO_dscrptn'=>$form['MFO_dscrptn'],
            );
            db::table($this->prfrmnce_db .".setup_mfopap")->insert($datx);
        }
    }
    public function getDepartment()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function removing($id)
    {
        db::table($this->prfrmnce_db . ".setup_fnctiongroup")
            ->where('id' , $id)
            ->update(['status' => 1]);
        // $this->G->success();
    }

    public function getEvaluation($id,$type)
    {
        $list = DB::table($this->prfrmnce_db.'.setup_fnctiongroup')
        ->where('status', 0)
        ->where('department', $id)
        ->where('fnctn_type', $type)
        ->get();

        return response()->json(new JsonResponse($list));
    }

    public function getlist($id)
    {
        $list = DB::table($this->prfrmnce_db.'.setup_fnctiongroup')
        ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'setup_fnctiongroup.department')
        ->join($this->prfrmnce_db . '.setup_mfopap', 'setup_mfopap.fnctngroup_id', 'setup_fnctiongroup.id')
        ->select("*", 'department.Name_Dept', 'setup_mfopap.MFO_dscrptn', 'setup_mfopap.id' )
        ->where('setup_fnctiongroup.status', 0)
        ->where('setup_mfopap.status', 0)
        ->where('setup_fnctiongroup.department', $id)
        ->orderBy('setup_fnctiongroup.fnctn_type')
        ->orderBy('setup_fnctiongroup.id','ASC')
        ->orderBy('setup_mfopap.MFO_dscrptn','ASC')
        ->get();
        return response()->json(new JsonResponse($list));
    }
    public function cancel($id)
    {
        db::table($this->prfrmnce_db . '.setup_mfopap')
            ->where('id', $id)
            ->update(['setup_mfopap.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function Edit($id)
    {
        // $data['formA'] =db::table($this->prfrmnce_db .'.setup_fnctiongroup')->where('id', $id)->get();
        $data['formB'] =db::table($this->prfrmnce_db .'.setup_mfopap')
        ->leftJoin($this->prfrmnce_db . '.setup_fnctiongroup', 'setup_fnctiongroup.id', 'setup_mfopap.fnctngroup_id')
        ->select("*", 'setup_fnctiongroup.department', 'setup_fnctiongroup.fnctn_type','setup_fnctiongroup.description', 'setup_mfopap.id' )
        ->where('setup_mfopap.id', $id)
        ->get();

        return response()->json(new JsonResponse($data));
    }

 public function print(Request $request){
    try{

        $Template='';
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

}
