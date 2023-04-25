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

class lawenforcecontroller extends Controller
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
    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'DFR';
        $table = $this->lgu_db . ".law_enforce";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }

    public function show()
    {
        $showAll = db::table($this->lgu_db .".law_enfoce_view_all")->where("employee_id",Auth::user()->Employee_id)->count();

        $uploaded = DB::table('docs_upload')
            ->select('trans_id', DB::raw('count(*) as "uploaded"'))
            ->where('trans_type', 'Law Enforcement')
            ->where('stat','ACTIVE')
            ->groupBy('trans_id');

         if ( $showAll>0) {
            $list = db::table($this->lgu_db . '.law_enforce')
            ->leftJoinSub($uploaded, 'uploaded', function ($join) {
                $join->on('law_enforce.id', '=', 'uploaded.trans_id');
            })

            ->where('law_enforce.stat', 0)
            ->orderBy('law_enforce.ref_no')
            ->get();
         }else{
            $list = db::table($this->lgu_db . '.law_enforce')
            ->leftJoinSub($uploaded, 'uploaded', function ($join) {
                $join->on('law_enforce.id', '=', 'uploaded.trans_id');
            })
            ->where('law_enforce.encoded_emp_id',Auth::user()->Employee_id )
            ->where('law_enforce.stat', 0)
            ->orderBy('law_enforce.ref_no')
            ->get();
         }
      
        return response()->json(new JsonResponse($list));
    }
    public function showStat($id)
    {
        $list = db::table($this->lgu_db . '.law_enforce_status')
            ->where('stats', 0)
            ->where('main_id', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function storeUpload(Request $request)
    {
        $stat = array(
            'main_id' =>  $request->main_id,
            'status' =>  $request->status
        );
        db::table($this->lgu_db . '.law_enforce')->insert($stat);
        $stat = $this->G->pk();
        $files = $request->file('files');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'trans_id' => $stat,
                        'file_name' => $filename,
                        'trans_type' => 'Resolution and Ordinance',
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'uid' => Auth::user()->id,
                    );
                    db::table('docs_upload')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    }
    public function updateStat(Request $request)
    {

        try {

            $idx = $request->id;
            $data = array(
                'status' =>  $request->status,
            );
            DB::beginTransaction();
            db::table($this->lgu_db . '.law_enforce')->where('id', $idx)->update($data);
            db::table($this->lgu_db . '.law_enforce_status')->insert(['main_id'=>$idx,'status'=>$request->status]);
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
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
                'department_id' => $request->department_id,
                'department_name' =>  $request->department_name,
                'penalties' =>  $request->penalties,
                'notices' =>  $request->notices,
                'status' =>  $request->status,
                'encoded_emp_id'=>Auth::user()->Employee_id
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
            // log::debug($request);
            // $files = $request->file('file');

            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function updateAction(Request $request)
    {
        try {
            $data = $request->form;
            $idx =  $data['id'];

            $main = array(
                'actions_taken' => $data['actions_taken']
            );

            db::table($this->lgu_db . '.law_enforce')->where('id', $idx)->update($main);
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function edit($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.law_enforce')->where('id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {
        DB::table($this->lgu_db . '.law_enforce')->where('id', $id)->update(['stat' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
}
