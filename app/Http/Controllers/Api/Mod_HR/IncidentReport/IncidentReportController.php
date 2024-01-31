<?php

namespace App\Http\Controllers\Api\Mod_HR\IncidentReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Auth;
use PDF;

class IncidentReportController extends Controller
{
    private $lgu_db;
    private $hr_db;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function incidentReports(Request $request)
    {
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            // ->select("*",db::raw("GROUP_CONCAT(DISTINCT item_description SEPARATOR ' <br/> ') AS items"))
            ->where('rep_by', Auth::user()->Employee_id)
            ->where('entry_type', 'Normal')
            ->whereNotIn('status', ['Invalid', 'CANCELLED'])
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function overtimeReports(Request $request)
    {
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->select("*",
                db::raw('CONCAT(am_in_note," - ", am_out_note) AS time'),
            )
            ->where('rep_by', Auth::user()->Employee_id)
            ->where('entry_type', 'Overtime Passlip')
            ->whereNotIn('status', ['Invalid', 'CANCELLED', 'Inactive'])
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getScheduleLog($date)
    {
        $list = db::select("call ".$this->hr_db . ".spl_display_employee_list_regular_jay_new1(?,?,?)",[$date,$date,Auth::user()->Employee_id]);
        return response()->json(new JsonResponse($list));
    }
    public function incidentForHeadApproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_dtr_incident_report.rep_by')
            // ->whereNull('dept_app_by')
            ->where('status', $stat)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getScheduleByDate(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_dtr_incident_report.rep_by')
            // ->whereNull('dept_app_by')
            ->where('status', $stat)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function incidentHeadApproved(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_dtr_incident_report.rep_by')
            ->where('dept_app_by')
            ->where('status', $stat)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function incidentHeadList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_dtr_incident_report.rep_by')
            // ->where("Head_Dept", Auth::user()->Employee_id)
            ->where('status', $stat)
            // ->orWhere('ir_head', Auth::user()->Employee_id)
            ->Where(function ($query) {
                $query->Where('Head_Dept', Auth::user()->Employee_id)
                    ->orWhere('AssistantHead_Dept', Auth::user()->Employee_id);
            })
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function incidentHeadListApproved(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_dtr_incident_report.rep_by')
            ->where("dept_app_by", Auth::user()->Employee_id)
            ->orderBy("dept_app_dateTime", "desc")
            ->limit(100)
            ->get();
        return response()->json(new JsonResponse($list));
    }


    public function incidentForHeadApprovalApproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'HEAD APPROVED', 'dept_app_status' => 'APPROVED', 'dept_app_by' => Auth::user()->Employee_id, 'dept_app_dateTime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function incidentForHeadApprovalDisapproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'DISAPPROVED', 'dept_app_status' => 'DISAPPROVED', 'dept_app_by' => Auth::user()->Employee_id, 'dept_app_dateTime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function incidentForHeadApprovalNoted(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'HEAD NOTED', 'dept_app_status' => 'APPROVED', 'dept_not_by' => Auth::user()->Employee_id, 'dept_not_dateTime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function incidentForHeadNotedDisapproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'DISAPPROVED', 'dept_app_status' => 'DISAPPROVED', 'dept_not_by' => Auth::user()->Employee_id, 'dept_not_dateTime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function incidentMayorApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_dtr_incident_report.rep_by')
            ->where("mayor_app_by", Auth::user()->Employee_id)
            ->orderBy("date_approved", "desc")
            ->limit(100)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function incidentForMayor(Request $request)
    {
        $app = db::select("CALL " . $this->lgu_db . ".jay_display_lgu_signatory('%MUN CITY MAYOR%')");
        $MayorId = 0;
        foreach ($app as $key => $value) {
            $MayorId = $value->Signatory_PP_ID;
        }
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'MAYOR APPROVED', 'mayor_app_by' => $MayorId, 'mayor_app_status' => 'APPROVED', 'mayor_autho_by' => Auth::user()->Employee_id, 'date_approved' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function incidentForMayorDisapproved(Request $request)
    {
        $app = db::select("CALL " . $this->lgu_db . ".jay_display_lgu_signatory('%MUN CITY MAYOR%')");
        $MayorId = 0;
        foreach ($app as $key => $value) {
            $MayorId = $value->Signatory_PP_ID;
        }

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'DISAPPROVED', 'mayor_app_by' => $MayorId, 'mayor_app_status' => 'DISAPPROVED', 'mayor_autho_by' => Auth::user()->Employee_id, 'date_approved' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function incidentAction(Request $request)
    {

        $list =  $request->list;
        $remarks =  $request->remarks;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['hr_notes' => $remarks, 'status' => 'HR APPROVED', 'action_status' => 'APPROVED', 'action_id' => Auth::user()->Employee_id, 'action_datetime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function incidentActionApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->hr_db . '.tbl_dtr_incident_report')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'tbl_dtr_incident_report.rep_by')
            ->where("action_id", Auth::user()->Employee_id)
            ->orderBy("action_datetime", "desc")
            ->limit(100)
            ->get();
        return response()->json(new JsonResponse($list));
    }


    public function incidentActionDisapproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $value['ir_id'])
                ->update(['status' => 'DISAPPROVED', 'action_id' => Auth::user()->Employee_id, 'action_status' => 'DISAPPROVED',  'action_datetime' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function reference(Request $request)
    {
        // dd($request);
        $pre = 'IR';
        $table = $this->hr_db . ".tbl_dtr_incident_report";
        $date = $request->date;
        $refDate = 'ref_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function remarks(Request $request)
    {
        // dd($request);
        $data = db::table($this->hr_db . ".tbl_dtr_incident_report_deduction")->get();
        return response()->json(new JsonResponse($data));
    }
    public function storeIncident(Request $request)
    {
        $form = $request->form;
        $id = $form['ir_id'];
        $form['rep_by'] = Auth::user()->Employee_id;
        
        if ($id > 0) {
            DB::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $id)
                ->update($form);
        } else {
            $form['rep_by'] = Auth::user()->Employee_id;
            DB::table($this->hr_db . '.tbl_dtr_incident_report')
                ->insert($form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function storeOvertime(Request $request)
    {
        $form = $request->form;
        $id = $form['ir_id'];
        $form['entry_type'] = "Overtime Passlip";
        $form['rep_by'] = Auth::user()->Employee_id;
        if ($id > 0) {
            DB::table($this->hr_db . '.tbl_dtr_incident_report')
                ->where("ir_id", $id)
                ->update($form);
        } else {
            $form['rep_by'] = Auth::user()->Employee_id;
            DB::table($this->hr_db . '.tbl_dtr_incident_report')
                ->insert($form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function cancelReport($id)
    {
        db::table($this->hr_db . '.tbl_dtr_incident_report')
            ->where('ir_id', $id)
            ->update(['status' => 'Inactive']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function cancelOvertime($id)
    {
        db::table($this->hr_db . '.tbl_dtr_incident_report')
            ->where('ir_id', $id)
            ->update(['status' => 'Inactive']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function print(Request $request)
    {
        // $incident = DB::table($this->hr_db . '.tbl_dtr_incident_report')
        //     ->where('rep_by', Auth::user()->Employee_id)
        //     ->select('*', db::raw("TIME_FORMAT(time_incident, '%h:%i %p') as 'time_incident'"))
        //     ->get();

        $list = $request->itm;
        $incident = DB::select('call ' . $this->hr_db . '.rans_print_incedent(?)', [$list['ir_id']]);
        $value = [];
        foreach ($incident as $key => $val) {
            $value = $val;
        }
        $Template = '<table width="100%" cellpadding="1" style="border-right:1px solid black; border-top:1px solid black;border-left:1px solid black; border-bottom:1px solid black">

      <tr>
          <th width="8%" align="left">
              <img src="' . public_path() . '/images/Logo1.png"  height="35" width="35">
          </th>

          <th width="1%"> </th>

          <th width="61%" align="left" style="font-size:8pt; border-right:1px solid black">
              Republic of the Philippines
              <br/>
             &nbsp; Province of Cebu
              <br/>
              &nbsp; '.env("cityname",false).'
          </th>
          <th width="30%" style="font-size:10pt" align="right"> <b>
                  INCIDENT REPORT
              <br/>
                  FORM 2 </b>
              <br/>
             <i> (Time Log Entries) </i>
           </th>
      </tr>
  </table>
  ';

        $Template .= ' <table width="100%" style="border-left:1px solid black" cellpadding="2">

        <tr>
            <td width="18%"  style="font-size:9pt; border-right:1px solid black; border-bottom:1px solid black">
                 <br/> <b> INCIDENT/SUBJECT </b>
            </td>
            <td width="52%" style="font-size:8pt; border-bottom:1px solid black; border-right:1px solid black "> ' . $value->subject . ' </td>
            <td width="10%" style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black">Date<br/>Reported:
            </td>
            <td width="20%" style="border-right:1px solid black; border-bottom:1px solid black">' . date_format(date_create($value->ref_date), "m/d/Y")  . '</td>
        </tr>
        <tr>
            <td width="18%"  style="font-size:9pt; border-right:1px solid black; border-bottom:1px solid black">
              <b> Date of Incident </b>
            </td>
            <td width="52%" style="font-size:9pt; border-bottom:1px solid black; border-right:1px solid black ">' . date_format(date_create($value->date_incident), "m/d/Y")  . '</td>
            <td width="30%" style="font-size:9pt; border-right:1px solid black;"> Reported by: </td>
        </tr>
        <tr>
            <td width="18%"  style="font-size:9pt; border-right:1px solid black; border-bottom:1px solid black">
            <b> Time of Incident </b>
            </td>
            <td width="52%" style="font-size:9pt; border-bottom:1px solid black; border-right:1px solid black "> ' . $value->time_incident . ' </td>
            <td width="30%" style="font-size:9pt; border-right:1px solid black;">  </td>
        </tr>
        <tr>
            <td width="21%"  style="font-size:9pt;  border-bottom:1px solid black">
            <b> DETAILS OF INCIDENT </b>
            </td>
            <td width="49%"  style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black">
                (Attach necessarry file, pictures, etc. and use another sheet if needed.)
            </td>
            <td width="30%" style="font-size:9pt; border-right:1px solid black;">  </td>
        </tr>
        <tr>
            <td width="70%"  style="font-size:8pt; border-right:1px solid black;">
            </td>
            <td width="30%" align="center" style="font-size:7pt; border-right:1px solid black;"> ' . $value->reported . ' </td>
        </tr>
        <table width="100%" cellpadding="2">
        <tr>
            <td width="70%" height="55px"  style="font-size:8pt; border-right:1px solid black;"> ' . $value->dtl_incident . '
            </td>
            <td width="30%"  style="font-size:8pt; border-right:1px solid black; border-top:1px solid black;"><b><i> For Dept./Section/ Unit Head </i></b> <br/> <b> Approved </b></td>
        </tr>
        </table>
        <tr>
            <td width="70%"  style="font-size:8pt; border-right:1px solid black;"> </td>
            <td width="30%"  style="font-size:6.5pt; border-right:1px solid black; text-align:center;"> ' . $value->dept_app_time . '</td>
        </tr>

        <tr>
            <td width="30%" style="font-size:8pt; border-top:1px solid black">
                <b> PARTICULARS </b>
            </td>
            <td width="40%" style="font-size:7pt; border-right:1px solid black; border-top:1px solid black">
              (For Personnel Section use only)
            </td>
            <td width="30%" align="center" style="font-size:8pt; border-top:1px solid black; border-right:1px solid black">
                <b> REMARKS/RECOMMENDATION </b>
            </td>
           
        </tr>
        <tr>
            <td width="40%" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black">
                Classication of Filing/Request
            </td>
            <td width="30%" align="center" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black">
                Frequency
            </td>
            <td width="30%" align="center" style="font-size:8pt; border-top:1px solid black; border-right:1px solid black">'.$value->hr_notes .'</td>
         
        </tr>
        <tr>
            <td width="3%" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black">
            </td>
            <td width="37%" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black">
                Within the prescribed period
            </td>
            <td width="30%" align="center" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black"> ' . $value->w_pres_period . '

            </td>
            <td width="30%" align="center" style="font-size:8pt; border-top:1px solid black; border-right:1px solid black"> </td>
          
        </tr>
        <tr>
            <td width="3%" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black">
            </td>
            <td width="37%" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black">
                Lapsed the prescribed period
            </td>
            <td width="30%" align="center" style="font-size:8pt; border-right:1px solid black; border-top:1px solid black"> ' . $value->l_pres_period . '

            </td>
            <td width="30%" align="center" style="font-size:8pt; border-top:1px solid black; border-right:1px solid black"> </td>
           
        </tr>
        <tr>
            <td width="70%"  style="font-size:8pt; border-top:1px solid black;border-right:1px solid black;"> NOTED for action: </td>
           
            <td width="30%" align="center"  style="font-size:6.5pt; border-right:1px solid black;border-top:1px solid black;"></td>
        </tr>
        <tr>
            <td width="70%" height="30px"  style="font-size:7pt; border-right:1px solid black;"> </td>
          
            <td width="30%" height="30px" style="font-size:8pt; border-right:1px solid black; "> </td>
        </tr>

        <tr>
            <td width="70%" align="center"  style="font-size:8pt; border-right:1px solid black;">' . $value->action_time . '</td>
            <td width="30%"  style="font-size:7pt; border-right:1px solid black;"> </td>
            
        </tr>
        <tr>
            <td width="70%" align="center"  style="font-size:7pt; border-right:1px solid black;"><b> HRMO IV </b></td>
            <td width="30%"  style="font-size:7pt; border-right:1px solid black;"> </td>
         
        </tr>
        <tr>
            <td width="100%"  style="font-size:6pt; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black;"><b>Distribution: Original Copy-Personnel, Dupplicate-Reporting Dept./Party, 3rd Copy-201 file, 4th Copy-Others as needed </b></td>
           
        </tr>

    </table>
';

        // PDF::Image(public_path() . $value->{'dept_not'}, 150, 203, 25, 25, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        // PDF::Image(public_path() . $value->{'mayor_autho'}, 80, 245, 40, 30, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        // PDF::Image(public_path() . $value->{'action'}, 80, 245, 40, 30, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        // log::debug();
        PDF::SetTitle('Incident Report');
        PDF::SetFont('helvetica', '', 8);
        PDF::SetImageScale(PDF_IMAGE_SCALE_RATIO);
        PDF::AddPage('P');

        // PDF::Image('img/Logo1.png', 160, 55, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        // PDF::Image('img/Logo1.png', 160, 80, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        // PDF::Image('img/Logo1.png', 160, 105, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        // PDF::Image('img/Logo1.png', 40, 100, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);

        PDF::Image(public_path() . $value->{'dept_app'}, 160, 55, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        // PDF::Image(public_path() . $value->{'dept_not'}, 160, 80, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        PDF::Image(public_path() . $value->{'mayor_autho'}, 160, 105, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);
        PDF::Image(public_path() . $value->{'action'}, 40, 100, 20, 20, 'PNG', 'http://www.tcpdf.org', '', false, 300);

        PDF::writeHTML($Template, true, 0, true, 0);
        PDF::Output(public_path() . '/prints.pdf', 'F');
        $full_path = public_path() . '/prints.pdf';

        try {
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Throwable $th) {
            log::debug($th);
            return response()->json(new JsonResponse(['errormsg' => $th, 'status' => 'error']));
        }
    }
}
