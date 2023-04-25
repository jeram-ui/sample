<?php

namespace App\Http\Controllers\Api\Mayors;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;

use PDF;

class MayorsClearance extends Controller
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
        $this->signatory = $this->G->signatoryReport();
        $this->LGUName = $this->G->LGUName();
    }
    public function getApplicantType()
    {
        $list = DB::select('Call ' . $this->lgu_db . '.profile_applicant_type_zoe()');
        return response()->json(new JsonResponse($list));
    }
    public function getRequirements(Request $request)
    {
        $frmname =  $request->frmname;
        $list = DB::select('Call ' . $this->lgu_db . '.display_certpermit_requirements_gigil(?)', array($frmname));
        return response()->json(new JsonResponse($list));
    }
    public function masterList(Request $request)
    {

        $dateFrom = $request['from'];
        $dateTo = $request['to'];
        $_formname = $request['formtype'];
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_profile1_gen(?,?,?)', array($dateFrom, $dateTo, $_formname));

        return response()->json(new JsonResponse($list));
    }
    public function ref(Request $request)
    {
        $frmname = $request->formname;
        if ($frmname === 'Mayor`s Clearance (Endorsement)') {
            $pre = 'MCE-';
        } else if ($frmname === 'Mayor`s Recommendation') {
            $pre = 'MRP-';
        } else if ($frmname === 'Mayor`s Certification') {
            $pre = 'MC-';
        } else if ($frmname === 'Mayor`s Clearance (Employment)') {
            $pre = 'MCEMP-';
        } else if ($frmname === 'Rental Permit') {
            $pre = 'RP-';
        } else if ($frmname === 'Motorboat Operation') {
            $pre = 'MBO-';
        } else if ($frmname === 'Environmental Certificate') {
            $pre = 'ENV-';
        } else if ($frmname === 'Certificate of Occupancy') {
            $pre = 'COC-';
        } else if ($frmname === 'Dance Permit') {
            $pre = 'DC-';
        } else if ($frmname === 'Cockfight Permit') {
            $pre = 'CP-';
        } else if ($frmname === 'Mahjong Permit') {
            $pre = 'MJ-P-';
        } else if ($frmname === 'Special Permit') {
            $pre = 'SP-';
        } else if ($frmname === 'Trisikad Permit') {
            $pre = 'TP-';
        } else if ($frmname === 'Tricycle Permit') {
            $pre = 'TCP-';
        } else if ($frmname === 'Fishing Permit') {
            $pre = 'FP-';
        } else if ($frmname === 'Bicycle Permit') {
            $pre = 'BIP-';
        }

        $table = $this->lgu_db . ".ebplo_tbl_profile";
        $date = $request->date;
        $refDate = 'appdate';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    // CRUD 
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $mainData = $request->main;
            $cedulaData = $request->cedula;
            $requirements = $request->requirements;
            $cc = $request->copyfurnish;
            $reference = $request->ord;
            $formid = $request->frmID;

           
            $idx = $mainData['pkid'];
            unset($mainData['pkid']);
            unset($mainData['applicantName']);
            unset($mainData['grantedto']);
            unset($mainData['brgyname']);
            if ($idx > 0) {
                $this->update($idx, $mainData, $cedulaData, $requirements, $reference, $cc);
            } else {
                $this->save($mainData, $cedulaData, $requirements, $reference, $cc, $formid);
            };
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function save($mainData, $cedulaData, $requirements, $reference, $cc, $frmid)
    {

        db::table($this->lgu_db . '.ebplo_tbl_profile')->insert($mainData);
        $id = DB::getPDo()->lastInsertId();
        $this->save_details($id, $mainData, $cedulaData, $requirements, $reference, $cc, $frmid);
    }
    public function save_details($id, $mainData, $cedulaData, $requirements, $reference, $cc, $formID)
    {
        //Billing

        $fees = DB::select('Call ' . $this->lgu_db . '.ebplo_display_accounts_jho(?)', array($formID));
        $cntlimit = $mainData['noofcounts'];
        $incomeamount = 0;
        $incomecode = '';
        $incomedesc = '';
        $incomeID = 0;
        
        foreach ($fees as $row) {
            
            if ($row->Type_ === 'Fixed') {

                $billing = array(
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'payer_type' => $mainData['apptype'],
                    'transaction_type' => $mainData['formname'],
                    'bill_number' => $mainData['appno'],
                    'payer_id' => $mainData['appid'],
                    'business_application_id' => $mainData['appid'],
                    'account_code' => $row->income_account_code,
                    'bill_description' => $row->income_account_description,
                    'bill_month' => $mainData['appdate'],
                    'bill_amount' => $row->base_amount
                );

                DB::table($this->lgu_db . '.cto_general_billing')->insert($billing);
                $profile_bill = array(
                    'mainid' => $id,
                    "accountid" => $row->id,
                    "accountcode" => $row->income_account_code,
                    "feeamount" => $row->base_amount
                );
                DB::table($this->lgu_db . '.ebplo_tbl_profile_fees')->insert($profile_bill);
            } else {
                $DTRange = DB::select('SELECT * FROM ' . $this->lgu_db . '.cto_income_account_list WHERE income_account_code = ' . $row->income_account_code);

                if ($row->Type_ === 'PER COUNT') {
                    $incomeamount = $row->base_amount * $cntlimit;
                    $incomecode = $row->income_account_code;
                    $incomedesc = $row->income_account_description;
                    $incomeID = $row->id;
                } else {
                    foreach ($DTRange as $y) {
                        if ($cntlimit >= $y->minimum_range && $cntlimit <= $y->minimum_range) {
                            $incomeamount = $y->range_amount * $cntlimit;
                            $incomecode = $y->income_account_code;
                            $incomedesc = $y->income_account_description;
                            $incomeID = $y->id;
                        }
                    }
                }
                if (count($DTRange) > 0 || strtoupper($row->Type_) === "PER COUNT") {
                    $fee = array(
                        'bill_number' => $mainData['appno'],
                        'payer_type' => $mainData['apptype'],
                        'payer_id' => $mainData['appid'],
                        'business_application_id' => $mainData['appid'],
                        'account_code' => $incomecode,
                        'bill_description' => $incomedesc,
                        'bill_amount' => $incomeamount,
                        'bill_month' => $mainData['appdate'],
                        'transaction_type' => $mainData['formname'],
                        'ref_id' => $id,
                        'bill_id' => $id
                    );
                    DB::table($this->lgu_db . '.cto_general_billing')->insert($fee);

                    $profile_bill = array(
                        'mainid' => $id,
                        "accountid" => $incomeID,
                        "accountcode" => $incomecode,
                        "feeamount" => $incomeamount,
                    );
                    DB::table($this->lgu_db . '.ebplo_tbl_profile_fees')->insert($profile_bill);
                }
            }
        }
        // Reference
        foreach ($reference as $row) {
            $ref = array(
                'mainid' => $id,
                'type' => $row['type'],
                'number' => $row['number'],
                'date' => $row['date'],
                'year' => $row['year']
            );
            DB::table($this->lgu_db . '.ebplo_tbl_profile_reference')->insert($ref);
        }

        // CC

        foreach ($cc as $rowcc) {
            $cc = array(
                'mainid' => $id,
                'copyfurnish' => $rowcc['cc'],
            );
            DB::table($this->lgu_db . '.ebplo_tbl_profile_copyfurnish')->insert($cc);
        }

        //Cedula
        if ($cedulaData['ctcid'] === null) {
        } else {
            $ced = array(
                'mainid' => $id,
                'ctcid' => $cedulaData['ctcid'],
                'ctcno' => $cedulaData['ctcno'],
                'ctcdate' => $cedulaData['ctcdate']
            );
            DB::table($this->lgu_db . '.ebplo_tbl_profile_cedula')->insert($ced);
        }


        // Requirements
        foreach ($requirements as $rowreq) {

            $reqdescdata = DB::select('SELECT requirement FROM ' . $this->lgu_db . '.ebplo_other_fees_setup_modules_requirements WHERE id = ' . $rowreq);
            foreach ($reqdescdata as $row) {
                $requirmnt = $row->requirement;
            }
            $req = array(
                'mainid' => $id,
                'reqid' => $rowreq,
                'requirements' =>  $requirmnt
            );
            DB::table($this->lgu_db . '.ebplo_tbl_profile_requirements')->insert($req);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.ebplo_tbl_profile')->where('pkid', $id)->update($data);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function edit($id)
    {
        try {

            $data['main'] = DB::select('Call ' . $this->lgu_db . '.spl_edit_profile1_gen(?)', array($id));
            
            $data['ord']  = DB::table($this->lgu_db . '.ebplo_tbl_profile_reference')
                ->where('mainid', $id)->get();
          
            $data['requirements'] = DB::table($this->lgu_db . '.ebplo_tbl_profile_requirements')
                ->where('mainid', $id)->get();

            $data['cedula'] = DB::table($this->lgu_db . '.ebplo_tbl_profile_cedula')
                ->where('mainid', $id)->get();
            
            $data['copyfurnish'] = DB::table($this->lgu_db . '.ebplo_tbl_profile_copyfurnish')
                ->where('mainid', $id)->get();
        return response()->json(new JsonResponse($data));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function update($idx, $mainData, $cedulaData, $requirements, $reference, $c)
    {
        try {

            DB::table($this->lgu_db . '.ebplo_tbl_profile')->where('pkid', $idx)->update($mainData);
            DB::table($this->lgu_db . '.ebplo_tbl_profile_cedula')->where('mainid', $idx)->update($cedulaData);
            DB::table($this->lgu_db . '.ebplo_tbl_profile_requirements')->where('mainid', $idx)->update($requirements);
            DB::table($this->lgu_db . '.ebplo_tbl_profile_reference')->where('mainid', $idx)->update($reference);
            DB::table($this->lgu_db . '.ebplo_tbl_profile_copyfurnish')->where('mainid', $idx)->update($c);
       
            return response()->json(new JsonResponse(['Message' => 'Updated Successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    //PRINT REPORTS CONTROLLER
    public function printList(Request $request)
    {
        $frm = $request->formname;
        if ($frm === 'Mayor`s Clearance (Endorsement)') {
            $name = 'MAYOR`S CLEARANCE (ENDORSEMENT) MASTER LIST';
        } else if ($frm === 'Mayor`s Recommendation') {
            $name = 'MAYOR`S RECOMMENDATION MASTER LIST';
        } else if ($frm === 'Mayor`s Certification') {
            $name = 'MAYOR`S CERTIFICATION MASTER LIST';
        } else if ($frm === 'Mayor`s Clearance (Employment)') {
            $name = 'MAYOR`S CLEARANCE (EMPLOYMENT) MASTER LIST';
        } else if ($frm === 'Rental Permit') {
            $name = 'RENTAL PERMIT MASTER LIST';
        } else if ($frm === 'Motorboat Operation') {
            $name = 'MOTORBOAT OPERATION MASTER LIST';
        } else if ($frm === 'Environmental Certificate') {
            $name = 'ENVIRONMENTAL CERTIFICATE MASTER LIST';
        } else if ($frm === 'Certificate of Occupancy') {
            $name = 'CERTIFICATE OF OCCUPANCY MASTER LIST';
        } else if ($frm === 'Dance Permit') {
            $name = 'DANCE PERMIT MASTER LIST';
        } else if ($frm === 'Cockfight Permit') {
            $name = 'COCKFIGHT PERMIT MASTER LIST';
        } else if ($frm === 'Mahjong Permit') {
            $name = 'MAHJONG PERMIT MASTER LIST';
        } else if ($frm === 'Special Permit') {
            $name = 'SPECIAL PERMIT MASTER LIST';
        } else if ($frm === 'Trisikad Permit') {
            $name = 'TRISIKAD PERMIT MASTER LIST';
        } else if ($frm === 'Tricycle Permit') {
            $name = 'TRICYCLE PERMIT MASTER LIST';
        } else if ($frm === 'Fishing Permit') {
            $name = 'FISHING PERMIT MASTER LIST';
        } else if ($frm === 'Bicycle Permit') {
            $name = 'BICYCLE PERMIT MASTER LIST';
        }
        $logo = config('variable.logo');
        try {
            $main = $request->main;
            $rptcaption = $request->reportcaption;

            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
                ' . $logo . ' 
          <h3 align="center">' . $name . '</h3>
          <table>
          <tr>
          <th style="text-align:center;">As of ' . $request->reportcaption . '</th>
          </tr>
          </table>
          <br></br>
          <br></br>
          <table style="padding:2px;width:100%;">
          <thead>
            <tr>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:3%;"><br><br><b>NO</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>TRANS NO</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>TRANS DATE</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>APPLICANT</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>BARANGAY</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>NO. OF UNITS</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>GRANTED TO</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>PURPOSE</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>VALIDITY</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>DATE ISSUED</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>TOTAL FEES</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:5%;"><br><br><b>OR NO</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:5%;"><br><br><b>OR DATE</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>PAYMENT STATUS</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>STATUS</b><br></th>
             
            </tr>
         </thead>
         <tbody >';
            $ctr = 1;

            foreach ($main as $row) {

                $html_content .= '
        <tr style="padding:2px;width:100%;">
        <td style="border:0.5px solid black;text-align:center;width:3%;">' . $ctr . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['appno'] . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['appdate'] . '</td>
        <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['NameofApplicant'] . '</td>
        <td style="border:0.5px solid black;text-align:left;">' . $row['brgy'] . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['noofunits'] . '</td>    
        <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['grantedto'] . '</td>
        <td style="border:0.5px solid black;text-align:left;">' . $row['purpose'] . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['valid'] . '</td>   
        <td style="border:0.5px solid black;text-align:center;">' . $row['Issued Date'] . '</td>   
        <td style="border:0.5px solid black;text-align:right;">' . $row['Permit Fee'] . '</td> 
        <td style="border:0.5px solid black;text-align:center;width:5%;">' . $row['OR No'] . '</td> 
        <td style="border:0.5px solid black;text-align:center;width:5%;">' . $row['OR Date'] . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['Payment Status'] . '</td>  
        <td style="border:0.5px solid black;text-align:center;">' . $row['status'] . '</td>                    
        </tr>';
                $ctr++;
            }
            $ctr = $ctr - 1;

            $html_content .= '<tr style="padding:2px;">
        <th colspan="2" style="border:0.5px solid black;text-align:right;height:20px;"><b>TOTAL RECORDS</b></th>  
        <th colspan="13"style="border:0.5px solid black;text-align:left;height:20px;"><b>' . $ctr . '</b></th>  
        </tr>';

            $html_content .= '</tbody>
        </table>
        ';

            PDF::SetTitle($name);
            PDF::AddPage('L', array(250, 350));
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printCount(Request $request)
    {

        $frmname = $request->frmname;

        $logo = config('variable.logo');
        try {
            $dataperbrgy = DB::select('Call ' . $this->lgu_db . '.display_countperbrgy_gigil(?)', array($frmname));

            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
                ' . $logo . ' 
          <h3 align="center">SUMMARY COUNT PER BARANGAY</h3>
          <table>
          <tr>
          <th style="text-align:center;">As of ' . $request->reportcaption . '</th>
          </tr>
          </table>
          <br />
          <br />
          <br />
          <table style="padding:2px;width:100%;">
          <thead>
            <tr>
             <th style="text-align:center;width:3%;"></th>
             <th style="text-align:center;"></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:40%;"><br><br><b>BARANGAY</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:20%;"><br><br><b>COUNT</b><br></th>
             <th style="text-align:center;"></th>
             <th style="text-align:center;"></th>
             <th style="text-align:center;"></th>
             <th style="text-align:center;"></th>
             
            </tr>
         </thead>
         <tbody >';
            $ctr = 1;


            foreach ($dataperbrgy as $row) {

                $html_content .= '
        <tr style="padding:2px;width:100%;">
            <td style="text-align:center;width:3%;"></td>
            <td style="text-align:center;"></td>
            <td style="border:0.5px solid black;text-align:left; width: 40%;">' . $row->brgy . '</td>
            <td style="border:0.5px solid black;text-align:center;width: 20%;">' . $row->cnt . '</td>    
            <td style="text-align:left;"></td>
            <td style="text-align:left;"></td>   
            <td style="text-align:left;"></td>   
            <td style="text-align:left;"></td>                     
        </tr>
        ';
            }
            $html_content .= ' 
            <br />
            <br />
            <br />
            <br />
            <tr>  
               
                <td style="width:40%;" align="left">
                    Prepared By :
                </td>   
                <td style="width:20%;" align="left"></td> 
                <td style="width:40%;" align="left">
                    Noted By :
                </td>    
            </tr>
            <br />
            <tr>  
                <td style="width:1%;" align="left"></td>   
                <td style="width:40%; border-bottom:0.5px solid black;" align="center"></td> 
                <td style="width:18%;" align="left"></td>                                 
                <td style="width:38%; border-bottom:0.5px solid black;" align="center"></td> 
            </tr>
        </tbody>
        </table>
        ';

            PDF::SetTitle('Mayor`s Recommendation Master List');
            PDF::AddPage('P');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printclearance($id)
    {
        $data = DB::select('call ' . $this->lgu_db . '.spl_print_Cert_profile1_gigil(?)', array($id));

        $muncity = DB::select('Call ' . $this->lgu_db . '.display_citymun_gigil()');

        foreach ($muncity as $rowciymun) {
            $citymundata = $rowciymun->Address;
        }

        foreach ($data as $row) {
            $appname = $row->{'NameofApplicant'} . ', ';
            $age =  $row->{'age'} . ' year old, ';
            $cstatus =  $row->{'civil status'} . ' and a resident of';
            $address = $row->{'appaddress'};
            $purpose = $row->{'purpose'};
        }
       
            $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '18
        ');
            $html_content = '
            <br />
            <br />
            <br />
        ' . $logo . '
        <h4 align="center" style="line-height: 10px"> OFFICE OF THE MAYOR </h4>
        <br />
        <br />
        <h2 align="center" style="line-height: 5px"> MAYOR`S CLEARANCE</h2>
        <style>
        table{
            width:100%;
            margin: 50px;
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
            <td style="width:100%;" align="left">To Whom it May Concern:</td>
        </tr>
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:28%;" align="left">Clearance is hereby granted to</td>
            <td style="width:28%;border-bottom:0.5px solid black;" align="center">' . $appname . '</td>
            <td style="width:11%;" align="center">' . $age . '</td>
            <td style="width:25%;" align="left">' . $cstatus . '</td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"></td>
            <td style="width:71%;border-bottom:0.5px solid black;" align="center">' . $address . '</td> 
            <td style="width:28%;" align="center">,  whose  record, namely  Police </td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">Clearance Certificate and Barangay Clearance, show that is law-abiding citizen and has neither in any manner engage</td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">in nor associated to any organization inimical to the interest of the community.</td>
        </tr>
        <br />
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:62%;" align="left">The certification is issued upon the request of the above-name person for</td>
            <td style="width:29%;border-bottom:0.5px solid black;" align="center">' . $purpose . '</td>
        </tr>
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:10%;" align="left">Given this </td>
            <td style="width:5%;border-bottom:0.5px solid black;" align="center">' . date("j") . '</td>
            <td style="width:8%;" align="left">day of</td>
            <td style="width:9%;border-bottom:0.5px solid black;" align="center">' . date("F") . '</td>
            <td style="width:1%;" align="left"></td>
            <td style="width:6%;border-bottom:0.5px solid black;" align="center">' . date("Y") . '</td>
            <td style="width:3%;" align="left">at</td>
            <td style="width:50%;" align="left">' . $citymundata . '</td>
        </tr>
        <br />
        <br />
        <br />
        <tr style="height:25px" align="left">   
            <td style="width:60%">            
            </td> 
            <td style="width:38%; border-bottom:0.5px solid black;" align="center">
                Atty. Kristine Vanessa T. Chiong
            </td>                             
        </tr>
        <tr style="height:25px" align="left">   
            <td style="width:60%">            
            </td> 
            <td style="width:38%;" align="center">
                City Mayor
            </td>                             
        </tr>
    </table>';

            PDF::SetTitle('CERTIFICATE');
            PDF::AddPage('P');
            PDF::SetFont('Helvetica', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printRecommendationcert($id)
    {
        $data = DB::select('call ' . $this->lgu_db . '.spl_print_Cert_profile1_gigil(?)', array($id));

        $muncity = DB::select('Call ' . $this->lgu_db . '.display_citymun_gigil()');

        foreach ($muncity as $rowciymun) {
            $citymundata = $rowciymun->Address;
        }

        foreach ($data as $row) {
            $appname = $row->{'NameofApplicant'} . ', ';
            $age =  $row->{'age'} . ' year old, ';
            $cstatus =  $row->{'civil status'};
            $address = 'of ' . $row->{'appaddress'};
            $purpose = $row->{'purpose'} . '.';
        }
        $citymun =
            $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '18
        ');
            $html_content = '
            <br />
            <br />
            <br />
        ' . $logo . '
        <h4 align="center" style="line-height: 10px"> OFFICE OF THE MAYOR </h4>
        <br />
        <br />
        <h2 align="center" style="line-height: 5px"> MAYOR`S RECOMMENDATION</h2>
        <style>
        table{
            width:100%;
            margin: 50px;
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
            <td style="width:100%;" align="left">' . date("F j, Y") . '</td>
        </tr>
        <br>
        <tr>
            <td style="width:30%;border-bottom:0.5px solid black;" align="left"></td>
        </tr>
        <tr>
            <td style="width:100%;" align="left"></td>
        </tr>
        <tr>
            <td style="width:100%;" align="left"></td>
        </tr>
        <br />
        <br />
        <br />
        <tr>
            <td style="width:100%;" align="left">Sir/Madam: </td>
        </tr>
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:37%;" align="left">I personally recommend to you the bearer,</td>
            <td style="width:30%;" align="center"><b>' . $appname . '</b></td>
            <td style="width:33%;" align="left">' . $address . '</td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:40%;" align="center">who is interested to apply in your company as </td> 
            <td style="width:28%;" align="left">' . $purpose . '</td>
        </tr>
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">He/she is resident of this city with good moral and social standing.</td>
        </tr>
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">Thank you and more power to you and your company.</td>
        </tr>
        <br />
        <br />
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:62%;" align="left">Very truly yours,</td>
        </tr>
        <br />
        <br />
        <br />
        <br />
        <tr style="height:25px" align="left">   
            <td style="width:38%;" align="left"><b>Atty. Kristine Vanessa T. Chiong</b></td>                             
        </tr>
        <tr style="height:25px" align="left">   
            <td style="width:38%;" align="left">
                City Mayor
            </td>                             
        </tr>
    </table>';

            PDF::SetTitle('CERTIFICATE');
            PDF::AddPage('P');
            PDF::SetFont('Helvetica', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printCertification($id)
    {
        $data = DB::select('call ' . $this->lgu_db . '.spl_print_Cert_profile1_gigil(?)', array($id));

        $muncity = DB::select('Call ' . $this->lgu_db . '.display_citymun_gigil()');

        foreach ($muncity as $rowciymun) {
            $citymundata = $rowciymun->Address;
        }

        foreach ($data as $row) {
            $appname = $row->{'NameofApplicant'};
            $headed = $row->{'grantedto'};
            $age =  $row->{'age'} . ' year old, ';
            $cstatus =  $row->{'civil status'} . ' and a resident of';
            $address = $row->{'appaddress'};
            $purpose = $row->{'purpose'};
        }
        $citymun =
            $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '18
        ');
            $html_content = '
            <br />
            <br />
            <br />
        ' . $logo . '
        <h4 align="center" style="line-height: 10px"> OFFICE OF THE MAYOR </h4>
        <br />
        <br />
        <h2 align="center" style="line-height: 5px"> CERTIFICATE OF GOOD REPUTE</h2>
        <style>
        table{
            width:100%;
            margin: 50px;
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
            <td style="width:100%;" align="left">To Whom it May Concern:</td>
        </tr>
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:28%;" align="left">Clearance is hereby granted to</td>
            <td style="width:28%;" align="center"><b>' . $appname . '</b></td>
            <td style="width:12%;" align="left">, headed by </td>
            <td style="width:28%;" align="left"><b>' . $headed . '</b></td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"></td>
            <td style="width:71%;border-bottom:0.5px solid black;" align="center">' . $address . '</td> 
            <td style="width:28%;" align="center">,  whose  record, namely  Police </td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">Clearance Certificate and Barangay Clearance, show that is law-abiding citizen and has neither in any manner engage</td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">in nor associated to any organization inimical to the interest of the community.</td>
        </tr>
        <br />
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:62%;" align="left">The certification is issued upon the request of the above-name person for</td>
            <td style="width:29%;border-bottom:0.5px solid black;" align="center">' . $purpose . '</td>
        </tr>
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:10%;" align="left">Given this </td>
            <td style="width:5%;border-bottom:0.5px solid black;" align="center">' . date("j") . '</td>
            <td style="width:8%;" align="left">day of</td>
            <td style="width:9%;border-bottom:0.5px solid black;" align="center">' . date("F") . '</td>
            <td style="width:1%;" align="left"></td>
            <td style="width:6%;border-bottom:0.5px solid black;" align="center">' . date("Y") . '</td>
            <td style="width:3%;" align="left">at</td>
            <td style="width:50%;" align="left">' . $citymundata . '</td>
        </tr>
        <br />
        <br />
        <br />
        <tr style="height:25px" align="left">   
            <td style="width:60%">            
            </td> 
            <td style="width:38%; border-bottom:0.5px solid black;" align="center">
                Atty. Kristine Vanessa T. Chiong
            </td>                             
        </tr>
        <tr style="height:25px" align="left">   
            <td style="width:60%">            
            </td> 
            <td style="width:38%;" align="center">
                City Mayor
            </td>                             
        </tr>
    </table>';

            PDF::SetTitle('CERTIFICATE');
            PDF::AddPage('P');
            PDF::SetFont('Helvetica', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printMayorEmployment($id)
    {
       
        $data = DB::select('call ' . $this->lgu_db . '.spl_print_Cert_profile1_gigil(?)', array($id));

        $muncity = DB::select('Call ' . $this->lgu_db . '.display_citymun_gigil()');

        foreach ($muncity as $rowciymun) {
            $citymundata = $rowciymun->Address;
        }
        
        foreach ($data as $row) {
            $appname = $row->{'NameofApplicant'};
            $headed = $row->{'grantedto'};
            $age =  $row->{'age'} . ' year old, ';
            $cstatus =  $row->{'civil status'} . ' and a resident of';
            $address = $row->{'appaddress'};
            $purpose = $row->{'purpose'};
        }
       
            $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '18
        ');
            $html_content = '
            <br />
            <br />
            <br />
        ' . $logo . '
        <h4 align="center" style="line-height: 10px"> OFFICE OF THE MAYOR </h4>
        <br />
        <br />
        <h2 align="center" style="line-height: 5px"> RENTAL PERMIT </h2>
        <style>
        table{
            width:100%;
            margin: 50px;
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
            <td style="width:7%;"></td>
            <td style="width:24%;" align="left">Permit is hereby granted to </td>
            <td style="width:28%;" align="center"><b>' . $appname . '</b></td>
            <td style="width:4%;" align="left"> of </td>
            <td style="width:35%;" align="left"><b>' . $address . '</b></td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"></td>
            <td style="width:71%;border-bottom:0.5px solid black;" align="center"></td> 
            <td style="width:28%;" align="center">,  whose  record, namely  Police </td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">Clearance Certificate and Barangay Clearance, show that is law-abiding citizen and has neither in any manner engage</td>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%;" align="left">in nor associated to any organization inimical to the interest of the community.</td>
        </tr>
        <br />
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:62%;" align="left">The certification is issued upon the request of the above-name person for</td>
            <td style="width:29%;border-bottom:0.5px solid black;" align="center">' . $purpose . '</td>
        </tr>
        <br />
        <tr style="line-height:10px" align="left">
            <td style="width:7%;"></td>
            <td style="width:10%;" align="left">Given this </td>
            <td style="width:5%;border-bottom:0.5px solid black;" align="center">' . date("j") . '</td>
            <td style="width:8%;" align="left">day of</td>
            <td style="width:9%;border-bottom:0.5px solid black;" align="center">' . date("F") . '</td>
            <td style="width:1%;" align="left"></td>
            <td style="width:6%;border-bottom:0.5px solid black;" align="center">' . date("Y") . '</td>
            <td style="width:3%;" align="left">at</td>
            <td style="width:50%;" align="left">' . $citymundata . '</td>
        </tr>
        <br />
        <br />
        <br />
        <tr style="height:25px" align="left">   
            <td style="width:60%">            
            </td> 
            <td style="width:38%; border-bottom:0.5px solid black;" align="center">
                Atty. Kristine Vanessa T. Chiong
            </td>                             
        </tr>
        <tr style="height:25px" align="left">   
            <td style="width:60%">            
            </td> 
            <td style="width:38%;" align="center">
                City Mayor
            </td>                             
        </tr>
    </table>';
       
            PDF::SetTitle('CERTIFICATE');
            PDF::AddPage('P');
            PDF::SetFont('Helvetica', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
