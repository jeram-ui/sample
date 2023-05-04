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

class familybackgroundcontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
   
   
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->pds_dum = $this->G->getPDSDummyDB();
    }

    public function familybackground(Request $request)
    {
        $chk = DB::table($this->hr_db . '.employees_familybackground')
        ->where('emp_number',Auth::user()->Employee_id)
        ->count();
        if( $chk > 0 ){
            $list = DB::table($this->pds_dum . '.employees_familybackground')
            // ->join($this->pds_dum .'.employees_familybackground','employees_familybackground.emp_number','employees.SysPK_Empl')
              ->where('emp_number',Auth::user()->Employee_id)
              // ->where('Status_Empl', 'Active')
              ->orderby('id', 'DESC')
              ->get();
            return response()->json(new JsonResponse($list));
        }else{
            $list = DB::table($this->hr_db . '.employees_familybackground')
            // ->join($this->pds_dum .'.employees_familybackground','employees_familybackground.emp_number','employees.SysPK_Empl')
              ->where('emp_number',Auth::user()->Employee_id)
              // ->where('Status_Empl', 'Active')
              ->get();
            return response()->json(new JsonResponse($list));
        }

}
    // public function familybackground(Request $request)
    // {
    //   $list = DB::table($this->hr_db . '.employees_familybackground')
    //     ->where('emp_number',Auth::user()->Employee_id)
    //     ->get();
    //   return response()->json(new JsonResponse($list));
    // }

    public function storeFamily(Request $request)
    {
        $form = $request->form;

                $form = array(
                    'emp_number' => $form['emp_number'],
                    'spouse_surname' =>$form['spouse_surname'],
                    'spouse_firstname' => $form['spouse_firstname'],
                    'spouse_middlename' => $form['spouse_middlename'],
                    'spouse_ext' => $form['spouse_ext'],
                    'spouse_occupation' => $form['spouse_occupation'],
                    'spouse_employer' => $form['spouse_employer'],
                    'spouse_employeradd' => $form['spouse_employeradd'],
                    'spouse_Telno' => $form['spouse_Telno'],
                    'father_surname' => $form['father_surname'],
                    'father_firstname' => $form['father_firstname'],
                    'father_middlename' => $form['father_middlename'],
                    'father_ext' => $form['father_ext'],
                    'mother_surname' => $form['mother_surname'],
                    'mother_firstname' => $form['mother_firstname'],
                    'mother_middlename' => $form['mother_middlename'],
            
        
                );
                db::table($this->pds_dum . ".employees_familybackground")->insert($form);

    }

  
}