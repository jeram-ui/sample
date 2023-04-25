<?php

namespace App\Http\Controllers\Api\SanitaryPermit;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use ZipArchive;
use PDF;

class sanitaryController extends Controller
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
  }
  public function ref(Request $request)
  {
    $pre = 'SP';
    $table = $this->lgu_db . ".cho1_sanitation_permit";
    $date = $request->date;
    $refDate = 'date_issued';
    $data = $this->G->generateReference($pre, $table, $date, $refDate);
    return response()->json(new JsonResponse(['data' => $data]));
  }
  public function refDirect($date)
  {
    $list = db::select('call '.$this->lgu_db.'.balodoy_get_sanitation_transno()');
      foreach ($list as $key => $value) {
          return$value->ref;
      }
  }
  public function store(Request $request)
  {
    try {
      // log::debug($request);
      DB::beginTransaction();
      $mainData = $request->main;
      $fees = $request->fees;
      $idx = $mainData['sanitation_id'];
      $card = $request->card;
     
      if ($idx > 0) {
        $reason = $request->reason;
        $this->update($idx, $mainData, $fees, $reason);
      } else {
        $mainData['sanitary_number'] = $this->refDirect($mainData['date_issued']);
        $this->save($mainData, $fees);

        if ($card) {
           log::debug($card);
         $this->storeHealth($request);
        }
      };
      DB::commit();
      return response()->json(new jsonresponse(['Message' => 'Data Saved Successfully!', 'status' => 'success']));
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
    }
  }

  public function save($mainData, $fees)
  {
    $mainData['or_no'] = '';
    $bid = $mainData['business_number'];
    unset($mainData['business_number']);
    db::table($this->lgu_db . '.cho1_sanitation_permit')->insert($mainData);
    $id = DB::getPDo()->lastInsertId();
    $signatory = DB::select('Call ' . $this->lgu_db . '.cvl_get_signatory_mayor_head()');
    foreach ($signatory as $row) {
      $sign = array(
        'form_id' => $id,
        'form_name' => 'Sanitary Application',
        'bns_id' => $mainData['app_pProfile_id'],
        'pp_id' => 0,
        'user_id' => Auth::user()->id,
        'head_id' => $row->health_head_id,
        'head_position' => $row->health_head_pos,
        'head_name' => $row->health_head_name,
        'mayor_id' => $row->mayor_id,
        'mayor_position' => $row->mayor_pos,
        'mayor_name' => $row->mayor_name,
      );

      DB::table($this->general . '.signatory_logs')->insert($sign);
    }
    foreach ($fees as $row) {
      if ($row['Include'] == 'True') {
        $billing = array(
          'ref_id' => $id,
          'bill_id' => $id,
          'payer_type' => "BUSINESS",
          'transaction_type' => "Sanitary Permit",
          'bill_number' => $mainData['sanitary_number'],
          'payer_id' => $bid,
          'business_application_id' => $mainData['app_pProfile_id'],
          'account_code' => $row['Account Code'],
          'bill_description' => $row['Account Description'],
          'net_amount' => $row['Initial Amount'],
          'bill_amount' => $row['Fee Amount'],
          'status' => $row['Status'],
          'nso'=>1
        );
        DB::table($this->lgu_db . '.cto_general_billing')->insert($billing);
        // $id = DB::getPDo()->lastInsertId();
      }
    }
  }
  public function delete(Request $request)
  {
    $id = $request->id;

    $data['approved_status'] = 'CANCELLED';
    DB::table($this->lgu_db . '.cho1_sanitation_permit')->where('sanitation_id', $id)->update($data);

    $reason['Form_name'] = 'Sanitary Application';
    $reason['Trans_ID'] = $id;
    $reason['Type_'] = 'Cancel Record';
    $reason['Trans_by'] = Auth::user()->id;

    $this->G->insertReason($reason);

    return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
  }
  public function edit(Request $request, $id)
  {
    $dateFrom = $request['from'];
    $dteTo = $request['to'];
    $transtype = $request['transtype'];
    try {

      $datax = DB::table($this->lgu_db . '.cho1_sanitation_permit')
        ->where('sanitation_id', $id)->get();
      $data['mainData'] = $datax;
      foreach ($datax as $row) {
        $appid = $row->app_pProfile_id;
      }

      $app = DB::table($this->lgu_db . '.ebplo_business_application')
        ->where('business_app_id', $appid)->get();

      $data['businessapp'] = $app;
      foreach ($app as $app) {

        $profile_id = $app->owner;
      }

      $data['person'] = DB::table($this->lgu_db . '.hr_person_profile')
        ->where('pp_person_code', $profile_id)->get();

      $data['fees'] = DB::table($this->lgu_db . '.cto_general_billing')
        ->select(
          'ref_id as id',
          'payer_type',
          'transaction_type',
          'bill_number',
          'payer_id',
          'business_application_id',
          'account_code',
          'bill_description',
          'net_amount',
          'bill_amount'
        )
        ->where('bill_id', $id)->get();
      return response()->json(new JsonResponse($data));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
    }
  }
  public function update($idx, $mainData, $fees, $reason)
  {
    $mainData['or_no'] = '';
    DB::table($this->lgu_db . '.cho1_sanitation_permit')->where('sanitation_id', $idx)->update($mainData);

    $reason = $reason;
    $reason['Form_name'] = 'Sanitary Application';
    $reason['Trans_ID'] = $idx;
    $reason['Type_'] = 'Modify Record';
    $reason['Trans_by'] = Auth::user()->id;

    $this->G->insertReason($reason);
    return response()->json(new JsonResponse(['Message' => 'Updated Successfully.', 'status' => 'success']));
  }
  public function getCategory()
  {
    $list = DB::select('Call ' . $this->lgu_db . '.health_category_zoe()');

    return response()->json(new JsonResponse($list));
  }

  /**
   * @OA\Get(
   *     path="/Sanitary/sanitaryList",
   *     summary="List of Sanitary Permits",
   *     operationId="sanitaryList",
   *     tags={"Sanitary"},
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
   *   @OA\Parameter(name="transtype",
   *     description="Transaction Type",
   *     in="query",
   *     required=true,
   *     example ="Sanitary Permit",
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
  public function sanitaryList(Request $request)
  {
    $dateFrom = $request['from'];
    $dteTo = $request['to'];
    $transtype = $request['transtype'];

    $list = DB::select('call ' . $this->lgu_db . '.ebplo_display_sanitaryinspection_gigil1_rans(?,?,?,?)', array($dateFrom, $dteTo, $transtype,"%"));
    return response()->json(new JsonResponse($list));
  }
  public function employeeIssuance($id)
  {
    $list = DB::select('call ' . $this->lgu_db . '.jay_display_declared_employees(?)', array($id));
    return response()->json(new JsonResponse($list));
  }

  public function businesschecking($year)
  {
    $business = DB::select('SELECT 
            sanitary_number,
            app_pProfile_id 
          FROM
            ' . $this->lgu_db . '.cho1_sanitation_permit 
          WHERE approved_status ="Active" 
            AND YEAR(date_issued) = "' . $year . '"');

    return response()->json(new JsonResponse($business));
  }
  public function checkInspection($id)
  {
    $inspect = DB::select('SELECT 
      sanitation_id,
      inspect_stat,
      ' . $this->lgu_db . '.jay_get_Person_name (insp.inspected_by) "Inspector",
      inspected_by 
      FROM ' . $this->lgu_db . '.cho1_sanitation_permit sp 
      INNER JOIN 
      ' . $this->lgu_db . '.cho1_sanitation_permit_inspection insp 
      ON (insp.sanitary_id = sp.sanitation_id)     
      WHERE sp.sanitation_id = ' . $id . '
      GROUP BY sp.sanitation_id;');
      return response()->json(new JsonResponse($inspect));
  }
    public function printSanitaryList(Request $request)
    {
      
      $logo = config('variable.logo');
      try {
        $main=$request->main;
        $reportcaption=$request->reportcaption;
        
          PDF::SetFont('Helvetica', '', '8');
          $html_content = '
                ' . $logo . ' 
          <h3 align="center">SANITARY PERMIT MASTER LIST</h3>
          <table>
          <tr>
          <th style="text-align:center;">As of ' . $request->reportcaption . '</th>
          </tr>
          </table>
          <br></br>
          <br></br>
          <table style="padding:2px;width:100%;">
          <thead>
            <tr>
             <th style="border:0.5px solid black;text-align:center;width:3%;background-color:#dedcdc;"><br><br><b>NO</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>REFERENCE NO</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>APPLICATION DATE</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:10%;background-color:#dedcdc;"><br><br><b>BUSINESS NAME</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:15%;background-color:#dedcdc;"><br><br><b>BUSINESS ADDRESS</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>CATEGORY</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>ISSUED DATE</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>INSPECTION STATUS</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>SANITARY FEE</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>OR NO</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>OR DATE</b><br></th>
             <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>PAYMENT STATUS</b><br></th>
            </tr>
         </thead>
         <tbody >';
      $ctr = 1;
      foreach ($main as $row) {
        $html_content .= '
        <tr style="padding:2px;width:100%;">
        <td style="border:0.5px solid black;text-align:center;width:3%;">' . $ctr . '</td>
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['ReferenceNo'] . '</td>
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['ApplicationDate'] . '</td>
        <td style="border:0.5px solid black;text-align:left;width:10%;">' . $row['BusinessName'] . '</td>
        <td style="border:0.5px solid black;text-align:left;width:15%;">' . $row['BusinessAddress'] . '</td>
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['Category'] . '</td>    
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['IssuedDate'] . '</td>
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['InspectionStatus'] . '</td>
        <td style="border:0.5px solid black;text-align:right;width:8%;">' . $row['SanitaryFee'] . '</td>   
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['ORNo'] . '</td>   
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['ORDate'] . '</td>
        <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['PaymentStatus'] . '</td>                    
        </tr>';
        $ctr++;
      }
      $ctr = $ctr - 1;

      $html_content .= '<tr style="padding:2px;">
        <th colspan="2" style="border:0.5px solid black;text-align:right;height:20px;"><b>TOTAL RECORDS</b></th>  
        <th colspan="10"style="border:0.5px solid black;text-align:left;height:20px;"><b>' . $ctr . '</b></th>  
        </tr>';
      $html_content .= '</tbody>
        </table>
        ';
      PDF::SetTitle('Sanitary Permit Master List');
      PDF::AddPage('L', array(250, 300));
      PDF::writeHTML($html_content, true, true, true, true, '');
      PDF::Output(public_path() . '/prints.pdf', 'F');
      return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
      return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }
  }
  public function printSanitaryCertificate(Request $request)
  {
    $signatory = $this->signatory;

    foreach ($signatory as $row) {
      $healthhead =   $row->{'health_head_name'};
      $position =   $row->{'health_head_pos'};
    }
    $template_file_name = public_path().'\HEALTH\Sanitary-Permit-Blank.docx';
    $rand_no = rand(111111, 999999);
    $fileName = "results_" . $rand_no . ".docx";
    $folder   = "results_sanitary";
    $full_path = $folder . '/' . $fileName;
    if (!file_exists($folder))
    {
      mkdir($folder);
    } 
    copy($template_file_name, $full_path);
    $zip_val = new ZipArchive;
    if($zip_val->open($full_path) == true)
    {
      $lgu = $this->G->get_lgu_data();
      $lgu = json_decode($lgu,true);
      foreach ($lgu as $key => $value) {
        $lgu = $value;
      }

      log::debug(str_replace($request['Business Name'],"&","&&"));
        $key_file_name = 'word/document.xml';
        $message = $zip_val->getFromName($key_file_name); 
        $message = str_replace("@sp",$request['Reference No'],$message);
        $message = str_replace("@YEAR",date("Y"),$message);
        $message = str_replace("@OWNER",$request->Owner,$message);
        $message = str_replace("@BUSINESS",$request['Business Name'],$message);
        $message = str_replace("@ADDRESS",$request['Business Address'],$message);
        $message = str_replace("@1DATE",$request['Issued Date'],$message);
        $message = str_replace("@EXPIRED",$request['Expiry Date'],$message);
        $message = str_replace("@head",$healthhead,$message);
        $message = str_replace("@inspector",$request->Inspector,$message);
        $message = str_replace("@ref",$request['OR No'],$message);
        $message = str_replace("@amount",$request['Sanitary Fee'],$message);

        $message = str_replace("@datepaid",$request['OR Date'],$message);
        $message = str_replace("@stamp",$this->G->serverdatetime(),$message);
        $message = str_replace("@c_right",$this->G->system_generated(),$message);
        $message = str_replace("@processno",Auth::user()->Employee_id,$message);
        $message = str_replace("@mun",$lgu['Municipal'],$message);
        $message = str_replace("@prov",$lgu['Province'],$message);
        log::debug($message);
        $zip_val->addFromString($key_file_name, str_replace("&","&amp;",$message));
        $zip_val->close();
        if (\File::exists(public_path()."/".$full_path)) {
            $file = \File::get($full_path);
            $type = \File::mimeType($full_path);
            $response = \Response::make($file, 200);
            $response->header("Content-Type", $type);
            return $response;
        }
    }

    // $logo = config('variable.sanitaryLogo');
    // try {
    //   PDF::SetFont('Helvetica', '', '10');
    //   $html_content = '
    //             ' . $logo . ' 
    //       <table>
    //       <tr>
    //       <th align="center"><h1 style="font-size:30px">SANITARY PERMIT</h1></th>
    //       </tr>
    //       <tr>
    //       <th align="center"><h1 style="font-size:20px">to operate a </h1></th>
    //       </tr>
    //       </table>
    //       <br><br>
    //       <table>
    //       <tr>
    //       <th align ="center"><h1  style="font-size:30px;border-bottom:1px solid black;">' . $request['BusinessName'] . '</h1></th>
    //       </tr>
    //       <br><br>
    //       <tr>
    //       <th style="width:20%;font-size:15pt;text-align:left;">ISSUED TO</th>
    //       <th style="width:5%;font-size:15pt;text-align:left;">:</th>
    //       <th style="width:75%;border-bottom:1px solid black;font-size:15pt;text-align:left;">' . $request['Owner'] . '</th>
    //       </tr><br>
    //       <tr>
    //       <th style="width:20%;font-size:15pt;text-align:left;">ADDRESS</th>
    //       <th style="width:5%;font-size:15pt;">:</th>
    //       <th style="width:75%;border-bottom:1px solid black;font-size:15pt;text-align:left;">' . $request['BusinessAddress'] . '</th>
    //       </tr> <br>
    //       <tr>
    //       <th style="width:20%;font-size:15pt;text-align:left;">DATE ISSUED</th>
    //       <th style="width:5%;font-size:15pt;text-align:left;">:</th>
    //       <th style="width:75%;border-bottom:1px solid black;font-size:15pt;text-align:left;">' . $request['IssuedDate'] . '</th>
    //       </tr>
    //       </table>
    //       <br><br><br><br>
    //       <table>
    //       <tr>
    //       <th style="width:48%;text-align:right;"><h3 style="font-size:15px">DATE OF EXPIRATION</h3></th>
    //       <th style="width:3%;text-align:left;"><h3 style="font-size:15px">:</h3></th>
    //       <th style="width:47%;text-align:left;"><h3 style="font-size:15px">' . strtoupper(date("F j, Y", strtotime($request['ExpiryDate']))) . '</h3></th>
    //       </tr>
    //       <br>
    //       <tr>
    //       <th style="width:100%;text-align:center;"><h4 style="font-size:12px">This permit is not transferrable and will be revoked</h4></th>
    //       </tr>
    //       <tr>
    //       <th style="width:100%;text-align:center;"><h4 style="font-size:12px">for Violation of any Sanitary Rule , law or regulations.</h4></th>
    //       </tr>
    //       <br><br><br>
    //       <tr>
    //       <th style="width:50%;text-align:right;"><h3 style="font-size:15px">SANITARY PERMIT NO</h3></th>
    //       <th style="width:3%;text-align:center;"><h3 style="font-size:15px">:</h3></th>
    //       <th style="width:42%;text-align:left;"><h3  style="font-size:15px"><u> ' . $request['ReferenceNo'] . '</u></h3></th>
    //       </tr>
    //       <tr>
    //       <th style="width:50%;text-align:right;"><h3 style="font-size:15px">OR DATE</h3></th>
    //       <th style="width:3%;text-align:center;"><h3 style="font-size:15px">:</h3></th>
    //       <th style="width:42%;text-align:left;"><h3  style="font-size:15px"><u> ' . $request['ORDate'] . '</u></h3></th>
    //       </tr>
    //       <tr>
    //       <th style="width:50%;text-align:right;"><h3 style="font-size:15px">OR NO</h3></th>
    //       <th style="width:3%;text-align:center;"><h3 style="font-size:15px">:</h3></th>
    //       <th style="width:42%;text-align:left;"><h3  style="font-size:15px"><u> ' . $request['ORNo'] . '</u></h3></th>
    //       </tr>
    //       </table>
    //       <br><br><br><br>
    //       <table>
    //       <tr>
    //       <th style="width:5%;text-align:right;"></th>
    //       <th style="width:40%;border-bottom:1px solid black;text-align:center;"><h3>' . strtoupper($request['Inspector']) . '</h3></th>
    //       <th style="width:10%;text-align:left;"><h3></h3></th>
    //       <th style="width:40%;border-bottom:1px solid black;text-align:center;"><h3>' . strtoupper($healthhead) . '</h3></th>
    //       <th style="width:5%;text-align:left;"><h3></h3></th>
    //       </tr>
    //       <tr>
    //       <th style="width:5%;text-align:right;"></th>
    //       <th style="width:40%;text-align:center;"><h3><i>' . $request['Position'] . '</i></h3></th>
    //       <th style="width:10%;text-align:left;"><h3></h3></th>
    //       <th style="width:40%;text-align:center;"><h3><i>' . $position . '</i></h3></th>
    //       <th style="width:5%;text-align:left;"><h3></h3></th>
    //       </tr>
    //       </table>
    //       <br><br><br>
    //       <table>
    //       <tr>
    //       <th style="width:10%;text-align:left;"><h3>NOTE:</h3></th>
    //       </tr>
    //       <tr>
    //       <th style="width:5%;text-align:center;"><h4 style="font-size:10px">1.</h4></th>
    //       <th style="width:85%;text-align:left;"><h4  style="font-size:10px">No Expansion</h4></th>
    //       </tr>
    //       <tr>
    //       <th style="width:5%;text-align:center;"><h4 style="font-size:10px">2.</h4></th>
    //       <th style="width:85%;text-align:left;"><h4 style="font-size:10px">To be revoked when complaint and nuisance arise</h4></th>
    //       </tr>
    //       <tr>
    //       <th style="width:5%;text-align:center;"><h4 style="font-size:10px">3.</h4></th>
    //       <th style="width:85%;text-align:left;"><h4 style="font-size:10px">Valid only on the date of compliance of all sanitary requirements and pertinent laws and</h4></th>
    //       </tr>
    //       <tr>
    //       <th style="width:5%;text-align:center;"><h4 style="font-size:10px"></h4></th>
    //       <th style="width:85%;text-align:left;"><h4 style="font-size:10px">ordinances of the National and Local Government.</h4></th>
    //       </tr> 
    //       <br><br><br>
    //       <tr>
    //       <th style="width:100%;text-align:center;"><h1 style="font-size:17px"><i>( This must be displayed in a public view. Not valid unless paid.)</i></h1></th>
    //       </tr>
    //       </table>

    //     ';
    //   PDF::SetTitle('Sanitary Permit Certificate');
    //   PDF::AddPage();
    //   PDF::SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
    //   PDF::Line(5, 5, PDF::getPageWidth() - 5, 5);
    //   PDF::Line(PDF::getPageWidth() - 5, 4.5, PDF::getPageWidth() - 5, PDF::getPageHeight() - 5);
    //   PDF::Line(5, PDF::getPageHeight() - 5, PDF::getPageWidth() - 5, PDF::getPageHeight() - 5);
    //   PDF::Line(5, 4.5, 5, PDF::getPageHeight() - 5);
    //   PDF::SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
    //   PDF::Line(8, 8, PDF::getPageWidth() - 8, 8);
    //   PDF::Line(PDF::getPageWidth() - 8, 7.4, PDF::getPageWidth() - 8, PDF::getPageHeight() - 8);
    //   PDF::Line(8, PDF::getPageHeight() - 8, PDF::getPageWidth() - 8, PDF::getPageHeight() - 8);
    //   PDF::Line(8, 7.4, 8, PDF::getPageHeight() - 8);
    //   PDF::writeHTML($html_content, true, true, true, true, '');
    //   PDF::Output(public_path() . '/prints.pdf', 'F');
      // return response()->json(new JsonResponse(['status' => 'success']));
    // } catch (\Exception $e) {
    //   return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    // }
  }
  public function storeHealth($request)
  {
      try {
        log::debug($request->main);
          $main =$request->main;
          $ctobill = $request->feesHealth;

          $idx = $main['sanitation_id'];
          log::debug($idx);
          if ($idx > 0) {
              $this->updatehealth($idx, $main, $ctobill);
          } else {
              $main['ref_no']=$this->transNoDirect();
              $this->saveHealth($main, $ctobill);
          };

      } catch (\Exception $e) {
          DB::rollBack();
          return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
      }
  }
  public function transNoDirect()
  {
      $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_healthcard_transno()');
      foreach ($list as $key => $value) {
          return$value->TransNo;
      }
  }
  public function saveHealth($main, $ctobill)
  {
    log::debug($main);
    log::debug('health');
    $ref = $this->transNoDirect();
      $mainF = array(
          'ref_no'  => $ref,
          'trans_date'  => $main['date_issued'],
          'bapp_id' => $main['app_pProfile_id'],
          'no_emp'  => $main['no_emp'],
      );
      DB::table($this->lgu_db . '.cho1_health_card_main')->insert($mainF);
      $id = DB::getPDo()->lastInsertId();
      foreach ($ctobill as $row) {
          if ($row['Include'] === "True") {
              $cto = array(
                  'payer_type' => 'Business',
                  'payer_id' => $main['business_number'],
                  'business_application_id' => $main['app_pProfile_id'],
                  'account_code' => $row['Account Code'],
                  'bill_description' => $row['Account Description'],
                  'net_amount' => $row['Fee Amount'],
                  'bill_amount' => $row['Fee Amount'],
                  'bill_month' => $main['date_issued'],
                  'bill_number' => $ref,
                  'transaction_type' => 'Health Certification',
                  'ref_id' => $id,
                  'bill_id' => $id,
                  'include_from' => 'Others',
              );
              DB::table($this->lgu_db . '.cto_general_billing')->insert($cto);
          }
      }
  }
  public function updatehealth($idx, $main, $ctobill)
  {
      $mainF = array(
          'ref_no'  => $main['ref_no'],
          'trans_date'  => $main['trans_date'],
          'bapp_id' => $main['app_pProfile_id'],
          'no_emp'  => $main['no_emp'],
      );
      DB::table($this->lgu_db . '.cho1_health_card_main')->where('id', $idx)->update($mainF);
  }
  public function  getInspection(Request $request){
    $datax = json_decode($request->data,true);
    log::debug($datax);
    $data = db::select('CALL '.$this->lgu_db.'.jay_display_cho1_sanitation_permit_inspection2(?)',[$datax['sanitation_id']]);
    return response()->json(new JsonResponse( $data));
  }
  public function  getInspector(Request $request){
    $data = db::select('CALL '.$this->lgu_db.'.cvl_inspector_per_HEALTHdept()');
    return response()->json(new JsonResponse( $data));
  }

  public function  storeInspection(Request $request){
   try {
    $list = $request->list;
    $selected = $request->selected;
    $form = $request->form;
    $fail = 0;
    $na = 0;
    $pass=0;
    DB::beginTransaction();
    db::table($this->lgu_db.'.cho1_sanitation_permit_inspection')
    ->where('sanitary_id',$selected['sanitation_id'])->delete();
    log::debug(1);
    foreach ($list as $key => $value) {
      // log::debug($value);
      $datax =array(
        'sanitary_id'=>$selected['sanitation_id']
        ,'date_inspected'=>$form['dateinspection']
        ,'inspected_by'=>$form['inspector']
        ,'Activity_id'=>$value['ID']
        ,'Activity'=>$value['Inspection Activities']
        ,'Pass'=>$value['Pass']
        ,'Fail'=>$value['Fail']
        ,'NA'=>$value['N.A.']
      );
      log::debug($datax);
      log::debug(2);

      if ($value['Pass'] ==='True') {
        $pass =+1;
      }
      if ($value['Fail'] ==='True') {
        $fail =+1;
      }
      if ($value['N.A.'] ==='True') {
        $na =+1;
      }
       db::table($this->lgu_db.'.cho1_sanitation_permit_inspection')->insert($datax);
    }
    log::debug($pass);
    if ($pass == 0 && $fail == 0) {

      $main=array(
        'inspect_stat'=>"N.A"
        ,'inspectedby'=>$form['inspector']
       );
       db::table($this->lgu_db.'.cho1_sanitation_permit')
       ->where('sanitation_id',$selected['sanitation_id'])
       ->update($main);
    }

    if ($fail > 0 ) {
 
      $main=array(
       'inspect_stat'=>"Fail"
       ,'inspectedby'=>$form['inspector']
      );
      db::table($this->lgu_db.'.cho1_sanitation_permit')
      ->where('sanitation_id',$selected['sanitation_id'])
      ->update($main);
    }else{

      $main=array(
        'inspect_stat'=>"Pass"
        ,'inspectedby'=>$form['inspector']
       );
       db::table($this->lgu_db.'.cho1_sanitation_permit')
       ->where('sanitation_id',$selected['sanitation_id'])
       ->update($main);
    }
    db::commit();
    return response()->json(new jsonresponse(['Message' => 'Data Saved Successfully!', 'status' => 'success']));
  } catch (\Exception $e) {
    DB::rollBack();
    return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
   }

  }
}
