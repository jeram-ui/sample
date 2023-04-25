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

class preventiveController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $prfrmnce_db;


    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->prfrmnce_db = $this->G->getPerformance();
        $this->trk_db = $this->G->getTrkDb();
    }
    public function getFunctions()
    {
        $list = db::table('it_printer_setup_functions')->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetOffice()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function GetAreOwner()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetUser()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetInspected()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetVerified()
    {
        $list = DB::table($this->hr_db . '.employee_information')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetEquipment()
    {
        $list = DB::table($this->trk_db . '.it_equipment')
            ->where('it_equipment.status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function GetSpecification($id)
    {
        $list = DB::table($this->trk_db . '.it_equipment_specifications')
            ->where('it_equipment_specifications.status', 0)
            ->where('equipment_id', $id)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        $form = $request->form;
        $formz = $request->formz;
        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->trk_db . '.it_preventive_maintenance')
                ->where('id', $id)
                ->update($form);

            db::table($this->trk_db . '.it_preventive_maintenance_specs')
                ->where("preventive_main", $id)
                ->delete();

            foreach ($formz as $key => $value) {
                $formzData = array(
                    'preventive_main' => $id,
                    'specs_item' => $value['specs_item'],
                    'manufacturer' => $value['manufacturer'],
                    'model' => $value['model'],
                    'serial_num' => $value['serial_num'],
                    'size_form_factor' => $value['size_form_factor'],
                    'remarks' => $value['remarks'],

                );
                db::table($this->trk_db . '.it_preventive_maintenance_specs')->insert($formzData);
                $preventive_specs_id = DB::getPdo()->LastInsertId();

                foreach ($value['functions'] as $keyx => $valuex) {
                    $formxData = array(
                        'preventive_specs_id' =>  $preventive_specs_id,
                        'functions' => $valuex,
                    );
                    db::table($this->trk_db . '.it_preventive_printer_functions')->insert($formxData);
                }

                foreach ($value['Connectivity'] as $keyz => $valuez) {
                    $formCData = array(
                        'preventive_specs_id' =>  $preventive_specs_id,
                        'Connectivity' => $valuez,
                    );
                    db::table($this->trk_db . '.it_preventive_printer_connectivity')->insert($formCData);
                }
            }
        } else {
            DB::table($this->trk_db . '.it_preventive_maintenance')->insert($form);
            $id = DB::getPdo()->LastInsertId();
            // $id = $this->G->pk();

            foreach ($formz as $key => $value) {
                $formzData = array(
                    'preventive_main' => $id,
                    'specs_item' => $value['specs_item'],
                    'manufacturer' => $value['manufacturer'],
                    'model' => $value['model'],
                    'serial_num' => $value['serial_num'],
                    'size_form_factor' => $value['size_form_factor'],
                    'remarks' => $value['remarks'],

                );
                db::table($this->trk_db . '.it_preventive_maintenance_specs')->insert($formzData);
                $preventive_specs_id = DB::getPdo()->LastInsertId();

                foreach ($value['functions'] as $keyx => $valuex) {
                    $formxData = array(
                        'preventive_specs_id' => $preventive_specs_id,
                        'functions' => $valuex,
                    );
                    db::table($this->trk_db . '.it_preventive_printer_functions')->insert($formxData);
                }

                foreach ($value['Connectivity'] as $keyz => $valuez) {
                    $formCData = array(
                        'preventive_specs_id' => $preventive_specs_id,
                        'Connectivity' => $valuez,
                    );
                    db::table($this->trk_db . '.it_preventive_printer_connectivity')->insert($formCData);
                }
            }
        }
        return  $this->G->success();
    }
    public function GetList(Request $request)
    {
        $list = DB::table($this->trk_db . '.it_preventive_maintenance')
            ->orderBy('it_preventive_maintenance.id')
            ->leftJoin($this->trk_db . '.it_equipment', 'it_equipment.id', 'it_preventive_maintenance.equipment')
            ->leftJoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_preventive_maintenance.are_owner')
            ->leftJoin($this->hr_db . '.department', 'department.SysPK_Dept', 'it_preventive_maintenance.office_name')
            ->select(
                "*",
                DB::raw("it_preventive_maintenance.id as id"),
                DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.are_owner) as are_owner'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.user_name) as user_name'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.inspected_by) as inspected_by'),
                DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.verified_by) as verified_by'),
            )
            ->where('office_name', 'like', '%' . $request->office_name)
            ->where('equipment', 'like', '%' . $request->equipment)
            ->where('it_preventive_maintenance.status', 0)
            ->get();
        
        $specs = array();
        foreach ($list as $key => $value) {
            $items = db::table($this->trk_db . '.it_preventive_maintenance_specs')
                ->join($this->trk_db . '.it_preventive_maintenance', 'it_preventive_maintenance.id', 'it_preventive_maintenance_specs.preventive_main')
                ->leftJoin($this->trk_db . '.it_equipment_specifications', 'it_equipment_specifications.id', 'it_preventive_maintenance_specs.specs_item')
                ->leftJoin($this->trk_db . '.it_preventive_printer_functions', 'it_preventive_printer_functions.preventive_specs_id', 'it_preventive_maintenance_specs.id')
                ->leftJoin($this->trk_db . '.it_preventive_printer_connectivity', 'it_preventive_printer_connectivity.preventive_specs_id', 'it_preventive_maintenance_specs.id')
                ->select("*",
                    db::raw("GROUP_CONCAT(DISTINCT it_preventive_printer_functions.functions SEPARATOR '/') AS functions"),
                    db::raw("GROUP_CONCAT(DISTINCT it_preventive_printer_connectivity.Connectivity SEPARATOR '/') AS Connectivity")
                )
                ->where('it_preventive_maintenance_specs.preventive_main', $value->id)
                ->get();
            $specList = array(
                'id' => $value->id,
                'date_inspection' => $value->date_inspection,
                'date_are' => $value->date_are,
                'eqpmnt_name' => $value->eqpmnt_name,
                'Name_Dept' => $value->Name_Dept,
                'are_number' => $value->are_number,
                'are_owner' => $value->are_owner,
                'user_name' => $value->user_name,
                'inspected_by' => $value->inspected_by,
                'verified_by' => $value->verified_by,
                'items' => $items,
            );
        array_push($specs, $specList );
        }

        return response()->json(new JsonResponse($specs));
    }
    function Edit($id)
    {
        $list['form'] = db::table($this->trk_db . '.it_preventive_maintenance')
            ->leftJoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_preventive_maintenance.are_owner')
            ->leftJoin($this->trk_db . '.it_equipment', 'it_equipment.id', 'it_preventive_maintenance.equipment')
            ->select("*", 'it_preventive_maintenance.id as id')
            ->where("it_preventive_maintenance.id", $id)
            ->get();
        $formz = db::table($this->trk_db . '.it_preventive_maintenance_specs')
            ->leftJoin($this->trk_db . '.it_equipment_specifications', 'it_equipment_specifications.id', 'it_preventive_maintenance_specs.specs_item')
            ->select("*", 'it_preventive_maintenance_specs.id as detail_id')
            ->where('it_preventive_maintenance_specs.preventive_main', $id)
            ->get();

        $specs = array();
        foreach ($formz as $key => $valueF) {
            $dumfunction = db::table($this->trk_db . '.it_preventive_printer_functions')
                ->where("preventive_specs_id", $valueF->detail_id)
                ->get();
            $arrayfunction = array();
            foreach ($dumfunction as $key => $value) {
                array_push($arrayfunction, $value->functions);
            }

            $dumConnect = db::table($this->trk_db . '.it_preventive_printer_connectivity')
                ->where("preventive_specs_id", $valueF->detail_id)
                ->get();
            $arrayConnect = array();
            foreach ($dumConnect as $keyz => $valuez) {
                array_push($arrayConnect, $valuez->Connectivity);
            }

            $specsData = array(
                'preventive_main' => $valueF->id,
                'specs_item' => $valueF->specs_item,
                'manufacturer' => $valueF->manufacturer,
                'model' => $valueF->model,
                'size_form_factor' => $valueF->size_form_factor,
                'remarks' => $valueF->remarks,
                'specs_name' => $valueF->specs_name,
                'serial_num' => $valueF->serial_num,
                'functions' => $arrayfunction,
                'Connectivity' => $arrayConnect,
            );
            array_push($specs, $specsData);
        }
        $list['formz'] = $specs;

        return response()->json(new JsonResponse($list));
        log::debug($id);
    }
    public function cancel($id)
    {
        db::table($this->trk_db . '.it_preventive_maintenance')
            ->where('it_preventive_maintenance.id', $id)
            ->update(['it_preventive_maintenance.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function p_Printer(Request $request)
    {
        try {
            // $id = $request->id;
            $form = $request->itm;
            $id = $form['id'];
            $x = 1;
            $main = DB::table($this->trk_db . '.it_preventive_maintenance')
                ->leftJoin($this->trk_db . '.it_equipment', 'it_equipment.id', 'it_preventive_maintenance.equipment')
                ->leftJoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_preventive_maintenance.are_owner')
                ->leftJoin($this->hr_db . '.department', 'department.SysPK_Dept', 'it_preventive_maintenance.office_name')
                ->select(
                    "*",
                    DB::raw($this->hr_db . '.jay_getEmployeeLastName(it_preventive_maintenance.are_owner) as are_lastName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeFirstName(it_preventive_maintenance.are_owner) as are_firstName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeMiddleName(it_preventive_maintenance.are_owner) as are_middleName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeLastName(it_preventive_maintenance.user_name) as are_uLastName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeFirstName(it_preventive_maintenance.user_name) as are_uFirstName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeMiddleName(it_preventive_maintenance.user_name) as are_uMiddleName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.inspected_by) as inspected_by'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.verified_by) as verified_by'),
                )
                ->where('it_preventive_maintenance.status', 0)
                ->where('it_preventive_maintenance.id', $form['id'])
                ->get();
            $mainData = "";
            foreach ($main as $key => $value) {
                $mainData = $value;
            }

            $specsPrint = db::select("call " . $this->trk_db . ".print_IT_Maintenance(?)", [$id]);

            $specsData = "";
            foreach ($specsPrint as $key => $value) {
                $z = $x++;
                $specsData .= '  
                    <tr>
                    <td width="2%" align="center" style="font-size:8pt">' . $z . '</td>
                    <td width="9%" align="center" style="font-size:8pt">' . $value->manufacturer . '</td>
                    <td width="14%" align="center" style="font-size:8pt">' . $value->model . '</td>
                    <td width="10%" align="center" style="font-size:8pt">' . $value->size_form_factor . '</td>
                    <td width="14%" align="center" style="font-size:8pt">' . $value->functionx . '</td>
                    <td width="14%" align="center" style="font-size:8pt">' . $value->serial_num . '</td>
                    <td width="14%" align="center" style="font-size:8pt">' . $value->Connectivityx .  '</td>
                    <td width="14%" align="center" style="font-size:8pt">' . $value->remarks . '</td>
                </tr>';
            }

            $Template = '<table width="100%">
            <tr>

            <th width="100%" style="font-size:11pt;  word-spacing:30px" align="center">
                    <b>CITY OF NAGA</b>
            <br />
                   <b> INFORMATION TECHNOLOGY CENTER </b>
            <br />

                Preventive Computer Maintenance System
                </th>
         </tr>
            <tr>
                <td width="100%" style="color:red; font-size: 12pt" align="center"><u>' . $mainData->eqpmnt_name . '</u></td>
            </tr>
            <br/>
            <br/>

            </table>
            <table cellpadding="1">
                <tr>
                    <td width="23%" align="right">DATE OF INSPECTION:</td>
                    <td width="2%"></td>
                    <td width="35%" style="border-bottom:1px solid black">' . (!empty($mainData->date_inspection) ? (date_format(date_create($mainData->date_inspection), "m/d/Y")) : "") . '</td>
                </tr>
                <tr>
                    <td width="23%" align="right">Office:</td>
                    <td width="2%"></td>
                    <td width="45%" style="border-bottom:1px solid black">' . $mainData->Name_Dept . '</td>
                </tr>
                <tr>
                    <td width="23%" align="right">ARE NO.:</td>
                    <td width="2%"></td>
                    <td width="15%" style="border-bottom:1px solid black">' . $mainData->are_number . '</td>
                </tr>
                <tr>
                    <td width="23%" align="right">Date of ARE:</td>
                    <td width="2%"></td>
                    <td width="15%" style="border-bottom:1px solid black">' . (!empty($mainData->date_are) ? (date_format(date_create($mainData->date_are), "m/d/Y")) : "") . '</td>
                </tr>
                <br/>
                <tr>
                    <td width="23%" align="right">ARE OWNER:</td>
                    <td width="2%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_lastName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_firstName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_middleName . '</b></td>
                </tr>
                <tr>
                    <td width="23%" align="right"></td>
                    <td width="2%"></td>
                    <td width="20%" align="center">LAST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">FIRST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">MIDDLE NAME</td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="23%" align="right">USER:</td>
                    <td width="2%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_uLastName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_uFirstName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_uMiddleName . '</b></td>
                </tr>
                <tr>
                    <td width="23%" align="right"></td>
                    <td width="2%"></td>
                    <td width="20%" align="center">LAST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">FIRST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">MIDDLE NAME</td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="100%" align="left">SPECIFICATION:</td>
                </tr>
                <tr>
                    <td width="100%">
                        <table border="1">
                        <tr>
                            <td width="2%" align="center" style="font-size:8pt"><b>#</b></td>
                            <td width="9%" align="center" style="font-size:8pt"><b>MANUFACTURER</b></td>
                            <td width="14%" align="center" style="font-size:8pt"><b>MODEL</b></td>
                            <td width="10%" align="center" style="font-size:8pt"><b>SIZE/FORM FACTOR</b></td>
                            <td width="14%" align="center" style="font-size:8pt"><b>FUNCTIONS</b></td>
                            <td width="14%" align="center" style="font-size:8pt"><b>SERIAL NO.</b></td>
                            <td width="14%" align="center" style="font-size:8pt"><b>CONNECTIVITY</b></td>
                            <td width="14%" align="center" style="font-size:8pt"><b>REMARKS</b></td>
                        </tr>
                            ' . $specsData . '
                        </table>
                    </td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="10%"></td>
                    <td width="30%">INSPECTED BY:</td>
                    <td width="15%"></td>
                    <td width="45%">VERIFIED BY:</td>
                </tr>
                <br/>
                <tr>
                    <td width="10%"></td>
                    <td width="30%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->inspected_by . '</b></td>
                    <td width="15%"></td>
                    <td width="30%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->verified_by . '</b></td>
                    <td width="15%"></td>
                </tr>
            </table>';


            PDF::SetTitle('Preventive Computer Maintenance Systemx');
            PDF::SetFont('helvetica', 8);
            PDF::AddPage('L');

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
    public function ITC(Request $request)
    {
        try {
            // $id = $request->id;
            $form = $request->itm;
            $id = $form['id'];
            $x = 1;
            $main = DB::table($this->trk_db . '.it_preventive_maintenance')
                ->leftJoin($this->trk_db . '.it_equipment', 'it_equipment.id', 'it_preventive_maintenance.equipment')
                ->leftJoin($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_preventive_maintenance.are_owner')
                ->leftJoin($this->hr_db . '.department', 'department.SysPK_Dept', 'it_preventive_maintenance.office_name')
                ->select(
                    "*",
                    DB::raw($this->hr_db . '.jay_getEmployeeLastName(it_preventive_maintenance.are_owner) as are_lastName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeFirstName(it_preventive_maintenance.are_owner) as are_firstName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeMiddleName(it_preventive_maintenance.are_owner) as are_middleName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeLastName(it_preventive_maintenance.user_name) as are_uLastName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeFirstName(it_preventive_maintenance.user_name) as are_uFirstName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeMiddleName(it_preventive_maintenance.user_name) as are_uMiddleName'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.inspected_by) as inspected_by'),
                    DB::raw($this->hr_db . '.jay_getEmployeeName(it_preventive_maintenance.verified_by) as verified_by'),

                )
                ->where('it_preventive_maintenance.status', 0)
                ->where('it_preventive_maintenance.id', $form['id'])
                ->get();
            $mainData = "";
            foreach ($main as $key => $value) {
                $mainData = $value;
            }

            $specsPrint = db::select("call " . $this->trk_db . ".print_IT_Maintenance(?)", [$id]);

            $specsData = "";

            foreach ($specsPrint as $key => $value) {
                $z = $x++;
                $specsData .= ' <tr>
                    <td width="2%" align="center" style="font-size:9pt">' . $z . '</td>
                    <td width="15%" align="center" style="font-size:9pt">' . $value->specs_name . '</td>
                    <td width="24%" align="center" style="font-size:9pt">' . $value->manufacturer . '</td>
                    <td width="24%" align="center" style="font-size:9pt">' . $value->model . '</td>
                    <td width="20%" align="center" style="font-size:9pt">' . $value->size_form_factor . '</td>
                    <td width="15%" align="center" style="font-size:9pt">' . $value->remarks . '</td>
                </tr>';
            }

            $Template = '<table width="100%">
            <tr>

            <th width="100%" style="font-size:11pt;  word-spacing:30px" align="center">
                    <b>CITY OF NAGA</b>
            <br />
                   <b> INFORMATION TECHNOLOGY CENTER </b>
            <br />

                Preventive Computer Maintenance System
                </th>
         </tr>
            <tr>
                <td width="100%" style="color:red; font-size: 12pt" align="center"><u>' . $mainData->eqpmnt_name . '</u></td>
            </tr>
            <br/>
            <br/>

            </table>
            <table cellpadding="1">
                <tr>
                    <td width="23%" align="right">DATE OF INSPECTION:</td>
                    <td width="2%"></td>
                    <td width="35%" style="border-bottom:1px solid black">' . (!empty($mainData->date_inspection) ? (date_format(date_create($mainData->date_inspection), "m/d/Y")) : "") . '</td>
                </tr>
                <tr>
                    <td width="23%" align="right">Office:</td>
                    <td width="2%"></td>
                    <td width="45%" style="border-bottom:1px solid black">' . $mainData->Name_Dept . '</td>
                </tr>
                <tr>
                    <td width="23%" align="right">ARE NO.:</td>
                    <td width="2%"></td>
                    <td width="15%" style="border-bottom:1px solid black">' . $mainData->are_number . '</td>
                </tr>
                <tr>
                    <td width="23%" align="right">Date of ARE:</td>
                    <td width="2%"></td>
                    <td width="15%" style="border-bottom:1px solid black">' . (!empty($mainData->date_are) ? (date_format(date_create($mainData->date_are), "m/d/Y")) : "") . '</td>
                </tr>
                <br/>
                <tr>
                    <td width="23%" align="right">ARE OWNER:</td>
                    <td width="2%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_lastName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_firstName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_middleName . '</b></td>
                </tr>
                <tr>
                    <td width="23%" align="right"></td>
                    <td width="2%"></td>
                    <td width="20%" align="center">LAST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">FIRST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">MIDDLE NAME</td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="23%" align="right">USER:</td>
                    <td width="2%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_uLastName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_uFirstName . '</b></td>
                    <td width="1%"></td>
                    <td width="20%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->are_uMiddleName . '</b></td>
                </tr>
                <tr>
                    <td width="23%" align="right"></td>
                    <td width="2%"></td>
                    <td width="20%" align="center">LAST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">FIRST NAME</td>
                    <td width="1%"></td>
                    <td width="20%" align="center">MIDDLE NAME</td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="100%" align="left">SPECIFICATION:</td>
                </tr>
                <tr>
                    <td width="100%">
                        <table border="1">
                        <tr>
                            <td width="2%" align="center" style="font-size:9pt"><b>#</b></td>
                            <td width="15%" align="center" style="font-size:9pt"><b>ITEM</b></td>
                            <td width="24%" align="center" style="font-size:9pt"><b>MANUFACTURER</b></td>
                            <td width="24%" align="center" style="font-size:9pt"><b>MODEL</b></td>
                            <td width="20%" align="center" style="font-size:9pt"><b>SIZE/FORM FACTOR</b></td>
                            <td width="15%" align="center" style="font-size:9pt"><b>REMARKS</b></td>
                        </tr>
                            ' . $specsData . '
                        </table>
                    </td>
                </tr>
                <br/>
                <br/>
                <tr>
                    <td width="10%"></td>
                    <td width="30%">INSPECTED BY:</td>
                    <td width="15%"></td>
                    <td width="45%">VERIFIED BY:</td>
                </tr>
                <br/>
                <tr>
                    <td width="10%"></td>
                    <td width="30%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->inspected_by . '</b></td>
                    <td width="15%"></td>
                    <td width="30%" style="border-bottom:1px solid black" align="center"><b>' . $mainData->verified_by . '</b></td>
                    <td width="15%"></td>
                </tr>
            </table>';


            PDF::SetTitle('Preventive Computer Maintenance Systemx');
            PDF::SetFont('helvetica', 8);
            PDF::AddPage('L');

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
