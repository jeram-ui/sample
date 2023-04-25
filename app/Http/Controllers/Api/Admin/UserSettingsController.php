<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Laravue\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function GetUserList()
    {
        $post =db::table('users')
        ->select(db::raw('id,Employee_id as "empid",
        name,
        email,
        DATE_FORMAT(created_at,"%b.%d,%Y %H:%m") AS Created,
        CASE WHEN email_verified_at IS NULL THEN "For Email Verification"
             WHEN approved = 0 THEN "For Admin Verification"
             WHEN cancel = 1 THEN "Deactivated" ELSE "Email Verified" END "Status" '))
             ->where('Employee_id',0)
        ->get();
        return response()->json(new JsonResponse($post));
    }

    public function GetUserRole(Request $request)
    {
        $id = $request->id;
        $post = DB::select('call getUserRoleList(?)', array($id));
        return response()->json(new JsonResponse($post));
    }

    public function StoreUserRole(Request $request)
    {
        $main = $request->main;
        $id = $request->main['userid'];

        $roles = $request->roles;

        if ($id  == 0) {
        } else {
            DB::table('users')
                ->where('id', $id)
                ->update(['name' => $main['name'], 'email' => $main['email']]);
            db::table('user_role')->where('userid', $id)->delete();
        }
      
        foreach ($roles as $row) {
            $data = array(
                'userid' => $id,
                'module' => $row['module'],
                'Read' => $row['Read'],
                'Write' => $row['Write'],
                'Create' => $row['Create'],
                'Delete' => $row['Delete'],
            );
            db::table('user_role')->insert($data);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'sucess']));
    }

    public function CheckPermission(Request $request)
    {
        $id = $request->userid;
        $frm = $request->frm;
        $post = DB::select('call checkUserRole(?,?)', array($id, $frm));
  
        return response()->json(new JsonResponse($post));
    }

    public function VerifyUser(Request $request)
    {
        $id = $request->userid;
        DB::table('users')
            ->where('id', $id)
            ->update(['approved' => 1]);
 
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'sucess']));
    }

}
