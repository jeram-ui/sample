<?php

namespace App\Http\Controllers\Api\OBO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;


class  AnnualInspController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    protected $G;
    private $general;

    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }

    public function delete($id)
    {


        $data['application_status'] = 'CANCELLED';
        DB::table($this->lgu_db . '.eceo_annual_inspection_report')->where('annual_inspection_no', $id)->update($data);

        $reason['Form_name'] = 'Annual Inspection';

        $reason['Trans_ID'] = $id;
        $reason['Type_'] = 'Cancel Record';
        $reason['Trans_by'] = Auth::user()->id;

        $this->G->insertReason($reason);

        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function edit($id)
    {
        $data = db::table($this->lgu_db . '.eceo_annual_inspection_report')
            ->join($this->lgu_db . '.ebplo_business_application', 'ebplo_business_application.business_app_id', '=', 'eceo_annual_inspection_report.bus_app_id')
            ->select('*', db::raw($this->lgu_db . '.eceo_get_business_owner(ebplo_business_application.business_number,"Company") as bnsOwner'))
            ->where('annual_inspection_no', $id)->get();

        return response()->json(new JsonResponse($data));
    }
    public function show(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $item  = db::select("CALL " . $this->lgu_db . ".jay_ebplo_display_annual_inspection_List('" . $from . "','" . $to . "','Annual Inspection')");
        return response()->json(new JsonResponse($item));
    }

    public function getchar_of_occupancy()
    {

        $type = db::select("SELECT `class_id`,`category_type` FROM " . $this->lgu_db . ".eceo_use_or_occupancy WHERE category_type <> '' GROUP BY category_type ORDER BY category_type ASC;");
        return response()->json(new JsonResponse($type));
    }

    public function getoccu_class($id)
    {

        $type = db::select("SELECT id,concat(`category_id`,' ',`category_group`,'. ',`category_label`,') ',category_use)'category_use'   FROM " . $this->lgu_db . ".eceo_use_or_occupancy WHERE category_type <> '' AND class_id = '$id'");
        return response()->json(new JsonResponse($type));
    }

    public function employeeIssuance($id)
    {
        $list = DB::select('call ' . $this->lgu_db . '.jay_display_declared_employees(?)', array($id));
        return response()->json(new JsonResponse($list));
    }
    public function ref(Request $request)
    {
        $pre = 'AI';
        $table = $this->lgu_db . ".eceo_annual_inspection_report";
        $date = $request->date;
        $refDate = 'annual_inspection_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }

    public function store(Request $Request)
    {
        try {
            DB::beginTransaction();
            $main1 = $Request->main;
            $fees = $Request->fees;
            $idx = $main1['annual_inspection_no'];

            if ($idx > 0) {
                $this->update($idx, $main1);
            } else {
                $this->save($main1, $fees);
            };

            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function save($main, $fee)
    {
        // dd($Request);
        $main2 = array(
            'annual_inspection_code' => $main['OBO_number'],
            'bus_app_id' => $main['bappID'],
            'classification_id' => $main['classification_id'],
            'char_of_occupancy_id' => $main['char_of_occupancy_id'],
            'recommendations' => $main['recommendation'],
            'annual_inspection_date' => $main['application_date'],
            'inspection_date' => $main['inspection_date'],
            // 'projectid' => $main1['issued_date'],
            'projectname' => $main['projectName'],

        );
        DB::table($this->lgu_db . '.eceo_annual_inspection_report')->insert($main2);
        $id = DB::getPDo()->lastInsertId();

        foreach ($fee as $row) {
            if ($row['Include'] == 'True') {
                $billing = array(
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'payer_type' => "BUSINESS",
                    'transaction_type' => "Annual Inspection",
                    'bill_number' => $main['OBO_number'],
                    'payer_id' => $main['business_number'],
                    'business_application_id' => $main['bappID'],
                    'account_code' => $row['Account Code'],
                    'bill_description' => $row['Account Description'],
                    'net_amount' => $row['Initial Amount'],
                    'bill_amount' => $row['Fee Amount'],

                );
                DB::table($this->lgu_db . '.cto_general_billing')->insert($billing);
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Saved', 'status' => 'success']));
    }

    public function update($id, $main)
    {
        // dd($Request);
        $main2 = array(
            'annual_inspection_code' => $main['OBO_number'],
            'bus_app_id' => $main['bappID'],
            'classification_id' => $main['classification_id'],
            'char_of_occupancy_id' => $main['char_of_occupancy_id'],
            'recommendations' => $main['recommendation'],
            'annual_inspection_date' => $main['application_date'],
            'inspection_date' => $main['inspection_date'],
            'projectname' => $main['projectName'],

        );

        DB::table($this->lgu_db . '.eceo_annual_inspection_report')->where('annual_inspection_no', $id)->update($main2);

        return response()->json(new JsonResponse(['Message' => 'Updated', 'status' => 'success']));
    }
    public function print(Request $Request)
    {
        // $logo = config('variable.logo');
        // dd($Request['annual_inspection_no']);
        // dd($Request);
        // $pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
        //$pdf->setPageOrientation('L');
        pdf::SetTitle('Annual Inspection Certificate');
        pdf::SetHeaderMargin(30);
        pdf::SetMargins(5, 5, 10);
        pdf::setFooterMargin(20);
        pdf::SetAutoPageBreak(false);
        pdf::SetAuthor('Author');
        pdf::SetDisplayMode('real', 'default');
        pdf::SetPrintFooter(false);
        pdf::SetPrintHeader(false);

        pdf::AddPage('L', array(216, 330));
        pdf::SetFont('', '', 8.5);
        $Template = '
               
        <table> 
               
            <tr>
                <th style="width:46%" border="4">
                    <font face="Courier New">
                    <table style="width:100%;" > 
                        <tr><th></th></tr>
                        <tr> 
                            <th align="left"><small>NBC FORM NO. B-19</small></th>
                        </tr>
                        <tr>
                            <th align="center"><font size="12"><b>CERTIFICATE OF ANNUAL INSPECTION</b></font></th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:35%;"></th>
                            <th align="center" style="width:30%; border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:35%;"></th>
                            <th align="center">Date Submitted</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:100%;">
                                <table cellspacing="5">
                                    <tr>
                                        <th style="width:1%;"></th>
                                        <th style="width:4%;" border="1"></th>
                                        <th style="width:0.5%;"></th>
                                        <th align="left" style="width:50%;">CERTIFICATE OF COMPLETION</th> 
                                        <th style="width:1%;"></th>
                                        <th style="width:4%;" border="1"></th>
                                        <th style="width:0.5%;"></th>
                                        <th align="left" style="width:50%;">AS-BUILT PLANS/SPECIFICATION</th> 
                                    </tr> 
                                    <tr>
                                        <th style="width:1%;"></th>
                                        <th style="width:4%;" border="1"></th>
                                        <th style="width:1%;"></th>
                                        <th style="width:52%;">DAILY CONSTRUCTION WORKS LOGBOOK</th> 
                                        
                                        <th style="width:4%;" border="1"></th>
                                        <th style="width:1%;"></th>
                                        <th style="width:10%;">(Specify)</th> 
                                        <th align="center" style="width:25%; border-bottom: 1px solid black"></th>
                                    </tr> 
                                </table>
                            </th>
                        </tr>  
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;border-bottom: 1px solid black">' . $Request['Character of Occupancy'] . '</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:46%;border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;">CHARACTER OF OCCUPANCY</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;">GROUP</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;">
                                A CERTIFICATE DULY SIGNED AND SEALED FROM A DULY LICENSED ARCHITECT / CIVIL ENGINEER PROFESSIONAL ELECTRICAL ENGINEER/ELECTRONICS ENGINEER/   PROFESSIONAL MECHANICAL ENGINEER, MASTER PLUMBER AND SANITARY ENGINEER HIRED BY THE OWNER WAS SUBMITTED AND WHO UNDERTOOK THE ANNUAL INSPECTION THAT THE BUILDING / STRUCTURE IS ARCHITECTURALLY   PRESENTABLE, STRUCTURALLY  SAFE. THE ELECTRICAL / ELECTRONIC / MECHANICAL/ PLUMBING/ SANITARY INSTALLATION ARE IN ORDER:
                                </span></th>
                            <th style="width:2%;"></th>
                        </tr>
                        <tr>
                        <br>
                            <th align="center" style="width:100%;">VERIFIED AND COMPLIED AS TO THE FOLLOWING REQUIREMENTS</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:5%;"></th>
                            <th align="center" style="width:25%;">LOCATIONAL/ZONING OF LAND USE</th>
                            <th style="width:6%;"></th>
                            <th align="center" style="width:31%;">LINE AND GRADE (GEODETIC)</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">ARCHITECTURAL</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">CIVIL/STRUCTURAL</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">ELECTRICAL</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">MECHANICAL</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">SANITARY</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">PLUMBING</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">ELECTRONICS</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">INTERIOR DESIGN</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">ACCESSIBILITY</th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:31%;">FIRE SAFETY</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:35%;"></th>
                            <th align="center" style="width:31%; border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:35%;"></th>
                            <th align="center">OTHERS (SPECIFY)</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;">
                                THE CONSTRUCTION/ERECTION OF THE BUILDING/STRUCTURE COVERED BY BUILDING PERMIT NO <u>' . $Request['Building Permit No'] . '</u> SIGN PERMIT NO <u></u> HAS BEEN COMPLETED, FINALLY INSPECTED AND THE REQUIREMENTS REVIEWED AND FOUND SUBSTANTIALLY SATISFACTORY COMPLIED. THEREFORE THE <b>"CERTIFICATE OF ANNUAL INSPECTION"</b> IS HEREBY RECOMMENDED FOR ISSUANCE.
                                </span></th>
                            <th style="width:2%;"></th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;border-bottom: 1px solid black"></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;border-bottom: 1px solid black"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;"><b>CHIEF INSPECTION &  ENFORCEMENT DIVISION</b></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;"><b>CHIEF PROCESSING &  ENFORCEMENT DIVISION</b></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;"><small>(SIGNATURE OVER PRINTED NAME)</small></th>
                            <th style="width:2%;"></th>
                            <th align="center" style="width:48%;"><small>(SIGNATURE OVER PRINTED NAME)</small></th>
                        </tr> 
                        <br>
                        <tr> 
                            <th style="width:7%;"></th>
                            <th style="width:10%;">DATE</th> 
                            <th align="center" style="width:26%;border-bottom: 1px solid black"></th>
                            <th style="width:14%;"></th>
                            <th style="width:10%;">DATE</th> 
                            <th align="center" style="width:26%;border-bottom: 1px solid black"></th>
                        </tr> 
                        <tr>  
                            <th style="width:2%;"></th> 
                        </tr> 
                    </table> 
                    </font>
                </th>
                <th style="width:1%"></th>
                <th style="width:54%" border="4">
                    <table style="width:100%;">
                        <br><br>
                        <tr>
                            <th align="center">REPUBLIC OF THE PHILIPPINES </th>
                        </tr> 
                        <tr>
                            <th align="center">Municipality</th>
                        </tr> 
                        <tr>
                            <th align="center">PROVINCE OF </th>
                        </tr> 
                        <br>
                        <tr>
                            <th align="center"><font size="10">OFFICE OF THE BUILDING OFFICIAL </font></th>
                        </tr>
                        <br>
                        <tr>
                            <th align="center"><font size="14">CERTIFICATE OF ANNUAL INSPECTION </font></th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:30%;"></th>
                            <th style="width:15%;">CFEI</th>
                            <th style="width:3%;"></th>
                            <th align="left" style="width:20%;border-bottom: 1px solid black">' . $Request['Reference No'] . '</th>
                        </tr>
                        <tr>
                            <th style="width:30%;"></th>
                            <th style="width:15%;">FEE PAID</th>
                            <th style="width:3%;"></th>
                            <th align="left" style="width:20%;border-bottom: 1px solid black">' . $Request['Inspection Fee'] . '</th>
                        </tr>
                        <tr>
                            <th style="width:30%;"></th>
                            <th style="width:15%;">OR NO</th>
                            <th style="width:3%;"></th>
                            <th align="left" style="width:20%;border-bottom: 1px solid black">' . $Request['OR No'] . '</th>
                        </tr>
                        <tr>
                            <th style="width:30%;"></th>
                            <th style="width:15%;">DATE PAID</th>
                            <th style="width:3%;"></th>
                            <th align="left" style="width:20%;border-bottom: 1px solid black">' . $Request['OR Date'] . '</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:70%;"></th> 
                            <th align="center" style="width:28%;border-bottom: 1px solid black">' . $Request['Issued Date'] . '</th>
                        </tr>
                        <tr>
                            <th style="width:70%;"></th>
                            <th align="center" style="width:28%;">DATE ISSUED</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;">
                            THIS <b>CERTIFICATE OF ANNUAL  INSPECTION</b> IS  ISSUED /GRANTED  PURSUANT  TO  PERTINENT    
                            PROVISION OF THE NATIONAL BUILDING CODE (PD 1096) AND ITS IMPLEMENTING RULES AND REGULATIONS.
                            </span></th>
                            <th style="width:2%;"></th>
                        </tr>
                        <br>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="left" style="width:32%;">NAME OF OWNER/LESSEE</th>
                            <th style="width:2%;"></th> 
                            <th style="width:59%;border-bottom: 1px solid black">' . $Request['Owner'] . '</th>
                            <th style="width:2%;"></th> 
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="left" style="width:32%;">NAME OF PROJECT</th>
                            <th style="width:2%;"></th> 
                            <th style="width:59%;border-bottom: 1px solid black">' . $Request['projectname'] . '</th>
                            <th style="width:2%;"></th> 
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="left" style="width:32%;">CHARACTER OF OCCUPANCY</th>
                            <th style="width:2%;"></th> 
                            <th style="width:30%;border-bottom: 1px solid black">' . $Request['Character of Occupancy'] . '</th>
                            <th style="width:2%;"></th> 
                            <th align="left" style="width:10%;">GROUP</th>
                            <th style="width:2%;"></th> 
                            <th style="width:15%;border-bottom: 1px solid black">' . $Request['Classification'] . '</th>
                            <th style="width:2%;"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="left" style="width:32%;">LOCATED/ERECTED AT/ALONG</th>
                            <th style="width:2%;"></th> 
                            <th style="width:59%;border-bottom: 1px solid black">' . $Request['Business Address'] . '</th>
                            <th style="width:2%;"></th> 
                        </tr>
                        <br> 
                        <br> 
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;"><font size="8.7">
                            THE   OWNER / LESSEE   SHALL   PROPERLY MAINTAIN   THE   BUILDING / STRUCTURE   TO   ENHANCE ARCHITECTURAL WELL-BEING, STRUCTURAL STABILITY, ELECTRICAL, MECHANICAL SANITATION, PLUMBING, ELECTRONICS INTERIOR DESIGN AND FIRE-PROTECTIVE PROPERTIES AND SHALL NOT BE OCCUPIED OR USED FOR PURPOSES OTHER THAN ITS INTENDED USE AS STATED ABOVE.
                            </font></span></th>
                            <th style="width:2%;"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;"><font size="8.7">
                            NO ALTERATION / ADDITION / REPAIRS / NEW ELECTRICAL / ELECTRONICS AND / OR MECHANICAL /   PLUMBING / SANITARY INSTALLATION SHALL BE MADE THEREON WITHOUT A PERMIT THEREFORE.
                            </font></span></th>
                            <th style="width:2%;"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;"><font size="8.7">
                            THE ARCHITECT OR ENGINEER WHO DREW UP THE PLANS AND SPECIFICATION FOR THE BUILDING/STRUCTURE IS AWARE THAT UNDER ARTICLE 1723 OF THE CIVIL CODE OF THE PHILIPPINES HE IS RESPONSIBLE FOR DAMAGES IF WITHIN FIFTEEN (15) YEARS FROM THE COMPLETION OF THE STRUCTURE. THE SAME SHOULD COLLAPSE DUE TO DEFECT IN THE PLANS OR SPECIFICATION OR DEFECTS IN THE GROUND. HE IS THEREFORE ENJOINED TO CONDUCT ANNUAL INSPECTION OF THE STRUCTURE TO ENSURE THAT THE CONDITIONS UNDER WHICH THE STRUCTURE WAS DESIGNED ARE NOT BEING VIOLATED OR ABUSED.
                            </font></span></th>
                            <th style="width:2%;"></th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;"><font size="8.7">
                            A CERTIFIED COPY  OF  HEREOF SHALL  BE  POSTED  WITHIN  THE  PREMISES OF THE BUILDING AND SHALL NOT BE REMOVED WITHOUT AUTHORITY FROM THE BUILDING OFFICIAL.
                            </font></span></th>
                            <th style="width:2%;"></th>
                        </tr>
                        <br>
                        <br>
                        <tr>
                        <th style="width:50%;"></th> 
                            <th align="center" style="width:45%;border-bottom: 1px solid black">' . $Request['head_name'] . '</th>
                        </tr>
                        <tr>
                            <th style="width:50%;"></th>
                            <th align="center" style="width:45%;">BUILDING OFFICIAL</th>
                        </tr>
                        <tr>
                            <th style="width:50%;"></th>
                            <th align="center" style="width:45%;"><font size="8.5">(SIGNATURE OVER PRINTED NAME)</FONT></th>
                        </tr>
                        <tr>
                            <th style="width:60%;"></th>
                            <th style="width:12%;">Date Issued</th>
                            <th style="width:1%;"></th>
                            <th align="center" style="width:15%;border-bottom: 1px solid black">' . $Request['Issued Date'] . '</th>
                        </tr>
                        <br>
                        <tr>
                            <th style="width:2%;"></th>
                            <th align="Left" style="width:20%;">NOTE:</th>
                        </tr>
                        <tr>
                            <th style="width:2%;"></th>
                            <th style="width:96%;"><span style="text-align:justify;"><font size="8.7">
                            THE OWNER/OCCUPANT OF THE BUILDING UNDER THE CHARACTER OF OCCUPANCY, GROUP B TO J SHALL NOTIFY IN WRITING THE OFFICE  OF  THE  BUILDING  OFFICIAL  FOR  THE  GRANTING/ISSUANCE  OF  AN ANNUAL INSPECTION CERTIFICATE AFTER ONE (1) YEAR FROM THE DATE OF THE ISSUANCE OF THIS CERTIFICATE AND YEARLY THEREAFTER.
                            </font></span></th>    
                        </tr>
                    </table>
                </th>
            </tr>
        </table>
        ';
        // $pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        // $pdf->Line(5, 5, $pdf->getPageWidth() - 5, 5);
        // $pdf->Line($pdf->getPageWidth() - 5, 4.5, $pdf->getPageWidth() - 5, $pdf->getPageHeight() - 5);
        // $pdf->Line(5, $pdf->getPageHeight() - 5, $pdf->getPageWidth() - 5, $pdf->getPageHeight() - 5);
        // $pdf->Line(5, 4.5, 5, $pdf->getPageHeight() - 5);

        PDF::writeHTML($Template, true, true, true, true, '');
        PDF::Output(public_path() . '/print.pdf', 'F');
        return response()->json(new JsonResponse(['status' => 'success']));
    }
    public function prints(Request $request)
    {

        $logo = config('variable.logo');
        try {
            $main = $request->main;
            $reportcaption = $request->reportcaption;


            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
                  ' . $logo . ' 
            <h3 align="center">ANNUAL INSPECTION LIST</h3>
            <table>
            <tr>
            <th style="text-align:center;">As of ' . $reportcaption . '</th>
            </tr>
            </table>
            <br></br>
            <br></br>
            <table style="padding:2px;width:100%;">
            <thead>
                <tr>
                   <th style="border:0.5px solid black;text-align:center;width:10%;background-color:#dedcdc;"><br><br><b>REFERENCE NO</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>APPLICATION DATE</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>INSPECTION SERVED</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:7%;background-color:#dedcdc;"><br><br><b>ISSUED DATE</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:20%;background-color:#dedcdc;"><br><br><b>BUSINESS NAME</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:15%;background-color:#dedcdc;"><br><br><b>BUSINESS OWNER</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:12%;background-color:#dedcdc;"><br><br><b>CHARACTER OF OCCUPANCY</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:12%;background-color:#dedcdc;"><br><br><b>RECOMMENDATIONS</b><br></th>
                   <th style="border:0.5px solid black;text-align:center;width:8%;background-color:#dedcdc;"><br><br><b>INSPECTION FEE</b><br></th>
                </tr>
            </thead>
           <tbody >';
            $ctr = 1;
            $total = 0;
            foreach ($main as $row) {
                $html_content .= '
                <tr style="padding:2px;width:100%;">
                    <td style="border:0.5px solid black;text-align:center;width:10%;">' . $row['Reference No'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['Application Date'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['Inspection Served'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:7%;">' . $row['Issued Date'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:20%;">' . $row['Business Name'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:15%;">' . $row['Owner'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:12%;">' . $row['Character of Occupancy'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:12%;">' . $row['Recommendations'] . '</td>
                    <td style="border:0.5px solid black;text-align:center;width:8%;">' . $row['Inspection Fee'] . '</td>

                    
               </tr>';
                $ctr++;
                $total += $row['Inspection Fee'];
            }
            $ctr = $ctr - 1;


            $html_content .= '
                <tr style="padding:2px;">
                    <th colspan="8" style="border:0.5px solid black;text-align:right;height:20px;"><font size="10"><b>TOTAL AMOUNT            </b></font></th>
                    <th colspan="1"style="border:0.5px solid black;text-align:center;height:20px;"><b>' . ($total) . ' </b></th> 
                </tr>
                <tr style="padding:2px;">
                    <th colspan="1" style="border:0.5px solid black;text-align:right;height:20px;"><b>TOTAL RECORDS</b></th>  
                    <th colspan="8"style="border:0.5px solid black;text-align:left;height:20px;"><b>' . $ctr . '</b></th>  
                </tr>';
            $html_content .= '</tbody>
          </table>
          ';
            PDF::SetTitle('Annual Inspection List');
            PDF::AddPage('L', array(250, 300));
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function local(Request $Request)
    {

        $logo = config('variable.logo');
        try {
            $main = $Request->main;
            $reportcaption = $Request->reportcaption;

            $pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
            //$pdf->setPageOrientation('L');
            pdf::SetHeaderMargin(30);
            pdf::SetMargins(11.7, 11.7, 11.7);
            pdf::setFooterMargin(20);
            pdf::SetAutoPageBreak(false);
            pdf::SetAuthor('Author');
            pdf::SetDisplayMode('real', 'default');
            pdf::SetPrintFooter(false);
            pdf::SetPrintHeader(false);


            // pdf::AddPage('L', array(216, 330));
            pdf::SetFont('', '', 11.5);
            $Template = '
                  <table> 
                               <tr>
                                  <th style="width:100%" border="1">
                                       ' . $logo . ' 
                                      <table style="width:100%;">
                                          <tr>
                                              <th align="center"><font size="12"><font face="times"><b>OFFICE OF THE BUILDING OFFICIAL </b></font></font></th>
                                          </tr>
                                          <br>
                                          <tr>
                                              <th align="center"><font size="30"><font face="times"><b>CERTIFICATE OF ANNUAL INSPECTION </b></font></font></th>
                                          </tr>
                                          <br>
                                          <tr>
                                              <th style="width:2%;"></th>
                                              <th style="width:12%;">NO.:</th>
                                              <th align="left" style="width:33%;border-bottom: 1px solid black">' . $Request['Reference No'] . '</th>
                                              <th style="width:3%;"></th>
                                              <th style="width:17%;">FEES PAID:</th>
                                              <th align="left" style="width:31%;border-bottom: 1px solid black">' . $Request['Inspection Fee'] . '</th>
                                              <th style="width:2%;"></th>
                                          </tr>
                                          <tr>
                                              <th style="width:2%;"></th>
                                              <th style="width:12%;">DATE ISSUED:</th>
                                              <th align="left" style="width:33%;border-bottom: 1px solid black">' . $Request['Issued Date'] . '</th>
                                              <th style="width:3%;"></th>
                                              <th style="width:17%;">OFFICIAL RECEIPT NO.:</th>
                                              <th align="left" style="width:31%;border-bottom: 1px solid black">' . $Request['OR No'] . '</th>
                                              <th style="width:2%;"></th>
                                          </tr>
                                          <tr>
                                              <th style="width:50%;"></th>
                                              <th style="width:17%;">DATE PAID:</th>
                                              <th align="left" style="width:31%;border-bottom: 1px solid black">' . $Request['OR Date'] . '</th>
                                          </tr>
                                          <br>
                                          <tr>
                                              <th style="width:2%;"></th>
                                              <th style="width:96%;"><span style="text-align:justify;">
                                              This Certificate of Annual Inspection is issued/granted pursuant provision of the National Building Code (PD 1096) and its implementing rules and regulations.
                                              </span></th>
                                              <th style="width:2%;"></th>
                                          </tr>
                                          <br>
                                          <tr>
                                              <th style="width:10%;"></th>
                                              <th align="left" style="width:20%;">Name of Owner/Lessee</th>
                                              <th style="width:2%;"></th> 
                                              <th style="width:59%;border-bottom: 1px solid black">' . $Request['Owner'] . '</th>
                                              <th style="width:2%;"></th> 
                                          </tr>
                                          <tr>
                                              <th style="width:10%;"></th>
                                              <th align="left" style="width:20%;">Name of Project</th>
                                              <th style="width:2%;"></th> 
                                              <th style="width:59%;border-bottom: 1px solid black">' . $Request['projectname'] . '</th>
                                              <th style="width:2%;"></th> 
                                          </tr>
                                          <tr>
                                              <th style="width:10%;"></th>
                                              <th align="left" style="width:22%;">Character of Occupancy</th>
                                               
                                              <th style="width:30%;border-bottom: 1px solid black">' . $Request['Character of Occupancy'] . '</th>
                                              <th style="width:2%;"></th> 
                                              <th align="right" style="width:6%;">Group</th>
                                              <th style="width:2%;"></th> 
                                              <th style="width:19%;border-bottom: 1px solid black">' . $Request['classification'] . '</th>
                                              <th style="width:2%;"></th>
                                          </tr>
                                          <tr>
                                              <th style="width:10%;"></th>
                                              <th align="left" style="width:22%;">Located/Erected at/Along</th>
                                              
                                              <th style="width:59%;border-bottom: 1px solid black">' . $Request['Business Address'] . '</th>
                                              <th style="width:2%;"></th> 
                                          </tr>
                                          <br> 
                                          <tr>
                                              <th style="width:2%;"></th>
                                              <th style="width:96%;"><span style="text-align:justify;">
                                              The Owner shall maintain the building/structure to enhance its architectural well-being, structural stability, electrical, mechanical, sanitation, plumbing, electronics, interior design and fire-protective properties and shall not be occupied or used for purposes other than intended use as stated above. 
                                              </span></th>
                                              <th style="width:2%;"></th>
                                          </tr>
                                          <br>
                                          <tr>
                                              <th style="width:2%;"></th>
                                              <th style="width:96%;"><span style="text-align:justify;">
                                              This <b>CERTIFICATION OF ANNUAL INSPECTION</b> is hereby issued pursuant to Section 309 of the National Building Code of the Philippines (PD 1096), its Revised IRR, other Referral Codes and JMC No. 2018-01.
                                              </span></th>
                                              <th style="width:2%;"></th>
                                          </tr>
                                          <br>
                                          <tr>
                                              <th style="width:2%;"></th>
                                              <th style="width:96%;"><span style="text-align:justify;">
                                              A certificate copy hereof shall be posed within the premises of the building and shall not be removed without authority from the Building Official.
                                             </span></th>
                                              <th style="width:2%;"></th>
                                          </tr>
                                          <br>
                                          <br>
                                          <tr>
                                          <th style="width:5%;"></th> 
                                          <th align="center" style="width:27%;border-bottom: 1px solid black"></th>
                                          <th style="width:5%;"></th> 
                                          <th align="center" style="width:27%;border-bottom: 1px solid black"></th>
                                          <th style="width:5%;"></th> 
                                          <th align="center" style="width:27%;border-bottom: 1px solid black"></th>
                                          </tr>
                                          <tr>
                                              <th style="width:5%;"></th>
                                              <th align="center" style="width:27%;">CHIEF PROCESSING AND EVALUATION</th>
                                              <th style="width:5%;"></th>
                                              <th align="center" style="width:27%;">CHIEF ENFORCEMENT DIVISION</th>
                                              <th style="width:5%;"></th>
                                              <th align="center" style="width:27%;">BUILDING OFFICIAL</th>
                                          </tr>
                                          <tr>
                                              <th style="width:5%;"></th>
                                              <th align="center" style="width:27%;"><font size="8.5">(SIGNATURE OVER PRINTED NAME)</FONT></th>
                                              <th style="width:5%;"></th>
                                              <th align="center" style="width:27%;"><font size="8.5">(SIGNATURE OVER PRINTED NAME)</FONT></th>
                                              <th style="width:5%;"></th>
                                              <th align="center" style="width:27%;"><font size="8.5">(SIGNATURE OVER PRINTED NAME)</FONT></th>
                                          </tr>
                                          <tr>
                                              <th style="width:5%;"></th>
                                              <th align="right"style="width:5%;">Date</th>
                                              <th style="width:1%;"></th>
                                              <th align="center" style="width:15%;border-bottom: 1px solid black"></th>
                                              <th style="width:12%;"></th>
                                              <th align="right"style="width:5%;">Date</th>
                                              <th style="width:1%;"></th>
                                              <th align="center" style="width:15%;border-bottom: 1px solid black"></th>
                                              <th style="width:12%;"></th>
                                              <th align="right"style="width:5%;">Date</th>
                                              <th style="width:1%;"></th>
                                              <th align="center" style="width:15%;border-bottom: 1px solid black">' . $Request['Issued Date'] . '</th>
                                          </tr>
                                          <br>
                                          <br>
                                          <tr>
                                              <th style="width:2%;"></th>
                                              <th style="width:96%;"><span style="text-align:justify;"><font size="11"><b>
                                              THIS CERTIFICATE MAY BE CANCELLED OR REVOKED PURSUANT TO SECTION 309 OF THE NATIONAL BUILDING CODE OF THE PHILIPPINES (PD 1096)
                                              </b></font></span></th>
                                              </tr>
                                      </table>
                                  </th>
                              </tr>
                          </table>
                  ';

            PDF::SetTitle('Annual Inspection Certificate Local');
            PDF::AddPage('L',  array(216, 330));
            PDF::writeHTML($Template, true, true, true, true, '');

            pdf::SetLineStyle(array('width' => .5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
            pdf::Line(10, 10, pdf::getPageWidth() - 10, 10);
            pdf::Line(pdf::getPageWidth() - 10, 10, pdf::getPageWidth() - 10, pdf::getPageHeight() - 10);
            pdf::Line(10, pdf::getPageHeight() - 10, pdf::getPageWidth() - 10, pdf::getPageHeight() - 10);
            pdf::Line(10, 10, 10, pdf::getPageHeight() - 10);

            PDF::Output(public_path() . '/local.pdf', 'F');



            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
