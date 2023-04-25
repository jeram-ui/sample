<?php

namespace App\Http\Controllers\Api\Mod_IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class repairController extends Controller
{
    private $lgu_db;
    private $hr_db;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }

    public function getEmpRequest()
    {

        $list = DB::table($this->hr_db . '.employee_information')
            // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
            // ->where('DEPID', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getRepairSetup()
    {

        $list = DB::table($this->hr_db . '.repairform_setup')
            ->where('status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getrecommend()
    {

        $list = DB::table($this->trk_db . '.recommend_setup')
            ->select("*",
                db::raw("CONCAT(recommend_abrv,' - ', recommend_desc) as recommendation"))
            ->where('status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function show()
    {
        $list = DB::table($this->trk_db . '.it_repairreq')
            ->join($this->hr_db . '.repairform_setup', 'repairform_setup.id', 'it_repairreq.category')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_repairreq.req_by')
            ->select("*", 'it_repairreq.id')
            ->where('it_repairreq.status', 0)
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function reference(Request $request)
    {
        // dd($request);
        $pre = 'PS';
        $table = $this->trk_db . ".it_repairReq";
        $date = $request->date;
        $refDate = 'req_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function Edit($id)
    {
        $data['form'] = db::table($this->trk_db . '.it_repairreq')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_repairreq.req_by')
        ->join($this->hr_db . '.repairform_setup', 'repairform_setup.id', 'it_repairreq.category')
            ->select("*", 'it_repairreq.id')
            ->where('it_repairreq.id', $id)
            ->get();

        $data['formA'] = db::table($this->trk_db . '.it_repairReq_inspect')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_repairReq_inspect.inspect_by')
            ->where('main_id', $id)
            ->get();

        $data['formx'] = db::table($this->trk_db . '.it_repairReq_bill')
            ->where('main_id', $id)
            ->get();



        return response()->json(new JsonResponse($data));
    }

    public function store(Request $request)
    {
        $form = $request->form;
        $id = $form['id'];
        // $form['emp_id'] = Auth::user()->Employee_id;
        if ($id > 0) {
            DB::table($this->trk_db . '.it_repairreq')
                ->where("id", $id)
                ->update($form);
        } else {
            // $form['emp_id'] = Auth::user()->Employee_id;
            DB::table($this->trk_db . '.it_repairreq')
                ->insert($form);
            $id = DB::getPdo()->lastInsertId();

        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function inspectStore(Request $request)
    {

        $idx = $request->idx;
        $formA = $request->formA;
        $id = $idx;

        if ($id > 0) {

            db::table($this->trk_db . ".it_repairReq_inspect")
                ->where("main_id", $id)
                ->delete();


                    $datx = array(
                        'main_id' => $id,
                        'inspect_by' => $formA['inspect_by'],
                        'requested_date' => $formA['requested_date'],
                        'date_conduct' => $formA['date_conduct'],
                        'recomend' => $formA['recomend'],
                        'recommend_others' => $formA['recommend_others'],
                        'statement'=> $formA['statement'],
                    );
                    db::table($this->trk_db . ".it_repairReq_inspect")->insert($datx);

        } else {

            $datx = array(
                'main_id' => $id,
                'inspect_by' => $formA['inspect_by'],
                'requested_date' => $formA['requested_date'],
                'date_conduct' => $formA['date_conduct'],
                'recommend_others' => $formA['recommend_others'],
                'recomend' => $formA['recomend'],
                'statement'=> $formA['statement'],
            );
            db::table($this->trk_db . ".it_repairReq_inspect")->insert($datx);
        }
    }
    public function billStore(Request $request)
    {

        $idx = $request->idx;
        $formx = $request->formx;
        $id = $idx;

        if ($id > 0) {

            db::table($this->trk_db . ".it_repairReq_bill")
                ->where("main_id", $id)
                ->delete();

                foreach ($formx as $key => $value) {
                    $datx = array(
                        'main_id' => $id,
                        'item_desc' => $value['item_desc'],
                        'quantity' => $value['quantity'],
                        'unit_measure' => $value['unit_measure'],
                        'avail_inhouse' => $value['avail_inhouse'],
                        'unit_Cost'=> $value['unit_Cost'],
                        'total_cost'=> $value['total_cost'],
                    );
                    db::table($this->trk_db . ".it_repairReq_bill")->insert($datx);
                }

        } else {

            foreach ($formx as $key => $value) {
                $datx = array(
                    'main_id' => $id,
                    'item_desc' => $value['item_desc'],
                    'quantity' => $value['quantity'],
                    'unit_measure' => $value['unit_measure'],
                    'avail_inhouse' => $value['avail_inhouse'],
                    'unit_Cost'=> $value['unit_Cost'],
                    'total_cost'=> $value['total_cost'],
                );
                db::table($this->trk_db . ".it_repairReq_bill")->insert($datx);
            }
        }
    }


    public function cancel($id)
    {
        db::table($this->hr_db . '.tbl_official_business')
            ->where('ob_id', $id)
            ->update(['status' => 'Cancelled']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function print(Request $request)
    {
        try {

            $form = $request->itm;

            $infoData = db::table($this->trk_db . '.it_repairreq')
            ->leftjoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_repairreq.req_by')
                ->where('id', $form['id'])
                ->get();
            $info = "";

            foreach ($infoData as $key => $value) {
                $info = $value;
            }

            $inspectorData = db::table($this->trk_db . '.it_repairreq_inspect')
            ->leftjoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_repairreq_inspect.inspect_by')
                ->where('main_id', $form['id'])
                ->select("*",
                        db::raw('UPPER(SUBSTRING(requested_date, 6, 1)) AS requested_date_m1'),
                        db::raw('UPPER(SUBSTRING(requested_date, 7, 1)) AS requested_date_m2'),
                        db::raw('UPPER(SUBSTRING(requested_date, 9, 1)) AS requested_date_d1'),
                        db::raw('UPPER(SUBSTRING(requested_date, 10, 1)) AS requested_date_d2'),
                        db::raw('UPPER(SUBSTRING(requested_date, 1, 1)) AS requested_date_y1'),
                        db::raw('UPPER(SUBSTRING(requested_date, 2, 1)) AS requested_date_y2'),
                        db::raw('UPPER(SUBSTRING(requested_date, 3, 1)) AS requested_date_y3'),
                        db::raw('UPPER(SUBSTRING(requested_date, 4, 1)) AS requested_date_y4'),

                        db::raw('UPPER(SUBSTRING(date_conduct, 6, 1)) AS date_conduct_m1'),
                        db::raw('UPPER(SUBSTRING(date_conduct, 7, 1)) AS date_conduct_m2'),
                        db::raw('UPPER(SUBSTRING(date_conduct, 9, 1)) AS date_conduct_d1'),
                        db::raw('UPPER(SUBSTRING(date_conduct, 10, 1)) AS date_conduct_d2'),
                        db::raw('UPPER(SUBSTRING(date_conduct, 1, 1)) AS date_conduct_y1'),
                        db::raw('UPPER(SUBSTRING(date_conduct, 2, 1)) AS date_conduct_y2'),
                        db::raw('UPPER(SUBSTRING(date_conduct, 3, 1)) AS date_conduct_y3'),
                        db::raw('UPPER(SUBSTRING(date_conduct, 4, 1)) AS date_conduct_y4'),

            )
            ->get();
            $inspect = "";

            foreach ($inspectorData as $key => $value) {
                $inspect = $value;
                $c= "";
                $a= "";
                $b= "";
                $d= "";
                $e= "";
                $f= "";
                $g= "";


                if ($value->recomend === "1"){
                    $c = "X";
                }elseif($value->recomend === "2"){
                    $a= "X";
                }elseif($value->recomend === "3"){
                    $b= "X";
                }elseif($value->recomend === "4"){
                    $d= "X";
                }elseif($value->recomend === "5"){
                    $e= "X";
                }elseif($value->recomend === "6"){
                    $f= "X";
                }elseif($value->recomend === "7"){
                    $g= "X";
                }else{
                    $c= "";
                    $a= "";
                    $b= "";
                    $d= "";
                    $e= "";
                    $f= "";
                    $g= "";
                }
            }

            $billData = db::table($this->trk_db . '.it_repairreq_bill')
                ->where('main_id', $form['id'])
                ->get();
            $bill = "";
            $x = 1;
            $z = "";
            foreach ($billData as $key => $value) {

                $z = $x++;

                    $bill .= '<tr>
                    <td width="2%"></td>
                    <td width="5%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center">'.$z.'</td>
                    <td width="30%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center">'.$value->item_desc.'</td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center">'.$value->quantity.'</td>
                    <td width="15%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center">'.$value->unit_measure.'</td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center">'.$value->avail_inhouse.'</td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; font-size:8pt" align="center">'.$value->unit_Cost.'</td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; font-size:8pt" align="center">'.$value->total_cost.'</td>
                    <td width="2%"></td>
                </tr>';
            }
            if(count($billData)< 3){
                for($i = count($billData); $i<3; $i++){
                    $bill .='<tr>
                    <td width="2%"></td>
                    <td width="5%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"></td>
                    <td width="30%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"></td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"></td>
                    <td width="15%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"></td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"></td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; font-size:8pt" align="center"></td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; font-size:8pt" align="center"></td>
                    <td width="2%"></td>
                </tr>' ;
                }
            }


            $Template = '<table cellpadding="1">
            <tr>
                <th width="32%" align="right">
                <img src="' . public_path() . '/img/logo1.png"  height="30" width="30">
                </th>
                <th width="38%" style="font-size:9pt;  word-spacing:30px" align="center">
                        Republic of the Philippines
                <br />
                ' . env('cityname', false) . '
                <br />

                ' . env('cityaddress', false) . '
                <br />

                    </th>

                <th align="left">
                <img src="' . public_path() . '/img/logo2.png"  height="30" width="45">
                </th>
             </tr>
             <tr>
                <th width="100%" style="font-size:14pt" align="center"><b>REQUEST FOR INSPECTION AND REAPAIR/MAINTENANCE FORM</b>
            </th>
             </tr>
             <tr>
                <td width="90%" align="right">Application No.:</td>
                <td width="10%"></td>
            </tr>
            </table>
            <br />
            <table width="100%" style="border:1px solid black" cellpadding="2">
                <tr>
                    <td width="100%"><b>I. REQUESTOR</b></td>
                </tr>
            <br />
                <tr>
                    <td width="2%"></td>
                    <td width="20%">REQUESTED BY:</td>
                    <td width="26%" style="border-bottom:1px solid black" align="center">'.$info->NAME.'</td>
                    <td width="2%"></td>
                    <td width="22%" style="border-bottom:1px solid black" align="center">'.$info->req_date.'</td>
                    <td width="2%"></td>
                    <td width="22%" style="border-bottom:1px solid black" align="center">'.$info->department.'</td>
                    <td width="2%"></td>
                </tr>
                <tr>
                    <td width="22%"></td>
                    <td width="26%" style="font-size:8pt">SIGNATURE OVER PRINTED NAME</td>
                    <td width="2%"></td>
                    <td width="22%" style="font-size:8pt" align="center">Date Requested</td>
                    <td width="2%"></td>
                    <td width="22%" style="font-size:8pt" align="center">Department</td>
                    <td width="2%"></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="27%" style="font-size:10pt"><b>REQUESTED DESCRIPTION</b></td>
                    <td width="67%" style="border-bottom:1px solid black">'.$info->req_desc.'</td>
                    <td width="2%"></td>
                </tr>
                <tr>
                    <td width="100%"><b>CATEGORY/</b></td>
                </tr>
                <tr>
                    <td width="10%"><b>CONCERN:</b></td>
                    <td width="15%" align="right">AIRCON</td>
                    <td width="28%"><input type="checkbox" checked="' . ($info->category === '1' ? "true" : "false") . '" name="1" value="1"> &nbsp; LEONIDO P. TAPIC</td>
                    <td width="20%" align="right">CARPENTRY</td>
                    <td width="27%"><input type="checkbox" checked="' . ($info->category === '7' ? "true" : "false") . '" name="1" value="1"> &nbsp; RICKY A. GALEOS</td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="15%" align="right">IT</td>
                    <td width="28%"><input type="checkbox" checked="' . ($info->category === '2' ? "true" : "false") . '" name="1" value="1"> &nbsp; ROMEO B. EMPLEO JR.</td>
                    <td width="20%" align="right">LANDSCAPING</td>
                    <td width="27%"><input type="checkbox" checked="' . ($info->category === '8' ? "true" : "false") . '" name="1" value="1"> &nbsp; ALEXANDER LARA</td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="15%" align="right">VEHICLES</td>
                    <td width="28%"><input type="checkbox" checked="' . ($info->category === '3' ? "true" : "false") . '" name="1" value="1"> &nbsp; RAYMUNDO V. MANTALABA</td>
                    <td width="20%" align="right">PLAZA MAINTENANCE</td>
                    <td width="27%"><input type="checkbox" checked="' . ($info->category === '9' ? "true" : "false") . '" name="1" value="1"> &nbsp; KERWIN L. UY</td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="15%" align="right">OTHERS</td>
                    <td width="28%"><input type="checkbox" checked="' . ($info->category === '4' ? "true" : "false") . '" name="1" value="1"> &nbsp; ALEXANDER D. MOZO</td>
                    <td width="20%" align="right">BUILDING</td>
                    <td width="27%"><input type="checkbox" checked="' . ($info->category === '10' ? "true" : "false") . '" name="1" value="1"> &nbsp; Engr.  Ma. Alpha P. Alojado</td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="15%" align="right">ELECTRICAL</td>
                    <td width="28%"><input type="checkbox" checked="' . ($info->category === '5' ? "true" : "false") . '" name="1" value="1"> &nbsp; PEDRITO S. LAURENTE JR.</td>
                    <td width="20%" align="right">BRGY RELATED</td>
                    <td width="27%"><input type="checkbox" checked="' . ($info->category === '11' ? "true" : "false") . '" name="1" value="1"> &nbsp; Hon. REY MANABAT</td>
                </tr>
                <tr>
                    <td width="10%"></td>
                    <td width="15%" align="right">PLUMBING</td>
                    <td width="28%"><input type="checkbox" checked="' . ($info->category === '6' ? "true" : "false") . '" name="1" value="1"> &nbsp; HENRY M. CUIZON</td>
                    <td width="20%" align="right"></td>
                    <td width="27%"></td>
                </tr>
                <tr>
                    <td width="100%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="100%"><b>II. INSPECTOR</b></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="25%">Date Receipt of Request:</td>
                    <td width="7%" style="border-bottom:1px solid black; border-top:1px solid black;
                                    border-right:1px solid black; border-left:1px solid black;" align="center">'.$inspect->requested_date_m1.''.$inspect->requested_date_m2.'</td>
                    <td width="7%" style="border-bottom:1px solid black; border-top:1px solid black;
                                    border-right:1px solid black; border-left:1px solid black;" align="center">'.$inspect->requested_date_d1.''.$inspect->requested_date_d2.'</td>
                    <td width="7%" style="border-bottom:1px solid black; border-top:1px solid black;
                                    border-right:1px solid black; border-left:1px solid black;" align="center">'.$inspect->requested_date_y1.''.$inspect->requested_date_y2.''.$inspect->requested_date_y3.''.$inspect->requested_date_y4.'</td>
                    <td width="25%" align="center">Conducted on:</td>
                    <td width="7%" style="border-bottom:1px solid black; border-top:1px solid black;
                                    border-right:1px solid black; border-left:1px solid black;" align="center">'.$inspect->date_conduct_m1.''.$inspect->date_conduct_m2.'</td>
                    <td width="7%" style="border-bottom:1px solid black; border-top:1px solid black;
                                    border-right:1px solid black; border-left:1px solid black;" align="center">'.$inspect->date_conduct_d1.''.$inspect->date_conduct_d2.'</td>
                    <td width="7%" style="border-bottom:1px solid black; border-top:1px solid black;
                                    border-right:1px solid black; border-left:1px solid black;" align="center">'.$inspect->date_conduct_y1.''.$inspect->date_conduct_y2.''.$inspect->date_conduct_y3.''.$inspect->date_conduct_y4.'</td>
                </tr>
                <tr>
                    <td width="27%"></td>
                    <td width="21%" align="center"><i>(MM/DD/YYYY)</i></td>
                    <td width="25%"></td>
                    <td width="21%" align="center"><i>(MM/DD/YYYY)</i></td>
                </tr>

                <tr>
                    <td width="2%"></td>
                    <td width="20%">Statement Findings:</td>
                    <td width="76%" style="border-bottom:1px solid black">'.$inspect->statement.'</td>
                    <td width="2%"></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="96%" style="border-bottom:1px solid black"></td>
                    <td width="2%"></td>
                </tr>

                <tr>
                    <td height="30px" width="68%"></td>
                    <td width="30%" style="border-bottom:1px solid black">'.$inspect->NAME.'</td>
                    <td width="2%"></td>
                </tr>
                <tr>
                    <td width="68%"></td>
                    <td width="30%" align="center">SIGNATURE OVER PRINTED NAME</td>
                    <td width="2%"></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="98%"><b>RECOMMENDATION (pls marked "x")</b></td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="10%" style="border-bottom:1px solid black" align="center">'.$c.'</td>
                    <td width="5%">a</td>
                    <td width="80%">in-house repair without materials/supplies requirement</td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="10%" style="border-bottom:1px solid black" align="center">'.$a.'</td>
                    <td width="5%">b*</td>
                    <td width="80%">In-house repair with materials / supplies / parts requirement</td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="10%" style="border-bottom:1px solid black" align="center">'.$b.'</td>
                    <td width="5%">b-1</td>
                    <td width="80%">In-house repair with in-house materials / supplies requirement</td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="10%" style="border-bottom:1px solid black" align="center">'.$d.'</td>
                    <td width="5%">c*</td>
                    <td width="80%">Unserviceable beyond repair  / waste material</td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="10%" style="border-bottom:1px solid black" align="center">'.$e.'</td>
                    <td width="5%">d</td>
                    <td width="80%">For unit replacement (please secure separately Request for Capital Outlay Form)</td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="10%" style="border-bottom:1px solid black" align="center">'.$f.'</td>
                    <td width="5%">e</td>
                    <td width="80%">For repair (by private entity)</td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="10%" style="border-bottom:1px solid black" align="center">'.$g.'</td>
                    <td width="5%">f</td>
                    <td width="15%">others pls specifiy:</td>
                    <td width="65%" style="border-bottom:1px solid black">'.$inspect->recommend_others.'</td>
                </tr>
                <tr>
                    <td width="5%"></td>
                    <td width="95%" style="font-size:7pt"><i>*(for items b & c, Waste Material Report shall be prepared separately)</i></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="70%"></td>
                    <td width="26%"style="border-top:1px solid black; border-right:1px solid black;
                                            border-left:1px solid black" align="center"><b>not available in-house</b></td>
                    <td width="2%"></td>

                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="70%" style="border-bottom:1px solid black"><b>III *BILL OF QUANTITIES (for items t and d)</b></td>
                    <td width="26%"style="border-bottom:1px solid black; border-right:1px solid black;
                                            border-left:1px solid black" align="center"><b>(SUBJECT for procurement)</b></td>
                    <td width="2%"></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="5%" style="border-left:1px solid black; border-right:1px solid black;" align="center"><b>ITEM</b></td>
                    <td width="30%" style="border-left:1px solid black; border-right:1px solid black;" align="center"><b>ITEM DESCRIPTION</b></td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black;" align="center"><b>QUANTITY</b></td>
                    <td width="15%" style="border-left:1px solid black; border-right:1px solid black;" align="center"><b>UNIT OF</b></td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black;" align="center"><b>Available</b></td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black;" align="center"><b>UNIT COST</b></td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black;" align="center"><b>Total COST</b></td>
                    <td width="2%"></td>
                </tr>

                <tr>
                    <td width="2%"></td>
                    <td width="5%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"><b>No.</b></td>
                    <td width="30%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"><b></b></td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"><b></b></td>
                    <td width="15%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"><b>MEASUREMENT</b></td>
                    <td width="10%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black" align="center"><b>In-house</b></td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; font-size:8pt" align="center">(TO BE FILLED IN BY GSO)</td>
                    <td width="13%" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; font-size:8pt" align="center">(TO BE FILLED IN BY GSO)</td>
                    <td width="2%"></td>
                </tr>
                '.$bill.'
                <tr>
                    <td width="37%" style="font-size:7pt; border-bottom:1px solid black" align="center"><i>*use separate sheet if necessary</i></td>
                    <td width="63%" style="border-bottom:1px solid black"></td>
                </tr>

                <tr>
                    <td width="23%" style="border-right:1px solid black" align="center">Received of Request for</td>
                    <td width="27%" style="border-bottom:1px solid black; border-right:1px solid black"></td>
                    <td width="23%" style="border-right:1px solid black" align="center">Date Return to End user</td>
                    <td width="27%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="23%" style="border-right:1px solid black" align="center">GSO Costing:</td>
                    <td width="27%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">GSO REPRESENTATIVE</td>
                    <td width="23%" style="border-right:1px solid black" align="center">for POW</td>
                    <td width="27%" style="border-bottom:1px solid black" align="center">REQUESTOR/END USER</td>
                </tr>
                <tr>
                    <td width="23%" style="border-right:1px solid black; border-bottom:1px solid black"></td>
                    <td width="27%" style="border-bottom:1px solid black; border-right:1px solid black">DATE</td>
                    <td width="23%" style="border-right:1px solid black; border-bottom:1px solid black"></td>
                    <td width="27%" style="border-bottom:1px solid black">DATE</td>
                </tr>
                <tr>
                    <td width="50%"><b>IV. REPAIR SCHEDULE</b></td>
                    <td width="50%"><b>IV. APPROVAL</b></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="20%" style="border-bottom:1px solid black"></td>
                    <td width="5%"></td>
                    <td width="73%">VERIFIED & RECOMMENDED FOR APPROVAL</td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="98%" style="font-size:8pt">(DATE * TIME)</td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="15%">APPROVED BY</td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                    <td width="5%"></td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td width="2%"></td>
                    <td width="15%"></td>
                    <td width="30%" align="center"><b>ENGR. ARTHUR S. VILLAMOR</b></td>
                    <td width="5%"></td>
                    <td width="30%" align="center">DEPARTMENT HEAD/UNIT HEAD</td>
                </tr>
                <tr>
                    <td width="2%" style="border-bottom:1px dashed black;"></td>
                    <td width="15%" style="border-bottom:1px dashed black"></td>
                    <td width="30%" style="border-bottom:1px dashed black;" align="center">City Administrator</td>
                    <td width="5%" style="border-bottom:1px dashed black"></td>
                    <td width="30%" style="border-bottom:1px dashed black; font-size:8pt"align="center">SIGNATURE OVER PRINTED NAME</td>
                    <td width="18%" style="border-bottom:1px dashed black;"></td>
                </tr>
                <tr>
                    <td width="100%"><b>V. POST - REPAIR</b></td>
                </tr>
                <tr>
                    <td width="18%">COMPLETION DATE:</td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                    <td width="15%">CONDUCTED BY:</td>
                    <td width="30%" style="border-bottom:1px solid black"></td>
                </tr>
                 <tr>
                    <td width="18%"></td>
                    <td width="30%" style="font-size:8pt" align="center">(DATE & TIME)</td>
                    <td width="15%"></td>
                    <td width="30%" style="font-size:8pt" align="center">SIGNATURE OVER PRINTED NAME</td>
                </tr>
            </table>';

            PDF::SetTitle('Request for Inspection and Repair/Maintenance Form');
            // PDF::AddPage('P');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('P', array(215.9, 330.2 ));

            // if (count($table) > 20){
            //     PDF::SetFont('helvetica', '', 8);
            //     PDF::AddPage('P', array(215.9, 330.2 ));
            // }else{
            //     PDF::SetFont('helvetica', '', 9);
            //     PDF::AddPage('P', array(215.9, 279.4 ));
            // }

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

