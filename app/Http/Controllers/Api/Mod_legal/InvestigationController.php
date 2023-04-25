<?php

namespace App\Http\Controllers\Api\Mod_legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use Storage;
use File;
use PDF;
class InvestigationController extends Controller
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
        $this->sched_db = $this->G->getSchedulerDb();
    }
    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'INV';
        $table = $this->lgu_db . ".law_investigation";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function show(Request $request)
    {
        $list = db::table($this->lgu_db.'.law_investigation')
        ->join($this->sched_db.'.tbl_member_info','tbl_member_info.id','=','law_investigation.member_id')
        ->join($this->sched_db.'.tbl_organization_profile','tbl_organization_profile.id','=','law_investigation.org_id')
        ->select(db::raw('law_investigation.*,
        tbl_organization_profile.`organization_name`,
        `dbfederation`.get_fullname (tbl_member_info.`pkID`) "Person Name" '))
        ->where('law_investigation.status',0)
        ->whereBetween('law_investigation.trans_date',[$request->from,$request->to])
        ->orderBy('law_investigation.trans_date', 'desc')
        ->get();
        return response()->json(new JsonResponse($list));
    }
    public function showForCase(Request $request){
        $list = db::table($this->lgu_db.'.law_investigation')
        ->select('*',db::raw('CONCAT(`lname`,", ",`fname`," ",`mname`) AS name'))
        ->where('status',0)
        ->where('with_case','1')
        ->orderBy('trans_date', 'desc')
        ->get();
        return response()->json(new JsonResponse($list));
    }

    public function store(Request $request) 
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_investigation')->insert($main);
            } else {
                db::table($this->lgu_db .'.law_investigation')->where('id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  


    public function edit($id) 
    {   
        $data['main'] = DB::table($this->lgu_db.'.law_investigation')->where('id',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {   
        DB::table($this->lgu_db.'.law_investigation')->where('id',$id)->update(['status'=>'1']);
      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    
    public function upload(Request $request){
        $files = $request->file('file');
        if(!empty($files)){
          $path = hash( 'sha256', time());
          for($i = 0; $i < count($files); $i++){
          $file = $files[$i];
          $filename = $file->getClientOriginalName();
          if(Storage::disk('docs')->put($path.'/'.$filename,  File::get($file))) {
              $data = array(
                'consult_id'=>$request->id,
                'file_name'=>$filename,
                'file_path'=>$path,
                'file_size'=>$file->getSize(),
                'uid'=>Auth::user()->id,
              );
              db::table($this->lgu_db.'.law_investigation_docs')->insert($data);           
              }
            }
        }
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
       }
       public function  uploaded($id){
        $data = db::table($this->lgu_db.'.law_investigation_docs')
        ->where('consult_id', $id)
        ->where('stat', "ACTIVE")
        ->get();
        return response()->json(new JsonResponse($data));
       }
       public function documentView($id){
        $main=DB::table($this->lgu_db.'.law_investigation_docs')->where('id',$id)->get();
        foreach ($main as $key => $value ) {
         $file = $value->file_name;
         $path = '../storage/files/document/'.$value->file_path.'/'.$file;
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
        $data = db::table($this->lgu_db.'.law_investigation_docs')->where('id', $id)
        ->update(['stat'=>"CANCELLED"])
        ;
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
       }
   
}                