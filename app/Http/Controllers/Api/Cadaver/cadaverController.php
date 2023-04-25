<?php

namespace App\Http\Controllers\Api\Cadaver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;
use ZipArchive;
class cadaverController extends Controller
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
        $this->signatory = $this->G->signatoryReport();
    }


    public function occurenceName()
    {
        $list = DB::table($this->lgu_db.'.cho_occurence_setup')
        ->where('status','ACTIVE')->get()
        ;
        return response()->json(new JsonResponse($list));
    }   
    public function occurenceStore(Request $request)
    {
      
        $data =  $request->form;
        $id =  $data['occurence_id'];
        if ($id == 0) {
            $list = DB::table($this->lgu_db.'.cho_occurence_setup')
            ->insert($data );
        }else
        {
             DB::table($this->lgu_db.'.cho_occurence_setup')
                ->where('occurence_id', $id)
                ->update($data);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    }      
    public function occurenceCancel($id){
     db::table($this->lgu_db.'.cho_occurence_setup')
     ->where('occurence_id',$id)
     ->update(['status'=>'CANCELLED']);
     return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    }
    public function displayData()
    {
        $list = DB::select('call '.$this->lgu_db.'.cho1_cadaver_display_gigil_all');
        return response()->json(new JsonResponse($list));
    }
    public function filterData(Request $request)
    {
        $datefrom = $request->from;
        $dateto = $request->to;
        $type = 'Cadaver Transfer';
        $list = DB::select('call '.$this->lgu_db.'.cho1_cadaver_display_gigil(?,?,?)',array($datefrom,$dateto,$type));
        return response()->json(new JsonResponse($list));
    }
    public function printList(Request $request){
        $data = $request->main;
        $filter = $request->filter;
        $from = date("F j, Y", strtotime($filter['from']));
        $to =  date("F j, Y", strtotime($filter['to']));

        if ($filter['filter'] =="Year") {
            $filters = "Year ". date("Y", strtotime($filter['from'])) ;
        }elseif ($filter['filter'] =="Month") {
            $filters = "Month of ". date("F Y", strtotime($filter['from'])) ;
        }else {
            $filters = "As of ".  $from .' - '. $to;
        }
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 style="width:14%;text-align:center;font-size:13px">CADAVER TRANSFER LIST</h2>
        <h3 style="width:14%;text-align:center;font-size:11px">'.$filters.'</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:13%;font-size:9px">Applicant Name</th>
        <th style = "width:14%;font-size:9px">Address</th>
        <th style = "width:13%;font-size:9px">Cadaver Name</th>
        <th style = "width:13%;font-size:9px">Transfer From</th>
        <th style = "width:13%;font-size:9px">Transfer To</th>
        <th style = "width:10%;font-size:9px">Application Date</th>
        <th style = "width:10%;font-size:9px">Transfer Date</th>
        <th style = "width:10%;font-size:9px">Date Died</th>
        <th style = "width:5%;font-size:9px">Amount</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            
            $main =($row);   
            $html_content .='
            <tr>
            <td style="width:13%;text-align:left;font-size:8px">'.$main['Applicant'].'</td>
            <td style="width:14%;text-align:left;font-size:8px">'.$main['Address'].'</td>
            <td style="width:13%;text-align:left;font-size:8px">'.$main['Cadaver Name'].'</td>
            <td style="width:13%;text-align:left;font-size:7.6px">'.$main['Transfer From'].'</td>
            <td style="width:13%;text-align:left;font-size:7.6px">'.$main['Transfer To'].'</td>
            <td style="width:10%;text-align:left;font-size:8px">'.$main['Application Date'].'</td>
            <td style="width:10%;text-align:left;font-size:8px">'.$main['Transfer Date'].'</td>
            <td style="width:10%;text-align:left;font-size:8px">'.$main['Date Died'].'</td>
            <td style="width:5%;text-align:left;font-size:9px">'.$main['Permit Fee'].'</td>
            </tr>';
        }
        $html_content .='</tbody>
        </table>';
    
        PDF::SetTitle('Sample');
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
        }catch (\Exception $e) {
            return response()->json(new JsonResponse(['status'=>'error']));
        }
    }
    public function printDtl(Request $request){
        $main = $request->main;
        $signatory = $this->signatory;

    foreach ($signatory as $row) {
      $healthhead =   $row->{'health_head_name'};
      $position =   $row->{'health_head_pos'};
    }
    $template_file_name = public_path().'\HEALTH\Transfer Permit.docx';
    $rand_no = rand(111111, 999999);
    $fileName = "results_" . $rand_no . ".docx";
    $folder   = "results_cadaver";
    $full_path = $folder . '/' . $fileName;
    if (!file_exists($folder))
    {
      mkdir($folder);
    } 
    copy($template_file_name, $full_path);
    $zip_val = new ZipArchive;
    if($zip_val->open($full_path) == true)
    {
      $lgu = $this->G->get_lgu_data();
      $lgu = json_decode($lgu,true);
      foreach ($lgu as $key => $value) {
        $lgu = $value;
      }

        $key_file_name = 'word/document.xml';
        $message = $zip_val->getFromName($key_file_name); 
        $message = str_replace("@appdate",$request['Application Date'],$message);
        $message = str_replace("@refno",$request['Certificate No.'],$message);
        
        $message = str_replace("@applicant",$request['Applicant'],$message);
        $message = str_replace("@address",$request['Address'],$message);
        $message = str_replace("@cadavername",$request['Cadaver Name'],$message);
        $message = str_replace("@from",$request['Transfer From'],$message);
        $message = str_replace("@to",$request['Transfer To'],$message);

        $message = str_replace("@issueddate",$request['Issued Date'],$message);
        $message = str_replace("@orno",$request['OR No'],$message);

        $message = str_replace("@stamp",$this->G->serverdatetime(),$message);
        $message = str_replace("@c_right",$this->G->system_generated(),$message);
        $message = str_replace("@processno",Auth::user()->Employee_id,$message);
       
        $zip_val->addFromString($key_file_name, str_replace("&","&amp;",$message));
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
    public function ref(Request $request)
    {
        $pre = 'CT';
        $table = $this->lgu_db . ".cho1_cadaver_transfer";
        $date = $request->date;
        $refDate = 'date_application';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function refDirect($date)
    {
        // log::debug($date);
        $pre = 'CT';
        $table = $this->lgu_db . ".cho1_cadaver_transfer";
        $refDate = 'date_application';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        // log::debug($data);
        foreach ($data as $key => $value) {
            log::debug($value->NOS);
            return $value->NOS;
        }
        // return response()->json(new JsonResponse(['data' => $data]));
    }
    public function store(Request $request) 
    {
        try {
            //DB::beginTransaction();
            //dd($request->details);
            // dd($request);
            
            $main = $request->main;
            $ctobill = $request->cto;
            $idx = $request->main['cadaver_transfer_id'];
            if ($idx>0) {
                $this->update($idx, $main, $ctobill);
            } else {
        
                $main['certificate_number']= $this->refDirect($main['date_application']);
                log::debug($this->refDirect($main['date_application']));
                $this->save($main,$ctobill);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  

    public function save($main,$ctobill) {
 
        DB::table($this->lgu_db.'.cho1_cadaver_transfer')->insert($main);
        $id = DB::getPDo()->lastInsertId();        
        foreach ($ctobill as $row) {                    
            if ($row['Include'] === "True") {  
                $cto = array(   
                   'payer_type'=>'Person',
                   'payer_id'=>$main['applicant_id'],
                   'business_application_id'=>$main['applicant_id'],
                   'account_code'=>$row['Account Code'],
                   'bill_description'=>$row['Account Description'],
                   'net_amount'=>$row['Initial Amount'],
                   'bill_amount'=>$row['Fee Amount'],
                   'bill_month'=>$main['date_application'],
                   'bill_number'=>$main['certificate_number'],
                   'transaction_type'=>'Cadaver Transfer',
                   'ref_id'=>$id,
                   'bill_id'=>$id,
                   'include_from'=>'Others',
                );                          
                DB::table($this->lgu_db.'.cto_general_billing')->insert($cto);
            }
        }
      
    }
    
    public function editData($id) 
    {   
        $data['main'] = DB::table($this->lgu_db.'.cho1_cadaver_transfer')->where('cadaver_transfer_id',$id)->get();
        return response()->json(new JsonResponse($data));

    }
    
    public function delete(Request $request)
    {   
      $id=$request->id;

      $data['status'] = 'CANCELLED';
      DB::table($this->lgu_db.'.cho1_cadaver_transfer')->where('cadaver_transfer_id', $id) ->update($data);
      
      $reason['Form_name'] ='Cadaver Transfer';
      $reason['Trans_ID'] =$id;
      $reason['Type_'] ='Cancel Record';
      $reason['Trans_by'] =Auth::user()->id;

      $this->G->insertReason($reason);

      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

    public function update($idx, $main,$ctobill) 
    {
        //$id = $request->main['id'];
        DB::table($this->lgu_db.'.cho1_cadaver_transfer')->where('cadaver_transfer_id',$idx)->update($main);
        
    }

    public function getInspection($main) {
    {
        try {                
            $data['main'] = DB::table($this->lgu_db.'.cho1_cadaver_transfer')->where('Inspector')->get();
            return response()->json(new JsonResponse(['Message' => 'Successfully Inspected.', 'status' => 'success']));

            } catch (\Exception $err) {
            
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
            }
        }
    } 
    
    public function updateInspection(Request $request) 
    {
        // dd($request);
        $id=$request->id;
        $main=array('inspection_date'=>$request->date
        ,'Inspector' => $request->Inspector);
        DB::table($this->lgu_db.'.cho1_cadaver_transfer')->where('cadaver_transfer_id',$id)->update($main);
        return response()->json(new JsonResponse(['Message' => 'Successfully Inspected.', 'status' => 'success']));
    }
}                