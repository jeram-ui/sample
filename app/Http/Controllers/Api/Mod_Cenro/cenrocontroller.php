<?php

namespace App\Http\Controllers\Api\Mod_Cenro;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Storage;
use File;
use PDF;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class cenrocontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    private $general;
    protected $G;


    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->signatory = $this->G->signatoryReport();
        $this->LGUName = $this->G->LGUName();
        $this->mayors_db = $this->G->getMayorsDb();
        $this->cenro_db = $this->G->getcenroDb();
    }
    public function getInstrument(Request $request){
        $list = db::table($this->cenro_db .'.instrument_name')->get();
        return response()->json(new JsonResponse($list));
    }

    public function store(Request $request){
        $main =$request->form;
        $reading =$request->reading;
        
        $id = $main['id'];
            foreach ($reading as $row) {
                $data = array(
                    'instrument_id'=>$row['id'],
                    'trans_date'=>$main['trans_date'],
                    'amount'=>$row['reading'],
                );
                if ($row['idx']>0) {
                    db::table($this->cenro_db .'.instrument_monitoring')->where('id',$row['idx'])->update($data); 
                }else{
                    db::table($this->cenro_db .'.instrument_monitoring')->insert($data); 
                }
                               
              }
              return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        // }
    }  
    public function getInstrument_id(Request $request){
        try {
         
            $data =db::select('call '.$this->cenro_db.'.get_instrument_reading(?,?)',[$request->id,$request->date]);
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getInstrument_id_name($id){
        try {
         
            $data =db::select($this->cenro_db.'.instrument')
            ->where('instrument_name_id',$id)->get();
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function show(Request $request){
        $list = db::select('call '.$this->cenro_db.'.show_instrument_reading(?,?)',[$request->from,$request->to]);
        return response()->json(new JsonResponse($list));
     }
     public function showMonitoring(Request $request){

        $list = db::select('call '.$this->cenro_db.'.instrument_monitoring_list_graph(?,?,?)',[$request->type,$request->from,$request->to]);
        return response()->json(new JsonResponse($list));
     }
    }
    


