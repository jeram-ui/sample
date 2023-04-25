<?php

namespace App\Http\Controllers\Api\Mod_legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
class judgeController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }
    public function show()
    {
        $list = db::table($this->lgu_db.'.law_judge')
        ->select('*',db::raw("trim(concat (`fname`,' ',`lname`,' ',`mname`)) as name"))
        ->where('status','ACTIVE')->get();
        return response()->json(new JsonResponse($list));
    }
  
    public function store(Request $request) 
    {
        try {
            log::debug($request);
            $main = $request->form;
            $idx = $main['judge_id'];
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_judge')->insert($main);
            } else {
              db::table($this->lgu_db .'.law_judge')->where('judge_id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  
    public function edit($id) 
    {   
        $data['main'] = DB::table($this->lgu_db.'.law_judge')->where('judge_id',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {   
        DB::table($this->lgu_db.'.law_judge')->where('judge_id',$id)->update(['status'=>'CANCELLED']);
      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
}                