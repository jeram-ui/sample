<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Api\Scheduler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\GlobalController;
use \App\Laravue\JsonResponse;
use PDF;
use Illuminate\Support\Facades\Log;
use Storage;
use File;

class eventController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $sched_db;
    private $empid;
    protected $G;

    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
    }


    public function displayCalendar(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $empid = Auth::user()->ID;
        $mapping = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Event')->first();
        $data_calendar = DB::table($lgu_db . '.appointments')->where('sched_group', $mapping->group_id)
            ->whereBetween('appointments.StartDate', [$request->from, $request->to])->get();;
        $calendar = array();
        foreach ($data_calendar as $key => $val) {
            if ($val->Status == 1) {
                $StatColor = 'red';
            } elseif ($val->StartDate < Date(Now())) {
                $StatColor = 'green';
            } elseif ($val->Status == 0) {
                $StatColor = 'blue';
            };
            $calendars = array(
                'id'     => intval($val->ID),
                'name' => $val->Subject,
                'StartDate' => date_format(date_create($val->StartDate), "Y-m-d"),
                'StarTime' => date_format(date_create($val->StartDate), "H:i:s"),
                'start' => date_format(date_create($val->StartDate), "Y-m-d H:i:s"),
                'EndDate'     => date_format(date_create($val->EndDate), "Y-m-d H:i:s"),
                'color' =>  $StatColor,
                'Description' => $val->Description,
                'Location' => $val->Location,
                'Subject' => $val->Subject,
                'Status' => $val->Status,
            );
            array_push($calendar, $calendars);
        }
        return response()->json(new JsonResponse($calendar));
    }
    public function printMayorSlip($id)
    {
        $dataMain = DB::select('call ' . $this->lgu_db . '.mTan_display_EventSchedule(?)', array($id));
        foreach ($dataMain as $row) {
            $infoMain = ($row);
        }
        $logo = config('variable.logo');
        try {
            $html_content = '<body>
            ' . $logo . '
        <table width ="100%">
        <tr style="height:25px">
            <th style="width:100%" align="Center">
                <h3>MAYOR&#8217;S APPOINTMENT SCHEDULE</h3>
            </th>
        </tr>
        <br>
        <br>
        <tr style="height:25px">
            <th style="width:55%"></th>
            <th style="width:20%" align="left"> Date Scheduled:</th>
            <th style="width:20%; border-bottom: 1px solid black" align="left">' . $infoMain->{'Date'} . '</th>
            <th style="width:5%"></th>
        </tr>
        <br>
        <tr style="height:25px">
            <th style="width:5%"></th>
            <th style="width:25%" align="left">MAYOR IS OK FOR:</th>
            <th style="width:65%;border-bottom: 1px solid black" align="left">' . $infoMain->{'Name'} . '</th>
            <th style="width:5%"></th>
        </tr>
        <br>
        <tr style="height:25px">
            <th style="width:5%"></th>
            <th style="width:20%" align="left">Location:</th>
            <th style="width:70%;border-bottom: 1px solid black" align="left">' . $infoMain->{'Address'} . '</th>
            <th style="width:5%"></th>
        </tr>
        <br>
        <br>
        <tr style="height:25px">
            <th style="width:5%"></th>
            <th style="width:20%" align="left">Start Date:</th>
            <th style="width:20%; border-bottom: 1px solid black" align="left">' . $infoMain->{'Date Start'} . '</th>
            <th style="width:10%"></th>
            <th style="width:20%" align="left"> Start Time:</th>
            <th style="width:20%; border-bottom: 1px solid black" align="left">' . $infoMain->{'Time Start'} . '</th>
            <th style="width:5%"></th>
        </tr>
        <br>
        <tr style="height:25px">
            <th style="width:5%"></th>
            <th style="width:20%" align="left">End Date:</th>
            <th style="width:20%; border-bottom: 1px solid black" align="left">' . $infoMain->{'Date End'} . '</th>
            <th style="width:10%"></th>
            <th style="width:20%" align="left"> End Time:</th>
            <th style="width:20%; border-bottom: 1px solid black" align="left">' . $infoMain->{'Time End'} . '</th>
            <th style="width:5%"></th>
        </tr>
        <br>
        <br>
        <br>
        <tr style="height:25px">
            <th style="width:70%"></th>
            <th style="width:30%" align="left"> <b>Mayor&#8217;s Staff</b></th>
        </tr>
        <br>
        <br>
        <br>
        <tr style="height:25px">
            <th style="width:100%">
            -----------------------------------------------------------------------------------------------------------------------------------
            </th>
        </tr>
    </table>
    </body>';

            PDF::SetTitle('Mayors Schedule');
            PDF::SetFont('times', '', 12);
            PDF::AddPage('');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function EventStore(Request $request)
    {

        log::debug($request);
        $lgu_db = config('variable.db_lgu');
        $files = $request->file('files');
        //  $main = unset($request->files);
        $main['Initiator'] = Auth::user()->id;
        $idx = $request->ID;
        $mapping = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Event')->first();
        $main['sched_group'] = $mapping->group_id;
        $main['Subject'] = $request->Subject;
        $main['Description'] = $request->Description;
        $main['Location'] = $request->Location;
        $main['StartDate'] = $request->StartDate;
        $main['EndDate'] = $request->EndDate;
        $main['GUID'] = $request->GUID;
        if ($idx == 0) {
            DB::table($lgu_db . '.appointments')->insert($main);
            $id = DB::getPdo()->lastInsertId();
            $path = hash('sha256', time());
            if (!empty($files)) {
                for ($i = 0; $i < count($files); $i++) {
                    $file = $files[$i];
                    $filename = $file->getClientOriginalName();
                    if (Storage::disk('docs')->put($path . '/' . $filename,  File::get($file))) {
                        $data = array(
                            'trans_id' => $id,
                            'file_name' => $filename,
                            'file_path' => $path,
                            'file_size' => $file->getSize(),
                            'trans_type' => 'Event'
                        );
                        db::table('docs_upload')->insert($data);
                    }
                }
            }
        } else {
            DB::table($lgu_db . '.appointments')
                ->where('ID', $idx)
                ->update($main);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    // public function store(Request $request)
    // {
    //     $lgu_db = config('variable.db_lgu');
    //     $pk =  $request->idx;
    //     $subject = $request->Subject;
    //     $venue = $request->location;
    //     $remarks = $request->remarks;
    //     $guid = $this->G->getGuid();
    //     $command = $request->command;
    //     $empid = Auth::user()->Employee_id;
    //     $bride =  $request->command;
    //     $bride_ =  $request->bride_;
    //     $groom_ =  $request->groom_;
    //     try {
    //         DB::beginTransaction();
    //         if ($command == 'attend') {
    //             $event_guid = DB::select('SELECT `GUID` FROM '.$lgu_db.'.appointments WHERE `ID` = ?', [$pk]);
    //             $data = array('Confirmed'=>1);
    //             foreach ($event_guid as $key) {
    //                 $event_guid = $key->GUID;
    //             }
    //             DB::table($lgu_db.'.appointments')
    //             ->where('GUIDS', $event_guid)
    //             ->where('UID', $empid)
    //             ->update($data);
    //         } elseif ($command == 'cancel') {
    //             $event_guid = DB::select('SELECT `GUID` FROM '.$lgu_db.'.appointments WHERE `ID` = ?', [$pk]);
    //             $data = array('Confirmed'=>0);
    //             DB::table($lgu_db.'.appointments')
    //             ->where('GUIDS', $event_guid)
    //             ->where('UID', $empid)
    //             ->update($data);
    //         } else {
    //             $data = array(
    //                 "StartDate" => $request->StartDate
    //                 ,"EndDate"=> $request->EndDate
    //                 ,"Subject"=> $subject
    //                 ,"Location"=> $request->Location
    //                 ,"Description"=> $request->Description
    //                 ,"GUID"=> $guid
    //                 ,"Initiator"=> Auth::user()->Employee_id
    //                 ,"sched_group"=>  $request->sched_group
    //                 ,'Status'=>($request->input('status') == 'true' ? "0" : "1")
    //             );

    //             if ($pk > 0) {
    //                 DB::table($lgu_db.'.appointments')
    //                 ->where('ID', $pk)
    //                 ->update($data);

    //                 $json_data_emp = json_decode($request->user, true);
    //                 DB::table($lgu_db.'.appointmentuser')->where('GUIDS', '=', $guid)->delete();
    //                 foreach ($json_data_emp as $items) {
    //                     if ($items['STATUS'] == 'true') {
    //                         $data =array('GUIDS' => $guid, 'UID' => $items['EMPLOYEEID']);
    //                         DB::table($lgu_db.'.appointmentuser')
    //                         ->insert($data);
    //                     }
    //                 }
    //             } else {
    //                 DB::table($lgu_db.'.appointments')->insert($data);
    //                 $json_data_emp = json_decode($request->user, true);
    //                 DB::table($lgu_db.'.appointmentuser')->where('GUIDS', '=', $guid)->delete();

    //                 foreach ($json_data_emp as $items) {
    //                     if ($items['STATUS'] == 'true') {
    //                         $data =array('GUIDS' => $guid, 'UID' => $items['EMPLOYEEID']);
    //                         DB::table($lgu_db.'.appointmentuser')
    //                         ->insert($data);
    //                     }
    //                 }
    //             }
    //         }
    //         DB::commit();
    //         return response()->json(['Message'=>'Transaction completed successfully.','status'=>'success','id'=>$pk]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['Message'=>$e,'status'=>'error']);
    //     }
    // }
}
