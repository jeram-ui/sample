<?php

namespace App\Http\Controllers\Api\Scheduler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;
use Illuminate\Support\Facades\log;
class MemberController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $sched_db;
    private $empid;
    protected $G;
  
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
    }
    // ***DROPDOWN LIST***
    public function organizationList(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.balodoy_display_organization()');
        return response()->json(new JsonResponse($list));
    }
    public function personList(Request $request)
    {
        $list = DB::select('Call balodoy_display_fullname()');
        return response()->json(new JsonResponse($list));
    }
    public function sectorList(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.balodoy_display_sector()');
        return response()->json(new JsonResponse($list));
    }
    public function relationList(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.balodoy_display_relation()');
        return response()->json(new JsonResponse($list));
    }
    public function educationList(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.balodoy_display_education()');
        return response()->json(new JsonResponse($list));
    }
    public function graduationList(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.balodoy_display_graduation()');
        return response()->json(new JsonResponse($list));
    }
    public function positionList(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.balodoy_display_organization_position()');
        return response()->json(new JsonResponse($list));
    }
    public function transNo(Request $request)
    {
        $list = DB::select('Call '.$this->sched_db.'.balodoy_get_member_transno()');
        return response()->json(new JsonResponse($list));
    }
  
    // ***SAVE MEMBER PROFILE***
    public function store(Request $request)
    {
        // dd($request);
        try {
            DB::beginTransaction();
            $basicInfo = $request->basicInfoData;
            $otherInfo = $request->otherInfoData;
            $educInfo = $request->educInfoData;
            $orgInfo = $request->orgInfoData;
            $idx=$basicInfo['id'];
            if ($idx > 0) {
                $this->update($idx, $basicInfo, $otherInfo, $educInfo, $orgInfo);
            } else {
                $this->save($basicInfo, $otherInfo, $educInfo, $orgInfo);
            };
            
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!','errormsg'=>$e,'status'=>'error']));
        }
    }
    public function save($basicInfo, $otherInfo, $educInfo, $orgInfo)
    {
        DB::table($this->sched_db.'.tbl_member_info')->insert($basicInfo);
        $id = DB::getPDo()->lastInsertId();
        foreach ($otherInfo as $row) {
            $array=array(
        'pkID'=>$id,
        'fullname'=>$row['fullname'],
        'relation'=>$row['relation'],
        'occupation'=>$row['occupation'],
        'mobileNo'=>$row['mobileno'],
          );
            DB::table($this->sched_db.'.tbl_other_info')->insert($array);
        }
        foreach ($educInfo as $row) {
            $array=array(
          'pkID'=>$id,
          'educ_type'=>$row['educ_type'],
          'school_name'=>$row['school_name'],
          'school_addr'=>$row['school_addr'],
          'inclusive_yr'=>$row['inclusive_yr'],
          'grad_type'=>$row['grad_type'],
          'honors_received'=>$row['honors_received'],
          'degree_received'=>$row['degree_received'],
          'title_received'=>$row['title_received'],
        );
            DB::table($this->sched_db.'.tbl_educ_info')->insert($array);
        }
        foreach ($orgInfo as $row) {
            $array=array(
            'pkID'=>$id,
            'org_name'=>$row['org_name'],
            'chapter'=>$row['chapter'],
            'position'=>$row['position'],
            'year_affiliated'=>$row['year_affiliated'],
          );
            DB::table($this->sched_db.'.tbl_org_info')->insert($array);
        };
    }
    public function update($idx, $basicInfo, $otherInfo, $educInfo, $orgInfo)
    {
        DB::table($this->sched_db.'.tbl_member_info')->where('id', $idx)->update($basicInfo);
        DB::table($this->sched_db.'.tbl_other_info')->where('pkID', $idx)->delete();
        foreach ($otherInfo as $row) {
            $array=array(
           'pkID'=>$idx,
           'fullname'=>$row['fullname'],
           'relation'=>$row['relation'],
           'occupation'=>$row['occupation'],
           'mobileNo'=>$row['mobileno'],
      );
            DB::table($this->sched_db.'.tbl_other_info')->insert($array);
        }
        DB::table($this->sched_db.'.tbl_educ_info')->where('pkID', $idx)->delete();
        foreach ($educInfo as $row) {
            $array=array(
            'pkID'=>$idx,
            'educ_type'=>$row['educ_type'],
            'school_name'=>$row['school_name'],
            'school_addr'=>$row['school_addr'],
            'inclusive_yr'=>$row['inclusive_yr'],
            'grad_type'=>$row['grad_type'],
            'honors_received'=>$row['honors_received'],
            'degree_received'=>$row['degree_received'],
            'title_received'=>$row['title_received'],
          );
            DB::table($this->sched_db.'.tbl_educ_info')->insert($array);
        }
        DB::table($this->sched_db.'.tbl_org_info')->where('pkID', $idx)->delete();
        foreach ($orgInfo as $row) {
            $array=array(
          'pkID'=>$idx,
          'org_name'=>$row['org_name'],
          'chapter'=>$row['chapter'],
          'position'=>$row['position'],
          'year_affiliated'=>$row['year_affiliated'],
          );
            DB::table($this->sched_db.'.tbl_org_info')->insert($array);
        };
    }

    public function edit(Request $request, $id)
    {
        $id = $id;
        $data['basicInfo'] = DB::select("select * from ".$this->sched_db.".tbl_member_info where id = '$id'");
        $data['otherInfo'] = DB::select("select fullname,relation,occupation,mobileNo AS 'mobileno' FROM ".$this->sched_db.".tbl_other_info where pkID = '$id'");
        $data['educInfo'] = DB::select("select educ_type,school_name,school_addr,inclusive_yr,grad_type,honors_received,degree_received,title_received FROM ".$this->sched_db.".tbl_educ_info where pkID = '$id'");
        $data['orgInfo'] = DB::select("select org_name,chapter,`position`,year_affiliated FROM ".$this->sched_db.".tbl_org_info where pkID = '$id'");
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {
        db::table($this->sched_db.'.tbl_member_info')->where('id', $id)->update(['trans_stat'=>'cancelled']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    // ***MEMBER PROFILE LIST***
    public function getOrganization()
    {
        $list = DB::select('Call '.$this->sched_db.'.organization()');
        // dd($list);
        return response()->json(new JsonResponse($list));
    }
    public function memberList(Request $request)
    {
        // dd($request);
        $org = $request->orgID;
        $_year = $request->from;
    
        $list = DB::select('call '.$this->sched_db.'.balodoy_display_member_info_org(?,?)', array($org,$_year));
    
        // dd($list);
        return response()->json(new JsonResponse($list));
    }
    // Members Profile Form
    public function printMember($id)
    {
        $logo = config('variable.logo');
        $lists = DB::select('Call '.$this->sched_db.'.balodoy_display_member_basic(?)', array($id));
        $affOrg = DB::select('Call '.$this->sched_db.'.member_info_affiliated_org(?)', array($id));
        $bckgrnd = DB::select('Call '.$this->sched_db.'.member_educational_bckgrnd(?)', array($id));
        $other = DB::select('Call '.$this->sched_db.'.member_other_information(?)', array($id));
        // dd($lists[0]->permStreet);
        // dd($lists);
       log::debug( $lists);
        try {
            // dd($request);
            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
        ' . $logo . ' 
        <table style="width:100%;padding:3px;">
        <tr>
        <th style="width:100%;line-height:1px"><h3 align="center">'.$lists[0]->NameofOrganization.'</h3></th>
        </tr>
        <tr>
        <th style="width:100%;line-height:1px"><h3 align="center">'.$lists[0]->chapter.'</h3></th> 
        </tr>
        <tr>
        <th style="width:100%;line-height:1px"><h2 align="center">MEMBER`S PROFILE</h2></th> 
        </tr>
        <tr>
        <th style="width:2%;align:left"></th>
        <th style="width:25%;align:center"><b>PYC FORM 4</b></th> 
        <th style="width:50%;align:center"></th>
        <th style="width:10%;align:center"><b>ID CODE:</b></th>
        <th style="width:21%;align:left">'.$lists[0]->memID.'</th> 
        <th style="width:2%;align:center"></th>
        </tr>
        </table>
        <table>
        <tr>
        <th style="border:0.5px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>BASIC INFORMATION</b></th> 
        </tr>
        </table>
        <table border="0.5">
        <tr>
          <th width="75%"><table>
              <tr>
                <th style="width:100%;text-align:left"><i><b> Fullname:</b></i></th> 
              </tr>
              <tr>
                <th style="width:30%;border-left:0.5px solid black;text-align:center">'.$lists[0]->lastname.'</th>
                <th style="width:30%;text-align:center">'.$lists[0]->firstname.'</th> 
                <th style="width:40%;border-right:0.5px solid black;text-align:center">'.$lists[0]->middlename.'</th>  
                </tr> 
              <tr>
                <th style="width:30%;border-bottom:0.5px solid black;border-left:0.5px solid black;text-align:center"><b>(Last Name)</b></th>
                <th style="width:30%;border-bottom:0.5px solid black;text-align:center"><b>(First Name)</b></th> 
                <th style="width:40%;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:center"><b>(Middle Name)</b></th>  
              </tr>
              <tr>
                <th style="width:35%;border-left:0.5px solid black;text-align:left"><b> Date of Birth:</b>(DD/MM/YYYY)</th>
                <th style="width:15%;border-left:0.5px solid black;text-align:center"><b> Age:</b></th> 
                <th style="width:50%;border-right:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Birthplace:</b></th>  
              </tr>
              <tr>
                <th style="width:35%;border-left:0.5px solid black;border-bottom:0.5px solid black;text-align:center">'.$lists[0]->bday.'</th>
                <th style="width:15%;border-left:0.5px solid black;border-bottom:0.5px solid black;text-align:center">'.$lists[0]->age.'</th> 
                <th style="width:50%;border-right:0.5px solid black;border-left:0.5px solid black;border-bottom:0.5px solid black;text-align:left">&nbsp;'.$lists[0]->bplace.'</th>  
              </tr>
              <tr>
                <th style="width:30%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"><b> Gender:</b></th>
                <th style="width:40%;text-align:left"><b> Civil Status:</b></th> 
                <th style="width:30%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"><b> Citizenship:</b></th>  
              </tr>
              <tr>
                <th style="width: 30%;border-right:0.5px solid black;border-bottom:0.5pxpx solid black;border-left:0.5px solid black;text-align:left">
                    <span>  <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Female.'" readonly="true">FEMALE    <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Male.'" readonly="true">MALE</span>
                </th> 
                <th style="width: 40%;border-bottom:0.5px solid black;text-align:left">
                  <span><input type="checkbox" name="0" value="0" checked="'.$lists[0]->Single.'" readonly="true">Single  <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Separated.'" readonly="true">Separated<br>
                     <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Married.'" readonly="true">Married    <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Widowed.'" readonly="true">Widowed
                  </span>
                </th>
                <th style="width:30%;border-bottom:0.5px solid black;border-right:0.5px solid black;border-left:0.5px solid black;text-align:left">&nbsp;Filipino</th> 
              </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b> Height:</b>(in meters)</th>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b> Weight:</b>(in kilograms)</th>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b> Religion</b></th> 
                <th style="width:25%;border-right:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Occupation:</b>(if applicable)</th>  
              </tr>
              <tr>
                <th style="width:25%;border-bottom:0.5px solid black;border-left:0.5px solid black;text-align:center"> '.$lists[0]->h.'Mtr(s)</th>
                <th style="width:25%;border-bottom:0.5px solid black;border-left:0.5px solid black;text-align:center"> '.$lists[0]->w.'kg(s)</th>
                <th style="width:25%;border-bottom:0.5px solid black;border-left:0.5px solid black;text-align:left"> '.$lists[0]->r.'</th> 
                <th style="width:25%;border-bottom:0.5px solid black;border-right:0.5px solid black;border-left:0.5px solid black;text-align:left"> '.$lists[0]->o.'</th> 
              </tr>
              <tr>
                <th style="width:100%;border-right:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> *Select only one</b>(1)</th>
              </tr>
              <tr>
                <th style="width:100%;border-right:0.5px solid black;border-left:0.5px solid black;text-align:left"><b>  Youth Sector</b></th>
              </tr>
              <tr>
                <th style="width: 5%;border-left:0.5px solid black;"></th>
                <th style="width: 30%;text-align:left">
                <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Out.'" readonly="true">Out-of-School Youth</th>
                <th style="width: 30%;text-align:left">
                <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Youth.'" readonly="true">Youth with Special Needs</th>
                <th style="width: 30%;text-align:left">
                <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Federation.'" readonly="true">Federation/Consortium</th>
                </tr>
              <tr>
                <th style="width: 5%;border-left:0.5px solid black;"></th>
                <th style="width: 30%;text-align:left">
                <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Faith.'" readonly="true">Faith-Based Youth</th>
                <th style="width: 30%;text-align:left">
                <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Working.'" readonly="true">Working Youth</th>
              </tr>
              <tr>
                <th style="width: 5%;border-left:0.5px solid black;"></th>
                <th style="width: 30%;text-align:left">
                <input type="checkbox" name="0" value="0" checked="'.$lists[0]->In.'" readonly="true">In-School Youth</th>
                <th style="width: 30%;text-align:left">
                <input type="checkbox" name="0" value="0" checked="'.$lists[0]->Community.'" readonly="true">Community-Based Youth</th>
              </tr> 
              </table>
          </th>
          <th width="25%"><table>
              <tr>
                <th width="10%"></th>
                <th style="border-bottom:0.5px solid black;"></th> 
              </tr> 
              <tr>
                <th width="10%"></th>
                <th width="80%" border="0.5"> 
                  <img src="'.public_path().'/images/memID.png" style="width:113px;height:120px;" border="1"> 
                </th> 
                <th width="10%"></th>
              </tr> 
              <tr>
                <th></th> 
              </tr> 
              <tr>
                <th style="width:60%;border-top:0.5px solid black;border-right:0.5px solid black;"><span style="font-size:7pt;"><b> PREFFERED NAME:</b></span></th>
                <th style="width:40%;border-top:0.5px solid black;border-right:0.5px solid black;"><span style="font-size:7pt;text-align:center"><b>BLOOD TYPE:</b></span></th>
              </tr> 
              <tr>
                <th style="width:60%;border-right:0.5px solid black;"><span style="font-size:7pt;"> '.$lists[0]->n.'</span></th>
                <th style="width:40%;"><span style="font-size:7pt;text-align:center"> '.$lists[0]->b.'</span></th>
              </tr> 
            </table> 
          </th>
        </tr>
        </table>
        <table>
        <tr>
        <th style="border:0.5px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>CONTACT DETAILS</b></th> 
        </tr>
        </table>
        <table border="0.5">
        <tr>
          <th width="100%"><table>
              <tr>
                <th style="width:100%;text-align:left"><b> Present Address:</b></th> 
              </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"></th>
                <th style="width:10%;text-align:center"></th> 
                <th style="width:15%;text-align:center">'.$lists[0]->presStreet.'</th>
                <th style="width:10%;text-align:center">'.$lists[0]->presbarangay.'</th>
                <th style="width:25%;text-align:center">'.$lists[0]->presMun.', '.$lists[0]->presZip.'</th>  
                <th style="width:15%;text-align:center">'.$lists[0]->presProvince.', '.$lists[0]->presRegion.'</th>
                </tr> 
                <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b>&nbsp;(Room/Floor/Building)</b></th>
                <th style="width:10%;text-align:center"><b>(Block/Lot)</b></th> 
                <th style="width:15%;text-align:center"><b>(Street)</b></th>
                <th style="width:10%;text-align:center"><b>(Barangay)</b></th>
                <th style="width:25%;text-align:center"><b>(City/Municipality/Town&ZipCode)</b></th>  
                <th style="width:15%;text-align:center"><b>(Province Region)</b></th>
                </tr> 
                <tr>
                <th style="width:100%;border-top:0.5px solid black;text-align:left"><b> Permanent Address:</b></th> 
                </tr>
                <tr >
                <th style="width:25%;border-left:0.5px solid black;text-align:left"></th>
                <th style="width:10%;text-align:center"></th> 
                <th style="width:15%;text-align:center">'.$lists[0]->permStreet.'</th>
                <th style="width:10%;text-align:center">'.$lists[0]->permBarangay.'</th>
                <th style="width:25%;text-align:center">'.$lists[0]->permMun.', '.$lists[0]->permZipcode.'</th>  
                <th style="width:15%;text-align:center">'.$lists[0]->permProvince.','.$lists[0]->permRegion .'</th>
                </tr>   
                <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b>&nbsp;(Room/Floor/Building)</b></th>
                <th style="width:10%;text-align:center"><b>(Block/Lot)</b></th> 
                <th style="width:15%;text-align:center"><b>(Street)</b></th>
                <th style="width:10%;text-align:center"><b>(Barangay)</b></th>
                <th style="width:25%;text-align:center"><b>(City/Municipality/Town&ZipCode)</b></th>  
                <th style="width:15%;text-align:center"><b>(Province Region)</b></th>
                </tr> 
                <tr>
                <th style="width:20%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"><b> Telephone:</b></th>
                <th style="width:30%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"><b> Cellular Number:</b></th>
                <th style="width:50%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"><b> Active Email Address:</b></th> 
                </tr>
                <tr >
                <th style="width:20%;border-left:0.5px solid black;text-align:left"> '.$lists[0]->tel.'</th>
                <th style="width:30%;border-left:0.5px solid black;text-align:left"> '.$lists[0]->cell.'</th>
                <th style="width:50%;border-left:0.5px solid black;text-align:left"> '.$lists[0]->email.'</th>
                </tr>
                <tr>
                <th style="width:50%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"><b> Social Media Username:</b></th>
                <th style="width:50%;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"><b><i> Please double check the correctness of your contact information</i></b></th>
                </tr>
                <tr >
                <th style="width:50%;border-left:0.5px solid black;text-align:left"> '.$lists[0]->social.'</th>
                <th style="width:50%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"><b><i>  before proceeding to the next section.</i></b></th>
                </tr>    
            </table>
          </th>
        </tr>
        </table>
        <table>
        <tr>
        <th style="border:0.5px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>OTHER INFORMATION</b></th> 
        </tr>
        </table>
        <table border="0.5">
        <tr>
          <th width="100%"><table>
              <tr>
                <th style="width:75%;text-align:left"><b> Father`s Fullname:</b></th>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b> Occupation:</b></th> 
              </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:center">'.$other[0]->lastName.'</th>
                <th style="width:25%;text-align:center">'.$other[0]->firstName.'</th> 
                <th style="width:25%;text-align:center">'.$other[0]->middleName.'</th>
                <th style="width:25%;border-left:0.5px solid black;text-align:left">&nbsp;'.$other[0]->occupation.'</th>
              </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:center"><b>(Last Name)</b></th>
                <th style="width:25%;text-align:center"><b>(First Name)</b></th> 
                <th style="width:25%;text-align:center"><b>(Middle Name)</b></th>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b></b></th>
              </tr>
              <tr>
                <th style="width:75%;border-top:0.5px solid black;text-align:left"><b> Mother`s Fullname:</b></th>
                <th style="width:25%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Occupation:</b></th> 
              </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:center">'.$other[2]->lastName.'</th>
                <th style="width:25%;text-align:center">'.$other[2]->firstName.'</th> 
                <th style="width:25%;text-align:center">'.$other[2]->middleName.'</th>
                <th style="width:25%;border-left:0.5px solid black;text-align:left">&nbsp;'.$other[2]->occupation.'</th>
              </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:center"><b>(Last Name)</b></th>
                <th style="width:25%;text-align:center"><b>(First Name)</b></th> 
                <th style="width:25%;text-align:center"><b>(Middle Name)</b></th>
                <th style="width:25%;border-left:0.5px solid black;text-align:left"><b></b></th>
              </tr> 
              <tr>
                <th style="width:75%;border-top:0.5px solid black;text-align:left"><b> Name of Person to contact in Case of Emergency:</b></th>
                <th style="width:10%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Relation:</b></th>
                <th style="width:15%;border-top:0.5px solid black;text-align:left">'.$other[1]->relation.'</th>
                </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:center">'.$other[1]->lastName.'</th>
                <th style="width:25%;text-align:center">'.$other[1]->firstName.'</th> 
                <th style="width:25%;border-right:0.5px solid black;text-align:center">'.$other[1]->middleName.'</th>
                <th style="font-size:6pt;width:25%;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:center"></th>
              </tr>
              <tr>
                <th style="width:25%;border-left:0.5px solid black;text-align:center"><b>(Last Name)</b></th>
                <th style="width:25%;text-align:center"><b>(First Name)</b></th> 
                <th style="width:25%;text-align:center"><b>(Middle Name)</b></th>
                <th style="width:10%;border-left:0.5px solid black;border-left:0.5px solid black;text-align:left"><b>&nbsp;Mobile No:</b></th>
                <th style="width:15%;text-align:left">'.$other[1]->mobileNo.'</th>
              </tr>   
            </table>
          </th>
        </tr>
        </table>
        <table>
        <tr>
        <th style="border:0.5px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>EDUCATIONAL BACKGROUND</b></th> 
        </tr>
        </table> 
        <table border="0.5">
        <tr>
        <th width="100%"><table>
         <tr>
           <th style="width:10%;text-align:left"><b> Elementary</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th> 
           <th style="width:50%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[1]->school_name.'</th> 
           <th style="width:15%;text-align:left"><b> Inclusive Years</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th>
           <th style="width:18%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[1]->inclusive_yr.'</th> 
          </tr>
          <tr>
           <th style="width:10%;text-align:left"><b> High School</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th> 
           <th style="width:50%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[2]->school_name.'</th> 
           <th style="width:15%;text-align:left"><b> Inclusive Years</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th>
           <th style="width:18%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[2]->inclusive_yr.'</th> 
          </tr>
          <tr>
           <th style="width:10%;text-align:left"><b> College</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th> 
           <th style="width:50%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[3]->school_name.'</th> 
           <th style="width:15%;text-align:left"><b> Inclusive Years</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th>
           <th style="width:18%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[3]->inclusive_yr.'</th> 
          </tr>
          <tr>
           <th style="width:15%;text-align:right"><b> Degree Received</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th> 
           <th style="width:45%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[0]->degree_received.'</th> 
           <th style="width:15%;text-align:left"><b> Honors Received</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th>
           <th style="width:18%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[0]->honors_received.'</th> 
          </tr>
          <tr>
           <th style="width:15%;text-align:left"><b> Graduate School</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th> 
           <th style="width:40%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[0]->school_name.'</th> 
           <th style="width:5%;text-align:left"><b> Title</b></th>
           <th style="width:2%;text-align:left"><b>:</b></th>
           <th style="width:33%;border-bottom:0.5px solid black;text-align:left">'.$bckgrnd[0]->title_received.'</th> 
          </tr>
          <tr>
          <th style="width:10%;text-align:left"></th>
          </tr>
        </table>
        </th>
        </tr>
        </table>
        <table>
        <tr>
        <th style="border:0.5px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>AFFILIATE ORGANIZATION REGISTERED WITH PYC</b></th> 
        </tr>
        </table> 
        <table border="0.5">
        <tr>
        <th width="100%"><table>';
            $lcnt = 0;
            foreach ($affOrg as $row) {
                if ($lcnt === 0) {
                    $html_content .= '
              <tr>
              <th style="width:35%;text-align:left"><b> Name of Organization:</b></th>
              <th style="width:30%;border-left:0.5px solid black;text-align:left"><b> Chapter:</b></th>
              <th style="width:20%;border-left:0.5px solid black;text-align:left"><b> Position/Designation:</b></th> 
              <th style="width:15%;border-left:0.5px solid black;text-align:left"><b> Year Affiliated:</b></th>
              </tr>';
                } else {
                    $html_content .= '
              <tr>
              <th style="width:35%;border-top:0.5px solid black;text-align:left"><b> Name of Organization:</b></th>
              <th style="width:30%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Chapter:</b></th>
              <th style="width:20%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Position/Designation:</b></th> 
              <th style="width:15%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Year Affiliated:</b></th>
              </tr>';
                }
                $html_content .= ' 
          <tr>
          <th style="width:35%;text-align:left"> '.$row->org_name.'</th>
          <th style="width:30%;border-left:0.5px solid black;text-align:left"> '.$row->chapter.'</th>
          <th style="width:20%;border-left:0.5px solid black;text-align:left"> '.$row->position.'</th> 
          <th style="width:15%;border-left:0.5px solid black;text-align:center">'.$row->year_affiliated.'</th>
          </tr>';
                $lcnt++;
            }
            if (count($affOrg) < 3) {
                $dfultLoop = 3 - count($affOrg);
                for ($x = 1; $x <= $dfultLoop; $x++) {
                    if ($x === 1 && count($affOrg) === 0) {
                        $html_content .= '
              <tr>
                <th style="width:35%;text-align:left"><b> Name of Organization:</b></th>
                <th style="width:30%;border-left:0.5px solid black;text-align:left"><b> Chapter:</b></th>
                <th style="width:20%;border-left:0.5px solid black;text-align:left"><b> Position/Designation:</b></th> 
                <th style="width:15%;border-left:0.5px solid black;text-align:left"><b> Year Affiliated:</b></th>
              </tr>';
                    } else {
                        $html_content .= '
              <tr>
              <th style="width:35%;border-top:0.5px solid black;text-align:left"><b> Name of Organization:</b></th>
              <th style="width:30%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Chapter:</b></th>
              <th style="width:20%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Position/Designation:</b></th> 
              <th style="width:15%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left"><b> Year Affiliated:</b></th>
              </tr>';
                    }
                    $html_content .= ' 
              <tr>
              <th style="width:35%;text-align:left"> </th>
              <th style="width:30%;border-left:0.5px solid black;text-align:left"> </th>
              <th style="width:20%;border-left:0.5px solid black;text-align:left"> </th> 
              <th style="width:15%;border-left:0.5px solid black;text-align:center"></th>
              </tr>';
                }
            }
            $html_content .= ' 
        </table>
        </th>
        </tr>
        </table>
        <table>
        <tr>
        <th style="border:0.5px solid black;text-align:center;color:#fcfffd;background-color:#2c2e2e;"><b>AGREEMENT</b></th> 
        </tr>
        </table>
        <table border="0.5">
        <tr>
        <th width="100%" font-size="6"><table>
        <br><br>
        <tr>
          <th style="width:5%;"></th>
          <th style="width:5%;"></th>
          <th style="width:5%;text-align:center">
          <input type="text" name="0" value="0" checked="" readonly="true"></th>
          <th style="width:85%;text-align:left"><h4><i> I hereby certify that the above information given are true and correct as to the best of my knowledge</i></h4></th>
        </tr>
        <br>
        <tr>
        <th style="width:35%;text-align:center"> '.$lists[0]->PersonName.'</th>
        <th style="width:2%;text-align:center"></th>
        <th style="width:26%;text-align:center"> </th> 
        <th style="width:2%;text-align:center"></th>
        <th style="width:35%;text-align:center"> </th>
      </tr>
        <tr>
          <th style="width:2%;text-align:center"></th>
          <th style="width:35%;border-top:0.5px solid black;text-align:center"><h5>SIGNATURE OVER PRINTED NAME OF APPLICANT</h5></th>
          <th style="width:2%;text-align:center"></th>
          <th style="width:26%;border-top:0.5px solid black;text-align:center"><h5>SECRETARY OF ORGANIZATION</h5></th> 
          <th style="width:2%;text-align:center"></th>
          <th style="width:30%;border-top:0.5px solid black;text-align:center"><h5>PROVINCIAL YOUTH COMMISSION DATA BANK</h5></th>
          <th style="width:2%;text-align:center"></th>
        </tr> 
        <br> 
        </table>
        </th>
        </tr>
        </table>
       ';
            //  dd($html_content);
            PDF::SetTitle('Members Profile Form');
            PDF::AddPage('', 'Legal');
            // PDF::Cell(5,5, 'FEMALE:');
            // PDF::CheckBox('female',5, true, array(), array(), 'OK');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printMemberProfileList(Request $request)
    {
        // dd($request);
        // $org = $request->orgID;
        // $_year = $request->from;
    
        // $list = DB::select('call balodoy_display_member_info_org(?,?)', array($org,$_year));
        $logo = config('variable.logo');
        try {
            $main=$request->main;
            // dd($main);
            // dd($main->{'Permit Number'});
        
            PDF::SetFont('Helvetica', '', '8');
            $html_content = '
              ' . $logo . ' 
              <h3 align="center">PYC MEMBER PROFILE MASTER LIST</h3>
              <br></br>
              <br></br>
              <br></br>
              <br></br> 
              <table style="padding:2px;">
              <thead>
              <tr>
              <th style="border:0.5px solid black;width:3%;text-align:center;background-color:#dedcdc;"><br><br><b>NO</b><br></th>
              <th style="border:0.5px solid black;width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>TRANSACTION NO</b><br></th>
              <th style="border:0.5px solid black;width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>TRANSACTION DATE</b><br></th>
              <th style="border:0.5px solid black;width:27%;text-align:center;background-color:#dedcdc;"><br><br><b>NAME OF ORGANIZATION</b><br></th>
              <th style="border:0.5px solid black;width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>MEMBER ID</b><br></th>
              <th style="border:0.5px solid black;width:18%;text-align:center;background-color:#dedcdc;"><br><br><b>MEMBER NAME</b><br></th>
              <th style="border:0.5px solid black;width:12%;text-align:center;background-color:#dedcdc;"><br><br><b>CHAPTER</b><br></th>
              <th style="border:0.5px solid black;width:10%;text-align:center;background-color:#dedcdc;"><br><br><b>STATUS</b><br></th>
              </tr>
              </thead>
              <tbody >';
            $ctr = 1;
            foreach ($main as $row) {
                $html_content .='
                  <tr >
                  <td style="border:0.5px solid black;width:3%;text-align:center;">' .$ctr. '</td>
                  <td style="border:0.5px solid black;width:10%;text-align:center;">' . $row['t_no'] . '</td>
                  <td style="border:0.5px solid black;width:10%;text-align:center;">' . $row['tdate'] . '</td>
                  <td style="border:0.5px solid black;width:27%;text-align:left;">' . $row['NameofOrganization'] . '</td>
                  <td style="border:0.5px solid black;width:10%;text-align:center;">' . $row['memID'] . '</td>
                  <td style="border:0.5px solid black;width:18%;text-align:left;">' . $row['PersonName'] . '</td>    
                  <td style="border:0.5px solid black;width:12%;text-align:left;">' . $row['chapter'] . '</td>
                  <td style="border:0.5px solid black;width:10%;text-align:center;">' . $row['Status'] . '</td>  
                  </tr>';
                $ctr++;
            }
            $ctr = $ctr - 1;
            $html_content .='<tr>
              <th colspan="2" style="border:0.5px solid black;text-align:right;height:20px;padding-top: 20px;"><b>TOTAL RECORDS</b></th>  
              <th colspan="17"style="border:0.5px solid black;text-align:left;height:20px;padding-top: 20px;"><b>'.$ctr.'</b></th>  
              </tr>';
            $html_content .='</tbody>
              </table>
              ';
            PDF::SetTitle('Member Profile List');
            PDF::AddPage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
