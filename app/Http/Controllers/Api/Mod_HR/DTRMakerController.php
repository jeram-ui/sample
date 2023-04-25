<?php

namespace App\Http\Controllers\Api\Mod_HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Str;
use Storage;

class DTRMakerController extends Controller
{
  private $lgu_db;
  private $hr_db;
  private $trk_db;
  private $empid;
  protected $G;
  private $general;
  private $Proc;
  private $Budget;
  public function __construct(GlobalController $global)
  {
    $this->G = $global;
    $this->lgu_db = $this->G->getLGUDb();
    $this->hr_db = $this->G->getHRDb();
    $this->trk_db = $this->G->getTrkDb();
    $this->general = $this->G->getGeneralDb();
    $this->Proc = $this->G->getProcDb();
    $this->Budget = $this->G->getBudgetDb();
  }
  public function getDTRList(Request $request){
    $emp_id = $request->empID;
    $from = $request->from;
    $to = $request->to;
    db::select("call ".$this->hr_db.".jay_generate_dtr_printing_new1(?,?,?,?,?,?)",[$emp_id,$from,$to,"","","1"]);
    $list = db::select("call ".$this->hr_db.".jay_display_tbl_dtr_print_irregular_new1(?,?,?,?,?,?)",[$emp_id,$from,$to,"","","1"]);

  //  $list= db::select("call ".$this->hr_db.".balodoy_display_tbl_dtr_print_jo4(?,?,?)",[$emp_id,$from,$to]);
   return response()->json(new JsonResponse($list));
  }
//   public function getDTRList($id){
//     $emp_id = $request->empID;
//     $from = $request->from;
//     $to = $request->to;
//     db::select("call ".$this->hr_db.".jay_generate_dtr_printing_new1(?,?,?,?,?,?)",[$emp_id,$from,$to,"","","1"]);
//     // ->where('emp_id', $id),
//     $list = db::select("call ".$this->hr_db.".jay_display_tbl_dtr_print_irregular_new1(?,?,?,?,?,?)",[$emp_id,$from,$to,"","","1"]);

//   //  $list= db::select("call ".$this->hr_db.".balodoy_display_tbl_dtr_print_jo4(?,?,?)",[$emp_id,$from,$to]);
//    return response()->json(new JsonResponse($list));
//   }
  public function storeSlip(Request $request)
  {
    $form = $request->form;
    $id = $form['id'];
    $form['emp_id']=Auth::user()->Employee_id;


    // $schedx = "";
    // if ($form['first_in'] ) {
    //   $schedx = ($form['first_in']);
    //   $schedFrom=date_create($form['firstin_time']);
    //   // $schedTo=date_create($form['first_in']);
    //   $schedx = date_format($schedFrom,'Y/m/d');
    //   // log::debug($schedx = implode(",", $form['first_in']));
    // }else{

    // }
    // $list = DB::table($this->hr_db . '.tbl_emp_passlip')
    // ->leftjoin($this->hr_db .'.employees_timeshift','employees_timeshift.shiftcode','tbl_emp_passlip.shift_id')
    // ->where('emp_id',Auth::user()->Employee_id)
    // ->where('tbl_emp_passlip.stat', 'Active')
    // ->get();
    if ( $id >0 ) {
      $data = array(
        'emp_id' =>Auth::user()->Employee_id,
        'date'=>$form['first_in'],
        'shift_id'=>$form['shift_id'],
        'first_in'=>$form['first_in'].' '.$form['firstin_time'],
        'first_out'=>$form['first_out'].' '.$form['firstout_time'],
        'firstin_notes'=>$form['firstin_notes'],
        'first_notes'=>$form['first_notes'],
        'second_in'=>$form['second_in'].' '.$form['secondin_time'],
        'second_out'=>$form['second_out'].' '.$form['secondout_time'],
        'secondin_notes'=>$form['secondin_notes'],
        'second_notes'=>$form['second_notes'],

      );
      DB::table($this->hr_db . '.tbl_emp_passlip')
      ->where("id",$id)
      ->update($data);
    }else{

      $data = array(
        'emp_id'=>Auth::user()->Employee_id,
        'date'=>$form['first_in'],
        'shift_id'=>$form['shift_id'],
        'first_in'=>$form['first_in'].' '.$form['firstin_time'],
        'first_out'=>$form['first_out'].' '.$form['firstout_time'],
        'firstin_notes'=>$form['firstin_notes'],
        'first_notes'=>$form['first_notes'],
        'second_in'=>$form['second_in'].' '.$form['secondin_time'],
        'second_out'=>$form['second_out'].' '.$form['secondout_time'],
        'secondin_notes'=>$form['secondin_notes'],
        'second_notes'=>$form['second_notes'],


      );
      // $form['emp_id']=Auth::user()->Employee_id;
      DB::table($this->hr_db . '.tbl_emp_passlip')
      ->insert($data);
    }
    return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
  }
  // public function getPslip(Request $request)
  // {
  //   $list = DB::table($this->hr_db . '.tbl_emp_passlip')
  //     ->leftjoin($this->hr_db .'.employees_timeshift','employees_timeshift.shiftcode','tbl_emp_passlip.shift_id')
  //     ->where('emp_id',Auth::user()->Employee_id)
  //     ->where('tbl_emp_passlip.stat', 'Active')
  //     ->get();
  //   return response()->json(new JsonResponse($list));
  // }
  public function showShifts(Request $request)
  {
    $list = DB::table($this->hr_db . '.employees_timeshift')
      // ->where('shiftcode',Auth::user()->Employee_id)
      ->select("*", 'shiftcode','description')
      ->where('employees_timeshift.status', 0)
      ->get();
    return response()->json(new JsonResponse($list));
  }

