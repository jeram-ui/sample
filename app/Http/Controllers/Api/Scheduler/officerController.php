<?php

namespace App\Http\Controllers\Api\Scheduler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;

use PDF;

class officerController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $sched_db;

    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
    }

    public function index()
    {
    }
    public function store(Request $request)
    {
    }
    public function save($main, $dtl, $check)
    {
    }
    public function update($idx, $main, $dtl, $check)
    {
    }
    public function officerOrg($idx)
    {
        $list = DB::select('call ' . $this->sched_db . '.spl_display_official_info_org(?)', array($idx));
        return response()->json(new JsonResponse($list));
    }
    public function officerList(Request $request)
    {
        $idx = $request->orgArr;
        $from = $request->from;
        $to = $request->to;
        // dd($idx);
        // dd('call '.$this->sched_db.'.spl_display_official_info_org(?)', array($idx));
        $list = DB::select('call ' . $this->sched_db . '.spl_display_official_info_org1(?,?,?)', [$idx, $from, $to]);
        return response()->json(new JsonResponse($list));
    }
    public function getTerm(Request $request)
    {
        $idx = $request->orgArr;
        // dd($idx);
        // dd('call '.$this->sched_db.'.spl_display_official_info_org(?)', array($idx));
        $list = DB::select("SELECT  *,CONCAT(`termFrom`,' - ',`termTo`) AS 'term' FROM dbfederation.tbl_official_info GROUP BY `termFrom`,`termTo`");
        return response()->json(new JsonResponse($list));
    }
    public function orgArr()
    {
        $list = DB::select('call ' . $this->sched_db . '.spl_display_organization');
        return response()->json(new JsonResponse($list));
    }

    public function officerPrint(Request $request)
    {
        $data = $request->main;
        $filter = $request->filter;
        $from = date("F j, Y", strtotime($filter['from']));
        $to =  date("F j, Y", strtotime($filter['to']));

        if ($filter['filter'] == "Year") {
            $filters = "Year " . date("Y", strtotime($filter['from']));
        } elseif ($filter['filter'] == "Month") {
            $filters = "Month of " . date("F Y", strtotime($filter['from']));
        } else {
            $filters = "As of " .  $from . ' - ' . $to;
        }
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 style="width:14%;text-align:center;font-size:14px">OFFICERS LISTS</h2>
        <h3 style="width:14%;text-align:center;font-size:11px">' . $filters . '</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:15%;font-size:10px">Position</th>
        <th style = "width:20%;font-size:10px">Name</th>
        <th style = "width:5%;font-size:10px">Age</th>
        <th style = "width:7%;font-size:10px">Sex*</th>
        <th style = "width:22%;font-size:10px">Address</th>
        <th style = "width:19%;font-size:10px">E-mail Address</th>
        <th style = "width:12%;font-size:10px">Contact Number</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {
                $main = ($row);
                $html_content .= '
            <tr>
            <td style="width:15%;text-align:left;font-size:9px">' . $main['Position'] . '</td>
            <td style="width:20%;text-align:left;font-size:9px">' . $main['Officers'] . '</td>
            <td style="width:5%;text-align:center;font-size:10px">' . $main['Age'] . '</td>
            <td style="width:7%;text-align:left;font-size:9px">' . $main['Sex*'] . '</td>
            <td style="width:22%;text-align:left;font-size:9px">' . $main['Address'] . '</td>
            <td style="width:19%;text-align:left;font-size:9px">' . $main['Email Add'] . '</td>
            <td style="width:12%;text-align:left;font-size:9px">' . $main['Contact No'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';

            PDF::SetTitle('Sample');
            PDF::AddPage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }
    public function printOfficers(Request $request)
    {
        $data = $request->main;
        $filter = $request->filter;
        $orgName = $request->filter['orgName'];
        $from = date("F j, Y", strtotime($filter['from']));
        $to =  date("F j, Y", strtotime($filter['to']));

        if ($filter['filter'] == "Year") {
            $filters = "Year " . date("Y", strtotime($filter['from']));
        } elseif ($filter['filter'] == "Month") {
            $filters = "Month of " . date("F Y", strtotime($filter['from']));
        } else {
            $filters = "As of " .  $from . ' - ' . $to;
        }
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 style="width:14%;text-align:center;font-size:15px">' . $orgName . '</h2>
        <h3 style="width:14%;text-align:center;font-size:11px">' . $filters . '</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th style = "width:22%;font-size:11px">Position</th>
        <th style = "width:32%;font-size:11px">Name</th>
        <th style = "width:28%;font-size:11px">E-mail Address</th>
        <th style = "width:18%;font-size:11px">Contact Number</th>
        </tr>
        <tbody>';
            $PositionGrp = array();
            foreach ($data as $key => $item) {
                $PositionGrp[$item['Position']][$key] = $item;
            }
            for ($i = 0; $i < count($PositionGrp); $i++) {
                $result = $PositionGrp[key($PositionGrp)];
                $html_content .= '
            <tr>
            <td rowspan="' . count($result) . '" style="width:22%;text-align:left;font-size:9px">' . key($PositionGrp) . '</td>';
                $cnt = 0;
                foreach ($result as $row) {
                    $main = ($row);
                    if ($cnt != 0) {
                        $html_content .= '<tr>';
                    }
                    $html_content .= ' 
                <td style="width:32%;text-align:left;font-size:9px">' . $main['Officers'] . '</td>
                <td style="width:28%;text-align:left;font-size:9px">' . $main['Email Add'] . '</td>
                <td style="width:18%;text-align:left;font-size:9px">' . $main['Contact No'] . '</td>
                </tr>';
                    $cnt++;
                }
                next($PositionGrp);
            }

            $html_content .= '</tbody>
        </table>';

            PDF::SetTitle('Sample');
            PDF::AddPage('');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }
}
