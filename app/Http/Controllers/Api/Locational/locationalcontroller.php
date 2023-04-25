<?php
namespace App\Http\Controllers\Api\Locational;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;


class locationalcontroller extends Controller
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
    }

    public function index()
    {
    }
    public function displaylist(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $type = $request->type;
        $list = DB::select('call ' . $this->lgu_db . '.jen_get_display_locational_type_new(?,?,?,?)', array($from,$to,$type,'Locational Clearance'));
        
        return response()->json(new JsonResponse($list));
    }
    public function display(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.cvl_display_project_registration_list_notin(?,?)', array($from,$to));
        return response()->json(new JsonResponse($list));
    }
    public function displayprojnature()
    {
        $list = DB::select("select `id`,`type_` from ".$this->lgu_db.".setup_project_registration_type");
        return response()->json(new JsonResponse($list));
    }
    public function displayclassification()
    {
        $list = DB::select("select class_id,".$this->lgu_db.".UpperCase_sly(class_name) 'class_name' from ".$this->lgu_db.".ecao_classification_setup WHERE stat <> 'Cancelled'");
        return response()->json(new JsonResponse($list));
    }

    public function displaycategory()
    {
        $list = DB::select("select * from ".$this->lgu_db.".ecpdc_new_fee_type");
        return response()->json(new JsonResponse($list));
    }

    public function ref(Request $request)
    {
        $pre = 'LC';
        $table = $this->lgu_db . ".tbl_locational_clearance";
        $date = $request->date;
        $refDate = 'locational_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function store(Request $request)
    {
        try {
        // dd($request);
        $idx = $request->main['id'];
        $main = $request->main; 
        $fees = $request->fees;
        $cto = $request->cto;
        $proj = $request->proj;
        $saveproj = $request->saveproj;
    
        if ($idx > 0) {
            $this->update($idx, $main);
        }else{
            $this->save($main,$fees,$cto,$proj,$saveproj);
         }
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function save($main,$fees,$cto, $proj, $saveproject)
    {
            $ctobill = $cto;
            $feesdata = $fees;
            $arraydecision = $main['mode_of_decision'];
            $decision = '';
            foreach ($arraydecision as $decrow) {
                if ($decrow == 'Pick-up') {
                    $decision = 'Pick-up';
                } else if ($decrow == 'Authorized Representative') {
                    $decision = 'Authorized Representative';
                } else if ($decrow == 'By mail, address to') {
                    $decision = 'By mail, address to';
                }
            }
            if ($saveproject == true) {
                    $projectsave = array(
                        'registration_no' => $main['zoning_no'],
                        'registration_date' => $main['locational_date'],
                        'project_name' => $main['project_name'],
                        'location' => $main['business_address'],
                        'project_type' => $main['type_of_entity'],
                        'duration_start' => $main['locational_date'],
                        'duration_end' => $main['locational_date'],
                        'right_overland' => $main['proj_overland'],
                        'project_classification' => $main['classification'],
                        'app_type' => $main['app_type'],
                        'property_id'=> 0,
                        'applicant_id'  => $main['applicant_id_name'],
                        'broker_id' => $main['applicant_id_name'],
                        'brgy_id' => $main['brgy'],
                    );
                    DB::table($this->lgu_db.'.setup_project_registration_main')->insert($projectsave);
                    $proj_id = DB::getPdo()->lastInsertId();
                    
                    $mainsave = array(
                            'zoning_no' =>  $main['zoning_no'],
                            'locational_date' =>  $main['locational_date'],
                            'business_name' =>  $main['business_name'],
                            'business_id_name' =>  $main['business_id_name'],
                            'business_address' =>  $main['business_address'],
                            'business_id_address' => 0,
                            'applicant_name' => $main['applicant_name'],
                            'applicant_id_name' => $main['applicant_id_name'],
                            'applicant_address' => $main['applicant_address'],
                            'applicant_id_address' => 0,
                            'type_of_entity' => $main['type_of_entity'],
                            'telephone_no' => $main['telephone_no'],
                            'TIN_no' => $main['TIN_no'],
                            'type'=> $main['type'],
                            'brgy' => $main['brgy'],
                            'lot_no' => $main['lot_no'],
                            'classification' => $main['classification'],
                            'resolution_no' => $main['resolution_no'],
                            'resolution_date'=> $main['resolution_date'],
                            'ordinance_no' => $main['ordinance_no'],
                            'ordinance_date' => $main['ordinance_date'],
                            'series_of' => $main['series_of'],
                            'certification_purpose' => $main['certification_purpose'],
                            'bns_tdno'=> $main['bns_tdno'],
                            'project_name' => $main['project_name'],
                            'project_id' => $proj_id,
                            'addon'=> $main['addon'],
                            'total_due' => $main['total_due'],
                            'proj_area' => $main['proj_area'],
                            'proj_overland' => $main['proj_overland'],
                            'capitalization' => $main['capitalization'],
                            'representative_ID' => $main['representative_ID'],
                            'representative_Name' => $main['representative_Name'],
                            'repAddress' => $main['repAddress'],
                            'decision' => $main['decision'],
                            'ground_' => $main['ground_'],
                            'mode_of_decision' => $decision,
                            'proj_nature' => $main['proj_nature'],
                            'app_type' => $main['app_type'],
                    );
                    DB::table($this->lgu_db.'.tbl_locational_clearance')->insert($mainsave);
                    
            } else {
                
                $mainsave = array(
                    'zoning_no' =>  $main['zoning_no'],
                    'locational_date' =>  $main['locational_date'],
                    'business_name' =>  $main['business_name'],
                    'business_id_name' =>  $main['business_id_name'],
                    'business_address' =>  $main['business_address'],
                    'business_id_address' => 0,
                    'applicant_name' => $main['applicant_name'],
                    'applicant_id_name' => $main['applicant_id_name'],
                    'applicant_address' => $main['applicant_address'],
                    'applicant_id_address' => 0,
                    'type_of_entity' => $main['type_of_entity'],
                    'telephone_no' => $main['telephone_no'],
                    'TIN_no' => $main['TIN_no'],
                    'type'=> $main['type'],
                    'brgy' => $main['brgy'],
                    'lot_no' => $main['lot_no'],
                    'classification' => $main['classification'],
                    'resolution_no' => $main['resolution_no'],
                    'resolution_date'=> $main['resolution_date'],
                    'ordinance_no' => $main['ordinance_no'],
                    'ordinance_date' => $main['ordinance_date'],
                    'series_of' => $main['series_of'],
                    'certification_purpose' => $main['certification_purpose'],
                    'bns_tdno'=> $main['bns_tdno'],
                    'project_name' => $main['project_name'],
                    'project_id' => $main['project_id'],
                    'addon'=> $main['addon'],
                    'total_due' => $main['total_due'],
                    'proj_area' => $main['proj_area'],
                    'proj_overland' => $main['proj_overland'],
                    'capitalization' => $main['capitalization'],
                    'representative_ID' => $main['representative_ID'],
                    'representative_Name' => $main['representative_Name'],
                    'repAddress' => $main['repAddress'],
                    'decision' => $main['decision'],
                    'ground_' => $main['ground_'],
                    'mode_of_decision' => $decision,
                    'proj_nature' => $main['proj_nature'],
                    'app_type' => $main['app_type'],
                );
                
                DB::table($this->lgu_db.'.tbl_locational_clearance')->insert($mainsave);
               
            }
            $id = DB::getPdo()->lastInsertId();
            foreach ($ctobill as $row) {
                
                if ($row['Include'] == true) {
                    $cto = array(
                        'payer_type' =>'Business',
                        'payer_id' =>$main['applicant_id_name'],
                        'business_application_id' =>$main['business_id_name'],
                        'account_code' =>$row['Account Code'],
                        'bill_description' =>$row['Account Description'],
                        'net_amount' =>$row['Initial Amount'],
                        'bill_amount' =>$row['Fee Amount'],
                        'bill_month' =>$main['locational_date'],
                        'bill_number' =>$main['zoning_no'],
                        'transaction_type' =>'Locational Clearance',
                        'ref_id' =>$id,
                        'bill_id' =>$id,
                        'include_from' =>'Others',
                    );
                    DB::table($this->lgu_db.'.cto_general_billing')->insert($cto);
                }
            }
                foreach($feesdata as $feesrow) {
                    $feessave = array (
                        'locational_id' =>  $id,
                        'Category_id' => $feesrow['Category_id'],
                        'Category' => $feesrow['Category'],
                        'Project_cost' => $feesrow['Project_cost'],
                        'no_of_units' => $feesrow['no_of_units'],
                        'add_fees' => $feesrow['add_fees'],
                        'percent' => $feesrow['percent'],
                        'excess_fees' => $feesrow['excess_fees'],
                        'tot_excess' => $feesrow['tot_excess'],
                        'fee_amount' => $feesrow['fee_amount'],
                        'sub_total' => $feesrow['sub_total'],
                        'add_on' => $feesrow['add_on'],
                        'tot_due' => $feesrow['tot_due'],
                    );
                    DB::table($this->lgu_db.'.tbl_locational_clearance_fees')->insert($feessave);
                }
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));  
    }
    public function editlocational($id) {

        $data['main'] = DB::table($this->lgu_db.'.tbl_locational_clearance')->where('id',$id)->get();
        $data['fees'] = DB::table($this->lgu_db.'.tbl_locational_clearance_fees')->where('locational_id',$id)->get();
        $data['cto'] = DB::table($this->lgu_db.'.cto_general_billing')
        ->select('ref_id as id',
        'payer_type',
        'transaction_type',
        'bill_number',
        'payer_id',
        'business_application_id',
        'account_code',
        'bill_description',
        'net_amount',
        'bill_amount')
    ->where('bill_id', $id) ->get(); 
        return response()->json(new JsonResponse($data));

    }
    public function update($id, $main) 
    {
        DB::table($this->lgu_db.'.tbl_locational_clearance')
            ->where('id',$id)
            ->update($main);
        
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    } 

    public function delete(Request $request)
    {  
        $id=$request->id;
  
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db.'.tbl_locational_clearance')->where('id', $id) ->update($data);
        
        $reason['Form_name'] ='Locational Clearance';
        $reason['Trans_ID'] =$id;
        $reason['Type_'] ='Cancel Record';
        $reason['Trans_by'] =Auth::user()->id;
  
        $this->G->insertReason($reason);
  
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    
    public function printLocCert($id)
    {
        $data = DB::select('call '.$this->lgu_db.'.jen_get_display_locational_typeprint(?)',array($id));
        
        foreach($data as $row) { 
            $appno = $row->{'Locational No'};
            $orno = $row->{'OR No'};
            $datereceived = $row->{'received_date'};
            $dateissue = $row->{'Issued Date'};
            $datereleased = $row->{'release_date'};
            $amntpaid = $row->{'Locational Fee'};
            $appname = $row->{'Applicant Name'};
            $bussname = $row->{'Business Name'};
            $appaddress = $row->{'Applicant Address'};
            $bussaddress = $row->{'Business Address'};
            $repname = $row->{'representative_Name'};
            $repaddress = $row->{'repAddress'};
            $bustype = $row->{'Type'};
            $projarea = $row->{'proj_area'};
            $projloc = $row->{'Business Address'};
            $projnature = $row->{'proj_nature'};
            $capital = $row->{'capitalization'};
        }
        $logo = config('variable.logo');             
        try{
        PDF::SetFont('Helvetica', '', '12
        ');
        $html_content ='
        '.$logo.'
        <h2 align="center" style="line-height: 5px"> HOUSING AND LAND USE REGULATORY BOARD </h2>
        <style>
        table{
            width:100%;
            padding:3px;
        }
        .caption-label{width: 15%
        }
        .caption-label-center{text-align: center;
        }
        .caption-line{width: 35%;
        border-bottom: 1px solid black;
        }          
        </style>
    
        <table width ="100%">
        <br>
        <tr style="height:10px" align="left">
            <td style="width:15%;"> Application No. :</td>
            <td style="width:35%; border-bottom: 1px solid black" align="center">
            '.$appno.'
            </td>  
            <td style="width:15%;"> OR No. :</td>
            <td style="width:35%; border-bottom: 1px solid black" align="center">
            '.$orno.'
            </td>  
        </tr>
        <tr style="height:10px" align="left">   
            <td style="width:15%;"> Date Received :</td>
            <td style="width:35%; border-bottom: 1px solid black" align="center">
                '.$datereceived.'
            </td>  
            <td style="width:15%;"> Date Issue :</td>
            <td style="width:35%; border-bottom: 1px solid black" align="center">
                '.$dateissue.'
            </td>                
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:15%;"> Date Released :</td>
            <td style="width:35%; border-bottom: 1px solid black" align="center">
                '.$datereleased.'
            </td>  
            <td style="width:15%;"> Amount Paid:</td>
            <td style="width:35%; border-bottom: 1px solid black" align="center">
                '.$amntpaid.'
            </td>   
        </tr> 
        <br>
        <tr style="height:10px" align="left">
            <td style="width:100%" align="left">       
            APPLICATION FOR LOCATIONAL CLEARANCE / CERTIFICATE OF ZONING COMPLIANCE
            </td> 
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:50%;"> 1. Name of Applicant</td> 
            <td style="width:50%;"> 2. Name of Corporation</td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:2%;" align="left">
            </td>            
            <td style="width:42%; border-bottom: 1px solid black" align="left">
                '.$appname.'
            </td> 
            <td style="width:7%;" align="left">
            </td> 
            <td style="width:47%; border-bottom: 1px solid black" align="left"> 
                '. $bussname.'
            </td>             
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:50%;"> 3. Address of Applicant</td> 
            <td style="width:50%;"> 4. Address of Corporation</td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:2%;" align="left">
            </td> 
            <td style="width:42%; border-bottom: 1px solid black" align="left">
                '. $appaddress.'
            </td> 
            <td style="width:7%;" align="left">
            </td> 
            <td style="width:47%; border-bottom: 1px solid black" align="left">
                '. $bussaddress.'
            </td>      
        </tr>

        <tr style="height:10px" align="left">
            <td style="width:50%;"> 5. Name of Authorized Representative</td> 
            <td style="width:50%;"> 6. Address of Authorized Representative</td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:2%;" align="left">
            </td> 
            <td style="width:42%; border-bottom: 1px solid black" align="left">
                '. $repname.'
            </td> 
            <td style="width:7%;" align="left">
            </td> 
            <td style="width:47%; border-bottom: 1px solid black" align="left">
                '. $repaddress.'
            </td>      
        </tr>
        <tr style="height:10px" align="left">
            <td style="width:50%;"> 7. Project Type / Business Type</td> 
            <td style="width:50%;"> 8. Project Area (in sq. m.)</td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:2%;" align="left">
            </td> 
            <td style="width:42%; border-bottom: 1px solid black" align="left">
                '. $bustype.'
            </td> 
            <td style="width:7%;" align="left">
            </td> 
            <td style="width:47%; border-bottom: 1px solid black" align="left">
                '. $projarea.'
            </td>     
        </tr>
        <tr style="height:10px" align="left">
            <td style="width:50%;"> 9. Project Location/Business Location</td> 
            <td style="width:50%;"> 10. Project Nature</td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:2%;" align="left">
            </td> 
            <td style="width:42%; border-bottom: 1px solid black" align="left">
                '. $projloc.'
            </td> 
            <td style="width:7%;" align="left">
            </td> 
            <td style="width:47%; border-bottom: 1px solid black" align="left">
                '. $projnature.'
            </td>  
        </tr>
        <tr style="height:10px" align="left">
            <td style="width:18%;"> 11.	Right Over Land</td> 
            <td style="width:30%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Owner</label>
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Lessee</label>
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Others</label>
            </td> 
            <td style="width:2%;"></td> 
            <td style="width:10%;"> 12.	Others</td> 
            <td style="width:38%; border-bottom: 1px solid black" align="left">
            </td>  
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:30%;"> 13.	Existing Land Use or Project Site</td> 
            <td style="width:16%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Commercial</label>
            </td> 
            <td style="width:5%;" align="left">
            </td> 
            <td style="width:18%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Residential</label>
            </td> 
            <td style="width:5%;" align="left">
            </td> 
            <td style="width:15%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Industrial</label>
            </td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:30%;" align="left">
            </td> 
            <td style="width:16%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Agricultural</label>
            </td> 
            <td style="width:5%;" align="left">
            </td> 
            <td style="width:18%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Agro-Industrial</label>
            </td> 
            <td style="width:5%;" align="left">
            </td> 
            <td style="width:15%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Institutional</label>
            </td> 
        </tr>
        <tr style="height:10px" align="left">
            <td style="width:100%;"> 14. Project Cost / Capitalization (In pesos, write in Figures and works)</td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:2%;" align="left">
            </td> 
            <td style="width:98%; border-bottom: 1px solid black" align="left">
                '. $capital.'
            </td> 
        </tr>
        <tr style="height:10px" align="left">
            <td style="width:100%;"> 15. Is the project applied for the sub\ Zoning Administrator to the effect requiring for presentation of Locational Clearance/</td> 
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:3%;" align="left">
            </td> 
            <td style="width:55%;"> Certificate of Zoning Compliance (LC/CBZ) or to apply for LC/CzC? </td> 
            <td style="width:10%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Yes</label>
            </td> 
            <td style="width:3%;" align="left">
            </td> 
            <td style="width:10%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> No</label>
            </td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:3%;" align="left">
            </td> 
            <td style="width:97%;"> If Yes, please answer the following: </td>
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width:6%;" align="left">
            </td> 
            <td style="width:54%;"> 15a. Name of Officer or Zoning Administrator who issued the notice </td>
            <td style="width:39%; border-bottom: 1px solid black" align="left">
            </td>
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width:6%;" align="left">
            </td> 
            <td style="width:17%;"> 15b. Date of notice </td>
            <td style="width:15%; border-bottom: 1px solid black" align="left">
            </td>
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width:6%;" align="left">
            </td> 
            <td style="width:35%;"> 15c. Order / request indicated in the notice </td>
            <td style="width:39%; border-bottom: 1px solid black" align="left">
            </td>
        </tr>
        <tr style="height:10px" align="left">
            <td style="width:100%;"> 16. Is the project applied or the subject of similar application(s) with others offices the hire and/or Deputized Zoning</td> 
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width:3%;" align="left">
            </td> 
            <td style="width:15%;"> Administrator?</td> 
            <td style="width:10%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Yes</label>
            </td> 
            <td style="width:3%;" align="left">
            </td> 
            <td style="width:10%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> No</label>
            </td> 
            <td style="width:56%;"> If Yes, please answer the following: </td>
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width:4%;" align="left">
            </td> 
            <td style="width:54%;"> 16a. Other HLURB offices were similar application(s) was/were filed </td>
            <td style="width:41%; border-bottom: 1px solid black" align="left">
            </td>
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width:100%;"> 17. PREFERRED MODE OF RELEASE OF DECISION. </td>
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width:20%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Pick-up</label>
            </td> 
            <td style="width:26%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Authorize Representative</label>
            </td> 
            <td style="width:30%;" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> By mail, address to</label>
            </td>
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width:50%;"> 18. Signature of Applicant </td>
            <td style="width:50%;"> 19.	Signature of Authorized Representative </td>
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width:4%;" align="left">
            </td> 
            <td style="width:40%; border-bottom: 1px solid black" align="left">
            </td>
            <td style="width:9%;" align="left">
            </td> 
            <td style="width:46%; border-bottom: 1px solid black" align="left">
            </td>
        </tr>
        <br>
        <br>
        <tr style="height:10px" align="center"> 
            <td style="width:25%;" align="left">
            </td> 
            <td style="width:50%; border-bottom: 1px solid black" text-align="center">
            </td>
        </tr>
        <tr style="height:10px" align="center"> 
            <td style="width:25%;" align="left">
            </td> 
            <td style="width:50%; font-size: 12p" text-align="center"> Planning & Dev’t. Coor./Zoning Administrator </td>
        </tr>
        <br>
    </table>';
        PDF::SetTitle('CERTIFICATE');
        PDF::AddPage('P');
        PDF::SetFont('times', '', 10);
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf','F');
        return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg'=> $e, 'status' => 'error']));
        }
    }
    public function printevaluatereport($id)
    {
        $data = DB::select('call '.$this->lgu_db.'.jen_get_display_locational_typeprint(?)',array($id));
        
        foreach($data as $row) { 
            $appno = $row->{'Locational No'};
            $orno = $row->{'OR No'};
            $datereceived = $row->{'received_date'};
            $dateissue = $row->{'Issued Date'};
            $datereleased = $row->{'release_date'};
            $amntpaid = $row->{'Locational Fee'};
            $appname = $row->{'Applicant Name'};
            $bussname = $row->{'Business Name'};
            $appaddress = $row->{'Applicant Address'};
            $bussaddress = $row->{'Business Address'};
            $repname = $row->{'representative_Name'};
            $repaddress = $row->{'repAddress'};
            $bustype = $row->{'Type'};
            $projarea = $row->{'proj_area'};
            $projloc = $row->{'Business Address'};
            $projnature = $row->{'proj_nature'};
            $capital = $row->{'capitalization'};
            $lotno = $row->{'Lot No'};
        }
        
        $logo = config('variable.logo');             
        try{
        PDF::SetFont('Helvetica', '', '12
        ');
        $html_content ='
        '.$logo.'
        <h4 align="center" style="line-height: 10px"> OFFICE OF THE CPDC </h4>
        <h4 style="border-top: 1px solid black"></h4>
        <h2 align="center" style="line-height: 10px"> EVALUATION REPORT </h2>
        <style>
        table{
            width:100%;
            padding:3px;
        }
        .caption-label{width: 15%
        }
        .caption-label-center{text-align: center;
        }
        .caption-line{width: 35%;
        border-bottom: 1px solid black;
        }          
        </style>
    
        <table width ="100%">
        <br>
        <tr style="height:10px" align="left">
            <th style="width:100%;background-color: black; color: white; border-left: 1px solid black; border-right: 1px solid black" align="center"> APPLICATION AND PROJECT INFORMATION </th>
        </tr>
        <tr style="line-height:10px" align="left">   
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Name of Applicant </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                : '. $appname.'
            </td>        
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Address of Applicant </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                : '. $appaddress.'
            </td>    
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Name of Corporation </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                : '. $bussname.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Address of Corporation </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                : '. $bussaddress.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Type of Project </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                : '. $bustype.'
            </td>             
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Location </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                : '. $bussaddress.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Area </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                : '. $projarea.'
            </td>     
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:20%; border-bottom: 1px solid black; border-left: 1px solid black"> Lot Number </td>
            <td style="width:80%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
              : '. $lotno.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <th style="width:100%;background-color: black; color: white; border-left: 1px solid black; border-right: 1px solid black" align="center"> PROJECT EVALUATION </th>    
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:25%; border-bottom: 1px solid black; border-left: 1px solid black; border-right: 1px solid black"> Project Lifespan/Tenure </td>
            <td style="width: 17%; border-bottom: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Permanent</label>
            </td> 
            <td style="width:17%; border-bottom: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Temporary</label>
            </td> 
            <td style="width:21%; border-bottom: 1px solid black" align="left">(specify no. of years)-</td>   
            <td style="width:20%; border-bottom: 1px solid black; border-right: 1px solid black" align="left"> 2020</td>
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <td style="width:25%; border-bottom: 1px solid black; border-left: 1px solid black; border-right: 1px solid black"> Project Significance </td>
            <td style="width: 17%; border-bottom: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Local</label>
            </td> 
            <td style="width:58%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> National</label>
            </td>   
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:25%; border-bottom: 1px solid black; border-left: 1px solid black; border-right: 1px solid black"> Project Classification </td>
            <td style="width:75%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
            </td>  
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <td style="width:25%; border-bottom: 1px solid black; border-left: 1px solid black; border-right: 1px solid black"> Site Zoning Classification </td>
            <td style="width:75%; border-bottom: 1px solid black; border-right: 1px solid black" align="left">
            </td> 
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:100%; border-bottom: 1px solid black; border-left: 1px solid black; border-right: 1px solid black"><b> Existing land uses in the Vicinity </b></td> 
        </tr> 
        <tr style="line-height:9px" align="left">
            <td style="width: 45%; border-left: 1px solid black; border-right: 1px solid black">
                a. Radius covered from lot boundaries of project site 
            </td> 
            <td style="width: 55%; border-left: 1px solid black; border-right: 1px solid black">
                b. Indicate land Uses within radius and corresponding percentage
            </td> 
        </tr> 
        <tr style="line-height:9px" align="left"> 
            <td style="width: 45%; border-right: 1px solid black; border-left: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> 100 meters (log significance)</label>
            </td> 
            <td style="width: 55%; border-right: 1px solid black" align="left"></td> 
        </tr>
        <tr style="line-height:9px" align="left">
            <td style="width: 45%; border-bottom: 1px solid black; border-right: 1px solid black; border-left: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> 1 kilometers (national significance)</label>
            </td> 
            <td style="width: 55%; border-bottom: 1px solid black; border-right: 1px solid black" align="left"></td>  
        </tr> 
        <tr style="height:10px" align="left"> 
            <th style="width:100%;background-color: black; color: white; border-left: 1px solid black; border-right: 1px solid black" align="center"> LEGAL BASIS FOR EVALUATION AND RECOMMENDATION DECISION </th>
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width: 20%; border-left: 1px solid black; border-right: 1px solid black" align="left"> Legal Basis</td>
            <td style="width: 18%;" align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Ordinance No.</label>
            </td> 
            <td style="width: 20%; border-bottom: 1px solid black;" align="left"> </td> 
            <td style="width: 4%;" align="left"> ,S.</td>  
            <td style="width: 18%; border-bottom: 1px solid black;" align="left"> </td> 
            <td style="width: 12%;" align="left"> approved per</td> 
            <td style="width: 6%; border-bottom: 1px solid black;" align="left"> </td> 
            <td style="width: 2%; border-right: 1px solid black;" align="left"> </td> 
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <td style="width: 20%; border-left: 1px solid black; border-right: 1px solid black" align="left"> </td>
            <td style="width: 18%;" align="left"> HRSC Res. No. </td> 
            <td style="width: 20%; border-bottom: 1px solid black;" align="left"> </td> 
            <td style="width: 4%;" align="left"> ,S.</td>  
            <td style="width: 18%; border-bottom: 1px solid black;" align="left"> </td> 
            <td style="width: 5%;" align="left"> 200</td> 
            <td style="width: 10%; border-bottom: 1px solid black;" align="left"> </td> 
            <td style="width: 5%; border-right: 1px solid black;" align="left"> </td> 
        </tr>
        <tr style="line-height:10px" align="left"> 
            <td style="width: 20%; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black" align="left"> </td>
            <td style="width: 80%; border-right: 1px solid black; border-bottom: 1px solid black" align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Others (specify law implementing rules, standard of guidelines)</label>
            </td> 
        </tr>
        <tr style="line-height:10px" align="left"> 
            <td style="width: 20%; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black" align="left"> Finding and Evaluation of facts </td>
            <td style="width: 80%; border-right: 1px solid black; border-bottom: 1px solid black" align="left"></td> 
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width: 20%; font-size:7px; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black" align="left"><b> DECISION RECOMMENDED </b></td>
            <td style="width: 80%; border-right: 1px solid black; border-bottom: 1px solid black" align="left"></td> 
        </tr> 
        <tr style="height:10px" align="left">
            <td style="width: 50%; border-left: 1px solid black; border-bottom: 1px solid black" align="left"> SITE INSPECTION FINDINGS (fill-up if site was inspected)</td>
            <td style="width: 50%; border-right: 1px solid black; border-bottom: 1px solid black" align="left"></td> 
        </tr> 
        <tr style="height:10px" align="left"> 
            <td style="width: 20%; border-left: 1px solid black; border-bottom: 1px solid black" align="left"> Date of Inspection :</td>
            <td style="width: 20%; border-bottom: 1px solid black" align="left"> </td>
            <td style="width: 20%; border-bottom: 1px solid black" align="left"> Name of Inspector :</td>
            <td style="width: 40%; border-right: 1px solid black; border-bottom: 1px solid black" align="left"></td> 
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width: 40%; border-left: 1px solid black; border-bottom: 1px solid black" align="left"> Project Status as of Inspection Date :</td>
            <td style="width: 60%; border-bottom: 1px solid black; border-right: 1px solid black" align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Proposed</label>
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Completed</label>
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Operational</label>
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Others</label>
            </td>
            
        </tr>
        <tr style="height:10px" align="left"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left"> Are information provided by applicant true?</td>
            <td style="width: 50%; border-right: 1px solid black" align="left"> Existing Land Uses abutting lot boundaries of project </td>
        </tr>
        <tr style="line-height:9px" align="left"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> Yes</label>
            </td>
            <td style="width: 10%;" align="left"> North </td>
            <td style="width: 15%;" align="left"> </td>
            <td style="width: 10%;" align="left"> East </td>
            <td style="width: 15%; border-right: 1px solid black" align="left"> </td>
        </tr>
        <tr style="line-height:9px" align="left"> 
            <td style="width: 30%; border-left: 1px solid black; border-bottom: 1px solid black" align="left">
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1"> No (specify findings if no)</label>
            </td>
            <td style="width: 20%; border-right: 1px solid black; border-bottom: 1px solid black" align="left">  </td>
            <td style="width: 10%; border-bottom: 1px solid black" align="left"> South </td>
            <td style="width: 15%; border-bottom: 1px solid black" align="left"> </td>
            <td style="width: 10%; border-bottom: 1px solid black" align="left"> West </td>
            <td style="width: 15%; border-bottom: 1px solid black; border-right: 1px solid black" align="left"> </td>
        </tr>
        <tr style="line-height:10px" align="center"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left"> Land uses and distances of surrounding properties from lot </td>
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left"> Existing land uses within lot boundaries of project: </td>
        
        </tr>
        <tr style="line-height:10px" align="center"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left"> boundary of project within the prescribed distance requirements </td>
            <td style="width: 33%; " align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1">a) Land uses in case of Agricultural</label>
            </td>
            <td style="width: 15%; border-bottom: 1px solid black;" align="left"></td>
            <td style="width: 2%; border-right: 1px solid black" align="left"></td>
        </tr>
        <tr style="line-height:10px" align="center"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left"> provided in laws, implementing rules/standards/guidelines (fill </td>
            <td style="width: 20%; " align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1">b) Specify Crop</label>
            </td>
            <td style="width: 28%; border-bottom: 1px solid black;" align="left"></td>
            <td style="width: 2%; border-right: 1px solid black" align="left"></td>
        </tr>
        <tr style="line-height:10px" align="center"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left"> -up if applicable). </td>
            <td style="width: 25%; " align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1">c) Indicate tenancy status:</label>
            </td>
            <td style="width: 23%; border-bottom: 1px solid black;" align="left"></td>
            <td style="width: 2%; border-right: 1px solid black" align="left"></td>
        </tr>
        <tr style="line-height:10px" align="center"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black" align="left"></td>
            <td style="width: 25%;" align="left"></td>
            <td style="width: 23%;" align="left"></td>
            <td style="width: 2%; border-right: 1px solid black" align="left"></td>
        </tr>
        <tr style="line-height:10px" align="center"> 
            <td style="width: 50%; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black" align="left"> Land Uses: Distance in meters from project boundary </td>
            <td style="width: 15%; border-bottom: 1px solid black;" align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1">Tenanted </label>
            </td>
            <td style="width: 35%; border-bottom: 1px solid black; border-right: 1px solid black" align="left"> 
                <input type="checkbox" id="0" name="0" value="0">
                <label for="vehicle1">Not Tenanted </label>
            </td>
        </tr>
        <br>
    </table>';
        
        PDF::SetTitle('CERTIFICATE');
        PDF::AddPage('P');
        PDF::SetFont('times', '', 10);
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf','F');
        return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg'=> $e, 'status' => 'error']));
        }
    }
    public function decisionzoning($id)
    {
        $data = DB::select('call '.$this->lgu_db.'.jen_get_display_locational_typeprint(?)',array($id));
        
        foreach($data as $row) { 
            $appno = $row->{'Locational No'};
            $orno = $row->{'OR No'};
            $datereceived = $row->{'received_date'};
            $dateissue = $row->{'Issued Date'};
            $datereleased = $row->{'release_date'};
            $amntpaid = $row->{'Locational Fee'};
            $appname = $row->{'Applicant Name'};
            $bussname = $row->{'Business Name'};
            $appaddress = $row->{'Applicant Address'};
            $bussaddress = $row->{'Business Address'};
            $repname = $row->{'representative_Name'};
            $repaddress = $row->{'repAddress'};
            $bustype = $row->{'Type'};
            $projarea = $row->{'proj_area'};
            $projloc = $row->{'Business Address'};
            $projnature = $row->{'proj_nature'};
            $capital = $row->{'capitalization'};
            $decision = $row->{'decision'};
            $decisionno = $row->{'decision_no'};
            $ground = $row->{'ground_'};
            $ordate = $row->{'OR Date'};
            $head = $row->{'head_name'};
            $headpos = $row->{'head_position'};
        }
        
        $logo = config('variable.logo');             
        try{
        PDF::SetFont('Helvetica', '', '12
        ');
        $html_content ='
        '.$logo.'
        <h4 align="center" style="line-height: 10px"> OFFICE OF THE CPDC </h4>
        <h4 style="border-top: 1px solid black"></h4>
        <h2 align="center" style="line-height: 15px"> DECISION ON ZONING </h2>
        <style>
        table{
            width:100%;
            padding:3px;
        }
        .caption-label{width: 15%
        }
        .caption-label-center{text-align: center;
        }
        .caption-line{width: 35%;
        border-bottom: 1px solid black;
        }          
        </style>
    
        <table width ="100%">
        <br>
        <tr style="line-height:10px" align="left">   
            <td style="width:18%;" align="left"> Application No. </td>
            <td style="width:28%; border-bottom: 1px solid black" align="left">
                '.$appno.'
            </td> 
            <td style="width:8%;"></td>
            <td style="width:18%;"> Decision No. </td>
            <td style="width:28%; border-bottom: 1px solid black" align="left">
                '.$decisionno.'
            </td>        
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:18%;" align="left"> Date Received </td>
            <td style="width:28%; border-bottom: 1px solid black" align="left">
                '.$datereceived.'
            </td> 
            <td style="width:8%;"></td>
            <td style="width:18%;"> Date Issued </td>
            <td style="width:28%; border-bottom: 1px solid black" align="left">
                '.$dateissue.'
            </td>                
        </tr> 
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:25%;"> * Name of Applicant </td>
            <td style="width:75%; border-bottom: 1px solid black" align="left">
                '. $appname.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:25%;"> * Address of Applicant </td>
            <td style="width:75%; border-bottom: 1px solid black" align="left">
                '.$appaddress.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:25%;"> * Name of Corporation </td>
            <td style="width:75%; border-bottom: 1px solid black;" align="left">
                '.$bussname.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:25%;"> * Address of Corporation </td>
            <td style="width:75%; border-bottom: 1px solid black;" align="left">
                '.$bussaddress.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <td style="width:25%;"> * Type of Project </td>
            <td style="width:75%; border-bottom: 1px solid black;" align="left">
                '.$bustype.'
            </td>             
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:25%;"> * Location </td>
            <td style="width:75%; border-bottom: 1px solid black;" align="left">
                '.$bussaddress.'
            </td> 
        </tr> 
        <tr style="line-height:10px" align="left"> 
            <td style="width:25%;"> * Area </td>
            <td style="width:75%; border-bottom: 1px solid black;" align="left">
                '.$projarea.'
            </td>     
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:25%;"> * Decision </td>
            <td style="width:75%; border-bottom: 1px solid black;" align="left">
              '.$decision.'
            </td> 
        </tr> 
        <tr style="line-height: 8px" align="left">
            <td style="width:50%;"> * Ground for Denied</td>
        </tr> 
        <tr style="line-height:8px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:24%;"> Application/Remarks</td>
            <td style="width:75%; border-bottom: 1px solid black;" align="left">
                '.$ground.'
            </td> 
        </tr> 
        <br>
        <tr style="line-height:10px" align="left">
            <td style="width:50%;" align="left"> I. CONDITION </td>
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> a) All Conditions stipulated herein form part of this decision and are subject to monitoring. </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> b) Non-compliance therewith shall be cause for cancellation of legal action. </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> c) The application requirements of government agencies and applicable provisions of existing laws shall be complied with. </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> d) No activity other than applied for shall be conducted within the project site. </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> e) No major expansion, alteration and/or improvement shall be introduced without prior clearance from this office. </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> f) This decision shall not be construed as a certification of HLURB as to the ownership by the applicant of the parcel of land </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:3%;"> </td>
            <td style="width:97%;"> subject of the decision. </td> 
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> g) Any misrepresentation, false statement or allegations material to the issuance of this decision shall be sufficient cause for the </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:3%;"> </td>
            <td style="width:97%;"> revocation of this decision. </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:1%;"> </td>
            <td style="width:99%;"> h)	Additional Conditions: </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:3%;"> </td>
            <td style="width:97%;"> o  Provisions as to setbacks, yard requirements, bulk, assessments, area height and other restrictions shall strictly conform  </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:5%;"> </td>
            <td style="width:95%;"> with the requirements for the National Building Code and other related laws.  </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:3%;"> </td>
            <td style="width:97%;"> o  This decision shall be considered automatically revoked if project is not commenced within one (1) year from date of   </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width:5%;"> </td>
            <td style="width:95%;"> issue of this decision. </td> 
        </tr> 
        <tr style="line-height:10px" align="left">
            <td style="width: 3%;"> </td>
            <td style="width: 28%;"> o  For other condition, please state:  </td> 
            <td style="width: 69%; border-bottom: 1px solid black;" align="left"></td>  
        </tr> 
        <br>
        <br>
         <tr style="line-height:10px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
            '.$head.'
            </td>                             
        </tr>
        <tr style="line-height:10px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
            '.$headpos.'
            </td>                             
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width: 20%;"> OR No. :  </td> 
            <td style="width: 18%; border-bottom: 1px solid black;" align="left">'.$orno.'</td>  
        </tr>  
        <tr style="line-height:10px" align="left">
            <td style="width: 20%"> Amount Paid :  </td> 
            <td style="width: 18%; border-bottom: 1px solid black" align="left">'.$amntpaid.'</td>  
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width: 20%"> Date Paid :  </td> 
            <td style="width: 18%; border-bottom: 1px solid black" align="left">'.$ordate.'</td>  
        </tr>
        <tr style="line-height:10px" align="left">
            <td style="width: 40%" align="left"> </td>  
        </tr>
        <br>
    </table>';
        
        PDF::SetTitle('CERTIFICATE');
        PDF::AddPage('P');
        PDF::SetFont('times', '', 10);
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf','F');
        return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg'=> $e, 'status' => 'error']));
        }
    }

    public function printlocationallist(Request $request)
    {
        $data = $request->main;
        $filter = $request->filter;
        $filterdisplay = '';
        
        if ($filter['filter'] == 'Month') {
            $filterdisplay = 'Month of ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Daily') {   
            $filterdisplay = 'As of ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Year') {   
            $filterdisplay = 'Year ' . $filter['reportcaption'];
        } else if ($filter['filter'] == 'Range') {   
            $filterdisplay = 'As of ' . $filter['reportcaption'];
        }
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '9');
            $html_content = '
            ' . $logo . '
            <h2 align="center">LOCATIONAL CLEARANCE LIST</h2>
            <h4 align="center"> '. $filterdisplay .'</h4>
            <br></br>
            <br></br>
            <br></br>
        <table border="1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 12%;text-align:center;vertical-align:middle;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">    
                <br><br>
                Reference Number
                <br>
            </th>
            <th rowspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Application Date
                <br>
            </th>
            <th rowspan="2" style="width: 15%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;">
                <br><br> 
                Applicant Name
                <br>
            </th> 
            <th colspan="2" style="width: 18%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                 Address
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                   Classification
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                TD No
                <br>
            </th> 
            <th colspan="2" style="width: 13%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Certification Purpose
                <br>
            </th> 
            <th colspan="2" style="width: 10%;text-align:center;border: 1px solid black;color:#fcfffd;background-color:#2c2e2e;"> 
                <br><br>
                    Locational Fee
                <br>
            </th> 
           
        </tr>
    </thead>
                    <tbody >';
                  
            foreach ($data as $row) {
                $html_content .= '
                <tr style="font-family: Arial, font-size: 8pt" align="center">
                <td width="12%"> ' . $row['Locational No'] . '</td>
                <td width="10%"> ' . $row['Trans Date'] . '</td>
                <td width="15%" align="left"> ' . $row['Applicant Name'] . '</td>
                <td width="18%" align="left">' . $row['Applicant Address'] . '</td>
                <td width="10%">' . $row['Classification'] . '</td>
                <td width="10%" align="left"> ' . $row['Lot No'] . '</td>
                <td width="13%">' . $row['Certification Purpose'] . '</td>
                <td width="10%">' . $row['Locational Fee'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
            </table>';
            
            PDF::SetTitle('Print Master List');
            PDF::Addpage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
