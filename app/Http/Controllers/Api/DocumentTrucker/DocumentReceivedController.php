<?php

namespace App\Http\Controllers\Api\DocumentTrucker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \App\Laravue\JsonResponse;
use Illuminate\Support\Facades\log;
use Storage;
use File;
use PDF;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrackerMailable;
use Multimail; // facade
class DocumentReceivedController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->middleware('auth');
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }
    public function index()
    {
        return view('trucker.Document-Received');
    }

    public function getRef(Request $request)
    {
        $pre = 'TRK';
        $table = $this->trk_db . ".documentstrackermain";
        $date = $request->date;
        $refDate = 'DateSubmitted';
        return $this->G->generateReference($pre, $table, $date, $refDate);
    }

    public function docType()
    {
        $data = DB::table($this->trk_db . '.documenttype')->get();
        $html = '';
        foreach ($data as $row) {
            $html .= "<option value = '" . $row->office . "'>" . $row->office . "</option>";
        }
        $data['select'] = $html;
        return $data;
    }

    public function getFLowName()
    {
        $employee = DB::table($this->hr_db . '.employee_information')
            ->select(DB::raw('CONCAT(PPID,"E")AS id,NAME AS name'));
        $flow = DB::table($this->trk_db . '.doc_flow_main')
            ->select(DB::raw('id AS id,flow_name AS name'))
            ->union($employee)->get();
        $html = '';
        foreach ($flow as $row) {
            $html .= "<option value = '" . $row->id . "'>" . $row->name . "</option>";
        }
        $data['select'] = $html;
        return $data;
    }
    public function getFlowchart(Request $Request)
    {
        $data = DB::select('call ' . $this->trk_db . '.rans_display_doc_flow("' . $Request->id . '")');
        return $data;
    }

    public function list(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $lgu_db = $this->lgu_db;
        $html = '';
        $result_set = db::select("SELECT DISTINCT(sched_group.`group_id`) AS 'id',(`group_name`) AS 'name' FROM " . $lgu_db . ".`sched_group` INNER JOIN " . $lgu_db . ".`sched_group_member` WHERE `emp_id` = '$empid'");
        foreach ($result_set as $row) {
            $html .= "<option value = '" . $row->id . "'>" . $row->name . "</option>";
        }
        $data['select'] = $html;
        return $data;
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data['main'] = DB::select("select * from  " . $this->trk_db . ".documentstrackermain where ID = '$id'");
        return $data;
    }
    public function signatoryStoreView(Request $request)
    {
        $selected = $request->selected;
        db::table($this->trk_db . '.document_signatory_group')
            ->where('employee_id', Auth::user()->Employee_id)
            ->delete();
        foreach ($selected as $key => $value) {
            $sig = array(
                'employee_id' => Auth::user()->Employee_id,
                'allowed_id' => $value['PPID'],
                // 'display_name'=>$value['NAME'],
            );
            db::table($this->trk_db . '.document_signatory_group')->insert($sig);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function signatoryStore(Request $request)
    {

        try {
            $main = $request->main;
            // $chkbusiness = db::table($this->trk_db.'.map_flow')
            // ->where('map_name','business permit')
            // ->where('flow_main_id',$main['flow_id'])->count();

            //  if ($chkbusiness > 0) {
            //     $this->storeBusinessPermit($request);
            //     return true;
            // }
            DB::beginTransaction();
            $signatoryStatus = $request->signatoryStatus;
            $selected = $request->selected;
            $orig_signatory =  $request->orig_signatory;
            $signatory =  $request->signatory;
            db::table($this->trk_db . '.documenttrackerdetails')->where('id', $main['idx'])->update(['forwarded' => '1', 'doc_status' => 1, 'forward_ts' => $this->G->serverdatetime()]);

            db::table($this->trk_db . '.documenttracketdetails_employee_signatory')
                ->where('doc_main_id', $main['ID'])
                ->where('sig_name', $signatory)
                ->delete();

            if ($signatory <> $orig_signatory) {

                $getSigantoryUpdate =  db::table($this->trk_db . '.documenttrackerdetails')
                    ->where('id', '>', $main['idx'])
                    ->where('doc_main', $main['ID'])->get();
                $insertdata = array(
                    'doc_main' => $main['ID'],
                    'flow_signatory' => $signatory
                );

                db::table($this->trk_db . '.documenttrackerdetails')
                    ->where('id', '>', $main['idx'])
                    ->where('doc_main', $main['ID'])->delete();

                db::table($this->trk_db . '.documenttrackerdetails')
                    ->insert($insertdata);

                foreach ($getSigantoryUpdate as $key => $value) {
                    $insertdata = array(
                        'doc_main' => $main['ID'],
                        'flow_signatory' => $value->flow_signatory
                    );
                    db::table($this->trk_db . '.documenttrackerdetails')
                        ->insert($insertdata);
                }

                foreach ($selected as $key => $value) {
                    $sig = array(

                        'employee_id' => $signatory,
                        'emp_id' => $value['PPID'],
                        'display_name' => $value['NAME'],
                    );

                    if ($key == 0) {
                        db::table($this->trk_db . '.documentstrackermain')
                            ->where('ID', $main['ID'])
                            ->update(['main_stat' => $signatory]);
                    }
                    db::table($this->trk_db . '.documenttracketdetails_employee_signatory')->insert($sig);
                }
            } else {
                db::table($this->trk_db . '.documenttracketdetails_employee_signatory')
                    ->where('doc_main_id', $main['ID'])
                    ->where('sig_name', $signatoryStatus['flow_signatory'])
                    ->delete();

                $chk = db::table($this->trk_db . '.documenttrackerdetails')
                    ->where('doc_main', $main['ID'])
                    ->where('flow_signatory', $signatoryStatus['flow_signatory'])->count();

                if ($chk < 1) {
                    $insertdata = array(
                        'doc_main' => $main['ID'],
                        'flow_signatory' => $signatoryStatus['flow_signatory']
                    );
                    db::table($this->trk_db . '.documenttrackerdetails')
                        ->insert($insertdata);
                }
                foreach ($selected as $key => $value) {
                    $sig = array(
                        'doc_main_id' => $main['ID'],
                        'sig_name' => $signatory,
                        'emp_id' => $value['PPID'],
                        'display_name' => $value['NAME'],
                    );

                    if ($key == 0) {
                        db::table($this->trk_db . '.documentstrackermain')
                            ->where('ID', $main['ID'])
                            ->update(['main_stat' => $signatoryStatus['flow_signatory']]);
                    }
                    db::table($this->trk_db . '.documenttracketdetails_employee_signatory')->insert($sig);
                }
            }

            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $th) {
            return response()->json(new JsonResponse(['Message' => 'Something Wrong..', 'status' => 'error']));
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $th, 'status' => 'error']));
        }
    }
    public function storeBusinessPermit($request)
    {

        $signatoryStatus = $request->signatoryStatus;
        $selected = $request->selected;
        $signatory =  $request->signatory;
        $main = $request->main;
        foreach ($selected as $key => $value) {
            $sig = array(
                'doc_main_id' => $signatoryStatus['doc_main'],
                'sig_name' => $signatory,
                'emp_id' => $value['PPID'],
                'display_name' => $value['NAME'],
            );

            if ($key == 0) {
                db::table($this->trk_db . '.documentstrackermain')
                    ->where('ID', $signatoryStatus['doc_main'])
                    ->update(['main_stat' => $signatory]);
            }
            db::table($this->trk_db . '.documenttracketdetails_employee_signatory')->insert($sig);
        }
    }
    public function signatoryDocs(Request $request)
    {
        $list = db::table($this->trk_db . '.document_signatory_group')->where('employee_id', Auth::user()->Employee_id)->get();

        return response()->json(new JsonResponse($list));
    }

    public function signatory(Request $request)
    {
        $id = $request->ID | 0;

        //  log::debug($id);
        if ($id > 0) {
            $trk_db = $this->trk_db;
            $datasig = db::table($trk_db . '.documenttrackerdetails')
                ->where('doc_main', $request->ID)
                ->whereNull('emp_id')
                ->limit('1')
                ->get();
            $signame = "";
            foreach ($datasig as $key => $value) {
                $signame = $value->flow_signatory;
            }


            if (strlen($signame) > 0) {
                $result_set['sig'] = $datasig;
                $result_set['name'] = db::table($trk_db . '.documenttracketdetails_employee_signatory')
                    ->where('doc_main_id', $request->ID)
                    ->where('sig_name', $signame)->get();
            } else {
                $result_set['sig'] = [array('flow_signatory' => "STEP " . db::table($trk_db . '.documenttrackerdetails')->where('doc_main', $id)->count())];
                $result_set['name'] = [];
            }
        } else {

            $flow_id =  $request->flow_id | 0;
            $trk_db = $this->trk_db;
            $datasig = db::table($trk_db . '.doc_flow_employee')
                ->where('doc_flow_main_id', $flow_id)
                ->get();

            $signame = "";
            $sigID = 0;
            foreach ($datasig as $key => $value) {
                if ($key == 1) {
                    $signame = $value->Signatory_name;
                    $sigID =  $value->id;
                }
            }
            $result_set['sig'] = $signame | "STEP " . db::table($trk_db . '.documenttrackerdetails')->where('doc_main', $id)->count();
            $result_set['name'] = db::select("SELECT doc_flow_employee.`id`,`Signatory_name` AS 'sig_name',doc_flow_employee_details.`emp_id`,doc_flow_employee_details.`display_name`
        FROM " . $trk_db . ".doc_flow_main
        INNER JOIN " . $trk_db . ".doc_flow_employee ON(doc_flow_main.`id` = doc_flow_employee.`doc_flow_main_id`)
        LEFT JOIN " . $trk_db . ".doc_flow_employee_details ON(doc_flow_employee_details.`sig_id` = doc_flow_employee.`id`)
        WHERE doc_flow_employee.`id` = " . $sigID . "
        ORDER BY doc_flow_main.`id`,doc_flow_employee.`id`");
        }
        return response()->json(new JsonResponse($result_set));
    }
    public function showIncoming(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $trk_db = $this->trk_db;
        $result_set = DB::select("CALL " . $this->trk_db . ".rans_display_doc_update5('" . $empid . "')");
        return response()->json(new JsonResponse(['data' => $result_set]));
    }
    public function showAllDocs(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $trk_db = $this->trk_db;
        $result_set = DB::select("CALL " . $this->trk_db . ".rans_display_doc_update5_new('" . $empid . "')");
        return response()->json(new JsonResponse(['data' => $result_set]));
    }
    public function return(Request $request, $idx)
    {
        $trk_db = $this->trk_db;
        DB::beginTransaction();
        $lastdata = DB::select("SELECT * FROM " . $trk_db . ".documenttrackerdetails WHERE `doc_main` = (SELECT d.`doc_main` FROM " . $trk_db . ".documenttrackerdetails d WHERE d.id = $idx) AND id < $idx ORDER BY id DESC LIMIT 1");

        foreach ($lastdata as $row) {
            $dataReturn['documenttrackerdetails_id'] = $idx;
            $dataReturn['emp_id'] = Auth::user()->Employee_id;
            DB::table($trk_db . '.documenttrackerdetails_return')->insert($dataReturn);
            $data['received'] = 0;
            $data['date_received'] = null;
            $data['doc_status'] = 0;
            $data['doc_ts'] = null;
            try {
                DB::table($trk_db . '.documenttrackerdetails')
                    ->whereIn('id', [$row->id, $idx])
                    ->update($data);
                DB::commit();
                return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
            }
        }
    }
    public function received(Request $request)
    {
        try {
            $idx = $request->idx;
            $doc_main = $request->doc_main;
            $empid = $request->employee;
            $lgu_db = $this->lgu_db;
            $trk_db = $this->trk_db;
            DB::beginTransaction();
            $status = DB::select("SELECT * FROM " . $trk_db . ".documenttrackerdetails WHERE `id` = '$idx'");
            foreach ($status as $row) {
                $status = $row->received;
                db::table($trk_db . '.documentstrackermain')
                    ->where('ID', $row->doc_main)
                    ->update(['main_stat' => $row->flow_signatory]);
            }

            if ($status == 0) {
                $data['received'] = 1;
                $data['emp_id'] = Auth::user()->Employee_id;
                $data['date_received'] = $this->G->serverdatetime();
            } else {
                $data['doc_status'] = 1;
                $data['doc_ts'] = $this->G->serverdatetime();
                if ($empid > 0) {
                    $tempdate = DB::select("SELECT * FROM " . $trk_db . ".documenttrackerdetails WHERE `id` = '$idx'");
                    foreach ($tempdate as $row) {
                        $insdata['doc_main'] = $row->doc_main;
                        $insdata['flow_id'] = 0;
                        $insdata['emp_id'] = $empid;
                        $insdata['flow_signatory'] = 'No Process Flow';
                        $insdata['sort_id'] = $row->sort_id + 1;
                        DB::table($trk_db . '.documenttrackerdetails')
                            ->insert($insdata);
                    }
                }
            }


            DB::table($trk_db . '.documenttrackerdetails')
                ->where('id', $idx)
                ->update($data);

            $datax =  DB::table($trk_db . '.documenttrackerdetails')
                ->where('id', '<', $idx)
                ->where('doc_main', $doc_main)
                ->orderBy('id', 'desc')->first();
            if ($datax) {
                db::table($trk_db . '.documenttrackerdetails')
                    ->where('id', $datax->id)
                    ->update(['doc_status' => 1]);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function receivedList(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $data = db::select('call ' . $this->trk_db . '.rans_display_outbox_docs(?,?,?)', [$request->from, $request->to, $empid]);
        // $data = DB::select("	SELECT * FROM (SELECT
        // documentstrackermain.`ID`
        // ,documentstrackermain.`TrackingNum`
        // ,documentstrackermain.Sender
        // ,`Subject`
        // ,`date_received` as 'dateReceived'
        // ,`ExpectedReturn` as 'ExpectedReturn'
        // ,`forward_ts` as 'dateForward'
        // ,documenttrackerdetails.`id` AS 'idx'
        // ,dep.Name_Dept AS 'Office'
        // ,flow_signatory
        // ,TRIM(CONCAT(
        // (CASE WHEN LENGTH(documentstrackermain.`payeeName`) >0  THEN  CONCAT('Payee : ',documentstrackermain.`payeeName`,'<br>') ELSE ''  END),
        // (CASE WHEN documentstrackermain.`payeeAmount` <> 0 THEN   CONCAT('Amount : ',CAST(FORMAT(documentstrackermain.`payeeAmount`,2) AS CHAR(30)),'<br>') ELSE ''  END),
        // (CASE WHEN LENGTH(documentstrackermain.`check_number`) > 0 THEN  CONCAT('Check Number : ',documentstrackermain.`check_number`,'<br>') ELSE '' END),
        // (CASE WHEN LENGTH(documentstrackermain.`check_date`) > 0  THEN CONCAT('Check Date : ',documentstrackermain.`check_date`,'<br>') ELSE '' END),
        // (CASE WHEN LENGTH(documentstrackermain.`source_of_fund`) > 0  THEN CONCAT('Fund : ',documentstrackermain.`source_of_fund`) ELSE '' END)
        // )) AS 'Details'
        // ,documentstrackermain.`Remarks`
        // ,documenttrackerdetails.`date_received`
        // ,ifnull(documentstrackermain.`date_done`,'') as date_done
        // ,ifnull(documentstrackermain.docs_qr,'') as docs_qr
        // ,doc_client_email.email_address
        // FROM ".$this->trk_db.".documentstrackermain
        // LEFT JOIN ".$this->trk_db.".documenttrackerdetails
        // ON(documentstrackermain.`ID` = documenttrackerdetails.`doc_main`)
        // left join ".$this->trk_db.".doc_client_email on(doc_client_email.id = documentstrackermain.email_add)
        // LEFT JOIN(
        // SELECT
        // `SysPK_Dept` AS 'depid'
        // ,`Name_Dept`
        // FROM `humanresource`.`department`
        // )dep ON(dep.depid = documentstrackermain.`ReceivingDeptID`)
        // WHERE `documenttrackerdetails`.`emp_id` LIKE '$empid%'

        // )A where date(date_received) between '".$request->from."'
        // and '".$request->to."'
        // ");
        return response()->json(new JsonResponse(['data' => $data, 'status' => 'success']));
    }

    public function receivedListDoneRead($id)
    {
        db::table($this->trk_db . '.documenttrackerdetails')
            ->where('doc_main', $id)
            ->where('emp_id', Auth::user()->Employee_id)
            ->update(['doneRead' => 1]);
    }
    public function receivedListDone(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $data = db::select('call ' . $this->trk_db . '.rans_display_done_docs(?,?,?)', [$request->from, $request->to, $empid]);
        // $data = DB::select("SELECT * FROM (SELECT
        // documentstrackermain.`ID`
        // ,documentstrackermain.`TrackingNum`
        // ,`Sender`
        // ,`Subject`
        // ,`date_received` as 'dateReceived'
        // ,`ExpectedReturn` as 'ExpectedReturn'
        // ,`forward_ts` as 'dateForward'
        // ,documenttrackerdetails.`id` AS 'idx'
        // ,dep.Name_Dept AS 'Office'
        // ,flow_signatory
        // ,TRIM(CONCAT(
        // (CASE WHEN LENGTH(documentstrackermain.`payeeName`) >0  THEN  CONCAT('Payee : ',documentstrackermain.`payeeName`,'<br>') ELSE ''  END),
        // (CASE WHEN documentstrackermain.`payeeAmount` <> 0 THEN   CONCAT('Amount : ',CAST(FORMAT(documentstrackermain.`payeeAmount`,2) AS CHAR(30)),'<br>') ELSE ''  END),
        // (CASE WHEN LENGTH(documentstrackermain.`check_number`) > 0 THEN  CONCAT('Check Number : ',documentstrackermain.`check_number`,'<br>') ELSE '' END),
        // (CASE WHEN LENGTH(documentstrackermain.`check_date`) > 0  THEN CONCAT('Check Date : ',documentstrackermain.`check_date`,'<br>') ELSE '' END),
        // (CASE WHEN LENGTH(documentstrackermain.`source_of_fund`) > 0  THEN CONCAT('Fund : ',documentstrackermain.`source_of_fund`) ELSE '' END)
        // )) AS 'Details'
        // ,documentstrackermain.`Remarks`
        // ,documenttrackerdetails.`date_received`
        // ,ifnull(documentstrackermain.`date_done`,'') as date_done
        // ,DateSubmitted
        // ,doneRead
        // ,ifnull(documentstrackermain.docs_qr,'') as docs_qr
        // FROM ".$this->trk_db.".documentstrackermain
        // LEFT JOIN ".$this->trk_db.".documenttrackerdetails
        // ON(documentstrackermain.`ID` = documenttrackerdetails.`doc_main`)
        // LEFT JOIN(
        // SELECT
        // `SysPK_Dept` AS 'depid'
        // ,`Name_Dept`
        // FROM `humanresource`.`department`
        // )dep ON(dep.depid = documentstrackermain.`ReceivingDeptID`)
        // WHERE `documenttrackerdetails`.`emp_id` LIKE '$empid%'
        // and documentstrackermain.date_done is not null
        // )A where date(DateSubmitted) between '".$request->from."'
        // and '".$request->to."'
        // ");

        return response()->json(new JsonResponse(['data' => $data, 'status' => 'success']));
    }
    public function getDoneCount()
    {

        $list = db::table($this->trk_db . '.documentstrackermain')
            ->join($this->trk_db . '.documenttrackerdetails', 'documenttrackerdetails.doc_main', '=', 'documentstrackermain.ID')
            ->where('documenttrackerdetails.emp_id', Auth::user()->Employee_id)
            ->where('documenttrackerdetails.doneRead', 0)
            ->whereNotNull('date_done')->count();
        return response()->json(new JsonResponse(['data' => $list, 'status' => 'success']));
    }
    public function UploadSignatory(Request $request)
    {
        try {

            $idx = $request->id;
            $data = array(
                'ref_no' => $request->ref_no,
                'trans_date' => $request->trans_date,
                'contract_type' => $request->contract_type,
                'description' => $request->description,
                'uid' => Auth::user()->id,
            );
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->trk_db . '.law_contract')->insert($data);
                $idx = $this->G->pk();
            } else {
                db::table($this->trk_db . '.law_contract')->where('id', $idx)->update($data);
            }

            $files = $request->file('files');
            log::debug($request);
            if (!empty($files)) {
                $path = hash('sha256', time());
                for ($i = 0; $i < count($files); $i++) {
                    $file = $files[$i];
                    $filename = $file->getClientOriginalName();
                    if (Storage::disk('contract')->put($path . '/' . $filename,  File::get($file))) {
                        $data = array(
                            'contract_id' => $idx,
                            'file_name' => $filename,
                            'file_path' => $path,
                            'file_size' => $file->getSize(),
                        );
                        db::table($this->trk_db . '.law_contract_docs')->insert($data);
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
    public function getDocs(Request $request)
    {
        $main = DB::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')
            ->where('stat', '0')
            ->where('doc_details_id', $request->id)
            ->where('entry_type', $request->type)
            ->get();
        return response()->json(new JsonResponse($main));
    }
    public function storeDocumentUpdate(Request $request)
    {
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('tracker_signatory')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'doc_details_id' => $request->id,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'entry_type' => $request->entry_type,
                    );
                    db::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function documentView($id)
    {
        $main = DB::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')->where('id', $id)
            ->where('stat', '0')
            ->get();
        foreach ($main as $key => $value) {
            $file = $value->file_name;
            //  $path = '../storage/files/tracker_signatory/'.$value->file_path.'/'.$file;
            $path = '/storage/files/tracker_signatory/' . $value->file_path . '/' . $file;
            return $path;
            //  if (\File::exists($path)) {
            //  $file = \File::get($path);
            //  $type = \File::mimeType($path);
            //  $response = \Response::make($file, 200);
            //  $response->header("Content-Type", $type);
            //  return $response;
            //  }
        }
    }
    public function uploadRemove($id)
    {
        DB::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')->where('id', $id)->update(['stat' => '1']);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function storeUpdatesCancel($id)
    {
        DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
            ->where('id', $id)
            ->update(['stat' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function storeUpdatesAdditional(Request $request)
    {
        $id = $request->form['id'];

        if ($id > 0) {
            DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
                ->where('id', $id)
                ->update($request->form);
        } else {
            DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
                ->insert($request->form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function storeUpdates(Request $request)
    {
        $id = $request->form['id'];
        if ($id > 0) {
            DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
                ->where('id', $id)
                ->update($request->form);
        } else {
            DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
                ->insert($request->form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function storeComments(Request $request)
    {
        $id = $request->form['id'];
        $request->form['uid'] = Auth::user()->id;
        if ($id > 0) {
            DB::table($this->trk_db . '.documentstrackermain_notes')
                ->where('id', $id)
                ->update($request->form);
        } else {
            DB::table($this->trk_db . '.documentstrackermain_notes')
                ->insert($request->form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function getComments($id)
    {
        $list = DB::table($this->trk_db . '.documentstrackermain_notes')->where('main_id', $id)->get();
        return response()->json(new JsonResponse($list));
    }
    public function storeUpdatesReply(Request $request)
    {
        $id = $request->form['id'];
        $doc_details_id = $request->form['doc_details_id'];
        $datax = $request->datax;
        if ($id > 0) {
            DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
                ->where('id', $id)
                ->update($request->form);
        } else {
            DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
                ->insert($request->form);
        }
        $attch = DB::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')
            ->where('doc_details_id', $doc_details_id)
            ->where('entry_type', 'reply')
            ->where('stat', '0')
            ->get();

        $user = array(
            //  'email'=>'kyllemig143@gmail.com',
            'email' => $datax['email_address'],
            'name' => 'Sender',
            'subject' =>  $datax['Subject'],
            'sender' => $datax['Sender'],
            'updates' => $request->form['updates'],
            'attachment' => $attch,
            'from' => env('MAIL_USERNAME'),
        );

        Mail::mailer('smtp')->send([], array('data' => $request->form['updates']), function ($m) use ($user) {
            // $m->from($user['from'],$user['from']);
            $m->to($user['email'], $user['name'])->subject($user['subject']);
            $m->setBody($user['updates'], 'text/html');
            foreach ($user['attachment'] as $key => $value) {

                $file = $value->file_name;
                $path = '../storage/files/tracker_signatory/' . $value->file_path . '/' . $file;
                if (\File::exists($path)) {
                    log::debug($path);
                    $m->attach($path);
                }
            }
        });
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }

    public function getUpdates(Request $request)
    {
        $data = DB::table($this->trk_db . '.documenttrackerdetails_signatory_updates')
            ->where('doc_details_id', $request->id)
            ->where('stat', 0)
            ->where('trans_type', $request->trans_type)->get();
        return response()->json(new JsonResponse($data));
    }
    public function notes(Request $request)
    {
        try {
            $form = $request->form;
            DB::beginTransaction();
            db::table($this->trk_db . '.documenttrackerdetails_notes')
                ->where('dtls_id', $form['dtls_id'])->delete();

            db::table($this->trk_db . '.documenttrackerdetails_notes')
                ->insert($form);
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $th, 'status' => 'error']));
        }
    }
    public function getNotes($id)
    {
        $list = db::table($this->trk_db . '.documenttrackerdetails_notes')
            ->where('dtls_id', $id)->get();
        return response()->json(new JsonResponse($list));
    }
    public function getPendingCorrespondence(Request $request)
    {
        $list = db::select('CALL ' . $this->trk_db . '.rans_display_pending_correnpondence(?,?)', [$request->from, $request->to]);
        return response()->json(new JsonResponse($list));
    }
    public function printCorrespondingList(Request $request)
    {
        $data = $request->data;
        $filter = $request->filter;
        try {
            $html_content = '<h2 align="center">PENDING CORRESPONDENCES</h2>';
            $html_content .= '<h4 align="center">( From ' . date("F j", strtotime($filter['from'])) . ' - ' . date("F j, Y", strtotime($filter['to'])) . ' )</h4>';
            $html_content .= '
            <table border=".5" cellpadding="2">
               <tr style="text-align:center">
                 <th  width="8%" >Date</th>
                 <th  width="8%" >Ref. No.</th>
                 <th  width="14%">From</th>
                 <th  width="14%">Subject</th>
                 <th  width="14%">Notes/Actions Taken/ Recommendations</th>
                 <th  width="16%">MKVC`s Remarks</th>
                 <th  width="13%">UPDATES</th>
                 <th  width="13%">Status</th>
               </tr>
               <tbody>';
            foreach ($data as $row) {
                $main = ($row);
                if (isset($main['label'])) {
                    $html_content .= '
                 <tr >
                  <td width = "100%" ><b>' . $main['label'] . '</b></td>
                </tr>';
                } else {
                    $html_content .= '
                    <tr >
                    <td width = "8%">' . $main['DateSubmitted'] . '</td>
                    <td width = "8%">' . $main['TrackingNum'] . '</td>
                    <td width = "14%">' . $main['Sender'] . '</td>
                    <td width = "14%">' . $main['Subject'] . '</td>
                    <td width = "14%">' . $main['notes'] . '</td>
                    <td width = "16%">' . $main['mayor'] . '</td>
                    <td width = "13%">' . $main['updates'] . '</td>
                    <td width = "13%">' . $main['status'] . '</td>
                    </tr>';
                }
            }
            $html_content .= '</tbody></table>';

            PDF::SetTitle('PENDING CORRESPONDENCES');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('L');
            PDF::writeHTML($html_content, true, 0, true, 0);
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
