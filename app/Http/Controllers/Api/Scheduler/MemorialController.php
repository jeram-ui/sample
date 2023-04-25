<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Api\Scheduler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\GlobalController;
use App\Laravue\JsonResponse;
use PDF;
use Illuminate\Support\Facades\log;


class MemorialController extends Controller
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
        $empid = Auth::user()->Employee_id;
        $lgu_db = config('variable.db_lgu');
        $empid = Auth::user()->id;
        $mapping = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Memorial')->first();
        $data_calendar = DB::table($lgu_db . '.appointments')
            ->where('Status', 0)
            ->where('sched_group', $mapping->group_id)
            ->whereBetween('appointments.StartDate', [$request->from, $request->to])
            ->orderBy("trans_date", "asc")->get();
        $calendar = [];

        foreach ($data_calendar as $key => $val) {
            if ($val->Status == 1) {
                $StatColor = 'red';
            } elseif ($val->StartDate < Date(Now())) {
                $StatColor = 'green';
            } elseif ($val->Status == 0) {
                $StatColor = 'blue';
            };

            $calendar[] = array(
                'id'     => intval($val->ID),
                'name' => $val->Subject,
                'start' => date_format(date_create($val->StartDate), "Y-m-d"),
                'end'     => date_format(date_create($val->EndDate), "Y-m-d"),
                'date_of_death'     => date_format(date_create($val->date_of_death), "Y-m-d"),
                'expiration_date'     => date_format(date_create($val->expiration_date), "Y-m-d"),
                'color' =>  $StatColor,
                'tomb_no' => $val->tomb_no,
                'contact_person' => $val->contact_person,
                'contact_no' => $val->contact_no,
                'Description' => $val->Description,
                'Location' => $val->Location,
                'Status' => $val->Status,
                'app_or_no' => $val->app_or_no,
                'trans_date' => $val->trans_date,
            );
        }

        return response()->json(new JsonResponse($calendar));
    }
    public function displayCalendarfive(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $lgu_db = config('variable.db_lgu');
        $empid = Auth::user()->id;
        $mapping1 = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Memorial')->first();
        $data_calendar1 = DB::table($lgu_db . '.appointments')
            ->select("*", db::raw("DATE_ADD(StartDate,INTERVAL 5 YEAR ) as fiveyears"))
            ->where('Status', 0)
            ->where('sched_group', $mapping1->group_id)
            ->whereBetween(db::raw("DATE_ADD(StartDate,INTERVAL 5 YEAR )"), [$request->from, $request->to]
             )
            ->orderBy("trans_date", "asc")
            ->get();
            $calendar = [];


            log::debug($data_calendar1);
            foreach ($data_calendar1 as $key => $val) {

                    $StatColor1 = 'red';

                $calendar[] = array(
                    'id'     => intval($val->ID),
                    'name' => $val->Subject,
                    'start'=>$val->fiveyears,
                    // 'start' => date_format(date_create($val->StartDate), "Y-m-d"),
                    'end'     => date_format(date_create($val->EndDate), "Y-m-d"),
                    'date_of_death'     => date_format(date_create($val->date_of_death), "Y-m-d"),
                    'expiration_date'     => date_format(date_create($val->expiration_date), "Y-m-d"),
                    'color' =>  $StatColor1,
                    'tomb_no' => $val->tomb_no,
                    'contact_person' => $val->contact_person,
                    'contact_no' => $val->contact_no,
                    'Description' => $val->Description,
                    'Location' => $val->Location,
                    'Status' => $val->Status,
                    'app_or_no' => $val->app_or_no,
                    'trans_date' => $val->trans_date,
                );
            }

        return response()->json(new JsonResponse($calendar));
    }
    public function displayCalendarten(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $lgu_db = config('variable.db_lgu');
        $empid = Auth::user()->id;
        $mapping = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Memorial')->first();
            $data_calendar = DB::table($lgu_db . '.appointments')
                ->select("*", db::raw("DATE_ADD(`StartDate`,INTERVAL 10 YEAR ) as tenyears"))
                ->where('Status', 0)
                ->where('sched_group', $mapping->group_id)
                ->whereBetween(db::raw("DATE_ADD(`StartDate`,INTERVAL 10 YEAR )"), [$request->from, $request->to])
                ->orderBy("trans_date", "asc")->get();
                $calendar = [];


                foreach ($data_calendar as $key => $val) {

                        $StatColor = 'purple';

                    $calendar[] = array(
                        'id'     => intval($val->ID),
                        'name' => $val->Subject,
                        'start'=>$val->tenyears,
                        // 'start' => date_format(date_create($val->StartDate), "Y-m-d"),
                        'end'     => date_format(date_create($val->EndDate), "Y-m-d"),
                        'date_of_death'     => date_format(date_create($val->date_of_death), "Y-m-d"),
                        'expiration_date'     => date_format(date_create($val->expiration_date), "Y-m-d"),
                        'color' =>  $StatColor,
                        'tomb_no' => $val->tomb_no,
                        'contact_person' => $val->contact_person,
                        'contact_no' => $val->contact_no,
                        'Description' => $val->Description,
                        'Location' => $val->Location,
                        'Status' => $val->Status,
                        'app_or_no' => $val->app_or_no,
                        'trans_date' => $val->trans_date,
                    );
                }

        return response()->json(new JsonResponse($calendar));
    }
    public function edit($id)
    {
        $data = db::table($this->lgu_db . '.appointments')->where('GUID', $id)->get();
        return response()->json(new JsonResponse($data));
    }

    public function printMemorialSlip($id)
    {
        $dataMain = DB::select('call ' . $this->lgu_db . '.balodoy_display_memorialschedule(?)', array($id));
        foreach ($dataMain as $row) {
            $infoMain = ($row);
        }
        $params1 = PDF::serializeTCPDFtagParameters(array($infoMain->ID, 'QRCODE,H', '', '', 15, 15, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
        $logo = config('variable.logo2');
        try {
            $html_content =  '
            <table>
              <tr>
                <td width ="100%">
                <table border="0" width ="100%" cellpadding ="2" >
                <tr>
                <td>
        <table width ="100%" cellpadding ="2" >
        <tr>
          <th>
          ' . $logo . '
          </th>
        </tr>
            <tr >
                <th style="width:100%" align="Center">
                    <h3>INTERMENT FORM CEMETERY</h3>
                </th>
            </tr>
            <br>

            <tr style="height:25px">
               <th style="width:5%"></th>
               <th style="width:30%" align="left">Scheduled:</th>
               <th style="width:60%;border-bottom: 1px solid black" align="left"><b>' . $infoMain->{'Date'} . '</b></th>
               <th style="width:5%"></th>
           </tr>


            <tr style="height:25px">
                <th style="width:5%"></th>
                <th style="width:30%" align="left">Cadaver Name:</th>
                <th style="width:60%;border-bottom: 1px solid black" align="left"><b>' . $infoMain->{'Name'} . '</b></th>
                <th style="width:5%"></th>
            </tr>

            <tr style="height:25px">
                <th style="width:5%"></th>
                <th style="width:30%" align="left">Date of Death:</th>
                <th style="width:60%;border-bottom: 1px solid black" align="left">' . strtoupper($infoMain->{'Date of Death'}) . '</th>
                <th style="width:5%"></th>
            </tr>

            <tr style="height:25px">
                <th style="width:5%"></th>
                <th style="width:30%" align="left">Date of Interment:</th>
                <th style="width:60%;border-bottom: 1px solid black" align="left">' . strtoupper($infoMain->{'Internment Date'}) . '</th>
                <th style="width:5%"></th>
            </tr>

            <tr style="height:25px">
                <th style="width:5%"></th>
                <th style="width:30%" align="left">Contact Person:</th>
                <th style="width:60%;border-bottom: 1px solid black" align="left">' . $infoMain->{'Contact Person'} . '</th>
                <th style="width:5%"></th>
            </tr>

            <tr style="height:25px">
                <th style="width:5%"></th>
                <th style="width:30%" align="left">Address:</th>
                <th style="width:60%;border-bottom: 1px solid black" align="left">' . $infoMain->{'Address'} . '</th>
                <th style="width:5%"></th>
            </tr>

            <tr style="height:25px">
                <th style="width:5%"></th>
                <th style="width:30%" align="left">OR No:</th>
                <th style="width:60%;border-bottom: 1px solid black" align="left">' . $infoMain->{'app_or_no'} . '</th>
                <th style="width:5%"></th>
            </tr>
            <br>
    
            <br>
            <tr style="height:25px">
            <th style="width:5%"></th>
            <th style="width:90%" align="center"></th>
            <th style="width:5%"></th>
        </tr>
        </table>
        </td>
                </tr>
               </table>

                </td>
              </tr>
            </table>

           ';

            PDF::SetTitle('Memorial Schedule');
            PDF::SetFont('helvetica', '', 11);
            PDF::AddPage('P');
            // PDF::SetMargins(1, 1, 1, 1);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function store(Request $request)
    {
        $lgu_db = $this->lgu_db;
        $main = $request->main;
        $main['Initiator'] = Auth::user()->id;
        $idx = $request->main['ID'];
        $mapping = DB::table($lgu_db . '.sched_group_mapping')->where('sched_name', 'Memorial')->first();
        $main['sched_group'] = $mapping->group_id;
        if ($idx == 0) {
            DB::table($lgu_db . '.appointments')->insert($main);
        } else {
            DB::table($lgu_db . '.appointments')
                ->where('ID', $idx)
                ->update($main);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
}
