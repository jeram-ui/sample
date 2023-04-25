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

class burialController extends Controller
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

    public function ref(Request $request)
    {
        $pre = 'BRL';
        $table = $this->mayors_db . ".burial_certification";
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
            $id = $form['id'];
            if ($id == 0) {
                $form['uid'] = Auth::user()->id;
                db::table($this->mayors_db . '.burial_certification')
                    ->insert($form);
            } else {
                $form['upid'] = Auth::user()->id;
                db::table($this->mayors_db . '.burial_certification')
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
            $data =  db::table($this->mayors_db . '.burial_certification')
                ->select('*', db::raw("TRIM(CONCAT(`fname`,' ',`mname`,' ',`lname`,' ',`suffix`))AS 'name'"))
                ->where('stat', 0)
                ->whereBetween('trans_date', [$from, $to])->get();
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function showsummary(Request $request)
    {
        try {
            $data =  db::table($this->mayors_db . '.burial_certification')
                ->select('barangay_name', db::raw('count(*) as counts'))
                ->where('stat', 0)
                ->groupBy('barangay_name');

            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function edit($id)
    {
        try {

            $data = db::table($this->mayors_db . '.burial_certification')
                ->where('id', $id)->get();
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function cancel($id)
    {
        try {
            $data = db::table($this->mayors_db . '.burial_certification_vigil')
                ->where('id', $id)
                ->update(['stat' => 1]);
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function upload(Request $request)
    {
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('Memorandum')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'trans_id' => $request->id,
                        'file_name' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'uid' => Auth::user()->id,
                        'entry_type' => 'Memorandum'
                    );
                    db::table($this->mayors_db . '.documents_uploded')->insert($data);
                }
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function  uploaded($id)
    {
        $data = db::table($this->mayors_db . '.documents_uploded')
            ->where('trans_id', $id)
            ->where('entry_type', 'Memorandum')
            ->where('stat', "ACTIVE")
            ->get();
        return response()->json(new JsonResponse($data));
    }
    public function documentView($id)
    {
        $main = DB::table($this->mayors_db . '.documents_uploded')->where('id', $id)->get();
        foreach ($main as $key => $value) {
            $file = $value->file_name;
            $path = '../storage/files/Memorandum/' . $value->file_path . '/' . $file;
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
        $data = db::table($this->mayors_db . '.documents_uploded')->where('id', $id)
            ->update(['stat' => "CANCELLED"]);
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function printform(Request $request)
    {
        $template_file_name = public_path() . '\MAYORS\Health Card2.docx';
        $rand_no = rand(111111, 999999);
        $fileName = "results_" . $rand_no . ".docx";
        $folder   = "results_health";
        $full_path = $folder . '/' . $fileName;
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        copy($template_file_name, $full_path);
        $zip_val = new ZipArchive;
        if ($zip_val->open($full_path) == true) {
            $data = DB::table($this->mayors_db . '.burial_certification')->where('id', $request->id)->first();

            $key_file_name = 'word/document.xml';
            $message = $zip_val->getFromName($key_file_name);
            $date = date_create($dataPhysical->{'Date of Exam'});

            $message = str_replace("@name", $info->{'Employee Name'}, $message);
            $message = str_replace("@occupation", $info->{'Occupation'}, $message);


            $zip_val->addFromString($key_file_name, str_replace("&", "&amp;", $message));
            $zip_val->close();

            if (\File::exists(public_path() . "/" . $full_path)) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }
    }
}
