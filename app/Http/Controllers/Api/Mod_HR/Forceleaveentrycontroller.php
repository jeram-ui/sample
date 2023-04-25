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

class Forceleaveentrycontroller extends Controller
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
            // $data =  db::select('select leave_type,leave_number FROM '.$this->hr_db. '.hr_emp_leave_type') ;
            // return response()->json(new jsonresponse($data));
            $list = db::table($this->hr_db.'.hr_emp_leave_type')
            ->where('hr_emp_leave_type.leave_type','Forced Leave')
            ->get();
            return response()->json(new jsonresponse($list));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'stat' => 'error']));
        }
    }
    public function showlist(Request $request)
    {
        try {

            $data = db::select('call '.$this->hr_db.'.p_tbl_leaves_fritz(?)',[Auth::user()->Employee_id]);

            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function store(Request $request)
    {
        try {
            $main = $request->form;
            $leave = $request->leave;
            // log::debug($main);
            // log::debug($leave);
            $idx = $main['leave_id'];
            // DB::beginTransaction();
            $main['employee_id']= Auth::user()->Employee_id;
            if ($idx == 0) {
            db::table($this->hr_db .'.tbl_leaves')->insert($main);
            $id = DB::getPdo()->lastInsertId();
            log::debug($id);
             foreach ($leave as $key => $value) {
                $data =array(
                'leave_date'=>$value['leave_from'],
                'leave_dateto'=>$value['leave_from'],
                'leave_for'=>$value['leave_for'],
                'leave_type_id'=>$value['leaveId'],
                'payment_mode'=>$value['payment_mode'],
                'leave_id'=>$id,
                );
                  db::table($this->hr_db .'.tbl_leaves_dtl')->insert($data);
             }

            } else {

            //   db::table($this->hr_db .'.tbl_leaves_dtl')->where('id', $idx)->update($main);
            }
            // DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            // DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }

    public function getRef(Request $request)
    {
        $query = DB::select("SELECT CONCAT(LPAD(COUNT(*)+1,4,0),'-',DATE_FORMAT(NOW(),'%Y'))as 'NOS' FROM " . $this->hr_db . ".tbl_leaves");
        return response()->json(new JsonResponse(['data' => $query]));
    }
   public function print(Request $request){
     $form = $request['itm'];
     log::debug($form['leave_id']);
   try{
    $data = db::select("call ".$this->hr_db.".balodoy_display_tbl_leaves_report(?)",[$form['leave_id']]);
    $row = [];
    foreach ($data as $key => $value) {
      $row = $value;
    }
    log::debug($row->Department);
    $header ='
    <h5>Civil Service Form No. 6</h5>
    <h5>Revised 2020</h5>
    <table style="width=100%;">
    <tr>
    <th align="right">
    <img src="' . public_path() . '/images/Logo1.png"  height="40" width="40">
    </th>
    <th style="font-size:9pt;" align="center">
    Republic of the Philippines
    <br>
    CITY GOVERNMENT OF NAGA, CEBU
    <br>
    East Poblacion, City of Naga, Cebu
    <br>
    </th>
    <th align="center" >
     <div style="border-bottom: 1px dashed black;border-top: 1px dashed black;border-right: 1px dashed black;border-left: 1px dashed black;" cellpadding="4">Stamp of Date of Receipt</div>
    </th>
    </tr>
    </table>';
    $html_content =  $header;
        $html_content .='<h3 align="center">APPLICATION FOR LEAVE</h3>';

        $html_content .='
        <table width="100%" cellpadding="4"  style="border-bottom: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;border-left: 1px solid black;">
            <tr >
              <td width="40%">1. OFFICE/DEPARTMENT</td>
              <td width="25%">2. NAME: (Last)</td>
              <td width="25%">(First)</td>
              <td width="10%">(Middle)</td>
            </tr>
            <tr >
              <td width="40%" style="border-bottom: 1px solid black" >'.$row->Department.'</td>
              <td width="25%" style="border-bottom: 1px solid black">'.$row->{'Last Name'}.'</td>
              <td width="25%" style="border-bottom: 1px solid black">'.$row->{'First Name'}.'</td>
              <td width="10%" style="border-bottom: 1px solid black">'.$row->MI.'</td>
            </tr>
          </table>'
        ;
        $html_content.='<table width="100%" style="border-bottom: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;border-left: 1px solid black;" cellpadding="4">
        <tr>
          <td width="16%">3. DATE OF FILING</td>
          <td  width="12%">
            <u>04-01-2022</u>
          </td>
          <td width="12%">4. POSITION</td>
          <td width="40%">
            <u>Agriculturist II</u>
          </td>
          <td width="10%">5. SALARY</td>
          <td width="10%">31,587.00</td>
        </tr>
       </table>';
       $html_content.='<table cellpadding="4" width="100%" style="border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black">
       <tr>
         <td width="100%" style="text-bold; text-align: center">
           <b> 6. DETAILS OF APPLICATION </b>
         </td>
       </tr>
     </table>';
     $html_content.=' <table width="100%" border="1" cellpadding="4">
     <tr>
       <td width="60%">
        6.A TYPE OF LEAVE TO BE AVAILED OF<br>
         <table width="100%" style="font-size:7pt;" cellpadding = "2" >
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Vacation Leave (Sec. 51,Rule XVI, Omnibus Rules Implementing
               E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Mandatory/Forced Leave (Sec. 25, Rule XVI, Omnibus Rules
               Implementing E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
              <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Sick Leave (Sec.43, Rule XVI,Omnibus Rules Implementing E.O.
               No.292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Maternity Leave (R.A. No. 11210/IRR issued by CSC,DOLE and
               SSS)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Paternity Leave (R.A. No. 8187/CSC MC No. 71, s. 1998, as
               amended)
             </td>
           </tr>
           <tr>
             <td>
              <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Special Privilege Leave (Sec. 21,
               Rule XVI, Omnibus Rules Implementing E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
              Solo Parent Leave (RA No. 8972/CSC
               MC No. 8, s. 2004)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Study Leave (Sec. 68, Rule XVI, Omnibus Rules Implementing
               E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
              <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               10-Day VAWC Leave (R.A. No. 9262/CSC
               MC No.15, s. 2005)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Rehabilitation Privilege (Sec. 55, Rule XVI, Omnibus Rules
               Implementing E.O. No. 292)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Special Leave Benefits for Women (R.A. No. 9710/CSC MC No. 25,
               s. 2010)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
               Special Emergency (Calamity) Leave (CSC MC No. 2, s. 2012, as
               amended)
             </td>
           </tr>
           <tr>
             <td>
             <input type="checkbox" name="0" value="0" checked="true" readonly="true">
             Adoption Leave (R.A. No. 8552)
             </td>
           </tr>
           <tr>
             <td>
               <br />
               <br />
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
               >
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
              <input type="checkbox" name="0" value="0" checked="true" readonly="true">Within the Philippines
             </td>
             <td width="50%" style="border-bottom: 1px solid black">datax</td>
           </tr>
           <tr>
             <td width="50%">
              <input type="checkbox" name="0" value="0" checked="true" readonly="true">Abroad (Specify)
             </td>
             <td width="50%" style="border-bottom: 1px solid black">datax</td>
           </tr>
           <tr>
             <td><br /><i>In case of Sick Leave:</i></td>
           </tr>
           <tr>
             <td width="60%">
             <input type="checkbox" name="0" value="0" checked="true" readonly="true"> In Hospital (Specify Illness)
             </td>
             <td width="40%" style="border-bottom: 1px solid black"></td>
           </tr>
           <tr>
             <td width="60%">
             <input type="checkbox" name="0" value="0" checked="true" readonly="true"> Out Patient (Specify Illness
             </td>
             <td width="40%" style="border-bottom: 1px solid black"></td>
           </tr>
           <tr>
             <td
               height="15px"
               width="100%"
               colspan="2"
               style="border-bottom: 1px solid black"
             ></td>
           </tr>
           <tr>
             <td width="100%"><i>In case of Special Leave Benefits for Women:</i></td>
           </tr>
           <tr>
             <td width="40%">(Specify Illness)</td>
             <td width="60%" style="border-bottom: 1px solid black"></td>
           </tr>
           <tr>
             <td
               height="15px"
               width="100%"
               colspan="2"
               style="border-bottom: 1px solid black"
             ></td>
           </tr>
           <tr>
             <td><i>in case of Study Leave:</i></td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked="true" readonly="true"> Completion of Masters Degree</td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked="true" readonly="true"> BAR/Board Examination Review</td>
           </tr>
           <tr>
             <td  colspan="2"><br /><i>Other purpose:</i></td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked="true" readonly="true"> Monetization of Leave Credits</td>
           </tr>
           <tr>
             <td  colspan="2"><input type="checkbox" name="0" value="0" checked="true" readonly="true"> Terminal Leave</td>
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
    $html_content.='<table width="100%" border="1">
        <tr>
          <td width="60%">
             <table width="100%" cellpadding="4">
              <tr>
                <td><b>7.A CERTIFICATION OF LEAVE CREDITS</b></td>
              </tr>
              <tr>
                <td width="100%" align="center"><br />As of 2022</td>
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
                 <td>0</td>
                 <td>0</td>
                </tr>
                <tr>
                <td align="center"><i>Less this application</i></td>
                <td>0</td>
                <td>0</td>
               </tr>
               <tr>
               <td align="center"><i>Balance</i></td>
               <td>0</td>
               <td>0</td>
              </tr>
               </table>
              </td>
              </tr>
              <tr>
                <td  width="15%"></td>
                <td
                  align ="center"
                  height="20px"
                  width="70%"
                  colspan="2"
                >
                  <br />
                  <br />
                  <table width ="100%" align ="center" cellpadding="4">
                   <tr>
                     <td style="text-align: center;border-bottom: 1px solid black;">
                     <b>Grace S. Marquez , Supervising Admin Officer IV </b>
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
                  <input type="checkbox" name="0" value="0" checked="true" readonly="true">For approval
                </td>
              </tr>
              <tr>
                <td width="50%"><input type="checkbox" name="0" value="0" checked="true" readonly="true">For disapproval due to</td>
                <td width="45%" style="border-bottom: 1px solid black"></td>
              </tr>
              <tr>
                <td
                  height="20px"
                  width="95%"
                  colspan="2"
                  style="border-bottom: 1px solid black"
                ></td>
              </tr>
              <tr>
                <td
                  height="20px"
                  width="95%"
                  colspan="2"
                  style="border-bottom: 1px solid black"
                ></td>
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
                  <td width="5%"></td>
                  <td width="90%" style="text-align: center;border-bottom: 1px solid black;">
                  <b>Leonila R. Camomot , Senior Agriculturist</b>
                  </td>
                  <td width="5%"></td>
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
      $html_content .='<table width="100%" style="border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black" cellpadding ="4">
      <tr>
        <td width="50%">
          <table width="100%" cellpadding="3">
              <tr>
                <td ><b>7.C APPROVED FOR:</b></td>
              </tr>
              <tr>
                <td
                  width="20%"
                  style="text-align: center; border-bottom: 1px solid black"
                >
                  <b> 0.0 </b>
                </td>
                <td width="80%">days with pay</td>
              </tr>
              <tr>
                <td
                  width="20%"
                  style="text-align: center; border-bottom: 1px solid black"
                >
                  <b> 1.0 </b>
                </td>
                <td width="80%">days without pay</td>
              </tr>
              <tr>
                <td
                  width="20%"
                  style="text-align: center; border-bottom: 1px solid black"
                ></td>
                <td width="80%">others (specify)</td>
              </tr>
            </table>
        </td>
        <td width="50%">
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
            <b>Kristine Vanessa T. Chiong , City Mayor </b>
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
                ->update(['status' => 'Invalid']);
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function Approved(Request $request){

        try {
            DB::beginTransaction();
            $details = $request->details;
            $status=0;
            foreach ($details as $key => $value) {
              if ($value['approved'] ==='true') {
                $status = $status + 1;
                db::table($this->hr_db.".tbl_leaves_dtl")->where("id",$value['id'])
                ->update(['status'=>'Recommended']);
                // log::debug("approved");
              }else{
                db::table($this->hr_db.".tbl_leaves_dtl")->where("id",$value['id'])
                ->update('status','Declined');
              }
            }
            if ($status > 0 ) {
                $status = "Recommended";
            }else{
                $status = "Declined";
            }
            db::table($this->hr_db.".tbl_leaves")
            ->where("leave_id",$request->leave_id)
            ->update(['recommended_by'=> Auth::user()->Employee_id,'status'=>$status]);

            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }

    }
    public function getForApproval(Request $request)
    {
        try {
            $data = db::table($this->hr_db.".employee_information")
            ->join($this->hr_db.".tbl_leaves",'tbl_leaves.employee_id','employee_information.PPID')
            ->where("tbl_leaves.status",'Active')
            ->where("Head_Dept",Auth::user()->Employee_id)
            ->get();

            $array = array();
            foreach ($data as $key => $value) {
                // $value;
                $dtlx = db::table($this->hr_db.".tbl_leaves_dtl")
                ->join($this->hr_db.".hr_emp_leave_type",'hr_emp_leave_type.leave_number','tbl_leaves_dtl.leave_type_id')
                ->select('*',db::raw(" 'false' as approved "))
                ->where("tbl_leaves_dtl.leave_id",$value->leave_id)
                ->get();
                $datax = array(
                    'leave_id'=>$value->leave_id,
                    'ref_no'=>$value->ref_no,
                    'applied_date'=>$value->applied_date,
                    'NAME'=>$value->NAME,
                    'purpose'=>$value->purpose,
                    'details'=>$dtlx
                    );
                // $datax['dtls'] = $dtlx;
                array_push($array, $datax );
            }
            return response()->json(new jsonresponse($array));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
}

