<?php

namespace App\Http\Controllers\Api\Mod_HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Validator;


class raffleNameController extends Controller
{
  private $lgu_db;
  private $hr_db;
  private $trk_db;
  private $empid;
  protected $G;

  public function __construct(GlobalController $global)
  {
    $this->middleware('auth');
    $this->G = $global;
    $this->lgu_db = $this->G->getLGUDb();
    $this->hr_db = $this->G->getHRDb();
    $this->trk_db = $this->G->getTrkDb();
  }
  public function getRaffleList(Request $request)
  {
      $list = DB::table($this->hr_db . '.raffle_name')
        ->leftjoin($this->hr_db .".raffle_winner",'raffle_winner.raffle_name_id','raffle_name.id')
        ->leftjoin($this->hr_db .".raffle_price",'raffle_price.id','raffle_winner.price_id')
        ->select("raffle_name.*","price_name as prizes")
        ->where('raffle_name.status', 0)
        // ->where('NAME', 'like',  '%'.$request->NUMBER . '%')
        // ->where('NAME', 'like',  '%'.$request->NUMBER . '%')
        ->get();
      return response()->json(new JsonResponse($list));
  }
  public function store(Request $request)
  {
      $form = $request->form;
      $id = $form['id'];
      if ($id > 0) {

          DB::table($this->hr_db . '.raffle_name')
              ->where('id', $id)
              ->update($form);

      } else {
          $chk = db::table($this->hr_db . '.raffle_name')
          ->where("NAME", $form['NAME'])
          // ->where("section", $form['section'])
          // ->where("class_year_id", $form['class_year_id'])
          ->where("raffle_name.status", 0)
          ->count();
          log::debug($chk);
          if ($chk > 0) {
              return response()->json(
                  new JsonResponse([
                      'Message' => 'Already Exist',
                      'status' => 'Error',
                      // 'errormsh' => $e,
                  ])
              );
          } else {
          
            DB::table($this->hr_db . '.raffle_name')->insert($form);
            $id = DB::getPdo()->LastInsertId();
          }
      }
      return  $this->G->success();
  }
  public function getDepartment()
  {
      $list = DB::table($this->hr_db . '.department')
          ->select("*", 'SysPK_Dept', 'Name_Dept')
          ->where('department.status', 'Active')
          ->get();

      return response()->json(new JsonResponse($list));
  }
  public function Edit($id)
  {
    $list['form'] = db::table($this->hr_db . '.raffle_name')
            ->where("raffle_name.id", $id)
            ->get();
    return response()->json(new JsonResponse($list));
  }
  public function cancel($id)
  {
        db::table($this->hr_db . '.raffle_name')
            ->where('id', $id)
            ->update(['raffle_name.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
  }
}
