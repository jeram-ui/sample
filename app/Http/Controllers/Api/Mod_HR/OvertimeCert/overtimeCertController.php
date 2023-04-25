<?php

namespace App\Http\Controllers\Api\Mod_HR\OvertimeCert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class overtimeCertController extends Controller
{
    private $lgu_db;
    private $hr_db;


    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }

    public function GetName()
    {

        $list = DB::table($this->hr_db . '.tbl_overtime_cert')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime_cert.id')

            ->where('tbl_overtime_cert.status', 'Approved')
            ->get();
        return response()->json(new JsonResponse($list));
    }


    public function getCert()
    {
        // $list = DB::table($this->hr_db . '.tbl_overtime_cert_dtl')
        //     ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime_cert_dtl.emp_id')
        //     ->join($this->hr_db . '.tbl_overtime_cert', 'tbl_overtime_cert.id', 'tbl_overtime_cert_dtl.cert_id', 'tbl_overtime_cert_dtl.id')
        //     // ->join($this->hr_db.'.tbl_overtime','tbl_overtime.cert_id','tbl_overtime_cert.id')
        //     // ->join($this->hr_db.'.tbl_overtime_cert','tbl_overtime_cert.id','tbl_overtime.cert_id')
        //     // ->select('*',db::raw('cert_id', 'emp_id','date' ),'tbl_overtime_cert_dtl.cert_id','tbl_overtime_cert.id')
        //     ->where('tbl_overtime_cert.status', 'approved')
        //     ->where('emp_id', Auth::user()->Employee_id)
        //     ->get();
        $list =db::select("SELECT *,tbl_overtime_cert_dtl.id as 'cert_id_dtl' FROM ".$this->hr_db .".tbl_overtime_cert_dtl
        INNER JOIN ".$this->hr_db .".tbl_overtime_cert ON(tbl_overtime_cert.`id` = tbl_overtime_cert_dtl.`cert_id`)
        INNER JOIN ".$this->hr_db .".employee_information ON(employee_information.`PPID` = tbl_overtime_cert_dtl.`emp_id`)
          WHERE tbl_overtime_cert.status = 'approved'
          AND tbl_overtime_cert_dtl.`emp_id` = ". Auth::user()->Employee_id ."
          AND tbl_overtime_cert.`id` NOT IN (SELECT tbl_overtime.`cert_id` FROM ".$this->hr_db .".tbl_overtime
          inner join ".$this->hr_db.".tbl_overtime_dtl on(tbl_overtime.overtime_id = tbl_overtime_dtl.overtime_id)
          WHERE `emp_id` = ". Auth::user()->Employee_id ." AND tbl_overtime_dtl.STATUS <>'Deleted')");

        return response()->json(new JsonResponse($list));
    }
    public function getRef(Request $request)
    {
      $query = DB::select("SELECT CONCAT(LPAD(COUNT(*)+1,4,0),'-',DATE_FORMAT(NOW(),'%Y'))as 'NOS' FROM " . $this->hr_db . ".tbl_overtime");
      return response()->json(new JsonResponse(['data' => $query]));
    }
    public function getDepartment()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }


    public function GetApplication()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }

    public function getovertCert(Request $request)
    {
        $list = DB::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime.emp_id')
            ->join($this->hr_db . '.tbl_overtime_dtl', 'tbl_overtime_dtl.overtime_id', 'tbl_overtime.overtime_id')
            ->where('tbl_overtime_dtl.status','<>', 'Deleted')
            ->where('tbl_overtime.emp_id',Auth::user()->Employee_id)
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        $data['FormA'] = db::table($this->hr_db . '.tbl_overtime')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_overtime.emp_id')
            ->select('tbl_overtime.*', 'employee_information.NAME', 'emp_id as name')
            ->where('tbl_overtime.overtime_id', $id)
            ->get();

        $data['FormB'] = db::table($this->hr_db . '.tbl_overtime_dtl')
            ->where('overtime_id', $id)
            ->get();

        // $data['formz'] =db::table($this->hr_db .'.sworn_assets')->where('mainID', $id)->get();


        return response()->json(new JsonResponse($data));
    }




    public function OvertimeStore(Request $request)
    {
        $form = $request->form;
        unset($form['name']);
        $formx = $request->formx;
        $id = $form['overtime_id'];
        if ($id > 0) {
            db::table($this->hr_db . ".tbl_overtime")
                ->where('overtime_id', $id)
                ->update($form);

            db::table($this->hr_db . ".tbl_overtime_dtl")
                ->where("overtime_id", $id)
                ->delete();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'overtime_id' => $id,
                    'date_overtime' => $value['date_overtime'],
                    'overtime_from' => $value['overtime_from'],
                    'overtime_to' => $value['overtime_to'],
                    'cert_id' => $value['cert_id'],
                    'is_wholed_day'=>$value['is_wholed_day'],
                    // 'overtime_from'=>$value['date_overtime'].' '.$value['overtime_from'],
                    // 'overtime_to'=>$value['date_overtime'].' '.$value['overtime_to'],
                    'app_hrs' => $value['app_hrs'],
                    'memo' => $value['memo'],

                );
                db::table($this->hr_db . ".tbl_overtime_dtl")->insert($datx);
            }
        } else {
            // $withdrawnSubjects = array (

            //     'schedule_id' => $shed['id'],
            //     'DescTitle' => $shed['sub_id'],
            //     'units' => $shed['no_of_units'],
            //     'mainID'  => $id
            // );
            // db::table('subjectwithdrawn')->insert($withdrawnSubjects);

            db::table($this->hr_db . ".tbl_overtime")->insert($form);
            $id = DB::getPdo()->LastInsertId();


            foreach ($formx as $key => $value) {
                $datx = array(
                    'overtime_id' => $id,
                    'date_overtime' => $value['date_overtime'],
                    'overtime_from' =>  $value['overtime_from'],
                    'overtime_to' =>  $value['overtime_to'],
                    'overtime_charge' => $value['app_hrs'],
                    'memo' => $value['memo'],
                    'cert_id' => $value['cert_id'],
                    'is_wholed_day'=>$value['is_wholed_day'],
                );
                db::table($this->hr_db . ".tbl_overtime_dtl")->insert($datx);
            }
        }
    }

    public function OverTimeCancel($id)
    {
        db::table($this->hr_db . '.tbl_overtime_dtl')
            ->where('overtime_id', $id)
            ->update(['status' => 'Deleted']);
            db::table($this->hr_db . '.tbl_overtime')
            ->where('overtime_id', $id)
            ->update(['status' => 'Deleted']);

        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

//     public function print(Request $request)
//     {
//         // $form = $request['itm'];
//         // log::debug($form['ir_id']);
//         // $ir_id = $form['ir_id'];

//         // try {
//         // $incident = db::select("call " . $this->hr_db . ".rans_display_tbl_leaves_report_new(?)", [$ir_id]);
//         // $incident = DB::table($this->hr_db . '.tbl_dtr_incident_report')
//         //     ->where('ir_id', $request->itm['ir_id'])
//         //     ->select('*', db::raw("TIME_FORMAT(time_incident, '%h:%i %p') as 'time_incident'"))
//         //     ->get();
//         // $row = [];
//         // foreach ($incident as $key => $value) {
//         //     $row = $value;
//         // }

//         $Template = '<table width="100%" cellpadding="2" >

//       <tr>
//           <th width="100%" align="center">
//               <img src="' . public_path() . '/img/NAGALOGO.jpg"  height="45" width="45">
//           </th>

//       </tr>
//       <tr>
//         <th width="100%" align="center" style="font-size:10pt"> Republic of the Philippines </th>
//       </tr>
//       <tr>
//         <th width="100%" align="center" style="font-size:10pt"> Province of Cebu </th>
//       </tr>
//       <tr>
//         <th width="100%" align="center" style="font-size:10pt"> City Government of Naga </th>
//       </tr>
//       <tr>
//          <th width="100%" align="center" style="font-size:10pt"> Office of the Cuty Mayor </th>
//       </tr>
//       <tr><th width="100%" align="center" style="font-size:11pt"> <b> BIDS AND AWARDS COMMITTEE </b> </th></tr>
//       <br/>
//       <tr> <th width="100%" align="center" style="font-size:11pt"> <b> CERTIFICATE OF ELIGIBILITY </b> </th> </tr>
//       <tr><th width="100%" align="center" style="font-size:9pt"> (Alternative Mode of Procurement) </th></tr>
//   </table>
//   ';

//         $Template .= ' <table width="100%" cellpadding="5">
//             <br/>
//             <br/>
//             <br/>
//             <br/>
//             <br/>

//             <tr>
//                 <td width="100%"><p style="text-align:justify;font-size:11pt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
//                  This is to certify that <b><u> 4DS VARIETY STORE as represented by  Glenda A. Patalingjug, proprietor</u></b>
//                   with business address at <u>Playa Del Sol, Central Poblacion, City of Naga, Cebu</u>
//                    submitted to this office, the following: </p> </td>
//             </tr>
//             <br/>
//             <br/>
//             <tr>
//                 <td width="6%"> <input type="checkbox" check="true" name="1" value="1">
//               </td>
//                 <td width="90%" style="font-size:11pt"> Valid Mayors Business Permit; </td>
//             </tr>
//             <br/>
//             <tr>
//                 <td width="6%">  <input type="checkbox" check="true" name="1" value="1">
//                 </td>
//                 <td width="90%" style="font-size:11pt"> PhilGEPS Registration </td>
//             </tr>
//          <br/>
//          <br/>


//             <tr>
//                 <td width="100%"><p style="text-align:justify;font-size:11pt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
//                 This is to further certify that having submitted the above eligibility document; the
//                 <b><u> 4DS VARIETY STORE </u></b>is found to be eligible to supply/provide <b> office supplies and household </b>
//                 for the City of Government of Naga, Cebu through
//                 <u><b> Alternative Modes of Procurement</b> - <i>Negotiated Procurement through Small Value Procurement</i></u>.
//                 </p></td>
//             </tr>


//             <br/>
//             <br/>
//             <br/>
//             <tr>
//                 <td width="10%"> </td>
//                 <td width="90%" style="font-size:11pt;text-align:justify"> Issued this 15th day of June 2022 at the City of Naga, Cebu, Philippines </td>
//             </tr>
//             <br/>
//             <br/>
//             <br/>
//             <tr>
//                 <td width="3%"> </td>
//                 <td width="97%" style="font-size:11pt;text-align:justify"> ANNAVIE E. BACOMO-LAPITAN </td>
//             </tr>
//             <tr>
//                 <td width="3%"> </td>
//                 <td width="97%" style="font-size:11pt;text-align:justify"> BAC Secretariat, Chairman </td>
//             </tr>
//             <br/>
//             <br/>
//             <br/>


//             <tr>
//                 <td width="19%"> </td>
//                 <td width="60%" style="font-size:11pt;text-align:justify"> Approved by: </td>
//             </tr>
//             <br/>
//             <br/>

//             <tr>
//                 <td width="19%"> </td>
//                 <td width="81%" style="font-size:11pt;text-align:justify"> ENGR.ARTHUR S. VILLAMOR </td>
//             </tr>
//             <tr>
//                 <td width="19%"> </td>
//                 <td width="81%" style="font-size:11pt;text-align:justify"> BAC Chairman </td>
//             </tr>






//         </table>

// ';


//         // PDF::Image(public_path() . $value->{'certSig'}, 55, 205, 27, 27, 'PNG', 'http://www.tcpdf.org', '', false, 300);
//         // PDF::Image(public_path() . $value->{'RecSig'}, 150, 203, 25, 25, 'PNG', 'http://www.tcpdf.org', '', false, 300);
//         // PDF::Image(public_path() . $value->{'Approved BySig'}, 80, 245, 40, 30, 'PNG', 'http://www.tcpdf.org', '', false, 300);

//         PDF::SetTitle('Print');
//         PDF::SetFont('helvetica', '', 8);
//         PDF::AddPage('P');
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
//         // } catch (\Exception $e) {
//         //     return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
//         // }
//     }
    public function print(Request $request)
    {
        try{
        $form = $request->itm;
        // $Data = db::table($this->hr_db .'.tbl_overtime')
        // // ->join($this->hr_db .'.employees','employees.SysPK_Empl','tbl_overtime.emp_id')
        // ->join($this->hr_db .'.employee_information','employee_information.PPID','tbl_overtime.emp_id')
        // ->where('overtime_id', $form['overtime_id'] )
        // ->get();
        $Data = db::select("SELECT *,".$this->hr_db."._getEmployeeName(`approved_by`)AS 'approvedBY' FROM ".$this->hr_db.".tbl_overtime
        INNER JOIN ".$this->hr_db.".employee_information ON(tbl_overtime.emp_id = employee_information.`PPID`)
        WHERE overtime_id = ".$form['overtime_id']."
        ");
        $OTdata ="";

        foreach ($Data as $key => $value) {
            $OTdata= $value;
        }

        $data1 = db::table($this->hr_db . '.tbl_overtime_dtl')
        ->where('overtime_id', $form['overtime_id'] )
        ->get();
        $OTdata1 ="";
        $OTdata2 ="";


        foreach ($data1 as $key => $value) {
            $OTdata1= $value;
        }

        foreach ($data1 as $key => $value) {
            $OTdata2 .=' <tr>
            <td width="9%">Date(s):</td>
            <td width="14%" style="border-bottom:1px solid black">'.$value->date_overtime.'</td>
            <td width="5%">From:</td>
            <td width="8%" style="border-bottom:1px solid black">' . (!empty($value->overtime_from) ? (date_format(date_create($value->overtime_from),"h:i:s")) : "") . '</td>
            <td width="6%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">am</td>
            <td width="8%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">pm</td>
            <td width="4%">To:</td>
            <td width="8%" style="border-bottom:1px solid black">' . (!empty($value->overtime_to) ? (date_format(date_create($value->overtime_to),"h:i:s")) : "") . '</td>
            <td width="6%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">am</td>
            <td width="8%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">pm</td>
            <td width="10%"># of Hours:</td>
            <td width="14%" style="border-bottom:1px solid black">'.number_format($value->app_hrs,2).'</td>
        </tr>' ;
        }
        log::debug($value->app_hrs);
            if(count($data1)< 6){
                for($i = count($data1); $i<6; $i++){
                    $OTdata2 .=' <tr>
                    <td width="9%">Date(s):</td>
                    <td width="14%" style="border-bottom:1px solid black"></td>
                    <td width="5%">From:</td>
                    <td width="8%" style="border-bottom:1px solid black"></td>
                    <td width="6%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">am</td>
                    <td width="8%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">pm</td>
                    <td width="4%">To:</td>
                    <td width="8%" style="border-bottom:1px solid black"></td>
                    <td width="6%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">am</td>
                    <td width="8%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">pm</td>
                    <td width="10%"># of Hours:</td>
                    <td width="14%" style="border-bottom:1px solid black"></td>
                </tr> ' ;
                }
            }


        $Template = '
        <table cellpadding="1">
                <tr>
                    <th width="35%" align="right">
                    <img src="' . public_path() . '/img/logo1.png"  height="50" width="50">
                    </th>
                    <th width="35%" style="font-size:9pt;  word-spacing:30px" align="center">
                            Republic of the Philippines
                    <br />
                            Province of Cebu
                    <br />

                        CIty of Naga
                    <br />
                    <br />
                    <b>OVERTIME APPLICATION FORM</b>
                        </th>
                    <th align="left">
                    <img src="' . public_path() . '/img/logo2.png"  height="50" width="50">
                    </th>
                 </tr>

                </table >
                <br />
                <br />

            <table width="100%" cellpadding="2">
                <tr>
                    <td width="15%">Name:</td>
                    <td width="33%" style="border-bottom:1px solid black">'.$OTdata->NAME.'</td>
                    <td width="2%"></td>
                    <td width="25%">Position/Status of Employment:</td>
                    <td width="25%" style="border-bottom:1px solid black">'.$OTdata->{'EMP TYPE'}.'</td>
                </tr>
                <tr>
                <td width="15%">Position Title:</td>
                <td width="33%" style="border-bottom:1px solid black">'.$OTdata->POSITION.'</td>
                <td width="2%"></td>
                <td width="8%">Office:</td>
                <td width="43%" style="border-bottom:1px solid black">'.$OTdata->DEPARTMENT.'</td>
            </tr>
            <br/>
            <tr>
                <td width="15%"></td>
                <td width="15%" style="font-size:9pt"><input type="checkbox" checked="' . ($OTdata1->excempt === 'True' ? "true" : "false") . '" name="1" value="1">Exempt</td>
                <td width="75%" style="font-size:9pt"><input type="checkbox" checked="' . ($OTdata1->excempt === 'False' ? "true" : "false") . '" name="1" value="1">Non-Exempt</td>
            </tr>
            <br/>

         '.$OTdata2.'
            <br/>
            <br/>

            <tr>
                <td width="20%">Type of Compensation:</td>
                <td width="15%">Check One</td>
                <td width="20%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">Compensatory Time</td>
                <td width="45%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">Monetary</td>
            </tr>
            <br/>
            <tr>
                <td width="100%">JUSTIFICATION: (Why overtime is necessary, what duties will be performed; why work cannot be performed during normal</td>
            </tr>
            <tr>
                <td width="100%">working hours; why work will be performed outside the office.)</td>
            </tr>
            <br/>
            <tr>
                <td width="100%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td width="100%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td width="100%" style="border-bottom:1px solid black"></td>
            </tr>
            <br/>
            <tr>
                <td width="25%" style="border-bottom:1px solid black"></td>
                <td width="2%"></td>
                <td width="20%" style="border-bottom:1px solid black"></td>
                <td width="53%"></td>
            </tr>
            <tr>
                <td width="25%" align="center">Supervisor\'s Signature</td>
                <td width="2%"></td>
                <td width="20%" align="center">Date</td>
                <td width="53%"></td>
            </tr>
            <br/>
            <tr>
                <td width="100%" style="border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black;
                 border-lefts:1px solid black; background-color:#C0C0C0" align="center"><b>SUBMIT TO LOCAL CHIEF EXECUTIVE OR DESIGNEE FOR APROVAL</b>
                 </td>
            </tr>
            <tr>
                <td width="15%"></td>
                <td width="25%">Noted By:</td>
                <td width="25%">Approved By:</td>
            </tr>
            <tr>
                <td width="100%" style="font-size:9pt"><input type="checkbox" checked="' . ($OTdata1->status === 'Approved' ? "true" : "false") . '" name="1" value="1">Approved</td>
            </tr>
            <tr>
                <td width="15%" style="font-size:9pt"><input type="checkbox" checked="' . ($OTdata1->status === 'Cancelled' ? "true" : "false") . '" name="1" value="1">Disapproved</td>
                <td width="27%" style="border-bottom:1px solid black"></td>
                <td width="2%"></td>
                <td width="27%" style="border-bottom:1px solid black;text-align:center">'.$OTdata->approvedBY.'</td>
                <td width="2%"></td>
                <td width="27%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td width="15%"></td>
                <td width="27%" align="center">HRMO Designate</td>
                <td width="2%"></td>
                <td width="27%" align="center">Municipal Mayor</td>
                <td width="2%"></td>
                <td width="27%" align="center">Date</td>
            </tr>
            <br/>
            <tr>
                <td width="100%">Total Payment Amount: P</td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="90%">(Total Amount Earned)</td>
            </tr>
            <tr>
                <td width="100%">Total Compensatory Time:</td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="90%">(No. of Hours Worked)</td>
            </tr>
            <br/>
            <tr>
                <td width="100%" style="border-style:dashed"></td>
            </tr>
            <tr>
                <td width="100%">NOTE:</td>
            </tr>
            <tr>
                <td width="100%">If overtime is requested for more than one employee for the same purpose, you may list the employees on a sheet of
                 paper and attach it to this request</td>
            </tr>
            <tr>
                <td width="100%">form. You must include the name of employee, position/status of employment, inclusive dates and # of hours overtimeto be done.</td>
            </tr>
            <br/>
            <tr>
                <td width="100%">If compensatory time is granted, furnished copy to HR for recording purposes. If Monetery value is granted, attached a copy of this request</td>
            </tr>
            <tr>
                <td width="100%">form to the payroll.</td>
            </tr>
            <br/>
            <tr>
                <td width="100%">One-hour breaks shall be observed for breakfast, lunch or supper and rest, and carry 3 of continous overtime serviceor as may be necessary.</td>
            </tr>
            <br/>
            <tr>
                <td width="100%">Only maximum of 12 hours of overtime services on a rest day or scheduled day off, holiday or special non-working days, shall be compensated through</td>
            </tr>
            <tr>
                <td width="100%">Overtime Pay. Any excess 12 hours shall be compensated through CTO.</td>
            </tr>
        </table>';


        PDF::SetTitle('Print');
        PDF::SetFont('helvetica', '', 8);
        PDF::AddPage('P');
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
