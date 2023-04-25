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
class RatingController extends Controller
{
    private $lgu_db;
    private $hr_db;
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function showRating(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $uid =$request->uid;

        $list = DB::select('call ' .$this->hr_db . '.getRatings(?,?,?)',[$from, $to, $uid]);

        return response()->json(new JsonResponse($list));

        // $list = DB::table($this->hr_db .'.dash_rating')
        // ->join($this->hr_db.'.department','department.SysPK_Dept','dash_rating.department')
        // ->select("*",'dash_rating.id' )
        //     ->where('dash_rating.status', 0)
        //     ->get();

        //   return response()->json(new JsonResponse($list));
        }
        public function GetRatings(Request $request)
        {

            $from = $request->from;
            $to = $request->to;
            $uid =$request->uid;
            $list = DB::select('call ' .$this->hr_db . '.getRatingsList(?,?,?)',[$from, $to, $uid]);

            return response()->json(new JsonResponse($list));

            // $list = DB::table($this->hr_db .'.dash_rating')
            // ->join($this->hr_db.'.department','department.SysPK_Dept','dash_rating.department')
            // ->select("*",'dash_rating.id' )
            // // ->where('id',Auth::user()->Employee_id)
            //     ->where('dash_rating.status', 0)
            //     ->get();

            //   return response()->json(new JsonResponse($list));
            }



    public function edit($project_id)
    {
      $list = DB::select('call ' .$this->lgu_db . '.spl_display_all_internal_project_jho1_PAT(?)',[$project_id])
      ->where('$project_id', project_id)
      ->get();
      return response()->json(new JsonResponse($data));
    }

    public function getAssistedName(Request $request)
    {

        $from = $request->from;
        $to = $request->to;
        $list = db::table($this->hr_db.'.dash_rating')
        ->join($this->hr_db .'.employee_information','employee_information.PPID','dash_rating.uid')
        ->whereBetween('Rate_time',[ $from, $to])
        ->select(db::raw('DISTINCT(`uid`) AS "uid",`NAME` AS "name"'))
        ->get()
        ;
      return response()->json(new JsonResponse($list));
    }

    public function getDepartment()
    {
        $list = DB::table($this->hr_db.'.department')
        ->select("*", 'SysPK_Dept', 'Name_Dept')
        ->where('department.status', 'Active')
        ->get();

        return response()->json(new JsonResponse($list));
    }

    public function store(Request $request)
    {

        $model1 =  $request->model1;
        $model2 =  $request->model2;
        $model3 =  $request->model3;
        $form = $request->form;
        $id = $form['id'];

             $form['uid'] = Auth::user()->Employee_id;
            db::table($this->hr_db .'.dash_rating')
            ->insert($form);
            $id = DB::getPdo()->LastInsertId();

            db::table($this->hr_db .".dash_assistance_survey")
            ->insert(['setup_id' => $model1,'main_id'=>$id]);
            log::debug($model1);

            db::table($this->hr_db .".dash_assistance_survey")
            ->insert(['setup_id' => $model2,'main_id'=>$id]);

            db::table($this->hr_db .".dash_assistance_survey")
            ->insert(['setup_id' => $model3,'main_id'=>$id]);

    //     $main_id = $request->main_id;
    //     $new=$request->new;
    //     $old=$request->old;
    //     $group_id = $request->group_id;
    //   $form = $request->form;
    //   $formx = $request->formx;
    //   $id = $form['id'];
    // //   $form['id']=Auth::user()->Employee_id;

    //   if ( $id >0 ) {
    //     DB::table($this->hr_db . '.dash_rating')
    //     ->where('id', $id)
    //     ->update($form);
    //   }else{
    //     // $form['id']=Auth::user()->Employee_id;
    //     DB::table($this->hr_db . '.dash_rating')
    //     ->insert($form);


    //   }
    //   return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));

}
    //   $form = $request->form;
    //   $id = $form['id'];
    //   $form['uid']=Auth::user()->Employee_id;
    //   if ( $id >0 ) {
    //     DB::table($this->hr_db . '.dash_rating')
    //     ->where('id', $id)
    //     ->update($form);
    //   }else{
    //     DB::table($this->hr_db . '.dash_rating')
    //     ->insert($form);
    //   }
    //   return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    // }



}
