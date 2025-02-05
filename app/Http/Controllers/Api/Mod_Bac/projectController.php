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
use ZipArchive;
use NumberFormatter;

class projectController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
    private $Proc;
    private $budget;
    private $dbEngr;
    private $Bac;
    private $sched_db;


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
        $this->dbEngr = $this->G->getEngDb();

        $this->budget = $this->G->getBudgetDb();
    }
    public function displayCalendarPerdate(Request $request)
    {
        $date = $request->date;
        $type = $request->type;
        $proc_type = $request->proc_type;
        $list = DB::select("call " . $this->Bac . ".rans_display_procurement_calendar_per_date(?,?,?)", [$date, $type, $proc_type]);
        return response()->json(new JsonResponse($list));
    }
    public function displayCalendar(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $data_pre = DB::table($this->Bac . '.bacc_proj')
            ->whereBetween('bacc_proj.pre_proc', [$request->from, $request->to])->get();
        $data = db::select("call " . $this->Bac . ".rans_display_procurement_calendar(?,?)", [$request->from, $request->to]);
        // $data_posting = DB::table($this->Bac . '.bacc_proj')
        //     ->whereBetween('bacc_proj.posting', [$request->from, $request->to])->get();

        // $data_prebid = DB::table($this->Bac . '.bacc_proj')
        //     ->whereBetween('bacc_proj.pre_bid', [$request->from, $request->to])->get();

        // $data_invitation = DB::table($this->Bac . '.bacc_proj')
        //     ->select('*', db::raw("ADDDATE(pre_bid,INTERVAL 5 DAY) as invitation_date"))
        //     ->whereBetween(db::raw("ADDDATE(pre_bid,INTERVAL 5 DAY)"), [$request->from, $request->to])->get();


        // $data_opening = DB::table($this->Bac . '.bacc_proj')
        //     ->whereBetween('bacc_proj.bid_opening', [$request->from, $request->to])->get();

        // $data_postqua = DB::table($this->Bac . '.bacc_proj')
        //     ->whereBetween('bacc_proj.post_qua', [$request->from, $request->to])->get();

        // $data_noa = DB::table($this->Bac . '.bacc_proj')
        //     ->whereBetween('bacc_proj.noa', [$request->from, $request->to])->get();

        // $data_ntp = DB::table($this->Bac . '.bacc_proj')
        //     ->whereBetween('bacc_proj.ntp_effective', [$request->from, $request->to])->get();

        // $data_dole = DB::table($this->Bac . '.bacc_proj')
        //     ->join($this->Bac.'.bacc_noa','bacc_noa.proj_id','bacc_proj.id')
        //     ->whereBetween('bacc_noa.deadline_dole_approved', [$request->from, $request->to])->get();
        // log::debug($data_dole);

        $calendar = array();
        foreach ($data as $key => $val) {
            // if ($val->pre_proc < Date(Now())) {
            //     $StatColor = 'green';
            // } else {
            //     $StatColor = 'blue';
            // };
            $calendars = array(
                'id'     => intval($val->id),
                'name' => $val->name,
                'start' => date_format(date_create($val->start), "Y-m-d"),
                'end'     => date_format(date_create($val->end), "Y-m-d"),
                'color' =>  $val->color,
                'Description' => $val->Description,
                'Subject' => $val->Subject,
                'Status' => 0,
                'refno' => $val->refno,
                'count' => $val->count,
                'proc_color' => $val->proc_color,
                'proc_type' => $val->proc_type,
            );
            array_push($calendar, $calendars);
        }

        // foreach ($data_posting as $key => $val) {
        //     if ($val->posting < Date(Now())) {
        //         $StatColor = 'green';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'Posting',
        //         'start' => date_format(date_create($val->posting), "Y-m-d"),
        //         'end'     => date_format(date_create($val->posting), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'Posting Procurement',
        //         'refno' => $val->doc_ref,
        //         'Status' => 0,
        //     );
        //     array_push($calendar, $calendars);
        // }

        // foreach ($data_prebid as $key => $val) {
        //     if ($val->pre_bid < Date(Now())) {
        //         $StatColor = 'green';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'Pre BID Confirence',
        //         'start' => date_format(date_create($val->pre_bid), "Y-m-d"),
        //         'end'     => date_format(date_create($val->pre_bid), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'Pre-BID Confirence',
        //         'Status' => 0,
        //         'refno' => $val->doc_ref,
        //     );
        //     array_push($calendar, $calendars);
        // }
        // foreach ($data_invitation as $key => $val) {
        //     if ($val->invitation_date < Date(Now())) {
        //         $StatColor = 'green';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'Invitation Letter to Observer',
        //         'start' => date_format(date_create($val->invitation_date), "Y-m-d"),
        //         'end'     => date_format(date_create($val->invitation_date), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'Invitation Letter to Observer',
        //         'Status' => 0,
        //         'refno' => $val->doc_ref,
        //     );
        //     array_push($calendar, $calendars);
        // }
        // foreach ($data_opening as $key => $val) {
        //     if ($val->bid_opening < Date(Now())) {
        //         $StatColor = 'green';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'BID Opening',
        //         'start' => date_format(date_create($val->bid_opening), "Y-m-d"),
        //         'end'     => date_format(date_create($val->bid_opening), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'BID Opening',
        //         'Status' => 0,
        //         'refno' => $val->doc_ref,
        //     );
        //     array_push($calendar, $calendars);
        // }

        // foreach ($data_postqua as $key => $val) {
        //     if ($val->post_qua < Date(Now())) {
        //         $StatColor = 'green';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'Post Qualification',
        //         'start' => date_format(date_create($val->post_qua), "Y-m-d"),
        //         'end'     => date_format(date_create($val->post_qua), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'Post Qualification',
        //         'Status' => 0,
        //         'refno' => $val->doc_ref,
        //     );
        //     array_push($calendar, $calendars);
        // }
        // foreach ($data_noa as $key => $val) {
        //     if ($val->noa < Date(Now())) {
        //         $StatColor = 'green';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'NOA',
        //         'start' => date_format(date_create($val->noa), "Y-m-d"),
        //         'end'     => date_format(date_create($val->noa), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'NOA',
        //         'Status' => 0,
        //         'refno' => $val->doc_ref,
        //     );
        //     array_push($calendar, $calendars);
        // }
        // foreach ($data_ntp as $key => $val) {
        //     if ($val->ntp_effective < Date(Now())) {
        //         $StatColor = 'green';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'NTP',
        //         'start' => date_format(date_create($val->ntp_effective), "Y-m-d"),
        //         'end'     => date_format(date_create($val->ntp_effective), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'NTP',
        //         'Status' => 0,
        //         'refno' => $val->doc_ref,
        //     );
        //     array_push($calendar, $calendars);
        // }
        // foreach ($data_dole as $key => $val) {
        //     if ($val->deadline_dole_approved < Date(Now())) {
        //         $StatColor = 'red';
        //     } else {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->id),
        //         'name' => 'Deadline Dole Approved',
        //         'start' => date_format(date_create($val->deadline_dole_approved), "Y-m-d"),
        //         'end'     => date_format(date_create($val->deadline_dole_approved), "Y-m-d"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->title_of_project,
        //         'Subject' => 'Deadline Dole Approved',
        //         'Status' => 0,
        //         'refno' => $val->doc_ref,
        //     );
        //     array_push($calendar, $calendars);
        // }
        return response()->json(new JsonResponse($calendar));
    }
    public function displayInfra(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $data_pre = DB::table($this->Bac . '.bacc_proj')
            ->whereBetween('bacc_proj.pre_proc', [$request->from, $request->to])->get();
        $data = db::select("call " . $this->Bac . ".rans_display_procurement_calendar_infra(?,?)", [$request->from, $request->to]);

        $calendar = array();
        foreach ($data as $key => $val) {
            $calendars = array(
                'id'     => intval($val->id),
                'name' => $val->name,
                'start' => date_format(date_create($val->start), "Y-m-d"),
                'end'     => date_format(date_create($val->end), "Y-m-d"),
                'color' =>  $val->color,
                'Description' => $val->Description,
                'Subject' => $val->Subject,
                'Status' => 0,
                'refno' => $val->refno,
                'count' => $val->count,
                'proc_color' => $val->proc_color,
                'proc_type' => $val->proc_type,
            );
            array_push($calendar, $calendars);
        }
        return response()->json(new JsonResponse($calendar));
    }
    public function displayGoods(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $data_pre = DB::table($this->Bac . '.bacc_proj')
            ->whereBetween('bacc_proj.pre_proc', [$request->from, $request->to])->get();
        $data = db::select("call " . $this->Bac . ".rans_display_procurement_calendar_goods(?,?)", [$request->from, $request->to]);

        $calendar = array();
        foreach ($data as $key => $val) {
            $calendars = array(
                'id'     => intval($val->id),
                'name' => $val->name,
                'start' => date_format(date_create($val->start), "Y-m-d"),
                'end'     => date_format(date_create($val->end), "Y-m-d"),
                'color' =>  $val->color,
                'Description' => $val->Description,
                'Subject' => $val->Subject,
                'Status' => 0,
                'refno' => $val->refno,
                'count' => $val->count,
                'proc_color' => $val->proc_color,
                'proc_type' => $val->proc_type,
            );
            array_push($calendar, $calendars);
        }
        return response()->json(new JsonResponse($calendar));
    }
    public function getMOP()
    {
        $list = db::table($this->budget . '.cto_budget_mode_pro')
            ->where('status', 'ACTIVE')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getBacMembers(Request $request)
    {

        $proj_id = $request->id;
        $tag = db::table($this->Bac . '.bacc_commite_tag')->where('description', 'BAC')->first();
        $list['bac'] = db::select('call ' . $this->Bac . '.rans_bacc_getBacMembers(?,?,?)', [$proj_id, $tag->org_id, 'BAC']);
        $list['observer'] = db::table($this->Bac . '.bacc_invitation_prebid')
            ->select('proj_id', 'business_id as id', 'business_name as name', 'organization', 'date_invitation as dateinvitation', 'date_receipt as dateofreceipt')
            ->where('proj_id', $proj_id)
            ->where('entry_type', 'OBSERVER')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function cancel($id)
    {
        db::table($this->Bac . '.bacc_proj')->where('id', $id)->update(['stat' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Successfully deleted', 'status' => 'success']));
    }
    public function getBacMembersForOpeningBID(Request $request)
    {

        $proj_id = $request->id;
        $tag = db::table($this->Bac . '.bacc_commite_tag')->where('description', 'BAC')->first();

        $list['bac'] = db::select('call ' . $this->Bac . '.rans_bacc_getBacMembersForOpening(?,?,?)', [$proj_id, $tag->org_id, 'BAC']);
        $chk = db::table($this->Bac . '.bacc_invitation_opening_bid')
            ->select('proj_id')
            ->where('proj_id', $proj_id)->get();

        if (count($chk) > 0) {
            $list['observer'] = db::table($this->Bac . '.bacc_invitation_opening_bid')
                ->select('bacc_invitation_opening_bid.proj_id', 'business_id as id', 'business_name as name', 'date_invitation as dateinvitation', 'date_receipt as dateofreceipt')
                ->where('bacc_invitation_opening_bid.proj_id', $proj_id)
                ->where('entry_type', 'OBSERVER')
                ->get();
        } else {
            $data = db::table($this->Bac . '.bacc_invitation_prebid')
                ->select('bacc_invitation_prebid.proj_id', 'business_id as id', 'business_name as name', 'date_invitation as dateinvitation', 'date_receipt as dateofreceipt')
                ->where('bacc_invitation_prebid.proj_id', $proj_id)
                ->where('entry_type', 'OBSERVER')
                ->get();

            foreach ($data as $key => $value) {
                $data[$key]->dateinvitation = "";
                $data[$key]->dateofreceipt = "";
            }
            $list['observer'] = $data;
            // for
        }
        $list['supplier'] = db::table($this->Bac . '.bacc_invitation_opening_bid')
            ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_invitation_opening_bid.proj_id')
            ->select('bacc_invitation_opening_bid.id', 'bacc_invitation_opening_bid.proj_id', 'business_id', 'business_name as name', 'organization', 'bacc_proj.ABC as abc_amount', 'date_invitation as dateinvitation', 'date_receipt as dateofreceipt', 'bacc_proj.itb_no', 'doc_amount')
            ->where('bacc_invitation_opening_bid.proj_id', $proj_id)
            ->where('entry_type', 'SUPPLIER')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getDocs(Request $request)
    {
        $_type = $request->type;
        $proj_id = $request->proj_id;

        if ($_type === '') {
            # code...
        }
        $list = db::table($this->Bac . '.bacc_preproc_docs')
            ->leftJoin(
                DB::raw('(SELECT proj_id,ifnull(COUNT(`doctype_id`),0) as docsCount,doctype_id FROM ' . $this->Bac . '.bacc_pre_docs_entry
        where proj_id = ' . $proj_id . '
        and stat = 0
        GROUP BY doctype_id)
        docs'),
                function ($join) {
                    $join->on('bacc_preproc_docs.id', '=', 'docs.doctype_id');
                }
            )
            ->where('type', $_type)
            ->where('stats', 0)
            ->orderBy('order')
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function getDocsRemove(Request $request)
    {
        $_type = $request->type;
        $proj_id = $request->proj_id;
    }

    public function getDocsPrebid_bulletin(Request $request)
    {
        $proj_id = $request->proj_id;
        $id = $request->id;
        $list = db::select('SELECT bacc_bid_bulletin.*,bacc_pre_docs_entry.`id` FROM
        ' . $this->Bac . '.bacc_bid_bulletin
        LEFT JOIN ' . $this->Bac . '.bacc_pre_docs_entry
        ON(bacc_pre_docs_entry.`proj_id` = bacc_bid_bulletin.`proj_id` AND bacc_pre_docs_entry.`bull_no` = bacc_bid_bulletin.`bull_no`
        AND bacc_bid_bulletin.`attch_description` = bacc_pre_docs_entry.`attch_description`
        )WHERE bacc_bid_bulletin.`proj_id` = ' . $proj_id . ' GROUP BY proj_id');
        return response()->json(new JsonResponse($list));
    }
    public function getProject()
    {
        $save = db::table($this->Bac . '.bacc_proj')
            ->where("stat", 0)
            ->get();
        $array = array();
        foreach ($save as $key => $value) {
            array_push($array, $value->pow_id);
        }

        $pow = db::table($this->Proc . ".tbl_pr_main")
            ->select("mode_proc_id", 'mode_proc', 'pow_id')
            ->where('status', '<>', 'CANCELLED');

        $list = db::table($this->Proc . '.pow_main_individual')
            ->leftJoinSub($pow, 'pow', function ($join) {
                $join->on('pow_main_individual.id', '=', 'pow.pow_id');
            })
            ->leftJoin($this->dbEngr . '.setup_project_registration_main', 'setup_project_registration_main.id', 'pow_main_individual.project_id')
            // ->leftJoin($this->Proc . '.pow_sof_detail', 'pow_sof_detail.pow_id', '=', 'pow_main_individual.id')
            ->select('pow.*', 'pow_main_individual.*', 'pow_main_individual.id as pow_id', 'setup_project_registration_main.project_classification as Project Type', 'setup_project_registration_main.location', db::raw($this->Proc . '.rans_get_fund_by_pow(pow_main_individual.id) as SOF_Description'), db::raw('concat(pow_main_individual.project_duration," CD") as project_duration'), 'pow_main_individual.bidamount as bidamount')
            ->where('pow_main_individual.status', '=', 'Approved')

            ->whereIn('pow_main_individual.frm', ['POW', 'PROPOSAL'])
            ->where('pow_main_individual.bidamount', '>', 800000.00)
            ->whereNotIn('pow_main_individual.id', $array)
            ->groupBy('pow_main_individual.id')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function upload(Request $request)
    {
        // log::debug($request);
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('bac_docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'proj_id' => $request->proj_id,
                        'trans_type' => $request->trans_type,
                        'doctype_id' => $request->doctype_id,
                        'bull_no' => $request->bull_no,
                        'attch_description' => $request->attch_description,
                        'remarks' => $request->remarks,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'uid' => Auth::user()->id,
                    );
                    db::table($this->Bac . '.bacc_pre_docs_entry')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function uploadPreBID(Request $request)
    {
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('bac_docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'proj_id' => $request->proj_id,
                        'type' => $request->trans_type,
                        'type_id' => $request->doctype_id,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'uid' => Auth::user()->id,
                    );
                    db::table($this->Bac . '.bacc_invitation_prebid_docs')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function uploadBIDOpining(Request $request)
    {
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('bac_docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'proj_id' => $request->proj_id,
                        'type' => $request->trans_type,
                        'type_id' => $request->doctype_id,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'uid' => Auth::user()->id,
                    );
                    db::table($this->Bac . '.bacc_invitation_bidopening_docs')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function  getAttach(Request $request)
    {
        $data = db::table($this->Bac . '.bacc_pre_docs_entry')
            ->where('proj_id', $request->proj_id)
            ->where('trans_type', $request->trans_type)
            ->where('doctype_id', $request->doctype_id);
        if ($request->id) {
            $data->where('id', $request->id);
        }
        $data->where('stat', 0)
            ->get();
        $result = $data->get();
        return response()->json(new JsonResponse($result));
    }
    public function documentView($id)
    {
        $main = DB::table($this->Bac . '.bacc_pre_docs_entry')->where('id', $id)->get();
        foreach ($main as $key => $value) {
            $file = $value->file_name;
            $path = '../storage/files/bac_docs/' . $value->file_path . '/' . $file;
            if (\File::exists($path)) {
                $file = \File::get($path);
                $type = \File::mimeType($path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }
    }
    public function  getAttachBull(Request $request)
    {
        $data = db::table($this->Bac . '.bacc_pre_docs_entry')
            ->where('proj_id', $request->proj_id)
            ->where('trans_type', $request->trans_type)
            ->where('doctype_id', $request->doctype_id)
            ->where('bull_no', $request->bull_no)
            ->where('attch_description', $request->attch_description);
        $data->where('stat', 0)
            ->get();
        $result = $data->get();
        return response()->json(new JsonResponse($result));
    }
    public function  getAttachPreBID(Request $request)
    {
        $data = db::table($this->Bac . '.bacc_invitation_prebid_docs')
            ->where('proj_id', $request->proj_id)
            ->where('type', $request->trans_type)
            ->where('type_id', $request->doctype_id);
        if ($request->id) {
            $data->where('id', $request->id);
        }
        $data->where('stat', 0)
            ->get();
        $result = $data->get();
        return response()->json(new JsonResponse($result));
    }
    public function  getAttachBIDOpening(Request $request)
    {
        $data = db::table($this->Bac . '.bacc_invitation_bidopening_docs')
            ->where('proj_id', $request->proj_id)
            ->where('type', $request->trans_type)
            ->where('type_id', $request->doctype_id);
        if ($request->id) {
            $data->where('id', $request->id);
        }
        $data->where('stat', 0)
            ->get();
        $result = $data->get();
        return response()->json(new JsonResponse($result));
    }
    public function documentViewPreBID($id)
    {
        // log::debug($id);
        $main = DB::table($this->Bac . '.bacc_invitation_prebid_docs')->where('id', $id)->get();
        // log::debug($value->file_name);
        foreach ($main as $key => $value) {
            // log::debug($value->file_name);
            $file = $value->file_name;
            $path = '../storage/files/bac_docs/' . $value->file_path . '/' . $file;
            if (\File::exists($path)) {
                $file = \File::get($path);
                $type = \File::mimeType($path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }
    }

    public function documentViewBIDOpining($id)
    {
        // log::debug($id);
        $main = DB::table($this->Bac . '.bacc_invitation_bidopening_docs')->where('id', $id)->get();
        // log::debug($value->file_name);
        foreach ($main as $key => $value) {
            // log::debug($value->file_name);
            $file = $value->file_name;
            $path = '../storage/files/bac_docs/' . $value->file_path . '/' . $file;
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
        $data = db::table($this->Bac . '.bacc_pre_docs_entry')->where('id', $id)
            ->update(['stat' => "1"]);
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function uploadRemovePreBID($id)
    {
        $data = db::table($this->Bac . '.bacc_invitation_prebid_docs')->where('id', $id)
            ->update(['stat' => "1"]);
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function uploadRemoveBIDOpening($id)
    {
        $data = db::table($this->Bac . '.bacc_invitation_bidopening_docs')->where('id', $id)
            ->update(['stat' => "1"]);
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }


    public function showBidout(Request $request)
    {


        $filter = $request;
        $date = date_create($filter->year);
        // log::debug($filter->type);
        $list = db::table($this->Bac . '.bacc_proj')
            ->leftJoin($this->lgu_db . '.setup_project_registration_main', 'setup_project_registration_main.id', 'bacc_proj.proj_id');
        $list = db::table($this->Bac . '.bacc_proj')
            ->leftJoin($this->Bac . '.bacc_2invitationtobid', 'bacc_2invitationtobid.bacc_proj_id', 'bacc_proj.proj_id')
            ->leftJoin($this->lgu_db . '.setup_project_registration_main', 'setup_project_registration_main.id', 'bacc_proj.proj_id')
            ->leftJoin($this->lgu_db . '.ebplo_business_list', 'ebplo_business_list.business_number', 'bacc_proj.bidder_id');
        $result = $list
            ->select('bacc_proj.*', 'bacc_2invitationtobid.ref', 'setup_project_registration_main.location', 'ebplo_business_list.business_name', 'reference_address', 'reference_owner_name')
            ->where("proc_type", $filter->type)
            ->where(db::raw("quarter(bid_opening)"), $filter->quarter)
            ->whereYear("bid_opening", date_format($date, "Y"))
            ->orderBy(db::Raw('ifnull(bacc_proj.itb_no,"")'), "asc")
            ->get();

        return response()->json(new JsonResponse($result));
    }
    public function show(Request $request)
    {

        // $filter = $request->filter;
        $list = db::table($this->Bac . '.bacc_proj')
            ->leftJoin($this->lgu_db . '.ebplo_business_list', 'bacc_proj.winning_bidder', 'ebplo_business_list.business_number')
            ->leftjoin($this->Bac . '.bacc_2invitationtobid', 'bacc_2invitationtobid.bacc_proj_id', 'bacc_proj.id')
            ->leftjoin($this->Bac . '.bacc_contract', 'bacc_contract.proj_id', 'bacc_proj.id')
            ->leftjoin($this->Proc . '.pow_main_individual', 'pow_main_individual.id', 'bacc_proj.pow_id')
            ->leftjoin($this->Proc . '.tbl_pr_main', 'tbl_pr_main.pow_id', 'pow_main_individual.id')
            ->where('stat', 0)
            ->where('transaction_entry_type', 'bidding')
            ->whereRaw(' (`doc_ref` like ? or `ABC` like ? or `title_of_project` like ? or bacc_proj.`SOF` like ? or bacc_proj.`remarks` like ? or `winning_bidder` like ? or bacc_proj.itb_no like ?)', ['%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%']);
        // if ($filter === 'This Day') {
        //     $list->where('pre_proc', '=', date("Y-m-d"))
        //         ->orWhere('posting', date("Y-m-d"))
        //         ->orWhere('pre_bid', date("Y-m-d"))
        //         ->orWhere('bid_opening', date("Y-m-d"))
        //         ->orWhere('post_qua', date("Y-m-d"))
        //         ->orWhere('noa', date("Y-m-d"))
        //         ->orWhere('contract_date', date("Y-m-d"))
        //         ->orWhere('ntp_issuance', date("Y-m-d"))
        //         ->orWhere('ntp_effective', date("Y-m-d"));
        // };
        // if ($filter === 'This Week') {
        //     $list->whereRaw('week(pre_proc) = week(now())')
        //         ->orwhereRaw('week(posting) = week(now())')
        //         ->orwhereRaw('week(pre_bid) = week(now())')
        //         ->orwhereRaw('week(bid_opening) = week(now())')
        //         ->orwhereRaw('week(post_qua) = week(now())')
        //         ->orwhereRaw('week(noa) = week(now())')
        //         ->orwhereRaw('week(contract_date) = week(now())')
        //         ->orwhereRaw('week(ntp_issuance) = week(now())')
        //         ->orwhereRaw('week(ntp_effective) = week(now())');
        // };
        // if ($filter === 'Done') {
        //     $list->where('steps', '>', 11);
        // };
        // if ($filter === 'On Going') {
        //     $list->where('steps', '<', 12);
        // };

        $result = $list
            ->select(
                '*',
                'bacc_proj.*',
                'bacc_contract.sp_resolution_no',
                'pow_main_individual.project_loc',
                'pow_main_individual.project_loc_CityProvince',
                'tbl_pr_main.pr_no',
                'ebplo_business_list.reference_address',
                db::raw("(CASE WHEN proc_type = 'Infrastructure' AND ABC > 5000000 THEN TRUE WHEN proc_type = 'Goods' AND ABC > 2000000 THEN TRUE WHEN proc_type = 'Consultancy' AND ABC > 1000000 THEN TRUE ELSE FALSE END) AS showss
            "),
                db::raw("ifnull(" . $this->Bac . ".get_project_status_bac(bacc_proj.pow_id),'On-going') as statusx")
            )
            ->orderBy(db::Raw('ifnull(bacc_proj.itb_no,"")'), "asc")
            // ->orderBy(db::Raw("ifnull(" . $this->Bac . ".bacc_proj.itb_no,"")"), "asc")
            // ->orderBy(db::raw("ifnull(" . $this->Bac . ".(bacc_proj.pow_id,"")"))
            ->get();

        return response()->json(new JsonResponse($result));
    }
    public function showFilter(Request $request)
    {

        $filter = $request->filter;
        $list = db::table($this->Bac . '.bacc_proj')
            ->where('stat', 0)
            ->whereRaw('(`proc_type` like ?)', ['%' . $request->proc_type . '%'])
            ->whereBetween('trans_date', [$request->dateFrom, $request->dateTo]);

        $result = $list
            ->select(
                '*',
                db::raw("(CASE WHEN proc_type = 'Infrastructure' AND ABC > 5000000 THEN TRUE WHEN proc_type = 'Goods' AND ABC > 2000000 THEN TRUE WHEN proc_type = 'Consultancy' AND ABC > 1000000 THEN TRUE ELSE FALSE END) AS showss
            "),
                db::raw("ifnull(" . $this->Bac . ".get_project_status_bac(pow_id),'On-going') as statusx")
            )
            ->orderBy(db::Raw('ifnull(itb_no,"")'), "asc")
            ->get();

        return response()->json(new JsonResponse($result));
    }
    public function showEntry(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $trk = db::table('documenttracker.documentstrackermain')
            ->select('documentstrackermain.ID as trkId', 'documentstrackermain.Subject', 'documentstrackermain.TrackingNum', 'pow_id')
            ->where("documentstrackermain.pow_id", ">", 0)
            ->where("documentstrackermain.status", "Active");

        $filter = $request->filter;
        $list = db::table($this->Bac . '.bacc_proj')
            ->leftJoinSub($trk, 'trk', function ($join) {
                $join->on('bacc_proj.pow_id', '=', 'trk.pow_id');
            })

            // ->leftJoin("documenttracker.documentstrackermain",'documentstrackermain.pow_id','bacc_proj.pow_id')
            ->where('bacc_proj.stat', 0)
            ->whereBetween(db::raw('date(ts)'), [$from, $to]);


        $result = $list
            ->select(
                'bacc_proj.*',
                'trk.TrackingNum',
                'trk.Subject',
                'trk.trkId',
                db::raw("ifnull(" . $this->Bac . ".get_project_status_bac(bacc_proj.pow_id),'On-going') as statusx")
            )
            ->whereRaw(" (`doc_ref` like ? or `title_of_project` like ? or `SOF` like ?  or `bid_bulletin` like ? or `remarks` like ? or `itb_no` like ?)", ['%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%'])
            ->orderBy("bacc_proj.id", "desc")
            ->get();

        return response()->json(new JsonResponse($result));
    }
    public function showprocProject_fltr(Request $request)
    {
        // $from = $request->from;
        // $to = $request->to;
        // $filterZ = $request->filterZ;
        $trk = db::table('documenttracker.documentstrackermain')
            ->select('documentstrackermain.ID as trkId', 'documentstrackermain.Subject', 'documentstrackermain.TrackingNum', 'pow_id')
            ->where("documentstrackermain.pow_id", ">", 0)
            ->where("documentstrackermain.status", "Active");

        $filterZ = $request->filterZ;
        $list = db::table($this->Bac . '.bacc_proj')

            ->leftJoinSub($trk, 'trk', function ($join) {
                $join->on('bacc_proj.pow_id', '=', 'trk.pow_id');
            })

            // ->leftJoin("documenttracker.documentstrackermain",'documentstrackermain.pow_id','bacc_proj.pow_id')
            ->where('bacc_proj.stat', 0)
            ->whereRaw('(`proc_type` like ?)', ['%' . $request->proc_type . '%'])
            ->whereBetween(db::raw('date(ts)'), [$request->dateFrom, $request->dateTo]);

        $result = $list
            ->select(
                'bacc_proj.*',
                'trk.TrackingNum',
                'trk.Subject',
                'trk.trkId',
                db::raw("ifnull(" . $this->Bac . ".get_project_status_bac(bacc_proj.pow_id),'On-going') as statusx")
            )
            ->orderBy("bacc_proj.id", "desc")
            ->get();

        return response()->json(new JsonResponse($result));
    }
    public function get_1pre(Request $request)
    {
        $list = db::table($this->Bac . '.bacc_1pre')
            ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_1pre.bacc_proj_id')
            ->select('bacc_1pre.*', 'bacc_proj.proc_type')
            ->whereRaw(" (bacc_1pre.trans_date like ? or bacc_1pre.ref_no like ? or bacc_1pre.name_of_project like ? or bacc_1pre.remarks like ? or bacc_proj.proc_type like ?)", ['%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%', '%' . $request->filterval . '%'])
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function get_1preSelected($_id)
    {
        $list = db::table($this->Bac . '.bacc_1pre')
            ->where('bacc_proj_id', $_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function storePreProc(Request $request)
    {
        $data = $request->form;
        $step = $request->step;
        $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $data['bacc_proj_id'])->first();
        $stepDone = $chkstep->steps;
        if ($stepDone <= $step) {
            db::table($this->Bac . '.bacc_proj')->where('id', $data['bacc_proj_id'])->update(['steps' => ($stepDone * 1) + 1]);
        }
        db::table($this->Bac . '.bacc_1pre')->where('bacc_proj_id', $data['bacc_proj_id'])->delete();
        db::table($this->Bac . '.bacc_1pre')->insert($data);
        $datax = array(
            'pre_proc' => $data['trans_date']
        );

        db::table($this->Bac . '.bacc_proj')->where('id', $data['bacc_proj_id'])->update($datax);
        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    }

    public function get_2invitation(Request $request)
    {
        $list = db::table($this->Bac . '.bacc_2invitationtobid')
            // ->whereRaw(" (`project_name` like ? or `philgep` like ? or `prebidd_conference` like ? or `opening` like ?)", ['%' . $request->filterW . '%', '%' . $request->filterW . '%', '%' . $request->filterW . '%', '%' . $request->filterW . '%'])
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function get_3invitation($_id)
    {
        $list = db::table($this->Bac . '.bacc_2invitationtobid')
            ->where('bacc_proj_id', $_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function get_3invitation_prebid()
    {
        $list = db::select('SELECT * FROM ' . $this->Bac . '.bacc_proj WHERE `id` IN (SELECT `proj_id` FROM ' . $this->Bac . '.bacc_invitation_prebid)');
        return response()->json(new JsonResponse($list));
    }
    public function get_4prebid_conference()
    {
        $list = db::select('SELECT * FROM ' . $this->Bac . '.bacc_proj WHERE pre_bid IS NOT NULL;');
        return response()->json(new JsonResponse($list));
    }
    public function storeInvitationToBid(Request $request)
    {
        $data = $request->form;
        $step = $request->step;
        $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $data['bacc_proj_id'])->first();
        $stepDone = $chkstep->steps;
        if ($stepDone <= $step) {
            db::table($this->Bac . '.bacc_proj')->where('id', $data['bacc_proj_id'])->update(['steps' => ($stepDone * 1) + 1]);
        }
        db::table($this->Bac . '.bacc_2invitationtobid')->where('bacc_proj_id', $data['bacc_proj_id'])->delete();
        db::table($this->Bac . '.bacc_2invitationtobid')->insert($data);
        $datax = array(
            'posting' => $data['philgep'],
            'pre_bid' => $data['prebidd_conference'],
            'bid_opening' => $data['opening'],
            'itb_no' => $data['itb_no'],
        );
        db::table($this->Bac . '.bacc_proj')->where('id', $data['bacc_proj_id'])->update($datax);
    }
    public function store4_prebid(Request $request)
    {
        try {
            DB::beginTransaction();
            $form = $request->form;
            $step = $request->step;
            $bulletin = $request->bulletin;

            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->first();
            $stepDone = $chkstep->steps;
            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1, 'pre_bid' => $form['pre_bid'], 'bid_opening' => $form['bid_opening']]);
            }
            db::table($this->Bac . '.bacc_bid_bulletin')->where('proj_id', $form['id'])->delete();
            foreach ($bulletin as $key => $value) {
                $data = array(
                    'proj_id' => $form['id'],
                    'bull_no' => $value['bull_no'],
                    'attch_description' => $value['attch_description'],
                    'trans_type' => $value['trans_type'],
                );
                db::table($this->Bac . '.bacc_bid_bulletin')->insert($data);
            }
            $datax = db::table($this->Bac . '.bacc_bid_bulletin')
                ->select(db::raw('GROUP_CONCAT(`attch_description`) AS "rem"'))
                ->where('proj_id', $form['id'])->first();
            db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])
                ->update(['bid_bulletin' => $datax->rem]);

            db::table($this->Bac . '.bacc_2invitationtobid')->where('bacc_proj_id', $form['id'])->update(['prebidd_conference' => $form['pre_bid'], 'opening' => $form['bid_opening']]);
            db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['pre_bid' => $form['pre_bid'], 'bid_opening' => $form['bid_opening']]);
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function removedocs(Request $request)
    {
        db::table($this->Bac . '.bacc_pre_docs_entry')
            ->where('id', $request->id)
            ->update(['stat' => 1]);
    }
    public function store3invitation_to_observer_prebid(Request $request)
    {
        // log::debug( $request->observer);
        try {
            DB::beginTransaction();
            $observer =  $request->observer;
            $form = $request->form;
            $bac =  $request->bac;
            $step = $request->step;

            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->first();
            $stepDone = $chkstep->steps;
            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1]);
            }

            db::table($this->Bac . '.bacc_invitation_prebid')->where('proj_id', $form['id'])->delete();
            foreach ($observer as $key => $value) {

                $data = array(
                    'proj_id' => $form['id'],
                    'entry_type' => 'OBSERVER',
                    'business_id' => $value['id'],
                    'business_name' => $value['name'],
                    'organization' => $value['organization'],
                    'date_invitation' => $value['dateinvitation'],
                    'date_receipt' => $value['dateofreceipt'],
                );
                db::table($this->Bac . '.bacc_invitation_prebid')->insert($data);
            }
            foreach ($bac as $key => $value) {
                $data = array(
                    'proj_id' => $form['id'],
                    'entry_type' => 'BAC',
                    'business_id' => $value['pkID'],
                    'business_name' => $value['name'],
                    'date_invitation' => $value['dateinvitation'],
                    'date_receipt' => $value['dateofreceipt'],
                );
                db::table($this->Bac . '.bacc_invitation_prebid')->insert($data);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function store5invitation_to_bid_opening(Request $request)
    {
        try {
            DB::beginTransaction();
            $observer =  $request->observer;
            $form = $request->form;
            $bac =  $request->bac;
            $supplier =  $request->supplier;
            $step = $request->step;
            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->first();
            $stepDone = $chkstep->steps;
            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1]);
            }
            db::table($this->Bac . '.bacc_invitation_opening_bid')->where('proj_id', $form['id'])
                ->where('entry_type', '<>', 'SUPPLIER')
                ->delete();
            foreach ($observer as $key => $value) {

                $data = array(
                    'proj_id' => $form['id'],
                    'entry_type' => 'OBSERVER',
                    'business_id' => $value['id'],
                    'business_name' => $value['name'],
                    'date_invitation' => $value['dateinvitation'],
                    'date_receipt' => $value['dateofreceipt'],
                    'organization' => $value['organization'],
                );
                db::table($this->Bac . '.bacc_invitation_opening_bid')->insert($data);
            }
            // foreach ($supplier as $key => $value) {
            //     $data = array(
            //         'proj_id'=>$form['id'],
            //         'entry_type'=>'SUPPLIER',
            //         'business_id'=>$value['id'],
            //         'business_name'=>$value['name'],
            //         'date_invitation'=>$value['dateinvitation'],
            //         'date_receipt'=>$value['dateofreceipt'],
            //         'doc_amount'=>$value['doc_amount'],
            //         'itb_no'=>$value['itb_no']
            //     );
            //     $bill = db::select('call spl_display_setup_certification_permit_jay(?,?)',[]);
            //     db::table($this->Bac.'.bacc_invitation_opening_bid')->insert($data);
            // }
            foreach ($bac as $key => $value) {
                $data = array(
                    'proj_id' => $form['id'],
                    'entry_type' => 'BAC',
                    'business_id' => $value['pkID'],
                    'business_name' => $value['name'],
                    'date_invitation' => $value['dateinvitation'],
                    'date_receipt' => $value['dateofreceipt'],
                );
                db::table($this->Bac . '.bacc_invitation_opening_bid')->insert($data);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function getBacMembersForOpeningBIDSupplierStore(Request $request)
    {

        try {
            DB::beginTransaction();
            $form = $request->form;
            // log::debug($form['proj_id']);
            $data = array(
                'proj_id' => $form['proj_id'],
                'entry_type' => 'SUPPLIER',
                'business_id' => $form['business_id'],
                'business_name' => $form['name'],
                'date_invitation' => $form['dateinvitation'],
                'date_receipt' => $form['dateofreceipt'],
                'doc_amount' => $form['doc_amount'],
                'itb_no' => $form['itb_no']
            );
            $existing = db::table($this->lgu_db . '.ebplo_business_application')->where('business_number', $form['business_id'])->count();
            if ($existing == 0) {
                $busName =  db::table($this->lgu_db . '.ebplo_business_list')->where('business_number', $form['business_id'])->first();
                $insertBuss = array(
                    'business_number' => $form['business_id'],
                    'business_name' => $busName->business_name,
                    'trade_name' => $busName->business_name,
                    'business_address' => $busName->reference_address,
                    'email_address' => $busName->business_email_add,
                    'contact_no' => $busName->business_contact_no_temp,
                    'tax_year' => date("Y", strtotime($busName->business_date_started)),
                    'transaction_type' => 'Others'
                );
                db::table($this->lgu_db . '.ebplo_business_application')->insert($insertBuss);
            }
            db::table($this->Bac . '.bacc_invitation_opening_bid')->insert($data);
            $id = DB::getPDo()->lastInsertId();
            // log::debug($id);
            $bill = db::select("SELECT * FROM `qpsii_lgusystem`.`cto_income_account_list`
            INNER JOIN `bac_lgu`.`bacc_account_mapping`
            ON(bacc_account_mapping.`account_code` = cto_income_account_list.`income_account_code`)
            WHERE `bacc_account_mapping`.`entry_type` = 'BID DOCS'");
            foreach ($bill as $key => $value) {
                $billing = array(
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'payer_type' => "BUSINESS",
                    'transaction_type' => "Bid Docs",
                    'bill_number' => $id,
                    'payer_id' => $form['business_id'],
                    'business_application_id' => $form['business_id'],
                    'account_code' => $value->{'income_account_code_disp'},
                    'bill_description' => $value->{'income_account_description'},
                    'net_amount' => $form['doc_amount'],
                    'bill_amount' => $form['doc_amount'],
                    // 'status' => $value->{'Status'},
                );
                // log::debug($billing);
                DB::table($this->lgu_db . '.cto_general_billing')->insert($billing);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function getBacMembersForOpeningBIDSupplierRemove(Request $request)
    {

        try {
            DB::beginTransaction();
            $chk = db::table($this->lgu_db . '.cto_general_billing')
                ->where('transaction_type', 'Bid Docs')
                ->where('bill_id', $request->id)->get();
            foreach ($chk as $key => $value) {
                if ($value->status === 'UNPAID') {
                    db::table($this->lgu_db . '.cto_general_billing')
                        ->where('SysPK_general_billing', $value->SysPK_general_billing)
                        ->update(['status' => 'CANCELLED']);
                    db::table($this->Bac . '.bacc_invitation_opening_bid')
                        ->where('id', $request->id)
                        ->delete();
                } else {
                    return response()->json(new JsonResponse(['Message' => 'Transaction Already Paid! Not Allowed', 'status' => 'success']));
                }
            }
            db::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function get_7suplemental()
    {
        $list = db::table($this->Bac . '.bacc_7suplemental')->get();
        return response()->json(new JsonResponse($list));
    }
    public function get_15reso()
    {
        $list = db::table($this->Bac . '.bacc_15resolution')->get();
        return response()->json(new JsonResponse($list));
    }
    public function bacc_16noa(Request $request)
    {
        $list = db::table($this->Bac . '.bacc_noa')
            ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_noa.proj_id')
            ->whereRaw(" (`noa_issuance_date` like ? or `title_of_project` like ? or `business_name` like ? or `bidamount` like ? or `performance_amount` like ?)", ['%' . $request->filterdata . '%', '%' . $request->filterdata . '%', '%' . $request->filterdata . '%', '%' . $request->filterdata . '%', '%' . $request->filterdata . '%'])
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function bacc_ContractList(Request $request)
    {
        $list = db::table($this->Bac . '.bacc_contract')
            ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_contract.proj_id')
            // ->whereRaw(" (bacc_contract.contract_date like ? or bacc_proj.title_of_project like ? or bacc_contract.contract_cost like ? or bacc_contract.sp_resolution_no like ?)",['%' . $request->filterdata . '%', '%' . $request->filterdata . '%', '%' . $request->filterdata . '%', '%' . $request->filterdata . '%'])
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function bacc_NPTList(Request $request)
    {
        $list = db::table($this->Bac . '.bacc_ntp')
            ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_ntp.proj_id')
            ->whereRaw("(bacc_ntp.contract_duration like ? or bacc_ntp.ntp_issuance like ? or bacc_ntp.effectivity like ? or bacc_ntp.expected_completion like ? or bacc_ntp.remarks like ? or bacc_proj.title_of_project like ? or bacc_proj.proc_type like ?)", ['%' . $request->type . '%', '%' . $request->type . '%', '%' . $request->type . '%', '%' . $request->type . '%', '%' . $request->type . '%', '%' . $request->type . '%', '%' . $request->type . '%'])
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'PN';
        $table = $this->Bac . ".bacc_proj";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function store(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            DB::beginTransaction();
            if ($idx == 0) {
                $main['steps'] = 2;
                if (floatval($main['ABC']) > 5000000.00 && $main['proc_type'] === 'Infrastructure') {
                    $main['steps'] = 1;
                }
                if (floatval($main['ABC']) > 2000000.00 && $main['proc_type'] === 'Goods and Service') {
                    $main['steps'] = 1;
                }
                db::table($this->Bac . '.bacc_proj')->insert($main);
                $idx = $this->G->pk();
            } else {
                db::table($this->Bac . '.bacc_proj')->where('id', $idx)->update($main);
            }
            $dataMain = array(
                'mode_proc_id' => $main['mop_id'],
            );
            db::table($this->Proc . '.tbl_pr_main')->where('id', $idx)->update($dataMain);
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function ifdirectITB($id)
    {
        $list = db::table($this->Bac . '.bacc_proj')
            ->where('id', $id)
            ->select(db::raw("(CASE WHEN proc_type = 'Infrastructure' AND contract_cost > 5000000 THEN TRUE WHEN proc_type = 'Goods' AND contract_cost > 2000000 THEN TRUE WHEN proc_type = 'Consultancy' AND contract_cost > 1000000 THEN TRUE ELSE FALSE END) AS showss"))
            ->get();
        foreach ($list as $key => $value) {
            if ($value->showss === '0') {
                // log::debug($value->showss);
                db::table($this->Bac . '.bacc_proj')
                    ->where('id', '=', $id)
                    ->update(['steps' => 2]);
            }
        }
    }

    public function getSupplierInvitedRemove(Request $request)
    {
        db::table($this->Bac . '.bacc_bid_opening')
            ->where('proj_id', $request->proj_id)
            ->where('business_number', $request->id)
            ->delete();
        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    }
    public function getSupplierInvited(Request $request)
    {
        $proj_id = $request->id;
        $chk = db::table($this->Bac . '.bacc_bid_opening')
            ->select('proj_id')
            ->where('proj_id', $proj_id)->get();
        if (count($chk) > 0) {
            $list['observer'] = db::table($this->Bac . '.bacc_bid_opening')
                ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_bid_opening.proj_id')
                ->select('bacc_bid_opening.proj_id', 'security_type', 'security_remarks', 'business_number as id', 'or_date', 'or_no', 'bid_fees', 'bacc_proj.ABC as abc_amount', 'business_name as name', 'bidamount', 'bidsecurity', 'rating', 'bacc_bid_opening.remarks', 'winner')
                ->where('bacc_bid_opening.proj_id', $proj_id)
                ->get();
        } else {
            // asdasd
            $bill = DB::table($this->lgu_db . '.cto_general_billing')
                ->leftJoin($this->lgu_db . '.cto_or_transactions', 'cto_or_transactions.or_id', 'cto_general_billing.or_id')
                ->select('cto_general_billing.ref_id', 'or_number', 'or_date')
                ->where('cto_general_billing.bill_description', 'like', 'bid')
                ->where('cto_general_billing.status', '<>', 'CANCELLED');

            $list['observer'] = db::table($this->Bac . '.bacc_invitation_opening_bid')
                ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_invitation_opening_bid.proj_id')
                ->leftJoinSub($bill, 'bill', function ($join) {
                    $join->on('bacc_invitation_opening_bid.id', '=', 'bill.ref_id');
                })
                ->select('bacc_invitation_opening_bid.proj_id', 'bill.or_number as or_no', 'bill.or_date', 'bacc_invitation_opening_bid.doc_amount as bid_fees', 'bacc_proj.ABC as abc_amount', 'organization', 'business_id as id', 'business_name as name', db::raw('0 as bidsecurity'), db::raw('0 as bidamount'), db::raw('"" as security_type'), db::raw('"" as rating'), db::raw('"" as remarks'), 'winner')
                ->where('bacc_invitation_opening_bid.proj_id', $proj_id)
                ->where('entry_type', 'SUPPLIER')
                ->get();
        }
        return response()->json(new JsonResponse($list));
    }
    public function get5invitation_to_bid_opening(Request $request)
    {
        $list = db::select('SELECT
       bacc_proj.`id`,
       `ref_no`,
       `title_of_project`,
        GROUP_CONCAT(bacc_invitation_opening_bid.`business_name` SEPARATOR "<br/>") AS "supplier"
     FROM
       ' . $this->Bac . '.bacc_proj
       INNER JOIN ' . $this->Bac . '.bacc_invitation_opening_bid
         ON (
           bacc_proj.`id` = bacc_invitation_opening_bid.`proj_id`
         )
         WHERE `bacc_invitation_opening_bid`.`entry_type` = "SUPPLIER"
         GROUP BY bacc_proj.`id`');

        return response()->json(new JsonResponse($list));
    }
    public function store6bid_opening(Request $request)
    {
        try {
            // DB::beginTransaction();
            $observer =  $request->observer;
            $form = $request->form;
            $bac =  $request->bac;
            $step = $request->step;
            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->first();
            $stepDone = $chkstep->steps;
            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1]);
            }
            $chk =  db::table($this->Bac . '.bacc_bid_opening')->where('proj_id', $form['id'])
                ->where('winner', 1)->count();
            // log::debug($chk);
            if ($chk == 0) {
                db::table($this->Bac . '.bacc_bid_opening')->where('proj_id', $form['id'])->delete();
                foreach ($observer as $key => $value) {

                    // log::debug($form['id']);
                    // log::debug($value['id']);
                    // log::debug($value['name']);
                    // log::debug($value['security_type']);
                    // log::debug($value['bidsecurity']);
                    // log::debug($value['bid_fees']);
                    // log::debug($value['bidamount']);
                    // log::debug($value['rating']);
                    // log::debug($value['remarks']);
                    // log::debug($value['security_remarks']);
                    // log::debug($value['or_date']);
                    // log::debug($value['or_no']);
                    $data = array(
                        'proj_id' => $form['id'],
                        'business_number' => $value['id'],
                        'business_name' => $value['name'],
                        'security_type' => $value['security_type'],
                        'bidsecurity' =>  $value['bidsecurity'],
                        'bid_fees' =>  $value['bid_fees'],
                        'bidamount' =>  $value['bidamount'],
                        'rating' => $value['rating'],
                        'remarks' => $value['remarks'],
                        'security_remarks' => $value['security_remarks'],
                        'or_date' => $value['or_date'],
                        'or_no' => $value['or_no'],
                    );
                    log::debug($data);
                    db::table($this->Bac . '.bacc_bid_opening')->insert($data);
                }
            } else {
                foreach ($observer as $key => $value) {
                    $chk = db::table($this->Bac . '.bacc_bid_opening')
                        ->where('proj_id', $form['id'])
                        ->where('business_number', $value['id'])->get();
                    if (count($chk) == 0) {
                        $data = array(
                            'proj_id' => $form['id'],
                            'business_number' => $value['id'],
                            'business_name' => $value['name'],
                            'security_type' => $value['security_type'],
                            'bidsecurity' =>  $value['bidsecurity'],
                            'bid_fees' =>  $value['bid_fees'],
                            'bidamount' =>  $value['bidamount'],
                            'rating' => $value['rating'],
                            'remarks' => $value['remarks'],
                            'security_remarks' => $value['security_remarks'],
                            'or_date' => $value['or_date'],
                            'or_no' => $value['or_no'],
                        );
                        db::table($this->Bac . '.bacc_bid_opening')->insert($data);
                    } else {
                        $data = array(
                            'proj_id' => $form['id'],
                            'business_number' => $value['id'],
                            'business_name' => $value['name'],
                            'security_type' => $value['security_type'],
                            'bidsecurity' =>  $value['bidsecurity'],
                            'bid_fees' =>  $value['bid_fees'],
                            'bidamount' =>  $value['bidamount'],
                            'rating' => $value['rating'],
                            'remarks' => $value['remarks'],
                            'security_remarks' => $value['security_remarks'],
                            'or_date' => $value['or_date'],
                            'or_no' => $value['or_no'],
                        );
                        db::table($this->Bac . '.bacc_bid_opening')
                            ->where('proj_id', $form['id'])
                            ->where('business_number', $value['id'])
                            ->update($data);
                    }
                }
            }
            // DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function getBIDList()
    {
        $list =   db::select('SELECT
        bacc_proj.`id`,
        bacc_proj.bid_opening,
        `ref_no`,
        `title_of_project`,
         GROUP_CONCAT(CONCAT(bacc_bid_opening.`business_name`," - ",CAST(FORMAT(`bidamount`,2) AS CHAR(200))) SEPARATOR "<br/>") AS "supplier"
      FROM
        ' . $this->Bac . '.bacc_proj
        INNER JOIN ' . $this->Bac . '.bacc_bid_opening
          ON (
            bacc_proj.`id` = bacc_bid_opening.`proj_id`
          )
          GROUP BY bacc_proj.`id`');
        return response()->json(new JsonResponse($list));
    }
    public function getPOSTQUAList()
    {
        $list =   db::select('SELECT
        bacc_proj.`id`,
        bacc_proj.post_qua,
        `ref_no`,
        `title_of_project`,
         GROUP_CONCAT(CONCAT(bacc_bid_opening.`business_name`," - ",CAST(FORMAT(`bidamount`,2) AS CHAR(200))) SEPARATOR "<br/>") AS "supplier"
      FROM
        ' . $this->Bac . '.bacc_proj
        INNER JOIN ' . $this->Bac . '.bacc_bid_opening
          ON (
            bacc_proj.`id` = bacc_bid_opening.`proj_id`
          )
          where `winner` = 1
          GROUP BY bacc_proj.`id`');
        return response()->json(new JsonResponse($list));
    }
    public function getNoaWinner(Request $request)
    {
        $proj_id = $request->id;
        $chk = db::table($this->Bac . '.bacc_noa')
            ->select('proj_id')
            ->where('proj_id', $proj_id)->get();
        if (count($chk) > 0) {
            $list['observer'] = db::table($this->Bac . '.bacc_proj')
                ->join($this->Bac . '.bacc_noa', 'bacc_proj.id', '=', 'bacc_noa.proj_id')
                ->select('bacc_noa.*')
                ->where('bacc_noa.proj_id', $proj_id)
                ->get();
        } else {
            $list['observer'] = db::table($this->Bac . '.bacc_bid_opening')
                ->join($this->Bac . '.bacc_proj', 'bacc_proj.id', '=', 'bacc_bid_opening.proj_id')
                ->select(
                    'bacc_bid_opening.proj_id',
                    'business_number',
                    'business_name',
                    'bidamount',
                    'bacc_proj.proc_type',
                    db::raw('"" as noa_issuance_date'),
                    db::raw('"" as noa_receipt'),
                    db::raw('"" as deadline_dole_approved'),
                    db::raw('"0" as performance_amount'),
                    db::raw('"" as performance_date'),
                    db::raw('"" as dole_cshp'),
                    db::raw('"" as remarks')
                )
                ->where('bacc_bid_opening.proj_id', $proj_id)
                ->where('winner', '1')
                ->get();
        }

        return response()->json(new JsonResponse($list));
    }
    public function store7postqua(Request $request)
    {
        try {

            DB::beginTransaction();
            $observer =  $request->observer;
            $form = $request->form;
            $form2 =  $request->form2;
            $step = $request->step;
            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->first();
            $stepDone = $chkstep->steps;
            db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['post_qua' => $form['post_qua']]);
            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1, 'post_qua' => $form['post_qua']]);
            }
            db::table($this->Bac . '.bacc_invitation_opening_bid')
                ->where('proj_id', $form['id'])
                ->update(['winner' => 0]);
            db::table($this->Bac . '.bacc_bid_opening')
                ->where('proj_id', $form['id'])
                ->update(['winner' => 0]);
            foreach ($observer as $key => $value) {
                db::table($this->Bac . '.bacc_invitation_opening_bid')
                    ->where('proj_id', $form['id'])
                    ->where('business_id', $value['id'])
                    ->where('entry_type', 'OBSERVER')
                    ->update(['winner' => 1]);
                db::table($this->Bac . '.bacc_bid_opening')
                    ->where('proj_id', $form['id'])
                    ->where('business_number', $value['id'])
                    ->update(['winner' => 1]);

                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['winning_bidder' => $value['name'], 'bidder_id' => $value['id'], 'contract_cost' => $value['bidamount']]);
            }

            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function store8NOA(Request $request)
    {
        try {

            DB::beginTransaction();
            $form = $request->form;
            $proj_id = $form['id'];
            $observer =  $request->observer;
            // log::debug($observer);
            $step = $request->step;

            foreach ($observer as $key => $value) {

                $data = $value;
            }

            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->first();
            $stepDone = $chkstep->steps;
            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1]);
            }

            db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])
                ->update(['noa' => $data['noa_issuance_date'], 'perf_sec' => $data['performance_date']]);

            $chk = db::table($this->Bac . '.bacc_noa')
                ->where('proj_id', $proj_id)->count();

            if ($chk > 0) {
                db::table($this->Bac . '.bacc_noa')->where('proj_id', $proj_id)->update($data);
            } else {
                db::table($this->Bac . '.bacc_noa')->insert($data);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function getContract(Request $request)
    {
        $proj_id = $request->proj_id;
        $list = db::table($this->Bac . '.bacc_contract')
            ->where('proj_id', $proj_id)
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function store9Contract(Request $request)
    {
        try {
            //  log::debug($request);
            DB::beginTransaction();
            $form = $request->form;
            $proj_id = $form['id'];

            $entry =  $request->entry;
            $step = $request->step;

            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->get();
            foreach ($chkstep as $key => $value) {
                $stepDone = $value->steps;
            }


            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1]);
            }
            db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['contract_date' => $entry['contract_date'], 'sp_resolution' => $entry['session_date']]);

            $chk = db::table($this->Bac . '.bacc_contract')
                ->select('proj_id')
                ->where('proj_id', $proj_id)->get();
            if (count($chk) > 0) {
                db::table($this->Bac . '.bacc_contract')->where('proj_id', $proj_id)->update($entry);
            } else {
                db::table($this->Bac . '.bacc_contract')->insert($entry);
            }

            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function store10NTP(Request $request)
    {
        try {
            DB::beginTransaction();
            $form = $request->form;
            $proj_id = $form['id'];

            $entry =  $request->entry;
            $step = $request->step;

            $chkstep = db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->get();
            foreach ($chkstep as $key => $value) {
                $stepDone = $value->steps;
            }
            if ($stepDone <= $step) {
                db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['steps' => ($stepDone * 1) + 1]);
            }
            db::table($this->Bac . '.bacc_proj')->where('id', $form['id'])->update(['ntp_issuance' => $entry['ntp_issuance'], 'ntp_effective' => $entry['effectivity'], 'expected_date_completion' => $entry['expected_completion']]);

            $chk = db::table($this->Bac . '.bacc_ntp')
                ->select('proj_id')
                ->where('proj_id', $proj_id)->get();

            // log::debug($chk);
            if (count($chk) > 0) {
                db::table($this->Bac . '.bacc_ntp')->where('proj_id', $proj_id)->update($entry);
            } else {
                db::table($this->Bac . '.bacc_ntp')->insert($entry);
            }

            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getNTP(Request $request)
    {
        $proj_id = $request->proj_id;
        $list = db::table($this->Bac . '.bacc_ntp')
            ->where('proj_id', $proj_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function edit($id)
    {

        $list = db::table($this->Bac . '.bacc_proj')
            ->where('id', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function dones($id)
    {
        $list = db::table($this->Bac . '.bacc_proj')
            ->where('id', $id)
            ->update(['steps' => '12']);
        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Done.', 'status' => 'success']));
    }
    public function itbGetActivities($id)
    {
        $list = db::table($this->Bac . '.bacc_2invitationtobid_activities')
            ->where('bacc_proj_id', $id)->get();
        return response()->json(new JsonResponse($list));
    }
    public function storeDeclaration(Request $request)
    {
        $form = $request->itm;
        $forPrint = $request->forPrint;

        // $id = $form['id'];

        DB::table($this->Bac . '.bacc_proj_bid_declaration')
            ->where('bac_proj_id', $form['id'])
            ->delete();

        $datx = array(
            'bac_proj_id' => $form['id'],
            'declaration' => $forPrint['declaration'],

        );
        // db::table("marriagecert_wifeinfo")->insert($datx);

        DB::table($this->Bac . '.bacc_proj_bid_declaration')->insert($datx);
        $id = DB::getPdo()->LastInsertId();

        return  $this->G->success();
    }

    public function printITB(Request $request)
    {
        try {

            $data = $request->data;
            $b = $data['amountWordABC'];
            $mainData = "";
            $prDescription = DB::table($this->Bac . '.bacc_proj')
                ->leftjoin($this->Proc . '.pow_main_individual', 'pow_main_individual.id', 'bacc_proj.pow_id')
                ->leftjoin($this->Proc . '.tbl_pr_main', 'tbl_pr_main.pow_id', 'pow_main_individual.id')
                ->leftjoin($this->Proc . '.tbl_pr_detail', 'tbl_pr_detail.main_id', 'tbl_pr_main.id')
                ->where('bacc_proj.pow_id', $data['pow_id'])
                ->get();
            $mainData = "";
            foreach ($prDescription as $key => $value) {
                $mainData .= '  <tr>
                <td style="font-size:9pt">' . $value->item_name . '</td>
                <td style="font-size:9pt" align="center">' . $value->qty . '</td>
                <td style="font-size:9pt" align="center">' . $value->unit_measure . '</td>
            </tr>';
            }

            $activitieszx = db::table($this->Bac . '.bacc_proj')
                ->leftJoin($this->Bac . '.bacc_proj_bid_declaration', 'bacc_proj_bid_declaration.bac_proj_id', 'bacc_proj.id')
                // ->where('bacc_proj.id', $main['id'])
                ->get();
            $acitivity = "";

            foreach ($activitieszx as $key => $value) {
                $acitivity = $value;
            }



            $Template = '  <table width="100%">

            <tr>
                <td width="23%">
                    <table width="100%">
                        <tr>
                            <td style="font-size:6pt;" ><i>Standard Form Number: SF-CONS-04</i></td>
                        </tr>
                        <tr>
                            <td style="font-size:6pt;" ><i>Revised on: July, 2004</i></td>
                        </tr>
                        <tr>
                            <td style="font-size:6pt;"><i>Municipality of Dumanjug, Cebu</i></td>
                        </tr>
                    </table>

                </td>
                <td width="54%">
                    <table width="100%">
                    <tr>
                    <td align="center">
                        <img src="' . public_path() . '/img/logo1.png"  height="60" width="60">
                    </td>

                </tr>
                        <tr>
                             <td align="center" style="font-size:10pt;letter-spacing:3px" >
                            <b>INVITATION TO BID</b>
                                </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                           CITY OF TALISAY
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                          <b>' . (!empty($data['bid_opening']) ? (date_format(date_create($data['bid_opening']), "Y")) : "") . '</b>
                           <br />
                            </td>
                        </tr>


                    </table>
                </td>
                <td width="23%">
                    <table width="100%">
                    <tr>
                        <td width="45%" style="font-size:6pt;" ><i>Reference Number</i></td>
                        <td width="55%" style="font-size:6pt;border-bottom:1px solid black" >' . $data['ref_no'] . '</td>
                    </tr>
                    <tr>
                        <td width="100%" style="font-size:6pt;"><i>Procurement of GOODS</i></td>
                    </tr>
                    </table>
                </td>
                </tr>
            </table>
            ';



            $Template .= '<table width="100%">

                        <tr>
                            <td width="100%"></td>
                        </tr>

                        <tr>
                            <td width="100%">The Municipality of Dumanjug, through its Bids and Awards Committee (BAC), invits suppliers/contractors to apply
                            for eligiblity and to bid for the project titled below. Bids received in excess of the ABC shall be automatically rejectd at bid opening. </td>
                        </tr>


                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="15%">Name of Project:</td>
                            <td width="78%" style="font-size:10pt;" >' . $data['title_of_project'] . '</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="10%">
                                <table width="100%">
                                    <tr>
                                        <td style="font-size:6pt;" ></td>
                                    </tr>

                                </table>

                             </td>

                            <td width="80%">
                                <table width="100%" border="1" cellpadding="2">
                                    <tr>
                                        <td width="70%" style="font-size:9pt" align="center"><b>ITEM DESCRIPTION</b></td>
                                        <td width="15%" style="font-size:9pt" align="center"><b>QTY</b></td>
                                        <td width="15%" style="font-size:9pt" align="center"><b>UNIT</b></td>
                                    </tr>
                                    ' . $mainData . '

                                </table>
                            </td>
                            <td width="10%">
                                <table width="100%">
                                    <tr>
                                        <td style="font-size:6pt;"></td>
                                    </tr>
                             </table>

                             </td>
                        </tr>

                        <tr>
                            <td width="100%"></td>
                        </tr>

                        <tr>
                            <td width="7%"></td>
                            <td width="6%">ABC: </td>
                            <td width="87%" style="font-size:9pt;" >' . strtoupper($b) . ' (' . number_format($data['ABC'], 2) . '):</td>

                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="20%">Purchase Request No.     : </td>
                            <td width="20%" style="font-size:9pt;" >' . $data['pr_no'] . '</td>
                            <td width="20%">Contract Duration     : </td>
                            <td width="20%" style="font-size:9pt;" >' . $data['contract_duration'] . '</td>
                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="20%">Source of Fund     : </td>
                            <td width="20%" style="font-size:9pt;" >' . $data['SOF'] . '</td>
                            <td width="20%">Bid Document Payment     : </td>
                            <td width="20%" style="font-size:9pt;" >' . $data['bid_fees'] . '</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">The list of minimum requirements is indicated in the Eligibility Form. Eligiblity of prospective bidder shall be checked
                            using non-discretionary "pass/fail" criteria. Bids whose technical proposals pass the minimum technical rating of 100% shall have its financial
                            proposals opened and evaluated. The technical proposals shall carry 100% weight in the bid evaluation.</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">All particulars relative to Eligibility Statement and Screening, Bid Security, Performance Security, Pre-Bidding Conference(s),
                            Evaluation of Bids, Post-Qualification and Award of Contract shall be governed by the pertinent provisions or R.A. 9184 and its Revised Implementing
                            Rules and Regulation (IRR)</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">The complete schedule of activities is listed as follows:</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="43%"><b>1. Posting and Issuance of Bid Documents</b></td>
                            <td width="50%" align="right"> <b>' . (!empty($data['posting']) ? (date_format(date_create($data['posting']), "F d, Y")) : "") . '</b></td>
                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="43%"><b>2. Pre-Bid BAC Conference Room</b></td>
                            <td width="50%" align="right"><b>' . (!empty($data['prebidd_conference']) ? (date_format(date_create($data['prebidd_conference']), "F d,Y")) : "") . ' @ 10:00 a.m</b></td>
                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="43%"><b>3. Dropping and Opening of Bids and BAC Room</b></td>
                            <td width="50%" align="right"><b> ' . (!empty($data['bid_opening']) ? (date_format(date_create($data['bid_opening']), "F d,Y")) : "") . ' @ 10:00 a.m</b></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">Bids Documents will be available upon payment of a non-refundable amount to the City Trasurer\'s Office,
                            City Hall Building, Municipality of Dumanjug, Cebu.</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">The bid security shall be in an amount equal to a percentage of the ABC in accordance with the following schedule:
                            Form of Bid Security Amount of Bid Security (Equal to Percentage of the ABC) a) Cash, cashie\'s/manager\'s check, bank draft/guarantee confirmed
                            by a Universal or Commercial Bank b) Irrevocable letter of credit issued by a Universal or Commercial Bank, if issued by a foreign bank c) Surety
                            bond callable upon demand issued by a surety or insurance company duly certified by the Insurance Commision as authorized to issue such security: Five
                            percent (5%); d) Any combination of the foregoing. Proportionate to share of form with respect to total amount of security.</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">The Bids and Awards Committee of the Municipality of Dumanjug reserves the right to reject documents which do not comply
                            with the requirements, waive any formalities of documents or consider any submission of documents as substantial compliance, reject any
                            and all bids, declare failure of bidding, annul the bidding process, or not award the contract, The Municipality of Dumanjug assumes no responsibility whatsoever to
                            compensate to indemnify bidders for any expenses incurred in the preparation of the bid.
                            </td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="25%"><i>Posting Schedule:</i></td>
                            <td width="18%"></td>
                            <td width="50%" align="center">Approved by:</td>
                        </tr>
                        <tr>
                            <td width="7%"></td>
                            <td width="25%">Bulletin Board</td>
                            <td width="63%">' . (!empty($data['posting']) ? (date_format(date_create($data['posting']), "F d - ")) : "") . (!empty($data['bid_opening']) ? (date_format(date_create($data['bid_opening']), "F d, Y")) : "") . '</td>
                        </tr>
                        <tr>
                            <td width="50%"></td>
                            <td width="20%"></td>
                            <td width="30%" style="border-bottom:1px solid black" align="left"><b>EDGAR M. MABUNAY</b></td>
                        </tr>
                        <tr>
                            <td width="50%"></td>
                            <td width="20%"></td>
                            <td width="30%" style="border-bottom:1px solid black" align="left">City Budget Officer/BAC Chairman</td>
                        </tr>


            </table>';










            PDF::SetTitle('Invitation to Bid');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('P', array(215.9, 279.4));
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printNTP(Request $request)
    {
        try {

            $data = $request->data;

            $mainData = "";
            $prDescription = DB::table($this->Bac . '.bacc_proj')
                ->leftjoin($this->Bac . '.bacc_2invitationtobid', 'bacc_2invitationtobid.bacc_proj_id', 'bacc_proj.id')
                ->leftjoin($this->Proc . '.pow_main_individual', 'pow_main_individual.id', 'bacc_proj.pow_id')
                ->leftjoin($this->Proc . '.tbl_pr_main', 'tbl_pr_main.pow_id', 'pow_main_individual.id')
                ->where($this->Proc . '.tbl_pr_main.pr_no', Auth::user()->Employee_id)
                // ->where('stat', 0)
                ->get();

            $Template = '  <table width="100%">

            <tr>
                <td width="25%" align="right">
                <img src="' . public_path() . '/img/logo1.png"  height="90" width="90">
                </td>
                <td width="65%">
                    <table width="100%">
                    <tr>
                    <td align="center">

                    </td>

                </tr>
                        <tr>
                             <td align="center" style="font-size:10pt;" >
                             Republic of the Philippines
                                </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                            Province of Cebu
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                            Municipality of Dumanjug
                           <br />
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:11pt;color:blue">
                            <b>OFFICE OF THE BIDS AND AWARDS COMMITTEE</b>
                            </td>
                        </tr>

                    </table>
                </td>
                <td width="10%">
                </td>
                </tr>
            </table>
            ';

            $Template .= '<table width="100%" cellpadding="2">

                <tr>
                    <td width="100%" style="border-bottom:2px solid black"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="20%" align="center">Date</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="90%"> The Contract therefore having been approved, copy of which will be furnished, NOTICE is hereby given:</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>



                <tr>
                    <td width="100%" style="font-size:15pt" align="center"><b><u>NOTICE TO PROCEED</u></b></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:9pt">Name of Contractor :</td>
                    <td width="65%" style="font-size:10pt;border-bottom:1px solid black"><b>' . $data['winning_bidder'] . '</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:9pt">Address :</td>
                    <td width="65%" style="font-size:10pt;border-bottom:1px solid black">' . $data['project_loc'] . '</td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:9pt">Project Description :</td>
                    <td width="65%" style="font-size:10pt;border-bottom:1px solid black"><b>' . $data['title_of_project'] . '</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:9pt">Contact Duration :</td>
                    <td width="65%" style="font-size:10pt;border-bottom:1px solid black"><b>' . $data['contract_duration'] . '</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:9pt">Purchase Request No. :</td>
                    <td width="65%" style="font-size:10pt;border-bottom:1px solid black"><b>' . $data['pr_no'] . '</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:9pt">BAC No. :</td>
                    <td width="65%" style="font-size:10pt;border-bottom:1px solid black"><b>' . $data['sp_resolution_no'] . '</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:9pt">Location of Project :</td>
                    <td width="65%" style="font-size:10pt;border-bottom:1px solid black"><b>at ' . $data['project_loc'] . '</b></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="42%" style="font-size:9pt"> That the work on this project may be commenced from</td>
                    <td width="35%" style="font-size:9pt;border-bottom:1px solid black"></td>
                    <td width="13%" style="font-size:9pt"> and the </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size:9pt"> CONTRACT TIME or the number of days allowable under this Contract will be counted Ten (10) days after the date of this notice.</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="43%"></td>
                    <td width="57%" style="font-size:9pt">Approved by:</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="55%"></td>
                    <td width="57%" style="font-size:10pt"><b>Hon. EFREN GUNTRANO Z. GICA</b></td>
                </tr>
                <tr>
                    <td width="55%"></td>
                    <td width="30%" style="font-size:9pt" align="center">Municipality Mayor</td>
                    <td width="15%"></td>
                </tr>
                <tr>
                    <td width="55%"></td>
                    <td width="30%" style="font-size:9pt" align="center">Municipality of Dumanjug</td>
                    <td width="15%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%" style="font-size:9pt">Received by:</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="30%" style="font-size:9pt;border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="30%" style="font-size:9pt" align="center">Signature over Printed Name</td>
                </tr>

                <tr>
                    <td width="100%"></td>
                </tr>

                <tr>
                    <td width="30%" style="font-size:9pt;border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="30%" style="font-size:9pt" align="center">Date Received</td>
                </tr>



            </table>';








            PDF::SetTitle('Notice To Proceed');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('P', array(215.9, 279.4));
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }





    // public function printITB1(Request $request)
    // {
    //     try {

    //         $data = $request->data;

    //         // log::debug($data);
    //         // $decl = $request->decl;
    //         // $id = $main['id'];
    //         // $projectx = db::table($this->Bac . '.bacc_proj')
    //         //     ->leftJoin($this->Bac . '.bacc_proj_bid_declaration', 'bacc_proj_bid_declaration.bac_proj_id', 'bacc_proj.id')
    //         //     ->where('bacc_proj.id', $main['id'])
    //         //     ->get();
    //         // $projectDatax = "";\

    //         $prDescription= DB::table($this->Bac . '.bacc_proj')
    //         ->leftjoin($this->Bac .'.bacc_2invitationtobid', 'bacc_2invitationtobid.bacc_proj_id','bacc_proj.id' )
    //         ->leftjoin($this->Proc .'.pow_main_individual', 'pow_main_individual.id', 'bacc_proj.pow_id')
    //         ->leftjoin($this->Proc .'.tbl_pr_main', 'tbl_pr_main.pow_id', 'pow_main_individual.id')
    //         ->where($this->Proc .'.tbl_pr_main.pr_no')
    //         // ->where('stat', 0)
    //         ->get();
    //         // $prDescription = DB::table($this->Proc .'.tbl_pr_main')
    //         // ->lefjoin($this->Bac .'tbl_pr_detail','tbl_pr_detail.main_id','tbl_pr_main.id')
    //         // ->where('.tbl_pr_main.pr_no',Auth::user()->Employee_id)
    //         // ->get();
    //         $mainData="";
    //         foreach ($prDescription as $key => $value) {
    //             $mainData.='  <tr>
    //             <td style="font-size:9pt">'.$value->pr_description.'</td>
    //             <td style="font-size:9pt" align="center">'.$value->qty.'</td>
    //             <td style="font-size:9pt" align="center">'.$value->unit_measure.'</td>
    //         </tr>';
    //         }
    //         if (count($prDescription)<3) {
    //             for ($i=count($prDescription); $i < 3; $i++) {
    //               $mainData.=' <tr>
    //               <td style="font-size:9pt"></td>
    //               <td style="font-size:9pt" align="center">1</td>
    //               <td style="font-size:9pt" align="center">UNIT</td>
    //           </tr> ';
    //             }
    //           }


    //           log::debug($mainData);

    //         $Template = '  <table width="100%">

    //         <tr>
    //             <td width="23%">
    //                 <table width="100%">
    //                     <tr>
    //                         <td style="font-size:6pt;" ><i>Standard Form Number: SF-CONS-04</i></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="font-size:6pt;" ><i>Revised on: July, 2004</i></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="font-size:6pt;"><i>Talisay City, Cebu</i></td>
    //                     </tr>
    //                 </table>

    //             </td>
    //             <td width="54%">
    //                 <table width="100%">
    //                 <tr>
    //                 <td align="center">
    //                     <img src="' . public_path() . '/img/logonaga.png"  height="60" width="60">
    //                 </td>

    //             </tr>
    //                     <tr>
    //                          <td align="center" style="font-size:10pt;letter-spacing:3px" >
    //                         <b>INVITATION TO BID</b>
    //                             </td>
    //                     </tr>
    //                     <tr>
    //                         <td align="center" style="font-size:10pt;" >
    //                        CITY OF TALISAY
    //                         </td>
    //                     </tr>
    //                     <tr>
    //                         <td align="center" style="font-size:10pt;" >
    //                       <b>2023</b>
    //                        <br />
    //                         </td>
    //                     </tr>


    //                 </table>
    //             </td>
    //             <td width="23%">
    //                 <table width="100%">
    //                 <tr>
    //                     <td width="45%" style="font-size:6pt;" ><i>Reference Number</i></td>
    //                     <td width="55%" style="font-size:6pt;border-bottom:1px solid black" ></td>
    //                 </tr>
    //                 <tr>
    //                     <td width="100%" style="font-size:6pt;"><i>Procurement of GOODS</i></td>
    //                 </tr>
    //                 </table>
    //             </td>
    //             </tr>
    //         </table>
    //         ';








    //         PDF::SetTitle('Invitation to Bid');
    //         PDF::SetFont('helvetica', '', 9);
    //         PDF::AddPage('P', array(215.9, 279.4));
    //         PDF::writeHTML($Template, true, 0, true, 0);
    //         PDF::Output(public_path() . '/prints.pdf', 'F');
    //         $full_path = public_path() . '/prints.pdf';
    //         if (\File::exists(public_path() . '/prints.pdf')) {
    //             $file = \File::get($full_path);
    //             $type = \File::mimeType($full_path);
    //             $response = \Response::make($file, 200);
    //             $response->header("Content-Type", $type);
    //             return $response;
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    //     }
    // }



    public function printAbstractBid(Request $request)
    {
        try {

            $main = $request->itm;
            // $decl = $request->decl;
            $id = $main['id'];
            $projectx = db::table($this->Bac . '.bacc_proj')
                ->leftJoin($this->Bac . '.bacc_proj_bid_declaration', 'bacc_proj_bid_declaration.bac_proj_id', 'bacc_proj.id')
                ->where('bacc_proj.id', $main['id'])
                ->get();
            $projectDatax = "";

            foreach ($projectx as $key => $value) {
                $projectDatax = $value;
            }

            $observer = db::table($this->Bac . '.bacc_invitation_prebid')
                ->select('proj_id', 'business_id as id', 'business_name as name', 'organization', 'date_invitation as dateinvitation', 'date_receipt as dateofreceipt')
                ->where('proj_id', $main['id'])
                ->where('entry_type', 'OBSERVER')
                ->get();

            $observe = "";
            $organization = "";
            // $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            // echo $f->format(1432);

            // $numberInput = "";
            foreach ($observer as $key => $value) {
                // if(isset($value->ABC)){
                //     $numberInput = $value->ABC;
                //     $locale = 'en_US';
                //     $fmt = numfmt_create($locale, NumberFormatter::SPELLOUT);
                //     $in_words = numfmt_format($fmt, $numberInput);
                //     echo $in_words;
                // }

                $observe .= '  <tr>
                        <td width="20%" align="center" style="font-size:10pt;"><b><u>' . $value->name . '</u></b></td>
                        <td width="5%" style="font-size:10pt;"></td>
                    </tr>';
                $organization .= '  <tr>
                        <td width="20%" align="center" style="font-size:10pt;">' . $value->organization . '</td>
                        <td width="5%" style="font-size:10pt;"></td>
                    </tr>';
            }

            // $present = db::table($this->Bac . '.bac_attendancemember')
            //     ->where('bac_attendancemember.category', 'member')
            //     ->where('bac_attendancemember.present', 'true')
            //     ->where('mem_id', $main['id'])
            //     ->get();

            // $presentx = "";
            // foreach ($present as $key => $value) {
            //     $presentx .= '  <tr>
            //         <td width="20%" style="font-size:8pt;"></td>
            //         <td width="35%" style="font-size:8pt;">' . $value->emp_name . '</td>
            //         <td width="3%" style="font-size:8pt;"></td>
            //         <td width="35%" style="font-size:8pt;">' . $value->position . '</td>

            //     </tr>';
            // }


            // $present = db::table($this->Bac . '.bac_attendancemember')
            //     ->where('bac_attendancemember.category', 'TWG')
            //     ->where('bac_attendancemember.present', 'true')
            //     ->where('mem_id', $main['id'])
            //     ->get();

            // $TWG = "";
            // foreach ($present as $key => $value) {
            //     $TWG .= '
            //     <tr>
            //     <td width="20%" style="font-size:8pt;"></td>
            //     <td width="35%" style="font-size:8pt;">' . $value->emp_name . '</td>
            //     <td width="3%" style="font-size:8pt;"></td>
            //     <td width="35%" style="font-size:8pt;">' . $value->position . '</td>
            // </tr>';
            // }

            // $present = db::table($this->Bac . '.bac_attendancemember')
            //     ->where('bac_attendancemember.present', 'false')
            //     ->where('mem_id', $main['id'])
            //     ->get();

            // $absent = "";
            // foreach ($present as $key => $value) {
            //     $absent .= '
            //     <tr>
            //     <td width="20%" style="font-size:8pt;"></td>
            //     <td width="35%" style="font-size:8pt;">' . $value->emp_name . '</td>
            //     <td width="3%" style="font-size:8pt;"></td>
            //     <td width="35%" style="font-size:8pt;">' . $value->position . '</td>
            // </tr>';
            // }

            // $data = db::select('call ' . $this->Bac . '.rans_bacc_resolutions_per_prPrint(?)', [$id]);
            // db::table($this->Bac . '.bacc_resolution_details')
            // ->join($this->Proc . '.tbl_pr_main', 'tbl_pr_main.id', 'bacc_resolution_details.pr_id')
            // ->leftjoin($this->Proc . '.tbl_pr_detail', 'tbl_pr_detail.main_id', 'tbl_pr_main.id')

            // // ->select('*' , 'unit_cost + total_cost as Total' )
            // ->select("*", db::raw("SUM(total_cost) as Total"), db::raw("(pr_ref) AS 'PR'"))
            // ->where('resolution_id', $main['id'])
            // ->groupBy('bacc_resolution_details.id')
            // ->get();
            // $total = $value->unit_cost + $value->total_cost;
            // $tableData = "";
            // $department = "";
            // foreach ($data as $key => $value) {
            //     log::debug($value->item_name);
            //     if ($department !== $value->department) {
            //         $tableData .= '<tr>
            //         <td rowspan="'. $value->countx.'" width="15%" style="font-size:9pt;" align="center"><b><br />' . $value->department . '<br /></b></td>
            //         <td width="55%" align="left" style="font-size:8pt;"><br />' . $value->item_name . '</td>
            //         <td width="15%" align="center" style="font-size:8pt;">' . $value->PR . '</td>
            //         <td width="15%" align="right" style="font-size:8pt;">' . number_format($value->total_cost, 2) . '</td>
            //         </tr>';
            //     } else {

            //     $tableData .= ' <tr>
            //     <td width="55%" align="left"><br />' . $value->item_name . '</td>
            //     <td width="15%" align="center">' . $value->PR . '</td>
            //     <td width="15%" align="right">' . number_format($value->total_cost, 2) . '</td>
            //     </tr>';
            // }
            //     $department = $value->department;
            // }

            // log::debug(2);
            // $tableData1 = "";

            // foreach ($data as $key => $valuez) {

            //     $tableData1 .= ' <tr>
            //     <td><br />' . $valuez->item_name . '</td>
            //     <td align="center">' . $valuez->PR . '</td>
            //     <td align="right">' . number_format($valuez->total_cost, 2) . '</td>
            // </tr>
            //     ';
            // }
            // log::debug(3);
            // $names = "";
            // $dataN = db::table($this->Bac . '.bac_members')

            //     ->where('bac_members.sig', '>', 0)
            //     ->orderBy('sig', 'ASC')
            //     ->get();

            // $name1 = "";
            // $position1 = "";
            // $name2 = "";
            // $position2 = "";
            // $name3 = "";
            // $position3 = "";
            // $name4 = "";
            // $position4 = "";
            // $name5 = "";
            // $position5 = "";
            // log::debug(1);
            // foreach ($dataN as $key => $valuez) {

            //     if ($key === 0) {
            //         $name1 = $valuez->emp_name;
            //         $position1 = $valuez->sig_position;
            //     }
            //     if ($key === 1) {

            //         $name2 = $valuez->emp_name;
            //         $position2 = $valuez->sig_position;
            //     }
            //     if ($key === 2) {

            //         $name3 = $valuez->emp_name;
            //         $position3 = $valuez->sig_position;
            //     }
            //     if ($key === 3) {

            //         $name4 = $valuez->emp_name;
            //         $position4 = $valuez->sig_position;
            //     }
            //     if ($key === 4) {

            //         $name5 = $valuez->emp_name;
            //         $position5 = $valuez->sig_position;
            //     }
            // }


            // foreach ($dataN as $key => $valuez) {

            //     $names .='    <tr>
            //     <td width="15%" style="font-size:9pt;">

            //     </td>
            //     <td width="38.5%" style="font-size:9pt;">
            //    <b> '.$valuez->emp_name.' </b>
            //     </td>
            //     <td width="8%" style="font-size:9pt;">

            //     </td>
            //     <td width="38.5%" style="font-size:9pt;">
            //     <b> '.$valuez->emp_name.' </b>
            //      </td>
            // </tr>

            //     ';
            // }


            $Template = '  <table width="100%">

            <tr>
                <td width="19%">

                </td>
                <td width="62%">
                    <table width="100%">
                    <tr>
                    <td align="center">
                        <img src="' . public_path() . '/img/logonaga.png"  height="30" width="30">
                    </td>

                </tr>
                        <tr>
                             <td align="center" style="font-size:10pt;" >
                             Republic of the Philippines
                                </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                            Province of Cebu
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                           City of Naga
                           <br />
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:11pt;">
                            <b>ABSTRACT OF BIDS</b>
                            </td>
                        </tr>

                    </table>
                </td>
                <td width="19%">
                </td>
                </tr>
            </table>
            ';

            $Template .= ' <table width="100%">
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <tr>
                                            <td width="8%" style="font-size:10pt;" align="left">Project Title: </td>
                                            <td width="48%" style="font-size:10pt;" align="left"><b><u> "' . $projectDatax->title_of_project . '" </u></b></td>
                                            <td width="4%" style="font-size:10pt;" align="left"></td>
                                            <td width="40%" style="font-size:10pt;" align="left">Reference No.: <b><u> ' . $projectDatax->itb_no . ' </u></b><br /> Date and Place of Bid: Opening: <b><u>' . $projectDatax->bid_opening . '</u></b></td>
                                        </tr>
                                        <tr>
                                            <td width="100% style="font-size:1pt;""></td>
                                        </tr>
                                        <tr>
                                            <td width="100%" style="font-size:10pt;" align="left">Approved Budget for the Contract: <b><u>P ' . number_format($projectDatax->ABC, 2) . '</u></b></td>
                                        </tr>
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <table width="100%" border="1" cellpadding="2">
                                            <tr>
                                                <td width="20%" align="center"><b> NAME OF BIDDER </b></td>
                                                <td width="80%" align="left"><b> ' . $projectDatax->winning_bidder . ' </b></td>
                                            </tr>
                                            <tr>
                                                <td width="20%" align="center"><b> Total Amount of Bid </b></td>
                                                <td width="80%" align="left"><b>P ' . number_format($projectDatax->ABC, 2) . ' </b></td>
                                            </tr>
                                            <tr>
                                                <td width="20%" align="center"><b>  </b></td>
                                                <td width="80%" align="left"><b> ' . $this->G->numberTowords_W_dec($projectDatax->ABC) . ' </b></td>
                                            </tr>
                                            <tr>
                                                <td width="20%" align="center"><b>  </b></td>
                                                <td width="80%" align="left"><b></b></td>
                                            </tr>
                                            <tr>
                                                <td width="20%" align="center"><b> Bid Securing Declaration </b></td>
                                                <td width="80%" align="left">' . $projectDatax->declaration . ' </td>
                                            </tr>

                                        </table>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="20%" align="center" style="font-size:10pt; border-bottom: 0.3px solid black"><b></b></td>
                                            <td width="80%" align="left"></td>
                                        </tr>
                                        <tr>
                                            <td width="20%" align="left" style="font-size:10pt">BAC-TWG</td>
                                            <td width="80%" align="left"></td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="20%" align="center" style="font-size:10pt"><b>ENGR. JOVENO  C. GARCIA</b></td>
                                            <td width="20%" align="center" style="font-size:10pt"><b>ENGR. ARTHUR  S. VILLAMOR</b></td>
                                            <td width="20%" align="center" style="font-size:10pt"><b>ENGR. MA. ALPHA P. ALOJADO</b></td>
                                            <td width="20%" align="center" style="font-size:10pt"><b>CERTERIA V. BUENAVISTA</b></td>
                                            <td width="20%" align="center" style="font-size:10pt"><b>FLORDELIS L. ABABA</b></td>
                                        </tr>
                                        <tr>
                                            <td width="20%" align="center" style="font-size:10pt">BAC Chairperson</td>
                                            <td width="20%" align="center" style="font-size:10pt">BAC Vice-Chairperson</td>
                                            <td width="20%" align="center" style="font-size:10pt">BAC Member</td>
                                            <td width="20%" align="center" style="font-size:10pt">BAC Member</td>
                                            <td width="20%" align="center" style="font-size:10pt">BAC Member</td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"><b>OBSERVERS:</b> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>

                                        ' . $observe . '
                                        ' . $organization . '


            </table>';
            PDF::SetTitle('Abstract of Bids');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('L');
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printBidOut(Request $request)
    {
        try {


            $data = $request->data;
            $filter =  $request->filter;
            // $direction = $data['direction'];
            // $location = $data['position'];
            // log::debug($data);
            PDF::AddPage('L');
            PDF::SetTitle('Bidout');
            // PDF::SetHeaderMargin(2);
            // PDF::SetTopMargin(2);
            // PDF::SetMargins(2, 2, 2, 2);
            PDF::SetFont('Helvetica', '', 8);
            // -- set new background ---
            // $bMargin = PDF::getBreakMargin();
            // $auto_page_break = PDF::getAutoPageBreak();
            // PDF::SetAutoPageBreak(false, 0);
            // PDF::SetAutoPageBreak($auto_page_break, $bMargin);

            // PDF::setPageMark();
            // PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);

            $head = "FDP Form 10b - Bid Result on Good and Services<br/>Note: Bid Results are three(3) separate forms, particularly, for Civil (Form 10a-CW), Goods and Services (Form 10b-GS) and Consulting Services
            (form 10c-cs).<br/> if there is no bidded project, good and services for the quarter, the forms must still be summitted with the said notation and signed according.";
            $qtr = '';
            if ($filter['quarter'] === "1") {
                $qtr = '1st Quarter';
            } elseif ($filter['quarter'] === "2") {
                $qtr = '2nd Quarter';
            } elseif ($filter['quarter'] === "3") {
                $qtr = '3rd Quarter';
            } elseif ($filter['quarter'] === "4") {
                $qtr = '4th Quarter';
            }
            $year = date("Y", strtotime($filter['year']));

            $title = "";
            if ($filter['type'] === 'Infrastructure') {
                $title = "CIVIL WORKS BID-OUT";
            } elseif ($filter['type'] === "Goods and Services") {
                $title = 'GOODS AND SERVICES BID-OUT';
            }
            $quarterly = "" . $qtr . ", CY " . $year . "";
            $header = '
            ' . $head . '
            <br/>
            <br/>
            <br/>
            <table style="width=100%;">
            <tr>
            <th align="right">
            <img src="' . public_path() . '/images/Logo1.png"  height="60" width="60">
            </th>
            <th style="font-size:9pt;" align="center">
           <b>GOODS AND SERVICES</b>

            </th>
            <th align="left">
             <img src="' . public_path() . '/images/Logo2.png"  height="60" width="75">
            </th>
            </tr>
                <tr>
                    <td width="10%"><b>REGION</b></td>
                    <td width="3%"><b>:</b></td>
                    <td width="37%"><b>REGION VII - CETNRAL VISAYAS</b></td>
                    <td width="40%" align="right"><b>CALENDAR YEAR:</b></td>
                    <td width="10%"><b>2023</b></td>
                </tr>
                <tr>
                    <td width="10%"><b>PROVINCE</b></td>
                    <td width="3%"><b>:</b></td>
                    <td width="37%"><b>CEBU</b></td>
                    <td width="40%" align="right"><b>QUARTER:</b></td>
                    <td width="10%"><b>2nd</b></td>
                </tr>
                <tr>
                    <td width="10%"><b>CITY</b></td>
                    <td width="3%"><b>:</b></td>
                    <td width="37%"><b>CITY OF TALISAY</b></td>
                    <td width="40%" align="right"><b></b></td>
                    <td width="10%"><b></b></td>
                </tr>
            </table> <br/><br/>';

            $Template = '
            ' . $header . '


            <table width="100%" border="1" cellpadding="2"  >
              <tr align="center">
                <th width="3%" >No.</th>
                <th width="8%" >Reference No.</th>
                <th width="32%" >Name of Project</th>
                <th width="7%">Approved Budget for Contract</th>
                <th width="12%">Winning Bidder</th>
                <th width="19%">Name and Address</th>
                <th width="8%">Bid Amount</th>
                <th width="8%">Bidding Date</th>
              </tr>
              <tbody>';
            foreach ($data as $key => $value) {
                $row = $key + 1;
                $bidDate = date("d-M-y", strtotime($value['bid_opening']));
                // log::debug($bidDate);
                $Template .= '<tr align="center" >
                <td>' . $row . '</td>
                <td>' . $value['ref'] . '<br/>ITB No. ' . $value['itb_no'] . '</td>
                <td>' . $value['title_of_project'] . '</td>
                <td>' . number_format($value['ABC'], 2) . '</td>
                <td>' . $value['business_name'] . '</td>
                <td>' . $value['reference_owner_name'] . '<br/>' . $value['reference_address'] . '</td>
                <td>' . number_format($value['contract_cost'], 2) . '</td>
                <td>' . $bidDate . '</td>
              </tr>';
            }
            $Template .= '</tbody></table><br><br>';

            $Template .= 'We hereby certify that we have reviewed the contents and hereby attest to the veracity and correctness of the data or information contained in this document. <br/><br/><br/><br/><br/><br/>';

            $Template .= '<table width ="100%" >
             <tr>
                <td width="10%"></td>
                <td width="30%" style="border-bottom:1px solid black; font-size:9pt" align="center"><b>ROY R. ALCOSEBA</b></td>
                <td width="20%"></td>
                <td width="30%" style="border-bottom:1px solid black; font-size:9pt" align="center"><b>EDGAR M. MABUNAY</b></td>
                <td width="10%"></td>
             </tr>
             <tr>
                <td width="10%"></td>
                <td width="30%" align="center"><i>BAC Secretariat Head (Assitant City Budget Officer)</i></td>
                <td width="20%"></td>
                <td width="30%" align="center"><i>Chairman (City Budget Officer)</i></td>
                <td width="10%"></td>
            </tr>
            </table>';

            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            // log::debug($e);
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function PrintBidOut2(Request $request)
    {
        try {



            $Template = '<table width="100%" cellpadding="2">
                <tr>
                    <td><b>FDP Form 10b - Bid Result on Good and Services</b></td>
                </tr>
                <tr>
                    <td>Note: Bid Results are three(3) separate forms, particularly, for Civil (Form 10a-CW), Goods and Services (Form 10b-GS) and Consulting Services
                    (form 10c-cs)</td>
                </tr>
                <tr>
                    <td>If there is no bidded project, good and services for the quarter, the forms must still be summitted with the said notation and signed accordingly.</td>
                </tr>
                <br/>
                <tr>
                    <td align="center" style="font-size:10pt"><b>GOODS AND SERVICES BID-OUT</b></td>
                </tr>
                <br/>
                <tr>
                    <td width="10%"><b>REGION</b></td>
                    <td width="3%"><b>:</b></td>
                    <td width="37%"><b>REGION VII - CETNRAL VISAYAS</b></td>
                    <td width="40%" align="right"><b>CALENDAR YEAR:</b></td>
                    <td width="10%"><b>2023</b></td>
                </tr>
                <tr>
                    <td width="10%"><b>REGION</b></td>
                    <td width="3%"><b>:</b></td>
                    <td width="37%"><b>REGION VII - CETNRAL VISAYAS</b></td>
                    <td width="40%" align="right"><b>CALENDAR YEAR:</b></td>
                    <td width="10%"><b>2023</b></td>
                </tr>
            </table>
            ';

            PDF::SetTitle('ABSTRACT OF BIDS AS CALCULATED');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('L', array(215.9, 330.2));
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function showITBINFRA(Request $request)
    {
        $datax = $request->datax;
        $form = $request->form;
        $activities = $request->activities;

        db::table($this->Bac . ".bacc_2invitationtobid")
            ->where("bacc_proj_id", $datax['id'])
            ->update([
                'itb_member_id' => $form['itb_member_id']
            ]);

        db::table($this->Bac . ".bacc_2invitationtobid_activities")
            ->where("bacc_proj_id", $datax['id'])
            ->delete();

        $signatory =  db::table($this->sched_db . ".tbl_official_info")
            ->where("id", $form['itb_member_id'])
            ->get();

        $signaName = "";
        $signaPos = "";
        foreach ($signatory as $key => $value) {
            $signaName = $value->personName;
            $signaPos = $value->position;
        }
        $a1 = "";
        $a2 = "";
        $a3 = "";
        $a4 = "";
        $a5 = "";
        foreach ($activities as $key => $value) {
            if ($key == 0) {
                $a1 = $value['schedule'];
            }
            if ($key == 1) {
                $a2 = $value['schedule'];
            }
            if ($key == 2) {
                $a3 = $value['schedule'];
            }
            if ($key == 3) {
                $a4 = $value['schedule'];
            }
            if ($key == 4) {
                $a5 = $value['schedule'];
            }
            $data = array(
                'bacc_proj_id' => $datax['id'],
                'activities' => $value['activities'],
                'schedule' => $value['schedule'],
            );
            db::table($this->Bac . ".bacc_2invitationtobid_activities")->insert($data);
        }


        if ($datax['proc_type'] === 'Infrastructure') {
            $template_file_name = public_path() . '\BAC\ITBINFRA.docx';
        } else {
            $template_file_name = public_path() . '\BAC\ITBGOODS.docx';
        }
        $rand_no = rand(111111, 999999);
        $fileName = "results_" . $rand_no . ".docx";
        $folder   = "results_bac";
        $full_path = $folder . '/' . $fileName;
        if (!file_exists($folder)) {
            mkdir($folder);
        }

        copy($template_file_name, $full_path);
        $zip_val = new ZipArchive;
        $date = date_create($datax['posting']);
        $datephil = date_create($datax['posting']);

        log::debug($datax['title_of_project']);

        if ($zip_val->open($full_path) == true) {
            $key_file_name = 'word/document.xml';
            $message = $zip_val->getFromName($key_file_name);
            $message = str_replace("@title", str_replace("@", "at", str_replace("&", "and", $datax['title_of_project'])), $message);
            $message = str_replace("@sof", $datax['SOF'], $message);
            $message = str_replace("@duration", $datax['contract_duration'], $message);
            $message = str_replace("@amount", number_format($datax['ABC'], 2), $message);
            $message = str_replace("@itb", $form['itb_no'], $message);
            $message = str_replace("@datephil", date_format($datephil, "F j, Y"), $message);
            $message = str_replace("@phildates", date_format($datephil, "F j, Y"), $message);
            $message = str_replace("@cy", date_format($date, "Y"), $message);
            $message = str_replace("@feesbid", number_format($form['bid_fees'], 2), $message);
            $message = str_replace("@refund", number_format($form['bid_fees'], 2), $message);

            $message = str_replace("@1issuance", $a1, $message);
            $message = str_replace("@2prebid", $a2, $message);
            $message = str_replace("@3supplemental", $a3, $message);
            $message = str_replace("@4submission", $a4, $message);
            $message = str_replace("@5opening", $a5, $message);
            $message = str_replace("@sigName", $signaName, $message);
            // $message = str_replace("@sigPos", $signaPos, $message);
            $zip_val->addFromString($key_file_name, $message);
            $zip_val->close();
            log::debug($message);

            if (\File::exists(public_path() . "/" . $full_path)) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }
    }
    function storeInvitationBID(Request $request)
    {
        $form = $request->form;
        $project = $request->project;
        $id = $form['id'];
        if ($id > 0) {
            db::table($this->Bac . ".bac_bid_invitation_leter")
                ->where("id", $id)
                ->update($form);
        } else {
            db::table($this->Bac . ".bac_bid_invitation_leter")
                ->insert($form);
            $id = DB::getPDo()->lastInsertId();
        }
        foreach ($project as $key => $value) {
            db::table($this->Bac . ".bacc_proj")
                ->where("id", $value['id'])
                ->update(['row_index' => $value['row_index']]);
        }
        $list = db::table($this->Bac . ".bac_bid_invitation_leter")->where("id", $id)->get();
        return response()->json(new JsonResponse($list));
    }
    function getBIDinvitationList()
    {
        $list = db::table($this->Bac . ".bac_bid_invitation_leter")->get();
        return response()->json(new JsonResponse($list));
    }
    function getOpeningProjectByDate($date, $type)
    {
        $list = db::table($this->Bac . ".bacc_proj")
            ->join($this->Bac . ".bacc_2invitationtobid", 'bacc_2invitationtobid.bacc_proj_id', 'bacc_proj.id')
            ->where("bacc_proj.bid_opening", $date)
            ->where("bacc_proj.proc_type", $type)
            ->get();
        foreach ($list as $key => $value) {
            if (!$value->row_index) {
                $value->row_index = $key + 1;
            }
        }
        return response()->json(new JsonResponse($list));
    }
    function getGSDocs($docs)
    {
        return 'storage/files/gso/' . $docs;
    }
    function printLetter($id)
    {
        try {
            // log::debug($id);
            $datax = db::table($this->Bac . ".bac_bid_invitation_leter")->where("id", $id)->first();
            $projectList = db::table($this->Bac . ".bacc_proj")
                ->join($this->Bac . ".bacc_2invitationtobid", 'bacc_2invitationtobid.bacc_proj_id', 'bacc_proj.id')
                ->where("bacc_proj.bid_opening", $datax->bidDate)
                ->where("bacc_proj.proc_type", $datax->trans_type)
                ->get();
            // PDF::SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);
            PDF::AddPage('P');
            PDF::SetTitle('Bid Letter');
            PDF::SetFont('Helvetica', '', 10);
            $header = '<table style="width=100%;">
			<tr>
			   <td style="font-size:10pt;" align="center">
		          <img src="' . public_path() . '/images/Logo1.png"  height="40" width="40"/>
			  </td>
			</tr>
			<tr>
			   <td style="font-size:12pt;" align="center">
			   Republic of the Philippines
			   <br>
			   Province of Cebu
			   <br>
			   City of Naga
               <br>
               OFFICE OF THE BIDS AND AWARDS COMMITTEE
			  </td>
			</tr>
	    	</table>';
            $project = '<table border="1" cellpadding ="3">

              <tr>
                 <th width="100%" >ITB No. ' . $datax->itb_no . '</th>
               </tr>
               <tr>
                 <th width="10%" align="center" >Reference No.</th>
                 <th width="35%" align="center" >Project/ Activity Description</th>
                 <th width="10%" align="center" >Delivery</th>
                 <th width="15%" align="center" >Approved Budget of the Contract (Php)</th>
                 <th width="15%" align="center" >Non-  Refundable Bid Fees (Php)</th>
                 <th width="15%" align="center" >Source of Funds</th>
               </tr>

             ';
            $date = date_create($datax->bidDate);
            $time = date_create($datax->bidTime);
            $desc = "morning";
            if (date_format($time, "h:i") > '12:00') {
                $desc = "afternoon";
            } else {
                $desc = "morning";
            }
            $dateOpen = 'In this regard, may I invite you to attend and witness the BID OPENING  on <b>' . date_format($date, "F d, Y") . ', ' . date_format($time, "h:i") . '</b> in the
             ' . $desc . ' at BAC Office 2nd Floor City Hall Building, East Poblacion City of Naga, Cebu.';
            foreach ($projectList as $key => $value) {
                // log::debug($value->row_index);
                $project .= '<tr>
                 <td align="center">' . str_pad($value->row_index, 2, "0", "0") . '</td>
                 <td>' . $value->title_of_project . '</td>
                 <td align="center">' . $value->contract_duration . '</td>
                 <td align="center">' . number_format($value->ABC, 2) . '</td>
                 <td align="center">' . number_format($value->bid_fees, 2) . '</td>
                 <td>' . $value->SOF . '</td>
               </tr>';
            }
            $project .= '
            </table> <br/><br/>';

            $to = $datax->to_person;
            $veryTruly = $datax->very_truly;
            $approved_by = $datax->approved_by;

            $Template = '';
            $Template .= $header;
            $Template .= "<br><br>";
            $Template .= $to;
            $Template .= "<br><br/>";
            $Template .= "Please be informed that the City Government of Naga, Cebu through the Bids and Awards Committee
            (BAC), will  have its <b>BID OPENING</b> for the following projects:";
            $Template .= "<br/><br/><br/>";
            $Template .= $project;
            $Template .= $dateOpen;
            $Template .= "<br><br><br><br> Very Truly Yours,<br><br>";
            $Template .= $veryTruly;
            $Template .= "<br><br><br>Approved By:<br><br>";
            $Template .= $approved_by;
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printNOATemplate(Request $request)
    {
        try {
            $data = $request->data;
            // log::debug($data['contract_cost'] * 1);
            $b = $data['amountWord'];
            // log::debug($b);
            // $totalAmount = '2332.01';
            // $amountInWords = ucwords((new NumberFormatter('en_IN', NumberFormatter::SPELLOUT))->format($totalAmount));
            // log::debug($amountInWords);
            $Template = '<table>
            <tr>
                <th width="32%" align="right">
                <img src="' . public_path() . '/img/logo1.png"  height="45" width="45">
                </th>
                <th width="38%" style="font-size:9pt;  word-spacing:30px" align="center">
                        Republic of the Philippines
                <br />
                        Province of Cebu
                <br />

                    Municipality of Dumanjug
                <br />
                    </th>

                <th align="left">

                </th>
            </tr>
            <tr>
                <th width="100%" style="font-size:15pt;color:blue ;border-bottom:double" align="center"><b>OFFICE OF THE BIDS AND AWARDS COMMITTEE</b>
                </th>
            </tr>
            </table>

            <table>
                <tr>
                    <td width="100%"></td>
                </tr>
                 <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                    <td width="80%"></td>
                </tr>
                <tr>
                    <td width="20%" align="center">Date</td>
                    <td width="80%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td><b>' . $data['winning_bidder'] . '</b></td>
                </tr>
                <tr>
                    <td>' . $data['reference_address'] . '</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%" style="font-size:15pt" align="center"><b><u>NOTICE OF AWARD</u></b></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="17%">Project Description:</td>
                    <td width="83%"><b>' . $data['title_of_project'] . '<br/>at ' . $data['project_loc'] . ', ' . $data['project_loc_CityProvince'] . ', Cebu, <br/>
                                            P.R # ' . $data['pr_no'] . '</b></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td style="text-justify: inter-word; text-align: justify">The Municipality of Dumanjug has considered your company for the above-described
                        work and you are hereby notified that your price <b><i>' . strtoupper($b) . ' (' . number_format($data['contract_cost'], 2) . '):</i></b></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td style="text-justify: inter-word; text-align: justify">Within a maximum period of ten (10) calender days from the receipt of the Notice of Award and in all
                    cases upon the signing of the contract, you should furnish the Procuring Entity with the perfomance security in accordance with the Conditions of Contract, and
                    in the Form prescribed in the Bidding Documents. It must be posted in favor of the Procuring Entity, and will be forfeited in the latter\'s favor in the event it
                    is established that the winning bidder is in default in any obligations under the contact. The performance security forms part of the contract.</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td style="text-justify: inter-word; text-align: justify">You are therefore required to contact the Municipality of Dumanjug for the preparation of Contract/ Agreement and furnish
                     all other requirements forming part of the Contract/Agreement as soon as possible</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td style="font-size:10pt"><b><u>Per BAC Resolution No. ' . $data['sp_resolution_no'] . '</u></b></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="50%">Recommending Approval:</td>
                    <td width="50%">Approved by:</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="50%" style="font-size:11pt"><b>_____________</b></td>
                    <td width="50%" style="font-size:11pt" style="text-align:center"><b>Hon. EFREN GUNTRANO Z. GICA</b></td>
                </tr>
                <tr>
                    <td width="50%">City Budget Officer/BAC Chairman</td>
                    <td width="50%" style="text-align:center">Municipality Mayor</td>
                </tr>

                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="50%">Receipt of the foregoing Notice of Award is hereby acknowledged this</td>
                    <td width="5%" style="border-bottom:1px solid black"></td>
                    <td width="7%">day of</td>
                    <td width="10%" style="border-bottom:1px solid black"></td>
                    <td width="23%">, ' . (!empty($data['noa']) ? (date_format(date_create($data['noa']), "Y")) : "") . '.</td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="50%"></td>
                    <td width="50%">Conforme:</td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="50%"></td>
                    <td width="5%">By:</td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                    <td width="15%"></td>





                </tr>
                <tr>
                    <td width="55%"></td>
                    <td width="30%" style="font-size:7pt" align="center">Signature over printed name</td>
                    <td width="15%"></td>
                </tr>
            </table>
            ';

            PDF::SetTitle('Notice of Award');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('P', array(215.9, 279.4));
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printContractBids(Request $request)
    {
        try {

            $data = $request->data;


            $Template = '<table cellpadding="2">
                <tr>
                    <td width="65%">
                        <table>
                            <tr>
                                <td>LGU - Municipality of Dumanjug, Cebu</td>
                            </tr>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td style="font-size:10pt"><b>INFRASTRUCTURE</b></td>
                            </tr>
                            <tr>
                                <td width="30%"></td>
                                <td width="60%" align="center" style="font-size:13pt"><b>ABSTRACT OF BIDS AS CALCULATED</b></td>
                            </tr>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="30%"><b>ADVERTISEMENT</b></td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black"><b></b></td>
                                <td width="3%"></td>
                            </tr>
                            <tr>
                                <td width="30%"><b></b></td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black"></td>
                                <td width="3%"></td>
                            </tr>
                            <tr>
                                <td width="30%">Project Name</td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black"><b>' . $data['title_of_project'] . '</b></td>
                                <td width="3%"></td>
                            </tr>
                            <tr>
                                <td width="30%"><b></b></td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black"></td>
                                <td width="3%"></td>
                            </tr>
                            <tr>
                                <td width="30%">Project Location</td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black"><b>at ' . $data['project_loc'] . '</b></td>
                                <td width="3%"></td>
                            </tr>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="30%">Implementing Office</td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black">' . $data['dept'] . '</td>
                                <td width="3%"></td>
                            </tr>
                            <tr>
                                <td width="30%">Approved Budget for the Contract</td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black">' . number_format($data['ABC'], 2) . '</td>
                                <td width="3%"></td>
                            </tr>
                            <tr>
                                <td width="30%">Time and Place of Bid Opening</td>
                                <td width="4%">:</td>
                                <td width="63%" style="border-bottom:1px solid black">10 a.m @ BAC Conference Room</td>
                                <td width="3%"></td>
                            </tr>

                        </table>
                    </td>


                    <td width="35%">
                        <table>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="40%">Project Ref. No.</td>
                                <td width="60%" style="border-bottom:1px solid black"><b>P.O.W # ' . $data['reference_no'] . '</b></td>
                            </tr>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="40%">Name of Project</td>
                                <td width="60%" style="border-bottom:1px solid black"><b>' . $data['title_of_project'] . '</b></td>
                            </tr>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="40%">Location of the Project</td>
                                <td width="60%" style="border-bottom:1px solid black">at ' . $data['project_loc'] . '</td>
                            </tr>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="40%">BAC Resolution:</td>
                                <td width="60%" style="border-bottom:1px solid black" align="center"><b>' . $data['sp_resolution_no'] . '</b></td>
                            </tr>
                            <tr>
                                <td width="40%">Bidding Date</td>
                                <td width="60%" style="border-bottom:1px solid black" align="center">' . $data['bid_opening'] . '</td>
                            </tr>
                            <tr>
                                <td width="40%">Time</td>
                                <td width="60%" style="border-bottom:1px solid black" align="center">10:00 a.m</td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
            <br/>
            <br/>
            <table cellpadding="2" border="1">
                <tr>
                    <td width="40%" height="20px" align="center"><b>NAME OF BIDDERS</b></td>
                    <td width="20%" align="center"><b>' . $data['winning_bidder'] . '</b></td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >Total Amount of Bid</td>
                    <td width="20%" align="right">' . number_format($data['bidamount'], 2) . '</td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >Form of Bid Security</td>
                    <td width="20%" align="right">Bid Securing Declaration</td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >&nbsp; &nbsp; &nbsp; &nbsp; Bank/Company</td>
                    <td width="20%" align="right"></td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >&nbsp; &nbsp; &nbsp; &nbsp; Number</td>
                    <td width="20%" align="right"></td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >&nbsp; &nbsp; &nbsp; &nbsp; Validity Period</td>
                    <td width="20%" align="right"></td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >&nbsp; &nbsp; &nbsp; &nbsp; Bid Security</td>
                    <td width="20%" align="right"></td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >Required Bid Security</td>
                    <td width="20%" align="right"></td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >Sufficient / Insufficient</td>
                    <td width="20%" align="right">Sufficient</td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
                <tr>
                    <td width="40%" >Remarks</td>
                    <td width="20%" align="right"></td>
                    <td width="20%" ></td>
                    <td width="20%" ></td>
                </tr>
            </table>
            <br/>
            <br/>
            <br/>
            <br/>
            <br/>
            <table>
                <tr>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:10pt"><i>Chairman (City Budget Officer)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Vice-Chairman (City Administrator)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Member (Attorney III)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Member (Engineer II)</i></td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="25%"></td>
                    <td width="25%"></td>
                    <td width="25%"></td>
                    <td width="25%">Approved:</td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>_________________________</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:10pt"><i>Member (Lincensing Officer III)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Member (City Legal Officer)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Member (GSO)</i></td>
                    <td width="20%" align="center" style="font-size:10pt"><i>City Mayor</i></td>
                    <td width="5%"></td>
                </tr>
            </table>
            ';

            PDF::SetTitle('ABSTRACT OF BIDS AS CALCULATED');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('L', array(215.9, 330.2));
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printRFQ(Request $request)
    {
        try {

            $data = $request->data;
            $mainData = "";
            $prDescription = DB::table($this->Bac . '.bacc_proj')
                ->leftjoin($this->Proc . '.pow_main_individual', 'pow_main_individual.id', 'bacc_proj.pow_id')
                ->leftjoin($this->Proc . '.tbl_pr_main', 'tbl_pr_main.pow_id', 'pow_main_individual.id')
                ->leftjoin($this->Proc . '.tbl_pr_detail', 'tbl_pr_detail.main_id', 'tbl_pr_main.id')
                ->where('bacc_proj.pow_id', $data['pow_id'])
                ->get();


            $bidder = db::table($this->Bac . ".bacc_bid_opening")->where("proj_id", $data['id'])->get();
            $bidder1 = "";
            $bidder2 = "";
            $bidder3 = "";
            $bidder1amount = "";
            $bidder2amount = "";
            $bidder3amount = "";
            $winner = "";
            $itm = "";
            foreach ($bidder as $key => $value) {
                if ($value->winner === "1") {
                    $winner = $value->business_name;
                }
                if ($key === 0) {
                    $bidder1 = $value->business_name;
                    $bidder1amount =  number_format($value->bidamount, 2);
                }
                if ($key === 1) {
                    $bidder2 = $value->business_name;
                    $bidder2amount = number_format($value->bidamount, 2);
                }
                if ($key === 2) {
                    $bidder3 = $value->business_name;
                    $bidder3amount = number_format($value->bidamount, 2);
                }
            }
            foreach ($prDescription as $key => $value) {
                $itm .= '<tr>
                <td width="10%" align="center">' . ($key + 1) . '</td>
                <td width="10%" align="center">' . $value->qty . '</td>
                <td width="10%" align="center">' . $value->unit_measure . '</td>
                <td width="25%" align="center"><b>' . $value->item_name . ' <br/><br/></b></td>
                <td width="15%" align="center">' . $bidder1amount . '</td>
                <td width="15%" align="center">' . $bidder2amount . '</td>
                <td width="15%" align="center">' . $bidder3amount . '</td>
            </tr>';
            }
            $datepostqua = date_create($data['post_qua']);
            // log::debug($bidder);

            $Template = '  <table width="100%" cellpadding="2">
                <tr>
                    <td width="65%" style="font-size:10pt">LGU - Municipality of Dumanjug, Cebu</td>
                    <td width="12%" style="font-size:10pt">Project Ref. No.</td>
                    <td width="23%" style="border-bottom:1px solid black;font-size:10pt">' . $data['reference_no'] . '</td>
                </tr>
                <tr>
                    <td width="65%" style="font-size:10pt">ALTERNATIVE MODE OF PROCUREMENT<br/>-Negotiated Procurement</td>
                    <td width="12%" style="font-size:10pt">Name of Project</td>
                    <td width="23%" style="border-bottom:1px solid black;font-size:10pt">' . $data['title_of_project'] . '</td>
                </tr>
                <tr>
                    <td width="65%" style="font-size:10pt"></td>
                    <td width="12%" style="font-size:10pt">Location of the Project</td>
                    <td width="23%" style="border-bottom:1px solid black;font-size:10pt">' . $data['project_loc'] . '</td>
                </tr>
                <tr>
                    <td width="65%" style="font-size:10pt"></td>
                    <td width="12%" style="font-size:10pt">BAC Resolution No.</td>
                    <td width="23%" style="border-bottom:1px solid black;font-size:10pt" align="center">28-1</td>
                </tr>
                <tr>
                    <td width="28.3%" style="font-size:10pt"></td>
                    <td width="33.5%" style="font-size:15pt" align="right"><b>ABSTRACT OF CANVASS</b></td>
                    <td width="3%" style="font-size:10pt"></td>
                    <td width="12.2%" style="font-size:10pt" align="left">Date</td>
                    <td width="23%" style="border-bottom:1px solid black;font-size:10pt" align="center">' . (!empty($data['session_date']) ? (date_format(date_create($data['session_date']), "d M, Y")) : "") . '</td>
                </tr>
                <tr>
                    <td width="100%" style="font-size:11pt" align="center"><b>Request for Quotation (RFQ)</b> </td>
                </tr>

            </table>
            ';

            $Template .= '<table width="100%" cellpadding="2">

                <table width="100%" cellpadding="2" border="1">
                <tr>
                    <td width="10%" align="center"><b>ITEM NO</b></td>
                    <td width="10%" align="center"><b>QTY</b></td>
                    <td width="10%" align="center"><b>UNIT</b></td>
                    <td width="25%" align="center"><b>ARTICLES</b></td>
                    <td width="15%" align="center"><b>' . $bidder1 . '</b></td>
                    <td width="15%" align="center"><b>' . $bidder2 . '</b></td>
                    <td width="15%" align="center"><b>' . $bidder3 . '</b></td>
                </tr>
                ' . $itm . '
                </table>
                <tr>
                    <td width="17%" style="font-size:10pt">Based on <i>CANVASS DATE: </i></td>
                     <td width="17%"style="border-bottom:1px solid black;font-size:10pt;text-align:center">' . date_format($datepostqua, "m/d/Y") . '</td>
                     <td width="23%" style="font-size:10pt">Based on <i>AWARD is hereby given to: </i></td>
                     <td width="43%"style="border-bottom:1px solid black;font-size:10pt">' .  $winner . '</td>
                </tr>
                <tr>
                    <td width="100%">
                    </td>
                </tr>
                <tr>
                    <td width="100%">
                    </td>
                </tr>
                <tr>
                    <td width="100%">
                    </td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:10pt"><i>Chairman (City Budget Officer)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Vice-Chairman (City Administrator)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Member (Attorney III)</i></td>
                    <td width="25%" style="font-size:10pt"><i>Member (Assistant City Engineer)</i></td>
                </tr>

                <tr>
                    <td width="100%">
                    </td>
                </tr>
                <tr>
                    <td width="57%"></td>
                    <td width="43%" align="center"> Approved: </td>
                </tr>
                <tr>
                    <td width="100%">
                    </td>
                </tr>

                <tr>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                    <td width="25%" style="font-size:10pt"><b>___________________</b></td>
                </tr>
                <tr>
                    <td width="25%" style="font-size:10pt"><i>Member (Licensing Officer III)</i></td>
                    <td width="25%" style="font-size:10pt"><i>City Legal Officer</i></td>
                    <td width="25%" style="font-size:10pt"><i>Member (GSO)</i></td>
                    <td width="25%" style="font-size:10pt"><i>City Mayor</i></td>
                </tr>

            </table>';

            PDF::SetTitle('Post Qualification');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('L');
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
