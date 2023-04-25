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

class TravelOrdersController extends Controller
{
    private $lgu_db;
    private $hr_db;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }

    public function show()
    {
        $list = DB::table($this->hr_db . '.tbl_official_business')
            ->leftJoin($this->hr_db . '.tbl_official_business_dtl', 'tbl_official_business_dtl.ob_id', 'tbl_official_business.ob_id')
            ->leftJoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.emp_id')
            ->select('tbl_official_business_dtl.*', 'tbl_official_business.*', 'employee_information.NAME', 'tbl_official_business.ob_id',
                DB::raw($this->hr_db . '.jay_getEmployeeName(tbl_official_business.empName) as empName'),
                db::raw('concat(ob_dest_timedept," - ",ob_dest_timearr) as TIME')
            )
            ->where('tbl_official_business.type', 'Travel Order')
            ->where('emp_id', Auth::user()->Employee_id)
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function Edit($id)
    {
        $data['FormA'] = db::table($this->hr_db . '.tbl_official_business')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.empName')
            ->where('ob_id', $id)
            ->get();

        $data['FormB'] = db::table($this->hr_db . '.tbl_official_business_dtl')
            ->select('*',
                db::raw('concat(ob_dest_timedept," - ",ob_dest_timearr) as TIME')
            )
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
        $form['type'] = "Travel Order";
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
                // 'am_in_note' => $formx['am_in_note'],
                // 'am_out_note' => $formx['am_out_note'],
                // 'pm_in_note' => $formx['pm_in_note'],
                // 'pm_out_note' => $formx['pm_out_note'],
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
                // 'am_in_note' => $formx['am_in_note'],
                // 'am_out_note' => $formx['am_out_note'],
                // 'pm_in_note' => $formx['pm_in_note'],
                // 'pm_out_note' => $formx['pm_out_note'],
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
        // // Page footer
        // public function Footer() {
        //     // Position at 15 mm from bottom
        //     $this->SetY(-15);
        //     // Set font
        //     $this->SetFont('helvetica', 'I', 8);
        //     // Page number
        //     $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        // }

    public function print(Request $request)
    {
        try {
            // $id = $request->id;
            $form = $request->itm;
            $main = DB::table($this->hr_db . '.tbl_official_business')
                ->join($this->hr_db . '.tbl_official_business_dtl', 'tbl_official_business_dtl.ob_id', 'tbl_official_business.ob_id')
                ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.emp_id')
                ->select('tbl_official_business_dtl.*', 'tbl_official_business.*', 'employee_information.NAME', 'tbl_official_business.ob_id',
                    DB::raw($this->hr_db . '.jay_getEmployeeName(tbl_official_business.empName) as empName'),
                    db::raw('concat(ob_dest_timedept," - ",ob_dest_timearr) as TIME')
                )
                ->where('tbl_official_business.ob_id',$form['id'])
                ->get();


            $main = DB::table($this->hr_db . '.tbl_official_business')
            ->join($this->hr_db . '.tbl_official_business_dtl', 'tbl_official_business_dtl.ob_id', 'tbl_official_business.ob_id')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'tbl_official_business.empName')
            ->select('tbl_official_business_dtl.*', 'tbl_official_business.*', 'employee_information.NAME','employee_information.POSITION' ,'tbl_official_business.ob_id',
                db::raw('CONCAT(ob_dest_timedept," - ",ob_dest_timearr) AS timex'), 'ob_dest_timedept', 'ob_dest_timearr',
                // db::raw($this->hr_db . '.jay_getEmployeeName(tbl_official_business.empName)as empName')
            )
            ->where('tbl_official_business.ob_id', $form['ob_id'])
            ->get();

            $mainData = "";
            foreach ($main as $key => $value) {
                $mainData = $value;
            }

            $Template = '<table width="100%">
                    <tr>
                        <td width="100%" align="center">
                        <img src="' . public_path() . '/img/Logo1.png"  height="60" width="60">
                        </td>
                    </tr>
                    <tr>
                        <td width="100%">
                            <table width="100%">
                                <tr>
                                    <td width="34%"></td>
                                    <td width="32%" style="font-size:9pt" align="center">Republic of the Philippines</td>
                                    <td width="34%"></td>
                                </tr>
                                 <tr>
                                    <td width="34%"></td>
                                    <td width="32%"  style="font-size:9pt" align="center">Province of Cebu</td>
                                    <td width="34%"></td>
                                </tr>
                                <tr>
                                    <td width="34%"></td>
                                    <td width="32%"  style="font-size:9pt" align="center">'.env("cityname",false).'</td>
                                    <td width="34%"></td>
                                </tr>
                                <tr>
                                    <td width="34%"></td>
                                    <td width="32%"  style="font-size:9pt" align="center">OFFICE OF THE CITY MAYOR</td>
                                    <td width="34%"></td>
                                </tr>
                                <tr>
                                    <td width="100%"></td>
                                </tr>
                                <tr>
                                    <td width="100%"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

            </table>';

            $Template .=' <table width="100%">
                    <tr>
                        <td width="100%" style="font-size:15pt;" align="center"><b>TRAVEL ORDER</b></td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <table width="100%">
                        <tr>
                            <td width="10%"></td>
                            <td width="12%" style="font-size:9pt">TO: </td>
                            <td width="68%" style="font-size:9pt"><b>'.$mainData->NAME.'</b></td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td width="10%"></td>
                            <td width="12%"></td>
                            <td width="68%" style="font-size:9pt">'.$mainData->POSITION.'</td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="10%"></td>
                            <td width="12%" style="font-size:9pt">From:</td>
                            <td width="68%" style="font-size:9pt">'.$mainData->trvl_from.'</td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td width="10%"></td>
                            <td width="80%" style="border-bottom:1px solid black"></td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="10%"></td>
                            <td width="80%" style="text-align: justify; font-size:9pt">'.ucfirst($mainData->ob_remarks).' </td>
                            <td width="10%"></td>
                        </tr>



                        <tr>
                            <td width="100%"></td>
                        </tr>
                       
                        <tr>
                            <td width="100%" height="100px"></td>
                        </tr>
                        <tr>
                           <td width="34%">
                                <table width="100%">
                                    <tr>
                                        <td></td>
                                    </tr>
                                </table>
                            </td>
                       
                            </tr>

                    </table>
            </table>';

            PDF::SetTitle('Travel Order');
            PDF::SetFont('helvetica', 7);
            PDF::AddPage('P');
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
