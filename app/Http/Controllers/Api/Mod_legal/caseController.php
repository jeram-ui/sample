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

class caseController extends Controller
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
        $pre = 'CSN';
        $table = $this->lgu_db . ".law_cases_entry";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function show()
    {
        // $law=DB::table($this->lgu_db.'.law_cases_entry_law')
        // ->select( 'case_id',DB::raw('group_concat(Description separator "<br>") as law_description'))
        // ->groupBy('case_id');

        // $client=DB::table($this->lgu_db.'.law_cases_entry_client')
        // ->join($this->lgu_db.'.law_client','law_client.patient_id','=','law_cases_entry_client.client_id')
        // ->select('case_entry_id',db::raw("group_concat(TRIM(CASE WHEN save_type = 'ClientPerson' THEN CONCAT(IFNULL(`prefix`,''),`fname`,' ',IFNULL(`mname`,''),' ',IFNULL(`lname`,''),' ',IFNULL(`suffix`,''))
        // ELSE `company_name`
        //  END) separator '<br/>') as client"))
        //  ->where('law_cases_entry_client.entry_type','client')
        // ->groupBy('case_entry_id');


        // $opponent=DB::table($this->lgu_db.'.law_cases_entry_client')
        // ->join($this->lgu_db.'.law_client','law_client.patient_id','=','law_cases_entry_client.client_id')
        // ->select('case_entry_id',db::raw("group_concat(TRIM(CASE WHEN save_type = 'OPPONENT' THEN CONCAT(IFNULL(`prefix`,''),`fname`,' ',IFNULL(`mname`,''),' ',IFNULL(`lname`,''),' ',IFNULL(`suffix`,''))
        // ELSE `company_name`
        //  END) separator '<br/>') as opponents"))
        //  ->where('law_cases_entry_client.entry_type','opponent')
        // ->groupBy('case_entry_id');

        // $update = db::table($this->lgu_db.'.law_case_update')
        // ->select('case_id','trans_date','comments')
        // ->where('stat',0)
        // ->groupBy('case_id')
        // ->orderBy('id','desc');

        // $main = db::table($this->lgu_db.'.law_cases_entry')
        // ->joinSub($law, 'law', function ($join) {
        //     $join->on('law_cases_entry.ID', '=', 'law.case_id');
        // })
        // ->leftJoinSub($client, 'client', function ($join) {
        //     $join->on('law_cases_entry.ID', '=', 'client.case_entry_id');
        // })
        // ->leftJoinSub($opponent, 'opponent', function ($join) {
        //     $join->on('law_cases_entry.ID', '=', 'opponent.case_entry_id');
        // })
        // ->leftJoinSub($update, 'update', function ($join) {
        //     $join->on('law_cases_entry.ID', '=', 'update.case_id');
        // })
        // ->join($this->lgu_db.'.law_court_setup','law_court_setup.id','=','law_cases_entry.court_id')
        // ->where('law_cases_entry.Status',0)
        // ->groupBy('law_cases_entry.ID')
        // ->get();
        $main = db::select('call ' . $this->lgu_db . '.rans_law_display_case_list();');
        return response()->json(new JsonResponse($main));
    }

    public function store(Request $request)
    {
        try {
            $main = $request->form;
            $law = $request->charges;
            $client = $request->client;
            $opponent = $request->opponent;
            $idx = $main['ID'];
            log::debug($request);
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_cases_entry')->insert($main);
                $idx = $this->G->pk();
                // db::table('')
                foreach ($client as $row) {
                    $data = array(
                        'case_entry_id' => $idx, 'consultation_id' => $row['consultation_id'], 'client_id' => $row['client_id'], 'client_name' => $row['client_name'], 'lawyer_id' => $row['lawyer_id'], 'lawyer_name' => $row['lawyer_name'], 'entry_type' => $row['entry_type']
                    );
                    db::table($this->lgu_db . '.law_cases_entry_client')->insert($data);
                }
                foreach ($opponent as $row) {
                    $data = array(
                        'case_entry_id' => $idx, 'consultation_id' => $row['consultation_id'], 'client_id' => $row['client_id'], 'client_name' => $row['client_name'], 'lawyer_id' => $row['lawyer_id'], 'lawyer_name' => $row['lawyer_name'], 'entry_type' => $row['entry_type']
                    );
                    db::table($this->lgu_db . '.law_cases_entry_client')->insert($data);
                }
                foreach ($law as $row) {
                    $data = array(
                        'case_id' => $idx, 'law_id' => $row['law_id'], 'Count' => $row['Count'], 'Description' => $row['Description']
                    );
                    db::table($this->lgu_db . '.law_cases_entry_law')->insert($data);
                }
            } else {
                db::table($this->lgu_db . '.law_cases_entry')->where('ID', $idx)->update($main);
                db::table($this->lgu_db . '.law_cases_entry_client')->where('case_entry_id', $idx)->delete();
                foreach ($client as $row) {
                    $data = array(
                        'case_entry_id' => $idx, 'client_id' => $row['client_id'], 'client_name' => $row['client_name'], 'lawyer_id' => $row['lawyer_id'], 'lawyer_name' => $row['lawyer_name'], 'entry_type' => $row['entry_type']
                    );
                    db::table($this->lgu_db . '.law_cases_entry_client')->insert($data);
                }
                foreach ($opponent as $row) {
                    $data = array(
                        'case_entry_id' => $idx, 'consultation_id' => $row['consultation_id'], 'client_id' => $row['client_id'], 'client_name' => $row['client_name'], 'lawyer_id' => $row['lawyer_id'], 'lawyer_name' => $row['lawyer_name'], 'entry_type' => $row['entry_type']
                    );
                    db::table($this->lgu_db . '.law_cases_entry_client')->insert($data);
                }
                db::table($this->lgu_db . '.law_cases_entry_law')->where('case_id', $idx)->delete();
                foreach ($law as $row) {
                    $data = array(
                        'case_id' => $idx, 'law_id' => $row['law_id'], 'Count' => $row['Count'], 'Description' => $row['Description']
                    );
                    db::table($this->lgu_db . '.law_cases_entry_law')->insert($data);
                }
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
        $data['main'] = DB::table($this->lgu_db . '.law_cases_entry')->where('ID', $id)->get();
        $data['law'] = DB::table($this->lgu_db . '.law_cases_entry_law')->where('case_id', $id)->get();
        $data['client'] = DB::table($this->lgu_db . '.law_cases_entry_client')->where('case_entry_id', $id)->where('entry_type', 'client')->get();
        $data['opponent'] = DB::table($this->lgu_db . '.law_cases_entry_client')->where('case_entry_id', $id)->where('entry_type', 'opponent')->get();
        return response()->json(new JsonResponse($data));
    }
    public function getCaseType()
    {
        $data = DB::table($this->lgu_db . '.law_case_type')->where('status', 'ACTIVE')->get();
        return response()->json(new JsonResponse($data));
    }
    public function editHearing($id)
    {
        $data = DB::table($this->lgu_db . '.law_hearing_schedule')->where('ID', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function editWitness($id)
    {
        $data = DB::table($this->lgu_db . '.law_witness')->where('id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {
        DB::table($this->lgu_db . '.law_cases_entry')->where('ID', $id)->update(['status' => 'CANCELLED']);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function storeHearing(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['ID'];
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_hearing_schedule')->insert($main);
            } else {
                db::table($this->lgu_db . '.law_hearing_schedule')->where('ID', $idx)->update($main);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function showHearing($id)
    {
        $main = DB::table($this->lgu_db . '.law_hearing_schedule')
            ->join($this->lgu_db . '.law_lawyer', 'law_lawyer.patient_id', 'law_hearing_schedule.lawyer_id')
            ->select('law_hearing_schedule.*', db::raw("trim(concat (`prefix`,' ',`fname`,' ',`lname`,' ',`mname`,' ',`suffix`)) as name"))
            ->where('case_id', $id)
            ->get();
        return response()->json(new JsonResponse($main));
    }
    public function storeWitness(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_witness')->insert($main);
                $idx = $this->G->pk();
            } else {
                db::table($this->lgu_db . '.law_witness')->where('id', $idx)->update($main);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'id' => $idx, 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function showWitness($id)
    {
        $main = DB::table($this->lgu_db . '.law_witness')
            ->select('law_witness.*', db::raw("trim(concat (`fname`,' ',ifnull(`mname`,''),' ',`lname`)) as name"))
            ->where('case_id', $id)
            ->get();
        return response()->json(new JsonResponse($main));
    }
    public function witnessUpload(Request $request)
    {
        log::debug($request);
        $id = $request->id;
        $trans_type = $request->trans_type;
        if ($request->file('file')) {
            foreach ($request->file('file') as $key => $file) {
                log::debug('asd');
                $originalImage = $file;
                $thumbnailPath = public_path() . '/legal_files/witness/' . $id . "/";
                $this->G->createFolder($thumbnailPath);
                $time = Str::random(5);
                $originalImage->move($thumbnailPath, $time . '.' . $originalImage->getClientOriginalExtension());
                $data = array(
                    'witness_id' => $id,
                    'file_name' => $originalImage->getClientOriginalName(),
                    'path_name' => $time . '.' . $originalImage->getClientOriginalExtension(),
                    'upload_type' => $trans_type
                );
                db::table($this->lgu_db . '.law_witness_uploaded')->insert($data);
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function witnessUpload1(Request $request)
    {
        log::debug($request);
        $files = $request->file('files');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                log::debug("asd");
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('docs')->put(public_path() . '/legal_files/witness/' . $request->id,  File::get($file))) {
                    $data = array(
                        'witness_id' => $request->id,
                        'file_name' => $filename,
                        'upload_type' => $request->trans_type,
                        'path_name' => $path,

                    );
                    db::table('docs_upload')->insert($data);
                }
            }
            return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
        }
    }
    public function  witnessUploaded(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $data = db::table($this->lgu_db . '.law_witness_uploaded')
            ->select('id', 'file_name as description', db::raw('concat("' . $this->path . '/legal_files/witness/' . $id . '/",path_name) as image'))
            ->where('witness_id', $id)
            ->where('upload_type', $type)
            ->get();
        return response()->json(new JsonResponse($data));
    }
    public function witnessUploadRemove($id)
    {
        $data = db::table($this->lgu_db . '.law_witness_uploaded')->where('id', $id)->first();
        if (file_exists(public_path() . '/legal_files/witness/' . $data->id . '/' . $data->path_name)) {
            unlink(public_path() . '/legal_files/witness/' . $data->id . '/' . $data->path_name);
        }
        $data = db::table($this->lgu_db . '.law_witness_uploaded')
            ->where('id', $id)->delete();
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function storeMeeting(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            log::debug($main);
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_case_meeting')->insert($main);
            } else {
                db::table($this->lgu_db . '.law_case_meeting')->where('id', $idx)->update($main);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function showMeeting($id)
    {

        $main = DB::table($this->lgu_db . '.law_case_meeting')
            ->join($this->lgu_db . '.law_lawyer', 'law_lawyer.patient_id', 'law_case_meeting.lawyer_id')
            ->select('law_case_meeting.*', db::raw("trim(concat (`prefix`,' ',`fname`,' ',`lname`,' ',`mname`,' ',`suffix`)) as name"))
            ->where('case_id', $id)
            ->get();
        return response()->json(new JsonResponse($main));
    }
    public function editMeeting($id)
    {
        $data = DB::table($this->lgu_db . '.law_case_meeting')->where('id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function documentType()
    {
        $data = DB::table($this->lgu_db . '.law_document_type')->get();
        return response()->json(new JsonResponse($data));
    }
    public function storeDocument(Request $request)
    {

        $id = $request->id;
        $case_id = $request->case_id;
        $lawyer_id = $request->lawyer_id;
        $doc_type = $request->doc_type;
        $remarks = $request->remarks;
        $received_from = $request->received_from;
        $date_receipt = $request->date_receipt;
        $data = array(
            'received_from' => $received_from,
            'remarks' => $remarks,
            'lawyer_id' => $lawyer_id,
            'case_id' => $case_id,
            'doc_type' => $doc_type,
            'date_receipt' => $date_receipt,
            'date_issuance'=>$request->date_issuance
        );

        if ($id == 0) {
            db::table($this->lgu_db . '.law_case_document')->insert($data);
            $id = $this->G->pk();
        } else {
            db::table($this->lgu_db . '.law_case_document')->where('id', $id)->update($data);
        }

        $files = $request->file('files');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();

                if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'doc_id' => $id,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                    );
                    db::table($this->lgu_db . '.law_case_document_docs')->insert($data);
                }
            }
        }
    }
    public function editdocument($id)
    {
        $data = DB::table($this->lgu_db . '.law_case_document')->where('id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function storeDocumentUpdate(Request $request)
    {
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();

                if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'doc_id' => $request->doc_id,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                    );
                    db::table($this->lgu_db . '.law_case_document_docs')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function uploadRemoveDoc($id)
    {
        DB::table($this->lgu_db . '.law_case_document_docs')->where('id', $id)->update(['stat' => '1']);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function showDocument($id)
    {
        $main = DB::table($this->lgu_db . '.law_case_document')
            ->join($this->lgu_db . '.law_lawyer', 'law_lawyer.patient_id', 'law_case_document.lawyer_id')
            ->join($this->lgu_db . '.law_document_type', 'law_document_type.ID', '=', 'law_case_document.doc_type')
            ->select('law_case_document.*', 'law_document_type.Type', db::raw("trim(concat (`prefix`,' ',`fname`,' ',`lname`,' ',`mname`,' ',`suffix`)) as name"))
            ->where('case_id', $id)
            ->orderBy('date_receipt','desc')
            ->get();
        return response()->json(new JsonResponse($main));
    }
    public function getUpdate($id)
    {
        $data = db::table($this->lgu_db . '.law_case_update')
            ->where('case_id', $id)
            ->where('stat', 0)
            ->orderBy('trans_date', "desc")
            ->get();
        return response()->json(new JsonResponse($data));
    }
    public function postUpdate(Request $request)
    {
        $main = $request->main;
        $id = $main['id'];
        if ($id > 0) {
            db::table($this->lgu_db . '.law_case_update')->where('id', $id)->update($main);
        } else {
            db::table($this->lgu_db . '.law_case_update')->insert($main);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function getDocs($id)
    {
        $main = DB::table($this->lgu_db . '.law_case_document_docs')->where('doc_id', $id)->where('stat', '0')->get();
        return response()->json(new JsonResponse($main));
    }
    public function documentView($id)
    {
        $main = DB::table($this->lgu_db . '.law_case_document_docs')->where('id', $id)->get();
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
    public function deleteUpdate($id)
    {
        db::table($this->lgu_db . '.law_case_update')->where('id', $id)->update(['stat' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
}
