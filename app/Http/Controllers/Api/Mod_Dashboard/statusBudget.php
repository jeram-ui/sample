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
class statusBudget extends Controller
{
    private $lgu_db;
    private $hr_db;
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function show_statusBudget(Request $request)
    {
        // $year = (new DateTime)->format("Y");
        // $list = db::select("CALL StatusOfAppropriation_dashboard(1,6,'2022','1,7',0,0);",[$_instructor,$_sem,$_acadYear]);
        $list = db::select("CALL budget.StatusOfAppropriation_dashboard(1,6,'2022','1,7',0,0);");

        return response()->json(new JsonResponse($list));
    }
    public function edit($project_id)
    {
      $list = DB::select('call ' .$this->lgu_db . '.spl_display_all_internal_project_jho1_PAT(?)',[$project_id])
      ->where('$project_id', project_id)
      ->get();
      return response()->json(new JsonResponse($data));
    }




}
