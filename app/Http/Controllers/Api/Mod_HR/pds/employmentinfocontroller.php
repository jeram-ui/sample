<?php

namespace App\Http\Controllers\Api\Mod_HR\pds;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class employmentinfocontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
   
   
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function employmentinformation(Request $request)
    {
      $list = DB::table($this->hr_db . '.employees')
      ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
      ->join($this->hr_db .'.employees_timeshift','employees.shift_code','employees_timeshift.shiftcode') 
      ->where('SysPK_Empl',Auth::user()->Employee_id)
      ->get();
      return response()->json(new JsonResponse($list));
    }

  
}