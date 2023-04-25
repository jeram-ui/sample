<?php
namespace App\Http\Controllers\Api\Scheduler;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \App\Laravue\JsonResponse;

class GroupController extends Controller
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
        return view('scheduler.Group');
    }
    public function edit(Request $request, $id)
    {
        $id = $id;
        $data['main'] = DB::select("select * from  " . $this->lgu_db . ".sched_group where group_id = '$id'");
        $data['details'] = DB::select("SELECT
        sched_group_member.`emp_id` AS 'ppid',
        sched_group_member.`position_name` AS 'signatory',
        employees.`Name_Empl` AS 'employee',
        sched_group_member.`display_name` AS 'displayname'  FROM " . $this->lgu_db . ".sched_group_member INNER JOIN `humanresource`.`employees` ON(employees.`SysPK_Empl` = sched_group_member.`emp_id`) WHERE  group_id = '$id'");
        return response()->json(new JsonResponse($data));
    }
    public function cancel(Request $request, $id)
    {

        try
        {
            DB::beginTransaction();
            $id = $id;
            $data['stat'] = '1';
            DB::table($this->lgu_db . '.sched_group')
                ->where('group_id', $id)
                ->update($data);
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }

    }

    public function show()
    {
        $lgu_db = config('variable.db_lgu');
        $lgu_hr = config('variable.db_hr');
        $result_set = DB::select("SELECT group_id  ,`DATE COVERED`,GROUPNAME,GROUP_CONCAT(A.GROUP  ORDER BY id ASC SEPARATOR '<br>') AS 'GROUP' FROM (SELECT
			`sched_group`.`group_id`
			,CONCAT(DATE_FORMAT(sched_group.`date_from`,'%m/%d/%Y'),' - ',DATE_FORMAT(sched_group.`date_to`,'%m/%d/%Y'))AS 'Date Covered'
			,`sched_group`.`group_name`AS 'GROUPNAME'
			, CONCAT(sched_group_member.`position_name`,' - ',`sched_group_member`.`display_name`) AS 'GROUP'
			,sched_group_member.`id`
			FROM " . $lgu_db . ".`sched_group`
			INNER JOIN " . $lgu_db . ".`sched_group_member` ON(`sched_group`.`group_id` = sched_group_member.`group_id`)
			INNER JOIN " . $lgu_hr . ".`employees` ON(employees.`SysPK_Empl` = sched_group_member.`emp_id` )
			AND sched_group.`stat` = 0
			ORDER BY sched_group_member.`id` ASC )a GROUP BY group_id");
        return response()->json(new JsonResponse(['data' => $result_set]));
    }

    public function store(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        try
        {
            DB::beginTransaction();
            $idx = $request->main['group_id'];
            $main = $request->main;
            $user = $request->list;
            if ($idx > 0) {
                DB::table($lgu_db . '.sched_group')
                    ->where('group_id', $idx)
                    ->update($main);
                DB::table($lgu_db . '.sched_group_member')->where('group_id', '=', $idx)->delete();
                foreach ($user as $items) {
                    $data = array('group_id' => $idx, 'position_name' => $items['signatory'], 'emp_id' => $items['ppid'], 'display_name' => $items['displayname']);
                    DB::table($lgu_db . '.sched_group_member')->insert($data);
                }
            } else {
                DB::table($lgu_db . '.sched_group')->insert($main);
                $id = DB::getPdo()->lastInsertId();
                foreach ($user as $items) {
                    $data = array('group_id' => $id, 'position_name' => $items['signatory'], 'emp_id' => $items['ppid'], 'display_name' => $items['displayname']);
                    DB::table($lgu_db . '.sched_group_member')->insert($data);
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }

    }

    public function Display_Name(Request $request)
    {
        $empid = $request->empid;
        $result_set = Employee::where('PPID', $empid)->get();
        $html = '';
        $html .= '<tr>
		<td class="pt-3-half" >
		<input class="form-control"></input>
		</td>
		<td >
		<select class = "employeex form-control"  >;
		';
        $names = "";
        foreach ($result_set as $row) {
            $html .= "<option  value = '" . $row->PPID . "'>" . $row->NAME . "</option>";
            $names = $row->NAME;
        }
        $html .= '</select>

		</td>
		<td class="pt-3-half" >
		<input class="form-control" value = "' . $names . '" ></input>
		</td>
		<td class="pt-3-half">
		<span class="table-up"><a href="#!" class="indigo-text"><i class="fas fa-long-arrow-alt-up" aria-hidden="true">↑↑</i></a></span>
		<span class="table-down"><a href="#!" class="indigo-text"><i class="fas fa-long-arrow-alt-down" aria-hidden="true">↓↓</i></a></span>
		</td>
		<td>
		<span class="table-remove"><button type="button" class="btn btn-danger btn-rounded btn-sm my-0 waves-effect waves-light">Remove</button></span>
		</td>
		</tr>';
        $data['list'] = $html;
        return $data;
    }
}
