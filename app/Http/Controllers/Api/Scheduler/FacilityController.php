<?php

namespace App\Http\Controllers\Api\Scheduler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\GlobalController;
use Guid;
use App\Laravue\JsonResponse;
use PDF;

class FacilityController extends Controller
{
    private $emp_id;
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->middleware('auth');
    }
    public function departmentList()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }
    public function displayCalendar(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $empid = Auth::user()->ID;
        $mapping = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Facility')->first();
        $data_calendar = DB::table($lgu_db . '.appointments')
            ->where('sched_group', $mapping->group_id)
            ->whereBetween('appointments.StartDate', [$request->from, $request->to])
            ->where('Location', 'like', '%' . $request->facility_name)
            ->where('Description', 'like', '%' . $request->Subject . '%')
            ->where('appointments.Status', 0)
            ->get();
        $calendar = array();
        foreach ($data_calendar as $key => $val) {
            if ($val->Status == 1) {
                $StatColor = 'red';
            } elseif ($val->StartDate < Date(Now())) {
                $StatColor = 'green';
            } elseif ($val->Status == 0) {
                $StatColor = 'blue';
            };
            $res = db::table($lgu_db . ".appointments_resources")->select('resource_id')->where("appointment_id", $val->ID)->get();
            $resArray = array();
            foreach ($res as $key => $value) {
                array_push($resArray, $value->resource_id);
            }

            $calendars = array(
                'id'     => intval($val->ID),
                'name' => $val->Location . " (" . $val->Description . ")",
                'start' => date_format(date_create($val->StartDate), "Y-m-d h:i:s"),
                'end'     => date_format(date_create($val->EndDate), "Y-m-d h:i:s"),
                'color' =>  $StatColor,
                'Description' => $val->Description,
                'Location' => $val->Location,
                'Subject' => $resArray,
                'Status' => $val->Status,
                'office' => $val->Notes,
            );
            array_push($calendar, $calendars);
        }
        return response()->json(new JsonResponse($calendar));
    }
    public function storeResources(Request $request)
    {
        $form = $request->form;
        $sources = $form['Subject'];
        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->lgu_db . '.appointment_resources')
                ->where('id', $id)
                ->update($form);
        } else {

            DB::table($this->lgu_db . '.appointment_resources')->insert($form);
            $id = DB::getPdo()->LastInsertId();
        }

        return  $this->G->success();
    }
    public function storeFacility(Request $request)
    {
        $form = $request->form;

        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->lgu_db . '.appointment_facility')
                ->where('id', $id)
                ->update($form);
        } else {

            DB::table($this->lgu_db . '.appointment_facility')->insert($form);
            $id = DB::getPdo()->LastInsertId();
        }

        return  $this->G->success();
    }
    public function cancelResources($id)
    {
        db::table($this->lgu_db . '.appointment_resources')
            ->where('id', $id)
            ->update(['status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function cancelFacility($id)
    {
        db::table($this->lgu_db . '.appointment_facility')
            ->where('id', $id)
            ->update(['status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function facilityList()
    {
        $data = db::table($this->lgu_db . '.appointment_facility')->where('status', 0)->get();
        return response()->json(new JsonResponse($data));
    }
    public function facilityFilter(Request $request)
    {
        // $form = $request->itm;
        $facility = $request->Location;
        $data = db::select("call " . $this->lgu_db . "._get_facility_sched(?)", [$facility]);

        // $calendar = array();
        // foreach ($data as $key => $val) {
        //     if ($val->Status == 1) {
        //         $StatColor = 'red';
        //     } elseif ($val->StartDate < Date(Now())) {
        //         $StatColor = 'green';
        //     } elseif ($val->Status == 0) {
        //         $StatColor = 'blue';
        //     };
        //     $calendars = array(
        //         'id'     => intval($val->ID),
        //         'name' => $val->Subject,
        //         'start' => date_format(date_create($val->StartDate), "Y-m-d h:i:s"),
        //         'end'     => date_format(date_create($val->EndDate), "Y-m-d h:i:s"),
        //         'color' =>  $StatColor,
        //         'Description' => $val->Description,
        //         'Location' => $val->Location,
        //         'Subject' => $val->Subject,
        //         'Status' => $val->Status,
        //     );
        //     array_push($calendar, $calendars);
        // }
        return response()->json(new JsonResponse($data));
    }
    public function resourceList()
    {
        $data = db::table($this->lgu_db . '.appointment_resources')->where('status', 0)->get();
        return response()->json(new JsonResponse($data));
    }
    public function printFacilitySlip($id)
    {
        $dataMain = DB::select('call ' . $this->lgu_db . '.balodoy_display_facilityschedule(?)', array($id));
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
                <h3>RESERVED FACILITY SCHEDULE</h3>
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
            <th style="width:20%" align="left">Resource Name:</th>
            <th style="width:70%;border-bottom: 1px solid black" align="left">' . $infoMain->{'Resource'} . '</th>
            <th style="width:5%"></th>
        </tr>
        <br>
        <tr style="height:25px">
            <th style="width:5%"></th>
            <th style="width:20%" align="left">Location:</th>
            <th style="width:70%;border-bottom: 1px solid black" align="left">' . $infoMain->{'Location'} . '</th>
            <th style="width:5%"></th>
        </tr>
        <br>
        <tr style="height:25px">
            <th style="width:5%"></th>
            <th style="width:20%" align="left">Subject:</th>
            <th style="width:70%;border-bottom: 1px solid black" align="left">' . $infoMain->{'Subject'} . '</th>
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

            PDF::SetTitle('Facility Schedule');
            PDF::SetFont('times', '', 12);
            PDF::AddPage('');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function store(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $main = $request->main;
        $main['Initiator'] = Auth::user()->id;
        $idx = $request->main['ID'];
        $rowz = $request->rowz;
        $mapping = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Facility')->first();
        $main['sched_group'] = $mapping->group_id;
        $resource = $main['Subject'];
        unset($main['Subject']);
        if ($idx == 0) {
            DB::table($lgu_db . '.appointments')->insert($main);
            $id = DB::getPdo()->LastInsertId();
            foreach ($rowz as $key => $value) {
                $res = array(
                    'appointment_id' => $id,
                    'resource_id' =>  $value['resource_id'],
                    'quantity' =>  $value['quantity']
                );
                db::table($lgu_db . ".appointments_resources")
                    ->insert($res);
            }
        } else {
            DB::table($lgu_db . '.appointments')
                ->where('ID', $idx)
                ->update($main);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    // function store(Request $request){
    // 	$lgu_db = config('variable.db_lgu');
    // 	$pk =  $request->idx;
    // 	$subject = $request->Subject;
    // 	$venue = $request->location;
    // 	$remarks = $request->remarks;
    // 	$guid = $this->G->getGuid();
    // 	$command = $request->command;
    // 	$empid = Auth::user()->Employee_id;
    // 	$groom_ =  $request->groom_;
    // 	try {

    // 		DB::beginTransaction();
    // 		if ($command == 'attend') {
    // 			$event_guid = DB::select('SELECT `GUID` FROM '.$lgu_db.'.appointments WHERE `ID` = ?', [$pk]);
    // 			$data = array('Confirmed'=>1);
    // 			foreach ($event_guid as $key ) {
    // 				$event_guid = $key->GUID;
    // 			}
    // 			DB::table($lgu_db.'.appointments')
    // 			->where('GUIDS', $event_guid)
    // 			->where('UID', $empid)
    // 			->update($data);
    // 		}elseif ($command == 'cancel') {
    // 			$event_guid = DB::select('SELECT `GUID` FROM '.$lgu_db.'.appointments WHERE `ID` = ?', [$pk]);
    // 			$data = array('Confirmed'=>0);
    // 			DB::table($lgu_db.'.appointments')
    // 			->where('GUIDS', $event_guid)
    // 			->where('UID', $empid)
    // 			->update($data);
    // 		}else{
    // 			$data = array(
    // 				"StartDate" => $request->StartDate
    // 				,"EndDate"=> $request->EndDate
    // 				,"Subject"=> $subject
    // 				,"Location"=> $request->Location
    // 				,"Description"=> $request->Description
    // 				,"GUID"=> $guid
    // 				,"Initiator"=> Auth::user()->Employee_id
    // 				,"sched_group"=>  $request->sched_group
    // 				,'Status'=>($request->input('status') == 'true' ? "0" : "1")
    // 			);

    // 			if ($pk > 0){
    // 				DB::table($lgu_db.'.appointments')
    // 				->where('ID',$pk)
    // 				->update($data);

    // 				$json_data_emp = json_decode($request->user, TRUE);
    // 				DB::table($lgu_db.'.appointmentuser')->where('GUIDS', '=', $guid)->delete();
    // 				foreach ($json_data_emp as $items)
    // 				{
    // 					if ($items['STATUS'] == 'true') {
    // 						$data =array('GUIDS' => $guid, 'UID' => $items['EMPLOYEEID']);
    // 						DB::table($lgu_db.'.appointmentuser')
    // 						->insert($data);
    // 					}
    // 				}
    // 			}else{
    // 				DB::table($lgu_db.'.appointments')->insert($data);
    // 				$json_data_emp = json_decode($request->user, TRUE);
    // 				DB::table($lgu_db.'.appointmentuser')->where('GUIDS', '=', $guid)->delete();

    // 				foreach ($json_data_emp as $items)
    // 				{
    // 					if ($items['STATUS'] == 'true') {
    // 						$data =array('GUIDS' => $guid, 'UID' => $items['EMPLOYEEID']);
    // 						DB::table($lgu_db.'.appointmentuser')
    // 						->insert($data);
    // 					}
    // 				}
    // 			}
    // 		}
    // 		DB::commit();
    // 		return response()->json(['Message'=>'Transaction completed successfully.','status'=>'success','id'=>$pk]);
    // 	} catch (Exception $e) {
    // 		DB::rollBack();
    // 		return response()->json(['Message'=>$e,'status'=>'error']);
    // 	}

    // }
}