  public function showData(Request $request)
  {
    $data = DB::table($this->hr_db . '.tbl_dtr_jo')
      ->where('emp_id',Auth::user()->Employee_id)
      ->where('date',$request->date)
      // ->where('emp_id', '250')
      ->get();

      // foreach ($data['main'] as $key => $value) {
      //   log::debug($value->id);
      //   $first = array(
      //     'first_in'=>explode(".' '.",$value->first_in)
      //   );
      // }
      // $data['first_in']= $first;
    return response()->json(new JsonResponse($data));
  }

  public function showList(Request $request)
  {
    $data = DB::table($this->hr_db . '.tbl_emp_passlip')
      ->where('emp_id',Auth::user()->Employee_id)
      ->where('date',$request->date)
      // ->where('emp_id', '250')
      ->get();

      // foreach ($data['main'] as $key => $value) {
      //   log::debug($value->id);
      //   $first = array(
      //     'first_in'=>explode(".' '.",$value->first_in)
      //   );
      // }
      // $data['first_in']= $first;
    return response()->json(new JsonResponse($data));
  }


  public function getDTRListPrint(Request $request){
    $emp_id = $request->empID;
    $from = $request->from;
    $to = $request->to;
    log::debug($emp_id);
    db::select("call ".$this->hr_db.".jay_generate_dtr_printing_new1(?,?,?,?,?,?)",[$emp_id,$from,$to,"","","1"]);
    $list = db::select("call ".$this->hr_db.".jay_display_tbl_dtr_print_irregular_new1(?,?,?,?,?,?)",[$emp_id,$from,$to,"","","1"]);
   return response()->json(new JsonResponse($list));
  }
  public function getLogs($date){
    $emp_id = Auth::user()->Employee_id;
    $list = db::select("call ".$this->hr_db.".rans_display_client_log_per_date(?,?)",[$emp_id,$date]);
   return response()->json(new JsonResponse($list));
  }

