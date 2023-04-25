<?php

namespace App\Http\Controllers\Api\Mayors;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;

use PDF;

class mayorsRecommendation extends Controller
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
    public function getRequirements()
    {
        $list = DB::select('Call ' . $this->lgu_db . '.profile_requirements_gen()');
        return response()->json(new JsonResponse($list));
    }
    public function mayorsRecommendationList(Request $request)
    {
        // dd($request['formtype']);
        $dateFrom = $request['from'];
        $dateTo = $request['to'];
        $_formname = $request['formtype'];

        $list = DB::select('call ' . $this->lgu_db . '.spl_display_profile1_gen(?,?,?)', array($dateFrom, $dateTo, $_formname));

        // dd($list);
        return response()->json(new JsonResponse($list));
    }
    public function ref(Request $request)
    {
        $pre = 'MRP';
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
            $reference = $request->reference;

            $idx = $mainData['pkid'];
            if ($idx > 0) {
                unset($mainData['applicantName']);
                unset($mainData['grantedto']);
                $this->update($idx, $mainData, $cedulaData, $requirements, $reference, $cc);
            } else {
                $this->save($mainData, $cedulaData, $requirements, $reference, $cc);
            };
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function save($mainData, $cedulaData, $requirements, $reference, $cc)
    {
        db::table($this->lgu_db . '.ebplo_tbl_profile')->insert($mainData);
        $id = DB::getPDo()->lastInsertId();
        $this->save_details($id, $mainData, $cedulaData, $requirements, $reference, $cc);
    }
    public function save_details($id, $mainData, $cedulaData, $requirements, $reference, $cc)
    {
        //Billing
        $fees = DB::select('Call ' . $this->lgu_db . '.ebplo_display_accounts_jho(20)');
        $cntlimit = $mainData['noofcounts'];
        $incomeamount = 0;
        $incomecode = '';
        $incomedesc = '';
        $incomeID = 0;

        foreach ($fees as $row) {
            if ($row->Type_ === 'FIXED') {
                $billing = array(
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'payer_type' => $mainData['apptype'],
                    'transaction_type' => "Mayor`s Recommendation",
                    'bill_number' => $mainData['appno'],
                    'payer_id' => $mainData['appid'],
                    'business_application_id' => $mainData['appid'],
                    'account_code' => $row->income_account_code,
                    'bill_description' => $row->income_account_description,
                    'bill_month' => $mainData['appdate'],
                    'bill_amount' => $row->base_amount
                );
                DB::table($this->lgu_db . '.cto_general_billing')->insert($billing);
                $id = DB::getPDo()->lastInsertId();

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
                        'account_code' => $row->income_account_code,
                        'bill_description' => $row->income_account_description,
                        'bill_amount' => $row->base_amount,
                        'bill_month' => $mainData['appdate'],
                        'transaction_type' => "Mayor`s Recommendation",
                        'ref_id' => $id,
                        'bill_id' => $id
                    );
                    DB::table($this->lgu_db . '.cto_general_billing')->insert($fee);

                    $profile_bill = array(
                        'mainid' => $id,
                        "accountid" => $row->id,
                        "accountcode" => $row->income_account_code,
                        "feeamount" => $row->base_amount
                    );
                    DB::table($this->lgu_db . '.ebplo_tbl_profile_fees')->insert($profile_bill);
                }
            }
        }
    }

    public function delete(Request $request)
    {

        $id = $request->id;
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.ebplo_tbl_profile')->where('pkid', $id)->update($data);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function edit(Request $request, $id)
    {
        $dateFrom = $request['from'];
        $dteTo = $request['to'];
        $transtype = $request['transtype'];
        try {

            $datax = DB::table($this->lgu_db . '.cho1_sanitation_permit')
                ->where('sanitation_id', $id)->get();
            $data['mainData'] = $datax;
            foreach ($datax as $row) {
                $appid = $row->app_pProfile_id;
            }

            $app = DB::table($this->lgu_db . '.ebplo_business_application')
                ->where('business_app_id', $appid)->get();

            $data['businessapp'] = $app;
            foreach ($app as $app) {

                $profile_id = $app->owner;
            }

            $data['person'] = DB::table($this->lgu_db . '.hr_person_profile')
                ->where('pp_person_code', $profile_id)->get();

            $data['fees'] = DB::table($this->lgu_db . '.cto_general_billing')
                ->select(
                    'ref_id as id',
                    'payer_type',
                    'transaction_type',
                    'bill_number',
                    'payer_id',
                    'business_application_id',
                    'account_code',
                    'bill_description',
                    'net_amount',
                    'bill_amount'
                )
                ->where('bill_id', $id)->get();
            return response()->json(new JsonResponse($data));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function update($idx, $mainData, $fees, $reason)
    {
        $mainData['or_no'] = '';
        DB::table($this->lgu_db . '.cho1_sanitation_permit')->where('sanitation_id', $idx)->update($mainData);

        $reason = $reason;
        $reason['Form_name'] = 'Sanitary Application';
        $reason['Trans_ID'] = $idx;
        $reason['Type_'] = 'Modify Record';
        $reason['Trans_by'] = Auth::user()->id;

        $this->G->insertReason($reason);
        return response()->json(new JsonResponse(['Message' => 'Updated Successfully.', 'status' => 'success']));
    }
    //PRINT REPORTS CONTROLLER
    public function printRecommendationList(Request $request)
    {
        $logo = config('variable.logo');
        try {
            $main = $request->main;

            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
                ' . $logo . ' 
          <h3 align="center">MAYOR`S RECOMMENDATION MASTER LIST</h3>
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
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>OPERATOR</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>BARANGAY</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>NO. OF UNITS</b><br></th>
             <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>DRIVER</b><br></th>
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
        <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['applicantName'] . '</td>
        <td style="border:0.5px solid black;text-align:left;">' . $row['brgy'] . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['units'] . '</td>    
        <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['grantedto'] . '</td>
        <td style="border:0.5px solid black;text-align:left;">' . $row['purpose'] . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['valid'] . '</td>   
        <td style="border:0.5px solid black;text-align:center;">' . $row['issue_date'] . '</td>   
        <td style="border:0.5px solid black;text-align:right;">' . $row['PermitFee'] . '</td> 
        <td style="border:0.5px solid black;text-align:center;width:5%;">' . $row['ORNo'] . '</td> 
        <td style="border:0.5px solid black;text-align:center;width:5%;">' . $row['ORDate'] . '</td>
        <td style="border:0.5px solid black;text-align:center;">' . $row['PaymentStatus'] . '</td>  
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
            PDF::SetTitle('Mayor`s Recommendation Master List');
            PDF::AddPage('L', array(250, 350));
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
