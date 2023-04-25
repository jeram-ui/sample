<?php

namespace App\Http\Controllers\Api\Mod_online;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;
use Storage;
use File;
use Exception;
use App\Mail\ResetPasswordMailable;
use App\Mail\approvedEmail;
use App\Mail\disapprovedEmail;
class onlineController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;
    private $Proc;
    private $budget;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->Proc = $this->G->getProcDb();
        $this->Bac = $this->G->getBACDb();
        $this->sched_db = $this->G->getSchedulerDb();
        $this->budget = $this->G->getBudgetDb();
    }
    public function getMasterList($business_number){
        $list = db::table($this->lgu_db.'.ebplo_business_application')
        ->join($this->lgu_db.'.ebplo_business_list','ebplo_business_list.business_number','=','ebplo_business_application.business_number')
        ->where('ebplo_business_application.business_number',$business_number)
        ->get();
        return response()->json(new jsonresponse($list));
    }
   public function getBusinessList(){
       $list = db::table($this->lgu_db.'.ebplo_business_list')
       ->join($this->lgu_db.'.ebplo_business_application','ebplo_business_application.business_number','=','ebplo_business_list.business_number')
       ->where('ol_business_number',0)
       ->where('tax_year',date('Y') -1)
    //    ->where('tax_year',date('Y'))
       ->where('ebplo_business_list.status','ACTIVE')->get();
       return response()->json(new JsonResponse($list));
   }
   public function getAssesment(Request $request){
    $business_number = $request->business_number;
    $taxyear = $request->taxyear;
    $list = db::select('CALL '.$this->lgu_db.'.spl_display_assessment_details_rans(?,?)',[$business_number,$taxyear]);
    return response()->json(new JsonResponse($list));
}
   public function insertBusiness(Request $request){
       $data = $request->selected;
       $datax = array(
        
       );
       db::table($this->lgu_db.'.ebplo_business_list')->insert($datax);
   }
   public function setScheduleDate(Request $request){
   db::table( $this->lgu_db .".ebplo_business_application")
   ->where("business_app_id",$request->business_application_id)
   ->update(['sched_date' =>$request->sched_date]);
   }
   public function getScheduleDate(Request $request){
    $lgu_db = config('variable.db_lgu');
    $data_calendar = DB::table($lgu_db . '.ebplo_business_application')
        ->whereBetween('ebplo_business_application.sched_date', [$request->from, $request->to])->get();
    $calendar = array();
    foreach ($data_calendar as $key => $val) {
        $calendars = array(
            'id'     => intval($val->business_app_id),
            'name' => $val->business_name,
            'start' => date_format(date_create($val->sched_date), "Y-m-d 08:00:00"),
            'end'     => date_format(date_create($val->sched_date), "Y-m-d 17:00:00"),
            'Description' => $val->trade_name,
            'Status' => $val->status,
            'color'=>'blue'
        );

        array_push($calendar, $calendars);
        // dd($calendar);
    }
    // dd($calendar);
    return response()->json(new JsonResponse($calendar));
    }
   public function getApplicant(){
    $list = DB::select('call ' . $this->lgu_db . '.rans_ebplo_display_business_class(?,?)',[date("Y-01-01"),date("Y-12-31")]);
    return response()->json(new JsonResponse($list));
   }
   public function getAppNo($_type){
    $date = $this->G->serverdatetime();
       $ref = db::select("SELECT CONCAT('APP','-',DATE_FORMAT('".$date."','%m'),'-',LPAD(IFNULL(SUBSTRING_INDEX(SUBSTRING_INDEX(appno,'-',3),'-',-1),0)+1,4,0),'-',DATE_FORMAT('".$date."','%Y'))AS 'ref' FROM ".$this->lgu_db.".ebplo_business_application WHERE 
       transaction_type = '".$_type."' and 
       `tax_year` = year('".$date."')
       ORDER BY business_app_id DESC 
       LIMIT 1 ");
       foreach ($ref as $key => $value) {
          return $value->ref;
       }
   }
   
   public function getPermetNo($_type){
    $ref = db::select('call '.$this->lgu_db.'.rans_get_permitno_new(?)',[$_type]);
    foreach ($ref as $key => $value) {
        return $value->PermitNo;
     }
   }

   public function getPermetNo1($_type){
    $ref = db::select('call '.$this->lgu_db.'.rans_get_permitno_new(?)',[$_type]);
    foreach ($ref as $key => $value) {
        return $value->PermitNo1;
     }
   }
   public function getAccountNo($_business_no){
       $chk = db::table($this->lgu_db.'.ebplo_business_application')
       ->where('business_number',$_business_no)->get();
       foreach ($chk as $key => $value) {
        return $value['busAccntNo'];
       }
    $ref = db::select('call '.$this->lgu_db.'.rans_get_business_accountno_new(?)',[$_business_no]);
    foreach ($ref as $key => $value) {
        return $value->code;
     }
   }
   public function approvedBusiness(Request $request){
   try {
    // log::debug($request);
    // db::beginTransaction();
    $reqMain = $request->ebplo_business_application;
    $tag = $request->ebplo_business_application['business_number'];

    $kindOfBusiness =[];
    $occupationalFee =[];
    $details = $request->ebplo_business_application_detail;
 
    // log::debug( $reqMain);
    $bnumber = 0;
    if ($tag > 0) {
        $bnumber = $tag;
        // $datatag = array(
        //     'ol_business_number'=>$reqMain['business_number']
        // );
        // db::table($this->lgu_db.'.ebplo_business_list')
        // ->where('business_number', $tag)
        // ->update($datatag);
    }else{
        $datatag = array(
            'business_name'=>$reqMain['business_name'],
            'trade_name'=>$reqMain['trade_name'],
            'registered'=>'YES',
            // 'business_address_temp'=>$details['business_address'],
            'brgy_id'=>$details['business_add_brgy'],
            'purok_id'=>$details['buss_purok'],
        );
        db::table($this->lgu_db.'.ebplo_business_list')
        ->insert($datatag);
        $bnumber =DB::getPDo()->lastInsertId();
        $datatag = array(
            'ol_business_number'=>$reqMain['business_number']
        );
        // db::table($this->lgu_db.'.ebplo_business_list')
        // ->where('business_number',$bnumber)
        // ->update($datatag);
    }
    $date=$this->G->serverdatetime();
    $taxyear = date("Y", strtotime($date));

    $chkExist =  db::table($this->lgu_db.'.ebplo_business_application')->where('business_number',$bnumber)
    ->where('tax_year',$taxyear)
    ->first();
    $entryType ="save";
    // if ($chkExist) {
    //     $entryType = "update";
    // }else{
    //     $entryType = "save";
    // }
    log::debug($bnumber);
    log::debug($reqMain['transaction_type']);
    log::debug($entryType);
    $main = array(
        'business_number'=>$bnumber,
        'appno'=>$this->getAppNo($reqMain['transaction_type']),
        'tax_year'=>$taxyear,
        'application_date'=>$date,
        // 'permit_no'=>$this->getPermetNo($reqMain['permit_status']),
        // 'permit_no1'=>$this->getPermetNo1($reqMain['permit_status']),
        // 'busAccntNo'=>$this->getAccountNo($bnumber),
        'permit_status'=>$reqMain['transaction_type']=='NEW'?'N':'R',
        'business_name'=>$reqMain['business_name'],
        'trade_name'=>$reqMain['trade_name'],
        'transaction_type'=>$reqMain['transaction_type'],
        'application_type'=>$reqMain['transaction_type'],
        'BMBE'=> $reqMain['BMBE']==0?'false':'true',
        'BMBE_no'=>$reqMain['BMBE_no'],
        'BSP'=>$reqMain['BSP'],
        'BSP_no'=>$reqMain['BSP_no'],
        'with_property'=>$reqMain['with_property'],
        'status'=>"ACTIVE",
        'organization_type'=>$reqMain['organization_type'],
        'pca_no'=>$reqMain['pca_no'],
        'nfa_no'=>$reqMain['nfa_no'],
        'dti_reg_no'=>$reqMain['dti_reg_no'],
        'dti_reg_date'=>$reqMain['dti_reg_date'],
        'dti_expiry_date'=>$reqMain['dti_expiry_date'],
        'business_address'=>$reqMain['business_address'],
        'brgy_address'=>$details['business_add_brgy'],
        'purok_id'=>$details['buss_purok'],
        'owners_address'=>$reqMain['owners_address'],
        'ctc_no'=>$reqMain['ctc_no'],
        'TIN'=>$reqMain['TIN'],
        'SSS'=>$reqMain['SSS'],
        'contact_no'=>$reqMain['contact_no'],
        'email_address'=>$reqMain['email_address'],
        'employee_residing'=>$reqMain['employee_residing'],
        'total_employee'=>$reqMain['total_employee'],
        'total_male'=>$reqMain['total_male'],
        'total_female'=>$reqMain['total_female'],
        'no_delivery_units'=>$reqMain['no_delivery_units'],
        'business_area'=>$reqMain['business_area'],
        'bus_activity'=>$reqMain['bus_activity'],
        'bus_activity_branch'=>$reqMain['bus_activity_branch'],
        'other_specify'=>$reqMain['other_specify'],
        'lessor_address'=>$reqMain['lessor_address'],
        'contact_person'=>$reqMain['contact_person'],
        'monthly_rental'=>$reqMain['monthly_rental'],
        'tel_no'=>$reqMain['tel_no'],
        'mode_of_payment'=>$reqMain['mode_of_payment'],
        'gross_sales_capitalization'=>$reqMain['gross_sales_capitalization'],
        'business_president'=>$reqMain['business_president'],
        'owner'=>$reqMain['owner'],
        'president_name'=>$reqMain['owner'],
        'application_from'=>'ONLINE',
        'status'=>'CANCELLED'
    );
    log::debug($main);
    $id = 0;
    if ($entryType =='save') {
        db::table($this->lgu_db.'.ebplo_business_application')->insert($main);
        $id = DB::getPdo()->lastInsertId();
        log::debug($id );
         $totalGross = 0;
    }else{
        db::table($this->lgu_db.'.ebplo_business_application')
        ->where('tax_year',$taxyear)
        ->where('business_number',$bnumber)
        ->update($main);
        $detailssss =  db::table($this->lgu_db.'.ebplo_business_application')->where('business_number',$bnumber)
        ->select('business_app_id')
        ->where('tax_year',$taxyear)
        ->get();
        foreach ($detailssss as $key => $value) {
            $id = $value['business_app_id'];
        }
    }
    // db::table($this->lgu_db.'.ebplo_business_kind_business')->where('bappid',$id)->delete();
    // foreach ($kindOfBusiness as $key => $value) {
    //     $totalGross += $value['cp_gs'];
    //     $kind = array(
    //         'bappid'=>$id,
    //         'kind_id'=>$value['kind_id'],
    //         'description'=>$value['description'],
    //         'remarks'=>$value['remarks'],
    //         'app_type'=>$reqMain['application_type'],
    //         'cp_gs'=>$value['cp_gs'],
    //     );
    //     db::table($this->lgu_db.'.ebplo_business_kind_business')->insert($kind);
    // }
  
    $detailss =array(
    'bapp_id'=>$id,
    'business_no'=>$bnumber,
    'capitalization'=>$totalGross,
    'dtl_tax_year'=>$taxyear,
    'lessor_name'=>$details['lessor_name'],
    'business_add_blk'=>$details['business_add_blk'],
    'business_add_lot'=>$details['business_add_lot'],
    'business_add_bldg'=>$details['business_add_bldg'],
    'business_add_street'=>$details['business_add_street'],
    'business_add_purok_name'=>$details['business_add_purok_name'],
    'business_add_brgy'=>$details['business_add_brgy'],
    'business_add_subd'=>$details['business_add_subd'],
    'business_add_city'=>$details['business_add_city'],
    'business_add_prov'=>$details['business_add_prov'],
    'business_add_telno'=>$details['business_add_telno'],
    'business_add_email'=>$details['business_add_email'],
    'owner_add_blk'=>$details['owner_add_blk'],
    'owner_add_lot'=>$details['owner_add_lot'],
    'owner_add_bldg'=>$details['owner_add_bldg'],
    'owner_add_street'=>$details['owner_add_street'],
    'owner_purok'=>$details['owner_purok'],
    'owner_add_brgy'=>$details['owner_add_brgy'],
    'owner_add_subd'=>$details['owner_add_subd'],
    'owner_add_city'=>$details['owner_add_city'],
    'owner_add_prov'=>$details['owner_add_prov'],
    'owner_add_telno'=>$details['owner_add_telno'],
    'owner_add_email'=>$details['owner_add_email'],
    'lessor_add_blk'=>$details['lessor_add_blk'],
    'lessor_add_lot'=>$details['lessor_add_lot'],
    'lessor_add_bldg'=>$details['lessor_add_bldg'],
    'lessor_add_street'=>$details['lessor_add_street'],
    'lessor_add_brgy'=>$details['lessor_add_brgy'],
    'lessor_add_subd'=>$details['lessor_add_subd'],
    'lessor_add_city'=>$details['lessor_add_city'],
    'lessor_add_prov'=>$details['lessor_add_prov'],
    'lessor_add_telno'=>$details['lessor_add_telno'],
    'lessor_add_email'=>$details['lessor_add_email'],
    'SSS'=>$details['SSS'],
    'in_case_telno'=>$details['in_case_telno'],
    'in_case_mobile'=>$details['in_case_mobile'],
    'in_case_email'=>$details['in_case_email'],
    );
    // db::table( $this->lgu_db.'.ebplo_business_application_detail')->where('bapp_id',$id);
    db::table( $this->lgu_db.'.ebplo_business_application_detail')->insert($detailss);

    // db::table($this->lgu_db.'.ebplo_business_occ_fees_app')->where('bappid',$id)->delete();
    // foreach ( $occupationalFee  as $key => $value) {
    // $fee = array(
    //    'bappid'=>$id,
    //    'occ_id'=>$value['occ_id'],
    //    'no_emp'=>$value['no_emp'],
    //    'remarks'=>$value['remarks'],
    //  );
    //  db::table($this->lgu_db.'.ebplo_business_occ_fees_app')->insert($fee);
    // }
    // db::commit();
    $return =array(
        'local_business_app_id'=>$id,
        'local_tax_payer_id'=>$reqMain['owner'],
        // 'taxpayerlocalname'=>$reqMain['local_taxpayer_name'],
        'local_business_number' =>  $bnumber
    );
    return response()->json(new JsonResponse($return));
   } catch (\Throwable $th) {
    //   db::rollback();
      return response()->json(new JsonResponse(['Message' => $th, 'status' => 'success']));
   }
 }
 public function disapproved(Request $request){
    try {
        $email= $request->email;
        $uid = $request->uid;
        $app_id = $request->app_id;
        db::beginTransaction();
        $count =  db::table('online_application')->where('uid',$uid)->count();
        if ($count ==='1') {
            db::table('online_application')->where('id',$app_id)->delete();
            db::table('users')->where('id',$uid)->delete();
        }else{
            db::table('online_application')->where('id',$app_id)->delete();
        }
        $this->disapprovedEmail($email);
        db::commit();
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    } catch (\Throwable $th) {
        db::rollback();
        return response()->json(new JsonResponse(['Message' =>$th, 'status' => 'error']));
    }
}
public function disapprovedEmail($email)
{
    $user = User::where('email', $email)->first();
    if (!isset($user->id)) {
        return response()->json(new JsonResponse(['Message' => 'Email address not exist','status'=>'error'], 401));
    }
    Mail::to($user)->send(new disapprovedEmail($email));
    $data = array(
        'email'=> $user->email
    );
    db::table('password_resets')->insert($data);
    return response()->json(new JsonResponse(['Message' => 'Successfully sent','status'=>'success'], 200));
}
}
