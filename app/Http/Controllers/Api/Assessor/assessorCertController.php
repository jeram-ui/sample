<?php

namespace App\Http\Controllers\Api\Assessor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;

class assessorCertController extends Controller
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function displayData()
    {
        $type = '%Certification of No Landholding%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMain(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of No LandHoldings Property List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "14%">Application No.</th>
        <th width = "12%">Application Date</th>
        <th width = "17%">Applicant</th>
        <th width = "12%">Reason/Purpose</th>
        <th width = "13%">Certification Fee</th>
        <th width = "10%">OR No.</th>
        <th width = "10%">OR Date</th>
        <th width = "12%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "14%">' . $main['Reference No'] . '</td>
            <td width = "12%">' . $main['Application Date'] . '</td>
            <td width = "17%">' . $main['Applicant'] . '</td>
            <td width = "12%">' . $main['Reason/Purpose'] . '</td>
            <td width = "13%">' . $main['Certification Fee'] . '</td>
            <td width = "10%">' . $main['OR No'] . '</td>
            <td width = "10%">' . $main['OR Date'] . '</td>
            <td width = "12%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function filterData(Request $request)
    {
        $type = '%Certification of No Landholding Property%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function businessList(Request $request)
    {
        // dd($request);
        //$date = date("Y", strtotime($date));
        $bname = '%';
        $proptype = '%';
        $list = DB::select('call ' . $this->lgu_db . '.ecao_display_company_noproperty_byproptype(?,?)', array($bname, $proptype));
        return response()->json(new JsonResponse($list));
    }

    public function personList(Request $request)
    {
        // dd($request);
        //$date = date("Y", strtotime($date));
        $bname = '%';
        $proptype = '%';
        $list = DB::select('call ' . $this->lgu_db . '.ecao_display_person_noproperty_byproptype(?,?)', array($bname, $proptype));
        return response()->json(new JsonResponse($list));
    }

    public function editData($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        return response()->json(new JsonResponse($data));
    }

    public function cancelData($id)
    {
        $data['status'] = 'Cancelled';
        DB::table($this->lgu_db . '.ecao_certification_trans')
            ->where('id', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    /** 
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            //DB::beginTransaction();
            //dd($request->details);
            //dd($request);
            $id = $request->main['id'];
            $main = $request->main;
            $cto = $request->cto;
            //dd($id);
            if ($id > 0) {
                $this->update($id, $main);
            } else {
                // dd($main);
                $this->save($main, $cto);
            }
            //DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            //DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }

    public function save($main, $cto)
    {
        // dd($request->main);
        // $row = $request->main; 
        // $details = $request->details;
        // $cto = $request->cto;
        //dd($cto);
        $rowData = $main;
        //dd($rowData);
        $data = array(
            'cert_no'  => $rowData['cert_no'],
            'series_no'  => $rowData['series_no'],
            'app_date'  => $rowData['app_date'],
            'payee_id'  => $rowData['payee_id'],
            'payee_type' => $rowData['payee_type'],
            'prop_owner'  => $rowData['prop_owner'],
            'prop_name' => $rowData['prop_name'],
            'prop_address' => $rowData['prop_address'],
            'taxdec_id' => $rowData['taxdec_id'],
            'taxdec_no' => $rowData['taxdec_no'],
            'pin_number' => $rowData['pin_number'],
            'prop_type' => $rowData['prop_type'],
            'brgy_address' => $rowData['brgy_address'],
            'cert_id'  => $rowData['cert_id'],
            'res_purpose' => $rowData['res_purpose'],
            'status' => $rowData['status'],
            'businessapplicant' => $rowData['businessapplicant'],
            'businessapplicant_id' => $rowData['businessapplicant_id'],
            'classification_type' => $rowData['classification_type'],
            'businessname' => $rowData['businessname'],
            'excempt' => $rowData['excempt'],
            'income_account_code' => $rowData['income_account_code'],
        );
        // dd($data);
        DB::table($this->lgu_db . '.ecao_certification_trans')->insert($data);
        $id = DB::getPdo()->lastInsertId();
        if ($rowData['payee_type'] == 'Person') {
            $payer_id = $rowData['payee_id'];
        } else {
            $payer_id = $rowData['businessapplicant'];
        }
        foreach ($cto as $row) {
            //dd($row);
            if ($row['Include'] == true) {
                // dd($row);
                $array = array(
                    'payer_type' => $rowData['payee_type'],
                    'payer_id' => $payer_id,
                    'business_application_id' => $rowData['businessapplicant_id'],
                    'account_code' => $row['Account Code'],
                    'bill_description' => $row['Account Description'],
                    'net_amount' => $row['Initial Amount'],
                    'bill_amount' => $row['Fee Amount'],
                    'bill_month' => $rowData['app_date'],
                    'bill_number' => $rowData['cert_no'],
                    'transaction_type' =>$rowData['classification_type'],
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'include_from' => 'Others',
                );
                // 'transaction_type' =>' $rowData['classification_type']',
                $signatory = DB::select('Call ' . $this->lgu_db . '.cvl_get_signatory_mayor_head()');
                foreach ($signatory as $row) {
                    $sign = array(
                        'na_no' => $id,
                        'na_emp_id' => $row->assessor_id,
                        'na_emp_sig' => $row->assessor_name,
                        'na_sig_type' => $row->assessor_pos,
                        'na_sig_date' => Auth::user()->id,
                        'na_pp_id' => $payer_id,
                        'na_mayor_id' => $row->mayor_id,
                        'na_mayor_name' => $row->mayor_name,
                        'na_mayor_sig' => $row->mayor_pos,
                        'na_user_id' => Auth::user()->id,
                    );
                    // dd($this->lgu_db.'.signatory_logs');
                    // dd($sign);
                    DB::table($this->lgu_db . '.ecao_certification_sig')->insert($sign);
                }
                // dd($array);
                DB::table($this->lgu_db . '.cto_general_billing')->insert($array);
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function update($id, $main)
    {   // dd($request->id);
        //$main = $request->main;
        // $details = $request->details;
        //$id = $request->id;
        DB::table($this->lgu_db . '.ecao_certification_trans')
            ->where('id', $id)
            ->update([
                'cert_no'  => $main['cert_no'],
                'series_no'  => $main['series_no'],
                'app_date'  => $main['app_date'],
                'payee_id'  => $main['payee_id'],
                'payee_type' => $main['payee_type'],
                'prop_owner'  => $main['prop_owner'],
                'prop_name' => $main['prop_name'],
                'prop_address' => $main['prop_address'],
                'taxdec_id' => $main['taxdec_id'],
                'taxdec_no' => $main['taxdec_no'],
                'pin_number' => $main['pin_number'],
                'prop_type' => $main['prop_type'],
                'cert_id'  => $main['cert_id'],
                'res_purpose' => $main['res_purpose'],
                'status' => $main['status'],
                'businessapplicant' => $main['businessapplicant'],
                'classification_type' => $main['classification_type'],
                'businessname' => $main['businessname'],
                'excempt' => $main['excempt'],
                'income_account_code' => $main['income_account_code'],
            ]);

        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));

        //
    }

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

    public function noLandholdingCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification of No Landholding%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
<br>
<br>
        
        <h2 align="center"> CERTIFICATION </h2>
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
      <br>
      <br>
         <tr style="height:25px">   
            <td style="width:100%">
            TO WHOM IT MAY CONCERN :
            </td>                  
         </tr> 
       <br> 
         <tr style="height:25px" align="center">   
            <td style="width:5%"> 
            </td> 
            <td style="width:40%">
            This it to certify that as per office records on file; 
            </td>    
            <td style="width:45%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'Applicant'} . '
            </td>
            <td style="width:10%" align="right">   
            has no  
            </td>                   
        </tr> 

        <tr style="height:25px" align="center">    
            <td style="width:1000%" align="left">   
            property declared in his/her/their name.  
            </td>                      
         </tr>
         <br>

         <tr style="height:25px" align="left">
            <td style="width:5%"> 
            </td>
            <td style="width:40%" align="left">   
            This certification is issued upon the request of   
            </td>
            <td style="width:35%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'Applicant'} . '
            </td>
            <td style="width:20%" align="right">   
            for any legal purpose.  
            </td>   
        </tr>
        <br> 

        <tr style="height:25px" align="center">   
            <td style="width:5%"> 
            </td> 
            <td style="width:10%" align="left">
            Done this
            </td>    
            <td style="width:7%; border-bottom: 1px solid black" align="center">                            
            ' . date("d") . '
            </td>
            <td style="width:10%">
            day of
            </td>
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            ' . date("M") . '
            </td>
            <td style="width:3%">
            ,
            </td>
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            ' . date("Y") . '
            </td>
            <td style="width:5%">
            at
            </td>
            <td style="width:15%; border-bottom: 1px solid black" align="center">                            
            Minglanilla
            </td>
            <td style="width:3%">
            ,
            </td>
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            Cebu
            </td>
            <td style="width:12%" align="right">
            Philippines.
            </td>                    
        </tr>
         
        <br>
        <br>
         <tr style="height:25px" align="left">
            <td style="width:70%">            
            </td>
            <td style="width:30%" align="center">
            PREPARED & VERIFIED BY:
            </td>
        </tr>

        <br>
        <tr style="height:25px" align="center">
            <td style="width:70%">            
            </td>
            <td style="width:30%; border-bottom: 1px solid black" align="center">
            ' . $infosig->{'na_emp_sig'} . '
            </td>                            
        </tr>

        <tr style="height:25px" align="center">
            <td style="width:70%">            
            </td>
            <td style="width:30%" align="center">
            ' . $infosig->{'na_sig_type'} . '
            </td>                            
        </tr>

        <br>
        <br>
         <tr style="height:25px">   
             <td style="width:20%"  align="left">
               Certification Fee:
             </td> 
             <td style="width:20% ; border-bottom: 1px solid black" align="left">
             ' . $info->{'Certification Fee'} . '
             </td>                  
         </tr> 

         <tr style="height:25px">   
            <td style="width:20%"  align="left">
             OR No.:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
            ' . $info->{'OR No'} . '
            </td>                  
         </tr> 

         <tr style="height:25px">   
            <td style="width:20%"  align="left">
             Date:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
            ' . $info->{'OR Date'} . '
            </td>                  
         </tr>

         <tr style="height:25px">   
            <td style="width:40%"  align="left">
             Minglanilla, Cebu, Philippines
            </td>                   
         </tr>

         <br>
         <br>
         <tr style="line-height:40px">
            <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF NO LANDHOLDING PROPERTY</i></span>
            </td>
            <br>
            <br>
            <td style="width:100%; border-bottom: 1px solid black" align="center">
            </td>
         </tr> 
    </table>
';
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

    public function transNo (Request $request)
    { 
      $type = $request->formType;
      // dd($type);
      if ($type == 'Certification of No Landholding') {
          $prefix = 'CNL';
      } elseif ($type == 'Certification of Newly Assessed') {
        $prefix = 'CNA';
      } elseif ($type == 'Certification for No Revision') {
        $prefix = 'CNR';
      } elseif ($type == 'Certification of Land History') {
        $prefix = 'CLH';
      } elseif ($type == 'Certification of Exempt Property') {
        $prefix = 'CEP';
      } elseif ($type == 'Certification of Real Property') {
        $prefix = 'CRP';
      } elseif ($type == 'Certification of Zero Assessment') {
        $prefix = 'CZA';
      } elseif ($type == 'Certification of Property Holdings') {
        $prefix = 'CPH';
      } elseif ($type == 'Certification of No Improvement') {
        $prefix = 'CNI';
      }
      $list = DB::select('call ' . $this->lgu_db . '.ecao_get_certification_transno(?,?)', array($prefix, $type ));   
      return response()->json(new JsonResponse($list));
    }
    // Newly Assessed
    public function displayDataNewlyAssessed()
    {
        $type = '%Certification of Newly Assessed%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainNewlyAssessed(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of Newly Assessed List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function filterDataNewlyAssessed(Request $request)
    {
        $type = '%Certification of Newly Assessed%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function editDataNewlyAssessed($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        // dd($data['main']);
        return response()->json(new JsonResponse($data));
    }

    public function newlyAssessedCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification of Newly Assessed%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
              
              <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
<br>
<br>
<h2 align="center"> CERTIFICATION </h2>
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
      <br>
      <br>
         <tr style="height:25px">   
            <td style="width:100%">
            TO WHOM IT MAY CONCERN :
            </td>                  
         </tr> 
       <br> 
         <tr style="height:25px" align="center">   
            <td style="width:5%"> 
            </td> 
            <td style="width:25%">
            This it to certify that a 
            </td>    
            <td style="width:30%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'Certification'} . '
            </td>
            <td style="width:10%" align="right">   
            located at  
            </td> 
            <td style="width:30%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'Property Address'} . '
            </td>                  
        </tr> 

        <tr style="height:25px" align="center">    
            <td style="width:25%" align="left">   
            declared in the name of  
            </td>
            <td style="width:30%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'Property Owner'} . '
            </td>
            <td style="width:25%" align="left">   
            under Tax Declaration No.  
            </td>
            <td style="width:20%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'Tax Declaration No'} . ' 
            </td>                      
         </tr>

         <tr style="height:25px" align="left">
            <td style="width:10%" align="left">   
            effective   
            </td>
            <td style="width:15%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'year'} . '
            </td>
            <td style="width:75%" align="right">   
            is a newly declared property. Pursuant to section 222 of R.A 7160, otherwise known as Local   
            </td>   
        </tr>

        <tr style="height:25px">    
            <td style="width:100%" align="left">
            Government Code of 1991, no interest for delinquency shall be imposed, if such taxes are paid on or before the quarter following
            </td>                        
        </tr>

        <tr style="height:25px" align="center">    
            <td style="width:100%" align="left">
            the date of the Notice of Assessment and Tax Bill is received by the owner.
            </td>                        
        </tr>

        <br>
        <tr style="height:25px" align="center">   
            <td style="width:5%"> 
            </td> 
            <td style="width:25%">
            This certification is issued to 
            </td>
            <td style="width:30%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'Property Owner'} . '
            </td>
            <td style="width:40%" align="right">
            in connection with their request for exemption
            </td>
        </tr>

        <tr style="height:25px">    
            <td style="width:50%">
            from penalties due on subject property. 
            </td>
        </tr>
        
        <br>
        <tr style="height:25px" align="center">   
            <td style="width:5%"> 
            </td> 
            <td style="width:10%" align="left">
            Done this
            </td>    
            <td style="width:7%; border-bottom: 1px solid black" align="center">                            
            ' . date("d") . '
            </td>
            <td style="width:10%">
            day of
            </td>
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            ' . date("M") . '
            </td>
            <td style="width:3%">
            ,
            </td>
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            ' . date("Y") . '
            </td>
            <td style="width:5%">
            at
            </td>
            <td style="width:15%; border-bottom: 1px solid black" align="center">                            
            Minglanilla
            </td>
            <td style="width:3%">
            ,
            </td>
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            Cebu
            </td>
            <td style="width:12%" align="right">
            Philippines.
            </td>                    
        </tr>

        <br>
        <br>
        <tr style="height:25px" align="left">
        <td style="width:70%">            
        </td>
        <td style="width:30%" align="center">
        PREPARED & VERIFIED BY:
        </td>
     </tr>

     <br>
    <tr style="height:25px" align="center">
        <td style="width:70%">            
        </td>
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        ' . $infosig->{'na_emp_sig'} . '
        </td>                            
    </tr>

    <tr style="height:25px" align="center">
            <td style="width:70%">            
            </td>
            <td style="width:30%" align="center">
            ' . $infosig->{'na_sig_type'} . '
            </td>                            
        </tr>
        
         <br>
         <br>
         <tr style="height:25px">   
             <td style="width:20%"  align="left">
               Certification Fee:
             </td> 
             <td style="width:20% ; border-bottom: 1px solid black" align="left">
             ' . $info->{'Certification Fee'} . '
             </td>                  
         </tr> 

         <tr style="height:25px">   
            <td style="width:20%"  align="left">
             OR No.:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
            ' . $info->{'OR No'} . '
            </td>                  
         </tr> 

         <tr style="height:25px">   
            <td style="width:20%"  align="left">
             Date:
            </td> 
            <td style="width:20%; border-bottom: 1px solid black" align="left">
            ' . $info->{'OR Date'} . '
            </td>                  
         </tr>

         <tr style="height:25px">   
            <td style="width:40%"  align="left">
             Minglanilla, Cebu, Philippines
            </td>                   
         </tr>
         
         <br>
         <br>
         <tr style="line-height:40px">
            <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF NEWLY ASSESSED PROPERTY</i></span>
            </td>
            <br>
            <br>
            <td style="width:100%; border-bottom: 1px solid black" align="center">
            </td>
         </tr> 
    </table>
      ';
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

    // No Revision
    public function displayDataNoRevision()
    {
        $type = '%Certification for No Revision%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainNoRevision(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification for No Revision List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function filterDataNoRevision(Request $request)
    {
        $type = '%Certification for No Revision%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function editDataNoRevision($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        // dd($data['main']);
        return response()->json(new JsonResponse($data));
    }

    public function noRevisionCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification for No Revision%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        $tdid = DB::table($this->lgu_db . '.ecao_certification_trans')->select('ecao_certification_trans.taxdec_id')->where('id', $ID)->first();
        $tddata = DB::table($this->lgu_db . '.ecao_tax_dec_profile')
            ->join($this->lgu_db . '.ecao_faas_profile', 'ecao_faas_profile.faas_id', '=', 'ecao_tax_dec_profile.faas_id')
            ->select($this->lgu_db . '.ecao_faas_profile.north', 'ecao_faas_profile.south', 'ecao_faas_profile.east', 'ecao_faas_profile.west')
            ->where('ecao_tax_dec_profile.td_id', $tdid->taxdec_id)
            ->get();
        foreach ($tddata as $rowData) {
            $infotd = ($rowData);
        }
        //dd($infosig);
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
           ' . $logo . '
                 
                 <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
   <br>
   <br>
   <h2 align="center"> CERTIFICATION </h2>
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
         <br>
         <br>
            <tr style="height:25px">   
               <td style="width:100%">
               TO WHOM IT MAY CONCERN :
               </td>                  
            </tr> 
          <br> 
            <tr style="height:25px" align="center">   
               <td style="width:5%"> 
               </td> 
               <td style="width:40%">
               This it to certify that the Tax Declaration No. 
               </td>    
               <td style="width:20%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Tax Declaration No'} . '
               </td>
               <td style="width:3%" align="right">   
               ;  
               </td>
               <td style="width:10%" align="right">   
               effective  
               </td> 
               <td style="width:10%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'year'} . '
               </td> 
               <td style="width:12%" align="right">   
               in the name  
               </td>                 
           </tr> 
   
           <tr style="height:25px" align="center">    
               <td style="width:5%" align="left">   
               of  
               </td>
               <td style="width:30%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Property Owner'} . '
               </td>
               <td style="width:65%" align="left">   
               is the current and existing Tax Declaration for a parcel of land situated   
               </td>                      
            </tr>
   
            <tr style="height:25px" align="left">
               <td style="width:5%">   
               at   
               </td>
               <td style="width:30%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Property Address'} . '
               </td>
               <td style="width:23%" align="right">   
               bounded on the North by   
               </td>
               <td style="width:7%; border-bottom: 1px solid black" align="center">                            
               ' . $infotd->{'north'} . '
               </td>
               <td style="width:3%" align="left">   
               ;  
               </td>
               <td style="width:22%" align="right">   
               bounded on the East by   
               </td>
               <td style="width:7%; border-bottom: 1px solid black" align="center">                            
               ' . $infotd->{'east'} . '
               </td>
               <td style="width:3%" align="left">   
               ;  
               </td>   
           </tr>
   
           <tr style="height:25px">    
               <td style="width:23%" align="left">   
               bounded on the South by   
               </td>
               <td style="width:7%; border-bottom: 1px solid black" align="center">                            
               ' . $infotd->{'south'} . '
               </td>
               <td style="width:20%" align="left">   
               and on the West by   
               </td>
               <td style="width:7%; border-bottom: 1px solid black" align="center">                            
               ' . $infotd->{'west'} . '
               </td>
               <td style="width:15%" align="left">   
               with an area of   
               </td>
               <td style="width:5%; border-bottom: 1px solid black" align="center">                            
               400
               </td>
               <td style="width:25%" align="left">   
               square meters (hectare).   
               </td>                        
           </tr>
           
           <br>
           <tr style="height:25px" align="center">   
               <td style="width:5%"> 
               </td> 
               <td style="width:95%" align="left">
               Further, this certifies that the Local Government of Naga, Cebu has not conducted a General Revision of Real Property
               </td>
            </tr>

            <tr style="height:20px">
            <td style="width:100%"align="left">
            Assessment and Classification since the year 2002. Section 60 of RA 9491, converting the Municipality of Naga into a City, 
            </td> 
            </tr>

            <tr style="height:20px">
            <td style="width:100%"align="left">
            states that there shall be no increase in the rate of local taxes within a period of five years (5) from its acquisition of corporate
            </td> 
            </tr>

            <tr style="height:20px">
            <td style="width:100%"align="left">
            existence.
            </td> 
            </tr>
           
           <br>
           <tr style="height:25px" align="center">   
               <td style="width:5%"> 
               </td> 
               <td style="width:12%" align="left">
               Issued this
               </td>    
               <td style="width:5%; border-bottom: 1px solid black" align="center">                            
               ' . date("d") . '
               </td>
               <td style="width:10%">
               day of
               </td>
               <td style="width:10%; border-bottom: 1px solid black" align="center">                            
               ' . date("M") . '
               </td>
               <td style="width:3%">
               ,
               </td>
               <td style="width:10%; border-bottom: 1px solid black" align="center">                            
               ' . date("Y") . '
               </td>
               <td style="width:5%">
               at
               </td>
               <td style="width:15%; border-bottom: 1px solid black" align="center">                            
               Minglanilla
               </td>
               <td style="width:3%">
               ,
               </td>
               <td style="width:10%; border-bottom: 1px solid black" align="center">                            
               Cebu
               </td>
               <td style="width:12%" align="right">
               Philippines.
               </td>                    
           </tr>

           <tr style="height:25px">   
               <td style="width:20%">
               upon the request of 
               </td>    
               <td style="width:25%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Property Owner'} . '
               </td>
               <td style="width:55%">
               for any legal purpose. 
               </td>
            </tr>
   
           <br>
           <br>
           <tr style="height:25px" align="left">
           <td style="width:70%">            
           </td>
           <td style="width:30%" align="center">
           PREPARED & VERIFIED BY:
           </td>
        </tr>
   
        <br>
       <tr style="height:25px" align="center">
           <td style="width:70%">            
           </td>
           <td style="width:30%; border-bottom: 1px solid black" align="center">
           ' . $infosig->{'na_emp_sig'} . '
           </td>                            
       </tr>
   
       <tr style="height:25px" align="center">
               <td style="width:70%">            
               </td>
               <td style="width:30%" align="center">
               ' . $infosig->{'na_sig_type'} . '
               </td>                            
           </tr>
           
            <br>
            <br>
            <tr style="height:25px">   
                <td style="width:20%"  align="left">
                  Certification Fee:
                </td> 
                <td style="width:20% ; border-bottom: 1px solid black" align="left">
                ' . $info->{'Certification Fee'} . '
                </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                OR No.:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR No'} . '
               </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                Date:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR Date'} . '
               </td>                  
            </tr>
   
            <tr style="height:25px">   
               <td style="width:40%"  align="left">
                Minglanilla, Cebu, Philippines
               </td>                   
            </tr>
            
            <br>
            <tr style="line-height:40px">
               <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF NO REVISION</i></span>
               </td>
               <br>
               <br>
               <td style="width:100%; border-bottom: 1px solid black" align="center">
               </td>
            </tr> 
        </table>
         ';
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

    public function displayDataLandHistory()
    {
        $type = '%Certification of Land History%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function filterDataLandHistory(Request $request)
    {
        $type = '%Certification of Land History%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainLandHistory(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of Land History List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function editDataLandHistory($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        // dd($data['main']);
        return response()->json(new JsonResponse($data));
    }

    public function landHistoryCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification of Land History%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        $tdid = DB::table($this->lgu_db . '.ecao_certification_trans')->select('ecao_certification_trans.taxdec_id')->where('id', $ID)->first();

        $tddata = DB::table($this->lgu_db . '.ecao_tax_dec_profile')
            ->join($this->lgu_db . '.ecao_faas_profile', 'ecao_faas_profile.faas_id', '=', 'ecao_tax_dec_profile.faas_id')
            ->join($this->lgu_db . '.ecao_certification_trans', 'ecao_certification_trans.taxdec_id', '=', 'ecao_tax_dec_profile.td_id')
            ->select($this->lgu_db . '.ecao_tax_dec_profile.td_no', 'ecao_tax_dec_profile.td_prop_owner', 'ecao_faas_profile.property_address_brgy', 'ecao_faas_profile.lot_no', 'ecao_faas_profile.total_area_landOrBuilding', 'ecao_faas_profile.year', 'ecao_certification_trans.pin_number')
            ->where('ecao_tax_dec_profile.td_id', $tdid->taxdec_id)
            ->where('ecao_certification_trans.id', $ID)
            ->get();
        // foreach($tddata as $rowData) { 
        //  $infotd = ($rowData);
        //  }
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
              
        <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
   <br>
   <br>
   <h2 align="center"> CERTIFICATION </h2>
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
         <br>
         <br>
            <tr style="height:25px">   
               <td style="width:100%">
               TO WHOM IT MAY CONCERN :
               </td>                  
            </tr>
                
             <br>            
             <tr style="height:25px">
                <td style="width:5%">               
                </td>    
                <td style="width:45%">
                This is to certify that according to our office record;
                </td>
                <td style="width:30%; border-bottom: 1px solid black" align="center">                            
                ' . $info->{'Property Owner'} . '
               </td>
               <td style="width:20%">
                has property declared
                </td>                   
             </tr>

             <tr style="height:25px">   
                <td style="width:100%">
                in his/her/their names for taxation purposes as follows;
                </td>                   
             </tr>
             </table>
             <br>
             <br>
        <table border="1">
        <thead>            
        <tr>
            <th style="width:10%" class="caption-label-center"><br><br>TD No.<br></th>      
            <th style="width:20%" class="caption-label-center"><br><br>Declared Owner<br></th>        
            <th style="width:10%" class="caption-label-center"><br><br>Location<br></th>
            <th style="width:10%" class="caption-label-center"><br><br>Lot No.<br></th>
            <th style="width:10%" class="caption-label-center"><br><br>Area<br></th>
            <th style="width:15%" class="caption-label-center"><br><br>PIN<br></th>
            <th style="width:15%" class="caption-label-center"><br><br>Conveyance<br></th>
            <th style="width:10%" class="caption-label-center"><br><br>Effectivity<br></th>
        </tr>   
        </thead>
        <tbody>';
            foreach ($tddata as $row) {
                // dd($row);        
                $html_content .= '        
            <tr>         
                <td align="center" style="width:10%">' . $row->td_no . '</td>
                <td align="center" style="width:20%">' . $row->td_prop_owner . '</td>
                <td align="center" style="width:10%">' . $row->property_address_brgy . '</td>
                <td align="center" style="width:10%">' . $row->lot_no . '</td>
                <td align="center" style="width:10%">' . $row->total_area_landOrBuilding . '</td>
                <td align="center" style="width:15%">' . $row->pin_number . '</td>
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:10%">' . $row->year . '</td>      
            </tr>';
            }
            for ($x = 0; $x <= 6; $x++) {
                $html_content .= '        
            <tr>         
                <td align="center" style="width:10%"></td>
                <td align="center" style="width:20%"></td>
                <td align="center" style="width:10%"></td>
                <td align="center" style="width:10%"></td>
                <td align="center" style="width:10%"></td>
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:10%"></td>       
            </tr>';
            }
            $html_content .= '
        </tbody>      
        </table>
        <br>
        <br>
             <table width ="100%">        
             <br>
             <tr style="height:25px">
                <td style="width:5%">               
                </td>   
                <td style="width:12%" align="left">
                Given this
                </td>    
                <td style="width:5%; border-bottom: 1px solid black" align="center">                            
                ' . date("d") . '
                </td>
                <td style="width:10%">
                day of
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                ' . date("M") . '
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                ' . date("Y") . '
                </td>
                <td style="width:5%">
                at
                </td>
                <td style="width:15%; border-bottom: 1px solid black" align="center">                            
                Minglanilla
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                Cebu
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:12%" align="right">
                Philippines
                </td>
                </tr>

             <tr style="height:25px">
                <td style="width:20%">
                upon the request of
                </td>
                 <td style="width:30%; border-bottom: 1px solid black" align="center">
                 ' . $info->{'Applicant'} . '
                 </td>
                 <td style="width:50%"  align="left">
                  for any legal purpose may serve.
                 </td>                 
             </tr>

             <br>
           <br>
           <tr style="height:25px" align="left">
           <td style="width:70%">            
           </td>
           <td style="width:30%" align="center">
           PREPARED & VERIFIED BY:
           </td>
        </tr>
   
        <br>
       <tr style="height:25px" align="center">
           <td style="width:70%">            
           </td>
           <td style="width:30%; border-bottom: 1px solid black" align="center">
           ' . $infosig->{'na_emp_sig'} . '
           </td>                            
       </tr>
   
       <tr style="height:25px" align="center">
               <td style="width:70%">            
               </td>
               <td style="width:30%" align="center">
               ' . $infosig->{'na_sig_type'} . '
               </td>                            
           </tr>
           
            <br>
            <tr style="height:25px">   
                <td style="width:20%"  align="left">
                  Certification Fee:
                </td> 
                <td style="width:20% ; border-bottom: 1px solid black" align="left">
                ' . $info->{'Certification Fee'} . '
                </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                OR No.:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR No'} . '
               </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                Date:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR Date'} . '
               </td>                  
            </tr>
   
            <tr style="height:25px">   
               <td style="width:40%"  align="left">
                Minglanilla, Cebu, Philippines
               </td>                   
            </tr>
             
             <br>
            <tr style="line-height:40px">
               <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF LAND HISTORY</i></span>
               </td>
               <br>
               <br>
               <td style="width:100%; border-bottom: 1px solid black" align="center">
               </td>
            </tr>
    </table>
      ';
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

    // Zero Assessment
    public function displayDataZeroAssessment()
    {
        $type = '%Certification of Zero Assessment%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainZeroAssessment(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of Zero Assessment List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function filterDataZeroAssessment(Request $request)
    {
        $type = '%Certification of Zero Assessment%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function editDataZeroAssessment($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        $data['details'] = DB::select('call ' . $this->lgu_db . '.ecao_sp_display_ecao_certification_trans_detail_id(?)', array($id));

        //dd($data['details']);
        //dd($data);
        return response()->json(new JsonResponse($data));
    }

    public function zeroAssessmentCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification of Zero Assessment%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        $tdid = DB::table($this->lgu_db . '.ecao_certification_trans')->select('ecao_certification_trans.taxdec_id')->where('id', $ID)->first();

        $tddata = DB::table($this->lgu_db . '.ecao_tax_dec_profile')
            ->join($this->lgu_db . '.ecao_faas_profile', 'ecao_faas_profile.faas_id', '=', 'ecao_tax_dec_profile.faas_id')
            ->join($this->lgu_db . '.ecao_certification_trans', 'ecao_certification_trans.taxdec_id', '=', 'ecao_tax_dec_profile.td_id')
            ->select($this->lgu_db . '.ecao_tax_dec_profile.td_no', 'ecao_tax_dec_profile.td_prop_owner', 'ecao_faas_profile.property_address_brgy', 'ecao_faas_profile.property_classification', 'ecao_faas_profile.total_area_landOrBuilding', 'ecao_faas_profile.tot_assessed_value', 'ecao_faas_profile.year')
            ->where('ecao_tax_dec_profile.td_id', $tdid->taxdec_id)
            ->where('ecao_certification_trans.id', $ID)
            ->get();
        // foreach($tddata as $rowData) { 
        //  $infotd = ($rowData);
        //  }
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
              
        <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
   <br>
   <br>
   <h2 align="center"> CERTIFICATION </h2>
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
         <br>
            <tr style="height:25px">   
               <td style="width:100%">
               TO WHOM IT MAY CONCERN :
               </td>                  
            </tr>
                
             <br>            
             <tr style="height:25px">
                <td style="width:5%">               
                </td>    
                <td style="width:45%">
                This is to certify that according to our office record;
                </td>
                <td style="width:30%; border-bottom: 1px solid black" align="center">                            
                ' . $info->{'Property Owner'} . '
               </td>
               <td style="width:20%">
                has property declared
                </td>                   
             </tr>

             <tr style="height:25px">   
                <td style="width:100%">
                in his/her/their names for taxation purposes as follows;
                </td>                   
             </tr>
             </table>
             <br>
             <br>
        <table border="1">
        <thead>            
        <tr>
            <th style="width:15%" class="caption-label-center"><br><br>TD No.<br></th>      
            <th style="width:25%" class="caption-label-center"><br><br>Declared Owner<br></th>        
            <th style="width:10%" class="caption-label-center"><br><br>Location<br></th>
            <th style="width:15%" class="caption-label-center"><br><br>Kind<br></th>
            <th style="width:10%" class="caption-label-center"><br><br>Area<br></th>
            <th style="width:15%" class="caption-label-center"><br><br>Assessed Value<br></th>
            <th style="width:10%" class="caption-label-center"><br><br>Effectivity<br></th>
        </tr>   
        </thead>
        <tbody>';
            foreach ($tddata as $row) {
                // dd($row);        
                $html_content .= '        
            <tr>         
                <td align="center" style="width:15%">' . $row->td_no . '</td>
                <td align="center" style="width:25%">' . $row->td_prop_owner . '</td>
                <td align="center" style="width:10%">' . $row->property_address_brgy . '</td>
                <td align="center" style="width:15%">' . $row->property_classification . '</td>
                <td align="center" style="width:10%">' . $row->total_area_landOrBuilding . '</td>
                <td align="center" style="width:15%">' . $row->tot_assessed_value . '</td>
                <td align="center" style="width:10%">' . $row->year . '</td>      
            </tr>';
            }
            for ($x = 0; $x <= 6; $x++) {
                $html_content .= '        
            <tr>         
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:25%"></td>
                <td align="center" style="width:10%"></td>
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:10%"></td>
                <td align="right" style="width:15%"></td>
                <td align="center" style="width:10%"></td>       
            </tr>';
            }
            $html_content .= '
        </tbody>      
        </table>
        <br>
        <br>
             <table width ="100%">        
             <br>
             <tr style="height:25px">
                <td style="width:5%">               
                </td>   
                <td style="width:95%" align="left">
                This is to certify further that the aforesaid property has zero assessed value and is not taxable pursuant to Sec. 218 (b) of
                </td>
             </tr>
                
            <tr style="height:25px">
                <td style="width:100%"  align="left">
                RA 7160.
                 </td>
             </tr>
             <br>
             <tr style="height:25px">
                <td style="width:5%">               
                </td>   
                <td style="width:12%" align="left">
                Given this
                </td>    
                <td style="width:5%; border-bottom: 1px solid black" align="center">                            
                ' . date("d") . '
                </td>
                <td style="width:10%">
                day of
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                ' . date("M") . '
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                ' . date("Y") . '
                </td>
                <td style="width:5%">
                at
                </td>
                <td style="width:15%; border-bottom: 1px solid black" align="center">                            
                Minglanilla
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                Cebu
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:12%" align="right">
                Philippines
                </td>
                </tr>

             <tr style="height:25px">
                <td style="width:20%">
                upon the request of
                </td>
                 <td style="width:30%; border-bottom: 1px solid black" align="center">
                 ' . $info->{'Applicant'} . '
                 </td>
                 <td style="width:50%"  align="left">
                  for any legal purpose may serve.
                 </td>                 
             </tr>

             <br>
             <tr style="height:25px" align="left">
                 <td style="width:70%">            
                 </td>
                 <td style="width:30%" align="center">
                 PREPARED & VERIFIED BY:
                 </td>
             </tr>
   
             <br>
             <tr style="height:25px" align="center">
                <td style="width:70%">            
                </td>
                <td style="width:30%; border-bottom: 1px solid black" align="center">
                ' . $infosig->{'na_emp_sig'} . '
                </td>                            
             </tr>
   
             <tr style="height:25px" align="center">
               <td style="width:70%">            
               </td>
               <td style="width:30%" align="center">
               ' . $infosig->{'na_sig_type'} . '
               </td>                            
             </tr>
           
             <br>
             <tr style="height:25px">   
                <td style="width:20%"  align="left">
                Certification Fee:
                </td> 
                <td style="width:20% ; border-bottom: 1px solid black" align="left">
                ' . $info->{'Certification Fee'} . '
                </td>                  
             </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                OR No.:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR No'} . '
               </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                Date:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR Date'} . '
               </td>                  
            </tr>
   
            <tr style="height:25px">   
               <td style="width:40%"  align="left">
                Minglanilla, Cebu, Philippines
               </td>                   
            </tr>
             
             <br>
            <tr style="line-height:40px">
               <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF ZERO ASSESSMENT</i></span>
               </td>
               <br>
               <td style="width:100%; border-bottom: 1px solid black" align="center">
               </td>
            </tr>
    </table>
      ';
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

    public function storeAdd(Request $request)
    {
        try {
            //DB::beginTransaction();
            //dd($request->details);
            //dd($request);
            $id = $request->main['id'];
            $main = $request->main;
            $cto = $request->cto;
            $details = $request->details;
            //dd($id);
            //dd($details);
            if ($id > 0) {
                $this->updateAdd($id, $main, $details);
            } else {
                // dd($main);
                $this->saveAdd($main, $cto, $details);
            }
            //DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            //DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    //public function saveAdd(Request $request) {
    public function saveAdd($main, $cto, $details)
    {
        // dd($request->main);
        // $row = $request->main; 
        // $details = $request->details;
        // $cto = $request->cto;
        //dd($details);
        $rowData = $main;
        //dd($rowData);
        $data = array(
            'cert_no'  => $rowData['cert_no'],
            'series_no'  => $rowData['series_no'],
            'app_date'  => $rowData['app_date'],
            'payee_id'  => $rowData['payee_id'],
            'payee_type' => $rowData['payee_type'],
            'prop_owner'  => $rowData['prop_owner'],
            'prop_name' => $rowData['prop_name'],
            'prop_address' => $rowData['prop_address'],
            'taxdec_id' => $rowData['taxdec_id'],
            'taxdec_no' => $rowData['taxdec_no'],
            'pin_number' => $rowData['pin_number'],
            'prop_type' => $rowData['prop_type'],
            'brgy_address' => $rowData['brgy_address'],
            'cert_id'  => $rowData['cert_id'],
            'res_purpose' => $rowData['res_purpose'],
            'status' => $rowData['status'],
            'businessapplicant' => $rowData['businessapplicant'],
            'businessapplicant_id' => $rowData['businessapplicant_id'],
            'classification_type' => $rowData['classification_type'],
            'businessname' => $rowData['businessname'],
        );
        // dd($data);
        DB::table($this->lgu_db . '.ecao_certification_trans')->insert($data);
        $id = DB::getPdo()->lastInsertId();
        // add to list
        foreach ($details as $row) {
            $array = array(
                'id' => $id,
                'td_id' => $row['TD ID'],
                'arp_no' => $row['TAX DEC'],
                'property_address' => $row['property_address_brgy'],
                'lot_no' => $row['LOT NO'],
                'title_no' => $row['octtctcloa_no'],
                'land_area' => $row['total_area_landOrBuilding'],
                'assessed_value' => $row['assessed_value'],
            );
            DB::table($this->lgu_db . '.ecao_certification_trans_detail')->insert($array);
        }

        if ($rowData['payee_type'] == 'Person') {
            $payer_id = $rowData['payee_id'];
        } else {
            $payer_id = $rowData['businessapplicant'];
        }

        foreach ($cto as $row) {
            //dd($row);
            if ($row['Include'] == true) {
                // dd($row);
                $array = array(
                    'payer_type' => $rowData['payee_type'],
                    'payer_id' => $payer_id,
                    'business_application_id' => $rowData['businessapplicant_id'],
                    'account_code' => $row['Account Code'],
                    'bill_description' => $row['Account Description'],
                    'net_amount' => $row['Initial Amount'],
                    'bill_amount' => $row['Fee Amount'],
                    'bill_month' => $rowData['app_date'],
                    'bill_number' => $rowData['cert_no'],
                    'transaction_type' => $rowData['classification_type'],
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'include_from' => 'Others',
                );
                // dd($array);
                DB::table($this->lgu_db . '.cto_general_billing')->insert($array);
            }
        }

        $signatory = DB::select('Call ' . $this->lgu_db . '.cvl_get_signatory_mayor_head()');
        foreach ($signatory as $row) {
            $sign = array(
                'na_no' => $id,
                'na_emp_id' => $row->assessor_id,
                'na_emp_sig' => $row->assessor_name,
                'na_sig_type' => $row->assessor_pos,
                'na_sig_date' => Auth::user()->id,
                'na_pp_id' => $payer_id,
                'na_mayor_id' => $row->mayor_id,
                'na_mayor_name' => $row->mayor_name,
                'na_mayor_sig' => $row->mayor_pos,
                'na_user_id' => Auth::user()->id,
            );
            // dd($this->lgu_db.'.signatory_logs');
            // dd($sign);
            DB::table($this->lgu_db . '.ecao_certification_sig')->insert($sign);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function updateAdd($id, $main, $details)
    {   // dd($request->id);
        //$main = $request->main;
        // $details = $request->details;
        //$id = $request->id;
        DB::table($this->lgu_db . '.ecao_certification_trans')
            ->where('id', $id)
            ->update([
                'cert_no'  => $main['cert_no'],
                'series_no'  => $main['series_no'],
                'app_date'  => $main['app_date'],
                'payee_id'  => $main['payee_id'],
                'payee_type' => $main['payee_type'],
                'prop_owner'  => $main['prop_owner'],
                'prop_name' => $main['prop_name'],
                'prop_address' => $main['prop_address'],
                'taxdec_id' => $main['taxdec_id'],
                'taxdec_no' => $main['taxdec_no'],
                'pin_number' => $main['pin_number'],
                'prop_type' => $main['prop_type'],
                'cert_id'  => $main['cert_id'],
                'res_purpose' => $main['res_purpose'],
                'status' => $main['status'],
                'businessapplicant' => $main['businessapplicant'],
                'classification_type' => $main['classification_type'],
                'businessname' => $main['businessname'],
            ]);

        DB::table($this->lgu_db . '.ecao_certification_trans_detail')->where('id', $id)->delete();
        foreach ($details as $row) {
            $array = array(
                'id' => $id,
                'td_id' => $row['TD ID'],
                'arp_no' => $row['TAX DEC'],
                'property_address' => $row['property_address_brgy'],
                'lot_no' => $row['LOT NO'],
                'title_no' => $row['octtctcloa_no'],
                'land_area' => $row['total_area_landOrBuilding'],
                'assessed_value' => $row['assessed_value'],
            );
            DB::table($this->lgu_db . '.ecao_certification_trans_detail')->insert($array);
        }

        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function displayDataPropertyHoldings()
    {
        $type = '%Certification of Property Holdings%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function filterDataPropertyHoldings(Request $request)
    {
        $type = '%Certification of Property Holdings%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainPropertyHoldings(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of Property Holdings List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function editDataPropertyHoldings($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        $data['details'] = DB::select('call ' . $this->lgu_db . '.ecao_sp_display_ecao_certification_trans_detail_id(?)', array($id));

        //dd($data['details']);
        //dd($data);
        return response()->json(new JsonResponse($data));
    }

    public function propertyHoldingsCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification of Property Holdings%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        $tdid = DB::table($this->lgu_db . '.ecao_certification_trans')->select('ecao_certification_trans.taxdec_id')->where('id', $ID)->first();

        $tddata = DB::table($this->lgu_db . '.ecao_tax_dec_profile')
            ->join($this->lgu_db . '.ecao_faas_profile', 'ecao_faas_profile.faas_id', '=', 'ecao_tax_dec_profile.faas_id')
            ->join($this->lgu_db . '.ecao_certification_trans', 'ecao_certification_trans.taxdec_id', '=', 'ecao_tax_dec_profile.td_id')
            ->select($this->lgu_db . '.ecao_tax_dec_profile.td_no', 'ecao_tax_dec_profile.td_prop_owner', 'ecao_faas_profile.property_address_brgy', 'ecao_faas_profile.property_classification', 'ecao_faas_profile.total_area_landOrBuilding', 'ecao_faas_profile.tot_assessed_value', 'ecao_faas_profile.year')
            ->where('ecao_tax_dec_profile.td_id', $tdid->taxdec_id)
            ->where('ecao_certification_trans.id', $ID)
            ->get();
        // foreach($tddata as $rowData) { 
        //  $infotd = ($rowData);
        //  }
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
           ' . $logo . '
                 
           <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
      <br>
      <br>
      <h2 align="center"> CERTIFICATION </h2>
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
            <br>
               <tr style="height:25px">   
                  <td style="width:100%">
                  TO WHOM IT MAY CONCERN :
                  </td>                  
               </tr>
                   
                <br>            
                <tr style="height:25px">
                   <td style="width:5%">               
                   </td>    
                   <td style="width:45%">
                   This is to certify that according to our office record;
                   </td>
                   <td style="width:30%; border-bottom: 1px solid black" align="center">                            
                   ' . $info->{'Property Owner'} . '
                  </td>
                  <td style="width:20%">
                   has property declared
                   </td>                   
                </tr>
   
                <tr style="height:25px">   
                   <td style="width:100%">
                   in his/her/their names for taxation purposes as follows;
                   </td>                   
                </tr>
                </table>
                <br>
                <br>
           <table border="1">
           <thead>            
           <tr>
               <th style="width:15%" class="caption-label-center"><br><br>TD No.<br></th>      
               <th style="width:25%" class="caption-label-center"><br><br>Declared Owner<br></th>        
               <th style="width:10%" class="caption-label-center"><br><br>Location<br></th>
               <th style="width:15%" class="caption-label-center"><br><br>Kind<br></th>
               <th style="width:10%" class="caption-label-center"><br><br>Area<br></th>
               <th style="width:15%" class="caption-label-center"><br><br>Assessed Value<br></th>
               <th style="width:10%" class="caption-label-center"><br><br>Effectivity<br></th>
           </tr>   
           </thead>
           <tbody>';
            foreach ($tddata as $row) {
                // dd($row);        
                $html_content .= '        
               <tr>         
                   <td align="center" style="width:15%">' . $row->td_no . '</td>
                   <td align="center" style="width:25%">' . $row->td_prop_owner . '</td>
                   <td align="center" style="width:10%">' . $row->property_address_brgy . '</td>
                   <td align="center" style="width:15%">' . $row->property_classification . '</td>
                   <td align="center" style="width:10%">' . $row->total_area_landOrBuilding . '</td>
                   <td align="center" style="width:15%">' . $row->tot_assessed_value . '</td>
                   <td align="center" style="width:10%">' . $row->year . '</td>      
               </tr>';
            }
            for ($x = 0; $x <= 6; $x++) {
                $html_content .= '        
               <tr>         
                   <td align="center" style="width:15%"></td>
                   <td align="center" style="width:25%"></td>
                   <td align="center" style="width:10%"></td>
                   <td align="center" style="width:15%"></td>
                   <td align="center" style="width:10%"></td>
                   <td align="right" style="width:15%"></td>
                   <td align="center" style="width:10%"></td>       
               </tr>';
            }
            $html_content .= '
           </tbody>      
           </table>
           <br>
           <br>
                <table width ="100%">        
                
                <br>
                <tr style="height:25px">
                   <td style="width:5%">               
                   </td>   
                   <td style="width:28%" align="left">
                   This certification is issued this
                   </td>    
                   <td style="width:5%; border-bottom: 1px solid black" align="center">                            
                   ' . date("d") . '
                   </td>
                   <td style="width:10%">
                   day of
                   </td>
                   <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                   ' . date("M") . '
                   </td>
                   <td style="width:3%">
                   ,
                   </td>
                   <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                   ' . date("Y") . '
                   </td>
                   <td style="width:5%">
                   at
                   </td>
                   <td style="width:15%; border-bottom: 1px solid black" align="center">                            
                   Minglanilla
                   </td>
                   <td style="width:3%">
                   ,
                   </td>
                   <td style="width:8%; border-bottom: 1px solid black" align="center">                            
                   Cebu
                   </td>
                   <td style="width:3%">
                   ,
                   </td>
                   </tr>
   
                <tr style="height:25px">
                   <td style="width:12%" align="right">
                   Philippines
                   </td>
                   <td style="width:20%">
                   upon the request of
                   </td>
                    <td style="width:30%; border-bottom: 1px solid black" align="center">
                    ' . $info->{'Applicant'} . '
                    </td>
                    <td style="width:50%"  align="left">
                     for any legal purpose may serve.
                    </td>                 
                </tr>
   
                <br>
                <br>
                <tr style="height:25px" align="left">
                    <td style="width:70%">            
                    </td>
                    <td style="width:30%" align="center">
                    PREPARED & VERIFIED BY:
                    </td>
                </tr>
      
                <br>
                <tr style="height:25px" align="center">
                   <td style="width:70%">            
                   </td>
                   <td style="width:30%; border-bottom: 1px solid black" align="center">
                   ' . $infosig->{'na_emp_sig'} . '
                   </td>                            
                </tr>
      
                <tr style="height:25px" align="center">
                  <td style="width:70%">            
                  </td>
                  <td style="width:30%" align="center">
                  ' . $infosig->{'na_sig_type'} . '
                  </td>                            
                </tr>
              
                <br>
                <tr style="height:25px">   
                   <td style="width:20%"  align="left">
                   Certification Fee:
                   </td> 
                   <td style="width:20% ; border-bottom: 1px solid black" align="left">
                   ' . $info->{'Certification Fee'} . '
                   </td>                  
                </tr> 
      
               <tr style="height:25px">   
                  <td style="width:20%"  align="left">
                   OR No.:
                  </td> 
                  <td style="width:20%; border-bottom: 1px solid black" align="left">
                  ' . $info->{'OR No'} . '
                  </td>                  
               </tr> 
      
               <tr style="height:25px">   
                  <td style="width:20%"  align="left">
                   Date:
                  </td> 
                  <td style="width:20%; border-bottom: 1px solid black" align="left">
                  ' . $info->{'OR Date'} . '
                  </td>                  
               </tr>
      
               <tr style="height:25px">   
                  <td style="width:40%"  align="left">
                   Minglanilla, Cebu, Philippines
                  </td>                   
               </tr>
                
                <br>
               <tr style="line-height:40px">
                  <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF PROPERTY HOLDINGS</i></span>
                  </td>
                  <br>
                  <td style="width:100%; border-bottom: 1px solid black" align="center">
                  </td>
               </tr>
       </table>
         ';
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

    public function displayDataNoImprovement()
    {
        $type = '%Certification of No Improvement%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function filterDataNoImprovement(Request $request)
    {
        $type = '%Certification of No Improvement%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainNoImprovement(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of No Improvement List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function editDataNoImprovement($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        $data['details'] = DB::select('call ' . $this->lgu_db . '.ecao_sp_display_ecao_certification_trans_detail_id(?)', array($id));

        //dd($data['details']);
        //dd($data);
        return response()->json(new JsonResponse($data));
    }

    public function noImprovementCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification of No Improvement%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        $tdid = DB::table($this->lgu_db . '.ecao_certification_trans')->select('ecao_certification_trans.taxdec_id')->where('id', $ID)->first();

        $tddata = DB::table($this->lgu_db . '.ecao_tax_dec_profile')
            ->join($this->lgu_db . '.ecao_faas_profile', 'ecao_faas_profile.faas_id', '=', 'ecao_tax_dec_profile.faas_id')
            ->join($this->lgu_db . '.ecao_certification_trans', 'ecao_certification_trans.taxdec_id', '=', 'ecao_tax_dec_profile.td_id')
            ->select($this->lgu_db . '.ecao_tax_dec_profile.td_no', 'ecao_tax_dec_profile.td_prop_owner', 'ecao_faas_profile.property_address_brgy', 'ecao_faas_profile.property_classification', 'ecao_faas_profile.total_area_landOrBuilding', 'ecao_faas_profile.tot_assessed_value', 'ecao_faas_profile.year')
            ->where('ecao_tax_dec_profile.td_id', $tdid->taxdec_id)
            ->where('ecao_certification_trans.id', $ID)
            ->get();
        // foreach($tddata as $rowData) { 
        //  $infotd = ($rowData);
        //  }
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
              
        <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
   <br>
   <br>
   <h2 align="center"> CERTIFICATION </h2>
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
         <br>
            <tr style="height:25px">   
               <td style="width:100%">
               TO WHOM IT MAY CONCERN :
               </td>                  
            </tr>
                
             <br>            
             <tr style="height:25px">
                <td style="width:5%">               
                </td>    
                <td style="width:45%">
                This is to certify that according to our office record;
                </td>
                <td style="width:30%; border-bottom: 1px solid black" align="center">                            
                ' . $info->{'Property Owner'} . '
               </td>
               <td style="width:20%">
                has property declared
                </td>                   
             </tr>

             <tr style="height:25px">   
                <td style="width:100%">
                in his/her/their names for taxation purposes as follows;
                </td>                   
             </tr>
             </table>
             <br>
             <br>
        <table border="1">
        <thead>            
        <tr>
            <th style="width:15%" class="caption-label-center"><br><br>TD No.<br></th>      
            <th style="width:25%" class="caption-label-center"><br><br>Declared Owner<br></th>        
            <th style="width:10%" class="caption-label-center"><br><br>Location<br></th>
            <th style="width:15%" class="caption-label-center"><br><br>Kind<br></th>
            <th style="width:10%" class="caption-label-center"><br><br>Area<br></th>
            <th style="width:15%" class="caption-label-center"><br><br>Assessed Value<br></th>
            <th style="width:10%" class="caption-label-center"><br><br>Effectivity<br></th>
        </tr>   
        </thead>
        <tbody>';
            foreach ($tddata as $row) {
                // dd($row);        
                $html_content .= '        
            <tr>         
                <td align="center" style="width:15%">' . $row->td_no . '</td>
                <td align="center" style="width:25%">' . $row->td_prop_owner . '</td>
                <td align="center" style="width:10%">' . $row->property_address_brgy . '</td>
                <td align="center" style="width:15%">' . $row->property_classification . '</td>
                <td align="center" style="width:10%">' . $row->total_area_landOrBuilding . '</td>
                <td align="center" style="width:15%">' . $row->tot_assessed_value . '</td>
                <td align="center" style="width:10%">' . $row->year . '</td>      
            </tr>';
            }
            for ($x = 0; $x <= 6; $x++) {
                $html_content .= '        
            <tr>         
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:25%"></td>
                <td align="center" style="width:10%"></td>
                <td align="center" style="width:15%"></td>
                <td align="center" style="width:10%"></td>
                <td align="right" style="width:15%"></td>
                <td align="center" style="width:10%"></td>       
            </tr>';
            }
            $html_content .= '
        </tbody>      
        </table>
        <br>
        <br>
             <table width ="100%">        
             <br>
             <tr style="height:25px">
                <td style="width:5%">               
                </td>   
                <td style="width:95%" align="left">
                This is to certify further that the aforesaid property has zero assessed value and is not taxable pursuant to Sec. 218 (b) of
                </td>
             </tr>
                
            <tr style="height:25px">
                <td style="width:100%"  align="left">
                RA 7160.
                 </td>
             </tr>
             <br>
             <tr style="height:25px">
                <td style="width:5%">               
                </td>   
                <td style="width:12%" align="left">
                Given this
                </td>    
                <td style="width:5%; border-bottom: 1px solid black" align="center">                            
                ' . date("d") . '
                </td>
                <td style="width:10%">
                day of
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                ' . date("M") . '
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                ' . date("Y") . '
                </td>
                <td style="width:5%">
                at
                </td>
                <td style="width:15%; border-bottom: 1px solid black" align="center">                            
                Minglanilla
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:10%; border-bottom: 1px solid black" align="center">                            
                Cebu
                </td>
                <td style="width:3%">
                ,
                </td>
                <td style="width:12%" align="right">
                Philippines
                </td>
                </tr>

             <tr style="height:25px">
                <td style="width:20%">
                upon the request of
                </td>
                 <td style="width:30%; border-bottom: 1px solid black" align="center">
                 ' . $info->{'Applicant'} . '
                 </td>
                 <td style="width:50%"  align="left">
                  for any legal purpose may serve.
                 </td>                 
             </tr>

             <br>
             <tr style="height:25px" align="left">
                 <td style="width:70%">            
                 </td>
                 <td style="width:30%" align="center">
                 PREPARED & VERIFIED BY:
                 </td>
             </tr>
   
             <br>
             <tr style="height:25px" align="center">
                <td style="width:70%">            
                </td>
                <td style="width:30%; border-bottom: 1px solid black" align="center">
                ' . $infosig->{'na_emp_sig'} . '
                </td>                            
             </tr>
   
             <tr style="height:25px" align="center">
               <td style="width:70%">            
               </td>
               <td style="width:30%" align="center">
               ' . $infosig->{'na_sig_type'} . '
               </td>                            
             </tr>
           
             <br>
             <tr style="height:25px">   
                <td style="width:20%"  align="left">
                Certification Fee:
                </td> 
                <td style="width:20% ; border-bottom: 1px solid black" align="left">
                ' . $info->{'Certification Fee'} . '
                </td>                  
             </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                OR No.:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR No'} . '
               </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                Date:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR Date'} . '
               </td>                  
            </tr>
   
            <tr style="height:25px">   
               <td style="width:40%"  align="left">
                Minglanilla, Cebu, Philippines
               </td>                   
            </tr>
             
             <br>
            <tr style="line-height:40px">
               <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF NO IMPROVEMENT</i></span>
               </td>
               <br>
               <td style="width:100%; border-bottom: 1px solid black" align="center">
               </td>
            </tr>
    </table>
      ';
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

    // Exempt Property
    public function displayDataExemptProperty()
    {
        $type = '%Certification of Exempt Property%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function filterDataExemptProperty(Request $request)
    {
        $type = '%Certification of Exempt Property%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainExemptProperty(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of Exempt Property List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function editDataExemptProperty($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        // dd($data['main']);
        return response()->json(new JsonResponse($data));
    }

    public function exemptPropertyCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $type = '%Certification of Exempt Property%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
           ' . $logo . '
                 
                 <h2 align="center"> OFFICE OF THE CITY ASSESSOR </h2>
   <br>
   <br>
   <h2 align="center"> CERTIFICATION </h2>
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
         <br>
         <br>
            <tr style="height:25px">   
               <td style="width:100%">
               TO WHOM IT MAY CONCERN :
               </td>                  
            </tr> 
          <br> 
            <tr style="height:25px" align="center">   
               <td style="width:5%"> 
               </td> 
               <td style="width:25%">
               This it to certify that a 
               </td>    
               <td style="width:30%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'prop_type'} . '
               </td>
               <td style="width:10%" align="right">   
               located at  
               </td> 
               <td style="width:30%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Property Address'} . '
               </td>                  
           </tr> 
   
           <tr style="height:25px" align="center">    
               <td style="width:25%" align="left">   
               declared in the name of  
               </td>
               <td style="width:30%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Property Owner'} . '
               </td>
               <td style="width:25%" align="left">   
               under Tax Declaration No.  
               </td>
               <td style="width:20%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Tax Declaration No'} . ' 
               </td>                      
            </tr>
   
            <tr style="height:25px" align="left">
               <td style="width:10%" align="left">   
               effective   
               </td>
               <td style="width:7%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'year'} . '
               </td>
               <td style="width:13%" align="right">   
               is used by a   
               </td>
               <td style="width:15%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Exempt'} . '
               </td>
               <td style="width:11%" align="right">   
               institution.   
               </td>
               <td style="width:44%" align="right">   
               Pursuant to Article 325(a) of R.A. 7160, otherwise    
               </td>   
           </tr>
   
           <tr style="height:25px">    
               <td style="width:100%" align="left">
               known as Local Government Code of 1991, All real properties owned by the Republic of the Philippines or any of its political               </td>                        
           </tr>
   
           <tr style="height:25px" align="center">    
               <td style="width:100%" align="left">
               subdivisions are exempted from payment of Real property Taxes.
               </td>                        
           </tr>
   
           <br>
           <tr style="height:25px" align="center">   
               <td style="width:5%"> 
               </td> 
               <td style="width:25%">
               This certification is issued to 
               </td>
               <td style="width:30%; border-bottom: 1px solid black" align="center">                            
               ' . $info->{'Property Owner'} . '
               </td>
               <td style="width:40%" align="left">
               for any legal purposes.
               </td>
           </tr>
           
           <br>
           <tr style="height:25px" align="center">   
               <td style="width:5%"> 
               </td> 
               <td style="width:10%" align="left">
               Done this
               </td>    
               <td style="width:7%; border-bottom: 1px solid black" align="center">                            
               ' . date("d") . '
               </td>
               <td style="width:10%">
               day of
               </td>
               <td style="width:10%; border-bottom: 1px solid black" align="center">                            
               ' . date("M") . '
               </td>
               <td style="width:3%">
               ,
               </td>
               <td style="width:10%; border-bottom: 1px solid black" align="center">                            
               ' . date("Y") . '
               </td>
               <td style="width:5%">
               at
               </td>
               <td style="width:15%; border-bottom: 1px solid black" align="center">                            
               Minglanilla
               </td>
               <td style="width:3%">
               ,
               </td>
               <td style="width:10%; border-bottom: 1px solid black" align="center">                            
               Cebu
               </td>
               <td style="width:12%" align="right">
               Philippines.
               </td>                    
           </tr>
   
           <br>
           <br>
           <tr style="height:25px" align="left">
           <td style="width:70%">            
           </td>
           <td style="width:30%" align="center">
           PREPARED & VERIFIED BY:
           </td>
        </tr>
   
        <br>
       <tr style="height:25px" align="center">
           <td style="width:70%">            
           </td>
           <td style="width:30%; border-bottom: 1px solid black" align="center">
           ' . $infosig->{'na_emp_sig'} . '
           </td>                            
       </tr>
   
       <tr style="height:25px" align="center">
               <td style="width:70%">            
               </td>
               <td style="width:30%" align="center">
               ' . $infosig->{'na_sig_type'} . '
               </td>                            
           </tr>
           
            <br>
            <br>
            <tr style="height:25px">   
                <td style="width:20%"  align="left">
                  Certification Fee:
                </td> 
                <td style="width:20% ; border-bottom: 1px solid black" align="left">
                ' . $info->{'Certification Fee'} . '
                </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                OR No.:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR No'} . '
               </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                Date:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="left">
               ' . $info->{'OR Date'} . '
               </td>                  
            </tr>
   
            <tr style="height:25px">   
               <td style="width:40%"  align="left">
                Minglanilla, Cebu, Philippines
               </td>                   
            </tr>
            
            <br>
            <br>
            <tr style="line-height:40px">
               <td style="width:100%" align="center"><span color = "red", font-style = ><i>CERTIFICATE OF EXEMPT PROPERTY (GOVERNMENT)</i></span>
               </td>
               <br>
               <br>
               <td style="width:100%; border-bottom: 1px solid black" align="center">
               </td>
            </tr> 
       </table>
         ';
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

    // Real Property Certification
    public function displayDataRealPropertyCert()
    {
        $type = '%Certification of Real Property%';
        $dateFr = '2020-01-01';
        $dateTo = '2020-12-01';
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function filterDataRealPropertyCert(Request $request)
    {
        $type = '%Certification of Real Property%';
        $dateFr =  $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay(?,?,?)', array($type, $dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMainRealPropertyCert(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Certification of Real Property List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Reference No.</th>
        <th width = "9%">Application Date</th>
        <th width = "9%">Applicant</th>
        <th width = "9%">Property Name</th>
        <th width = "9%">Property Type</th>
        <th width = "9%">Tax Declaration No</th>
        <th width = "9%">Reason/Purpose</th>
        <th width = "9%">Certification Fee</th>
        <th width = "9%">OR No.</th>
        <th width = "9%">OR Date</th>
        <th width = "9%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "9%">' . $main['Reference No'] . '</td>
            <td width = "9%">' . $main['Application Date'] . '</td>
            <td width = "9%">' . $main['Applicant'] . '</td>
            <td width = "9%">' . $main['Property Name'] . '</td>
            <td width = "9%">' . $main['Property Type'] . '</td>
            <td width = "9%">' . $main['Tax Declaration No'] . '</td>
            <td width = "9%">' . $main['Reason/Purpose'] . '</td>
            <td width = "9%">' . $main['Certification Fee'] . '</td>
            <td width = "9%">' . $main['OR No'] . '</td>
            <td width = "9%">' . $main['OR Date'] . '</td>
            <td width = "9%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function editDataRealPropertyCert($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.ecao_certification_trans')->where('id', $id)->get();
        // dd($data['main']);
        return response()->json(new JsonResponse($data));
    }

    public function realPropertyCertPrint($ID)
    {
        // $info = $request->id;
        // dd($ID);
        $kindmachinery = '';
        $no_of_storey = '';
        $structural_type = '';
        //    dd($this->G->numberTowords(1002302002012.00));
        $type = '%Certification of Real Property%';
        $data = DB::select('call ' . $this->lgu_db . '.mj_ecao_certification_trans_jay_id(?,?)', array($type, $ID));
        foreach ($data as $rowData) {
            $info = ($rowData);
        }

        $tdid = DB::table($this->lgu_db . '.ecao_certification_trans')->select('ecao_certification_trans.taxdec_id')->where('id', $ID)->first();
        //dd($tdid->taxdec_id);
        $faasid = DB::table($this->lgu_db . '.ecao_tax_dec_profile')->select('ecao_tax_dec_profile.faas_id')->where('td_id', $tdid->taxdec_id)->first();
        //dd($faasid->faas_id);
        if ($info->{'prop_type'} == "LAND") {
            $TransLand = '<span style="font-family:zapfdingbats;">4</span>';
        } else {
            $TransLand = '';
        };
        if ($info->{'prop_type'} == "BLDG") {
            $TransBuilding = '<span style="font-family:zapfdingbats;">4</span>';
            $buildtd = DB::table($this->lgu_db . '.ecao_faas_bldg')
                ->select($this->lgu_db . '.ecao_faas_bldg.no_of_storey', 'ecao_faas_bldg.structural_type')
                ->where('ecao_faas_bldg.faas_id', $faasid->faas_id)
                ->get();
            //dd($buildtd);
            foreach ($buildtd as $rowData) {
                $infobuild = ($rowData);
                $no_of_storey = $infobuild->{'no_of_storey'};
                $structural_type = $infobuild->{'structural_type'};
            }
        } else {
            $TransBuilding = '';
        };
        if ($info->{'prop_type'} == "MACHINERY") {
            $TransMachinery = '<span style="font-family:zapfdingbats;">4</span>';
            $machtd = DB::table($this->lgu_db . '.ecao_faas_mach')
                ->select($this->lgu_db . '.ecao_faas_mach.kindmachinery')
                ->where('ecao_faas_mach.faas_id', $faasid->faas_id)
                ->get();
            //dd($buildtd);
            foreach ($machtd as $rowData) {
                $infomach = ($rowData);
                $kindmachinery = $infomach->{'kindmachinery'};
            }
        } else {
            $TransMachinery = '';
        };
        if ($info->{'prop_type'} == "OTHERS") {
            $TransOthers = '<span style="font-family:zapfdingbats;">4</span>';
        } else {
            $TransOthers = '';
        };

        $sigdata = DB::table($this->lgu_db . '.ecao_certification_sig')->where('na_no', $ID)->get();
        //dd($tddtl); 
        foreach ($sigdata as $rowData) {
            $infosig = ($rowData);
        }
        //dd($infosig);



        $tddata = DB::table($this->lgu_db . '.ecao_tax_dec_profile')
            ->select(
                $this->lgu_db . '.ecao_tax_dec_profile.faas_id',
                'ecao_tax_dec_profile.tax_qrtr',
                'ecao_tax_dec_profile.tax_year',
                'ecao_tax_dec_profile.td_no',
                'ecao_tax_dec_profile.td_prop_owner',
                'ecao_tax_dec_profile.pin_prov',
                'ecao_tax_dec_profile.pin_city_mun',
                'ecao_tax_dec_profile.pin_brgy',
                'ecao_tax_dec_profile.pin_section',
                'ecao_tax_dec_profile.pin_parcel',
                'ecao_tax_dec_profile.pin_ext',
                'ecao_tax_dec_profile.sb_ord_no',
                'ecao_tax_dec_profile.tax_type'
            )
            ->where('ecao_tax_dec_profile.td_id', $tdid->taxdec_id)
            ->get();
        foreach ($tddata as $rowData) {
            $infotd = ($rowData);
        }
        if ($infotd->{'tax_type'} == "TAXABLE") {
            $TransTaxable = '<span style="font-family:zapfdingbats;">4</span>';
        } else {
            $TransTaxable = '';
        };
        if ($infotd->{'tax_type'} == "EXEMPT") {
            $TransExempt = '<span style="font-family:zapfdingbats;">4</span>';
        } else {
            $TransExempt = '';
        };
        //dd($tdid->taxdec_id);
        $prevtd = DB::table($this->lgu_db . '.ecao_tax_dec_prev_td_dtl')
            ->select(
                $this->lgu_db . '.ecao_tax_dec_prev_td_dtl.prev_td_id',
                'ecao_tax_dec_prev_td_dtl.prev_td_no',
                'ecao_tax_dec_prev_td_dtl.prev_td_owner',
                'ecao_tax_dec_prev_td_dtl.prev_td_ass_val'
            )
            ->where('ecao_tax_dec_prev_td_dtl.td_id', $tdid->taxdec_id)
            ->get();
        $prev_td_no = '';
        $prev_td_owner = '';
        $prev_td_ass_val = '';
        //dd($prevtd);
        foreach ($prevtd as $rowData) {
            $infoprev = ($rowData);
            $prev_td_no = $infoprev->{'prev_td_no'};
            $prev_td_owner = $infoprev->{'prev_td_owner'};
            $prev_td_ass_val = $infoprev->{'prev_td_ass_val'};
        }

        $faasdata = DB::table($this->lgu_db . '.ecao_faas_profile')
            ->select(
                $this->lgu_db . '.ecao_faas_profile.faas_id',
                'ecao_faas_profile.north',
                'ecao_faas_profile.south',
                'ecao_faas_profile.east',
                'ecao_faas_profile.west',
                'ecao_faas_profile.owner_tin',
                'ecao_faas_profile.date_approved',
                'ecao_faas_profile.owner_address_brgy',
                'ecao_faas_profile.owner_tel_no',
                'ecao_faas_profile.beneficial_name',
                'ecao_faas_profile.beneficial_tin',
                'ecao_faas_profile.beneficial_address_brgy',
                'ecao_faas_profile.beneficial_tel_no',
                'ecao_faas_profile.octtctcloa_no',
                'ecao_faas_profile.survey_no',
                'ecao_faas_profile.lot_no',
                'ecao_faas_profile.blk_no',
                'ecao_faas_profile.oct_date',
                'ecao_faas_profile.memoranda',
                'ecao_faas_profile.property_address_brgy',
                'ecao_faas_profile.property_address_city_mun'
            )
            ->where('ecao_faas_profile.faas_id', $faasid->faas_id)
            ->get();
        foreach ($faasdata as $rowData) {
            $infofaas = ($rowData);
        }

        $tddtl = DB::table($this->lgu_db . '.ecao_faas_profile_dtl')
            ->select(
                $this->lgu_db . '.ecao_faas_profile_dtl.classification',
                'ecao_faas_profile_dtl.dtl_area',
                'ecao_faas_profile_dtl.market_value',
                'ecao_faas_profile_dtl.actual_use',
                'ecao_faas_profile_dtl.assessment_level',
                'ecao_faas_profile_dtl.assessed_value'
            )
            ->where('ecao_faas_profile_dtl.ecao_faas_id', $faasid->faas_id)
            ->get();

        //dd($info->{'app_date'});
        $logo = config('variable.logo');
        try {
            $html_content = '
           ' . $logo . '
                 
                 <h2 align="center"> TAX DECLARATION OF REAL PROPERTY </h2>
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
         <br>
         <br>
         <tr style="height:25px">   
         <td style="width:20%"  align="left">
           TD No.:
         </td> 
         <td style="width:30% ; border-bottom: 1px solid black" align="center">
         ' . $infotd->{'td_no'} . '
         </td>
         <td style="width:5%"  align="center"></td>
         <td style="width:20%"  align="center">
           Property Identification No.:
         </td> 
         <td style="width:25% ; border-bottom: 1px solid black" align="center">
         ' . $infotd->{'pin_prov'} . ' ' . $infotd->{'pin_city_mun'} . ' ' . $infotd->{'pin_brgy'} . ' ' . $infotd->{'pin_section'} . ' ' . $infotd->{'pin_parcel'} . ' ' . $infotd->{'pin_ext'} . '
         </td>                 
     </tr> 

     <tr style="height:25px">   
        <td style="width:20%"  align="left">
         Owner:
        </td> 
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        ' . $infotd->{'td_prop_owner'} . '
        </td>
        <td style="width:5%"  align="center"></td>
        <td style="width:20%"  align="left">
         TIN.:
        </td> 
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'owner_tin'} . '
        </td>                  
     </tr> 

     <tr style="height:25px">   
        <td style="width:20%"  align="left">
         Address:
        </td> 
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'owner_address_brgy'} . '
        </td>
        <td style="width:5%"  align="center"></td>
        <td style="width:20%"  align="left">
         Telephone No.:
        </td> 
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'owner_tel_no'} . '
        </td>                  
     </tr>

     <tr style="height:25px">   
        <td style="width:30%"  align="left">
         Administrator/Beneficial User:
        </td> 
        <td style="width:20% ; border-bottom: 1px solid black" align="left">
        ' . $infofaas->{'beneficial_name'} . '
        </td>
        <td style="width:5%"  align="center"></td>
        <td style="width:20%"  align="left">
         TIN.:
        </td> 
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'beneficial_tin'} . '
        </td>                 
     </tr> 
   
     <tr style="height:25px">   
        <td style="width:20%"  align="left">
         Address:
        </td> 
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'beneficial_address_brgy'} . '
        </td>
        <td style="width:5%"  align="center"></td>
        <td style="width:20%"  align="left">
         Telephone No.:
        </td> 
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'beneficial_tel_no'} . '
        </td>                  
    </tr> 
   
    <tr style="height:25px">   
        <td style="width:20%"  align="left">
            Location of Property:
        </td> 
        <td style="width:20%; border-bottom: 1px solid black" align="center">
        </td>
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'property_address_brgy'} . '
        </td>
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'property_address_city_mun'} . '
        </td>                  
    </tr>

    <tr style="height:25px" align="center">
           <td style="width:20%"  align="center"></td>
           <td style="width:20%"><span font-style = ><i>(Number and Street)</i></span>
           </td>
           <td style="width:30%"><span font-style = ><i>(Barangay/District)</i></span>
           </td>
           <td style="width:5%"  align="center"></td>
           <td style="width:20%"><span font-style = ><i>(Municipality/Province/City)</i></span>
           </td>                  
     </tr>
          
    <tr style="height:25px">   
        <td style="width:20%"  align="left">
         OCT/TCT/CLOA No.:
        </td> 
        <td style="width:30% ; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'octtctcloa_no'} . '
        </td>
        <td style="width:5%"  align="center"></td>
        <td style="width:20%"  align="left">
        Survey No.:
        </td> 
        <td style="width:25% ; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'survey_no'} . '
        </td>                 
    </tr> 
   
    <tr style="height:25px">   
        <td style="width:20%"  align="left">
        CCT:
        </td> 
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        </td>
        <td style="width:5%"  align="center"></td>
        <td style="width:20%"  align="left">
        Lot No.:
        </td> 
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'lot_no'} . '
        </td>                  
    </tr> 
   
    <tr style="height:25px">   
        <td style="width:20%"  align="left">
        Dated:
        </td> 
        <td style="width:30%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'oct_date'} . '
        </td>
        <td style="width:5%"  align="center"></td>
        <td style="width:20%"  align="left">
        Blk No.:
        </td> 
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        ' . $infofaas->{'blk_no'} . '
        </td>                  
    </tr>

    <tr style="height:25px" align="left">
        <td style="width:30%">
        BOUNDARIES:
        </td>
    </tr>

    <tr style="height:25px" align="center">   
        <td style="width:5%"> 
        </td> 
        <td style="width:20%">
        North: 
        </td>    
        <td style="width:25%; border-bottom: 1px solid black" align="center">                            
        ' . $infofaas->{'north'} . '
        </td>
        <td style="width:5%"> 
        </td> 
        <td style="width:20%">
        South: 
        </td>    
        <td style="width:25%; border-bottom: 1px solid black" align="center">                            
        ' . $infofaas->{'south'} . '
        </td>              
    </tr>

    <tr style="height:25px" align="center">   
        <td style="width:5%"> 
        </td> 
        <td style="width:20%">
        East: 
        </td>    
        <td style="width:25%; border-bottom: 1px solid black" align="center">                            
        ' . $infofaas->{'east'} . '
        </td>
        <td style="width:5%"> 
        </td> 
        <td style="width:20%">
        West: 
        </td>    
        <td style="width:25%; border-bottom: 1px solid black" align="center">                            
        ' . $infofaas->{'west'} . '
        </td>              
    </tr>

    <tr style="height:15px">
        <td style="width:100%; border-bottom: 1px solid black" align="center">
        </td>
    </tr>

    <tr style="height:25px" align="left">
        <td style="width:35%"><span font-style = ><b>KIND OF PROPERTY ASSESSED:</b></span>
        </td>
    </tr>

    <table cellspacing="3">
    <tr>
    	<td style="width:5%"></td>            
        <td style="width:5%" align="center" border="1">' . $TransLand . '</td>
        <td style="width:40%" align="left"><span font-style = ><b>LAND</b></span>
        </td>
        <td style="width:5%" align="center" border="1">' . $TransMachinery . '</td>
        <td style="width:35%" align="left"><span font-style = ><b>MACHINERY</b></span>
        </td>
    </tr>

    <tr>
        <td style="width:60%"></td>            
        <td style="width:15%" align="left">Brief Description :</td>           
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        ' . $kindmachinery . '
        </td>
    </tr>

    <tr>
        <td style="width:5%"></td>            
        <td style="width:5%" align="center" border="1">' . $TransBuilding . '</td>
        <td style="width:40%" align="left"><span font-style = ><b>BUILDING</b></span>
        </td>
        <td style="width:5%" align="center" border="1">' . $TransOthers . '</td>
        <td style="width:40%" align="left"><span font-style = ><b>OTHERS</b></span>
        </td>
    </tr>

    <tr>
        <td style="width:13%"></td>            
        <td style="width:13%" align="left">No. of Storeys :</td>           
        <td style="width:23%; border-bottom: 1px solid black" align="center">
        ' . $no_of_storey . '
        </td>
        <td style="width:10%"></td>
        <td style="width:10%" align="left">Specify :</td>
        <td style="width:4%"></td>           
        <td style="width:25%; border-bottom: 1px solid black" align="center">
        </td>
    </tr>

    <tr>
        <td style="width:13%"></td>            
        <td style="width:13%" align="left">Brief Description :</td>           
        <td style="width:23%; border-bottom: 1px solid black" align="center">
        ' . $structural_type . '
        </td>
    </tr> 
    </table>

    <table border="1">
    <thead>            
    <tr>
        <th style="width:15%" class="caption-label-center"><br><br>Classification<br></th>
        <th style="width:15%" class="caption-label-center"><br><br>Area<br></th>      
        <th style="width:25%" class="caption-label-center"><br><br>Market Value<br></th>        
        <th style="width:15%" class="caption-label-center"><br><br>Actual Use<br></th>
        <th style="width:15%" class="caption-label-center"><br><br>Assessment Level<br></th>
        <th style="width:15%" class="caption-label-center"><br><br>Assessed Value<br></th>
    </tr>   
    </thead>
    <tbody>';

            $marketvaltotal = 0;
            $assessedvaluetotal = 0;

            foreach ($tddtl as $row) {
                // dd($row);        
                $marketvaltotal += $row->market_value;
                $assessedvaluetotal += $row->assessed_value;
                $html_content .= '        
    <tr>         
        <td align="center" style="width:15%">' . $row->classification . '</td>
        <td align="center" style="width:15%">' . $row->dtl_area . '</td>
        <td align="right" style="width:25%">' . $row->market_value . '</td>
        <td align="center" style="width:15%">' . $row->actual_use . '</td>
        <td align="center" style="width:15%">' . $row->assessment_level . '</td>
        <td align="right" style="width:15%">' . $row->assessed_value . '</td>      
    </tr>';
            }
            for ($x = 0; $x <= 5; $x++) {
                $html_content .= '        
    <tr>         
        <td align="right" style="width:15%"></td>
        <td align="center" style="width:15%"></td>
        <td align="right" style="width:25%"></td>
        <td align="center" style="width:15%"></td>
        <td align="center" style="width:15%"></td>
        <td align="right" style="width:15%"></td>       
    </tr>';
            }
            $html_content .= '        
    <tr>         
        <td align="center" style="width:15%">TOTAL</td>
        <td align="center" style="width:15%"></td>
        <td align="right" style="width:25%">' . $marketvaltotal . '</td>
        <td align="center" style="width:15%"></td>
        <td align="center" style="width:15%"></td>
        <td align="right" style="width:15%">' . $assessedvaluetotal . '</td>      
    </tr>';
            $html_content .= '
    </tbody>      
    </table>

    <tr style="height:25px">   
        <td style="width:20%"  align="left">
        Total Assessed Value:
        </td> 
        <td style="width:80%; border-bottom: 1px solid black" align="center">
        </td>                  
     </tr>
  
     <tr style="height:25px" align="center">
           <td style="width:100%"><span font-style = ><i>(Amount In Words)</i></span>
           </td>                  
     </tr>

     <table cellspacing="3">
     <tr>
         <td style="width:5%"></td>
         <td style="width:7%" align="left">Taxable</td>            
         <td style="width:5%" align="center" border="1">' . $TransTaxable . '</td>
         <td style="width:3%"></td>
         <td style="width:7%" align="left">Exempt</td>
         <td style="width:5%" align="center" border="1">' . $TransExempt . '</td>
         <td style="width:3%"></td>
         <td style="width:35%" align="left">Effectivity of Assessment/Reassessment</td>
         <td style="width:15%; border-bottom: 1px solid black" align="center">
         ' . $infotd->{'tax_qrtr'} . '
         </td>
         <td style="width:16%; border-bottom: 1px solid black" align="center">
         ' . $infotd->{'tax_year'} . '
         </td>
     </tr>

     <tr>
         <td style="width:79%"></td>
         <td style="width:11%" align="left">Quarter</td>            
         <td style="width:5%"></td>
         <td style="width:5%" align="left">Year</td>
         </tr>
         </table>

     <br>
     <tr style="height:25px" align="left">
         <td style="width:35%"></td>
         <td style="width:20%" align="center">
         APPROVED BY:
         </td>
         <td style="width:3%"></td>
         <td style="width:24%; border-bottom: 1px solid black" align="center">
         ' . $infosig->{'na_emp_sig'} . '
         </td>
         <td style="width:3%"></td>
         <td style="width:18%; border-bottom: 1px solid black" align="center">
         ' . $infofaas->{'date_approved'} . ' 
         </td>
     </tr>
   
     <tr style="height:25px" align="center">
         <td style="width:56%">            
         </td>
         <td style="width:30%" align="center">
         ' . $infosig->{'na_sig_type'} . '
         </td>
         <td style="width:5%"></td>
         <td style="width:9%"  align="left">
         Date
         </td>                            
    </tr> 
   
            <tr style="height:25px">   
               <td style="width:24%"  align="left">
                This declaration cancels TD No.:
               </td>
               <td style="width:15%; border-bottom: 1px solid black" align="center">
               ' . $prev_td_no . '
               </td>
               <td style="width:8%"  align="left">
                Owner:
               </td>
               <td style="width:20%; border-bottom: 1px solid black" align="center">
               ' . $prev_td_owner . '
               </td>
               <td style="width:15%"  align="left">
               Previous A.V. Php:
               </td>
               <td style="width:18%; border-bottom: 1px solid black" align="center">
               ' . $prev_td_ass_val . '
               </td>                   
            </tr>

            <tr style="height:25px" align="left">
               <td style="width:100%"><span font-style = ><b>Memoranda:</b></span>
               </td>
            </tr>

            <tr style="height:25px">
               <td style="width:100%; border-bottom: 1px solid black" align="left">
               ' . $infofaas->{'memoranda'} . '
               </td>
            </tr>

            <tr style="height:25px" align="left">
              <td style="width:100%"><span color = "red", font-style = ><i>CERTIFIED TRUE COPY FROM THE OFFICE FILE</i></span>
              </td>
            </tr>

            <br>
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                Paid Under OR No.:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="center">
               ' . $info->{'OR No'} . '
               </td>
               <td style="width:30%">            
               </td>
               <td style="width:30%; border-bottom: 1px solid black" align="center">
               ' . $infosig->{'na_emp_sig'} . '
               </td>                  
            </tr> 
   
            <tr style="height:25px">   
               <td style="width:20%"  align="left">
                Date:
               </td> 
               <td style="width:20%; border-bottom: 1px solid black" align="center">
               ' . $info->{'OR Date'} . '
               </td>
               <td style="width:30%">            
               </td>
               <td style="width:30%" align="center">
               ' . $infosig->{'na_sig_type'} . '
               </td>                  
            </tr>
   
            <br>
            <tr style="height:12px" align="left">
               <td style="width:6%"><span><b>Note*</b></span>
               </td>
               <td style="width:94%"  align="left">
                This declaration is for real property taxation purposes only and the valuation indicated herein are based on the schedule of unit market values
               </td>
            </tr>

            <tr style="height:12px" align="left">
               <td style="width:75%">
                prepared for the purpose and duly enacted into an Ordinance by the Sangguniang Panglungsod under Ord. No.
               </td>
               <td style="width:25%; border-bottom: 1px solid black" align="center">
               ' . $infotd->{'sb_ord_no'} . '
               </td>
            </tr>

            <tr <tr style="height:12px" align="left">>
               <td style="width:6%">
                dated:
               </td>
               <td style="width:15%; border-bottom: 1px solid black" align="center">
               </td>
               <td style="width:3%"  align="left">
               .
               </td>
               <td style="width:66%"  align="left">
                It does not and cannot by itself alone confer any ownership or legal title to the property.
               </td>
            </tr>
        </table>
         ';
            PDF::SetTitle('Tax Declaration of Real Property');
            PDF::AddPage('P', array(210, 320));
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
