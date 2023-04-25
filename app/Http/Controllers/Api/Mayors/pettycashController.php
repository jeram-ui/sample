<?php

namespace App\Http\Controllers\Api\Mayors;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Storage;
use File;
use PDF;
use Illuminate\Support\Facades\Log;
use ZipArchive;
class pettycashController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    private $general;
    protected $G;


    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->signatory = $this->G->signatoryReport();
        $this->LGUName = $this->G->LGUName();
        $this->mayors_db = $this->G->getMayorsDb();
    }
    public function getEmpRequest($id)
    {

        $list = DB::table($this->hr_db.'.employee_information')
            ->where('DEPID', $id)
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
    public function show()
    {
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
            ->join($this->hr_db . '.department', 'department.SysPK_Dept', $this->mayors_db . '.pettycashvoucher_main.dept_id')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID', $this->mayors_db . '.pettycashvoucher_main.req_by')
            ->select('*',
                        db::raw('SysPK_Dept', 'Name_Dept'),
                        db::raw('PPID', 'NAME')
                    ,'department.SysPK_Dept'
                    ,'pettycashvoucher_main.status as PettyStatus'
                    , 'pettycashvoucher_main.id')
            // ->where('tbl_overtime.status', 'Active')
            ->where("pettycashvoucher_main.status", "!=", "CANCELLED")
            ->where('req_by', Auth::user()->Employee_id)

            // ->where('emp_id',Auth::user()->Employee_id)
            ->get();
        // $list="";
        return response()->json(new JsonResponse($list));
    }
    public function Edit($id)
    {
        $data['office'] = db::table($this->mayors_db . '.pettycashvoucher_main')->where('id', $id)->get();
        $data['Empname'] = db::table($this->mayors_db . '.pettycashvoucher_dtls')
            ->where('petty_id', $id)
            ->get();

        // $data['formz'] =db::table($this->hr_db .'.sworn_assets')->where('mainID', $id)->get();


        return response()->json(new JsonResponse($data));
    }

    public function store(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;


        $id = $form['id'];
        if ($id > 0) {
            db::table($this->mayors_db . ".pettycashvoucher_main")
                ->where('id', $id)
                ->update($form);

            // db::table($this->hr_db .".tbl_overtime_cert")
            // ->where('cert_id' ,$id)
            // ->update(['status' => 'Approved']);


            db::table($this->mayors_db . ".pettycashvoucher_dtls")
                ->where("petty_id", $id)
                ->delete();

            // db::table($this->hr_db . '.tbl_overtime_cert')
            // ->where('id', $id)
            // ->update(['status' => '0']);

            foreach ($formx as $key => $value) {
                $datx = array(
                    'petty_id' => $id,
                    'particular' => $value['particular'],
                    'amount' => $value['amount'],

                );
                db::table($this->mayors_db . ".pettycashvoucher_dtls")->insert($datx);
            }
        } else {
            db::table($this->mayors_db . ".pettycashvoucher_main")->insert($form);
            $id = DB::getPdo()->LastInsertId();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'petty_id' => $id,
                    'particular' => $value['particular'],
                    'amount' => $value['amount'],

                );
                db::table($this->mayors_db . ".pettycashvoucher_dtls")->insert($datx);
            }
        }
    }
    public function getRef(Request $request)
    {
      $query = DB::select("SELECT CONCAT(LPAD(COUNT(*)+1,4,0),'-',DATE_FORMAT(NOW(),'%Y'))as 'NOS' FROM " . $this->mayors_db . ".pettycashvoucher_main");
      return response()->json(new JsonResponse(['data' => $query]));
    }
    public function cancel($id)
    {
        db::table($this->mayors_db . '.pettycashvoucher_main')
            ->where('id', $id)
            ->update(['status' => 'CANCELLED']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function pettyCashHeadList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
            ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
            ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
            // ->where("Head_Dept", Auth::user()->Employee_id)
            ->where('pettycashvoucher_main.status', $stat)
            // ->orWhere('ir_head', Auth::user()->Employee_id)
            ->Where(function ($query) {
                $query->Where('department.Head_Dept', Auth::user()->Employee_id);
                    // ->orWhere('AssistantHead_Dept', Auth::user()->Employee_id);
            })
            ->get();

              $petty = array() ;
         foreach ($list as $key => $value) {
            $Cash = array(
                'id'=> $value->id,
                'Name_Dept'=> $value->Name_Dept,
                'resp_center'=> $value->resp_center,
                'ref_no'=> $value->ref_no,
                'App_date'=> $value->App_date,

                'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                ->where('petty_id', $value->id)
                ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                 as Total"))
                ->get(),



                'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                ->select(
                    'particular',
                    db::raw("format(`amount`, 2) as Amount"),

                )
                ->where('petty_id',$value->id)->get()

            );
            array_push($petty,$Cash);
         }
        return response()->json(new JsonResponse($petty));
    }

    public function PettyCashHeadListApproved(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
        ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
        ->where("app_by", Auth::user()->Employee_id)
            ->orderBy("pettycashvoucher_main.approved_date", "desc")
            ->limit(100)
            ->get();

            $petty = array() ;
            foreach ($list as $key => $value) {
               $Cash = array(
                   'id'=> $value->id,
                   'Name_Dept'=> $value->Name_Dept,
                   'resp_center'=> $value->resp_center,
                   'ref_no'=> $value->ref_no,
                   'App_date'=> $value->App_date,

                    'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                ->where('petty_id', $value->id)
                ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                 as Total"))
                ->get(),

                   'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                   ->select('particular',
                   db::raw("format(`amount`, 2) as amount"))
                   ->where('id',$value->id)->get()

               );
               array_push($petty,$Cash);
            }
           return response()->json(new JsonResponse($petty));

    }

    public function PettyCashHeadApprovalApproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
                ->where("id", $value['id'])
                ->update(['status' => 'Head Approved', 'App_by_status' => 'Approved', 'app_by' => Auth::user()->Employee_id, 'approved_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function PettyCashHeadApprovalDisapproved(Request $request)
    {
        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
            ->where("id", $value['id'])
            ->update(['status' => 'DISAPPROVED', 'App_by_status' => 'DISAPPROVED', 'app_by' => Auth::user()->Employee_id, 'approved_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function PettyDisbursingApproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
        ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
        // ->whereNull('dept_app_by')
            ->where('pettycashvoucher_main.status', $stat)
            ->get();

            $petty = array() ;
            foreach ($list as $key => $value) {
               $Cash = array(
                   'id'=> $value->id,
                   'Name_Dept'=> $value->Name_Dept,
                   'resp_center'=> $value->resp_center,
                   'ref_no'=> $value->ref_no,
                   'App_date'=> $value->App_date,

                   'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                   ->where('petty_id', $value->id)
                   ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                    as Total"))
                   ->get(),

                   'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                   ->select('particular',
                   db::raw("format(`amount`, 2) as amount"))

                   ->where('id',$value->id)->get()

               );
               array_push($petty,$Cash);
            }
           return response()->json(new JsonResponse($petty));
    }
    public function PettyCashDisbursingApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
        ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
            ->where("Disbursing_by", Auth::user()->Employee_id)
            ->orderBy("Disbursing_date", "desc")
            ->limit(100)
            ->get();

            $petty = array() ;
            foreach ($list as $key => $value) {
               $Cash = array(
                   'id'=> $value->id,
                   'Name_Dept'=> $value->Name_Dept,
                   'resp_center'=> $value->resp_center,
                   'ref_no'=> $value->ref_no,
                   'App_date'=> $value->App_date,

                   'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                   ->where('petty_id', $value->id)
                   ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                    as Total"))
                   ->get(),

                   'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                   ->select('particular',
                   db::raw("format(`amount`, 2) as amount"))

                   ->where('id',$value->id)->get()

               );
               array_push($petty,$Cash);
            }
           return response()->json(new JsonResponse($petty));

    }

    public function PettyCashDisbursingApproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
                ->where("id", $value['id'])
                ->update(['status' => 'Disbursed', 'Disbursing_by_status' => 'Approved', 'Disbursing_by' => Auth::user()->Employee_id, 'Disbursing_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function PettyCashDisbursingDisapproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
            ->where("id", $value['id'])
            ->update(['status' => 'disapproved', 'Disbursing_by_status' => Auth::user()->Employee_id, 'Disbursing_by_status' => 'disapproved',  'Disbursing_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function PettyCashAppropriationApproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
        ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
        // ->whereNull('dept_app_by')
            ->where('pettycashvoucher_main.status', $stat)
            ->get();

            $petty = array() ;
            foreach ($list as $key => $value) {
               $Cash = array(
                   'id'=> $value->id,
                   'Name_Dept'=> $value->Name_Dept,
                   'resp_center'=> $value->resp_center,
                   'ref_no'=> $value->ref_no,
                   'App_date'=> $value->App_date,

                   'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                   ->where('petty_id', $value->id)
                   ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                    as Total"))
                   ->get(),


                   'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                   ->select('particular',
                   db::raw("format(`amount`, 2) as amount"))

                   ->where('petty_id',$value->id)->get()

               );
               array_push($petty,$Cash);
            }
           return response()->json(new JsonResponse($petty));
    }
    public function PettyCashAppropriationApproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
                ->where("id", $value['id'])
                ->update(['status' => 'Appropriated', 'Approp_by_status' => 'Approved', 'Approp_by' => Auth::user()->Employee_id, 'Approp_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function PettyCashAppropriationApprovedList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
        ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
            ->where("Approp_by", Auth::user()->Employee_id)
            ->orderBy("Approp_date", "desc")
            ->limit(100)
            ->get();

            $petty = array() ;
            foreach ($list as $key => $value) {
               $Cash = array(
                   'id'=> $value->id,
                   'Name_Dept'=> $value->Name_Dept,
                   'resp_center'=> $value->resp_center,
                   'ref_no'=> $value->ref_no,
                   'App_date'=> $value->App_date,

                   'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                   ->where('petty_id', $value->id)
                   ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                    as Total"))
                   ->get(),

                   'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                   ->select('particular',
                   db::raw("format(`amount`, 2) as amount"))

                   ->where('petty_id',$value->id)->get()

               );
               array_push($petty,$Cash);
            }
           return response()->json(new JsonResponse($petty));
    }
    public function PettyCashAppropriationDisapproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
            ->where("id", $value['id'])
            ->update(['status' => 'disapproved', 'Approp_by_status' => Auth::user()->Employee_id, 'Approp_by_status' => 'disapproved',  'Approp_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function pettyCashallotmentApproval(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
        ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
        // ->whereNull('dept_app_by')
            ->where('pettycashvoucher_main.status', $stat)
            ->get();

            $petty = array() ;
            foreach ($list as $key => $value) {
               $Cash = array(
                   'id'=> $value->id,
                   'Name_Dept'=> $value->Name_Dept,
                   'resp_center'=> $value->resp_center,
                   'ref_no'=> $value->ref_no,
                   'App_date'=> $value->App_date,

                   'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                   ->where('petty_id', $value->id)
                   ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                    as Total"))
                   ->get(),

                   'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                   ->select(
                    'particular',
                    db::raw("format(`amount`, 2) as amount"))

                   ->where('petty_id',$value->id)->get()

               );
               array_push($petty,$Cash);
            }
           return response()->json(new JsonResponse($petty));
    }
    public function pettyCashallotmentApprovalList(Request $request)
    {
        $stat = $request->status;
        $list = DB::table($this->mayors_db . '.pettycashvoucher_main')
        ->join($this->hr_db . ".department", 'department.SysPK_Dept', 'pettycashvoucher_main.dept_id')
        ->join($this->hr_db . '.employee_information', 'employee_information.PPID','pettycashvoucher_main.req_by')
            ->where("allot_by", Auth::user()->Employee_id)
            ->orderBy("allot_date", "desc")
            ->limit(100)
            ->get();

            $petty = array() ;
            foreach ($list as $key => $value) {
               $Cash = array(
                   'id'=> $value->id,
                   'Name_Dept'=> $value->Name_Dept,
                   'resp_center'=> $value->resp_center,
                   'ref_no'=> $value->ref_no,
                   'App_date'=> $value->App_date,

                   'total' => DB::table($this->mayors_db . '.pettycashvoucher_dtls')
                   ->where('petty_id', $value->id)
                   ->select(db::raw("format(sum(pettycashvoucher_dtls.`amount`), 2)
                    as Total"))
                   ->get(),

                   'dtls'=>db::table($this->mayors_db . ".pettycashvoucher_dtls")
                   ->select('particular',

                   db::raw("format(`amount`, 2) as amount"))

                   ->where('petty_id',$value->id)->get()

               );
               array_push($petty,$Cash);
            }
           return response()->json(new JsonResponse($petty));
    }
    public function PettyCashAllotmentApproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
                ->where("id", $value['id'])
                ->update(['status' => 'Approved', 'allot_by_status' => 'Approved', 'allot_by' => Auth::user()->Employee_id, 'allot_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function PettyCashAllotmentDisapproved(Request $request)
    {

        $list =  $request->list;
        foreach ($list as $key => $value) {
            db::table($this->mayors_db . '.pettycashvoucher_main')
            ->where("id", $value['id'])
            ->update(['status' => 'disapproved', 'allot_by_status' => Auth::user()->Employee_id, 'allot_by_status' => 'disapproved',  'allot_date' => $this->G->serverdatetime()]);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function printPettyCash(Request $request)
    {
        try {
            $form = $request->itm;
            $id = $form['id'];

            $emp = db::table($this->mayors_db .'.pettycashvoucher_main')
            ->join($this->hr_db.'.employee_information','employee_information.PPID','pettycashvoucher_main.req_by')
            // ->select("*", db::raw("strtoupper(employee_information.NAME) as NAME"))
            ->where('id', $form['id'] )
            ->get();
            $empData ="";

            foreach ($emp as $key => $value) {
                $empData= $value;
            }

            $particulars = db::table($this->mayors_db . '.pettycashvoucher_dtls')
            ->where('petty_id', $form['id'] )
            ->get();

            $partData ="";
            foreach ($particulars as $key => $value) {
                    $partData .='
                    <tr>
                        <td width="70%">'.$value->particular.'</td>
                        <td width="30%" align="right">'.number_format($value->amount, 2).'</td>
                    </tr>' ;
                }
                    if(count($particulars)< 7){
                        for($i = count($particulars); $i<7; $i++){
                            $partData .='  <tr>
                            <td width="70%"></td>
                            <td width="30%"></td>
                        </tr>' ;
                        }
                    }

                    $datarow = db::select("call " . $this->mayors_db . ".pettcashApproved(?)", [$id]);
                    $row = [];

                    foreach ($datarow as $key => $value) {
                      $row = $value;


                    //   log::debug($row->{'disburseSig'});
                    //   log::debug($row->{'appSig'});
                    //   log::debug($row->{'FundSig'});
                    //   log::debug($row->{'ApprvdSig'});

                    }


            $Template = '<table border="1" cellspadding="1">
                <tr>
                    <td width="60%">
                        <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                                 border-right:1px solid black; ">
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="100%" style="font-size:12pt" align="center"><b>PETTY CASH VOUCHER</b></td>
                            </tr>
                            <tr>
                                <td width="100%" align="center"><b>Naga, Cebu</b></td>
                            </tr>
                            <tr>
                                <td width="100%" align="center"><b>Lgu</b></td>
                            </tr>
                        </table>
                    </td>

                    <td width="40%">
                    <table width="100%" style=" border-top:1px solid black;
                                             border-right:1px solid black; ">
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="20%">No.:</td>
                            <td width="70%" style="border-bottom:1px solid black">'.$empData->ref_no.'</td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td width="20%">Date:</td>
                            <td width="70%" style="border-bottom:1px solid black">'.$empData->App_date.'</td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td width="100%" align="center"></td>
                        </tr>
                    </table>
                </td>
                </tr>

                <tr>
                <td width="60%">
                    <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                             border-right:1px solid black; ">

                        <tr>
                            <td width="30%">Payee/Office:</td>
                            <td width="70%" >'.strtoupper($empData->NAME).'</td>
                        </tr>
                        <tr>
                            <td width="30%">Address:</td>
                            <td width="70%" >'.$empData->dept_name.'</td>
                        </tr>
                    </table>
                </td>

                <td width="40%">
                <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                         border-right:1px solid black; ">
                    <tr>
                        <td width="100%">Responsibility Center:</td>
                    </tr>
                       <tr>
                        <td width="100%" align="center">'.$empData->resp_center.'</td>
                    </tr>

                </table>
            </td>
            </tr>

            <tr>
                <td width="50%">
                    <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                             border-right:1px solid black; ">

                        <tr>
                            <td width="100%"><b><i>I. To be filled up upon request</i></b></td>
                        </tr>

                    </table>
                </td>

                <td width="50%">
                <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                         border-right:1px solid black; ">
                        <tr>
                            <td width="100%"><b><i>II. To be filled up upon liquidation</i></b></td>
                        </tr>



                </table>
            </td>
            </tr>

            <tr>
            <td width="50%">
                <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                         border-right:1px solid black; ">

                    <tr>
                        <td width="70%" style="border-bottom:1px solid black" align="center">Particulars</td>
                        <td width="30%" style="border-bottom:1px solid black" align="center">Amount</td>
                    </tr>
                    '.$partData.'

                </table>
            </td>

            <td width="50%">
            <table width="100%" style=" border-top:1px solid black;
                                     border-right:1px solid black; ">
                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <tr>
                        <td width="50%">Total Amount Granted</td>
                        <td width="50%" style="border-bottom:1px solid black"></td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>

                    <tr>
                        <td width="50%">Total Amount Pair Per</td>
                        <td width="50%" style="border-bottom:1px solid black"></td>
                    </tr>
                    <tr>
                        <td width="15%">OR No.</td>
                        <td width="85%" style="border-bottom:1px solid black"></td>
                    </tr>

                    <tr>
                        <td width="100%">Amount Received/</td>
                    </tr>
                    <tr>
                        <td width="100%">Reimbursement</td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>
            </table>
        </td>
        </tr>

        <tr>
            <td width="50%">
                <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                         border-right:1px solid black; ">

                    <tr>
                        <td width="10%" style="border-bottom:1px solid black; border-left:1px solid black;
                             border-right:1px solid black" align="center">A</td>

                        <td width="2%"></td>
                        <td width="88%"><i>Requested by:</i></td>
                    </tr>

                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <tr>
                        <td width="100%" align="center"><b>'.strtoupper($empData->NAME).'</b></td>
                    </tr>
                    <tr>
                        <td width="20%"></td>
                        <td width="60%" style="border-bottom:1px solid black" align="center">'.$empData->POSITION.'</td>
                        <td width="20%"></td>

                    </tr>

                </table>
            </td>

            <td width="50%">
            <table width="100%" style=" border-top:1px solid black;
                                     border-right:1px solid black; ">
                        <tr>
                            <td width="10%" style="border-bottom:1px solid black; border-left:1px solid black;
                                border-right:1px solid black" align="center">C</td>
                            <td width="90%"></td>
                        </tr>

                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="25%"></td>
                            <td width="75%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">Received Refund</td>
                        </tr>
                        <tr>
                            <td width="25%"></td>
                            <td width="75%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">Reimbursement Paid</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
            </table>
        </td>
        </tr>
        <tr>
        <td width="50%">
            <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                     border-right:1px solid black; ">

                <tr>
                    <td width="100%">Approved:</td>
                </tr>

                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="100%"></td>
                </tr>
                <tr>
                    <td width="20%"></td>
                    <td width="30%" style="border-bottom:1px solid black" align="center"><b>' . $row->{'Approved By'} . '</b></td>
                    <td width="30%" style="border-bottom:1px solid black" align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'approvedSig'}. '"></td>
                    <td width="20%"></td>

                </tr>
                <tr>
                    <td width="100%" style="border-bottom:1px solid black" align="center">' . $row->{'approvedPos'} . '</td>
                </tr>
                <tr>
                    <td width="100%" align="center">Immediate Supervisor</td>
                </tr>

            </table>
        </td>

            <td width="50%">
            <table width="100%" style=" border-top:1px solid black;
                    border-right:1px solid black; ">
            <tr>
                <td width="100%"></td>
            </tr>
            <tr>
                <td width="100%"></td>
            </tr>

            <tr>
                <td width="100%"></td>
            </tr>
            <tr>
                <td width="50%"align="center"><b>' . $row->{'disburse By'} . '</b></td>
                <td width="50%"  align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'disburseSig'}. '"></td>

            </tr>
            <tr>
                <td width="100%" style="border-bottom:1px solid black" align="center"></td>
            </tr>
            <tr>
                <td width="100%" align="center">Disbursing Officer</td>
            </tr>

            </table>
        </td>
        </tr>

        <tr>
            <td width="50%">
                <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                         border-right:1px solid black; ">

                    <tr>
                        <td width="10%" style="border-bottom:1px solid black; border-left:1px solid black;
                             border-right:1px solid black" align="center">B</td>

                        <td width="2%"></td>
                        <td width="88%"><i>Paid by:</i></td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>

                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <tr>
                        <td width="100%" align="center"><b>ANTONITA SASAN</b></td>
                    </tr>
                    <tr>
                        <td width="100%" style="border-bottom:1px solid black" align="center"><i>Disbursing Officer</i></td>
                    </tr>
                    <tr>
                        <td width="100%">CASH RECEIVED BY:</td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>
                    <tr>
                        <td width="20%"></td>
                        <td width="60%" style="border-bottom:1px solid black; text-transform: uppercase" align="center">'.strtoupper($empData->NAME).'</td>
                        <td width="20%"></td>
                    </tr>
                    <tr>
                        <td width="100%" align="center">Signature over Printed Name of Payee</td>
                    </tr>

                    <tr>
                        <td width="100%"></td>
                    </tr>

                    <tr>
                        <td width="20%"></td>
                        <td width="15%">Date:</td>
                        <td width="45%" style="border-bo ttom:1px solid black"></td>
                        <td width="20%"></td>
                    </tr>
                    <tr>
                        <td width="100%"></td>
                    </tr>

                </table>
            </td>

            <td width="50%">
            <table width="100%" style=" border-top:1px solid black;
                                     border-right:1px solid black; ">
                        <tr>
                            <td width="10%" style="border-bottom:1px solid black; border-left:1px solid black;
                                border-right:1px solid black" align="center">D</td>
                            <td width="90%"></td>
                        </tr>

                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="25%"></td>
                            <td width="75%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">Liquidation Submitted</td>
                        </tr>
                        <tr>
                            <td width="25%"></td>
                            <td width="75%" style="font-size:9pt"><input type="checkbox" checked="" name="1" value="1">Reimbursement Paid</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="20%"></td>
                            <td width="60%" style="border-bottom:1px solid black" align="center"></td>
                            <td width="20%"></td>
                        </tr>
                        <tr>
                            <td width="100%" align="center">Signature of Payee</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="20%"></td>
                            <td width="15%">Date:</td>
                            <td width="45%" style="border-bottom:1px solid black"></td>
                            <td width="20%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>

                        </table>
                        </td>
                        </tr>



                        <tr>
                        <td width="50%">
                            <table width="100%" style="border-bottom:1px solid black; border-top:1px solid black;
                                                     border-right:1px solid black; ">

                                <tr>
                                    <td width="100%" style="border-bottom:1px solid black; "><i>Ok as to Appropriation:</i></td>

                                </tr>

                                <tr>
                                    <td width="100%"></td>
                                </tr>
                                <tr>
                                    <td width="100%"></td>
                                </tr>
                                <tr>
                                    <td width="50%" align="center"><b>' . $row->{'App By'} . '</b></td>
                                    <td width="50%"  align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'appSig'}. '"></td>
                                </tr>
                                <tr>
                                    <td width="100%" align="center"><i>OIC-Budget Officer</i></td>
                                </tr>

                            </table>
                        </td>

                        <td width="50%">
                        <table width="100%" style=" border-top:1px solid black;
                                border-right:1px solid black; ">

                            <tr>
                                <td width="100%"><i>Ok as to Allotment:</i></td>

                            </tr>

                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="100%"></td>
                            </tr>
                            <tr>
                                <td width="50%" align="center"><b>' . $row->{'allot By'} . '</b></td>
                                <td width="50%"  align="center"><img style="border: -5px" height="40px" width="75px" src="' . public_path() . $row->{'allotSig'}. '"></td>

                            </tr>
                            <tr>
                                <td width="100%" align="center"><i>City Accountant</i></td>
                            </tr>
            </table>
        </td>
        </tr>
            </table>
            ';

            PDF::SetTitle('Petty Cash Voucher');
            PDF::SetFont('helvetica', '', 9);
            PDF::AddPage('P');

            // PDF::Image(public_path() . $row->{'ReqSig'}, 40, 70+$height, 27, 27, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image(public_path() . $row->{'RecSig'}, 40, 100+$height, 27, 27, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image(public_path() . $row->{'appSig'}, 150, 70+$height, 25, 25, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image(public_path() . $row->{'FundSig'}, 150, 100+$height, 40, 30, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            // PDF::Image(public_path() . $row->{'ApprvdSig'}, 90, 130+$height, 40, 30, 'PNG', 'http://www.tcpdf.org', '', false, 300);
            PDF::writeHTML($Template, true, 0, true, 0);
            PDF::Output(public_path() . '/prints.pdf', 'F');

            $full_path = public_path() . '/prints.pdf';
            if (\File::exists(public_path() . '/prints.pdf')) {
                $file = \File::get($full_path);
                $type = \File::mimeType($full_path);
                $response = \Response::make($file, 200);
                $response->header("Content-Type", $type);
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }


}
