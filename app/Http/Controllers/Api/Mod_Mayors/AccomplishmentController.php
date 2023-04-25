<?php

namespace App\Http\Controllers\Api\Mod_Mayors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use Storage;
use File;
use PDF;

class AccomplishmentController extends Controller
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
    $this->myr_db = $this->G->getMayorsDb();
  }

  public function getAssistedName(Request $request)
  {

    $from = $request->from;
    $to = $request->to;
    $list = db::table($this->myr_db . '.assistance_main')
      ->whereBetween('trans_date', [$from, $to])
      ->select(db::raw('DISTINCT(`uid`) AS "uid",`assisted_by` AS "name"'))
      ->get();
    return response()->json(new JsonResponse($list));
  }
  public function purpose()
  {
    $list = db::table($this->myr_db . '.assistance_main')
      ->whereBetween('trans_date', [$from, $to])
      ->select(db::raw('DISTINCT(`uid`) AS "uid",`assisted_by` AS "name"'))
      ->get();
    return response()->json(new JsonResponse($list));
  }
  public function SurveyList(Request $request)
  {

    $from = $request->from;
    $to = $request->to;
    $uid = $request->uid;
    $list = db::select('call ' . $this->myr_db . '.rans_get_assistance_survey(?,?,?)', [$from, $to, $uid]);
    return response()->json(new JsonResponse($list));
  }
  public function getGenderCount(Request $request)
  {
    $filter = $request->filter;
    $item = $request->item;
    $from = $filter['from'];
    $to = $filter['to'];
    $uid = '%%';
    $brgy_id = $item['brgy_id'];
    log::debug([$from, $to, $uid, $brgy_id]);
    $list = db::select('call ' . $this->myr_db . '.rans_get_assistance_survey_details(?,?,?,?)', [$from, $to, $uid, $brgy_id]);
    return response()->json(new JsonResponse($list));
  }
  public function ref(Request $request)
  {
    // dd($request);
    $pre = 'ASST';
    $table = $this->myr_db . ".assistance_main";
    $date = $request->date;
    $refDate = 'trans_date';
    $data = $this->G->generateReference($pre, $table, $date, $refDate);
    return response()->json(new JsonResponse(['data' => $data]));
  }

  public function getAccom()
  {
    $list = db::table($this->myr_db . '.accomplishment_setup')
      ->where('stat', 0)
      ->where('emp_id', Auth::user()->Employee_id)
      ->get();
    return response()->json(new JsonResponse($list));
  }
  public function show(Request $request)
  {
    $list = db::table($this->myr_db . '.accomplishment')
      ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'accomplishment.dept_id')
      ->select('accomplishment.*', 'department.Name_Dept')
      ->where('stat', 0)
      ->where('emp_id', $request->emp_id)
      ->whereBetween('trans_date', [$request->from, $request->to])
      ->orderBy('trans_date', 'asc')
      ->get();
    return response()->json(new JsonResponse($list));
  }

  public function print(Request $request)
  {
    $heads = $request->heads;
    $list = db::table($this->myr_db . '.accomplishment')
      ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'accomplishment.dept_id')
      ->select('accomplishment.*', 'department.Name_Dept')
      ->where('stat', 0)
      ->where('emp_id', $request->emp_id)
      ->whereBetween('trans_date', [$request->from, $request->to])
      ->orderBy('trans_date', 'asc')
      ->get();

    $dtls = db::table($this->myr_db . '.accomplishment')
      ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'accomplishment.dept_id')
      ->select('accomplishment.*', 'department.Name_Dept')
      ->where('stat', 0)
      ->where('emp_id', $request->emp_id)
      ->whereBetween('trans_date', [$request->from, $request->to])
      ->orderBy('trans_date', 'asc')
      ->first();

    $details = db::table($this->hr_db . '.employee_information')->where('PPID', $request->emp_id)->first();
    $logo = config('variable.logo');
    $from  = date_format(date_create($request->from), "M d");
    $to  = date_format(date_create($request->to), "d, Y");
    try {

      $html_content =    $logo . '<h2 align="center">Accomplishment Report</h2> <br>';
      $html_content .= '<table width ="100%">
       <tr>
       <td width="10%">Name</td>
       <td width="2%">:</td>
       <td width="88%"><b>' . $details->NAME . '</b></td>
       </tr>

       <tr>
       <td width="10%">Period</td>
       <td width="2%">:</td>
       <td width="88%">' .  ($from) . '-' .   $to . '</td>
       </tr>

       <tr>
       <td width="10%">Status</td>
       <td width="2%">:</td>
       <td width="88%">' . $dtls->emp_status . '</td>
       </tr>

       <tr>
       <td width="10%">Designation</td>
       <td width="2%">:</td>
       <td width="88%">' . $dtls->Name_Dept . '</td>
       </tr>

      </table> <br><hr><br></br>';
      $html_content .= '<table style ="width:100%"   cellpadding="2" >
      ';
      foreach ($list as $value) {
        $date = date_create($value->trans_date);
        $html_content .= ' <tr >
        <td width="12%" style="text-align:left" >' . date_format($date, "M d, Y")  . '</td>
        <td width="2%" style="text-align:center" >:</td>
        <td width="86%" style="text-align:left"><p>' . $value->accomplish . '</p></td>
        </tr>';
      }
      $html_content .= '</table><br></br><br></br><br></br>';
      $html_content .= '<table width ="100%" style ="text-align:center">
         <tr>
           <td width="70%">
           </td>
           <td width="30%" style="border-top: 1px solid black;" >
           Department Head
          </td>
         </tr>
         <tr>
         <td width="70%">
         </td>
         <td width="30%">
         ' . $dtls->head . '
        </td>
       </tr>
      </table>';
      PDF::SetTitle('Accomplishment Report');
      PDF::SetFont('helvetica', '', 9);
      PDF::SetHeaderMargin(10);
      PDF::SetFooterMargin(10);
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

  public function store(Request $request)
  {
    try {

      $main = $request->form;
      $idx = $main['id'];

      if ($idx == 0) {
        $main['uid'] = Auth::user()->id;
        db::table($this->myr_db . '.accomplishment')->insert($main);
      } else {
        db::table($this->myr_db . '.accomplishment')->where('id', $idx)->update($main);
      }
      $chk = db::table($this->myr_db . '.accomplishment_setup')
        ->where('emp_id', $main['emp_id'])
        ->where('accom', $main['accomplish'])->count();
      if ($chk == 0) {
        $accomp = array(
          'emp_id' => $main['emp_id'],
          'accom' => $main['accomplish']
        );
        db::table($this->myr_db . '.accomplishment_setup')->insert($accomp);
      }
      return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
    } catch (\Exception $err) {
      return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    }
  }
  public function edit($id)
  {
    $data['main'] = DB::table($this->myr_db . '.assistance_main')->where('id', $id)->get();
    return response()->json(new JsonResponse($data));
  }
  public function cancel($id)
  {
    DB::table($this->myr_db . '.accomplishment')->where('id', $id)->update(['stat' => '1']);
    return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
  }
  public function upload(Request $request)
  {
    $files = $request->file('file');
    if (!empty($files)) {
      $path = hash('sha256', time());
      for ($i = 0; $i < count($files); $i++) {
        $file = $files[$i];
        $filename = $file->getClientOriginalName();
        if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
          $data = array(
            'trans_id' => $request->id,
            'file_name' => $filename,
            'trans_type' => 'legalOpinion',
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'uid' => Auth::user()->id,
          );
          db::table('docs_upload')->insert($data);
        }
      }
    }
    return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
  }

  public function  uploaded($id)
  {
    $data = db::table('docs_upload')
      ->where('trans_id', $id)
      ->where('trans_type', 'legalOpinion')
      ->where('stat', "ACTIVE")
      ->get();
    return response()->json(new JsonResponse($data));
  }
  public function documentView($id)
  {
    $main = DB::table('docs_upload')->where('id', $id)->get();
    foreach ($main as $key => $value) {
      $file = $value->file_name;
      $path = '../storage/files/document/' . $value->file_path . '/' . $file;
      if (\File::exists($path)) {
        $file = \File::get($path);
        $type = \File::mimeType($path);
        $response = \Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
      }
    }
  }
  public function uploadRemove($id)
  {
    $data = db::table('docs_upload')->where('id', $id)
      ->update(['stat' => "CANCELLED"]);
    return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
  }
}
