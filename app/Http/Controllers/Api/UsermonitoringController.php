<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class UsermonitoringController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $sched_db;
    private $empid;
    protected $G;
    private $path;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
        $this->path = 'http://20.20.40.220:8000/lgu_back/public/images/client/';
    }
    public function users()
    {
        // $data = DB::select(
        //     "SELECT *,CONCAT(".$this->path.".'/images/client/',users.image_path) AS image from el_laravue.users WHERE Employee_id > 0 "
        // );
        // $results = DB::table("users")
        //     ->join($this->hr_db . '.employee_information', 'employee_information.PPID', '=', 'users.Employee_id')
        //     ->select(
        //         'users.*',
        //         'employee_information.DEPARTMENT',
        //         'employee_information.POSITION',
        //         db::raw('CONCAT("' . $this->path . '/images/client/",users.image_path) AS image')
        //     )
        //     ->where('Employee_id', '>', 0)
        //     ->where('Employee_id', '<>', Auth::user()->Employee_id)
        //     ->orderByDesc('isLogin')->get();
            $results= db::select("call getUserList(?,?)",[Auth::user()->id,$this->path]);
        return response()->json(new JsonResponse($results));
    }
    public function userdeparments()
    {
        $data = DB::select("SELECT DEPID 'id',DEPARTMENT 'name' FROM humanresource.employee_information INNER JOIN el_laravue.users ON Employee_id=ppid WHERE Employee_id>0");
        return response()->json(new JsonResponse($data));
    }
    public function logout($email)
    {
        db::table('users')->where('email', $email)->update(['isLogin' => 0, 'Logout' => $this->G->serverdatetime()]);
    }
}
