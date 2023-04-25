<?php

namespace App\Http\Controllers\Api\Mod_HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;

class leaveentrycontroller extends Controller
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
    // $this->eagles_db = $this->G->geteaglesDb();
  }
  public function showleave(Request $request)
  {
    try {
      $data =  db::select('select leave_type,leave_number FROM ' . $this->hr_db . '.hr_emp_leave_type');
      return response()->json(new jsonresponse($data));
    } catch (\Exception $e) {

      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'stat' => 'error']));
    }
    // try {
    //   $data = db::table($this->hr_db . ".employee_information")
    //     ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
    //     // ->where("tbl_leaves.status", 'Active')
    //     ->where("employee_id", Auth::user()->Employee_id)
    //     ->orderBy("tbl_leaves", "desc")
    //     ->get();

    //   $array = array();
    //   foreach ($data as $key => $value) {
    //     // $value;
    //     $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
    //       ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
    //       ->select('*', db::raw(" 'false' as approved "))
    //       ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
    //       ->get();
    //     $datax = array(
    //       'leave_id' => $value->leave_id,
    //       'ref_no' => $value->ref_no,
    //       'applied_date' => $value->applied_date,
    //       'NAME' => $value->NAME,
    //       'purpose' => $value->purpose,
    //       'details' => $dtlx
    //     );
    //     // $datax['dtls'] = $dtlx;
    //     array_push($array, $datax);
    //   }
    //   return response()->json(new jsonresponse($array));
    // } catch (\Exception $e) {
    //   return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    // }
  }

  public function showlist(Request $request)
  {

    try {
      $data = db::table($this->hr_db . ".employee_information")
        ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
        ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
        ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
        // ->where("tbl_leaves.status", 'Active')
        ->select(
          "tbl_leaves.*",
          'employee_information.*',
          db::raw("GROUP_CONCAT(DISTINCT(leave_type)) AS 'leavetype'"),
          db::raw("GROUP_CONCAT(DISTINCT(DATE_FORMAT(`leave_date`,'%m/%d/%Y'))ORDER BY leave_date asc ) AS 'leavedate'"),
          'tbl_leaves.dateSelected'
        )
        ->where("employee_id", Auth::user()->Employee_id)
        ->whereNotIn("tbl_leaves.status", ['Cancelled', 'Invalid'])
        ->orderBy("tbl_leaves.applied_date", "desc")
        ->groupBy("tbl_leaves.leave_id")
        ->get();
      $array = array();
      foreach ($data as $key => $value) {
        // $value;
        $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->select('*', db::raw(" 'false' as approved "))
          ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
          ->get();
        $datax = array(
          'leave_id' => $value->leave_id,
          'ref_no' => $value->ref_no,
          'applied_date' => $value->applied_date,
          'leavetype' => $value->leavetype,
          'NAME' => $value->NAME,
          'purpose' => $value->purpose,
          'details' => $dtlx,
          'leave_date' => $value->leavedate,
          'status' => $value->status,
          'dateSelected' => $value->dateSelected


        );
        // $datax['dtls'] = $dtlx;
        array_push($array, $datax);
      }
      return response()->json(new jsonresponse($array));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function chekingLimit(Request $request)
  {
    $leaveId = $request->leaveId;
    $list = db::select("call " . $this->hr_db . ".rans_git_Leave_balance(?,?)", [$leaveId, Auth::user()->Employee_id]);
    $okeys = "false";
    return response()->json(new jsonresponse($list));
  }
  public function chekingLeaveDate($date)
  {
    $list['date'] = db::table($this->hr_db . ".tbl_leaves")
      ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
      ->where("tbl_leaves.employee_id", Auth::user()->Employee_id)
      ->where("tbl_leaves_dtl.leave_date", $date)
      ->whereNotIn("tbl_leaves_dtl.status", ['Invalid', 'Cancelled', 'Declined', 'Disapproved'])
      ->get();
    $list['restday'] = db::select("CALL " . $this->hr_db . ".spl_check_restday_jay(?,?,?)", [$date, $date, Auth::user()->Employee_id]);
    // $list['holiday'] =  db::select("CALL " . $this->hr_db . ".rans_display_check_holiday(?)", [$date]);
    $list['holiday'] = [];
    $list['EmpStatus'] = db::table($this->hr_db . ".employee_information")
        // ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_leaves.employee_id')
        ->where("employee_information.PPID", Auth::user()->Employee_id)
        ->where("EMP TYPE", 'Job Order')
        ->get();
    ;
    return response()->json(new jsonresponse($list));
  }
  public function store(Request $request)
  {
    try {
      $main = $request->form;
      $leave = $request->leave;
      $dateSelected = $request->dateSelected;
      // log::debug($main);
      // log::debug($leave);
      $idx = $main['leave_id'];
      DB::beginTransaction();
      $main['employee_id'] = Auth::user()->Employee_id;
      $main['isWebApp'] = 'True';


//       $chk = db::table($this->hr_db . '.tbl_leaves')
//       ->join('employee_information', 'employee_information.PPID', 'tbl_leaves.employee_id')
//       ->where("EMP TYPE", 'Job Order')
//       ->count();
//   log::debug($chk);
//   if ($chk > 0) {
//       return response()->json(
//           new JsonResponse([
//               'Message' => 'You are not allowed to apply a Leave',
//               'status' => 'Error',
//               // 'errormsh' => $e,
//           ])
//       );
//   }
//    else {

      if ($idx == 0) {
        db::table($this->hr_db . '.tbl_leaves')->insert($main);
        $id = DB::getPdo()->lastInsertId();
        log::debug($id);
        foreach ($leave as $key => $value) {
          $data = array(
            'leave_date' => $value['leave_from'],
            'leave_dateto' => $value['leave_from'],
            'leave_for' => $value['leave_for'],
            'leave_type_id' => $value['leaveId'],
            'leave_others' => $value['leave_others'],
            'payment_mode' => $value['payment_mode'],
            'leave_id' => $id,
          );
          db::table($this->hr_db . '.tbl_leaves_dtl')->insert($data);
        }
      } else {

        //   db::table($this->hr_db .'.tbl_leaves_dtl')->where('id', $idx)->update($main);
      }
    // }
      DB::commit();
      return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    } catch (\Exception $err) {
      DB::rollback();
      return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    }
  }

  public function setLeave($leaveType)
  {
    $leaveEntry = "false";
    if ($leaveType === 'Vacation Leave') {
      $leaveEntry = "true";
    } elseif ($leaveType === 'Vacation Leave') {
      $leaveEntry = "true";
    }
    return $leaveEntry;
  }
  public function getRef(Request $request)
  {
    $query = DB::select("SELECT CONCAT(LPAD(COUNT(*)+1,4,0),'-',DATE_FORMAT(NOW(),'%Y'))as 'NOS' FROM " . $this->hr_db . ".tbl_leaves");
    return response()->json(new JsonResponse(['data' => $query]));
  }
  public function print(Request $request)
  {
    $form = $request['itm'];
    log::debug($form['leave_id']);
    $leave_id = $form['leave_id'];
    //  $leave_id = 6127;
    try {
      // $data = db::select("call ".$this->hr_db.".rans_display_tbl_leaves_report_new(?)",[$form['leave_id']]);
      $data = db::select("call " . $this->hr_db . ".rans_display_tbl_leaves_report_new(?)", [$leave_id]);
      $declinedData =  db::select(" SELECT GROUP_CONCAT(DATE_FORMAT(leave_dateto,'%m/%d/%Y'))AS'dateDeclined' FROM " . $this->hr_db . ".tbl_leaves_dtl WHERE leave_id = ? AND `status` ='declined';", [$leave_id]);
      $dateDeclined = "";
      foreach ($declinedData as $key => $value) {
        $dateDeclined = $value->dateDeclined;
      }
      $row = [];
      foreach ($data as $key => $value) {
        $row = $value;
      }
      $params1 = PDF::serializeTCPDFtagParameters(array($row->{'Application No'}, 'QRCODE,H', '', '', 10, 10, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 25), 'N'));
      $header = '
    <table style="width=100%;">
    <tr>
      <th width ="15%">
      <table>
       <tr>
        <td><h5>Civil Service Form No. 6</h5>
        </td>
       </tr>
       <tr>
       <td>
       <h5>Revised 2020</h5></td>
      </tr>
      </table>
      </th>
      <th width ="15%" align="right"><img src="' . public_path() . '/images/Logo1.png"  height="25" width="25"></th>
      <th width ="35%" style="font-size:8pt;" align="center">
    Republic of the Philippines
    <br>
    ' . env('cityname', false) . '
    <br>
    ' . env('cityaddress', false) . '
    </th>
    <th width ="20%" align="center" >
      <tcpdf method="write2DBarcode" params="' . $params1 . '" />
    </th>
    <th width ="15%"></th>
    </tr>
    </table>';
      $html_content =  $header;
      $html_content .= '<h4 align="center">APPLICATION FOR LEAVE</h4>';
      $html_content .= '<table width="100%" cellpadding="2">
                            <tr>
                                <td width="90%" align="right">Application No.:</td>
                                <td width="10%" style="border-bottom:1px solid black"> &nbsp; '.$row->{'Application No'}.'</td>
                            </tr>
                        </table>';
      $html_content .= '
        <table width="100%" cellpadding="2"  style="border-bottom: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;border-left: 1px solid black;">
            <tr >
              <td width="45%">1. OFFICE/DEPARTMENT</td>
              <td width="20%">2. NAME: (Last)</td>
              <td width="25%">(First)</td>
              <td width="10%">(Middle)</td>
            </tr>
            <tr >
              <td width="45%" style="border-bottom: 1px solid black" >' . $row->Department . '</td>
              <td width="20%" style="border-bottom: 1px solid black">' . $row->{'Last Name'} . '</td>
              <td width="25%" style="border-bottom: 1px solid black">' . $row->{'First Name'} . '</td>
              <td width="10%" style="border-bottom: 1px solid black">' . $row->MI . '</td>
            </tr>
          </table>';
      $html_content .= '<table width="100%" style="border-bottom: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;border-left: 1px solid black;" cellpadding="4">
        <tr>
          <td width="16%">3. DATE OF FILING</td>
          <td  width="12%">
            <u>' . $row->{'Application Date'} . '</u>
          </td>
          <td width="12%">4. POSITION</td>
          <td width="40%">
            <u>' . $row->{'Position'} . '</u>
          </td>
          <td width="10%">5. SALARY</td>
          <td width="10%">' . number_format($row->{'Basic Salary'}, 2) . '</td>
        </tr>
       </table>';
      $html_content .= '<table cellpadding="4" width="100%" style="border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black">
       <tr>
         <td width="100%" style="text-bold; text-align: center">
           <b> 6. DETAILS OF APPLICATION </b>
         </td>
       </tr>
     </table>';
      $leaveType = $row->{'leave_types'};
      $stat = db::select("
     SELECT  status, SUM(IF(`leave_for` = 'Whole Day',1,.5))AS 'count'
                                      ,sum(CASE WHEN `status` = 'Approved' AND `payment_mode` = 'With Pay' THEN IF(`leave_for`='Whole Day',1,.5) ELSE 0 END) AS 'payapp'
                                      ,sum(CASE WHEN `status` = 'Approved' AND `payment_mode` <> 'With Pay' THEN IF(`leave_for`='Whole Day',1,.5) ELSE 0 END) AS 'nopayapp'
                                      FROM " . $this->hr_db . ".tbl_leaves_dtl WHERE leave_id=" . $leave_id . "");
      $leaveCount = "";
      foreach ($stat as $key => $value) {
        $leaveCount = $value;
        // log::debug($leaveCount->payapp);
        // log::debug($leaveCount->nopayapp);
      }
      $html_content .= ' <table width="100%" border="1" cellpadding="4">
     <tr>
       <td width="60%">
        6.A TYPE OF LEAVE TO BE AVAILED OF<br>
         <table width="100%" style="font-size:7pt;" cellpadding = "2" >
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Vacation Leave') ? 'true' : 'false') . '" readonly="true">
               Vacation Leave (Sec. 51,Rule XVI, Omnibus Rules Implementing
               E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Forced Leave') ? 'true' : 'false') . '" readonly="true">
               Mandatory/Forced Leave (Sec. 25, Rule XVI, Omnibus Rules
               Implementing E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
              <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Sick Leave')   ? 'true' : 'false') . '" readonly="true">
               Sick Leave (Sec.43, Rule XVI,Omnibus Rules Implementing E.O.
               No.292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Maternity Leave') ? 'true' : 'false') . '" readonly="true">
               Maternity Leave (R.A. No. 11210/IRR issued by CSC,DOLE and
               SSS)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Paternity Leave')  ? 'true' : 'false') . '" readonly="true">
               Paternity Leave (R.A. No. 8187/CSC MC No. 71, s. 1998, as
               amended)
             </td>
           </tr>
           <tr>
             <td>
              <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Special Privilege Leave')  ? 'true' : 'false') . '" readonly="true">
               Special Privilege Leave (Sec. 21,
               Rule XVI, Omnibus Rules Implementing E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Solo Parent Leave')   ? 'true' : 'false') . '" readonly="true">
              Solo Parent Leave (RA No. 8972/CSC
               MC No. 8, s. 2004)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Study Leave')  ? 'true' : 'false') . '" readonly="true">
               Study Leave (Sec. 68, Rule XVI, Omnibus Rules Implementing
               E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
              <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'VAWC Leave')  ? 'true' : 'false') . '" readonly="true">
               10-Day VAWC Leave (R.A. No. 9262/CSC
               MC No.15, s. 2005)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Rehabilitation Privelege')   ? 'true' : 'false') . '" readonly="true">
               Rehabilitation Privilege (Sec. 55, Rule XVI, Omnibus Rules
               Implementing E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Special Leave Benefits for Women')  ? 'true' : 'false') . '" readonly="true">
               Special Leave Benefits for Women (R.A. No. 9710/CSC MC No. 25,
               s. 2010)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Special Emergency (Calamity) Leave')  ? 'true' : 'false') . '" readonly="true">
               Special Emergency (Calamity) Leave (CSC MC No. 2, s. 2012, as
               amended)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="' . (str_contains($leaveType, 'Adoption Leave')  ? 'true' : 'false') . '" readonly="true">
             Adoption Leave (R.A. No. 8552)
             </td>
           </tr>
           <tr>
             <td>
               <br />
               others:
             </td>
           </tr>
           <tr>
             <td
               height="20px"
               width="100%"
               colspan="2"
               style="border-bottom: 1px solid black;text-align: center"
               >' . $row->{'otherLeave'} . '
               <br />
             </td>
           </tr>
         </table>
       </td>
       <td width="40%">
         6.B DETAILS OF LEAVE <br/>
         <table width="100%" style ="font:7pt;" cellpadding ="2">
           <tr>
             <td>
               <i>In case of Vacation/Special Privilege Leave:</i>
             </td>
           </tr>

           <tr>
             <td width="50%">
              <input type="checkbox" name="0" value="0" checked=' . ($row->{'VLPhil Check'} == "NO" ? "false" : "true") . ' readonly="true">Within the Philippines
             </td>
             <td width="50%" style="border-bottom: 1px solid black">' . $row->{'VLPhil Text'} . '</td>
           </tr>
           <tr>
             <td width="50%">
              <input type="checkbox" name="0" value="0" checked=' . ($row->{'VLAbroad Check'} == "NO" ? "false" : "true") . ' readonly="true">Abroad (Specify)
             </td>
             <td width="50%" style="border-bottom: 1px solid black">' . $row->{'VLAbroad Text'} . '</td>
           </tr>
           <tr>
             <td><br /><i>In case of Sick Leave:</i></td>
           </tr>
           <tr>
             <td width="60%">
             <input type="checkbox" name="0" value="0" checked = ' . ($row->{'SLHospital Check'} == "NO" ? "false" : "true") . ' readonly="true"> In Hospital (Specify Illness)
             </td>
             <td width="40%" style="border-bottom: 1px solid black">' . $row->{'SLHospital Text'} . '</td>
           </tr>
           <tr>
             <td width="60%">
             <input type="checkbox" name="0" value="0" checked =' . ($row->{'SLOutpatient Check'} == "NO" ? "false" : "true") . ' readonly="true"> Out Patient (Specify Illness)
             </td>
             <td width="40%" style="border-bottom: 1px solid black">' . $row->{'SLOutpatient Text'} . '</td>
           </tr>
           <tr>
             <td width="100%"><i>In case of Special Leave Benefits for Women:</i></td>
           </tr>
           <tr>
             <td width="40%">(Specify Illness)</td>
             <td width="60%" style="border-bottom: 1px solid black">' . $row->{'SpecialLeave Women'} . '</td>
           </tr>

           <tr>
             <td><i>in case of Study Leave:</i></td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked=' . ($row->{'Study MastersDegree'} == "NO" ? "false" : "true") . ' readonly="true"> Completion of Masters Degree</td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked=' . ($row->{'Study BAR/Board'} == "NO" ? "false" : "true") . ' readonly="true"> BAR/Board Examination Review</td>
           </tr>
           <tr>
             <td  colspan="2"><br /><i>Other purpose:</i></td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked=' . ($row->{'Monetization Leave Check'} == "NO" ? "false" : "true") . ' readonly="true"> Monetization of Leave Credits</td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked=' . ($row->{'Terminal Leave Check'} == "NO" ? "false" : "true") . ' readonly="true"> Terminal Leave</td>
           </tr>
         </table>
       </td>
     </tr>
     <tr>
      <td width="60%">
        6.C NUMBER OF WORKING DAYS APPLIED FOR <br/>
        <table width="100%" cellpadding ="4">
        <tr>
        <td width="20%"></td>
        <td
          width="40%"
          colspan="2"
          style="text-align: center;border-bottom: 1px solid black"
        >
          <b>' . $leaveCount->count . '</b>
        </td>
        <td width="20%"></td>
      </tr>
        <tr>
          <td  width="100%"><b>INCLUSIVE DATES</b></td>
        </tr>
        <tr>
        <td width="20%"></td>
          <td
            height="20px"
            width="60%"
            colspan="2"
            style="text-align: center;border-bottom: 1px solid black"
          >
            <b>' . $row->{'Date of Absence'} . '</b>
          </td>
          <td width="20%"></td>
        </tr>
      </table>
      </td>
      <td width="40%">
       6.D COMMUTATION<br/>
        <table width="100%" cellpadding = "2">
          <tr>
            <td width="100%" >
              <input type="checkbox" name="0" value="0" checked=' . ($row->{'Commutation'} == "NOT REQUESTED" ? "true" : "false") . ' readonly="true">Not Requested
            </td>
          </tr>
          <tr>
          <td width="100%" >
            <input type="checkbox" name="0" value="0" checked=' . ($row->{'Commutation'} == "REQUESTED" ? "true" : "false") . ' readonly="true">Requested
          </td>
         </tr>
          <tr>
            <td width="20%"></td>
            <td width="60%" style="text-align: center"></td>
            <td width="20%"></td>
          </tr>
          <tr>
            <td width="20%"></td>
              <td width="60%" style="text-align: center;border-top: 1px solid black;">
                  <b>(Signature of Applicant)</b>
                </td>
                <td width="20%"></td>
          </tr>
        </table>
      </td>
     </tr>
   </table>

   <table width="100%" border="1" cellpadding ="4">
   <tr>
     <td width="100%" style="text-align: center">
       <b>7. DETAILS OF ACTION ON APPLICATION</b>
     </td>
   </tr>
   </table>
   ';

      $VLAppCountData = db::select("SELECT SUM(CASE WHEN leave_type_id IN('15','28') THEN IF(`leave_for`='Whole Day',1,.5) ELSE 0 END) AS 'VL Count' FROM " . $this->hr_db . ".tbl_leaves_dtl WHERE status not in('Declined','Cancelled','Disapproved') and  leave_id =" . $leave_id);
      $VLAppCount = "0";

      foreach ($VLAppCountData as $key => $value) {
        $VLAppCount = $value->{'VL Count'};
      }
      $SLAppCountData = db::select("SELECT SUM(CASE WHEN leave_type_id = 14 THEN IF(`leave_for`='Whole Day',1,.5) ELSE 0 END) AS 'VL Count' FROM " . $this->hr_db . ".tbl_leaves_dtl WHERE status not in ('Declined','Cancelled','Disapproved') and leave_id = " . $leave_id);
      $SLAppCount = "0";
      foreach ($SLAppCountData as $key => $value) {
        $SLAppCount = $value->{'VL Count'};
      }

      $year = date("Y", strtotime($row->{'Application Date'}));

      // $VLTotalEarnedData = db::select("SELECT IFNULL(`no_of_days_allotted`,0) as 'vl' FROM ".$this->hr_db.".hr_emp_leave_credits WHERE leave_number = '15' AND emp_number = ".$row->{'SysPK'}." AND YEAR(`year`) = '".$year."'");
      $VLTotalEarned = $row->{"vl_bal"};
      if ($VLTotalEarned > 0) {
      }else {
        $VLTotalEarned = 0;
      }
      // foreach ($VLTotalEarnedData as $key => $value) {
      //   $VLTotalEarned = $value->{'vl'};
      // }

      // $SLTotalEarnedData =db::select("SELECT IFNULL(`no_of_days_allotted`,0)as 'sl' FROM ".$this->hr_db.".hr_emp_leave_credits WHERE leave_number = '14' AND emp_number = ".$row->{'SysPK'}." AND YEAR(`year`) = '".$year."'");
      $SLTotalEarned = $row->{"sl_bal"};
      // foreach ($SLTotalEarnedData as $key => $value) {
      //   $SLTotalEarned =  $value->{'sl'};
      // }
      if ($SLTotalEarned > 0) {
      }else {
        $SLTotalEarned = 0;
      }
      $html_content .= '<table width="100%" border="1">
        <tr>
          <td width="60%">
             <table width="100%" cellpadding="4">
              <tr>
                <td><b>7.A CERTIFICATION OF LEAVE CREDITS</b></td>
              </tr>
              <tr>
                <td width="100%" align="center"><br />As of ' . $row->{"asOfDate"} . '</td>
              </tr>
              <tr>
               <td  >
                <table style="margin-top: 100px;" width="90%" border = "1" cellpadding ="4">
                <tr>
                  <td></td>
                  <td align="center">
                    <b>Vacation Leave</b>
                  </td>
                  <td align="center"><b>Sick Leave</b></td>
                </tr>
                <tr>
                 <td align="center"><i>Total Earned</i></td>
                 <td align="center">' . $VLTotalEarned . '</td>
                 <td align="center">' . $SLTotalEarned . '</td>
                </tr>
                <tr>
                <td align="center"><i>Less this application</i></td>
                <td align="center">' . $VLAppCount . '</td>
                <td align="center">' . $SLAppCount . '</td>
               </tr>
               <tr>
               <td align="center"><i>Balance</i></td>
               <td align="center">' . ($VLTotalEarned - $VLAppCount) . '</td>
               <td align="center">' . ($SLTotalEarned - $SLAppCount) . '</td>
              </tr>
               </table>
              </td>
              </tr>

              <tr>
                <td
                  height="20px"
                  width="100%"
                >
                  <table width ="100%"  cellpadding="2">
                   <tr>
                    <td width="10%" style="text-align: center;border-bottom: 1px solid black;">
                     </td>
                     <td width="80%" style="text-align: center;border-bottom: 1px solid black;">
                       ' . $row->{'cert By'} . '
                     </td>
                 </tr>
              <tr>
              <td  width="15%"></td>
                <td  width="70%" style="text-align: center"><b>(Authorized Officer)</b></td>
                <td  width="15%"></td>
              </tr>
                  </table>
                </td>
                <td  width="15%"></td>
              </tr>
             </table>
          </td>
          <td width="40%">

          <table width="100%" cellpadding="4">
            <tr>
             <td colspan="2">
              <b>7.B RECOMMENDATION</b><br/>
             </td>
           </tr>
              <tr>
                <td width="100%" >
                  <br />
                  <input type="checkbox" name="0" value="0" checked="false" readonly="true">For approval
                </td>
              </tr>
              <tr>
                <td width="50%"><input type="checkbox" name="0" value="0" checked="' . ($row->{'disapproved'} > 0 ? "true" : "false") . '" readonly="true">For disapproval due to</td>
                <td width="45%" style="border-bottom: 1px solid black">' . $row->{'dateDisapproved'} . '</td>
              </tr>
              <tr>
                <td
                  height="20px"
                  width="95%"
                  colspan="2"
                  style="border-bottom: 1px solid black"
                >' . $row->{'declined_remarks'} . '</td>
              </tr>
              <tr>
                <td
                  height="20px"
                  width="95%"
                  colspan="2"
                  style="text-align: center"
                >
                <br>
                <br>
                <table width ="100%" align ="center" cellpadding="4">
                <tr>

                  <td width="100%" style="text-align: center;border-bottom: 1px solid black;">
                  ' . $row->{'Rec By'} . '
                  </td>

                </tr>
           <tr>
           <td  width="5%"></td>
             <td  width="90%" style="text-align: center"><b>(Authorized Officer)</b></td>
             <td  width="5%"></td>
            </tr>
               </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>';
      // Dim VLAppCount As Double = DataObject("SELECT SUM(CASE WHEN `status` = 'Approved' AND `payment_mode` = 'With Pay' AND leave_type_id IN('15','28') THEN IF(`leave_for`='Whole Day',1,.5) ELSE 0 END) AS 'VL Count' FROM tbl_leaves_dtl WHERE leave_id = '" & CInt(GridView1.GetFocusedRowCellValue("leave_id")) & "'")
      // Dim SLAppCount As Double = DataObject("SELECT SUM(CASE WHEN `status` = 'Approved' AND `payment_mode` = 'With Pay' AND leave_type_id = 14 THEN IF(`leave_for`='Whole Day',1,.5) ELSE 0 END) AS 'VL Count' FROM tbl_leaves_dtl WHERE leave_id = '" & CInt(GridView1.GetFocusedRowCellValue("leave_id")) & "'")
      // Dim VLTotalEarned As Double = DataObject("SELECT IFNULL(`no_of_days_allotted`,0) FROM hr_emp_leave_credits WHERE leave_number = '15' AND emp_number = '" & CInt(GridView1.GetFocusedRowCellValue("SysPK_Empl")) & "' AND YEAR(`year`) = '" & CDate(dt.Rows(0)("Application Date")).ToMysqlFormat & "'")
      // Dim SLTotalEarned As Double = DataObject("SELECT IFNULL(`no_of_days_allotted`,0) FROM hr_emp_leave_credits WHERE leave_number = '14' AND emp_number = '" & CInt(GridView1.GetFocusedRowCellValue("SysPK_Empl")) & "' AND YEAR(`year`) = '" & CDate(dt.Rows(0)("Application Date")).ToMysqlFormat & "'")

      $html_content .= '<table width="100%" style="border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black" cellpadding ="4">
      <tr>
        <td width="60%">
          <table width="100%" cellpadding="3">
              <tr>
                <td ><b>7.C APPROVED FOR:</b></td>
              </tr>
              <tr>
                <td
                  width="20%"
                  style="text-align: center; border-bottom: 1px solid black"
                >
                  <b>' . $leaveCount->payapp . '</b>
                </td>
                <td width="80%">days with pay</td>
              </tr>
              <tr>
                <td
                  width="20%"
                  style="text-align: center; border-bottom: 1px solid black"
                >
                  <b>' . $leaveCount->nopayapp . '</b>
                </td>
                <td width="80%">days without pay</td>
              </tr>
              <tr>
                <td
                  width="20%"
                  style="text-align: center; border-bottom: 1px solid black"
                ></td>
                <td width="80%">others (specify)  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . $row->{'ApprovedAutho'} . '</td>

              </tr>
            </table>
        </td>
        <td width="40%">
          <b>7.D DISAPPROVED DUE TO:</b>
        </td>

      </tr>
      <tr>
       <td width="100%" >
        <table width="100%" cellpadding="4">
          <tr>
           <td  width="25%"></td>
          <td height="20px" width="50%" colspan="2" style="text-align: center">
            <br />
            ' . $row->{'Approved By'} . '
          </td>
          <td  width="25%"></td>
        </tr>
        <tr>
          <td width="20%"></td>
          <td width="60%" style="text-align: center;border-top: 1px solid black"><b>(Authorized Official)</b></td>
          <td width="20%"></td>
        </tr>
      </table>
       </td>
      </tr>
    </table>
';

      PDF::SetTitle('Leave Application');
      PDF::SetFont('helvetica', '', 8);
      PDF::AddPage('P');
      // PDF::SetMargins(1, 1, 1, 1);
      // PDF::Text(150, 150, 'CLIPPING TEXT');
      // PDF::Image(public_path() . '/signature/sample.png', 150, 150, 25, 25, 'PNG', 'http://www.tcpdf.org', '', false, 300);
      // PDF::Text(150, 150, 'CLIPPING TEXT', 2, true);
      // log::debug(public_path() .$row->{'RecSig'});
      // log::debug(public_path() . $row->{'certSig'});
      PDF::Image(public_path() . $row->{'certSig'}, 55, 200, 27, 27, 'PNG', 'http://www.tcpdf.org', '', false, 300);
      PDF::Image(public_path() . $row->{'RecSig'}, 150, 190, 25, 25, 'PNG', 'http://www.tcpdf.org', '', false, 300);
      PDF::Image(public_path() . $row->{'Approved BySig'}, 80, 235, 40, 30, 'PNG', 'http://www.tcpdf.org', '', false, 300);
      PDF::writeHTML($html_content, true, 0, true, 0);
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

  public function cancel($leave_id)
  {
    try {
      $data = db::table($this->hr_db . '.tbl_leaves')
        ->where('leave_id', $leave_id)
        ->update(['status' => 'Cancelled']);

      $data = db::table($this->hr_db . '.tbl_leaves_dtl')
        ->where('leave_id', $leave_id)
        ->update(['status' => 'Cancelled']);

      return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function ApprovedMayor(Request $request)
  {
    try {
      DB::beginTransaction();
      $details = $request->details;
      $remarks = $request->remarks;
      $status = 0;
      foreach ($details as $key => $value) {
        if ($value['approved'] === 'true') {
          $status = $status + 1;
          db::table($this->hr_db . ".tbl_leaves_dtl")->where("id", $value['id'])
            ->update(['status' => 'Approved', 'status_approved' => 'Approved']);
          db::table($this->hr_db . '.tbl_dtr_jo')->where("trans_id", $value['id'])
            ->update(['status' => 'L']);
        } else {
          db::table($this->hr_db . ".tbl_leaves_dtl")->where("id", $value['id'])
            ->update(['status' => 'Disapproved', 'status_approved' => 'Disapproved']);
        }
      }
      if ($status > 0) {
        $status = "Approved";
      } else {
        $status = "Disapproved";
      }
      $app = db::select("CALL " . $this->lgu_db . ".jay_display_lgu_signatory('%MUN CITY MAYOR%')");
      $appId = 0;
      foreach ($app as $key => $value) {
        $appId = $value->Signatory_PP_ID;
      }
      $update = array(
        'approved_date' => $this->G->serverdatetime(),
        'approved_by' => $appId,
        'status' => $status,
        'declined_remarks_approval' => $remarks
      );

      if ($appId !== Auth::user()->Employee_id) {
        // log::debug("aut");
        //  log::debug($this->G->serverdatetime());
        //  log::debug($appId);
        //  log::debug($status);
        //  log::debug(Auth::user()->Employee_id);
        //  log::debug($remarks);
        //  log::debug($update);
        $update = array(
          'approved_date' => $this->G->serverdatetime(),
          'approved_by' => $appId,
          'status' => $status,
          'by_authority_id' => Auth::user()->Employee_id,
          'declined_remarks_approval' => $remarks
        );
      }

      db::table($this->hr_db . ".tbl_leaves")
        ->where("leave_id", $request->leave_id)
        ->update($update);

      DB::commit();
      return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $th, 'status' => 'error']));
    }
  }
  public function Approved(Request $request)
  {

    try {
      DB::beginTransaction();
      $details = $request->details;
      $remarks = $request->remarks;
      $status = 0;
      $disapproved = 0;
      $leaveCount = 0;
      foreach ($details as $key => $value) {
        $leaveCount  = $leaveCount + 1;
        if ($value['approved'] === 'true') {
          $status = $status + 1;
          db::table($this->hr_db . ".tbl_leaves_dtl")->where("id", $value['id'])
            ->update(['status' => 'Recommended', 'status_rec' => 'Recommended']);
        } else {
          db::table($this->hr_db . ".tbl_leaves_dtl")->where("id", $value['id'])
            ->update(['status' => 'Disapproved', 'status_rec' => 'Disapproved']);
          $disapproved =  $disapproved + 1;
        }
      }
      if ($status > 0) {
        $status = "Recommended";
      } else {
        $status = "Disapproved";
      }
      // if ($status ==='Disapproved') {
      //   $app = db::select("CALL " . $this->lgu_db . ".jay_display_lgu_signatory('%MUN CITY MAYOR%')");
      //   $appId = 0;
      //   $certById = 0;
      //   $certBy = db::select("SELECT `certify_by` FROM ".$this->hr_db .".tbl_leaves WHERE `certify_by` > 0 ORDER BY `leave_id` DESC LIMIT 1");
      //   foreach ($certBy as $key => $valuecert) {
      //     $certById = $valuecert->certify_by;
      //   }
      //   foreach ($app as $key => $value) {
      //     $appId = $value->Signatory_PP_ID;
      //   }
      //   $update = array(
      //    'approved_date' => $this->G->serverdatetime(),
      //    'approved_by' => $appId,
      //    'certify_by'=>$certById,
      //    'certify_date'=> $this->G->serverdatetime(),
      //   );
      // db::table($this->hr_db . ".tbl_leaves")
      // ->where("leave_id", $request->leave_id)
      // ->update($update);
      // }

      db::table($this->hr_db . ".tbl_leaves")
        ->where("leave_id", $request->leave_id)
        ->update(['recommended_date' => $this->G->serverdatetime(), 'recommended_by' => Auth::user()->Employee_id, 'status' => $status, 'declined_remarks' => $remarks]);

      DB::commit();
      return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $th, 'status' => 'error']));
    }
  }
  public function getForApprovalMayor(Request $request)
  {
    try {

      $app = db::select("CALL " . $this->lgu_db . ".jay_display_lgu_signatory('%MUN CITY MAYOR%')");
      $MayorId = 0;
      foreach ($app as $key => $value) {
        $MayorId = $value->Signatory_PP_ID;
      }
      $array = array();
      if (Auth::user()->Employee_id === $MayorId) {
        $data = db::table($this->hr_db . ".employee_information")
          ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
          ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->where("hr_emp_leave_type.leave_type", '<>', 'Forced Leave')
          ->where("tbl_leaves.isWebApp", '=', 'True')
          ->whereNotIn("tbl_leaves_dtl.status", ['Invalid', 'Cancelled', 'Disapproved'])
          ->whereNotNull("tbl_leaves.certify_date")
          ->whereNull("tbl_leaves.approved_date")
          ->orderBy("tbl_leaves.leave_id", "desc")
          ->groupBy("tbl_leaves.leave_id")
          ->get();

        foreach ($data as $key => $value) {
          if ($value->PPID === $value->headId && $value->leave_date >= $this->G->serverdate()) {
            $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
              ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
              ->select('*', db::raw(" 'false' as approved "))
              ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
              ->where("tbl_leaves_dtl.status", 'Certified')
              ->get();
            $datax = array(
              'leave_id' => $value->leave_id,
              'ref_no' => $value->ref_no,
              'applied_date' => $value->applied_date,
              'NAME' => $value->NAME,
              'purpose' => $value->purpose,
              'details' => $dtlx
            );
            array_push($array, $datax);
          }
        }

        $data = db::table($this->hr_db . ".employee_information")
          ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
          ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->where("hr_emp_leave_type.leave_type", '=', 'Forced Leave')
          ->where("tbl_leaves.isWebApp", '=', 'True')
          // ->where("tbl_leaves_dtl.leave_id",$value->leave_id)
          ->whereNotIn("tbl_leaves_dtl.status", ['Invalid', 'Cancelled', 'Disapproved'])
          ->whereNotNull("tbl_leaves.certify_date")
          ->whereNull("tbl_leaves.approved_date")
          ->orderBy("tbl_leaves.leave_id", "desc")
          ->groupBy("tbl_leaves.leave_id")
          ->get();

        foreach ($data as $key => $value) {
          if ($value->PPID === $value->headId && $value->leave_date >= $this->G->serverdate()) {
            $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
              ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
              ->select('*', db::raw(" 'false' as approved "))
              ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
              #->where("tbl_leaves_dtl.status_cert", 'Certified')
              ->get();
            $datax = array(
              'leave_id' => $value->leave_id,
              'ref_no' => $value->ref_no,
              'applied_date' => $value->applied_date,
              'NAME' => $value->NAME,
              'purpose' => $value->purpose,
              'details' => $dtlx
            );
            array_push($array, $datax);
          }
        }
      } else {
        $data = db::table($this->hr_db . ".employee_information")
          ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
          ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->where("hr_emp_leave_type.leave_type", '<>', 'Forced Leave')
          ->where("tbl_leaves.isWebApp", '=', 'True')
          ->whereNotIn("tbl_leaves_dtl.status", ['Invalid', 'Cancelled', 'Disapproved'])
          ->whereNotNull("tbl_leaves.certify_date")
          ->whereNull("tbl_leaves.approved_date")
          ->orderBy("tbl_leaves.leave_id", "desc")
          ->groupBy("tbl_leaves.leave_id")
          ->get();
        foreach ($data as $key => $value) {
          $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
            ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
            ->select('*', db::raw(" 'false' as approved "))
            # ->where("tbl_leaves_dtl.status_cert", 'Certified')
            ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
            ->get();
          if ($value->PPID == $value->headId &&  $value->leave_date < $this->G->serverdate()) {
            $datax = array(
              'leave_id' => $value->leave_id,
              'ref_no' => $value->ref_no,
              'applied_date' => $value->applied_date,
              'NAME' => $value->NAME,
              'purpose' => $value->purpose,
              'details' => $dtlx
            );
            array_push($array, $datax);
          } elseif ($value->PPID <> $value->headId) {
            $datax = array(
              'leave_id' => $value->leave_id,
              'ref_no' => $value->ref_no,
              'applied_date' => $value->applied_date,
              'NAME' => $value->NAME,
              'purpose' => $value->purpose,
              'details' => $dtlx
            );
            array_push($array, $datax);
          }
        }
        $data = db::table($this->hr_db . ".employee_information")
          ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
          ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->where("hr_emp_leave_type.leave_type", '=', 'Forced Leave')
          ->where("tbl_leaves.isWebApp", '=', 'True')
          ->whereNotIn("tbl_leaves_dtl.status", ['Invalid', 'Cancelled', 'Disapproved'])
          ->whereNotNull("tbl_leaves.certify_date")
          ->whereNull("tbl_leaves.approved_date")
          // ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
          ->orderBy("tbl_leaves.leave_id", "desc")
          ->groupBy("tbl_leaves.leave_id")
          ->get();
        foreach ($data as $key => $value) {
          $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
            ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
            ->select('*', db::raw(" 'false' as approved "))
            #  ->where("tbl_leaves_dtl.status_cert", 'Certified')
            ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
            ->get();
          if ($value->PPID == $value->headId &&  $value->leave_date < $this->G->serverdate()) {
            $datax = array(
              'leave_id' => $value->leave_id,
              'ref_no' => $value->ref_no,
              'applied_date' => $value->applied_date,
              'NAME' => $value->NAME,
              'purpose' => $value->purpose,
              'details' => $dtlx
            );
            array_push($array, $datax);
          } elseif ($value->PPID <> $value->headId) {
            $datax = array(
              'leave_id' => $value->leave_id,
              'ref_no' => $value->ref_no,
              'applied_date' => $value->applied_date,
              'NAME' => $value->NAME,
              'purpose' => $value->purpose,
              'details' => $dtlx
            );
            array_push($array, $datax);
          }
        }
      }
      // $dtlx = db::table($this->hr_db.".tbl_leaves_dtl")
      //       ->join($this->hr_db.".hr_emp_leave_type",'hr_emp_leave_type.leave_number','tbl_leaves_dtl.leave_type_id')
      //       ->select('*',db::raw(" 'false' as approved "))
      //       ->where("tbl_leaves_dtl.leave_id",$value->leave_id)
      //       ->where("tbl_leaves_dtl.status",'Certified')
      //       ->get();
      //       $datax = array();
      //       foreach ($data as $key => $value) {
      //         if (Auth::user()->Employee_id === $MayorId) {
      //           if ($value->leave_date < $this->G->serverdatetime()) {
      //             $datax = array(
      //                         'leave_id'=>$value->leave_id,
      //                         'ref_no'=>$value->ref_no,
      //                         'applied_date'=>$value->applied_date,
      //                         'NAME'=>$value->NAME,
      //                         'purpose'=>$value->purpose,
      //                         'details'=>$dtlx
      //                         );
      //               array_push($array, $datax );
      //            }
      //         }else{
      //           if ($value->leave_date > $this->G->serverdatetime()) {
      //             $datax = array(
      //                         'leave_id'=>$value->leave_id,
      //                         'ref_no'=>$value->ref_no,
      //                         'applied_date'=>$value->applied_date,
      //                         'NAME'=>$value->NAME,
      //                         'purpose'=>$value->purpose,
      //                         'details'=>$dtlx
      //                         );
      //               array_push($array, $datax );
      //            }
      //         }
      //       }

      return response()->json(new jsonresponse($array));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function getForApproval(Request $request)
  {
    try {
      $data = db::table($this->hr_db . ".employee_information")
        ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
        ->where("tbl_leaves.status", 'Active')
        ->where("tbl_leaves.isWebApp", '=', 'True')
        // ->where("Head_Dept", '=', Auth::user()->Employee_id)
        // ->orWhere(function ($query) {
        //   $query->where('Head_Dept_asign', '=', Auth::user()->Employee_id);
        // })
        // ->where("Head_Dept", Auth::user()->Employee_id)
        // ->orWhere("Head_Dept_asign", Auth::user()->Employee_id)
        ->Where(function ($query) {
          $query->where('Head_Dept', Auth::user()->Employee_id)
            ->orWhere('Head_Dept_asign', Auth::user()->Employee_id);
        })
        ->orderBy("leave_id", "desc")
        ->get();
      // $data=db::select("")

      $array = array();
      foreach ($data as $key => $value) {
        // $value;
        $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->select('*', db::raw(" 'false' as approved "))
          ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
          ->get();
        $datax = array(
          'leave_id' => $value->leave_id,
          'ref_no' => $value->ref_no,
          'applied_date' => $value->applied_date,
          'NAME' => $value->NAME,
          'purpose' => $value->purpose,
          'details' => $dtlx
        );
        // $datax['dtls'] = $dtlx;
        array_push($array, $datax);
      }
      return response()->json(new jsonresponse($array));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function getRecommendedList(Request $request)
  {
    try {
      $data = db::table($this->hr_db . ".employee_information")
        ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
        ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
        ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
        ->select("tbl_leaves.*", 'employee_information.*', db::raw("GROUP_CONCAT(DISTINCT(status_rec)) AS 'status'"), db::raw("GROUP_CONCAT(DISTINCT(leave_type)) AS 'leavetype'"), db::raw("GROUP_CONCAT(DISTINCT(DATE_FORMAT(`leave_date`,'%m/%d/%Y'))ORDER BY leave_date asc ) AS 'leavedate'"))
        ->where("recommended_by", Auth::user()->Employee_id)
        ->orderBy("tbl_leaves.recommended_date", "desc")
        ->groupBy("tbl_leaves.leave_id")
        ->limit(100)
        ->get();
      $array = array();
      foreach ($data as $key => $value) {
        // $value;
        $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->select('*', db::raw(" 'false' as approved "))
          ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
          ->get();
        $datax = array(
          'leave_id' => $value->leave_id,
          'ref_no' => $value->ref_no,
          'recommended_date' => $value->recommended_date,
          'applied_date' => $value->applied_date,
          'name' => $value->NAME,
          'leavetype' => $value->leavetype,
          'NAME' => $value->NAME,
          'purpose' => $value->purpose,
          'details' => $dtlx,
          'leave_date' => $value->leavedate,
          'status' => $value->status

        );
        array_push($array, $datax);
      }
      return response()->json(new jsonresponse($array));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function getApprovedList(Request $request)
  {
    try {
      $data = db::table($this->hr_db . ".employee_information")
        ->join($this->hr_db . ".tbl_leaves", 'tbl_leaves.employee_id', 'employee_information.PPID')
        ->join($this->hr_db . ".tbl_leaves_dtl", 'tbl_leaves_dtl.leave_id', 'tbl_leaves.leave_id')
        ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
        ->select(
          "tbl_leaves.*",
          'employee_information.*',
          db::raw("GROUP_CONCAT(DISTINCT(status_approved)) AS 'status_approved'"),
          db::raw("GROUP_CONCAT(DISTINCT(leave_type)) AS 'leavetype'"),
          db::raw("GROUP_CONCAT(DISTINCT(DATE_FORMAT(`leave_date`,'%m/%d/%Y'))ORDER BY leave_date asc ) AS 'leavedate'"),
          db::raw("GROUP_CONCAT(DISTINCT(`status_rec`)ORDER BY status_rec asc ) AS 'status_rec'")
        )
        ->where("approved_by", Auth::user()->Employee_id)
        ->orWhere("by_authority_id", Auth::user()->Employee_id)
        ->orderBy("tbl_leaves.approved_date", "desc")
        ->groupBy("tbl_leaves.leave_id")
        ->limit(300)
        ->get();
      $array = array();
      foreach ($data as $key => $value) {
        // $value;
        $dtlx = db::table($this->hr_db . ".tbl_leaves_dtl")
          ->join($this->hr_db . ".hr_emp_leave_type", 'hr_emp_leave_type.leave_number', 'tbl_leaves_dtl.leave_type_id')
          ->select('*', db::raw(" 'false' as approved "))
          ->where("tbl_leaves_dtl.leave_id", $value->leave_id)
          ->get();
        $datax = array(
          'leave_id' => $value->leave_id,
          'ref_no' => $value->ref_no,
          'approved_date' => $value->approved_date,
          'applied_date' => $value->applied_date,
          'name' => $value->NAME,
          'leavetype' => $value->leavetype,
          'NAME' => $value->NAME,
          'purpose' => $value->purpose,
          'details' => $dtlx,
          'leave_date' => $value->leavedate,
          'status' => $value->status,
          'status_rec' => $value->status_rec,
          'status_approved' => $value->status_approved
        );
        array_push($array, $datax);
      }
      return response()->json(new jsonresponse($array));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function getLeaveLedger(Request $request)
  {
    $empId = Auth::user()->Employee_id;
    $refDate  = date_create($request->date);
    $TempDate = date_format($refDate, 'Y-01-01');
    $empData = db::table($this->hr_db . ".employees")->where("SysPK_Empl",  $empId)->first();
    // log::debug($empData->lc_earn_start_date_casual);
    if ($empData->lc_earn_start_date_casual > date_format($refDate, 'Y-m-d')) {
    }
    if ($empData->lc_earn_start_date_casual < $TempDate && date_format(date_create($empData->lc_earn_start_date_casual), 'Y')  === date_format(date_create($TempDate), 'Y')) {
      $TempDate = $empData->lc_earn_start_date_casual;
    } elseif ($empData->lc_earn_start_date_casual < $refDate && date_format(date_create($empData->lc_earn_start_date_casual), 'Y')  === date_format($refDate, 'Y')) {
      $TempDate = $empData->lc_earn_start_date_casual;
    }
    $DateFrom = $TempDate;
    $DateTo =  date_format($refDate, 'Y-m-d');
    $DateCasual = $empData->lc_earn_start_date_casual;
    $DatePermanent = $empData->lc_earn_start_date;
    // db::select("call " . $this->hr_db . ".spl_display_leave_cummulation_jay_new_hostory1(?,?,?,?,?)", [$empId, $DateFrom, $DateTo,  $DateCasual, $DatePermanent]);
    $list =  db::select("call " . $this->hr_db . ".spl_display_leave_cummulation_jay_new1_rans(?,?,?,?,?)", [$empId, $DateFrom, $DateTo,  $DateCasual, $DatePermanent]);
    return response()->json(new jsonresponse($list));
  }
  public function updateForApproval($id)
  {
    try {
      DB::beginTransaction();
      db::table($this->hr_db . ".tbl_leaves")
        ->where("leave_id", $id)
        ->update(['status' => 'Certified', 'approved_date' => null, 'approved_by' => null, 'by_authority_id' => null]);
      db::table($this->hr_db . ".tbl_leaves_dtl")
        ->where("leave_id",  $id)
        ->update(['status_approved' => 'For Approval', 'status' => 'Certified']);
      DB::commit();
      return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
    } catch (\Throwable $th) {
      DB::rollback();
      //throw $th;
    }
  }
}
