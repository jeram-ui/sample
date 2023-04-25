<?php

namespace App\Http\Controllers\Api\mod_Bac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Storage;
use File;
use Exception;
class alternativeController extends Controller
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
    public function getAlternative()
    {
        $list = db::select('call '.$this->Bac.'.rans_get_alternative');
    
        return response()->json(new JsonResponse($list));
    }
    public function getAlternativeMode(Request $request)
    {
        $filter = $request->filter;
        // $startRow = $request->startRow;
        // $count = $request->count;
        // $list = db::select('call '.$this->Bac.'.rans_get_alternative_mode(?,?,?)',["%".$filter['type']."%", $startRow, $count]);

        // $data['list'] = $list;
      
        $list = db::table($this->Proc. '.pow_main_individual')
            ->leftjoin($this->Proc. '.pow_sof_detail', 'pow_sof_detail.pow_id','pow_main_individual.id')
            ->leftjoin($this->Proc. '.tbl_pr_main', 'tbl_pr_main.pow_id','pow_main_individual.id')
            ->leftjoin($this->Bac. '.bacc_resolution_details', 'bacc_resolution_details.pr_id','tbl_pr_main.id')
            ->leftjoin($this->Bac. '.bacc_resolution', 'bacc_resolution.id', 'bacc_resolution_details.id')
            ->leftjoin($this->Proc. '.tbl_canvass_main', 'tbl_canvass_main.pr_id', 'tbl_pr_main.id')
            ->leftjoin($this->Proc. '.tbl_po_main', 'tbl_po_main.id', 'pow_main_individual.id')
            ->leftjoin($this->Proc. '.request_for_quotation_main', 'request_for_quotation_main.pow_id', 'pow_main_individual.id')
            ->leftjoin(
                DB::raw('(SELECT
                proj_id AS "powid",
                COUNT(*)AS "count"
                FROM ' . $this->Bac . '.bacc_pre_docs_entry WHERE `trans_type` = "ELIGIBILITY"
                GROUP BY `proj_id`)eli'),
                        function ($join) {
                            $join->on('pow_main_individual.id', '=', 'eli.powid');
                        }
            )
            ->select(db::raw('pow_main_individual.id AS "powid"'),
                    db::raw('pow_main_individual.project_title AS "project_desc"') ,
                    db::raw('pow_main_individual.reference_date AS "powDate"') ,
                    db::raw('GROUP_CONCAT(DISTINCT(pow_sof_detail.SOF_Description)) AS "SOF_Description"') ,
                    db::raw('tbl_pr_main.pr_date AS "pr_date"') ,
                    db::raw('tbl_pr_main.pr_no') ,
                    db::raw('tbl_pr_main.id AS "pr_id"') ,
                    db::raw('tbl_po_main.id AS "po_id"') ,
                    db::raw('bacc_resolution.mop_name') ,
                    db::raw('bacc_resolution.trans_date AS "resoDate"') ,
                    db::raw('"" AS "philgeps"') ,
                    db::raw('CONCAT(request_for_quotation_main.canvass_datefrom,"-",request_for_quotation_main.canvass_dateto) AS "supplierQuota"') ,
                    db::raw('tbl_canvass_main.trans_date AS "abstractQuotation"') ,
                    db::raw('eli.count AS "certOfEligibility"') ,
                    db::raw('tbl_canvass_main.noa_date') ,
                    db::raw('tbl_canvass_main.id AS "noa_id"') ,
                    db::raw('tbl_po_main.po_date') ,
            
   )
            ->where("pow_main_individual.status", "Approved")
            ->whereRaw("(pow_main_individual.project_title like ? or tbl_pr_main.pr_date like ? or pow_sof_detail.SOF_Description like ? or tbl_pr_main.pr_no like ?)", 
                    ['%' . $filter['type'] . '%','%' . $filter['type'] . '%','%' . $filter['type'] . '%', '%' . $filter['type'] . '%'])
            ->groupBy("pow_main_individual.id")
            ->orderBy("pow_main_individual.id", "DESC")
            ->skip($request->startRow)
            ->take($request->count)
            ->get();

         $data['list'] = $list;
        return response()->json(new JsonResponse($data));
    }
    public function getAlternativeCount(Request $request)
    {
        $filter = $request->filter;
        // $list = db::select('call '.$this->Bac.'.rans_get_alternative_count(?)',["%".$filter['type']."%"]);
        $list = db::table($this->Proc. '.pow_main_individual')
            ->leftjoin($this->Proc. '.pow_sof_detail', 'pow_sof_detail.pow_id','pow_main_individual.id')
            ->leftjoin($this->Proc. '.tbl_pr_main', 'tbl_pr_main.pow_id','pow_main_individual.id')
            ->leftjoin($this->Bac. '.bacc_resolution_details', 'bacc_resolution_details.pr_id','tbl_pr_main.id')
            ->leftjoin($this->Bac. '.bacc_resolution', 'bacc_resolution.id', 'bacc_resolution_details.id')
            ->leftjoin($this->Proc. '.tbl_canvass_main', 'tbl_canvass_main.pr_id', 'tbl_pr_main.id')
            ->leftjoin($this->Proc. '.tbl_po_main', 'tbl_po_main.id', 'pow_main_individual.id')
            ->leftjoin($this->Proc. '.request_for_quotation_main', 'request_for_quotation_main.pow_id', 'pow_main_individual.id')
            ->leftjoin(
                DB::raw('(SELECT
                proj_id AS "powid",
                COUNT(*)AS "count"
                FROM ' . $this->Bac . '.bacc_pre_docs_entry WHERE `trans_type` = "ELIGIBILITY"
                GROUP BY `proj_id`)eli'),
                        function ($join) {
                            $join->on('pow_main_individual.id', '=', 'eli.powid');
                        }
            )
            ->select(db::raw('pow_main_individual.id AS "powid"'),
                    db::raw('pow_main_individual.project_title AS "project_desc"') ,
                    db::raw('pow_main_individual.reference_date AS "powDate"') ,
                    db::raw('GROUP_CONCAT(DISTINCT(pow_sof_detail.SOF_Description)) AS "SOF_Description"') ,
                    db::raw('tbl_pr_main.pr_date AS "pr_date"') ,
                    db::raw('tbl_pr_main.pr_no') ,
                    db::raw('tbl_pr_main.id AS "pr_id"') ,
                    db::raw('tbl_po_main.id AS "po_id"') ,
                    db::raw('bacc_resolution.mop_name') ,
                    db::raw('bacc_resolution.trans_date AS "resoDate"') ,
                    db::raw('"" AS "philgeps"') ,
                    db::raw('CONCAT(request_for_quotation_main.canvass_datefrom,"-",request_for_quotation_main.canvass_dateto) AS "supplierQuota"') ,
                    db::raw('tbl_canvass_main.trans_date AS "abstractQuotation"') ,
                    db::raw('eli.count AS "certOfEligibility"') ,
                    db::raw('tbl_canvass_main.noa_date') ,
                    db::raw('tbl_canvass_main.id AS "noa_id"') ,
                    db::raw('tbl_po_main.po_date') ,
            
   )
            ->where("pow_main_individual.status", "Approved")
            ->whereRaw("(pow_main_individual.project_title like ? or tbl_pr_main.pr_date like ? or pow_sof_detail.SOF_Description like ? or tbl_pr_main.pr_no like ?)", 
                    ['%' . $filter['type'] . '%','%' . $filter['type'] . '%','%' . $filter['type'] . '%', '%' . $filter['type'] . '%'])
            ->groupBy("pow_main_individual.id")
            ->orderBy("pow_main_individual.id", "DESC")
            ->get();

        $data['count'] = count($list);
        return response()->json(new JsonResponse($data));
    }
    function print(Request $request){
        try {
            // $procurement = $request->itm;
            // $main = db::select('call '.$this->Bac.'.rans_get_alternative');
            $main = db::select("call ".$this->Bac.".rans_get_alternative()");

            $mainData = "";
            foreach ($main as $key => $value) {

                $mainData .= '<tr>
                <td width="25%" style="font-size:6pt;" align="center" height="15px"> '.$value->project_desc.' </td>
                <td width="7%" style="font-size:6pt;" align="center" height="15px"> '.$value->powDate.' </td>
                <td width="7%" style="font-size:6pt;" align="center" height="15px"> '.$value->SOF_Description.' </td>
                <td width="7%" style="font-size:6pt;" align="center" height="15px"> '.$value->pr_date.' </td>
                <td  width="8%" style="font-size:6pt;" align="center" height="15px"> '.$value->pr_no.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->mop_name.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->resoDate.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->philgeps.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->supplierQuota.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->abstractQuotation.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->certOfEligibility.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->noa_date.' </td>
                <td width="6%" style="font-size:6pt;" align="center" height="15px"> '.$value->po_date.' </td>

            </tr>';

                }

                if(count($main)< 10){
                    for($i = count($main); $i<10; $i++){
                        $mainData .= '<tr>
                        <td width="25%" style="font-size:6pt;" align="center" height="15px"> </td>
                        <td width="7%" style="font-size:6pt;" align="center" height="15px">  </td>
                        <td width="7%" style="font-size:6pt;" align="center" height="15px">  </td>
                        <td width="7%" style="font-size:6pt;" align="center" height="15px">  </td>
                        <td  width="8%" style="font-size:6pt;" align="center" height="15px">  </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px"> </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px">  </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px">  </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px"> </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px"> </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px"> </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px"> </td>
                        <td width="6%" style="font-size:6pt;" align="center" height="15px"> </td>

                </tr>';
                }
            }





        $header ='<table style="width=100%;">
        <tr>

            <th style="font-size:10pt;" align="center">
                <b>ALTERNATIVE METHOD OF PROCUREMENT</b>

        <br/>
        <br/>
            </th>

        </tr>
        </table>';
        $Template = $header;




     $Template .='<table width="100%" cellpadding="2">

     <table width="100%" border="1" cellpadding="2">
     <tr>
         <th width="25%" style="font-size:7pt;"  align="center"><br/><br/><br/><br/><br/><br/> <b>Name of Project/General Description of Project</b></th>
         <th width="7%" style="font-size:7pt;"  align="center" height="10px"> <b> Program of Work (for infrastructure projects), Procurement/Project Proposal (for goods), Training Design (for seminar/training), Terms of Reference  (for services)</b>  </th>
         <th width="7%" style="font-size:7pt;"  align="center"><br/><br/><br/><br/><br/><br/> <b>Source of Funds</b> </th>
         <th width="7%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>  Purchase Request (PR Date) </b></th>
         <th width="8%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b> PR Number </b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>   Request for BAC Resolution </b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>   BAC Resolution </b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><b>PhilGEPS Posting (for Two Failed Biddings, for NP-SVP with above 50,000 and shopping with above 50,000) </b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>  RFQ(3 quotations)</b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>  Abstract of Quotation </b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>  Cert. of Eligibility </b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>  Notice of Award </b></th>
         <th width="6%" style="font-size:7pt;" align="center"><br/><br/><br/><br/><br/><br/><b>  Purchase Order </b></th>
     </tr>

   '.$mainData.'




     </table>





</table>';






        PDF::AddPage('L', 'mm', 'A4', true, 'UTF-8', false);
        PDF::SetFont('Helvetica', '', 10);
        PDF::SetTitle('Alternative Method of Procurement');
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




