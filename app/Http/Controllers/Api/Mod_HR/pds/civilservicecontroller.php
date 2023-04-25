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

class civilservicecontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
   
   
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function civilserviceeligibility(Request $request)
    {
      $list = DB::table($this->hr_db . '.employees_civilserviceeligibility')
   // ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
        ->where('emp_number',Auth::user()->Employee_id)
        ->where('civil_statx', 'ACTIVE')
        ->get();
      return response()->json(new JsonResponse($list));
    }
    public function storeCivil(Request $request)
    {
      $form = $request->form;
      $id = $form['id'];
      $form['emp_number']=Auth::user()->Employee_id;
      if ( $id >0 ) {
        DB::table($this->hr_db . '.employees_civilserviceeligibility')
        ->where("id",$id)
        ->update($form);
      }else{
        $form['emp_number']=Auth::user()->Employee_id;
        DB::table($this->hr_db . '.employees_civilserviceeligibility')
        ->insert($form);
      }
      return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function civilCancel($id)
    {
        db::table($this->hr_db . '.employees_civilserviceeligibility')
            ->where('id', $id)
            ->update(['civil_statx' => 1]);
      return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
}