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

class certOfAppearanceController extends Controller
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
    public function store(Request $request)
    {
        $form = $request->form;
        $id = $form['id'];
        $form['employeeID']=Auth::user()->Employee_id;
        if ( $id >0 ) {
          DB::table($this->hr_db . '.hr_cert_of_appearance')
          ->where("id",$id)
          ->update($form);
        }else{
          $form['employeeID']=Auth::user()->Employee_id;
          DB::table($this->hr_db . '.hr_cert_of_appearance')
          ->insert($form);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
      }
    // {
    //     try {
    //         $main = $request->form;
    //         // $leave = $request->leave;
    //         // log::debug($main);
    //         // log::debug($leave);
    //         $idx = $main['id'];
    //         // DB::beginTransaction();
    //         $main['employeeID']= Auth::user()->employeeID;
    //         if ($idx == 0) {
    //         db::table($this->hr_db .'.hr_cert_of_appearance')->insert($main);
    //         $id = DB::getPdo()->lastInsertId();
    //         log::debug($id);

    //         } else {
    //             $main['employeeID']= Auth::user()->employeeID;
    //           db::table($this->hr_db .'.hr_cert_of_appearance')->where('id', $idx)->update($main);
    //         }
    //         // DB::commit();
    //         return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
    //     } catch (\Exception $err) {
    //         // DB::rollback();
    //         return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
    //     }
    // }
    public function certList(Request $request)
    {
      $list = DB::table($this->hr_db .'.hr_cert_of_appearance')
      // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime.emp_id')
      ->select("*",db::raw("TIME_FORMAT(`timex`,'%h:%i %p') AS 'time'"))
        ->where('hr_cert_of_appearance.status', '0')
        // ->where('emp_id',Auth::user()->Employee_id)
        ->get();
    // $list="";
      return response()->json(new JsonResponse($list));
    }
    public function Edit($id)
    {
        // $data['office'] =db::table($this->hr_db .'.tbl_overtime_cert')->where('id', $id)->get();
        $data =db::table($this->hr_db .'.hr_cert_of_appearance')
        // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
        ->where('id', $id)
        ->get();

        // $data['formz'] =db::table($this->hr_db .'.sworn_assets')->where('mainID', $id)->get();


        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {
        try {
            $data = db::table($this->hr_db . '.hr_cert_of_appearance')
                ->where('id', $id)
                ->update(['status' => 1]);
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function print(Request $request){
        try{
            $form = $request->itm;
            $cert = db::table($this->hr_db .'.hr_cert_of_appearance')
            // ->join($this->hr_db .'.employees','employees.SysPK_Empl','hr_cert_of_appearance.employeeID')
            ->leftjoin($this->hr_db .'.employee_information','employee_information.PPID','hr_cert_of_appearance.employeeID')
            ->select("*",db::raw("TIME_FORMAT(`timex`,'%h:%i %p') AS 'time'")
            ,'employee_information.NAME','employee_information.POSITION', 'hr_cert_of_appearance.id' )
            ->where('hr_cert_of_appearance.id', $form['id'] )
            ->get();
            $certData ="";

            foreach ($cert as $key => $value) {
                // log::debug($value->Fname);
                $certData= $value;
            }

$Template='
            <table width="50%" style="border-bottom:1px solid black; border-top:1px solid black;
            border-right:1px solid black; border-left:1px solid black">
            <tr>
            <br />
            <th width="35%" align="right"><img src="' . public_path() . '/img/logo1.png"  height="30" width="30"></th>
            <th width="35%" style="font-size:9pt;  word-spacing:30px" align="center"><b>Republic of the Philippines</b>
            <br />
                    Province of Cebu
            <br />
                   City of Naga
            <br />
            </th>
            <th align="left"><img src="/img/NAGA LOGO2.png"  height="40" width="45"></th>
            </tr>
                <br />
            <tr>
                <th width="100%" style="font-size:10pt;  word-spacing:30px" align="center" ><b>CERTIFICATE OF APPEARANCE</b></th>
            </tr>
            <br />
            <tr>
                <td  width="60%" style="font-size:9pt" align="left">TO WHOM IT MAY CONCERN:</td>
            </tr>
                <br />
                <tr>
                    <td width="15%" style="font-size:9pt" align="center"></td>
                    <td width="82%" style="font-size:9pt; text-align: justify" align="center">This is to certify that I attended to Mr./Ms.</td>
                </tr>
                <tr>
                    <td width="3%" style="font-size:9pt" align="center"></td>
                    <td width="80%" style="font-size:9pt; border-bottom: 1px solid black" align="center">'.$certData->fullname.'</td>
                    <td width="15%" style="font-size:9pt; text-align: justify" align="center">of the</td>
                </tr>

                <tr>
                    <td width="3%"></td>
                    <td width="40%" style="font-size:9pt; border-bottom: 1px solid black" align="center">'.$certData->event.'</td>
                    <td width="6%" style="font-size:9pt" align="center">on</td>
                    <td width="30%" style="font-size:9pt; border-bottom: 1px solid black" align="center">'.$certData->datex.'</td>
                    <td width="5%" style="font-size:9pt" align="center">at</td>
                </tr>

                <tr>
                    <td width="3%" style="font-size:9pt" align="center"></td>
                    <td width="30%" style="font-size:9pt; border-bottom: 1px solid black" align="center">'.$certData->time.'</td>
                    <td width="65%" style="font-size:9pt; text-align: justify" align="left">a.m./p.m. When he/she transacted</td>
                </tr>
                <tr>
                    <td width="3%" style="font-size:9pt" align="center"></td>
                    <td width="65%" style="font-size:9pt; text-align: justify" align="left">business with my Agency/Companyx.</td>
                </tr>
                <br />
                <tr>
                    <td width="3%" style="font-size:9pt" align="center"></td>
                    <td width="50%" style="font-size:9pt; border-bottom: 1px solid black" align="center">'.$certData->NAME.'</td>
                    <td width="5%" style="font-size:9pt" align="center"></td>
                    <td width="40%" style="font-size:9pt; border-bottom: 1px solid black" align="center">'.$certData->POSITION.'</td>
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
                    <td width="3%" style="font-size:9pt" align="center"></td>
                    <td width="40%" style="font-size:9pt" align="center"></td>
                    <td width="15%" style="font-size:9pt" align="center">Date:</td>
                    <td width="40%" style="font-size:9pt; border-bottom: 1px solid black" align="center">'.$certData->datex.'</td>
                </tr>
                <br />
                <tr>
                    <td width="3%" style="font-size:9pt" align="left"></td>
                    <td width="30%" style="font-size:9pt" align="left">Agency/Company</td>
                    <td width="64%" style="font-size:9pt; border-bottom: 1px solid black" align="left">'.$certData->companyx.'</td>
                </tr>

                <tr>
                <td width="3%" style="font-size:9pt" align="left"></td>
                <td width="16%" style="font-size:9pt" align="left">Address</td>
                <td width="78%" style="font-size:9pt; border-bottom: 1px solid black" align="left">'.$certData->addressx.'</td>
            </tr>
                <tr>
                    <td width="3%" style="font-size:9pt" align="left"></td>
                    <td width="16%" style="font-size:9pt" align="left">Tel. No.</td>
                    <td width="79%" style="font-size:9pt; border-bottom: 1px solid black" align="left">'.$certData->tell_no.'</td>
                </tr>
                <br />
                <tr>
                    <td width="3%" style="font-size:9pt" align="left"></td>
                    <td width="95%" style="font-size:8pt" align="left">For verification purposes, additional documents may be required.</td>

                </tr>
            </table>


            ';
            PDF::SetTitle('Certificate of Appearance');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('P');
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

}

