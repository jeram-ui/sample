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

class nonElegibilityController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
    private $Proc;
    private $budget;
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
        $this->budget = $this->G->getBudgetDb();
    }
    public function getPR()
    {
        $list = db::select('call ' . $this->Bac . '.rans_bacc_getPR');
        return response()->json(new JsonResponse($list));
    }
    public function getDocu()
    {
        $list = db::table($this->Bac . '.eligibility_documents_setup')
            // ->join($this->Bac . '.eligibility_documents_setup', 'eligibility_main.id', '=', 'eligibility_remarks.main_id')
            ->where('eligibility_documents_setup.stat', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getDocsList($id)
    {
        $list = db::table($this->Bac . '.eligibility_documents_setup')
            ->leftjoin($this->Bac . '.eligibility_documents', 'eligibility_documents_setup.id', '=', 'eligibility_documents.documents_issued')
            ->select("*", 'eligibility_documents_setup.id', 'eligibility_documents_setup.document_name', 'eligibility_documents.id')
            ->where("mainID", $id)
            ->where('status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function save(Request $request)
    {
        $data = $request->data;
        $form = $request->form;
        $idx = $request->id;
        // $id = $form['id'];
        if ($idx > 0) {
            db::table($this->Bac . '.eligibility_documents')
                ->where('id', $idx)
                ->update($form);
        } else {
            db::table($this->Bac . '.eligibility_documents')->insert($form);
            // $data = array(

            //     'mainID' => $request->$idx,
            //     'documents_issued' => $form['documents_issued'],
            //     'date_issued' => $form['date_issued']

            // 'mainID' => $id,
            // 'documents_issued' => $form['documents_issued'],
            // 'date_issued' => $form['date_issued']
            // );
            // db::table($this->Bac . '.eligibility_documents')->insert($data);
        }
        return response()->json(new JsonResponse($data));
    }
    public function show(Request $request)
    {
        // log::debug('asd');
        $list = db::table($this->Bac . '.eligibility_non_main')
        // ->join($this->Bac . '.eligibility_remarks', 'eligibility_non_main.id', '=', 'eligibility_remarks.main_id')
        // ->join($this->budget . '.cto_budget_mode_pro', 'cto_budget_mode_pro.id', '=', 'eligibility_non_main.mop')
        ->select('eligibility_non_main.*',
        //  db::raw("GROUP_CONCAT(eligibility_remarks.`remarks` SEPARATOR '<br>') as remarks")
        )
        ->where('eligibility_non_main.stat', 0)
        // ->where('eligibility_non_main.validity','!=', 'NULL')
        // ->whereRaw(" (`business_name` like ? or `business_owner` like ? or `eligibility_description` like ? or `mode_pro_desc` like ? or `remarks` like ? or `validity` like ? ) ",['%' . $request->dataz . '%','%' . $request->dataz . '%','%' . $request->dataz . '%', '%' . $request->dataz . '%','%' . $request->dataz . '%','%' . $request->dataz . '%'])
        // ->whereYear('validity', Carbon::now()->year)
        // ->whereBetween(db::raw('ifnull(date(validity),"")'),[$request->yearFrom, $request->yearTo])
        // ->orderBy('eligibility_non_main.validity', 'desc')
        ->groupBy('eligibility_non_main.id')->get();
        return response()->json(new JsonResponse($list));
    }
    public function showfilter_year(Request $request)
    {
        $list = db::table($this->Bac . '.eligibility_non_main')
            // ->join($this->Bac . '.eligibility_remarks', 'eligibility_non_main.id', '=', 'eligibility_remarks.main_id')
            ->join($this->budget . '.cto_budget_mode_pro', 'cto_budget_mode_pro.id', '=', 'eligibility_non_main.mop')
            ->select('eligibility_non_main.*', 'cto_budget_mode_pro.mode_pro_desc',
            // db::raw("GROUP_CONCAT(eligibility_remarks.`remarks` SEPARATOR '<br>') as remarks")
            )
            ->where('eligibility_non_main.stat', 0)
            // ->whereRaw(" (`business_name` like ? or `business_owner` like ? or `eligibility_description` like ? or `mode_pro_desc` like ? or `remarks` like ? or `validity` like ? ) ",['%' . $request->dataz . '%','%' . $request->dataz . '%','%' . $request->dataz . '%', '%' . $request->dataz . '%','%' . $request->dataz . '%','%' . $request->dataz . '%'])
            // ->whereBetween(db::raw('ifnull(date(validity),"")'),[$request->yearFrom, $request->yearTo])
            // ->orderBy('eligibility_non_main.validity', 'desc')
            ->groupBy('eligibility_non_main.id')->get();
        return response()->json(new JsonResponse($list));
    }
    public function edit($id)
    {
        $main['main'] = db::table($this->Bac . '.eligibility_non_main')->where('id', $id)->get();
        $main['remarks'] = db::table($this->Bac . '.eligibility_remarks')->where('main_id', $id)->get();
        return response()->json(new JsonResponse($main));
    }
    public function cancel($id)
    {
        db::table($this->Bac . '.eligibility_non_main')->where('id', $id)->update(['stat' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function removeDocu($id)
    {
        db::table($this->Bac . '.eligibility_documents')
            ->where('id', $id)
            ->update(['status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function getEligibility()
    {
        $list = db::select("SELECT DISTINCT(`eligibility_description`) AS 'name' FROM bac_lgu.eligibility_main");
        return response()->json(new JsonResponse($list));
    }
    public function updateForEligibility(Request $request)
    {

        $eligible = $request->IsEligible;
        if ($eligible === 'True') {
            $eligible = "False";
        } else {
            $eligible = "True";
        }
        db::table($this->Proc . '.tbl_canvass_supplier')
            ->where('can_id', $request->CSVID)
            ->where('supplierid', $request->SUPPID)->update(['IsEligible' => $eligible]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function getForEligibility(Request $request)
    {

        $from =  $request->from;
        $to =  $request->to;
        $list = db::select("call " . $this->Proc . ".rans_display_canvass_for_eligible(?,?)", [$from, $to]);
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {

        try {

            $idx = $request->id;
            $data = array(
                'business_id' => $request->business_id,
                'business_name' => $request->business_name,
                'business_owner' => $request->business_owner,
                'business_address' => $request->business_address,
                // 'mop' => $request->mop,

                'Vat' => $request->Vat,
                'uid' => Auth::user()->id,
            );
            $remarks =  $request->remarks;
            // log::debug($remarks);
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->Bac . '.eligibility_non_main')->insert($data);
                $idx = $this->G->pk();
            } else {
                $data['upid'] = Auth::user()->id;
                db::table($this->Bac . '.eligibility_non_main')->where('id', $idx)->update($data);
            }
            // db::table($this->Bac . '.eligibility_remarks')->where('main_id', $idx)->delete();
            // foreach ($remarks as  $value) {
            //     $dtls = array(
            //         'main_id' => $idx,
            //         'remarks' => $value
            //     );
            //     db::table($this->Bac . '.eligibility_remarks')->insert($dtls);
            // }
            $files = $request->file('files');
            // log::debug($request);
            if (!empty($files)) {
                $path = hash('sha256', time());
                for ($i = 0; $i < count($files); $i++) {
                    $file = $files[$i];
                    $filename = $file->getClientOriginalName();
                    if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                        $data = array(
                            'trans_id' => $idx,
                            'file_name' => $filename,
                            'trans_type' => 'Representation Only',
                            'file_path' => $path,
                            'file_size' => $file->getSize(),
                            'uid' => Auth::user()->id,
                        );
                        db::table('docs_upload')->insert($data);
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

    public function  uploaded($id)
    {
        $data = db::table('docs_upload')
            ->where('trans_id', $id)
            ->where('trans_type', 'lawEnforcement')
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

    public function print(Request $request)
    {
        $form = $request['itm'];
        // log::debug($form['id']);
        $id = $form['id'];

        try {
            $main = DB::table($this->Bac . '.eligibility_main')
                // ->join($this->Bac . '.eligibility_remarks', 'eligibility_main.id', '=', 'eligibility_remarks.main_id')
                // ->join($this->budget . '.cto_budget_mode_pro', 'cto_budget_mode_pro.id', '=', 'eligibility_main.mop')
                // ->select('eligibility_main.*', 'cto_budget_mode_pro.mode_pro_desc', db::raw("GROUP_CONCAT(eligibility_remarks.`remarks` SEPARATOR '<br>') as remarks"))
                // ->where('eligibility_main.stat', 0)
                ->where('id', $request->itm['id'])
                // ->select('*', db::raw("TIME_FORMAT(time_incident, '%h:%i %p') as 'time_incident'"))
                ->get();
            $row = [];
            foreach ($main as $key => $value) {
                $row = $value;
            }



            $description = DB::table($this->Bac . '.eligibility_main')
                ->join($this->Bac . '.eligibility_remarks', 'eligibility_main.id', '=', 'eligibility_remarks.main_id')
                ->join($this->budget . '.cto_budget_mode_pro', 'cto_budget_mode_pro.id', '=', 'eligibility_main.mop')
                ->select('eligibility_main.*', 'cto_budget_mode_pro.mode_pro_desc', db::raw("GROUP_CONCAT(eligibility_remarks.`remarks` SEPARATOR '<br>') as remarks"))
                ->where('eligibility_main.id', $id)

                // ->select('*', db::raw("TIME_FORMAT(time_incident, '%h:%i %p') as 'time_incident'"))
                ->get();
            $desc = [];
            foreach ($description as $key => $value) {
                $desc = $value;
            }


            $documents = DB::table($this->Bac . '.eligibility_documents_setup')
                ->leftjoin($this->Bac . '.eligibility_documents', 'eligibility_documents_setup.id', '=', 'eligibility_documents.documents_issued')
                ->select("*", 'eligibility_documents_setup.id', 'eligibility_documents_setup.document_name', 'eligibility_documents.id')
                ->where("mainID", $id)
                // ->where('status', 0)
                ->get();

            $docs = "";
            foreach ($documents as $key => $value) {
                $docs .= '<tr>
                <td width="6%"> •
                </td>
                  <td width="90%" style="font-size:11pt"> ' . $value->document_name . ' ; </td>
            </tr>';
            }


            $Template = '<table width="100%" cellpadding="2" >

      <tr>
          <th width="100%" align="center">
              <img src="' . public_path() . '/img/NAGALOGO.jpg"  height="45" width="45">
          </th>
      </tr>
      <tr>
        <th width="100%" align="center" style="font-size:10pt"> Republic of the Philippines </th>
      </tr>
      <tr>
        <th width="100%" align="center" style="font-size:10pt"> Province of Cebu </th>
      </tr>
      <tr>
        <th width="100%" align="center" style="font-size:10pt"> City Government of Naga </th>
      </tr>
      <tr>
         <th width="100%" align="center" style="font-size:10pt"> Office of the City Mayor </th>
      </tr>
      <tr><th width="100%" align="center" style="font-size:11pt"> <b> BIDS AND AWARDS COMMITTEE </b> </th></tr>
      <br/>
      <tr> <th width="100%" align="center" style="font-size:11pt"> <b> CERTIFICATE OF ELIGIBILITY </b> </th> </tr>
      <tr><th width="100%" align="center" style="font-size:9pt"> (Alternative Mode of Procurement) </th></tr>
  </table>
  ';

            $Template .= ' <table width="100%" cellpadding="5">
            <br/>
            <br/>
            <br/>
            <br/>
            <br/>

            <tr>
                <td width="100%"><p style="text-align:justify;font-size:11pt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                 This is to certify that <b><u>  ' . $row->business_name . ' as represented by  ' . $row->business_owner . ', proprietor</u></b>
                  with business address at <u>' . $row->business_address . '</u>
                   submitted to this office, the following: </p> </td>
            </tr>
            <br/>
            <br/>

             ' . $docs . '

            <br/>

         <br/>
         <br/>


            <tr>
                <td width="100%"><p style="text-align:justify;font-size:11pt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                This is to further certify that having submitted the above eligibility document; the
                <b><u> ' . $row->business_name . ' </u></b>is found to be eligible to supply/provide <b> ' . $row->eligibility_description . ' </b>
                for the City of Government of Naga, Cebu through
                <u><b> Alternative Modes of Procurement</b> - <i>' . $desc->mode_pro_desc . '</i></u>.
                </p></td>
            </tr>


            <br/>
            <br/>
            <br/>
            <tr>
                <td width="10%"> </td>
                <td width="90%" style="font-size:11pt;text-align:justify"> Issued this  ' . date_format(date_create($value->date_issued), "jS \d\a\y \of F Y")  . ' at the City of Naga, Cebu, Philippines </td>
            </tr>
            <br/>
            <br/>
            <br/>
            <tr>
                <td width="3%"> </td>
                <td width="97%" style="font-size:11pt;text-align:justify"> ANNAVIE E. BACOMO-LAPITAN </td>
            </tr>
            <tr>
                <td width="3%"> </td>
                <td width="97%" style="font-size:11pt;text-align:justify"> BAC Secretariat, Chairman </td>
            </tr>
            <br/>
            <br/>
            <br/>


            <tr>
                <td width="19%"> </td>
                <td width="60%" style="font-size:11pt;text-align:justify"> Approved by: </td>
            </tr>
            <br/>
            <br/>

            <tr>
                <td width="19%"> </td>
                <td width="81%" style="font-size:11pt;text-align:justify"> ENGR.ARTHUR S. VILLAMOR </td>
            </tr>
            <tr>
                <td width="19%"> </td>
                <td width="81%" style="font-size:11pt;text-align:justify"> BAC Chairman </td>
            </tr>






        </table>

';


            // PDF::Image(public_path() . $value->{'certSig'}, 55, 205, 27, 27, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image(public_path() . $value->{'RecSig'}, 150, 203, 25, 25, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image(public_path() . $value->{'Approved BySig'}, 80, 245, 40, 30, 'PNG', 'http://www.tcpdf.org', '', false, 300);

            PDF::SetTitle('Print');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('P');
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/print.pdf', 'F');
            $full_path = public_path() . '/print.pdf';
            if (\File::exists(public_path() . '/print.pdf')) {
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
