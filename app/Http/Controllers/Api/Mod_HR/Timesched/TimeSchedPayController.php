<?php

namespace App\Http\Controllers\Api\Mod_HR\Timesched;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class TimeSchedPayController extends Controller
{
    private $lgu_db;
    private $hr_db;


  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function showShifts(Request $request)
    {
      $list = DB::table($this->hr_db . '.employees_timeshift')
        // ->where('shiftcode',Auth::user()->Employee_id)
        ->select("*", 'shiftcode','description')
        ->where('employees_timeshift.status', 0)
        ->orderBy("shiftcode","desc")
        ->get();
      return response()->json(new JsonResponse($list));
    }
    public function getpayrollMakerList()
    {

      $list = db::select("call ".$this->hr_db.".jay_employee_list_pay_maker(?)",[Auth::user()->Employee_id]);
      return response()->json(new JsonResponse($list));

    }
    public function Timeschedule($id)
    {
      $list = DB::table($this->hr_db . '.tbl_timeshift_setup_main')
        ->leftjoin($this->hr_db .'.employees_timeshift','employees_timeshift.shiftcode','tbl_timeshift_setup_main.timeshift')
        ->where('emp_id', $id)
        ->where('tbl_timeshift_setup_main.status', 'Active')
        ->get();
      return response()->json(new JsonResponse($list));
    }

    public function storeTime(Request $request)
    {
      $form = $request->form;
      $formx = $request->formx;
      $id = $form['id'];
    //   $form['emp_id']=Auth::user()->Employee_id;
      $form['emp_id'] = $formx['empID'];

      $setupfor ="";
      $changeshift=$form['change_shift'];
      $shift="None";
      if (!$form['description']) {
        $shift ="None";
      }
      if ($form['setup_for']==='Day Off') {
        $setupfor ="DO";


      }else if($form['setup_for']==='Working Days') {
        $setupfor ="WD";

      }else {
        $setupfor ="INV";

      }



      $schedx = "";
      if ($form['setup_for'] === 'Invalid Logs') {
        $schedFrom=date_create($form['typeDatefrom']);
        $schedx = date_format($schedFrom,'m/d/Y');
      }else{
        // $schedFrom=date_create($form['typeDatefrom']);
        // $schedTo=date_create($form['typeDateto']);
        // $schedx = date_format($schedFrom,'m/d/Y').'-'.date_format($schedTo,'m/d/Y');


      if ($form['type'] === 'Day of the Week') {
        $schedx = implode(",", $form['sched']);
        log::debug($schedx = implode(",", $form['sched']));
      }else{
         $schedFrom=date_create($form['typeDatefrom']);
        $schedTo=date_create($form['typeDateto']);
        $schedx = date_format($schedFrom,'m/d/Y').'-'.date_format($schedTo,'m/d/Y');
      }

      }



      $periodx = "";
      if ($form['period_type'] === 'Specific Date') {
        $typeFrom=date_create($form['periodDatefrom']);
        $typeTo=date_create($form['periodDateto']);
        $periodx = date_format($typeFrom,'m/d/Y').'-'.date_format($typeTo,'m/d/Y');
      }else{
        $periodx ="None";

      }

      if ( $id >0 ) {
        $data = array(
          'emp_id' =>$formx['empID'],
        //   'emp_id' =>Auth::user()->Employee_id,
          'setup_for'=>$setupfor,
          'type'=>$form['type'],
          'sched'=> $schedx,
          'timeshift'=> $shift,
          'period_type'=>$form['period_type'],
          'period_shed'=>  $periodx ,
          'change_shift'=> $changeshift

        );

        DB::table($this->hr_db . '.tbl_timeshift_setup_main')
        ->where("id",$id)
        ->update($data);
      }else{
          $data = array(
            'emp_id' =>$formx['empID'],

            // 'emp_id' =>Auth::user()->Employee_id,
            'setup_for'=>$setupfor,
            'type'=>$form['type'],
            'sched'=> $schedx,
            'timeshift'=> $shift,
            'period_type'=>$form['period_type'],
            'period_shed'=>  $periodx ,
            'change_shift'=> $changeshift

          );

        DB::table($this->hr_db . '.tbl_timeshift_setup_main')
        ->insert($data);
      }
      return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function timeCancel($id)
    {
        db::table($this->hr_db . '.tbl_timeshift_setup_main')
            ->where('id', $id)
            ->update(['status' => 1]);
      return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function timeEdit($id)
    {

        $data['main']= db::table($this->hr_db .'.tbl_timeshift_setup_main')
        ->select('*', db::raw("(case when `setup_for` = 'DO' then 'Day Off'
        when `setup_for` = 'WD' THEN 'Working Days' when `setup_for` = 'IL' THEN 'Invalid Logs'
       else setup_for end
       ) as 'setup_for' "))
        ->where('id' , $id)->get();


        foreach ($data['main'] as $key => $value) {
          log::debug($value->id);
          $sched = array(
            'sched'=>explode(",",$value->sched)

          );
          $time = array(
            'timeshift'=>explode("-",$value->sched)
          );

          $period = array(
            'period'=>explode("-",$value->period_shed)
          );
          $invalid = array(
            'invalid'=>explode("-",$value->sched)
          );
        }
        $data['sched']= $sched;
        $data['shift']= $time;
        $data['period']= $period;
        $data['invalid']= $invalid;

        return response()->json(new JsonResponse($data));
    }


}
