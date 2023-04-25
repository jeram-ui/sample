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
use Illuminate\Support\Facades\Log;
use ZipArchive;

class burialvigilController extends Controller
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
    public function getCemetery()
    {
        $data = db::table($this->lgu_db . '.cho_occurence_setup')->where('status', 'ACTIVE')->get();
        return response()->json(new JsonResponse($data));
    }
    public function ref(Request $request)
    {
        $pre = 'VC';
        $table = $this->mayors_db . ".burial_certification_vigil";
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
            Log::debug($id);
            if ($id == 0) {
                Log::debug('insert');
                $form['uid'] = Auth::user()->id;
                db::table($this->mayors_db . '.burial_certification_vigil')->insert($form);
            } else {
                Log::debug('update');
                $form['upidint'] = Auth::user()->id;
                db::table($this->mayors_db . '.burial_certification_vigil')
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
            $data =  db::table($this->mayors_db . '.burial_certification_vigil')
            ->leftjoin('qpsii_lgusystem.cho_occurence_setup','cho_occurence_setup.occurence_id','burial_certification_vigil.transported')
            ->select('*', db::raw("TRIM(CONCAT(`fname`,' ',ifnull(`mname`,''),' ',`lname`,' ',ifnull(`suffix`,'')))AS 'name'"),db::raw("DATEDIFF(`schedule`,`started`)AS daysdiff"),db::raw("TIME_FORMAT (`starttime`,'%h:%i %p') AS timeofmass"))
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
            $data =  db::table($this->mayors_db . '.burial_certification_vigil')
                ->select('barangay_name', db::raw('count(*) as counts'))
                ->where('stat', 0)
                ->groupBy('barangay_name')->get();

            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function edit($id)
    {
        try {

            $data = db::table($this->mayors_db . '.burial_certification_vigil')
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
    public function printform($id) {
       
        $datax = db::select('call '.$this->mayors_db.'.get_vigil_print(?)',[$id]);

        $row = "";
        foreach ($datax as $val ) {
            $row=$val;
        }
        Log::debug($row->name);
        $template_file_name = public_path().'\MAYORS\specialpermit.docx';
        $rand_no = rand(111111, 999999);
        $fileName = "results_" . $rand_no . ".docx";
        $folder   = "results_mayors";
        $full_path = $folder . '/' . $fileName;
        if (!file_exists($folder))
        {
            mkdir($folder);
        } 
        copy($template_file_name, $full_path);
        $zip_val = new ZipArchive;
        if($zip_val->open($full_path) == true)
        {
           
           log::debug($row->dateto);
            $key_file_name = 'word/document.xml';
            $message = $zip_val->getFromName($key_file_name); 
            // $date=date_create($dataPhysical->{'Date of Exam'});

            
              $message = str_replace("@vigildays",$row->daysdiff,$message);
              $message = str_replace("@address",$row->adrs,$message);
              $message = str_replace("@requestor",$row->name,$message);
              $message = str_replace("@cadavername",$row->cadavername,$message);
              $message = str_replace("@month",$row->months,$message);
              $message = str_replace("@day",$row->DAY,$message);
              $message = str_replace("@datestart",$row->datefrom,$message);
              $message = str_replace("@dateend",$row->dateto,$message);
              $message = str_replace("@poi",$row->occurence_name,$message);
              $message = str_replace("@internment",$row->timeofmass,$message);
              $message = str_replace("@funeralname",$row->funeral_services,$message);
              $message = str_replace("@pom",$row->placeofmass,$message);
              $message = str_replace("@brgy",$row->adrs,$message);
              $message = str_replace("@cemetery",$row->cemaddress,$message);
              $message = str_replace("@countdays",$row->dayword,$message);
              $message = str_replace("@addofmass",$row->massaddress,$message);

            //  $message = str_replace("@school",$datax->school_name.' '.$datax->school_add,$message);
            //  $message = str_replace("@lastname",$datax->genderx,$message);
          
            $zip_val->addFromString($key_file_name, str_replace("&","&amp;",$message));
            log::debug($message);
            $zip_val->close();
    
            if (\File::exists(public_path()."/".$full_path)) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }
    }
}