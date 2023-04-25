<?php

namespace App\Http\Controllers\Api\DocumentTrucker;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \App\Laravue\JsonResponse;
use Illuminate\Support\Facades\log;
class FlowController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    public function __construct()
    {
        $this->middleware('auth');
        $this->lgu_db = config('variable.db_lgu');
        $this->hr_db = config('variable.db_hr');
        $this->trk_db = config('variable.db_trk');
    }
    public function create(Request $request)
    {
    }
    public function getDb()
    {
        return config('variable.db_lgu');
    }
    public function index()
    {
        return view('trucker.Flow');
    }
    function list(Request $request) {
        $empid = Auth::user()->Employee_id;
        $lgu_db = $this->lgu_db;
        $html = '';
        $result_set = db::select("SELECT DISTINCT(sched_group.`group_id`) AS 'id',(`group_name`) AS 'name' FROM " . $lgu_db . ".`sched_group` INNER JOIN " . $lgu_db . ".`sched_group_member` WHERE `emp_id` = '$empid'");
        foreach ($result_set as $row) {
            $html .= "<option value = '" . $row->id . "'>" . $row->name . "</option>";
        }
        $data['select'] = $html;
        return $data;
    }
    function docType(){
        $data = DB::select("SELECT `SysPK_id`,`office` FROM ".$this->trk_db.".documenttype ");
        return response()->json(new JsonResponse(['data' => $data]));
    }

    public function edit(Request $request, $id)
    {
        $id = $id;
        $data['main'] = DB::select("select * from  " . $this->trk_db . ".doc_flow_main where id = '$id'");
        $data['details'] = DB::select("SELECT   `emp_id` ppid,
        doc_flow_employee.`Signatory_name` signatory,
        employees.`Name_Empl` employee,
        doc_flow_employee.`display_name` displayname  FROM " . $this->trk_db . ".doc_flow_employee INNER JOIN " . $this->hr_db . ".employees ON(employees.`SysPK_Empl` = doc_flow_employee.`emp_id`) WHERE  doc_flow_main_id = '$id'");
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function cancel(Request $request, $id)
    {
        $id = $id;
        $data['status'] = '1';
        DB::table($this->trk_db . '.doc_flow_main')
            ->where('id', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function show()
    {
        $trk_db = $this->trk_db;
        $result_set = DB::select("SELECT
        doc_flow_main.`id`
        ,doc_flow_main.`flow_name` AS 'Flow Name'
        ,doc_flow_employee.`Signatory_name`
        ,GROUP_CONCAT(doc_flow_employee_details.`display_name` SEPARATOR '<br>')AS 'names'
        ,doc_flow_employee_details.`display_name`
        ,doc_flow_employee.`id` AS 'dtlid'
        ,doc_flow_employee_details.id
        ,doc_flow_employee.noflow
        FROM ".$trk_db.".doc_flow_main
        left JOIN ".$trk_db.".doc_flow_employee
        ON(doc_flow_main.`id` = doc_flow_employee.`doc_flow_main_id`)
        left JOIN ".$trk_db.".doc_flow_employee_details
        ON(doc_flow_employee_details.`sig_id` = doc_flow_employee.`id`)
        WHERE doc_flow_main.status = 0
        GROUP BY doc_flow_employee.`id`
        ORDER BY doc_flow_employee.`id`
        ");
        return response()->json(new JsonResponse(['data' => $result_set]));
    }
    public function signatory($id){
    log::debug($id);
    log::debug($this->trk_db);
    $data = DB::table($this->trk_db.".doc_flow_employee_details")
    ->where('sig_id',$id)->get()
    ;
    return response()->json(new JsonResponse($data));
}
    public function getTagReferral($id){
        $trk_db = $this->trk_db;
        $result_set = DB::select("SELECT `flow_name` FROM " . $trk_db . ".doc_flow_tag WHERE `flow_group` = '".$id."'");
        return response()->json(new JsonResponse(['data' => $result_set]));
    }

    
    public function updateSignatory(Request $request)
    {
        $lgu_db = $this->lgu_db;
        $trk_db = $this->trk_db;
        $datax = $request->signatory;
        $selected = $request->selected;
       log::debug($request);
       log::debug($datax['dtlid']);
        try {
            DB::beginTransaction();
            db::table( $trk_db.'.doc_flow_employee_details')
            ->where('sig_id',$datax['dtlid'])->delete();
            db::table($trk_db.'.doc_flow_employee')
            ->where('id',$datax['dtlid'])
            ->update(['Signatory_name'=>$datax['Signatory_name'],'noflow'=>$datax['noflow']])
            ;
            foreach ($selected as $key => $value) {
                $dtls = array(
                    'sig_id'=>$datax['dtlid'],
                    'emp_id'=>$value['PPID'],
                    'display_name'=>$value['NAME'],
                );
                db::table( $trk_db.'.doc_flow_employee_details')->insert($dtls);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function store(Request $request)
    {
        $lgu_db = $this->lgu_db;
        $trk_db = $this->trk_db;
        $idx = $request->idx;
        $user = $request->list;
        try {
            DB::beginTransaction();
            $data = request()->all();
            $data = $request->except('_token', 'idx', 'status', 'list');
            if ($idx > 0) {
                DB::table($trk_db . '.doc_flow_main')
                    ->where('id', $idx)
                    ->update($data);
                DB::table($trk_db . '.doc_flow_employee')->where('doc_flow_main_id', '=', $idx)->delete();
                foreach ($user as $items) {
                    $data = array('doc_flow_main_id' => $idx, 'Signatory_name' => $items['signatory']);
                    DB::table($trk_db . '.doc_flow_employee')->insert($data);
                }
            } else {

                DB::table($trk_db . '.doc_flow_main')->insert($data);
                $id = DB::getPdo()->lastInsertId();
                foreach ($user as $items) {
                    $data = array('doc_flow_main_id' => $id, 'Signatory_name' => $items['signatory']);
                    DB::table($trk_db . '.doc_flow_employee')->insert($data);
                    $idSig = DB::getPdo()->lastInsertId();
                    foreach ($items['selected'] as $key => $value) {
                       $sigData = array(
                           'sig_id'=> $idSig,
                           'emp_id'=> $value['PPID'],
                           'display_name'=> $value['NAME'],
                       );
                       DB::table($trk_db . '.doc_flow_employee_details')->insert($sigData);
                    }
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
}
