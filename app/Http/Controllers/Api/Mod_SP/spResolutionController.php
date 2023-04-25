<?php

namespace App\Http\Controllers\Api\Mod_SP;

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

class spResolutionController extends Controller
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
        $this->myr_db = $this->G->getMayorsDb();
        $this->sched_db = $this->G->getSchedulerDb();
    }
    public function getRef($date)
    {
        log::debug($date);
        $pre = 'SPR';
        $table = $this->myr_db . ".sp_resolution_main";
        $date = $date;
        $refDate = 'date_sp_request';
        $data = $this->G->generateReferenceDirect($pre, $table, $date, $refDate);
        return $data;
    }
    public function show(Request $request)
    {
        // $search = $request->search;
        // $remarks = DB::select('SELECT * FROM(SELECT *FROM ' . $this->myr_db . '.sp_resolution_updates ORDER BY `id` DESC LIMIT 100000000000000)A GROUP BY main_id');
        $list = db::select('call ' . $this->myr_db . '.rans_sp_list (?)', ["%".$request->type."%"]);
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        try {
            $main = $request->form;
            $committe = $request->committe;
            $idx = $main['id'];
            DB::beginTransaction();
            if ($idx == 0) {
                $main['ref_no'] =  $this->getRef($main['date_sp_request']);
                log::debug($main);
                db::table($this->myr_db . '.sp_resolution_main')->insert($main);
                $idx = $this->G->pk();
                if ($main['remarks_update']) {
                    $remData = array(
                        'main_id' => $idx,
                        'trans_date' => $main['sp_date'],
                        'updates' => $main['remarks_update']
                    );
                    db::table($this->myr_db . '.sp_resolution_updates')->insert($remData);
                }
            } else {
                db::table($this->myr_db . '.sp_resolution_main')->where('id', $idx)->update($main);
            }
            db::table($this->myr_db . '.sp_resolution_commi')->where('main_id', $idx)->delete();
            foreach ($committe as  $value) {
                $com  = array(
                    'main_id' => $idx,
                    'member_id' => $value
                );
                db::table($this->myr_db . '.sp_resolution_commi')->insert($com);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function storeSector(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->myr_db . '.sp_sector_setup')->insert($main);
            } else {
                db::table($this->myr_db . '.sp_sector_setup')->where('id', $idx)->update($main);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function edit($id)
    {
        $data['main'] = DB::table($this->myr_db . '.sp_resolution_main')->where('id', $id)->get();
        $data['com'] = DB::table($this->myr_db . '.sp_resolution_commi')->where('main_id', $id)->select('member_id')->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {
        DB::table($this->myr_db . '.sp_resolution_main')->where('id', $id)->update(['stat' => $id]);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function getSector(Request $request)
    {
        $list = db::table($this->myr_db . '.sp_sector_setup')->where('stat', 0)->get();
        return response()->json(new JsonResponse($list));
    }
    public function getUpdate($id)
    {
        $list = db::table($this->myr_db . '.sp_resolution_updates')
            ->where('main_id', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function getChairman()
    {
        $list = db::table($this->sched_db . '.tbl_member_info')
            ->join($this->sched_db . '.tbl_organization_profile', 'tbl_organization_profile.id', 'tbl_member_info.orgID')
            ->where('tbl_member_info.position', 'Chairman')
            ->select(db::raw("tbl_member_info.id,CONCAT(UCASE(organization_name),' - ',get_fullname(tbl_member_info.pkID))AS 'name'  "))
            ->where("tbl_member_info.trans_stat", 'Active')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getChairman2()
    {
        $list = db::table($this->sched_db . '.tbl_member_info')
            ->join($this->sched_db . '.tbl_organization_profile', 'tbl_organization_profile.id', 'tbl_member_info.orgID')
            ->where('tbl_member_info.position', 'Chairman')
            ->select(db::raw("tbl_member_info.id,CONCAT(UCASE(organization_name),' - ',get_fullname(tbl_member_info.pkID))AS 'name'  "))
            ->where("tbl_member_info.trans_stat", 'Active')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function postUpdate(Request $request)
    {
        $form = $request->main;
        $id =  $form['id'];
        if ($id > 0) {
            db::table($this->myr_db . '.sp_resolution_updates')
                ->where('id', $id)
                ->update($form);
        } else {
            db::table($this->myr_db . '.sp_resolution_updates')
                ->insert($form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function deleteUpdate($id)
    {
        db::table($this->myr_db . '.sp_resolution_updates')
            ->where('id', $id)
            ->delete();
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
}
