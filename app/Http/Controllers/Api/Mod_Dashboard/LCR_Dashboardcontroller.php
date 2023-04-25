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

class LCR_Dashboardcontroller extends Controller
{
    private $lgu_db;
   
   
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        
    }
  public function livebirth_monitoring()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_livebirth_monitoring();');
      return response()->json(new JsonResponse($list));
    }

  public function livebirth_monitoring_perbarangay()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_livebirth_monitoring_barangay();');
      return response()->json(new JsonResponse($list));
    }

  public function death_monitoring()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_death_monitoring();');
      return response()->json(new JsonResponse($list));
    }

  public function death_monitoring_monthly()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_causeofdeath_monitoring();');
      return response()->json(new JsonResponse($list));
    }

  public function death_monitoring_perbarangay()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_death_monitoring_perbarangay();');
      return response()->json(new JsonResponse($list));
    }

  public function marriage_monitoring_monthly()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_marriage_monthly();');
      return response()->json(new JsonResponse($list));
    }
 
  public function showtypeofmarriage(Request $request)
    {
        try {
            $list = db::select('select DISTINCT (lcr_mctypemar) FROM '.$this->lgu_db. '.lcr_marriagecertificate');
            return response()->json(new jsonresponse($list));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
public function type_marriage_monitoring_monthly()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_type_marriage_monthly();');
      return response()->json(new JsonResponse($list));
    }
}