<?php

namespace App\Http\Controllers\Api\Qr;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\db;
use App\Laravue\JsonResponse;
use App\Http\Controllers\Api\GlobalController;

class appointmentController extends Controller
{
    protected $G;

    public function __construct(GlobalController $global)
    {
        $this->G = $global;
    }
    public function showAppointed()
    {
    //     $department = DB::table('user_assign_department')
    //     ->select('department_id', 'user_id')
    //     ->where('user_id', Auth::user()->id);
    //     $query = db::table('pass')
    //     ->join('household_members', 'household_members.id', '=', 'pass.members_id')
    //     ->join('shop_representative', 'shop_representative.client_guid', '=', 'household_members.client_guid')
    //     ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
    //     ->select(db::raw('household_members.`id`
    //     ,pass.id as passid
    //     ,household_members.members_id
    //     ,ucase(barangay.`brgy_name`) AS barangay
    //     ,shop_representative.`rep_address` AS purok
    //     ,`getFullName`(household_members.`id`) AS fullname
    //     ,`gender`
    //     ,`getAge`(`household_members`.`birthdate`) AS age
    //     ,`ts`'))
    //     ->joinSub($department, 'department', function ($join) {
    //         $join->on('pass.department_id', '=', 'department.department_id');
    //     })
    //     ->where('pass.department_id', '>', 0)
    //     ->where('accept', '=', 0);
    //     $results = $query->get();
    //     return response()->json(new JsonResponse($results));
    }
    public function showAppointedList()
    {
        $department = DB::table('user_assign_department')
        ->select('department_id', 'user_id')
        ->where('user_id', Auth::user()->id);
        $query = db::table('pass')
        ->join('household_members', 'household_members.id', '=', 'pass.members_id')
        ->join('shop_representative', 'shop_representative.client_guid', '=', 'household_members.client_guid')
        ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
        ->select(db::raw('household_members.`id`
        ,pass.id as passid
        ,household_members.members_id
        ,ucase(barangay.`brgy_name`) AS barangay
        ,shop_representative.`rep_address` AS purok
        ,`getFullName`(household_members.`id`) AS fullname
        ,`gender`
        ,`getAge`(`household_members`.`birthdate`) AS age
        ,`ts`'))
        ->joinSub($department, 'department', function ($join) {
            $join->on('pass.department_id', '=', 'department.department_id');
        })
        ->where('pass.department_id', '>', 0)
        ->where('accept', '=', 1);
        $results = $query->get();
        return response()->json(new JsonResponse($results));
    }
    public function approved(Request $request)
    {
        $data = array(
            'pass_id'=>$request->passid,
            'uid'=>Auth::user()->id,
            'type'=>0,
        );
        $dataupdate =array(
            'accept'=>1,
            'accept_ts'=>$this->G->serverdatetime(),
            'accept_uid'=>Auth::user()->id
        );
        db::table('pass')->where('id', $request->passid)->update($dataupdate);
        db::table('pass_accept')->insert($data);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function decline(Request $request)
    {
        $data = array(
            'pass_id'=>$request->passid,
            'uid'=>Auth::user()->id,
            'type'=>1,
        );
        $dataupdate =array(
            'accept'=>2,
            'accept_ts'=>$this->G->serverdatetime(),
            'accept_uid'=>Auth::user()->id
        );
        db::table('pass')->where('id', $request->passid)->update($dataupdate);
        db::table('pass_accept')->insert($data);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function transfer(Request $request)
    {
        $data = $request->data;
        $dataupdate =array(
           'department_id'=>$request->department
        );
        db::table('pass')->where('id', $data['passid'])->update($dataupdate);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function forward(Request $request)
    {
        $data = $request->data;
        $dataupdate =array(
            'uid'=>Auth::user()->id,
            'members_id'=>$data['id'],
            'member_guid'=>$data['members_id'],
           'department_id'=>$request->department
        );
        db::table('pass')->insert($dataupdate);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    
}
