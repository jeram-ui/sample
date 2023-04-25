<?php

namespace App\Http\Controllers\Api\Mod_legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PDF;
use Storage;
use File;

class appointmentController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->path = env('LGU_FRONT');
    }
    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'CSN';
        $table = $this->lgu_db . ".law_cases_entry";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function show(Request $request)
    {
        $list = db::table($this->lgu_db . '.law_appointment')
            ->leftJoin('dbfederation.tbl_organization_profile', 'tbl_organization_profile.id', '=', 'law_appointment.org_id')
            ->select('law_appointment.*', 'tbl_organization_profile.organization_name')
            ->where('lawyer_id', $request->lawyer_id)
            ->whereBetween('start', [$request->from, $request->to])
            ->where('entry_type', $request->type)
            ->get();
        $calendar = array();
        foreach ($list as $key => $val) {
            if ($val->stat == 1) {
                $StatColor = 'red';
            } elseif ($val->start < Date(Now())) {
                $StatColor = 'green';
            } elseif ($val->stat == 0) {
                $StatColor = 'blue';
            };
            $calendars = array(
                'id'     => intval($val->id),
                'name' => $val->subject_name,
                'start' => date_format(date_create($val->start), "Y-m-d H:i:s"),
                'end'     => date_format(date_create($val->end), "Y-m-d H:i:s"),
                'color' =>  $StatColor,
                'Description' => $val->subject_name,
                'Location' => $val->venue,
                'Subject' => $val->subject_name,
                'Status' => $val->stat,
                'remarks' => $val->remarks,
                'remarks' => $val->remarks,
                'recommendation' => $val->recommendation,
                'committee' => $val->organization_name
            );
            array_push($calendar, $calendars);
        }

        return response()->json(new JsonResponse($calendar));
    }

    public function store(Request $request)
    {
        try {
            $main = $request->main;
            $idx = $main['id'];
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_appointment')->insert($main);
            } else {
                db::table($this->lgu_db . '.law_appointment')->where('id', $idx)->update($main);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function edit($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.law_cases_entry')->where('ID', $id)->get();
        $data['law'] = DB::table($this->lgu_db . '.law_cases_entry_law')->where('case_id', $id)->get();
        $data['client'] = DB::table($this->lgu_db . '.law_cases_entry_client')->where('case_entry_id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function showCalendar_data($id){
      $data =  db::table($this->lgu_db . '.law_appointment')->where('id',$id)->get();
      return response()->json(new JsonResponse($data));
    }
    public function showCalendar(Request $request)
    {
        $meeting = db::table($this->lgu_db . '.law_case_meeting')
            ->select('id', 'agenda', 'date_time as start', 'date_time as end', db::raw('"meeting" as type'), db::raw('"#71a1e3" as colors')
             ,'law_case_meeting.remarks',
             'venue as location',
             'lawyer_id'
            )
            ->where('lawyer_id', $request->lawyer_id)
            ->where('stat',0)
            ->whereBetween('date_time', [$request->from, $request->to]);

        $hearing = db::table($this->lgu_db . '.law_hearing_schedule')
            ->select('ID', 'Agenda', 'Date_time as start', 'Date_time as end', db::raw('"hearing" as type'), db::raw('"#4fd167" as colors')
             ,'law_hearing_schedule.todolist as remarks',
           db::raw('"" as location'),
           'lawyer_id'
            )
            
            ->where('lawyer_id', $request->lawyer_id)
            ->where('Status','ACTIVE')
            ->whereBetween('Date_time', [$request->from, $request->to]);

        $list = db::table($this->lgu_db . '.law_appointment')
            ->select(
                'id',
                'subject_name',
                'start as start',
                'end as end',
                db::raw('entry_type as type')
                ,
                db::raw('(case when entry_type = "appointment" then "#d5e038"
                when entry_type = "committee" then "#671bb3" end) as colors
               ')
               ,'law_appointment.remarks',
               'law_appointment.venue as location',
               'lawyer_id'

               )
            ->where('lawyer_id', $request->lawyer_id)
            ->where('law_appointment.stat',0)
            ->whereBetween('start', [$request->from, $request->to])
            ->union($meeting)
            ->union($hearing)
            ->get();
        //    log::debug($list);
        $calendar = array();
        foreach ($list as $key => $val) {



            $calendars = array(
                'id'     => intval($val->id),
                'name' => $val->subject_name,
                'start' => date_format(date_create($val->start), "Y-m-d H:i:s"),
                'end'     => date_format(date_create($val->end), "Y-m-d H:i:s"),
                'color' => $val->colors,
                'Description' => $val->subject_name,
                'Subject' => $val->subject_name,
                'type'=> $val->type,
                'remarks'=> $val->remarks,
                'location'=> $val->location,
                'lawyer_id'=> $val->lawyer_id,
            );
            array_push($calendar, $calendars);
        }

        return response()->json(new JsonResponse($calendar));
    }
}
