<?php

/**
 * File UserController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Laravue\JsonResponse;
use App\Laravue\Models\Permission;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Mail\VerifyMail;
use Illuminate\Support\Facades\Mail;
use App\VerifyUser;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    const ITEM_PER_PAGE = 15;
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $userQuery = User::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($role)) {
            $userQuery->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if (!empty($keyword)) {
            $userQuery->where('name', 'LIKE', '%' . $keyword . '%');
            $userQuery->where('email', 'LIKE', '%' . $keyword . '%');
        }

        return UserResource::collection($userQuery->paginate($limit));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'password' => ['required'],
                    'confirmPassword' => 'same:password',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $user = User::create([
                'name' => $params['name'],
                'email' => $params['email'],
                'Employee_id' => $params['Employee_id'],
                'password' => Hash::make($params['password']),
            ]);

            $role = Role::findByName('admin');
            $user->syncRoles($role);

            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        }
    }
    public function storeInside(Request $request)
    {
        log::debug($request->all());
        $params = $request->all();
        // log::debug($params['id']);
        $id = $params['id'];
        if ($id > 0 ) {
            DB::table('users')
                     ->where('id', $request->id)
                     ->update([
                        'name' => $params['name'],
                        'email' => $params['email'],
                        'Employee_id' => $params['Employee_id'],
                        'email_verified_at' => Date(Now()),
                        'password' => Hash::make($params['password']),
                    ]);
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));  
        }else{
            $validator = Validator::make(
                $request->all(),
                array_merge(
                    $this->getValidationRules(),
                    [
                        'password' => ['required'],
                        'confirmPassword' => 'same:password',
                    ]
                )
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 403);
            } else {
                    $user = User::create([
                        'name' => $params['name'],
                        'email' => $params['email'],
                        'Employee_id' => $params['Employee_id'],
                        'email_verified_at' => Date(Now()),
                        'password' => Hash::make($params['password']),
                    ]);
                    $user->markEmailAsVerified();
                return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
            }
        }
        
    }
    /**

     * @bodyParam title string required The title of the post.
     * @bodyParam body string required The content of the post.
     * @bodyParam type string The type of post to create. Defaults to 'textophonious'.
     * @bodyParam author_id int the ID of the author. Example: 2
     * @bodyParam thumbnail image This is required if the post type is 'imagelicious'.
     */
    public function show(Request $request)
    {
        // $posts = User::where('status', '0')->get();
        $profile = DB::table('form_user_profile')
            ->join('form_profile', 'form_profile.id', '=', 'form_user_profile.profile_id')
            ->select('uid', db::raw('group_concat(form_profile.profile_name separator "<br/>") as profile_access'))
            ->groupBy('form_user_profile.uid');

        $posts = db::table('users')
            ->leftJoinSub($profile, 'profile', function ($join) {
                $join->on('profile.uid', '=', 'users.id');
            })
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', '=', 'users.Employee_id')
            ->select('users.*', 'employee_information.DEPARTMENT', 'profile.profile_access')
            ->get();
        return response()->json(new JsonResponse(['data' => $posts]));
    }

    public function update(Request $request)
    {
        if ($request->id === null) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $email = $request->get('email');
            $found = User::where('email', $email)->first();
            if ($found && $found->id !== $request->id) {
                return response()->json(['error' => 'username has been taken'], 403);
            }
            $old = User::where('id', $request->id)->first();
            if (Hash::check($request->old, $old->password)) {
            } else {
                return response()->json(['error' => 'Invalid Old Password'], 403);
            }
            $data['name'] = $request->get('name');
            $data['email'] = $request->get('email');
            $data['password'] = Hash::make($request['new']);
            DB::table('users')
                ->where('id', $request->id)
                ->update($data);
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
            // return new UserResource($user);
        }
    }

    public function updatePermissions(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $permissionIds = $request->get('permissions', []);
        $rolePermissionIds = array_map(
            function ($permission) {
                return $permission['id'];
            },
            $user->getPermissionsViaRoles()->toArray()
        );
        $newPermissionIds = array_diff($permissionIds, $rolePermissionIds);
        $permissions = Permission::allowed()->whereIn('id', $newPermissionIds)->get();
        $user->syncPermissions($permissions);
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            response()->json(['error' => 'Ehhh! Can not delete admin user'], 403);
        }

        try {
            $user->delete();
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }
        return response()->json(null, 204);
    }

    public function permissions(User $user)
    {
        try {
            return new JsonResponse([
                'user' => PermissionResource::collection($user->getDirectPermissions()),
                'role' => PermissionResource::collection($user->getPermissionsViaRoles()),
            ]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }
    }
    private function getValidationRules($isNew = true)
    {
        return [
            'name' => 'required',
            'email' => $isNew ? 'required|unique:users' : 'required',
        ];
    }
    public function cancel($id)
    {
        $id = $id;
        $data['status'] = '1';
        DB::table('users')
            ->where('id', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function edit($id)
    {

       
      $list= DB::table('users')
            ->where('id', $id)
            ->get();
        return response()->json(new JsonResponse( $list));
    }
    public function display()
    {
        $item = DB::table('users')
            ->select('name', 'email', 'isActive')
            ->where('isActive', 'Active')
            ->get();
        return response()->json(new JsonResponse($item));
    }
    public function sendMessage(Request $request)
    {
        $main = $request->main;
        DB::table('chatlogs')
            ->insert($main);
        return response()->json(new JsonResponse(['Message' => 'Message successfully sent', 'status' => 'success']));
    }
    public function showMessage(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $data =  DB::select("
        SELECT *,IF(" . $from . " = `from_uid`,'sender','receiver') AS 'type' FROM `chatlogs`
        WHERE (`from_uid` =" . $from . " AND `to_uid` = " . $to . ")
        OR (`to_uid` =" . $from . " AND `from_uid` = " . $to . ")
        ORDER BY `chatlogs`.`ts` ASC
        ");
        db::select("UPDATE `chatlogs` SET isRead=1
        WHERE (`from_uid` =" .  $to . " AND `to_uid` = " .$from  . ")
        ");
        return response()->json(new JsonResponse($data));
    }
    public function getMessageNotification(Request $request)
    {
        $data =  DB::select("SELECT *
        ,COUNT(*) AS 'count'        
        FROM `chatlogs`
        WHERE `to_uid` = ".Auth::user()->id."
        AND `isRead` = 0
        GROUP BY `from_uid`
        ORDER BY `chatlogs`.`ts` ASC;
        ");
        return response()->json(new JsonResponse($data));
    }
}
