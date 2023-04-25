<?php
namespace App\Http\Controllers\Api\LotZoning;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;


class lotcalcination extends Controller
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
        $list = DB::select('call ' . $this->lgu_db . '.jay_ebplo_display_ecpdc_lot_zoning_calcination_gigil(?,?,?)', array($from,$to,'Lot Zoning Calcination'));
    
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
            DB::table($this->lgu_db.'.ecpdc_lot_zoning_calcination')->insert($main);
            
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
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));  
    }
    public function editlotzoning($id) {
        $data['main'] = DB::select('call '.$this->lgu_db.'.jay_ebplo_modify_ecpdc_lot_zoning_calcination_gigil(?)',array($id));
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
        DB::table($this->lgu_db.'.ecpdc_lot_zoning_calcination')
            ->where('application_form_id',$id)
            ->update($main);
        
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    } 

    public function delete(Request $request)
    {  
        $id=$request->id;
  
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db.'.ecpdc_lot_zoning_calcination')->where('application_form_id', $id) ->update($data);
        
        $reason['Form_name'] ='Lot Zoning Calcination';
        $reason['Trans_ID'] =$id;
        $reason['Type_'] ='Cancel Record';
        $reason['Trans_by'] =Auth::user()->id;
  
        $this->G->insertReason($reason);
  
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
   
    public function printLotCert($id)
    {
        $data = DB::select('call '.$this->lgu_db.'.jay_ebplo_PRINT_ecpdc_lot_zoning_calcination_gigil(?,?)',array($id,'Lot Zoning Calcination'));
        $otherdata = DB::select('select * from '.$this->general.'.tbl_general_header_setup');
        
        foreach($data as $row) { 
            
            $lotno = $row->{'bns_lotno'};
            $tdno = $row->{'bns_tdno'};
            $location = $row->{'lot_location'};
            // $muncity = $otherdata->{'header3'};
            // $prov = $otherdata->{'header2'};
            $classification = $row->{'classification'};
            $resno = $row->{'res_no'};
            $series = $row->{'series_of'};
            $resdate = $row->{'res_date'};
            $ordno = $row->{'ord_no'};
            $orddate = $row->{'ord_date'};
            $applicant = $row->{'applicant_name'};
            $orno = $row->{'OR No'};
            $ordate = $row->{'OR Date'};
            $oramnt = $row->{'Zoning Fee'};
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
        <h2 align="center" style="line-height: 5px"> LOT ZONING CALCINATION </h2>
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
            <td style="width:100%;" align="left">TO WHOM IT MAY CONCERN:</td>
        </tr>
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:8%;"></td>
            <td style="width:92%;" align="left">This is to certify that according to the Zoning Map/Comprehensive Land Use Plan of 1997-2005 in this office, Cad Lot</td>
        </tr>
        <tr style="line-height:10px" align="left">   
            <td style="width:5%;"> No.</td>
            <td style="width:33%; border-bottom: 1px solid black" align="center">
                '.$lotno.'
            </td>  
            <td style="width:13%;"> under Tax No.</td>
            <td style="width:32%; border-bottom: 1px solid black" align="center">
                '.$tdno.'
            </td>    
            <td style="width:20%;"> which is located at</td>            
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"></td>
            <td style="width:50%; border-bottom: 1px solid black" align="center">
            '.$location.'
            </td>  
            <td style="width:2%;">,</td>
            <td style="width:22%; border-bottom: 1px solid black" align="center">
                '.$location.'
            </td>   
            <td style="width:2%;">,</td>
            <td style="width:21%; border-bottom: 1px solid black" align="center">
                '.$location.'
            </td>  
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:12%;">is within the</td> 
            <td style="width:22%; border-bottom: 1px solid black" align="center">
            '.$classification.'
            </td>  
            <td style="width:35%;">area  as per Provincial Board Resolution No. </td>
            <td style="width:22%; border-bottom: 1px solid black" align="center">
            '.$resno.'
            </td>  
            <td style="width:10%;">series of</td>
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:10%; border-bottom: 1px solid black" align="center">'.$series.'</td> 
            <td style="width:21%;"> which was approved last </td> 
            <td style="width:15%; border-bottom: 1px solid black" align="center">'.$resdate.'</td> 
            <td style="width:31%;"> and per Municipal Ordinance Number</td> 
            <td style="width:21%; border-bottom: 1px solid black" align="center">
            '.$ordno.'
            </td>  
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:10%;">series of</td>   
            <td style="width:10%; border-bottom: 1px solid black" align="center">'.$series.'</td> 
            <td style="width:12%;">approved on</td>
            <td style="width:15%; border-bottom: 1px solid black" align="center">'.$orddate.'</td>
            <td style="width:2%;">.</td> 
        </tr> 
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:8%;"></td>
            <td style="width:34%;" align="left">This certification is issued upon request of </td>
            <td style="width:56%; border-bottom: 1px solid black" align="center">'.$applicant.'</td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%;">for area classification only and NOT FOR MAYOR’S PERMIT APPLICATION.</td>
        </tr>
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:8%;"></td>
            <td style="width:9%;" align="left">Given this</td>
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
            <td style="width:20%" align="left">
             OR No.:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
                '. $orno.'
            </td>                  
         </tr> 

         <tr style="height:25px">   
             <td style="width:20%"  align="left">
               Amount Paid :
             </td> 
             <td style="width:20% ; border-bottom: 1px solid black" align="left">
             '. $oramnt.'
             </td>                  
         </tr> 

         <tr style="height:25px">   
            <td style="width:20%"  align="left">
             Date:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
            '. $ordate.'
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
            <h2 align="center">LIST OF LOT ZONING CALCINATION</h2>
            <h4 align="center"> '. $filterdisplay .'</h4>
            <br></br>
            <br></br>
            <br></br>
        <table border="1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 6%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                Reference Number
                <br>
            </th>
            <th rowspan="2" style="width: 6%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Application Date
                <br>
            </th>
            <th rowspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Applicant
                <br>
            </th> 
            <th colspan="2" style="width: 13%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                 Address
                <br>
            </th> 
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Type of Entity
                <br>
            </th>
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Resolution No.
                <br>
            </th>
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Ordinance No.
                <br>
            </th>
            <th colspan="2" style="width: 4%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Series Of
                <br>
            </th>
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Lot No.
                <br>
            </th>
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   Classification
                <br>
            </th> 
            <th colspan="2" style="width: 7%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Certification Purpose
                <br>
            </th> 
            <th colspan="2" style="width: 4%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    North
                <br>
            </th> 
            <th colspan="2" style="width: 4%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    South
                <br>
            </th> 
            <th colspan="2" style="width: 4%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    East
                <br>
            </th> 
            <th colspan="2" style="width: 4%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   West
                <br>
            </th> 
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Calcination
                <br>
            </th> 
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
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
                <td width="6%"> ' . $row['Reference No'] . '</td>
                <td width="6%"> ' . $row['Application Date'] . '</td>
                <td width="12%" align="left"> ' . $row['Applicant'] . '</td>
                <td width="13%" align="left">' . $row['Address'] . '</td>
                <td width="5%">' . $row['Type of Entity'] . '</td>
                <td width="5%">' . $row['Resolution No'] . '</td>
                <td width="5%">' . $row['Ordinance No'] . '</td>
                <td width="4%">' . $row['Series No'] . '</td>
                <td width="5%" align="left"> ' . $row['Lot No'] . '</td>
                <td width="5%">' . $row['Classification'] . '</td>
                <td width="7%">' . $row['Certification Purpose'] . '</td>
                <td width="4%">' . $row['North'] . '</td>
                <td width="4%">' . $row['South'] . '</td>
                <td width="4%">' . $row['East'] . '</td>
                <td width="4%">' . $row['West'] . '</td>
                <td width="5%">' . $row['Calcination'] . '</td>
                <td width="5%">' . $row['Zoning Fee'] . '</td>
            </tr>';
            $TotalAmnt += floatval(str_replace(',','',$row['Zoning Fee']));
            $COUNT = $COUNT + 1;
            }
            $html_content .= '</tbody>
            </table>
            <table width="100%">
            <tr>
                <th style="width:94%;text-align:left;border: 1px solid black;"> TOTAL >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>></th>  
                <th style="width:5%;text-align:center;height:15px;border: 1px solid black;">'.number_format($TotalAmnt,2).'</th>  
            </tr> 
            <tr>
                <td style="width:99%;text-align:left;border: 1px solid black;">Total Records: '. $COUNT.'</td>  
            </tr> 
            </table>' ;
            PDF::SetTitle('Print Master List');
            PDF::Addpage('L',array(250,450));
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
