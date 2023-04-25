<?php

namespace App\Http\Controllers\Api\Mod_HR\Travel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class travelOrderController extends Controller
{
    private $lgu_db;
    private $hr_db;


    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }

    public function GetPurpose()
    {

        $list = DB::table($this->hr_db . '.ecswd_travel_order_purpose')
            ->where('status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }

        public function getDept()
    {

        $list = DB::table($this->hr_db . '.department')
            ->where('status', 'Active')
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function GetName(Request $request)
    {
        $list = DB::table($this->hr_db . '.employees')
        ->join($this->hr_db .'.department','department.SysPK_Dept','employees.SysPK_Empl')
        ->join($this->hr_db .'.employees_position','employees_position.SysPK_ID','employees.SysPK_Empl')
        ->select("*", 'department.DeptCode_Dept', 'employees_position.Position_Empl', 'employees.Name_Empl', 'employees.SysPK_Empl')
        ->where('employees.Status_Empl', 'Active')
          ->where('SysPK_Empl',Auth::user()->Employee_id)
          ->get();
        return response()->json(new JsonResponse($list));
    }

    // public function GetName(Request $request)
    // {
    //     db::select("call ".$this->hr_db.".getEmpName()");

    // }

    public function getTravel()
    {

        $list = DB::table($this->hr_db . '.ecswd_travel_order')
        ->join($this->hr_db . '.ecswd_travel_order_purpose', 'ecswd_travel_order_purpose.id', 'ecswd_travel_order.purpose')
        ->select("*", 'ecswd_travel_order_purpose.purpose', 'ecswd_travel_order.travel_id')
            ->where('ecswd_travel_order.status', 'Active')
            // ->where('emp_id', Auth::user()->Employee_id)
            ->get();

        return response()->json(new JsonResponse($list));
    }

    public function reference(Request $request)
    {
        // dd($request);
        $pre = 'TO';
        $table = $this->hr_db . ".ecswd_travel_order";
        $date = $request->date;
        $refDate = 'application_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
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
            ->where('tbl_overtime.status', 'Active')
            // ->where('emp_id',Auth::user()->Employee_id)
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        $data['office'] = db::table($this->hr_db . '.ecswd_travel_order')

                //    ->select('tbl_overtime.*', 'employee_information.NAME', 'emp_id as name')
            ->where('ecswd_travel_order.travel_id', $id)
            ->get();

        $data['Empname'] = db::table($this->hr_db . '.ecswd_travel_order_dtail')
        ->join($this->hr_db .'.department','department.SysPK_Dept','ecswd_travel_order_dtail.dept_id')
        ->join($this->hr_db .'.employees','employees.SysPK_Empl','ecswd_travel_order_dtail.empl_id')
        // ->select("*", 'department.DeptCode_Dept', 'employees_position.Position_Empl', 'employees.Name_Empl', 'employees.SysPK_Empl')
            ->where('travel_id', $id)
            ->get();

        // $data['formz'] =db::table($this->hr_db .'.sworn_assets')->where('mainID', $id)->get();


        return response()->json(new JsonResponse($data));
    }


    public function store(Request $request)
    {
        $form = $request->form;
        unset($form['name']);
        $formx = $request->formx;
        $id = $form['travel_id'];
        if ($id > 0) {
            db::table($this->hr_db . ".ecswd_travel_order")
                ->where('travel_id', $id)
                ->update($form);

            db::table($this->hr_db . ".ecswd_travel_order_dtail")
                ->where("travel_id", $id)
                ->delete();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'travel_id' => $id,
                    'dept_id' => $value['dept_id'],
                    // 'Name_Empl' =>$value['employee_name'],
                    'empl_id' => $value['empl_id'],
                    'position' => $value['position'],
                );
                db::table($this->hr_db . ".ecswd_travel_order_dtail")->insert($datx);
            }
        } else {

            db::table($this->hr_db . ".ecswd_travel_order")->insert($form);
            $id = DB::getPdo()->LastInsertId();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'travel_id' => $id,
                    'dept_id' => $value['dept_id'],
                    // 'Name_Empl' =>$value['employee_name'],
                    'empl_id' => $value['empl_id'],
                    'position' => $value['position'],
                );
                db::table($this->hr_db . ".ecswd_travel_order_dtail")->insert($datx);
            }
        }
    }

    public function cancel($id)
    {
        db::table($this->hr_db . '.ecswd_travel_order')

            ->where('travel_id', $id)
            ->update(['status' => 'Deleted']);

        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function print_TO(Request $request){
        try{

            $data = $request->itm;
            $travel = db::table($this->hr_db . '.ecswd_travel_order')
            ->join($this->hr_db .'.ecswd_travel_order_purpose','ecswd_travel_order_purpose.id','ecswd_travel_order.purpose')
            ->select('*',DB::raw('ecswd_travel_order_purpose.purpose as purpose'))
            ->where('ecswd_travel_order.travel_id',$data)
            ->get();
            $travelData = "";

            foreach ($travel as $key => $value) {
            $travelData =$value;
            }

            $details =db::table($this->hr_db . '.ecswd_travel_order_dtail')
            ->join($this->hr_db .'.employees','employees.SysPK_Empl','ecswd_travel_order_dtail.empl_id')
            ->select('*',DB::raw('employees.Name_Empl as name'))
            ->where('ecswd_travel_order_dtail.travel_id',$data)
            ->get();
            $dtlData = "";

            foreach ($details as $key => $value) {
                $dtlData =$value;
                }

            $Template='<table width="100%" cellpadding="3">
            <tr>
            <br />
            <th width="30%" align="right"><img src="' . public_path() . '/img/logo1.png"  height="40" width="40"></th>
            <th width="40%" style="font-size:11pt;  word-spacing:30px" align="center">Republic of the Philippines
            <br />
                    Province of Cebu
            <br />
                   City of Naga</th>
            <th align="left"><img src="' . public_path() . '/img/Logo3.png"  height="45" width="65"></th>
            </tr>
            <tr>
                <th width="100%" style="font-size:11pt;  word-spacing:30px" align="center"><b>OFFICE OF THE CITY MAYOR</b></th>
            </tr>
            <br />
            <br />
            <tr>
                <td style="font-size:12pt" align="center"><b>TRAVEL ORDERsss</b></td>
            </tr>
            <br />
            <br />
            <tr>
                <td width="5%"></td>
                <td width="30%" align="left" style="font-size: 11pt">'. date_format(date_create($travelData->application_date), "F d, Y").'</td>
            </tr>
            <tr>
            <br />
                <td width="5%"></td>
                <td width="10%" align="left" style="font-size: 11pt">To</td>
                <td width="2%" align="left">:</td>
                <td width="75%" align="left" style="font-size: 11pt"><b>* '.$dtlData->name.'</b></td>
                <td width="5%"></td>
            </tr>
            <tr>
                <td width="5%"></td>
                <td width="10%" align="left" style="font-size: 11pt">From</td>
                <td width="2%" align="left">:</td>
                <td width="75%" align="left" style="font-size: 11pt"><b>&nbsp;&nbsp;City Mayor</b></td>
                <td width="5%"></td>
            </tr>
            <br />
            <br />

            <tr>
                <td width="5%"></td>
                <td width="90%" style="text-align:justify" style="font-size: 11pt"><p>You are hereby authorized to represent our government Unit to '.$travelData->place.', on '.date_format(date_create($travelData->inclusive_from), "m/d/Y").' to '.date_format(date_create($travelData->inclusive_to), "m/d/Y").'</p></td>
                <td width="5%"></td>
            </tr>
            <br />
            <tr>
                <td width="5%"></td>
                <td width="10%" align="left" style="font-size: 11pt">Purpose:</td>
                <td width="80%" align="left" style="font-size: 11pt">'.$travelData->purpose.'</td>
                <td width="5%"></td>
            </tr>
            <br />
            <tr>
                <td width="5%"></td>
                <td width="90%" align="left" style="font-size: 11pt">For your information and guidance.</td>
                <td width="5%"></td>
            </tr>
            <br />
            <br />
            <br />
            <tr>
                <td width="65%" align="right"></td>
                <td width="30%" style="font-size: 11pt; border-bottom: 1px solid black; text-align:center"><b>Valdemar Mendiola Chiong</b></td>
                <td width="5%"></td>
            </tr>
            <tr>
                <td width="65%" align="right"></td>
                <td width="30%" style="font-size: 11pt; text-align:center">City Mayor</td>
                <td width="5%"></td>
            </tr>



            </table>
            ';

            PDF::SetTitle('Travel Order');
            PDF::SetFont('helvetica', '', 8);
            PDF::AddPage('P', array(215.9, 279.4 ));
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
