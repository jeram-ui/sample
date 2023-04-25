<?php

namespace App\Http\Controllers\Api\DocumentTrucker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \App\Laravue\JsonResponse;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Str;
use PDF;
use Storage;
use File;

class DocumentController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $proc_db;
    private $empid;
    protected $G;
    private $path;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->middleware('auth');
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
        $this->proc_db = $this->G->getProcDb();
        $this->path = env('LGU_FRONT');
    }
    public function index()
    {
        return view('trucker.Document-Entry');
    }

    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'TRK';
        $table = $this->trk_db . ".documentstrackermain";
        $date = $request->date;
        $refDate = 'DateSubmitted';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function getAssistedName(Request $request){

    $list = db::table($this->trk_db . '.documentstrackermain')
      ->join($this->hr_db.'.employee_information','employee_information.PPID','documentstrackermain.employee')
      ->select(db::raw('DISTINCT(employee_information.PPID) AS "uid",employee_information.NAME AS "name"'))
      ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getDepartMent()
    {
        $data = DB::select(" SELECT `short_desc` FROM `department` WHERE STATUS = 'ACTIVE'   AND LENGTH(short_desc)>1 ORDER BY short_desc");
        return response()->json(new JsonResponse($data));
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

    public function edit(Request $request, $id)
    {
        $data = DB::select("select * from  " . $this->trk_db . ".documentstrackermain where ID = '$id'");
        return response()->json(new JsonResponse(['data' => $data]));
    }

    public function show(Request $request)
    {
        $trk_db = $this->trk_db;
        $result_set = DB::select("CALL " . $this->trk_db . ".rans_display_doc('" . $request->from . "','" . $request->to . "','%%','%%')");
        return response()->json(new JsonResponse(['data' => $result_set]));
    }
    public function showPerType(Request $request)
    {
        $trk_db = $this->trk_db;
        $id = $request->flowid;
        $result_set = DB::select("CALL " . $this->trk_db . ".rans_display_doc_per_type('" . $request->from . "','" . $request->to . "','" . $id . "','%%','%%')");
        return response()->json(new JsonResponse(['data' => $result_set]));
    }
    public function showAll(Request $request)
    {
        $result_set = DB::select("CALL " . $this->trk_db . ".rans_display_doc_All2('".$request->type."','" . $request->from . "','" . $request->to . "','%%','%%','".$request->uid."%')");
        return response()->json(new JsonResponse($result_set));
    }
    public function showPerNumber(Request $request)
    {
        $id = $request->id;
        $result_set = DB::select("CALL " . $this->trk_db . ".rans_display_doc_per_number(?)", [$id]);
        return response()->json(new JsonResponse($result_set));
    }
    public function showStatus(Request $request, $id)
    {
        $result_set = DB::select("call " . $this->trk_db . ".rans_display_docs_status(?)", [$id]);
        return response()->json(new JsonResponse(['data' => $result_set]));
    }
    public function storeEmail(Request $request)
    {
        $form = $request->form;
        $trk_db = $this->trk_db;
        $id = $form['id'];
        if ($id == 0) {
            db::table($trk_db . '.doc_client_email')
                ->insert($form);
        } else {
            db::table($trk_db . '.doc_client_email')
                ->where('id', $id)
                ->update($form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function getSigantorySetup($id)
    {
        $trk_db = $this->trk_db;
        $sig = db::table($trk_db . '.doc_flow_main')
            ->join($trk_db . '.doc_flow_employee', 'doc_flow_employee.doc_flow_main_id', '=', 'doc_flow_main.id')
            ->leftJoin($trk_db . '.doc_flow_employee_details', 'doc_flow_employee_details.sig_id', '=', 'doc_flow_employee.id')
            ->where('doc_flow_main.id', $id)
            ->where('doc_flow_employee.Signatory_name', 'doc_flow_main.second_signatory')->get();
        return response()->json(new JsonResponse($sig));
    }
    public function EmailList(Request $request)
    {
        $trk_db = $this->trk_db;
        $data = db::table($trk_db . '.doc_client_email')->where('stat', 0)->get();
        return response()->json(new JsonResponse($data));
    }
    public function documentstraker_category(){
        $trk_db = $this->trk_db;
        $data = db::table($trk_db.'.documentstraker_category')->get();
        return response()->json(new JsonResponse($data));
    }
    public function done(Request $request)
    {

        $form = $request->form;
        $main = $request->main;
        $trk_db = $this->trk_db;
        $id = $main['ID'];
        db::table($trk_db . '.documentstrackermain')
            ->where('ID', $id)
            ->update(['date_done' => $form['date_done'], 'done_uid' => Auth::user()->Employee_id]);


        $chk = db::table($this->trk_db . '.documenttrackerdetails')
            ->where('doc_main', $id)
            ->where('doc_status', 0)
            ->orderBy('id', 'asc')->first();

        db::table($trk_db . '.documenttrackerdetails')
            ->where('id', $chk->id)
            ->update(['doc_status' => 1, 'forwarded' => 1, 'forward_ts' => $this->G->serverdatetime()]);
        db::table($trk_db . '.documenttrackerdetails')
            ->where('doc_main', $id)
            ->where('emp_id',Auth::user()->Employee_id)
            ->update(['doneRead'=>1])
            ;

        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function store(Request $request)
    {

        $lgu_db = $this->lgu_db;
        $trk_db = $this->trk_db;
        DB::beginTransaction();
        $idx = $request['form']['ID'];

        $signatoryData =  $request['signatory']['signatoryData'];
        $signatoryname = $request['signatory']['signatory'];
        // $data = request()->all();
        log::debug($request['form']);
        $data = $request['form'];
        $data['GUID'] = $this->G->getGuid();
        try {
            if ($idx > 0) {
                DB::table($trk_db . '.documentstrackermain')
                    ->where('ID', $idx)
                    ->update($data);

                $chk = DB::table($trk_db . '.documenttrackerdetails')
                    ->where('doc_main', $idx)
                    ->where('doc_status', 1)->count();
                if ($chk == 0) {
                    // DB::table($trk_db . '.documenttrackerdetails')->where('doc_main', $idx)->delete();
                    // $id = $idx;
                    // $details = DB::select('SELECT * FROM ' . $trk_db . '.doc_flow_employee WHERE `doc_flow_main_id` = "' . $request['form']['flow_id'] . '"');
                    // $sig_setup = db::table($trk_db . '.doc_flow_main')
                    //     ->join($trk_db . '.doc_flow_employee', 'doc_flow_employee.doc_flow_main_id', '=', 'doc_flow_main.id')
                    //     ->leftJoin($trk_db . '.doc_flow_employee_details', 'doc_flow_employee_details.sig_id', '=', 'doc_flow_employee.id')
                    //     ->select('doc_flow_employee.id as sig_id', 'doc_flow_employee_details.emp_id', 'signatory_name', 'doc_flow_employee_details.display_name')
                    //     ->where('doc_flow_main.id', $request['form']['flow_id'])
                    //     ->where('Signatory_name', '<>', $signatoryname)
                    //     ->orderBy('doc_flow_employee.id')
                    //     ->get();
                    // log::debug($sig_setup);

                    // db::table($trk_db . '.documenttracketdetails_employee_signatory')
                    //     ->where('doc_main_id', $id)
                    //     ->delete();
                    // foreach ($sig_setup as $key => $value) {
                    //     $sig = array(
                    //         'doc_main_id' => $id,
                    //         'sig_id' => $value->sig_id,
                    //         'emp_id' => $value->emp_id,
                    //         'sig_name' => $value->signatory_name,
                    //         'display_name' => $value->display_name,
                    //     );
                    //     db::table($trk_db . '.documenttracketdetails_employee_signatory')
                    //         ->insert($sig);
                    //     db::table($trk_db . '.documentstrackermain')
                    //         ->where('ID', $id)
                    //         ->update(['last_stat' => 'Released']);
                    //     if ($key == 0) {
                    //         db::table($trk_db . '.documentstrackermain')
                    //             ->where('ID', $id)
                    //             ->update(['main_stat' => $value->signatory_name]);
                    //     }
                    // }
                    // $sort = 0;
                    // if (count($details) == 0) {
                    //     $array = array(
                    //         'doc_main' => $id,
                    //         'flow_id' => 0,
                    //         'emp_id' => $request->employee,
                    //         'flow_signatory' => $request->DocumentFlowCode,
                    //         'sort_id' => $sort,
                    //     );
                    //     $sort = $sort + 1;
                    //     DB::table($trk_db . '.documenttrackerdetails')->insert($array);
                    // }

                    // foreach ($details as $row) {
                    //     $array = array(
                    //         'doc_main' => $id,
                    //         'flow_id' => $request->flow_id,
                    //         'emp_id' => $row->emp_id,
                    //         'flow_signatory' => $row->Signatory_name,
                    //         'sort_id' => $sort,
                    //     );
                    //     $sort = $sort + 1;
                    //     DB::table($trk_db . '.documenttrackerdetails')->insert($array);
                    // }

                    // $idx = $id;
                    // db::table($this->trk_db . '.documenttracketdetails_employee_signatory')
                    //     ->where('doc_main_id', $id)
                    //     ->where('sig_name', $signatoryname)->delete();;
                    // if (count($signatoryData) > 0) {
                    //     $updateReceived =  DB::table($trk_db . '.documenttrackerdetails')
                    //         ->where('doc_main', $idx)->first();
                    //     db::table($trk_db . '.documentstrackermain')->where('ID', $idx)
                    //         ->update(['main_stat' => $signatoryname]);
                    //     DB::table($trk_db . '.documenttrackerdetails')
                    //         ->where('id', $updateReceived->id)
                    //         ->update(['emp_id' => Auth::user()->Employee_id, 'doc_ts' => $this->G->serverdatetime(), 'received' => 1, 'date_received' => $this->G->serverdatetime()]);
                    //     db::table($trk_db . '.documenttrackerdetails_notes')
                    //         ->insert(['dtls_id' => $updateReceived->id, 'notes' => $data['Remarks']]);
                    //     foreach ($signatoryData as $key => $value) {
                    //         $sig = array(
                    //             'doc_main_id' => $idx,
                    //             'sig_name' => $signatoryname,
                    //             'emp_id' => $value['PPID'],
                    //             'display_name' => $value['NAME'],
                    //         );
                    //         db::table($this->trk_db . '.documenttracketdetails_employee_signatory')->insert($sig);
                    //     }
                    // }
                }
            } else {
                $data['employee'] = Auth::user()->Employee_id;
                // $data['employee']
                DB::table($trk_db . '.documentstrackermain')->insert($data);
                $id = DB::getPdo()->lastInsertId();

                $details = DB::select('SELECT * FROM ' . $trk_db . '.doc_flow_employee WHERE `doc_flow_main_id` = "' . $request['form']['flow_id'] . '"');
                // log::debug($details);
                $sig_setup = db::table($trk_db . '.doc_flow_main')
                    ->join($trk_db . '.doc_flow_employee', 'doc_flow_employee.doc_flow_main_id', '=', 'doc_flow_main.id')
                    ->leftJoin($trk_db . '.doc_flow_employee_details', 'doc_flow_employee_details.sig_id', '=', 'doc_flow_employee.id')
                    ->select('doc_flow_employee.id as sig_id', 'doc_flow_employee_details.emp_id', 'signatory_name', 'doc_flow_employee_details.display_name')
                    ->where('doc_flow_main.id', $request['form']['flow_id'])
                    ->where('Signatory_name', '<>', $signatoryname)
                    ->orderBy('doc_flow_employee.id')
                    ->get();

                foreach ($sig_setup as $key => $value) {
                    $sig = array(
                        'doc_main_id' => $id,
                        'sig_id' => $value->sig_id,
                        'emp_id' => $value->emp_id|Auth::user()->Employee_id,
                        'sig_name' => $value->signatory_name|'Step 1',
                        'display_name' => $value->display_name,
                    );
                    db::table($trk_db . '.documenttracketdetails_employee_signatory')
                        ->insert($sig);
                    db::table($trk_db . '.documentstrackermain')
                        ->where('ID', $id)
                        ->update(['last_stat' => $value->signatory_name]);
                    if ($key == 0) {
                        db::table($trk_db . '.documentstrackermain')
                            ->where('ID', $id)
                            ->update(['main_stat' => $value->signatory_name]);
                    }
                }
                $sort = 0;
                if (count($details) == 0) {
                    $array = array(
                        'doc_main' => $id,
                        'flow_id' => 0,
                        'emp_id' => $request->employee,
                        'flow_signatory' => $request->DocumentFlowCode,
                        'sort_id' => $sort,
                    );
                    $sort = $sort + 1;
                    DB::table($trk_db . '.documenttrackerdetails')->insert($array);
                }

                foreach ($details as $row) {
                    $array = array(
                        'doc_main' => $id,
                        'flow_id' => $request->flow_id,
                        'emp_id' => $row->emp_id,
                        'flow_signatory' => $row->Signatory_name,
                        'sort_id' => $sort,
                    );
                    $sort = $sort + 1;
                    DB::table($trk_db . '.documenttrackerdetails')->insert($array);
                }

                $idx = $id;

                $updateReceived =  DB::table($trk_db . '.documenttrackerdetails')
                    ->where('doc_main', $idx)->first();

                DB::table($trk_db . '.documenttrackerdetails')
                    ->where('id', $updateReceived->id)
                    ->update(['emp_id' => Auth::user()->Employee_id, 'received' => 1, 'date_received' => $this->G->serverdatetime()]);
                db::table($trk_db . '.documentstrackermain')->where('ID', $idx)
                    ->update(['main_stat' => $updateReceived->flow_signatory]);
                db::table($trk_db . '.documenttrackerdetails_notes')
                    ->insert(['dtls_id' => $updateReceived->id, 'notes' => $data['Remarks']]);

                if (count($signatoryData) > 0) {
                    db::table($trk_db . '.documentstrackermain')->where('ID', $idx)
                        ->update(['main_stat' => $signatoryname]);

                    DB::table($trk_db . '.documenttrackerdetails')
                        ->where('id', $updateReceived->id)
                        ->update(['emp_id' => Auth::user()->Employee_id, 'doc_status' => 1, 'doc_ts' => $this->G->serverdatetime(), 'forwarded' => 1, 'forward_ts' => $this->G->serverdatetime(), 'received' => 1, 'date_received' => $this->G->serverdatetime()]);

                    foreach ($signatoryData as $key => $value) {
                        $sig = array(
                            'doc_main_id' => $idx,
                            'sig_name' => $signatoryname,
                            'emp_id' => $value['PPID'],
                            'display_name' => $value['NAME'],
                        );
                        db::table($this->trk_db . '.documenttracketdetails_employee_signatory')->insert($sig);
                        $chkDetails = db::table($this->trk_db . '.documenttrackerdetails')
                        ->where("flow_signatory",$signatoryname)
                        ->where("doc_main",$idx)->count();
                        if ($chkDetails ==0) {
                            $array = array(
                                'doc_main' => $id,
                                'flow_signatory' => $signatoryname,
                                'sort_id' => $sort,
                            );
                            $sort = $sort + 1;
                            DB::table($trk_db . '.documenttrackerdetails')->insert($array);
                        }
                    }
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['id'=>$id,'Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function cancel(Request $request, $id)
    {
        $id = $id;
        $ck =
            $data['status'] = 'CANCELLED';
        DB::table($this->trk_db . '.documentstrackermain')
            ->where('ID', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function uploadFile(Request $request)
    {
        log::debug($request);
        $Id = $request->id;
        // if ($files =  $request->file('file')) {
        //     foreach ($request->file('file') as $key => $file) {
        //         $originalImage= $file;
        //         $thumbnailPath = public_path().'/images/tracker/'. $Id ."/";
        //         $this->G->createFolder($thumbnailPath);
        //         $time = Str::random(5);
        //         $originalImage->move($thumbnailPath, $time. '.' .$originalImage->getClientOriginalExtension());
        //         $data = array(
        //             'main_id'=>$Id,
        //             'file_name'=>$originalImage->getClientOriginalName(),
        //             'file_path' =>$time. '.' .$originalImage->getClientOriginalExtension(),
        //         );
        //         db::table($this->trk_db.'.documentstracke_files')->insert($data);
        //     }
        // }
        $files = $request->file('file');
        $dtData = db::table($this->trk_db . '.documenttrackerdetails')->where('doc_main', $Id)->first();
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();

                if (Storage::disk('tracker_signatory')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'doc_details_id' => $dtData->id,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                    );
                    db::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function uploaded($id)
    {

        $dtData = db::table($this->trk_db . '.documenttrackerdetails')->where('doc_main', $id)->first();
        $id = $dtData->id;

        $main = DB::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')
            ->where('stat', '0')
            ->where('doc_details_id', $id)->get();

        return response()->json(new JsonResponse($main));
    }

    public function uploadedRemove($id)
    {

        DB::table($this->trk_db . '.documenttrackerdetails_signatory_uploded')->where('id', $id)->update(['stat' => '1']);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function printDoc(Request $request){
        try{
            $list = $request->list;

            $documentx = "";

            foreach ($list as $key => $value) {
                // log::debug($value->TrackingNum);
                $documentx .='
                     <tr>

                <td style="font-size:8pt" align="center">' . $value['TrackingNum'] . '</td>
                <td style="font-size:8pt" align="center">' . $value['ExpectedReturn'] . '</td>
                <td style="font-size:8pt" align="center">' . $value['Days Left'] . '</td>
                <td style="font-size:8pt" align="center">' . date_format(date_create($value['date_done']), "m/d/Y h:i:s A") . '</td>
                <td style="font-size:8pt" align="left">' . $value['Sender'] . '</td>
                <td style="font-size:8pt" align="justify">' . $value['Subject'] . '</td>
                <td style="font-size:8pt" align="center">' . $value['Remarks'] . '</td>
                <td style="font-size:8pt" align="center">' . $value['Details'] . '</td>
                <td style="font-size:8pt" align="center">' . $value['docs_qr'] . '</td>

            </tr>';

            }


            $Template='<table width="100%" cellpadding="3">
            <tr>
            <br />
            <th width="42%" align="right"><img src="' . public_path() . '/img/logo1.png"  height="30" width="30"></th>
            <th width="20%" style="font-size:11pt;  word-spacing:30px" align="center">Republic of the Philippines
            <br />
                    Province of Cebu
            <br />
                   City of Naga
            <br />
            </th>
            <th align="left"><img src="/img/NAGA LOGO2.png"  height="40" width="45"></th>
            </tr>
            <table border=".3" width="100%" cellpadding="3">
                <tr>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Ref #</b></td>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Target Date</b></td>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Days Left</b></td>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Date Done</b></td>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Sender</b></td>
                    <td width="30%" style="font-size:9pt; text-align: center"><b>Subject</b></td>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Remarks</b></td>
                    <td width="5%" style="font-size:9pt; text-align: center"><b>Details</b></td>
                    <td width="5%" style="font-size:9pt; text-align: center"><b>QR</b></td>

                </tr>
                '.$documentx.'

            </table>
            </table>
            ';

            PDF::SetTitle('Document Tracker');
            PDF::SetFont('helvetica', '', 8);
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
       public function print_TO(Request $request){
        try{

            // $main = $request->itm;
            // $projectx = db::table($this->proc_db . '.pow_main_individual')
            // ->join($this->proc_db .'.pow_detail_individual','pow_detail_individual.main_id','pow_main_individual.id')
            // ->join($this->proc_db . '.pow_sof_detail', 'pow_sof_detail.pow_id', 'pow_main_individual.id')
            // ->select('*',DB::raw('pow_sof_detail.SOF_Description as description'))
            // ->where('pow_main_individual.id',$main)
            // ->get();
            // $projectDatax = "";

            // foreach ($projectx as $key => $value) {
            // $projectDatax =$value;
            // }

            // $details =db::table($this->proc_db . '.pow_detail_individual')
            // ->where('pow_detail_individual.main_id',$main)
            // ->get();
            // $dtlData = "";

            
            // foreach ($details as $key => $value) {
            //     $key +=1;
            //     $dtlData .=' 
            //     <tr>
            //         <td style="font-size:8pt" align="center">'.$key.'</td>
            //         <td style="font-size:8pt" align="center">'.$value->qty.'</td>
            //         <td style="font-size:8pt" align="center">'.$value->unit.'</td>
            //         <td style="font-size:8pt" align="center">'.$value->description.'</td>
            //         <td style="font-size:8pt" align="right">'.number_format($value->unit_cost,2).'</td>
            //         <td style="font-size:8pt" align="right">'.number_format($value->total_cost,2).'</td>
            //     </tr>';
            // }
            
            $Template='<table width="100%" cellpadding="3">
            <tr>
            <br />
            <th width="30%" align="right"><img src="' . public_path() . '/img/logo1.png"  height="40" width="40"></th>
            <th width="40%" style="font-size:10pt;  word-spacing:30px" align="center">Republic of the Philippines
            <br />
                    Province of Cebu
            <br />
                   City of Naga</th>
            <th align="left"><img src="' . public_path() . '/img/Logo3.png"  height="45" width="65"></th>
            </tr>
            <tr>
                <th width="100%" style="font-size:11pt;  word-spacing:30px" align="center"><b>OFFICE OF THE CITY MAYOR</b></th>
            </tr>
            <br />
            <br />
            <tr>
                <td style="font-size:12pt" align="center"><b>TRAVEL ORDER</b></td>
            </tr>
            <br />
            <br />
            <tr>
                <td width="5%"></td>
                <td width="30%" align="left" style="font-size: 11pt">April 08, 2022</td>
            </tr>
            <tr>
            <br />
            <br />
                <td width="5%"></td>
                <td width="10%" align="left" style="font-size: 11pt">To</td>
                <td width="2%" align="left">:</td>
                <td width="80%" align="left" style="font-size: 11pt"><b>* Ababa, Dexter L.</b></td>
            </tr>
            <tr>
                <td width="5%"></td>
                <td width="10%" align="left" style="font-size: 11pt">From</td>
                <td width="2%" align="left">:</td>
                <td width="80%" align="left" style="font-size: 11pt"><b>&nbsp;&nbsp;City Mayor</b></td>
            </tr>
            <br />
            <br />
            <br />
            <br />
            <tr>
                <td width="5%"></td>
                <td width="100%" style="text-align:justify" style="font-size: 11pt"><p>You are hereby authorized to represent our government Unit to csc, on April 08, 2022 To April 10, 2022</p></td>
            </tr>
            <br />
            <tr>
                <td width="5%"></td>
                <td width="12%" align="left" style="font-size: 11pt">Purpose:</td>
                <td width="83%" align="left" style="font-size: 11pt">TO FOLLOW-UP DOCUMENT.</td>
            </tr>
            <br />
            <tr>
                <td width="5%"></td>
                <td width="95%" align="left" style="font-size: 11pt">For your information and guidance.</td>
            </tr>
            <br />
            <br />
            <br />
            <tr>
                <td width="65%" align="right"></td>
                <td width="30%" style="font-size: 11pt; border-bottom: 1px solid black; text-align:center"><b>Valdemar Mendiola Chiong</b></td>
                <td width="5%"></td>
            </tr>
            <tr>
                <td width="65%" align="right"></td>
                <td width="30%" style="font-size: 11pt; text-align:center">City Mayor</td>
                <td width="5%"></td>
            </tr>


            
            </table>
            ';

            PDF::SetTitle('Travel Order');
            PDF::SetFont('helvetica', '', 10);
            PDF::AddPage('P');
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
       public function printProposal(Request $request){
        try{

            $main = 596;
            $projectx = db::table($this->proc_db . '.pow_main_individual')
            ->join($this->proc_db .'.pow_detail_individual','pow_detail_individual.main_id','pow_main_individual.id')
            ->join($this->proc_db . '.pow_sof_detail', 'pow_sof_detail.pow_id', 'pow_main_individual.id')
            ->select('*',DB::raw('pow_sof_detail.SOF_Description as description'))
            ->where('pow_main_individual.id',$main)
            ->get();
            $projectDatax = "";

            foreach ($projectx as $key => $value) {
            $projectDatax =$value;
            }

            $details =db::table($this->proc_db . '.pow_detail_individual')
            ->where('pow_detail_individual.main_id',$main)
            ->get();
            $dtlData = "";

            
            foreach ($details as $key => $value) {
                $key +=1;
                $dtlData .=' 
                <tr>
                    <td style="font-size:8pt" align="center">'.$key.'</td>
                    <td style="font-size:8pt" align="center">'.$value->qty.'</td>
                    <td style="font-size:8pt" align="center">'.$value->unit.'</td>
                    <td style="font-size:8pt" align="center">'.$value->description.'</td>
                    <td style="font-size:8pt" align="right">'.number_format($value->unit_cost,2).'</td>
                    <td style="font-size:8pt" align="right">'.number_format($value->total_cost,2).'</td>
                </tr>';
            }
            
            $Template='<table width="100%" cellpadding="3">
            <tr>
            <br />
            <th width="30%" align="right"><img src="' . public_path() . '/img/logo1.png"  height="40" width="40"></th>
            <th width="40%" style="font-size:10pt;  word-spacing:30px" align="center">Republic of the Philippines
            <br />
                    Province of Cebu
            <br />
                   City of Naga</th>
            <th align="left"><img src="' . public_path() . '/img/Logo2.png"  height="45" width="65"></th>
            </tr>
            <tr>
                <td width="76%" align="right" style="font-size:8pt; border-bottom:3px solid black"><b>TXN #:</b></td>
                <td width="1%" align="right" style="font-size:8pt; border-bottom:3px solid black"><b></b></td>
                <td width="23%" align="left" style="font-size:8pt; border-bottom:3px solid black">'.$projectDatax->TXN.'</td>
            </tr>
            <br />
            <tr>
                <td width="100%" align="center" style="font-size:11pt"><b>PROJECT PROPOSAL</b></td>
            </tr>
            <br />
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Date</b></td>
                <td width="80%" align="left" style="font-size:9pt">' . date_format(date_create($projectDatax->reference_date),"m/d/Y")  . '</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Name of Project</b></td>
                <td width="80%" align="left" style="font-size:9pt">'.$projectDatax->project_title.'</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Source of Fund</b></td>
                <td width="80%" align="left" style="font-size:9pt">'.$projectDatax->description.'</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Amount</b></td>
                <td width="80%" align="left" style="font-size:9pt">'.number_format($projectDatax->appropriation,2).'</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Rationale</b></td>
                <td width="80%" align="left" style="font-size:9pt">'.$projectDatax->rationale.'</td>
            </tr>
            <br />
            <tr>
                <td width="100%" align="left" style="font-size:9pt"><b>Details:</b></td>
            </tr>
            <table border=".3" width="100%" cellpadding="3">
                <tr>
                    <td width="7%" style="font-size:9pt; text-align: center"><b>Item No.</b></td>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Quantity</b></td>
                    <td width="11%" style="font-size:9pt; text-align: center"><b>Unit Measure</b></td>
                    <td width="48%" style="font-size:9pt; text-align: center"><b>Articles and Description</b></td>
                    <td width="12%" style="font-size:9pt; text-align: center"><b>Unit Cost</b></td>
                    <td width="12%" style="font-size:9pt; text-align: center"><b>Total Cost</b></td>
                </tr>
                '.$dtlData.'
                <tr>
                    <td width="7%" style="font-size:8pt; text-align: center"></td>
                    <td width="10%" style="font-size:8pt; text-align: center"></td>
                    <td width="11%" style="font-size:8pt; text-align: center"></td>
                    <td width="48%" style="font-size:8pt; text-align: center">***nothing follows***</td>
                    <td width="12%" style="font-size:8pt; text-align: right"></td>
                    <td width="12%" style="font-size:8pt; text-align: right"></td>
                </tr>
                <tr>
                    <td width="17%" style="font-size:8pt; text-align: left"><b>DELIVERY TERM:</b></td>
                    <td width="59%" style="font-size:8pt; text-align: left">'.$projectDatax->project_desc.'</td>
                    <td width="12%" style="font-size:8pt; text-align: right"><b>Total:</b></td>
                    <td width="12%" style="font-size:8pt; text-align: right"><b>'.number_format($projectDatax->appropriation,2).'</b></td>
                </tr>
            </table>
            
            </table>
            ';
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Prepared by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Requested by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Prepared by:</td>
            //     </tr>
                
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>

            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Admin. Aide I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Admin. Aide I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Admin. Aide III</td>
            //     </tr>
            // <br />
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Verified by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Recommending Approval:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Ok as to Appropriation:</td>
            //     </tr>
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">CGDH I (City Government Department</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Administrator II</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Budget Officer</td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Head) I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left"></td>
            //     </tr>
            // <br />
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Ok as to Fund:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Approved by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Noted by:</td>
            //     </tr>
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Treasurer I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Mayor</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Association of Barangay Councils President</td>
            //     </tr>

            PDF::SetTitle('Project Proposal');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('P');
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
    public function printqr(Request $request)
    {
        try {

            $data = $request->data;
            $direction = $data['direction'];
            $location = $data['position'];
            log::debug($data);
            PDF::AddPage('P', array(230.9, 330.2));
            PDF::SetTitle('Tracker');
            PDF::SetHeaderMargin(2);
            PDF::SetTopMargin(2);
            PDF::SetMargins(2, 2, 2, 2);
            PDF::SetFont('Helvetica', '', 8);
            // -- set new background ---
            $bMargin = PDF::getBreakMargin();
            $auto_page_break = PDF::getAutoPageBreak();
            PDF::SetAutoPageBreak(false, 0);
            PDF::SetAutoPageBreak($auto_page_break, $bMargin);
            if ($location === 'Bottom') {
                PDF::SetXY(15, 280);
            }
            PDF::setPageMark();
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            $params1 = PDF::serializeTCPDFtagParameters(array($data['TrackingNum'], 'QRCODE,H', '', '', 20, 20, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));

            $Template = '
                    <div style="text-align: left;"  >
                     <table  style="" cellpadding ="4" width = "100%" >
                       <tr>
                       <td width = "40%" >';
            if ($direction === 'Left') {
                $Template .= ' <table cellpadding ="1"  width = "100%"  >
              <tr>
                 <th  style="font-size:10px;" width="25%" ><tcpdf method="write2DBarcode" params="' . $params1 . '" /></th>
                 <th  style="font-size:8px;" width="75%">
                 <table cellpadding ="1" width = "100%">
            <tr>
             <th width ="18%">Ref</th>
             <th width ="2%">:</th>
             <th width ="80%">' . $data['TrackingNum'] . '</th>
            </tr>
            <tr>
               <th width ="18%">Sender</th>
               <th width ="2%">:</th>
               <th width ="80%">' . $data['Sender'] . '</th>
            </tr>
            <tr>
               <th width ="18%">Subject</th>
               <th width ="2%">:</th>
               <th width ="80%">' . $data['Subject'] . '</th>
            </tr>
         <tr>
            <th width ="18%">Received</th>
            <th width ="2%">:</th>
            <th width ="80%">' . date_format(date_create($data['DateSubmitted']),"m/d/Y H:i A")  . '</th>
         </tr>
         <tr>
           <th width ="18%">Date Due</th>
           <th width ="2%">:</th>
           <th width ="80%">' . date_format(date_create($data['ExpectedReturn']),"m/d/Y")  . '</th>
         </tr>
          </table>
       </th>
    </tr>
</table>';
            }


            $Template .= '</td>
                    <td width = "20%" ></td>';

            if ($direction === 'Right') {
                $Template .= '<td width = "40%" >
                        <table cellpadding ="1"  width = "100%"  >
                            <tr>
                               <th  style="font-size:10px;" width="25%" ><tcpdf method="write2DBarcode" params="' . $params1 . '" /></th>
                               <th  style="font-size:8px;" width="75%">
                                  <table cellpadding ="1" width = "100%">
                                    <tr>
                                     <th width ="15%">Ref</th>
                                     <th width ="5%">:</th>
                                     <th width ="80%">' . $data['TrackingNum'] . '</th>
                                    </tr>
                                    <tr>
                                       <th width ="15%">Sender</th>
                                       <th width ="5%">:</th>
                                       <th width ="80%">' . $data['Sender'] . '</th>
                                    </tr>
                                    <tr>
                                       <th width ="15%">Subject</th>
                                       <th width ="5%">:</th>
                                       <th width ="80%">' . $data['Subject'] . '</th>
                                    </tr>
                                    <tr>
                                       <th width ="18%">Received</th>
                                       <th width ="2%">:</th>
                                       <th width ="80%">' . date_format(date_create($data['DateSubmitted']),"m/d/Y H:i A")  . '</th>
                                    </tr>
                                 <tr>
                                   <th width ="18%">Date Due</th>
                                   <th width ="2%">:</th>
                                   <th width ="80%">' . date_format(date_create($data['ExpectedReturn']),"m/d/Y")  . '</th>
                                 </tr>
                                  </table>
                               </th>
                            </tr>
                        </table>
                    </td>';
            }
            $Template .= '</tr>
                 </table>
                     </div>
                ';
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
    public function undone(Request $request)
    {

        // log::debug($request);
        db::table($this->trk_db . '.documentstrackermain')
            ->where("ID", $request->ID)
            ->update(['date_done' => null, 'done_uid' => 0]);

        $chk = db::table($this->trk_db . '.documenttrackerdetails')
            ->where('doc_main', $request->ID)
            ->where('doc_status', 1)
            ->orderBy('id', 'desc')->first();
        // log::debug($chk);

        db::table($this->trk_db . '.documenttrackerdetails')
            ->where('id', $chk->id)
            ->update(['doc_status' => 0, 'forwarded' => 0, 'forward_ts' => null]);

        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function allCommunications()
    {
        $data = db::select('call ' . $this->trk_db . '.rans_get_all_communications()');
        return response()->json(new JsonResponse($data));
    }
}
