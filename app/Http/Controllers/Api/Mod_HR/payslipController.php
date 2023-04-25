<?php

namespace App\Http\Controllers\Api\Mod_HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Validator;


class payslipController extends Controller
{
  private $lgu_db;
  private $hr_db;
  private $trk_db;
  private $empid;
  protected $G;

  public function __construct(GlobalController $global)
  {
    $this->middleware('auth');
    $this->G = $global;
    $this->lgu_db = $this->G->getLGUDb();
    $this->hr_db = $this->G->getHRDb();
    $this->trk_db = $this->G->getTrkDb();
    // $this->eagles_db = $this->G->geteaglesDb();
  }
  public function getPaySlip(Request $request)
  {
    try {
      $data = db::select("call " . $this->hr_db . ".showEmpPaySlip(?)",[Auth::user()->Employee_id]);

      $array = array();
      foreach ($data as $key => $value) {
        $dtlx = db::table($this->hr_db . ".tbl_payroll_contributions")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_contributions.gen_id')
          ->join($this->hr_db . ".tbl_premium_setup", 'tbl_premium_setup.id', 'tbl_payroll_contributions.premium_id')
          ->where("tbl_payroll_contributions.gen_id", $value->gen_id)
          ->where("tbl_payroll_contributions.amount", '!=', 0)
          ->get();
        $dtlDeds = db::table($this->hr_db . ".tbl_payroll_ca_loans")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_ca_loans.gen_id')
          ->join($this->hr_db . ".otherdeductions", 'otherdeductions.OD_ID', 'tbl_payroll_ca_loans.deduction_id')
          ->where("tbl_payroll_ca_loans.gen_id", $value->gen_id)
          ->where("tbl_payroll_ca_loans.amount", '!=', 0)
          ->get();
          $dtlDeds2 = db::table($this->hr_db . ".tbl_payroll_other_deductions")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_other_deductions.gen_id')
          ->join($this->hr_db . ".otherdeductions", 'otherdeductions.OD_ID', 'tbl_payroll_other_deductions.deduction_id')
          ->where("tbl_payroll_other_deductions.gen_id", $value->gen_id)
          ->where("tbl_payroll_other_deductions.amount", '!=', 0)
          ->get();

          $deductions = array();
          foreach ($dtlDeds as $key => $valueDtls) {
            $datax = array(
                'OD_Name'=>$valueDtls->OD_Name,
                'amount'=>$valueDtls->amount,
            );
            array_push($deductions,$datax);
          }
          foreach ($dtlDeds2 as $key => $valueDtls2) {
            $datax = array(
              'OD_Name'=>$valueDtls2->OD_Name,
              'amount'=>$valueDtls2->amount,
          );
          array_push($deductions,$datax);
          }
        $datax = array(
          'gen_id' => $value->gen_id,
          'payroll_covered' => $value->payroll_covered,
          'empNo' => $value->empNo,
          'Name_Empl' => $value->Name_Empl,
          'Name_Dept' => $value->Name_Dept,
          'gross_pay' => $value->gross_pay,
          'total_deductions' => $value->total_deductions,
           'total_allowance' => $value->total_allowance,
          'net_pay' => $value->net_pay,
          'details' => $dtlx,
          'otherDeds' => $deductions,
          'allowance' => db::table($this->hr_db . ".tbl_payroll_allowances")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_allowances.gen_id')
          ->join($this->hr_db . ".tbl_alowance_setup", 'tbl_alowance_setup.ID', 'tbl_payroll_allowances.allowance_id')
          ->where("tbl_payroll_allowances.gen_id", $value->gen_id)
          ->where("tbl_payroll_allowances.amount", '!=', 0)
          ->get()
        );
        // $datax['dtls'] = $dtlx;
        array_push($array, $datax);
      }
      return response()->json(new jsonresponse($array));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }

  public function getAllPaySlip($_empID)

  {
    // $_empID = $request->empID;

    try {
      $data = db::select("call " . $this->hr_db . ".showAllEmpPaySlip(?)",[$_empID]);

      $array = array();
      foreach ($data as $key => $value) {
        $dtlx = db::table($this->hr_db . ".tbl_payroll_contributions")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_contributions.gen_id')
          ->join($this->hr_db . ".tbl_premium_setup", 'tbl_premium_setup.id', 'tbl_payroll_contributions.premium_id')
          ->where("tbl_payroll_contributions.gen_id", $value->gen_id)
          ->where("tbl_payroll_contributions.amount", '!=', 0)
          ->get();
        $dtlDeds = db::table($this->hr_db . ".tbl_payroll_ca_loans")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_ca_loans.gen_id')
          ->join($this->hr_db . ".otherdeductions", 'otherdeductions.OD_ID', 'tbl_payroll_ca_loans.deduction_id')
          ->where("tbl_payroll_ca_loans.gen_id", $value->gen_id)
          ->where("tbl_payroll_ca_loans.amount", '!=', 0)
          ->get();
          $dtlDeds2 = db::table($this->hr_db . ".tbl_payroll_other_deductions")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_other_deductions.gen_id')
          ->join($this->hr_db . ".otherdeductions", 'otherdeductions.OD_ID', 'tbl_payroll_other_deductions.deduction_id')
          ->where("tbl_payroll_other_deductions.gen_id", $value->gen_id)
          ->where("tbl_payroll_other_deductions.amount", '!=', 0)
          ->get();

          $deductions = array();
          foreach ($dtlDeds as $key => $valueDtls) {
            $datax = array(
                'OD_Name'=>$valueDtls->OD_Name,
                'amount'=>$valueDtls->amount,
            );
            array_push($deductions,$datax);
          }
          foreach ($dtlDeds2 as $key => $valueDtls2) {
            $datax = array(
              'OD_Name'=>$valueDtls2->OD_Name,
              'amount'=>$valueDtls2->amount,
          );
          array_push($deductions,$datax);
          }
        $datax = array(
          'gen_id' => $value->gen_id,
          'payroll_covered' => $value->payroll_covered,
          'empNo' => $value->empNo,
          'Name_Empl' => $value->Name_Empl,
          'Name_Dept' => $value->Name_Dept,
          'gross_pay' => $value->gross_pay,
          'total_deductions' => $value->total_deductions,
           'total_allowance' => $value->total_allowance,
          'net_pay' => $value->net_pay,
          'details' => $dtlx,
          'otherDeds' => $deductions,
          'allowance' => db::table($this->hr_db . ".tbl_payroll_allowances")
          ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_allowances.gen_id')
          ->join($this->hr_db . ".tbl_alowance_setup", 'tbl_alowance_setup.ID', 'tbl_payroll_allowances.allowance_id')
          ->where("tbl_payroll_allowances.gen_id", $value->gen_id)
          ->where("tbl_payroll_allowances.amount", '!=', 0)
          ->get()
        );
        // $datax['dtls'] = $dtlx;
        array_push($array, $datax);
      }
      log::debug($array);
      return response()->json(new jsonresponse($array));
    } catch (\Exception $e) {
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function getEmployee(){
    $list = DB::select("SELECT `SysPK_Empl` as 'PPID',`Name_Empl` as 'NAME',`Status_Empl` 
    FROM (SELECT DISTINCT(`emp_id`) AS 'emp_id' FROM ".$this->hr_db.".tbl_payroll_general)A
    INNER JOIN ".$this->hr_db.".employees ON(employees.`SysPK_Empl` = A.emp_id)");
    return response()->json(new JsonResponse($list));
  }
  public function printpayslip(Request $request)
  {
      try{
        $form = $request->itm;
        $gen_id = $form['gen_id'];
        $data = db::select("call " . $this->hr_db . ".printEmpPaySlip(?)", [$gen_id]);
        $mainData = "";
        $allowance =  $request->itm['allowance'];
        $allowanceHTML="";



        foreach ($data as $key => $value) {
            $mainData = $value;
        }
        $contrib = db::table($this->hr_db . ".tbl_payroll_contributions")
        ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_contributions.gen_id')
        ->join($this->hr_db . ".tbl_premium_setup", 'tbl_premium_setup.id', 'tbl_payroll_contributions.premium_id')
        ->where("tbl_payroll_contributions.gen_id", $form['gen_id'])
        ->where("tbl_payroll_contributions.amount", '!=', 0)
        ->get();
        $contribData = "";
        foreach ($contrib as $key => $value) {
          $contribData .=' <tr>
              <td width="50%">'.$value->description.'</td>
              <td width="50%" align="right">'. number_format($value->amount, 2).'</td>

          </tr>';
      }

        $otherDeds = db::table($this->hr_db . ".tbl_payroll_ca_loans")
        ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_ca_loans.gen_id')
        ->leftJoin($this->hr_db . ".otherdeductions", 'otherdeductions.OD_ID', 'tbl_payroll_ca_loans.deduction_id')
        ->where("tbl_payroll_ca_loans.gen_id", $form['gen_id'])
        ->where("tbl_payroll_ca_loans.amount", '!=', 0)
        ->get();

        $otherDeds2 = db::table($this->hr_db . ".tbl_payroll_other_deductions")
        ->leftJoin($this->hr_db . ".tbl_payroll_general", 'tbl_payroll_general.id', 'tbl_payroll_other_deductions.gen_id')
        ->leftJoin($this->hr_db . ".otherdeductions", 'otherdeductions.OD_ID', 'tbl_payroll_other_deductions.deduction_id')
        ->where("tbl_payroll_other_deductions.gen_id", $form['gen_id'])
        ->where("tbl_payroll_other_deductions.amount", '!=', 0)
        ->get();
        $otherData = "";

        $totalGross = $mainData->gross_pay;
        foreach ( $allowance as $key => $valueA) {
            $totalGross += $valueA['amount'];
            $allowanceHTML .='<tr>
            <td width="50%">'.$valueA['ALLOWANCE_NAME'].'</td>
            <td width="50%" align="right">' . number_format($valueA['amount'],2) . '</td>
          </tr>';
        }
        foreach ($otherDeds as $key => $value) {
          $otherData .=' <tr>
              <td width="50%">'.$value->OD_Name.'</td>
              <td width="50%" align="right">'. number_format($value->amount, 2).'</td>

          </tr>';
      }
      foreach ($otherDeds2 as $key => $value) {
        $otherData .=' <tr>
            <td width="50%">'.$value->OD_Name.'</td>
            <td width="50%" align="right">'. number_format($value->amount, 2).'</td>

        </tr>';
    }

          $Template = '<table width="100%" cellpadding="2">
              <tr>
                  <td width="100%" align="center"><b>EMPLOYEE\'S PAYSLIP</b></td>
              </tr>
              <tr>
                  <td width="100%" align="center"><b>City of Naga</b></td>
              </tr>
              <br/>
              <tr>
                  <td width="15%">Employee No :</td>
                  <td width="45%">'.$mainData->empNo.'</td>
                  <td width="15%">Date :</td>
                  <td width="25%">'.date_format(date_create($this->G->serverdatetime()), "m/d/Y h:i A").'</td>
              </tr>
              <br/>
              <tr>
                  <td width="15%">Employee Name :</td>
                  <td width="45%">'.$mainData->Name_Empl.'</td>
                  <td width="15%">Payroll Period :</td>
                  <td width="25%">' . (!empty($mainData->payroll_from) ? (date_format(date_create($mainData->payroll_from), "m/d/Y")) : "") . ' to ' . (!empty($mainData->payroll_to) ? (date_format(date_create($mainData->payroll_to), "m/d/Y")) : "") . '</td>
              </tr>
              <br/>
              <tr>
                  <td width="15%">Department :</td>
                  <td width="85%">' . $mainData->Name_Dept . '</td>
              </tr>
              <br/>
              <tr>
                  <td width="30%" style="font-size:11pt; border-bottom:1px solid black; border-top:1px solid black;
                  border-left:1px solid black; border-right:1px solid black;" align="center">
                  Earnings</td>

                  <td width="70%" style="font-size:11pt; border-bottom:1px solid black; border-top:1px solid black;
                  border-left:1px solid black; border-right:1px solid black;" align="center">
                  Deductions</td>
             </tr>
             <tr>
                  <td width="30%" style="border-right:1px solid black">
                  <table>
                      <tr>
                          <td width="50%">Basic</td>
                          <td width="50%" align="right">' . number_format($mainData->gross_pay,2) . '</td>
                      </tr>
                      '.$allowanceHTML.'
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
                          <td width="100%"></td>
                      </tr>
                      <tr>
                          <td width="100%"></td>
                      </tr>
                  </table>
              </td>

                  <td width="35%">
                      <table>
                          '.$contribData.'

                      </table>
                  </td>

                  <td width="35%">
                  <table>
                      '.$otherData.'

                  </table>
              </td>
             </tr>

             <tr>
              <td width="20%" style="font-size:10pt; border-left:1px solid black; border-bottom:1px solid black;
              border-top:1px solid black;">Total Earnings :</td>

              <td width="15%" style="font-size:10pt; border-bottom:1px solid black;
              border-top:1px solid black;">' . number_format($totalGross, 2) . '</td>

              <td width="25%" style="font-size:10pt; border-bottom:1px solid black;
              border-top:1px solid black;">Total Deductions :</td>

              <td width="15%" style="font-size:10pt; border-bottom:1px solid black;
              border-top:1px solid black;">' . number_format($mainData->total_deductions, 2) . '</td>

              <td width="15%" style="font-size:10pt; border-bottom:1px solid black;
              border-top:1px solid black;">Net Pay :</td>

              <td width="10%" style="font-size:10pt; border-bottom:1px solid black;
              border-top:1px solid black; border-right:1px solid black;">' . number_format($mainData->net_pay, 2) . '</td>
         </tr>
          </table>';
          PDF::SetTitle('EMPLOYEE\'S PAYSLIP');
          PDF::SetFont('helvetica', '', 9);
          PDF::AddPage('P',);

          // PDF::AddPage('P');
          PDF::writeHTML($Template, true, 0, true, 0);
          PDF::Output(public_path() . '/print.pdf', 'F');
          $full_path = public_path() . '/print.pdf';
          if (\File::exists(public_path() . '/print.pdf')) {
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
