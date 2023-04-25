<?php

namespace App\Http\Controllers\Api\Treasury;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class RealPropertyTax extends Controller
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


public function getrptTaxMasterList(Request $request)
{
    try {  
        $proptype = $request->proptype;      
        $brgy = $request->brgyId;           
        $revision = $request->revision;  
                    
        $list = DB::select('call '.$this->lgu_db.'.balodoy_ecao_display_rpt_masterlist(?,?,?)',array($proptype,$revision,$brgy));    
        return response()->json(new JsonResponse($list));
    } catch (\Exception $e) {
    return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
    }
}
  
public function rptTaxMasterListPrint(Request $request)
{
    $data = $request->main;        
    $logo = config('variable.logo');        
    try {
        $html_content ='<body>
        '.$logo.'            
        <h2 align="center">RPT Master List</2>
        <br></br>
        <br></br>
        <table border="1"  cellpadding="2">
            <tr align="center" >
                <th style="width:15%">OWNER NAME</th>
                <th style="width:10%">TD NO</th>
                <th style="width:13%">MARKET VALUE</th>
                <th style="width:13%">ASSESSED VALUE</th>
                <th style="width:15%">PIN</th>
                <th style="width:14%">CLASSIFICATION TYPE</th>
                <th style="width:10%">REVISION YEAR</th>
                <th style="width:10%">BARANGAY</th>
            </tr>
            <tbody>';
            foreach($data as $row){
                $rptMain =$row;
                $html_content .='
                <tr>
                <td align="left" style="width:15%">'.$rptMain['OWNER NAME'].'</td>
                <td align="center" style="width:10%">'.$rptMain['TD NO'].'</td>
                <td align="right" style="width:13%">'.$rptMain['MV'].'</td>
                <td align="right" style="width:13%">'.$rptMain['AV'].'</td>
                <td align="center" style="width:15%">'.$rptMain['PIN'].'</td>
                <td align="left" style="width:14%">'.$rptMain['CLASSIFICATION TYPE'].'</td>
                <td align="center" style="width:10%">'.$rptMain['REVISION YEAR'].'</td>                 
                <td align="center" style="width:10%">'.$rptMain['BARANGAY'].'</td>              
                </tr>';
            }
            $html_content .='</tbody>
        </table>
        </body>';
        
        
        PDF::SetTitle('RPT Master List');
        PDF::SetFont('times', '', 8);
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
        return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }

}  
 
public function getrptTaxClearance($id)
{    
    
    $list = DB::select('call '.$this->lgu_db.'.balodoy_cto_get_TaxClearance_OR_no(?)',array($id));    
    return response()->json(new JsonResponse($list));
}

