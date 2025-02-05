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
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class resolutionController extends Controller
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
    public function storeTitle(Request $request)
    {
        $form = $request->form;

        DB::table($this->Bac . '.bacc_resolution_title')
            ->where('date', $form['date'])
            ->delete();

        DB::table($this->Bac . '.bacc_resolution_title')->insert($form);
        $id = DB::getPdo()->LastInsertId();

        return  $this->G->success();
    }

    public function storePR(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;

        foreach ($formx as $key => $value) {
            $dataz = array(
                'resolution_id' => $form,
                'pr_id' => $value['prid'],
                'title' => $value['title'],
                'pr_ref' => $value['pr_ref'],
                'AMOUNT' => $value['AMOUNT']
            );
            if ($value['selectx'] === 'true') {
                DB::table($this->Bac . '.bacc_resolution_details')
                    // ->where('resolution_id', $form['id'])
                    ->insert($dataz);
                $id = DB::getPdo()->LastInsertId();
            }
        }

        return  $this->G->success();
    }
    public function getPR()
    {
        $list = db::select('call ' . $this->Bac . '.rans_bacc_getPR');

        return response()->json(new JsonResponse($list));
    }
    public function getPendingWorks(Request $request)
    {
        $filter = $request->filter;
        $startRow = $request->startRow;
        $count = $request->count;
        $list = db::select('call ' . $this->Bac . '.rans_bacc_getPendingWorks(?,?,?)',["%".$filter['type']."%", $startRow, $count]);

        $final = array();
        foreach ($list as $key => $value) {
        $dtls = db::table($this->Proc .'.tbl_pr_detail')
            ->join($this->Proc . ".tbl_pr_main", 'tbl_pr_main.id', 'tbl_pr_detail.main_id')
            ->select('item_name',"qty",'unit_measure', 'unit_cost', 'total_cost')
            ->where("tbl_pr_detail.main_id", $value->prid)
            ->get();
        $dum = array(
            'prid'=>$value->prid,
            'department'=>$value->department,
            'title'=>$value->title,
            'pr_ref'=>$value->pr_ref,
            'pr_date'=>$value->pr_date,
            'AMOUNT'=>$value->AMOUNT,
            'mode_proc'=>$value->mode_proc,
            'pow_id'=>$value->pow_id,
            'mop'=>$value->mop,
            'date_approved'=>$value->date_approved,
            'detailx'=>$dtls,

        );
        array_push( $final,$dum);
        }
        // $data['list'] = $final->skip($request->startRow)->take($request->count)->get();
        $data['list'] = $final;
        return response()->json(new JsonResponse($data));
    }
    public function getPendingFilter(Request $request)
    {
        $filter = $request->filter;
        $startRow = $request->startRow;
        $count = $request->count;
        $list = db::select('call ' . $this->Bac . '.rans_bacc_getPendingFilter(?,?,?,?,?)',["%".$filter['type']."%", $startRow, $count, $filter['yearFrom'], $filter['yearTo']]);

        $final = array();
        foreach ($list as $key => $value) {
        $dtls = db::table($this->Proc .'.tbl_pr_detail')
            ->join($this->Proc . ".tbl_pr_main", 'tbl_pr_main.id', 'tbl_pr_detail.main_id')
            ->select('item_name',"qty",'unit_measure', 'unit_cost', 'total_cost')
            ->where("tbl_pr_detail.main_id", $value->prid)
            ->get();
        $dum = array(
            'prid'=>$value->prid,
            'department'=>$value->department,
            'title'=>$value->title,
            'pr_ref'=>$value->pr_ref,
            'pr_date'=>$value->pr_date,
            'AMOUNT'=>$value->AMOUNT,
            'mode_proc'=>$value->mode_proc,
            'pow_id'=>$value->pow_id,
            'mop'=>$value->mop,
            'date_approved'=>$value->date_approved,
            'detailx'=>$dtls,

        );
        array_push($final, $dum);
        }
        // $data['list'] = $final->skip($request->startRow)->take($request->count)->get();
        $data['list'] = $final;
        return response()->json(new JsonResponse($data));
    }
    public function getPRCount(Request $request)
    {
        $filter = $request->filter;
        $list = db::select('call ' . $this->Bac . '.rans_bacc_getPR_count(?)',["%".$filter['type']."%"]);

        $data['count'] = count($list);
        return response()->json(new JsonResponse($data));
    }
    public function getPR_byYearCount(Request $request)
    {
        $filter = $request->filter;
        $list = db::select('call ' . $this->Bac . '.rans_bacc_getPR_YearCount(?,?,?)',["%".$filter['type']."%", $filter['yearFrom'], $filter['yearTo']]);

        $data['count'] = count($list);
        return response()->json(new JsonResponse($data));
    }
    public function getNOAapprovalCount(Request $request)
    {
        $filter =  $request->filter;
        $list = db::table($this->Proc . ".tbl_canvass_main")
            ->join($this->Proc . ".tbl_canvass_supplier", "tbl_canvass_main.id", "tbl_canvass_supplier.can_id")
            ->join($this->Proc . ".tbl_canvass_detail", "tbl_canvass_supplier.GUID", "tbl_canvass_detail.GUID")
            // ->select('*', db::raw(GROUP_CONCAT("item_description",  "<br/>"),"as items"))
            ->select("*", db::raw("GROUP_CONCAT(DISTINCT item_description SEPARATOR '<br/>') AS items"))
            ->where("tbl_canvass_supplier.status", "Awarded")
            ->where("tbl_canvass_main.status", '<>', "Cancelled")
            ->whereRaw(" (`title` like ? or `abstract_no` like ? or `suppliername` like ? ) ",['%' . $filter['title'] . '%','%' . $filter['title'] . '%','%' . $filter['title'] . '%'])
            ->whereNull("noa_status")
            ->groupBy("can_id")
            ->groupBy("supplierid")
            ->get();

        // $noaApproval = array();
        // foreach ($list as $key => $value) {
        //     $itemx = db::table($this->Proc . ".tbl_canvass_detail")
        //         ->leftjoin($this->Proc . ".tbl_canvass_supplier", "tbl_canvass_supplier.GUID", "tbl_canvass_detail.GUID")
        //         ->select('item_description', 'UoM', 'quantity')
        //         ->where("tbl_canvass_detail.main_id", $value->can_id)
        //         ->groupBy("tbl_canvass_detail.id")
        //         // ->groupBy("supplierid")
        //         ->get();

        //     $noaArray= array(
        //         'id' => $value->id,
        //         'pow_id' => $value->pow_id,
        //         'pr_id' => $value->pr_id,
        //         'abstract_no' => $value->abstract_no,
        //         'title' => $value->title,
        //         'suppliername' => $value->suppliername,
        //         'supplierid' => $value->supplierid,
        //         'can_id' => $value->can_id,
        //         'items' => $value->items,
        //         'date_approved' => $value->date_approved,
        //         'selected' => 'false',
        //         'itemx' => $itemx,

        //     );
        //     array_push($noaApproval, $noaArray);
        // }

        $data['count'] = count($list);
        return response()->json(new JsonResponse($data));
    }
    public function addPR()
    {
        $list = db::select('call ' . $this->Bac . '.rans_bacc_addPR');
        return response()->json(new JsonResponse($list));
    }
    public function PRListing($id)
    {
        $list = db::select('call ' . $this->Bac . '.rans_bacc_PRListing(?)', [$id]);
        // ->where('resolution_id', $id)
        // ->where('status', 0)
        // ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getNOAApproval()
    {
        $list = db::table($this->Proc . ".tbl_canvass_main")
            ->join($this->Proc . ".tbl_canvass_supplier", "tbl_canvass_main.id", "tbl_canvass_supplier.can_id")
            ->join($this->Proc . ".tbl_canvass_detail", "tbl_canvass_supplier.GUID", "tbl_canvass_detail.GUID")
            // ->select('*', db::raw(GROUP_CONCAT("item_description",  "<br/>"),"as items"))
            ->select("*", db::raw("GROUP_CONCAT(DISTINCT item_description SEPARATOR '<br/>') AS items"))
            ->where("tbl_canvass_supplier.status", "Awarded")
            ->where("tbl_canvass_main.status", '<>', "Cancelled")
            ->whereNull("noa_status")
            ->groupBy("can_id")
            ->groupBy("supplierid")
            ->get();
        return response()->json(new JsonResponse($list));
        // $list = db::select('call ' . $this->Proc . '.rans_procurement_NOOA_Approval');
        // return response()->json(new JsonResponse($list));
    }
    public function getNOAApprovalEntry(Request $request)
    {
        $filter =  $request->filter;
        $list = db::table($this->Proc . ".tbl_canvass_main")
            ->join($this->Proc . ".tbl_canvass_supplier", "tbl_canvass_main.id", "tbl_canvass_supplier.can_id")
            ->join($this->Proc . ".tbl_canvass_detail", "tbl_canvass_supplier.GUID", "tbl_canvass_detail.GUID")
            // ->select('*', db::raw(GROUP_CONCAT("item_description",  "<br/>"),"as items"))
            ->select("*", db::raw("GROUP_CONCAT(DISTINCT item_description SEPARATOR '<br/>') AS items"))
            ->where("tbl_canvass_supplier.status", "Awarded")
            ->where("tbl_canvass_main.status", '<>', "Cancelled")
            ->whereRaw(" (`title` like ? or `abstract_no` like ? or `suppliername` like ? ) ",['%' . $filter['title'] . '%','%' . $filter['title'] . '%','%' . $filter['title'] . '%'])
            ->whereNull("noa_status")
            ->groupBy("can_id")
            ->groupBy("supplierid")
            ->skip($request->startRow)
            ->take($request->count)
            ->get();

        $noaApproval = array();
        foreach ($list as $key => $value) {
            $itemx = db::table($this->Proc . ".tbl_canvass_detail")
                ->leftjoin($this->Proc . ".tbl_canvass_supplier", "tbl_canvass_supplier.GUID", "tbl_canvass_detail.GUID")
                ->select('item_description', 'UoM', 'quantity')
                ->where("tbl_canvass_detail.main_id", $value->can_id)
                ->groupBy("tbl_canvass_detail.id")
                // ->groupBy("supplierid")
                ->get();

            $noaArray= array(
                'id' => $value->id,
                'pow_id' => $value->pow_id,
                'pr_id' => $value->pr_id,
                'abstract_no' => $value->abstract_no,
                'title' => $value->title,
                'suppliername' => $value->suppliername,
                'supplierid' => $value->supplierid,
                'can_id' => $value->can_id,
                'items' => $value->items,
                'date_approved' => $value->date_approved,
                'selected' => 'false',
                'itemx' => $itemx,

            );
            array_push($noaApproval, $noaArray);
        }
        $data['list'] = $noaApproval;
                    // ->skip($request->startRow)
                    // ->take($request->count)
                    // ->get();
        return response()->json(new JsonResponse($data));
        // $list = db::select('call ' . $this->Proc . '.rans_procurement_NOOA_Approval');
        // return response()->json(new JsonResponse($list));
    }
    public function getNOAApprovalList(Request $request)
    {
        $list = db::table($this->Proc . ".tbl_canvass_main")
            ->join($this->Proc . ".tbl_canvass_supplier", "tbl_canvass_main.id", "tbl_canvass_supplier.can_id")
            ->leftjoin($this->Proc . ".tbl_canvass_detail", "tbl_canvass_supplier.GUID", "tbl_canvass_detail.GUID")
            ->select("*",
                db::raw("GROUP_CONCAT(DISTINCT item_description SEPARATOR ' <br/> ') AS items"),
                db::raw("tbl_canvass_supplier.GUID AS GUID")
            )
            ->where("tbl_canvass_supplier.noa_status", "Approved")
            ->whereRaw(" (`title` like ? or `abstract_no` like ? or `suppliername` like ? or `date_approved` like ? ) ",['%' . $request->titler . '%','%' . $request->titler . '%','%' . $request->titler . '%', '%' . $request->titler . '%'])
            ->groupBy("can_id")
            ->groupBy("supplierid")
            ->get();
        $noaApproved = array();
        foreach ($list as $key => $value) {
            $details = db::table($this->Proc . '.tbl_canvass_detail')
                ->leftjoin($this->Proc . ".tbl_canvass_supplier", "tbl_canvass_supplier.GUID", "tbl_canvass_detail.GUID")
                ->select('item_description', 'UoM', 'quantity')
                ->where("tbl_canvass_detail.main_id", $value->can_id)
                ->groupBy("tbl_canvass_detail.id")
                // ->groupBy("supplierid")
                ->get();
            $approved = array(
                'id' => $value->id,
                'GUID' => $value->GUID,
                'pow_id' => $value->pow_id,
                'pr_id' => $value->pr_id,
                'abstract_no' => $value->abstract_no,
                'title' => $value->title,
                'suppliername' => $value->suppliername,
                'supplierid' => $value->supplierid,
                'can_id' => $value->can_id,
                'date_approved' => $value->date_approved,
                'items' => $value->items,
                'details' => $details,
            );
        array_push($noaApproved, $approved);
        }
        return response()->json(new JsonResponse($noaApproved));
    }

    public function getofficials()
    {
        $list = db::table($this->Bac . ".bac_members")
            ->select('*', db::raw("'false' as present"))
            ->where("bac_members.status", 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        // $data = db::table($this->Bac . '.bac_attendancemember')
        // ->leftJoin($this->Bac . '.bac_members', 'bac_members.id', 'bac_attendancemember.bacmember_id')
        //     ->where('mem_id', $id)
        //     ->get();

        $attend = DB::table($this->Bac . '.bac_attendancemember')
            ->where('mem_id', $id);

        $dummyFee = DB::table($this->Bac . '.bac_members')
            ->leftJoinSub($attend, 'data', function ($join) {
                $join->on('data.bacmember_id', '=', 'bac_members.id');
            })
            ->select(
                "bac_members.*",
                // "data.present"

                db::raw("ifnull( data.present, 'false') as present")
            )
            ->get();

        $specs = array();
        foreach ($dummyFee as $key => $valueF) {
            $specsData = array(
                'id' => $valueF->id,
                'mem_id' => $valueF->id,
                'emp_name' => $valueF->emp_name,
                'position' => $valueF->position,
                'category' => $valueF->category,
                'present' => $valueF->present,

            );
            array_push($specs, $specsData);
        }
        $data['rowData'] = $specs;
        return response()->json(new JsonResponse($data));
        // log::debug($id);
    }

    public function officialstore(Request $request)
    {
        $idx = $request->idx;
        $selectedData = $request->selectedData;
        $id = $idx;
        if ($id > 0) {

            db::table($this->Bac . ".bac_attendanceMember")
                ->where("mem_id", $id)
                ->delete();

            foreach ($selectedData as $key => $value) {
                $datx = array(
                    'mem_id' => $idx,
                    'bacmember_id' => $value['id'],
                    'emp_name' => $value['emp_name'],
                    'position' => $value['position'],
                    'category' => $value['category'],
                    'present' => $value['present'],
                );
                db::table($this->Bac . ".bac_attendanceMember")->insert($datx);
            }
        } else {
            foreach ($selectedData as $key => $value) {
                $datx = array(
                    'mem_id' => $idx,
                    'bacmember_id' => $value['id'],
                    'emp_name' => $value['emp_name'],
                    'position' => $value['position'],
                    'category' => $value['category'],
                    'present' => $value['present'],
                );
                db::table($this->Bac . ".bac_attendanceMember")->insert($datx);
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function updateForNOAApproval(Request $request)
    {
        $list = $request->list;

        foreach ($list as $key => $value) {

            if($value['selected']==='true'){
                 db::table($this->Proc . ".tbl_canvass_supplier")
                ->where("supplierid", $value['supplierid'])
                ->where("can_id", $value['can_id'])
                ->update(['noa_status' => 'Approved', 'date_approved' => $this->G->serverdatetime(), 'approved_id' => Auth::user()->Employee_id]);
            }

        }
        // return response()->json(new JsonResponse($list));
    }

    public function numberToLetter($num)
    {
        // log::debug($num);
        if ($num == 1) {
            return "A";
        } elseif ($num == 2) {
            return "B";
        } elseif ($num == 3) {
            return "C";
        } elseif ($num == 4) {
            return "D";
        } elseif ($num == 5) {
            return "E";
        } elseif ($num == 6) {
            return "F";
        } elseif ($num == 7) {
            return "G";
        } elseif ($num == 8) {
            return "H";
        } elseif ($num == 9) {
            return "I";
        } elseif ($num == 10) {
            return "J";
        } elseif ($num == 11) {
            return "K";
        } elseif ($num == 12) {
            return "L";
        } elseif ($num == 13) {
            return "M";
        } elseif ($num == 14) {
            return "N";
        }
    }
    public function getRef($date)
    {
        $datex = $date;
        $finalref = "";
        $dateFormated = date_create($datex);


        $lastref = db::select("SELECT trans_date,resolution_no,SUBSTRING_INDEX(`resolution_no`,'-',1)AS 'year' ,SUBSTRING(SUBSTRING_INDEX(`resolution_no`,'-',-1),1,3) AS 'series',SUBSTRING(SUBSTRING_INDEX(`resolution_no`,'-',-1),4,3) AS 'ext' FROM " . $this->Bac . ".bacc_resolution
       ORDER BY `trans_date` DESC LIMIT 1");

        $refday = db::select("SELECT trans_date,resolution_no,SUBSTRING_INDEX(`resolution_no`,'-',1)AS 'year' ,SUBSTRING(SUBSTRING_INDEX(`resolution_no`,'-',-1),1,3) AS 'series',SUBSTRING(SUBSTRING_INDEX(`resolution_no`,'-',-1),4,3) AS 'ext' FROM " . $this->Bac . ".bacc_resolution
       where trans_date = '" . $datex . "' and stat = 0");

        if (count($refday) > 0) {
            foreach ($refday as $key => $value) {
                return response()->json(new JsonResponse(['data' => $value->year . "-" . $value->series . " " . $this->numberToLetter(count($refday) + 1)]));
            }
        } else {
            if (count($lastref) > 0) {
                // log::debug($lastref);
                foreach ($lastref as $key => $valuex) {
                    if ($valuex->year == date_format($dateFormated, "Y")) {
                        return response()->json(new JsonResponse(['data' => $valuex->year . "-" . $this->getSeries($valuex->series)]));
                    } else {
                        return response()->json(new JsonResponse(['data' => date_format($dateFormated, "Y") . "-001"]));
                    }
                }
            } else {
                return response()->json(new JsonResponse(['data' => date_format($dateFormated, "Y") . "-001"]));
            }
        }
    }

    public function getSeries($number)
    {
        $datx = db::select("SELECT LPAD((" . $number . " *1)+1,3,0) AS series");
        foreach ($datx as $key => $value) {
            return $datx =  $value->series;
        }
    }
    public function show(Request $request)
    {

        $date = db::select("SELECT DISTINCT(trans_date)AS 'date',COUNT(*) AS 'count',bacc_resolution.id FROM " . $this->Bac . ".bacc_resolution
        inner join " . $this->Bac . ".bacc_resolution_details on(bacc_resolution_details.resolution_id = bacc_resolution.id)
        WHERE `stat` = 0
        AND  bacc_resolution_details.status = 0
        and pr_ref like '%".$request->pr."%'
        GROUP BY trans_date");
        $final = array();
        foreach ($date as $key => $value) {

            $details = db::select('call ' . $this->Bac . '.rans_bacc_resolutions_per_date1(?,?)', [$value->date,"%".$request->pr."%"]);
             $dtlPR = array();
            foreach ($details as $keyD => $valueD ) {

                $pr = db::select('call ' . $this->Bac . '.rans_bacc_resolutions_per_prList(?,?)', [$valueD->id,"%".$request->pr."%"]);
                    // db::table($this->Bac .'.bacc_resolution_details')
                    // ->join($this->Bac . ".bacc_resolution", 'bacc_resolution.id', 'bacc_resolution_details.resolution_id')
                    // ->join($this->Proc . ".tbl_pr_main", 'tbl_pr_main.pr_no', 'bacc_resolution_details.pr_ref')
                    // ->where("bacc_resolution_details.resolution_id", $valueD->id)
                    // ->where("bacc_resolution_details.pr_ref",'like', "%".$request->pr."%")
                    // ->get();
                $prList = array();
                foreach ($pr as $keyL => $valueL) {
                    $items = db::table($this->Proc .'.tbl_pr_detail')
                    ->join($this->Proc . ".tbl_pr_main", 'tbl_pr_main.id', 'tbl_pr_detail.main_id')
                    ->select('item_name',"qty",'unit_measure', 'unit_cost', 'total_cost')
                    ->where("tbl_pr_detail.main_id", $valueL->pr_id)
                    ->get();

                    array_push($prList, array(
                        'id' => $valueL->id,
                        'pr_id' => $valueL->pr_id,
                        'pr_ref' => $valueL->pr_ref,
                        'title' => $valueL->title,
                        'AMOUNT' => $valueL->AMOUNT,
                        'items' => $items,
                        'deptx' => $valueL->dept,
                    ));
                }

            array_push($dtlPR, array(
                'id' => $valueD->id,
                'trans_date' => $valueD->trans_date,
                'resolution_no' => $valueD->resolution_no,
                'mop_name' => $valueD->mop_name,
                'remarks' => $valueD->remarks,
                'pr' => $prList,
            ));
            }

            $title = db::select('call ' . $this->Bac . '.getResolutionTitle(?)', [$value->date]);
            $titlez = "";
            foreach ($title as $key => $valuex) {
                $titlez = $valuex->resolution_title;
            }
            $datex = array(
                'date' => $value->date,
                'count' => $value->count,
                'remarks' => "",
                'details' => $dtlPR,
                'titlez' => $titlez,

            );
            array_push($final, $datex);
        }
        // $list = db::select('call ' . $this->Bac . '.rans_bacc_resolutions');
        return response()->json(new JsonResponse($final));
    }

    public function cancel($id)
    {
        db::table($this->Bac . '.bacc_resolution')
            ->where("id", $id)
            ->update(['stat' => 1]);
        return response()->json(new JsonResponse(['status' => 'success']));
    }
    public function cancelPR($id)
    {
        db::table($this->Bac . '.bacc_resolution_details')
            ->where('bacc_resolution_details.id', $id)
            ->update(['bacc_resolution_details.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function store(Request $request)
    {
        $form = $request->form;
        $selected = $request->selected;
        if ($form['id'] == 0) {
            db::table($this->Bac . '.bacc_resolution')->insert($form);
            $id = $this->G->pk();
            foreach ($selected as $value) {
                $form = array(
                    'resolution_id' => $id,
                    'pr_id' => $value['prid'],
                    'title' => $value['title'],
                    'pr_ref' => $value['pr_ref'],
                    'AMOUNT' => $value['AMOUNT'],
                );
                db::table($this->Bac . '.bacc_resolution_details')->insert($form);
            }
        } else {
            db::table($this->Bac . '.bacc_resolution')->where('id', $form['id'])->update($form);
            $id = $form['id'];
            db::table('bacc_resolution_details')->where('resolution_id', $id)->delete();
            foreach ($selected as $value) {
                $form = array(
                    'resolution_id' => $id,
                    'pr_id' => $value['prid'],
                    'title' => $value['title'],
                    'pr_ref' => $value['pr_ref'],
                    'AMOUNT' => $value['AMOUNT'],
                );
                db::table($this->Bac . '.bacc_resolution_details')->insert($form);
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function printProposal(Request $request)
    {
        try {
            // $main = 596;
            $main = $request->itm;
            $projectx = db::table($this->Proc . '.pow_main_individual')
                ->join($this->Proc . '.pow_sof_detail', 'pow_sof_detail.pow_id', 'pow_main_individual.id')
                ->select('*', DB::raw('pow_sof_detail.SOF_Description as description'))
                ->where('pow_main_individual.id', $main['pow_id'])
                ->get();
            $projectDatax = "";

            foreach ($projectx as $key => $value) {
                $projectDatax = $value;
            }

            $details = db::table($this->Proc . '.pow_detail_individual')
                ->where('pow_detail_individual.main_id', $main['pow_id'])
                ->get();
            $dtlData = "";

            foreach ($details as $key => $value) {
                $key += 1;
                $dtlData .= '
                <tr>
                    <td style="font-size:8pt" align="center">' . $key . '</td>
                    <td style="font-size:8pt" align="center">' . $value->qty . '</td>
                    <td style="font-size:8pt" align="center">' . $value->unit . '</td>
                    <td style="font-size:8pt" align="center">' . $value->description . '</td>
                    <td style="font-size:8pt" align="right">' . number_format($value->unit_cost, 2) . '</td>
                    <td style="font-size:8pt" align="right">' . number_format($value->total_cost, 2) . '</td>
                </tr>';
            }

            $Template = '<table width="100%" cellpadding="3">
            <tr>
            <br />
            <th width="30%" align="right"><img src="' . public_path() . '/img/logo1.png"  height="40" width="40"></th>
            <th width="40%" style="font-size:10pt;  word-spacing:30px" align="center">Republic of the Philippines
            <br />
                    Province of Cebu
            <br />
                   City of Naga</th>
            <th align="left"><img src="' . public_path() . '/img/Logo2.png"  height="45" width="65"></th>
            </tr>
            <tr>
                <td width="76%" align="right" style="font-size:8pt; border-bottom:3px solid black"><b>TXN #:</b></td>
                <td width="1%" align="right" style="font-size:8pt; border-bottom:3px solid black"><b></b></td>
                <td width="23%" align="left" style="font-size:8pt; border-bottom:3px solid black">' . $projectDatax->reference_no . '</td>
            </tr>
            <br />
            <tr>
                <td width="100%" align="center" style="font-size:11pt"><b>PROJECT PROPOSAL</b></td>
            </tr>
            <br />
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Date</b></td>
                <td width="80%" align="left" style="font-size:9pt">' . date_format(date_create($projectDatax->reference_date), "m/d/Y")  . '</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Name of Project</b></td>
                <td width="80%" align="left" style="font-size:9pt">' . $projectDatax->project_title . '</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Source of Fund</b></td>
                <td width="80%" align="left" style="font-size:9pt">' . $projectDatax->description . '</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Amount</b></td>
                <td width="80%" align="left" style="font-size:9pt">' . number_format($projectDatax->appropriation, 2) . '</td>
            </tr>
            <tr>
                <td width="2%" align="left" style="font-size:9pt"></td>
                <td width="18%" align="left" style="font-size:9pt"><b>Rationale</b></td>
                <td width="80%" align="left" style="font-size:9pt">' . $projectDatax->rationale . '</td>
            </tr>
            <br />
            <br />
            <br />
            <tr>
                <td width="100%" align="left" style="font-size:9pt"><b>Details:</b></td>
            </tr>
            <table border=".3" width="100%" cellpadding="3">
                <tr>
                    <td width="7%" style="font-size:9pt; text-align: center"><b>Item No.</b></td>
                    <td width="10%" style="font-size:9pt; text-align: center"><b>Quantity</b></td>
                    <td width="11%" style="font-size:9pt; text-align: center"><b>Unit Measure</b></td>
                    <td width="48%" style="font-size:9pt; text-align: center"><b>Articles and Description</b></td>
                    <td width="12%" style="font-size:9pt; text-align: center"><b>Unit Cost</b></td>
                    <td width="12%" style="font-size:9pt; text-align: center"><b>Total Cost</b></td>
                </tr>
                ' . $dtlData . '
                <tr>
                    <td width="7%" style="font-size:8pt; text-align: center"></td>
                    <td width="10%" style="font-size:8pt; text-align: center"></td>
                    <td width="11%" style="font-size:8pt; text-align: center"></td>
                    <td width="48%" style="font-size:8pt; text-align: center">***nothing follows***</td>
                    <td width="12%" style="font-size:8pt; text-align: right"></td>
                    <td width="12%" style="font-size:8pt; text-align: right"></td>
                </tr>
                <tr>
                    <td width="17%" style="font-size:8pt; text-align: left"><b>DELIVERY TERM:</b></td>
                    <td width="59%" style="font-size:8pt; text-align: left">' . $projectDatax->project_desc . '</td>
                    <td width="12%" style="font-size:8pt; text-align: right"><b>Total:</b></td>
                    <td width="12%" style="font-size:8pt; text-align: right"><b>' . number_format($projectDatax->appropriation, 2) . '</b></td>
                </tr>
            </table>

            </table>
            ';
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Prepared by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Requested by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Prepared by:</td>
            //     </tr>

            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>

            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Admin. Aide I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Admin. Aide I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Admin. Aide III</td>
            //     </tr>
            // <br />
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Verified by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Recommending Approval:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Ok as to Appropriation:</td>
            //     </tr>
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">CGDH I (City Government Department</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Administrator II</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Budget Officer</td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Head) I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left"></td>
            //     </tr>
            // <br />
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Ok as to Fund:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Approved by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Noted by:</td>
            //     </tr>
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Treasurer I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Mayor</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Association of Barangay Councils President</td>
            //     </tr>

            PDF::SetTitle('Project Proposal');
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
    public function printofficial(Request $request)
    {
        try {

            $main = $request->itm;
            $id = $main['id'];
            $projectx = db::table($this->Bac . '.bacc_resolution')
                ->where('bacc_resolution.id', $main['id'])
                ->get();
            $projectDatax = "";

            foreach ($projectx as $key => $value) {
                $projectDatax = $value;
            }

            $present = db::table($this->Bac . '.bac_attendancemember')
                ->where('bac_attendancemember.category', 'member')
                ->where('bac_attendancemember.present', 'true')
                ->where('mem_id', $main['id'])
                ->get();

            $presentx = "";
            foreach ($present as $key => $value) {
                $presentx .= '  <tr>
                    <td width="20%" style="font-size:8pt;"></td>
                    <td width="35%" style="font-size:8pt;">' . $value->emp_name . '</td>
                    <td width="3%" style="font-size:8pt;"></td>
                    <td width="35%" style="font-size:8pt;">' . $value->position . '</td>

                </tr>';
            }


            $present = db::table($this->Bac . '.bac_attendancemember')
                ->where('bac_attendancemember.category', 'TWG')
                ->where('bac_attendancemember.present', 'true')
                ->where('mem_id', $main['id'])
                ->get();

            $TWG = "";
            foreach ($present as $key => $value) {
                $TWG .= '
                <tr>
                <td width="20%" style="font-size:8pt;"></td>
                <td width="35%" style="font-size:8pt;">' . $value->emp_name . '</td>
                <td width="3%" style="font-size:8pt;"></td>
                <td width="35%" style="font-size:8pt;">' . $value->position . '</td>
            </tr>';
            }

            $present = db::table($this->Bac . '.bac_attendancemember')
                ->where('bac_attendancemember.present', 'false')
                ->where('mem_id', $main['id'])
                ->get();

            $absent = "";
            foreach ($present as $key => $value) {
                $absent .= '
                <tr>
                <td width="20%" style="font-size:8pt;"></td>
                <td width="35%" style="font-size:8pt;">' . $value->emp_name . '</td>
                <td width="3%" style="font-size:8pt;"></td>
                <td width="35%" style="font-size:8pt;">' . $value->position . '</td>
            </tr>';
            }

            $data = db::select('call ' . $this->Bac . '.rans_bacc_resolutions_per_prPrint(?)', [$id]);
                // db::table($this->Bac . '.bacc_resolution_details')
                // ->join($this->Proc . '.tbl_pr_main', 'tbl_pr_main.id', 'bacc_resolution_details.pr_id')
                // ->leftjoin($this->Proc . '.tbl_pr_detail', 'tbl_pr_detail.main_id', 'tbl_pr_main.id')

                // // ->select('*' , 'unit_cost + total_cost as Total' )
                // ->select("*", db::raw("SUM(total_cost) as Total"), db::raw("(pr_ref) AS 'PR'"))
                // ->where('resolution_id', $main['id'])
                // ->groupBy('bacc_resolution_details.id')
                // ->get();
            // $total = $value->unit_cost + $value->total_cost;
            $tableData = "";
            $department = "";
            foreach ($data as $key => $value) {
                log::debug($value->item_name);
                if ($department !== $value->department) {
                    $tableData .= '<tr>
                    <td rowspan="'. $value->countx.'" width="15%" style="font-size:9pt;" align="center"><b><br />' . $value->department . '<br /></b></td>
                    <td width="55%" align="left" style="font-size:8pt;"><br />' . $value->title . '</td>
                    <td width="15%" align="center" style="font-size:8pt;">' . $value->PR . '</td>
                    <td width="15%" align="right" style="font-size:8pt;">' . number_format($value->total_cost, 2) . '</td>
                    </tr>';
                } else {

                $tableData .= ' <tr>
                <td width="55%" align="left"><br />' . $value->title . '</td>
                <td width="15%" align="center">' . $value->PR . '</td>
                <td width="15%" align="right">' . number_format($value->total_cost, 2) . '</td>
                </tr>';
            }
                $department = $value->department;
            }

            log::debug(2);
            $tableData1 = "";

            foreach ($data as $key => $valuez) {

                $tableData1 .= ' <tr>
                <td><br />' . $valuez->title . '</td>
                <td align="center">' . $valuez->PR . '</td>
                <td align="right">' . number_format($valuez->total_cost, 2) . '</td>
            </tr>
                ';
            }
            log::debug(3);
            $names = "";
            $dataN = db::table($this->Bac . '.bac_members')

                ->where('bac_members.sig', '>', 0)
                ->orderBy('sig', 'ASC')
                ->get();

            $name1 = "";
            $position1 = "";
            $name2 = "";
            $position2 = "";
            $name3 = "";
            $position3 = "";
            $name4 = "";
            $position4 = "";
            $name5 = "";
            $position5 = "";
            log::debug(1);
            foreach ($dataN as $key => $valuez) {

                if ($key === 0) {
                    $name1 = $valuez->emp_name;
                    $position1 = $valuez->sig_position;
                }
                if ($key === 1) {

                    $name2 = $valuez->emp_name;
                    $position2 = $valuez->sig_position;
                }
                if ($key === 2) {

                    $name3 = $valuez->emp_name;
                    $position3 = $valuez->sig_position;
                }
                if ($key === 3) {

                    $name4 = $valuez->emp_name;
                    $position4 = $valuez->sig_position;
                }
                if ($key === 4) {

                    $name5 = $valuez->emp_name;
                    $position5 = $valuez->sig_position;
                }
            }


            // foreach ($dataN as $key => $valuez) {

            //     $names .='    <tr>
            //     <td width="15%" style="font-size:9pt;">

            //     </td>
            //     <td width="38.5%" style="font-size:9pt;">
            //    <b> '.$valuez->emp_name.' </b>
            //     </td>
            //     <td width="8%" style="font-size:9pt;">

            //     </td>
            //     <td width="38.5%" style="font-size:9pt;">
            //     <b> '.$valuez->emp_name.' </b>
            //      </td>
            // </tr>

            //     ';
            // }


            $Template = '  <table width="100%">

            <tr>
                <td width="19%">

                </td>
                <td width="62%">
                    <table width="100%">
                    <tr>
                    <td align="center">
                        <img src="' . public_path() . '/images/Logo1.png"  height="45" width="55">
                    </td>

                </tr>
                        <tr>
                             <td align="center" style="font-size:10pt;" >
                             Republic of the Philippines
                                </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                            Province of Cebu
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;" >
                           City of Naga
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:10pt;">
                            <b>OFFICE OF THE BIDS AND AWARDS COMMITTEE</b>
                            </td>
                        </tr>

                    </table>
                </td>
                <td width="19%">
                </td>

                </tr>

            </table>
            ';

            $Template .= '<table width="100%">
                            <tr>
                                <td width="5%"></td>
                                <td width="90%">
                                    <table width="100%">
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <tr>
                                             <td width="100%"></td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:9pt;" align="right"><b><u> RESOLUTION NO. ' . $projectDatax->resolution_no . ' </u></b></td>
                                        </tr>
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:9pt;">
                                                <b>EXCERPT FROM THE MINUTES OF THE OPENING OF BIDS
                                                HELD LAST JUNE 22, 2022 AT THE BAC OFFICE,</b>
                                            </td>
                                        </tr>
                                        <tr>
                                        <td style="font-size:9pt;">
                                            <b>2ND FLOOR, CITY HALL BLDG., EAST PORBLACION, CITY OF NAGA, CEBU</b>
                                        </td>
                                    </tr>
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <tr>
                                            <td width="15%" style="font-size:8pt;" align="right">PRESENT:</td>
                                        </tr>
                                        ' . $presentx . '

                                        <tr>
                                        <td width="15%" style="font-size:8pt;"></td>
                                        <td width="35%" style="font-size:8pt;">TECHNICAL WORKING GROUP:</td>
                                    </tr>
                                    ' . $TWG . '
                                    <tr>
                                        <td width="15%" style="font-size:8pt;" align="right">ABSENT:</td>
                                    </tr>
                                    ' . $absent . '
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:9pt;">
                                                <b>RESOLUTION RECOMMENDING APPROVAL TO ENTER INTO NEGOTIATED PROCUREMENT
                                                THROUGH</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:9pt;">
                                                <b>SMALL VALUE PROCUREMENT AS ALTERNATIVE METHOD OF PROCUREMENT </b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <tr>
                                            <td width="16%" style="font-size:9pt;">
                                                <b>WHEREAS,  </b>
                                            </td>
                                            <td width="84%" style="font-size:9pt;">
                                                the following offices requested for the purchase of the corresponding supplies/materials/services;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"></td>
                                        </tr>
                                        <table width="100%" border="1" cellpadding="2">
                                            <tr>
                                                <td width="15%" align="center"><b> Requesting Office </b></td>
                                                <td width="55%" align="center"><b> Item Description </b></td>
                                                <td width="15%" align="center"><b> Purchase Request (PR) No.</b></td>
                                                <td width="15%" align="center"><b> PR Amount </b></td>
                                            </tr>
                                           ' . $tableData . '

                                        </table>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>

                                        <tr>
                                            <td width="16%" style="font-size:9pt;">
                                                <b>WHEREAS,  </b>
                                            </td>
                                            <td width="84%" style="font-size:9pt;">
                                                the Local Chief Executive has approved the request to purchase the items found in the respective
                                            </td>
                                        </tr>
                                        <tr>
                                        <td width="16%" style="font-size:9pt;">

                                        </td>
                                        <td width="84%" style="font-size:9pt;">
                                            Purchase Requests;
                                        </td>
                                    </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="16%" style="font-size:9pt;">
                                                <b>WHEREAS,  </b>
                                            </td>
                                            <td width="84%" style="font-size:9pt;">
                                                the BAC conducted a thorough verification, evaluation and careful deliberation on the said matter
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="16%" style="font-size:9pt;">
                                                <b>WHEREAS,  </b>
                                            </td>
                                            <td width="84%" style="font-size:9pt;">
                                               pursuant to Section 48 in relation to 53.9 of the 2016 Revised Implementing Rules and Regulations of Republic Act No. 9184,
                                                otherwise known as the "Government Procurement Reform Act", the procuring entity, in this case, the City Government of Naga,
                                                in order to promote economy and efficiency, may resort to Negotiated Procurement through <i>Small Value Procurement</i> through Small Value Procurement,
                                                as an alternative mode of procurement for Goods and Services provided that in case of goods, the procurement does not fall under shopping;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="16%" style="font-size:9pt;">
                                                <b>WHEREAS,  </b>
                                            </td>
                                            <td width="84%" style="font-size:9pt;">
                                                after thorugh verification, evaluation and deliveration,
                                                 the Bids & Awards Committee unanimously agreed that negotiated procurement through small value procurement as an alternative
                                                  mode of procurement is more appropriate to the government fo the purchase of the aforesaid items.
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%" style="font-size:9pt;">
                                                <b>NOW, THEREFORE,  </b> considering the above premises,
                                                the Bids and Awards Committee hereby RESOLVE as it is
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width=".1%" style="font-size:9pt;">
                                            </td>
                                            <td width="99.9%" style="font-size:9pt;">
                                            hereby RESOLVED:
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <table width="100%" border="1" cellpadding="2">
                                            <tr>
                                                <td width="60%" style="font-size:9pt" align="center"><b>Item Description</b></td>
                                                <td width="20%" style="font-size:9pt" align="center"><b>PR Number</b></td>
                                                <td width="20%" style="font-size:9pt" align="center"><b>PR Amount</b></td>
                                            </tr>
                                            ' . $tableData1 . '

                                        </table>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>

                                        <tr>
                                            <td width="2%" style="font-size:9pt;">
                                            </td>
                                            <td width="98%" style="font-size:9pt;">
                                           2. To recommend for approval by the Head of the Procuring Entity (HoPE) of the City of Naga the foregoing findings.
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>


                                        <tr>
                                             <td width="9%" style="font-size:9pt;" >

                                             </td>
                                             <td width="39%" style="font-size:9pt;" align="center">
                                            <b> ' . $name2 . ' </b>
                                             </td>
                                             <td width="4%" style="font-size:9pt;">

                                             </td>
                                             <td width="39%" style="font-size:9pt;" align="center">
                                             <b> ' . $name1 . ' </b>
                                              </td>
                                              <td width="9%" style="font-size:9pt;" >

                                              </td>
                                         </tr>
                                         <tr>
                                            <td width="9%" style="font-size:9pt;" >

                                            </td>
                                            <td width="39%" style="font-size:9pt;" align="center">
                                            ' . $position1 . '
                                            </td>
                                            <td width="4%" style="font-size:9pt;">

                                            </td>
                                            <td width="39%" style="font-size:9pt;" align="center">
                                            ' . $position2 . '
                                            </td>
                                            <td width="9%" style="font-size:9pt;" >

                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>

                                            <td width="33.1%" style="font-size:9pt;" align="center">
                                            <b> ' . $name3 . ' </b>
                                            </td>
                                            <td width="33.2%" style="font-size:9pt;" align="center">
                                            <b> ' . $name4 . ' </b>
                                            </td>
                                            <td width="33.2%" style="font-size:9pt;" align="center">
                                            <b> ' . $name5 . ' </b>
                                            </td>
                                        </tr>
                                        <tr>

                                            <td width="33.1%" style="font-size:9pt;" align="center">
                                            ' . $position3 . '
                                            </td>
                                            <td width="33.2%" style="font-size:9pt;" align="center">
                                            ' . $position4 . '
                                            </td>
                                            <td width="33.2%" style="font-size:9pt;" align="center">
                                            ' . $position5 . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%" style="font-size:9pt;" align="center">
                                            Approved by:
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%"> </td>
                                        </tr>
                                        <tr>
                                            <td width="100%" align="center" style="font-size:9pt;">
                                            <b>VALDEMAR M. CHIONG</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100%" align="center" style="font-size:9pt;" >
                                           City Mayor
                                            </td>
                                        </tr>

                                    </table>
                                </td>
                                <td width="5%"></td>
                            </tr>






            </table>';
            PDF::SetTitle('Official\'s Attendance');
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
    function print(Request $request)
    {
        try {
            $Template = $this->printPR($request);

            PDF::AddPage('P', array(215.9, 355.6));
            PDF::lastPage();
            PDF::SetFont('Helvetica', '', 9);
            PDF::SetTitle('Resolution');
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::AddPage('P', array(215.9, 355.6));
            $Template2 = $this->PrintPP($request);
            PDF::SetFont('Helvetica', '', 10);
            PDF::writeHTML($Template2, true, 0, true, 0);
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
    public function printPR($request)
    {
        try {
            // $form = 413;
            $form = $request->itm;
            $main = db::table($this->Proc . '.tbl_pr_main')
                ->where('id', $form['prid'])
                ->get();
            // $main =db::select("CALL" . $this->Proc_db.".getPR_main()");

            $mainData = "";

            foreach ($main as $key => $value) {
                $mainData = $value;
            }

            $mainx = db::table($this->Proc . '.tbl_pr_detail')
                ->where('main_id', $form['prid'])
                ->get();
            $mainDatax = "";

            $totalx = 0;
            $x = 1;

            $sof = db::select("SELECT GROUP_CONCAT(`Description1`) AS 'codex' FROM " . $this->Proc . ".pr_sof_detail
            INNER JOIN " . $this->Proc . ".tbl_pr_main ON(tbl_pr_main.`id` = pr_sof_detail.`prid`)
            INNER JOIN `budget`.`accountbudget` ON(accountbudget.`ID` = pr_sof_detail.`SOF_ID`)
            WHERE `tbl_pr_main`.`id` =" . $form['prid']);
            $sof_code = "";
            // log::debug("asdsad" . $form['prid']);
            foreach ($sof as $key => $value) {
                // log::debug($value->codex);
                $sof_code = $value->codex;
            }
            foreach ($mainx as $key => $value) {
                $z = $x++;
                $totalx = $totalx + $value->total_cost;
                $mainDatax .= '
                    <tr>
                <td width="7%" height="12px" style="border-bottom:1px solid black; border-right:1px solid black;
                border-left:1px solid black" align="center">' . $z . '</td>
                <td width="7%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . $value->qty . '</td>
                <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . $value->unit_measure . '</td>
                <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . $value->item_name . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . number_format($value->unit_cost, 2) . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center">' . number_format($value->total_cost, 2) . '</td>
            </tr>';
            }

            // if(count($mainx)< 28){
            //         for($i = count($mainx); $i<28; $i++){
            //             $mainDatax .=' <tr>
            //             <td width="7%" height="12px" style="border-bottom:1px solid black; border-right:1px solid black;
            //             border-left:1px solid black" align="center"></td>
            // <td width="7%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>

            // <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            // </tr>' ;
            //         }
            //     }

            $Template = '
            <style>
               .container {
                position: relative;
                text-align: center;
                color: black;
              }
              .bottom-left {
                position: absolute;
                bottom: 8px;
                left: 16px;
              }

              .top-left {
                position: absolute;
                top: 8px;
                left: 16px;
              }

              .top-right {
                position: absolute;
                top: 8px;
                right: 16px;
              }

              .bottom-right {
                position: absolute;
                bottom: 8px;
                right: 16px;
              }

              .centered {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
              }

            </style>

            <table cellpadding="1">
                <tr>
                    <th width="35%" align="right">
                    <img src="' . public_path() . '/img/logo1.png"  height="30" width="30">
                    </th>
                    <th width="35%" style="font-size:11pt;  word-spacing:30px" align="center">
                            Republic of the Philippines
                    <br />
                            Province of Cebu
                    <br />

                        CIty of Naga
                    <br />
                    <br />
                        </th>

                    <th align="left">
                    <img src="' . public_path() . '/img/logo2.png"  height="35" width="60">
                    </th>
                 </tr>
                 <tr>
                 <td width="70%"></td>
                 <td width="7%"><b>TXN #:</b></td>
                 <td width="23%">' . $mainData->txn_num . '</td>
         </tr>
                </table >
        <table cellpadding="2">
            <tr>
            <td width="100%" height="18px" style="border-bottom:1px solid black; border-top:1px solid black;
                        border-right:1px solid black;  border-left:1px solid black; font-size:12pt" align="center">
                        <b>PURCHASE REQUEST</b></td>
            </tr>
            <tr>
                <td width="79%" height="13px" align="right" style="border-bottom:1px solid black; border-right:1px solid black;
                                border-left:1px solid black ">
                <b><i>Account Code: </i></b>
                </td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black">' .  $sof_code . '</td>
            </tr>
            <tr>
                <td width="14%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Department:</b></td>
                <td width="48%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->dept . '</td>
                <td width="17%" style="border-bottom:1px solid black; border-right:1px solid black"><b> PR No:</b></td>
                <td width="21%"  style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->pr_no . '</td>
            </tr>
            <tr>
                <td width="62%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"></td>
                <td width="17%" style="border-bottom:1px solid black; border-right:1px solid black"><b> PR Date:</b></td>
                <td width="21%"  style="border-bottom:1px solid black; border-right:1px solid black">' . date_format(date_create($mainData->pr_date), "m/d/Y") . '</td>
            </tr>
            <tr>
                <td width="58%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black"></td>
                <td width="21%"  style="border-bottom:1px solid black; border-right:1px solid black"></td>
            </tr>
            <tr>
                <td width="14%" height="13px" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Section:</b></td>
                <td width="44%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->section_name . '</td>
                <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black"><b> SAI No:</b></td>
                <td width="13%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->sai_no . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black"><b> Date:  ' . date_format(date_create($mainData->sai_date), "m/d/Y") . '</b></td>
            </tr>
             <tr>
                <th rowspan="1.5" width="7%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black; background-color:#bdbdbd  " align="center"><b>Item No.</b></th>
                <td width="7%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Qty</b></td>

                <td  width="8%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Unit of Measure</b></td>
                <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><br /><b>Item Description</b></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Estimated Unit Cost</b></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black; background-color:#bdbdbd  " align="center"><b>Estimated Cost</b></td>
            </tr>
          ' . $mainDatax . '
            <tr>
                <td width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black" ><b> Delivery Term:</b></td>
                <td width="36%" style="border-bottom:1px solid black; border-right:1px solid black" >' . $mainData->terms . '</td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black; font-size:10pt" align="center"><b>TOTAL</b></td>
                <td width="21%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"><b>' . number_format($totalx, 2) . '</b></td>
            </tr>
            <tr>
                <td width="22%" style="border-bottom:1px solid black; border-right:1px solid black;
                            border-left:1px solid black"><b> Purpose:</b></td>
                <td width="78%" style="border-bottom:1px solid black; border-right:1px solid black">' . $mainData->pr_description . '</td>
            </tr>

        </table>
               ';
            //        <tr>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center"><b> Requested By</b></td>
            //        <td width="34%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center"><b> Cash Availability</b></td>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center"><b> Approved</b></td>
            //    </tr>
            //    <tr>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black"></td>
            //        <td width="34%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black"></td>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black"></td>
            //    </tr>
            //    <tr>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center">ROWENA REPOLLO ARNOZA</td>
            //        <td width="34%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center">ANNA MARIA BACON GABILAN</td>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center">ATTY. KRISTINE VANESSA TADIWAN CHIONG</td>
            //    </tr>
            //    <tr>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center"><b>CGDH I (City Government Department Head) I</b></td>
            //        <td width="34%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center"><b>City Treasurer I</b></td>
            //        <td width="33%" style="border-bottom:1px solid black; border-right:1px solid black; border-left:1px solid black" align="center"><b>City Mayor</b></td>
            //    </tr>
            //    <tr>
            //        <td height="12px" width="52%" align="center" style="border-bottom:1px solid black; border-right:1px solid black;border-left:1px solid black"><b> OK AS TO APPROPRIATION</b></td>
            //        <td width="48%" align="center" style="border-bottom:1px solid black; border-right:1px solid black"><b> OK AS TO ALLOTMENT</b></td>
            //    </tr>
            //    <tr>
            //        <td height="20px" align="center" width="52%" style="border-bottom:1px solid black; border-right:1px solid black;
            //                    border-left:1px solid black"></td>
            //        <td width="48%" style="border-bottom:1px solid black; border-right:1px solid black" align="center"></td>
            //    </tr>
            //    <tr>
            //        <td height="12px" align="center" width="52%" style="border-bottom:1px solid black; border-right:1px solid black;
            //                 border-left:1px solid black; font-size:7pt" align="center">CERTERIA VILLARICO BUENAVISTA</td>
            //        <td width="48%" style="border-bottom:1px solid black; border-right:1px solid black; font-size:7pt"
            //                 align="center">KELVIN RAY LAPINING ABABA</td>
            //        </tr>
            //    <tr>
            //        <td height="12px" align="center" width="52%" style="border-bottom:1px solid black; border-right:1px solid black;
            //                 border-left:1px solid black" align="center"><b>Budget Officer</b></td>
            //        <td width="48%" align="center" style="border-bottom:1px solid black; border-right:1px solid black"
            //                 align="center"><b>City Accountant</b></td>
            //    </tr>
            $Template .= $this->signatory("Purchase Request", $form['prid']);


            // PDF::SetTitle('Pending Works');
            // PDF::SetFont('helvetica', '', 8);
            // PDF::AddPage('P');
            // PDF::writeHTML($Template, true, 0, true, 0);
            // PDF::Output(public_path() . '/prints.pdf', 'F');
            // $full_path = public_path() . '/prints.pdf';
            // if (\File::exists(public_path() . '/prints.pdf')) {
            //     $file = \File::get($full_path);
            //     $type = \File::mimeType($full_path);
            //     $response = \Response::make($file, 200);
            //     $response->header("Content-Type", $type);
            //     return $response;
            // }
            return $Template;
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function signatory($frm, $id)
    {
        $list = db::table($this->Proc . ".gso_signatories_value")
            ->where("FrmName", $frm)
            ->where("tranID", $id)->get();

        $sig1 = "";
        $sigName1 = "";
        $sigPos1 = "";

        $sig2 = "";
        $sigName2 = "";
        $sigPos2 = "";

        $sig3 = "";
        $sigName3 = "";
        $sigPos3 = "";

        $sig4 = "";
        $sigName4 = "";
        $sigPos4 = "";

        $sig5 = "";
        $sigName5 = "";
        $sigPos5 = "";

        $sig6 = "";
        $sigName6 = "";
        $sigPos6 = "";

        $sig7 = "";
        $sigName7 = "";
        $sigPos7 = "";

        foreach ($list as $key => $value) {
            // log::debug($key);
            if ($key == 0) {
                $sig1 = $value->SignatoryType;
                $sigName1 = $value->SignatoryName;
                $sigPos1 = $value->Position;
            }
            if ($key == 1) {
                $sig2 = $value->SignatoryType;
                $sigName2 = $value->SignatoryName;
                $sigPos2 = $value->Position;
            }
            if ($key == 2) {
                $sig3 = $value->SignatoryType;
                $sigName3 = $value->SignatoryName;
                $sigPos3 = $value->Position;
            }
            if ($key == 3) {
                $sig4 = $value->SignatoryType;
                $sigName4 = $value->SignatoryName;
                $sigPos4 = $value->Position;
            }
            if ($key == 4) {
                $sig5 = $value->SignatoryType;
                $sigName5 = $value->SignatoryName;
                $sigPos5 = $value->Position;
            }
            if ($key == 5) {
                $sig6 = $value->SignatoryType;
                $sigName6 = $value->SignatoryName;
                $sigPos6 = $value->Position;
            }
            if ($key == 6) {
                $sig7 = $value->SignatoryType;
                $sigName7 = $value->SignatoryName;
                $sigPos7 = $value->Position;
            }
        }
        $str = '<br/><br/><br/><table  cellpadding="5"  width ="100%" st  align="center">
          <tr>
           <td>
            <table>
             <tr>
               <td><b>' . $sig1 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
              <tr>
               <td>' . $sigName1 . '</td>
             </tr>
             <tr>
               <td><b>' . $sigPos1 . '</b></td>
             </tr>
            <tr>
               <td></td>
             </tr>
            </table>
           </td>
           <td>
            <table>
             <tr>
                 <td><b>' . $sig2 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
              <tr>
               <td>' . $sigName2 . '</td>
             </tr>
             <tr>
               <td><b>' . $sigPos2 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
            </table>
           </td>
            <td>
            <table>
             <tr>
               <td><b>' . $sig3 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
              <tr>
               <td>' . $sigName3 . '</td>
             </tr>
             <tr>
               <td><b>' . $sigPos3 . '</b></td>
             </tr>
            <tr>
               <td></td>
             </tr>
            </table>
           </td>
          </tr>
           <tr>
           <td>
            <table>
             <tr>
               <td><b>' . $sig4 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
              <tr>
               <td>' . $sigName4 . '</td>
             </tr>
             <tr>
               <td><b>' . $sigPos4 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
            </table>
           </td>
           <td>
            <table>
             <tr>
               <td><b>' . $sig5 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
              <tr>
               <td>' . $sigName5 . '</td>
             </tr>
             <tr>
               <td><b>' . $sigPos5 . '</b></td>
             </tr>
            <tr>
               <td></td>
             </tr>
            </table>
           </td>
            <td>
            <table>
             <tr>
               <td><b>' . $sig6 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
              <tr>
               <td>' . $sigName6 . '</td>
             </tr>
             <tr>
               <td><b>' . $sigPos6 . '</b></td>
             </tr>
             <tr>
               <td></td>
             </tr>
            </table>
           </td>
          </tr>

          <tr>
          <td>
           <table>
            <tr>
              <td><b>' . $sig7 . '</b></td>
            </tr>
            <tr>
              <td></td>
            </tr>
             <tr>
              <td>' . $sigName7 . '</td>
            </tr>
            <tr>
              <td><b>' . $sigPos7 . '</b></td>
            </tr>
            <tr>
              <td></td>
            </tr>
           </table>
          </td>
         </tr>
        </table>';
        return  $str;
    }
    public function PrintPP($request)
    {
        try {
            $form = $request->itm;
            $projectx = db::table($this->Proc . '.pow_main_individual')
                ->join($this->Proc . '.pow_sof_detail', 'pow_sof_detail.pow_id', 'pow_main_individual.id')
                ->select('*', DB::raw('pow_sof_detail.SOF_Description as description'), 'pow_main_individual.id')
                ->where('pow_main_individual.id', $form['pow_id'])
                ->get();
            $projectDatax = "";

            foreach ($projectx as $key => $value) {
                $projectDatax = $value;
            }

            $details = db::table($this->Proc . '.pow_detail_individual')
                ->where('pow_detail_individual.main_id', $form['pow_id'])
                ->get();
            $dtlData = "";


            foreach ($details as $key => $value) {
                $key += 1;
                $dtlData .= '
                    <tr>
                        <td align="center">' . $key . '</td>
                        <td align="center">' . $value->qty . '</td>
                        <td align="center">' . $value->unit . '</td>
                        <td align="center">' . $value->description . '</td>
                        <td align="right">' . number_format($value->unit_cost, 2) . '</td>
                        <td align="right">' . number_format($value->total_cost, 2) . '</td>
                    </tr>';
            }

            $Template = '<table width="100%" cellpadding="3">
                <tr>
                <br />
                <th width="30%" align="right"><img src="' . public_path() . '/img/logo1.png"  height="40" width="40"></th>
                <th width="40%" style="font-size:11pt;  word-spacing:30px" align="center">Republic of the Philippines
                <br />
                        Province of Cebu
                <br />
                       City of Naga</th>
                <th align="left"><img src="' . public_path() . '/img/Logo2.png"  height="45" width="65"></th>
                </tr>
                <tr>
                    <td width="76%" align="right" style="font-size:8pt; border-bottom:3px solid black"><b>TXN #:</b></td>
                    <td width="1%" align="right" style="font-size:8pt; border-bottom:3px solid black"><b></b></td>
                    <td width="23%" align="left" style="font-size:8pt; border-bottom:3px solid black">' . $projectDatax->TXN . '</td>
                </tr>
                <br />
                <tr>
                    <td width="100%" align="center" style="font-size:11pt"><b>PROJECT PROPOSAL</b></td>
                </tr>
                <br />
                <tr>
                    <td width="2%" align="left"></td>
                    <td width="18%" align="left"><b>Date</b></td>
                    <td width="80%" align="left">' . date_format(date_create($projectDatax->reference_date), "F d, Y")  . '</td>
                </tr>
                <tr>
                    <td width="2%" align="left"></td>
                    <td width="18%" align="left"><b>Name of Project</b></td>
                    <td width="80%" align="left">' . $projectDatax->project_title . '</td>
                </tr>
                <tr>
                    <td width="2%" align="left"></td>
                    <td width="18%" align="left"><b>Source of Fund</b></td>
                    <td width="80%" align="left">' . $projectDatax->description . '</td>
                </tr>
                <tr>
                    <td width="2%" align="left"></td>
                    <td width="18%" align="left"><b>Amount</b></td>
                    <td width="80%" align="left">' . number_format($projectDatax->appropriation, 2) . '</td>
                </tr>
                <tr>
                    <td width="2%" align="left"></td>
                    <td width="18%" align="left"><b>Rationale</b></td>
                    <td width="80%" align="left">' . $projectDatax->rationale . '</td>
                </tr>
                <br />
                <tr>
                    <td width="100%" align="left"></td>
                </tr>
                <tr>
                  <td>
                  <table border=".3" width="100%" cellpadding="3">
                  <tr>
                      <td width="7%" style="text-align: center"><b>Item No.</b></td>
                      <td width="10%" style="text-align: center"><b>Quantity</b></td>
                      <td width="11%" style="text-align: center"><b>Unit Measure</b></td>
                      <td width="48%" style="text-align: center"><b>Articles and Description</b></td>
                      <td width="12%" style="text-align: center"><b>Unit Cost</b></td>
                      <td width="12%" style="text-align: center"><b>Total Cost</b></td>
                  </tr>
                  ' . $dtlData . '
                  <tr>
                      <td width="7%" style="text-align: center"></td>
                      <td width="10%" style="text-align: center"></td>
                      <td width="11%" style="text-align: center"></td>
                      <td width="48%" style="text-align: center">***nothing follows***</td>
                      <td width="12%" style="text-align: right"></td>
                      <td width="12%" style="text-align: right"></td>
                  </tr>
                  <tr>
                      <td width="17%" style="text-align: left"><b>DELIVERY TERM:</b></td>
                      <td width="59%" style="text-align: left">' . $projectDatax->project_desc . '</td>
                      <td width="12%" style="text-align: right"><b>Total:</b></td>
                      <td width="12%" style="text-align: right"><b>' . number_format($projectDatax->appropriation, 2) . '</b></td>
                  </tr>
              </table>
                  </td>
                </tr>


                </table>
                ';
            $Template .= $this->signatory("Project Proposal", $form['pow_id']);
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Prepared by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Requested by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Prepared by:</td>
            //     </tr>

            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>

            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Admin. Aide I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Admin. Aide I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Admin. Aide III</td>
            //     </tr>
            // <br />
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Verified by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Recommending Approval:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Ok as to Appropriation:</td>
            //     </tr>
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">CGDH I (City Government Department</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Administrator II</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Budget Officer</td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Head) I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left"></td>
            //     </tr>
            // <br />
            // <br />
            // <br />
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;  text-align: left">Ok as to Fund:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">Approved by:</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Noted by:</td>
            //     </tr>
            // <br />
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left"><b></b></td>
            //     </tr>
            //     <tr>
            //         <td width="2%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Treasurer I</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt; text-align: left">City Mayor</td>
            //         <td width="6%" style="font-size:8pt; text-align: left"></td>
            //         <td width="28%" style="font-size:8pt;text-align: left">Association of Barangay Councils President</td>
            //     </tr>

            // PDF::SetTitle('Project Proposal');
            // PDF::SetFont('helvetica', '', 8);
            // PDF::AddPage('P');
            // PDF::writeHTML($Template, true, 0, true, 0);
            // PDF::Output(public_path() . '/prints.pdf', 'F');
            // $full_path = public_path() . '/prints.pdf';
            // if (\File::exists(public_path() . '/prints.pdf')) {
            //     $file = \File::get($full_path);
            //     $type = \File::mimeType($full_path);
            //     $response = \Response::make($file, 200);
            //     $response->header("Content-Type", $type);
            //     return $response;
            // }
            return $Template;
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function viewDocAll(Request $request)
    {

        if (File::exists(public_path() . '/merged.pdf')) {
            unlink(public_path() . '/merged.pdf');
        }
        $merger = PDFMerger::init();
        $par = $request->parx;
        $count = 0;
        foreach ($par as $key => $value) {
            $path = '../storage/files/gso/' . $value['pathx'];
            if (File::exists($path)) {
                $merger->addPDF($path, 'all');
                $count += 1;
            }
        }
        if ($count > 0) {
            $merger->merge();
            $merger->save(public_path() . '/merged.pdf');
            $path = public_path() . '/merged.pdf';
            if (\File::exists($path)) {
                $file = \File::get($path);
                $type = \File::mimeType($path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        }
    }
}
