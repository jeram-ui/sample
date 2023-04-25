<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use Guid;
use Illuminate\Support\Facades\DB;
use PDF;
use \App\Laravue\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Image;
use Illuminate\Support\Facades\Log;

class QueuingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getFacility()
    {
        $list = db::table('vaccine_facility')->get();
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        $main = $request->form;
        log::debug($main['queuing_count']);
        db::table('vaccine_queuing')->where('facility_id', $main['facility_id'])->delete();
        db::table('vaccine_queuing')->insert($main);
        $ret = db::table('qpsii_lgusystem.ebplo_business_application_priority_number')
        ->Leftjoin('qpsii_lgusystem.ebplo_business_application','ebplo_business_application.business_app_id','ebplo_business_application_priority_number.baid')
        ->where('ebplo_business_application_priority_number.priority_no',$main['queuing_count'])
        ->get();
        return response()->json(new JsonResponse($ret));
    }
    public function getAllPriority(){
        $list = db::table('vaccine_facility')
        ->join('vaccine_queuing','vaccine_queuing.facility_id','vaccine_facility.id')
        ->leftJoin('qpsii_lgusystem.ebplo_business_application_priority_number','ebplo_business_application_priority_number.priority_no','vaccine_queuing.queuing_count')
        ->leftJoin('qpsii_lgusystem.ebplo_business_application','ebplo_business_application.business_app_id','ebplo_business_application_priority_number.baid')
        ->select('vaccine_facility.*','vaccine_queuing.*','ebplo_business_application_priority_number._categoryx','ebplo_business_application.business_name')
        ->orderBy('sorting')
        ->get();
        return response()->json(new JsonResponse($list));
    }
    public function show($id)
    {
        $list = db::table('vaccine_queuing')
        ->leftJoin('qpsii_lgusystem.ebplo_business_application_priority_number','vaccine_queuing.queuing_count','ebplo_business_application_priority_number.priority_no')
        ->leftJoin('qpsii_lgusystem.ebplo_business_application','ebplo_business_application.business_app_id','ebplo_business_application_priority_number.baid')
        ->where('facility_id', $id)->get();
        return response()->json(new JsonResponse($list));
    }
    public function getVaccinator()
    {
        $list = db::table('vaccinator')
            ->orderBy('full_name')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function updateTarget(Request $request)
    {
        $form = $request->form;
        db::table('vaccine_facility')->where('id', $form['id'])->update(['target' => $form['target']]);
    }
    public function getDailyVaccinated($date)
    {
        $list = db::table('vaccine_entry')
            ->join('vaccine_profiling', 'vaccine_profiling.id', 'vaccine_entry.profile_id')
            ->select('vaccine_entry.facility', db::raw('count(vaccine_entry.id) as count'))
            ->Where(function ($query) use ($date) {
                $query->where('first_dose_date', $date)
                    ->orWhere('second_dose_date', $date);
            })
            ->where('vaccine_profiling.stat', 0)
            ->groupBy('vaccine_entry.facility')
            ->get();
        return response()->json(new JsonResponse($list));
    }
}
