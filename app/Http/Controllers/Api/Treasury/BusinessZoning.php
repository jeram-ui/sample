<?php

namespace App\Http\Controllers\Api\Treasury;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;
use Illuminate\Support\Facades\log;

class BusinessZoning extends Controller
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

    public function index()
    {
    }
    public function display(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.jay_ebplo_display_ecpdc_application_form_cert(?,?,?)', array($from, $to, 'Business Zoning'));
        return response()->json(new JsonResponse($list));
    }
    public function displaybusinesslist(Request $request)
    {
        $dateNow = date("Y", strtotime($request->now));
        $list = DB::select('call ' . $this->lgu_db . '.cvl_get_registered_business(?)', array($dateNow));
        return response()->json(new JsonResponse($list));
    }
    public function displaybrgylist()
    {
        $list = DB::select('call ' . $this->lgu_db . '.jay_display_brangay_list()');
        return response()->json(new JsonResponse($list));
    }

    public function displaycadastrallot()
    {
        $list = DB::select('call ' . $this->lgu_db . '.cvl_display_cadastral_lot()');
        return response()->json(new JsonResponse($list));
    }
    public function ref(Request $request)
    {
        $pre = 'ZN';
        $table = $this->lgu_db . ".ecpdc_application_form_cert";
        $date = $request->date;
        $refDate = 'application_trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function displaytaxdec(Request $request)
    {
        $brgyid = $request->brgy_id;
        $list = DB::select('call ' . $this->lgu_db . '.Cvl_display_taxNo3(?)', array($brgyid));
        return response()->json(new JsonResponse($list));
    }
    public function displayclassification()
    {
        $list = DB::select('call ' . $this->lgu_db . '.displayclassification_gigil()');
        return response()->json(new JsonResponse($list));
    }

    public function displaybillingfees(Request $request)
    {
        $id = $request->application_form_id;
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_setup_certification_permit_jay(?,?)', array('Business Zoning', $id));
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        try {
         

            $idx = $request->main['application_form_id'];
            $main = $request->main;
            $detail = $request->main;
            $cto = $request->cto;
            
            unset($main['businessname']);
            unset($main['owner']);
            unset($main['bill_id']);
            unset($main['td_no']);
            unset($main['lot_no']);
            unset($main['bussno']);
            log::debug($request);
            if ($idx > 0) {
                $this->update($idx, $main);
            } else {
                $this->save($main, $detail, $cto);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function save($main, $detail, $cto)
    {
        $ctobill = $cto;
        log::debug($detail);
        DB::table('' . $this->lgu_db . '.ecpdc_application_form_cert')->insert($main);
        $id = DB::getPdo()->lastInsertId();

        foreach ($ctobill as $row) {
            if ($row['Include'] == true) {
                $cto = array(
                    'payer_type' => 'Business',
                    'payer_id' => $main['bus_no'],
                    'business_application_id' => $main['bns_applicant_id'],
                    'account_code' => $row['Account Code'],
                    'bill_description' => $row['Account Description'],
                    'net_amount' => $row['Initial Amount'],
                    'bill_amount' => $row['Fee Amount'],
                    'bill_month' => $main['application_trans_date'],
                    'bill_number' => $main['reference_no'],
                    'transaction_type' => 'Business Zoning',
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'include_from' => 'Others',
                );
               log::debug($cto);
                DB::table($this->lgu_db . '.cto_general_billing')->insert($cto);
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function editzoning($id)
    {

        $data['main'] = DB::select('call ' . $this->lgu_db . '.gigil_ebplo_modify_ecpdc_application_form_cert(?)', array($id));
        return response()->json(new JsonResponse($data));
    }

    public function update($id, $main)
    {
        DB::table($this->lgu_db . '.ecpdc_application_form_cert')
            ->where('application_form_id', $id)
            ->update($main);
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
    public function delete(Request $request)
    {
        $id = $request->id;

        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.ecpdc_application_form_cert')->where('application_form_id', $id)->update($data);

        $reason['Form_name'] = 'Business Zoning';
        $reason['Trans_ID'] = $id;
        $reason['Type_'] = 'Cancel Record';
        $reason['Trans_by'] = Auth::user()->id;

        $this->G->insertReason($reason);

        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

    public function printbussinesszoninglist(Request $request)
    {
        $data = $request->main;
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h2 align="center">BUSINESS ZONING LIST</h2>
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
            <th rowspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Application Date
                <br>
            </th>
            <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Business Name
                <br>
            </th> 
            <th colspan="2" style="width: 20%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Business Address
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   Classification
                <br>
            </th> 
            <th colspan="2" style="width: 8%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
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

            foreach ($data as $row) {
                $html_content .= '
                <tr style="font-family: Arial, font-size: 8pt" align="center">
                <td width="12%"> ' . $row['Reference No'] . '</td>
                <td width="12%"> ' . $row['Application Date'] . '</td>
                <td width="15%" align="left"> ' . $row['Business Name'] . '</td>
                <td width="20%" align="left">' . $row['Business Address'] . '</td>
                <td width="10%">' . $row['Classification'] . '</td>
                <td width="8%" align="left"> ' . $row['Lot No'] . '</td>
                <td width="13%">' . $row['Certification Purpose'] . '</td>
                <td width="8%">' . $row['Zoning Fee'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
            </table>';

            PDF::SetTitle('Print Master List');
            PDF::Addpage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printCERT($ID)
    {
        $data = DB::select('call ' . $this->lgu_db . '.jay_ebplo_display_ecpdc_application_form_certprint(?)', array($ID));

        foreach ($data as $row) {
            $businessname = $row->{'Business Name'};
            $appname = $row->{'Applicant Name'};
            $busadd = $row->{'Business Address'};
            $lotno = $row->{'Lot No'};
            $class = $row->{'Classification'};
            $resno = $row->{'Resolution No'};
            $dtrn = $row->{'res_date'};
            $series = $row->{'Series No'};
            $ordno = $row->{'Ordinance No'};
            $orddate = $row->{'ord_date'};
            $orNo = $row->{'OR No'};
            $orDate = $row->{'OR Date'};
            $amount = $row->{'Zoning Fee'};
        }
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '12
        ');
            $html_content = '
        ' . $logo . '
        <h2 style="width:100%; border-top: 1px solid black"></h2>
        <br>
        <br>
        <br>
        <br>
        <h2 align="center" style="line-height: 5px"> BUSINESS ZONING CERTIFICATION </h2>
        
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
        <br>
        <tr style="height:25px" align="left">   
            <td style="width:100%;">
                TO WHOM IT MAY CONCERN:
            </td>    
        </tr> 
        <br>
        <tr>
            <td style="width:100%; text-indent: 80px"><span style="text-align:justify;">This is to certify that according to the Zoning Map/Comprehensive Land Use Plan of 1997-2005 in this office,</span></td>           
        </tr>
        <tr style="height:25px" align="left">
            <td style="width:81%; border-bottom: 1px solid black" align="center">
            ' . $businessname . '
            </td>  
            <td style="width:20%">   
            which is/are  located  at
            </td>  
        </tr>
        <tr style="height:25px" align="left">   
            <td style="width:82%; border-bottom: 1px solid black" align="center">                            
            ' . $busadd . '
            </td>   
            <td style="width:20%" align="left">                            
            with the Cad Lot No.
            </td>                 
         </tr> 
         <tr style="height:25px" align="left">
            <td style="width:15%; border-bottom: 1px solid black" align="center">
            ' . $lotno . '
            </td>  
            <td style="width:15%" align="left">       
            is/are within the 
            </td> 
            <td style="width:14%; border-bottom: 1px solid black" align="center">
            ' . $class . '
            </td> 
            <td style="width:60%" align="left">       
            area (except Timberland Area) as per Provincial Board Resolution No. 
            </td> 
        </tr> 
        <tr style="height:25px" align="left">
            <td style="width:15%; border-bottom: 1px solid black" align="center">
            ' . $resno . '
            </td>  
            <td style="width:10%" align="left">       
            series of 
            </td> 
            <td style="width:10%; border-bottom: 1px solid black" align="center">
            ' . $series . '
            </td> 
            <td style="width:21%" align="left">       
            which was approved last  
            </td> 
            <td style="width:13%; border-bottom: 1px solid black" align="center">
            ' . $dtrn . '
            </td>  
            <td style="width:40%" align="left">       
            and per Municipal Ordinance Number 
            </td> 
        </tr> 
        <tr style="height:25px" align="left">
            <td style="width:15%; border-bottom: 1px solid black" align="center">
                ' . $ordno . '
            </td>  
            <td style="width:10%" align="left">       
                series of 
            </td> 
            <td style="width:10%; border-bottom: 1px solid black" align="center">
                ' . $series . '
            </td> 
            <td style="width:12%" align="left">       
             approved on  
            </td> 
            <td style="width:12%; border-bottom: 1px solid black" align="center">
                ' . $orddate . '
            </td><td>.</td>
        </tr> 
        <br>
        <tr style="height:25px" align="left">            
            <td style="width:50%; text-indent: 80px">This certification is issued upon request of</td>
            <td style="width:49%; border-bottom: 1px solid black" align="center">
                ' . $appname . '
            </td>                  
        </tr> 
        <tr style="height:25px" align="left"> 
            <td style="width:100%;" align="left">
                for area classification only and NOT FOR MAYOR’S PERMIT APPLICATION.
            </td>   
        </tr>

        <br>
        <br>
        <br>
         <tr style="height:25px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
            ENGR. ELISA P. MADRAZO
            </td>                             
        </tr>

        <tr style="height:25px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
            City ENR Officer
            </td>                             
        </tr>
        <br>
        <br>
        <tr style="height:25px">   
            <td style="width:20%"  align="left">
             OR No.:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
                ' . $orNo . '
            </td>                  
         </tr> 

         <tr style="height:25px">   
             <td style="width:20%"  align="left">
               Amount Paid :
             </td> 
             <td style="width:20% ; border-bottom: 1px solid black" align="left">
             ' . $amount . '
             </td>                  
         </tr> 

         <tr style="height:25px">   
            <td style="width:20%"  align="left">
             Date:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
            ' . $orDate . '
            </td>                  
         </tr>

         <tr style="height:25px">   
            <td style="width:40%"  align="left">
            
            </td>                   
         </tr>

    </table>';

            PDF::SetTitle('CERTIFICATE');
            PDF::AddPage('P');
            PDF::SetFont('times', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
