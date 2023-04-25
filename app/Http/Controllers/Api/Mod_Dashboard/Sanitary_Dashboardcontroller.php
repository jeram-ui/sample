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

class Sanitary_Dashboardcontroller extends Controller
{
    private $lgu_db;
   
   
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        
    }
  public function sanitary_businessmonitoring()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_sanitary_business_category_monitoring();');
      return response()->json(new JsonResponse($list));
    }
  public function sanitary_business_monthlymonitoring()
    {
      $list = db::select('CALL ' . $this->lgu_db . '.p_sanitary_business_monthly_monitoring();');
      return response()->json(new JsonResponse($list));
    }


}