<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use \App\Laravue\JsonResponse;

class FullCalendarController extends Controller
{
    private $emp_id;
    public function __construct()
    {
        $this->middleware('auth');
    }
    function displayCalendar()
    {

        $empid = Auth::user()->Employee_id;
        // $data_calendar = DB::select("call qpsii_lgusystem._rans_display_calendar('$empid')");
        $data_calendar = DB::select("call qpsii_lgusystem._rans_display_calendar('1')");

        $calendar = array();
        foreach ($data_calendar as $key => $val) {
            $calendar[] = array(
                'id'     => intval($val->ID),
                'title' => $val->type,
                'subject' => trim($val->Subject),
                'location' => trim($val->Location),
                'description' => trim($val->Description),
                'start' => date_format(date_create($val->StartDate), "Y-m-d H:i:s"),
                'end'     => date_format(date_create($val->EndDate), "Y-m-d H:i:s"),
                'endx'     => date_format(date_create($val->EndDate), "Y-m-d H:i:s"),
                'guid'  => $val->guid,
                'initiator' => $val->initiator,
                'creator' => $val->creator,
                'status' => $val->Confirmed,
                'color' => $val->color,
                'type' => $val->type,
                'groupid' => $val->groupid,
            );
        }
        return response()->json(new JsonResponse(['data' => $calendar]));
    }

    function store(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $pk =  $request->PK;
        $guid = $request->guid;
        $command = $request->command;
        $empid = Auth::user()->Employee_id;
        if ($command == 'attend') {
            $event_guid = DB::select('SELECT `GUID` FROM `appointments` WHERE `ID` = ?', [$pk]);
            foreach ($event_guid as $key) {
                $event_guid = $key->GUID;
            }
            $data = array('Confirmed' => 1);
            DB::table($lgu_db . '.appointments')
                ->where('GUIDS', $event_guid)
                ->where('UID', $empid)
                ->update($data);
        } elseif ($command == 'cancel') {
            $event_guid = DB::select('SELECT `GUID` FROM `appointments` WHERE `ID` = ?', [$pk]);
            $data = array('Confirmed' => 0);
            DB::table($lgu_db . '.appointments')
                ->where('GUIDS', $event_guid)
                ->where('UID', $empid)
                ->update($data);
        } else {
            $data = array(
                "StartDate" => $request->start, "EndDate" => $request->end, "Subject" => $request->subject, "Location" => $request->location, "Description" => $request->remarks, "GUID" => $request->guid, "Initiator" => Auth::user()->Employee_id
            );

            if ($pk > 0) {
                DB::table($lgu_db . '.appointments')
                    ->where('ID', $pk)
                    ->update($data);

                $json_data_emp = json_decode($request->user, TRUE);
                DB::table($lgu_db . '.appointmentuser')->where('GUIDS', '=', $guid)->delete();
                foreach ($json_data_emp as $items) {
                    if ($items['STATUS'] == 'true') {
                        $data = array('GUIDS' => $guid, 'UID' => $items['EMPLOYEEID']);
                        DB::table($lgu_db . '.appointmentuser')
                            ->insert($data);
                    }
                }
            } else {
                DB::table($lgu_db . '.appointments')->insert($data);
                $json_data_emp = json_decode($request->user, TRUE);
                DB::table($lgu_db . '.appointmentuser')->where('GUIDS', '=', $guid)->delete();

                foreach ($json_data_emp as $items) {
                    if ($items['STATUS'] == 'true') {
                        $data = array('GUIDS' => $guid, 'UID' => $items['EMPLOYEEID']);
                        DB::table($lgu_db . '.appointmentuser')
                            ->insert($data);
                    }
                }
            }
        }
    }
}
