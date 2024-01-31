<?php

namespace App\Http\Controllers\Api\Mod_HR\individual;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class individualController extends Controller
{
    private $lgu_db;
    private $hr_db;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }

    public function getReqName()
    {
        $list = DB::table($this->hr_db . '.tbl_official_business')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.emp_id')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function HeadList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_official_business')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_official_business.emp_id')
            // ->where("Head_Dept", Auth::user()->Employee_id)

            ->Where(function ($query) {
                $query->Where('Head_Dept', Auth::user()->Employee_id)
                    ->orWhere('AssistantHead_Dept', Auth::user()->Employee_id);
            })
            ->where('status', $stat)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function HeadListApproved(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_official_business')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_official_business.emp_id')
            ->where("ob_recom_by", Auth::user()->Employee_id)
            // ->where('status', $stat)
            ->get();
        return response()->json(new JsonResponse($list));
    }


    public function HeadApprovalApproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_official_business')
                ->where("ob_id", $value['ob_id'])
                ->update(['status' => 'For Posting', 'ob_recom_by' => Auth::user()->Employee_id, 'ob_recommended_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function HeadApprovalDisApproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_official_business')
                ->where("ob_id", $value['ob_id'])
                ->update(['status' => 'Declined', 'ob_recom_by' => Auth::user()->Employee_id, 'ob_recommended_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function GetEmpName(Request $request)
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->where('PPID', Auth::user()->Employee_id)
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function show()
    {
        $list = DB::table($this->hr_db . '.tbl_official_business')
            ->join($this->hr_db . '.tbl_official_business_dtl', 'tbl_official_business_dtl.ob_id', 'tbl_official_business.ob_id')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.emp_id')
            ->select('tbl_official_business_dtl.*', 'tbl_official_business.*', 'employee_information.NAME', 'tbl_official_business.ob_id')
            ->where('tbl_official_business.type', 'Individual')
            ->where('emp_id', Auth::user()->Employee_id)
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function reference(Request $request)
    {
        // dd($request);
        $pre = 'PS';
        $table = $this->hr_db . ".tbl_official_business";
        $date = $request->date;
        $refDate = 'ob_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function Edit($id)
    {
        $data['FormA'] = db::table($this->hr_db . '.tbl_official_business')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.emp_id')
            ->where('ob_id', $id)
            ->get();

        $data['FormB'] = db::table($this->hr_db . '.tbl_official_business_dtl')
            ->where('ob_id', $id)
            ->get();

        return response()->json(new JsonResponse($data));
    }

    public function store(Request $request)
    {
        $form = $request->form;
        // unset($form['PPID']);
        $formx = $request->formx;
        $id = $form['ob_id'];
        $form['emp_id'] = Auth::user()->Employee_id;
        if ($id > 0) {
            DB::table($this->hr_db . '.tbl_official_business')
                ->where("ob_id", $id)
                ->update($form);

            db::table($this->hr_db . ".tbl_official_business_dtl")
                ->where("ob_id", $id)
                ->delete();

            $formz = array(
                'ob_id' => $id,
                'ob_off_timearr' => $formx['ob_off_timearr'],
                'ob_off_timedept' => $formx['ob_off_timedept'],
                'ob_dest_timearr' => $formx['ob_dest_timearr'],
                'dtr_date' => $formx['dtr_date'],
                'ob_dest_timedept' => $formx['ob_dest_timedept'],
                'am_in_note' => $formx['am_in_note'],
                'am_out_note' => $formx['am_out_note'],
                'pm_in_note' => $formx['pm_in_note'],
                'pm_out_note' => $formx['pm_out_note'],
                // 'ob_off_verdept'=>$formx['ob_off_verdept']

            );
            db::table($this->hr_db . ".tbl_official_business_dtl")->insert($formz);
        } else {
            $form['emp_id'] = Auth::user()->Employee_id;
            DB::table($this->hr_db . '.tbl_official_business')
                ->insert($form);
            $id = DB::getPdo()->lastInsertId();

            $formz = array(
                'ob_id' => $id,
                'ob_off_timearr' => $formx['ob_off_timearr'],
                'ob_off_timedept' => $formx['ob_off_timedept'],
                'ob_dest_timearr' => $formx['ob_dest_timearr'],
                'dtr_date' => $formx['dtr_date'],
                'ob_dest_timedept' => $formx['ob_dest_timedept'],
                'am_in_note' => $formx['am_in_note'],
                'am_out_note' => $formx['am_out_note'],
                'pm_in_note' => $formx['pm_in_note'],
                'pm_out_note' => $formx['pm_out_note'],
                // 'ob_off_verdept'=>$formx['ob_off_verdept']
            );
            db::table($this->hr_db . ".tbl_official_business_dtl")->insert($formz);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function cancel($id)
    {
        db::table($this->hr_db . '.tbl_official_business')
            ->where('ob_id', $id)
            ->update(['status' => 'Cancelled']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function print2(Request $request)
    {
        try {
            $form = $request->itm;
            $sworn = db::table($this->hr_db . '.tbl_official_business')
                ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.emp_id')
                ->select(
                    '*',
                    //  db::raw("TIME_FORMAT(int_depart, '%h:%i %p') as 'int_depart'"),
                    // db::raw("TIME_FORMAT(int_arrival, '%h:%i %p') as 'int_arrival'"),
                    // db::raw("TIME_FORMAT(Actual_Depart, '%h:%i %p') as 'Actual_Depart'"),
                    // db::raw("TIME_FORMAT(Actual_Arrival, '%h:%i %p') as 'Actual_Arrival'"),
                    'employee_information.NAME',
                    'employee_information.POSITION',
                    'tbl_official_business.ob_id',
                    db::raw("CONCAT('Digitally signed by '," . $this->hr_db . "._getEmployeeName(`ob_recom_by`),'<br/> Dated ',IFNULL( `ob_recommended_date`,'')) AS 'dept_app_time'"),
                    db::raw("IFNULL(" . $this->hr_db . "._get_signature (`ob_recom_by`), '') AS 'dept_app'")
                )
                ->where('ob_id', $form['ob_id'])
                ->get();
            log::Debug($sworn);
            $IndividData = "";
            foreach ($sworn as $key => $value) {
                $IndividData = $value;
            }

            $certified = db::table($this->hr_db . '.tbl_official_business_dtl')
                ->select(
                    '*',
                    db::raw("TIME_FORMAT(ob_off_timedept, '%h:%i %p') as 'ob_off_timedept'"),
                    db::raw("TIME_FORMAT(ob_off_timearr, '%h:%i %p') as 'ob_off_timearr'"),
                    db::raw("TIME_FORMAT(ob_dest_timedept, '%h:%i %p') as 'ob_dest_timedept'"),
                    db::raw("TIME_FORMAT(ob_dest_timearr, '%h:%i %p') as 'ob_dest_timearr'")
                )
                ->where('ob_id', $form['ob_id'])
                ->get();
            $Cert = "";

            foreach ($certified as $key => $value) {
                $Cert = $value;
            }

            $Template = '
            <table width="100%" cellpadding="2" style="border-bottom:1px solid black; border-top:1px solid black;
            border-right:1px solid black; border-left:1px solid black">

            <tr>
            <td width="50%">
            <table width="100%">
                <tr>
                    <th width="35%" align="right">
                    <img src="' . public_path() . '/images/Logo1.png"  height="30" width="30">
                    </th>
                    <th width="35%" style="font-size:9pt;  word-spacing:30px" align="center">
                            <b>Republic of the Philippines</b>
                    <br />
                            Province of Cebu
                    <br />

                    '.env("cityname",false).'
                    <br />

                        </th>

                    <th align="left">
                        <img src="/img/NAGA LOGO2.png"  height="40" width="45">
                    </th>
                 </tr>



        <tr>
            <th width="100%" style="font-size:10pt;  word-spacing:30px" align="center" >
            <b>Indiviual Pass/Time Adjustment Slip</b>
            </th>
        </tr>


            <br />



                <tr>
                <td width="2%"></td>

                    <td width="55%" style="border-bottom:1px solid black" align="center">' . $IndividData->NAME . '</td>

                    <td width="15%"></td>
                    <td width="25%" style="border-bottom:1px solid black" align="center">' . $IndividData->ob_date . '</td>
                    <td width="10%"></td>
                </tr>
                <tr>

                    <td width="60%">(Printed name of employee and Signature)</td>
                    <td width="15%"></td>
                    <td width="5%"></td>
                    <td width="17%">Date</td>

                </tr>
            <br />

                <tr>
                    <td width="100%" style="font-size:9pt"><input type="radio" checked="' . ($IndividData->ob_type === 'Outside' ? "true" : "false") . '" name="1" value="1">
                    Leaving the office premises during office hours from: </td>
                </tr>

                <tr>
                    <td width="30%"></td>
                    <td width="40%">Intended time of departure</td>
                    <td width="27%" style="border-bottom:1px solid black" align="center">' . $Cert->ob_off_timedept . '</td>
                </tr>

                <tr>
                    <td width="30%"></td>
                    <td width="40%">Intended time of Arrival</td>
                    <td width="27%" style="border-bottom:1px solid black" align="center">' . $Cert->ob_off_timearr . '</td>
                </tr>
            <br />

                <tr>
                    <td width="100%" style="font-size:9pt"><input type="checkbox" checked="' . ($IndividData->ob_type === 'Premises' ? "true" : "false") . '" name="1" value="1">
                    Within the office premises(meeting, conference, seminar, training)</td>
                </tr>
                <tr>
                <br />
                    <td>
                        <table  style="border-bottom:1px solid black; border-top:1px solid black;
                                        border-right:1px solid black; border-left:1px solid black">
                        <tr>
                            <br />

                            <td height="20" width="10%"></td>
                            <td width="20%">Purpose</td>
                            <td width="35%" align="center" style="font-size:8pt;" >
                                <input type="radio" checked="' . ($IndividData->ob_purpose === 'Official' ? "true" : "false") . '" name="1" value="1">Official
                            </td>
                            <td width="35%" align="center" style="font-size:8pt;" >
                                <input type="radio" checked="' . ($IndividData->ob_purpose === 'Personal' ? "true" : "false") . '" name="1" value="1">Personal
                            </td>
                        </tr>

                        </table>
                    </td>

                </tr>
            <br />
                <tr>
                    <td width="15%">Reason</td>
                    <td width="75%" style="border-bottom:1px solid black">' . $IndividData->ob_remarks . '</td>
                </tr>

                <tr>
                    <td width="5%"></td>
                    <td width="90%" style="border-bottom:1px solid black"></td>
                </tr>

                <tr>
                    <td width="5%"></td>
                    <td width="90%" style="border-bottom:1px solid black"></td>
                 </tr>

            <br />
                <tr>
                    <td width="35%">Actual time of departure</td>
                    <td width="40%" style="border-bottom:1px solid black" align="center">' . $Cert->ob_dest_timedept . '</td>
                </tr>

            <br />

                <tr>
                    <td width="35%">Actual time of Arrival</td>
                    <td width="40%" style="border-bottom:1px solid black" align="center">' . $Cert->ob_dest_timearr  . '</td>
                </tr>
            <br />
                <tr>
                    <td width="25%">Approved by:</td>
                    <td width="65%" style="border-bottom:1px solid black">' . $IndividData->dept_app_time . '</td>
                </tr>
                <tr>
                    <td width="28%"></td>
                    <td width="65%">(Head of Office/Authorized Representative)</td>
            </tr>

                </table>
                </td>


                <td width="49%">
                <table width="100%">
                <tr>
                <br />
                <th width="35%" align="right"><img src="' . public_path() . '/images/Logo1.png"  height="30" width="30"></th>
                <th width="35%" style="font-size:9pt;  word-spacing:30px" align="center">
                        <b>Republic of the Philippines</b>
                <br />
                        Province of Cebu
                <br />
                '.env("cityname",false).'
                <br />
                    </th>
                <th align="left"><img src="/images/Logo1.png"  height="40" width="45"></th>
                </tr>
                    <br />
                <tr>
                    <th width="2%"> </th>
                    <th width="98%" style="font-size:10pt;  word-spacing:30px" align="center" ><b>CERTIFICATE OF APPEARANCE</b></th>
                </tr>

                <tr>
                    <td  width="60%" style="font-size:9pt" align="left">TO WHOM IT MAY CONCERN:</td>
                </tr>
                <tr>

                <td width="100%" height="5x"> </td>
                    </tr>
                    <tr>
                        <td width="15%" style="font-size:9pt" align="center"></td>
                        <td width="82%" style="font-size:9pt; text-align: justify" align="center">This is to certify that I attended to Mr./Ms.</td>
                    </tr>
                    <tr>
                        <td width="3%" style="font-size:9pt" align="center"></td>
                        <td width="80%" style="font-size:9pt; border-bottom: 1px solid black" align="center">' . $IndividData->NAME . '</td>
                        <td width="15%" style="font-size:9pt; text-align: justify" align="center">of the</td>
                    </tr>
                    <tr>
                        <td width="3%"></td>
                        <td width="40%" style="font-size:9pt; border-bottom: 1px solid black" align="center">' . $IndividData->ob_address . '</td>
                        <td width="6%" style="font-size:9pt" align="center">on</td>
                        <td width="30%" style="font-size:9pt; border-bottom: 1px solid black" align="center">' . $IndividData->ob_date . '</td>
                        <td width="5%" style="font-size:9pt" align="center">at</td>
                    </tr>

                    <tr>
                        <td width="3%" style="font-size:9pt" align="center"></td>
                        <td width="38%" style="font-size:9pt; border-bottom: 1px solid black" align="center">' . $Cert->ob_dest_timedept . ' - ' . $Cert->ob_dest_timearr . '</td>
                        <td width="57%" style="font-size:9pt; text-align: justify" align="left">a.m./p.m. When he/she transacted</td>
                    </tr>
                    <tr>
                        <td width="3%" style="font-size:9pt" align="center"></td>
                        <td width="65%" style="font-size:9pt; text-align: justify" align="left">business with my Agency/Company.</td>
                    </tr>
                    <br />
                    <br />
                    <br />
                    <tr>
                        <td width="3%"  style="font-size:9pt" align="center"></td>
                        <td width="50%" style="font-size:9pt; border-bottom: 1px solid black" align="center"></td>
                        <td width="5%" style="font-size:9pt" align="center"></td>
                        <td width="40%" style="font-size:9pt; border-bottom: 1px solid black" align="center"></td>
                    </tr>
                    <tr>
                        <td width="3%" style="font-size:9pt" align="center"></td>
                        <td width="50%" style="font-size:8pt" align="center">Signature over Printed Name of</td>
                        <td width="5%" style="font-size:9pt" align="center"></td>
                        <td width="40%" style="font-size:8pt" align="center">Position</td>
                    </tr>
                    <tr>
                        <td width="3%" style="font-size:9pt" align="center"></td>
                        <td width="50%" style="font-size:8pt" align="center">Attending Employee</td>
                        <td width="5%" style="font-size:9pt" align="center"></td>
                        <td width="40%" style="font-size:9pt" align="center"></td>
                    </tr>

                    <tr>
                        <td width="100%" height="20px"> </td>
                    </tr>

                    <tr>
                        <td width="3%" style="font-size:9pt" align="center"></td>
                        <td width="40%" style="font-size:9pt" align="center"></td>
                        <td width="15%" style="font-size:9pt" align="center">Date:</td>
                        <td width="40%" style="font-size:9pt; border-bottom: 1px solid black" align="center">' . $Cert->dtr_date . '</td>
                    </tr>
                    <tr>
                        <td width="100%" height="20px"> </td>
                    </tr>

                    <tr>
                        <td width="3%" style="font-size:9pt" align="left"></td>
                        <td width="30%" style="font-size:9pt" align="left">Agency/Company</td>
                        <td width="64%" style="font-size:9pt; border-bottom: 1px solid black" align="left">' . $IndividData->ob_agency . '</td>
                    </tr>

                    <tr>
                    <td width="3%" style="font-size:9pt" align="left"></td>
                    <td width="16%" style="font-size:9pt" align="left">Address</td>
                    <td width="78%" style="font-size:9pt; border-bottom: 1px solid black" align="left">' . $IndividData->ob_address . '</td>
                </tr>
                    <tr>
                        <td width="3%" style="font-size:9pt" align="left"></td>
                        <td width="16%" style="font-size:9pt" align="left">Tel. No.</td>
                        <td width="79%" style="font-size:9pt; border-bottom: 1px solid black" align="left">' . $IndividData->ob_tel_no . '</td>
                    </tr>
                    <tr>
                        <td width="3%" style="font-size:9pt" align="left"></td>
                        <td width="95%" style="font-size:8pt" align="left">For verification purposes, additional documents may be required.</td>
                    </tr>
                </table>
                </td>
                </tr>
                </table>
           ';
            // log::debug($IndividData->dept_app);
            PDF::SetTitle('Individual Pass/Time Adjustment Slip');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('P');
            // log::debug(public_path() . $IndividData->dept_app);
            // PDF::Image($value->{'dept_app'}, 50, 107, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image('img/Logo1.png', 50, 107, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image('img/Grace Marquez.png', 50, 107);

            // PDF::Image(public_path() . '/Attach Documents/HR/E-Signatures New/213 Employee Profile/10/Grace Marquez.png', 50, 107, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);

            // PDF::Image(public_path() . $value->{'dept_app'}, 50, 107, 20, 20,  'PNG', 'http://www.tcpdf.org', '', false, 300);
            PDF::Image(public_path() . $IndividData->{'dept_app'}, 50, 107, 27, 27, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            PDF::writeHTML($Template, true, 0, true, 0);

            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);

                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function print(Request $request)
    {
        try {
            $form = $request->itm;
            $sworn = db::table($this->hr_db . '.tbl_official_business')
                ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.emp_id')
                ->select(
                    '*',
                    //  db::raw("TIME_FORMAT(int_depart, '%h:%i %p') as 'int_depart'"),
                    // db::raw("TIME_FORMAT(int_arrival, '%h:%i %p') as 'int_arrival'"),
                    // db::raw("TIME_FORMAT(Actual_Depart, '%h:%i %p') as 'Actual_Depart'"),
                    // db::raw("TIME_FORMAT(Actual_Arrival, '%h:%i %p') as 'Actual_Arrival'"),
                    'employee_information.NAME',
                    'employee_information.POSITION',
                    'tbl_official_business.ob_id',
                    db::raw($this->hr_db . "._getEmployeeName(`ob_recom_by`) AS 'dept_app_time'"),
                    db::raw("IFNULL(" . $this->hr_db . "._get_signature (`ob_recom_by`), '') AS 'dept_app'")
                )
                ->where('ob_id', $form['ob_id'])
                ->get();
            log::Debug($sworn);
            $IndividData = "";
            foreach ($sworn as $key => $value) {
                $IndividData = $value;
            }

            $certified = db::table($this->hr_db . '.tbl_official_business_dtl')
                ->select(
                    '*',
                    db::raw("TIME_FORMAT(ob_off_timedept, '%h:%i %p') as 'ob_off_timedept'"),
                    db::raw("TIME_FORMAT(ob_off_timearr, '%h:%i %p') as 'ob_off_timearr'"),
                    db::raw("TIME_FORMAT(ob_dest_timedept, '%h:%i %p') as 'ob_dest_timedept'"),
                    db::raw("TIME_FORMAT(ob_dest_timearr, '%h:%i %p') as 'ob_dest_timearr'")
                )
                ->where('ob_id', $form['ob_id'])
                ->get();
            $Cert = "";

            foreach ($certified as $key => $value) {
                $Cert = $value;
            }

            $Template = '<table cellpadding="1">
            <tr>
                <th width="32%" align="right" style="border-bottom:1px solid black">
                <img src="' . public_path() . '/img/flag.jpg"  height="60" width="90">
                </th>
                <th width="38%" style="font-size:11pt; border-bottom:1px solid black; word-spacing:30px" align="center">
                        Republic of the Philippines
                <br />
                        Province of Cebu
                <br />

                    <b>Municipality of Dumanjug</b>
                <br />
                <br />
                    </th>

                <th align="left" style="border-bottom:1px solid black">
                <img src="' . public_path() . '/img/logo1.png"  height="60" width="60">
                </th>
             </tr>
             <tr>
                <th width="100%" align="center" style="font-size:11pt"><b>PASS SLIP</b></th>
             </tr>
            </table >
            <br/>
            <br/>
            <table>
                <tr>
                    <td width="25%" style="border-bottom:1px solid black" align="center">' . $IndividData->NAME . '</td>
                    <td width="50%"></td>
                    <td width="25%" style="border-bottom:1px solid black" align="center">' . $IndividData->ob_date . '</td>
                </tr>
                <tr>
                    <td width="25%" align="center">NAME AND SIGNATURE</td>
                    <td width="50%"></td>
                    <td width="25%" align="center">Date</td>
                </tr>
                <br/>
                <tr>
                    <td width="7%"></td>
                    <td width="3%">1.</td>
                    <td width="10%">Destination:</td>
                    <td width="25%" style="border-bottom:1px solid black" align="center">' . $IndividData->ob_agency . '</td>
                    <td width="7%"></td>
                    <td width="3%">5.</td>
                    <td width="25%">Summary of Accomplishment:</td>
                    <td width="18%" style="border-bottom:1px solid black" align="center"></td>
                </tr>

                <tr>
                    <td width="7%"></td>
                    <td width="3%">2.</td>
                    <td width="10%">Purpose:</td>
                    <td width="25%" style="border-bottom:1px solid black" align="center">' . $IndividData->ob_remarks . '</td>
                    <td width="7%"></td>
                    <td width="3%">6.</td>
                    <td width="23%">Appearance Certified by:</td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"></td>
                </tr>
                <tr>
                    <td width="7%"></td>
                    <td width="3%">3.</td>
                    <td width="10%">Type</td>
                </tr>
                <br/>
                <tr>
                    <td width="10%"></td>
                    <td><input type="checkbox" checked="' . ($IndividData->ob_purpose === 'Official' ? "true" : "false") . '" name="1" value="1"></td>
                    <td width="10%">Official</td>
                    <td width="20%"></td>
                    <td width="7%">Name:</td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td><input type="checkbox" checked="' . ($IndividData->ob_purpose === 'Quasi- official' ? "true" : "false") . '" name="1" value="1"></td>
                    <td width="15%">Quasi-official</td>
                    <td width="15%"></td>
                    <td width="9%">Signature:</td>
                    <td width="28%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td><input type="checkbox" checked="' . ($IndividData->ob_purpose === 'Personal' ? "true" : "false") . '" name="1" value="1"></td>
                    <td width="15%">Personal</td>
                    <td width="15%"></td>
                    <td width="7%">Office:</td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                </tr>
                <br/>
                <tr>
                    <td width="10%">Time Out:</td>
                    <td width="25%" style="border-bottom:1px solid black" align="center">' . $Cert->ob_dest_timedept . '</td>
                    <td width="15%"></td>
                    <td width="20%">Time In/Back to Station:</td>
                    <td width="25%" style="border-bottom:1px solid black" align="center">' . $Cert->ob_dest_timearr . '</td>
                </tr>
                <br/>
                <br/>
                <br/>
                <tr>
                    <td width="50%">APPROVED:</td>
                    <td width="50%">NOTED:</td>
                </tr>
                <br/>
                <tr>
                    <td width="30%" style="border-bottom:1px solid black" align="center"><b>'.$IndividData->{'dept_app_time'}.'</b></td>
                    <td width="30%"></td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                    <td width="10%"></td>
                </tr>
                <tr>
                    <td width="30%" align="center">Dept. Head/Supervisors</td>
                </tr>
            </table>

           ';
            PDF::SetTitle('Individual Pass/Time Adjustment Slip');
            PDF::SetFont('helvetica', '', 10);
            PDF::AddPage('P');
            // PDF::Image(public_path() . $IndividData->{'dept_app'}, 50, 50, 27, 27, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            PDF::writeHTML($Template, true, 0, true, 0);

            PDF::Output(public_path() . '/prints.pdf', 'F');
            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);

                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    // public function printpayslip(Request $request)
    // {
    //     try{



    //         $Template = '<table width="100%" cellpadding="2">
    //             <tr>
    //                 <td width="100%" align="center"><b>EMPLOYEE\'S PAYSLIP</b></td>
    //             </tr>
    //             <tr>
    //                 <td width="100%" align="center"><b>City of Naga</b></td>
    //             </tr>
    //             <br/>
    //             <tr>
    //                 <td width="15%">Employee No :</td>
    //                 <td width="45%">EMP-1-0103-2019</td>
    //                 <td width="15%">Date :</td>
    //                 <td width="25%">10/24/2022 09:59 AM</td>
    //             </tr>
    //             <br/>
    //             <tr>
    //                 <td width="15%">Employee Name :</td>
    //                 <td width="45%">Juan Dela Cruz</td>
    //                 <td width="15%">Payroll Period :</td>
    //                 <td width="25%">9/1/2022 To 9/15/202</td>
    //             </tr>
    //             <br/>
    //             <tr>
    //                 <td width="15%">Department :</td>
    //                 <td width="85%">OFFICE OF THE CITY ACCOUNTAN</td>
    //             </tr>
    //             <br/>
    //             <tr>
    //                 <td width="30%" style="font-size:11pt; border-bottom:1px solid black; border-top:1px solid black;
    //                 border-left:1px solid black; border-right:1px solid black;" align="center">
    //                 Earnings</td>

    //                 <td width="70%" style="font-size:11pt; border-bottom:1px solid black; border-top:1px solid black;
    //                 border-left:1px solid black; border-right:1px solid black;" align="center">
    //                 Deductions</td>
    //            </tr>
    //            <tr>
    //                 <td width="30%" style="border-right:1px solid black">
    //                 <table>
    //                     <tr>
    //                         <td width="50%">Basic</td>
    //                         <td width="50%" align="right">46,847.50</td>
    //                     </tr>
    //                     <tr>
    //                         <td width="100%"></td>
    //                     </tr>
    //                     <tr>
    //                         <td width="100%"></td>
    //                     </tr>
    //                     <tr>
    //                         <td width="100%"></td>
    //                     </tr>
    //                     <tr>
    //                         <td width="100%"></td>
    //                     </tr>
    //                     <tr>
    //                         <td width="100%"></td>
    //                     </tr>
    //                     <tr>
    //                         <td width="100%"></td>
    //                     </tr>
    //                     <tr>
    //                         <td width="100%"></td>
    //                     </tr>
    //                 </table>
    //             </td>

    //                 <td width="35%">
    //                     <table>
    //                         <tr>
    //                             <td width="50%">Medicare</td>
    //                             <td width="50%" align="right">1,600.00</td>
    //                         </tr>
    //                         <tr>
    //                             <td width="50%">GSIS </td>
    //                             <td width="50%" align="right">8,432.55</td>
    //                         </tr>
    //                         <tr>
    //                             <td width="50%">Pag-Ibig </td>
    //                             <td width="50%" align="right">100.00</td>
    //                         </tr>

    //                     </table>
    //                 </td>

    //                 <td width="35%">
    //                 <table>
    //                     <tr>
    //                         <td width="50%">GSIS Emergency </td>
    //                         <td width="50%" align="right">564.75</td>
    //                     </tr>
    //                     <tr>
    //                         <td width="50%">CFI Loan</td>
    //                         <td width="50%" align="right">2,844.79</td>
    //                     </tr>
    //                     <tr>
    //                         <td width="50%">CFI Loan</td>
    //                         <td width="50%" align="right">4,000.00</td>
    //                     </tr>
    //                 </table>
    //             </td>
    //            </tr>

    //            <tr>
    //             <td width="20%" style="font-size:10pt; border-left:1px solid black; border-bottom:1px solid black;
    //             border-top:1px solid black;">Total Earnings :</td>

    //             <td width="15%" style="font-size:10pt; border-bottom:1px solid black;
    //             border-top:1px solid black;">46,847.50</td>

    //             <td width="25%" style="font-size:10pt; border-bottom:1px solid black;
    //             border-top:1px solid black;">Total Deductions :</td>

    //             <td width="15%" style="font-size:10pt; border-bottom:1px solid black;
    //             border-top:1px solid black;">17,542.09 </td>

    //             <td width="15%" style="font-size:10pt; border-bottom:1px solid black;
    //             border-top:1px solid black;">Net Pay :</td>

    //             <td width="10%" style="font-size:10pt; border-bottom:1px solid black;
    //             border-top:1px solid black; border-right:1px solid black;">29,305.41</td>
    //        </tr>


    //         </table>';


    //         PDF::SetTitle('EMPLOYEE\'S PAYSLIP');
    //         PDF::SetFont('helvetica', '', 9);
    //         PDF::AddPage('P',);

            // PDF::AddPage('P');
    //         PDF::writeHTML($Template, true, 0, true, 0);
    //         PDF::Output(public_path() . '/print.pdf', 'F');
    //         $full_path = public_path() . '/print.pdf';
    //         if (\File::exists(public_path() . '/print.pdf')) {
    //             $file = \File::get($full_path);
    //             $type = \File::mimeType($full_path);
    //             $response = \Response::make($file, 200);
    //             $response->header("Content-Type", $type);
    //             return $response;
    //         }
    //         } catch (\Exception $e) {
    //             return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    //         }
    // }

    public function ITC(Request $request)
    {
        try{



            $Template = '<table width="100%">
            <tr>

            <th width="100%" style="font-size:11pt;  word-spacing:30px" align="center">
                    <b>'.env("cityname",false).'</b>
            <br />
                   <b> INFORMATION TECHNOLOGY CENTER </b>
            <br />

                Preventive Computer Maintenance System
                </th>
         </tr>
            <tr>
                <td width="100%" style="color:red; font-size: 12pt" align="center"><u>LAPTOP</u></td>
            </tr>
            <br/>
            <br/>

            </table>
            <table cellpadding="1">
                <tr>
                    <td width="23%" align="right">DATE OF INSPECTION:</td>
                    <td width="2%"></td>
                    <td width="35%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="23%" align="right">Office:</td>
                    <td width="2%"></td>
                    <td width="45%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="23%" align="right">ARE NO.:</td>
                    <td width="2%"></td>
                    <td width="15%" style="border-bottom:1px solid black"></td>
                </tr>
                <br/>
                <tr>
                    <td width="23%" align="right">ARE OWNER:</td>
                    <td width="2%"></td>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="23%" align="right"></td>
                    <td width="2%"></td>
                    <td width="20%" align="center">LAST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">FIRST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">MIDDLE NAME</td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="23%" align="right">USER:</td>
                    <td width="2%"></td>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="23%" align="right"></td>
                    <td width="2%"></td>
                    <td width="20%" align="center">LAST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">FIRST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">MIDDLE NAME</td>
                </tr>
                <br/>
                <br/>

                <tr>
                    <td width="1%" align="left"></td>
                    <td width="9%" align="left">SPECIFICATION:</td>
                </tr>
                <tr>
                    <td width="9%" align="left"></td>
                    <td width="91%">
                        <table border="1">
                            <tr>
                                <td width="5%"></td>
                                <td width="17%" align="center"><b>ITEM</b></td>
                                <td width="17%" align="center"><b>MANUFACTURER</b></td>
                                <td width="17%" align="center"><b>MODEL</b></td>
                                <td width="17%" align="center"><b>SIZE/FORM FACTOR</b></td>
                                <td width="18%" align="center"><b>REMARKS</b></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">1</td>
                                <td width="17%" align="center">CPU</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                             <tr>
                                <td width="5%" align="center">2</td>
                                <td width="17%" align="center">MOTHERBOARD</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">3</td>
                                <td width="17%" align="center">RAM</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">4</td>
                                <td width="17%" align="center">STORAGE</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">5</td>
                                <td width="17%" align="center">GPU</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">6</td>
                                <td width="17%" align="center">OPERATING SYSTEM</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">7</td>
                                <td width="17%" align="center">MONITOR</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">8</td>
                                <td width="17%" align="center">MOUSE</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">9</td>
                                <td width="17%" align="center">KEYBOARD</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">10</td>
                                <td width="17%" align="center">WIRELESS</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">11</td>
                                <td width="17%" align="center">UPS</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                            <tr>
                                <td width="5%" align="center">12</td>
                                <td width="17%" align="center">AVR</td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="17%" align="center"></td>
                                <td width="18%" align="center"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="10%"></td>
                    <td width="30%">INSPECTED BY:</td>
                    <td width="15%"></td>
                    <td width="45%">VERIFIED BY:</td>
                </tr>
                <br/>
                <tr>
                    <td width="10%"></td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                    <td width="15%"></td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                    <td width="15%"></td>
                </tr>

            </table>';


            PDF::SetTitle('Preventive Computer Maintenance System');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('L',);

            // PDF::AddPage('P');
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/print.pdf', 'F');
            $full_path = public_path() . '/print.pdf';
            if (\File::exists(public_path() . '/print.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
            } catch (\Exception $e) {
                return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
            }
    }
}