  public function printDtr(Request $request){
    try {

      $data = $request->data;
      $date = $request->par;
      // PDF::AddPage('P');
      PDF::AddPage('P', array(215.9,330.2));
      PDF::SetTitle('DTR');
      // PDF::SetHeaderMargin(1);
      PDF::SetTopMargin(1);
      // PDF::SetMargins(1, 1, 1, 1);
      PDF::SetFont('Helvetica', '', 8);
      // -- set new background ---
      $bMargin = PDF::getBreakMargin();
      $auto_page_break = PDF::getAutoPageBreak();
      PDF::SetAutoPageBreak(false, 0);
      PDF::SetAutoPageBreak($auto_page_break, $bMargin);

      PDF::setPageMark();
      PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
      $Template = '<table width="100%" cellpadding="2">
             <tr>
                <th width="49%">'.$this->printingDtr($data,$date).'</th>
                <th width="2%"></th>
                <th width="49%">'.$this->printingDtr($data,$date).'</th>
             </tr>
        </table>';
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
//   public function printingDtr($data,$date){
//     $from = date_create($date['from']);
//     $to = date_create($date['to']);
//     $datetime = $this->G->serverdatetime();
//     $datetime = date_create($datetime);
//     $covered =  date_format($from,"F j-").date_format($to,"j Y") ;
//     $emp_id = $request->empID;
//     $datax = db::table($this->hr_db.".employee_information")->where('PPID',$emp_id)->first();
//     db::select("call ".$this->hr_db.".jay_generate_dtr_printing(?,?,?)",[$emp_id,$from,$to]);
//     $days = db::select("call ".$this->hr_db.".balodoy_display_tbl_dtr_print_irregular5_late_undertime(?,?,?)",[$emp_id,$from,$to]);

//     $late ="";
//     $undertime ="";
//     $equivalent ="";
//     foreach ($days as $key => $value) {
//       $late = $value->Late;
//       $undertime = $value->Undertime;
//       $equivalent = $value->equivalent;
//     }
//     $html ='
//     <div>&nbsp;&nbsp;CIVIL SERVICE FORM No. 48</div>
//     <br>
//     <table  cellpadding="3" width="100%" >
//       <tr>
//         <th  height="30px;" width = "100%" align="center" style="border-top:solid black 5px;border-left:solid black 5px;border-right:solid black 5px;"><b>Daily Time Record</b></th>
//       </tr>

//      <tr>
//        <th style="border-left:solid black 5px;border-right:solid black 5px;" width = "100%">EMPLOYEE NAME: <b>'.$datax->NAME.'</b></th>
//      </tr>
//      <tr>
//        <th style="border-left:solid black 5px;border-right:solid black 5px;" width = "100%">PERIOD COVERED: <b>'.$covered.'</b></th>
//       </tr>
//     </table>
//     <table border=".3" cellpadding="1.5" width="100%">

//             <tr style="text-align:center;">
//              <th width = "12%">Day</th>
//              <th width = "15%">AM IN</th>
//              <th width = "15%">AM OUT</th>
//              <th width = "15%">PM IN</th>
//              <th width = "15%">PM OUT</th>
//              <th width = "14%">LATE</th>
//              <th width = "14%">UT</th>
//             </tr>
//             <tbody>';
//               foreach ($data as $key => $value) {

//                 if ($value['prop_type'] ==='1') {
//                       $html .='
//                       <tr >
//                        <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                        <td width = "60%" style="text-align:center;background-color:lightblue;" >'.$value['prop_notes'].'</td>
//                        <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                        <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                       </tr>';
//                     }
//                   elseif ($value['prop_type'] === '2') {
//                         $html .='
//                         <tr >
//                          <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                          <td width = "60%" style="text-align:center;background-color:lightpink;" >'.$value['prop_notes'].'</td>
//                          <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                          <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                         </tr>';
//                       }
//                 elseif  ($value['prop_type'] ==='2.1') {
//                         $html .='
//                              <tr style="text-align:center;">
//                         <td width = "12%">'.$value['Day'].'</td>
//                         <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['First In'].'</td>
//                         <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['First Out'].'</td>
//                         <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['Second In'].'</td>
//                         <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['Second Out'].'</td>
//                         <td width = "14%">'.$value['Late'].'</td>
//                         <td width = "14%">'.$value['Undertime'].'</td>
//                        </tr>'; }

//               elseif  ($value['prop_type'] ==='3') {
//                 $html .='
//                 <tr >
//                  <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                  <td width = "60%" style="text-align:center;background-color:lightgray;" >'.$value['prop_notes'].'</td>
//                  <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                  <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                 </tr>';
//               }
//                   elseif  ($value['prop_type'] ==='3.1') {
//                     $html .='
//                          <tr style="text-align:center;">
//                     <td width = "12%">'.$value['Day'].'</td>
//                     <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['First In'].'</td>
//                     <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['First Out'].'</td>
//                     <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['Second In'].'</td>
//                     <td width = "15%" style="text-align:center;background-color:lightgray;" >'.$value['Second Out'].'</td>
//                     <td width = "14%">'.$value['Late'].'</td>
//                     <td width = "14%">'.$value['Undertime'].'</td>
//                    </tr>'; }
//                   elseif ($value['prop_type'] ==='3.2') {
//                             $html .='
//                             <tr >
//                              <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                              <td width = "60%" style="text-align:center;background-color:lightgray;" >'.$value['prop_notes'].'</td>
//                              <td width = "14%" style="text-align:center;"> '.$value['Late'].'</td>
//                              <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                             </tr>';
//                   }
//                   elseif ($value['prop_type'] ==='3') {
//                             $html .='
//                             <tr >
//                              <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                              <td width = "60%" style="text-align:center;background-color:lightgray;" >'.$value['prop_notes'].'</td>
//                              <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                              <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                             </tr>';
//                   }
//                   else if ($value['prop_type'] ==='4') {
//                               $html .='
//                               <tr >
//                                <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                                <td width = "60%" style="text-align:center;background-color:lightblue;" >'.$value['prop_notes'].'</td>
//                                <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                                <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                               </tr>';
//                   }
//                   elseif ($value['prop_type'] ==='4.1') {
//                           $html .='
//                       <tr style="text-align:center;">
//                       <td width = "12%">'.$value['Day'].'</td>
//                       <td width = "15%" style="text-align:center;background-color:lightblue;" >'.$value['First In'].'</td>
//                       <td width = "15%" style="text-align:center;background-color:lightblue;" >'.$value['First Out'].'</td>
//                       <td width = "15%" style="text-align:center;background-color:lightblue;" >'.$value['Second In'].'</td>
//                       <td width = "15%" style="text-align:center;background-color:lightblue;" >'.$value['Second Out'].'</td>
//                       <td width = "14%">'.$value['Late'].'</td>
//                       <td width = "14%">'.$value['Undertime'].'</td>
//                      </tr>'; }
//                   elseif ($value['prop_type'] ==='4.2') {
//                     $html .='
//                     <tr >
//                      <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                      <td width = "60%" style="text-align:center;background-color:lightblue;" >'.$value['prop_notes'].'</td>
//                      <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                      <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                     </tr>';    }
//                     elseif ($value['prop_type'] ==='6') {
//                       $html .='
//                       <tr >
//                        <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                        <td width = "30%" style="text-align:center;background-color:lightblue;" >'.$value['prop_notes'].'</td>
//                        <td width = "15%" style="text-align:center;">'.$value['First In'].'</td>
//                        <td width = "15%" style="text-align:center;">'.$value['First Out'].'</td>
//                        <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                        <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                       </tr>';
//                     }
//                     elseif ($value['prop_type'] ==='6.1') {
//                       $html .='
//                       <tr >
//                       <td style="text-align:center;background-color:lightblue;" width = "12%">'.$value['Day'].'</td>
//                       <td style="text-align:center;background-color:lightblue;" width = "15%">'.$value['First In'].'</td>
//                       <td style="text-align:center;background-color:lightblue;" width = "15%">'.$value['First Out'].'</td>
//                       <td style="text-align:center;background-color:lightblue;" width = "15%">'.$value['Second In'].'</td>
//                       <td style="text-align:center;background-color:lightblue;" width = "15%">'.$value['Second Out'].'</td>
//                       <td style="text-align:center;background-color:lightblue;" width = "14%">'.$value['Late'].'</td>
//                       <td style="text-align:center;background-color:lightblue;" width = "14%">'.$value['Undertime'].'</td>
//                       </tr>';
//                     }
//                     elseif ($value['prop_type'] ==='6.2') {
//                       $html .='
//                       <tr >
//                       <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                       <td width = "30%" style="text-align:center;background-color:lightblue;" >'.$value['prop_notes'].'</td>
//                       <td width = "15%" style="text-align:center;">'.$value['Second In'].'</td>
//                       <td width = "15%" style="text-align:center;">'.$value['Second Out'].'</td>
//                       <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                       <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                       </tr>';
//                     }
//                     elseif ($value['prop_type'] ==='7') {
//                       $html .='
//                       <tr style="text-align:center;">
//                       <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>

//                       <td width = "15%" style="text-align:center;">'.$value['First In'].'</td>
//                       <td width = "15%" style="text-align:center;">'.$value['First Out'].'</td>
//                       <td width = "30%" style="text-align:center;background-color:lightblue;" >'.$value['prop_notes'].'</td>
//                       <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                       <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                       </tr>';
//                     }
//                     elseif ($value['prop_type'] ==='7.1') {
//                       $html .='
//                       <tr style="text-align:center;">
//                       <td style="text-align:center;" width = "12%">'.$value['Day'].'</td>
//                       <td style="text-align:center;" width = "15%">'.$value['First In'].'</td>
//                       <td style="text-align:center;" width = "15%">'.$value['First Out'].'</td>
//                       <td style="background-color:lightblue;text-align:center;" width = "15%">'.$value['Second In'].'</td>
//                       <td style="background-color:lightblue;text-align:center;" width = "15%">'.$value['Second Out'].'</td>
//                       <td style="text-align:center;" width = "14%">'.$value['Late'].'</td>
//                       <td style="text-align:center;" width = "14%">'.$value['Undertime'].'</td>
//                       </tr>';
//                     }
//                     elseif ($value['prop_type'] ==='7.2') {
//                       $html .='
//                       <tr style="text-align:center;">
//                       <td width = "12%"  style="text-align:center;">'.$value['Day'].'</td>
//                       <td width = "15%"  style="text-align:center;">'.$value['First In'].'</td>
//                       <td width = "15%"  style="text-align:center;"  >'.$value['First Out'].'</td>
//                       <td width = "30%" style="text-align:center;background-color:lightblue;" >'.$value['prop_notes'].'</td>
//                       <td width = "14%" style="text-align:center;">'.$value['Late'].'</td>
//                       <td width = "14%" style="text-align:center;">'.$value['Undertime'].'</td>
//                       </tr>';
//                     }
//                   else{
//                        $html .='
//                       <tr style="text-align:center;">
//                         <td width = "12%">'.$value['Day'].'</td>
//                         <td width = "15%">'.$value['First In'].'</td>
//                         <td width = "15%">'.$value['First Out'].'</td>
//                         <td width = "15%">'.$value['Second In'].'</td>
//                         <td width = "15%">'.$value['Second Out'].'</td>
//                         <td width = "14%">'.$value['Late'].'</td>
//                         <td width = "14%">'.$value['Undertime'].'</td>
//                        </tr>';
//                    }
//               }
//               $html .='<tr>
//               <td  width = "100%">
//                   <table style="font-size:10px;" width = "100%" style="text-align:center;">
//                      <br/>
//                      <br/>
//                     <tr>
//                       <td style="text-align:left;" width = "70%">Total Late: <b>'.$late.'</b></td>
//                       <td style="text-align:center;" width = "30%">EQUIVALENT</td>
//                     </tr>
//                     <tr>
//                     <td style="text-align:left;" width = "70%">Total UT:&nbsp;&nbsp;  <b>'.$undertime.'</b></td>
//                     <td style="text-align:center;" width = "30%"><b>'.$equivalent.'</b></td>
//                     <br/>
//                   </tr>
//                   <br/>
//                   <br/>
//                   <tr>
//                          <td width = "100%">
//                          I CERTIFY on my honor that the above is a true and correct report of the hours of work performed, record of
//                          which was made daily at the time of arrival and departure from office.
//                          </td>
//                     </tr>
//                 <br/>
//                 <br/>
//                 <tr>
//                   <td >_________________________________</td>
//                 </tr>

//                 <tr>
//                 <td>Verified as to the prescribed office hours. </td>
//               </tr>
//               <br/>
//               <tr>
//               <td >_________________________________</td>
//             </tr>
//             <tr>
//                <td>In Charge</td>
//              </tr>
//              <tr>
//                 <td>'. date_format($datetime,"l jS \of F Y h:i:s A").'</td>
//            </tr>
//              <br/>
//              <br/>
//               </table>
//              </td>
//               </tr>
//               ';

//       $html .='
//             </tbody>
//            </table>';
//            return $html;
//   }
// }
public function printingDtr($data, $date)
  {
    $from = date_create($date['from']);
    $to = date_create($date['to']);
    $datetime = $this->G->serverdatetime();
    $datetime = date_create($datetime);
    $covered =  date_format($from, "F j-") . date_format($to, "j Y");
    $emp_id = $date['empID'];
    $datax = db::table($this->hr_db . ".employee_information")->where('PPID', $emp_id)->first();
    db::select("call " . $this->hr_db . ".jay_generate_dtr_printing(?,?,?)", [$emp_id, $from, $to]);
    log::debug([$emp_id, $from, $to]);
    $days = db::select("call " . $this->hr_db . ".balodoy_display_tbl_dtr_print_irregular5_late_undertime(?,?,?)", [$emp_id, date_format($from, 'Y-m-d'),  date_format($to, 'Y-m-d'),]);
    // log::debug($days);
    $late = "";
    $undertime = "";
    $equivalent = "";
    $equivalentUndertime=0.00;
    $equivalentLate=0.00;
    $totalOvertimeEqui = 0.00;
    $totalOvertime = 0.00;

    foreach ($data as $key => $value) {
      $totalOvertimeEqui += $value['overtime'];
      $totalOvertime+= $value['app_hrs'];

      $late = $value['Late Equi'];
      $undertime= $value['Undertime Equi'];
      
      $equivalentUndertime = $value['U Equivalent'];
      $equivalentLate = $value['L Equivalent'];
    };
    
    $html = '
    <div>&nbsp;&nbsp;CIVIL SERVICE FORM No. 48</div>
    <br>
    <table  cellpadding="3" width="100%" >
      <tr>
        <th  height="30px;" width = "100%" align="center" style="border-top:solid black 5px;border-left:solid black 5px;border-right:solid black 5px;"><b>Daily Time Record</b></th>
      </tr>

     <tr>
       <th style="border-left:solid black 5px;border-right:solid black 5px;" width = "100%">EMPLOYEE NAME: <b>' . $datax->NAME . '</b></th>
     </tr>
     <tr>
       <th style="border-left:solid black 5px;border-right:solid black 5px;" width = "100%">PERIOD COVERED: <b>' . $covered . '</b></th>
      </tr>
    </table>
    <table border=".3" cellpadding="1.5" width="100%">

            <tr style="text-align:center;">
             <th width = "12%">Day</th>
             <th width = "15%">AM IN</th>
             <th width = "15%">AM OUT</th>
             <th width = "15%">PM IN</th>
             <th width = "15%">PM OUT</th>
             <th width = "14%">LATE</th>
             <th width = "14%">UT</th>
            </tr>
            <tbody>';
    foreach ($data as $key => $value) {

      if ($value['prop_type'] === '1') {
        $html .= '
                      <tr >
                       <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                       <td width = "60%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
                       <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                       <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                      </tr>';
      }elseif ($value['prop_type'] === '1.1') {
        $html .= '
        <tr style="text-align:center;">
        <td width = "12%"  style="text-align:center;">' . $value['Day'] . '</td>
        <td width = "30%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
        <td width = "15%"  style="text-align:center;">' . $value['Second In'] . '</td>
        <td width = "15%"  style="text-align:center;"  >' . $value['Second Out'] . '</td>
        <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
        <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
        </tr>';
      
      } 
      elseif ($value['prop_type'] === '1.2') {
        $html .= '
         <tr style="text-align:center;">
         <td width = "12%"  style="text-align:center;">' . $value['Day'] . '</td>
         <td width = "15%"  style="text-align:center;">' . $value['First In'] . '</td>
         <td width = "15%"  style="text-align:center;"  >' . $value['First Out'] . '</td>
         <td width = "30%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
         <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
         <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
         </tr>';
      
      }
      elseif ($value['prop_type'] === '2') {
        $html .= '
                        <tr >
                         <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                         <td width = "60%" style="text-align:center;background-color:lightpink;" >' . $value['prop_notes'] . '</td>
                         <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                         <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                        </tr>';
      } elseif ($value['prop_type'] === '2.1') {
        $html .= '
                             <tr style="text-align:center;">
                        <td width = "12%">' . $value['Day'] . '</td>
                        <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['First In'] . '</td>
                        <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['First Out'] . '</td>
                        <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['Second In'] . '</td>
                        <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['Second Out'] . '</td>
                        <td width = "14%">' . $value['Late'] . '</td>
                        <td width = "14%">' . $value['Undertime'] . '</td>
                       </tr>';
      } elseif ($value['prop_type'] === '3') {
        $html .= '
                <tr >
                 <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                 <td width = "60%" style="text-align:center;background-color:lightgray;" >' . $value['prop_notes'] . '</td>
                 <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                 <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                </tr>';
      } elseif ($value['prop_type'] === '3.1') {
        $html .= '
                         <tr style="text-align:center;">
                    <td width = "12%">' . $value['Day'] . '</td>
                    <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['First In'] . '</td>
                    <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['First Out'] . '</td>
                    <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['Second In'] . '</td>
                    <td width = "15%" style="text-align:center;background-color:lightgray;" >' . $value['Second Out'] . '</td>
                    <td width = "14%">' . $value['Late'] . '</td>
                    <td width = "14%">' . $value['Undertime'] . '</td>
                   </tr>';
      } elseif ($value['prop_type'] === '3.2') {
        $html .= '
                            <tr >
                             <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                             <td width = "60%" style="text-align:center;background-color:lightgray;" >' . $value['prop_notes'] . '</td>
                             <td width = "14%" style="text-align:center;"> ' . $value['Late'] . '</td>
                             <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                            </tr>';
      } elseif ($value['prop_type'] === '3') {
        $html .= '
                            <tr >
                             <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                             <td width = "60%" style="text-align:center;background-color:lightgray;" >' . $value['prop_notes'] . '</td>
                             <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                             <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                            </tr>';
      } else if ($value['prop_type'] === '4') {
        $html .= '
                              <tr >
                               <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                               <td width = "60%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
                               <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                               <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                              </tr>';
      } elseif ($value['prop_type'] === '4.1') {
        $html .= '
                      <tr style="text-align:center;">
                      <td width = "12%">' . $value['Day'] . '</td>
                      <td width = "15%" style="text-align:center;background-color:lightblue;" >' . $value['First In'] . '</td>
                      <td width = "15%" style="text-align:center;background-color:lightblue;" >' . $value['First Out'] . '</td>
                      <td width = "15%" style="text-align:center;background-color:lightblue;" >' . $value['Second In'] . '</td>
                      <td width = "15%" style="text-align:center;background-color:lightblue;" >' . $value['Second Out'] . '</td>
                      <td width = "14%">' . $value['Late'] . '</td>
                      <td width = "14%">' . $value['Undertime'] . '</td>
                     </tr>';
      } elseif ($value['prop_type'] === '4.2') {
        $html .= '
                    <tr >
                     <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                     <td width = "60%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
                     <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                     <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                    </tr>';
      } elseif ($value['prop_type'] === '6') {
        $html .= '
                      <tr >
                       <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                       <td width = "30%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
                       <td width = "15%" style="text-align:center;">' . $value['First In'] . '</td>
                       <td width = "15%" style="text-align:center;">' . $value['First Out'] . '</td>
                       <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                       <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                      </tr>';
      } elseif ($value['prop_type'] === '6.1') {
        $html .= '
                      <tr >
                      <td style="text-align:center;background-color:lightblue;" width = "12%">' . $value['Day'] . '</td>
                      <td style="text-align:center;background-color:lightblue;" width = "15%">' . $value['First In'] . '</td>
                      <td style="text-align:center;background-color:lightblue;" width = "15%">' . $value['First Out'] . '</td>
                      <td style="text-align:center;background-color:lightblue;" width = "15%">' . $value['Second In'] . '</td>
                      <td style="text-align:center;background-color:lightblue;" width = "15%">' . $value['Second Out'] . '</td>
                      <td style="text-align:center;background-color:lightblue;" width = "14%">' . $value['Late'] . '</td>
                      <td style="text-align:center;background-color:lightblue;" width = "14%">' . $value['Undertime'] . '</td>
                      </tr>';
      } elseif ($value['prop_type'] === '6.2') {
        $html .= '
                      <tr >
                      <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                      <td width = "30%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
                      <td width = "15%" style="text-align:center;">' . $value['Second In'] . '</td>
                      <td width = "15%" style="text-align:center;">' . $value['Second Out'] . '</td>
                      <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                      <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                      </tr>';
      } elseif ($value['prop_type'] === '7') {
        $html .= '
                      <tr style="text-align:center;">
                      <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>

                      <td width = "15%" style="text-align:center;">' . $value['First In'] . '</td>
                      <td width = "15%" style="text-align:center;">' . $value['First Out'] . '</td>
                      <td width = "30%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
                      <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                      <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                      </tr>';
      } elseif ($value['prop_type'] === '7.1') {
        $html .= '
                      <tr style="text-align:center;">
                      <td style="text-align:center;" width = "12%">' . $value['Day'] . '</td>
                      <td style="text-align:center;" width = "15%">' . $value['First In'] . '</td>
                      <td style="text-align:center;" width = "15%">' . $value['First Out'] . '</td>
                      <td style="background-color:lightblue;text-align:center;" width = "15%">' . $value['Second In'] . '</td>
                      <td style="background-color:lightblue;text-align:center;" width = "15%">' . $value['Second Out'] . '</td>
                      <td style="text-align:center;" width = "14%">' . $value['Late'] . '</td>
                      <td style="text-align:center;" width = "14%">' . $value['Undertime'] . '</td>
                      </tr>';
      } elseif ($value['prop_type'] === '7.2') {
        $html .= '
                      <tr style="text-align:center;">
                      <td width = "12%"  style="text-align:center;">' . $value['Day'] . '</td>
                      <td width = "15%"  style="text-align:center;">' . $value['First In'] . '</td>
                      <td width = "15%"  style="text-align:center;"  >' . $value['First Out'] . '</td>
                      <td width = "30%" style="text-align:center;background-color:lightblue;" >' . $value['prop_notes'] . '</td>
                      <td width = "14%" style="text-align:center;">' . $value['Late'] . '</td>
                      <td width = "14%" style="text-align:center;">' . $value['Undertime'] . '</td>
                      </tr>';
      } else {
        $html .= '
                      <tr style="text-align:center;">
                        <td width = "12%">' . $value['Day'] . '</td>
                        <td width = "15%">' . $value['First In'] . '</td>
                        <td width = "15%">' . $value['First Out'] . '</td>
                        <td width = "15%">' . $value['Second In'] . '</td>
                        <td width = "15%">' . $value['Second Out'] . '</td>
                        <td width = "14%">' . $value['Late'] . '</td>
                        <td width = "14%">' . $value['Undertime'] . '</td>
                       </tr>';
      }
    }
    $html .= '<tr>
              <td  width = "100%">
                  <table style="font-size:10px;" width = "100%" style="text-align:center;">
                     <br/>
                     <br/>
                     <tr>
                     <td style="text-align:left;" width = "70%"></td>
                     <td style="text-align:center;" width = "30%">Equivalent</td>
                   </tr>
                    <tr>
                      <td style="text-align:left;" width = "70%">Total Late: <b>' . $late . '</b></td>
                      <td style="text-align:center;" width = "30%">' . $equivalentLate . '</td>
                    </tr>
                    <tr>
                    <td style="text-align:left;" width = "70%">Total UT:&nbsp;&nbsp;  <b>' . $undertime . '</b></td>
                    <td style="text-align:center;" width = "30%"><b>' . $equivalentUndertime . '</b></td>
                    <br/>
                    <tr>
                    <td style="text-align:left;" width = "70%">Total Overtime:&nbsp;&nbsp;  <b>' . $totalOvertime . '</b></td>
                    <td style="text-align:center;" width = "30%"><b>' . $totalOvertimeEqui . '</b></td>
                    <br/>
                  </tr>
                  <br/>
                  <tr>
                    <td width = "100%">
                         I CERTIFY on my honor that the above is a true and correct report of the hours of work performed, record of
                         which was made daily at the time of arrival and departure from office.
                         </td>
                    </tr>
                <br/>
                <tr>
                  <td >_________________________________</td>
                </tr>

                <tr>
                <td>Verified as to the prescribed office hours. </td>
              </tr>
              <br/>
              <tr>
              <td >_________________________________</td>
            </tr>
            <tr>
               <td>In Charge</td>
             </tr>
             <tr>
                <td>' . date_format($datetime, "l jS \of F Y h:i:s A") . '</td>
           </tr>
             <br/>
             <br/>
              </table>
             </td>
              </tr>
              ';

    $html .= '
            </tbody>
           </table>';
    return $html;
  }
}
