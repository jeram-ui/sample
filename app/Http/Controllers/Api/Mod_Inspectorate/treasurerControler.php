<?php

namespace App\Http\Controllers\Api\Mod_Inspectorate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;
use Storage;
use File;
class treasurerControler extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
    private $Proc;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->Proc = $this->G->getProcDb();
        $this->sched_db = $this->G->getSchedulerDb();
        $this->ins_db = $this->G->getInsDb();
    }
    public function show(Request $request){
        $list = db::table($this->ins_db.'.treasurer_inspection')
        ->where('stat',0)
        ->whereBetween(db::raw('date(date_time)'),[$request->from,$request->to])
        ->orderBy('date_time', 'desc')
        ->get();
        return response()->json(new JsonResponse($list));
    }
    public function cancel($id){
        db::table($this->ins_db.'.treasurer_inspection')->where('id',$id)->update(['stat'=>1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Deleted.', 'status' => 'success']));

    }
    public function ref(Request $request)
    {
        $pre = 'TIR';
        $table = $this->ins_db . ".treasurer_inspection";
        $date = $request->date;
        $refDate = 'date_time';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function refDirect($date)
    {
        $pre = 'TIR';
        $table = $this->ins_db . ".treasurer_inspection";
        $date = $date;
        $refDate = 'date_time';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);

        foreach ($data as $key => $value) {
            return$value->NOS;
        }

    }
    public function update($id){
      
        $main['form']=   db::table($this->ins_db.'.treasurer_inspection')->where('id',$id)->get();
        return response()->json(new JsonResponse($main));
    }
    public function store(Request $request){
     try {
        db::beginTransaction();
        $form = $request->form;
        $id = $form['id'];
       if ($id == 0) {
           $form['ref_no'] = $this->refDirect($form['date_time']);
          db::table($this->ins_db.'.treasurer_inspection')->insert($form);
          $id = $this->G->pk();
       }else{
          db::table($this->ins_db.'.treasurer_inspection')->where('id',$id)->update($form);
       }
       
       db::commit();
       return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
     } catch (\Throwable $err) {
         //throw $th;
         db::rollback();
         return response()->json(new JsonResponse(['Message' => 'Error Saving Data!', 'errormsg' => $err, 'status' => 'error']));
     }
    }
    public function upload(Request $request){
        $files = $request->file('file');
        if(!empty($files)){
          $path = hash( 'sha256', time());
          for($i = 0; $i < count($files); $i++){
          $file = $files[$i];
          $filename = $file->getClientOriginalName();
    
          if(Storage::disk('inspectorate')->put($path.'/'.$filename,  File::get($file))) {
              $data = array(
                'doc_id'=>$request->id,
                'file_name'=>$filename,
                'file_path'=>$path,
                'file_size'=>$file->getSize(),
                'entry_type'=>'treasurer'
              );
              db::table($this->ins_db.'.documents')->insert($data);           
              }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
       }
       public function uploadRemove($id){
        DB::table($this->ins_db.'.documents')->where('id',$id)->update(['stat'=>'1']);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
       }
  
    
       public function uploaded($id){
        $main=DB::table($this->ins_db.'.documents')->where('doc_id',$id)
        ->where('entry_type','treasurer')
        ->where('stat','0')->get();
        return response()->json(new JsonResponse($main));
       }
       public function documentView($id){
           $main=DB::table($this->ins_db.'.documents')->where('id',$id)->get();
           foreach ($main as $key => $value ) {
            $file = $value->file_name;
            $path = '../storage/files/inspectorate/'.$value->file_path.'/'.$file;
            if (\File::exists($path)) {
            $file = \File::get($path);
            $type = \File::mimeType($path);
            $response = \Response::make($file, 200);
            $response->header("Content-Type", $type);
            return $response;
            }
           }
       }
}
