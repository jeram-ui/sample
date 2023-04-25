<?php
namespace App\Http\Controllers\Api\LotZoning;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;


class lotzoningvariance extends Controller
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
    }

    public function index()
    {
    }
    public function displaylist(Request $request)
    {
        
        $from = $request->from;
        $to =  $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.jay_ebplo_display_ecpdc_application_form_cert_gigil_withvariance(?,?,?)', array($from,$to,'Lot Zoning With Variance'));
     
        return response()->json(new JsonResponse($list));
    }
    public function display(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.cvl_display_project_registration_list_notin(?,?)', array($from,$to));
        return response()->json(new JsonResponse($list));
    }
    public function displayzpurpose()
    {
        $list = DB::select("select * from ".$this->lgu_db.".ecpdc_lot_purpose");
        return response()->json(new JsonResponse($list));
    }
    public function displayclassification()
    {
        $list = DB::select("select class_id,".$this->lgu_db.".UpperCase_sly(class_name) 'class_name' from ".$this->lgu_db.".ecao_classification_setup WHERE stat <> 'Cancelled'");
        return response()->json(new JsonResponse($list));
    }
    public function displaytaxdeclist(Request $request)
    {
        $list = DB::select('call '.$this->lgu_db.'.Cvl_display_taxNo()');
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        try {
        // dd($request);
            $idx = $request->main['id'];
            $main = $request->main; 
            $cto = $request->cto;
            $detail = $request->main;

            unset($main['id']);
            unset($main['applicant_name']);
            unset($main['bus_id']);
            unset($main['lotid']);
            unset($main['tdid']);
            unset($main['existing']);
  
            if ($idx > 0) {
                $this->update($idx, $main);
            }else{
                $this->save($main,$cto,$detail);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function save($main,$cto,$detail)
    {
           
            $ctobill = $cto;
            DB::table($this->lgu_db.'.ecpdc_application_form_cert_lotzoning')->insert($main);
            $id = DB::getPdo()->lastInsertId();
            
            foreach ($ctobill as $row) {
                
                if ($row['Include'] == true) {
                    $cto = array(
                        'payer_type' =>$main['applicant_type'],
                        'payer_id' =>$detail['bus_id'],
                        'business_application_id' =>$detail['applicant_id'],
                        'account_code' =>$row['Account Code'],
                        'bill_description' =>$row['Account Description'],
                        'net_amount' =>$row['Initial Amount'],
                        'bill_amount' =>$row['Fee Amount'],
                        'bill_month' =>$main['application_trans_date'],
                        'bill_number' =>$main['reference_no'],
                        'transaction_type' => $main['frm_name'],
                        'ref_id' =>$id,
                        'bill_id' =>$id,
                        'include_from' =>'Others',
                    );
                    DB::table($this->lgu_db.'.cto_general_billing')->insert($cto);
                }
            }
            // $signatory = DB::select('Call ' . $this->lgu_db . '.cvl_get_signatory_mayor_head()');
            
            // foreach($signatory as $row){
               
            // $sign = array(
            //     'form_id' => $id,
            //     'form_name'=>'Lot Zoning',
            //     'bns_id' => $main['bus_id'],
            //     'pp_id' => 0,
            //     'user_id' => Auth::user()->id,
            //     'head_id' => $row->envi_id,
            //     'head_name' => $row->envi_name,
            //     'head_position' => $row->envi_pos,  
            //     'mayor_id' => $row->mayor_id,
            //     'mayor_name' => $row->mayor_name, 
            //     'mayor_position' => $row->mayor_pos,  
            // );
            
            // DB::table($this->general.'.signatory_logs')->insert($sign);
            // dd($sign);
            // }
            
            // $land = array(
            //     'frm_name' => 'Lot Zoning',
            //     'frm_pk' => $id,
            //     'new_class' => $main['new_lot_classification'],
            //     'old_class' => $detail['classification'],
            //     'td_id' => $detail['tdid'],
            //     'lot_id' => $detail['lotid'],
            //     'brgy_id' => $main['brgy_id'],
            // );
            // DB::table($this->general.'.ecpdc_land_TD_dtl')->insert($land);
            // dd($land);
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));  
    }
    public function editlotzoning($id) {
        $data['main'] = DB::select('call '.$this->lgu_db.'.jay_ebplo_modify_ecpdc_application_form_cert_gigil(?)',array($id));
        $data['cto'] = DB::table($this->lgu_db.'.cto_general_billing')
        ->select('ref_id as id',
        'payer_type',
        'transaction_type',
        'bill_number',
        'payer_id',
        'business_application_id',
        'account_code',
        'bill_description',
        'net_amount',
        'bill_amount')
    ->where('bill_id', $id) ->get(); 
        return response()->json(new JsonResponse($data));

    }
    public function update($id, $main) 
    {
        DB::table($this->lgu_db.'.ecpdc_application_form_cert_lotzoning')
            ->where('application_form_id',$id)
            ->update($main);
        
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    } 

    public function delete(Request $request)
    {  
        $id=$request->id;
  
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db.'.ecpdc_application_form_cert_lotzoning')->where('application_form_id', $id) ->update($data);
        
        $reason['Form_name'] ='Lot Zoning';
        $reason['Trans_ID'] =$id;
        $reason['Type_'] ='Cancel Record';
        $reason['Trans_by'] =Auth::user()->id;
  
        $this->G->insertReason($reason);
  
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
   
    public function printLotCert($id)
    {
        $data = DB::select('call '.$this->lgu_db.'.jay_ebplo_print_ecpdc_application_form_certwithvariance_gigil(?,?)',array($id,'Lot Zoning With Variance'));
        $otherdata = DB::select('select * from '.$this->general.'.tbl_general_header_setup');
        
        foreach($data as $row) { 
            
            $lotno = $row->{'Lot No'};
            $tdno = $row->{'bns_tdno'};
            $location = $row->{'lot_location'};
            // $muncity = $otherdata->{'header3'};
            // $prov = $otherdata->{'header2'};
            $classification = $row->{'new_lot_classification'};
            $resno = $row->{'Resolution No'};
            $series = $row->{'Series No'};
            $resdate = $row->{'res_date'};
            $ordno = $row->{'Ordinance No'};
            $orddate = $row->{'ord_date'};
            $applicant = $row->{'Applicant'};
            $orno = $row->{'OR No'};
            $ordate = $row->{'OR Date'};
            $oramnt = $row->{'Zoning Fee'};
            $spresno = $row->{'sp_resolution_no'};
            $spdate = $row->{'date_approvedby_sp'}; 
            $areaclass = $row->{'area_reclassified'}; 
            $reclasslot = $row->{'reclassified_lotno'};
            $newlot = $row->{'new_lot_classification'};
            $head = $row->{'head_name'};
            $pos = $row->{'head_position'};
        }
        $logo = config('variable.logo');             
        try{
        PDF::SetFont('Helvetica', '', '12
        ');
        $html_content ='
        '.$logo.'
        <h4 align="center" style="line-height: 10px"> OFFICE OF THE CPDC </h4>
        <br>
        <h4 style="border-top: 1px solid black"></h4>
        <br>
        <h2 align="center" style="line-height: 5px"> LOT ZONING CERTIFICATION WITH VARIANCE</h2>
        <style>
        table{
            width:100%;
            padding:3px;
        }
        .caption-label{width: 15%
        }
        .caption-label-center{text-align: center;
        }
        .caption-line{width: 35%;
        border-bottom: 1px solid black;
        }          
        </style>
    
        <table width ="100%">
        <br>
        <br>
        <br>
        <tr>
            <td style="width:100%;" align="left"><b>TO WHOM IT MAY CONCERN:</b></td>
        </tr>
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:93%;" align="left">This  is  to  certify  that according to the Zoning Map/Comprehensive Land Use Plan of 1997-2005 in this office, Lot owned</td>
        </tr>
        <tr style="line-height:10px" align="left">   
            <td style="width:4%;">by</td>
            <td style="width:45%;border-bottom: 1px solid black" align="center">
                '.$applicant.'
            </td>  
            <td style="width:16%;"> with Cad. Lot No.</td>
            <td style="width:22%;border-bottom: 1px solid black" align="center">
                '.$lotno.'
            </td>    
            <td style="width:20%;"> under Tax No.</td>            
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"></td>
            <td style="width:22%;border-bottom: 1px solid black" align="center">
                '.$tdno.'
            </td>    
            <td style="width:10%;">, located at</td>    
            <td style="width:34%;border-bottom: 1px solid black" align="center">
                '.$location.'
            </td>   
            <td style="width:2%;">,</td>
            <td style="width:23%;border-bottom: 1px solid black" align="center">
               
            </td>  
            <td style="width:10%;">is within</td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:4%;">the</td> 
            <td style="width:20%;border-bottom: 1px solid black" align="center">
                '.$classification.'
            </td>  
            <td style="width:35%;">area  as per Provincial Board Resolution No. </td>
            <td style="width:22%;border-bottom: 1px solid black" align="center">
            '.$resno.'
            </td>  
            <td style="width:8%;">series of</td>
            <td style="width:10%;border-bottom: 1px solid black" align="center">'.$series.'</td> 
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:21%;">which was approved last </td> 
            <td style="width:17%;border-bottom: 1px solid black" align="center">'.$resdate.'</td> 
            <td style="width:31%;"> and per Municipal Ordinance Number</td> 
            <td style="width:23%;border-bottom: 1px solid black" align="center">
                '.$ordno.'
            </td> 
            <td style="width:10%;">series of</td>    
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:10%;border-bottom: 1px solid black" align="center">'.$series.'</td> 
            <td style="width:12%;">approved on</td>
            <td style="width:17%;border-bottom: 1px solid black" align="center">'.$orddate.'</td> 
            <td style="width:2%;">.</td> 
        </tr> 
        <br>
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:74%;" align="left">However pursuant to the variance as to Land-use granted by the Sangguniang Panlungsod of the </td>
            <td style="width:18%; border-bottom: 1px solid black" align="center">
            
            </td> 
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:25%; border-bottom: 1px solid black" align="center">
            
            </td> 
            <td style="width:4%;">per</td>
            <td style="width:22%; border-bottom: 1px solid black" align="center">
            '.$spresno.'
            </td> 
            <td style="width:12%;">approved on</td>
            <td style="width:15%; border-bottom: 1px solid black" align="center">'.$spdate.'</td>  
            <td style="width:22%;">reclassifying the lot area of</td> 
        </tr>
        <tr style="line-height:10px" align="left">
           <td style="width:10%; border-bottom: 1px solid black" align="center">
                '.$areaclass.'
            </td> 
            <td style="width:5%;">from</td>
            <td style="width:22%; border-bottom: 1px solid black" align="center">
                '.$reclasslot.'
            </td> 
            <td style="width:5%;">to</td>
            <td style="width:22%; border-bottom: 1px solid black" align="center">
                '.$newlot.'
            </td> 
            <td style="width:5%;">.</td>
        </tr>
        <br>
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:10%;" align="left">Issued this</td>
            <td style="width:5%; border-bottom: 1px solid black" align="center"></td>
            <td style="width:6%;">day of</td>
            <td style="width:12%; border-bottom: 1px solid black" align="center"></td>
            <td style="width:2%;">,</td>
            <td style="width:7%; border-bottom: 1px solid black" align="center"></td>
            <td style="width:3%;">at</td>
            <td style="width:15%; border-bottom: 1px solid black" align="center"></td>
            <td style="width:2%;">,</td>
            <td style="width:15%; border-bottom: 1px solid black" align="center"></td>
            <td style="width:15%;">Philippines.</td>
        </tr>
        <br>
        <br>
        <br>
         <tr style="height:25px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
              '.$head.'
            </td>                             
        </tr>

        <tr style="height:25px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
             '.$pos.'
            </td>                             
        </tr>
        <br>
        <br>
        <tr style="height:25px">   
            <td style="width:20%"  align="left">
             OR No.:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
               '.$orno.'
            </td>                  
         </tr> 

         <tr style="height:25px">   
             <td style="width:20%"  align="left">
               Amount Paid :
             </td> 
             <td style="width:20% ; border-bottom: 1px solid black" align="left">
             '.$oramnt.'
             </td>                  
         </tr> 

         <tr style="height:25px">   
            <td style="width:20%"  align="left">
             Date:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
            '.$ordate.'
            </td>                  
         </tr>

         <tr style="height:25px">   
            <td style="width:40%"  align="left">

            </td>                   
         </tr>
        <br>
    </table>';
   
        PDF::SetTitle('CERTIFICATE');
        PDF::AddPage('P');
        PDF::SetFont('times', '', 10);
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf','F');
        return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg'=> $e, 'status' => 'error']));
        }
    }
    
    public function printLotzoninglist(Request $request)
    {
        $data = $request->main;
        $filter = $request->filter;
        $filterdisplay = '';
        
        if ($filter['filter'] == 'Month') {
            $filterdisplay = 'Month of ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Daily') {   
            $filterdisplay = 'As of ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Year') {   
            $filterdisplay = 'Year ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Range') {   
            $filterdisplay = 'As of ' . $filter['reportcaption'];
        }
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h2 align="center">LOT ZONING LIST</h2>
            <h4 align="center"> '. $filterdisplay .'</h4>
            <br></br>
            <br></br>
            <br></br>
        <table border="1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 12%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                Reference Number
                <br>
            </th>
            <th rowspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Application Date
                <br>
            </th>
            <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Applicant Name
                <br>
            </th> 
            <th colspan="2" style="width: 18%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                 Address
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   Classification
                <br>
            </th> 
            <th colspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                TD No
                <br>
            </th> 
            <th colspan="2" style="width: 13%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Certification Purpose
                <br>
            </th> 
            <th colspan="2" style="width: 8%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Zoning Fee
                <br>
            </th> 
           
        </tr>
    </thead>
                    <tbody >';
                    $TotalAmnt = 0;  
                    $COUNT = 0;    
            foreach ($data as $row) {
                
                $html_content .= '
                <tr style="font-family: Arial, font-size: 8pt" align="center">
                <td width="12%"> ' . $row['Reference No'] . '</td>
                <td width="10%"> ' . $row['Application Date'] . '</td>
                <td width="15%" align="left"> ' . $row['Applicant'] . '</td>
                <td width="18%" align="left">' . $row['Address'] . '</td>
                <td width="10%">' . $row['Classification'] . '</td>
                <td width="12%" align="left"> ' . $row['bns_tdno'] . '</td>
                <td width="13%">' . $row['Certification Purpose'] . '</td>
                <td width="8%">' . $row['Zoning Fee'] . '</td>
            </tr>';
            $TotalAmnt += floatval(str_replace(',','',$row['Zoning Fee']));
            $COUNT = $COUNT + 1;
            }
            $html_content .= '</tbody>
            </table>
            <table width="100%">
            <tr>
                <th style="width:90%;text-align:left;border: 1px solid black;"> TOTAL >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>></th>  
                <th style="width:8%;text-align:center;height:15px;border: 1px solid black;">'.number_format($TotalAmnt,2).'</th>  
            </tr> 
            <tr>
                <td style="width:98%;text-align:left;border: 1px solid black;">Total Records: '. $COUNT.'</td>  
            </tr> 
            </table>' ;
            PDF::SetTitle('Print Master List');
            PDF::Addpage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
