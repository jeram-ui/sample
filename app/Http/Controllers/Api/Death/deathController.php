<?php

namespace App\Http\Controllers\Api\Death;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;

class deathController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
    }

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //public function store(Request $request)
    //
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }     
    // public function displayData()
    // {
    //     $list = DB::select('call '.$this->lgu_db.'.cho1_exhumation_display_gigil');
    //     return response()->json(new JsonResponse($list));
    // }
    public function filterData(Request $request)
    {
        $datefrom = $request->from;
        $dateto = $request->to;
        $type = 'Death Certificate Health';
        $list = DB::select('call '.$this->lgu_db.'.cvl_cho1_death_certificate_display(?,?,?)',array($datefrom,$dateto,$type));
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
        <h2 style="width:14%;text-align:center;font-size:13px">DEATH CERTIFICATE LIST</h2>
        <h3 style="width:14%;text-align:center;font-size:11px">'.$filters.'</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:10%;font-size:9px">Certificate No.</th>
        <th style = "width:10%;font-size:9px">Application Date</th>
        <th style = "width:20%;font-size:9px">Cadaver Name</th>
        <th style = "width:10%;font-size:9px">Died</th>
        <th style = "width:20%;font-size:9px">Died At</th>
        <th style = "width:15%;font-size:9px">Requested By</th>
        <th style = "width:15%;font-size:9px">Doctor Name</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            
            $main =($row);   
            $html_content .='
            <tr>
            <td style="width:10%;text-align:left;font-size:8px">'.$main['Certificate No.'].'</td>
            <td style="width:10%;text-align:center;font-size:8px">'.$main['Date of Application'].'</td>
            <td style="width:20%;text-align:left;font-size:8px">'.$main['Cadaver Name'].'</td>
            <td style="width:10%;text-align:center;font-size:8px">'.$main['Died'].'</td>
            <td style="width:20%;text-align:left;font-size:8px">'.$main['Died At'].'</td>
            <td style="width:15%;text-align:left;font-size:8px">'.$main['Requested By'].'</td>
            <td style="width:15%;text-align:left;font-size:9px">'.$main['Doctor Name'].'</td>
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
        <br>
        <h2 style="width:14%;text-align:center;font-size:15px">DEATH CERTIFICATE</h2>
        <h3 style="width:14%;text-align:center;font-size:15px"></h3>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<table width ="100%">
         <tr style="height:30px" align="left">
            <td style="width:.01%">               
            </td>                
            <td style="width:98%;">
            <b><u>'.$main['head_name'].'</u></b>                   
            </td>
         </tr>
         <tr style="height:25px" align="left">
            <td style="width:.01%">               
            </td>                
            <td style="width:98%;">
            City Health Officer                  
            </td>
         </tr>
         <tr style="height:25px" align="left">
            <td style="width:.01%">               
            </td>                
            <td style="width:98%;">
            City of Iligan, Lanao Del Norte                  
            </td>
         </tr>
         <br>
         <br>      
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;       This is to certify that <b><u>'.$main['Cadaver Name'].'</u></b> died last <b><u>'.$main['Died'].'</u></b> at <b><u>'.$main['Died At'].'</u></b>.</span>
            </td>                   
         </tr>
         <br>      
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This certification is being issued upon the request of <b><u>'.$main['Requested By'].'</u></b> for the issuance of Death Certificate.</span>
            </td>                   
         </tr>
         <br>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;     Done this <b><u>'.date("jS \of F, Y ",strtotime($main['Issued Date'])).'</u></b>, at Rural Health Unit of City of Iligan, Lanao Del Norte.</span>
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
            Certification Fee:
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
        $pre = 'DC';
        $table = $this->lgu_db . ".cho1_death_certificate";
        $date = $request->date;
        $refDate = 'date_application';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function store(Request $request) 
    {
        try {
            //DB::beginTransaction();
            //dd($request->details);
            // dd($request);
            
            $main = $request->main;
            $ctobill = $request->cto;
            $idx = $request->main['cert_id'];
            if ($idx>0) {
                $this->update($idx,$main,$ctobill);
            } else {
                $this->save($main,$ctobill);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  

    public function save($main,$ctobill) {
 
        DB::table($this->lgu_db.'.cho1_death_certificate')->insert($main);
        $id = DB::getPDo()->lastInsertId();     
        $signatory = DB::select('Call ' . $this->lgu_db . '.cvl_get_signatory_mayor_head()');
        foreach($signatory as $row){
          $sign = array(
            'form_id'=>$id,
            'form_name'=>'Death Certificate Health',
            'bns_id'=>0,
            'pp_id'=>$main['req_id'] ,
            'user_id'=> Auth::user()->id,
            'head_id'=>$row->health_head_id,
            'head_position'=>$row->health_head_pos,
            'head_name'=>$row->health_head_name,
            'mayor_id'=>$row->mayor_id,
            'mayor_position'=>$row->mayor_pos,
            'mayor_name'=>$row->mayor_name,   
          );
          DB::table($this->general.'.signatory_logs')->insert($sign);
   
        }
        foreach ($ctobill as $row) {                      
            if ($row['Include'] === "True") {  
          
                $cto = array(   
                   'payer_type'=>'Person',
                   'payer_id'=>$main['req_id'],
                   'business_application_id'=>$main['req_id'],
                   'account_code'=>$row['Account Code'],
                   'bill_description'=>$row['Account Description'],
                   'net_amount'=>$row['Initial Amount'],
                   'bill_amount'=>$row['Fee Amount'],
                   'bill_month'=>$main['date_application'],
                   'bill_number'=>$main['certificate_number'],
                   'transaction_type'=>'Death Certificate Health',
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
        $data['main'] = DB::table($this->lgu_db.'.cho1_death_certificate')->where('cert_id',$id)->get();
        return response()->json(new JsonResponse($data));

    }
    
    public function delete(Request $request)
    {   
      $id=$request->id;

      $data['status'] = 'CANCELLED';
      DB::table($this->lgu_db.'.cho1_death_certificate')->where('cert_id', $id) ->update($data);
      
      $reason['Form_name'] ='Death Certificate Health';
      $reason['Trans_ID'] =$id;
      $reason['Type_'] ='Cancel Record';
      $reason['Trans_by'] =Auth::user()->id;

      $this->G->insertReason($reason);

      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

    public function update($idx, $main,$ctobill) 
    {
        DB::table($this->lgu_db.'.cho1_death_certificate')->where('cert_id',$idx)->update($main);
        
    }
}                