<?php

namespace App\Http\Controllers\Api\MissionOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class missionorderController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //public function store(Request $request)
    //
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
    public function purposeDescription()
    {
        $list = DB::select('select * from ' . $this->lgu_db . '.ebplo_mission_order_purpose');
        return response()->json(new JsonResponse($list));
    }
    public function filterData(Request $request)
    {
        $datefrom = $request->from;
        $dateto = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_ebplo_mission_order_form(?,?)', array($datefrom, $dateto));
        return response()->json(new JsonResponse($list));
    }
    public function displayDetails($id)
    {
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_ebplo_mission_order_detail(?)', array($id));
        return response()->json(new JsonResponse($list));
    }
    public function getBusMasterlist(Request $request)
    {
        // dd($request);
        $dateFrom = $request->from;
        $dteTo = $request->to;
        $brgy = $request->barangays;
        $permitstat = "'" . $request->bus_stat . "'";
        $transtype = "'" . $request->bustype . "'";

        if ($brgy === '%') {
            $brgy = "All";
        }

        if ($permitstat === '%') {
            $permitstat = "All";
        }

        if ($transtype === '%') {
            $transtype =  "'All'";
        }

        $list = DB::select(
            'call ' . $this->lgu_db .
                '.spl_ebplo_business_masterlist_gigil1(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $dateFrom, $dteTo, 'With', $brgy, 'All', 'All', 'All', 'All', 'All', 'All', 'All', 'All', 'All', 'All',
            ]
        );

        return response()->json(new JsonResponse($list));
    }
    public function printList(Request $request)
    {
        $data = $request->main;
        $details = $request->details;
        $filter = $request->filter;
        $from = date("F j, Y", strtotime($filter['from']));
        $to =  date("F j, Y", strtotime($filter['to']));

        if ($filter['filter'] == "Year") {
            $filters = "Year " . date("Y", strtotime($filter['from']));
        } elseif ($filter['filter'] == "Month") {
            $filters = "Month of " . date("F Y", strtotime($filter['from']));
        } else {
            $filters = "As of " .  $from . ' - ' . $to;
        }
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 style="width:14%;text-align:center;font-size:13px">MISSION ORDER LIST</h2>
        <h3 style="width:14%;text-align:center;font-size:11px">' . $filters . '</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:10%;font-size:9px">Transaction No.</th>
        <th style = "width:15%;font-size:9px">Transaction Date</th>
        <th style = "width:15%;font-size:9px">Inspection Date From</th>
        <th style = "width:15%;font-size:9px">Inspection Date To</th>
        <th style = "width:15%;font-size:9px">Compliance Date</th>
        <th style = "width:20%;font-size:9px">Mission Order</th>
        <th style = "width:10%;font-size:9px">Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td style="width:10%;text-align:center;font-size:8px">' . $main['Transaction No'] . '</td>
            <td style="width:15%;text-align:center;font-size:8px">' . $main['Transaction Date'] . '</td>
            <td style="width:15%;text-align:center;font-size:8px">' . $main['Date of Inspection From'] . '</td>
            <td style="width:15%;text-align:center;font-size:8px">' . $main['Date of Inspection To'] . '</td>
            <td style="width:15%;text-align:center;font-size:8px">' . $main['Compliance Date'] . '</td>
            <td style="width:20%;text-align:center;font-size:8px">' . $main['Mission Order'] . '</td>
            <td style="width:10%;text-align:center;font-size:8px">' . $main['Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';

            $html_content .= '
        <h2 style="width:14%;text-align:center;font-size:10px">MISSION ORDER DETAILS</h2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:25%;font-size:9px">Business Name</th>
        <th style = "width:20%;font-size:9px">Name of Owner</th>
        <th style = "width:12%;font-size:9px">Barangay</th>
        <th style = "width:13%;font-size:9px">Type of Business</th>
        <th style = "width:10%;font-size:9px">Business Status</th>
        <th style = "width:10%;font-size:9px">Permit Status</th>
        <th style = "width:10%;font-size:9px">Contact Number</th>
        </tr>
        <tbody>';
            foreach ($details as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td style="width:25%;text-align:left;font-size:8px">' . $main['Business Name'] . '</td>
            <td style="width:20%;text-align:left;font-size:8px">' . $main['Name of Owner'] . '</td>
            <td style="width:12%;text-align:center;font-size:8px">' . $main['Barangay'] . '</td>
            <td style="width:13%;text-align:center;font-size:8px">' . $main['Type of Business'] . '</td>
            <td style="width:10%;text-align:center;font-size:8px">' . $main['Business Status'] . '</td>
            <td style="width:10%;text-align:center;font-size:8px">' . $main['Permit Status'] . '</td>
            <td style="width:10%;text-align:center;font-size:8px">' . $main['Contact No'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';

            PDF::SetTitle('Sample');
            PDF::AddPage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }
    public function printForm(Request $request)
    {
        $main = $request->main;
        $details = db::select('CALL ' . $this->lgu_db . '.spl_display_ebplo_mission_order_detail(' . $main['mof_id'] . ')');
        $cc = DB::table($this->lgu_db . '.ebplo_mission_order_detail_cc_to')->where('type', 'cc')->where('mof_id', $main['mof_id'])->get();
        // dd($cc);
        $logo = config('variable.logo');
        try {
            $html_content = '   
        ' . $logo . '
        <br>
        <h1 style="width:14%;text-align:center;font-size:14px">BUSINESS PERMIT AND LICENSING OFFICE</h1>
        <h2 style="width:14%;text-align:center;font-size:13px">Mission Order No. <b><u>' . $main['Transaction No'] . '</u></b></h2>
        <br>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<br><br/>
<table width ="100%">
         <br>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;"><b>TO:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   ' . $main['Dept To'] . '</span>
            </td>                   
         </tr>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   Team Leader - ' . $main['Dept To'] . '</span>
            </td>                   
         </tr>
         <br>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;"><b>FROM:</b>&nbsp;&nbsp;&nbsp;&nbsp;' . $main['Dept Fr'] . '</span>
            </td>                   
         </tr>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">-------------------------------------------------------------------------------------------------------------------------------------</span>
            </td>                   
         </tr>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   You are hereby ordered to ' . $main['Subject'] . ' of Business Permit applicants as listed on ' . date("F d, Y", strtotime($main['Date of Inspection From'])) . ' to ' . date("F d, Y", strtotime($main['Date of Inspection To'])) . '.</span>
            </td>                   
         </tr>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:5%;font-size:10px"><b>No.</b></th>
        <th style = "width:35%;font-size:10px"><b>Business Name</b></th>
        <th style = "width:25%;font-size:10px"><b>Name of Owner</b></th>
        <th style = "width:35%;font-size:10px"><b>Business Address</b></th>
        </tr>
        <tbody>';
            $count = 1;
            foreach ($details as $row) {
                $html_content .= '
            <tr>
            <td style="width:5%;text-align:center;font-size:9px">' . $count++ . '</td>
            <td style="width:35%;text-align:left;font-size:9px">' . $row->{'Business Name'} . '</td>
            <td style="width:25%;text-align:left;font-size:9px">' . $row->{'Name of Owner'} . '</td>
            <td style="width:35%;text-align:left;font-size:9px">' . $row->{'Business Address'} . '</td>
            </tr>';
            }
            $html_content .= '</tbody> 
        </table> 
         <br>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">' . $main['mof_remarks'] . '</span>
            </td>                   
         </tr>
         <tr style="height:25px">   
            <td style="width:100%"><span style="text-align:justify;line-height:20px;">Date:   ' . date("F d, Y", strtotime($main['Transaction Date'])) . '</span>
            </td>                   
         </tr> 
         <br><br/>    
         <tr style="height:25px">   
            <td style="width:60%">Recommending Approval:
            </td>   
            <td style="width:40%"><span style="text-align:justify;line-height:20px;">Approved By:</span>
            </td>                 
         </tr>
         <br>
         <br>
         <tr style="height:25px">   
            <td style="width:60%"><b>Minnie C. Abangan</b>
            </td>
            <td style="width:40%"><span style="text-align:justify;line-height:20px;"><b>' . $main['From'] . '</b></span>
            </td>                   
         </tr>
         <tr style="height:25px">   
            <td style="width:60%">Team Leader
            </td>
            <td style="width:40%"><span style="text-align:justify;line-height:20px;">' . $main['from_pos'] . '</span>
            </td>                   
         </tr>                  
</table>  
<br><br/>
<br><br/>
<table width ="100%">
        ';

            $html_content .= '
                <tr style="height:25px">
                <td style="width:5%"><span style="text-align:justify;line-height:20px;">Cc:</span></td>
                <td style="width:95%"><span style="text-align:justify;font size:9px;">';
            foreach ($cc as $key) {

                $html_content .= $key->t_member . "<br/>";
            };

            $html_content .= '</span></td>

                </tr> ';

            $html_content .= '                   
                                        
</table>';
            PDF::SetTitle('Sample');
            PDF::AddPage('');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error', 'error' => $e]));
        }
    }
    public function ref(Request $request)
    {
        $pre = 'MO';
        $table = $this->lgu_db . ".ebplo_mission_order_form";
        $date = $request->date;
        $refDate = 'mof_reg_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function store(Request $request)
    {
        try {
            //DB::beginTransaction();
            //dd($request->details);
            // dd($request);

            $main = $request->main;
            $ctobill = $request->cto;
            $idx = $request->main['potability_id'];
            if ($idx > 0) {
                $this->update($idx, $main, $ctobill);
            } else {
                $this->save($main, $ctobill);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error Saving Data!', 'errormsg' => $err, 'status' => 'error']));
        }
    }

    public function save($main, $ctobill)
    {

        DB::table($this->lgu_db . '.cho1_potability_permit')->insert($main);
        $id = DB::getPDo()->lastInsertId();
        $signatory = DB::select('Call ' . $this->lgu_db . '.cvl_get_signatory_mayor_head()');
        foreach ($signatory as $row) {
            $sign = array(
                'form_id' => $id,
                'form_name' => 'Water Potability',
                'bns_id' => 0,
                'pp_id' => $main['person_business_name_id'],
                'user_id' => Auth::user()->id,
                'head_id' => $row->health_head_id,
                'head_position' => $row->health_head_pos,
                'head_name' => $row->health_head_name,
                'mayor_id' => $row->mayor_id,
                'mayor_position' => $row->mayor_pos,
                'mayor_name' => $row->mayor_name,
            );
            DB::table($this->general . '.signatory_logs')->insert($sign);
        }
        foreach ($ctobill as $row) {
            if ($row['Include'] === "True") {

                $cto = array(
                    'payer_type' => $main['app_type'],
                    'payer_id' => $main['person_business_name_id'],
                    'business_application_id' => $main['person_business_name_id'],
                    'account_code' => $row['Account Code'],
                    'bill_description' => $row['Account Description'],
                    'net_amount' => $row['Initial Amount'],
                    'bill_amount' => $row['Fee Amount'],
                    'bill_month' => $main['trans_date'],
                    'bill_number' => $main['certificate_number'],
                    'transaction_type' => 'Water Potability',
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'include_from' => 'Others',
                );
                DB::table($this->lgu_db . '.cto_general_billing')->insert($cto);
            }
        }
    }

    public function editData($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.cho1_potability_permit')->where('potability_id', $id)->get();
        $data['water'] = DB::table($this->lgu_db . '.cho1_potability_permit')->where('potability_id', $id)->get();

        return response()->json(new JsonResponse($data));
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.cho1_potability_permit')->where('potability_id', $id)->update($data);

        $reason['Form_name'] = 'Water Potability';
        $reason['Trans_ID'] = $id;
        $reason['Type_'] = 'Cancel Record';
        $reason['Trans_by'] = Auth::user()->id;

        $this->G->insertReason($reason);

        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

    public function update($idx, $main, $ctobill)
    {
        DB::table($this->lgu_db . '.cho1_potability_permit')->where('potability_id', $idx)->update($main);
    }
    public function getInspection($main)
    { {
            try {
                $data['main'] = DB::table($this->lgu_db . '.cho1_potability_permit')->where('insp_name')->get();
                return response()->json(new JsonResponse(['Message' => 'Successfully Inspected.', 'status' => 'success']));
            } catch (\Exception $err) {

                return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
            }
        }
    }

    public function updateInspection(Request $request)
    {
        $id = $request->potability_id;
        //dd($request);
        $main = array(
            'insp_id' => $request->insp_id, 'insp_name' => $request->insp_name
        );
        DB::table($this->lgu_db . '.cho1_potability_permit')->where('potability_id', $id)->update($main);
        return response()->json(new JsonResponse(['Message' => 'Successfully Inspected.', 'status' => 'success']));
    }
}
