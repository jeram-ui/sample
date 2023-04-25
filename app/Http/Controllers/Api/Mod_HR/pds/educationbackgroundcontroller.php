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

class educationbackgroundcontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
   
   
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function educationbackground(Request $request)
    {
      $list = DB::table($this->hr_db . '.employees_eduback')
   // ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
        ->where('emp_number',Auth::user()->Employee_id)
        ->where('status', 0)
        ->get();
      return response()->json(new JsonResponse($list));
    }

    public function storeEduc(Request $request)
    {
      $form = $request->form;
      $id = $form['id_edu'];
      $form['emp_number']=Auth::user()->Employee_id;
      if ( $id >0 ) {
        DB::table($this->hr_db . '.employees_eduback')
        ->where("id_edu",$id)
        ->update($form);
      }else{
        $form['emp_number']=Auth::user()->Employee_id;
        DB::table($this->hr_db . '.employees_eduback')
        ->insert($form);
      }
      return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function EducCancel($id)
    {
        db::table($this->hr_db . '.employees_eduback')
            ->where('id_edu', $id)
            ->update(['status' => 1]);
      return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

  
}