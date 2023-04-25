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
class projectmonitoringcontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function show_project(Request $request)
    { 
      $list = DB::select('call ' .$this->lgu_db . '.spl_display_all_internal_project_jho1_PAT1()');
      return response()->json(new JsonResponse($list));
    }
    public function show_proj(Request $request)
    { 
      $list = DB::select('call ' .$this->lgu_db . '.spl_display_all_internal_project_jho1_PAT1');
      return response()->json(new JsonResponse($list));
    }
    public function show_Attach($id)
    { 
      $list = DB::select('call ' .$this->lgu_db . '.spl_getWeeklyAttachment_jho_rans(?)',[$id]);
      return response()->json(new JsonResponse($list));
    }
    // public function show_proj(Request $request)
    // { 
    //   $list = DB::select('call ' .$this->lgu_db . '.spl_display_all_internal_project_jho1_PAT2');
    //   return response()->json(new JsonResponse($list));
    // }

    public function edit($project_id)
    {
      $list = DB::select('call ' .$this->lgu_db . '.spl_display_all_internal_project_jho1_PAT(?)',[$project_id])
      ->where('$project_id', project_id)
      ->get();
      return response()->json(new JsonResponse($data));
    }


    
  
}