<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class DashboadController extends Controller
{
   
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }


    public function getNewRenewBusiness(Request $request)
    {
      try {         
        
        $from = $request->from;
        $to = $request->to;
        
        $data['newList'] = DB::select('call '.$this->lgu_db.'.jay_Generate_SQL_Abstractdashboard_new_renew(?,?,?)',array($from,$to,'NEW'));  
        $data['renewList'] = DB::select('call '.$this->lgu_db.'.jay_Generate_SQL_Abstractdashboard_new_renew(?,?,?)',array($from,$to,'RENEW'));  
             
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

  
}
