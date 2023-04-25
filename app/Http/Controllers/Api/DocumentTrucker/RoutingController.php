<?php

namespace App\Http\Controllers\Api\DocumentTrucker;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use PDF;
use \App\Laravue\JsonResponse;

class RoutingController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->middleware('auth');
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }

    public function getRef(Request $request)
    {
        $pre = 'RN';
        $table = $this->lgu_db . ".law_routing_entry";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }

    public function store(Request $request)
    {
        $lgu_db = $this->lgu_db;
        $trk_db = $this->trk_db;
        
        DB::beginTransaction();
        $idx = $request->main['id'];
        $main = $request->main;
        $dtl = $request->dtl;
        unset($main['id']);
        try {
            if ($idx > 0) {
                DB::table($lgu_db . '.law_routing_entry')
                    ->where('ID', $idx)
                    ->update($main);
                DB::table($lgu_db . '.law_routing_entry_details')
                    ->where('routing_main_id', $idx)
                    ->delete();
                foreach ($dtl as $items) {
                    $data = array('routing_main_id' => $idx, 'routing_setup_id' => $items['id'], 'routingname' => $items['description']);
                    DB::table($lgu_db . '.law_routing_entry_details')->insert($data);
                }
            } else {
                DB::table($lgu_db . '.law_routing_entry')->insert($main);
                $id = DB::getPdo()->lastInsertId();
                foreach ($dtl as $items) {
                    $data = array('routing_main_id' => $id, 'routing_setup_id' => $items['id'], 'routingname' => $items['description']);
                    DB::table($lgu_db . '.law_routing_entry_details')->insert($data);
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function show(Request $request)
    {
        $data = DB::select("SELECT
        `law_routing_entry`.`id`
         ,`trans_date` AS 'date'
         ,`ref_no` AS 'ref'
         ,department.`Name_Dept` 'From'
         ,employeedepartment.name AS 'To'
         ,GROUP_CONCAT(law_routing_setup.description SEPARATOR '<br>') AS 'Detail'
         ,law_routing_entry.`remarks`
         FROM " . $this->lgu_db . ".law_routing_entry
        INNER JOIN " . $this->lgu_db . ".law_routing_setup
        ON(law_routing_entry.`actions_taken` =law_routing_setup.`id`)
        INNER JOIN `humanresource`.`department`
        ON(department.`SysPK_Dept` = law_routing_entry.`from_`)
        INNER JOIN `documenttracker`.`employeedepartment`
        ON(CAST(employeedepartment.`id` AS CHAR(225)) = CAST(law_routing_entry.`to_` AS CHAR(5)))
        where law_routing_entry.status = 0
        GROUP BY `law_routing_entry`.`id`");
        return response()->json(new JsonResponse(['data' => $data, 'status' => 'success']));
    }

    public function edit(Request $request, $id)
    {
        $data['main'] = DB::select("select * from  " . $this->lgu_db . ".law_routing_entry where id = '$id'");
        $data['dtls'] = DB::select("SELECT `routing_setup_id` AS 'id',`routingname` AS 'description' FROM " . $this->lgu_db . ".law_routing_entry_details WHERE `routing_main_id` = $id");
        return response()->json(new JsonResponse(['data' => $data]));
    }

    public function cancel(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $id = $id;
            $data['status'] = '1';
            DB::table($this->lgu_db . '.law_routing_entry')
                ->where('id', $id)
                ->update($data);
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function print(Request $request)
    {
        $data = $request->selected;
        $date=date_create($data['date']);
        $date= date_format($date,"m/d/Y");

        $logo = $this->G->printHeader('City Legal Office');
        $html_content = $logo;
        $html_content .='<br/>';
        $html_content .='<br/>';
        $html_content .='<table style = "width:100%" cellpadding ="2">
        <tr>
        <td width = "8%">To:</td>
        <td width = "62%" style="border-bottom: 1px solid black">'. $data['To'].'</td>
        <td width = "10%" >Ref No:</td>
        <td width = "20%" style="border-bottom: 1px solid black">'. $data['ref'].'</td>
        </tr>
        <br/>
        <tr>
        <td width = "100%">
        <input type="checkbox" checked ="true" name="box" value="1" readonly="true"/>
        <label for="box">'. $data['Detail'].'</label>
        </td>
        </tr>

        <tr>
        <td width = "100%" style="border-bottom: 1px solid black">
        </td>
        </tr>

        <tr>
        <td width = "100%" style="border-bottom: 1px solid black">
        </td>
        </tr>

        <tr>
        <td width = "100%" style="border-bottom: 1px solid black">
        </td>
        </tr>

        <tr>
        <td width = "100%" style="border-bottom: 1px solid black">
        </td>
        </tr>
        <br/>
        <br/>
        <br/>
        <tr>
        <td width = "30%" style="border-bottom: 1px solid black" align="center">'. $date.'</td>
        <td width = "40%"></td>
        <td width = "30%" style="border-bottom: 1px solid black"></td>
        </tr>

        <tr>
        <td width = "30%" align="center" >Date</td>
        <td width = "40%"></td>
        <td width = "30%" align="center" >OIC-CLO</td>
        </tr>

        </table>';

        PDF::SetTitle('Routing');
        PDF::AddPage();
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::SetXY(10, 58);
        $subject = '<p >'.$data['remarks'].'</p>';
        PDF::writeHTML($subject, true, false, false, false, '');


        PDF::Output(public_path().'/print.pdf', 'F');
    }
}
