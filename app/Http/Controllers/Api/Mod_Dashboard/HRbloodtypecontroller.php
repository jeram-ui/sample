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
use Illuminate\Support\Str;
use Storage;

class HRbloodtypecontroller extends Controller
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
    $this->Budget = $this->G->getBudgetDb();
  }
  
  public function hr_employee_bloodtype()
  {
    $list = db::select('CALL ' . $this->hr_db . '.p_blood_type();');
    return response()->json(new JsonResponse($list));
  }

  public function hr_employee_barangay()
  {
    $list = db::select('CALL ' . $this->hr_db . '.p_emp_barangay();');
    return response()->json(new JsonResponse($list));
  }
  public function hr_employee_yearsinservice()
  {
    $list = db::select('CALL ' . $this->hr_db . '.p_employee_years_in_service();');
    return response()->json(new JsonResponse($list));
  }
}
