<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\GlobalController;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;



class MeterController extends Controller
{
    protected $G;
    protected $meter;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->meter = $this->G->getMeter();
    }

    public function loadBill(Request $request)
    {
        $list = db::select("SELECT * FROM  " . $this->meter . ".`tbl_water_bill_new` WHERE `sched_id` = '" . $request->id . "'");
        return response()->json(new JsonResponse($list));
    }

    public function billSched(Request $request)
    {
        $list = db::select("SELECT * FROM  " . $this->meter . ".`tbl_bill_sched` WHERE `id` = '" . $request->id . "' AND guid = '" . $request->guid . "'");
        return response()->json(new JsonResponse($list));
    }

    public function wateRates()
    {
        $list = db::select("SELECT * FROM  " . $this->meter . ".`tbl_cto_water_rates`");
        return response()->json(new JsonResponse($list));
    }

    public function complaints()
    {
        $list = db::select("SELECT * FROM  " . $this->meter . ".`tbl_ww_complaint_setup`");
        return response()->json(new JsonResponse($list));
    }

    public function updatebillSched(Request $request)
    {
        db::table(" . $this->meter . " . 'tbl_bill_sched')
            ->where('id', $request->id)
            ->update(['status' =>  $request->status]);
        return $this->G->success();
    }

    public function updatebilling(Request $request)
    {
        db::table(" . $this->meter . " . 'tbl_water_bill_new')
            ->where('bn_id', $request->bn_id)
            ->update([
                'PRES RDNG' => $request->PRESRDNG,
                'CONSUMPTION' => $request->CONSUMPTION,
                'CURRENT BILL' => floatval($request->CURRENTBILL),
                'OTHER CHARGE' => floatval($request->OTHERCHARGE),
                'SC AMOUNT' => floatval($request->SCAMOUNT),
                'TOTAL AMOUNT' => ($request->TOTALAMOUNT),
                'DATE SAVED' => $request->DATESAVED,
                'REMARKS' => $request->REMARKS,
                'rem_id' => $request->rem_id,
                'STATUS' => $request->STATUS,
                'POWER COST AMOUNT' => floatval($request->POWERCOSTAMOUNT)
            ]);
        return $this->G->success();
    }

    public function loadBillbyid($id)
    {
        $list = db::select("SELECT * FROM  " . $this->meter . ".`tbl_water_bill_new` WHERE `sched_id` = '" . $id . "'");
        return response()->json(new JsonResponse($list));
    }
}
