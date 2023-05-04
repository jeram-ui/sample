<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use PDF;
use \App\Laravue\JsonResponse;
use Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Storage;

class GlobalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function print()
    {
        $html_content = '<h1>Hello world</h1>';
        PDF::SetTitle("List of users");
        PDF::AddPage();
        PDF::writeHTML($html_content, true, false, true, false, '');
        PDF::Output('userlist.pdf');
    }
    public function StaledChecks()
    {
        $list = DB::select("CALL ".$this->getLGUDb().".jay_StaledChecks1_notification(NOW())");
        return response()->json(new JsonResponse($list));
    }
    public function printHeader($_department)
    {
        return '<table style="width=100%;">
        <tr>
        <th align="right">
        <img src="' . public_path() . '/images/Logo1.png"  height="60" width="60">
        </th>
        <th style="font-size:12pt;" align="center">
        Republic of the Philippines
        <br>
        Province of Cebu
        <br>
        City Government of Naga
        <br>
        <b>' . $_department . '</b>
        </th>
        <th align="left">
        <img src="' . public_path() . '/images/NAGA LOGO2.png"  height="60" width="65">
        </th>
        </tr>
    </table>';
    }
    public function generateReference($prefix, $table, $date, $refdate)
    {
        $query = DB::select("SELECT CONCAT('" . $prefix . "',DATE_FORMAT('" . $date . "', '%y'),'-',LPAD(COUNT(" . $refdate . ")+ 1,5,0)) AS 'NOS' FROM " . $table . " WHERE  YEAR(" . $refdate . ") =  YEAR('" . $date . "')");
        return $query;
    }
    public function generateReferenceDirect($prefix, $table, $date, $refdate)
    {
        $query = DB::select("SELECT CONCAT('" . $prefix . "',DATE_FORMAT('" . $date . "', '%y'),'-',LPAD(COUNT(" . $refdate . ")+ 1,5,0)) AS 'NOS' FROM " . $table . " WHERE  YEAR(" . $refdate . ") =  YEAR('" . $date . "')");

        foreach ($query as  $value) {
            return $value->NOS;
        }
    }
    public function getGuid()
    {
        // return Guid::create();
    }
    public function getLGUDb()
    {
        return config('variable.db_lgu');
    }
    public function getPDSDummyDB()
    {
        return 'pds_dummy';
    }
    public function getLogo2($h1 = '')
    {
        $html = '<table style="width=100%;">
    <tr>
    <th align="right">
    <img src="' . public_path() . '/images/Logo1.png"  height="60" width="60">
    </th>
    <th style="font-size:12pt;" align="center">
    Republic of the Philippines
    <br>
    Province of Cebu
    <br>
    City Government of Naga
    <br>
    </th>
    <th align="left">
    <img src="' . public_path() . '/images/NAGA LOGO2.png"  height="60" width="65">
    </th>
    </tr>
    </table>';
    }
    public function getHRDb()
    {
        return config('variable.db_hr');
    }
    public function getPerformance()
    {
        return 'performance_management';
    }
    public function getTrkDb()
    {
        return config('variable.db_trk');
    }
    public function getGeneralDb()
    {
        return config('variable.db_general');
    }
    public function getSchedulerDb()
    {
        return config('variable.db_scheduler');
    }
    public function getProcDb()
    {
        return 'qpsii_lguprocurement';
    }
    public function getInsDb()
    {
        return 'inspectorate';
    }
    public function getBACDb()
    {
        return 'bac_lgu';
    }
    public function getBudgetDb()
    {
        return 'budget';
    }
    public function getQRDb()
    {
        return 'naga_qr';
    }
    public function getCENRODb()
    {
        return 'cenro';
    }
    public function getMayorsDb()
    {
        return 'mayors';
    }
    public function getLogo()
    {
        return config('variable.logo');
    }

    public function pk()
    {
        return DB::getPDo()->lastInsertId();
    }
    public function serverdatetime()
    {
        $sql = DB::select('select now() as "date"');
        foreach ($sql as $row) {
            return $row->date;
        }
    }
    public function success()
    {
        return response()->json(
            new JsonResponse([
                'Message' => 'Transaction completed successfully.',
                'status' => 'success',
            ])
        );
    }
    public function serverdate()
    {
        $sql = DB::select('select date(now()) as "date"');
        foreach ($sql as $row) {
            return $row->date;
        }
    }
    public function get_lgu_data()
    {
        $sql = DB::select('call ' . $this->getLGUDb() . '.jay_display_lgu_name()');
        return json_encode($sql);
    }
    public function system_generated()
    {
        return "Cylix Technologies, Inc.";
    }
    public function getDepartment()
    {
        try {
            $query = DB::select("SELECT `SysPK_Dept` AS 'id',`Name_Dept` AS 'name' FROM " . $this->getHRDb() . ".department WHERE STATUS = 'Active' AND include = 'True'");
            return response()->json(new JsonResponse(['data' => $query]));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function getAllDepartmentEmployee()
    {
        try {
            $query = DB::select("SELECT * FROM " . $this->getTrkDb() . ".employeedepartment");
            return response()->json(new JsonResponse(['data' => $query]));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function getSOF()
    {
        try {
            $query = DB::select("SELECT  `fund_description` FROM " . $this->getLGUDb() . ".cto_fund_setup ");
            return response()->json(new JsonResponse(['data' => $query]));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function getRoutingSetup()
    {
        try {
            $query = DB::select("SELECT `id`,`Flow_Name`,`description`  FROM " . $this->getLGUDb() . ".law_routing_setup WHERE `Flow_Name` = 'LEGAL'");
            return response()->json(new JsonResponse(['data' => $query]));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function numberTowords($num)
    {
        $ones = array(
            0 => "Zero",
            1 => "One",
            2 => "Two",
            3 => "Three",
            4 => "Four",
            5 => "Five",
            6 => "Six",
            7 => "Seven",
            8 => "Eight",
            9 => "Nine",
            10 => "Ten",
            11 => "Eleven",
            12 => "Twelve",
            13 => "Thirteen",
            14 => "Fourteen",
            15 => "Fifteen",
            16 => "Sixteen",
            17 => "Seventeen",
            18 => "Eighteen",
            19 => "Nineteen",
        );
        $tens = array(
            0 => "Zero",
            1 => "Ten",
            2 => "Twenty",
            3 => "Thirty",
            4 => "Forty",
            5 => "Fifty",
            6 => "Sixty",
            7 => "Seventy",
            8 => "Eighty",
            9 => "Ninety"
        );
        $hundreds = array(
            "Hundred",
            "Thousand",
            "Million",
            "Billion",
            "Trillion",
            "Quadrillion"
        ); /*limit t quadrillion */
        $num = number_format($num, 2, ".", ",");
        // dd($num);
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {
            //    dd($whole_arr);
            while (substr($i, 0, 1) == "0") {
                $i = substr($i, 1, 5);
            }
            if ($i < 20) {
                /* echo "getting:".$i; */
                // dd($ones[$i]);

                $rettxt .= $ones[$i];
            } elseif ($i < 100) {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $tens[substr($i, 0, 1)];
                }
                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 1, 1)];
                }
            } else {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                }
                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $tens[substr($i, 1, 1)];
                }
                if (substr($i, 2, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 2, 1)];
                }
            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0) {
            $rettxt .= " and ";
            if ($decnum < 20) {
                $rettxt .= $ones[$decnum];
            } elseif ($decnum < 100) {
                $rettxt .= $tens[substr($decnum, 0, 1)];
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
        }
        return $rettxt . ' pesos only';
    }
    public function numberTowords_W_dec($num)
    {
        $ones = array(
            0 => "ZERO",
            1 => "ONE",
            2 => "TWO",
            3 => "THREE",
            4 => "FOUR",
            5 => "FIVE",
            6 => "SIX",
            7 => "SEVEN",
            8 => "EIGHT",
            9 => "NINE",
            10 => "TEN",
            11 => "ELEVEN",
            12 => "TWELVE",
            13 => "THIRTEEN",
            14 => "FOURTEEN",
            15 => "FIFTEEN",
            16 => "SIXTEEN",
            17 => "SEVENTEEN",
            18 => "EIGHTEEN",
            19 => "NINETEEN",
        );
        $tens = array(
            0 => "ZERO",
            1 => "TEN",
            2 => "TWENTY",
            3 => "THIRTY",
            4 => "FORTY",
            5 => "FIFTY",
            6 => "SIXTY",
            7 => "SEVENTY",
            8 => "EIGHTY",
            9 => "NINETY"
        );
        $hundreds = array(
            "HUNDRED",
            "THOUSAND",
            "MILLION",
            "BILLION",
            "TRILLION",
            "QUADRILLION"
        ); /*limit t quadrillion */
        $num = number_format($num, 2, ".", ",");
        // dd($num);
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {
            //    dd($whole_arr);
            while (substr($i, 0, 1) == "0") {
                $i = substr($i, 1, 5);
            }
            if ($i < 20) {
                /* echo "getting:".$i; */
                // dd($ones[$i]);

                $rettxt .= $ones[$i];
            } elseif ($i < 100) {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $tens[substr($i, 0, 1)];
                }
                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 1, 1)];
                }
            } else {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                }
                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $tens[substr($i, 1, 1)];
                }
                if (substr($i, 2, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 2, 1)];
                }
            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0) {
            $rettxt .= " PESOS AND ";
            if ($decnum > 99) {
                $rettxt .= $decnum . "/1000";
            } elseif ($decnum < 100) {
                $rettxt .= substr($decnum, 0, 1);
                $rettxt .= substr($decnum, 1, 1) . "/100";
            }
        }
        return $rettxt ;
    }

    /**
     * @OA\Get(
     *    path="globalVariable",
     *    summary="Global Variables",
     *    description="List of Global Data with corresponding Variables-> Department:department, Employees Department:employeeDepartment, Source of Fund:SOF, employee:Employees, Barangay:barangay, Revision Years:revisionyears, Cashier:cashier. Note use the map getters to retrieve this data ",
     *    operationId="globalVariable",
     *    tags={"Global Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getEmployee()
    {
        $employee = Employee::all();
        return response()->json(new JsonResponse($employee));
    }
    public function globalVariable()
    {
        try {
            // $department = DB::select("SELECT `SysPK_Dept` AS 'id',`Name_Dept` AS 'name' FROM " . $this->getHRDb() . ".department WHERE STATUS = 'Active' AND include = 'True'");
            // $employeeDepartment = DB::select("SELECT * FROM " . $this->getTrkDb() . ".employeedepartment");
            // $SOF = DB::select("SELECT  `fund_description` FROM " . $this->getLGUDb() . ".cto_fund_setup ");
            // $flows = DB::select("SELECT
            // doc_flow_main.`id`
            // ,doc_flow_main.`flow_name` as 'Flow Name'
            // ,doc_flow_employee.`Signatory_name`
            // ,GROUP_CONCAT(CONCAT(doc_flow_employee.`Signatory_name`,' (',doc_flow_employee.`display_name`,')') ORDER BY doc_flow_employee.`id` ASC SEPARATOR '<br>') AS 'display_name'
            // ,doc_flow_employee.`id` AS 'dtlid'
            // FROM " . $this->getTrkDb() . ".doc_flow_main
            // INNER JOIN " . $this->getTrkDb() . ".doc_flow_employee
            // ON(doc_flow_main.`id` = doc_flow_employee.`doc_flow_main_id`)
            // where doc_flow_main.status = 0
            // GROUP BY doc_flow_main.`id`
            // ");
            // $empid = Auth::user()->Employee_id;
            // $data_calendar = DB::select("call qpsii_lgusystem._rans_display_calendar('1')");
            // $calendars = array();
            // foreach ($data_calendar as $key => $val) {
            //     $calendars[] = array(
            //         'id' => intval($val->ID),
            //         'title' => $val->type,
            //         'subject' => trim($val->Subject),
            //         'location' => trim($val->Location),
            //         'description' => trim($val->Description),
            //         'start' => date_format(date_create($val->StartDate), "Y-m-d H:i:s"),
            //         'end' => date_format(date_create($val->EndDate), "Y-m-d H:i:s"),
            //         'endx' => date_format(date_create($val->EndDate), "Y-m-d H:i:s"),
            //         'guid' => $val->guid,
            //         'initiator' => $val->initiator,
            //         'creator' => $val->creator,
            //         'status' => $val->Confirmed,
            //         'color' => $val->color,
            //         'type' => $val->type,
            //         'groupid' => $val->groupid,
            //     );
            // }
            // $employee = Employee::all();
            // $data['department'] = $department;
            // $data['employeeDepartment'] = $employeeDepartment;
            // $data['SOF'] = $SOF;
            // $data['employee'] = $employee;
            // $data['flows'] = $flows;
            // $data['calendar'] = $calendars;
            // $data['barangay'] = DB::select("CALL " . $this->getLGUDb() . ".jay_display_brangay_list");
            // $data['revisionyears'] = DB::select("CALL " . $this->getLGUDb() . ".spl_getAll_revisions_jay");
            // $data['cashier'] = DB::select("CALL " . $this->getLGUDb() . ".jay_new_get_employee_list_cashier");
            // $data['genericBusinessList'] = DB::select("CALL " . $this->getLGUDb() . ".jay_new_get_business_list_application_inquiry('%%')");
            // $data['person'] = db::table($this->getLGUDb() . '.hr_person_profile')->where('status', 'active')->get();


            // $data = Cache::remember('global', 60, function () {
            $empid = Auth::user()->Employee_id;
            //    log::debug($empid);
            $department = DB::select("SELECT `SysPK_Dept` AS 'id',`Name_Dept` AS 'name',short_desc FROM " . $this->getHRDb() . ".department WHERE STATUS = 'Active'");
            $employeeDepartment = DB::select("SELECT * FROM " . $this->getTrkDb() . ".employeedepartment");
            $SOF = DB::select("SELECT  `fund_description` FROM " . $this->getLGUDb() . ".cto_fund_setup ");

            $flows = DB::select('SELECT id,flow_name AS "Flow Name" FROM (SELECT doc_flow_main.* FROM `documenttracker`.doc_flow_main INNER JOIN documenttracker.doc_flow_employee
             ON(doc_flow_main.`id` = doc_flow_employee.`doc_flow_main_id`)
             INNER JOIN documenttracker.doc_flow_employee_details ON(doc_flow_employee_details.`sig_id` = `doc_flow_employee`.`id`)
             WHERE (doc_flow_employee_details.`emp_id` = "' . $empid . '"
             OR doc_flow_main.`entry_type` = 1)
             ORDER BY doc_flow_employee.`id` ASC)B GROUP BY id
             ');

            $data_calendar = DB::select("call qpsii_lgusystem._rans_display_calendar('1')");
            $calendars = array();
            foreach ($data_calendar as $key => $val) {
                $calendars[] = array(
                    'id' => intval($val->ID),
                    'title' => $val->type,
                    'subject' => trim($val->Subject),
                    'location' => trim($val->Location),
                    'description' => trim($val->Description),
                    'start' => date_format(date_create($val->StartDate), "Y-m-d H:i:s"),
                    'end' => date_format(date_create($val->EndDate), "Y-m-d H:i:s"),
                    'endx' => date_format(date_create($val->EndDate), "Y-m-d H:i:s"),
                    'guid' => $val->guid,
                    'initiator' => $val->initiator,
                    'creator' => $val->creator,
                    'status' => $val->Confirmed,
                    'color' => $val->color,
                    'type' => $val->type,
                    'groupid' => $val->groupid,
                );
            }
            $employee = Employee::all();
            $data['department'] = $department;
            $data['employeeDepartment'] = $employeeDepartment;
            $data['SOF'] = $SOF;
            $data['employee'] = $employee;
            $data['flows'] = $flows;
            $data['calendar'] = $calendars;
            $data['barangay'] = DB::select("CALL " . $this->getLGUDb() . ".jay_display_brangay_list");
            $data['revisionyears'] = DB::select("CALL " . $this->getLGUDb() . ".spl_getAll_revisions_jay");
            $data['cashier'] = DB::select("CALL " . $this->getLGUDb() . ".jay_new_get_employee_list_cashier");
            // $data['genericBusinessList'] = DB::select("CALL " . $this->getLGUDb() . ".jay_new_get_business_list_application_inquiry('%%')");
            // $data['person'] = db::table($this->getLGUDb() . '.hr_person_profile')->where('status', 'active')->get();
            // return  $data;
            // });


            return response()->json(new JsonResponse(['data' => $data]));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function getAllDepatmentEmployee()
    {
        try {
            $query = DB::select("SELECT * FROM " . $this->getTrkDb() . ".employeedepartment");
            return response()->json(new JsonResponse(['data' => $query]));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function LGUName()
    {
        return DB::select("CALL " . $this->getLGUDb() . ".jay_display_lgu_name()");
    }
    public function getItem()
    {
        $item = DB::select('select * from item_list');
        return response()->json(new JsonResponse($item));
    }
    public function province()
    {
        $item = DB::table('refprovince')->select('provCode as idx', 'provDesc as name')->get();
        return response()->json(new JsonResponse($item));
    }
    public function city($id)
    {
        $item = DB::table('refcitymun')->select('citymunCode as id', 'citymunDesc as name')->where('provCode', $id)->get();
        return response()->json(new JsonResponse($item));
    }
    public function barangay($id)
    {
        $item = DB::table('refbrgy')->select('brgyCode as id', 'brgyDesc as name')->where('citymunCode', $id)->get();
        return response()->json(new JsonResponse($item));
    }
    public function profileUpdate(Request $request)
    {
        $basic = $request->basic;
        $address = $request->address;
        $other = $request->other;
        $contact = $request->contact;
        $data = array_merge($basic, $address, $other, $contact);
        log::debug($data);
        if ($data['pkID'] == 0) {
            db::table('tbl_person_setup')->insert($data);
        } else {
            db::table('tbl_person_setup')->where('user_id', $data['user_id'])->update($data);
        }
        return response()->json(new JsonResponse(['Message' => 'Successfully updated', 'status' => 'success']));
    }
    public function getProfile($id)
    {
        $item = db::table('tbl_person_setup')->where('user_id', $id)->get();
        $data = array(
            'isLogin' => 1, 'Login' => $this->serverdatetime()
        );
        db::table('users')->where('id', '=', $id)->update($data);
        return response()->json(new JsonResponse($item));
    }
    public function porfileUpload(Request $request)
    {
        $empid = Auth::user()->id;
        if ($request->hasFile('avatar')) {
            $originalImage = $request->file('avatar');
            $thumbnailImage = Image::make($originalImage);
            $thumbnailPath = public_path() . '/images/client/' . $empid . '/';
            $this->createFolder($thumbnailPath);
            $time = time();
            $thumbnailImage->resize(100, 100);
            $thumbnailImage->save($thumbnailPath . $time . '.' . $originalImage->getClientOriginalExtension());
            db::table('users')->where('id', $empid)->update(['image_path' => $empid . '/' . $time . '.' . $originalImage->getClientOriginalExtension()]);
            return response()->json(new JsonResponse(['Message' => 'Successfully updated', 'status' => 'success']));
        }
    }
    public function createFolder($path)
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
    }
    public function getBusinessForAssessment(Request $request)
    {
        $unregister = $request->unregister;
        $dateNow = date("Y");
        $name = "%" . $request->name . "%";

        log::debug($unregister);
        if ($unregister === 'true') {

            log::debug('2');
            $item = DB::select('CALL ' . $this->getLGUDb() . '.jay_get_forAssessment_business_list(?,?)', array($dateNow, $name));
        } else {
            log::debug('1');
            $item = DB::select('CALL ' . $this->getLGUDb() . '.jay_get_forAssessment_business_list1(?,?)', array($dateNow, $name));
        }
        return response()->json(new JsonResponse($item));
    }
    public function getBusinessList(Request $request)
    {
        $name = $request->name;
        $item = db::table($this->getLGUDb() . '.ebplo_business_list')
            ->select('business_number as BNUMBER', 'business_number', 'business_contact_no_temp', 'business_email_add', 'business_name as Business Name', 'trade_name as Trade Name/Franchise', 'reference_address as Business Address', 'reference_owner_name as owner')
            ->where('business_name', 'like', "%" . $name . "%")
            ->where('business_status', 'ACTIVE')
            ->limit(50)
            ->get();
        return response()->json(new JsonResponse($item));
    }
    public function getPersonProfileList(Request $request)
    {
        $lastname = $request->lastname;
        $firstname = $request->firstname;
        $filter1 = "%" . $lastname . "%";
        $filter2 = "%" . $firstname . "%";
        $item = db::select('CALL ' . $this->getLGUDb() . '.jay_display_person_profile_list(?,?)', array($filter1, $filter2));
        // dd($item);
        return response()->json(new JsonResponse($item));
    }
    public function displaybillingfees(Request $request)
    {
        $id = $request->form_id;
        $name = $request->form_name;
        $list = DB::select('call ' . $this->getLGUDb() . '.spl_display_setup_certification_permit_jay(?,?)', array($name, $id));
        return response()->json(new JsonResponse($list));
    }
    public function displaybillingfeesCode(Request $request)
    {
        $id = $request->form_id;
        $name = $request->form_name;
        $code = $request->account_code;
        $list = DB::select('call ' . $this->getLGUDb() . '.spl_display_setup_certification_permit_jay1(?,?,?)', array($name, $id, $code));
        return response()->json(new JsonResponse($list));
    }
    public function displayProjectList(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.cvl_display_project_registration_list_notin(?,?)', array($from, $to));
        return response()->json(new JsonResponse($list));
    }
    public function displaybusinessList(Request $request)
    {
        //dd($request);
        //$date = date("Y", strtotime($date));
        $bname = '%';
        $proptype = '%';
        $list = DB::select('call ' . $this->getLGUDb() . '.ecao_display_company_withproperty_byproptype(?,?)', array($bname, $proptype));
        return response()->json(new JsonResponse($list));
    }

    public function displaypersonList(Request $request)
    {
        // dd($request);
        //$date = date("Y", strtotime($date));
        $bname = '%';
        $proptype = '%';
        $list = DB::select('call ' . $this->getLGUDb() . '.ecao_display_person_withproperty_byproptype(?,?)', array($bname, $proptype));
        return response()->json(new JsonResponse($list));
    }

    public function displaytaxdeclist(Request $request)
    {
        $brgy = $request->brgyid;
        $list = DB::select('call ' . $this->getLGUDb() . '.Cvl_display_taxNo3(?)', array($brgy));
        return response()->json(new JsonResponse($list));
    }

    public function displaytaxDecListOwner(Request $request)
    {   //dd($request->owner_id);
        $owner_id = $request->owner_id;
        $person_id = $request->payee_id;
        $payee_id = '0';
        if ($request->payee_type == 'Business') {
            $payee_type = 'Company';
            $payee_id = $owner_id;
        } else {
            $payee_type = $request->payee_type;
            if ($owner_id == $person_id) {
                $payee_id = $owner_id;
            } else {
                $payee_id = $person_id;
            }
        }
        $list = DB::select('call ' . $this->getLGUDb() . '.ecao_sp_display_alltd_proptype_owner_id(?,?)', array($payee_type, $payee_id));

        return response()->json(new JsonResponse($list));
    }
    public function displayRPTexempt()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.mj_ecao_display_exempt_list()');
        return response()->json(new JsonResponse($list));
    }
    public function displayCertTrueCopy()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.mj_ecao_display_truecopy_list()');
        return response()->json(new JsonResponse($list));
    }
    public function displaycadastrallot()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.cvl_display_cadastral_lot()');
        return response()->json(new JsonResponse($list));
    }
    public function displaybrgylist()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.jay_display_brangay_list()');
        return response()->json(new JsonResponse($list));
    }
    public function insertReason($reason)
    {
        $item = db::table($this->getLGUDb() . '.ebplo_transaction_logs')->insert($reason);
        $id = DB::getPDo()->lastInsertId();
        return response()->json(new JsonResponse($item));
    }
    public function formIssuance(Request $request)
    {
        // dd($request);
        $issuance = $request->main;
        $item = db::table($this->getGeneralDb() . '.tbl_issuance')->insert($issuance);
        return response()->json(new JsonResponse(['Message' => 'Successfully saved!', 'status' => 'success']));
    }

    /**
     * @OA\Get(
     *    path="signatoryReport",
     *    summary="Report Signatories",
     *    description="Report Signatories",
     *    operationId="signatoryReport",
     *    tags={"Global Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getBusinessForInspection(Request $request)
    {
        $dateNow = date("Y");
        $name = "%" . $request->name . "%";
        $app_type = $request->type . "%";
        $entry = $request->entry;
        $item = DB::select('CALL ' . $this->getLGUDb() . '.rans_get_business_for_inspection(?,?,?,?)', array($app_type, $dateNow, $name, $entry));
        return response()->json(new JsonResponse($item));
    }

    public function signatoryReport()
    {
        $item =  DB::select('call ' . $this->getLGUDb() . '.cvl_get_signatory_mayor_head()');
        return $item;
        //return response()->json(new JsonResponse($item));
    }
    /**
     * @OA\Get(
     *    path="getAZCol",
     *    summary="Global Variables",
     *    description="Advance Filter",
     *    operationId="getAZCol",
     *    tags={"Global Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getAZCol()
    {
        $AZCol =  DB::select('call ' . $this->getLGUDb() . '.jay_temp_abstract_a_z()');
        return response()->json(new JsonResponse($AZCol));
    }

    public function getclearancespermits()
    {
        $permits =  DB::select('call ' . $this->getLGUDb() . '.spl_display_verification_docs_jho()');
        return response()->json(new JsonResponse($permits));
    }
    public function getkindbusiness()
    {
        $kindbus =  DB::select('call ' . $this->getLGUDb() . '.jay_display_cto_kind_business_setup()');
        return response()->json(new JsonResponse($kindbus));
    }
    public function getclassification()
    {
        $class =  DB::select('call ' . $this->getLGUDb() . '.display_classification_gigil()');
        return response()->json(new JsonResponse($class));
    }
    public function getbusstat()
    {
        $class =  DB::select('call ' . $this->getLGUDb() . '.spl_display_business_status_jho()');
        return response()->json(new JsonResponse($class));
    }

    public function getApplicationType()
    {
        $class =  DB::select('call ' . $this->getLGUDb() . '.spl_display_business_transaction_type_jho()');
        return response()->json(new JsonResponse($class));
    }
    /**
     * @OA\Get(
     *    path="businessLocation",
     *    summary="Business locator",
     *    description="Business locator",
     *    operationId="businessLocation",
     *    tags={"Global Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function businessLocation(Request $request)
    {
        $dateNow = date("Y");
        $geojson = array(
            'type'      => 'FeatureCollection',
            'features'  => array(),
        );
        $coor = array();
        $data = db::table($this->getLGUDb() . '.ebplo_business_list')
            ->join($this->getLGUDb() . '.ebplo_business_application', 'ebplo_business_application.business_number', '=', 'ebplo_business_list.business_number')
            ->leftJoin('mapa', 'mapa.business_id', '=', 'ebplo_business_list.business_number')
            ->select('ebplo_business_list.business_number', 'ebplo_business_application.business_app_id', db::raw($this->getLGUDb() . '.CamelCase_sly(ebplo_business_list.business_name) as name'), 'mapa.lng', 'mapa.lat', 'business_address_temp as address', 'payment_status')
            ->where('ebplo_business_application.tax_year', $dateNow)
            ->where('ebplo_business_application.transaction_type', '<>', 'Others')
            ->get();

        foreach ($data as $row) {
            $marker = array(
                'type' => 'Feature',
                'properties' => array(
                    'title' => $row->name,
                    'icon' => 'marker',
                    'idx' => $row->business_number,
                    'appid' => $row->business_app_id,
                    'status' => $row->payment_status,
                    'lat' => $row->lat,
                    'lng' => $row->lng,
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        (float) $row->lng,
                        (float) $row->lat
                    ),
                ),
            );
            //   array_push($geojson['id'], $row->business_number);
            array_push($geojson['features'], $marker);
            if ($row->lat > 0) {
                $coors = array('lng' => (float) $row->lng, 'lat' => (float) $row->lat, 'status' => $row->payment_status);
                array_push($coor, $coors);
            }
        }
        $datas['list'] = $data;
        $datas['map'] = $geojson;
        $datas['coordinate'] = $coor;
        return response()->json(new JsonResponse($datas));
    }
    public function updateLocation(Request $request)
    {
        $id = $request->id;
        $data = array(
            'business_id' => $id,
            'name' => $request->name,
            'lat' => $request->loc['lat'],
            'lng' => $request->loc['lng'],
        );
        db::table('mapa')->where('business_id', $id)->delete();
        db::table('mapa')->insert($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    // Tricycle
    public function displayOrdinance()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.spl_display_reference_zoe()');
        return response()->json(new JsonResponse($list));
    }
    public function displayCC()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.spl_display_cc_zoe()');
        return response()->json(new JsonResponse($list));
    }
    public function displayOrganization()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.organization_display_zoe()');
        return response()->json(new JsonResponse($list));
    }
    public function displayDepartment()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.department_display_zoe()');
        return response()->json(new JsonResponse($list));
    }
    //OBO
    public function getBuildingList()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.Cvl_display_building_list_zoe()');
        return response()->json(new JsonResponse($list));
    }
    public function addPersonProfileList(Request $request)
    {
        $data = array_merge($request->data['basic'], $request->data['address']);
        db::table($this->getLGUDb() . '.hr_person_profile')
            ->insert($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        // return response()->json(new JsonResponse($list));
    }
    public function getAddFees(Request $request)
    {
        $payerid = $request->payer_id;
        $payerType = $request->payerType;
        $year = $request->year;
        $trans_id = $request->trans_id;
        $transType = $request->transType;
        $list = DB::select('call ' . $this->getLGUDb() . '.cto_miscellaneous_bill_echarge_group_addfees(?,?,?,?,?)', [$payerid, $payerType, $year, $trans_id, $transType]);
        return response()->json(new JsonResponse($list));
    }
    public function getAccountForBill()
    {
        $list = DB::select('call ' . $this->getLGUDb() . '.jay_cto_income_account_setup_DISPLAY()');
        return response()->json(new JsonResponse($list));
    }
    public function printPriorityBlank()
    {
        try {
            // $data = $request->data;
            $chkNumber = db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')
                ->select('priority_no')
                ->orderBy('id', 'desc')->first();
            $chkNumberExist = db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')->where('baid', $id)->count();
            if ($chkNumberExist > 0) {
            } else {
                if ($chkNumber) {
                    $pr = $chkNumber->priority_no + 1;
                    db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')->insert(['baid' => $id, 'priority_no' => $pr]);
                } else {
                    db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')->insert(['baid' => $id, 'priority_no' => 1]);
                }
            }
            $datax = db::table($this->getLGUDb() . '.ebplo_business_application')
                ->join($this->getLGUDb() . '.ebplo_business_application_priority_number', 'ebplo_business_application.business_app_id', 'ebplo_business_application_priority_number.baid')
                ->select('*', db::raw("qpsii_lgusystem._GetBusiness_BrgyAddress(ebplo_business_application.business_app_id) as 'addresss'"))
                ->where('baid', $id)
                ->orderBy('ebplo_business_application_priority_number.id', 'desc')->first();
            $count = $datax->priority_no;

            // log::debug($datax->id);
            PDF::AddPage('P', array(80, 80));
            PDF::SetTitle('Priority Number');
            // PDF::SetHeaderMargin(1);
            // PDF::SetTopMargin(1);
            PDF::SetMargins(1, 1, 1, 1);
            PDF::SetFont('Helvetica', '', 8);
            $bMargin = PDF::getBreakMargin();
            $auto_page_break = PDF::getAutoPageBreak();
            PDF::SetAutoPageBreak(false, 0);
            PDF::SetAutoPageBreak($auto_page_break, $bMargin);
            PDF::setPageMark();
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            $params1 = PDF::serializeTCPDFtagParameters(array("" . $datax->business_app_id . "", 'QRCODE,H', '', '', 20, 20, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $Template = '

                                          <table  style="width:100%" cellpadding ="1">
                                           <tr >
                                              <th width="100%" style="text-align:center; font-size:70px;">' . $count . '</th>
                                           </tr>
                                            <tr>
                                             <th width="20%">Business Name</th>
                                             <th width="2%">:</th>
                                             <th width="78%">' . $datax->business_name . '</th>
                                            </tr>
                                            <tr>
                                               <th width="20%">Address</th>
                                               <th width="2%">:</th>
                                               <th width="78%">' . $datax->addresss . '</th>
                                            </tr>
                                            <tr>
                                            <th width="20%">Time</th>
                                            <th width="2%">:</th>
                                            <th width="78%">' . $datax->ts . '</th>
                                            </tr>
                                          </table>


                ';
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
    public function printPriority1($id, $category)
    {
        try {
            // $data = $request->data;
            $chkNumber = db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')
                ->select('priority_no')
                ->orderBy('id', 'desc')->first();
            $chkNumberExist = db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')->where('baid', $id)->count();
            if ($chkNumberExist > 0) {
                db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')->where('baid', $id)->update(['_categoryx' => $category]);
            } else {
                if ($chkNumber) {
                    $pr = $chkNumber->priority_no + 1;
                    db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')->insert(['baid' => $id, 'priority_no' => $pr, '_categoryx' => $category]);
                } else {
                    db::table($this->getLGUDb() . '.ebplo_business_application_priority_number')->insert(['baid' => $id, 'priority_no' => 1, '_categoryx' => $category]);
                }
            }
            $datax = db::table($this->getLGUDb() . '.ebplo_business_application')
                ->join($this->getLGUDb() . '.ebplo_business_application_priority_number', 'ebplo_business_application.business_app_id', 'ebplo_business_application_priority_number.baid')
                ->select('*', db::raw("qpsii_lgusystem._GetBusiness_BrgyAddress(ebplo_business_application.business_app_id) as 'addresss'"))
                ->where('baid', $id)
                ->orderBy('ebplo_business_application_priority_number.id', 'desc')->first();
            $count = $datax->priority_no;

            // log::debug($datax->id);
            PDF::AddPage('P', array(80, 80));

            PDF::SetTitle('Priority Number');
            // PDF::SetHeaderMargin(1);
            // PDF::SetTopMargin(1);
            PDF::SetMargins(1, 1, 1, 1);
            PDF::SetFont('Helvetica', '', 8);
            $bMargin = PDF::getBreakMargin();
            $auto_page_break = PDF::getAutoPageBreak();
            PDF::SetAutoPageBreak(false, 0);
            PDF::SetAutoPageBreak($auto_page_break, $bMargin);
            PDF::setPageMark();
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            $params1 = PDF::serializeTCPDFtagParameters(array("" . $datax->business_app_id . "", 'QRCODE,H', '', '', 20, 20, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $Template = '

                                          <table  style="width:100%" cellpadding ="1">
                                           <tr >
                                              <th width="100%" style="text-align:center; font-size:70px;">' . $count . '</th>
                                           </tr>
                                            <tr>
                                             <th width="20%">Business Name</th>
                                             <th width="2%">:</th>
                                             <th width="78%">' . $datax->business_name . '</th>
                                            </tr>
                                            <tr>
                                               <th width="20%">Address</th>
                                               <th width="2%">:</th>
                                               <th width="78%">' . $datax->addresss . '</th>
                                            </tr>
                                            <tr>
                                            <th width="20%">Time</th>
                                            <th width="2%">:</th>
                                            <th width="78%">' . $datax->ts . '</th>
                                            </tr>
                                          </table>


                ';
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
    public function insertFees(Request $request)
    {
        log::debug($request);
        $payer = $request->data;
        $account = $request->amount;
        $data = array(
            'payer_id' => $payer['payer_id'],
            'business_application_id' => $payer['app_id'],
            'payer_type' => $payer['payerType'],
            'bill_id' => $payer['trans_id'],
            'ref_id' => $payer['trans_id'],
            'bill_amount' => $account['Initial Amount'],
            'bill_description' => $account['Account Description'],
            'account_code' => $account['Code'],
            'transaction_type' => $payer['transType'],
        );
        db::table($this->getLGUDb() . '.cto_general_billing')
            ->insert($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function getAddFeesDelete($id)
    {
        db::table($this->getLGUDb() . '.cto_general_billing')
            ->where('SysPK_general_billing', $id)
            ->update(['status' => 'CANCELLED']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function getORPosting(Request $request)
    {
        $transaction_type = $request->transaction_type;
        $payer_id = $request->payer_id;
        $result = db::table($this->getLGUDb() . '.cto_general_billing')
            ->join($this->getLGUDb() . '.cto_or_transactions', 'cto_or_transactions.or_id', '=', 'cto_general_billing.or_id')
            ->where('bill_description', $transaction_type)
            ->where('payer_type', 'person')
            ->where('cto_general_billing.transaction_type', 'Miscellaneous')
            ->where('cto_general_billing.status', 'PAID')
            ->where('payer_id', $payer_id)->get();
        return response()->json(new JsonResponse($result));
    }
    public function upload(Request $request)
    {
        log::debug($request);
        $files = $request->file('file');
        if (!empty($files)) {
            $path = hash('sha256', time());
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $filename = $file->getClientOriginalName();
                if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                    $data = array(
                        'trans_id' => $request->trans_id,
                        'file_name' => $filename,
                        'trans_type' => $request->trans_type,
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

    public function  uploaded(Request $request)
    {
        $trans_id = $request->trans_id;
        $trans_type = $request->trans_type;
        $data = db::table('docs_upload')
            ->where('trans_id', $trans_id)
            ->where('trans_type', $trans_type)
            ->where('stat', "ACTIVE")
            ->get();
        return response()->json(new JsonResponse($data));
    }
    public function documentView($id)
    {
        $main = DB::table('docs_upload')->where('id', $id)->get();
        foreach ($main as $key => $value) {
            $file = $value->file_name;
            // $path = '../storage/files/document/' . $value->file_path . '/' . $file;
            // if (\File::exists($path)) {
            //     $file = \File::get($path);
            //     $type = \File::mimeType($path);
            //     $response = \Response::make($file, 200);
            //     $response->header("Content-Type", $type);
            //     return $response;
            // }
            return '/storage/files/document/' . $value->file_path . '/' . $file;
        }
    }
    public function uploadRemove($id)
    {
        $data = db::table('docs_upload')->where('id', $id)
            ->update(['stat' => "CANCELLED"]);
        return response()->json(new JsonResponse(['Message' => 'Successfully uploaded', 'status' => 'success']));
    }
    public function getListPerDepartment()
    {
        $data = db::select("SELECT PPID,`NAME` FROM " . $this->getHRDb() . ".employee_information WHERE DEPID = (SELECT DEPID FROM " . $this->getHRDb() . ".employee_information WHERE PPID = " . Auth::user()->Employee_id . ")");
        return response()->json(new JsonResponse($data));
    }
    public function getProjectRegister(Request $request)
    {
        $list = db::table($this->getLGUDb() . '.setup_project_registration_main')
            ->select('id', 'project_name', 'location')
            ->where('project_category', 'Internal')
            ->where('STATUS', 'ACTIVE')
            ->where('project_name', 'like', '%' . $request->project_name . '%')
            ->get();

        return response()->json(new JsonResponse($list));
    }
}
