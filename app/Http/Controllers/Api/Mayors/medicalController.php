<?php

namespace App\Http\Controllers\Api\Mayors;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Storage;
use File;
use PDF;
use Illuminate\Support\Facades\log;
class medicalController extends Controller
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
    }
    public function ref(Request $request)
    {   $pre = 'SEK';
        $table = $this->mayors_db. ".memorandum";
        $date = $request->date;
        $refDate = 'trans_date';
        $query = DB::select("SELECT CONCAT('" . $pre . "',DATE_FORMAT('" . $date . "', '%y'),'-',LPAD(COUNT(" . $refDate . ")+ 1,5,0)) AS 'NOS' FROM " . $table . " WHERE  YEAR(" . $refDate . ") =  YEAR('" . $date . "')");
        return response()->json(new JsonResponse(['data' => $query]));
    }
    public function store(Request $request)
    {
        log::debug($request);
        try {
            DB::beginTransaction();
            $form = $request->form;
            $details =  $request->details;
            $id =$form['id'];
            if ($id == 0) {
                $form['uid'] = Auth::user()->id;
                db::table($this->mayors_db.'.seek_medical')
                ->insert($form);
                $id = DB::getPDo()->lastInsertId();
                foreach ($details as $key => $value) {
                    $details = array(
                        'seek_id'=>$id,
                        'hh_id'=>$value['hhid']
                    );
                    db::table($this->mayors_db.'.seek_medical_details')
                    ->insert($details);
                }
            }else{
                $form['upid'] = Auth::user()->id;
                db::table($this->mayors_db.'.seek_medical')
                ->where('id', $id)
                ->update($form);
                db::table($this->mayors_db.'.seek_medical_details')
                ->where('seek_id',$id)
                ->delete();
                foreach ($details as $key => $value) {
                    $details = array(
                        'seek_id'=>$id,
                        'hh_id'=>$value['hhid']
                    );
                    db::table($this->mayors_db.'.seek_medical_details')
                    ->insert($details);
                }
            }
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function show(Request $request)
    {
        try {
            $from = $request->from;
            $to = $request->to;
           $data=  db::select('call '.$this->mayors_db.'.rans_seek_medical(?,?)',[$from,$to]);
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {
           
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function showsummary(Request $request)
    {
        try {
           $data=  db::select('call '.$this->mayors_db.'.rans_seek_medical_summary()');
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {
           
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    
    public function edit($id)
    {
        try {
          
           $data['main'] = db::table($this->mayors_db.'.seek_medical')
            ->where('id',$id)->get();
            $data['details'] = db::select('call '.$this->mayors_db.'.rans_seek_medical_details(?)',[$id]);
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function cancel($id)
    {
        try {
           $data = db::table($this->mayors_db.'.memorandum')
            ->where('id',$id)
            ->update(['stat'=>1])
            ;
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function upload(Request $request){
        $files = $request->file('file');
        if(!empty($files)){
          $path = hash( 'sha256', time());
          for($i = 0; $i < count($files); $i++){
          $file = $files[$i];
          $filename = $file->getClientOriginalName();
          if(Storage::disk('Memorandum')->put($path.'/'.$filename,  File::get($file))) {
              $data = array(
                'trans_id'=>$request->id,
                'file_name'=>$filename,
                'file_path'=>$path,
                'file_size'=>$file->getSize(),
                'uid'=>Auth::user()->id,
                'entry_type'=>'Memorandum'
              );
              db::table($this->mayors_db.'.documents_uploded')->insert($data);           
              }
            }
        }
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
       }
       public function  uploaded($id){
        $data = db::table($this->mayors_db.'.documents_uploded')
        ->where('trans_id', $id)
        ->where('entry_type','Memorandum')
        ->where('stat', "ACTIVE")
        ->get();
        return response()->json(new JsonResponse($data));
       }
       public function documentView($id){
        $main=DB::table($this->mayors_db.'.documents_uploded')->where('id',$id)->get();
        foreach ($main as $key => $value ) {
         $file = $value->file_name;
         $path = '../storage/files/Memorandum/'.$value->file_path.'/'.$file;
         if (\File::exists($path)) {
         $file = \File::get($path);
         $type = \File::mimeType($path);
         $response = \Response::make($file, 200);
         $response->header("Content-Type", $type);
         return $response;
         }
        }
    }
       public function uploadRemove($id){
        $data = db::table($this->mayors_db.'.documents_uploded')->where('id', $id)
        ->update(['stat'=>"CANCELLED"])
        ;
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
       }
}
