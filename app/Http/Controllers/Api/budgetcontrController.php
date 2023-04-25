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
class budgetcontrController extends BaseController
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

    public function store(Request $request)
    {
        $form = $request->form;


        $id = $form['id'];
        if ($id > 0) {
            db::table($this->hr_db . ".budget_controller")
                ->where('id', $id)
                ->update($form);

        } else {
            db::table($this->hr_db . ".budget_controller")->insert($form);
            $id = DB::getPdo()->LastInsertId();

        }
    }
    public function getBudgetContrl()
    {
        $list = DB::table($this->hr_db.'.budget_controller')
        ->join($this->hr_db . '.department', 'department.SysPK_Dept', 'budget_controller.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'budget_controller.emp_id')
        ->where('budget_controller.status', 0)
        ->get();

        return response()->json(new JsonResponse($list));
    }

    public function removing($id)
    {
        db::table($this->hr_db . ".budget_controller")
            ->where('id' , $id)
            ->update(['status' => 1]);
        // $this->G->success();
    }

    public function getEmpRequest()
    {

        $list = DB::table($this->hr_db.'.employee_information')
        // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
        // ->where('DEPID', $id)
          ->get();
        return response()->json(new JsonResponse($list));
    }

    public function getDepartment()
    {
        $list = DB::table($this->hr_db . '.department')
            ->select("*", 'SysPK_Dept', 'Name_Dept')
            ->where('department.status', 'Active')
            ->get();

        return response()->json(new JsonResponse($list));
    }




}
