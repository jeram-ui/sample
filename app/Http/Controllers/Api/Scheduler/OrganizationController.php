<?php

namespace App\Http\Controllers\Api\Scheduler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class OrganizationController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $sched_db;
    private $empid;
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
        $this->path = env('LGU_FRONT');
    }
    public function index()
    {
    }
    public function getListOrganization(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.spl_display_organization_gigil()');
        return response()->json(new JsonResponse($list));
    }

    public function getDetails($id)
    {
        $vdtls = DB::select('Call '.$this->sched_db.'.spl_display_organization_details_gigil(' . $id . ')');
        return response()->json(new JsonResponse($vdtls));
    }
    public function printlist()
    {
    }
    public function getOrganizationByMember($id)
    {
        $data = DB::table($this->sched_db.'.tbl_organization_profile')
    ->join($this->sched_db.'.tbl_member_info', 'tbl_organization_profile.id', '=', 'tbl_member_info.orgID')
    ->join('tbl_person_setup', 'tbl_person_setup.pkID', '=', 'tbl_member_info.pkID')
    ->where('tbl_person_setup.user_id', $id)
    ->select($this->sched_db.'.tbl_organization_profile.*')
    ->groupBy('tbl_organization_profile.id')->get();
        return response()->json(new JsonResponse($data));
    }
    public function printdetail(Request $request)
    {
        $logo = config('variable.logo');
        try {
            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
          ' . $logo . ' 
          <table style="width:100%;padding:3px;">
            <tr>
                <th style="width:100%; line-height:1px"><h2 align="center">ORGANIZATIONAL PROFILE</h2></th>
            </tr>
            <tr>
                <th style="width:100%"><h3 align="center">PYC FORM 1</h3></th>
            </tr>
            <tr><th style="line-height:0.1px"></th></tr>
            <table>
                <tr>
                    <th style="border:1px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>BASIC INFORMATION</b></th> 
                </tr>
                <tr>
                    <th style="border-left:0.5px solid black; border-right:0.5px solid black"><b> Name of Organization</b></th>
                </tr>
                <tr>
                    <th style="border-left:0.5px solid black; border-right:0.5px solid black; border-bottom:0.5px solid black">   </th>
                </tr>
                <tr>
                    <th style="border-left:0.5px solid black; border-right:0.5px solid black"><b> Full Address</b></th>
                </tr>
                <tr>
                    <th style="width:16%; border-left:0.5px solid black"> </th>
                    <th style="width:16%"> </th>
                    <th style="width:17%"> </th>
                    <th style="width:17%"> </th>
                    <th style="width:18%"> </th>
                    <th style="width:16%; border-right:0.5px solid black"> </th>
                </tr>
                <tr>
                    <th style="width:16%; border-left:0.5px solid black; border-bottom:0.5px solid black; font-size:5p"> Room/Floor/Building</th>
                    <th style="width:16%; border-bottom:0.5px solid black; font-size:5p">  Block/Lot No.</th>
                    <th style="width:17%; border-bottom:0.5px solid black; font-size:5p">  Street</th>
                    <th style="width:17%; border-bottom:0.5px solid black; font-size:5p">  Barangay</th>
                    <th style="width:18%; border-bottom:0.5px solid black; font-size:5p">  City/Municipality/Town</th>
                    <th style="width:16%; border-bottom:0.5px solid black; border-right:0.5px solid black; font-size:5p"> Province/Region</th>
                </tr>
                <tr>
                    <th style="width:28%; border-left:0.5px solid black; border-right:0.5px solid black"><b> Mobile Number</b></th>
                    <th style="width:28%; border-right:0.5px solid black"><b> Official Email Address</b></th>
                    <th style="width:28%; border-right:0.5px solid black"><b> Date of Registration(DD/MM/YYYY)</b></th>
                    <th style="width:16%;border-right:0.5px solid black"><b> Zip Code</b></th>
                </tr>
                <tr>
                    <th style="width:28%; border-left:0.5px solid black; border-bottom:0.5px solid black; border-right:0.5px solid black; font-size:8p"> (+63)</th>
                    <th style="width:28%; border-bottom:0.5px solid black; border-right:0.5px solid black; font-size:8p"> </th>
                    <th style="width:28%; border-bottom:0.5px solid black; border-right:0.5px solid black; font-size:8p"> </th>
                    <th style="width:16%; border-bottom:0.5px solid black; font-size:8p; border-right:0.5px solid black"> </th>
                </tr>
                <tr>
                    <th style="width:28%; border-left:0.5px solid black; border-right:0.5px solid black; align:center"><b> Telephone Number</b></th>
                    <th style="width:72%; border-right:0.5px solid black"><b> Official Mailing Address</b></th>
                </tr>
                <tr>
                    <th style="width:28%; border-left:0.5px solid black; border-right:0.5px solid black; font-size:8p"> (032)</th>
                    <th style="width:72%; font-size:8p; border-right:0.5px solid black"> </th>
                </tr>
                <tr>
                <th style="width:100%; border:1px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>ORGANIZATION SPECIFICS</b></th> 
                </tr>
                <tr>
                    <th style="border-left:0.5px solid black; border-right:0.5px solid black; border-bottom:0.5px solid black; font-size:5p" align="center"> Please shade the bullet that matches your organization</th>
                </tr>
                <tr>
                    <th style="width:30%; border-left:0.5px solid black; border-right:0.5px solid black; font-size:5p"> *Shade only one(1)</th>
                    <th style="width:70%; border-left:0.5px solid black; border-right:0.5px solid black; font-size:5p"> *Shade which matches your advocacy maybe one or more</th>
                </tr>
                <tr>
                    <th style="width:30%; border-left:0.5px solid black; border-right:0.5px solid black; align:center"><b> Organization Level:</b></th>
                    <th style="width:70%; border-right:0.5px solid black"><b> Advocacies:</b></th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"> National Organization
                    </th>
                    <th style="width: 35%;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Education</b>
                    </th>
                    <th style="width: 35%;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Active Citizenship</b>
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"> Regional Organization
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Basic Education
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Leadership and Capability Building
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"> Provincial Organization
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Technical Vocational Education
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Culture and Arts
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">City/Municipal Organization
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Out-of-School Youths
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Volunteerism
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Barangay Organization
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 68%; border-right:0.5px solid black;">
                        <input style="indent:2%" type="radio" name="0" value="0" checked="0" readonly="true">Science and Technology
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black; border-right:0.5px solid black; border-bottom:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Sition/Purok Organization
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 68%; border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Values and Education
                    </th>
                </tr>
                <tr>
                    <th style="width:30%; border-left:0.5px solid black; border-right:0.5px solid black; font-size:5p"> *Shade only one(1)</th>
                    <th style="width:70%; border-left:0.5px solid black; border-right:0.5px solid black; font-size:5p"></th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black; border-right:0.5px solid black;">
                        Major Classification
                    </th>
                    <th style="width: 35%">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Environment</b>
                    </th>
                    <th style="width: 35%; border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Global Mobility</b>
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black; border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Youth Organization
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Environmental Protection
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">People-to-People Exchange
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black; border-right:0.5px solid black;border-bottom:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Youth-Serving Organization
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Climate Change Adaptation and Mitigation
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Youth Trafficking
                    </th>
                </tr>
                <tr>
                    <th style="width:30%; border-left:0.5px solid black; border-right:0.5px solid black; font-size:5p"> *Shade only one(1)</th>
                    <th style="width: 2%;"></th>
                    <th style="width:68%; border-right:0.5px solid black;"> 
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Disaster Risk Reduction and Management
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;"><b>
                        Sub-Classification/Subsector:</b>
                    </th>
                    <th style="width: 35%">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Health</b>
                    </th>
                    <th style="width: 35%;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Social Inclusion</b>
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Community-Based Youth
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Healthy Lifestyle
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Youth with Disability
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Faith-Based Youth
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Reproductive Health
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Indigenous People
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">In-School Youth
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Substance Addiction
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Violence Against Women and Children
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Out-of-School Youth
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Mental Health
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Gender Sensitivity
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Youth with Special Needs
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Sports and Wellness
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Youth with Special Needs
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Working Youth
                    </th>
                    <th style="width: 35%;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Peace Building and Security</b>
                    </th>
                    <th style="width: 35%;border-right:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Economic Empowerment</b>
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true">Federation/Consortium
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Peace and Order
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Employment
                    </th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <b>Current Number of Active</b>
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Juvenile Justice
                    </th>
                    <th style="width: 33%;border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Entreprenuership and Livelihood
                    </th>
                </tr>
                <tr>    
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;">
                        <b>Registered Numbers:</b>
                    </th>
                    <th style="width: 2%"></th>
                    <th style="width: 68%; border-right:0.5px solid black;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Human Rights
                    </th>
                </tr>
                <tr>    
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;" align="center" font-size="12p"><u></u></th>
                    <th style="width: 35%; border-left:0.5px solid black;">
                        <input type="checkbox" name="0" value="0" checked="0" readonly="true"><b>Governance</b>
                    </th>
                    <th style="width: 35%; border-right:0.5px solid black;">
                        <b>Others:</b>
                    </th>
                </tr>
                <tr>
                    <th style="width:30%; border-left:0.5px solid black; border-right:0.5px solid black; border-bottom:0.5px solid black;"></th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Voters Education
                    </th>
                    <th style="width:33%; border-bottom:1px solid black;width:28%;align:center;"></th>
                    <th style="width: 5%; border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width:15%;border-left:0.5px solid black;"><b> Date Established:</b></th>
                    <th style="width:15%; border-right:0.5px solid black; font-size:7p">(DD/MM/YYYY)</th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Election Watch
                    </th>
                    <th style="width:33%; border-bottom:1px solid black;width:28%;align:center;"></th>
                    <th style="width: 5%; border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;" align="center" font-size="12p"></th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%;">
                        <input type="radio" name="0" value="0" checked="0" readonly="true">Youth in Governance
                    </th>
                    <th style="width:33%; border-bottom:1px solid black;width:28%;align:center;"></th>
                    <th style="width: 5%; border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;" align="center" font-size="12p"><u></u></th>
                    <th style="width: 2%"></th>
                    <th style="width: 35%;">
                       
                    </th>
                    <th style="width:33%; border-bottom:1px solid black;width:28%;align:center;"></th>
                    <th style="width: 5%; border-right:0.5px solid black;"></th>
                   
                </tr>
                <tr>
                    <th style="width: 30%; border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;"></th>
                    <th style="width: 70%;border-bottom:0.5px solid black;border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 100%; border-left:0.5px solid black;border-right:0.5px solid black;"><b> Brief Description and Objectives of the Organization :</b></th>
                </tr>
                <tr>
                    <th style="width: 2%; border-left:0.5px solid black;"></th>
                    <th style="width:96%; border-bottom:1px solid black;width:96%;align:center;"></th>
                    <th style="width: 2%; border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 2%; border-left:0.5px solid black;"></th>
                    <th style="width:96%; border-bottom:1px solid black;width:96%;align:center;"></th>
                    <th style="width: 2%; border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 2%; border-left:0.5px solid black;"></th>
                    <th style="width:96%; border-bottom:1px solid black;width:96%;align:center;"></th>
                    <th style="width: 2%; border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 2%; border-left:0.5px solid black;"></th>
                    <th style="width:96%; border-bottom:1px solid black;width:96%;align:center;"></th>
                    <th style="width: 2%; border-right:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 100%; border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;"></th>
                </tr>
                <tr>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"><b> Vision Statement</b></th>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"><b> Mission Statement</b></th>
                </tr><tr>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                </tr><tr>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                </tr><tr>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                </tr><tr>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                </tr><tr>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;"></th>
                </tr><tr>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;"></th>
                    <th style="width: 50%; border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;"></th>
                </tr>
            </table>
          </table>
         ';
            PDF::SetTitle('Organizational Profile Form');
            PDF::AddPage();
            PDF::writeHTML($html_content, true, true, true, true, '');
      
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    public function getSetupData()
    {
        try {
            $data['tbl_organization_level_setup'] = DB::table($this->sched_db.'.tbl_organization_level_setup')->select('id', 'description')->get();
            $data['tbl_organization_major_class_setup'] = DB::table($this->sched_db.'.tbl_organization_major_class_setup')->select('id', 'description')->get();
            $data['tbl_organization_sub_class_setup'] = DB::table($this->sched_db.'.tbl_organization_sub_class_setup')->select('id', 'description')->get();
            $data['tbl_organization_advocacies_setup'] = DB::table($this->sched_db.'.tbl_organization_advocacies_setup')->select('id', 'description')->get();
            $data['tbl_organization_advocacies_dtls_setup'] = DB::table($this->sched_db.'.tbl_organization_advocacies_dtls_setup')->select('id', 'main_id', 'description')->get();

            return response()->json(new JsonResponse($data));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $idx = $request->main['id'];
            $main = $request->main;
            $details = $request->details;
            if ($idx > 0) {
                $this->update($idx, $main, $details);
            } else {
                $this->save($main, $details);
            }

            DB::commit();
            return response()->json(new JsonResponse(['Message'=>'Transaction completed successfully','status'=>'success']));
        } catch (\Exception $e) {
            db::rollBack();
            return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
        }
    }

    public function save($main, $details)
    {
        DB::table($this->sched_db.'.tbl_organization_profile')->insert($main);
        
        // Get ID
        $id = DB::getPdo()->lastInsertId();
     
        //Save Organization Level
        foreach ($details['tbl_organization_level'] as $row) {
            $data = array(
                'org_prof_id' => $id,
                'level_setup_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_level')->insert($data);
        }

        //Save Major Class
        foreach ($details['tbl_organization_major_class'] as $row) {
            $data = array(
                'org_prof_id' => $id,
                'major_class_setup_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_major_class')->insert($data);
        }

        //Save Sub Class
        foreach ($details['tbl_organization_sub_class'] as $row) {
            $data = array(
                'org_prof_id' => $id,
                'sub_class_setup_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_sub_class')->insert($data);
        }

        //Save Advocaies
        foreach ($details['tbl_organization_advocacies'] as $row) {
            $data = array(
                'org_prof_id' => $id,
                'org_adv_dtl_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_advocacies')->insert($data);
        }
    }

    public function edit($id)
    {
        try {
            $data['main'] = DB::table($this->sched_db.'.tbl_organization_profile')->where('id', $id)->get();
            
            $data['tbl_organization_level'] = DB::table($this->sched_db.'.tbl_organization_level')->select('level_setup_id as id')->where('org_prof_id', $id)->get();
            $data['tbl_organization_major_class'] = DB::table($this->sched_db.'.tbl_organization_major_class')->select('major_class_setup_id as id')->where('org_prof_id', $id)->get();
            $data['tbl_organization_sub_class'] = DB::table($this->sched_db.'.tbl_organization_sub_class')->select('sub_class_setup_id as id')->where('org_prof_id', $id)->get();
            $data['tbl_organization_advocacies'] = DB::table($this->sched_db.'.tbl_organization_advocacies')->select('org_adv_dtl_id as id')->where('org_prof_id', $id)->get();
           
            return response()->json(new JsonResponse($data));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
        }
    }

    public function update($idx, $main, $details)
    {
        // Update
        // dd($main);
        DB::table($this->sched_db.'.tbl_organization_profile')->where('id', $idx)->update($main);
        
        // Delete Details
        DB::table($this->sched_db.'.tbl_organization_level')->where('org_prof_id', $idx)->delete();
        DB::table($this->sched_db.'.tbl_organization_major_class')->where('org_prof_id', $idx)->delete();
        DB::table($this->sched_db.'.tbl_organization_sub_class')->where('org_prof_id', $idx)->delete();
        DB::table($this->sched_db.'.tbl_organization_advocacies')->where('org_prof_id', $idx)->delete();
        
        //Save Organization Level
        foreach ($details['tbl_organization_level'] as $row) {
            $data = array(
                'org_prof_id' => $idx,
                'level_setup_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_level')->insert($data);
        }
       
        //Save Major Class
        foreach ($details['tbl_organization_major_class'] as $row) {
            $data = array(
                'org_prof_id' => $idx,
                'major_class_setup_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_major_class')->insert($data);
        }

        //Save Sub Class
        foreach ($details['tbl_organization_sub_class'] as $row) {
            $data = array(
                'org_prof_id' => $idx,
                'sub_class_setup_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_sub_class')->insert($data);
        }

        //Save Advocaies
        foreach ($details['tbl_organization_advocacies'] as $row) {
            $data = array(
                'org_prof_id' => $idx,
                'org_adv_dtl_id' => $row
            );
           
            DB::table($this->sched_db.'.tbl_organization_advocacies')->insert($data);
        }
    }

    public function cancel($id)
    {
        $data['status'] = 'CANCELLED';
  
        DB::table($this->sched_db.'.tbl_organization_profile')->where('id', $id) ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function members($id)
    {
        $results = DB::table("users")
        ->join('tbl_person_setup', 'tbl_person_setup.user_id', '=', 'users.id')
        ->join($this->sched_db.'.tbl_member_info', 'tbl_member_info.pkID', '=', 'tbl_person_setup.pkID')
        ->select(
            'users.*',
            'tbl_member_info.*',
            db::raw('CONCAT("'.$this->path.'/images/client/",users.image_path) AS image')
        )
        ->where('tbl_member_info.orgID', '=', $id)
       ->get()
        ;
        return response()->json(new JsonResponse($results));
    }
}
