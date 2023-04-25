<?php

namespace App\Http\Controllers\Api\Mod_legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PDF;
use Storage;
use File;
class contractController extends Controller
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
        $this->path = env('LGU_FRONT');
    }
    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'CNT';
        $table = $this->lgu_db . ".law_contract";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function getType(Request $request){
        $list = db::table($this->lgu_db .'.law_contract_type')->get();
        return response()->json(new JsonResponse($list));
    }
    public function storeDocType(Request $request){
        $main =$request->form;
        $id = $main['id'];
        if( $id > 0){
            db::table($this->lgu_db .'.law_contract_type')->where('id', $id)->update($main);
        }else{
            db::table($this->lgu_db .'.law_contract_type')->insert($main);
        }

        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    }
   public function show(Request $request){
      $list = db::table($this->lgu_db .'.law_contract')
      ->join($this->lgu_db .'.law_contract_type','law_contract_type.id','=','law_contract.contract_type')
      ->select('law_contract.*','law_contract_type.contract_type')
      ->where('stat',0)
      ->get();
      return response()->json(new JsonResponse($list));
   }
    public function store(Request $request)
    {
        try {

            $idx = $request->id;
            $data = array(
                'ref_no'=> $request->ref_no,
                'trans_date'=> $request->trans_date,
                'contract_type'=> $request->contract_type,
                'description'=>$request->description,
                'partner'=>$request->partner,
                'uid'=>Auth::user()->id,
           );
            DB::beginTransaction();
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_contract')->insert($data);
               $idx = $this->G->pk();
            } else {
                db::table($this->lgu_db.'.law_contract')->where('id', $idx)->update($data);
            }

            $files = $request->file('files');
            log::debug($request);
            if(!empty($files)){
              $path = hash( 'sha256', time());
              for($i = 0; $i < count($files); $i++){
              $file = $files[$i];
              $filename = $file->getClientOriginalName();
              if(Storage::disk('contract')->put($path.'/'.$filename,  File::get($file))) {
                  $data = array(
                    'contract_id'=>$idx,
                    'file_name'=>$filename,
                    'file_path'=>$path,
                    'file_size'=>$file->getSize(),
                  );
                  db::table($this->lgu_db.'.law_contract_docs')->insert($data);
                  }
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function getDocs($id){
        $main=DB::table($this->lgu_db.'.law_contract_docs')
        ->where('stat','0')
        ->where('contract_id',$id)->get();
        return response()->json(new JsonResponse($main));
       }
    public function storeDocumentUpdate(Request $request){
        $files = $request->file('file');
        if(!empty($files)){
          $path = hash( 'sha256', time());
          for($i = 0; $i < count($files); $i++){
          $file = $files[$i];
          $filename = $file->getClientOriginalName();
          if(Storage::disk('contract')->put($path.'/'.$filename,  File::get($file))) {
              $data = array(
                'contract_id'=>$request->id,
                'file_name'=>$filename,
                'file_path'=>$path,
                'file_size'=>$file->getSize(),
              );
              db::table($this->lgu_db.'.law_contract_docs')->insert($data);
              }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
       }
       public function documentView($id){
        $main=DB::table($this->lgu_db.'.law_contract_docs')->where('id',$id)
        ->where('stat','0')
        ->get();
        foreach ($main as $key => $value ) {
         $file = $value->file_name;
         $path = '../storage/files/legal_contract/'.$value->file_path.'/'.$file;
         if (\File::exists($path)) {
         $file = \File::get($path);
         $type = \File::mimeType($path);
         $response = \Response::make($file, 200);
         $response->header("Content-Type", $type);
         return $response;
         }
        }
    }
    public function edit($id)
    {
        $data['main'] = DB::table($this->lgu_db.'.law_contract')->where('id',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function uploadRemove($id){
        DB::table($this->lgu_db.'.law_contract_docs')->where('id',$id)->update(['stat'=>'1']);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }

    public function cancel($id)
       {
           DB::table($this->lgu_db.'.law_contract')->where('id',$id)->update(['stat'=>'1']);
         return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
       }
}
