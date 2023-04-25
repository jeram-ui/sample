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

class lawEnforcementController extends Controller
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
    public function notes(Request $request)
    {

        try {
            // db::table($this->lgu_db . '.law_enforcement')->where('ref_id', $request->form['ref_id'])->delete();
            db::table($this->lgu_db . '.law_enforcement')->insert($request->form);
            db::table($this->lgu_db . '.law_enforce')
            ->where('id',$request->form['ref_id'])
            ->update(['notes'=>$request->form['notes']]);
            
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function showOffender(Request $request)
    {
        $list = db::select('call ' . $this->lgu_db . '.rans_legal_get_law_enforcement();');
        return response()->json(new JsonResponse($list));
    }
   
    public function upload(Request $request)
    {
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'trans_id' => $request->id,
                        'file_name' => $filename,
                        'trans_type' => 'lawEnforcement',
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'uid' => Auth::user()->id,
                    );
                    db::table('docs_upload')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function store(Request $request)
    {
        try {

            $idx = $request->id;
            $stat = 0;
            $data = array(
                'ref_no' => $request->ref_no,
                'trans_date' => $request->trans_date,
                'pp_id' => $request->pp_id,
                'fullname' => $request->fullname,
                'address' => $request->address,
                'department_id' => $request->department_id,
                'department_name' =>  $request->department_name,
                'penalties' =>  $request->penalties,
                'status' =>  $request->status,

            );
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_enforce')->insert($data);
                $idx = $this->G->pk();
                $stat = array(
                    'main_id' =>  $idx,
                    'status' =>  $request->status
                );
                db::table($this->lgu_db . '.law_enforce_status')->insert($stat);
                $stat = $this->G->pk();
                $files = $request->file('files');
                if (!empty($files)) {
                    $path = hash('sha256', time());
                    for ($i = 0; $i < count($files); $i++) {
                        $file = $files[$i];
                        $filename = $file->getClientOriginalName();
                        if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                            $data = array(
                                'trans_id' => $idx,
                                'file_name' => $filename,
                                'trans_type' => 'Law Enforcement',
                                'file_path' => $path,
                                'file_size' => $file->getSize(),
                                'uid' => Auth::user()->id,
                            );
                            db::table('docs_upload')->insert($data);
                        }
                    }
                }
            } else {
                db::table($this->lgu_db . '.law_enforce')->where('id', $idx)->update($data);
            }
           

            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }

    public function showlist()
    {
        $uploaded = DB::table('docs_upload')
            ->select('trans_id', DB::raw('count(*) as "uploaded"'))
            ->where('trans_type', 'Law Enforcement')
            ->groupBy('trans_id');

        $list = db::table($this->lgu_db . '.law_enforce')
            ->leftJoinSub($uploaded, 'uploaded', function ($join) {
                $join->on('law_enforce.id', '=', 'uploaded.trans_id');
            })
            ->where('law_enforce.stat', 0)
            ->orderBy('law_enforce.ref_no')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    
    public function getUpdate($id)
    {
        $data = db::table($this->lgu_db . '.law_enforce')
            ->where('id', $id)
            ->get();
        return response()->json(new JsonResponse($data));
    }

    public function postUpdate(Request $request)
    {
        $main = $request->main;
        $id = $main['id'];
        if ($id > 0) {
            db::table($this->lgu_db . '.law_enforce')->where('id', $id)->update($main);
        } else {
            db::table($this->lgu_db . '.law_enforcement')->insert($main);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function deleteUpdate($id)
    {
        db::table($this->lgu_db . '.law_enforcement')->where('id', $id)->update(['stat' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }

    public function  uploaded($id)
    {
        $data = db::table('docs_upload')
            ->where('trans_id', $id)
            ->where('trans_type', 'Law Enforcement')
            ->where('stat', "ACTIVE")
            ->get();
        return response()->json(new JsonResponse($data));
    }

    public function documentView($id)
    {
        $main = DB::table('docs_upload')->where('id', $id)->get();
        foreach ($main as $key => $value) {
            $file = $value->file_name;
            $path = '../storage/files/document/' . $value->file_path . '/' . $file;
            if (\File::exists($path)) {
                $file = \File::get($path);
                $type = \File::mimeType($path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }
    }

    public function uploadRemove($id)
    {
        $data = db::table('docs_upload')->where('id', $id)
            ->update(['stat' => "CANCELLED"]);
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }



    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'LE';
        $table = $this->lgu_db . ".law_enforce";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }

}
