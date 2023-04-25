<?php

namespace App\Http\Controllers\Api\BicyclePermit;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;

use PDF;

class bicycleController extends Controller
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
    }
    public function getApplicantType()
    {
      $list = DB::select('Call '.$this->lgu_db.'.profile_applicant_type_zoe()');
      return response()->json(new JsonResponse($list));
     
    }
    public function getRequirements()
    {
      $list = DB::select('Call '.$this->lgu_db.'.Bicycle_requirements_zoe()');
      return response()->json(new JsonResponse($list));
    }
    public function bicyclelistdisplay( Request $request)
    {
      $dateFrom = $request['from'] ;    
      $dateTo = $request['to'];
      $_formname = $request['formtype'];   

      $list = DB::select('call ' . $this->lgu_db . '.spl_display_PROFILE3_zoe(?,?,?)', array($dateFrom,$dateTo,$_formname));
      return response()->json(new JsonResponse($list));
    }
    public function ref(Request $request)
    {
        $pre = 'MBP';
        $table = $this->lgu_db . ".ebplo_tbl_profile";
        $date = $request->date;
        $refDate = 'appdate';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    // CRUD 
  public function store(Request $request)
  {   
      try { 
        DB::beginTransaction();     
        $mainData = $request->main;
        $cedulaData = $request->cedula;  
        $requirements = $request->requirements;  
        $unit = $request->unit;  
        $route = $request->route;  
        $cc=$request->copyfurnish;
        $reference=$request->reference;

        $idx=$mainData['pkid'];
          if ($idx > 0) {
              unset($mainData['applicantName']);
              unset($mainData['grantedto']); 
              $this->update($idx,$mainData,$cedulaData,$requirements,$unit,$route,$reference,$cc);
          }else { 
              $this->save($mainData,$cedulaData,$requirements,$unit,$route,$reference,$cc);
          }; 
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!','errormsg'=>$e,'status'=>'error']));
        }
  }
  public function save($mainData,$cedulaData,$requirements,$unit,$route,$reference,$cc)
  {  
    db::table($this->lgu_db.'.ebplo_tbl_profile')->insert($mainData);
    $id = DB::getPDo()->lastInsertId(); 
    $this->save_details($id,$mainData,$cedulaData,$requirements,$unit,$route,$reference,$cc);
  } 
  public function save_details($id,$mainData,$cedulaData,$requirements,$unit,$route,$reference,$cc)
  {
      //Billing
      $fees= DB::select('Call '.$this->lgu_db.'.ebplo_display_accounts_jho(138)');
      $cntlimit = $mainData['noofcounts'];
      $incomeamount = 0;
      $incomecode = '';
      $incomedesc = '';
      $incomeID = 0; 
  
      foreach($fees as $row){  
        if($row->Type_ === 'FIXED'){
          $billing = array(
            'ref_id'=>$id,
            'bill_id'=>$id,
            'payer_type'=>$mainData['apptype'],
            'transaction_type'=>"Bicycle Permit",
            'bill_number'=>$mainData['appno'],
            'payer_id'=> ($mainData['appbusinessno'] === null) ? 0 : $mainData['appbusinessno'],
            'business_application_id' ($mainData['appbusinessno'] === null) ? 0 : $mainData['appbusinessno'],
            'account_code'=> $row->income_account_code,
            'bill_description'=> $row->income_account_description,
            'bill_month'=>$mainData['appdate'],
            'bill_amount'=>$row->base_amount
          ); 
          DB::table( $this->lgu_db.'.cto_general_billing')->insert($billing);
          $id = DB::getPDo()->lastInsertId();
  
          $profile_bill = array(
            'mainid' => $id,
            "accountid" => $row->id,
            "accountcode" => $row->income_account_code,
            "feeamount" => $row->base_amount
          );
          DB::table( $this->lgu_db.'.ebplo_tbl_profile_fees')->insert($profile_bill);
        }else{ 
          $DTRange = DB::select('SELECT * FROM '.$this->lgu_db.'.cto_income_account_list WHERE income_account_code = '.$row->income_account_code);
        
          if($row->Type_ === 'PER COUNT'){
            $incomeamount = $row->base_amount * $cntlimit;
            $incomecode = $row->income_account_code;
            $incomedesc = $row->income_account_description;
            $incomeID = $row->id;
          }else{
            foreach($DTRange as $y){ 
              if($cntlimit >= $y->minimum_range && $cntlimit <= $y->minimum_range){
                $incomeamount = $y->range_amount * $cntlimit;
                $incomecode = $y->income_account_code;
                $incomedesc = $y->income_account_description;
                $incomeID = $y->id; 
              } 
            }
          } 
          if(count($DTRange) > 0 || strtoupper($row->Type_) === "PER COUNT"){
            $fee = array(
              'bill_number' => $mainData['appno'],
              'payer_type' => $mainData['apptype'],
              'payer_id' => ($mainData['appbusinessno'] === null) ? 0 : $mainData['appbusinessno'],
              'business_application_id' => ($mainData['appbusinessno'] === null) ? 0 : $mainData['appbusinessno'],
              'account_code' => $row->income_account_code,
              'bill_description' => $row->income_account_description,
              'bill_amount' => $row->base_amount,
              'bill_month' => $mainData['appdate'],
              'transaction_type' => "Bicycle Permit",
              'ref_id' => $id,
              'bill_id' => $id
            );
            DB::table( $this->lgu_db.'.cto_general_billing')->insert($fee);
  
            $profile_bill = array(
              'mainid' => $id,
              "accountid" => $row->id,
              "accountcode" => $row->income_account_code,
              "feeamount" => $row->base_amount
            );
            DB::table( $this->lgu_db.'.ebplo_tbl_profile_fees')->insert($profile_bill);
            
          }
        }
      }
  
      $cedula_insert = array(
        "mainid" => $id,
        "ctcid" => ($cedulaData['ctcid'] === null) ? 0 : $cedulaData['ctcid'],
        "ctcno" => ($cedulaData['ctcno'] === null) ? "" : $cedulaData['ctcno'], 
        "ctcdate" => $cedulaData['ctcdate']
      ); 
      DB::table( $this->lgu_db.'.ebplo_tbl_profile_cedula')->insert($cedula_insert);
   
      $reference_insert = array(
        "mainid" => $id,
        "type" => ($reference[0]['type'] === null) ? 0 : $reference[0]['type'],
        "number" => ($reference[0]['number'] === null) ? "" : $reference[0]['number'], 
        "date" => $reference[0]['date'],
        "year" => $reference[0]['year']
      ); 
      DB::table( $this->lgu_db.'.ebplo_tbl_profile_reference')->insert($reference_insert);
       
      $cc_insert = array(
        "mainid" => $id,
        "copyfurnish" => ($cc[0]['cc'] === null) ? 0 : $cc[0]['cc'],
      ); 
      DB::table( $this->lgu_db.'.ebplo_tbl_profile_copyfurnish')->insert($cc_insert);
       
      foreach($requirements as $row){
        $require = array(
          "mainid" => $id,
          "reqid" => $row['id'],
          "requirements" => $row['requirement']
        );
        DB::table( $this->lgu_db.'.ebplo_tbl_profile_requirements')->insert($require);
      } 
  
      foreach($unit as $row){
        $Unitdetail = array(
          "mainid" => $id,
          "make_brand" => $row['make_brand'],
          "engine_motors" => $row['engine_motors'],
          "chasis" => $row['chasis'],
          "serial" => $row['serial'], 
          "plate_reg" => $row['plate_reg'],
          "name_file" => $row['name_file'],
          "body_case" => $row['body_case'],
          "status_color" => $row['status_color'],
          "lenght" => $row['lenght'],
          "width" => $row['width'],
          "depth" => $row['depth'],
          "capacity_wt" => $row['capacity_wt']
        ); 
        DB::table( $this->lgu_db.'.ebplo_tbl_profile_unitdetails')->insert($Unitdetail);
      } 
       
      foreach($route as $row){
        $route = array(
          "mainid" => $id,
          "fromid" => $row['fromid'],
          "from" => $row['from'],
          "toid" => $row['toid'],
          "to_" => $row['to_'],
          "remarks" => $row['remarks']
        );
        DB::table( $this->lgu_db.'.ebplo_tbl_profile_route')->insert($route);
      } 
  }
  public function edit(Request $request,$id)
  { 

      $data['req'] = DB::select('Select * from '.$this->lgu_db.'.ebplo_tbl_profile_requirements where mainid = '.$id);  
      $data['main'] =DB::select('Call '.$this->lgu_db.'.ebplo_tbl_profile_zoe('.$id.')');
      $data['cedula'] = DB::table($this->lgu_db.'.ebplo_tbl_profile_cedula')->where('mainid',$id)->get();
      $data['unit'] = DB::table($this->lgu_db.'.ebplo_tbl_profile_unitdetails')->where('mainid',$id)->get();
      $data['route'] = DB::table($this->lgu_db.'.ebplo_tbl_profile_route')->where('mainid',$id)->get();
     return response()->json(new JsonResponse($data));
  }
  public function update($idx,$mainData,$cedulaData,$requirements,$unit,$route,$reference,$cc)
  { 
      DB::table($this->lgu_db.'.ebplo_tbl_profile') ->where('pkid',$idx)->update($mainData);
      DB::delete('DELETE FROM '.$this->lgu_db.'.ebplo_tbl_profile_reference WHERE mainid = ?',[$idx]);
      DB::delete('DELETE FROM '.$this->lgu_db.'.ebplo_tbl_profile_copyfurnish WHERE mainid = ?',[$idx]);
      DB::delete('DELETE FROM '.$this->lgu_db.'.ebplo_tbl_profile_requirements WHERE mainid = ?',[$idx]);
      DB::delete('DELETE FROM '.$this->lgu_db.'.ebplo_tbl_profile_cedula WHERE mainid = ?',[$idx]);
      DB::delete('DELETE FROM '.$this->lgu_db.'.ebplo_tbl_profile_unitdetails WHERE mainid = ?',[$idx]);
      DB::delete('DELETE FROM '.$this->lgu_db.'.ebplo_tbl_profile_route WHERE mainid = ?',[$idx]);
      DB::delete('DELETE FROM '.$this->lgu_db.'.cto_general_billing WHERE transaction_type = ? and ref_id = ? and bill_id = ?',['Bicycle Permit',$idx,$idx]);

      $this->save_details($idx,$mainData,$cedulaData,$requirements,$unit,$route,$reference,$cc);

      return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
  }
    public function delete(Request $request)
    {  
        $id=$request->id;
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db.'.ebplo_tbl_profile')->where('pkid', $id) ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
 // PRINT REPORTS CONTROLLER

 public function printBicyclemasterlist(Request $request)
 {
   
   $logo = config('variable.logo');
   try {
     $main=$request->main;
     
       PDF::SetFont('Helvetica', '', '8');
       $html_content = '
             ' . $logo . ' 
       <h3 align="center">BICYCLE PERMIT MASTER LIST</h3>
       <table>
       <tr>
       <th style="text-align:center;">As of '.$request->reportcaption.'</th>
       </tr>
       </table>
       <br></br>
       <br></br>
       <table style="padding:2px;width:100%;">
       <thead>
         <tr>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:3%;"><br><br><b>NO</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>TRANS NO</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>TRANS DATE</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>OPERATOR</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>BARANGAY</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>NO. OF UNITS</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>DRIVER</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>PURPOSE</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>VALIDITY</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>DATE ISSUED</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>TOTAL FEES</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:5%;"><br><br><b>OR NO</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:5%;"><br><br><b>OR DATE</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>PAYMENT STATUS</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;"><br><br><b>STATUS</b><br></th>
          
         </tr>
      </thead>
      <tbody >'; 
      $ctr = 1; 
     foreach($main as $row){                           
     $html_content .='
     <tr style="padding:2px;width:100%;">
     <td style="border:0.5px solid black;text-align:center;width:3%;">' .$ctr. '</td>
     <td style="border:0.5px solid black;text-align:center;">' . $row['appno'] . '</td>
     <td style="border:0.5px solid black;text-align:center;">' . $row['appdate'] . '</td>
     <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['applicantName'] . '</td>
     <td style="border:0.5px solid black;text-align:left;">' . $row['brgy'] . '</td>
     <td style="border:0.5px solid black;text-align:center;">' . $row['units'] . '</td>    
     <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['grantedto'] . '</td>
     <td style="border:0.5px solid black;text-align:left;">' . $row['purpose'] . '</td>
     <td style="border:0.5px solid black;text-align:center;">' . $row['valid'] . '</td>   
     <td style="border:0.5px solid black;text-align:center;">' . $row['issue_date'] . '</td>   
     <td style="border:0.5px solid black;text-align:right;">' . $row['PermitFee'] . '</td> 
     <td style="border:0.5px solid black;text-align:center;width:5%;">' . $row['ORNo'] . '</td> 
     <td style="border:0.5px solid black;text-align:center;width:5%;">' . $row['ORDate'] . '</td>
     <td style="border:0.5px solid black;text-align:center;">' . $row['PaymentStatus'] . '</td>  
     <td style="border:0.5px solid black;text-align:center;">' . $row['status'] . '</td>                    
     </tr>';
     $ctr++;
     }
     $ctr = $ctr - 1;

     $html_content .='<tr style="padding:2px;">
     <th colspan="2" style="border:0.5px solid black;text-align:right;height:20px;"><b>TOTAL RECORDS</b></th>  
     <th colspan="13"style="border:0.5px solid black;text-align:left;height:20px;"><b>'.$ctr.'</b></th>  
     </tr>';
     $html_content .='</tbody>
     </table>
     ';                       
     PDF::SetTitle('Bicycle Permit Master List');
     PDF::AddPage('L',array(250,350));
     PDF::writeHTML($html_content, true, true, true, true, '');
     PDF::Output(public_path() . '/prints.pdf', 'F');
     return response()->json(new JsonResponse(['status' => 'success']));
   } catch (\Exception $e) {
     return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
   }
 }
 public function printsummarycount(Request $request)
 {
  $summary = DB::select('Call '.$this->lgu_db.'.bicycle_summarycount_zoe()');
  $signatory = $this->signatory;
      foreach($signatory as $row){ 
        
          $admin =   $row->{'admin_name'};
          $position =   $row->{'admin_pos'};
      }
  $logo = config('variable.logo');
   try {
       PDF::SetFont('Helvetica', '', '10');
       $html_content = '
             ' . $logo . ' 
       <h3 align="center">TOTAL REGISTERED PER BARANGAY</h3>
       <table>
       <tr>
       <th style="text-align:center;">As of '.$request->reportcaption.'</th>
       </tr>
       </table>
       <br></br>
       <br></br>
       <table style="padding:2px;width:100%;">
       <thead>
         <tr>
          <th style="width:17%;"></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;height:20px;"><b>BARANGAY</b></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;height:20px;"><b>COUNT</b></th> 
         </tr>';
         foreach($summary as $row){    
          $html_content .= '<tr>
          <th style="width:17%;"></th>
          <th style="border:0.5px solid black;text-align:left;">'.$row->brgy.'</th>
          <th style="border:0.5px solid black;text-align:center;">'.$row->cnt.'</th> 
          </tr>';
         }  
    $html_content .= '</thead> </table> <br><br><br>';
    $html_content .=  '<table style="padding:2px;width:100%;">
    <tr>
    <th style="width:17%;"></th>
    <th>Prepared By:</th>
    <th style="width:10%;"></th>
    <th>Noted By:</th> 
    </tr>
    <br>
    <tr>
    <th style="width:17%;"></th>
    <th style="border-bottom:0.5px solid black;text-align:center;"><h4>'.Auth::user()->id.'</h4></th>
    <th style="width:15%;"></th>
    <th style="border-bottom:0.5px solid black;text-align:center;"><h4>'.strtoupper($admin).'</h4></th> 
    </tr>
    <tr>
    <th style="width:17%;"></th>
    <th style="text-align:center;">Admin Staff</th>
    <th style="width:15%;"></th>
    <th style="text-align:center;">'.$position.'</th> 
    </tr>
    </table>
    ';   
     PDF::SetTitle('Bicycle Summary Count');
     PDF::AddPage();
     PDF::writeHTML($html_content, true, true, true, true, '');
     PDF::Output(public_path() . '/prints.pdf', 'F');
     return response()->json(new JsonResponse(['status' => 'success']));
   } catch (\Exception $e) {
     return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
   }
 }
 public function printBicyclePermit(Request $request)
 {
   $dtls = DB::select('Select * from '.$this->lgu_db.'.ebplo_tbl_profile_unitdetails where mainid = '.$request->pkid);  
      $signatory = $this->signatory;
          foreach($signatory as $row){  
              $mayor =   $row->{'mayor_name'};
              $position =   $row->{'mayor_pos'};
          }
      $LGU= $this->LGUName;
      foreach($LGU as $row){  
        $name =   $row->{'LGU Name'};
        $type =   $row->{'LGU Type'};
    }
    $logo = config('variable.logo');
     try {
       PDF::SetFont('Helvetica', '', '10');
       $html_content = '
             ' . $logo . ' 
       <h3 align="center">OFFICE OF THE MAYOR</h3>
       <h3 align="center">MAYOR`S BICYCLE PERMIT(MBP)</h3>
       <br><br><br>
       <table>
       <tr>
       <th style="width:20%;">Applicant / Operator</th>
       <th style="width:3%;align:left;">:</th>
       <th style="width:30%;"><b>'.$request['applicantName'].'</b></th>
       </tr>
       <tr>
       <th style="width:20%;">Address</th>
       <th style="width:3%;align:left;">:</th>
       <th style="width:30%;"><b>'.$request['appaddress'].'</b></th>
       </tr>
       <tr>
       <th style="width:20%;">No. of Unit(s)</th>
       <th style="width:3%;align:left;">:</th>
       <th style="width:30%;"><b>'.$request['units'].'</b></th>
       </tr>
       <tr>
       <th style="width:20%;">Route</th>
       <th style="width:3%;align:left;">:</th>
       <th style="width:30%;"><b>'.$request['route'].'</b></th>
       </tr>
       <br>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">Applicant/Operator is hereby authorized to operate the following Bicycle units:</th>
       </tr>
       </table>
       <br></br>
       <br></br>
       ';
       $html_content .= '<table>
       <tr>
       <th style="width:5%;"></th>
       <th style="width:20%;border:0.5px solid black;text-align:center;background-color:#dedcdc;line-height:15px;"><b>MAKE</b></th>
       <th style="width:15%;border:0.5px solid black;text-align:center;background-color:#dedcdc;line-height:15px;"><b>MOTOR NO.</b></th>
       <th style="width:15%;border:0.5px solid black;text-align:center;background-color:#dedcdc;line-height:15px;"><b>CHASSIS NO.</b></th>
       <th style="width:20%;border:0.5px solid black;text-align:center;background-color:#dedcdc;line-height:15px;"><b>PLATE NO.</b></th>
       <th style="width:15%;border:0.5px solid black;text-align:center;background-color:#dedcdc;line-height:15px;"><b>BODY NO.</b></th>
       <td style="width:5%;"></td>
       
       </tr>';
       foreach($dtls as $row){  
       $html_content .= '
       <tr>
       <td style="width:5%;"></td>
       <td style="width:20%;border:0.5px solid black;text-align:center;">'.$row->make_brand.'</td>
       <td style="width:15%;border:0.5px solid black;text-align:center;">'.$row->engine_motors.'</td>
       <td style="width:15%;border:0.5px solid black;text-align:center;">'.$row->chasis.'</td>
       <td style="width:20%;border:0.5px solid black;text-align:center;">'.$row->plate_reg.'</td>
       <td style="width:15%;border:0.5px solid black;text-align:center;">'.$row->body_case.'</td>
       <td style="width:5%;"></td>
       </tr>';
      }
       $html_content .= '
       </table>
       <br></br>
       <br></br>
       <table>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">Subject to the following conditions:</th>
       </tr> 
       <br>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;"><span>1. Applicant/Operator shall be a resident and a registered voter of '.$name.';</span></th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">2. Applicant/Operator shall submit to this Office current LTO registration of the above unit/s before the</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">issuance of this Permit, among other requirements required by law;</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">3. Applicant/Operator shall employ or contract only with duly licensed registered drivers who are resident</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">and registered voters of the '.$type.';</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">4. Applicant/Operator shall only ply on the route assigned in this Permit;</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">5. Applicant/Operator shall pay in full the required Permit fee;</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">6. Applicant/Operator shall protect this document as its loss or destruction may affect his legal right to</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">operate the service;</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">7. Applicant/Operator/Driver shall strictly observe and comply with the policies, ordinance, rules and</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;"><span>regulations of the '.$type.' Government of '.$name.', and all other applicable laws of the</span></th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">Republic of the Philippines; and</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">8. Any violation of the conditions set forth herein shall be sufficient cause for the automatic revocation of</th>
       </tr>
       <tr>
       <th  style="width:5%;"></th>
       <th style="width:95%;">this Permit.</th>
       </tr>
       <br><br>
       <tr>
       <th style="width:5%;"></th>
       <th style="width:35%;"><span>Valid until <b>December 31,'.date("Y").'</b></span></th>
       </tr>
       <br>
       <tr>
       <th style="width:5%;"></th>
       <th style="width:95%;"><span>Issued on <b>'.date("F j,Y", strtotime($request['issue_date'])).'</b> at '.$name.', Philippines.</span></th>
       </tr>
       </table>
       <br><br><br>
       <table style="width:100%;padding:2px;">
       <tr>
       <th style="width:60%;"></th>
       <th style="width:30%;border-bottom:0.5px solid black;text-align:center;"><h4>'.strtoupper($mayor).'</h4></th>
       </tr>
       <tr>
       <th style="width:60%;"></th>
       <th style="width:30%;text-align:center;"><i>'.$position.'</i></th>
       </tr>
       </table>
       <br><br><br>
       <table style="width:100%;padding:2px;">
       <tr>
       <th style="width:5%"></th>
           <th style="width:20%">OR No</th>
           <th style="width:2%">:</th> 
           <th style="border-bottom:1px solid black;width:25%">'.$request['ORNo'].'</th>
       </tr>
       <tr>
           <th style="width:5%"></th>
           <th style="width:20%">Amount Paid</th>
           <th style="width:2%">:</th> 
           <th style="border-bottom:1px solid black;width:25%">'.$request['PermitFee'].'</th>
       </tr>
       <tr>
           <th style="width:5%"></th>
           <th style="width:20%">Date Issued</th>
           <th style="width:2%">:</th> 
           <th style="border-bottom:1px solid black;width:25%">'.$request['issue_date'].'</th>
       </tr>
       <tr>
           <th style="width:5%"></th>
           <th style="width:20%">Issued At</th>
           <th style="width:2%">:</th> 
           <th style="border-bottom:1px solid black;width:28%">'.$name.'</th>
       </tr>
       </table>
    ';   
    PDF::SetTitle('Bicycle Permit');
    PDF::AddPage('P','Legal');
    PDF::writeHTML($html_content, true, true, true, true, '');
    PDF::Output(public_path() . '/prints.pdf', 'F');
    return response()->json(new JsonResponse(['status' => 'success']));
  } catch (\Exception $e) {
    return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
  }
 }
   }
  