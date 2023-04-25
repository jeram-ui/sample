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

class lawController extends Controller
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
        $list = db::table($this->lgu_db.'.law_law_setup')
        ->select('law_case_type.case_type as casetype','law_law_setup.*')
        ->join($this->lgu_db.'.law_case_type','law_law_setup.case_type',"=",'law_case_type.id')
        ->where('law_law_setup.status','ACTIVE')->get();
        return response()->json(new JsonResponse($list));
    }
    public function getType(){
        $list = db::table($this->lgu_db.'.law_case_type')
        ->where('law_case_type.status','ACTIVE')->get();
        return response()->json(new JsonResponse($list));
    }
    public function storeCaseType(Request $request) 
    {
        try {
            $main = $request->form;
            $idx = $main['ID'];
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_case_type')->insert($main);
            } else {
              db::table($this->lgu_db .'.law_case_type')->where('ID', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  

    public function store(Request $request) 
    {
        try {
            $main = $request->form;
            $idx = $main['ID'];
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_law_setup')->insert($main);
            } else {
              db::table($this->lgu_db .'.law_law_setup')->where('ID', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  

    public function edit($id) 
    {   
        $data['main'] = DB::table($this->lgu_db.'.law_law_setup')->where('ID',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {   
        DB::table($this->lgu_db.'.law_law_setup')->where('ID',$id)->update(['status'=>'CANCELLED']);
      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

   
}                