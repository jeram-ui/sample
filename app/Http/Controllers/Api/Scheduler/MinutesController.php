<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Api\Scheduler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use DataTables;
use PDF;


class MinutesController extends Controller
{
    private $lgu_db;
    public function __construct()
    {
        $this->middleware('auth');
        $this->lgu_db = config('variable.db_lgu');
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
        return view('scheduler.Minutes');
    }

    public function agenda(Request $request)
    {
        $detail = "<tr>
		<td class='pt-3-half'><TextArea class='form-control' >".$request->agenda."</TextArea></td>
		<td><span class='table-remove'><button type='button' class='btn btn-danger btn-rounded btn-sm my-0 waves-effect waves-light'>Remove</button></span></td>
		</tr>" ;
        $data['list'] = $detail;
        return $data;
    }
    public function list(Request $request)
    {
        $empid = Auth::user()->Employee_id;
        $lgu_db = config('variable.db_lgu');
        $html = '';
        $result_set = db::select("SELECT DISTINCT(sched_group.`group_id`) AS 'id',(`group_name`) AS 'name' FROM ".$lgu_db.".`sched_group` INNER JOIN ".$lgu_db.".`sched_group_member` WHERE `emp_id` = '$empid'");

        foreach ($result_set as $row) {
            $html .="<option value = '".$row->id."'>".$row->name."</option>";
        }
        $data['select'] = $html;
        return $data;
    }

    public function edit(Request $request)
    {
        $id = $request->id;

        $data['main'] = DB::select("select * from  ".$this->lgu_db.".sched_group where group_id = '$id'");
        $result_set = DB::select("SELECT * FROM ".$this->lgu_db.".sched_group_member INNER JOIN `humanresource`.`employees` ON(employees.`SysPK_Empl` = sched_group_member.`emp_id`) WHERE  group_id = '$id'");

        $detail = " ";
        foreach ($result_set as $row) {
            $detail .= "<tr>
			<td class='pt-3-half'><input class='form-control' value = '".$row->position_name."' ></input></td>
			<td ><select class = 'employeex form-control'><option  value = '".$row->emp_id."'>".$row->Name_Empl."</option></select></td>
			<td class='pt-3-half'><input class='form-control' value = '".$row->display_name."' ></input></td>
			<td class='pt-3-half'><span class='table-up'><a  class='indigo-text'><i class='fas fa-long-arrow-alt-up' aria-hidden='true'>↑↑</i></a></span><span class='table-down'><a  class='indigo-text'><i class='fas fa-long-arrow-alt-down' aria-hidden='true'>↓↓</i></a></span>
			</td>
			<td><span class='table-remove'><button type='button' class='btn btn-danger btn-rounded btn-sm my-0 waves-effect waves-light'>Remove</button></span></td>
			</tr>" ;
        }

        $data['details'] = $detail;
        return $data;
    }

    public function show()
    {
        $lgu_db = config('variable.db_lgu');
        $lgu_hr = config('variable.db_hr');
        $result_set = DB::select("SELECT group_id ,`DATE COVERED`,GROUPNAME,GROUP_CONCAT(A.GROUP  ORDER BY id ASC SEPARATOR '<br>') AS 'GROUP' FROM (SELECT
			`sched_group`.`group_id`
			,CONCAT(DATE_FORMAT(sched_group.`date_from`,'%m/%d/%Y'),' - ',DATE_FORMAT(sched_group.`date_to`,'%m/%d/%Y'))AS 'Date Covered'
			,`sched_group`.`group_name`AS 'GROUPNAME'
			, CONCAT(sched_group_member.`position_name`,' - ',`sched_group_member`.`display_name`) AS 'GROUP'
			,sched_group_member.`id`
			FROM ".$lgu_db.".`sched_group`
			INNER JOIN ".$lgu_db.".`sched_group_member` ON(`sched_group`.`group_id` = sched_group_member.`group_id`)
			INNER JOIN ".$lgu_hr.".`employees` ON(employees.`SysPK_Empl` = sched_group_member.`emp_id` )
			AND sched_group.`stat` = 0
			ORDER BY sched_group_member.`id` ASC )a GROUP BY group_id");
        $html='';
        $html .= "
		<table id='TransactionList'  class='table table-bordered table-responsive-md table-striped display' cellspacing='0' width='100%' >
		<thead>
		<tr>
		<th>id</th>
		<th>Date Covered</th>
		<th>Group Name</th>
		<th>Members</th>
		</tr>
		</thead>
		<tbody >
		";
        foreach ($result_set as $row) {
            $html .= "<tr>
			<td>".$row->group_id."</td>
			<td>".$row->{'DATE COVERED'}."</td>
			<td>".$row->GROUPNAME."</td>
			<td>".$row->GROUP."</td>
			</tr>
			"
            ;
        }

        $html .= "</tbody>
		</table>";
        $data['list'] = $html;
        return $data;
    }

    public function store(Request $request)
    {
        $lgu_db = config('variable.db_lgu');
        $user = json_decode($request->user, true);
        try {
            DB::beginTransaction();
            $stat = ($request->input('status') == 'true' ? "0" : "1");
            $idx = $request->input('idx');
            $data = request()->all();
            $data = $request->except('_token', 'idx', 'status', 'user');
            $data['stat'] = $stat;
            if ($idx > 0) {
                DB::table($lgu_db.'.sched_group')
                ->where('group_id', $idx)
                ->update($data);
                DB::table($lgu_db.'.sched_group_member')->where('group_id', '=', $idx)->delete();
                foreach ($user  as  $items) {
                    $data =array('group_id' => $idx, 'position_name' => $items['position'],'emp_id'=>$items['empid'],'display_name'=>$items['display']);
                    DB::table($lgu_db.'.sched_group_member')->insert($data);
                }
            } else {
                DB::table($lgu_db.'.sched_group')->insert($data);


                $id = DB::getPdo()->lastInsertId();
                foreach ($user  as  $items) {
                    $data =array('group_id' => $id, 'position_name' => $items['position'],'emp_id'=>$items['empid'],'display_name'=>$items['display']);
                    DB::table($lgu_db.'.sched_group_member')->insert($data);
                }
            }
            DB::commit();
            return response()->json(['Message'=>'Transaction completed successfully.','status'=>'success','id'=>$idx]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['Message'=>$e,'status'=>'error']);
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
        $names ="";
        foreach ($result_set as  $row) {
            $html .="<option  value = '".$row->PPID."'>".$row->NAME."</option>";
            $names = $row->NAME;
        }
        $html .= '</select>

		</td>
		<td class="pt-3-half" >
		<input class="form-control" value = "'.$names.'" ></input>
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
