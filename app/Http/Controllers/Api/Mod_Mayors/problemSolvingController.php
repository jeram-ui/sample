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

class problemSolvingController extends Controller
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

  public function getRef(Request $request)
  {
    // dd($request);
    $pre = 'EXC';
    $table = $this->myr_db . ".project_exception";
    $date = $request->date;
    $refDate = 'trans_date';
    $data = $this->G->generateReference($pre, $table, $date, $refDate);
    return response()->json(new JsonResponse(['data' => $data]));
  }
  public function store(Request $request)
  {
    try {

      $main = $request->form;
      $idx = $main['id'];

      if ($idx == 0) {
        $main['uid'] = Auth::user()->id;
        db::table($this->myr_db . '.project_exception')->insert($main);
      } else {
        $main['upid'] = Auth::user()->id;
        db::table($this->myr_db . '.project_exception')->where('id', $idx)->update($main);
      }
      return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
    } catch (\Exception $err) {
      return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    }
  }
  public function edit($id)
  {
    $data['main'] = DB::table($this->myr_db . '.project_exception')->where('id', $id)->get();
    return response()->json(new JsonResponse($data));
  }
  public function cancel($id)
  {
    DB::table($this->myr_db . '.project_exception')->where('id', $id)->update(['stat' => '1']);
    return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
  }
  public function show(Request $request)
  {
    $list = DB::table($this->myr_db . '.project_exception')
      ->where('stat', 0)
      ->orderBy('id', 'desc')->get();
    return response()->json(new JsonResponse($list));
  }
  public function printform(Request $request)
  {
    $id = $request->id;

    $list = db::table($this->myr_db . '.project_exception')

      ->where('id', $id)
      ->get();
    // $logo = config('variable.logo');
    // $from  = date_format(date_create($request->from), "M d");
    // $to  = date_format(date_create($request->to), "d, Y");
    try {
      $html_content = '';
      // $html_content = '<div style ="width:100%;height:"500px">';
      $html_content .= '<table border=".5" cellpadding="2" height ="100vh">
       <tr>
         <td>
         <table cellpadding="2" >
            <tr>
              <td><b>PMC FORM 1-3</b></td>
            </tr>
            <tr>
              <td><b>PROJECT EXCEMPTION REPORT</b></td>
            </tr>
            <br>
            <tr>
              <td width="1%"></td>
              <td width="10%">Name of Project:</td>
              <td width="59%" style="border-bottom: 1px solid black;" >City of Naga Coastal Development Project</td>
              <td width="7%">Location:</td>
              <td width="23%" style="border-bottom: 1px solid black;">South Poblacion</td>
            </tr>
            <tr>
              <td width="1%"></td>
              <td width="10%">Sector/Subsector</td>
              <td width="44%" style="border-bottom: 1px solid black;" >Infrastructure Development</td>
              <td width="14%">Implementing Agency:</td>
              <td width="31%" style="border-bottom: 1px solid black;">LGU</td>
            </tr>
            <tr>
             <td width="1%"></td>
             <td width="10%">Implement Status</td>
             <td width="20%" ><input type="radio" readonly="true" name="radioquestion" id="a" value="1" /> <label for="a">Ahead</label></td>
             <td width="20%" ><input type="radio" readonly="true" name="radioquestion" id="b" value="1" /> <label for="b">Behind-Schedule</label></td>
             <td width="20%" ><input type="radio" readonly="true" name="radioquestion" id="c" value="1" /> <label for="c">On-Schedule</label></td>
           </tr>
           <tr>
             <td width="100%">
              <table border =".5" cellpadding="1" width = "100%">
               <tr>
                <td>FINDINGS</td>
                <td>POSSIBLE REASON/CAUSE</td>
                <td>RECOMMENDATION</td>
               </tr>
              </table>
             </td>
           </tr>
          </table>
         </td>
       </tr>
      </table>';
      // $html_content .= '</div>';

      PDF::SetTitle('Project Exception Report');
      PDF::SetFont('helvetica', '', 9);
      PDF::SetHeaderMargin(10);
      PDF::SetFooterMargin(10);
      PDF::AddPage('L');
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
}
