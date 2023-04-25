<?php

namespace App\Http\Controllers\Api\Mod_HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class training_SchedController extends Controller
{
    private $lgu_db;
    private $hr_db;


    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }

    // public function GetEmpName()
    // {

    //     $list = DB::table($this->hr_db.'.tbl_overtime_cert_dtl')
    //     ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')

    //       ->get();
    //     return response()->json(new JsonResponse($list));
    // }
    public function GetEmpName()
    {

        $list = DB::table($this->hr_db . '.employee_information')
            // ->join($this->hr_db.'.employee_information','employee_information.PPID','tbl_overtime_cert_dtl.emp_id')
            // ->where('DEPID', $id)
            ->orderBy("employee_information.NAME")
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

    public function list()
    {
        // $list = DB::table($this->hr_db . '.training_schedule')
        //     ->where('training_schedule.status', 'Active')
        //     ->get();

        // return response()->json(new JsonResponse($list));
        $data = DB::table($this->hr_db . '.training_schedule')
            ->where('training_schedule.status', 'Active')
            ->whereNotIn('training_schedule.typex', [21])
            ->get();
        $array = array();
        foreach ($data as $key => $value) {
            // $value;
            $dtlx = db::table($this->hr_db . ".training_schedule_dtls")
                ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'training_schedule_dtls.emp_id')
                ->select("employee_information.NAME as Attendee")
                ->where("training_schedule_dtls.cert_id", $value->id)
                ->get();
            $datax = array(
                'id' => $value->id,
                'ref_no' => $value->ref_no,
                'ref_date' => $value->ref_date,
                'subject' => $value->project_name,
                'remarks' => $value->remarks,
                'date_from' => $value->date_from,
                'date_to' => $value->date_to,
                'details' => $dtlx,
            );
            // $datax['dtls'] = $dtlx;
            array_push($array, $datax);
        }
        return response()->json(new jsonresponse($array));
    }

    public function listForcedLeave()
    {
        $data = DB::table($this->hr_db . '.training_schedule_dtls')
            ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'training_schedule_dtls.emp_id')
            ->join($this->hr_db . ".training_schedule", 'training_schedule_dtls.cert_id', 'training_schedule.id')
            ->select("*", db::raw('date(date_from) as date_from'))
            ->where('training_schedule.status', 'Active')
            ->where('training_schedule.typex', 21)
            ->whereYear('training_schedule.date_from', date("Y"))
            ->orderBy('training_schedule.id', "desc")
            ->get();
        $array = array();
        foreach ($data as $key => $value) {
            // $value;
            // $dtlx = db::table($this->hr_db . ".training_schedule_dtls")
            //     ->join($this->hr_db . ".employee_information", 'employee_information.PPID', 'training_schedule_dtls.emp_id')
            //     ->select("employee_information.NAME as Attendee")
            //     ->where("training_schedule_dtls.cert_id", $value->id)
            //     ->get();
            $datax = array(
                'id' => $value->id,
                'ref_no' => $value->ref_no,
                'ref_date' => $value->ref_date,
                'subject' => $value->project_name,
                'remarks' => $value->remarks,
                'date_from' => $value->date_from,
                'date_to' => $value->date_to,
                'name' => $value->NAME,
                'savedate' => $value->savedate,
            );
            // $datax['dtls'] = $dtlx;
            array_push($array, $datax);
        }
        return response()->json(new jsonresponse($array));
    }

    public function checkDateApply(Request $request)
    {


        $list = DB::table($this->hr_db . '.training_schedule_dtls')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'training_schedule_dtls.emp_id')
            ->join($this->hr_db . '.training_schedule', 'training_schedule.id', 'training_schedule_dtls.cert_id')
            ->whereDate('training_schedule.date_from', $request->date)
            ->where('training_schedule_dtls.emp_id', $request->emp_id)
            ->where('training_schedule.status', 'Active')
            ->get();

        // $list="";
        return response()->json(new JsonResponse($list));
    }

    public function getCert()
    {

        $list = DB::table($this->hr_db . '.training_schedule_dtls')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'training_schedule_dtls.emp_id')
            ->join($this->hr_db . '.training_schedule', 'training_schedule.id', 'training_schedule.cert_id', 'training_schedule_dtls.id')
            // ->select('*',db::raw('cert_id', 'emp_id','date' ),'tbl_overtime_cert_dtl.cert_id','tbl_overtime_cert.id')
            ->where('training_schedule.status', 'Active')
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        $data['office'] = db::table($this->hr_db . '.training_schedule')->where('id', $id)->get();
        $data['Empname'] = db::table($this->hr_db . '.training_schedule_dtls')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'training_schedule_dtls.emp_id')
            ->join($this->hr_db . '.training_schedule', 'training_schedule.id', 'training_schedule_dtls.cert_id')
            ->select("*", db::raw('DATE(date_from) as date_from'))
            ->where('cert_id', $id)
            ->get();

        // $data['formz'] =db::table($this->hr_db .'.sworn_assets')->where('mainID', $id)->get();


        return response()->json(new JsonResponse($data));
    }
    public function getRef($dateX)
    {
        // dd($request);
        $pre = 'TS';
        $table = $this->hr_db . ".training_schedule";
        $date = $dateX;
        $refDate = 'ref_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        // foreach ($data as $key => $value) {
        //     return $value->NOS;
        // }
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function getRefDIrect($dateX)
    {
        // dd($request);
        $pre = 'TS';
        $table = $this->hr_db . ".training_schedule";
        $date = $dateX;
        $refDate = 'ref_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        foreach ($data as $key => $value) {
            return $value->NOS;
        }
        // return response()->json(new JsonResponse(['data' => $data]));
    }
    public function Store(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $id = $form['id'];
        try {
            DB::beginTransaction();
            if ($id > 0) {
                db::table($this->hr_db . ".training_schedule")
                    ->where('id', $id)
                    ->update($form);
                db::table($this->hr_db . ".training_schedule_dtls")
                    ->where("cert_id", $id)
                    ->delete();
                foreach ($formx as $key => $value) {
                    $datx = array(
                        'cert_id' => $id,
                        'emp_id' => $value['emp_id'],
                    );
                    db::table($this->hr_db . ".training_schedule_dtls")->insert($datx);
                }
            } else {
                // $form['ref_no'] = $this->getRef($form['date_from']);
                $form['uid'] = Auth::user()->Employee_id;
                db::table($this->hr_db . ".training_schedule")->insert($form);
                $id = DB::getPdo()->LastInsertId();
                foreach ($formx as $key => $value) {
                    $datx = array(
                        'cert_id' => $id,
                        'emp_id' => $value['emp_id'],
                    );
                    db::table($this->hr_db . ".training_schedule_dtls")->insert($datx);
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            //throw $th;
        }
    }
    public function StoreForced(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $id = $form['id'];
        try {
            DB::beginTransaction();
            if ($id > 0) {
                // db::table($this->hr_db . ".training_schedule")
                //     ->where('id', $id)
                //     ->update($form);
                // db::table($this->hr_db . ".training_schedule_dtls")
                //     ->where("cert_id", $id)
                //     ->delete();
                // foreach ($formx as $key => $value) {
                //     $datx = array(
                //         'cert_id' => $id,
                //         'emp_id' => $value['emp_id'],
                //     );
                //     db::table($this->hr_db . ".training_schedule_dtls")->insert($datx);
                // }
                $form['uid'] = Auth::user()->Employee_id;
                db::table($this->hr_db . ".training_schedule")
                    ->where("id", $id)
                    ->delete();
                db::table($this->hr_db . ".training_schedule_dtls")
                    ->where("cert_id", $id)
                    ->delete();

                foreach ($formx as $key => $value) {
                    unset($form['id']);
                    $form['ref_no'] = $this->getRefDIrect($value['date_from']);
                    $form['date_to'] = $value['date_from'];
                    db::table($this->hr_db . ".training_schedule")->insert($form);
                    $id = DB::getPdo()->LastInsertId();
                    $datx = array(
                        'cert_id' => $id,
                        'emp_id' => $value['emp_id'],
                    );
                    db::table($this->hr_db . ".training_schedule_dtls")->insert($datx);
                }
            } else {
                $form['uid'] = Auth::user()->Employee_id;
                foreach ($formx as $key => $value) {
                    $form['ref_no'] = $this->getRefDIrect($value['date_from']);
                    $form['date_from'] = $value['date_from'];
                    $form['date_to'] = $value['date_from'];
                    db::table($this->hr_db . ".training_schedule")->insert($form);
                    $id = DB::getPdo()->LastInsertId();
                    $datx = array(
                        'cert_id' => $id,
                        'emp_id' => $value['emp_id'],
                    );
                    db::table($this->hr_db . ".training_schedule_dtls")->insert($datx);
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(new JsonResponse(['Message' => $th, 'status' => 'error']));
            //throw $th;
        }
    }
    public function cancel($id)
    {
        db::table($this->hr_db . '.training_schedule')
            ->where('id', $id)
            ->update(['status' => 'Cancelled']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
}
