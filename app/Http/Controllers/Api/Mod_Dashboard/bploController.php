<?php

namespace App\Http\Controllers\Api\Mod_Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Str;
use Storage;

class bploController extends Controller
{
  private $lgu_db;
  private $hr_db;
  private $trk_db;
  private $empid;
  protected $G;
  private $general;
  private $Proc;
  private $Budget;
  private $qpsii_lgusystem;
  public function __construct(GlobalController $global)
  {
    $this->G = $global;
    $this->lgu_db = $this->G->getLGUDb();
    $this->hr_db = $this->G->getHRDb();
    $this->trk_db = $this->G->getTrkDb();
    $this->general = $this->G->getGeneralDb();
    $this->Proc = $this->G->getProcDb();
    $this->Budget = $this->G->getBudgetDb();
    $this->qpsii_lgusystem = $this->G->getlguDb();
  }
  public function getpermitstatus1()
  {
    $list = db::select("SELECT application_date as 'date'
      ,application_type as 'type'
      ,COUNT(*) 'applied'
      ,SUM(IF(release_date IS NULL,0,1)) 'release'
    FROM  " . $this->lgu_db . ".ebplo_business_application
    WHERE tax_year = year(now())
    AND transaction_type <> 'Others'
    GROUP BY application_date,application_type
    ORDER BY application_type,application_date");
    return response()->json(new JsonResponse($list));
  }
  public function getpermitstatus()
  {
    $list = db::select("CALL " . $this->lgu_db . "._rans_applied_count(?,?)", [DATE("Y-m-1"), DATE("Y-m-d")]);
    return response()->json(new JsonResponse($list));
  }
  public function updatePermitStatus(Request $request)
  {
    $bappid = $request->bappid;
    $docx_status = $request->docx_status;
    $docx_remarks = $request->docx_remarks;
    db::table($this->lgu_db . '.ebplo_business_application')
      ->where('business_app_id', $bappid)
      ->update(['docx_status' => $docx_status, 'docx_remarks' => $docx_remarks]);

    $datalogs = array(
      'signatory' => $request->docx_status,
      'remarks' => $request->docx_remarks,
      'uid' => $request->uid,
      'bapid' => $request->bappid
    );

    db::table($this->lgu_db . '.ebplo_business_application_logs')
      ->insert($datalogs);
    $rnd = Str::random(10);

    if (isset($request->claim)) {
      if (strlen($request->claim) > 1) {
        db::table($this->lgu_db . '.ebplo_business_application')
        ->where('business_app_id', $bappid)
        ->update(['docx_status' => $docx_status, 'release_date' => $this->G->serverdatetime(), 'docx_remarks' => $docx_remarks]);
        db::table($this->lgu_db . '.ebplo_business_application_released')
          ->insert(['bappid' => $request->bappid, 'claimant' => $request->claim, 'uid' => $request->uid, 'filename' => $rnd]);
      }
    }
    if (isset($request->signature)) {
      $image_64 = $request->signature; //your base64 encoded data
      $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
      $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
      $image = str_replace($replace, '', $image_64);
      $image = str_replace(' ', '+', $image);
      $imageName = $request->bappid . '/' . $rnd . '.' . $extension;
      Storage::disk('public')->put($imageName, base64_decode($image));
    }
    return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
  }
  public function businessStatus()
  {
    $list = db::select("SELECT * FROM(SELECT
    tax_year AS 'year',
    SUM(CASE WHEN application_type ='NEW' THEN 1 ELSE 0 END)AS'new',
    SUM(CASE WHEN application_type ='RENEW' THEN 1 ELSE 0 END)AS'renew'
    FROM
      `qpsii_lgusystem`.ebplo_business_application b
    WHERE application_type NOT IN ('')
      AND b.status != 'Cancelled'
      AND b.transaction_type != 'Others'
    GROUP BY `tax_year` ORDER BY `year` DESC LIMIT 5)A ORDER BY `year` ASC");
    return response()->json(new JsonResponse($list));
  }
  public function businessCollection()
  {
    $list = db::select('SELECT * FROM (SELECT
    YEAR(`or_date`) AS "year",
    SUM(or_amount) "Total"
  FROM
`qpsii_lgusystem`.cto_or_transactions b
  WHERE b.or_status NOT IN ("C", "D")
  GROUP BY YEAR(`or_date`) ORDER BY YEAR(`or_date`) DESC LIMIT 5)A ORDER BY `year` ASC ');
    return response()->json(new JsonResponse($list));
  }
  public function businessCollectionCount()
  {
    $list = db::select('SELECT
        b.transaction_type as "name",
        sum(`or_amount`)*1 "count"
      FROM
      ' . $this->lgu_db . '.cto_or_transactions b
      WHERE b.or_status NOT IN ("C", "D")
      and YEAR(or_date) = year(now())
      GROUP BY b.transaction_type,
        year(or_date)');
    return response()->json(new JsonResponse($list));
  }
  public function businessCollectionType()
  {
    $list = db::select("SELECT b.type,SUM(b.count)AS 'count' FROM (SELECT
    b.transaction_type AS 'name',
    SUM(`or_amount`)*1 'count' ,
    (CASE WHEN b.transaction_type = 'Business Tax' THEN 'Business'
     WHEN b.transaction_type IN ('CTax Corporate','CTax Individual','Direct Cashbook','Miscellaneous') THEN 'Miscellaneous'
    WHEN b.transaction_type IN('Real Property') THEN 'Real Property' ELSE 'Others' END
     )AS 'type'
  FROM
`qpsii_lgusystem`.cto_or_transactions b
  WHERE b.or_status NOT IN ('C', 'D')
  AND YEAR(or_date) = YEAR(NOW())
  GROUP BY b.transaction_type,
    YEAR(or_date))B GROUP BY b.type");
    return response()->json(new JsonResponse($list));
  }
  public function hr_employee_type()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_hr_dashboard_employee_type_count();');
    return response()->json(new JsonResponse($list));
  }
  public function hr_employee_gender()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_hr_dashboard_employee_gender_count();');
    return response()->json(new JsonResponse($list));
  }
  public function hr_Department()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_hr_dashboard_employee_department_count();');
    return response()->json(new JsonResponse($list));
  }
  public function hr_age_gap()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_hr_dashboard_employee_age_bracket_count();');
    return response()->json(new JsonResponse($list));
  }

  public function ass_deliquency()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_assesor_dashboard_delinquency();');
    return response()->json(new JsonResponse($list));
  }
  public function ass_app_type()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_assessor_employee_proptype_count();');
    return response()->json(new JsonResponse($list));
  }
  public function ass_building_count()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_assessor_employee_propclassification_count("BLDG");');
    return response()->json(new JsonResponse($list));
  }
  public function ass_land_count()
  {
    $list = db::select('CALL ' . $this->lgu_db . '.spl_assessor_employee_propclassification_count("LAND");');
    return response()->json(new JsonResponse($list));
  }
  public function get_bud_saao()
  {
    $list = db::select('CALL ' . $this->Budget . '.dash_saao();');
    return response()->json(new JsonResponse($list));
  }
  public function get_bud_project()
  {
    $list = db::select('CALL ' . $this->Budget . '.dash_ProjectCount();');
    return response()->json(new JsonResponse($list));
  }
  public function get_bud_total()
  {
    $list = db::select('CALL ' . $this->Budget . '.StatusOfAppropriation_dashboard_total(1,6,"2022","1,7",0,0);'); 
    return response()->json(new JsonResponse($list));
  }
  public function get_project_status()
  {
    $list = db::select('CALL ' . $this->qpsii_lgusystem . '.spl_eceo_count_proj_status_jho(NOW())'); 
    return response()->json(new JsonResponse($list));
  }
  public function getDailyCollection(Request $request)
  {
    $from = $request->from;
    $to = $request->to;
    $data = DB::select('call ' . $this->lgu_db . '.cto_cashier_report_rans(?,?)', array($from, $to));
    return response()->json(new JsonResponse($data));
  }
  public function getDailyApplied(Request $request)
  {
    $from = date('Y-01-01');
    $to = date('Y-m-d');
    $data = DB::select("SELECT * FROM (SELECT
    application_date AS 'date'
    ,COUNT(*) AS 'count'
 FROM
   `ebplo_business_application`
   WHERE `application_date` BETWEEN ? AND ?)
   AND ebplo_business_application.status <> 'cancelled'
  GROUP BY ebplo_business_application.application_date
  ORDER BY application_date DESC
  LIMIT 20)A ORDER BY a.date ASC", array($from, $to));
    return response()->json(new JsonResponse($data));
  }
  public function getGraphYearly(Request $request)
  {


    $list['collection'] = db::select('SELECT * FROM(SELECT YEAR(`or_date`) AS "year",CAST(SUM(od.net_amount) AS DECIMAL (20,2))  "Total" FROM
    ' . $this->lgu_db . '.cto_or_transactions b
     INNER JOIN ' . $this->lgu_db . '.cto_or_transactions_details od
     USING(or_id)
       WHERE b.or_status ="A"
       GROUP BY YEAR(`or_date`) ORDER BY YEAR(`or_date`) DESC
       LIMIT 7)B ORDER BY YEAR ASC');
    $list['collection_type1'] = db::select('call ' . $this->lgu_db . '._rans_jay_display_sre_new()');

    $list['applied'] = db::select("SELECT * FROM (SELECT tax_year as 'year',
      sum(case when application_type ='NEW' then 1 else 0 end)as'new',
      sum(case when application_type ='RENEW' then 1 else 0 end)as'renew'
      FROM " . $this->lgu_db . ".ebplo_business_application b
        WHERE application_type NOT IN ('')
        and b.status != 'Cancelled'
        and b.transaction_type != 'Others'
        GROUP BY `tax_year` ORDER BY tax_year DESC LIMIT 5)B ORDER BY b.year ASC");
    $list['collection_type1'] = db::select('call ' . $this->lgu_db . '._rans_jay_display_sre_new()');
    return response()->json(new JsonResponse($list));
  }
  public function getGraph(Request $request)
  {

    $data = json_decode($request->data);
    $from = $data->min;
    $to = $data->max;
    //   $list['dailyApplied']= DB::select("SELECT * FROM (SELECT
    //   application_date AS 'date',
    //   sum(case when application_type ='NEW' then 1 else 0 end)as'new',
    //   sum(case when application_type ='RENEW' then 1 else 0 end)as'renew'
    //    FROM
    //   ".$this->lgu_db.".ebplo_business_application
    //  WHERE ebplo_business_application.status <> 'cancelled'
    //  and week(application_date) between ".$from." and ".$to."
    //  and year(application_date) = year(now())
    // GROUP BY ebplo_business_application.application_date
    // ORDER BY application_date DESC
    // LIMIT 20)A ORDER BY a.date ASC");

    $list['dailyApplied'] = DB::select("call " . $this->lgu_db . "._rans_applied_count_graph(?,?)", [$from, $to]);
    $list['collectionDaily'] = db::select('SELECT  DATE_FORMAT(b.date,"%b %d") as date,b.amount FROM(SELECT (`or_date`) AS "date",SUM(od.net_amount) "amount" FROM
     ' . $this->lgu_db . '.cto_or_transactions b
     INNER JOIN  ' . $this->lgu_db . '.cto_or_transactions_details od
     USING(or_id)
       WHERE b.or_status NOT IN ("C", "D")
       AND week(or_date) BETWEEN ' . $from . ' AND  ' . $to . '
     AND YEAR(or_date) = YEAR(NOW())
       GROUP BY (`or_date`) ORDER BY (`or_date`) DESC
       )B ORDER BY B.date ASC');

    return response()->json(new JsonResponse($list));
  }
  public function getBusinessSize()
  {
    $list = db::select('call ' . $this->lgu_db . '.spl_jay_reprt_business_rpt_tax_monitoring_graph()');
    return response()->json(new JsonResponse($list));
  }
  public function businessAppliedStatus(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '.rans_get_businesslist_count()');
    return response()->json(new JsonResponse($list));
  }
  public function businessAppliedLacking(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '.rans_get_business_lacking()');
    return response()->json(new JsonResponse($list));
  }
  public function businessAppliedHold(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '.rans_get_business_hold()');
    return response()->json(new JsonResponse($list));
  }
  public function getbusinessAssessmentPaid(Request $request)
  {

    $list = DB::select('call ' . $this->lgu_db . '.jay_display_assement_tax_due_graph(?,?)', [date("Y-01-01"), date("Y-m-d")]);
    return response()->json(new JsonResponse($list));
  }
  public function getbusinessAppliedComparative(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '.spl_jay_display_business_comparative(?,?)', [date("Y-01-01"), date("Y-m-d")]);
    return response()->json(new JsonResponse($list));
  }

  public function businessCollectionRunning(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '._rans_get_collection_running_total()');
    return response()->json(new JsonResponse($list));
  }
  public function getapplicationStatus(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '.spl_jay_reprt_business_rpt_tax_monitoring3(?,?)', [date("Y-01-01"), date("Y-m-d")]);
    return response()->json(new JsonResponse($list));
  }
  public function getapplicationStatusDetails($id)
  {
    $list = DB::select('call ' . $this->lgu_db . '.spl_jay_reprt_business_rpt_tax_monitoring_details_new(?)', [$id]);
    return response()->json(new JsonResponse($list));
  }

  public function releasedaging(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '._rans_applied_count1()');
    return response()->json(new JsonResponse($list));
  }
  public function assessmentaging(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '.jay_generate_business_assessment_aging(?,?)', [date("Y-01-01"), date("Y-m-d")]);
    return response()->json(new JsonResponse($list));
  }
  public function releasedaging1(Request $request)
  {
    $list = DB::select('call ' . $this->lgu_db . '.jay_generate_business_relased_aging1(?,?)', [date("Y-01-01"), date("Y-m-d")]);
    return response()->json(new JsonResponse($list));
  }
  public function topTaxPayer(Request $request)
  {
    $from = $request->from;
    $to = $request->to;
    $list = DB::select('call ' . $this->lgu_db . '._rans_dahsboard_top_tax_payer(?,?)', [$from, $to]);
    return response()->json(new JsonResponse($list));
  }
}
