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

class denguemonitoringcontroller extends Controller
{
    private $lgu_db;
   
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        
    }
 
    public function showbrgy(Request $request)
    {
        try {
            $list = db::select('select DISTINCT(brgy_name),brgy_name as id FROM '.$this->lgu_db. '.tbl_dengue_monitoring');
            return response()->json(new jsonresponse($list));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }

    public function showyear(Request $request)
    {
        try {
            $list = db::select('select DISTINCT(YEAR) FROM '.$this->lgu_db. '.tbl_dengue_monitoring');
            return response()->json(new jsonresponse($list));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }

   public function showmonitoring(Request $request)
      {
         $year= $request->year;
         $barangay= $request->barangay;
        $list = DB::select('call ' . $this->lgu_db . '.tbl_dengue_monitoring_graph(?,?)',[$year,$barangay]);
        return response()->json(new JsonResponse($list));

      }


}