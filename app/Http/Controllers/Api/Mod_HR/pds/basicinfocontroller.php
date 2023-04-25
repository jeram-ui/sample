<?php

namespace App\Http\Controllers\Api\Mod_HR\pds;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class basicinfocontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
   
   
  public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }
    public function basicinfo(Request $request)
    {
      $list = DB::table($this->hr_db . '.employees')
      ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
        ->where('SysPK_Empl',Auth::user()->Employee_id)
        ->get();
      return response()->json(new JsonResponse($list));
    }

    public function print(Request $request){
    try{
        
            $basicinfo = DB::table($this->hr_db . '.employees')
            ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
              ->where('SysPK_Empl',Auth::user()->Employee_id)
              ->get();
              $basic=[];
              foreach ($basicinfo as $key => $value) {
                $basic=$value;
              }
      
              $family = DB::table($this->hr_db . '.employees_familybackground')
              ->where('emp_number',Auth::user()->Employee_id)
              ->get();
              $fam=[];
              foreach ($family as $key => $value) {
                $fam=$value;
              }
              $civilservice = DB::table($this->hr_db . '.employees_civilserviceeligibility')
                   ->where('emp_number',Auth::user()->Employee_id)
                   ->get();
                   $civil="";
                   foreach ($civilservice as $key => $value) {
                     $civil.='<tr>
                     <td height="15px" style="font-size:6pt;" align="center">  '.$value->cse_careerservice.'   </td>
                     <td style="font-size:6pt;" align="center"> '.$value->cse_rating.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->cse_dateofexam.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->cse_placeofexam.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->cse_licenseno.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->cse_datereleased.' </td>
                 </tr>';
                     
                   }
                   if (count($civilservice)<23) {
                      for ($i=count($civilservice); $i < 23; $i++) { 
                        $civil.=' <tr>
                        <td height="15px" style="font-size:6pt;" align="center"></td>
                        <td style="font-size:6pt;" align="center">  </td>
                        <td style="font-size:6pt;" align="center">  </td>
                        <td style="font-size:6pt;" align="center">  </td>
                        <td style="font-size:6pt;" align="center">  </td>
                        <td style="font-size:6pt;" align="center">  </td>
                    </tr> ';
                      }
                    }
      
      
              $education = DB::table($this->hr_db . '.employees_eduback')
                   ->where('emp_number',Auth::user()->Employee_id)     
                   ->get();
      
              $educ="";
              foreach ($education as $key => $value) {
                $educ.=' <tr>
                <td height="20px" style="font-size:7pt; " align="center"> '.$value->edu_level.' </td>
                <td style="font-size:7pt; "  align="center"> '.$value->edu_schoolname.' </td>
                <td style="font-size:6pt; "  align="center"> '.$value->edu_degree.' </td>
                <td style="font-size:7pt; "  align="center"> '.$value->edu_inclusivedatefrom.' </td>
                <td style="font-size:7pt; "  align="center"> '.$value->edu_inclusivedateto.' </td>
                <td style="font-size:7pt; "  align="center"> '.$value->edu_highgrade.' </td>
                <td style="font-size:7pt; "  align="center"> '.$value->edu_yeargraduated.' </td>
                <td style="font-size:7pt; "  align="center"> '.$value->edu_honor.' </td>
                </tr> ';
              }
              if (count($education)<6) {
                for ($i=count($education); $i < 6; $i++) { 
                  $educ.=' <tr>
                  <td height="20px" style="font-size:7pt; " align="center"></td>
                  <td style="font-size:7pt; "  align="center"></td>
                  <td style="font-size:7pt; "  align="center"></td>
                  <td style="font-size:7pt; "  align="center"></td>
                  <td style="font-size:7pt; "  align="center"></td>
                  <td style="font-size:7pt; "  align="center"></td>
                  <td style="font-size:7pt; "  align="center"></td>
                  <td style="font-size:7pt; "  align="center">  </td>
                  </tr> ';
                }
              }
      
              $empWork = DB::table($this->hr_db . '.employees_workexperience')
              ->where('emp_number',Auth::user()->Employee_id)
              ->get();
              $employment="";
              foreach ($empWork as $key => $value) {
                $employment.='  <tr>
                <td height="15px" style="font-size:6pt;" align="center"> '.$value->workexp_startdate.' </td>
                <td style="font-size:6pt;" align="center"> '.$value->workexp_enddate.' </td>
                <td style="font-size:6pt;" align="center"> '.$value->workexp_position.' </td>
                <td style="font-size:6pt;" align="center"> '.$value->workexp_company.' </td>
                <td style="font-size:6pt;" align="center"> '.$value->workexp_monthlysal.' </td>
                <td style="font-size:6pt;" align="center"> '.$value->workexp_salgrade.' </td>
                <td style="font-size:6pt;" align="center"> '.$value->workexp_statofemployment.' </td>
                <td style="font-size:6pt;" align="center"> '.$value->workexp_govser.' </td>            
            </tr> ';
              }
              if (count($empWork)<17) {
                  for ($i=count($empWork); $i < 17; $i++) { 
                    $employment.=' <tr>
                    <td height="15px" style="font-size:6pt;" align="center"></td>
                    <td style="font-size:6pt;" align="center">  </td>
                    <td style="font-size:6pt;" align="center">  </td>
                    <td style="font-size:6pt;" align="center">  </td>
                    <td style="font-size:6pt;" align="center">  </td>
                    <td style="font-size:6pt;" align="center">  </td>
                    <td style="font-size:6pt;" align="center">  </td>
                    <td style="font-size:6pt;" align="center">  </td>            
                </tr> ';
                  }
                }
      
                $volwork = DB::table($this->hr_db . '.employees_voluntarilywork')
                // ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
                     ->where('emp_number',Auth::user()->Employee_id)
                     ->where('status',0)
                     ->get();
                     $vwork="";
                     foreach ($volwork as $key => $value) {
                       $vwork.='  <tr>
                       <td height="13px" style="font-size:6pt;" align="center"> '.$value->Name_address_org.' </td>
                       <td style="font-size:6pt;" align="center"> '.$value->datefrom.' </td>
                       <td style="font-size:6pt;" align="center"> '.$value->dateto.' </td>
                       <td style="font-size:6pt;" align="center"> '.$value->No_ofHours.' </td>
                       <td style="font-size:6pt;" align="center"> '.$value->position.' </td>
                                 
                   </tr> ';
                     }
                     if (count($volwork)<9) {
                      for ($i=count($volwork); $i < 9; $i++) { 
                        $vwork.='  <tr>
                        <td height="13px" style="font-size:6pt;" align="center"> </td>
                        <td style="font-size:6pt;" align="center">  </td>
                        <td style="font-size:6pt;" align="center">  </td>
                        <td style="font-size:6pt;" align="center">  </td>
                        <td style="font-size:6pt;" align="center">  </td>         
                    </tr> ';
                      }
                    }
      
                    $training = DB::table($this->hr_db . '.employees_trainingprogram')
                         ->where('emp_number',Auth::user()->Employee_id)
                         ->get();
                         $train="";
                         foreach ($training as $key => $value) {
                           $train.='<tr>
                           <td  height="15px" style="font-size:7pt;" align="center"> '.$value->title_of_seminar.' </td>
                           <td style="font-size:7pt;" align="center"> '.$value->date_from.' </td>
                           <td style="font-size:7pt;" align="center"> '.$value->date_to.' </td>
                           <td style="font-size:7pt;" align="center"> '.$value->no_of_hour.' </td>
                           <td style="font-size:7pt;" align="center"> '.$value->type_of_LD.' </td>
                           <td style="font-size:7pt;" align="center"> '.$value->conducted.' </td>
                       </tr>';
                         }
                         if (count($training)<7) {
                          for ($i=count($training); $i < 7; $i++) { 
                            $train.='  <tr>
                            <td height="13px" style="font-size:6pt;" align="center"> </td>
                            <td style="font-size:6pt;" align="center">  </td>
                            <td style="font-size:6pt;" align="center">  </td>
                            <td style="font-size:6pt;" align="center">  </td>
                            <td style="font-size:6pt;" align="center">  </td>
                            <td style="font-size:6pt;" align="center">  </td>
                                     
                        </tr> ';
                          }
                        }
      
                        
                        $skillsH = DB::table($this->hr_db . '.employees_skillshobbies')
                          ->where('emp_number',Auth::user()->Employee_id)
                          ->get();
                          $skills="";
                         foreach ($skillsH as $key => $value) {
                           $skills.='  <tr>
                           <td rowspan="1" style="font-size:7pt;" align="center"> '.$value->skills_hobbies.' </td>
                           <td rowspan="2" style="font-size:6pt;" align="center"> '.$value->distinction_recognition.' </td>
                           <td style="font-size:7pt;" align="center"> '.$value->membership_org.' </td>   
                       </tr>  ';
                         }
                         if (count($skillsH)<4) {
                          for ($i=count($skillsH); $i < 4; $i++) { 
                            $skills.='  <tr>
                            <td height="13px" style="font-size:6pt;" align="center"> </td>
                            <td style="font-size:6pt;" align="center">  </td>
                            <td style="font-size:6pt;" align="center">  </td>
                            <td style="font-size:6pt;" align="center">  </td>
                            <td style="font-size:6pt;" align="center">  </td>         
                        </tr> ';
                          }
                        }

                        $reference = DB::table($this->hr_db . '.employees_reference')
                             ->where('emp_number',Auth::user()->Employee_id)
                             ->get();
                            
                             $ref1Name="";
                             $ref1Address="";
                             $ref1Tele="";
                             $ref2Name="";
                             $ref2Address="";
                             $ref2Tele="";
                             $ref3Name="";
                             $ref3Address="";
                             $ref3Tele="";



                         foreach ($reference as $key => $value) {
                             if ($key==0) {
                                $ref1Name=$value->reference_name;
                                $ref1Address=$value->address;
                                $ref1Tele=$value->tel_no;
                             }
                             
                             if ($key==1) {
                                $ref2Name=$value->reference_name;
                                $ref2Address=$value->address;
                                $ref2Tele=$value->tel_no;
                             }

                             if ($key==2) {
                                $ref3Name=$value->reference_name;
                                $ref3Address=$value->address;
                                $ref3Tele=$value->tel_no;
                             }
                         }

                         $otherinfo = DB::table($this->hr_db . '.employees_otherinfos')
                             ->where('emp_number',Auth::user()->Employee_id)
                             ->get();
                             $others=[];
                             foreach ($otherinfo as $key => $value) {
                               $others=$value;
                             }



        $Template='<table width="100%" style="border-left:1px solid black; border-top:1px solid black; border-right:1px solid black;">
        <tr>
            <td  style="font-size:8pt"><b> CS Form No.212 </b></td>
               
        </tr>
        <tr>
            <td  style="font-size:8pt"><b> Revised 2017 </b></td>
        </tr>
        <tr>
            <td width="100%" align="center"  style="font-size:14pt"><b> PERSONAL DATA SHEET </b> </td>
    
        </tr>
        <tr>
            <td width="100%" align="center" style="font-size:6pt"> WARNING: Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filling of administrative/criminal case/s against the person concerned </td>
        </tr>
        <tr>
            <td  style="font-size:7pt"><b> READ THE ATTACHED GUIDE TO FILLING OUT THE PERSONAL DATA SHEESH (PDS) BEFORE ACCOMPLISHING THE PDS FORM. </b></td>
        </tr>
        <table width="100%" style="border-bottom:1px solid black">
        <tr>
        
            <td width="20%" style="font-size:6pt; border-left:1px solid black;"> Print legibly. Tick appropriate boxez ( </td>
            <td width="50%" style="font-size:6pt">
            <input type="checkbox" check="true" name="1" value="1">
             ) and use separate sheet if necessary. Indicate N/A if not applicable. DO NOT ABBREVIATE.    
             </td>
             <td width="10%" style="font-size:7pt; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black;"> 1. CS ID No.</td>
             <td width="20%" style="font-size:5pt; border-top:1px solid black; border-right:1px solid black; border-bottom:1px solid black;" align="right"> (Do not fill up. For CSC use only)</td>
        </tr>
        </table>
        <tr>    
            <td height="18px" width="100%" style="font-size:10pt; background-color:grey; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black; color:white; " align="left"> I. PERSONAL INFORMATION</td>
        </tr>
        <table width="100%" cellpadding="4">
            <tr>
                <td height="15px" width="15%" align="center" style="font-size:8pt;border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; background-color:#C3BEBF; ">2. SURNAME </td>
                <td width="85%" style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black;">'.$basic->LastName_Empl.'</td>
            </tr>  
            <tr>
            <td height="15px" width="15%" align="center" style="font-size:8pt;border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> FIRST NAME </td>   
            <td height="15px" width="60%" style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->FirstName_Empl.' </td>
                <td height="15px" width="25%" style="font-size:6pt;border-right:1px solid black; background-color:#C3BEBF; border-bottom:1px solid black; "> NAME EXTENSION <br/>'.$basic->SuffixName_Empl.'</td>
            </tr>
            <tr>
                <td height="15px" width="15%" align="center" style="font-size:8pt;border-left:1px solid black; border-right:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;"> MIDDLE NAME </td>
                <td height="15px" width="85%" style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->MiddleName_Empl.' </td>
                </tr>
    
        </table>
            
                <tr>
                    
                    <td height="22px" width="15%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"> 3. DATE OF BIRTH (mm/dd/yyyy) </td>
                    <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->BirthDate_Empl.' </td>
                    <td width="25%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"> 16. CITIZENSHIP</td>
                    <td width="10%" style="font-size:7pt">
                    <input type="checkbox" check="true" name="1" value="1"> Filipino   
                    </td>
                    <td width="30%" style="font-size:6pt">
                    <input type="checkbox" check="true" name="1" value="1"> Dual Citizenship   
                    </td>
                </tr>
                <tr>
                    <td height="15px" width="15%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;background-color:#C3BEBF; border-left:1px solid black;"> PLACE OF BIRTH</td>
                    <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->birthplace.' </td>
                    <td width="25%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;" align="center"> If holder of dual citizenship, <br> please indicate the details</td>
                    <td width="10%"> </td>
                    <td width="12%" style="font-size:6pt"> 
                    <input type="checkbox" check="true" name="1" value="1"> by birth  <br> Pls. indicate country:
                    </td>
                    <td width="15%" style="font-size:6pt">
                    <input type="checkbox" check="true" name="1" value="1"> by naturalization 
                    </td>
                </tr>
                <tr>
                    <td height="15px" width="15%" style="font-size:7pt; border-bottom:1px solid black; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"> SEX </td>
                    <td width="12%" style="font-size:7pt; border-bottom:1px solid black;">
                    <input type="checkbox" checked="'.($basic->gender=== 'Male'? "true":"false").'" name="1" value="1"> Male</td>
                    <td width="12%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;">
                    <input type="checkbox" checked="'.($basic->gender=== 'Female'? "true":"false").'" name="1" value="1"> Female</td>
                    <td width="25%" style="font-size:7pt; border-right:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
                    <td width="33%" style="font-size:7pt; border-bottom:1px solid black; border-top:1px solid black;"></td>
                    <td width="3%" style="font-size:7pt; border-bottom:1px solid black;  border-top:1px solid black;  border-left:1px solid black;"></td>
                 </tr>
                <tr>
                    <td style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"> CIVIL STATUS </td>              
                    <td width="12%" style="font-size:7pt;">
                        <input type="checkbox" checked="'.($basic->civilStatus=== 'Single'? "true":"false").'" name="1" value="1"> Single</td>
                    <td width="12%" style="font-size:7pt; border-right:1px solid black; ">
                        <input type="checkbox" checked="'.($basic->civilStatus=== 'Married'? "true":"false").'" name="1" value="1"> Married</td>
                     <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"> 17. RESIDENTIAL </td>
                     <td width="2.5%" style="font-size:7pt;   "> </td>
                     <td width="25%" style="font-size:6pt;    "> '.$basic->RHouse_No.' </td>
                     <td width="16.5%" style="font-size:6pt;    "> '.$basic->RStreet.' </td>
                </tr>
                <tr>
                   <td  style="font-size:7pt; border-right:1px solid black;  background-color:#C3BEBF; border-left:1px solid black; "> </td>        
                   <td width="24%" style="font-size:7pt;  border-right:1px solid black; "></td>
                   <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;">ADDRESS</td>
                   <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                   <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">House/Block/Lot no.</td>
                   <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Street</td>
               </tr>
               
               <tr>
                    <td  style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"></td>        
                    <td width="12%" style="font-size:7pt;">
                    <input type="checkbox" checked="'.($basic->civilStatus=== 'Widowed'? "true":"false").'" name="1" value="1"> Widowed</td>
                    <td width="12%" style="font-size:7pt; border-right:1px solid black; ">
                    <input type="checkbox" checked="'.($basic->civilStatus=== 'Separated'? "true":"false").'" name="1" value="1"> Separated</td>
                    <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"></td>
                    <td width="3%" style="font-size:7pt; "></td>
                    <td width="23%" style="font-size:6pt; "> <b> '.$basic->RSubd_Village.' </b></td>
                    <td width="17%" style="font-size:6pt;  ">  <b> '.$basic->RBrgy.' </b></td>
               </tr>
               <tr>
                    <td  style="font-size:7pt; border-right:1px solid black;background-color:#C3BEBF; border-left:1px solid black;"></td>        
                    <td width="12%" style="font-size:7pt;  ">
                    </td>
                    <td width="12%" style="font-size:7pt; border-right:1px solid black; ">
                    </td>
                    <td width="17%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black; "></td>
                    <td width="3%" style="font-size:7pt; border-left:1px solid black; border-bottom:1px solid black; "></td>
                    <td width="24%" style="font-size:6pt; border-bottom:1px solid black; ">Subdivision/Village</td>
                    <td width="17%" style="font-size:6pt; border-bottom:1px solid black;  "> Barangay</td>
                </tr>
               <tr>
                    <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"></td>        
                    <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;">
                    <input type="checkbox" checked="'.($basic->civilStatus=== 'Other/s'? "true":"false").'" name="1" value="1"> Other/s:</td>
                    <td width="17%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black; "></td>
                    <td  width="3%" style="font-size:7pt; border-left:1px solid black;  "></td>
                    <td  width="24%" style="font-size:6pt;   "> <b> '.$basic->RCity_Mun.' </b> </td>
                    <td  width="17%" style="font-size:6pt; "> <b> '.$basic->RProvince.'</b></td>
             </tr>
             <tr>
                    <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;background-color:#C3BEBF; border-left:1px solid black; "> HEIGHT (m)</td>        
                    <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;">'.$basic->height.'</td>
                    <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"></td>
                    <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                    <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">City/Municipality</td>
                    <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Province</td>
             </tr>
             <tr>
                    <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> WEIGHT (kg)</td>        
                    <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;">'.$basic->weight.'</td>
                    <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> ZIP CODE</td>
                    <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->RZipcode.' </td>        
                    
            </tr>
            <tr>
                <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> <br> BLOOD TYPE</td>        
                <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->BloodType_Empl.' </td>
                <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;">18. PERMANENT</td>
                <td width="3%" style="font-size:7pt; "></td>
                <td width="24%" style="font-size:6pt; "><b> '.$basic->PCity_Mun.' </b></td>
                <td width="17%" style="font-size:6pt;  "><b> '.$basic->PStreet.' </b></td>
            </tr>
            <tr>
                   <td  style="font-size:7pt; border-right:1px solid black;  background-color:#C3BEBF; border-left:1px solid black; "> </td>        
                   <td width="24%" style="font-size:7pt;  border-right:1px solid black; "></td>
                   <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;">ADDRESS</td>
                   <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                   <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">City/Municipality</td>
                   <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Street</td>
               </tr>
            <tr>
                <td height="15px" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> <br> GSIS ID NO.</td>        
                <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black; ">'.$basic->GSIS_Empl.'</td>
                <td width="17%" style="font-size:7pt;background-color:#C3BEBF; border-right:1px solid black; "></td>
                <td width="3%" style="font-size:7pt; border-left:1px solid black; "></td>
                <td width="23%" style="font-size:6pt;  "> <b> '.$basic->PSubd_Village.' </b></td>
                <td width="17%" style="font-size:6pt; "> <b> '.$basic->PBrgy.' </b></td>
            </tr>
            
            <tr>
                <td  style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;">PAG-IBIG ID NO.</td>        
                <td width="24%" style="font-size:7pt;  border-right:1px solid black; ">'.$basic->pagibig_no.'</td>
                <td width="17%" style="font-size:7pt;background-color:#C3BEBF; border-right:1px solid black; "></td>
                <td width="3%" style="font-size:7pt; border-left:1px solid black; border-bottom:1px solid black; "></td>
                <td width="24%" style="font-size:6pt; border-bottom:1px solid black; ">Subdivision/Village</td>
                <td width="17%" style="font-size:6pt; border-bottom:1px solid black;  "> Barangay</td>
            </tr>
            <tr>
                    <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"></td>        
                    <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;"></td>
                    <td width="17%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black;   "></td>
                    <td  width="3%" style="font-size:7pt; border-left:1px solid black;  "></td>
                    <td  width="24%" style="font-size:6pt;   "> <b> '.$basic->PCity_Mun.' </b> </td>
                    <td  width="17%" style="font-size:6pt; "> <b> '.$basic->PProvince.' </b></td>
             </tr>
            <tr>
                <td  style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> PHILHEALTH NO.</td>        
                <td width="24%" style="font-size:7pt;  border-right:1px solid black; ">'.$basic->philhealth_no.'</td>
                <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"></td>
                <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">City/Municipality</td>
                <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Province</td>
            </tr>
            <tr>
                    <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "></td>        
                    <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black; "></td>
                    <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> ZIP CODE</td>
                    <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->PZipcode.' </td>        
                    
            </tr>
            <tr>
                <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> SSS NO. </td>
                <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black; "> '.$basic->SSS_Empl.' </td>
                <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; " > 19. TELEPHONE NO.</td>
                <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;">  </td>
            </tr>
            <tr>
                <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> TIN NO. </td>
                <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "> '.$basic->TIN_Empl.' </td>
                <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" > 20. MOBILE NO.</td>
                <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->empl_contactno.' </td>
            </tr>
            <tr>
                <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> AGENCY EMP NO.</td>
                <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "></td>
                <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" >E-MAIL ADD (if any)</td>
                <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->email_address.'   </td>
            </tr>
            <table width="100%" cellpadding="2">
                <tr>
                    <td width="100%" height="18px" style="font-size:10pt; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black; color:white;"> II. FAMILY BACKGROUND </td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">22. SPOUSES SURNAME</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_surname.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;background-color:#C3BEBF;"> 23. NAME OF CHILD (Write full name and list all) </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> DATE OF BIRTH (mm/dd/yyyy)</td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; "> FIRST NAME</td>
                    <td width="20%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_firstname.'</td>
                    <td width="17%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; " > NAME EXTENSION <br/> '.$fam->spouse_ext.' </td>
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; "> MIDDLE NAME</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_middlename.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; ">  OCCUPATION</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_occupation.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; ">  EMPLOYER/BUS NAME</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_employer.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black;background-color:#C3BEBF; ">  BUSINESS ADDRESS</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_employeradd.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; ">  TELEPHONE NO.</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_Telno.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">  24. FATHERS SURNAME</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->father_surname.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
                <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black;background-color:#C3BEBF; "> FIRST NAME</td>
                    <td width="20%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "> '.$fam->father_firstname.' </td>
                    <td width="17%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; ">NAME EXTENSION <br/> '.$fam->father_ext.' </td>   
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black"> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "> </td>
                 </tr>
            <tr>
                 <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; ">  MIDDLE NAME</td>
                 <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->father_middlename.'</td>
                 
                 <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                 <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
             </tr>
             <tr>
                    <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">  25. MOTHERS MAIDEN NAME</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->mother_surname.'</td>
                    
                    <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                </tr>
            <tr>
                 <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">  SURNAME</td>
                 <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->mother_surname.'</td>
                 
                 <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                 <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
             </tr>
             <tr>
                    <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; "> FIRST NAME</td>
                    <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "> '.$fam->mother_firstname.' </td> 
                    <td width="26%" style="font-size:6pt;"> </td>
                    <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "> </td>
            </tr>
            <tr>
                 <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; ">  MIDDLE NAME</td>
                 <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->mother_middlename.'</td>
                 
                 <td width="48%" style="font-size:7pt; color:red; border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; " align="center"> (Continue on separate sheet if necessary)</td>
                 
             </tr>
            </table>
    
            <table width="100%">
                <tr>
                    <td width="100%" height="18px" style="font-size:10pt; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black; color:white;"> III. EDUCATIONAL BACKGROUND </td>
                </tr>
            <table width="100%" border="1" cellpadding="2">
                <tr>
                    <td  width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;"> 26. <br> <br> <b>LEVEL</b></td>
                    <td  width="25%" rowspan="2" style="font-size:8pt; background-color:#C3BEBF; "  align="center"> <br/><br/> <b> NAME OF SCHOOL </b> <br/> (Write in full)</td>
                    <td  width="13.5%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF; "  align="center">  <br/> <b> BASIC EDUCATION/DEGREE/COURSE </b> (Write in full) </td>
                    <td  width="15%" colspan="2" style="font-size:7pt; background-color:#C3BEBF; " align="center" height="30"><b> PERIOD OF ATTENDANCE </b></td>
                    <td  width="14%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF; "  align="center"> <b> HIGHEST LEVEL/UNITS EARNED (if not graduated) </b></td>
                    <td  width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;"  align="center"> <br/><br/><b> YEAR GRADUATED </b> </td>
                    <td  rowspan="2" style="font-size:7pt; background-color:#C3BEBF;"  align="center"><b> SCHOLARSHIP/ACADEMIC HONORS RECEIVED </b></td>
          
                </tr>
                    <tr>
                            <td height="15px" style="font-size:7pt; background-color:#C3BEBF; " align="center"><b> From </b></td>
                            <td style="font-size:7pt; background-color:#C3BEBF; "  align="center"><b> TO </b></td>
                    </tr>
    
                 '.$educ.'
             
                <tr>
                    <td width="100%" height="9px" style="font-size:7pt;  color:red" align="center"> (Continue on separate sheet if necessary) </td>
                </tr>
    
            </table>
         
    
            <table width="100%">  
            <tr>
                <td width="12%"  height="15px" align="center" style="font-size:10pt; border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;" align="center"><b>SIGNATURE</b></td>
                <td width="43%"  style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
                <td width="12%"  height="12px" style="font-size:10pt; border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; " align="center"><b>DATE</b></td>
                <td width="33%" style="font-size:10pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
            </tr>
            <tr>
    
                <td  width="100%"  height="10px" style="font-size:7pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;" align="right"> CS FORM 212 (Revised 2017), Page 1 of 4 </td>
    
            </tr>
            </table>
            </table>
            </table>
           ';
        $Template2='<table width="100%" border="1" cellpadding="2">
        <tr>
                 
                 <td width="100%" height="18px" style="font-size:10pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black;"> IV. CIVIL SERVICE ELIGIBLITY </td>
                 </tr>
        <tr>
             
             <td rowspan="2" width="30%" style="font-size:8pt; background-color:#C3BEBF;" align="center"> 27. CAREER SERVICE/ RA 1080 (BOARD/BAR) UNDER SPECIAL LAW/CES/CSEE</td>
             <td rowspan="2" width="10%" style="font-size:8pt; background-color:#C3BEBF;" align="center"> RATING</td>
             <td rowspan="2" width="10%" style="font-size:8pt; background-color:#C3BEBF;" align="center"> DATE OF EXAMINATION/CONFERMENT</td>
             <td rowspan="2" width="30%" style="font-size:8pt; background-color:#C3BEBF;" align="center"> <br/>PLACE OF EXAMINATION / CONFERNMENT</td>
             <td width="20%" colspan="2" style="font-size:8pt; background-color:#C3BEBF;" align="center"> LICENSE (if applicable)</td>
                
         </tr>
         <tr>
             <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> NUMBER </td>
             <td style="font-size:6pt; background-color:#C3BEBF;" align="center"> DATE OF RELEASE </td>
         </tr>

         '.$civil.'
       
         <tr>
             <td height="13px" width="100%" style="font-size:7pt; color:red" align="center"> (Continue on separate sheet if necessary) </td>
         </tr>
             </table>
             <table width="100%">
             <tr>
                 <td width="100%" height="18px" style="font-size:10pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black;"> V. WORK EXPERIENCE (Include private employment. Start from your current work) </td>
             </tr>
             <table width="100%" border="1" cellpadding="2">
             <tr>
             
             <td colspan="2" width="18%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> 28.  INCLUSIVE DATES (mm/dd/yyyy)</td>
             <td rowspan="2" width="21%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> POSITION TITLE <br/> (Write in full) </td>
             <td rowspan="2" width="20%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> DEPARTMENT / AGENCY / OFFICE COMPANY <br/> (Write in full)</td>
             <td rowspan="2" width="10%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> MONTHLY SALARY</td>
             <td width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> SALARY GRADE & STEP INCREMENT (Format *00-0*)</td>
             <td width="13%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> STATUS OF APPOINTMENT</td>
             <td width="8%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> GOVT SERVICE (YES/NO)</td>
                
         </tr>
         <tr>
             <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> From </td>
             <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> TO </td>
         </tr>

         '.$employment.'
    
     <tr>
         <td width="100%" height="10px" style="font-size:6pt; color:red" align="center"> (Continue on separate sheet if necessary)</td>
     </tr>
     <tr>
         <td width="15%" style="font-size:10pt; background-color:#C3BEBF;" align="center"><b>SIGNATURE</b></td>
         <td width="38%"></td>
         <td width="10%" style="font-size:10pt; background-color:#C3BEBF;" align="center"><b>DATE</b></td>
         <td width="37%"></td>
     </tr>
     <tr>
         <td width="100%" height="10px" style="font-size:6pt;" align="right">CC FORM 212 (Revised 2017), Page 2 of 4</td>
     </tr>

         </table>
         </table>
          ';

        $Template3=' 
        <table width="100%" cellpadding="2">
            <tr>
                <td width="100%" height="18px" style="font-size:10pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black;"> VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENT / PEOPLE/ VOLUNTARY ORGANIZATION/S </td>
            </tr>
            <table width="100%" border="1" cellpadding="2">
            <tr>
            
                <td rowspan="2" width="45%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> 29.  NAME & ADDRESS OF ORGANIZATION <br/> (Write in full)</td>
                <td colspan="2" width="21%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> INCLUSIVE DATES <br/> (mm/dd/yyyy)</td>
                <td rowspan="2" width="10%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> NUMBER OF HOURS (Write in full)</td>
                <td rowspan="2" width="24%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> POSITION / NATURE OF WORK</td>
               
            </tr>
            <tr>
                <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> From </td>
                <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> TO </td>
            </tr>
               
            '.$vwork.'

            <tr>
                <td width="100%" style="font-size:6pt; color:red" align="center"> (Continue on separate sheet if necessary) </td>
            </tr>

            </table>
            
            
            <table width="100%">
            <tr>
            
       
            
            <td width="100%" height="18px" style="font-size:10pt; color:white; background-color:grey; border-right:1px solid black; border-left:1px solid black; border-top:1px solid black;"> VII.LEARNING AND DEVELOPMENT (L&D) INTERENTIONS PROGRAMS ATTENDED </td>
               
            </tr>
            <tr>
                <td style="font-size:8pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black;"> (Start from the most recent L & D/training program include only yhe relevant L&D/training taken for the last five (5) years for Division Chief/Executive/Managerial positions)</td>
            </tr>
            <table width="100%" border="1">
            <tr>
            
                <td width="38%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> 30. TITLE OF SEMINAR/CONFERENCE/WORKSHOP/SHORT COURSES <br/> (Write in full) </td>
                <td width="17%" style="font-size:7pt; background-color:#C3BEBF;" align="center" colspan="2"> INCLUSIVE DATES OF ATTENDANCE <br/>(mm/dd/yyyy) </td>
                <td width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> <br/>NUMBER OF HOURS </td>
                <td width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> Type of  LD(Managerial/Supervisory/Tehnical etc.) </td>
                <td width="25%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> CONDUCTED/ SPONSORED BY <br/> (write in full) </td>
               
         
            </tr>
                <tr>
                    <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> FROM </td>
                    <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> TO </td>
                </tr>
               '.$train.' 
            </table>
            
            <table width="100%" cellpadding="2" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;">
                <tr>
                    <td width="100%" height="150px" style="font-size:7pt;"></td>
                </tr>
                <tr>
                    <td width="100%"  style="border-top:1px solid black; font-size:7pt; color:red" align="center"> (Continue on separate sheet if necessary)</td>
                </tr>
                <tr>
                    <td width="100%" height="18px" style="font-size:10pt; color:white; background-color:grey; border-right:1px solid black; border-left:1px solid black; border-top:1px solid black;"> VIII. OTHER INFORMATION </td>
                </tr>
                <table width="100%" border="1" cellpadding="2">
                <tr>
                    <td style="font-size:7pt; background-color:#C3BEBF;">31. SPECIAL SKILLS/HOBBIES</td>
                    <td style="font-size:7pt; background-color:#C3BEBF;">32. NON-ACADEMIC DISTINCTIONS/RECOGNITION <br/> (Write in full)</td>
                    <td style="font-size:7pt; background-color:#C3BEBF;">33.  MEMBERSHIP IN ASSOCIATION/ORGNAZITION <br/> (Write in full) </td>
                </tr>
                   '.$skills.'
                <tr>
                    <td width="100%" height="55px" style="font-size:7pt;"></td>
                </tr>
                <tr>
                    <td width="100%" height="15px" style="font-size:7pt; color:red" align="center"> (Continue on separate sheet if necessary)</td>
                </tr>
                
                 <tr>
                    
                        <td width="13%"  height="15px" align="center" style="font-size:10pt; border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"><b> SIGNATURE </b></td>
                        <td width="42%"  style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
                        <td width="12%" style="font-size:10pt; border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; " align="center"><b> DATE </b></td>
                        <td width="33%" style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
                    </tr>
                    <tr>

                        <td  width="100%" style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;" align="right"> CS FORM 212 (Revised 2017), Page 3 of 4 </td>
                    </tr>
                </table>
                </table>
                </table>
                </table>';
                
                $Template4=' <table width="100%" style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black;">
                <tr>
                    <td width="65%" style="font-size:8pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> 34. Are you related by consanguinity or affinity to the appointing or recommending authority or to the chief of bureau or office or the person you who has immediate supervision over you in the Office, Burueau of Department  where you will be appointed,
                    </td>
                    <td width="10%" style="font-size:7pt;">

                    </td>
                    <td width="25%" style="font-size:7pt;"> 
                    
                    </td>
                </tr>
                <tr>
                    <td  width="65%" style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> a. within the third degree?</td>
                    <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_1A=== 'Yes'? "true":"false").'" name="1" value="1">
                   YES
                    </td>
                    <td width="10%" style="font-size:8pt;"> <input type="checkbox" checked="'.($others->otherinfo_1A=== 'No'? "true":"false").'" name="1" value="1">
                   NO
                    </td>
                </tr>
                <tr>
                    <td  width="65%" style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> b. within the fourth degree(forLocal Government Unit - Career Employee</td>
                    <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_1B=== 'Yes'? "true":"false").'" name="1" value="1">
                   YES
                    </td>
                    <td width="10%" style="font-size:8pt;"> <input type="checkbox" checked="'.($others->otherinfo_1B=== 'No'? "true":"false").'" name="1" value="1">
                   NO
                    </td>
                </tr>
            <tr>
                <td  width="65%" style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="20%" style="font-size:7pt;"> 
               If YES, give details;
                </td>  
            </tr>
            <tr>
                <td  width="65%" style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="5%"> </td>
                <td width="29%" style="font-size:7pt; border-bottom:1px solid black;"> '.$others->otherinfo_1BDesc.' 
                </td>
         </tr>
            <tr>
                <td width="65%" style="font-size:8pt; border-left:1px solid black; border-bottom:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="35%" style="font-size:7pt; border-left:1px solid black;border-bottom:1px solid black;"> 
                </td>
            </tr>
            <tr>
            
                    <td  width="65%" style="font-size:8pt; background-color:#C3BEBF; border-right:1px solid black; border-left:1px solid black;" >35.  a. Have you ever been found guilty of any administrative offense? </td>
                    <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_2A=== 'Yes'? "true":"false").'" name="1" value="1">
                    YES
                    </td>
                    <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_2A=== 'No'? "true":"false").'" name="1" value="1">
                    NO
                    </td>
            </tr>
            <tr>
                <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="20%" style="font-size:7pt;"> 
                If YES, give details;
                </td>  
            </tr>
            <tr>
                <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="5%" style="border-left;1px solid black;"> </td>
                <td width="29%" style="font-size:7pt; border-bottom:1px solid black;">  '.$others->otherinfo_2ADESC.'    
                </td>
            </tr>
            <tr>
                <td width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"></td>
                <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
                </td>
            </tr>
            <tr>
            
                    <td  width="65%" style="font-size:8pt; border-left:1px solid black; background-color:#C3BEBF; border-right:1px solid black;" > b. Have you been criminally charged before any court?</td>
                    <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_2B=== 'Yes'? "true":"false").'" name="1" value="1">
                    YES
                    </td>
                    <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_2B=== 'No'? "true":"false").'" name="1" value="1">
                    NO
                    </td>
            </tr>
            <tr>
                <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="20%" style="font-size:7pt;"> 
                If YES, give details;
                </td>  
            </tr>
            <tr>
                <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="15%" style="border-left;1px solid black;font-size:7pt;" align="right"> Date Filed: </td>
                <td width="18%" style="font-size:7pt; border-bottom:1px solid black;">  '.$others->otherinfo_2BDatefile.'     
                </td>
            </tr>
            <tr>
                <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="15%" style="border-left;1px solid black;font-size:7pt;" align="right">Status of Case/s:</td>
                <td width="18%" style="font-size:7pt; border-bottom:1px solid black;">   '.$others->otherinfo_2BDesc.'    
                </td>
             </tr>
             <tr>
                <td width="65%" style="font-size:7pt; border-left:1px solid black; border-bottom:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                <td width="35%" style="font-size:7pt; border-left:1px solid black;border-bottom:1px solid black;"> 
                </td>
            </tr>
            <tr>
            
            
            <td  width="65%" style="font-size:8pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; " >36. Have you ever been convicted of any crime or violation of any law, devree, ordinance or regulation by any court or tribunal </td>
            <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_3=== 'Yes'? "true":"false").'" name="1" value="1">
            YES
            </td>
            <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_3=== 'No'? "true":"false").'" name="1" value="1">
            NO
            </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="20%" style="font-size:7pt;"> 
        If YES, give details;
        </td>  
    </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="5%" style="border-left;1px solid black;"> </td>
        <td width="29%" style="font-size:7pt; border-bottom:1px solid black;"> '.$others->otherinfo_3Desc.'    
        </td>
    </tr>
    <tr>
        <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
        <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
        </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:8pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" >37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished conract or phased out(abolition) in the public or private sector</td>
        <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_4=== 'Yes'? "true":"false").'" name="1" value="1">
        YES
        </td>
        <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_4=== 'No'? "true":"false").'" name="1" value="1">
        NO
        </td>
    </tr>
     <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="20%" style="font-size:7pt;"> 
        If YES, give details;
        </td>  
    </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="5%" style="border-left;1px solid black;"> </td>
        <td width="29%" style="font-size:7pt; border-bottom:1px solid black;">   '.$others->otherinfo_4Desc.'  
        </td>
    </tr>
    <tr>
        <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
        <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
        </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:8pt; border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; background-color:#C3BEBF;" >38. a. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?</td>
        <td width="10%" style="font-size:7pt; border-left:1px solid black; border-top:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_5A=== 'Yes'? "true":"false").'" name="1" value="1">
        YES
        </td>
        <td width="10%" style="font-size:7pt; border-top:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_5A=== 'No'? "true":"false").'" name="1" value="1">
        NO
        </td>
        <td width="15%" style="border-top:1px solid black"> </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="15%" style="font-size:7pt;"> 
        If YES, give details: </td>
        <td width="18%" style="font-size:7pt; border-bottom:1px solid black;">'.$others->otherinfo_5ADesc.'</td>    
    </tr>
    <tr>
   
        <td  width="65%" style="font-size:8pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" >b. Have you resigned from the government service during the three(3)-month period before the last election to promote/actively campaign for national or local candidate? </td>
        <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_5B=== 'Yes'? "true":"false").'" name="1" value="1">
        YES
        </td>
        <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_5B=== 'No'? "true":"false").'" name="1" value="1">
        NO
        </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="15%" style="font-size:7pt;"> 
        If YES, give details: </td>
        <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"> '.$others->otherinfo_5BDesc.' </td>    
            
    </tr>
    
    <tr>
        <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
        <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
        </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:8pt; background-color:#C3BEBF; border-right:1px solid black; border-left:1px solid black;" >39. Have you acquired the status of an immigrant or permanent resident of another country?</td>
        <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_6=== 'Yes'? "true":"false").'" name="1" value="1">
        YES
        </td>
        <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_6=== 'No'? "true":"false").'" name="1" value="1">
        NO
        </td>
        </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; border-right:1px solid black;"> </td>
        <td width="20%" style="font-size:7pt;"> 
        If YES, give details (country);
        </td>  
    </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="5%" style="border-left;1px solid black;"> </td>
        <td width="29%" style="font-size:7pt; border-bottom:1px solid black;"> '.$others->otherinfo_6Desc.'      
        </td>
    </tr>
    <tr>
        <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
        <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
        </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:8pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" >40. Pursuant to: (a) Indigenous Peoples Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277); and (c) Solo Parents Welfare Act of 2000 (RA 8972), please answer the following items:</td>
    </tr>
    
        <tr>

            <td  width="65%" style="font-size:8pt;  border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;">a. Are you a member of any indigenous group</td>
            <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_7A=== 'Yes'? "true":"false").'" name="1" value="1">
            YES
            </td>
            <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_7A=== 'No'? "true":"false").'" name="1" value="1">
            NO
            </td>
        </tr>
        <tr>
            <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
            <td width="15%" style="font-size:7pt;"> 
            If YES, give details: </td>
            <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"> '.$others->otherinfo_7ADesc.' </td>    
                
        </tr>

        <tr>

            <td  width="65%" style="font-size:8pt;  border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;">b. Are you a a person with disability?</td>
            <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_7B=== 'Yes'? "true":"false").'" name="1" value="1">
            YES
            </td>
            <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_7B=== 'No'? "true":"false").'" name="1" value="1">
            NO
            </td>
        </tr>
        <tr>
            <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
            <td width="15%" style="font-size:7pt;"> 
            If YES, give details: </td>
            <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"> '.$others->otherinfo_7BDesc.' </td>    
                
        </tr>

        <tr>

        <td  width="65%" style="font-size:8pt;  border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;">c. Are you a solo parent?</td>
        <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" checked="'.($others->otherinfo_7C=== 'Yes'? "true":"false").'" name="1" value="1">
        YES
        </td>
        <td width="10%" style="font-size:7pt;"> <input type="checkbox" checked="'.($others->otherinfo_7C=== 'No'? "true":"false").'" name="1" value="1">
        NO
        </td>
    </tr>
    <tr>
        <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
        <td width="15%" style="font-size:7pt;"> 
        If YES, give details: </td>
        <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"> '.$others->otherinfo_7CDesc.' </td>    
            
    </tr>


        <tr>
            <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
            <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
            </td>
        </tr>
        </table> 
        <table width="100%" style="border-right:1px solid black;">
         <tr>
             <td width="15%" height="15px" style="font-size:8pt; ;border-left:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;">41. REFERENCES</td>
             <td width="60%" height="15px" style="font-size:7pt; color:red;border-right:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;">(Person not related by consanguinity or affinity to applicant/appointee) </td>
             <td width="3%"> </td>
             <td width="20%"> </td>
             <td width="2%"> </td>
         </tr>
          <tr>
             <td width="27%" height="10px" style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> <b><br/>NAME</b></td>
             <td width="28%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"><b><br/>ADDRESS</b></td>
             <td width="20%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"><b><br/>TEL.NO.</b></td>
                 
             <td width="3%"  style="font-size:7pt; border-right:1px solid black; "> </td>
             <td width="20%"  style="font-size:7pt;  border-right:1px solid black; border-top:1px solid black;"> ID picture taken within the last 6 months 3.5cm. x.4.5cm. <br/> (passport size) </td>
             <td width="2%"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;"> </td>
          </tr>
          <tr>
          <td width="27%"  style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref1Name.'</td>
          <td width="28%"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref1Address.'</td>
          <td width="20%"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref1Tele.' </td>
                 
             <td width="3%"  style="font-size:7pt; border-right:1px solid black; "> </td>
             <td width="20%"  style="font-size:7pt; border-right:1px solid black; " align="center"></td>
             <td width="2%"  style="font-size:7pt; border-right:1px solid black;"> </td>         
          </tr>
          <tr>
          <td width="27%"  style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref2Name.'</td>
          <td width="28%"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref2Address.'</td>
          <td width="20%"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref2Tele.' </td>
                 
             <td width="3%"  style="font-size:7pt; border-right:1px solid black; "> </td>
             <td width="20%"  style="font-size:7pt; border-right:1px solid black; " align="center"> With full and handwritten name</td>
             <td width="2%"  style="font-size:7pt; border-right:1px solid black;"> </td>         
          </tr>
          <tr>
          <td width="27%"  style="font-size:8pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref3Name.'</td>
          <td width="28%"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref3Address.'</td>
          <td width="20%"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; " align="center"> '.$ref3Tele.' </td>
                 
             <td width="3%"  style="font-size:7pt; border-right:1px solid black; "> </td>
             <td width="20%"  style="font-size:6pt; border-right:1px solid black; " align="center"> tag and signature over printed name </td>
             <td width="2%"  style="font-size:7pt; border-right:1px solid black;"> </td>         
          </tr>
         
          <tr>
             <td width="75%"  style="border-left:1px solid black;  border-right:1px solid black;"> </td>
             <td width="3%" style="border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="20%" style="border-right:1px solid black; "> </td>
             <td width="2%"> </td>
          </tr>
          <tr>
             <td width="75%"  style="font-size:8pt; border-left:1px solid black;  border-right:1px solid black; border-top:1px solid black; background-color:#C3BEBF;"> 42. I declare under oath that I have accomplished this Personal Data Sheet which is true,
              correct and complete statement pursuant to the provisions of pertinent laws,
               rules and regulations of the Republic of the Philippinnes.
                I authorize the agency head/authorized representative to verify/validate the contents stated herin.
                 I agree that any misinterpretation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me. </td>
             <td width="3%" style="border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="20%" style="border-bottom:1px solid black; border-right:1px solid black; font-size:7pt; " align="center"> Computer generated or photocopied picture is not acceptable </td>
             <td width="2%"> </td>
          </tr>
          <tr>
             <td width="75%" style="font-size:7pt; border-left:1px solid black;  border-right:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;  "> </td>
             <td width="3%"> </td>
             <td width="20%" style="font-size:8pt;" align="center"> PHOTO </td>
             <td width="2%"> </td>
          
          </tr>
          <tr>
             <td width="40%" style="font-size:6pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> Government Issued ID (i.e.Passport, GSIS, SSS, PRC, Drivers License, etc.) </td>
             <td width="1%"> </td>
             <td width="34%" style="border-right:1px solid black; border-left:1px solid black; "> </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black;" align="center"></td>   
          </tr>
          <tr>
             <td width="40%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;"> PLEASE INDICATE ID Number and Date of Issuance </td>
             <td width="1%"> </td>
             <td width="34%" style="border-right:1px solid black; border-left:1px solid black;"> </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black;" align="center"></td>   
         </tr>
         <tr>
             <td width="15%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;"> Government Issued ID: </td>
             <td width="25%"  style="border-right:1px solid black; border-bottom:1px solid black;" > </td>
             <td width="1%"> </td>
             <td width="34%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black; border-bottom:1px solid black;  border-top:1px solid black;" align="center"> SIGNATURE (Sign inside the box) </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black;" align="center"></td>   
         </tr>
         <tr>
             <td width="15%" style="font-size:6pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;"> ID/License/Passport No.: </td>
             <td width="25%"  style="border-right:1px solid black; border-bottom:1px solid black;" > </td>
             <td width="1%"> </td>
             <td width="34%" style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; border-top:1px solid black;" align="center"> </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black;" align="center"></td>   
         </tr>
         <tr>
             <td width="15%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;">Date/Place of Issuance: </td>
             <td width="25%"  style="border-right:1px solid black; border-bottom:1px solid black;" > </td>
             <td width="1%"> </td>
             <td width="34%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; border-top:1px solid black;" align="center"> Date Accomplished </td>
             <td width="2%"> </td>
             <td width="22%" style="font-size:7pt; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" align="center"> Right Thumbmark</td>   
         </tr>
         <tr>
             <br/>
             <td width="29%"  style="font-size:7pt; border-left:1px solid black; border-top:1px solid black;"> SUBSCRIBED AND SWORN to before me this</td>
             <td width="17%"  style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black;"> </td>
             <td width="54%"  style="font-size:7pt; border-top:1px solid black;">, affiant exhibiting his/her validly issued government ID as indicated above. </td>
         </tr>
         <tr>
             <td width="100%" style="border-left:1px solid black"></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-top:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:7pt; border-left:1px solid black; border-top:1px solid black; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> Person Administering Oath </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="100%" style="border-bottom:1px solid black; border-left:1px solid black"></td>
         </tr>
         <tr>
             <td width="100%" height="10px" style="font-size:7pt; border-bottom:1px solid black; border-left:1px solid black" align="right">CS FORM 212(Revised 2017), Page 4 of 4</td>
         </tr>
                                                                                                                                                                 
     
      </table>';

         PDF::SetTitle('PERSONAL DATA SHEET');
         PDF::SetFont('helvetica', '', 8);

         PDF::AddPage('P', array(215.9,355.6));
        PDF::lastPage();
         PDF::writeHTML($Template, true, 0, true, 0);
         PDF::AddPage('P', array(215.9,355.6));
         PDF::writeHTML($Template2, true, 0, true, 0);
         PDF::AddPage('P', array(215.9,355.6));
         PDF::writeHTML($Template3, true, 0, true, 0);
         PDF::AddPage('P', array(215.9,355.6));
         PDF::writeHTML($Template4, true, 0, true, 0);

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
    public function print2(Request $request){
        try{
          $basicinfo = DB::table($this->hr_db . '.employees')
          ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
            ->where('SysPK_Empl',Auth::user()->Employee_id)
            ->get();
            $basic=[];
            foreach ($basicinfo as $key => $value) {
              $basic=$value;
            }
    
            $family = DB::table($this->hr_db . '.employees_familybackground')
            ->where('emp_number',Auth::user()->Employee_id)
            ->get();
            $fam=[];
            foreach ($family as $key => $value) {
              $fam=$value;
            }
            $civilservice = DB::table($this->hr_db . '.employees_civilserviceeligibility')
                 ->where('emp_number',Auth::user()->Employee_id)
                 ->get();
                 $civil="";
                 foreach ($civilservice as $key => $value) {
                   $civil.='<tr>
                   <td height="13px" style="font-size:6pt;" align="center">  '.$value->cse_careerservice.'   </td>
                   <td style="font-size:6pt;" align="center"> '.$value->cse_rating.' </td>
                   <td style="font-size:6pt;" align="center"> '.$value->cse_dateofexam.' </td>
                   <td style="font-size:6pt;" align="center"> '.$value->cse_placeofexam.' </td>
                   <td style="font-size:6pt;" align="center"> '.$value->cse_licenseno.' </td>
                   <td style="font-size:6pt;" align="center"> '.$value->cse_datereleased.' </td>
               </tr>';
                   
                 }
                 if (count($civilservice)<15) {
                    for ($i=count($civilservice); $i < 15; $i++) { 
                      $civil.=' <tr>
                      <td height="13px" style="font-size:6pt;" align="center"></td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                  </tr> ';
                    }
                  }
    
    
            $education = DB::table($this->hr_db . '.employees_eduback')
                 ->where('emp_number',Auth::user()->Employee_id)     
                 ->get();
    
            $educ="";
            foreach ($education as $key => $value) {
              $educ.=' <tr>
              <td height="20px" style="font-size:7pt; " align="center"> '.$value->edu_level.' </td>
              <td style="font-size:7pt; "  align="center"> '.$value->edu_schoolname.' </td>
              <td style="font-size:6pt; "  align="center"> '.$value->edu_degree.' </td>
              <td style="font-size:7pt; "  align="center"> '.$value->edu_inclusivedatefrom.' </td>
              <td style="font-size:7pt; "  align="center"> '.$value->edu_inclusivedateto.' </td>
              <td style="font-size:7pt; "  align="center"> '.$value->edu_highgrade.' </td>
              <td style="font-size:7pt; "  align="center"> '.$value->edu_yeargraduated.' </td>
              <td style="font-size:7pt; "  align="center"> '.$value->edu_honor.' </td>
              </tr> ';
            }
            if (count($education)<6) {
              for ($i=count($education); $i < 6; $i++) { 
                $educ.=' <tr>
                <td height="20px" style="font-size:7pt; " align="center"></td>
                <td style="font-size:7pt; "  align="center"></td>
                <td style="font-size:7pt; "  align="center"></td>
                <td style="font-size:7pt; "  align="center"></td>
                <td style="font-size:7pt; "  align="center"></td>
                <td style="font-size:7pt; "  align="center"></td>
                <td style="font-size:7pt; "  align="center"></td>
                <td style="font-size:7pt; "  align="center">  </td>
                </tr> ';
              }
            }
    
            $empWork = DB::table($this->hr_db . '.employees_workexperience')
            ->where('emp_number',Auth::user()->Employee_id)
            ->get();
            $employment="";
            foreach ($empWork as $key => $value) {
              $employment.='  <tr>
              <td height="13px" style="font-size:6pt;" align="center"> '.$value->workexp_startdate.' </td>
              <td style="font-size:6pt;" align="center"> '.$value->workexp_enddate.' </td>
              <td style="font-size:6pt;" align="center"> '.$value->workexp_position.' </td>
              <td style="font-size:6pt;" align="center"> '.$value->workexp_company.' </td>
              <td style="font-size:6pt;" align="center"> '.$value->workexp_monthlysal.' </td>
              <td style="font-size:6pt;" align="center"> '.$value->workexp_salgrade.' </td>
              <td style="font-size:6pt;" align="center"> '.$value->workexp_statofemployment.' </td>
              <td style="font-size:6pt;" align="center"> '.$value->workexp_govser.' </td>            
          </tr> ';
            }
            if (count($empWork)<9) {
                for ($i=count($empWork); $i < 9; $i++) { 
                  $employment.=' <tr>
                  <td height="13px" style="font-size:6pt;" align="center"></td>
                  <td style="font-size:6pt;" align="center">  </td>
                  <td style="font-size:6pt;" align="center">  </td>
                  <td style="font-size:6pt;" align="center">  </td>
                  <td style="font-size:6pt;" align="center">  </td>
                  <td style="font-size:6pt;" align="center">  </td>
                  <td style="font-size:6pt;" align="center">  </td>
                  <td style="font-size:6pt;" align="center">  </td>            
              </tr> ';
                }
              }
    
              $volwork = DB::table($this->hr_db . '.employees_voluntarilywork')
              // ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
                   ->where('emp_number',Auth::user()->Employee_id)
                   ->where('status',0)
                   ->get();
                   $vwork="";
                   foreach ($volwork as $key => $value) {
                     $vwork.='  <tr>
                     <td height="13px" style="font-size:6pt;" align="center"> '.$value->Name_address_org.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->datefrom.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->dateto.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->No_ofHours.' </td>
                     <td style="font-size:6pt;" align="center"> '.$value->position.' </td>
                               
                 </tr> ';
                   }
                   if (count($volwork)<9) {
                    for ($i=count($volwork); $i < 9; $i++) { 
                      $vwork.='  <tr>
                      <td height="13px" style="font-size:6pt;" align="center"> </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>
                      <td style="font-size:6pt;" align="center">  </td>         
                  </tr> ';
                    }
                  }
    
                  $training = DB::table($this->hr_db . '.employees_trainingprogram')
                       ->where('emp_number',Auth::user()->Employee_id)
                       ->get();
                       $train="";
                       foreach ($training as $key => $value) {
                         $train.='<tr>
                         <td  height="15px" style="font-size:7pt;" align="center"> '.$value->title_of_seminar.' </td>
                         <td style="font-size:7pt;" align="center"> '.$value->date_from.' </td>
                         <td style="font-size:7pt;" align="center"> '.$value->date_to.' </td>
                         <td style="font-size:7pt;" align="center"> '.$value->no_of_hour.' </td>
                         <td style="font-size:7pt;" align="center"> '.$value->type_of_LD.' </td>
                         <td style="font-size:7pt;" align="center"> '.$value->conducted.' </td>
                     </tr>   ';
                       }
                       if (count($training)<7) {
                        for ($i=count($training); $i < 7; $i++) { 
                          $train.='  <tr>
                          <td height="13px" style="font-size:6pt;" align="center"> </td>
                          <td style="font-size:6pt;" align="center">  </td>
                          <td style="font-size:6pt;" align="center">  </td>
                          <td style="font-size:6pt;" align="center">  </td>
                          <td style="font-size:6pt;" align="center">  </td>
                          <td style="font-size:6pt;" align="center">  </td>
                                   
                      </tr> ';
                        }
                      }
    
                      
                      $skillsH = DB::table($this->hr_db . '.employees_skillshobbies')
                        ->where('emp_number',Auth::user()->Employee_id)
                        ->get();
                        $skills="";
                       foreach ($skillsH as $key => $value) {
                         $skills.='  <tr>
                         <td rowspan="1" style="font-size:7pt;" align="center"> '.$value->skills_hobbies.' </td>
                         <td rowspan="2" style="font-size:6pt;" align="center"> '.$value->distinction_recognition.' </td>
                         <td style="font-size:7pt;" align="center"> '.$value->membership_org.' </td>   
                     </tr>  ';
                       }
                       if (count($skillsH)<4) {
                        for ($i=count($skillsH); $i < 4; $i++) { 
                          $skills.='  <tr>
                          <td height="13px" style="font-size:6pt;" align="center"> </td>
                          <td style="font-size:6pt;" align="center">  </td>
                          <td style="font-size:6pt;" align="center">  </td>
                          <td style="font-size:6pt;" align="center">  </td>
                          <td style="font-size:6pt;" align="center">  </td>         
                      </tr> ';
                        }
                      }
    
    
    
         $Template =' <table width="100%" style="border-left:1px solid black; border-top:1px solid black; border-right:1px solid black;">
         <tr>
             <td  style="font-size:8pt"><b> CS Form No.212 </b></td>
            
             
         </tr>
         <tr>
             <td  style="font-size:8pt"><b> Revised 2017 </b></td>
         </tr>
         <tr>
             <td width="100%" align="center"  style="font-size:14pt"><b> PERSONAL DATA SHEET </b> </td>
     
         </tr>
         <tr>
             <td width="100%" align="center" style="font-size:6pt"> WARNING: Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filling of administrative/criminal case/s against the person concerned </td>
         </tr>
         <tr>
             <td  style="font-size:7pt"><b> READ THE ATTACHED GUIDE TO FILLING OUT THE PERSONAL DATA SHEESH (PDS) BEFORE ACCOMPLISHING THE PDS FORM. </b></td>
         </tr>
         <table width="100%" style="border-bottom:1px solid black">
         <tr>
         
             <td width="20%" style="font-size:6pt; border-left:1px solid black;"> Print legibly. Tick appropriate boxez ( </td>
             <td width="50%" style="font-size:6pt">
             <input type="checkbox" check="true" name="1" value="1">
              ) and use separate sheet if necessary. Indicate N/A if not applicable. DO NOT ABBREVIATE.    
              </td>
              <td width="10%" style="font-size:7pt; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black;"> 1. CS ID No.</td>
              <td width="20%" style="font-size:5pt; border-top:1px solid black; border-right:1px solid black; border-bottom:1px solid black;" align="right"> (Do not fill up. For CSC use only)</td>
         </tr>
         </table>
         <tr>    
             <td height="20px" width="100%" style="font-size:12pt; background-color:grey; border-left:1px solid black; border-top:1px solid black; border-bottom:1px solid black; border-right:1px solid black; color:white; " align="left"> I. PERSONAL INFORMATION</td>
         </tr>
         <table width="100%" cellpadding="4">
             <tr>
                 <td height="15px" width="15%" align="center" style="font-size:8pt;border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; background-color:#C3BEBF; ">2. SURNAME </td>
                 <td width="85%" style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black;">'.$basic->LastName_Empl.'</td>
             </tr>  
             <tr>
             <td height="15px" width="15%" align="center" style="font-size:8pt;border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> FIRST NAME </td>   
             <td height="15px" width="60%" style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->FirstName_Empl.' </td>
                 <td height="15px" width="25%" style="font-size:6pt;border-right:1px solid black; background-color:#C3BEBF; border-bottom:1px solid black; "> NAME EXTENSION(JR.,SR.)</td>
             </tr>
             <tr>
                 <td height="15px" width="15%" align="center" style="font-size:8pt;border-left:1px solid black; border-right:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;"> MIDDLE NAME </td>
                 <td height="15px" width="85%" style="font-size:8pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->MiddleName_Empl.' </td>
                 </tr>
     
         </table>
             
                 <tr>
                     
                     <td height="22px" width="15%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"> 3. DATE OF BIRTH (mm/dd/yyyy) </td>
                     <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->BirthDate_Empl.' </td>
                     <td width="25%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"> 16. CITIZENSHIP</td>
                     <td width="10%" style="font-size:7pt">
                     <input type="checkbox" check="true" name="1" value="1"> Filipino   
                     </td>
                     <td width="30%" style="font-size:6pt">
                     <input type="checkbox" check="true" name="1" value="1"> Dual Citizenship   
                     </td>
                 </tr>
                 <tr>
                     <td height="15px" width="15%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;background-color:#C3BEBF; border-left:1px solid black;"> PLACE OF BIRTH</td>
                     <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->birthplace.' </td>
                     <td width="25%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;" align="center"> If holder of dual citizenship, <br> please indicate the details</td>
                     <td width="10%"> </td>
                     <td width="12%" style="font-size:6pt"> 
                     <input type="checkbox" check="true" name="1" value="1"> by birth  <br> Pls. indicate country:
                     </td>
                     <td width="15%" style="font-size:6pt">
                     <input type="checkbox" check="true" name="1" value="1"> by naturalization 
                     </td>
                 </tr>
                 <tr>
                     <td height="15px" width="15%" style="font-size:7pt; border-bottom:1px solid black; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"> SEX </td>
                     <td width="12%" style="font-size:7pt; border-bottom:1px solid black;">
                     <input type="checkbox" checked="'.($basic->gender=== 'Male'? "true":"false").'" name="1" value="1"> Male</td>
                     <td width="12%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;">
                     <input type="checkbox" checked="'.($basic->gender=== 'Female'? "true":"false").'" name="1" value="1"> Female</td>
                     <td width="25%" style="font-size:7pt; border-right:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
                     <td width="33%" style="font-size:7pt; border-bottom:1px solid black; border-top:1px solid black;"></td>
                     <td width="3%" style="font-size:7pt; border-bottom:1px solid black;  border-top:1px solid black;  border-left:1px solid black;"></td>
                  </tr>
                 <tr>
                     <td style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"> CIVIL STATUS </td>              
                     <td width="12%" style="font-size:7pt;">
                         <input type="checkbox" checked="'.($basic->civilStatus=== 'Single'? "true":"false").'" name="1" value="1"> Single</td>
                     <td width="12%" style="font-size:7pt; border-right:1px solid black; ">
                         <input type="checkbox" checked="'.($basic->civilStatus=== 'Married'? "true":"false").'" name="1" value="1"> Married</td>
                      <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"> 17. RESIDENTIAL </td>
                      <td width="2.5%" style="font-size:7pt;   "> </td>
                      <td width="25%" style="font-size:6pt;    "> '.$basic->RHouse_No.' </td>
                      <td width="16.5%" style="font-size:6pt;    "> '.$basic->RStreet.' </td>
                 </tr>
                 <tr>
                    <td  style="font-size:7pt; border-right:1px solid black;  background-color:#C3BEBF; border-left:1px solid black; "> </td>        
                    <td width="24%" style="font-size:7pt;  border-right:1px solid black; "></td>
                    <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;">ADDRESS</td>
                    <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                    <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">House/Block/Lot no.</td>
                    <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Street</td>
                </tr>
                
                <tr>
                     <td  style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"></td>        
                     <td width="12%" style="font-size:7pt;">
                     <input type="checkbox" checked="'.($basic->civilStatus=== 'Widowed'? "true":"false").'" name="1" value="1"> Widowed</td>
                     <td width="12%" style="font-size:7pt; border-right:1px solid black; ">
                     <input type="checkbox" checked="'.($basic->civilStatus=== 'Separated'? "true":"false").'" name="1" value="1"> Separated</td>
                     <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"></td>
                     <td width="3%" style="font-size:7pt; "></td>
                     <td width="23%" style="font-size:6pt; "> <b> '.$basic->RSubd_Village.' </b></td>
                     <td width="17%" style="font-size:6pt;  ">  <b> '.$basic->RBrgy.' </b></td>
                </tr>
                <tr>
                     <td  style="font-size:7pt; border-right:1px solid black;background-color:#C3BEBF; border-left:1px solid black;"></td>        
                     <td width="12%" style="font-size:7pt;  ">
                     </td>
                     <td width="12%" style="font-size:7pt; border-right:1px solid black; ">
                     </td>
                     <td width="17%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black; "></td>
                     <td width="3%" style="font-size:7pt; border-left:1px solid black; border-bottom:1px solid black; "></td>
                     <td width="24%" style="font-size:6pt; border-bottom:1px solid black; ">Subdivision/Village</td>
                     <td width="17%" style="font-size:6pt; border-bottom:1px solid black;  "> Barangay</td>
                 </tr>
                <tr>
                     <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"></td>        
                     <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;">
                     <input type="checkbox" checked="'.($basic->civilStatus=== 'Other/s'? "true":"false").'" name="1" value="1"> Other/s:</td>
                     <td width="17%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black; "></td>
                     <td  width="3%" style="font-size:7pt; border-left:1px solid black;  "></td>
                     <td  width="24%" style="font-size:6pt;   "> <b> '.$basic->RCity_Mun.' </b> </td>
                     <td  width="17%" style="font-size:6pt; "> <b> '.$basic->RProvince.'</b></td>
              </tr>
              <tr>
                     <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;background-color:#C3BEBF; border-left:1px solid black; "> HEIGHT (m)</td>        
                     <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;">'.$basic->height.'</td>
                     <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"></td>
                     <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                     <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">City/Municipality</td>
                     <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Province</td>
              </tr>
              <tr>
                     <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> WEIGHT (kg)</td>        
                     <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;">'.$basic->weight.'</td>
                     <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> ZIP CODE</td>
                     <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->RZipcode.' </td>        
                     
             </tr>
             <tr>
                 <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> <br> BLOOD TYPE</td>        
                 <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->BloodType_Empl.' </td>
                 <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;">18. PERMANENT</td>
                 <td width="3%" style="font-size:7pt; "></td>
                 <td width="24%" style="font-size:6pt; "><b> '.$basic->PCity_Mun.' </b></td>
                 <td width="17%" style="font-size:6pt;  "><b> '.$basic->PStreet.' </b></td>
             </tr>
             <tr>
                    <td  style="font-size:7pt; border-right:1px solid black;  background-color:#C3BEBF; border-left:1px solid black; "> </td>        
                    <td width="24%" style="font-size:7pt;  border-right:1px solid black; "></td>
                    <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;">ADDRESS</td>
                    <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                    <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">City/Municipality</td>
                    <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Street</td>
                </tr>
             <tr>
                 <td height="15px" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> <br> GSIS ID NO.</td>        
                 <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black; ">'.$basic->GSIS_Empl.'</td>
                 <td width="17%" style="font-size:7pt;background-color:#C3BEBF; border-right:1px solid black; "></td>
                 <td width="3%" style="font-size:7pt; border-left:1px solid black; "></td>
                 <td width="23%" style="font-size:6pt;  "> <b> '.$basic->PSubd_Village.' </b></td>
                 <td width="17%" style="font-size:6pt; "> <b> '.$basic->PBrgy.' </b></td>
             </tr>
             
             <tr>
                 <td  style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black;">PAG-IBIG ID NO.</td>        
                 <td width="24%" style="font-size:7pt;  border-right:1px solid black; ">'.$basic->pagibig_no.'</td>
                 <td width="17%" style="font-size:7pt;background-color:#C3BEBF; border-right:1px solid black; "></td>
                 <td width="3%" style="font-size:7pt; border-left:1px solid black; border-bottom:1px solid black; "></td>
                 <td width="24%" style="font-size:6pt; border-bottom:1px solid black; ">Subdivision/Village</td>
                 <td width="17%" style="font-size:6pt; border-bottom:1px solid black;  "> Barangay</td>
             </tr>
             <tr>
                     <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black;"></td>        
                     <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black;"></td>
                     <td width="17%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black;   "></td>
                     <td  width="3%" style="font-size:7pt; border-left:1px solid black;  "></td>
                     <td  width="24%" style="font-size:6pt;   "> <b> '.$basic->PCity_Mun.' </b> </td>
                     <td  width="17%" style="font-size:6pt; "> <b> '.$basic->PProvince.' </b></td>
              </tr>
             <tr>
                 <td  style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> PHILHEALTH NO.</td>        
                 <td width="24%" style="font-size:7pt;  border-right:1px solid black; ">'.$basic->philhealth_no.'</td>
                 <td width="17%" style="font-size:7pt; border-right:1px solid black; background-color:#C3BEBF;"></td>
                 <td width="3%" style="font-size:7pt; border-bottom:1px solid black;"></td>
                 <td width="24%" style="font-size:6pt; border-bottom:1px solid black;">City/Municipality</td>
                 <td width="17%" style="font-size:6pt; border-bottom:1px solid black; "> Province</td>
             </tr>
             <tr>
                     <td  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "></td>        
                     <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black; "></td>
                     <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> ZIP CODE</td>
                     <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->PZipcode.' </td>        
                     
             </tr>
             <tr>
                 <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> SSS NO. </td>
                 <td width="24%" style="font-size:7pt;  border-right:1px solid black; border-bottom:1px solid black; "> '.$basic->SSS_Empl.' </td>
                 <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; " > 19. TELEPHONE NO.</td>
                 <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;">  </td>
             </tr>
             <tr>
                 <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> TIN NO. </td>
                 <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "> '.$basic->TIN_Empl.' </td>
                 <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" > 20. MOBILE NO.</td>
                 <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->empl_contactno.' </td>
             </tr>
             <tr>
                 <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; border-left:1px solid black; "> AGENCY EMP NO.</td>
                 <td width="24%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "></td>
                 <td width="17%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" >E-MAIL ADD (if any)</td>
                 <td width="44%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black;"> '.$basic->email_address.'   </td>
             </tr>
             <table width="100%" cellpadding="2">
                 <tr>
                     <td width="100%" height="20px" style="font-size:12pt; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black; color:white;"> II. FAMILY BACKGROUND </td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">22. SPOUSES SURNAME</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_surname.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;background-color:#C3BEBF;"> 23. NAME OF CHILD (Write full name and list all) </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> DATE OF BIRTH (mm/dd/yyyy)</td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; "> FIRST NAME</td>
                     <td width="20%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_firstname.'</td>
                     <td width="17%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; " > NAME EXTENSION <br> (JR., SR.) </td>
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; "> MIDDLE NAME</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_middlename.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; ">  OCCUPATION</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_occupation.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; ">  EMPLOYER/BUS NAME</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_employer.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black;background-color:#C3BEBF; ">  BUSINESS ADDRESS</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_employeradd.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black;  border-left:1px solid black; background-color:#C3BEBF; ">  TELEPHONE NO.</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->spouse_Telno.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">  24. FATHERS SURNAME</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->father_surname.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
                 <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black;background-color:#C3BEBF; "> FIRST NAME</td>
                     <td width="20%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "> '.$fam->father_firstname.' </td>
                     <td width="17%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; ">NAME EXTENSION <br> (JR., SR.) </td>   
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black"> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "> </td>
                  </tr>
             <tr>
                  <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; ">  MIDDLE NAME</td>
                  <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->father_middlename.'</td>
                  
                  <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                  <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
              </tr>
              <tr>
                     <td width="15%" height="15px"  style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">  25. MOTHERS MAIDEN NAME</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->mother_surname.'</td>
                     
                     <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
                 </tr>
             <tr>
                  <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; ">  SURNAME</td>
                  <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->mother_surname.'</td>
                  
                  <td width="26%" style="font-size:6pt; border-bottom:1px solid black  "> </td>
                  <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "></td>
              </tr>
              <tr>
                     <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; "> FIRST NAME</td>
                     <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; "> '.$fam->mother_firstname.' </td> 
                     <td width="26%" style="font-size:6pt;"> </td>
                     <td width="22%" style="font-size:6pt; border-right:1px solid black; border-bottom:1px solid black "> </td>
             </tr>
             <tr>
                  <td width="15%" height="15px"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF; ">  MIDDLE NAME</td>
                  <td width="37%" style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; ">'.$fam->mother_middlename.'</td>
                  
                  <td width="48%" style="font-size:6pt; color:red; border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; " align="center"> (Continue on separate sheet if necessary)</td>
                  
              </tr>
             </table>
     
             <table width="100%">
                 <tr>
                     <td width="100%" height="20px" style="font-size:12pt; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black; color:white;"> III. EDUCATIONAL BACKGROUND </td>
                 </tr>
             <table width="100%" border="1" >
                 <tr>
                     <td  width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;"> 26. <br> <br>LEVEL</td>
                     <td  width="25%" rowspan="2" style="font-size:8pt; background-color:#C3BEBF; "  align="center"> <br/><br/> <b> NAME OF SCHOOL </b> <br/> (Write in full)</td>
                     <td  width="13.5%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF; "  align="center">  <br/> <b> BASIC EDUCATION/DEGREE/COURSE </b> (Write in full) </td>
                     <td  width="15%" colspan="2" style="font-size:7pt; background-color:#C3BEBF; " align="center" height="30"><b> PERIOD OF ATTENDANCE </b></td>
                     <td  width="14%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF; "  align="center"> <b> HIGHEST LEVEL/UNITS EARNED (if not graduated) </b></td>
                     <td  width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;"  align="center"> <br/><br/><b> YEAR GRADUATED </b> </td>
                     <td  rowspan="2" style="font-size:7pt; background-color:#C3BEBF;"  align="center"> SCHOLARSHIP/ACADEMIC HONORS RECEIVED</td>
           
                 </tr>
                     <tr>
                             <td height="15px" style="font-size:7pt; background-color:#C3BEBF; " align="center">From</td>
                             <td style="font-size:7pt; background-color:#C3BEBF; "  align="center"> TO </td>
                     </tr>
     
                  '.$educ.'
                
                 <tr>
                     <td width="100%" height="9px" style="font-size:6pt;  color:red" align="center"> (Continue on separate sheet if necessary) </td>
                 </tr>
     
             </table>
     
     
             <table width="100%">
               
     
             <tr>
                 <td width="12%"  align="center" style="font-size:7pt; border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;">SIGNATURE</td>
                 <td width="43%"  style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
                 <td width="12%" style="font-size:7pt; border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; " align="center">DATES</td>
                 <td width="33%" style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
             </tr>
             <tr>
     
                 <td  width="100%" style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;" align="right"> CS FORM 212 (Revised 2017), Page 1 of 4 </td>
     
             </tr>
             <tr>
                 <td></td>
             </tr>
             
     
             </table>
     
             <table width="100%" cellpadding="2">
             
                 <tr>
                 
                 <td width="100%" height="17px" style="font-size:8pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black;"> IV. CIVIL SERVICE ELIGIBLITY </td>
                 </tr>
                 <table width="100%" border="1">
                <tr>
                     
                     <td rowspan="2" width="30%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> 27. CAREER SERVICE/ RA 1080 (BOARD/BAR) UNDER SPECIAL LAW/CES/CSEE</td>
                     <td rowspan="2" width="10%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> RATING</td>
                     <td rowspan="2" width="10%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> DATE OF EXAMINATION / CONFERMENT</td>
                     <td rowspan="2" width="30%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> PLACE OF EXAMINATION / CONFERNMENT</td>
                     <td width="20%" colspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> LICENSE (if applicable)</td>
                        
                 </tr>
                 <tr>
                     <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> NUMBER </td>
                     <td style="font-size:6pt; background-color:#C3BEBF;" align="center"> DATE OF RELEASE </td>
                 </tr>
    
                 '.$civil.'
               
                 <tr>
                     <td height="13px" width="100%" style="font-size:6pt; color:red" align="center"> (Continue on separate sheet if necessary) </td>
                 </tr>
                     </table>  
     
                 <table width="100%">
                     <tr>
                         <td width="100%" height="17px" style="font-size:8pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black;"> V. WORK EXPERIENCE (Include private employment. Start from your current work) </td>
                     </tr>
                     <table width="100%" border="1" cellpadding="2">
                     <tr>
                     
                     <td colspan="2" width="18%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> 28.  INCLUSIVE DATES (mm/dd/yyyy)</td>
                     <td rowspan="2" width="21%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> POSITION TITLE <br/> (Write in full) </td>
                     <td rowspan="2" width="20%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> DEPARTMENT / AGENCY / OFFICE COMPANY <br/> (Write in full)</td>
                     <td rowspan="2" width="10%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> MONTHLY SALARY</td>
                     <td width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> SALARY GRADE & STEP INCREMENT (Format *00-0*)</td>
                     <td width="13%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> STATUS OF APPOINTMENT</td>
                     <td width="8%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> GOVT SERVICE (YES/NO)</td>
                        
                 </tr>
                 <tr>
                     <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> From </td>
                     <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> TO </td>
                 </tr>
    
                 '.$employment.'
            
             <tr>
                 <td width="100%" height="10px" style="font-size:6pt; color:red" align="center"> (Continue on separate sheet if necessary)</td>
             </tr>
             <tr>
                 <td width="15%" style="font-size:10pt; background-color:#C3BEBF;" align="center"><b>SIGNATURE</b></td>
                 <td width="38%"></td>
                 <td width="10%" style="font-size:10pt; background-color:#C3BEBF;" align="center"><b>DATE</b></td>
                 <td width="37%"></td>
             </tr>
             <tr>
                 <td width="100%" height="10px" style="font-size:6pt;" align="right">CC FORM 212 (Revised 2017), Page 2 of 4</td>
             </tr>
     
                 </table>
     
                 <table width="100%" cellpadding="2">
                     <tr>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
                      <br/>
    
    
    
                         <td width="100%" height="17px" style="font-size:8pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black; border-top:1px solid black;"> VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENT / PEOPLE/ VOLUNTARY ORGANIZATION/S </td>
                     </tr>
                     <table width="100%" border="1" cellpadding="2">
                     <tr>
                     
                         <td rowspan="2" width="45%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> 29.  NAME & ADDRESS OF ORGANIZATION <br/> (Write in full)</td>
                         <td colspan="2" width="21%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> INCLUSIVE DATES <br/> (mm/dd/yyyy)</td>
                         <td rowspan="2" width="10%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> NUMBER OF HOURS (Write in full)</td>
                         <td rowspan="2" width="24%" style="font-size:7pt; background-color:#C3BEBF;" align="center"> POSITION / NATURE OF WORK</td>
                        
                     </tr>
                     <tr>
                         <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> From </td>
                         <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> TO </td>
                     </tr>
                        
                     '.$vwork.'
    
                     <tr>
                         <td width="100%" style="font-size:6pt; color:red" align="center"> (Continue on separate sheet if necessary) </td>
                     </tr>
     
                     </table>
     
     
                         
                         <table width="100%">
                         <tr>
                         
                    
                         
                         <td width="100%" height="18px" style="font-size:10pt; color:white; background-color:grey; border-right:1px solid black; border-left:1px solid black; border-top:1px solid black;"> VII.LEARNING AND DEVELOPMENT (L&D) INTERENTIONS PROGRAMS ATTENDED </td>
                            
                         </tr>
                         <tr>
                             <td style="font-size:7pt; color:white; background-color:grey; border-right:1px solid black; border-bottom:1px solid black; border-left:1px solid black;"> (Start from the most recent L & D/training program include only yhe relevant L&D/training taken for the last five (5) years for Division Chief/Executive/Managerial positions)</td>
                         </tr>
                         <table width="100%" border="1">
                         <tr>
                         
                             <td width="38%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> 30. TITLE OF SEMINAR/CONFERENCE/WORKSHOP/SHORT COURSES <br/> (Write in full) </td>
                             <td width="17%" style="font-size:7pt; background-color:#C3BEBF;" align="center" colspan="2"> INCLUSIVE DATES OF ATTENDANCE <br/>(mm/dd/yyyy) </td>
                             <td width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> <br/>NUMBER OF HOURS </td>
                             <td width="10%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> Type of  LD(Managerial/Supervisory/Tehnical etc.) </td>
                             <td width="25%" rowspan="2" style="font-size:7pt; background-color:#C3BEBF;" align="center"> CONDUCTED/ SPONSORED BY <br/> (write in full) </td>
                            
                      
                         </tr>
                             <tr>
                                 <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> FROM </td>
                                 <td style="font-size:7pt; background-color:#C3BEBF;" align="center"> TO </td>
                             </tr>
                            '.$training.' 
                         </table>
     
                         <table width="100%" cellpadding="2" style="border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;">
                             <tr>
                                 <td width="100%" height="150px" style="font-size:7pt;"></td>
                             </tr>
                             <tr>
                                 <td width="100%"  style="border-top:1px solid black; font-size:7pt; color:red" align="center"> (Continue on separate sheet if necessary)</td>
                             </tr>
                             <tr>
                                 <td width="100%" height="18px" style="font-size:8pt; color:white; background-color:grey; border-right:1px solid black; border-left:1px solid black; border-top:1px solid black;"> VIII. OTHER INFORMATION </td>
                             </tr>
                             <table width="100%" border="1" cellpadding="2">
                             <tr>
                                 <td style="font-size:7pt; background-color:#C3BEBF;">31. SPECIAL SKILLS/HOBBIES</td>
                                 <td style="font-size:7pt; background-color:#C3BEBF;">32. NON-ACADEMIC DISTINCTIONS/RECOGNITION <br/> (Write in full)</td>
                                 <td style="font-size:7pt; background-color:#C3BEBF;">33.  MEMBERSHIP IN ASSOCIATION/ORGNAZITION <br/> (Write in full) </td>
                             </tr>
                                '.$skills.'
                             <tr>
                                 <td width="100%" height="55px" style="font-size:7pt;"></td>
                             </tr>
                             <tr>
                                 <td width="100%" height="15px" style="font-size:7pt; color:red" align="center"> (Continue on separate sheet if necessary)</td>
                             </tr>
                             
                              <tr>
                                 
                                     <td width="12%"  height="15px" align="center" style="font-size:7pt; border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;">SIGNATURE</td>
                                     <td width="43%"  height="12px" style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
                                     <td width="12%" style="font-size:7pt; border-bottom:1px solid black; border-top:1px solid black; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; " align="center">DATE</td>
                                     <td width="33%" style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;"></td>
                                 </tr>
                                 <tr>
     
                                     <td  width="100%" style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black; border-left:1px solid black; border-right:1px solid black;" align="right"> CS FORM 212 (Revised 2017), Page 3 of 4 </td>
                                 </tr>
                             </table>
                             <table width="100%" style="border-left:1px solid black; border-right:1px solid black;">
                             <tr>
                                <br/>
                                <br/>
                                <br/>
                                <br/>
                                <br/>
                                <br/>
                                <br/>
                                <br/>
                                 <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> 34. Are you related by consanguinity or affinity to the appointing or recommending authority or to the chief of bureau or office or the person you who has immediate supervision over you in the Office, Burueau of Departmentn  where you will be appointed,
                                 </td>
                                 <td width="10%" style="font-size:7pt;">
        
                                 </td>
                                 <td width="25%" style="font-size:7pt;"> 
                                 
                                 </td>
                             </tr>
                             <tr>
                                 <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> a. within the third degree?</td>
                                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                                YES
                                 </td>
                                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                                NO
                                 </td>
                             </tr>
                             <tr>
                                 <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> b. within the fourth degree(forLocal Government Unit - Career Employee</td>
                                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                                YES
                                 </td>
                                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                                NO
                                 </td>
                             </tr>
                         <tr>
                             <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="20%" style="font-size:7pt;"> 
                            If YES, give details;
                             </td>  
                         </tr>
                         <tr>
                             <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="5%"> </td>
                             <td width="29%" style="font-size:7pt; border-bottom:1px solid black;"> 
                             
                          </td>
                      </tr>
                         <tr>
                             <td width="65%" style="font-size:7pt; border-left:1px solid black; border-bottom:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="35%" style="font-size:7pt; border-left:1px solid black;border-bottom:1px solid black;"> 
                             </td>
                         </tr>
                         <tr>
                         
                                 <td  width="65%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black; border-left:1px solid black;" >35.  a. Have you ever been found guilty of any administrative offense? </td>
                                 <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                                 YES
                                 </td>
                                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                                 NO
                                 </td>
                         </tr>
                         <tr>
                             <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="20%" style="font-size:7pt;"> 
                             If YES, give details;
                             </td>  
                         </tr>
                         <tr>
                             <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="5%" style="border-left;1px solid black;"> </td>
                             <td width="29%" style="font-size:7pt; border-bottom:1px solid black;">      
                             </td>
                         </tr>
                         <tr>
                             <td width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"></td>
                             <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
                             </td>
                         </tr>
                         <tr>
                         
                                 <td  width="65%" style="font-size:7pt; border-left:1px solid black; background-color:#C3BEBF; border-right:1px solid black;" > b. Have you been criminally charged before any court?</td>
                                 <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                                 YES
                                 </td>
                                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                                 NO
                                 </td>
                         </tr>
                         <tr>
                             <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="20%" style="font-size:7pt;"> 
                             If YES, give details;
                             </td>  
                         </tr>
                         <tr>
                             <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="15%" style="border-left;1px solid black;font-size:7pt;" align="right"> Date Filed: </td>
                             <td width="18%" style="font-size:7pt; border-bottom:1px solid black;">  3/29/2022 12:00:00 AM     
                             </td>
                         </tr>
                         <tr>
                             <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="15%" style="border-left;1px solid black;font-size:7pt;" align="right">Status of Case/s:</td>
                             <td width="18%" style="font-size:7pt; border-bottom:1px solid black;">      
                             </td>
                          </tr>
                          <tr>
                             <td width="65%" style="font-size:7pt; border-left:1px solid black; border-bottom:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                             <td width="35%" style="font-size:7pt; border-left:1px solid black;border-bottom:1px solid black;"> 
                             </td>
                         </tr>
                         <tr>
                         
                         
                         <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; " >36. Have you ever been convicted of any crime or violation of any law, devree, ordinance or regulation by any court or tribunal </td>
                         <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                         YES
                         </td>
                         <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                         NO
                         </td>
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
                     <td width="20%" style="font-size:7pt;"> 
                     If YES, give details;
                     </td>  
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
                     <td width="5%" style="border-left;1px solid black;"> </td>
                     <td width="29%" style="font-size:7pt; border-bottom:1px solid black;">      
                     </td>
                 </tr>
                 <tr>
                     <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
                     <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
                     </td>
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" >37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished conract or phased out(abolition) in the public or private sector</td>
                     <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                     YES
                     </td>
                     <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                     NO
                     </td>
                 </tr>
                  <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
                     <td width="20%" style="font-size:7pt;"> 
                     If YES, give details;
                     </td>  
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
                     <td width="5%" style="border-left;1px solid black;"> </td>
                     <td width="29%" style="font-size:7pt; border-bottom:1px solid black;">      
                     </td>
                 </tr>
                 <tr>
                     <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
                     <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
                     </td>
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; border-top:1px solid black; background-color:#C3BEBF;" >38. a. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?</td>
                     <td width="10%" style="font-size:7pt; border-left:1px solid black; border-top:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                     YES
                     </td>
                     <td width="10%" style="font-size:7pt; border-top:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                     NO
                     </td>
                     <td width="15%" style="border-top:1px solid black"> </td>
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
                     <td width="15%" style="font-size:7pt;"> 
                     If YES, give details: </td>
                     <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"></td>    
                 </tr>
                 <tr>
                
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" >b. Have you resigned from the government service during the three(3)-month period before the last election to promote/actively campaign for national or local candidate? </td>
                     <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                     YES
                     </td>
                     <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                     NO
                     </td>
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> </td>
                     <td width="15%" style="font-size:7pt;"> 
                     If YES, give details: </td>
                     <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"></td>    
                         
                 </tr>
                 
                 <tr>
                     <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
                     <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
                     </td>
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; background-color:#C3BEBF; border-right:1px solid black; border-left:1px solid black;" >39. Have you acquired the status of an immigrant or permanent resident of another country?</td>
                     <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                     YES
                     </td>
                     <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                     NO
                     </td>
                     </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; border-right:1px solid black;"> </td>
                     <td width="20%" style="font-size:7pt;"> 
                     If YES, give details (country);
                     </td>  
                 </tr>
                 <tr>
                     <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
                     <td width="5%" style="border-left;1px solid black;"> </td>
                     <td width="29%" style="font-size:7pt; border-bottom:1px solid black;">      
                     </td>
                 </tr>
                 <tr>
                     <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
                     <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
                     </td>
                 </tr>
                 <tr>
                 <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" >40. Pursuant to: (a) Indigenous Peoples Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277); and (c) Solo Parents Welfare Act of 2000 (RA 8972), please answer the following items:</td>
                 <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                 YES
                 </td>
                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                 NO
                 </td>
             </tr>
             <tr>
                 <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF; "> </td>
                 <td width="15%" style="font-size:7pt;"> 
                 If YES, give details: </td>
                 <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"></td>    
             </tr>
             <tr>
            
                 <td  width="65%" style="font-size:7pt;  border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;">a. Are you a member of any indigenous group</td>
                 <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                 YES
                 </td>
                 <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                 NO
                 </td>
             </tr>
             <tr>
                 <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> </td>
                 <td width="15%" style="font-size:7pt;"> 
                 If YES, give details: </td>
                 <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"></td>    
                     
             </tr>
             
                 <tr>
                 
                     <td  width="65%" style="font-size:7pt;  border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;">b. Are you a a person with disability?</td>
                     <td width="10%" style="font-size:7pt; border-left:1px solid black;"> <input type="checkbox" check="true" name="1" value="1">
                     YES
                     </td>
                     <td width="10%" style="font-size:7pt;"> <input type="checkbox" check="true" name="1" value="1">
                     NO
                     </td>
             </tr>
            
             <tr>
                 <td  width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black; background-color:#C3BEBF;"> c. Are you a solo parent?</td>
                 <td width="15%" style="font-size:7pt;"> 
                 If YES, give details: </td>
                 <td width="18%" style="font-size:7pt; border-bottom:1px solid black;"></td>    
                     
             </tr>
             
             <tr>
                 <td width="65%" style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;  border-bottom:1px solid black; background-color:#C3BEBF;"></td>
                 <td width="35%" style="font-size:7pt; border-bottom:1px solid black;">  
                 </td>
             </tr>
       </table>
     
      <table width="100%" style="border-right:1px solid black;">
         <tr>
             <td width="15%" height="15px" style="font-size:7pt; ;border-left:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;">41. REFERENCES</td>
             <td width="60%" height="15px" style="font-size:7pt; color:red;border-right:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;">(Person not related by consanguinity or affinity to applicant/appointee) </td>
             <td width="3%"> </td>
             <td width="20%"> </td>
             <td width="2%"> </td>
         </tr>
          <tr>
             <td width="27%" height="15px" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> <br/>NAMES</td>
             <td width="28%"  height="15px"style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center">ADDRESS</td>
             <td width="20%"  height="15px"style="font-size:7pt; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center">TEL.NO.</td>
                 
             <td width="3%"  style="font-size:7pt; border-right:1px solid black; "> </td>
             <td width="20%"  style="font-size:7pt;  border-right:1px solid black; border-top:1px solid black;"> ID picture taken within the last 6 months 3.5cm. x.4.5cm. <br/> (passport size) </td>
             <td width="2%"  style="font-size:7pt; border-right:1px solid black; border-left:1px solid black;"> </td>
          </tr>
          <tr>
             <td width="75%"  style="border-left:1px solid black;  border-right:1px solid black;"> </td>
             <td width="3%" style="border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="20%" style="border-right:1px solid black; "> </td>
             <td width="2%"> </td>
          </tr>
          <tr>
             <td width="27%" height="15px" style="font-size:7pt; border-left:1px solid black;" align="center"></td>
             <td width="28%"  height="15px"style="font-size:7pt;" align="center"></td>
             <td width="20%"  height="15px"style="font-size:7pt; border-right:1px solid black;" align="center"></td>
                 
             <td width="3%"  style="font-size:7pt; border-right:1px solid black; "> </td>
             <td width="20%"  style="font-size:7pt; border-right:1px solid black; " align="center"> With full and handwritten name tag and signature over printed name </td>
             <td width="2%"  style="font-size:7pt; border-right:1px solid black;"> </td>         
          </tr>
          <tr>
             <td width="75%"  style="border-left:1px solid black;  border-right:1px solid black;"> </td>
             <td width="3%" style="border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="20%" style="border-right:1px solid black; "> </td>
             <td width="2%"> </td>
          </tr>
          <tr>
             <td width="75%"  style="font-size:7pt; border-left:1px solid black;  border-right:1px solid black; border-top:1px solid black; background-color:#C3BEBF;"> 42. I declare under oath that I have accomplished this Personal Data Sheet which is true,
              correct and complete statement pursuant to the provisions of pertinent laws,
               rules and regulations of the Republic of the Philippinnes.
                I authorize the agency head/authorized representative to verify/validate the contents stated herin.
                 I agree that any misinterpretation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me. </td>
             <td width="3%" style="border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="20%" style="border-bottom:1px solid black; border-right:1px solid black; font-size:7pt; " align="center"> Computer generated or photocopied picture is not acceptable </td>
             <td width="2%"> </td>
          </tr>
          <tr>
             <td width="75%" style="font-size:7pt; border-left:1px solid black;  border-right:1px solid black;border-bottom:1px solid black; background-color:#C3BEBF;  "> </td>
             <td width="3%"> </td>
             <td width="20%" style="font-size:8pt;" align="center"> PHOTO </td>
             <td width="2%"> </td>
          
          </tr>
          <tr>
             <td width="40%" style="font-size:6pt; border-left:1px solid black; border-right:1px solid black; background-color:#C3BEBF;"> Government Issued ID (i.e.Passport, GSIS, SSS, PRC, Drivers License, etc.) </td>
             <td width="1%"> </td>
             <td width="34%" style="border-right:1px solid black; border-left:1px solid black; "> </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black; border-top:1px solid black;" align="center"></td>   
          </tr>
          <tr>
             <td width="40%" style="font-size:7pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;"> PLEASE INDICATE ID Number and Date of Issuance </td>
             <td width="1%"> </td>
             <td width="34%" style="border-right:1px solid black; border-left:1px solid black;"> </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black;" align="center"></td>   
         </tr>
         <tr>
             <td width="15%" style="font-size:6pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;"> Government Issued ID: </td>
             <td width="25%"  style="border-right:1px solid black; border-bottom:1px solid black;" > </td>
             <td width="1%"> </td>
             <td width="34%" style="font-size:6pt; border-right:1px solid black; background-color:#C3BEBF; border-left:1px solid black; border-bottom:1px solid black;  border-top:1px solid black;" align="center"> SIGNATURE (Sign inside the box) </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black;" align="center"></td>   
         </tr>
         <tr>
             <td width="15%" style="font-size:6pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;"> ID/License/Passport No.: </td>
             <td width="25%"  style="border-right:1px solid black; border-bottom:1px solid black;" > </td>
             <td width="1%"> </td>
             <td width="34%" style="font-size:6pt; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; border-top:1px solid black;" align="center"> </td>
             <td width="2%"> </td>
             <td width="22%" style="border-right:1px solid black; border-left:1px solid black;" align="center"></td>   
         </tr>
         <tr>
             <td width="15%" style="font-size:6pt; border-left:1px solid black; border-right:1px solid black; border-bottom:1px solid black;">Date/Place of Issuance: </td>
             <td width="25%"  style="border-right:1px solid black; border-bottom:1px solid black;" > </td>
             <td width="1%"> </td>
             <td width="34%" style="font-size:6pt; background-color:#C3BEBF; border-right:1px solid black; border-left:1px solid black; border-bottom:1px solid black; border-top:1px solid black;" align="center"> Date Accomplished </td>
             <td width="2%"> </td>
             <td width="22%" style="font-size:6pt; border-right:1px solid black; border-top:1px solid black; border-bottom:1px solid black; border-left:1px solid black; background-color:#C3BEBF;" align="center"> Right Thumbmark</td>   
         </tr>
         <tr>
             <br/>
             <td width="29%"  style="font-size:7pt; border-left:1px solid black; border-top:1px solid black;"> SUBSCRIBED AND SWORN to before me this</td>
             <td width="17%"  style="font-size:6pt; border-bottom:1px solid black; border-top:1px solid black;"> </td>
             <td width="54%"  style="font-size:6pt; border-top:1px solid black;">, affiant exhibiting his/her validly issued government ID as indicated above. </td>
         </tr>
         <tr>
             <td width="100%" style="border-left:1px solid black"></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-top:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-right:1px solid black;"> </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="33%"  style="font-size:7pt; border-left:1px solid black; "> </td>
             <td width="34%"  style="font-size:6pt; border-left:1px solid black; border-top:1px solid black; border-right:1px solid black; border-bottom:1px solid black; background-color:#C3BEBF;" align="center"> Person Administering Oath </td>
             <td width="33%"  style="font-size:6pt; "></td>
         </tr>
         <tr>
             <td width="100%" style="border-bottom:1px solid black; border-left:1px solid black"></td>
         </tr>
         <tr>
             <td width="100%" style="font-size:6pt; border-bottom:1px solid black; border-left:1px solid black" align="right">CS FORM 212(Revised 2017), Page 4 of 4</td>
         </tr>
                                                                                                                                                                 
     
      </table>
     
     
     
     
     
     
                         </table>
     
     
     
     
                         </table>
     
     
                     </table>
     
     
     
                     </table>
     
     
               
                 
     
     
             </table>
     
     
     
     
     
     
             </table>
     
     </table>
     
              ';
            
             PDF::SetTitle('PERSONAL DATA SHEET');
             PDF::SetFont('helvetica', '', 8);
            //  PDF::AddPage('P');
           
            


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