public function rptTaxClearancePrint(Request $request)      
{
            $id = $request->main['id'];
            $info = $request->main;       
    
            $dataRPT = DB::select('call '.$this->lgu_db.'.balodoy_cto_display_list_property_owner_tax_clearance_byYear(?)',array($id));
            $logo = config('variable.logo');             
            try{
            $html_content ='
            '.$logo.'
            <h2 align="center"> TAX CLEARANCE </h2>
        <br>
        <br>
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
        <body>
        <table width ="100%">
            <tr style="height:25px">
                <td style="width:70%" align="left">               
                </td>                
                <td style="width:30%; border-bottom: 1px solid black" align="center">           
                '.date("F j, Y").'                             
                </td>
            </tr>
            <br>
            <tr style="height:25px">   
                <td style="width:100%">
                TO WHOM IT MAY CONCERN :
                </td>                  
            </tr>   
            <br>            
            <tr style="height:25px">
                <td style="width:10%">               
                </td>    
                <td style="width:90%">
                This is to certify that according to the real property tax register on file in this office, the following real
                </td>                   
            </tr>
            <tr style="height:25px">   
                <td style="width:100%">
                property/properties is/are declared in the name of:
                </td>                   
            </tr>
            <br>
            <tr style="height:25px; font-weight:bold" align="center">   
                <td style="width:100%">      
                '.$info['or_tax_payer_business_name'].'
                </td>                   
            </tr>                     
        </table>
        <br>
        <br>
        <table border="1">
        <thead>            
            <tr>
                <th style="width:15%" class="caption-label-center"><br><br>TD NO<br></th>      
                <th style="width:20%" class="caption-label-center"><br><br>LOCATION<br></th>        
                <th style="width:15%" class="caption-label-center"><br><br>ASSESSED VALUE<br></th>
                <th style="width:15%" class="caption-label-center"><br><br>OR NO.<br></th>
                <th style="width:15%" class="caption-label-center"><br><br>OR DATE<br></th>
                <th style="width:20%" class="caption-label-center"><br><br>PERIOD<br></th>
            </tr>   
        </thead>
        <tbody>';
        foreach($dataRPT as $row){           
        $html_content .='        
        <tr>         
            <td align="center" style="width:15%">'.$row->TaxDecNo.'</td>
            <td align="left" style="width:20%">'.$row->PropertyLocation.'</td>
            <td align="right" style="width:15%">'.$row->AssessedValue.'</td>
            <td align="center" style="width:15%">'.$row->ORNumber.'</td>
            <td align="center" style="width:15%">'.$row->DatePaid.'</td>
            <td align="center" style="width:20%">'.$row->Period.'</td>       
        </tr>';
        }
        for ($x = 0; $x <= 6; $x++) {
            $html_content .='        
            <tr>         
                <td align="center" style="width:15%"></td>
                <td align="left" style="width:20%"></td>
                <td align="right" style="width:15%"></td>
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:15%"></td>
                <td align="left" style="width:20%"></td>       
            </tr>';
        }
        $html_content .='
        </tbody>      
        </table>
        <br>
        <br>
        <table width ="100%">        
            <br>
            <tr style="height:25px">  
                <td style="width:5%">        
                </td> 
                <td style="width:95%">
                    NOTE : A MARK, ERASURES, ALTERATION OF ANY ENTRY INVALIDATES THIS CLEARANCE.
                </td>                  
            </tr>   
            <br>            
            <tr style="height:25px"> 
                <td style="width:10%">        
                </td>             
                <td style="width:90%">
                It is further certified that taxes of the real property/properties described above is/are paid up to and including
                </td>                               
            </tr>
            <tr style="height:25px" align="left">            
                <td style="width:10%">
                the year
                </td>  
                <td style="width:10% ; border-bottom: 1px solid black" align="center">
                '.date("Y").'
                </td>                             
            </tr>
            <br>
            <tr style="height:25px">   
                <td style="width:40%" align="left">
                This certification is issued upon the request of
                </td> 
                <td style="width:30%; border-bottom: 1px solid black" align="center">
                '.$info['or_tax_payer_business_name'].'
                </td>                  
            </tr>
            <tr style="height:25px">   
                <td style="width:5%"  align="left">
                for
                </td>
                <td style="width:45%; border-bottom: 1px solid black"  align="Center">
                '.$info['purpose'].'
                </td>
                <td style="width:45%"  align="left">
                purposes
                </td>                    
            </tr>
            <br>
            <br>
            <tr style="height:25px">   
                <td style="width:20%"  align="left">
                Certification Fee:
                </td> 
                <td style="width:20% ; border-bottom: 1px solid black" align="left">
                '.$info['or_amount'].'
                </td>                  
            </tr> 
            <tr style="height:25px">   
                <td style="width:20%"  align="left">
                Paid under OR No.:
                </td> 
                <td style="width:20%; border-bottom: 1px solid black" align="left">
                '.$info['or_number'].'
                </td>                  
            </tr> 
            <tr style="height:25px">   
                <td style="width:20%" align="left">
                Issued On:
                </td> 
                <td style="width:20%; border-bottom: 1px solid black" align="left">
                '.$info['or_date'].'
                </td> 
                <td style="width:20%">           
                </td>           
                <td style="width:40%; font-weight:bold; border-bottom: 1px solid black" align="center">
                Louella S. Maybituin
                </td>                      
            </tr> 
            <tr style="height:25px" align="left">   
                <td style="width:60%">            
                </td> 
                <td style="width:40%" align="center">
                City/Municipal Treasurer
                </td>                             
            </tr>                 
        </table>';
            PDF::SetTitle('RPT TAX CLEARANCE');
            PDF::AddPage('P');
            PDF::SetFont('times', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, ''); 
            PDF::Output(public_path().'/print.pdf','F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
                return response()->json(new JsonResponse(['errormsg'=> $e, 'status' => 'error']));
            }
    }
    public function getrptTaxComputation($id)
    {
        try {        
            $list = DB::select('call '.$this->lgu_db.'.jay_get_rpt_computation(?,?)',array($id,2));    
            return response()->json(new JsonResponse($list));    
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
        }
    }    




    public function getrptTaxDelinquency(Request $request)

    {
    try {

        $owner = $request->main['abc'];
        $brgy = $request->main['brgyId'];
        $year = $request->main['year'];
        $revision = $request->main['revision'];
        $blDisabledUnpaid = $request->main['blDisabledUnpaid'];
        $yearsUnpaid = $request->main['yearsUnpaid'];

        $unpaidyears = ($blDisabledUnpaid == 'true') ? '%' : $yearsUnpaid;        
        // dd(array($owner.'%',$brgy,$year,$revision,$unpaidyears));
        $list = DB::select('call '.$this->lgu_db.'.jay_getAll_delinquency_list_new(?,?,?,?,?)',array($owner.'%',$brgy,$year,$revision,$unpaidyears));    
        // dd($list);
        $main = array();
        
        foreach($list as $row){   
        $computation = DB::select('call '.$this->lgu_db.'.jay_get_rpt_computation(?,?)',array($row->td_id,'0'));    
     
        foreach($computation as $row1){ 
            $totalPenalty =   $row1->{'Total Penalty'};
            $totalTax =   $row1->{'Total Tax Dues'};
            $totalAll =   $row1->{'Total'};
        }   

        $temp = array(
            'TDID'=> $row->{'td_id'},
            'TAX DEC. NO'=> $row->{'TAX DEC. NO'},
            'PIN'=> $row->{'PIN'},
            'OWNER'=> $row->{'OWNER'},
            'OWNER ADDRESS'=> $row->{'OWNER ADDRESS'},
            'PROPERTY ADDRESS'=> $row->{'PROPERTY ADDRESS'},
            'PROPERTY TYPE'=> $row->{'PROPERTY TYPE'},
            'ASSESSED VALUE'=> $row->{'ASSESSED VALUE'},
            'PENALTY'=> $totalPenalty,
            'TAXDUES'=> $totalTax,
            'DELINQUENCY'=> $totalAll,
            'TAX YEAR(S)'=> $row->{'TAX YEAR(S)'},
            'UNPAID YEARS'=> $row->{'UNPAID YEARS'},                   
        );
        array_push($main, $temp);           
        }
      
        return response()->json(new JsonResponse($main));
    
    } catch (\Exception $e) {
    
        return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
    }
}

public function rptTaxDelinquencyPrint(Request $request)
{


    $year = $request->filter['year'];
    
    $data = $request->main;        
    $logo = config('variable.logo');  
    
    try {
        
        PDF::SetFont('Helvetica', '', '9');
        
        $html_content = '
            ' . $logo . ' 
            <h2 align="center">Real Property Tax Delinquency List</h2>            
            <h3 align="center">Tax Year '.$year.'</h3>
            <br></br>
            <br></br>
            <br></br>
            <br></br> 
            <table  border="1" style="padding:2px;">
            <thead>
            <tr>
                <th style="width:5%;text-align:center;background-color:#dedcdc;"><br><br><b>No</b><br></th>
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Dec No</b><br></th>
                <th style="width:14%;text-align:center;background-color:#dedcdc;"><br><br><b>PIN</b><br></th>
                <th style="width:20%;text-align:left;background-color:#dedcdc;"><br><br><b>OR Property Owner</b><br></th>
                <th style="width:8%;text-align:left;background-color:#dedcdc;"><br><br><b>Property Barangay</b><br></th>
                <th style="width:8%;text-align:center;background-color:#dedcdc;"><br><br><b>Property Type</b><br></th>
                <th style="width:8%;text-align:center;background-color:#dedcdc;"><br><br><b>Assessed Value</b><br></th>
                <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Delinquency Amount</b><br></th>
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Year(s)</b><br></th>
                <th style="width:5%;text-align:center;background-color:#dedcdc;"><br><br><b>Unpaid Years</b><br></th>
            </tr>   
            </thead>
            <tbody >'; 
            $ctr = 1; 
            $total = 0;
            foreach($data as $row){                              
                $html_content .='
                <tr >        
                    <td style="width:5%;text-align:center;">' .$ctr. '</td>
                    <td style="width:10%;text-align:center;">' .$row['TAX DEC. NO'] . '</td>                    
                    <td style="width:14%;text-align:center;">' .$row['PIN'] . '</td>

                    <td style="width:20%;text-align:left;">'.$row['OWNER'] . '</td>
                    <td style="width:8%;text-align:left;">' . $row['PROPERTY ADDRESS'] . '</td>                                 

                    <td style="width:8%;text-align:center;">' .$row['PROPERTY TYPE'] . '</td>
                    <td style="width:8%;text-align:right;">' .$row['ASSESSED VALUE'] . '</td>
                    <td style="width:12%;text-align:right;">' .$row['DELINQUENCY'] . '</td>
                    <td style="width:10%;text-align:right;">' .$row['TAX YEAR(S)'] . '</td>

                    <td style="width:5%;text-align:center;">' . $row['UNPAID YEARS'] . '</td>                                              
                </tr>';
                $ctr++;
                $total+=(str_replace(',','',$row['DELINQUENCY']));
            }           
            $ctr = $ctr - 1;
            $html_content .='
            <tr>
            <th colspan="2" style="text-align:right;height:20px;padding-top: 20px;"><b>TOTAL AMOUNT</b></th>  
            <th colspan="17"style="text-align:left;height:20px;padding-top: 20px;"><b>'. number_format($total,2).'</b></th>  
            </tr>
            <tr>
            <th colspan="2" style="text-align:right;height:20px;padding-top: 20px;"><b>TOTAL RECORDS</b></th>  
            <th colspan="17"style="text-align:left;height:20px;padding-top: 20px;"><b>'.$ctr.'</b></th>  
            </tr>';
            $html_content .='</tbody>
            </table>
            ';
            
    PDF::SetTitle('Real Property Delinquency List');
    PDF::AddPage('L','A4');

    PDF::writeHTML($html_content, true, true, true, true, '');
    PDF::Output(public_path() . '/printLists.pdf', 'F');

    return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
    return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    //dd($e);
    }
}



// Print Individual Notice***
public function rptTaxDelinquencyNoticePrint(Request $request)
{

$data = $request->main;

$signatory = $this->signatory;

foreach($signatory as $row){ 
    $treasureName =   $row->{'head_name'};
    $designation =   $row->{'head_pos'};   
}  

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
        <th style="width:75%"></th> 
        <th style="text-align:right;width:25%;"><h3>' . $request->dateNotice . '</h3></th> 
    </tr>
    <tr>
        <th style="width:5%">To</th> 
        <th style="width:2%">:</th>
        <th style="width:34%;"><h3>' . $data['OWNER'] . '</h3></th>  
    </tr>
    <tr>
        <th style="width:7%"></th>  
        <th style="width:34%;"><h3>' . $data['OWNER ADDRESS'] . '</h3></th>  
    </tr>       
    </table>  
    <br> <br>  <br> 
    <table style="width:100%;padding:3px;">
    <tr>
        <th style="width:100%">SIR/MADAM:</th>  
    </tr> 
    <tr>
        <th style="width:5%;"></th> 
        <th style="width:100%;">Records of the office show that you have not paid your real property tax as follows ;</th>         
    </tr>

    <table  border="1" style="padding:2px;">
        <thead>
        <tr>             
            <th style="width:15%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Dec No</b><br></th>
            <th style="width:17%;text-align:center;background-color:#dedcdc;"><br><br><b>Location of Property</b><br></th>
            <th style="width:15%;text-align:center;background-color:#dedcdc;"><br><br><b>Assessed Value</b><br></th>
            <th style="width:17%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Year(s)</b><br></th>
            <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Basic/SEF Tax Dues</b><br></th>
            <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Basic/SEF Tax Penalties</b><br></th>
            <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Amount</b><br></th>
        </tr>  
        </thead>
        <tbody>
        <tr>    
            
            <td style="width:15%;text-align:center;">' .$data['TAX DEC. NO']. '</td>
            <td style="width:17%;text-align:center;">' .$data['PROPERTY ADDRESS'] . '</td>                    
            <td style="width:15%;text-align:center;">' .$data['ASSESSED VALUE'] . '</td>

            <td style="width:17%;text-align:center;">' .$data['TAX YEAR(S)'] . '</td>
            <td style="width:12%;text-align:right;">' . $data['TAXDUES'] . '</td>                                 

            <td style="width:12%;text-align:right;">' .$data['PENALTY'] . '</td>
            <td style="width:12%;text-align:right;">' .$data['DELINQUENCY'] . '</td>    

        </tr>
        <tr>                           
            <td style="width:100%;text-align:center;">***Nothing Follows***</td>       
        </tr>';

        for($counter = 1; $counter <= 3; $counter += 1){                              
            $html_content .='
            <tr>        
                <td style="width:15%;text-align:center;">  </td>
                <td style="width:17%;text-align:center;">  </td>                  
                <td style="width:15%;text-align:center;">  </td>

                <td style="width:17%;text-align:left;">  </td>
                <td style="width:12%;text-align:left;">  </td>                              

                <td style="width:12%;text-align:center;">  </td>
                <td style="width:12%;text-align:right;">  </td>                                                        
            </tr>';
        }  

    $html_content .='  
    </table> 
    <br>
    <br>
    <br>
    <table>    
    <tr>  
        <th style="text-align:right;width:75%;"><h3>Deinquency Amount</h3></th>   
        <th style="text-align:right;width:25%;"><h2>P '.$data['DELINQUENCY'].'</h2></th>     
    </tr>
    <br>     
    <tr>        
        <th style="width:100%;">          Please reconcile this with your records and inform our office of any discrepancies as soon as possible so that the necessary corrections can be affected. It has been our earnest desire to keep our records accurate in order to avoid inconvenience on your part.</th>  
    </tr>
    <br>
    <tr>        
        <th style="width:100%;">          On the other hand, if you simply missed to effect payments, we would appreciate it very much if you can pay the same within the period of fifteen (15) days from reciept hereof, so that your name could be deleted or dropped from the list of delinquent taxpayers in this municipality.</th>  
    </tr> 
    <br>
    <tr>        
        <th style="width:100%;">          If we do not hear from you within the period aforementioned, we shall be constrained to avail of the administrative and/ or judicial remedies for the collection thereof pursuant to Secs. 256 - 266, R.A 7160, otherwise known as "The Local Government Code of 1991" , to wit ;</th>  
    </tr> 
    <tr>        
        <th style="width:20%;"></th>  
        <th style="width:80%;"><b>(a) Administrative through levy on real property and sale at public auction, or simutaneously,</b></th>  
    </tr> 
    <tr>        
        <th style="width:20%;"></th>  
        <th style="width:80%;"><b>(b) by juridical Action</b></th>  
    </tr>       
    <br>
    <tr>        
        <th style="width:100%;">We hope this notice merit your preferential attention.</th>             
    </tr> 
    <br>
    <br>
    <tr>   
        <th style="text-align:center;width:100%";><b>Please disregard notice if payment has been made.</b></th>  
    </tr>        
    </table>  
    <br>
    <br>
    <br>
    <table style="width:100%;padding:3px;">
    <tr>
    <th style="width:60%"></th> 
    <th style="width:40%;">Very truly yours,</th> 
    </tr>
    <br> 
    <tr>
    <th style="width:70%"></th> 
    <th style="text-align:center;width:30%"><b>'.$treasureName.'</b></th>  
    </tr>      
    <tr>
    <th style="width:70%"></th> 
    <th style="text-align:center;width:30%">'.$designation.'</th>  
    </tr>
    </table> 
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

  // Print All Notice***
  public function rptTaxDelinquencyAllNoticePrint(Request $request)
  {
 
    $signatory = $this->signatory;

    foreach($signatory as $row){ 
        $treasureName =   $row->{'head_name'};
        $designation =   $row->{'head_pos'};   
    }  

    $logo = config('variable.logo');    
    try 
    {
        $data = $request->main;  
        
        foreach($data as $row){  

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
                <th style="width:75%"></th> 
                <th style="text-align:right;width:25%;"><h3>' . $request->dateNotice . '</h3></th> 
                </tr>
                <tr>
                <th style="width:5%">To</th> 
                <th style="width:2%">:</th>
                <th style="width:34%;"><h3>' . $row['OWNER'] . '</h3></th>  
                </tr>
                <tr>
                <th style="width:7%"></th>  
                <th style="width:34%;"><h3>' . $row['OWNER ADDRESS'] . '</h3></th>  
                </tr>       
            </table>  
            <br> <br>  <br> 
            <table style="width:100%;padding:3px;">
                <tr>
                <th style="width:100%">SIR/MADAM:</th>  
                </tr> 
                <tr>
                <th style="width:5%;"></th> 
                <th style="width:100%;">Records of the office show that you have not paid your real property tax as follows ;</th>         
                </tr>

                <table  border="1" style="padding:2px;">
                    <thead>
                    <tr>             
                        <th style="width:15%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Dec No</b><br></th>
                        <th style="width:17%;text-align:center;background-color:#dedcdc;"><br><br><b>Location of Property</b><br></th>
                        <th style="width:15%;text-align:center;background-color:#dedcdc;"><br><br><b>Assessed Value</b><br></th>
                        <th style="width:17%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Year(s)</b><br></th>
                        <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Basic/SEF Tax Dues</b><br></th>
                        <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Basic/SEF Tax Penalties</b><br></th>
                        <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Amount</b><br></th>
                    </tr>  
                    </thead>
                    <tbody>
                    <tr>    
                        
                        <td style="width:15%;text-align:center;">' .$row['TAX DEC. NO']. '</td>
                        <td style="width:17%;text-align:center;">' .$row['PROPERTY ADDRESS'] . '</td>                    
                        <td style="width:15%;text-align:center;">' .$row['ASSESSED VALUE'] . '</td>

                        <td style="width:17%;text-align:center;">' .$row['TAX YEAR(S)'] . '</td>
                        <td style="width:12%;text-align:right;">' . $row['TAXDUES'] . '</td>                                 

                        <td style="width:12%;text-align:right;">' .$row['PENALTY'] . '</td>
                        <td style="width:12%;text-align:right;">' .$row['DELINQUENCY'] . '</td>    

                    </tr>
                    <tr>                           
                        <td style="width:100%;text-align:center;">***Nothing Follows***</td>       
                    </tr>';

                    for($counter = 1; $counter <= 3; $counter += 1){                              
                        $html_content .='
                        <tr>        
                            <td style="width:15%;text-align:center;">  </td>
                            <td style="width:17%;text-align:center;">  </td>                  
                            <td style="width:15%;text-align:center;">  </td>

                            <td style="width:17%;text-align:left;">  </td>
                            <td style="width:12%;text-align:left;">  </td>                              

                            <td style="width:12%;text-align:center;">  </td>
                            <td style="width:12%;text-align:right;">  </td>                                                        
                        </tr>';
                    }  

                $html_content .='  
                </table> 
                <br>
                <br>
                <br>
                <table>    
                <tr>  
                <th style="text-align:right;width:75%;"><h3>Deinquency Amount</h3></th>   
                <th style="text-align:right;width:25%;"><h2>P '.$row['DELINQUENCY'].'</h2></th>     
                </tr>
                <br>     
                <tr>        
                <th style="width:100%;">          Please reconcile this with your records and inform our office of any discrepancies as soon as possible so that the necessary corrections can be affected. It has been our earnest desire to keep our records accurate in order to avoid inconvenience on your part.</th>  
                </tr>
                <br>
                <tr>        
                <th style="width:100%;">          On the other hand, if you simply missed to effect payments, we would appreciate it very much if you can pay the same within the period of fifteen (15) days from reciept hereof, so that your name could be deleted or dropped from the list of delinquent taxpayers in this municipality.</th>  
                </tr> 
                <br>
                <tr>        
                <th style="width:100%;">          If we do not hear from you within the period aforementioned, we shall be constrained to avail of the administrative and/ or judicial remedies for the collection thereof pursuant to Secs. 256 - 266, R.A 7160, otherwise known as "The Local Government Code of 1991" , to wit ;</th>  
                </tr> 
                <tr>        
                    <th style="width:20%;"></th>  
                    <th style="width:80%;"><b>(a) Administrative through levy on real property and sale at public auction, or simutaneously,</b></th>  
                </tr> 
                <tr>        
                    <th style="width:20%;"></th>  
                    <th style="width:80%;"><b>(b) by juridical Action</b></th>  
                </tr>       
                <br>
                <tr>        
                    <th style="width:100%;">We hope this notice merit your preferential attention.</th>             
                </tr> 
                <br>
                <br>
                <tr>   
                    <th style="text-align:center;width:100%";><b>Please disregard notice if payment has been made.</b></th>  
                </tr>        
            </table>  
            <br>
            <br>
            <br>
            <table style="width:100%;padding:3px;">
            <tr>
                <th style="width:60%"></th> 
                <th style="width:40%;">Very truly yours,</th> 
            </tr>
            <br> 
            <tr>
                <th style="width:70%"></th> 
                <th style="text-align:center;width:30%"><b>'.$treasureName.'</b></th>  
            </tr>      
            <tr>
                <th style="width:70%"></th> 
                <th style="text-align:center;width:30%">'.$designation.'</th>  
            </tr>
            </table> 
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

      PDF::SetTitle('Notice of Delinquency');
    
      PDF::Output(public_path() . '/prints.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }


public function getrptCollectionAbtract(Request $request)
{
    try {
        
        $tmp = json_decode($request->main);     
        $from = $tmp->dates->from ;
        $to = $tmp->dates->to;   
        $year = $tmp->year;   
        $cashierid = $tmp->cashierId;        
        $list = DB::select('call '.$this->lgu_db.'.cto_abstract_rowtocol_joy(?,?,?,?)',array($from,$to,$cashierid,$year));               
        return response()->json(new JsonResponse($list));      
    } catch (\Exception $e) { 
        return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
    }
}

public function rptCollectionAbtractPrint(Request $request)
{
    
    $tmp = $request->main['dates'];   
    $from = date("F d,Y", strtotime($tmp['from'])); 
    $to = date("F d,Y", strtotime($tmp['to']));     
    $data = $request->details;        
    $logo = config('variable.logo');  
    try {
        PDF::SetFont('Helvetica', '', '9');       
        $html_content = '
            ' . $logo . ' 
            <h2 align="center">Real Property Abstract Collection</h2>            
            <h3 align="center">Period Covered '.$from.' to '.$to.'</h3>
            <br></br>
            <br></br>
            <br></br>
            <br></br> 
            <table  border="1" style="padding:2px;">
            <thead>
            <tr>
            <th rowspan="2" style="width:2%;text-align:center;background-color:#dedcdc;"><br><br><b>No</b><br></th>
            <th rowspan="2" style="width:7.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Name of Tax Payer</b><br></th>
                <th rowspan="2" style="width:6.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Barangay</b><br></th>
                <th colspan="3" style="width:11%;text-align:center;background-color:#dedcdc;"><br><br><b>OR Information</b><br></th>
                <th colspan="10" style="width:36.5%;text-align:center;background-color:#dedcdc;"><br><br><b>BASIC</b><br></th>
                <th colspan="10" style="width:36.5%;text-align:center;background-color:#dedcdc;"><br><br><b>SEF</b><br></th>
            </tr>  

            <tr>
                <th style="text-align:center;background-color:#dedcdc;"><br><br><b>OR Date</b><br></th>
                <th style="text-align:center;background-color:#dedcdc;"><br><br><b>OR No</b><br></th>
                <th style="text-align:center;background-color:#dedcdc;"><br><br><b>OR Amount</b><br></th>                
            
                
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Advance Year Amount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Advance Year Discount</b><br></th>

                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>Current Year Amount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Current Year Discount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Current Penalty</b><br></th>

                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>Preceeding Amount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Preceeding Penalties</b><br></th>

                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>Prior Year Amount</b><br></th>
                <th style="width:3%;text-align:center;background-color:#dedcdc;"><br><br><b>Prior Penalties</b><br></th>
                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>Basic Total</b><br></th>

                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Advance Year Amount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Advance Year Discount</b><br></th>

                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>Current Year Amount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Current Year Discount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Current Penalty</b><br></th>

                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>Preceeding Amount</b><br></th>
                <th style="width:3.5%;text-align:center;background-color:#dedcdc;"><br><br><b>Preceeding Penalties</b><br></th>

                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>Prior Year Amount</b><br></th>
                <th style="width:3%;text-align:center;background-color:#dedcdc;"><br><br><b>Prior Penalties</b><br></th>
                
                <th style="width:4%;text-align:center;background-color:#dedcdc;"><br><br><b>SEF Total</b><br></th>

            </tr>
            </thead>
            <tbody >'; 
            $ctr = 1; 
            foreach($data as $row){                              
                $html_content .='
                <tr >        
                    <td style="width:2%;text-align:center;">' .$ctr. '</td>
                    <td style="width:7.5%;text-align:Left;">' . $row['td_prop_owner'] . '</td>                    
                    <td style="width:6.5%;text-align:center;">' . $row['Barangay'] . '</td>

                    <td style="width:3.67%;text-align:center;">' . $row['Date'] . '</td>
                    <td style="width:3.67%;text-align:center;">' . $row['OR_NUMBER'] . '</td>
                    <td style="width:3.67%;text-align:right;">' . $row['OR Amount'] . '</td>            

                    <td style="width:3.5%;text-align:right;">' . $row['BASIC_advamount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['BASIC_advdiscount'] . '</td>

                    <td style="width:4%;text-align:right;">' . $row['BASIC_curramount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['BASIC_currdiscount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['BASIC_currpenalty'] . '</td>

                    <td style="width:4%;text-align:right;">' . $row['BASIC_precramount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['BASIC_precpenalty'] . '</td>
                    
                    <td style="width:4%;text-align:right;">' . $row['BASIC_prioramount'] . '</td>
                    <td style="width:3%;text-align:right;">' . $row['BASIC_priorpenalty'] . '</td>

                    <td style="width:4%;text-align:right;">' . $row['BASIC_Total'] . '</td>


                    <td style="width:3.5%;text-align:right;">' . $row['SEF_advamount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['SEF_advdiscount'] . '</td>

                    <td style="width:4%;text-align:right;">' . $row['SEF_curramount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['SEF_currdiscount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['SEF_currpenalty'] . '</td>

                    <td style="width:4%;text-align:right;">' . $row['SEF_precramount'] . '</td>
                    <td style="width:3.5%;text-align:right;">' . $row['SEF_precpenalty'] . '</td>
                    
                    <td style="width:4%;text-align:right;">' . $row['SEF_prioramount'] . '</td>
                    <td style="width:3%;text-align:right;">' . $row['SEF_priorpenalty'] . '</td>

                    <td style="width:4%;text-align:right;">' . $row['SEF_Total'] . '</td>               
                                            
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
    PDF::SetTitle('Real Property Abstract Collection');
    PDF::AddPage('L',array(600,400));
    PDF::writeHTML($html_content, true, true, true, true, '');
    PDF::Output(public_path() . '/printLists.pdf', 'F');
    return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
    return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    //dd($e);
    }
}

public function getrptTaxDueandPayment($id)
{
    try {   
        // dd($id);                              
        $list = DB::select('call '.$this->lgu_db.'.balodoy_cto_display_ecao_bill_profile_dtl_history(?)',array($id));    
        // $list = DB::select('call '.$this->lgu_db.'.balodoy_cto_display_ecao_bill_profile_dtl_history');
        return response()->json(new JsonResponse($list));
} catch (\Exception $e) {
    return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
    }
}    

public function getrptTaxDueDisplayList(Request $request)
{
    try {
        //dd($request->main);
        $dateFr = $request->main['from'];
        $dateTo = $request->main['to']; 
        $list = DB::select('call '.$this->lgu_db.'.jay_ecao_display_tax_dues_and_payment(?,?)',array($dateFr,$dateTo));    
        return response()->json(new JsonResponse($list));
    
    } catch (\Exception $e) {
    
        return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
    }
}

public function getrptTaxDueandPaymentPrint(Request $request)
{       
    // dd($request->details);
    $data = $request->details;          
    $logo = config('variable.logo');         
    try{
    $html_content ='
    '.$logo.'
    <h2 align="center">REAL PROPERTY TAX ACCOUNT REGISTER</2>
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
    <body>
    <table width ="100%">
        <tr>
            <td width = "50%">
                <table border="1">
                    <thead>
                        <tr>
                            <th colspan="3" class="caption-label-center">
                                RECORD OF OWNERSHIP
                            </th>
                        </tr>
                        <tr>
                            <th style="width:50%" class="caption-label-center"><br><br>Name</th>
                            <th style="width:30%" class="caption-label-center"><br><br>Address</th>
                            <th style="width:20%" class="caption-label-center"><br><br>Date of Transfer<br></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td align="center"></td>
                            <td align="left"></td>
                            <td align="center"></td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td width = "10%">
                <table>
                    <tr style="height:25px">   
                        <td>                            
                        </td>                      
                    </tr>    
                </table>
            </td>
            <td width = "40%">
            <table>
                <tr style="height:25px">   
                    <td style="width:60%">
                        PROPERTY INDEX NO.(PIN
                    </td>
                    <td style="width:40%; border-bottom: 1px solid black">                        
                    </td>
                </tr>
                <tr style="height:25px">   
                    <td style="width:60%">
                        LOCATION OF PROPERTY
                    </td>
                    <td style="width:40%; border-bottom: 1px solid black">                      
                    </td>
                </tr>               
                <tr style="height:25px">   
                    <td style="width:60%">
                        STREET
                    </td>
                    <td style="width:40%; border-bottom: 1px solid black">                        
                    </td>
                </tr>
                <tr style="height:25px">   
                    <td style="width:60%">
                        BRGY./CITY/DISTRICT
                    </td>
                    <td style="width:40%; border-bottom: 1px solid black">                      
                    </td>
                </tr>
                <tr style="height:25px">   
                    <td style="width:60%">
                        TD #
                    </td>
                    <td style="width:40%; border-bottom: 1px solid black">                        
                    </td>
                </tr>
                <tr style="height:25px">   
                    <td style="width:60%">
                        EFFECTIVE YEAR
                    </td>
                    <td style="width:40%; border-bottom: 1px solid black">                      
                    </td>
                </tr>
            </table>
            </td>
        </tr>
    </table>
    <br></br>
    <br></br>
    <table border="1">
    <thead>            
        <tr>
            <th rowspan="2" style="width:10%" class="caption-label-center"><br><br><br>TD NO<br></th>
            <th colspan="3" style="width:15%" class="caption-label-center">ASSESSED VALUE </th>              
            <th rowspan="2" style="width:5%" class="caption-label-center"><br><br>TAX YEAR<br></th>
            <th colspan="4"style="width:20%" class="caption-label-center">TAX DUE</th>
            <th colspan="4" style="width:20%" class="caption-label-center">TAX COLLECTED</th>
            <th rowspan="2" style="width:5%" class="caption-label-center"><br><br>OR NO.<br></th>
            <th rowspan="2" style="width:5%" class="caption-label-center"><br><br>OR DATE<br></th>
            <th rowspan="2" style="width:10%" class="caption-label-center"><br><br>PROPERTY OWNER<br></th>
            <th rowspan="2" style="width:10%" class="caption-label-center"><br><br>PERIOD COVERED<br></th>
        </tr>
        <tr>
            <th style="width:5%">Land</th>
            <th style="width:5%">Improv.</th>
            <th style="width:5%">Total</th>
            <th style="width:5%">BASIC</th>
            <th style="width:5%">SEF</th>
            <th style="width:5%">PENALTY</th>
            <th style="width:5%">TOTAL</th>               
            <th style="width:5%">BASIC</th>
            <th style="width:5%">SEF</th>
            <th style="width:5%">PENALTY</th>
            <th style="width:5%">TOTAL</th>
        </tr>     
        </thead>
        <tbody>';
        foreach($data as $row){          
        $html_content .='
        <tr>           
            <td align="center" style="width:10%">'.$row['td_no'].'</td>
            <td align="left" style="width:5%"></td>
            <td align="center" style="width:5%"></td>
            <td align="center" style="width:5%"></td>
            <td align="center" style="width:5%">'.$row['tax_year'].'</td>
            <td align="right" style="width:5%">'.$row['BASIC_TAX'].'</td>
            <td align="right" style="width:5%">'.$row['SEF_TAX'].'</td>
            <td align="right" style="width:5%">'.$row['PENALTY'].'</td>
            <td align="right" style="width:5%">'.$row['TOTAL_TAX_DUE'].'</td>
            <td align="center" style="width:5%"></td>
            <td align="center" style="width:5%"></td>
            <td align="center" style="width:5%"></td>
            <td align="center" style="width:5%"></td>
            <td align="center" style="width:5%">'.$row['or_number'].'</td>
            <td align="center" style="width:5%">'.$row['or_date'].'</td>
            <td align="center" style="width:10%">'.$row['td_prop_owner'].'</td>
            <td align="center" style="width:10%">'.$row['particulars'].'</td>
        </tr>';
        }
        $html_content .='</tbody>      
</table>';
    PDF::SetTitle('RPT TAX DUES AND PAYMENTS');
    PDF::AddPage('L');
    PDF::SetFont('times', '', 7);
    PDF::writeHTML($html_content, true, true, true, true, ''); 
    PDF::Output(public_path().'/printLists.pdf','F');
    return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
        return response()->json(new JsonResponse(['errormsg'=> $e, 'status' => 'error']));
    }
    }
// end of controller   
}
