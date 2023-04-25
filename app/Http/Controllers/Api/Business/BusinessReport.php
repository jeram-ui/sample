<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class BusinessReport extends Controller
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

    public function businessPermitStatus(Request $request)
    {  
        $date = $request->from;
        //$date = date("Y", strtotime($date));
        $barangay = 'All';
        $businessType = 'All';
        $inspectionStatus = '%';
        if ($request->barangay == 'All') {
            $barangay = 'All';
        } else {
            $barangay = $request->barangay;
        }
        if ($request->businessType == 'All') {
            $businessType = 'All';
        } else {
            $businessType = $request->businessType;
        }
        if ($request->inspectionStatus == '') {
            $inspectionStatus = '%';
        } else {
            $inspectionStatus = $request->inspectionStatus;
        }
        
        if ($request->permitStatus == 'Unrenewed') {
            $list = DB::select('call '.$this->lgu_db.'.spl_display_unrenewed_business(?,?,?,?,?,?,?,?)',array($request->from,$request->to,$barangay,$businessType,$businessType,$request->permitStatus,$date,$request->now));
            return response()->json(new JsonResponse($list));
        } else {
            $list = DB::select('call '.$this->lgu_db.'.spl_display_business_list_permit_status(?,?,?,?,?,?,?,?)',array($request->from,$request->to,$barangay,$businessType,$businessType,$request->permitStatus,$date,$inspectionStatus));
            return response()->json(new JsonResponse($list));
        }  
    }
    public function businessPermitStatusPrint(Request $request){
        $data = $request->main;
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">Business Permit Status</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "13%">Business Name</th>
        <th width = "12%">Name of Owner</th>
        <th width = "12%">Business Address</th>
        <th width = "12%">Contact No.</th>
        <th width = "12%">Business Type</th>
        <th width = "12%">Kind of Business</th>
        <th width = "9%">Date Registered</th>
        <th width = "9%">Date Released</th>
        <th width = "9%">No. of Employees</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "13%">'.$main['Business Name'].'</td>
            <td width = "12%">'.$main['Owner'].'</td>
            <td width = "12%">'.$main['Business Address'].'</td>
            <td width = "12%">'.$main['Contact No'].'</td>
            <td width = "12%">'.$main['Organization Type'].'</td>
            <td width = "12%">'.$main['Nature of Business'].'</td>
            <td width = "9%">'.$main['Application Date'].'</td>
            <td width = "9%">'.$main['Date of Issuance'].'</td>
            <td width = "9%">'.$main['No of Employee'].'</td>
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
    public function businessPaymentStatus(Request $request)
    {
        $datefrom = $request->from;
        $dateto = $request->to;
        $brgy = 'All';
        $businessType = 'All';
        $businessStatus = 'All';
        $paymentStatus = 'All';
        $businessCategory = 'All';
        if ($request->brgy == '' || $request->brgy == 'All') {
            $brgy = 'All';
        } else {
            $brgy = $request->brgy;
        }
        if ($request->businessType == '' || $request->businessType == 'All') {
            $businessType = 'All';
        } else {
            $businessType = $request->businessType;
        }
        if ($request->businessStatus == '' || $request->businessStatus == 'All') {
            $businessStatus = 'All';
        } else {
            $businessStatus = $request->businessStatus;
        }
        if ($request->paymentStatus == '' || $request->paymentStatus == 'All') {
            $paymentStatus = 'All';
        } else {
            $paymentStatus = $request->paymentStatus;
        }
        if ($request->businessCategory == '' || $request->businessCategory == 'All') {
            $businessCategory = 'All';
        } else {
            $businessCategory = $request->businessCategory;
        }
        {
            $list = DB::select('call '.$this->lgu_db.'.spl_display_business_list_payment_status(?,?,?,?,?,?,?)',array($datefrom,$dateto,$brgy,$businessType,$businessStatus,$paymentStatus,$businessCategory));
            return response()->json(new JsonResponse($list));
        }
    } 
    public function businessPaymentStatusPrint(Request $request){
        $data = $request->main;
        $filter = $request->filter;
      
        $from = date("F j, Y", strtotime($filter['from'])) ;
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
        <h2 align="center">PAYMENT STATUS MASTER LIST</h2>
        <h3 align="center">'.$filters.'</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "8%">Permit No.</th>
        <th width = "10%">Name of Owner</th>
        <th width = "10%">Business Name</th>
        <th width = "10%">Business Address</th>
        <th width = "10%">Type of Business</th>
        <th width = "10%">Kind of Business</th>
        <th width = "8%">Date Released</th>
        <th width = "7%">Mode of Payment</th>
        <th width = "8%">Assessed Amount</th>
        <th width = "6%">OR Number</th>
        <th width = "7%">OR Amount</th>
        <th width = "8%">Status of Payment</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "8%">'.$main['Permit No'].'</td>
            <td width = "10%">'.$main['Tax Payers Name'].'</td>
            <td width = "10%">'.$main['Business Name'].'</td>
            <td width = "10%">'.$main['Address'].'</td>
            <td width = "10%">'.$main['Type Of Ownership'].'</td>
            <td width = "10%">'.$main['kind of business'].'</td>
            <td width = "8%">'.$main['Date of Issuance'].'</td>
            <td width = "7%">'.$main['mode_of_payment'].'</td>
            <td width = "8%">'.$main['Assessment Amount'].'</td>
            <td width = "6%">'.$main['or_number'].'</td>
            <td width = "7%">'.$main['or_amount'].'</td>
            <td width = "8%">'.$main['Payment Status'].'</td>
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
    public function taxPayerReport(Request $request)
    {   
        $datefrom = $request->from;
        $dateto = $request->to;
        $brgy = 'All';
        $businessType = 'All';
        $businessStatus = 'All';
        $kindofBusiness = 'All';
        if ($request->brgy == '' || $request->brgy == 'All') {
            $brgy = 'All';
        } else {
            $brgy = $request->brgy;
        }
        if ($request->businessType == '' || $request->businessType == 'All') {
            $businessType = 'All';
        } else {
            $businessType = $request->businessType;
        }
        if ($request->businessStatus == '' || $request->businessStatus == 'All') {
            $businessStatus = 'All';
        } else {
            $businessStatus = $request->businessStatus;
        }
        if ($request->kindofBusiness == '' || $request->kindofBusiness == 'All') {
            $kindofBusiness = 'All';
        } else {
            $kindofBusiness = $request->kindofBusiness;
        }
        {
            $list = DB::select('call '.$this->lgu_db.'.jay_ebplo_display_business_taxpayer_report1(?,?,?,?,?,?)',array($datefrom,$dateto,$brgy,$businessType,$businessStatus,$kindofBusiness));
            return response()->json(new JsonResponse($list));
        }
    }
    
    public function taxPayerReportPrint(Request $request){
        $data = $request->main;
        $filter = $request->filter;
        $from = date("F j, Y", strtotime($filter['from'])) ;
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
        <h2 align="center">TAX PAYER REPORT</h2>
        <h3 align="center">'.$filters.'</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "16%">Name of Owner</th>
        <th width = "15%">Name of Business</th>
        <th width = "10%">Business Address</th>
        <th width = "9%">Contact No.</th>
        <th width = "11%">Type of Business</th>
        <th width = "10%">Kind of Business</th>
        <th width = "10%">Date Registered</th>
        <th width = "9%">Date Released</th>
        <th width = "11%">Status of Business</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "16%">'.$main['Tax Payers Name'].'</td>
            <td width = "15%">'.$main['Business Name'].'</td>
            <td width = "10%">'.$main['Address'].'</td>
            <td width = "9%">'.$main['Contact No'].'</td>
            <td width = "11%">'.$main['Type of Ownership'].'</td>
            <td width = "10%">'.$main['Kind of Business'].'</td>
            <td width = "10%">'.$main['Date of Application'].'</td>
            <td width = "9%">'.$main['Date of Issuance'].'</td>
            <td width = "11%">'.$main['business_status'].'</td>
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
    public function businessEnterprise(Request $request)
    {
        $date = $request->from;
        //$date = date("Y", strtotime($date));
        $barangay = 'All';
        $businessType = 'All';
        $category = 'All';
        if ($request->barangay == 'All') {
            $barangay = 'All';
        } else {
            $barangay = $request->barangay;
        }
        if ($request->businessType == 'All') {
            $businessType = 'All';
        } else {
            $businessType = $request->businessType;
        }
        if ($request->category == 'All') {
            $category = 'All';
        } else {
            $category = $request->category;
        }
        $list = DB::select('call '.$this->lgu_db.'.spl_ebplo_display_business_sme_report_jho1(?,?,?,?,?)',array($request->from,$request->to,$barangay,$businessType,$category));
        return response()->json(new JsonResponse($list));
        
    }
    public function businessEnterprisePrint(Request $request){
        $data = $request->main;

        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">Business Enterprise Report</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "13%">Business Name</th>
        <th width = "12%">Name of Owner</th>
        <th width = "12%">Business Address</th>
        <th width = "12%">Contact No.</th>
        <th width = "12%">Business Type</th>
        <th width = "12%">Capital/Gross</th>
        <th width = "9%">Type of SME</th>
        <th width = "9%">Drug Free</th>
        <th width = "9%">No. of Employees</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "13%">'.$main['Business Name'].'</td>
            <td width = "12%">'.$main['Tax Payers Name'].'</td>
            <td width = "12%">'.$main['Address'].'</td>
            <td width = "12%">'.$main['Contact No'].'</td>
            <td width = "12%">'.$main['Type of Ownership'].'</td>
            <td width = "12%">'.$main['Capital/Gross'].'</td>
            <td width = "9%">'.$main['SME'].'</td>
            <td width = "9%">'.$main['drug_free'].'</td>
            <td width = "9%">'.$main['No of Employee'].'</td>
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
    public function businessDTIReport(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $bustype = $request->businesstype;
        $busstat = $request->businessstatus;
        $buskind = $request->businesskind;
        $brgy = $request->barangay;

        $list = DB::select('call '.$this->lgu_db.'.jay_ebplo_display_business_dilgmonitoring_report_laravel(?,?,?,?,?,?)', array($from,$to,$brgy,$bustype,$busstat,$buskind));
        // dd($list);
        return response()->json(new JsonResponse($list));

    }
    public function businessDTIReportPrint(Request $request)
    {
        $data = $request->main;
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h2 align="center">DTI Report</h2>
            <br></br>
            <br></br>
        <table border="1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 18%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                    Name of Business
                <br>
            </th>
            <th rowspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                    Address of Business
                <br>
            </th>
            <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Name of Owner
                <br>
            </th> 
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Gender
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Type of Business
                <br>
            </th> 
            <th colspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                Kind of Business
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    No. of Months/Years in Business
                <br>
            </th> 
            <th colspan="2" style="width: 9%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Capitalization
                <br>
            </th> 
            <th colspan="2" style="width: 6%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    No. of Employees
                <br>
            </th> 
        </tr>
    </thead>
                    <tbody >';
            foreach ($data as $row) {
                
                $html_content .= '
                <tr style="font-family: Arial, font-size: 8pt" align="center">
                <td width="18%" align="left"> ' . $row['Business Name'] . '</td>
                <td width="12%" align="left"> ' . $row['Address'] . '</td>
                <td width="15%" align="left"> ' . $row['Tax Payers Name'] . '</td>
                <td width="5%">' . $row['Gender'] . '</td>
                <td width="10%">' . $row['Type of Business'] . '</td>
                <td width="15%" align="left"> ' . $row['Kind of Business'] . '</td>
                <td width="10%">' . $row['No of years/months'] . '</td>
                <td width="9%">' . $row['Capitalization'] . '</td>
                <td width="6%">' . $row['total_employee'] . '</td>
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

    public function businessDTIReportPrintMEI(Request $request)
    {
        $data = $request->main;
        
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h3 align="center">DEPARTMENT OF TRADE AND INDUSTRY - REGION VII</h3>
            <h3 align="center">MONITORING OF EMPLOYMENT AND INVESTMENTS</h3>
            <br></br>
            <br></br>
        <table border="1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 5%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                    No.
                <br>
            </th>
            <th rowspan="2" style="width: 16%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                Name of Business
                <br>
            </th>
            <th rowspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Address of Business
                <br>
            </th>
            <th rowspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Name of Owner
                <br>
            </th> 
            <th colspan="2" style="width: 5%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Gender
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Type of Business
                <br>
            </th> 
            <th colspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                Kind of Business
                <br>
            </th> 
            <th colspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    No. of Months/Years in Business
                <br>
            </th> 
            <th colspan="2" style="width: 9%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Capitalization
                <br>
            </th> 
            <th colspan="2" style="width: 6%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    No. of Employees
                <br>
            </th> 
        </tr>
    </thead>
                    <tbody >';
                    $cntno = 0;
            foreach ($data as $row) {
                $cntno = $cntno + 1;
                $html_content .= '
                <tr style="font-family: Arial, font-size: 8pt" align="center">
                <td width="5%">' . $cntno. '</td>
                <td width="16%" align="left"> ' . $row['Business Name'] . '</td>
                <td width="12%" align="left"> ' . $row['Address'] . '</td>
                <td width="12%" align="left"> ' . $row['Tax Payers Name'] . '</td>
                <td width="5%">' . $row['Gender'] . '</td>
                <td width="10%">' . $row['Type of Business'] . '</td>
                <td width="12%" align="left"> ' . $row['Kind of Business'] . '</td>
                <td width="12%">' . $row['No of years/months'] . '</td>
                <td width="9%">' . $row['Capitalization'] . '</td>
                <td width="6%">' . $row['total_employee'] . '</td>
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
    public function businessBMBE(Request $request)
    {
    
        $barangay = 'All';
        $businessType = 'All';
        $businessStatus = 'All';
        if ($request->barangay == 'All') {
            $barangay = 'All';
        } else {
            $barangay = $request->barangay;
        }
        if ($request->businessType == 'All') {
            $businessType = 'All';
        } else {
            $businessType = $request->businessType;
        }
        if ($request->businessStatus == 'All') {
            $businessStatus = 'All';
        } else {
            $businessStatus = $request->businessStatus;
        }
        $list = DB::select('call '.$this->lgu_db.'.spl_ebplo_display_business_bmbe_report_jho1(?,?,?,?,?)',array($request->from,$request->to,$barangay,$businessType,$businessStatus));
        return response()->json(new JsonResponse($list));
    }
    public function businessBMBEPrint(Request $request){
        $data = $request->main;

        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">BMBE Report</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "12%">Business Name</th>
        <th width = "9%">Name of Owner</th>
        <th width = "11%">Business Address</th>
        <th width = "8%">Contact No.</th>
        <th width = "11%">Business Type</th>
        <th width = "11%">Kind of Business</th>
        <th width = "9%">Date Registered</th>
        <th width = "9%">Capital / Gross</th>
        <th width = "9%">No. of Employees</th>
        <th width = "9%">Business Status</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "12%">'.$main['Business Name'].'</td>
            <td width = "9%">'.$main['Tax Payers Name'].'</td>
            <td width = "11%">'.$main['Address'].'</td>
            <td width = "8%">'.$main['Contact No'].'</td>
            <td width = "11%">'.$main['Type of Ownership'].'</td>
            <td width = "11%">'.$main['Nature of Business'].'</td>
            <td width = "9%">'.$main['Date of Application'].'</td>
            <td width = "9%">'.$main['Capital/Gross'].'</td>
            <td width = "9%">'.$main['No of Employee'].'</td>
            <td width = "9%">'.$main['Permit Status'].'</td>
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
    public function businessBSP(Request $request)
    {
        $date = $request->from;
        //$date = date("Y", strtotime($date));
        $barangay = 'All';
        $businessType = 'All';
        $officeType = 'All';
        $BSPType = 'All';
        if ($request->barangay == 'All') {
            $barangay = 'All';
        } else {
            $barangay = $request->barangay;
        }
        if ($request->businessType == 'All') {
            $businessType = 'All';
        } else {
            $businessType = $request->businessType;
        }
        if ($request->officeType == 'All') {
            $officeType = 'All';
        } else {
            $officeType = $request->officeType;
        }
        if ($request->BSPType == 'All') {
            $BSPType = 'All';
        } else {
            $BSPType = $request->BSPType;
        }
        $list = DB::select('call '.$this->lgu_db.'.spl_ebplo_display_business_bsp_report_jho1(?,?,?,?,?,?)',array($request->from,$request->to,$barangay,$businessType,$officeType,$BSPType));
        return response()->json(new JsonResponse($list));   
    }
    public function businessBSPReport(Request $request) {
        $data = $request->main;

        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">BSP Report</2>
        <br></br>
        <br></br>
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
    <body p class="font-weight-light">
    <table border="1">
        <thead>            
            <tr>
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>Business Reg. No.<br></th>
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>Business Name<br></th>            
                <th rowspan="2" style="width:10%" class="caption-label-center"><br><br>Name of Owner<br></th>
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>Business Address<br></th>
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>Contact No.<br></th>
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>Business Type<br></th>
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>Branch Name<br></th>
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>TIN<br></th>
                <th colspan="2" style="width:18%" class="caption-label-center">Business Permit</th> 
                <th rowspan="2" style="width:9%" class="caption-label-center"><br><br>Remarks<br></th>
            </tr>
            <tr>
                <th style="width:9%">Date of Issuance</th>
                <th style="width:9%">Permit No.</th>
            </tr>     
         </thead>

        <tbody>';
        foreach($data as $row){
            $main =($row);          
            $html_content .='
            <tr>
                <td align="center" style="width:9%">'.$main['BSP_no'].'</td>
                <td align="center" style="width:9%">'.$main['Business Name'].'</td>
                <td align="center" style="width:10%">'.$main['Tax Payers Name'].'</td>
                <td align="center" style="width:9%">'.$main['brgy'].'</td>
                <td align="center" style="width:9%">'.$main['Contact No'].'</td>
                <td align="center" style="width:9%">'.$main['code'].'</td>
                <td align="center" style="width:9%">'.$main['Branch Name'].'</td>
                <td align="center" style="width:9%">'.$main['TIN'].'</td>
                <td align="center" style="width:9%">'.$main['Date of Issuance'].'</td>
                <td align="center" style="width:9%">'.$main['Permit No'].'</td>
                <td align="center" style="width:9%">'.$main['Remarks'].'</td>
            </tr>
            ';
        }
        $html_content .='</tbody></table>';
       
        PDF::SetTitle('Sample');
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
         }catch (\Exception $e) {
             return response()->json(new JsonResponse(['status'=>'error']));
         }
    }
    public function businessBSPPrint(Request $request){
        $data = $request->main;

        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">BSP Report</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
            <th width = "5%">BSP Reg. No.</th>
            <th width = "12%">Business Name</th>
            <th width = "11%">Name of Owner</th>
            <th width = "9%">Business Address</th>
            <th width = "8%">Contact No.</th>
            <th width = "9%">Business Type</th>
            <th width = "11%">Branch Name</th>
            <th width = "9%">Date Released</th>
            <th width = "7%">Office Type</th>
            <th width = "5%">TIN</th>
            <th width = "5%">Permit No.</th>
            <th width = "9%">Remarks</th>
        </tr>

        <tbody>';
        foreach($data as $row){
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "5%">'.$main['BSP_no'].'</td>
            <td width = "12%">'.$main['Business Name'].'</td>
            <td width = "11%">'.$main['Tax Payers Name'].'</td>
            <td width = "9%">'.$main['brgy'].'</td>
            <td width = "8%">'.$main['Contact No'].'</td>
            <td width = "9%">'.$main['code'].'</td>
            <td width = "11%">'.$main['Branch Name'].'</td>
            <td width = "9%">'.$main['Date of Issuance'].'</td>
            <td width = "7%">'.$main['Typeoffice'].'</td>
            <td width = "5%">'.$main['TIN'].'</td>
            <td width = "5%">'.$main['Permit No'].'</td>
            <td width = "9%">'.$main['Remarks'].'</td>
            </tr>';
        }
        $html_content .='</tbody></table>';
       
        PDF::SetTitle('Sample');
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
         }catch (\Exception $e) {
             return response()->json(new JsonResponse(['status'=>'error']));
         }
    }
 // end of controller   
}
