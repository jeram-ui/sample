<?php

namespace App\Http\Controllers\Api\mod_Bac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Storage;
use File;
use Exception;

class motioncontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
    private $Proc;
    private $budget;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->Proc = $this->G->getProcDb();
        $this->Bac = $this->G->getBACDb();
        $this->sched_db = $this->G->getSchedulerDb();
        $this->budget = $this->G->getBudgetDb();
    }

    
    // public function store_motion(Request $request)
    // {
    //     try {
    //         $main = $request->datax;
    //         $idx = $main['id'];


    //         if ($idx == 0) {
            
    //             // log::debug($main);
    //             db::table($this->Bac . '.motion_recon')->insert($main);
    //         } else {
    //             db::table($this->Bac . '.motion_recon')->where('id', $idx)->update($main);
    //         }
            
    //         return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    //     } catch (Exception $err) {
    //         DB::rollback();
    //         return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    //     }
    // }
    public function store_motion(Request $request)
  {
    try {

      $main = $request->datax;
      $idx = $main['id'];

      if ($idx == 0) {
        
        db::table($this->Bac . '.motion_recon')->insert($main);    
      } else {
        db::table($this->Bac . '.motion_recon')->where('id', $idx)->update($main);
      }
      return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
    } catch (\Exception $err) {
      return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    }
  }
  public function show_motion(Request $request)
  {
      try {
          
          $data = db::select('call '.$this->Bac.'.pat_motion_list');
          return response()->json(new jsonresponse($data));
      } catch (\Exception $e) {

          return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
      }
  }









