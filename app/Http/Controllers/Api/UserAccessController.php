<?php

/**
 * File AuthController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use App\Laravue\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMailable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\log;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class UserAccessController extends BaseController
{
    use RegistersUsers;
    use VerifiesEmails;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    private $general;
    protected $G;

    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->signatory = $this->G->signatoryReport();
    }


    public function insertform(Request $request)
    {
        try {
            db::beginTransaction();
            db::table('form_name')->delete();
            $main = $request->main;
            db::table('form_name')->insert($main);
            db::commit();
            return response()->json(new JsonResponse(['Message' => 'Password successfully reset!', 'status' => 'success']));
        } catch (\Throwable $th) {
            return response()->json(new JsonResponse(['Message' => $th, 'status' => 'success']));
            db::rollback();
        }
    }
    public function DepartmentApproval(Request $request)
    {
        try {
            $list = $request->form;
            db::beginTransaction();
            foreach ($list as $key => $value) {
                db::table($this->hr_db . ".department")->where("SysPK_Dept", $value['DEPID'])->update(['ir_head' => $value['ir_head']]);
            }
            db::commit();
            return response()->json(new JsonResponse(['Message' => 'Completed Successfully!', 'status' => 'success']));
        } catch (\Throwable $th) {
            return response()->json(new JsonResponse(['Message' => $th, 'status' => 'success']));
            db::rollback();
        }
    }

    public function departmentList(Request $request)
    {
        $list = db::select("call " . $this->hr_db . ".rans_display_department_list()");
        return response()->json(new JsonResponse($list));
    }
    public function formList($profile_id)
    {

        try {

            $access = DB::table('form_profile_access')
                ->where('profile_id', $profile_id);
            $form_name = DB::table('form_name')
                ->leftJoinSub($access, 'access', function ($join) {
                    $join->on('form_name.file_route', '=', 'access.file_route')
                        ->on('form_name.access', '=', 'access.access');
                })
                ->select('form_name.*', db::raw('if(access.id is null,"0","1")as Selected'), db::raw('CONCAT(`module`," -> ",`grouping`," -> ",`form_name`," -> ",form_name.access) as title'))
                ->get();

            return response()->json(new JsonResponse($form_name));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function formProfile()
    {
        try {
            $query = DB::table("form_profile")->get();
            return response()->json(new JsonResponse($query));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function profileStore(Request $request)
    {
        try {
            $id = $request->form['id'];
            if ($id === 0) {
                $query = DB::table("form_profile")->insert($request->form);
            } else {
                $query = DB::table("form_profile")->where('id', $id)->update($request->form);
            }

            return response()->json(new JsonResponse(['Message' => 'Completed Successfully', 'status' => 'success']));
        } catch (\Throwable $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function storeProfile(Request $request)
    {
        try {
            $profile = $request->profile;
            $access = $request->access;
            db::beginTransaction();
            DB::table('form_profile_access')->where('profile_id', $profile)->delete();
            foreach ($access as  $value) {
                $data = array(
                    'profile_id' => $profile,
                    'file_route' => $value['file_route'],
                    'access' => $value['access']
                );
                DB::table('form_profile_access')->insert($data);
            }
            db::commit();
            return response()->json(new JsonResponse(['Message' => 'Successfully Updated', 'status' => 'success']));
        } catch (\Throwable $e) {
            db::rollback();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsh' => $e, 'status' => 'error']));
        }
    }
    public function userProfile(Request $request)
    {
        try {
            db::beginTransaction();
            $uid = $request->uid;
            $profile = $request->profile_id;
            db::table('form_user_profile')->where('uid', $uid)->delete();
            foreach ($profile as  $value) {
                $data = array(
                    'uid' => $uid,
                    'profile_id' => $value
                );
                DB::table('form_user_profile')->insert($data);
            }
            db::commit();
            return response()->json(new JsonResponse(['Message' => 'Successfully Updated', 'status' => 'success']));
        } catch (\Throwable $th) {
            db::rollback();
            return response()->json(new JsonResponse(['Message' => $th, 'status' => 'success']));
        }
    }
    public function userProfileAccess($id)
    {
        $query = DB::table('form_user_profile')->where('uid', $id)->get();
        return response()->json(new JsonResponse($query));
    }
}
