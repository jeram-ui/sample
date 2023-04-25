<?php

namespace App\Http\Controllers\Api\Treasury;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class TaxOnBusiness extends Controller
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
    // start of controller 
    public function getbusinessTaxMasterList(Request $request)
    {
      $filter = $request->main;  
      $_year = $filter['from'];
      $_qrtr = $filter['quarter'];
      $_brgy = strval($filter['barangays']);
      $_include = $filter['bstatus'];
      $list = DB::select('call ' . $this->lgu_db . '.spl_cto_display_business_list_joy(?,?,?,?)', array($_year, $_qrtr, $_brgy, $_include));
      return response()->json(new JsonResponse($list));
    }
    // Print Business List
  public function businessTaxMasterListPrint(Request $request)
  {
    
    $logo = config('variable.logo');
    try {
      $main=$request->main;
      // dd($main);
      // dd($main->{'Permit Number'});
    
        PDF::SetFont('Helvetica', '', '8');
        $html_content = '
              ' . $logo . ' 
              <h3 align="center">BUSINESS TAX MASTER LIST</h3>
              <br></br>
              <br></br>
              <br></br>
              <br></br> 
              <table  border="1" style="padding:2px;">
              <thead>
              <tr>
              <th style="width:3%;text-align:center;background-color:#dedcdc;"><br><br><b>NO</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>PERMIT NUMBER</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>APPLICATION NO</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>DATE OF APPLICATION</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>BUSINESS NAME</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>TAX PAYERS NAME</b><br></th>
              <th style="width:8%;text-align:center;background-color:#dedcdc;"><br><br><b>BUSINESS ADDRESS</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>TIN</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>NO. OF EMPLOYEES</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>APPLICATION STATUS</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>CAPITAL</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>GROSS SALES</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>BARANGAY</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>DATE OF ISSUANCE</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>ASSESSMENT AMOUNT</b><br></th>
              <th style="text-align:center;background-color:#dedcdc;"><br><br><b>PAYMENT STATUS</b><br></th>
              <th style="width:7%;text-align:center;background-color:#dedcdc;"><br><br><b>BUSINESS STATUS</b><br></th>
              </tr>
              </thead>
              <tbody >'; 
              $ctr = 1; 
              foreach($main as $row){                              
               $html_content .='
                  <tr >
                  <td style="width:3%;text-align:center;">' .$ctr. '</td>
                  <td style="text-align:center;">' . $row['Permit No'] . '</td>
                  <td style="text-align:center;">' . $row['Application No'] . '</td>
                  <td style="text-align:center;">' . $row['Date of Application'] . '</td>
                  <td style="text-align:left;">' . $row['Business Name'] . '</td>
                  <td style="text-align:left;">' . $row['Tax Payers Name'] . '</td>    
                  <td style="width:8%;text-align:left;">' . $row['Business Address'] . '</td>
                  <td style="text-align:center;">' . $row['TIN'] . '</td>
                  <td style="text-align:center;">' . $row['No of Employee'] . '</td>
                  <td style="text-align:center;">' . $row['Application Status'] . '</td>
                  <td style="text-align:right;">' . $row['Capital'] . '</td>
                  <td style="text-align:right;">' . $row['Gross Sales'] . '</td>
                  <td style="text-align:center;">' . $row['Barangay'] . '</td>
                  <td style="text-align:center;">' . $row['Date of Issuance'] . '</td>    
                  <td style="text-align:right;">' . $row['Assessment'] . '</td>
                  <td style="text-align:center;">' . $row['Payment Status'] . '</td>
                  <td style="width:7%;text-align:center;">' . $row['business_status'] . '</td>                  
                  </tr>';
                  $ctr++;
              }
              $ctr = $ctr - 1;
              $html_content .='<tr>
              <th colspan="2" style="text-align:right;height:20px;padding-top: 20px;"><b>TOTAL RECORDS</b></th>  
              <th colspan="17"style="text-align:left;height:20px;padding-top: 20px;"><b>'.$ctr.'</b></th>  
              </tr>';
              $html_content .='</tbody>
              </table>
              ';
      PDF::SetTitle('Business Tax Master List');
      PDF::AddPage('L',array(300,350));
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path() . '/printLists.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }
  // Print Business Tax Payment Certificate
  public function businessTaxCertificatePrint(Request $request)
  {
    $logo = config('variable.logo');
    try {
      PDF::SetFont('Helvetica', '', '10');
      $html_content = '
      <table style="width:100%;padding:3px;">
      <br>
      <br>
      '.$logo.'
      <tr>
      <br>
      <br>
      <br>
      <br>
      <th style="width:100%"><h3 align="center">BUSINESS TAX PAYMENT CERTIFICATE</h3></th>  
      </tr>
      <br>
      <br>
      <tr>
        <th style="width:60%"></th> 
        <th style="width:10%;"><b>YEAR</b></th> 
        <th style="width:30%;"><b>'.$request['YR'].'</b></th> 
      </tr>
      <tr>
        <th style="width:60%;"></th> 
        <th style="border-top:1px solid black;border-left:1px solid black;width:10%;"><b>BIN NO:</b></th>
        <th style="border-top:1px solid black;border-right:1px solid black;width:30%;">'.$request['Permit No'].'<b></b></th>
       </tr>   
       <tr>
        <th style="border-top:1px solid black;border-left:1px solid black;width:30%;"><b>BUSINESS NAME</b></th>
        <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
        <th style="border-top:1px solid black;border-right:1px solid black;width:67%;">'.$request['Business Name'].'</th> 
       </tr>       
       <tr>
        <th style="border-left:1px solid black;border-top:1px solid black;width:30%;"><b>SIGNAGE/TRADE NAME</b></th>
        <th style="text-align:center;border-top:1px solid black;width:3%;"><b>:</b></th>     
        <th style="border-top:1px solid black;border-right:1px solid black;width:67%;">'.$request['trade_name'].'</th> 
       </tr>        
       <tr>
        <th style="border-left:1px solid black;border-top:1px solid black;width:30%;"><b>PERMITEE NAME</b></th>
        <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
        <th style="border-top:1px solid black;border-right:1px solid black;width:67%;">'.$request['Tax Payers Name'].'</th>    
       </tr>
       <tr>
        <th style="border-left:1px solid black;border-top:1px solid black;width:30%;"><b>BUSINESS ADDRESS</b></th>
        <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
        <th style="border-top:1px solid black;border-right:1px solid black;width:67%;">'.$request['Business Address'].'</th> 
       </tr>
       <tr>
        <th style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;width:30%;"><b>FORM OF OWNERSHIP</b></th> 
        <th style="border-top:1px solid black;border-bottom:1px solid black;text-align:center;width:3%;"><b>:</b></th>
        <th style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;width:67%;">'.$request['Type Of Ownership'].'</th> 
       </tr>
         <br>
         <br>
         <br>
         <br>
        <tr>
          <th style="border-left:1px solid black;border-top:1px solid black;width:35%;"><b>NATURE OF BUSINESS</b></th>
          <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;width:62%;">'.$request['Nature of Business'].'</th>  
        </tr>
        <tr>
          <th style="border-left:1px solid black;border-top:1px solid black;width:35%;"><b>GROSS SALES/CAPITALIZATION</b></th>
          <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;width:20%;">'.$request['Gross Sales'].'</th> 
          <th style="border-left:1px solid black;border-top:1px solid black;width:20%;"><b>AMOUNT PAID</b></th>
          <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;width:19%;">'.$request['Total Amount'].'</th>   
        </tr>
        <tr>
          <th style="border-left:1px solid black;border-top:1px solid black;width:35%;"><b>OFFICIAL RECEIPT NO.</b></th> 
          <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;width:20%;">'.$request['OR No'].'</th> 
          <th style="border-left:1px solid black;border-top:1px solid black;width:20%;"><b>DATE PAID</b></th>
          <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;width:19%;">'.$request['or_date'].'</th>   
        </tr>
        <tr>
          <th style="border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;width:35%;"><b>BUSINESS PLATE NO.</b></th> 
          <th style="border-top:1px solid black;border-bottom:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;width:20%;">'.$request['business_number'].'</th> 
          <th style="border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;width:20%;"><b>MODE OF PAYMENT</b></th>
          <th style="border-top:1px solid black;border-bottom:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;width:19%;">'.$request['mode_of_payment'].'</th> 
        </tr>
        <br>
        <br>
        <br>
        <br>
        <tr>
          <th style="border-left:1px solid black;border-top:1px solid black;width:32%;"><b>Previous O.R. No.</b></th> 
          <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;width:18%;">'.$request['prevOR'].'</th> 
        </tr>
        <tr>
          <th style="border-left:1px solid black;border-top:1px solid black;width:32%;"><b>Date Paid</b></th>
          <th style="border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;width:18%;">'.$request['prevORDAte'].'</th>
          <th colspan="3" style"width:47%;"><i>&nbsp;&nbsp;&nbsp;&nbsp;"QUALITY SERVICE IS OUR BUSINESS"</i></th>
        </tr>
        <tr>
          <th style="border-bottom:1px solid black;border-left:1px solid black;border-top:1px solid black;width:32%;"><b>Previous Year Business Plate No</b></th>
          <th style="border-bottom:1px solid black;border-top:1px solid black;text-align:center;width:3%;"><b>:</b></th>
          <th style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;width:18%;">'.$request['prevORDAte'].'</th> 
        </tr>
        <tr>
          <th colspan="6"><b>All requirements for renewal of business permit shall be complied</b></th>
        </tr>
        <tr>
          <th colspan="6"><b>within 90 days period but not exceed December 31, 2020</b></th>
        </tr>
        <br>
        <br>
        <br>
        <tr>
            <th style="width:75%"></th> 
            <th style="width:25%"><b>LOUELA S. MAYBITUIN</b></th>  
          </tr>
          <tr>
            <th style="width:80%"></th> 
            <th style="width:20%"><i>City Treasurer</i></th>  
          </tr>
          <br>
          <br>
          <tr>
            <th style="width:80%"></th> 
            <th style="width:20%"></th>  
          </tr>
      </table>';


      PDF::SetTitle('Business Tax Payment Certificate');
      PDF::AddPage();
      PDF::SetLineStyle( array( 'width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
      PDF::Line(5,5,PDF::getPageWidth()-5,5); 
      PDF::Line(PDF::getPageWidth()-5,4.5,PDF::getPageWidth()-5,PDF::getPageHeight()-5);
      PDF::Line(5,PDF::getPageHeight()-5,PDF::getPageWidth()-5,PDF::getPageHeight()-5);
      PDF::Line(5,4.5,5,PDF::getPageHeight()-5);
      PDF::SetLineStyle( array( 'width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
      PDF::Line(8,8,PDF::getPageWidth()-8,8); 
      PDF::Line(PDF::getPageWidth()-8,7.4,PDF::getPageWidth()-8,PDF::getPageHeight()-8);
      PDF::Line(8,PDF::getPageHeight()-8,PDF::getPageWidth()-8,PDF::getPageHeight()-8);
      PDF::Line(8,7.4,8,PDF::getPageHeight()-8);
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path() . '/print.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }
  // Print Business Closure Certificate
  public function businessTaxOnClosurePrint(Request $request)
  {
    // dd($request);
    $logo = config('variable.logo');
    try {
      PDF::SetFont('Helvetica', '', '12');
      $html_content = '
        ' . $logo.'
        <table style="width:100%;padding:3px;">
          <tr>
            <th style="width:100%"><h3 align="center">OFFICE OF THE CITY TREASURER</h3></th>  
          </tr> 
          <br><br><br>
          <tr>
            <th style="width:100%"><h1 align="center">C E R T I F I C A T I O N </h1></th> 
          </tr> 
        </table>
        <br><br>
        <table style="width:100%;padding:3px;">
          <tr>
            <th style="width:3%"></th>   
            <th style="width:93%;"><span style="text-align:justify;line-height:20px;">This is to certify that <b>'.$request['Tax Payers Name'].'</b> the proprietor of <b>'.$request['Business Name'].',</b> 
              located at <b>'.$request['Business Address'].'</b> had closed/stopped his business operation effective <b>'.date("F j,Y", strtotime($request['Date Terminated'])).'</b>.
              <br><br>This certification is issued upon the request of <b>'.$request['Tax Payers Name'].'</b> for whatever legal purposes this may server best. 
              <br><br>Issued this <b>'.date("jS \of F, Y ", strtotime($request['Date of Issuance'])).'</b> at City of Iligan, Lanao Del Norte, Philippines.
              <br><br>
              <br><br><b>Louela S. Maybituin</b>
              <br>City Treasurer  
              </span>
            </th>  
            <th style="width:3%"></th>  
          </tr> 
        </table>
        <br><br><br><br>
        <table style="width:100%;padding:3px;">
        <tr>
        <th style="width:3%"></th>
            <th style="width:20%">Amount Paid</th>
            <th style="width:2%">:</th> 
            <th style="border-bottom:1px solid black;width:20%">'.$request['termAmt'].'</th>
        </tr>
        <tr>
            <th style="width:3%"></th>
            <th style="width:20%">OR No.</th>
            <th style="width:2%">:</th> 
            <th style="border-bottom:1px solid black;width:20%">'.$request['termOR_'].'</th>
        </tr>
        <tr>
            <th style="width:3%"></th>
            <th style="width:20%">OR Date</th>
            <th style="width:2%">:</th> 
            <th style="border-bottom:1px solid black;width:20%">'.$request['termORDAte_'].'</th>
        </tr>
        </table>
        ';

      PDF::SetTitle('Closure Certification'); 
      PDF::AddPage();  
      PDF::SetLineStyle( array( 'width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
      PDF::Line(20,45,PDF::getPageWidth()-20,45); 
      PDF::SetLineStyle( array( 'width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
      PDF::Line(20,43.5,PDF::getPageWidth()-20,43.5); 
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path() . '/print.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }
   // ***BUSINESS TAX DELINQUENCY***
   public function businessTaxDeliquencyList(Request $request)
   {
     $_letter = $request->abc;
     $_year = $request->from;
     $_qrtr = $request->quarter;
     $_brgy = $request->barangays;
     $_type = $request->classification;
     $_Street = $request->street;
     $_notice = $request->noticedate;
     $list = DB::select('call ' . $this->lgu_db . '.jay_cto_display_business_deliquent(?,?,?,?,?,?,?)', array($_letter . '%', $_year, $_qrtr, $_brgy, $_type, $_Street, $_notice));
     return response()->json(new JsonResponse($list));
   }
   // Print Display List 
  public function businessTaxDeliquencyListPrint(Request $request)
  {
    
    $logo = config('variable.logo');
    try {
      $main=$request->main;
      // dd($main);
      // dd($main->{'Permit Number'});
    
        PDF::SetFont('Helvetica', '', '9');
        $html_content = '
              ' . $logo . ' 
              <h3 align="center">LIST OF BUSINESS TAX DELINQUENCY</h3>
              <br></br>
              <br></br>
              <br></br>
              <br></br> 
              <table  border="1" width:100%; style="padding:2px;">
              <thead>
              <tr>
              <th style="text-align:center;width:3%;background-color:#dedcdc;"><br><br><b>NO</b><br></th>
              <th style="text-align:center;width:10%;background-color:#dedcdc;"><br><br><b>PERMIT NUMBER</b><br></th>
              <th style="text-align:center;width:12%;background-color:#dedcdc;"><br><br><b>ACCOUNT NO</b><br></th>
              <th style="text-align:center;width:15%;background-color:#dedcdc;"><br><br><b>COMPANY NAME</b><br></th>
              <th style="text-align:center;width:12%;background-color:#dedcdc;"><br><br><b>TRADE NAME</b><br></th>
              <th style="text-align:center;width:10%;background-color:#dedcdc;"><br><br><b>OWNER NAME</b><br></th>
              <th style="text-align:center;width:18%;background-color:#dedcdc;"><br><br><b>BUSINESS ADDRESS</b><br></th>
              <th style="text-align:center;width:10%;background-color:#dedcdc;"><br><br><b>DELINQUENCY</b><br></th>
              <th style="text-align:center;width:10%;background-color:#dedcdc;"><br><br><b>TOTAL DELINQUENCY</b><br></th>
              </tr>
              </thead>
              <tbody >'; 
              $ctr = 1; 
              foreach($main as $row){                              
               $html_content .='
                  <tr >
                  <td style="text-align:center;width:3%;">' .$ctr. '</td>
                  <td style="text-align:center;width:10%;">' . $row['Permit Number'] . '</td>
                  <td style="text-align:center;width:12%;">' . $row['Account No'] . '</td>
                  <td style="text-align:left;width:15%;">' . $row['Company Name'] . '</td>
                  <td style="text-align:left;width:12%;">' . $row['Trade Name'] . '</td>
                  <td style="text-align:left;width:10%;">' . $row['Owner Name'] . '</td>    
                  <td style="text-align:left;width:18%;">' . $row['Address'] . '</td>
                  <td style="text-align:left;width:10%;">' . $row['Delinquency'] . '</td>
                  <td style="text-align:right;width:10%;">' . $row['Total Delinquency'] . '</td>                    
                  </tr>';
                  $ctr++;
              }
              $ctr = $ctr - 1;
              $html_content .='<tr>
              <th colspan="2" style="text-align:right;height:20px;padding-top: 20px;"><b>TOTAL RECORDS</b></th>  
              <th colspan="7"style="text-align:left;height:20px;padding-top: 20px;"><b>'.$ctr.'</b></th>  
              </tr>';
              $html_content .='</tbody>
              </table>
              ';
      PDF::SetTitle('List of Business Tax Delinquency');
      PDF::AddPage('L');
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path() . '/prints.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }
  // Print Individual Notice***
  public function businessTaxNoticeOfDelinquency(Request $request)
  {
    $logo = config('variable.logo');
    try {
      PDF::SetFont('Helvetica', '', '9');
      $html_content = '
        ' . $logo . ' 
        <table style="width:100%;padding:3px;">
        <tr>
        <th style="width:100%"><h3 align="center">Notice of Delinquency</h3></th>  
        </tr>
        <br>
        <br>
        <br>
        <tr>
          <th style="width:80%"></th> 
          <th style="border-bottom: 1px solid black;width:15%;">' . $request['Date Processed'] . '</th> 
        </tr>
        <tr>
          <th style="width:5%">To</th> 
          <th style="width:2%">:</th>
          <th style="border-bottom: 1px solid black;width:34%;">' . $request['Owner Name'] . '</th>  
        </tr>
        <tr>
          <th style="width:7%"></th>  
          <th style="border-bottom: 1px solid black;width:34%;">' . $request['Company Name'] . '</th>  
        </tr>
        <tr>
          <th style="width:7%"></th>  
          <th style="border-bottom: 1px solid black;width:34%;">' . $request['Address'] . '</th>  
          </tr>
      </table>  
      <br> <br>  <br> 
      <table style="width:100%;padding:3px;">
        <tr>
          <th style="width:100%">SIR/MADAM:</th>  
        </tr> 
        <tr>
          <th style="width:5%;"></th> 
          <th style="width:40%;">Please be reminded of your business tax</th>  
          <th style="border-bottom: 1px solid black;width:30%;">' . $request['Delinquency'] . '</th>  
          <th style="width:25%;">in the amount of</th>   
        </tr>
        <tr>  
          <th style="text-align:center;border-bottom: 1px solid black;width:75%;">' . $this->G->numberTowords(str_replace(',','',$request['Total Delinquency'])) . '</th>   
          <th style="width:4%;">(P</th> 
          <th style="text-align:center;border-bottom: 1px solid black;width:20%;">'.$request['Total Delinquency'].'</th>     
          <th style="width:4%;">)</th>  
        </tr>
        <tr>
          <th style="width:5%;"></th> 
          <th style="width:60%;">You are hereby requested to settle your obligation on or before</th>  
          <th style="border-bottom: 1px solid black;width:27%;">' . $request['Notice'] . '</th>  
          <th style="width:5%;">, to</th>    
          </tr>
        <tr>
          <th style="width:100%">avoid further penalties and surcharges.</th>  
        </tr>
      </table> 
      <br>
      <br>
      <br>
      <br>
      <br>
      <br>
      <br>
      <br>
      <table style="width:100%;padding:3px;">
      <tr>
        <th style="width:70%"></th> 
        <th style="width:25%;">Very truly yours,</th> 
      </tr>
      <br>
      <br> 
      <tr>
        <th style="width:73%"></th> 
        <th style="width:25%"><b>Louela Maybituin</b></th>  
      </tr>
      <tr>
        <th style="width:73%"></th> 
        <th style="width:25%">City Treasurer</th>  
      </tr>
      </table>
      <br>
      <br> 
      <br>
      <br> 
      <table style="width:100%;padding:3px;">
        <tr> 
          <th style="width:12%;"><b>Received :</b></th> 
          <th style="border-bottom: 1px solid black;width:27%;"></th>
        </tr> 
        <tr>
          <th style="width:12%;"><b>Date :</b></th>  
          <th style="border-bottom: 1px solid black;width:27%;"></th>
        </tr> 
      </table>';

      PDF::SetTitle('Notice of Delinquency');
      PDF::AddPage();
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path() . '/prints.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }
  // Print All Notice
  public function businessTaxNoticeOfDelinquencyAll(Request $request)
  {
    $logo = config('variable.logo');
    try {
      PDF::SetTitle('Notice of Delinquency');
      $jsonData = $request->request->all(); 
        foreach($jsonData as $row){  
          PDF::AddPage();
        PDF::SetFont('Helvetica', '', '9');
        $html_content = '
              ' . $logo . '  
              <table style="width:100%;padding:3px;">
              <tr>
                <th style="width:100%"><h3 align="center">Notice of Delinquency</h3></th>  
              </tr>
              <br>
              <br>
              <br>
              <tr>
                <th style="width:80%"></th> 
                <th style="border-bottom: 1px solid black;width:15%;">' . $row['Date Processed'] . '</th> 
              </tr>
            <tr>
              <th style="width:5%">To</th> 
              <th style="width:2%">:</th>
              <th style="border-bottom: 1px solid black;width:34%;">' . $row['Owner Name'] . '</th>  
            </tr>
            <tr>
              <th style="width:7%"></th>  
              <th style="border-bottom: 1px solid black;width:34%;">' . $row['Company Name'] . '</th>  
            </tr>
            <tr>
              <th style="width:7%"></th>  
              <th style="border-bottom: 1px solid black;width:34%;">' . $row['Address'] . '</th>  
            </tr>
        </table>  
        <br> <br>  <br> 
        <table style="width:100%;padding:3px;">
            <tr>
              <th style="width:100%">SIR/MADAM:</th>  
            </tr> 
            <tr>
              <th style="width:5%;"></th> 
              <th style="width:40%;">Please be reminded of your business tax</th>  
              <th style="border-bottom: 1px solid black;width:30%;">' . $row['Delinquency'] . '</th>  
              <th style="width:25%;">in the amount of</th>   
            </tr>
            <tr>  
              <th style="text-align:center;border-bottom: 1px solid black;width:75%;">' . $this->G->numberTowords(str_replace(',','',$row['Total Delinquency'])) . '</th>   
              <th style="width:4%;">(P</th> 
              <th style="text-align:center;border-bottom: 1px solid black;width:20%;">'.$row['Total Delinquency'].'</th>     
              <th style="width:4%;">)</th>  
            </tr>
            <tr>
              <th style="width:5%;"></th> 
              <th style="width:60%;">You are hereby requested to settle your obligation on or before</th>  
              <th style="border-bottom: 1px solid black;width:27%;">' . $row['Notice'] . '</th>  
              <th style="width:5%;">, to</th>    
            </tr>
            <tr>
              <th style="width:100%">avoid further penalties and surcharges.</th>  
            </tr>
        </table> 
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <table style="width:100%;padding:3px;">
            <tr>
              <th style="width:70%"></th> 
              <th style="width:25%;">Very truly yours,</th> 
            </tr>
        <br>
        <br> 
            <tr>
              <th style="width:73%"></th> 
              <th style="width:25%"><b>Louela Maybituin</b></th>  
            </tr>
            <tr>
              <th style="width:73%"></th> 
              <th style="width:25%">City Treasurer</th>  
            </tr>
        </table>
        <br>
        <br> 
        <br>
        <br> 
        <table style="width:100%;padding:3px;">
            <tr> 
              <th style="width:12%;"><b>Received :</b></th> 
              <th style="border-bottom: 1px solid black;width:27%;"></th>
            </tr> 
            <tr>
              <th style="width:12%;"><b>Date :</b></th>  
              <th style="border-bottom: 1px solid black;width:27%;"></th>
            </tr> 
        </table>';
   
        PDF::writeHTML($html_content, true, true, true, true, '');
      }
 
      PDF::Output(public_path() . '/prints.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }
  // print notice per brgy
  public function businessTaxNoticeOfDelinquencyBrgy(Request $request)
  {
    
    $logo = config('variable.logo');
    try {
  
      $mainB=$request->main;
      // dd($main);
      // dd($main->{'Permit Number'});
    
        PDF::SetFont('Helvetica', '', '8');
        $html_content = '
              ' . $logo . ' 
              <h3 align="center">LIST OF BUSINESS TAX DELINQUENCY - PER BARANGAY</h3>
              <br></br><br></br><br></br>
              <style>
              <table border="1" width:100%; style="padding:2px;">
                <tr border="1">
                  <th style="text-align:center;width:3%;background-color:#dedcdc;"><b>NO</b></th>    
                  <th style="text-align:center;width:8%;background-color:#dedcdc;"><b>DATE PROCESSED</b></th>
                  <th style="text-align:center;width:15%;background-color:#dedcdc;"><b>COMPANY NAME</b></th>
                  <th style="text-align:center;width:15%;background-color:#dedcdc;"><b>OWNER NAME</b></th>
                  <th style="text-align:center;width:20%;background-color:#dedcdc;"><b>ADDRESS</b></th>
                  <th style="text-align:center;width:8%;background-color:#dedcdc;"><b>NOTICE DATE</b></th>
                  <th style="text-align:center;width:14%;background-color:#dedcdc;"><b>QTR</b></th>
                  <th style="text-align:center;width:7%;background-color:#dedcdc;"><b>TAX YEAR</b></th>
                  <th style="text-align:center;width:10%;background-color:#dedcdc;"><b>TOTAL DELINQUENCY</b></th>
                </tr>'; 
                $arr = array();
                foreach ($mainB as $key => $item) {
                  $arr[$item['brgy_name']][$key] = $item;
                }
                for($i = 0; $i < count($arr); $i++){
                  $html_content .='<tr><th style="height:17px;background-color:#dedcdc;" colspan="9"><b>'.strtoupper(key($arr)).'</br></th></tr>'; 
                  $result = $arr[key($arr)]; 
                  $ctr = 1; $totalamnt = 0;
                  foreach($result as $row){    
                    PDF::SetFont('Helvetica', '', '9');                             
                     $html_content .='
                     <tr style="vertical-align:middle;">
                     <td align="center">' . $ctr . '</td>
                     <td align="center">' . $row['Date Processed'] . '</td>
                     <td align="left">' . $row['Company Name'] . '</td>       
                     <td align="left" >' . $row['Owner Name'] . '</td>
                     <td align="left">' . $row['Address'] . '</td>
                     <td align="center">' . $row['Notice'] . '</td>
                     <td align="center">' . $row['Qtr'] . '</td>    
                     <td align="center">' . $row['Tax Year'] . '</td>
                     <td align="right">' . $row['Total Delinquency'] . '</td>             
                     </tr>';
                     $totalamnt+= floatval(str_replace(',','',$row['Total Delinquency']));
                     $ctr++;
                    }
                    $html_content .='<tr>
                    <th colspan="8" style="text-align:right;"><b>TOTAL</b></th>  
                    <th style="text-align:right;height:15px;vertical-align:middle;"><b>'.number_format($totalamnt,2).'</b></th>  
                    </tr>';
                  next($arr);
                }  
                
            $html_content .='</table>';
            PDF::SetTitle('List of Business Tax Delinquency- Per Barangay');
            PDF::AddPage('L',array(250,350));
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
          return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
          return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }


  public function getbusinessTaxLedgerList(Request $request)
    {
        $date = $request->from;
        $date = date("Y", strtotime($date));
        $list = DB::select('call '.$this->lgu_db.'.spl_display_all_subsidiaryLedger_joy(?)',array($date));
        return response()->json(new JsonResponse($list));
    }
    
    public function getbusinessTaxLedgerHistory($id)
    {
        $list = DB::select('call '.$this->lgu_db.'.spl_display_business_transaction_history_joy(?)',array($id));
        return response()->json(new JsonResponse($list));
    }
    public function businessTaxLedgerListPrint(Request $request)
    {
        $data = $request->main;
        
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h2 align="center">Business Tax Ledger Master List</h2>
            <br></br>
            <br></br>
        <table border="1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 20%;text-align:center;vertical-align:middle;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                Business Name
                <br>
            </th>
            <th rowspan="2" style="width: 15%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Tax Payers Name
                <br>
            </th>
            <th rowspan="2" style="width: 13%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Business Type
                <br>
            </th> 
            <th rowspan="2" style="width: 7%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Tax Year
                <br>
            </th> 
            <th colspan="2" style="width: 25%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Business Address
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                OR Date
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    OR. No.
                <br>
            </th> 
        </tr>
    </thead>
                    <tbody >';
                    
            foreach ($data as $row) {
                $html_content .= '
                <tr style="font-family: Arial, font-size: 7pt" align="center">
                <td width="20%" align="left" indent="5%">' . $row['Business Name'] . '</td>
                <td width="15%" align="left">' . $row['Tax Payers Name'] . '</td>
                <td width="13%">' . $row['organization_type'] . '</td>
                <td width="7%">' . $row['Taxyear'] . '</td>
                <td width="25%" align="left">' . $row['address'] . '</td>
                <td width="10%">' . $row['OR Date'] . '</td>
                <td width="10%">' . $row['OR No'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
            </table>';
            PDF::SetTitle('Master List');
            PDF::Addpage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function businessTaxSubsidiaryLedgerPrint($id){
      $maindata = DB::select('Call ' . $this->lgu_db . '.spl_display_subsidiaryLedger_laravel(?)', array($id));
      $data = DB::select('call '.$this->lgu_db.'.spl_display_subsidiaryLedger_joy('.$id.')');

      foreach($maindata as $main){
         
         $acctno=$main->{'busAccntNo'};
         $bussname=$main->{'Business Name'};
         $address=$main->{'Address'};
         $permitno=$main->{'PermitNo'};
         $tin=$main->{'TIN'};
         $taxpayer=$main->{'Tax Payers Name'};
         $bustype=$main->{'organization_type'};
         $busclass=$main->{'kind'};
      }
      
      $logo = config('variable.logo');
      try {
      $html_content = '
      '.$logo.'
      <h2 align="center">Business Tax Subsidiary Ledger</2>
      <br></br>
      <br></br>
      <table border="1" cellpadding="2">
          <tr align="left">
              <th width = "18%">Account No.</th>
              <th width = "22%">: '. $acctno.'</th>
          </tr>
          <tr align="left">
              <th width = "18%">Business Name</th>
              <th width = "20%">: '.$bussname.'</th>
          </tr>
          <tr align="left">
              <th width = "18%">Business Address</th>
              <th width = "20%">: '.$address.'</th>
          </tr>
          <tr align="left">
              <th width = "18%">Permit No.</th>
              <th width = "20%">: '.$permitno.'</th>
          </tr>
          <tr align="left">
              <th width = "18%">TIN No.</th>
              <th width = "20%">: '.$tin.'</th>
          </tr>
          <tr align="left">
              <th width = "18%">Name of Taxpayer</th>
              <th width = "20%">: '.$taxpayer.'</th>
          </tr>
          <tr align="left">
              <th width = "18%">Business Type</th>
              <th width = "20%">: '.$bustype.'</th>
          </tr>
          <tr align="left">
              <th width = "18%">Business Classification</th>
              <th width = "20%">: '.$busclass.'</th>
          </tr>
          <br /> <br />
          <table border="1">
            <thead>
                  <tr>
                      <th rowspan="2" style="width: 8%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                          <br><br> 
                          No
                              <br>
                      </th>
                      <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                          <br><br> 
                          Date Paid
                          <br>
                      </th>
                      <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                          <br><br> 
                          Period
                          <br>
                      </th> 
                      <th colspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                          <br><br>
                              OR No.
                          <br>
                      </th> 
                      <th colspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                          <br><br>
                              Tax Due
                          <br>
                      </th> 
                      <th colspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                          <br><br>
                              Penalty
                          <br>
                      </th> 
                      <th colspan="2" style="width: 14%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                          <br><br>
                              Total
                          <br>
                      </th> 
                  </tr>
              </thead>
              <tbody >';
              $cnt=0;
              $totalamnt = 0;
      foreach($data as $row){ 
          $cnt = $cnt + 1;
          $html_content .='
          <tr>
          <td style="width: 8%; text-align: center;">'.$cnt.'</td>
          <td style="width: 15%; text-align: center;">'.$row->{'OR Date'}.'</td>
          <td style="width: 15%">'.$row->{'Period'}.'</td>
          <td style="width: 15%; text-align: center;">'.$row->{'OR No'}.'</td>
          <td style="width: 15%; text-align: right;">'.$row->{'Tax Due'}.'</td>
          <td style="width: 15%; text-align: right;">'.$row->{'Penalty'}.'</td>
          <td style="width: 14%; text-align: right;">'.$row->{'Total'}.'</td>
          </tr>'; 
      $totalamnt += floatval(str_replace(',','',$row->{'Total'}));
      }
      if($cnt < 5){
          $lp = 5 - $cnt;
          for ($x = 0; $x <= $lp; $x++) {
              $html_content .='
              <tr>
              <td style="width: 8%; text-align: center;"></td>
          <td style="width: 15%; text-align: center;"></td>
          <td style="width: 15%"></td>
          <td style="width: 15%; text-align: center;"></td>
          <td style="width: 15%; text-align: center;"></td>
          <td style="width: 15%; text-align: center;"></td>
          <td style="width: 14%; align: center;"></td>
              </tr>';
          }
      }
      
      $html_content .='</tbody>
      </table>
      <table width1="100%" border="1">
      <tr>
      <th style="width:83%;text-align:right;"><b>TOTAL:</b></th>  
      <th style="width:14%;text-align:right;height:15px;"><b>'.number_format($totalamnt,2).'</b></th>  
       </tr> 
  </table>'; 
      PDF::SetTitle('Print Detail');
      PDF::AddPage('L');
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path().'/print.pdf', 'F');
      return response()->json(new JsonResponse(['status'=>'success']));
       }catch (\Exception $e) {
           return response()->json(new JsonResponse(['status'=>'error']));
       }
  }

  public function businessTaxLedgerHistoryPrint($id){
    $list = DB::select('call '.$this->lgu_db.'.spl_display_business_transaction_history_joy(?)',array($id));
    foreach($list as $main){
        $bussname=$main->{'Business Name'};
     }
    $logo = config('variable.logo');
    try {
    $html_content = '
    '.$logo.'
    <h2 align="center">Business Payment History</2>
    <br></br>
    <br></br>
    <table border="1" cellpadding="2">
        <tr align="left">
            <th width = "15%">Business Name :</th>
            <th width = "50%"><b>'. $bussname.'</b></th>
        </tr>
        <br />
        <table border="1">
          <thead>
                <tr>
                    <th rowspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                        <br><br> 
                        OR Date
                            <br>
                    </th>
                    <th rowspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                        <br><br> 
                        OR No.
                        <br>
                    </th>
                    <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                        <br><br> 
                        Transaction Date
                        <br>
                    </th> 
                    <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                        <br><br>
                            Tax Year
                        <br>
                    </th> 
                    <th colspan="2" style="width: 8%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                        <br><br>
                            Period
                        <br>
                    </th> 
                    <th colspan="2" style="width: 30%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                        <br><br>
                            Particular
                        <br>
                    </th> 
                    <th colspan="2" style="width: 12%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                        <br><br>
                            Amount
                        <br>
                    </th> 
                </tr>
            </thead>
            <tbody >';
    foreach($list as $row){ 
        $html_content .='
        <tr>
        <td style="width: 12%; text-align: center;">'.$row->{'OR Date'}.'</td>
        <td style="width: 12%; text-align: center;">'.$row->{'OR No'}.'</td>
        <td style="width: 15%; text-align: center;">'.$row->{'Transaction Date'}.'</td>
        <td style="width: 10%; text-align: center;">'.$row->{'Tax Year'}.'</td>
        <td style="width: 8%; text-align: center;">'.$row->{'Period'}.'</td>
        <td style="width: 30%; text-align: left;"> '.$row->{'Particular'}.'</td>
        <td style="width: 12%; text-align: right;">'.$row->{'OR Amount'}.'</td>
        </tr>'; 
    }
   
    $html_content .='</tbody>
    </table>'; 
    PDF::SetTitle('Print Payment History');
    PDF::AddPage('L');
    PDF::writeHTML($html_content, true, true, true, true, '');
    PDF::Output(public_path().'/print.pdf', 'F');
    return response()->json(new JsonResponse(['status'=>'success']));
     }catch (\Exception $e) {
         return response()->json(new JsonResponse(['status'=>'error']));
     }
  }
  
  public function getbusinessCollectionReport(Request $request)
    {
        $date = $request->from;
        $date = date("Y", strtotime($date));
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_collectibles_laravel(?)', array($date));
        return response()->json(new JsonResponse($list));
    }
    public function getbusinessCollectionDetails($id)
    {
        $vdtls = DB::select('Call ' . $this->lgu_db . '.spl_display_collectibles_laravel_details(' . $id . ')');
        return response()->json(new JsonResponse($vdtls));
    }
    public function businessCollectionReportPrint(Request $request)
    {
        $data = $request->main;
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h2 align="center">Business Collectibles</h2>
            <br></br>
            <br></br>
            <table border="1">
            <thead>
            <tr>
            <th rowspan="2" style="width: 25%;text-align:center;vertical-align:middle;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                Business Name
                <br>
            </th>
            <th rowspan="2" style="width: 18%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Tax Payers Name
                <br>
            </th>
            <th rowspan="2" style="width: 17%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Account No
                <br>
            </th> 
            <th colspan="2" style="width: 25%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Address
                <br>
            </th> 
            <th colspan="2" style="width: 15%;text-align:center;border: .5px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Total
                <br>
            </th> 
        </tr>
    </thead>
    <tbody >';
            $TotalAmnt = 0;
            foreach ($data as $row) {
                $html_content .= '
                <tr style="font-family: Arial, font-size: 7pt" align="center">
                <td width="25%" align="left" indent="5%">' . $row['Business Name'] . '</td>
                <td width="18%" align="left">' . $row['Tax Payers Name'] . '</td>
                <td width="17%">' . $row['Account No'] . '</td>
                <td width="25%" align="left">' . $row['Address'] . '</td>
                <td width="15%">' . $row['Total'] . '</td>
            </tr>';
            $TotalAmnt += floatval(str_replace(',','',$row['Total']));
            }
            $html_content .= '</tbody>
            </table>
            <table width1="100%" border="1">
            <tr>
            <th style="width:85%;text-align:right;"><b>TOTAL:</b></th>  
            <th style="width:15%;text-align:center;height:15px;"><b>'.number_format($TotalAmnt,2).'</b></th>  
             </tr> 
        </table>';
            PDF::SetTitle('Master List');
            PDF::Addpage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function businessCollectionDetailsPrint($id)
    {
        $maindata = DB::select('Call ' . $this->lgu_db . '.spl_display_collectibles_laravelbyid(?)', array($id));
        $detaildata = DB::select('Call ' . $this->lgu_db . '.spl_display_collectibles_laravel_details(' . $id . ')');
        
        foreach ($maindata as $main) {
            $name = $main->{'Business Name'};
            $taxpayername = $main->{'Tax Payers Name'};
            $acctno = $main->{'Account No'};
            $address = $main->{'Address'};
            $total = $main->{'Total'};
        }
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
            ' . $logo . '
                <style>
                    table{
                        width:100%;
                        padding:3px;
                    }
                    .caption-label{
                        width: 15%; 
                    }
                    .caption-line{
                        width: 25%; 
                    border-bottom: 1px solid black;
                    }
                </style>
                <table style="width: 100%;">
                    <tr>
                        <th class="caption-label">
                            Business Name:
                        </th>
                        <th class="caption-line">' .  $name . '</th>
                        <th class="caption-label">
                        Tax Payers Name:
                        </th >
                        <th class="caption-line">' . $taxpayername . '</th>      
                    </tr>  
                    <tr style="height: 25px">
                        <th class="caption-label">
                        Account No:
                        </th>
                        <th class="caption-line">' . $acctno . '</th>
                        <th class="caption-label">
                        Address:
                        </th>
                        <th class="caption-line">' . $address . '</th>    
                    </tr>  
                </table> 
                <br> <br>
                <label style="text-align: center"><b>Details</b></label>
                <br /> <br />
                <table border="1">
                  <thead>
                        <tr>
                            <th rowspan="2" style="width: 20%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                                <br><br>
                                Period
                                <br>
                            </th>
                            <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                                <br><br> 
                                Tax Due
                                <br>
                            </th>
                            <th rowspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                                <br><br> 
                                Penalty
                                <br>
                            </th> 
                            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                                <br><br>
                                    Interest
                                <br>
                            </th> 
                            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                                <br><br>
                                    Others
                                <br>
                            </th> 
                            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                                <br><br>
                                    Total
                                <br>
                            </th> 
                            <th colspan="2" style="width: 25%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                                <br><br>
                                    Type
                                <br>
                            </th> 
                        </tr>
                    </thead>
                    <tbody >';
            foreach ($detaildata as $row) {
                $html_content .= '
                        <tr>
                        <td style="width: 20%;text-align:center">' . $row->Period . '</td>
                        <td style="width: 15%;text-align:center">' . $row->{'Tax Due'} . '</td>
                        <td style="width: 10%;text-align:center">' . $row->Penalty . '</td>
                        <td style="width: 10%;text-align:center">' . $row->Interest . '</td>   
                        <td style="width: 10%;text-align:center">' . $row->Others . '</td>  
                        <td style="width: 10%;text-align:center">' . $row->Total . '</td>   
                        <td style="width: 25%;text-align:center">' . $row->Type . '</td>                     
                        </tr>';
            } 
            $html_content .= '</tbody>
            </table>
             <table width1="100%" border="1">
              <tr>
                <th style="width:65%;text-align:right;"><b>TOTAL</b></th>  
                <th style="width:35%;text-align:left;height:15px;"><b>' . $total . '</b></th>  
              </tr> 
            </table>';
            PDF::SetTitle('Print Detail');
            PDF::Addpage();
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getbusinessComparativeReport(Request $request)
    {
        // $datefrom = $request->from;
        // $dateF = date("Y", strtotime($datefrom));
        // $dateto = $request->to;
        // $dateT = date("Y", strtotime($dateto));
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_collectibles_comparative_jay_LARAVEL1(?,?)', array($request->from,$request->to));
        return response()->json(new JsonResponse($list));
    }
    public function businessComparativeReportPrint(Request $request)
    {   
        $dateF =  $request->from;
        $dateT = $request->to;
        // $data = $request->main;
        $data = DB::select('call ' . $this->lgu_db . '.spl_display_collectibles_comparative_jay_LARAVEL1(?,?)', array($request->from,$request->to));

        $logo = config('variable.logo');        
        try {
          PDF::SetFont('Helvetica', '', '8');
            $html_content ='     
            ' . $logo . '  
            <h2 align="center">Business Collectibles Comparative Report</h2>
            <table border="1" cellpadding="2">
                <tr align="center" >
                    <th style="width:25%">Business Name</th>
                    <th style="width:15%">Tax Payers Name</th>
                    <th style="width:15%">Account No</th>
                    <th style="width:25%">Address</th>
                    <th style="width:7%">' . $dateF . '</th>
                    <th style="width:7%">' . $dateT . '</th>
                </tr>
                <tbody>';
                foreach($data as $row){       
                    //dd($row);        
                    $html_content .='
                    <tr>
                    <td style="width: 25%;text-align:left">' . $row->{'Business Name'} . '</td>
                    <td style="width: 15%;text-align:left">' . $row->{'Tax Payers Name'} . '</td>
                    <td style="width: 15%;text-align:left">' . $row->{'Account No'} . '</td>
                    <td style="width: 25%;text-align:left">' . $row->{'Address'} . '</td>
                    <td style="width: 7%;text-align:right">' . $row->{'Year1'} . '</td>
                    <td style="width: 7%;text-align:right">' . $row->{'Year2'} . '</td>                               
                    </tr>';
                }
                $html_content .='</tbody>
            </table>
            ';

            PDF::SetTitle('Master List');
            PDF::Addpage('L');
            //dd($html_content);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    // end of controller 
}