//   public function show(Request $request)
//     {
//         $filter = $request->filter;
//         $list = db::table($this->Bac . '.motion_recon')->where('stat', 0);
//         if ($filter === 'This Day') {
//             $list->where('pre_proc', '=', date("Y-m-d"))
//                 ->orWhere('posting', date("Y-m-d"))
//                 ->orWhere('pre_bid', date("Y-m-d"))
//                 ->orWhere('bid_opening', date("Y-m-d"))
//                 ->orWhere('post_qua', date("Y-m-d"))
//                 ->orWhere('noa', date("Y-m-d"))
//                 ->orWhere('contract_date', date("Y-m-d"))
//                 ->orWhere('ntp_issuance', date("Y-m-d"))
//                 ->orWhere('ntp_effective', date("Y-m-d"));
//         };
//         if ($filter === 'This Week') {
//             $list->whereRaw('week(pre_proc) = week(now())')
//                 ->orwhereRaw('week(posting) = week(now())')
//                 ->orwhereRaw('week(pre_bid) = week(now())')
//                 ->orwhereRaw('week(bid_opening) = week(now())')
//                 ->orwhereRaw('week(post_qua) = week(now())')
//                 ->orwhereRaw('week(noa) = week(now())')
//                 ->orwhereRaw('week(contract_date) = week(now())')
//                 ->orwhereRaw('week(ntp_issuance) = week(now())')
//                 ->orwhereRaw('week(ntp_effective) = week(now())');
//         };
//         if ($filter === 'Done') {
//             $list->where('steps', '>', 11);
//         };
//         if ($filter === 'On Going') {
//             $list->where('steps', '<', 12);
//         };
//         $result = $list
//             ->select('*', db::raw("(CASE WHEN proc_type = 'Infrastructure' AND ABC > 5000000 THEN TRUE WHEN proc_type = 'Goods' AND ABC > 2000000 THEN TRUE WHEN proc_type = 'Consultancy' AND ABC > 1000000 THEN TRUE ELSE FALSE END) AS showss"))
//             ->orderBy(db::Raw('ifnull(itb_no,"")'), "asc")
//             ->get();
//         return response()->json(new JsonResponse($result));
//     }
    // public function show(Request $request)
    // {
    //     $filter = $request->filter;
    //     $list = db::table($this->Bac . '.bacc_proj')->where('stat', 0);
    //     if ($filter === 'This Day') {
    //         $list->where('pre_proc', '=', date("Y-m-d"))
    //             ->orWhere('posting', date("Y-m-d"))
    //             ->orWhere('pre_bid', date("Y-m-d"))
    //             ->orWhere('bid_opening', date("Y-m-d"))
    //             ->orWhere('post_qua', date("Y-m-d"))
    //             ->orWhere('noa', date("Y-m-d"))
    //             ->orWhere('contract_date', date("Y-m-d"))
    //             ->orWhere('ntp_issuance', date("Y-m-d"))
    //             ->orWhere('ntp_effective', date("Y-m-d"));
    //     };
    //     if ($filter === 'This Week') {
    //         $list->whereRaw('week(pre_proc) = week(now())')
    //             ->orwhereRaw('week(posting) = week(now())')
    //             ->orwhereRaw('week(pre_bid) = week(now())')
    //             ->orwhereRaw('week(bid_opening) = week(now())')
    //             ->orwhereRaw('week(post_qua) = week(now())')
    //             ->orwhereRaw('week(noa) = week(now())')
    //             ->orwhereRaw('week(contract_date) = week(now())')
    //             ->orwhereRaw('week(ntp_issuance) = week(now())')
    //             ->orwhereRaw('week(ntp_effective) = week(now())');
    //     };
    //     if ($filter === 'Done') {
    //         $list->where('steps', '>', 11);
    //     };
    //     if ($filter === 'On Going') {
    //         $list->where('steps', '<', 12);
    //     };
    //     $result = $list
    //         ->select('*', db::raw("(CASE WHEN proc_type = 'Infrastructure' AND ABC > 5000000 THEN TRUE WHEN proc_type = 'Goods' AND ABC > 2000000 THEN TRUE WHEN proc_type = 'Consultancy' AND ABC > 1000000 THEN TRUE ELSE FALSE END) AS showss"))
    //         ->orderBy(db::Raw('ifnull(itb_no,"")'), "asc")
    //         ->get();
    //     return response()->json(new JsonResponse($result));
    // }
    // public function ifdirectITB($id)
    // {
    //     $list = db::table($this->Bac . '.motion_recon')
    //         ->where('id', $id)
    //         ->select(db::raw("(CASE WHEN proc_type = 'Infrastructure' AND contract_cost > 5000000 THEN TRUE WHEN proc_type = 'Goods' AND contract_cost > 2000000 THEN TRUE WHEN proc_type = 'Consultancy' AND contract_cost > 1000000 THEN TRUE ELSE FALSE END) AS showss"))
    //         ->get();
    //     foreach ($list as $key => $value) {
    //         if ($value->showss === '0') {
    //             log::debug($value->showss);
    //             db::table($this->Bac . '.motion_recon')
    //                 ->where('id', '=', $id)
    //                 ->update(['steps' => 2]);
    //         }
    //     }
    // }

    
    // public function store(Request $request) 
    // {
    //     try {
    //         $main = $request->form;
    //         $idx = $main['id'];
    //         if ($idx == 0) {
    //            db::table($this->bac_db .'.motion_recon')->insert($main);
    //         } else {
    //           db::table($this->bac_db .'.motion_recon')->where('id', $idx)->update($main);
    //         }
    //         return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    //     } catch (\Exception $err) {
    //         return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    //     }
    // }  
        
    // public function  uploaded($id)
    // {
    //     $data = db::table('docs_upload')
    //         ->where('trans_id', $id)
    //         ->where('trans_type', 'Procurement Monitoring')
    //         ->where('stat', "ACTIVE")
    //         ->get();
    //     return response()->json(new JsonResponse($data));
    // }

    // public function documentView($id)
    // {
    //     $main = DB::table('docs_upload')->where('id', $id)->get();
    //     foreach ($main as $key => $value) {
    //         $file = $value->file_name;
    //         $path = '../storage/files/document/' . $value->file_path . '/' . $file;
    //         if (\File::exists($path)) {
    //             $file = \File::get($path);
    //             $type = \File::mimeType($path);
    //             $response = \Response::make($file, 200);
    //             $response->header("Content-Type", $type);
    //             return $response;
    //         }
    //     }
    // }

    // public function uploadRemove($id)
    // {
    //     $data = db::table('docs_upload')->where('id', $id)
    //         ->update(['stat' => "CANCELLED"]);
    //     return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    // }
} 

