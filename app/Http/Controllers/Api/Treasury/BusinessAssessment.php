<?php

namespace App\Http\Controllers\Api\Treasury;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class BusinessAssessment extends Controller
{
   
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }

    public function generateRef(Request $request)
    {
        $pre = 'BTA-';
        $table = $this->lgu_db . ".ebplo_business_classification";
        $date = $request->date;
        $refDate = 'transdate';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }

    public function store(Request $request)
    {
      try 
      {
        dd($request);
        DB::beginTransaction();     
        $mainData = $request->main;    
        $details = $request->details; 
        $idx=$mainData['SysPK_business_classification'];
        if ($idx > 0) {
          $reason=$request->reason;
            $this->update($idx,$mainData,$fees,$reason);
        }else { 
            $this->save($mainData,$fees);
        };         
        DB::commit();
        return response()->json(new jsonresponse(['Message' => 'Data Saved Successfully!','status'=>'success']));
      
      } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(new jsonresponse(['Message' => 'Error Saving Data!','errormsg'=>$e,'status'=>'error']));
    }
   }

   public function save($mainData,$details)
   {     
    unset($mainData['SysPK_business_classification']);
     db::table($this->lgu_db.'.ebplo_business_classification')->insert($mainData);
     $id = DB::getPDo()->lastInsertId();
     
     $this->save($id,$details);         
   }

   public function update($id,$mainData,$details)
   { 
     DB::table($this->lgu_db.'.ebplo_business_classification')->where('sanitation_id', $id) ->update($details);
     
     $this->deleteDetails($id);  
     $this->save($id,$details);    
 
   }

   public function deleteDetails($id,$mainData,$details)
  { 
    DB::table($this->lgu_db.'.ebplo_business_classification')->where('sanitation_id', $id) ->delete();
    $this->save($id,$details);  
  }

  public function saveDetails($id,$details)
  {
    // entryFrom 
    foreach($details['dataEntryForm'] as $row)   
    {             
      $data = array(
        'SysPK_buss_class'=>$id,
        'kind_id' =>$row['kind_id'],
        'size_id' =>$row['size_id'],
        'type_id' =>$row['type_id'],
        'zone_id' =>$row['zone_id'],
        'occ_id' =>$row['occ_id'],
        'class_code'=>$row['class_code'],
        'type'=>$row['Type'],
        'size'=>$row['Size'],
        'zone'=>$row['Zone'],
        'storage_cum'=> $row['Storage of CM'],        
        'no_emp'=>$row['No of Employee'],
        'app_type'=>$row['Application Type'],
        'gross_capital'=>$row['Gross/Capitalization'],
        'declaration_of_sale'=>$row['Declaration of Sale'],
        'type_'=>'Normal',
      );
      DB::table( $this->lgu_db.'.ebplo_business_assessment_entry')->insert($data);  
    }


    // LineBusiness 
    foreach($details['dataLineBusiness'] as $row)   
    {             
      $data = array(
        'SysPK_business_classification'=>$id,
        'activity_code' =>$row['SysPK_business_activity'],
        'income_code' =>$row['income_code'],
        'category' =>$row['Category'],
        'particular' =>$row['Nature of Business'],
        'essential' =>$row['essential'],
        'capital_gross'=>$row['Capitalization/Gross Sales'],
        'taxable'=>$row['taxable'],
        'tax_amount'=>$row['Tax Amount'],
        'mode_payment'=>$row['Mode of Payment'],
        'type_'=> 'Normal',        
        'EF'=>$row['EF'],
        'penalty'=>$row['Penalty'],
        'interest'=>$row['Interest'],        
      );
      DB::table( $this->lgu_db.'.ebplo_business_activity')->insert($data);  
    }


    // GarabageFees 
    foreach($details['dataGarabageFees'] as $row)   
    {             
      $data = array(
        'SysPK_business_classification'=>$id,
        'garbage_code' =>$row['garbage_code'],
        'income_code' =>$row['income_code'],
        'type' =>$row['type'],
        'classification' =>$row['classification'],
        'no' =>$row['No'],
        'tax_amount'=>$row['Garbage Fee'],
        'garbage_amount'=>$row['Garbage Amount'],
        'mode_payment'=>$row['Mode of Payment'],     
        'EF'=>$row['EF'],
        'penalty'=>$row['Penalty'],
        'interest'=>$row['Interest'],        
      );
      DB::table( $this->lgu_db.'.ebplo_business_garbage')->insert($data);  
    }


    // CombustibleFees 
    foreach($details['dataCombustibleFees'] as $row)   
    {             
      $data = array(
        'SysPK_business_classification'=>$id,
        'combustible_code' =>$row['garbage_code'],
        'income_code' =>$row['income_code'],
        'type' =>$row['type'],
        'unit' =>$row['classification'],
        'no' =>$row['No'],
        'amount'=>$row['Garbage Fee'],
        'mode_payment'=>$row['Mode of Payment'],     
        'EF'=>$row['EF'],
        'penalty'=>$row['Penalty'],
        'interest'=>$row['Interest'],        
      );
      DB::table( $this->lgu_db.'.ebplo_business_combustible')->insert($data);  
    }



    // OccupationalFees 
    foreach($details['dataOccupationalFees'] as $row)   
    {             
      $data = array(
        'SysPK_business_classification'=>$id,
        'occupational_id' =>$row['garbage_code'],
        'income_code' =>$row['income_code'],   
        'initial_amount'=>$row['Initial Amount'],
        'no' =>$row['No'],
        'amount'=>$row['Amount'],
        'mode_payment'=>$row['Mode of Payment'],     
        'EF'=>$row['EF'],
        'penalty'=>$row['Penalty'],
        'interest'=>$row['Interest'],        
      );
      DB::table( $this->lgu_db.'.ebplo_business_occupational_fees')->insert($data);  
    }


    // OtherFees 
    foreach($details['dataOtherFees'] as $row)   
    {             
      $data = array(
        'SysPK_business_classification'=>$id,
        'income_code' =>$row['income_code'],   
        'initial_amount'=>$row['Initial Amount'],
        'no' =>$row['No'],
        'amount'=>$row['Amount'],
        'mode_payment'=>$row['Mode of Payment'],               
      );
      DB::table( $this->lgu_db.'.ebplo_business_other_fees')->insert($data);  
    }
  }

    /**
     * @OA\Get(
     *     path="Treasury/Assessment/getList",
     *     summary="Business Assessment List",
     *     operationId="getList",
     *     tags={"Treasury - Business Assessment"},
     *  @OA\Parameter(name="from",
     *     description="From",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="to",
     *     description="To",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *         response=200,
     *         description="An paged array of pets",
     *         @OA\Schema(ref="#"),
     *         @OA\Header(header="x-next", @OA\Schema(type="string"), description="A link to the next page of responses")
     *   ),
     *   @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/Error")
     *   )
     * )
     */
    public function getList(Request $request)
    {
      try {
          
        $from = $request['from'] ;
        $to = $request['to'];
      
        $list = DB::select('call '.$this->lgu_db.'.jay_ebplo_display_business_class(?,?)',array($from,$to));   
            
        return response()->json(new JsonResponse($list));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function checkExist(Request $request)
    {
      try {

        $bappId = $request['bappId'] ;
        $taxYear = $request['taxYear'];
      
        $list = DB::select('call '.$this->lgu_db.'.jay_check_assessment_ebplo(?,?)',array($bappId,$taxYear));   
            
        return response()->json(new JsonResponse($list));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }


    public function getPreloadEntryForm(Request $request)
    {
      try {
         

        $busId = $request['busId'] ;
        $data['kindBusiness'] = DB::select('call '.$this->lgu_db.'.jay_display_ebplo_business_kind_business(?)',array($busId));   
        $data['natureBusiness'] = DB::select('call '.$this->lgu_db.'.jay_display_ebplo_business_nature_business()');   
        $data['sizeBusiness'] = DB::select('call '.$this->lgu_db.'.jay_display_ebplo_business_category_size()');   
        $data['zoneBusiness'] = DB::select('call '.$this->lgu_db.'.jay_display_ebplo_business_zone()');   
        $data['typeBusiness'] = DB::select('call '.$this->lgu_db.'.jay_display_cto_type_business()');   
        $data['occupationalFees'] = DB::select('call '.$this->lgu_db.'.jay_display_cto_occupation_fees()');   
        
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getEntryFormList(Request $request)
    {
      try {
         
        $classId = $request['classId'] ;
        $data['list'] = DB::select('call '.$this->lgu_db.'.jay_cto_ebplo_assessment_entry_web(?)',array($classId));   
        
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getPreloadLineBusiness()
    {
      try {         
        
        $data['Fixed'] = DB::select('call '.$this->lgu_db.'.jay_cto_tax_base_classification_mayors_fee(1)');   
        $data['Business'] = DB::select('call '.$this->lgu_db.'.jay_cto_tax_base_classification_business_tax(0)');   
        $data['ModePayment'] = DB::select('call '.$this->lgu_db.'.jay_display_tbl_mode_payment()');   
      
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getLineBusinessList(Request $request)
    {
      try {
         
        $classId = $request['classId'] ;
        $data['list'] = DB::select('call '.$this->lgu_db.'.jay_cto_ebplo_Line_Of_Business_Display(?)',array($classId));   
        
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }


    public function computeBusinessTaxGraduated(Request $request)
    {
      try {
         
        $pBusinessClassCode = $request['pBusinessClassCode'];        
        $pCapitalization_GrossSales = $request['pCapitalization_GrossSales'];
        $pIsEssential = $request['pIsEssential'];
        $pApplicationType = $request['pApplicationType'];

        $data = DB::select('call '.$this->lgu_db.'.cto_Get_TaxPerAnnum_LineBusiness(?,?,?,?)',array($pBusinessClassCode,$pCapitalization_GrossSales,$pIsEssential,$pApplicationType));   
            
        
        return response()->json(new JsonResponse($data));        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }


    public function getPreloadGarabageFees()
    {
      try {         
        
        $data['Fixed'] = DB::select('call '.$this->lgu_db.'.cto_garbage_collection_setup_DISPLAY()');   
        $data['Garbage'] = DB::select('call '.$this->lgu_db.'.jay_cto_tax_base_classification_garbage()');   
        $data['Classification'] = DB::select('Select * From '.$this->lgu_db.'.cto_garbage_classification');   
        $data['ModePayment'] = DB::select('call '.$this->lgu_db.'.jay_display_tbl_mode_payment()');   
      
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getGarabageList(Request $request)
    {
      try {
         
        $classId = $request['classId'] ;
        $data['list'] = DB::select('call '.$this->lgu_db.'.jay_cto_ebplo_garbage_Display(?)',array($classId));   
        
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function computeGarabageGraduated(Request $request)
    {
      try {
         
        $classCode = $request['classCode'];        
        $size = $request['size'];
     
        $data = DB::select('call '.$this->lgu_db.'.cto_Get_TaxPerAnnum_Garbage(?,?)',array($classCode,$size));   
  
        return response()->json(new JsonResponse($data));        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getPreloadCombustibleFees()
    {
      try {         
        
        $data['Fixed'] = DB::select('call '.$this->lgu_db.'.cto_combustible_collection_setup_DISPLAY(?)',array('Per Unit'));  
        $data['Combustible'] = DB::select('call '.$this->lgu_db.'.cto_combustible_collection_setup_DISPLAY(?)',array('Range'));
        $data['ModePayment'] = DB::select('call '.$this->lgu_db.'.jay_display_tbl_mode_payment()');   
      
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getCombustibleList(Request $request)
    {
      try {
         
        $classId = $request['classId'] ;
        $data['list'] = DB::select('call '.$this->lgu_db.'.jay_cto_ebplo_combustible_Display(?)',array($classId));   
        
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }


    public function computeCombustibleGraduated(Request $request)
    {
      try {
         
        $combustileId = $request['combustileId'];        
        $size = $request['size'];
     
        $data = DB::select('call '.$this->lgu_db.'.cto_Get_TaxPerAnnum_Combustible(?,?)',array($combustileId,$size));   
  
        return response()->json(new JsonResponse($data));        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }


    public function getPreloadOccupationalFees()
    {
      try {         
        
        $data['Fixed'] = DB::select('call '.$this->lgu_db.'.cto_occupation_fees_DISPLAY()');  
        $data['ModePayment'] = DB::select('call '.$this->lgu_db.'.jay_display_tbl_mode_payment()');   
      
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getOccupationalFeesList(Request $request)
    {
      try {
         
        $bappId = $request['bappId'] ;
        $classId = $request['classId'] ;
        $mode = $request['mode'] ;

        if($mode=='Add'){
          $data['list'] = DB::select('call '.$this->lgu_db.'.jay_ebplo_business_occupational_fees_new(?)',array($bappId));           
        }else{
          $data['list'] = DB::select('call '.$this->lgu_db.'.jay_ebplo_business_occupational_fees(?)',array($classId));   
        }
        
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }


    public function getPreloadOtherFees()
    {
      try {         
        
        $data['Fixed'] = DB::select('call '.$this->lgu_db.'.cto_otherfees_setup_DISPLAY()');  
        $data['ModePayment'] = DB::select('call '.$this->lgu_db.'.jay_display_tbl_mode_payment()');   
      
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }


    public function getOtherFeesList(Request $request)
    {
      try {
         
        $classId = $request['classId'] ;
    
        $data['list'] = DB::select('call '.$this->lgu_db.'.jay_cto_ebplo_otherFees_Display(?)',array($classId));   
                
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    public function getOtherFeesDefault(Request $request)
    {
      try {
         
        $appType = $request['appType'] ;
        $noEmp = $request['noEmp'] ;
    
        $data['list'] = DB::select('call '.$this->lgu_db.'.jay_tax_base_other_fees_defaults(?,?)',array($appType,$noEmp));   
                
        return response()->json(new JsonResponse($data));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

    /**
     * @OA\Get(
     *     path="Treasury/Assessment/getDetails",
     *     summary="Business Assessment Details",
     *     operationId="getDetails",
     *     tags={"Treasury - Business Assessment"},
     *  @OA\Parameter(name="assessmentId",
     *     description="Assessment Id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="busNum",
     *     description="Business Number",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="taxYear",
     *     description="Tax year",
     *     in="query",
     *     required=true,
     *     @OA\JsonContent(ref="#")
     *   ),
     *   @OA\Response(
     *         response=200,
     *         description="An paged array of pets",
     *         @OA\Schema(ref="#"),
     *         @OA\Header(header="x-next", @OA\Schema(type="string"), description="A link to the next page of responses")
     *   ),
     *   @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/Error")
     *   )
     * )
     */
    public function getDetails(Request $request)
    {
      try {
          
        $assessmentId = $request['assessmentId'] ;
        $busNum = $request['busNum'] ;
        $taxYear = $request['taxYear'] ;
    
        $list = DB::select('call '.$this->lgu_db.'.spl_display_assessment_details_joy(?,?,?)',array($assessmentId,$busNum,$taxYear));   
            
        return response()->json(new JsonResponse($list));
        
      } catch (\Excemption $e) {
      
          return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
      }
    }

   

  /**
   * @OA\Post(
   *      path="Treasury/Assessment/printList",
   *      operationId="printList",
   *      tags={"Treasury - Business Assessment"},
   *      summary="Print Business Assessment List",
   *      description="Returns list data",
   *      @OA\RequestBody(
   *          required=true,
   *          @OA\JsonContent(ref="#")
   *      ),
   *      @OA\Response(
   *          response=201,
   *          description="Successful operation",
   *          @OA\JsonContent(ref="#")
   *       ),
   *      @OA\Response(
   *          response=400,
   *          description="Bad Request"
   *      ),
   *      @OA\Response(
   *          response=401,
   *          description="Unauthenticated",
   *      ),
   *      @OA\Response(
   *          response=403,
   *          description="Forbidden"
   *      )
   * )
   */
  public function printList(Request $request)
  {

    $tmp = $request->filter;   
    $coveredDate = $tmp['reportcaption']; 
    $data = $request->main;      
    
    $logo = config('variable.logo');  

    try {
       
        PDF::SetFont('Helvetica', '', '9');
        
        $html_content = '
              ' . $logo . ' 
              <h2 align="center">Business Tax Assessment</h2>            
              <h3 align="center">Period Covered '.$coveredDate.'</h3>

              <br></br>
              <br></br>
              <br></br>
              <br></br> 
              <table  border="1" style="padding:2px;">
              <thead>
              <tr>
                <th style="width:3%;text-align:center;background-color:#dedcdc;"><br><br><b>No</b><br></th>
                <th style="width:8%;text-align:center;background-color:#dedcdc;"><br><br><b>Reference No</b><br></th>
                <th style="width:8%;text-align:center;background-color:#dedcdc;"><br><br><b>Trans Date</b><br></th>
                <th style="width:11%;text-align:center;background-color:#dedcdc;"><br><br><b>Account No</b><br></th>
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>Business Name</b><br></th>
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>Trade Name</b><br></th>
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>Owner Name</b><br></th>
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>Address</b><br></th>
                          
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>Capitalization</b><br></th>                
                <th style="width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>New Capitalization</b><br></th> 
             
                <th style="width:5%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Year</b><br></th>
                <th style="width:5%;text-align:center;background-color:#dedcdc;"><br><br><b>Status</b><br></th>
              
              </tr>
    
              </thead>
              <tbody >'; 
              
              $ctr = 1; 
              foreach($data as $row){                              
               $html_content .='
                  <tr >        
                    <td style="width:3%;text-align:center;">' .$ctr. '</td>
                    <td style="width:8%;text-align:center;">' . $row['Reference No'] . '</td>                    
                    <td style="width:8%;text-align:center;">' . $row['Trans Date'] . '</td>

                    <td style="width:11%;text-align:center;">' . $row['Account No'] . '</td>
                    <td style="width:10%;text-align:left;">' . $row['Business Name'] . '</td>
                    <td style="width:10%;text-align:left;">' . $row['Trade Name'] . '</td>
                    

                    <td style="width:10%;text-align:left;">' . $row['Owner Name'] . '</td>
                    <td style="width:10%;text-align:left;">' . $row['Address'] . '</td>

                    <td style="width:10%;text-align:right;">' . $row['Capitalization'] . '</td>
                    <td style="width:10%;text-align:right;">' . $row['New Capitalization'] . '</td>
                    <td style="width:5%;text-align:center;">' . $row['Tax Year'] . '</td>

                    <td style="width:5%;text-align:center;">' . $row['Status'] . '</td>        

                                            
                  </tr>';
                  $ctr++;
              }
              $ctr = $ctr - 1;
              $html_content .='<tr>
              <th colspan="2" style="text-align:right;height:20px;padding-top: 20px;"><b>TOTAL RECORDS</b></th>  
              <th colspan="17"style="text-align:left;height:20px;padding-top: 20px;"><b>'.$ctr.'</b></th>  
              </tr>';
              $html_content .='</tbody>
              </table>';


      PDF::SetTitle('Business Tax Assessment');
      PDF::AddPage('L','A4');
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path() . '/printList.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {  
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
     
    }
  }



  
// Print Assessment***
public function rptAssessmentPrint(Request $request)
{

$data = $request->main;

$signatory = $this->signatory;

foreach($signatory as $row){ 
    $treasureName =   $row->{'head_name'};
    $designation =   $row->{'head_pos'};   
}  

$logo = config('variable.logo');
try {
    PDF::SetFont('Helvetica', '', '9');
    $html_content = '
    ' . $logo . ' 
    <table style="width:100%;padding:3px;">
    <tr>
    <th style="width:100%"><h3 align="center">Notice of Delinquency</h3></th>  
    </tr>
    <br>
    <br>
    <br>
    <tr>
        <th style="width:75%"></th> 
        <th style="text-align:right;width:25%;"><h3>' . $request->dateNotice . '</h3></th> 
    </tr>
    <tr>
        <th style="width:5%">To</th> 
        <th style="width:2%">:</th>
        <th style="width:34%;"><h3>' . $data['OWNER'] . '</h3></th>  
    </tr>
    <tr>
        <th style="width:7%"></th>  
        <th style="width:34%;"><h3>' . $data['OWNER ADDRESS'] . '</h3></th>  
    </tr>       
    </table>  
    <br> <br>  <br> 
    <table style="width:100%;padding:3px;">
    <tr>
        <th style="width:100%">SIR/MADAM:</th>  
    </tr> 
    <tr>
        <th style="width:5%;"></th> 
        <th style="width:100%;">Records of the office show that you have not paid your real property tax as follows ;</th>         
    </tr>

    <table  border="1" style="padding:2px;">
        <thead>
        <tr>             
            <th style="width:15%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Dec No</b><br></th>
            <th style="width:17%;text-align:center;background-color:#dedcdc;"><br><br><b>Location of Property</b><br></th>
            <th style="width:15%;text-align:center;background-color:#dedcdc;"><br><br><b>Assessed Value</b><br></th>
            <th style="width:17%;text-align:center;background-color:#dedcdc;"><br><br><b>Tax Year(s)</b><br></th>
            <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Basic/SEF Tax Dues</b><br></th>
            <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Basic/SEF Tax Penalties</b><br></th>
            <th style="width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>Amount</b><br></th>
        </tr>  
        </thead>
        <tbody>
        <tr>    
            
            <td style="width:15%;text-align:center;">' .$data['TAX DEC. NO']. '</td>
            <td style="width:17%;text-align:center;">' .$data['PROPERTY ADDRESS'] . '</td>                    
            <td style="width:15%;text-align:center;">' .$data['ASSESSED VALUE'] . '</td>

            <td style="width:17%;text-align:center;">' .$data['TAX YEAR(S)'] . '</td>
            <td style="width:12%;text-align:right;">' . $data['TAXDUES'] . '</td>                                 

            <td style="width:12%;text-align:right;">' .$data['PENALTY'] . '</td>
            <td style="width:12%;text-align:right;">' .$data['DELINQUENCY'] . '</td>    

        </tr>
        <tr>                           
            <td style="width:100%;text-align:center;">***Nothing Follows***</td>       
        </tr>';

        for($counter = 1; $counter <= 3; $counter += 1){                              
            $html_content .='
            <tr>        
                <td style="width:15%;text-align:center;">  </td>
                <td style="width:17%;text-align:center;">  </td>                  
                <td style="width:15%;text-align:center;">  </td>

                <td style="width:17%;text-align:left;">  </td>
                <td style="width:12%;text-align:left;">  </td>                              

                <td style="width:12%;text-align:center;">  </td>
                <td style="width:12%;text-align:right;">  </td>                                                        
            </tr>';
        }  

    $html_content .='  
    </table> 
    <br>
    <br>
    <br>
    <table>    
    <tr>  
        <th style="text-align:right;width:75%;"><h3>Deinquency Amount</h3></th>   
        <th style="text-align:right;width:25%;"><h2>P '.$data['DELINQUENCY'].'</h2></th>     
    </tr>
    <br>     
    <tr>        
        <th style="width:100%;">          Please reconcile this with your records and inform our office of any discrepancies as soon as possible so that the necessary corrections can be affected. It has been our earnest desire to keep our records accurate in order to avoid inconvenience on your part.</th>  
    </tr>
    <br>
    <tr>        
        <th style="width:100%;">          On the other hand, if you simply missed to effect payments, we would appreciate it very much if you can pay the same within the period of fifteen (15) days from reciept hereof, so that your name could be deleted or dropped from the list of delinquent taxpayers in this municipality.</th>  
    </tr> 
    <br>
    <tr>        
        <th style="width:100%;">          If we do not hear from you within the period aforementioned, we shall be constrained to avail of the administrative and/ or judicial remedies for the collection thereof pursuant to Secs. 256 - 266, R.A 7160, otherwise known as "The Local Government Code of 1991" , to wit ;</th>  
    </tr> 
    <tr>        
        <th style="width:20%;"></th>  
        <th style="width:80%;"><b>(a) Administrative through levy on real property and sale at public auction, or simutaneously,</b></th>  
    </tr> 
    <tr>        
        <th style="width:20%;"></th>  
        <th style="width:80%;"><b>(b) by juridical Action</b></th>  
    </tr>       
    <br>
    <tr>        
        <th style="width:100%;">We hope this notice merit your preferential attention.</th>             
    </tr> 
    <br>
    <br>
    <tr>   
        <th style="text-align:center;width:100%";><b>Please disregard notice if payment has been made.</b></th>  
    </tr>        
    </table>  
    <br>
    <br>
    <br>
    <table style="width:100%;padding:3px;">
    <tr>
    <th style="width:60%"></th> 
    <th style="width:40%;">Very truly yours,</th> 
    </tr>
    <br> 
    <tr>
    <th style="width:70%"></th> 
    <th style="text-align:center;width:30%"><b>'.$treasureName.'</b></th>  
    </tr>      
    <tr>
    <th style="width:70%"></th> 
    <th style="text-align:center;width:30%">'.$designation.'</th>  
    </tr>
    </table> 
    <br> 
    <table style="width:100%;padding:3px;">
    <tr> 
        <th style="width:12%;"><b>Received :</b></th> 
        <th style="border-bottom: 1px solid black;width:27%;"></th>
    </tr> 
    <tr>
        <th style="width:12%;"><b>Date :</b></th>  
        <th style="border-bottom: 1px solid black;width:27%;"></th>
    </tr> 
    </table>';

    PDF::SetTitle('Notice of Delinquency');
    PDF::AddPage();
    PDF::writeHTML($html_content, true, true, true, true, '');
    PDF::Output(public_path() . '/prints.pdf', 'F');
    return response()->json(new JsonResponse(['status' => 'success']));
} catch (\Exception $e) {
    return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
}
}
  
}
