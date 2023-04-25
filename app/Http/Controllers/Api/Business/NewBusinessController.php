<?php

namespace App\Http\Controllers\Api\Business;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;
use Illuminate\Support\Facades\log;
class NewBusinessController extends Controller
{
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }
    public function storeBusinesslist(Request $request){
   try {

   
      $data =  $request->form;
      if ($data['business_number']>0) {
        db::table( $this->lgu_db.'.ebplo_business_list')
        ->where("business_number",$data['business_number'])
        ->update($data);
    }else{
        db::table( $this->lgu_db.'.ebplo_business_list')
        ->insert($data);
    }

    return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
   } catch (\Throwable $th) {
      
   }
    }
    public function businessApplied(Request $request){

        $list = DB::select('call ' . $this->lgu_db . '.rans_get_businesslist(?,?)',[date("Y-01-01"),date("Y-12-31")]);
        return response()->json(new JsonResponse($list));
    }
    public function applied_per_brgy(Request $request){
        $list = DB::table($this->lgu_db .'.ebplo_business_application')
        ->join($this->lgu_db.'.lgu_brgy_setup','lgu_brgy_setup.brgy_id','=','ebplo_business_application.brgy_address')
        ->select('lgu_brgy_setup.brgy_name as BARANGAY',db::raw('SUM(
            CASE
              WHEN `ebplo_business_application`.`tax_year` = "2021"
              and application_type ="NEW"
              THEN 1
           ELSE 0 END) as "2021 New" ')
           ,db::raw('SUM(
            CASE
              WHEN `ebplo_business_application`.`tax_year` = "2021"
              and application_type ="RENEW"
              THEN 1
           ELSE 0 END) as "2021 Renew" ')
           ,db::raw('SUM(
            CASE
              WHEN `ebplo_business_application`.`tax_year` = "2022"
              and application_type ="NEW"
              THEN 1
           ELSE 0 END) as "2022 New"')
           
           ,db::raw('SUM(
            CASE
              WHEN `ebplo_business_application`.`tax_year` = "2022"
              and application_type ="RENEW"
              THEN 1
           ELSE 0 END) as "2022 Renew"')
           )
        
        ->groupBy('brgy_name')->get()
        ;
        return response()->json(new JsonResponse($list));
    }
    public function businessDocs(Request $request){
        log::debug($request);
        $bapid = $request->BPAID;
        $type =$request->application_type;
        $org = $request['Organization_Type'];
        $rpt = $request['Real_Property'];
        $appdate = $request['application_date'];
        $list = DB::select('call ' . $this->lgu_db . '.jay_display_VerificationDocumentsRetrieve_new(?,?,?,?,?)',[$bapid,$type,$org, $rpt,$appdate]);
        return response()->json(new JsonResponse($list));
    }
    public function getOrganizationType(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_organizationaltype()');
        return response()->json(new JsonResponse($list));
    }
    public function getBSPType(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_bsptype()');
        return response()->json(new JsonResponse($list));
    }
    public function getOccupationalFees(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_occupationalfees()');
        return response()->json(new JsonResponse($list));
    }
    public function getBusinessKind(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_businesskind()');
        return response()->json(new JsonResponse($list));
    }
    public function permitNoNew(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_permitno_new()');
        return response()->json(new JsonResponse($list));
    }
    public function businessNoNew(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_businessno_new()');
        return response()->json(new JsonResponse($list));
    }
    public function businessAccountNoNew(Request $request)
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_accountno_new()');
        return response()->json(new JsonResponse($list));
    }
    public function getBusinessListforrenew(Request $request)
    {
        $taxyear = $request->tax_year;
        $item = DB::select('CALL ' . $this->lgu_db . '.jay_new_display_business_list_application_renewal_gigil(?)', array($taxyear));

        return response()->json(new JsonResponse($item));
    }
    public function getDocumentList(Request $request)
    {
        try {
            $businessID = 0;
            $appType = 'New';
            $orgType = $request->org_Type;
            $rpt = 'False';
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_display_VerificationDocuments(?,?,?,?)', array($businessID, $appType, $orgType, $rpt));
            return response()->json(new JsonResponse($list));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getBarangaylist()
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_display_barangay_list()');
        return response()->json(new JsonResponse($list));
    }
    public function getBusinessMasterlist(Request $request)
    {
        // dd($request);
        $dateFrom = $request->from;
        $dteTo = $request->to;
        $type = $request->type_filt;
        $brgy = $request->barangays;
        $docs = $request->certpermits;
        $kindbus = $request->kindbus;
        $class = $request->classification;
        $gender = $request->gender;
        $permitstat = $request->bus_stat;
        $paymentstat = $request->payment_stat;
        $apptype = $request->app_type;
        $transtype = $request->bustype;
        $osstype = $request->osstype;
        $release = $request->releasestat;

        if ($brgy === '%') {
            $brgy = "'All'";
        } else {
            foreach ($brgy as $arraybrgy) {
                $brgy = $arraybrgy;
            }
        }

        if ($docs === null) {
            $docs = "'All'";
            $type = "'All'";
        } else {
            foreach ($docs as $arraydocs) {
                $docs = $arraydocs;
            }
        }

        if ($kindbus === '%') {
            $kindbus = "'All'";
        } else {
            foreach ($kindbus as $arraykindbus) {
                $kindbus = $arraykindbus;
            }
        }


        if ($class === '%') {
            $class = "'All'";
        } else {
            foreach ($class as $arrayclass) {
                $class = $arrayclass;
            }
        }

        if ($gender === '%') {
            $gender = "'All'";
        } else {
            foreach ($gender as $arraygender) {
                $gender = $arraygender;
            }
        }

        if ($permitstat === '%') {
            $permitstat = "'All'";
        } else {

            foreach ($permitstat as $arraypermitstat) {
                $permitstat = $arraypermitstat;
            }
        }

        if ($paymentstat === '%') {
            $paymentstat = "'All'";
        } else {
            foreach ($paymentstat as $arraypaymentstat) {
                $paymentstat = $arraypaymentstat;
            }
        }

        if ($apptype === '%') {
            $apptype = "'All'";
        } else {
            foreach ($apptype as $arrayapptype) {
                $apptype = $arrayapptype;
            }
        }

        if ($transtype === '%') {
            $transtype =  "'All'";
        } else {
            foreach ($transtype as $arraytranstype) {
                $transtype = $arraytranstype;
            }
        }

        if ($osstype === '%') {
            $osstype = "'All'";
        } else {
            foreach ($osstype as $arrayosstype) {
                $osstype = $arrayosstype;
            }
        }

        if ($release === 'All') {
            $release = "'All'";
        } else {
            foreach ($release as $arrayrelease) {
                $release = $arrayrelease;
            }
        }
        $list = DB::select('call ' . $this->lgu_db . '.spl_ebplo_business_masterlist_gigil2(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array($dateFrom, $dteTo, $type, $brgy, $brgy, $docs, $docs, $kindbus, $kindbus, $class, $class, $gender, $gender, $permitstat, $permitstat, $paymentstat, $paymentstat, $apptype, $apptype, $transtype, $transtype, $osstype, $osstype, $release, $release));
        
        // $list = DB::select('call ' . $this->lgu_db . '.spl_ebplo_business_masterlist_gigil1(?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array($dateFrom, $dteTo, $type, $brgy, $docs, $kindbus, $class, $gender, $permitstat, $paymentstat, $apptype, $transtype, $osstype, $release));

        return response()->json(new JsonResponse($list));
    }
    public function getbusinessList(Request $request)
    {
        $tmp = json_decode($request->dates);
        $dateFrom = $tmp->from;
        $dteTo = $tmp->to;
        $apptype = $request->appType;       
        if ($apptype === 'New') {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_businesslistNEW(?,?)', array($dateFrom, $dteTo));
        } else if ($apptype === 'Renew') {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_businesslistRENEW(?,?)', array($dateFrom, $dteTo));
        } else if ($apptype === 'Transfer') {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_businesslistTRANSFER()');
        } else if ($apptype === 'Amend') {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_businesslistAMEND()');
        } else if ($apptype === 'Additional') {
            $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_businesslistADDITIONAL()');
        }
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        log::debug($request);
        try {
            DB::beginTransaction();
            $businessMain = $request->businessMainData;
            $taxpayerData = $request->taxpayerInfoData;
            $employeeData = $request->employeeInfoData;
            $employeeDetails = $request->employeeInfoDetails;
            $businessData = $request->businessKindData;
            $businessDetails = $request->businessKindDetails;
            $lessorData = $request->lessorInfoData;
            $bspDetails = $request->bspInfoDetails;
            $documentDetails = [];
            $transfType = $request->transferType;
            $tansferlocation = $request->transferloc;
            $transferowner = $request->transferowner;
            $idx = $businessMain['business_app_id'];
            $busNo = $businessMain['business_no'];

            if ($idx > 0) {
                $this->update($idx, $busNo, $businessMain, $taxpayerData, $employeeData, $employeeDetails, $businessData, $businessDetails, $lessorData, $bspDetails, $documentDetails);
            } else {
                $this->save($businessMain, $taxpayerData, $employeeData, $employeeDetails, $businessData, $businessDetails, $lessorData, $bspDetails, $documentDetails, $transfType, $tansferlocation, $transferowner);
            };
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function edit(Request $request, $id)
    {
        log::debug($id);
        $data['businessMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_main(?)', array($id));
        $data['businessTaxpayerMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_businessnew_taxpayerinfomain(?)', array($id));
        $data['businessEmployeeMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_employeeinfomain(?)', array($id));
        $data['businessEmployeeDetails'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_employeeinfodetails(?)', array($id));
        $data['businessKindMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_businessinfomain(?)', array($id));
        $data['businessKindDetail'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_businessinfodetails(?)', array($id));
        $data['businessLessor'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_lessorinfomain(?)', array($id));
        $data['businessBsp'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_bspinfodetails(?)', array($id));
        $data['businessDocuments'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_documentinfodetails(?)', array($id));
        return response()->json(new JsonResponse($data));
    }

    public function retrievedata($id)
    {
        $data['businessTaxpayerMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_businessnew_taxpayerinfomain(?)', array($id));
        $data['businessEmployeeMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_employeeinfomain(?)', array($id));
        $data['businessEmployeeDetails'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_employeeinfodetails(?)', array($id));
        $data['businessKindMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_businessinfomain(?)', array($id));
        $data['businessKindDetail'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_businessinfodetails(?)', array($id));
        $data['businessLessor'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_lessorinfomain(?)', array($id));
        $data['businessDocuments'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_new_documentinfodetails(?)', array($id));

        return response()->json(new JsonResponse($data));
    }

    public function save($businessMain, $taxpayerData, $employeeData, $employeeDetails, $businessData, $businessDetails, $lessorData, $bspDetails, $documentDetails, $transfType, $tansferlocation, $transferowner)
    {
        if ($businessMain['app_bmbe'] == "Yes") {
            $bmbe = 'True';
        } else {
            $bmbe = 'False';
        };
        if ($businessMain['reg_bsp'] == "Yes") {
            $bsp = 'True';
        } else {
            $bsp = 'False';
        };
        if ($businessMain['business_rented'] == "Yes") {
            $rented = 'False';
        } else {
            $rented = 'True';
        };
        if ($businessMain['application_type'] === 'New') {
            $newBusinessList = array(
                'business_name' => $taxpayerData['tp_business_name'],
                'trade_name' => $taxpayerData['tp_tradename_franchise'],
                'registered' => 'YES',
                'brgy_id' => $taxpayerData['tp_businessadd_barangayid'],
                'business_address_temp' => $taxpayerData['tp_businessadd_houseno'] . ' ' . $taxpayerData['tp_businessadd_bldgname'] . ' ' . $taxpayerData['tp_businessadd_unitno'] . ', ' . $taxpayerData['tp_businessadd_street'] . ' ' . $taxpayerData['tp_businessadd_subdivision'] . ', ' . $taxpayerData['tp_businessadd_barangay'] . ', ' . $taxpayerData['tp_businessadd_citymun'] . ' ' . $taxpayerData['tp_businessadd_province'],
                'business_contact_no_temp' => $taxpayerData['tp_businessadd_contactno'],
                'business_email_add' => $taxpayerData['tp_businessadd_emailadd'],
                'reference_owner' => $taxpayerData['tp_taxpayer_nameid'],
                'reference_address' => $taxpayerData['tp_homeadd_houseno'] . ' ' . $taxpayerData['tp_homeadd_bldgname'] . ' ' . $taxpayerData['tp_homeadd_unitno'] . ', ' . $taxpayerData['tp_homeadd_street'] . ' ' . $taxpayerData['tp_homeadd_subdivision'] . ', ' . $taxpayerData['tp_homeadd_barangay'] . ', ' . $taxpayerData['tp_homeadd_citymun'] . ' ' . $taxpayerData['tp_homeadd_province'],
                'reference_org' => $businessMain['organization_type']
            );
            DB::table($this->lgu_db . '.ebplo_business_list')->insert($newBusinessList);
            $busID = DB::getPDo()->lastInsertId();
            $newBusiness = array(
                'business_number' => $busID,
                'tax_year' => date("Y", strtotime($taxpayerData['tp_application_date'])),
                'application_date' => $taxpayerData['tp_application_date'],
                'permit_status' => 'N',
                'permit_no' => $businessMain['permit_no'],
                'permit_no1' => $businessMain['permit_no1'],
                'busAccntNo' => $taxpayerData['tp_businessaccountno_a'] . $taxpayerData['tp_businessadd_barangayid'] . '-00' . $busID . $taxpayerData['tp_businessaccountno_c'],
                'business_name' => $taxpayerData['tp_business_name'],
                'trade_name' => $taxpayerData['tp_tradename_franchise'],
                'transaction_type' => $businessMain['application_type'],
                'application_type' => 'NEW',
                'BMBE' => $bmbe,
                'BMBE_no' => $businessMain['bmbe_no'],
                'BSP' => $bsp,
                'BSP_no' => $businessMain['bsp_no'],
                'with_property' => $rented,
                'status' => 'ACTIVE',
                'business_status' => 'PENDING',
                'bstatus_beforeTerm' => '',
                'transfer' => '',
                'transfer_tax_pyer_id' => 0,
                'transfer_owner_id' => 0,
                'ammendment' => '',
                'place_of_issuance' => '',
                'PIN' => '',
                'PIC' => '',
                'organization_type' => $businessMain['organization_type'],
                'pca_no' => $taxpayerData['tp_pca_no'],
                'nfa_no' => $taxpayerData['tp_nfa_no'],
                'dti_reg_no' => $taxpayerData['tp_dtisec_regno'],
                'dti_reg_date' => $taxpayerData['tp_dtisec_regdate'],
                'dti_expiry_date' => $taxpayerData['tp_dtisec_expdate'],
                'entity' => $taxpayerData['tp_taxpayer_noincentive'],
                'owner' => $taxpayerData['tp_taxpayer_nameid'],
                'president_name' => $taxpayerData['tp_president_treasurerid'],
                'business_address' => $taxpayerData['tp_businessadd_houseno'] . ' ' . $taxpayerData['tp_businessadd_bldgname'] . ' ' . $taxpayerData['tp_businessadd_unitno'] . ', ' . $taxpayerData['tp_businessadd_street'] . ' ' . $taxpayerData['tp_businessadd_subdivision'] . ', ' . $taxpayerData['tp_businessadd_barangay'] . ', ' . $taxpayerData['tp_businessadd_citymun'] . ' ' . $taxpayerData['tp_businessadd_province'],
                'brgy_address' => $taxpayerData['tp_businessadd_barangayid'],
                'owners_address' => $taxpayerData['tp_homeadd_houseno'] . ' ' . $taxpayerData['tp_homeadd_bldgname'] . ' ' . $taxpayerData['tp_homeadd_unitno'] . ', ' . $taxpayerData['tp_homeadd_street'] . ' ' . $taxpayerData['tp_homeadd_subdivision'] . ', ' . $taxpayerData['tp_homeadd_barangay'] . ', ' . $taxpayerData['tp_homeadd_citymun'] . ' ' . $taxpayerData['tp_homeadd_province'],
                'bookeeper' => $taxpayerData['tp_representative_bookkeeperid'],
                'reference_no' => $taxpayerData['tp_reference_no'],
                'ctc_no' => $taxpayerData['tp_ctc_no'],
                'TIN' => $taxpayerData['tp_tin_no'],
                'SSS' => $taxpayerData['tp_sss_no'],
                'contact_no' => $taxpayerData['tp_businessadd_contactno'],
                'email_address' => $taxpayerData['tp_businessadd_emailadd'],
                'employee_residing' => $employeeData['empinfo_lguresident_cnt'],
                'total_employee' => $employeeData['empinfo_employee_cnt'],
                'total_male' => $employeeData['empinfo_male_cnt'],
                'total_female' => $employeeData['empinfo_female_cnt'],
                'no_delivery_units' => $taxpayerData['tp_delivery_units'],
                'business_area' => $taxpayerData['tp_business_area'],
                'bus_activity' => $taxpayerData['tp_office_type'],
                'other_specify' => $taxpayerData['tp_office_others'] . '',
                'lessor' => $lessorData['lessor_tp_id'],
                'lessor_address' => $lessorData['lessor_tp_bldgno'] . ' ' . $lessorData['lessor_tp_bldgname'] . ' ' . $lessorData['lessor_tp_unitno'] . ', ' . $lessorData['lessor_tp_street'] . ' ' . $lessorData['lessor_tp_barangay'] . ', ' . $lessorData['lessor_tp_subdivision'] . ', ' . $lessorData['lessor_tp_citymun'] . ' ' . $lessorData['lessor_tp_province'],
                'contact_person' => $lessorData['lessor_tp_contactperson'],
                'monthly_rental' => $lessorData['lessor_tp_monthlyrental'],
                'tel_no' => $lessorData['lessor_tp_emergencytelno'],
                'mode_of_payment' => $businessMain['modeof_payment'],
                'gross_sales_capitalization' => $businessData['gross_capital_total'],
                'new_capital' => 0,
                'application_from' => 'NORMAL',
                'payment_status' => 'For Assessment',
                'td_id' => $businessMain['taxdec_id'],
                'business_president' => $taxpayerData['tp_president_treasurer'],
            );
            DB::table($this->lgu_db . '.ebplo_business_application')->insert($newBusiness);
            $id = DB::getPDo()->lastInsertId();
            $newBusinessDetails = array(
                'business_no' => $busID,
                'capitalization' => $businessData['gross_capital_total'],
                'gross_sales' => 0,
                'new_gross_sales' => 0,
                'lessor_name' => $lessorData['lessor_tp_name'],
                'business_add_blk' => $taxpayerData['tp_businessadd_houseno'],
                'business_add_lot' => $taxpayerData['tp_businessadd_unitno'],
                'business_add_bldg' => $taxpayerData['tp_businessadd_bldgname'],
                'business_add_street' => $taxpayerData['tp_businessadd_street'],
                'business_add_brgy' => $taxpayerData['tp_businessadd_barangay'],
                'business_add_subd' => $taxpayerData['tp_businessadd_subdivision'],
                'business_add_city' => $taxpayerData['tp_businessadd_citymun'],
                'business_add_prov' => $taxpayerData['tp_businessadd_province'],
                'business_add_telno' => $taxpayerData['tp_businessadd_contactno'],
                'business_add_email' => $taxpayerData['tp_businessadd_emailadd'],
                'owner_add_blk' => $taxpayerData['tp_homeadd_houseno'],
                'owner_add_lot' => $taxpayerData['tp_homeadd_unitno'],
                'owner_add_bldg' => $taxpayerData['tp_homeadd_bldgname'],
                'owner_add_street' => $taxpayerData['tp_homeadd_street'],
                'owner_add_brgy' => $taxpayerData['tp_homeadd_barangay'],
                'owner_add_subd' => $taxpayerData['tp_homeadd_subdivision'],
                'owner_add_city' => $taxpayerData['tp_homeadd_citymun'],
                'owner_add_prov' => $taxpayerData['tp_homeadd_province'],
                'owner_add_telno' => $taxpayerData['tp_homeadd_contactno'],
                'owner_add_email' => $taxpayerData['tp_homeadd_emailadd'],
                'lessor_add_blk' => $lessorData['lessor_tp_bldgno'],
                'lessor_add_lot' => $lessorData['lessor_tp_unitno'],
                'lessor_add_bldg' => $lessorData['lessor_tp_bldgname'],
                'lessor_add_street' => $lessorData['lessor_tp_street'],
                'lessor_add_brgy' => $lessorData['lessor_tp_barangay'],
                'lessor_add_subd' => $lessorData['lessor_tp_subdivision'],
                'lessor_add_city' => $lessorData['lessor_tp_citymun'],
                'lessor_add_prov' => $lessorData['lessor_tp_province'],
                'lessor_add_telno' => $lessorData['lessor_tp_telno'],
                'lessor_add_email' => $lessorData['lessor_tp_emailadd'],
                'SSS' => $taxpayerData['tp_sss_no'],
                'in_case_telno' => $lessorData['lessor_tp_emergencytelno'],
                'in_case_mobile' => $lessorData['lessor_tp_emergencymobileno'],
                'in_case_email' => $lessorData['lessor_tp_emergencyemailadd'],
            );
            DB::table($this->lgu_db . '.ebplo_business_application_detail')->insert($newBusinessDetails);
            foreach ($employeeDetails as $row) {
                $array = array(
                    'bappid' => $id,
                    'occ_id' => $row['descriptionid'],
                    'no_emp' => $row['noofemployees'],
                    'remarks' => $row['remarks'],
                );
                DB::table($this->lgu_db . '.ebplo_business_occ_fees_app')->insert($array);
            }
            foreach ($businessDetails as $row) {
                $array = array(
                    'bappid' => $id,
                    'kind_id' => $row['descriptionid'],
                    'description' => $row['description'],
                    'remarks' => $row['remarks'],
                    'cp_gs' => $row['gross'],
                    'app_type' => 'NEW',
                );
                DB::table($this->lgu_db . '.ebplo_business_kind_business')->insert($array);
            }
            foreach ($bspDetails as $row) {
                $array = array(
                    'bsp_id' => $row,
                    'bus_id' => $id,
                );
                DB::table($this->lgu_db . '.ebplo_application_bsp')->insert($array);
            }
            foreach ($documentDetails as $row) {
                $array = array(
                    'bus_app_id' => $id,
                    'verified' => $row['Include'],
                    'doc_id' => $row['doc_id'],
                    'doc_description' => $row['Document Description'],
                    'date_issued' => $row['Date Issued'],
                    'verified_by' => $row['Verified By'],
                    'verified_by_ID' => $row['Verified By ID'],
                    'dept' => $row['Dept ID'],
                );
                DB::table($this->lgu_db . '.ebplo_verification_docs')->insert($array);
            }
        } else if ($businessMain['application_type'] === 'Renew' or $businessMain['application_type'] === 'Additional') {
            $ttype = '';

            if ($businessMain['application_type'] === 'Renew') {
                $ttype = 'Renew';
            } else {
                $ttype = 'Additional';
            }

            $newBusiness = array(
                'business_number' => $taxpayerData['business_id'],
                'tax_year' => date("Y", strtotime($taxpayerData['tp_application_date'])),
                'application_date' => $taxpayerData['tp_application_date'],
                'permit_status' => 'R',
                'permit_no' => $businessMain['permit_no'],
                'permit_no1' => $businessMain['permit_no1'],
                'busAccntNo' => $taxpayerData['tp_businessaccountno_a'] . $taxpayerData['tp_businessadd_barangayid'] . '-00' . $taxpayerData['business_id'] . $taxpayerData['tp_businessaccountno_c'],
                'business_name' => $taxpayerData['tp_business_name'],
                'trade_name' => $taxpayerData['tp_tradename_franchise'],
                'transaction_type' => $ttype,
                'application_type' => $ttype,
                'BMBE' => $bmbe,
                'BMBE_no' => $businessMain['bmbe_no'],
                'BSP' => $bsp,
                'BSP_no' => $businessMain['bsp_no'],
                'with_property' => $rented,
                'status' => 'ACTIVE',
                'business_status' => 'PENDING',
                'bstatus_beforeTerm' => '',
                'transfer' => '',
                'transfer_tax_pyer_id' => 0,
                'transfer_owner_id' => 0,
                'ammendment' => '',
                'place_of_issuance' => '',
                'PIN' => '',
                'PIC' => '',
                'organization_type' => $businessMain['organization_type'],
                'pca_no' => $taxpayerData['tp_pca_no'],
                'nfa_no' => $taxpayerData['tp_nfa_no'],
                'dti_reg_no' => $taxpayerData['tp_dtisec_regno'],
                'dti_reg_date' => $taxpayerData['tp_dtisec_regdate'],
                'dti_expiry_date' => $taxpayerData['tp_dtisec_expdate'],
                'entity' => $taxpayerData['tp_taxpayer_noincentive'],
                'owner' => $taxpayerData['tp_taxpayer_nameid'],
                'president_name' => $taxpayerData['tp_president_treasurerid'],
                'business_address' => $taxpayerData['tp_businessadd_houseno'] . ' ' . $taxpayerData['tp_businessadd_bldgname'] . ' ' . $taxpayerData['tp_businessadd_unitno'] . ', ' . $taxpayerData['tp_businessadd_street'] . ' ' . $taxpayerData['tp_businessadd_subdivision'] . ', ' . $taxpayerData['tp_businessadd_barangay'] . ', ' . $taxpayerData['tp_businessadd_citymun'] . ' ' . $taxpayerData['tp_businessadd_province'],
                'brgy_address' => $taxpayerData['tp_businessadd_barangayid'],
                'owners_address' => $taxpayerData['tp_homeadd_houseno'] . ' ' . $taxpayerData['tp_homeadd_bldgname'] . ' ' . $taxpayerData['tp_homeadd_unitno'] . ', ' . $taxpayerData['tp_homeadd_street'] . ' ' . $taxpayerData['tp_homeadd_subdivision'] . ', ' . $taxpayerData['tp_homeadd_barangay'] . ', ' . $taxpayerData['tp_homeadd_citymun'] . ' ' . $taxpayerData['tp_homeadd_province'],
                'bookeeper' => $taxpayerData['tp_representative_bookkeeperid'],
                'reference_no' => $taxpayerData['tp_reference_no'],
                'ctc_no' => $taxpayerData['tp_ctc_no'],
                'TIN' => $taxpayerData['tp_tin_no'],
                'SSS' => $taxpayerData['tp_sss_no'],
                'contact_no' => $taxpayerData['tp_businessadd_contactno'],
                'email_address' => $taxpayerData['tp_businessadd_emailadd'],
                'employee_residing' => $employeeData['empinfo_lguresident_cnt'],
                'total_employee' => $employeeData['empinfo_employee_cnt'],
                'total_male' => $employeeData['empinfo_male_cnt'],
                'total_female' => $employeeData['empinfo_female_cnt'],
                'no_delivery_units' => $taxpayerData['tp_delivery_units'],
                'business_area' => $taxpayerData['tp_business_area'],
                'bus_activity' => $taxpayerData['tp_office_type'],
                'other_specify' => $taxpayerData['tp_office_others'] . '',
                'lessor' => $lessorData['lessor_tp_id'],
                'lessor_address' => $lessorData['lessor_tp_bldgno'] . ' ' . $lessorData['lessor_tp_bldgname'] . ' ' . $lessorData['lessor_tp_unitno'] . ', ' . $lessorData['lessor_tp_street'] . ' ' . $lessorData['lessor_tp_barangay'] . ', ' . $lessorData['lessor_tp_subdivision'] . ', ' . $lessorData['lessor_tp_citymun'] . ' ' . $lessorData['lessor_tp_province'],
                'contact_person' => $lessorData['lessor_tp_contactperson'],
                'monthly_rental' => $lessorData['lessor_tp_monthlyrental'],
                'tel_no' => $lessorData['lessor_tp_emergencytelno'],
                'mode_of_payment' => $businessMain['modeof_payment'],
                'gross_sales_capitalization' => $businessData['gross_capital_total'],
                'new_capital' => 0,
                'application_from' => 'NORMAL',
                'payment_status' => 'For Assessment',
                'td_id' => $businessMain['taxdec_id'],
                'business_president' => $taxpayerData['tp_president_treasurer'],
            );
            DB::table($this->lgu_db . '.ebplo_business_application')->insert($newBusiness);
            $id = DB::getPDo()->lastInsertId();
            $newBusinessDetails = array(
                'business_no' => $taxpayerData['business_id'],
                'capitalization' => $businessData['gross_capital_total'],
                'gross_sales' => 0,
                'new_gross_sales' => 0,
                'lessor_name' => $lessorData['lessor_tp_name'],
                'business_add_blk' => $taxpayerData['tp_businessadd_houseno'],
                'business_add_lot' => $taxpayerData['tp_businessadd_unitno'],
                'business_add_bldg' => $taxpayerData['tp_businessadd_bldgname'],
                'business_add_street' => $taxpayerData['tp_businessadd_street'],
                'business_add_brgy' => $taxpayerData['tp_businessadd_barangay'],
                'business_add_subd' => $taxpayerData['tp_businessadd_subdivision'],
                'business_add_city' => $taxpayerData['tp_businessadd_citymun'],
                'business_add_prov' => $taxpayerData['tp_businessadd_province'],
                'business_add_telno' => $taxpayerData['tp_businessadd_contactno'],
                'business_add_email' => $taxpayerData['tp_businessadd_emailadd'],
                'owner_add_blk' => $taxpayerData['tp_homeadd_houseno'],
                'owner_add_lot' => $taxpayerData['tp_homeadd_unitno'],
                'owner_add_bldg' => $taxpayerData['tp_homeadd_bldgname'],
                'owner_add_street' => $taxpayerData['tp_homeadd_street'],
                'owner_add_brgy' => $taxpayerData['tp_homeadd_barangay'],
                'owner_add_subd' => $taxpayerData['tp_homeadd_subdivision'],
                'owner_add_city' => $taxpayerData['tp_homeadd_citymun'],
                'owner_add_prov' => $taxpayerData['tp_homeadd_province'],
                'owner_add_telno' => $taxpayerData['tp_homeadd_contactno'],
                'owner_add_email' => $taxpayerData['tp_homeadd_emailadd'],
                'lessor_add_blk' => $lessorData['lessor_tp_bldgno'],
                'lessor_add_lot' => $lessorData['lessor_tp_unitno'],
                'lessor_add_bldg' => $lessorData['lessor_tp_bldgname'],
                'lessor_add_street' => $lessorData['lessor_tp_street'],
                'lessor_add_brgy' => $lessorData['lessor_tp_barangay'],
                'lessor_add_subd' => $lessorData['lessor_tp_subdivision'],
                'lessor_add_city' => $lessorData['lessor_tp_citymun'],
                'lessor_add_prov' => $lessorData['lessor_tp_province'],
                'lessor_add_telno' => $lessorData['lessor_tp_telno'],
                'lessor_add_email' => $lessorData['lessor_tp_emailadd'],
                'SSS' => $taxpayerData['tp_sss_no'],
                'in_case_telno' => $lessorData['lessor_tp_emergencytelno'],
                'in_case_mobile' => $lessorData['lessor_tp_emergencymobileno'],
                'in_case_email' => $lessorData['lessor_tp_emergencyemailadd'],
            );
            DB::table($this->lgu_db . '.ebplo_business_application_detail')->insert($newBusinessDetails);
            foreach ($employeeDetails as $row) {
                $array = array(
                    'bappid' => $id,
                    'occ_id' => $row['descriptionid'],
                    'no_emp' => $row['noofemployees'],
                    'remarks' => $row['remarks'],
                );
                DB::table($this->lgu_db . '.ebplo_business_occ_fees_app')->insert($array);
            }
            foreach ($businessDetails as $row) {
                $array = array(
                    'bappid' => $id,
                    'kind_id' => $row['descriptionid'],
                    'description' => $row['description'],
                    'remarks' => $row['remarks'],
                    'cp_gs' => $row['gross'],
                    'app_type' => 'NEW',
                );
                DB::table($this->lgu_db . '.ebplo_business_kind_business')->insert($array);
            }
            foreach ($bspDetails as $row) {
                $array = array(
                    'bsp_id' => $row,
                    'bus_id' => $id,
                );
                DB::table($this->lgu_db . '.ebplo_application_bsp')->insert($array);
            }
            foreach ($documentDetails as $row) {
                $array = array(
                    'bus_app_id' => $id,
                    'verified' => $row['Include'],
                    'doc_id' => $row['doc_id'],
                    'doc_description' => $row['Document Description'],
                    'date_issued' => $row['Date Issued'],
                    'verified_by' => $row['Verified By'],
                    'verified_by_ID' => $row['Verified By ID'],
                    'dept' => $row['Dept ID'],
                );
                DB::table($this->lgu_db . '.ebplo_verification_docs')->insert($array);
            }
        } else if ($businessMain['application_type'] === 'Transfer') {
            if ($transfType === 'Ownership') {
                $updatebussapp = array(
                    'transaction_type' => $businessMain['application_type'],
                    'transfer' => $transfType,
                    'transfer_tax_pyer_id' => $taxpayerData['tp_taxpayer_nameid'],
                    'transfer_owner_id' => $taxpayerData['tp_president_treasurerid'],
                    'OWNER' => $transferowner['T_owner'],
                    'president_name' => $transferowner['T_president_name'],
                );
                DB::table($this->lgu_db . '.ebplo_business_application')->where('business_app_id', $transferowner['business_app_id'])->update($updatebussapp);
                // dd($updatebussapp);
                $inserttransferowner = array(
                    'business_app_id' => $transferowner['business_app_id'],
                    'OWNER' => $taxpayerData['tp_taxpayer_nameid'],
                    'president_name' => $taxpayerData['tp_president_treasurerid'],
                    'bookeeper' => $taxpayerData['tp_representative_bookkeeperid'],
                    'T_owner' => $transferowner['T_owner'],
                    'T_president_name' => $transferowner['T_president_name'],
                    'T_bookeeper' => $transferowner['T_bookeeper'],
                );
                DB::table($this->lgu_db . '.ebplo_business_application_trasfer_owner')->insert($inserttransferowner);
            } else {
                $updatebussapp = array(
                    'transaction_type' => $businessMain['application_type'],
                    'transfer' => $businessMain['application_type'],
                    'business_address' => $taxpayerData['tp_businessadd_houseno'] . ' ' . $taxpayerData['tp_businessadd_bldgname'] . ' ' . $taxpayerData['tp_businessadd_unitno'] . ', ' . $taxpayerData['tp_businessadd_street'] . ' ' . $taxpayerData['tp_businessadd_subdivision'] . ', ' . $taxpayerData['tp_businessadd_barangay'] . ', ' . $taxpayerData['tp_businessadd_citymun'] . ' ' . $taxpayerData['tp_businessadd_province'],
                    'brgy_address' => $taxpayerData['tp_businessadd_barangayid'],
                );
                DB::table($this->lgu_db . '.ebplo_business_application')->where('business_app_id', $tansferlocation['bappid'])->update($updatebussapp);
                $inserttransferloc = array(
                    'bappid' => $tansferlocation['bappid'],
                    'business_add_blk' => $taxpayerData['tp_businessadd_houseno'],
                    'business_add_lot' => $taxpayerData['tp_businessadd_unitno'],
                    'business_add_bldg' => $taxpayerData['tp_businessadd_bldgname'],
                    'business_add_street' => $taxpayerData['tp_businessadd_street'],
                    'business_add_brgy' => $taxpayerData['tp_businessadd_barangay'],
                    'business_add_subd' => $taxpayerData['tp_businessadd_subdivision'],
                    'business_add_city' => $taxpayerData['tp_businessadd_citymun'],
                    'business_add_prov' => $taxpayerData['tp_businessadd_province'],
                    'business_add_telno' => $taxpayerData['tp_businessadd_contactno'],
                    'business_add_email' => $taxpayerData['tp_businessadd_emailadd'],
                    'transfer_add_blk' => $tansferlocation['transfer_add_blk'],
                    'transfer_add_lot' => $tansferlocation['transfer_add_lot'],
                    'transfer_add_bldg' => $tansferlocation['transfer_add_bldg'],
                    'transfer_add_street' => $tansferlocation['transfer_add_street'],
                    'transfer_add_brgy' => $tansferlocation['transfer_add_brgy'],
                    'transfer_add_subd' => $tansferlocation['transfer_add_subd'],
                    'transfer_add_city' => $tansferlocation['transfer_add_city'],
                    'transfer_add_prov' => $tansferlocation['transfer_add_prov'],
                    'transfer_add_telno' => $tansferlocation['transfer_add_telno'],
                    'transfer_add_email' => $tansferlocation['transfer_add_email'],
                );
                DB::table($this->lgu_db . '.ebplo_business_application_transfer_loc')->insert($inserttransferloc);
                $newBusinessDetails = array(
                    'business_no' => $taxpayerData['business_id'],
                    'capitalization' => $businessData['gross_capital_total'],
                    'gross_sales' => 0,
                    'new_gross_sales' => 0,
                    'lessor_name' => $lessorData['lessor_tp_name'],
                    'business_add_blk' => $taxpayerData['tp_businessadd_houseno'],
                    'business_add_lot' => $taxpayerData['tp_businessadd_unitno'],
                    'business_add_bldg' => $taxpayerData['tp_businessadd_bldgname'],
                    'business_add_street' => $taxpayerData['tp_businessadd_street'],
                    'business_add_brgy' => $taxpayerData['tp_businessadd_barangay'],
                    'business_add_subd' => $taxpayerData['tp_businessadd_subdivision'],
                    'business_add_city' => $taxpayerData['tp_businessadd_citymun'],
                    'business_add_prov' => $taxpayerData['tp_businessadd_province'],
                    'business_add_telno' => $taxpayerData['tp_businessadd_contactno'],
                    'business_add_email' => $taxpayerData['tp_businessadd_emailadd'],
                    'owner_add_blk' => $taxpayerData['tp_homeadd_houseno'],
                    'owner_add_lot' => $taxpayerData['tp_homeadd_unitno'],
                    'owner_add_bldg' => $taxpayerData['tp_homeadd_bldgname'],
                    'owner_add_street' => $taxpayerData['tp_homeadd_street'],
                    'owner_add_brgy' => $taxpayerData['tp_homeadd_barangay'],
                    'owner_add_subd' => $taxpayerData['tp_homeadd_subdivision'],
                    'owner_add_city' => $taxpayerData['tp_homeadd_citymun'],
                    'owner_add_prov' => $taxpayerData['tp_homeadd_province'],
                    'owner_add_telno' => $taxpayerData['tp_homeadd_contactno'],
                    'owner_add_email' => $taxpayerData['tp_homeadd_emailadd'],
                    'lessor_add_blk' => $lessorData['lessor_tp_bldgno'],
                    'lessor_add_lot' => $lessorData['lessor_tp_unitno'],
                    'lessor_add_bldg' => $lessorData['lessor_tp_bldgname'],
                    'lessor_add_street' => $lessorData['lessor_tp_street'],
                    'lessor_add_brgy' => $lessorData['lessor_tp_barangay'],
                    'lessor_add_subd' => $lessorData['lessor_tp_subdivision'],
                    'lessor_add_city' => $lessorData['lessor_tp_citymun'],
                    'lessor_add_prov' => $lessorData['lessor_tp_province'],
                    'lessor_add_telno' => $lessorData['lessor_tp_telno'],
                    'lessor_add_email' => $lessorData['lessor_tp_emailadd'],
                    'SSS' => $taxpayerData['tp_sss_no'],
                    'in_case_telno' => $lessorData['lessor_tp_emergencytelno'],
                    'in_case_mobile' => $lessorData['lessor_tp_emergencymobileno'],
                    'in_case_email' => $lessorData['lessor_tp_emergencyemailadd'],
                );
                DB::table($this->lgu_db . '.ebplo_business_application_detail')->insert($newBusinessDetails);
            };
        } else if ($businessMain['application_type'] === 'Amend') {
            $updatebussapp = array(
                'transaction_type' => $businessMain['application_type'],
                'ammendment' => $businessMain['from_organization_type'],
                'organization_type' => $businessMain['to_organization_type'],
            );
            DB::table($this->lgu_db . '.ebplo_business_application')->where('business_app_id', $businessMain['business_app_id'])->update($updatebussapp);
        };
    }
    public function update($idx, $busNo, $businessMain, $taxpayerData, $employeeData, $employeeDetails, $businessData, $businessDetails, $lessorData, $bspDetails, $documentDetails)
    {
        if ($businessMain['app_bmbe'] == "Yes") {
            $bmbe = 'True';
        } else {
            $bmbe = 'False';
        };
        if ($businessMain['reg_bsp'] == "Yes") {
            $bsp = 'True';
        } else {
            $bsp = 'False';
        };
        if ($businessMain['business_rented'] == "Yes") {
            $rented = 'False';
        } else {
            $rented = 'True';
        };
        $newBusinessList = array(
            'business_name' => $taxpayerData['tp_business_name'],
            'trade_name' => $taxpayerData['tp_tradename_franchise'],
            'registered' => 'YES',
            'brgy_id' => $taxpayerData['tp_businessadd_barangayid'],
            'business_address_temp' => $taxpayerData['tp_businessadd_houseno'] . ' ' . $taxpayerData['tp_businessadd_bldgname'] . ' ' . $taxpayerData['tp_businessadd_unitno'] . ', ' . $taxpayerData['tp_businessadd_street'] . ' ' . $taxpayerData['tp_businessadd_subdivision'] . ', ' . $taxpayerData['tp_businessadd_barangay'] . ', ' . $taxpayerData['tp_businessadd_citymun'] . ' ' . $taxpayerData['tp_businessadd_province'],
            'business_contact_no_temp' => $taxpayerData['tp_businessadd_contactno'],
            'business_email_add' => $taxpayerData['tp_businessadd_emailadd'],
            'reference_owner' => $taxpayerData['tp_taxpayer_nameid'],
            'reference_address' => $taxpayerData['tp_homeadd_houseno'] . ' ' . $taxpayerData['tp_homeadd_bldgname'] . ' ' . $taxpayerData['tp_homeadd_unitno'] . ', ' . $taxpayerData['tp_homeadd_street'] . ' ' . $taxpayerData['tp_homeadd_subdivision'] . ', ' . $taxpayerData['tp_homeadd_barangay'] . ', ' . $taxpayerData['tp_homeadd_citymun'] . ' ' . $taxpayerData['tp_homeadd_province'],
            'reference_org' => $businessMain['organization_type']
        );
        DB::table($this->lgu_db . '.ebplo_business_list')->where('business_number', $busNo)->update($newBusinessList);
        $newBusiness = array(
            'business_number' => $busNo,
            'tax_year' => date("Y", strtotime($taxpayerData['tp_application_date'])),
            'application_date' => $taxpayerData['tp_application_date'],
            'permit_status' => 'N',
            'permit_no' => $businessMain['permit_no'],
            'permit_no1' => $businessMain['permit_no1'],
            'busAccntNo' => $businessMain['business_account_no'],
            'business_name' => $taxpayerData['tp_business_name'],
            'trade_name' => $taxpayerData['tp_tradename_franchise'],
            'transaction_type' => $businessMain['application_type'],
            'application_type' => 'NEW',
            'BMBE' => $bmbe,
            'BMBE_no' => $businessMain['bmbe_no'],
            'BSP' => $bsp,
            'BSP_no' => $businessMain['bsp_no'],
            'with_property' => $rented,
            'status' => 'ACTIVE',
            'business_status' => 'PENDING',
            'bstatus_beforeTerm' => '',
            'transfer' => '',
            'transfer_tax_pyer_id' => 0,
            'transfer_owner_id' => 0,
            'ammendment' => '',
            'place_of_issuance' => '',
            'PIN' => '',
            'PIC' => '',
            'organization_type' => $businessMain['organization_type'],
            'pca_no' => $taxpayerData['tp_pca_no'],
            'nfa_no' => $taxpayerData['tp_nfa_no'],
            'dti_reg_no' => $taxpayerData['tp_dtisec_regno'],
            'dti_reg_date' => $taxpayerData['tp_dtisec_regdate'],
            'dti_expiry_date' => $taxpayerData['tp_dtisec_expdate'],
            'entity' => $taxpayerData['tp_taxpayer_noincentive'],
            'owner' => $taxpayerData['tp_taxpayer_nameid'],
            'president_name' => $taxpayerData['tp_president_treasurerid'],
            'business_address' => $taxpayerData['tp_businessadd_houseno'] . ' ' . $taxpayerData['tp_businessadd_bldgname'] . ' ' . $taxpayerData['tp_businessadd_unitno'] . ', ' . $taxpayerData['tp_businessadd_street'] . ' ' . $taxpayerData['tp_businessadd_subdivision'] . ', ' . $taxpayerData['tp_businessadd_barangay'] . ', ' . $taxpayerData['tp_businessadd_citymun'] . ' ' . $taxpayerData['tp_businessadd_province'],
            'brgy_address' => $taxpayerData['tp_businessadd_barangayid'],
            'owners_address' => $taxpayerData['tp_homeadd_houseno'] . ' ' . $taxpayerData['tp_homeadd_bldgname'] . ' ' . $taxpayerData['tp_homeadd_unitno'] . ', ' . $taxpayerData['tp_homeadd_street'] . ' ' . $taxpayerData['tp_homeadd_subdivision'] . ', ' . $taxpayerData['tp_homeadd_barangay'] . ', ' . $taxpayerData['tp_homeadd_citymun'] . ' ' . $taxpayerData['tp_homeadd_province'],
            'bookeeper' => $taxpayerData['tp_representative_bookkeeperid'],
            'reference_no' => $taxpayerData['tp_reference_no'],
            'ctc_no' => $taxpayerData['tp_ctc_no'],
            'TIN' => $taxpayerData['tp_tin_no'],
            'SSS' => $taxpayerData['tp_sss_no'],
            'contact_no' => $taxpayerData['tp_businessadd_contactno'],
            'email_address' => $taxpayerData['tp_businessadd_emailadd'],
            'employee_residing' => $employeeData['empinfo_lguresident_cnt'],
            'total_employee' => $employeeData['empinfo_employee_cnt'],
            'total_male' => $employeeData['empinfo_male_cnt'],
            'total_female' => $employeeData['empinfo_female_cnt'],
            'no_delivery_units' => $taxpayerData['tp_delivery_units'],
            'business_area' => $taxpayerData['tp_business_area'],
            'bus_activity' => $taxpayerData['tp_office_type'],
            'other_specify' => $taxpayerData['tp_office_others'] . '',
            'lessor' => $lessorData['lessor_tp_id'],
            'lessor_address' => $lessorData['lessor_tp_bldgno'] . ' ' . $lessorData['lessor_tp_bldgname'] . ' ' . $lessorData['lessor_tp_unitno'] . ', ' . $lessorData['lessor_tp_street'] . ' ' . $lessorData['lessor_tp_barangay'] . ', ' . $lessorData['lessor_tp_subdivision'] . ', ' . $lessorData['lessor_tp_citymun'] . ' ' . $lessorData['lessor_tp_province'],
            'contact_person' => $lessorData['lessor_tp_contactperson'],
            'monthly_rental' => $lessorData['lessor_tp_monthlyrental'],
            'tel_no' => $lessorData['lessor_tp_emergencytelno'],
            'mode_of_payment' => $businessMain['modeof_payment'],
            'gross_sales_capitalization' => $businessData['gross_capital_total'],
            'new_capital' => 0,
            'application_from' => 'NORMAL',
            'payment_status' => 'For Assessment',
            'td_id' => $businessMain['taxdec_id'],
            'business_president' => $taxpayerData['tp_president_treasurer'],
        );
        DB::table($this->lgu_db . '.ebplo_business_application')->where('business_app_id', $idx)->update($newBusiness);
        $newBusinessDetails = array(
            'business_no' => $busNo,
            'capitalization' => $businessData['gross_capital_total'],
            'gross_sales' => 0,
            'new_gross_sales' => 0,
            'lessor_name' => $lessorData['lessor_tp_name'],
            'business_add_blk' => $taxpayerData['tp_businessadd_houseno'],
            'business_add_lot' => $taxpayerData['tp_businessadd_unitno'],
            'business_add_bldg' => $taxpayerData['tp_businessadd_bldgname'],
            'business_add_street' => $taxpayerData['tp_businessadd_street'],
            'business_add_brgy' => $taxpayerData['tp_businessadd_barangay'],
            'business_add_subd' => $taxpayerData['tp_businessadd_subdivision'],
            'business_add_city' => $taxpayerData['tp_businessadd_citymun'],
            'business_add_prov' => $taxpayerData['tp_businessadd_province'],
            'business_add_telno' => $taxpayerData['tp_businessadd_contactno'],
            'business_add_email' => $taxpayerData['tp_businessadd_emailadd'],
            'owner_add_blk' => $taxpayerData['tp_homeadd_houseno'],
            'owner_add_lot' => $taxpayerData['tp_homeadd_unitno'],
            'owner_add_bldg' => $taxpayerData['tp_homeadd_bldgname'],
            'owner_add_street' => $taxpayerData['tp_homeadd_street'],
            'owner_add_brgy' => $taxpayerData['tp_homeadd_barangay'],
            'owner_add_subd' => $taxpayerData['tp_homeadd_subdivision'],
            'owner_add_city' => $taxpayerData['tp_homeadd_citymun'],
            'owner_add_prov' => $taxpayerData['tp_homeadd_province'],
            'owner_add_telno' => $taxpayerData['tp_homeadd_contactno'],
            'owner_add_email' => $taxpayerData['tp_homeadd_emailadd'],
            'lessor_add_blk' => $lessorData['lessor_tp_bldgno'],
            'lessor_add_lot' => $lessorData['lessor_tp_unitno'],
            'lessor_add_bldg' => $lessorData['lessor_tp_bldgname'],
            'lessor_add_street' => $lessorData['lessor_tp_street'],
            'lessor_add_brgy' => $lessorData['lessor_tp_barangay'],
            'lessor_add_subd' => $lessorData['lessor_tp_subdivision'],
            'lessor_add_city' => $lessorData['lessor_tp_citymun'],
            'lessor_add_prov' => $lessorData['lessor_tp_province'],
            'lessor_add_telno' => $lessorData['lessor_tp_telno'],
            'lessor_add_email' => $lessorData['lessor_tp_emailadd'],
            'SSS' => $taxpayerData['tp_sss_no'],
            'in_case_telno' => $lessorData['lessor_tp_emergencytelno'],
            'in_case_mobile' => $lessorData['lessor_tp_emergencymobileno'],
            'in_case_email' => $lessorData['lessor_tp_emergencyemailadd'],
        );
        DB::table($this->lgu_db . '.ebplo_business_application_detail')->where('business_no', $idx)->update($newBusinessDetails);
        DB::table($this->lgu_db . '.ebplo_business_occ_fees_app')->where('bappid', $idx)->delete();
        foreach ($employeeDetails as $row) {
            $array = array(
                'bappid' => $idx,
                'occ_id' => $row['descriptionid'],
                'no_emp' => $row['noofemployees'],
                'remarks' => $row['remarks'],
            );
            DB::table($this->lgu_db . '.ebplo_business_occ_fees_app')->insert($array);
        }
        DB::table($this->lgu_db . '.ebplo_business_kind_business')->where('bappid', $idx)->delete();
        foreach ($businessDetails as $row) {
            $array = array(
                'bappid' => $idx,
                'kind_id' => $row['descriptionid'],
                'description' => $row['description'],
                'remarks' => $row['remarks'],
                'cp_gs' => $row['gross'],
                'app_type' => 'NEW',
            );
            DB::table($this->lgu_db . '.ebplo_business_kind_business')->insert($array);
        }
        DB::table($this->lgu_db . '.ebplo_application_bsp')->where('bus_id', $idx)->delete();
        foreach ($bspDetails as $row) {
            $array = array(
                'bsp_id' => $row,
                'bus_id' => $idx,
            );
            DB::table($this->lgu_db . '.ebplo_application_bsp')->insert($array);
        }
        DB::table($this->lgu_db . '.ebplo_verification_docs')->where('bus_app_id', $idx)->delete();
        foreach ($documentDetails as $row) {
            $array = array(
                'bus_app_id' => $idx,
                'verified' => $row['Include'],
                'doc_id' => $row['doc_id'],
                'doc_description' => $row['Document Description'],
                'date_issued' => $row['Date Issued'],
                'verified_by' => $row['Verified By'],
                'verified_by_ID' => $row['Verified By ID'],
                'dept' => $row['Dept ID'],
            );
            DB::table($this->lgu_db . '.ebplo_verification_docs')->insert($array);
        };
    }
    public function delete(Request $request)
    {
        $id = $request->id;
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.ebplo_business_application')->where('business_app_id', $id)->update($data);
        $reason['Form_name'] = 'Business Application - New';
        $reason['Trans_ID'] = $id;
        $reason['Type_'] = 'Cancel Record';
        $reason['Trans_by'] = Auth::user()->id;
        $this->G->insertReason($reason);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function printBusinessApplicationForm($id)
    {
        $data = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_application_form(?)', array($id));
        // dd($data);
        $dataKindOfBusiness = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_kind_form(?)', array($id));
        $dataDocuments = DB::select('call ' . $this->lgu_db . '.balodoy_get_business_document_form(?)', array($id));
        $TransfertypeOWNER = '';
        $TransfertypeLOC = '';

        foreach ($data as $row) {
            $info = ($row);
        }
        if ($info->{'Transaction Type'} == "NEW" or $info->{'Transaction Type'} == "New") {
            $TransNew = 'X';
        } else {
            $TransNew = '';
        };
        if ($info->{'Transaction Type'} == "RENEW" or $info->{'Transaction Type'} == "Renew") {
            $TransRenew = 'X';
        } else {
            $TransRenew = '';
        };
        if ($info->{'Transaction Type'} == "AMEND" or $info->{'Transaction Type'} == "Amend") {
            $TransAmend = 'X';
        } else {
            $TransAmend = '';
        };
        if ($info->{'Transaction Type'} == "TRANSFER" or $info->{'Transaction Type'} == "Transfer") {
            $TransTransfer = 'X';
            if ($info->{'Transfer Type'} == "Ownership" or $info->{'Transfer Type'} == "OWNERSHIP") {
                $TransfertypeOWNER = 'x';
            } else {
                $TransfertypeOWNER = '';
            }
            if ($info->{'Transfer Type'} == "Location" or $info->{'Transfer Type'} == "LOCATION") {
                $TransfertypeLOC = 'x';
            } else {
                $TransfertypeLOC = '';
            }
        } else {
            $TransTransfer = '';
        };
        if ($info->{'Transaction Type'} == "ADDITIONAL" or $info->{'Transaction Type'} == "Additional") {
            $TransAdditional = 'X';
        } else {
            $TransAdditional = '';
        };
        // if ($info->{'Transaction Type'} == "Others") {
        //     $TransOthers = 'X';
        // } else {
        //     $TransOthers = '';
        // };
        if ($info->{'Mode of Payment'} == "Annually") {
            $ModeAnnual = 'X';
        } else {
            $ModeAnnual = '';
        };
        if ($info->{'Mode of Payment'} == "Bi-Annually") {
            $ModeBiAnnual = 'X';
        } else {
            $ModeBiAnnual = '';
        };
        if ($info->{'Mode of Payment'} == "Quarterly") {
            $ModeQuarter = 'X';
        } else {
            $ModeQuarter = '';
        };
        if ($info->{'Tax Incentive'} !== "") {
            $TaxIncentiveNo = 'X';
            $TaxIncentiveYes = '';
        } else {
            $TaxIncentiveYes = 'X';
            $TaxIncentiveNo = '';
        };

        $fromorg = '';
        if ($info->{'Transaction Type'} == "AMEND" or $info->{'Transaction Type'} == "Amend") {
            $fromorg = $info->{'Organization Type'};
        } else {
            $fromorg = '';
        };

        $logo = config('variable.logo');
        try {
            $first_page = '<body>
        ' . $logo . '
        <table width ="100%">       
        <tr style="height:25px">                        
            <th style="width:50%" align="right">
            Tax Year
            </th>
            <th style="width:5%" align="center">
            <b>' . $info->{'Tax Year'} . '</b>
            </th>
            <th style="width:45%" align="left">           
            </th>
        </tr>
        <tr style="height:25px">                        
            <th style="width:45%" align="right">
            Application No. :
            </th>
            <th style="width:25%" align="center">
            <b>' . $info->{'Business Account No'} . '</b>
            </th>
            <th style="width:30%" align="left">           
            </th>                                
        </tr>             
        <tr style="height:25px">
            <th style="width:100%; border-top-style: 1px solid black" align="left">                      
            </th>                             
        </tr> 
  </table>  
    <table cellspacing="3">
    	<tr>
    	    <td style="width:5%"></td>            
            <td style="width:5%" align="center" border="1">' . $TransNew . '</td>
            <td style="width:30%" align="left">New</td>
            <td style="width:5%" align="center" border="1">' . $TransAmend . '</td>
            <td style="width:30%" align="left">Amendment :</td>
            <td style="width:25%" align="left">Mode of Payment :</td>
    	</tr>
    	<tr>
            <td style="width:5%"></td>            
            <td style="width:5%" align="center" border="1">' . $TransRenew . '</td>
            <td style="width:37%" align="left">Renewal</td>
            <td style="width:6%" align="left">From :</td>
            <td style="width:24%" align="left">' . $fromorg . '</td>
            <td style="width:5%" align="center" border="1">' . $ModeAnnual . '</td>
            <td style="width:20%" align="left">Annually</td>
        </tr> 
        <tr>
            <td style="width:5%"></td>            
            <td style="width:5%" align="center" border="1">' . $TransAdditional . '</td>
            <td style="width:37%" align="left">Additional</td>
            <td style="width:6%" align="left">To :</td>
            <td style="width:24%" align="left">' . $info->{'Amendment Org Type'} . '</td>
            <td style="width:5%" align="center" border="1">' . $ModeBiAnnual . '</td>
            <td style="width:20%" align="left">Bi-Annually</td>
        </tr> 
        <tr>
            <td style="width:5%"></td>            
            <td style="width:5%" align="center" border="1">' . $TransTransfer . '</td>
            <td style="width:40%" align="left">Transfer</td>
            <td style="width:8%" align="left"></td>
            <td style="width:19%" align="left"></td>
            <td style="width:5%" align="center" border="1">' . $ModeQuarter . '</td>
            <td style="width:20%" align="left">Quarterly</td>
        </tr> 
        <tr>
            <td style="width:12%"></td>            
            <td style="width:4%" align="center" border="1">' . $TransfertypeOWNER . '</td>
            <td style="width:40%" align="left">Ownership </td> 
        </tr> 
        <tr>
            <td style="width:12%"></td>            
            <td style="width:4%" align="center" border="1">' . $TransfertypeLOC . '</td>
            <td style="width:40%" align="left">Location </td> 
        </tr> 
    </table> 
        <br><br>
    <table>  
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Date of Application:</th>
            <th style="width:10%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . strtoupper(date("m/d/Y", strtotime($info->{'Application Date'}))) . '</th>
            <th style="width:30%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> DTI/SEC/CDA Registration No:</th> 
            <th style="width:15%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'DTI Reg. No'} . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> CTC No:</th> 
            <th style="width:15%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'CTC No'} . '</th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Reference No:</th>
            <th style="width:10%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Reference No'} . '</th>
            <th style="width:30%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> DTI/SEC/CDA Date of Registration:</th> 
            <th style="width:15%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . strtoupper(date("m/d/Y", strtotime($info->{'DTI Reg. Date'}))) . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> TIN:</th> 
            <th style="width:15%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'TIN'} . '</th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-bottom:0.5px solid black;text-align:left"></th>
            <th style="width:10%;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:left"></th>
            <th style="width:30%;border-left:0.5px solid black;border-bottom:0.5px solid black;text-align:left"></th> 
            <th style="width:15%;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:left"></th>
            <th style="width:10%;border-left:0.5px solid black;border-bottom:0.5px solid black;text-align:left"> SSS:</th> 
            <th style="width:15%;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:left">' . $info->{'SSS No'} . '</th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-bottom:0.5px solid black;text-align:left"> Type of Organization:</th>
            <th style="width:80%;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:left">' . $info->{'Organization Type'} . '</th>
        </tr>
        <tr>
            <th style="width:48%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Are you enjoying tax incentive from any Government Entity?</th>
            <th style="width:2%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"> (</th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;text-align:center">' . $TaxIncentiveYes . '</th>
            <th style="width:2%;border-top:0.5px solid black;text-align:left"> )</th>
            <th style="width:4%;border-top:0.5px solid black;text-align:left"> Yes</th>
            <th style="width:2%;border-top:0.5px solid black;text-align:left"> (</th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;text-align:center">' . $TaxIncentiveNo . '</th>
            <th style="width:2%;border-top:0.5px solid black;text-align:left"> )</th>
            <th style="width:4%;border-top:0.5px solid black;text-align:left"> No</th>
            <th style="width:30%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>            
        </tr>
        <tr>
        <th style="width:6%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> If No,</th>
        <th style="width:24%;border-top:0.5px solid black;text-align:left"> Please specify the entity:</th>
        <th style="width:70%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Tax Incentive'} . '</th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> <b>Name of Taxpayer:</b></th>
            <th style="width:80%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Business Owner'} . '</th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> <b>Business Name:</b></th>
            <th style="width:80%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Business Name'} . '</th>
        </tr>
        <tr>
            <th style="width:30%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> <b>Trade Name / Franchise :</b></th>
            <th style="width:70%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Trade Name'} . '</th>
        </tr>
        <tr>
            <th style="width:40%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> <b>Name of President/Treasurer of Corporation:</b></th>
            <th style="width:60%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'President Name'} . '</th>
        </tr>
        <tr>
            <th style="width:50%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center"> <b>Business Address</b></th>
            <th style="width:50%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:center"> <b>Owner&#8217;s Address</b></th>
        </tr>
        <tr>
            <th style="width:18%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> House No./Bldg. No.:</th>
            <th style="width:32%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Building No'} . '</th>
            <th style="width:18%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> House No./Bldg. No.:</th>
            <th style="width:32%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Building No'} . '</th>
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Building Name:</th>
            <th style="width:35%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Building Name'} . '</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Building Name:</th>
            <th style="width:35%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Building Name'} . '</th>
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Unit No.:</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Unit No'} . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Unit No.:</th>
            <th style="width:40%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Unit No'} . '</th>
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Street:</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Street'} . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Street:</th>
            <th style="width:40%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Street'} . '</th>
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Barangay:</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Barangay'} . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Barangay:</th>
            <th style="width:40%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Barangay'} . '</th>
        </tr>
        <tr>
            <th style="width:13%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Subdivision:</th>
            <th style="width:37%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Subdivision'} . '</th>
            <th style="width:13%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Subdivision:</th>
            <th style="width:37%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Subdivision'} . '</th>
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> City/Municipality:</th>
            <th style="width:35%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. City/Mun'} . '</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> City/Municipality:</th>
            <th style="width:35%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner City/Mun'} . '</th>
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Province:</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Province'} . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Province:</th>
            <th style="width:40%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Province'} . '</th>
        </tr>
        <tr>
            <th style="width:12%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Contact No.:</th>
            <th style="width:38%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Tel. No'} . '</th>
            <th style="width:12%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Contact No.:</th>
            <th style="width:38%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Tel. No'} . '</th>
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Email Address:</th>
            <th style="width:35%;border-top:0.5px solid black;text-align:left">' . $info->{'Bus. Email Add'} . '</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Email Address:</th>
            <th style="width:35%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Owner Email Add'} . '</th>
        </tr>
        <tr>
            <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Property Index Number (PIN):</th>
            <th style="width:25%;border-top:0.5px solid black;text-align:left">' . $info->{'PIN'} . '</th>
            <th style="width:30%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> # of Employees Residing in LGU:</th>
            <th style="width:20%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'No. of Employee Residence'} . '</th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Business Area (in sqm):</th>
            <th style="width:30%;border-top:0.5px solid black;text-align:left">' . $info->{'Business Area'} . '</th>
            <th style="width:35%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Total No. of Employees in Establishment:</th>
            <th style="width:15%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Total No. of Employee'} . '</th>
        </tr>
        <tr>
            <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> No of Delivery Units:</th>
            <th style="width:25%;border-top:0.5px solid black;text-align:left">' . $info->{'No. Delivery Units'} . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Male:</th>
            <th style="width:15%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Total Male'} . '</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Female:</th>
            <th style="width:15%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Total Female'} . '</th>
        </tr>
        <tr>
            <th style="width:50%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> If Place of Business is Rented, please identify the following: </th>
            <th style="width:50%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Rented'} . '</th>           
        </tr>
        <tr>
            <th style="width:13%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Lessor Name:</th>
            <th style="width:37%;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Name'} . '</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Monthly Rental:</th>
            <th style="width:35%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Monthly Rental'} . '</th>
        </tr> 
        <tr>
            <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:center"> <b>Lessor&#8217;s Address</b></th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> House No./Bldg. No.:</th>
            <th style="width:30%;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Building/Unit No'} . '</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Subdivision:</th>
            <th style="width:35%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Subdivision'} . '</th>
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Street:</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Street'} . '</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> City/Municipality:</th>
            <th style="width:35%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor City/Mun'} . '</th>
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Barangay:</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Barangay'} . '</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Province:</th>
            <th style="width:35%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Province'} . '</th>
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Tel No.:</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Tel. No'} . '</th>
            <th style="width:13%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> Email Address:</th>
            <th style="width:37%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Email Add'} . '</th>
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> In case of Emergency:</th>
            <th style="width:80%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left">' . $info->{'Lessor Contact Person'} . '</th>           
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;text-align:left"> <b>Business Activity:</b></th>
            <th style="width:85%;border-right:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;text-align:left">' . $info->{'Business Activity'} . '</th>           
        </tr>
        <thead>
            <tr style="height:25px">                                                
                <th style="width:60%" align="center" border="1">
                <b>Line of Business</b>
                </th>
                <th style="width:20%" align="center" border="1">
                <b>Capital</b>
                </th>
                <th style="width:20%" align="center" border="1">
                <b>Gross Sales</b>
                </th>                                          
            </tr>
        </thead>
        <tbody>';
            foreach ($dataKindOfBusiness as $row) {
                $first_page .= '
            <tr>         
                <td style="width:60%" align="left" border="1">' . $row->{'Line of Business'} . '</td>
                <td style="width:20%" align="right" border="1">' . $row->{'Capital'} . '</td>
                <td style="width:20%" align="right" border="1"> 0.00</td>
            </tr>';
            }
            for ($x = 0; $x < 6; $x++) {
                $first_page .= '        
            <tr>         
                <td style="width:60%" align="left" border="1"></td>
                <td style="width:20%" align="left" border="1"></td>
                <td style="width:20%" align="left" border="1"></td>                                 
            </tr>';
            }
            $first_page .= '
        </tbody>    
    </table>';
            $second_page =
                '<table width ="100%">  
    <tr style="height:25px">                        
        <th style="width:100%" align="Center">
            <i><b>Oath of Undertaking:</b></i>
        </th>                         
    </tr> 
    <tr style="height:25px">
        <th style="width:5%" align="left">      
        </th>                          
        <th style="width:95%" align="left">
        <i><b>I undertake to comply with the regulatory requirement and other deficiencies within 30 days from release of the</b></i>
        </th>                 
    </tr>
    <tr style="height:25px"> 
        <th style="width:15%" align="left">
        <i><b>Business Permit.</b></i>
        </th>
        <th style="width:85%" align="left">      
        </th>            
    </tr>
    <br>
    <br>
    <tr style="height:25px">                        
        <th style="width:48%;border-bottom:0.5px solid black"; align="center">
        ' . $info->{'President Name'} . '
        </th>
        <th style="width:4%" align="center">           
        </th>
        <th style="width:48%;border-bottom:0.5px solid black"; align="center">
        </th>                                       
    </tr>
    <tr style="height:25px">                        
        <th style="width:50%" align="center">
        SIGNATURE OF APPLICANT OVER PRINTED NAME:
        </th>
        <th style="width:50%" align="center">
        POSITION/TITLE:
        </th>                                       
    </tr>
    <tr style="height:25px">                        
        <th style="width:100%" align="Center">
        Annex 1 (Page 2 of 2): Application Form Business Permit
        </th>                         
    </tr>        
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"> <b>I. VERIFICATION OF DOCUMENTS</b></th>
    </tr>  
    <thead>
        <tr style="height:25px">                                                
            <th style="width:30%" align="center" border="1">
            <b>Description</b>
            </th>
            <th style="width:30%" align="center" border="1">
            <b>Department/Office Agency</b>
            </th>
            <th style="width:15%" align="center" border="1">
            <b>Date Issued</b>
            </th>
            <th style="width:25%" align="center" border="1">
            <b>Verified By:(BPLO Staff)</b>
            </th>                                          
        </tr>
    </thead>    
    <tbody>';

            foreach ($dataDocuments as $row) {
                $second_page .= '
            <tr>      
                <td style="width:30%" align="left" border="1">' . $row->{'Document Description'} . '</td>
                <td style="width:30%" align="left" border="1">' . $row->{'Department/Office/Agency'} . '</td>
                <td style="width:15%" align="center" border="1">' . $row->{'Date Issued'} . '</td>
                <td style="width:25%" align="left" border="1">' . $row->{'Verified By'} . '</td>
            </tr>';
            }
            $second_page .= '
    </tbody>  
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"> <b>I. BUREAU OF FIRE STATION (APPLICATION FOR FIRE SAFETY INSPECTION CERTIFICATE)</b></th>
    </tr>
    <tr>
        <th style="width:50%;border-left:0.5px solid black;text-align:left"></th>
        <th style="width:50%;border-right:0.5px solid black;text-align:left"> <b>Date:</b></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"> <b>Tracking No :</b></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"> (TO BE FILL UP BY APPLICANT/OWNER)</th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:30%;border-left:0.5px solid black;text-align:left"> <b>NAME OF APPLICANT/OWNER:</b></th>
        <th style="width:70%;border-right:0.5px solid black;text-align:left">' . $info->{'Business Owner'} . '</th>
    </tr>
    <tr>
        <th style="width:20%;border-left:0.5px solid black;text-align:left"> <b>NAME OF BUSINESS:</b></th>
        <th style="width:80%;border-right:0.5px solid black;text-align:left">' . $info->{'Business Name'} . '</th>
    </tr>
    <tr>
        <th style="width:25%;border-left:0.5px solid black;text-align:left"> <b>TOTAL FLOOR AREA:</b></th>
        <th style="width:25%;text-align:left">' . $info->{'Business Area'} . '</th>
        <th style="width:15%;text-align:left"> <b>CONTACT NO:</b></th>
        <th style="width:35%;border-right:0.5px solid black;text-align:left">' . $info->{'Bus. Tel. No'} . '</th>
    </tr>
    <tr>
        <th style="width:30%;border-left:0.5px solid black;text-align:left"> <b>ADDRESS OF ESTABLISHMENT:</b></th>
        <th style="width:70%;border-right:0.5px solid black;text-align:left">' . $info->{'Business Address'} . '</th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr> 
    <tr>
        <th style="width:60%;border-left:0.5px solid black;text-align:center">' . $info->{'Business Owner'} . '</th>
        <th style="width:20%;text-align:left">   FIRE SAFETY</th>
        <th style="width:20%;border-right:0.5px solid black;text-align:left"></th>
    </tr> 
    <tr>
        <th style="width:10%;border-left:0.5px solid black;text-align:left"></th>
        <th style="width:40%;border-top:0.5px solid black;text-align:center"> Signature of Applicant/Owner</th>
        <th style="width:10%;text-align:left"></th>
        <th style="width:20%;text-align:left"> ASSESSMENT FEE</th>
        <th style="width:20%;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:20%;border-left:0.5px solid black;text-align:left"> Certified By :</th>
        <th style="width:80%;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:25%;border-left:0.5px solid black;text-align:left"> Customer Relation Officer :</th>
        <th style="width:75%;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:25%;border-left:0.5px solid black;text-align:left"> Time and Date Received :</th>
        <th style="width:75%;border-right:0.5px solid black;text-align:left"></th>
    </tr>    
   
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:left"></th>
    </tr>      
</table>
</body>';

            PDF::SetTitle('Business Application Form');
            PDF::SetFont('times', '', 10);
            PDF::AddPage();
            PDF::SetLineStyle(array('width' => 0.7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
            PDF::Line(8, 8, PDF::getPageWidth() - 8, 8);
            PDF::Line(PDF::getPageWidth() - 8, 7.4, PDF::getPageWidth() - 8, PDF::getPageHeight() - 8);
            PDF::Line(8, PDF::getPageHeight() - 8, PDF::getPageWidth() - 8, PDF::getPageHeight() - 8);
            PDF::Line(8, 7.4, 8, PDF::getPageHeight() - 8);
            PDF::writeHTML($first_page, true, true, true, true, '');
            PDF::AddPage();
            PDF::SetLineStyle(array('width' => 0.7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
            PDF::Line(8, 8, PDF::getPageWidth() - 8, 8);
            PDF::Line(PDF::getPageWidth() - 8, 7.4, PDF::getPageWidth() - 8, PDF::getPageHeight() - 8);
            PDF::Line(8, PDF::getPageHeight() - 8, PDF::getPageWidth() - 8, PDF::getPageHeight() - 8);
            PDF::Line(8, 7.4, 8, PDF::getPageHeight() - 8);
            PDF::writeHTML($second_page, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            dd($e);
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function printBusinessMasterlist(Request $request)
    {
        
        $data = $request->main;
        $filter = $request->filter;
        $filterdisplay = '';
        if ($filter['filter'] == 'Month') {
            $filterdisplay = 'Month of ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Daily') {
            $filterdisplay = 'As of ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Year') {
            $filterdisplay = 'Year ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Range') {
            $filterdisplay = 'As of ' . $filter['reportcaption'];
        }
        
        // dd($filter);
        
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h2 align="center">BUSINESS MASTER LIST</h2>
            <h4 align="center"> ' . $filterdisplay . '</h4>
            <br></br>
            <br></br>
            <br></br>
        <table border="1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 3%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                No.
                <br>
            </th>
            <th rowspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Permit No
                <br>
            </th>
            <th rowspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Business Name
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                 Owner Name
                <br>
            </th> 
            <th colspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   Business Address
                <br>
            </th> 
            <th colspan="2" style="width: 7%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                Application Type
                <br>
            </th> 
            <th colspan="2" style="width: 8%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   Application Date
                <br>
            </th> 
            <th colspan="2" style="width: 8%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Organization Type
                <br>
            </th> 
            <th colspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Nature of Business
                <br>
            </th> 
            <th colspan="2" style="width: 8%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   Gross/Capital
                <br>
            </th> 
            <th colspan="2" style="width: 6%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Tax Amount
                <br>
            </th> 
        </tr>
    </thead>
                    <tbody >';
            $COUNT = 0;
            // dd($data);
            foreach ($data as $row) {

                $html_content .= '
                <tr style="font-family: Arial, font-size: 8pt" align="center">
                <td width="3%"> ' . $COUNT . '</td>
                <td width="10%"> ' . $row['Permit Number'] . '</td>
                <td width="10%" align="left"> ' . $row['Business Name'] . '</td>
                <td width="10%" align="left">' . $row['Owner'] . '</td>
                <td width="15%" align="left">' . $row['Business Address'] . '</td>
                <td width="7%"> ' . $row['Application Type'] . '</td>
                <td width="8%">' . $row['Application Date'] . '</td>
                <td width="8%">' . $row['Organization Type'] . '</td>
                <td width="15%"> ' . $row['Nature of Business'] . '</td>
                <td width="8%">' . $row['Capital/Gross'] . '</td>
                <td width="6%">' . $row['Assessment Amount'] . '</td>
            </tr>';
                $COUNT = $COUNT + 1;
            }
            $html_content .= '</tbody>
            </table>
            <table width="100%">
            <tr>
                <td style="width:98%;text-align:left;border: 1px solid black;">Total Records: ' . $COUNT . '</td>  
            </tr> 
            </table>';

            PDF::SetTitle('Print Master List');
            PDF::Addpage('L',Array(300,400));
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
