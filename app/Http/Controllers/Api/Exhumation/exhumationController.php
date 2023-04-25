<?php

namespace App\Http\Controllers\Api\Exhumation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;
use ZipArchive;
class exhumationController extends Controller
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

   
    public function occurenceName()
    {
        $list = DB::select('select * from '.$this->lgu_db.'.cho_occurence_setup');
        return response()->json(new JsonResponse($list));
    }       

    public function filterData(Request $request)
    {
        $datefrom = $request->from;
        $dateto = $request->to;
        $type = 'Exhumation Permit';
        $list = DB::select('call '.$this->lgu_db.'.cho1_exhumation_display_gigil(?,?,?)',array($datefrom,$dateto,$type));
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
        <h2 style="width:14%;text-align:center;font-size:13px">EXHUMATION PERMIT LIST</h2>
        <h3 style="width:14%;text-align:center;font-size:11px">'.$filters.'</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:17%;font-size:9px">Applicant Name</th>
        <th style = "width:18%;font-size:9px">Applicant`s Address</th>
        <th style = "width:17%;font-size:9px">Cadaver Name</th>
        <th style = "width:18%;font-size:9px">Cadaver`s Address</th>
        <th style = "width:10%;font-size:9px">Application Date</th>
        <th style = "width:15%;font-size:9px">Buried At</th>
        <th style = "width:5%;font-size:9px">Amount</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            
            $main =($row);   
            $html_content .='
            <tr>
            <td style="width:17%;text-align:left;font-size:8px">'.$main['Applicant'].'</td>
            <td style="width:18%;text-align:left;font-size:8px">'.$main['Applicant`s Address'].'</td>
            <td style="width:17%;text-align:left;font-size:8px">'.$main['Cadaver Name'].'</td>
            <td style="width:18%;text-align:left;font-size:8px">'.$main['Cadaver`s Address'].'</td>
            <td style="width:10%;text-align:left;font-size:8px">'.$main['Application Date'].'</td>
            <td style="width:15%;text-align:left;font-size:8px">'.$main['Burried At'].'</td>
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
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h1 style="width:14%;text-align:center;font-size:13px">CITY HEALTH OFFICE</h1>
        <br>
        <h2 style="width:14%;text-align:center;font-size:14px">EXHUMATION PERMIT</h2>
        <h3 style="width:14%;text-align:center;font-size:13px">Permit No. <b><u>'.$main['Certificate No.'].'</u></b></h3>
<br>
<br>
<br>
<br>
<br>
<table width ="100%">
         <tr style="height:25px" align="left">
            <td style="width:78%">               
            </td>                
            <td style="width:22%;">
            <u>'.$main['Application Date'].'</u>                   
            </td>
         </tr>
         <tr style="height:25px" align="left">
            <td style="width:83%">               
            </td>                
            <td style="width:17%;">
            Date                  
            </td>
         </tr>
         <br>
         <tr style="height:25px">   
            <td style="width:100%"><b>TO WHOM IT MAY CONCERN:</b>
            </td>                  
         </tr>   
         <br>
         <br>      
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Permission is hereby granted to <b><u>'.$main['Applicant'].'</u></b> of <b><u>'.$main['Applicant`s Address'].'</u></b> for the exhumation of the remains of <I><b><u>'.$main['Cadaver Name'].'</u></b></I> of <b><u>'.$main['Cadaver`s Address'].'</u></b> buried at the <b><u>'.$main['Burried At'].'</u></b>, subject to following conditions:</span>
            </td>                   
         </tr>
         <br>      
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    	1. A copy of the death certificate must always accompany this permit.</span>
            </td>                   
         </tr>
         <br>      
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    	2. That immediately upon exhumation, the remains shall be disinfected and after the </span>
            </td>                   
         </tr>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    	   necessary investigation by the authorities concerned shall have been completed,</span>
            </td>                   
         </tr>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    	   the same shall be re-buried at authorized burial place.</span>
            </td>                   
         </tr>
         <br>      
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    	3. The remains must be placed in a coffin or an airtight box and properly sealed.</span>
            </td>                   
         </tr>   
         <br>
         <tr style="height:25px">   
            <td style="width:100%"><span style="line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; The burial of the cadaver/remains must be conducted in the most sanitary manner to safeguard public health.</span>
            </td>                   
         </tr>                   
</table>  
<br>
<br>
<br>
<br>
<br>
<table width ="100%">  
         <tr style="height:25px" align="left"> 
            <td style="width:69%">               
            </td>     
            <td style="width:31%">
            <b><u>'.$main['head_name'].'</u></b>
            </td>                      
         </tr> 
         <tr style="height:25px" align="left">  
         <td style="width:72%">               
         </td>      
         <td style="width:28%">
         City Health Officer
         </td>                      
         </tr>
         <br>
         <br>
         <br>
         <br> 
         <tr style="height:25px" align="left">   
            <td style="width:22%">
            Permit Fee:
            </td> 
            <td style="width:20%">
            <u>P'.$main['Permit Fee'].'</u>
            </td>                  
         </tr> 
         <tr style="height:25px" align="left">   
            <td style="width:22%">
            Paid under OR No.:
            </td> 
            <td style="width:20%">
            <u>'.$main['OR No'].'</u>
            </td>                  
         </tr> 
         <tr style="height:25px" align="left">   
            <td style="width:22%">
            OR Date:
            </td> 
            <td style="width:20%">
            <u>'.$main['OR Date'].'</u>
            </td>                       
         </tr>   
         <tr style="height:25px" align="left">   
            <td style="width:50%">
            City of Iligan, Lanao Del Norte, Philippines
            </td>                      
         </tr>              
</table>';
        PDF::SetTitle('Sample');
        PDF::AddPage('');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
        }catch (\Exception $e) {
            return response()->json(new JsonResponse(['status'=>'error']));
        }
    }
    public function ref(Request $request)
    {
        $pre = 'EP';
        $table = $this->lgu_db . ".cho1_exhumation_permit";
        $date = $request->date;
        $refDate = 'date_application';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function refDirect($date)
    {
        // log::debug($date);
        $pre = 'EP';
        $table = $this->lgu_db . ".cho1_exhumation_permit";
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
            $idx = $request->main['exhumation_permit_id'];
            if ($idx>0) {
                $this->update($idx,$main,$ctobill);
            } else {
                $main['certificate_number']= $this->refDirect($main['date_application']);
                $this->save($main,$ctobill);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  

    public function save($main,$ctobill) {
 
        DB::table($this->lgu_db.'.cho1_exhumation_permit')->insert($main);
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
                   'transaction_type'=>'Exhumation Permit',
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
        $data['main'] = DB::table($this->lgu_db.'.cho1_exhumation_permit')->where('exhumation_permit_id',$id)->get();
        return response()->json(new JsonResponse($data));

    }
    
    public function delete(Request $request)
    {   
      $id=$request->id;

      $data['status'] = 'CANCELLED';
      DB::table($this->lgu_db.'.cho1_exhumation_permit')->where('exhumation_permit_id', $id) ->update($data);
      
      $reason['Form_name'] ='Exhumation Permit';
      $reason['Trans_ID'] =$id;
      $reason['Type_'] ='Cancel Record';
      $reason['Trans_by'] =Auth::user()->id;

      $this->G->insertReason($reason);

      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

    public function update($idx, $main,$ctobill) 
    {
        //$id = $request->main['id'];
        DB::table($this->lgu_db.'.cho1_exhumation_permit')->where('exhumation_permit_id',$idx)->update($main);
        
    }
}                