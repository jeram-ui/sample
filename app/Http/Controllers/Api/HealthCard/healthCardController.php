<?php

namespace App\Http\Controllers\Api\HealthCard;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use PDF;
use ZipArchive;
class healthCardController extends Controller
{
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }
    public function transNo(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_healthcard_transno()');
        return response()->json(new JsonResponse($list));
    }
    public function transNoDirect()
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_healthcard_transno()');
        foreach ($list as $key => $value) {
            return$value->HealthNo;
        }
    }
    public function healthNo(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_healthcard_no()');
        return response()->json(new JsonResponse($list));
    }
    public function healthNoDirect()
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_healthcard_no()');
        foreach ($list as $key => $value) {
            return$value->HealthNo;
        }
    }
    public function checking($id)
    {
        $list = DB::select('call ' . $this->lgu_db . '.jay_display_declared_employees(?)', array($id));
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $main = $request->healthMain;
            $details = $request->details;
            $ctobill = $request->cto;
            $idx = $main['id'];
            if ($idx > 0) {
                $this->update($idx, $main, $details, $ctobill);
            } else {
       
                $main['ref_no']=$this->transNoDirect();
                $this->save($main, $details, $ctobill);
            };

            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function edit(Request $request, $id)
    {
        $id = $id;
        $data['main'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho1_health_card_main(?)', array($id));
        return response()->json(new JsonResponse($data));
    }
    public function save($main, $details, $ctobill)
    {
        $mainF = array(
            'ref_no'  => $main['ref_no'],
            'trans_date'  => $main['trans_date'],
            'bapp_id' => $main['bapp_id'],
            'no_emp'  => $main['no_emp'],    
        );
        DB::table($this->lgu_db . '.cho1_health_card_main')->insert($mainF);
        $id = DB::getPDo()->lastInsertId();
        foreach ($ctobill as $row) {
            if ($row['Include'] === "True") {
                $cto = array(
                    'payer_type' => 'Business',
                    'payer_id' => $main['BusinessNo'],
                    'business_application_id' => $main['bapp_id'],
                    'account_code' => $row['Account Code'],
                    'bill_description' => $row['Account Description'],
                    'net_amount' => $row['Fee Amount'],
                    'bill_amount' => $row['Fee Amount'],
                    'bill_month' => $main['trans_date'],
                    'bill_number' => $main['ref_no'],
                    'transaction_type' => 'Health Certification',
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'include_from' => 'Others',
                );
                DB::table($this->lgu_db . '.cto_general_billing')->insert($cto);
            }
        }
    }
    public function update($idx, $main, $details, $ctobill)
    {
        $mainF = array(
            'ref_no'  => $main['ref_no'],
            'trans_date'  => $main['trans_date'],
            'bapp_id' => $main['bapp_id'],
            'no_emp'  => $main['no_emp'],
            'remarks'  => $main['remarks'],
            'ContactNumber' => $main['ContactNumber'],
        );
        DB::table($this->lgu_db . '.cho1_health_card_main')->where('id', $idx)->update($mainF);
    }
    public function store2(Request $request)
    {
        try {
            DB::beginTransaction();

            $certMain = $request->healthCertData;
            $certPhysical = $request->physicalExamData;
            $certXray = $request->xraySputumData;
            $certImmunization = $request->immunizationData;
            $certRectal = $request->rectalSwabData;
            $idx = $certMain['health_id'];
            $cto = $request->cto;
            if ($idx > 0) {
                $this->update2($idx, $certMain, $certPhysical, $certXray, $certImmunization, $certRectal);
            } else {
                $certMain['health_no'] =$this->healthNoDirect();
                $this->save2($certMain, $certPhysical, $certXray, $certImmunization, $certRectal,$cto);
            };

            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function edit2(Request $request, $id)
    {
        $data['certMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho1_health_certificate_main(?)', array($id));
        $data['certPhysical'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho_health_physicalexam(?)', array($id));
        $data['certXray'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho_health_xraysputum(?)', array($id));
        $data['certImmunization'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho_health_immunization(?)', array($id));
        $data['certRectal'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho_health_rectalswab(?)', array($id));
        return response()->json(new JsonResponse($data));
    }
    public function save2($certMain, $certPhysical, $certXray, $certImmunization, $certRectal,$fees)
    {
        //   dd($certMain);
        unset($certMain['person_profile_name']);
        unset($certMain['business_address']);
        $certMain['date_issued'] = $this->G->serverdatetime();
        DB::table($this->lgu_db . '.cho1_health_certificate')->insert($certMain);
        $id = DB::getPDo()->lastInsertId();
        foreach ($certPhysical as $row) {
            $array = array(
                'health_no' => $id,
                'date_of_exam' => $row['date'],
                'result' => $row['result'],
                'examiner' => $row['examiner_id'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_physical_exam')->insert($array);
        }
        foreach ($certXray as $row) {
            $array = array(
                'health_no' => $id,
                'date_of_exam' => $row['date'],
                'place' => $row['place'],
                'cause' => $row['cause'],
                'result' => $row['result'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_xray_exam')->insert($array);
        }
        foreach ($certImmunization as $row) {
            $array = array(
                'health_no' => $id,
                'date_of_exam' => $row['date'],
                'place' => $row['place'],
                'type' => $row['type'],
                'result' => $row['result'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_immunization_exam')->insert($array);
        }
        foreach ($certRectal as $row) {
            $array = array(
                'health_no' => $id,
                'date_of_exam' => $row['date'],
                'result' => $row['result'],
                'examiner' => $row['examiner_id'],
                'cause' => $row['cause'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_rectual_exam')->insert($array);
        };
        foreach ($fees as $row) {
            if ($row['Include'] == 'True') {
              $billing = array(
                'ref_id' => $id,
                'bill_id' => $id,
                'payer_type' => "PERSON",
                'transaction_type' => "Health Certificate",
                'bill_number' => $certMain['health_no'],
                'payer_id' => $certMain['person_profile_id'],
                'business_application_id' => $certMain['person_profile_id'],
                'account_code' => $row['Account Code'],
                'bill_description' => $row['Account Description'],
                'net_amount' => $row['Initial Amount'],
                'bill_amount' => $row['Fee Amount'],
                'status' => $row['Status'],
              );
              DB::table($this->lgu_db . '.cto_general_billing')->insert($billing);
              // $id = DB::getPDo()->lastInsertId();
            }
          }


    }
    public function update2($idx, $certMain, $certPhysical, $certXray, $certImmunization, $certRectal)
    {
        unset($certMain['person_profile_name']);
        unset($certMain['business_address']);
        DB::table($this->lgu_db . '.cho1_health_certificate')->where('health_id', $idx)->update($certMain);
        DB::table($this->lgu_db . '.cho1_health_physical_exam')->where('health_no', $idx)->delete();
        foreach ($certPhysical as $row) {
            $array = array(
                'health_no' => $idx,
                'date_of_exam' => $row['date'],
                'result' => $row['result'],
                'examiner' => $row['examiner_id'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_physical_exam')->insert($array);
        }
        DB::table($this->lgu_db . '.cho1_health_xray_exam')->where('health_no', $idx)->delete();
        foreach ($certXray as $row) {
            $array = array(
                'health_no' => $idx,
                'date_of_exam' => $row['date'],
                'place' => $row['place'],
                'cause' => $row['cause'],
                'result' => $row['result'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_xray_exam')->insert($array);
        }
        DB::table($this->lgu_db . '.cho1_health_immunization_exam')->where('health_no', $idx)->delete();
        foreach ($certImmunization as $row) {
            $array = array(
                'health_no' => $idx,
                'date_of_exam' => $row['date'],
                'place' => $row['place'],
                'result' => $row['result'],
                'type' => $row['type'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_immunization_exam')->insert($array);
        }
        DB::table($this->lgu_db . '.cho1_health_rectual_exam')->where('health_no', $idx)->delete();
        foreach ($certRectal as $row) {
            $array = array(
                'health_no' => $idx,
                'date_of_exam' => $row['date'],
                'result' => $row['result'],
                'examiner' => $row['examiner_id'],
                'cause' => $row['cause'],
                'health_number' => $row['healthnumber'],
            );
            DB::table($this->lgu_db . '.cho1_health_rectual_exam')->insert($array);
        };
    }
    public function delete(Request $request)
    {
        $id = $request->id;
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.cho1_health_card_main')->where('id', $id)->update($data);
        $reason['Form_name'] = 'Health Certification';
        $reason['Trans_ID'] = $id;
        $reason['Type_'] = 'Cancel Record';
        $reason['Trans_by'] = Auth::user()->id;
        $this->G->insertReason($reason);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function delete2(Request $request)
    {
        $id = $request->id;
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.cho1_health_certificate')->where('health_id', $id)->update($data);
        // $reason['Form_name'] = 'Health Card';
        // $reason['Trans_ID'] = $id;
        // $reason['Type_'] = 'Cancel Record';
        // $reason['Trans_by'] = Auth::user()->id;
        // $this->G->insertReason($reason);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function getHealthCardList(Request $request)
    {

        $tmp = json_decode($request->dates);
        $dateFrom = $tmp->from;
        $dteTo = $tmp->to;
        $transtype = $request->form;

        $list = DB::select('call ' . $this->lgu_db . '.jay_display_cho1_health_card_main_rans(?,?,?)', array($dateFrom, $dteTo, $transtype));
        return response()->json(new JsonResponse($list));
    }
    public function getHealthCertList(Request $request)

    {
        try {
            $id = $request->id;
            $filter = json_decode($request->filter,true);

            if ($id > 0) {
                $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho1_health_certificate_list(?)', array($id));
            }else{
                $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_cho1_health_certificate_list_range(?,?)', [$filter['from'],$filter['to']]);
            }
    
            return response()->json(new JsonResponse($list));
        } catch (\Excemption $e) {

            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getHistoryPhysical($id)
    {
        try {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_physical_history(?)', array($id));
            return response()->json(new JsonResponse($list));
        } catch (\Excemption $e) {

            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getHistoryXray($id)
    {
        try {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_xray_history(?)', array($id));
            return response()->json(new JsonResponse($list));
        } catch (\Excemption $e) {

            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getHistoryImmunization($id)
    {
        try {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_immunization_history(?)', array($id));
            return response()->json(new JsonResponse($list));
        } catch (\Excemption $e) {

            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getHistoryRectal($id)
    {
        try {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_rectalswab_history(?)', array($id));
            return response()->json(new JsonResponse($list));
        } catch (\Excemption $e) {

            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printHealthCardList(Request $request)
    {
        $data = $request->healthList;
        $logo = config('variable.logo');
        try {
            $html_content = '<body>
            ' . $logo . '            
            <h2 align="center">Health Card Master List</2>
            <br></br>
            <br></br>
            <table border="1" cellpadding="2">
                <tr align="center" >
                    <th style="width:10%">Reference No</th>
                    <th style="width:10%">Application Date</th>
                    <th style="width:15%">Business Name</th>
                    <th style="width:15%">Owner</th>
                    <th style="width:20%">Business Address</th>
                    <th style="width:10%">No. of Employees</th>
                    <th style="width:10%">Applied</th>
                    <th style="width:10%">Health Fee</th>
                </tr>
                <tbody>';
            foreach ($data as $row) {
                $html_content .= '
                    <tr>
                    <td align="center" style="width:10%">' . $row['Reference No'] . '</td>
                    <td align="center" style="width:10%">' . $row['Application Date'] . '</td>
                    <td align="left" style="width:15%">' . $row['Business Name'] . '</td>
                    <td align="left" style="width:15%">' . $row['Owner'] . '</td>
                    <td align="left" style="width:20%">' . $row['Business Address'] . '</td>
                    <td align="center" style="width:10%">' . $row['No of Employee/s'] . '</td>
                    <td align="center" style="width:10%">' . $row['Applied'] . '</td>                 
                    <td align="right" style="width:10%">' . $row['Health Fee'] . '</td>              
                    </tr>';
            }
            $html_content .= '</tbody>
            </table>
            </body>';

            PDF::SetTitle('Health Card Master List');
            PDF::SetFont('times', '', 8);
            PDF::AddPage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printHealthCertificate($id)
    {
        $data = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_health_certificate(?)', array($id));
        foreach ($data as $row) {
            $info = ($row);
            $date = $info->{'Issue Date'};
        }
        $logo = config('variable.logo');
        try {


            $html_content = '<body>
        ' . $logo . '
        <h2 align="center"> HEALTH CERTIFICATE </h2>
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
        <tr style="height:25px" align="left">   
            <td style="width:100%">
            TO WHOM IT MAY CONCERN:
            </td>                             
        </tr> 
        <br>
        <tr style="height:25px" align="left"> 
            <td style="width:5%"  align="left"> 
            </td> 
            <td style="width:30%">
            This is to certify that Mr./Mrs./Ms.
            </td>
            <td style="width:30%; border-bottom: 1px solid black" align="center">            
            ' . $info->{'Employee Name'} . '
            </td>
            <td style="width:2%"  align="left">,
            </td>
            <td style="width:5%; border-bottom: 1px solid black" align="center">                        
            ' . $info->{'Age'} . '
            </td>
            <td style="width:28%">
            years old, has been examined
            </td>                                       
        </tr>
        <tr style="height:25px" align="left">   
            <td style="width:100%">
            in this office and was found as of this date to be free from any communicable diseases.
            </td>                             
        </tr>
        <br>
        <tr style="height:25px"> 
            <td style="width:5%"  align="left"> 
            </td> 
            <td style="width:12%">
            Issued this
            </td>
            <td style="width:6%; border-bottom: 1px solid black" align="right">
            ' . date("j") . '' . date("S") . '
            </td>           
            <td style="width:8%" align="center">                            
            day of
            </td>
            <td style="width:12%; border-bottom: 1px solid black" align="center">
            ' . date("F") . '
            </td>
            <td style="width:2%" align="center">,
            </td>
            <td style="width:8%; border-bottom: 1px solid black" align="center">
            ' . date("Y") . '
            </td>
            <td style="width:2%" align="center">,
            </td>
            <td style="width:48%" align="left">
            at Rural Health Unit of City of Naga, Cebu.
            </td>                                       
        </tr>
        <br>
        <tr style="height:25px">   
            <td style="width:30%" align="left">   
            Type of Work:  
            </td>  
            <td style="width:70%; border-bottom: 1px solid black" align="left">                            
            ' . $info->{'Type of Work'} . '
            </td>                        
         </tr>
         <tr style="height:25px">   
            <td style="width:30%" align="left">   
            Kind of Business:  
            </td>  
            <td style="width:70%; border-bottom: 1px solid black" align="left">                   
            ' . $info->{'Kind of Business'} . '
            </td>                        
         </tr> 
         <tr style="height:25px">   
            <td style="width:30%" align="left">   
            Name of Establishment:  
            </td>  
            <td style="width:70%; border-bottom: 1px solid black" align="left">    
            ' . $info->{'Business Name'} . '
            </td>                        
         </tr> 
         <tr style="height:25px">   
            <td style="width:30%" align="left">   
            Business Address:  
            </td>  
            <td style="width:70%; border-bottom: 1px solid black" align="left">                            
           ' . $info->{'Business Address'} . '
            </td>                        
         </tr>
         <tr style="height:25px">   
            <td style="width:30%" align="left">   
            Immunization:  
            </td>  
            <td style="width:70%; border-bottom: 1px solid black" align="left">                            
            ' . $info->{'Immunization'} . '
            </td>                        
         </tr>
         <tr style="height:25px">   
            <td style="width:30%" align="left">   
            Remarks:  
            </td>  
            <td style="width:70%; border-bottom: 1px solid black" align="left">                            
            ' . $info->{'Remarks'} . '
            </td>                        
         </tr>
         <br>
         <br>
         <br>
         <tr style="height:25px">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%; border-bottom: 1px solid black" align="center">
            DR. CHERLINA  CAÑAVERAL
            </td>                             
        </tr>
        <tr style="height:25px">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
            City Health Officer
            </td>                             
        </tr>
        <br>
        <br>
        <br>
        <tr style="height:25px">   
            <td style="width:15%" align="left">   
            Amt Fee:  
            </td>  
            <td style="width:15%; border-bottom: 1px solid black" align="left">                            
            ' . $info->{'Health Fee'} . '
            </td>
            <td style="width:70%" align="left">            
            </td>                       
         </tr>
         <tr style="height:25px">   
            <td style="width:15%" align="left">   
            OR No.:  
            </td>  
            <td style="width:15%; border-bottom: 1px solid black" align="left">                            
            ' . $info->{'OR No'} . '
            </td>
            <td style="width:70%" align="left">            
            </td>                        
         </tr> 
         <tr style="height:25px">   
            <td style="width:15%" align="left">   
            Date:  
            </td>  
            <td style="width:15%; border-bottom: 1px solid black" align="left">                            
            ' . $info->{'OR Date'} . '
            </td>
            <td style="width:70%" align="left">            
            </td>                        
         </tr> 
         <tr style="height:25px">   
            <td style="width:100%" align="left">   
            City of Naga, Cebu Philippines  
            </td>                                 
         </tr>
    </table>
    </body>';
            PDF::SetTitle('Health Certificate');
            PDF::SetFont('times', '', 10);
            PDF::AddPage();
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printHealthCard($id)
    {
        $data = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_health_card(?)', array($id));
        $dataPhysical = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_physicalexam(?)', array($id));
        $dataXray = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_xraysputum(?)', array($id));
        $dataImmunization = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_immunization(?)', array($id));
        $dataRectal = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_rectalswab(?)', array($id));
        foreach ($data as $row) {
            $info = ($row);
        }
        // $logo = config('variable.logo');        
        try {
            $html_content = '<body>
        <table width ="100%">
        <tr style="height:25px">
            <th style="width:9%">
            </th>                 
            <th style="width:31%" align="Center">
            Republic of the Philippines
            </th>
            <th style="width:10%">
            </th>
            <th style="width:50%">
            </th>                  
        </tr>
        <tr style="height:25px">
            <th style="width:9%">
            </th>                 
            <th style="width:31%" align="Center">
            Department of Health
            </th>
            <th style="width:10%">
            </th>
            <th style="width:50%">
            </th>                  
        </tr>
        <tr style="height:25px">
            <th style="width:9%">
            </th>                 
            <th style="width:31%" align="Center">
            Regional Health Office No. 7
            </th>
            <th style="width:10%">
            </th>
            <th style="width:50%">
            </th>                  
        </tr>
        <tr style="height:25px">
            <th style="width:9%">
            </th>                 
            <th style="width:31%" align="Center">
            Office of the City Health Officer
            </th>
            <th style="width:10%">
            </th>
            <th style="width:50%">
            </th>                  
        </tr>
        <tr style="height:25px">
            <th style="width:9%">
            </th>                 
            <th style="width:31%" align="Center">
            City of Naga, Cebu
            </th>
            <th style="width:10%">
            </th>
            <th style="width:50%">
            </th>                  
        </tr>
        <br>  
        <tr style="height:25px">
            <th style="width:10%">
            </th>                 
            <th style="width:30%" align="Center">
                <b>HEALTH CERTIFICATE</b>
            </th>
            <th style="width:10%">
            </th>
            <th style="width:50%">
            </th>                  
        </tr> 
        <br>
        <br>
        <tr>
            <th style="width:15%">
            </th>                      
            <th style="width:20%" border="1" align="Center">
                <img src="C:\Users\Admin\Desktop\My Web\04_2020\noimage.png" style="width:100px;height:100px;"> 
            </th>
            <th style="width:15%">
            </th>
            <th style="width:50%">
            </th> 
        </tr>
        <br>
        <br>
        <tr style="height:25px">     
            <th style="width:5%">
            NO.
            </th>                 
            <th style="width:20%; border-bottom: 1px solid black" align="left">
            ' . $info->{'Health No'} . '
            </th>
            <th style="width:25%">
            </th>
            <th style="width:50%">
            </th>                            
        </tr>
        <br>
        <tr style="height:25px">   
            <th style="width:20%" align="left">
            IMPORTANT:
            </th>
            <th style="width:30%">
            </th>
            <th style="width:50%">
            </th>                            
        </tr>
        <br>
        <tr style="height:25px">     
            <th style="width:5%">
            </th>
            <th style="width:45%" align="left">
            THIS PERMIT IS NULL & VOID IF ALL
            </th>
            <th style="width:50%">
            </th>                    
        </tr>
        <tr style="height:25px">     
            <th style="width:50%" align="left">
            REQUIREMENTS ARE NOT COMPLIED WITH 
            </th>
            <th style="width:50%">
            </th>                                     
        </tr>
        <tr style="height:25px">    
            <th style="width:50%" align="left">
            TO BE CARRIED ALWAYS AT PLACE OF WORK.
            </th>
            <th style="width:50%">
            </th>                                     
        </tr>
        <br>
        <br>
        <br>
        <br>                 
        <tr style="height:25px">     
            <th style="width:100%">
            ---------------------------------------------------------------------------------------------------------------------------------------------------------
            </th>        
        </tr>   
    </table>
    <br>
    <br>
    <br>
    <br>
    <br>
    <table width ="100%">
        <tr>
            <td width = "50%">
                <table>
                    <tr style="height:50px">
                        <th style="width:100%" align="center">
                        <b>IDENTIFICATION</b>
                        </th>    
                    </tr>
                    <br>
                    <br>
                    <tr style="height:50px">
                        <th style="width:20%" align="left">
                        Name:
                        </th>
                        <th style="width:80%; border-bottom: 1px solid black" align="left">
                        ' . $info->{'Employee Name'} . '                      
                        </th>                        
                    </tr>
                    <tr style="height:50px">
                        <th style="width:20%" align="left">
                        Age:
                        </th>
                        <th style="width:30%; border-bottom: 1px solid black" align="center">
                        ' . $info->{'Age'} . '                      
                        </th>
                        <th style="width:15%" align="left">
                        Sex:
                        </th>
                        <th style="width:35%; border-bottom: 1px solid black" align="center">
                        ' . $info->{'Gender'} . '                      
                        </th>                         
                    </tr>
                    <tr style="height:50px">
                        <th style="width:25%" align="left">
                        Civil Status:
                        </th>
                        <th style="width:30%; border-bottom: 1px solid black" align="center">                      
                        ' . $info->{'Civil Status'} . '
                        </th>
                        <th style="width:10%" align="left">
                        Nat.:
                        </th>
                        <th style="width:35%; border-bottom: 1px solid black" align="left">
                        ' . $info->{'Nationality'} . '                      
                        </th>                        
                    </tr>
                    <tr style="height:50px">
                        <th style="width:25%" align="left">
                        Residence:
                        </th>
                        <th style="width:75%; border-bottom: 1px solid black" align="left">
                        ' . $info->{'Address'} . '                     
                        </th>                       
                    </tr>
                    <tr style="height:50px">
                        <th style="width:25%" align="left">
                        Occupation:
                        </th>
                        <th style="width:75%; border-bottom: 1px solid black" align="left">
                        ' . $info->{'Occupation'} . '                     
                        </th>                        
                    </tr>
                    <tr style="height:50px">
                        <th style="width:15%" align="left">
                        TIN:
                        </th>
                        <th style="width:85%; border-bottom: 1px solid black" align="left">
                        ' . $info->{'TIN'} . '                      
                        </th>                        
                    </tr>
                    <br>
                    <br>
                    <br>
                    <tr style="height:50px">
                        <th style="width:25%" align="left">                      
                        </th>
                        <th style="width:50%; border-bottom: 1px solid black" align="left">                      
                        </th>
                        <th style="width:25%" align="left">                      
                        </th>   
                    </tr>                 
                    <tr style="height:50px">
                        <th style="width:25%" align="left">                      
                        </th>
                        <th style="width:50%" align="center">
                        (Signature)                      
                        </th>
                        <th style="width:25%" align="left">                      
                        </th>   
                    </tr>
                    <br>
                    <br>
                    <br>
                    <tr style="height:50px">
                        <th style="width:100%" align="center">
                        <b>PERMIT</b>
                        </th>    
                    </tr>
                    <br>
                    <tr style="height:50px">                       
                        <th style="width:15%">
                        </th>
                        <th style="width:85%" align="left">
                        The above named person is hereby granted a permit
                        </th>                    
                    </tr>
                    <tr style="height:50px">                    
                        <th style="width:100%" align="left">
                        to act as
                        </th>                    
                    </tr>
                    <br>
                    <tr style="height:50px">
                        <th style="width:15%">
                        </th>                   
                        <th style="width:70%; border-bottom: 1px solid black" align="left">
                        </th>
                        <th style="width:15%">
                        </th>                  
                    </tr>
                </table>
            </td>
            <td width = "50%">
                <table >
                    <tr style="height:50px">                
                        <th style="width:100%" align="center">
                        <b>HEALTH CERTIFICATE</b>
                        </th>               
                    </tr>
                    <br>
                    <tr style="height:50px">
                        <th style="width:5%" align="left">                       
                        </th>                       
                        <th style="width:90%" align="left">
                        I. PHYSICAL EXAMINATION(Yearly)
                        </th>
                        <th style="width:5%" align="left">                       
                        </th>                    
                    </tr>
                    <thead>
                        <tr style="height:50px">                                                
                            <th style="width:30%" align="center" border="1">
                            Date of Exam
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Result
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Initial of Examiner
                            </th>                     s                     
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($dataPhysical as $row) {
                $html_content .= '
                        <tr>
                            <td align="center" border="1">' . $row->{'Date of Exam'} . '</td>
                            <td align="center" border="1">' . $row->{'Result'} . '</td>
                            <td align="left" border="1"></td>
                        </tr>';
            }
            for ($x = 0; $x < 1; $x++) {
                $html_content .= '        
                        <tr>         
                            <td align="center" border="1"></td>
                            <td align="center" border="1"></td>
                            <td align="left" border="1"></td>                                 
                        </tr>';
            }
            $html_content .= '
                    </tbody>
                    <tr style="height:50px">
                        <th style="width:5%" align="left">                       
                        </th>                       
                        <th style="width:90%" align="left">
                        II. X-RAY OR SPUTUM EXAM(6 Months)
                        </th>
                        <th style="width:5%" align="left">                       
                        </th>                    
                    </tr>
                    <thead>
                        <tr style="height:50px">                                                
                            <th style="width:30%" align="center" border="1">
                            Date of Exam
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Place
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Result
                            </th>                                          
                        </tr>
                    </thead>
                    <tbody>
                    ';
            foreach ($dataXray as $row) {
                $html_content .= '
                        <tr>
                            <td align="center" border="1">' . $row->{'Date of Exam'} . '</td>
                            <td align="center" border="1">' . $row->{'Place'} . '</td>
                            <td align="center" border="1">' . $row->{'Result'} . '</td>
                        </tr>';
            }
            for ($x = 0; $x < 1; $x++) {
                $html_content .= '        
                        <tr>         
                            <td align="center" border="1"></td>
                            <td align="center" border="1"></td>
                            <td align="left" border="1"></td>                                 
                        </tr>';
            }
            $html_content .= '
                    </tbody>
                    <tr style="height:50px">
                        <th style="width:5%" align="left">                       
                        </th>                       
                        <th style="width:90%" align="left">
                        III. URINALYSIS / CBC
                        </th>
                        <th style="width:5%" align="left">                       
                        </th>                    
                    </tr>
                    <thead>
                        <tr style="height:50px">                                                
                            <th style="width:30%" align="center" border="1">
                            Date of Exam
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Place
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Result
                            </th>                                          
                        </tr>
                    </thead>
                    <tbody>
                    ';
            foreach ($dataImmunization as $row) {
                $html_content .= '
                        <tr>
                            <td align="center" border="1">' . $row->{'Date of Exam'} . '</td>
                            <td align="center" border="1">' . $row->{'Place'} . '</td>
                            <td align="center" border="1">' . $row->{'Result'} . '</td>
                        </tr>';
            }
            for ($x = 0; $x < 1; $x++) {
                $html_content .= '        
                        <tr>         
                            <td align="center" border="1"></td>
                            <td align="center" border="1"></td>
                            <td align="center" border="1"></td>                                 
                        </tr>';
            }
            $html_content .= '
                    </tbody>                   
                    <tr style="height:50px">
                        <th style="width:5%" align="left">                       
                        </th>                       
                        <th style="width:90%" align="left">
                        IV. RECTAL SWAB
                        </th>
                        <th style="width:5%" align="left">                       
                        </th>                    
                    </tr>
                    <thead>
                        <tr style="height:50px">                                                
                            <th style="width:30%" align="center" border="1">
                            Date of Exam
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Result
                            </th>
                            <th style="width:35%" align="center" border="1">
                            Inititial of Examiner
                            </th>                                          
                        </tr>
                    </thead>
                    <tbody>
                    ';
            foreach ($dataRectal as $row) {
                $html_content .= '
                        <tr>
                            <td align="center" border="1">' . $row->{'Date of Exam'} . '</td>
                            <td align="center" border="1">' . $row->{'Result'} . '</td>
                            <td align="center" border="1"></td>
                        </tr>';
            }
            for ($x = 0; $x < 1; $x++) {
                $html_content .= '        
                        <tr>         
                            <td align="center" border="1"></td>
                            <td align="center" border="1"></td>
                            <td align="center" border="1"></td>                                 
                        </tr>';
            }
            $html_content .= '
                    </tbody>                              
                </table>
            </td>
        </tr>
    </table>';
            PDF::SetTitle('Health Card');
            PDF::SetFont('times', '', 10);
            PDF::AddPage();
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printCardFedIn($id)
    {
       
        $template_file_name = public_path().'\HEALTH\Health Card Fed In.docx';
        $rand_no = rand(111111, 999999);
        $fileName = "results_" . $rand_no . ".docx";
        $folder   = "results_health";
        $full_path = $folder . '/' . $fileName;
        if (!file_exists($folder))
        {
            mkdir($folder);
        } 
        copy($template_file_name, $full_path);
        $zip_val = new ZipArchive;
        if($zip_val->open($full_path) == true)
        {
            $data = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_health_card(?)', array($id));
            $dataPhysical = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_physicalexam(?)', array($id));
            $dataXray = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_xraysputum(?)', array($id));
            $dataImmunization = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_immunization(?)', array($id));
            $dataRectal = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_rectalswab(?)', array($id));
            foreach ($data as $row) {
                $info = ($row);
            }
            foreach ($dataPhysical as $row) {
                $dataPhysical = ($row);
            }
            foreach($dataXray as $row){
                $dataXray = ($row);
            }
            foreach($dataRectal as $row){
                $dataRectal = ($row);
            }
            
            $d = "";
            $A = "";
            $_1 = "";

            $e = "";
            $B = "";
            $_2 = "";

            $f = "";
            $C = "";
            $_3 = "";
           

             if (array_key_exists(0, $dataImmunization) ) {
                if ($dataImmunization[0]->{'Date of Exam'}) {
                    $date=date_create($dataImmunization[0]->{'Date of Exam'});

                    $d=date_format($date,"d/m/Y");
                    }
                     if ($dataImmunization[0]->{'type'}) {
                     $A=$dataImmunization[0]->{'type'};
                      }
                      if ($dataImmunization[0]->{'Result'}) {
                     $_1=$dataImmunization[0]->{'Result'};
                     }
             }
         
             if (array_key_exists(1, $dataImmunization) ) {
                if ($dataImmunization[1]->{'Date of Exam'}) {
                    $date=date_create($dataImmunization[1]->{'Date of Exam'});
                    $e=date_format($date,"d/m/Y");
                   
                 }
                  if ($dataImmunization[1]->{'type'}) {
                    $B=$dataImmunization[1]->{'type'};
                  }
                   if ($dataImmunization[1]->{'Result'}) {
                    $_2=$dataImmunization[1]->{'Result'};
                   }
             }

        

             if (array_key_exists(2, $dataImmunization) ) {
                if ($dataImmunization[2]->{'Date of Exam'}) {
                    $date=date_create($dataImmunization[2]->{'Date of Exam'});
                    $f=date_format($date,"d/m/Y");
                 }
                  if ($dataImmunization[2]->{'type'}) {
                    $C=$dataImmunization[2]->{'Result'};
                  }
                   if ($dataImmunization[2]->{'examiner'}) {
                    $_3=$dataImmunization[2]->{'examiner'};
                   }
             }

            $key_file_name = 'word/document.xml';
            $message = $zip_val->getFromName($key_file_name); 
            $date=date_create($dataPhysical->{'Date of Exam'});

            $message = str_replace("@name",$info->{'Employee Name'},$message);
            $message = str_replace("@occupation",$info->{'Occupation'},$message);
            $message = str_replace("@age",$info->{'Age'},$message);
            $message = str_replace("@sex",$info->{'Gender'},$message);
            $message = str_replace("@nat",$info->{'Nationality'},$message);
            $message = str_replace("@bus",$info->{'Address'},$message);

            $message = str_replace("@phydateofexam",date_format($date,"d/m/Y"),$message);
            $message = str_replace("@phyresult",$dataPhysical->{'Result'},$message);
            $message = str_replace("@examiner",$dataPhysical->{'examiner'},$message);
            $message = str_replace("@ornumber",$info->{'or_number'},$message);

            
        
  
            $message = str_replace("@D",$d,$message);
            $message = str_replace("{A}",$A,$message);
            $message = str_replace("khcsaoicdk",$_1,$message);


            $message = str_replace("csacajb",$e,$message);
            $message = str_replace("biybn",$B,$message);
            $message = str_replace("dsaiuds",$_2,$message);

            
            $message = str_replace("{f}",$f,$message);
            $message = str_replace("{C}",$C,$message);
            $message = str_replace("{3}",$_3,$message);
            $date=date_create($dataXray->{'Date of Exam'});
            $message = str_replace("@xraydofexam",date_format($date,"d/m/Y"),$message);
            $message = str_replace("@adsavrw",$dataXray->{'Place'},$message);
            $message = str_replace("@xrayresult",$dataXray->{'Result'},$message);

            if ($dataRectal) {
                $date=date_create($dataRectal->{'Date of Exam'});
                $message = str_replace("@rectaldate",date_format($date,"d/m/Y")  ,$message);
                $message = str_replace("@rectalresult",$dataRectal->{'Result'},$message);
                $message = str_replace("@rectalcause",$dataRectal->{'examiner'},$message);
            }else{
                $message = str_replace("@rectaldate",""  ,$message);
                $message = str_replace("@rectalresult","",$message);
                $message = str_replace("@rectalcause","",$message);
            }
          
            $zip_val->addFromString($key_file_name, str_replace("&","&amp;",$message));
            $zip_val->close();
      
            // log::debug(public_path()."/".$full_path);
            // return response()->download(public_path('uploads/TestWordFile.docx'));
            // return response()->download(public_path()."/".$full_path);
            if (\File::exists(public_path()."/".$full_path)) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }

    }
    public function postOR(Request $request){
 
        try {
            DB::beginTransaction();
            $selected = $request->selected;
            $or_data = $request->or_data;
            
            db::table($this->lgu_db.'.cto_general_billing')
            ->where('transaction_type',$request->transaction_type)
            ->where('ref_id',$selected['ref_id'])
            ->update(['status'=>'CANCELLED']);
    
            db::table($this->lgu_db.'.cto_general_billing')
            ->where('SysPK_general_billing',$or_data['SysPK_general_billing'])
            ->update(['status'=>'paid','ref_id'=>$selected['ref_id'],'bill_id'=>$selected['ref_id'],'transaction_type'=>$request->transaction_type]);
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
       } catch (\Exception $e) {
           DB::rollBack();
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }

    }
    public function printCardFedIn2($id)
    {
        log::debug($id);
        $template_file_name = public_path().'\HEALTH\Health Card2.docx';
        $rand_no = rand(111111, 999999);
        $fileName = "results_" . $rand_no . ".docx";
        $folder   = "results_health";
        $full_path = $folder . '/' . $fileName;
        if (!file_exists($folder))
        {
            mkdir($folder);
        } 
        copy($template_file_name, $full_path);
        $zip_val = new ZipArchive;
        if($zip_val->open($full_path) == true)
        {
            $data = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_health_card(?)', array($id));
            $dataPhysical = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_physicalexam(?)', array($id));
            $dataXray = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_xraysputum(?)', array($id));
            $dataImmunization = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_immunization(?)', array($id));
            $dataRectal = DB::select('call ' . $this->lgu_db . '.balodoy_print_cho1_rectalswab(?)', array($id));
            foreach ($data as $row) {
                $info = ($row);
            }
            foreach ($dataPhysical as $row) {
                $dataPhysical = ($row);
            }
            foreach($dataXray as $row){
                $dataXray = ($row);
            }
            foreach($dataRectal as $row){
                $dataRectal = ($row);
            }
            
            $d = "";
            $A = "";
            $_1 = "";

            $e = "";
            $B = "";
            $_2 = "";

            $f = "";
            $C = "";
            $_3 = "";
           

             if (array_key_exists(0, $dataImmunization) ) {
                if ($dataImmunization[0]->{'Date of Exam'}) {
                    $date=date_create($dataImmunization[0]->{'Date of Exam'});

                    $d=date_format($date,"d/m/Y");
                    }
                     if ($dataImmunization[0]->{'type'}) {
                     $A=$dataImmunization[0]->{'type'};
                      }
                      if ($dataImmunization[0]->{'Result'}) {
                     $_1=$dataImmunization[0]->{'Result'};
                     }
             }
         
             if (array_key_exists(1, $dataImmunization) ) {
                if ($dataImmunization[1]->{'Date of Exam'}) {
                    $date=date_create($dataImmunization[1]->{'Date of Exam'});
                    $e=date_format($date,"d/m/Y");
                   
                 }
                  if ($dataImmunization[1]->{'type'}) {
                    $B=$dataImmunization[1]->{'type'};
                  }
                   if ($dataImmunization[1]->{'Result'}) {
                    $_2=$dataImmunization[1]->{'Result'};
                   }
             }

        

             if (array_key_exists(2, $dataImmunization) ) {
                if ($dataImmunization[2]->{'Date of Exam'}) {
                    $date=date_create($dataImmunization[2]->{'Date of Exam'});
                    $f=date_format($date,"d/m/Y");
                 }
                  if ($dataImmunization[2]->{'type'}) {
                    $C=$dataImmunization[2]->{'Result'};
                  }
                   if ($dataImmunization[2]->{'examiner'}) {
                    $_3=$dataImmunization[2]->{'examiner'};
                   }
             }

            $key_file_name = 'word/document.xml';
            $message = $zip_val->getFromName($key_file_name); 
            $date=date_create($dataPhysical->{'Date of Exam'});

            $message = str_replace("@name",$info->{'Employee Name'},$message);
            $message = str_replace("@occupation",$info->{'Occupation'},$message);
            $message = str_replace("@age",$info->{'Age'},$message);
            $message = str_replace("@sex",$info->{'Gender'},$message);
            $message = str_replace("@nat",$info->{'Nationality'},$message);
            $message = str_replace("@bus",$info->{'Address'},$message);

            $message = str_replace("@phydateofexam",date_format($date,"d/m/Y"),$message);
            $message = str_replace("@phyresult",$dataPhysical->{'Result'},$message);
            $message = str_replace("@examiner",$dataPhysical->{'examiner'},$message);
            $message = str_replace("@ornumber",$info->{'or_number'},$message);
        
  
            $message = str_replace("@D",$d,$message);
            $message = str_replace("{A}",$A,$message);
            $message = str_replace("khcsaoicdk",$_1,$message);


            $message = str_replace("csacajb",$e,$message);
            $message = str_replace("biybn",$B,$message);
            $message = str_replace("dsaiuds",$_2,$message);

            
            $message = str_replace("{f}",$f,$message);
            $message = str_replace("{C}",$C,$message);
            $message = str_replace("{3}",$_3,$message);
            $date=date_create($dataXray->{'Date of Exam'});
            $message = str_replace("@xraydofexam",date_format($date,"d/m/Y"),$message);
            $message = str_replace("@adsavrw",$dataXray->{'Place'},$message);
            $message = str_replace("@xrayresult",$dataXray->{'Result'},$message);

            if ($dataRectal) {
                $date=date_create($dataRectal->{'Date of Exam'});
                $message = str_replace("@rectaldate",date_format($date,"d/m/Y")  ,$message);
                $message = str_replace("@rectalresult",$dataRectal->{'Result'},$message);
                $message = str_replace("@rectalcause",$dataRectal->{'examiner'},$message);
            }else{
                $message = str_replace("@rectaldate",""  ,$message);
                $message = str_replace("@rectalresult","",$message);
                $message = str_replace("@rectalcause","",$message);
            }
          
            $zip_val->addFromString($key_file_name, str_replace("&","&amp;",$message));
            $zip_val->close();
                        // log::debug(public_path()."/".$full_path);
            // return response()->download(public_path('uploads/TestWordFile.docx'));
            // return response()->download(public_path()."/".$full_path);
            if (\File::exists(public_path()."/".$full_path)) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }

    }
}
