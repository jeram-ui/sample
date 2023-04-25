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

class opponentController extends Controller
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
        $list = db::table($this->lgu_db.'.law_client')
        ->select('*',db::raw("TRIM(CASE WHEN `save_type` = 'OPPONENT' THEN CONCAT(IFNULL(`prefix`,''),' ',`fname`,' ',IFNULL(`mname`,''),' ',IFNULL(`lname`,''),' ',IFNULL(`suffix`,''))
        ELSE `company_name`
         END) as name"))
        ->where('status','ACTIVE')
        ->whereIn('save_type', ['OPPONENT', 'OpponentCompany'])->get();
        return response()->json(new JsonResponse($list));
    }
    public function showOpponent()
    {
        $list = db::table($this->lgu_db.'.law_client')
        ->select('*',db::raw("TRIM(CASE WHEN `save_type` = 'OPPONENT' THEN CONCAT(IFNULL(`prefix`,''),`fname`,' ',IFNULL(`mname`,''),' ',IFNULL(`lname`,''),' ',IFNULL(`suffix`,''))
        ELSE `company_name`
         END) as name"))
        ->where('status','ACTIVE')
        ->whereIn('save_type', ['OpponentCompany', 'OPPONENT'])->get();
        return response()->json(new JsonResponse($list));
    }
    
    public function store(Request $request) 
    {
        try {
            $main = $request->form;
            $idx = $main['patient_id'];
            $main['save_type'] = 'OPPONENT';
            log::debug( $idx);
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_client')->insert($main);
            } else {
                db::table($this->lgu_db .'.law_client')->where('patient_id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  
public function storeCompany(Request $request){
    try {
        $main = $request->form;
        $idx = $main['patient_id'];
        $main['save_type'] = 'OpponentCompany';
        log::debug( $idx);
        if ($idx == 0) {
           db::table($this->lgu_db .'.law_client')->insert($main);
        } else {
            db::table($this->lgu_db .'.law_client')->where('patient_id', $idx)->update($main);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    } catch (\Exception $err) {
        return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    }
}

    public function edit($id) 
    {   
        $data['main'] = DB::table($this->lgu_db.'.law_client')->where('patient_id',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {   
        DB::table($this->lgu_db.'.law_client')->where('patient_id',$id)->update(['status'=>'CANCELLED']);
      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

   
}                