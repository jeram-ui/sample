<?php

namespace App\Http\Controllers\Api\OBOPermit;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;

use PDF;

class architecturalController extends Controller
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
    public function getPermitStatus()
    {
    $status =  DB::select('call ' . $this->lgu_db . '.permit_Status_zoe()');
  

    return response()->json(new JsonResponse($status));
    }
    public function getOccupancy()
    {
      $occupancy = DB::select('Call '.$this->lgu_db.'.eceo_occupancy_zoe()');
      return response()->json(new JsonResponse($occupancy));
    }
    public function architecturalList( Request $request)
    {
      $dateFrom = $request['from'] ;    
      $dateTo = $request['to'];
      $issuancetype = $request['issuancetype'];   
     
      $list = DB::select('call ' . $this->lgu_db . '.architectural_permit_display_zoe(?,?,?)', array($dateFrom,$dateTo,$issuancetype));

      return response()->json(new JsonResponse($list));
    }
    public function ref(Request $request)
    {
        $pre = 'ARP-';
        $table = $this->lgu_db . ".eceo_application";
        $date = $request->date;
        $refDate = 'application_save_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    // CRUD 
    public function delete(Request $request)
    {  
        $id=$request->id;        
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db.'.eceo_application')->where('application_no', $id) ->update($data);
        $reason['Form_name'] ='Architectural Permit';       
        $reason['Trans_ID'] =$id;       
        $reason['Type_'] ='Cancel Record';       
        $reason['Trans_by'] =Auth::user()->id;       
        $this->G->insertReason($reason);  
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }   

 // PRINT REPORTS CONTROLLER
 public function issuance(Request $request)
 {  
  
  $id=$request->id;

  $ref['permit_code'] = $request->permit_code;
  $ref['bldg_code'] = $request->bldg_code;
  DB::table($this->lgu_db.'.eceo_application')->where('application_no', $id) ->update($ref);

  return response()->json(new JsonResponse(['Message' => 'Issued Successfully.', 'status' => 'success']));
 }
 public function issuanceRef(Request $request)
{
        $pre = 'REF-';
        $table = $this->lgu_db . ".eceo_application";
        $date = $request->date;
        $refDate = 'application_save_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
}
 public function printArchitecturalPermitList(Request $request)
 {
   
   $logo = config('variable.logo');
   try {
     $main=$request->main;
     
       PDF::SetFont('Helvetica', '', '8');
       $html_content = '
             ' . $logo . ' 
       <h3 align="center">MASTER LIST FOR ISSUANCE OF ARCHITECTURAL PERMITS</h3>
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
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>APPLICATION NO</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>APPLICATION DATE</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:17%;"><br><br><b>PROJECT NAME</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>PROJECT LOCATION</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>APPLICANT NAME</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>TCT NUMBER</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>ISSUED DATE</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;%;"><br><br><b>OR NO</b><br></th>
          <th style="border:0.5px solid black;text-align:center;background-color:#dedcdc;width:10%;"><br><br><b>PAYMENT STATUS</b><br></th>
          
         </tr>
      </thead>
      <tbody >'; 
      $ctr = 1; 
     foreach($main as $row){                           
     $html_content .='
     <tr style="padding:2px;width:100%;">
     <td style="border:0.5px solid black;text-align:center;width:3%;">' .$ctr. '</td>
     <td style="border:0.5px solid black;text-align:center;width:10%;">' . $row['ApplicationNo'] . '</td>
     <td style="border:0.5px solid black;text-align:center;width:10%;">' . $row['TransDate'] . '</td>
     <td style="border:0.5px solid black;text-align:left;width:17%;">' . $row['ProjectName'] . '</td>
     <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['ProjectLocation'] . '</td>
     <td style="border:0.5px solid black;text-align:center;width:10%;">' . $row['ApplicantName'] . '</td>    
     <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['TCTNo'] . '</td>
     <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['IssuedDate'] . '</td>
     <td style="border:0.5px solid black;text-align:center;width:10%;%;">' . $row['ORNo'] . '</td>   
     <td style="border:0.5px solid black;text-align:center;width:10%;">' . $row['PaymentStatus'] . '</td>                     
     </tr>';
     $ctr++;
     }
     $ctr = $ctr - 1;

     $html_content .='<tr style="padding:2px;">
     <th colspan="2" style="border:0.5px solid black;text-align:right;height:20px;"><b>TOTAL RECORDS</b></th>  
     <th colspan="8"style="border:0.5px solid black;text-align:left;height:20px;"><b>'.$ctr.'</b></th>  
     </tr>';
     $html_content .='</tbody>
     </table>
     ';                       
     PDF::SetTitle('Architectural Permit Master List');
     PDF::AddPage('L');
     PDF::writeHTML($html_content, true, true, true, true, '');
     PDF::Output(public_path() . '/prints.pdf', 'F');
     return response()->json(new JsonResponse(['status' => 'success']));
   } catch (\Exception $e) {
     return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
   }
 }
 public function printArchitecturalPermit(Request $request)
 {
  $signatory = $this->signatory;
      foreach($signatory as $row){ 
        
          $buildingOfficial =   $row->{'building_official_name'};
      }

  $idx = $request['permitNO'];   
  $owner = DB::select('call ' . $this->lgu_db . '.print_display_architectural_zoe(?)', array($idx));

  $appno = $request['appNo'];
  $architect = DB::select('call ' . $this->lgu_db . '.signatory_architectural_zoe(?)', array($appno));
  $engineer = DB::select('call ' . $this->lgu_db . '.signatory_architectural_zoe2(?)', array($appno));
  $buildingOwner = DB::select('call ' . $this->lgu_db . '.signatory_buildingOwner_zoe(?)', array($appno));
  $lotOwner = DB::select('call ' . $this->lgu_db . '.signatory_LotOwner_zoe(?)', array($appno));
  $BLDG =DB::select('call ' . $this->lgu_db . '.architectural_facilities_zoe(?)', array($appno));
  $FIRE =DB::select('call ' . $this->lgu_db . '.architectural_fireConformance_zoe(?)', array($appno));
  $SCOPE =DB::select('call ' . $this->lgu_db . '.architectural_scopeofwork_zoe(?)', array($appno));
  $percentage =DB::select('call ' . $this->lgu_db . '.architectural_percentage_zoe(?)', array($appno));
  $received=DB::select('call ' . $this->lgu_db . '.architectural_receive_zoe(?)', array($appno));
  $DOCS =DB::select('call ' . $this->lgu_db . '.architectural_bldg_documents_zoe(?)', array($appno));
  $PROCESS =DB::select('call ' . $this->lgu_db . '.architectural_app_process_zoe(?)', array($appno));

    $logo = config('variable.archLogo');
     try {
      PDF::SetFont('Helvetica', '', '8');
       $html_content = '
       <h4 align="left">NBC FORM NO. A - 02</h4>
      ' . $logo . ' 
       <h3 style="line-height:8px;"align="center">OFFICE OF THE BUILDING OFFICIAL</h3>
       <h2 style="font-family:Times New Roman;line-height:10px;" align="center">ARCHITECTURAL PERMIT</h2>
       <table>
       <tr >
       <th style="width:30%;text-align:center;"><b>APPLICATION PERMIT</b></th>
       <th style="width:5%;"></th>
       <th style="width:30%;text-align:center;"><b>AP NO</b></th>
       <th style="width:5%;"></th>
       <th style="width:30%;text-align:center;"><b>BUILDING PERMIT NO</b></th>
       </tr>
       <br>
       <tr style="line-height:15px;">
       <th style="width:30%;border:0.5px solid black;line-height:15px;text-align:center;">'.$owner[0]->ApplicationNo.'</th>
       <th style="width:5%;"></th>
       <th style="width:30%;border:0.5px solid black;line-height:15px;text-align:center;">'.$owner[0]->PermitNo.'</th>
       <th style="width:5%;"></th>
       <th style="width:30%;border:0.5px solid black;line-height:15px;text-align:center;">'.$owner[0]->bldg_permit_no.'</th>
       </tr>
       <tr style="line-height:15px;">
       <th style="width:100%;"><h4>BOX 1  (TO BE ACCOMPLISHED IN PRINT BY THE OWNER/APPLICANT)</h4></th>
       </tr>
       </table>
       <table>
       <tr>
       <th style="width:18%;border-top:0.5px solid black;border-left:0.5px solid black;"><b> OWNER/APPLICANT</b></th>
       <th style="width:30%;border-top:0.5px solid black"><b>LAST NAME</b></th>
       <th style="width:30%;border-top:0.5px solid black"><b>FIRST NAME</b></th>
       <th style="width:10%;border-top:0.5px solid black"><b>M.I.</b></th>
       <th style="width:12%;border-top:0.5px solid black;border-left:0.5px solid black;border-right:0.5px solid black;"><b> TIN</b></th>
       </tr>
       <tr>
       <th style="width:18%;border-left:0.5px solid black;"></th>
       <th style="width:30%;">'.$owner[0]->LastName.'</th>
       <th style="width:30%;">'.$owner[0]->Firstname.'</th>
       <th style="width:10%;">'.$owner[0]->MiddleName.'</th>
       <th style="width:12%;border-left:0.5px solid black;border-right:0.5px solid black;"> '.$owner[0]->TinNo.'</th>
       </tr>
       <tr>
       <th style="width:30%;border-top:0.5px solid black;border-left:0.5px solid black;border-right:0.5px solid black;"><b> FOR CONSTRUCTION OWNED</b></th>
       <th style="width:30%;border-top:0.5px solid black;border-right:0.5px solid black;"><b> FORM OF OWNERSHIP</b></th>
       <th style="width:40%;border-top:0.5px solid black;border-right:0.5px solid black;"><b> USE OR CHARACTER OF OCCUPANCY</b></th>
       </tr>
       <tr>
       <th style="width:30%;border-left:0.5px solid black;border-right:0.5px solid black;"><b> BY ENTERPRISE</b></th>
       <th style="width:30%;border-right:0.5px solid black;"> '.$owner[0]->BUSINESSTYPE.'</th>
       <th style="width:40%;border-right:0.5px solid black;"> '.$owner[0]->occupancy.'</th>
       </tr>

       <tr>
       <th style="width:10%;border-top:0.5px solid black;border-left:0.5px solid black;"><b> ADDRESS:</b></th>
       <th style="width:5%;border-top:0.5px solid black;"><b>NO</b></th>
       <th style="width:15%;border-top:0.5px solid black;"><b>STREET</b></th>
       <th style="width:20%;border-top:0.5px solid black;"><b>BARANGAY</b></th>
       <th style="width:20%;border-top:0.5px solid black;"><b>CITY/MUNICIPALITY</b></th>
       <th style="width:10%;border-top:0.5px solid black;"><b>ZIP CODE</b></th>
       <th style="text-align:center;width:20%;border-top:0.5px solid black;border-right:0.5px solid black;"><b>TELEPHONE NO</b></th>
       </tr>
       <tr>
       <th style="width:10%;border-left:0.5px solid black;"></th>
       <th style="width:5%;">'.$owner[0]->NO.'</th>
       <th style="width:15%;">'.$owner[0]->ST.'</th>
       <th style="width:20%;">'.$owner[0]->BRGY.'</th>
       <th style="width:20%;">'.$owner[0]->CITY_MUN.'</th>
       <th style="width:10%;">'.$owner[0]->ZIPCODE.'</th>
       <th style="text-align:center;width:20%;border-right:0.5px solid black;">'.$owner[0]->TELNO.'</th>
       </tr>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;"><b> LOCATION OF CONSTRUCTION:</b></th>
       </tr>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:7%;border-left:0.5px solid black;"><b> LOT NO.</b></th>
       <th style="width:10%;border-bottom:0.5px solid black;text-align:center;"> '.$owner[0]->LotNo.'</th>
       <th style="width:3%;"></th>
       <th style="width:7%;"><b>BLK NO.</b></th>
       <th style="width:10%;border-bottom:0.5px solid black;text-align:center;"> '.$owner[0]->BlkNo.'</th>
       <th style="width:3%;"></th>
       <th style="width:7%;"><b>TCT NO.</b></th>
       <th style="width:15%;border-bottom:0.5px solid black;text-align:center;"> '.$owner[0]->TCTNo.'</th>
       <th style="width:3%;"></th>
       <th style="width:10%;"><b>TAX DEC NO.</b></th>
       <th style="width:23%;border-bottom:0.5px solid black;text-align:center;"> '.$owner[0]->TDNo.'</th>
       <th style="width:2%;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
       <th style="width:7%;line-height:15px;border-left:0.5px solid black;"><b> STREET</b></th>
       <th style="width:13%;line-height:15px;border-bottom:0.5px solid black;"> '.$owner[0]->PropertySt.'</th>
       <th style="width:10%;line-height:15px;"><b>BARANGAY</b></th>
       <th style="width:17%;line-height:15px;border-bottom:0.5px solid black;"> '.$owner[0]->PropertyBrgy.'</th>
       <th style="width:15%;line-height:15px;"><b>CITY/MUNICIPALITY</b></th>
       <th style="width:36%;line-height:15px;border-bottom:0.5px solid black;"> '.$owner[0]->PropertyCity_Mun.'</th>
       <th style="width:2%;line-height:15px;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:100%;border-top:0.5px solid black;border-left:0.5px solid black;border-right:0.5px solid black;"><b>  SCOPE OF WORK</b></th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>  
     
       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[0]->Check.'</span></th>
       <th style="width:15%"> NEW CONSTRUCTION</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[1]->Check.'</span></th>
       <th style="width:10%;"> RENOVATION</th>
       <th style="width:20%;border-bottom:0.5px solid black;">'.$SCOPE[1]->Remarks.'</th>
       <th style="width:1%;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[2]->Check.'</span></th>
       <th style="width:25%;"> RAISING</th>
       <th style="width:20%;border-bottom:0.5px solid black;">'.$SCOPE[2]->Remarks.'</th>
       <th style="width:2%;border-right:0.5px solid black;"></th>
       </tr>
           
       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[3]->Check.'</span></th>
       <th style="width:15%"> ERECTION</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[4]->Check.'</span></th>
       <th style="width:10%;"> CONVERSION</th>
       <th style="width:20%;border-bottom:0.5px solid black;">'.$SCOPE[4]->Remarks.'</th>
       <th style="width:1%;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[5]->Check.'</span></th>
       <th style="width:25%;"> ACCESSORY BUILDING/STRUCTURE</th>
       <th style="width:20%;border-bottom:0.5px solid black;">'.$SCOPE[5]->Remarks.'</th>
       <th style="width:2%;border-right:0.5px solid black;"></th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[6]->Check.'</span></th>
       <th style="width:15%"> ADDITION</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[7]->Check.'</span></th>
       <th style="width:10%;"> REPAIR</th>
       <th style="width:20%;border-bottom:0.5px solid black;">'.$SCOPE[7]->Remarks.'</th>
       <th style="width:1%;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[8]->Check.'</span></th>
       <th style="width:25%;"> OTHERS (SPECIFY)</th>
       <th style="width:20%;border-bottom:0.5px solid black;">'.$SCOPE[8]->Remarks.'</th>
       <th style="width:2%;border-right:0.5px solid black;"></th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[9]->Check.'</span></th>
       <th style="width:15%"> ALTERATION</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$SCOPE[10]->Check.'</span></th>
       <th style="width:10%;"> MOVING</th>
       <th style="width:20%;border-bottom:0.5px solid black;">'.$SCOPE[9]->Remarks.'</th>
       <th style="width:1%;"></th>
       <th style="width:48%;border-bottom:0.5px solid black;">'.$SCOPE[10]->Remarks.'</th>
       <th style="width:1%;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>


       </table>

       <table>
       <tr>
       <th style="width:100%;border:0.5px solid black;line-height:10px;vertical-align:middle"><h4>BOX 2 (TO BE ACCOMPLISHED BY THE DESIGN PROFESSIONAL)</h4></th>
       </tr>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"><h4> 1.ARCHITECTURAL FACILITIES AND OTHER FEATURES PURSUANT TO BATAS PAMBANSA BILANG 344, REQUIRING CERTAIN</h4></th>
       </tr>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"><h4>   BUILDING, INSTITUTION, ESTABLISHMENT AND PUBLIC UTILITIES TO INSTALL FACILITIES AND OTHER DEVICES.</h4></th>
       </tr>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
      
       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[0]->EXIST.'</span></th>
       <th style="width:27%"> STAIRS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[1]->EXIST.'</span></th>
       <th style="width:19%"> WASH ROOMS AND TOILETS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[2]->EXIST.'</span></th>
       <th style="width:24%"> WITCHERS,CONTROLERS,BUZZERS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[3]->EXIST.'</span></th>
       <th style="width:21%;border-right:0.5px solid black;"> DRINKING FOUNTAINS</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[4]->EXIST.'</span></th>
       <th style="width:27%"> WALKWAYS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[5]->EXIST.'</span></th>
       <th style="width:19%"> LIFTS/ELEVATORS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[6]->EXIST.'</span></th>
       <th style="width:24%"> HANDRAILS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[7]->EXIST.'</span></th>
       <th style="width:21%;border-right:0.5px solid black;"> PUBLIC TELEPHONES</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[8]->EXIST.'</span></th>
       <th style="width:27%"> CORRIDORS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[9]->EXIST.'</span></th>
       <th style="width:19%"> RAMPS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[10]->EXIST.'</span></th>
       <th style="width:24%"> THRESHOLDS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[11]->EXIST.'</span></th>
       <th style="width:21%;border-right:0.5px solid black;"> SEATING ACCOMODATIONS</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:1%;border-left:0.5px solid black;"></th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[12]->EXIST.'</span></th>
       <th style="width:27%"> DOORS,ENTRANCES AND THRESHOLDS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[13]->EXIST.'</span></th>
       <th style="width:19%"> PARKING AREAS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[14]->EXIST.'</span></th>
       <th style="width:24%"> FLOOR FINISHES</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$BLDG[15]->EXIST.'</span></th>
       <th style="width:13%;"> OTHERS(SPECIFY)</th>
       <th style="width:7%;border-bottom:0.5px solid black;"></th>
       <th style="width:1%;border-right:0.5px solid black;"></th>
       </tr>
       
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       </table>

       <table>
       <tr>
       <th style="width:39%;border-top:0.5px solid black;border-left:0.5px solid black;line-height:15px;"><h4>  2. PERCENTAGE OF SITE OCCUPANCY</h4></th>
       <th style="width:61%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:15px;"><h4> 3. CONFORMANCE OF FIRE CODE OF THE PHILIPPINES (P.D. 1185)</h4></th>
       </tr>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:32%;border-left:0.5px solid black;">  PERCENTAGE OF BUILDING FOOTPRINT</th>
       <th style="width:3%;border-bottom:0.5px solid black;">'.$percentage[0]->measure_length.'</th>
       <th style="width:2%;">%</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[0]->EXIST.'</span></th>
       <th style="width:25%"> NUMBER AND WIDTH OF EXIT DOORS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[1]->EXIST.'</span></th>
       <th style="width:10%;border-right:0.5px solid black;"> FIRE WALLS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[2]->EXIST.'</span></th>
       <th style="width:13%;"> OTHERS(SPECIFY)</th>
       <th style="width:9%;border-bottom:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:32%;border-left:0.5px solid black;">  PERCENTAGE OF IMPERVOUS AREA</th>
       <th style="width:3%;border-bottom:0.5px solid black;">'.$percentage[0]->measure_area.'</th>
       <th style="width:2%;">%</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[3]->EXIST.'</span></th>
       <th style="width:25%"> WIDTH OF CORRIDORS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[4]->EXIST.'</span></th>
       <th style="width:34%;border-right:0.5px solid black;"> FIRE FIGHTING AND SAFETY FACILITIES</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:32%;border-left:0.5px solid black;">  PERCENTAGE OF UNPAVED SURFACE AREA</th>
       <th style="width:3%;border-bottom:0.5px solid black;">'.$percentage[0]->measure_height.'</th>
       <th style="width:2%;">%</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[5]->EXIST.'</span></th>
       <th style="width:25%"> DISTANCE TO FIRE EXITS</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[6]->EXIST.'</span></th>
       <th style="width:34%;border-right:0.5px solid black;"> SMOKE DETECTORS</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:32%;border-left:0.5px solid black;">  OTHERS (SPECIFY)</th>
       <th style="width:3%;border-bottom:0.5px solid black;">'.$percentage[0]->measure_others.'</th>
       <th style="width:2%;">%</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[7]->EXIST.'</span></th>
       <th style="width:25%"> ACCESS TO PUBLIC STREET</th>
       <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$FIRE[8]->EXIST.'</span></th>
       <th style="width:34%;border-right:0.5px solid black;"> EMERGENCY LIGHTS</th>
       </tr>
      
       </table>
       <table>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:50%;border-top:0.5px solid black;line-height:10px"><h4> BOX 3</h4></th>
       <th style="width:50%;border-top:0.5px solid black;line-height:10px"><h4> BOX 4</h4></th>
       </tr>
       <tr>
       <th style="width:50%;border:0.5px solid black;"><h4> DESIGN PROFESSIONAL, PLANS AND SPECIFICATIONS</h4></th>
       <th style="width:50%;border:0.5px solid black;"><h4> SUPERVISOR/IN-CHARGE OF CIVIL/STRUCTURAL  WORKS</h4></th>
       </tr>
       <tr>
       <th style="width:50%;border-left:0.5px solid black;"></th>
       <th style="width:50%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;border-bottom:0.5px solid black;text-align:center;line-height:20px;">'.strtoupper($architect[0]->person_name).'</th>
       <th style="width:5%;"></th>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;border-bottom:0.5px solid black;text-align:center;line-height:20px;">'.strtoupper($engineer[0]->person_name).'</th>
       <th style="width:5%;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;text-align:center;"><b>ARCHITECT</b></th>
       <th style="width:5%;"></th>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;text-align:center;"><b>CIVIL/STRUCTURAL ENGINEER</b></th>
       <th style="width:5%;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;text-align:center;">(Signed and Sealed Over Printed Name)</th>
       <th style="width:5%;"></th>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;text-align:center;">(Signed and Sealed Over Printed Name)</th>
       <th style="width:5%;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:15%;border-left:0.5px solid black;"></th>
       <th style="width:5%;text-align:right;">Date:</th>
       <th style="width:15%;border-bottom:0.5px solid black;text-align:center;">'.$architect[0]->sig_date.'</th>
       <th style="width:15%;"></th>

       <th style="width:15%;border-left:0.5px solid black;"></th>
       <th style="width:5%;text-align:right;">Date:</th>
       <th style="width:15%;border-bottom:0.5px solid black;text-align:center;">'.$engineer[0]->sig_date.'</th>
       <th style="width:15%;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:50%;border-left:0.5px solid black;"></th>
       <th style="width:50%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       <tr style="font-size:7pt;">
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Address:</th>
       <th style="width:40%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->st_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;"> Address:</th>
       <th style="width:40%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->st_no.'</th>
       </tr>
       <tr style="font-size:7pt;">
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  PRC No.:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_prc_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Validity:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_prc_date.'</th>  

       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;"> PRC No.:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_prc_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Validity:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_prc_date.'</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  IAPOA No.:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_add_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  OR No:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_prc_date.'</th>  

       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;"> IAPOA No.:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_add_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  OR No:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_add_date.'</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  PTR No.:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_ptr_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Place Issued:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_ptr_place.'</th>  

       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;"> PTR No.:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_ptr_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Place Issued:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_ptr_place.'</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Date Issued.:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_ptr_date.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Date Issued:</th>
       <th style="width:15%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_ptr_date.'</th>  

       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;"> Date Issued.:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_ptr_date.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Date Issued:</th>
       <th style="width:15%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_ptr_date.'</th>
       </tr>

       <tr style="font-size:7pt;">
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  TIN:</th>
       <th style="width:40%;border-top:0.5px solid black;line-height:10px;">'.$architect[0]->sig_tin_no.'</th>

       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;"> TIN:</th>
       <th style="width:40%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$engineer[0]->sig_tin_no.'</th>
       </tr>

       <tr>
       <th style="width:50%;border-top:0.5px solid black;line-height:10px"><h4> BOX 5</h4></th>
       <th style="width:50%;border-top:0.5px solid black;line-height:10px"><h4> BOX 6</h4></th>
       </tr>

       <tr>
       <th style="width:50%;border:0.5px solid black;"><h4> BUILDING OWNER</h4></th>
       <th style="width:50%;border:0.5px solid black;"><h4> WITH MY CONSENT : LOT OWNER</h4></th>
       </tr>

       <tr>
       <th style="width:50%;border-left:0.5px solid black;"></th>
       <th style="width:50%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;border-bottom:0.5px solid black;text-align:center;line-height:20px;"><b>'.strtoupper($buildingOwner[0]->person_name).'</b></th>
       <th style="width:5%;"></th>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;border-bottom:0.5px solid black;text-align:center;line-height:20px;"><b>'.strtoupper($lotOwner[0]->person_name).'</b></th>
       <th style="width:5%;border-right:0.5px solid black;"></th>
       </tr>
      
       <tr>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;text-align:center;">(Signature Over Printed Name)</th>
       <th style="width:5%;"></th>
       <th style="width:5%;border-left:0.5px solid black;"></th>
       <th style="width:40%;text-align:center;">(Signature Over Printed Name)</th>
       <th style="width:5%;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
       <th style="width:15%;border-left:0.5px solid black;"></th>
       <th style="width:5%;text-align:right;">Date:</th>
       <th style="width:15%;border-bottom:0.5px solid black;text-align:center;">'.$buildingOwner[0]->sig_date.'</th>
       <th style="width:15%;"></th>

       <th style="width:15%;border-left:0.5px solid black;"></th>
       <th style="width:5%;text-align:right;">Date:</th>
       <th style="width:15%;border-bottom:0.5px solid black;text-align:center;">'.$lotOwner[0]->sig_date.'</th>
       <th style="width:15%;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
       <th style="width:50%;border-left:0.5px solid black;"></th>
       <th style="width:50%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>

       </tr>

       <tr>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Address:</th>
       <th style="width:40%;border-top:0.5px solid black;line-height:10px;">'.$buildingOwner[0]->st_no.'</th>

       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;"> Address:</th>
       <th style="width:40%;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">'.$lotOwner[0]->st_no.'</th>
       </tr>

       <tr>
       <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  CTC No.:</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Date Issued:</th>
       <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Place Issued:</th>

       <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  CTC No.:</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;line-height:10px;">  Date Issued:</th>
       <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;line-height:10px;">  Place Issued:</th>
       </tr>

       <tr>
       <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;line-height:10px;">  '.$buildingOwner[0]->sig_ctc_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;line-height:10px;">  '.$buildingOwner[0]->sig_ctc_date.'</th>
       <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;line-height:10px;">  '.$buildingOwner[0]->sig_ctc_place.'</th>

       <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;line-height:10px;">  '.$lotOwner[0]->sig_ctc_no.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;line-height:10px;">  '.$lotOwner[0]->sig_ctc_date.'</th>
       <th style="width:25%;border:0.5px solid black;line-height:10px;">  '.$lotOwner[0]->sig_ctc_place.'</th>
       </tr>

       </table>
       
 ';   
    PDF::SetTitle('Architectural Permit');
    PDF::AddPage('P','Legal'); 
    PDF::writeHTML($html_content, true, true, true, true, '');
    
    $html_content='
    <table>
       <tr>
       <th style="width:100%;"><h4>TO BE ACCOMPLISHED BY THE PROCESSING AND EVALUATION DIVISION</h4></th>
       </tr>

       <tr>
       <th style="width:100%;line-height:10px"><h4> BOX 7</h4></th>
       </tr>

       <tr style="line-height:15px;">
       <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;"><b>  RECEIVED BY:</b></th>
       <th style="width:35%;border-top:0.5px solid black;">'.$received[0]->sig_name.'</th>
       <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;"><b>  DATE:</b></th>
       <th style="width:40%;border-top:0.5px solid black;border-right:0.5px solid black;">'.$received[0]->sig_date.'</th>
       </tr>

       <tr style="line-height:15px;">
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:center;"><h4>FIVE (5) SETS OF CIVIL/STRUCTURAL DOCUMENTS</h4></th>
       </tr>
    </table>

    <table>
       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>
       <tr>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[0]->EXIST.'</span></th>
        <th style="width:47%"> 1. VICINITY MAP/LOCATION PLANT WITHIN A TWO </th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[1]->EXIST.'</span></th>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> 8. DETAILS OF RAMPS, PARKING FOR THE DISABLED, STAIRS</th>
       </tr>
        
       <tr>
        <th style="width:3%;border-left:0.5px solid black;"></th>
        <th style="width:47%;"> KILOMETERS RADIUS</th>
        <th style="width:3%;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> FIRE ESCAPES. CABINETS AND PARTITIONS</th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[2]->EXIST.'</span></th>
        <th style="width:47%"> 2. SITE DEVELOPMENT PLAN</th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[3]->EXIST.'</span></th>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> 9. SCHEDULES OF DOORS AND WINDOWS</th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[4]->EXIST.'</span></th>
        <th style="width:47%"> 3. PERSPECTIVE</th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[5]->EXIST.'</span></th>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> 10. SCHEDULE OF FINISHES FOR FLOOR, CEILING AND WALLS</th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[6]->EXIST.'</span></th>
        <th style="width:47%"> 4. FLOOR PLAN</th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[7]->EXIST.'</span></th>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> 11. ARCHITECTURAL INTERIOR</th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[8]->EXIST.'</span></th>
        <th style="width:47%"> 5. ELEVATIONS, AT LEAST FOUR (4)</th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[9]->EXIST.'</span></th>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> 12. SPECIFICATIONS</th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[10]->EXIST.'</span></th>
        <th style="width:47%"> 6. SECTION, AT LEATS TWO (2)</th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[11]->EXIST.'</span></th>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> 13. COST ESTIMATE</th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[12]->EXIST.'</span></th>
        <th style="width:47%"> 7. CEILING PLANS SHOWING LIGHTING FIXTURES</th>
        <th style="width:2%;border:0.5px solid black;text-align:center;"><span style="font-family:zapfdingbats;">'.$DOCS[13]->EXIST.'</span></th>
        <th style="width:1%;border-left:0.5px solid black;"></th>
        <th style="width:47%;border-right:0.5px solid black;"> 14. OTHERS (Specify)</th>
       </tr>

       <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
       </tr>

       <tr>
       <th style="width:3%;border-left:0.5px solid black;"></th>
       <th style="width:47%;"> AND DIFFUSERS</th>
       <th style="width:3%;"></th>
       <th style="width:45%;border-bottom:0.5px solid black;">'.$DOCS[13]->type_remarks.'</th>
       <th style="width:2%;border-right:0.5px solid black;"></th>

      </tr>
      
      <tr>
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;"></th>
      </tr>
    </table>

    <table>
    <tr>
      <th style="width:100%;line-height:10px"><h4> BOX 8</h4></th>
    </tr>

    <tr style="line-height:15px;">
       <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:center;s"><h4>PROGRESS FLOW</h4></th>
    </tr>

    <tr style="line-height:15px;">
       <th style="width:35%;border-left:0.5px solid black;border-top:0.5px solid black;"></th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;"><b>IN</b></th>
       <th style="width:20%;border:0.5px solid black;text-align:center;"><b>OUT</b></th>
       <th style="width:25%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:center;line-height:15px;"></th>
    </tr>

    <tr style="line-height:15px;">
       <th style="width:35%;border-left:0.5px solid black;"></th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;"><b>DATE/TIME</b></th>
       <th style="width:20%;border-left:0.5px solid black;text-align:center;"><b>DATE/TIME</b></th>
       <th style="width:25%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:center;"><b>PROCESSED BY</b></th>
    </tr>

    <tr style="line-height:15px;">
       <th style="width:35%;border-left:0.5px solid black;border-top:0.5px solid black;"> ARCHITECTURAL DRAWING</th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;">'.$PROCESS[0]->IN.'</th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;">'.$PROCESS[0]->OUT.'</th>
       <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:center;">'.$PROCESS[0]->EMP.'</th>
    </tr>

    <tr style="line-height:15px;">
       <th style="width:35%;border-left:0.5px solid black;border-top:0.5px solid black;"> SPECIFICATIONS</th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;">'.$PROCESS[1]->IN.'</th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;">'.$PROCESS[1]->OUT.'</th>
       <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left;">'.$PROCESS[1]->EMP.'</th>
    </tr>

    <tr style="line-height:15px;">
       <th style="width:35%;border-left:0.5px solid black;border-top:0.5px solid black;"> OTHERS(SPECIFY)</th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;">'.$PROCESS[2]->IN.'</th>
       <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center;">'.$PROCESS[2]->OUT.'</th>
       <th style="width:25%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left;">'.$PROCESS[2]->EMP.'</th>
    </tr>
    <tr style="line-height:15px;">
      <th style="width:100%;border-top:0.5px solid black;"><h4> BOX 9</h4></th>
    </tr>
    <tr style="line-height:15px;">
      <th style="width:100%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;"><h4> ACTION TAKEN</h4></th>
    </tr>
    <tr style="line-height:15px;">
      <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"><h4> PERMIT IS HEREBY ISSUED SUBJECT TO THE FOLLOWING:</h4></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:2%;border-left:0.5px solid black;"></th>
    <th style="width:96%;"><p>1. That under Article 1723 of the Civil Code of the Philippines, the engineer (or architect) who drew up the plans and specifications
      <span style="text-align:justify;">for the building/structure is responsible for damages if within fifteen (15) years from the completion of the building/structure, the</span>
      <span style="text-align:justify;">same should collapse due to defect in the plans or specifications or defect sin the ground. The engineer or architect who</span>
      <span style="text-align:justify;">supervises the construction shall be solidarily liable with the contractor should the edifice collapse due to defect in the</span>
      <span style="text-align:justify;">construction or the use of inferior materials.</span>
    </p>
    </th>
    <th style="width:2%;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:2%;border-left:0.5px solid black;"></th>
    <th style="width:98%;border-right:0.5px solid black;"><p>2.	That the proposed civil/structural works shall be in accordance with the civil/structural plans filed with this office and in
      <span>conformity with the latest National Structural Code of the Philippines, the National Building Code and its IRR.</span>
    </p>
    </th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:2%;border-left:0.5px solid black;"></th>
    <th style="width:98%;border-right:0.5px solid black;"><p>3.	That prior to any construction activity, a duly accomplished prescribed <b>“Notice of Construction”</b> shall be submitted to the
      <span>Office of the Building Official.</span>
    </p>
    </th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:2%;border-left:0.5px solid black;"></th>
    <th style="width:96%;"><p>4.	That upon completion of the construction, the licensed full-time inspector and supervisor/in-charge of construction works shall
      <span style="text-align:justify;">submit the entry to the logbook duly signed and sealed to the Building Official including as-built plans and other documents and</span>
      <span style="text-align:justify;">shall also accomplish and submit a certificate of completion stating that the civil/structural works conform to the provisions of the</span>
      <span style="text-align:justify;">National Structural Code of the Philippines, the National Building Code and its IRR.</span>
    </p>
    </th>
    <th style="width:2%;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:2%;border-left:0.5px solid black;"></th>
    <th style="width:98%;border-right:0.5px solid black;"><p>5.	That this permit is null and void unless accompanied by the building permit.</p></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
    </tr>
    
    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:2%;border-left:0.5px solid black;"></th>
    <th style="width:96%;"><h3>PERMIT ISSUED BY:</h3></th>
    <th style="width:2%;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:35%;border-left:0.5px solid black;"></th>
    <th style="width:30%;border-bottom:0.5px solid black;text-align:center;">'.strtoupper($buildingOfficial).'</th>
    <th style="width:35%;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:35%;border-left:0.5px solid black;"></th>
    <th style="width:30%;text-align:center;"><h4>BUILDING OFFICIAL</h4></th>
    <th style="width:35%;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:35%;border-left:0.5px solid black;"></th>
    <th style="width:30%;text-align:center;">(Signature Over Printed Name)</th>
    <th style="width:35%;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:35%;border-left:0.5px solid black;"></th>
    <th style="width:5%;text-align:center;">Date:</th>
    <th style="width:20%;border-bottom:0.5px solid black;text-align:center;">'.date("F j, Y").'</th>
    <th style="width:40%;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;"></th>
    </tr>

    <tr style="line-height:15px;font-family:Times New Roman;">
    <th style="width:5%;border-left:0.5px solid black;"></th>
    <th style="width:93%;">NOTE: THIS PERMIT MAY BE CANCELLED PURSUANT TO SECTIONS 305 AND 306 OF THE “NATIONAL BUILDING CODE”</th>
    <th style="width:2%;border-right:0.5px solid black;"></th>
    </tr>
    
    <tr style="line-height:15px;border-left:0.5px solid black;">
    <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;"></th>
    </tr>

    </table>
    
    ';
    
    PDF::AddPage('P','Legal');
    PDF::writeHTML($html_content, true, true, true, true, '');
    PDF::Output(public_path() . '/prints.pdf', 'F');
    return response()->json(new JsonResponse(['status' => 'success']));
  } catch (\Exception $e) {
    return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
  }
 }
 }
  