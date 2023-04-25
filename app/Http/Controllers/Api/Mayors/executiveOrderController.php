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

class executiveOrderController extends Controller
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
    public function doneMemo($id)
    {
        db::table($this->mayors_db.'.memorandum')
            ->where('id', $id)
            ->update(['memorandum.doneStat' => "Done"]);
      
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function getApplicantType()
    {
        $list = DB::select('Call ' . $this->lgu_db . '.profile_applicant_type_zoe()');
        return response()->json(new JsonResponse($list));
    }
    public function getRequirements(Request $request)
    {
        $frmname =  $request->frmname;
        $list = DB::select('Call ' . $this->lgu_db . '.display_certpermit_requirements_gigil(?)', array($frmname));
        return response()->json(new JsonResponse($list));
    }
    public function masterList(Request $request)
    {

        $dateFrom = $request['from'];
        $dateTo = $request['to'];
        $_formname = $request['formtype'];
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_profile1_gen(?,?,?)', array($dateFrom, $dateTo, $_formname));

        return response()->json(new JsonResponse($list));
    }
    public function ref(Request $request)
    {   $pre = 'EO';
        $table = $this->mayors_db. ".executive_order";
        $date = $request->date;
        $refDate = 'trans_date';
        $query = DB::select("SELECT CONCAT('" . $pre . "',DATE_FORMAT('" . $date . "', '%y'),'-',LPAD(COUNT(" . $refDate . ")+ 1,5,0)) AS 'NOS' FROM " . $table . " WHERE  YEAR(" . $refDate . ") =  YEAR('" . $date . "')");
        return response()->json(new JsonResponse(['data' => $query]));
    }
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $form = $request->form;
            $id =$form['id'];
            if ($id == 0) {
                $form['uid'] = Auth::user()->id;
                db::table($this->mayors_db.'.executive_order')
                ->insert($form);
            }else{
                $form['upid'] = Auth::user()->id;
                db::table($this->mayors_db.'.executive_order')
                ->where('id', $id)
                ->update($form);
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
           $data=  db::table($this->mayors_db.'.executive_order')
            ->where('status',0)
            ->whereBetween('trans_date',[ $from,$to])
            ->orderBy("id","desc")
            ->get();
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {
           
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
   
    public function edit($id)
    {
        try {
          
           $data = db::table($this->mayors_db.'.executive_order')
            ->where('id',$id)->get();
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function cancel($id)
    {
        try {
           $data = db::table($this->mayors_db.'.executive_order')
            ->where('id',$id)
            ->update(['status'=>1])
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
          if(Storage::disk('ExecutiveOrder')->put($path.'/'.$filename,  File::get($file))) {
              $data = array(
                'trans_id'=>$request->id,
                'file_name'=>$filename,
                'file_path'=>$path,
                'file_size'=>$file->getSize(),
                'uid'=>Auth::user()->id,
                'entry_type'=>'ExecutiveOrder'
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
        ->where('entry_type','ExecutiveOrder')
        ->where('stat', "ACTIVE")
        ->get();
        return response()->json(new JsonResponse($data));
       }
       public function documentView($id){
        $main=DB::table($this->mayors_db.'.documents_uploded')->where('id',$id)->get();
        foreach ($main as $key => $value ) {
         $file = $value->file_name;
         $path = '../storage/files/ExecutiveOrder/'.$value->file_path.'/'.$file;
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
