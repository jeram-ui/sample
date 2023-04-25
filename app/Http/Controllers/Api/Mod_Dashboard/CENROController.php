<?php

namespace App\Http\Controllers\Api\Mod_Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class CENROController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
    private $Proc;
    private $Budget;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->Proc = $this->G->getProcDb();
        $this->cenro = $this->G->getCENRODb();
        
    }
     
public function getRTS(Request $request){
  $from =$request->from;
  $to =$request->to;
  $instrument_id = $request->instrument_id;
  $type =$request->type;
  $list = DB::select('call '.$this->cenro.'.instrument_monitoring_list(?,?,?,?)',[$type ,$from,$to,$instrument_id]);
  return response()->json(new JsonResponse($list));
}

public function proceeds(Request $request){
  $list = DB::select('call ' . $this->lgu_db . '.spl_jay_rpt_proceeds_new(?,?,?)',[date("Y-01-01"),date("Y-m-d"),$request->summary]);
  return response()->json(new JsonResponse($list));
}

}
