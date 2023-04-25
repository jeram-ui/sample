<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use \App\Laravue\JsonResponse;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    function getEmployee(Request $request)
    {
        $lgu = config('variable.db_lgu');

        $id = $request->id;
        $groupid = $request->groupid;
        $initiator = $request->initiator;
        $empid = Auth::user()->Employee_id;

        if ($initiator == 0) {
            $status = "checked";
        } else {
            $status = "%";
        }
        $html = '';
        $result_set = DB::select("SELECT * FROM (SELECT
			UCASE(department.`Name_Dept`) AS 'DEPARTMENT'
			,UCASE(hr.`Name_Empl`) AS 'EMPLOYEE NAME'
			,hr.SysPK_Empl AS 'empid'
			,IF(ATT.UID>0,'checked','unchecked') AS 'STATUS'
			,IF(IFNULL(ATT.Confirmed,0)=0,'','Confirmed') AS 'Confirmed'
			FROM `users`
			INNER JOIN `humanresource`.`employees` hr
			ON(hr.`SysPK_Empl` = users.`Employee_id`)
			INNER JOIN humanresource.`department` ON(department.`SysPK_Dept` = hr.`Department_Empl`)
			LEFT JOIN(
			SELECT *FROM(SELECT
			`UID`
			,`Confirmed`
			FROM 	" . $lgu . ".`appointments`
			INNER JOIN " . $lgu . ".`appointmentuser` ON(appointments.`GUID` = appointmentuser.`GUIDS`)
			WHERE appointments.`ID` = '$id'
			UNION ALL
			SELECT  `Initiator`,1 FROM " . $lgu . ".appointments WHERE appointments.`ID` = '$id')A GROUP BY UID,Confirmed
		)ATT ON(hr.SysPK_Empl = ATT.UID)
		where
		hr.SysPK_Empl in(SELECT  `emp_id` FROM " . $lgu . ".`sched_group_member` WHERE `group_id` = '$groupid' AND `emp_id` NOT IN('$empid'))
		GROUP BY users.`Employee_id`,Name_Dept ORDER BY IF(ATT.UID>0,'checked','unchecked') ASC )a");

        $html .= "<table id = 'tbl_list' style='font-size: 12px;' width='100%' class='table'>
		<thead>
		<tr>
		<th hidden >ID</th>
		<th>Select</th>
		<th>Employee Name</th>
		<th>Department</th>
		<th>Status</th>
		</tr>
		</thead>
		<tbody>
		";

        if ($id > 0) {
            foreach ($result_set as $row) {
                $html .= "
				<tr>
				<td hidden>" . $row->empid . "</td>
				<td>
				<label class='switch'>
				<input  " . $row->STATUS . " type='checkbox'>
				<span class='slider round'></span>
				</label>
				</td>
				<td>" . $row->{'EMPLOYEE NAME'} . "</td>
				<td >" . $row->DEPARTMENT . "</td>
				<td >" . $row->Confirmed . "</td>
				</tr>
				";
            }
        } else {
            foreach ($result_set as $row) {
                $html .= "
				<tr>
				<td hidden>" . $row->empid . "</td>
				<td>
				<label class='switch'>
				<input  checked type='checkbox'>
				<span class='slider round'></span>
				</label>
				</td>
				<td>" . $row->{'EMPLOYEE NAME'} . "</td>
				<td >" . $row->DEPARTMENT . "</td>
				<td >" . $row->Confirmed . "</td>
				</tr>
				";
            }
        }

        $html .= "</tbody>
		</table>";
        $data['list'] = $html;
        return $data;
    }
    function getEmployeeListTable(Request $request)
    {
        $posts = Employee::all();
        return response()->json(new JsonResponse(['data' => $posts]));
    }

    
    function getEmployeeList(Request $request)
    {
        $hr = config('variable.db_hr');

        $columns = array(
            0 => 'DEPARTMENT',
            1 => 'POSITION',
            2 => 'NAME',
            3 => 'COMMAND',
        );
        $limit = $request->length;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $totalData = Employee::count();
        $totalFiltered = $totalData;


        if (empty($request->input('search.value'))) {
            $posts = Employee::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $posts =  Employee::where('DEPARTMENT', 'LIKE', "%{$search}%")
                ->orWhere('NAME', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Employee::where('DEPARTMENT', 'LIKE', "%{$search}%")
                ->orWhere('NAME', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $nestedData['DEPARTMENT'] = $post->DEPARTMENT;
                $nestedData['POSITION'] =  $post->POSITION;
                $nestedData['NAME'] =  $post->NAME;
                $nestedData['COMMAND'] =  "<button  class ='btn btn-primary'  onclick='Get_Employee_Info(" . $post->PPID . ")' >Select</button>";
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }
}
