<?php

namespace App\Http\Controllers\Api\CivilStructuralPermit;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF; 
class civilStructuralController extends Controller
{
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
    }
    public function displayProjectList(Request $request)
    {
        $type = 0;
        $from = $request->from;
        $to = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_display_project_registration_list(?,?,?)', array($type,$from,$to));
        return response()->json(new JsonResponse($list));
    } 
    public function displayBuildingList(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_display_buildinglist(?,?)', array($from,$to));
        return response()->json(new JsonResponse($list));
    }
    public function civilStructuralPermitNoNew (Request $request)
    {     
      $list = DB::select('call ' . $this->lgu_db . '.balodoy_get_cspermitno_new()');   
      return response()->json(new JsonResponse($list));
    } 
    public function getBarangaylist()
    {
        $list = DB::select('call ' . $this->lgu_db . '.balodoy_display_barangay_list()');
        return response()->json(new JsonResponse($list));
    }    
    public function getcivilStructuralList(Request $request)
    {           
      $tmp = json_decode($request->dates);
      $dateFrom = $tmp->from ;
      $dteTo = $tmp->to ;
      $issueType = $request->issuanceType;        
      $list = DB::select('call ' . $this->lgu_db . '.balodoy_display_CivilStructural(?,?,?)', array($dateFrom,$dteTo,$issueType));
      return response()->json(new JsonResponse($list));
    }
     public function getDocumentList(Request $request)   
    {
    try {        
        $trans_Name = 'Building Permit';      
        $trans_ID = 0;           
        $pay_ID = 0; 
        $pay_Type = 0;  
        $list = DB::select('call '.$this->lgu_db.'.balodoy_display_setup_certification_building_permit(?,?,?,?)',array($trans_Name,$trans_ID,$pay_ID,$pay_Type));    
        return response()->json(new JsonResponse($list));
    } catch (\Exception $e) {
    return response()->json(new JsonResponse(['Message'=>'Error!','errormsg'=>$e,'status'=>'error']));
    }
    }
    public function store(Request $request)
    {
      try {        
        DB::beginTransaction();                         
        $csMain = $request->csMainData;                  
        $box1 = $request->box1Data;                                        
        $box2 = $request->box2Data;          
        $box3 = $request->box3Data;                        
        $box4 = $request->box4Data;                       
        $box5 = $request->box5Data;          
        $box6 = $request->box6Data;      
        $box7 = $request->box7Data;
        $csDocDetails = $request->csDocInfoDetails;     
        $box8 = $request->box8Data;       
        $csprogressDetails = $request->csprogressInfoDetails;       
        $box9 = $request->box9Data;           
        $idx=$csMain['application_id'];                                         
        if ($idx > 0) {                       
            $this->update($idx, $csMain, $box1,$box2, $box3, $box4, $box5, $box6, $box7, $csDocDetails, $box8, $csprogressDetails, $box9);
        }else {
            $this->save($csMain, $box1, $box2, $box3, $box4, $box5, $box6, $box7, $csDocDetails, $box8, $csprogressDetails, $box9);
        };
              
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!','errormsg'=>$e,'status'=>'error']));
        }
    }  
    public function edit(Request $request, $id)
    {         
        $data['csMain'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_main(?)', array($id));      
        $data['csbox1'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box1(?)', array($id));       
        $data['csbox2'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box2(?)', array($id));
        $data['csbox3'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box3(?)', array($id));
        $data['csbox4'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box4(?)', array($id));   
        $data['csbox5'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box5(?)', array($id));  
        $data['csbox6'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box6(?)', array($id));
        $data['csbox7'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box7(?)', array($id));
        $data['csDocuments'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_csdocument_details(?)', array($id));
        $data['csProgress'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_csprogress_details(?)', array($id));
        $data['csbox9'] = DB::select('call ' . $this->lgu_db . '.balodoy_get_civilstructure_box9(?)', array($id));    
        return response()->json(new JsonResponse($data));        
    }
    public function save($csMain, $box1, $box2, $box3, $box4, $box5, $box6, $box7, $csDocDetails, $box8, $csprogressDetails, $box9)
    { 
        if ($box1['cs_ownedConstructionID'] > 0) {
            $appType = 'BUSINESS';
            $appID = $box1['cs_ownedConstructionID'];
            $appName = $box1['cs_ownedConstruction'];
            } else {
            $appType = 'PERSON';           
            $appID = $box1['cs_applicantID'];
            $appName = $box1['cs_applicantName'];
            };            
        $newCivilStructure = array( 
            'application_save_date' => Date(Now()),     
            'application_status' => 'NEW',
            'applicant_type' => $appType,
            'applicant_id' =>  $appID,
            'application_code' => $csMain['cs_applicationNo'],
            'area_code' => $csMain['cs_areaNo'],           
            'applicant_address' => $box1['cs_addBarangay'], 
            'lot_no' => $box1['cs_locLotNo'],
            'blk_no' => $box1['cs_locBlockNo'],           
            'tct_no' => $box1['cs_TCTNo'],
            'property_st' => $box1['cs_locStreet'],
            'property_brgy' => $box1['cs_locBarangay'],
            'property_city' => $box1['cs_locCityMun'],
            'with_td' => 'NO',
            'indigens_status' => 'NO',
            'with_bp' => 'NO',
            'td_no' => $box1['cs_TDNo'],
            'user_id' => Auth::user()->id,
            'permit_code' => $box9['cs_issuanceNo'],
            'bldg_code' => $csMain['cs_buildingNo'],
            'permit_type' => 2,
            'bldg_permit_id' => $csMain['cs_buildingID'],                                       
            );                                          
        DB::table($this->lgu_db.'.eceo_application')->insert($newCivilStructure);
        $csID = DB::getPDo()->lastInsertId();
        if ($box1['cs_scopeofWork'] == "New Construction") {
            $scopeID = 1;          
        } elseif ($box1['cs_scopeofWork'] == "Erection") {
            $scopeID = 2;
        } elseif ($box1['cs_scopeofWork'] == "Addition") {
            $scopeID = 3; 
        } elseif ($box1['cs_scopeofWork'] == "Alteration") {
            $scopeID = 4; 
        } elseif ($box1['cs_scopeofWork'] == "Renovation") {
            $scopeID = 5; 
        } elseif ($box1['cs_scopeofWork'] == "Conversion") {
            $scopeID = 6; 
        } elseif ($box1['cs_scopeofWork'] == "Repair") {
            $scopeID = 7; 
        } elseif ($box1['cs_scopeofWork'] == "Moving") {
            $scopeID = 8; 
        } elseif ($box1['cs_scopeofWork'] == "Raising") {
            $scopeID = 9; 
        } elseif ($box1['cs_scopeofWork'] == "Accessory Building/Structure") {
            $scopeID = 10;
        } else {
            $scopeID = 11;   
        };

        if ($box1['cs_characterofOccupancy'] == "Group A: Residential, Dwellings") {
            $occupancyID = 1;       
        } elseif ($box1['cs_characterofOccupancy'] == "Group B: Residential Hotel, Apartment") {
            $occupancyID = 2;
        } elseif ($box1['cs_characterofOccupancy'] == "Group C: Educational, Recreational") {
            $occupancyID = 3;   
        } elseif ($box1['cs_characterofOccupancy'] == "Group D: Institutional") {
            $occupancyID = 4;
        } elseif ($box1['cs_characterofOccupancy'] == "Group E: Business and Mercantile") {
            $occupancyID = 5;
        } elseif ($box1['cs_characterofOccupancy'] == "Group F: Industrial") {
            $occupancyID = 6;
        } elseif ($box1['cs_characterofOccupancy'] == "Group G: Industrial Storage and Hazardous") {
            $occupancyID = 7;
        } elseif ($box1['cs_characterofOccupancy'] == "Group H: Recreational, Assembly Occupant Load Less Than 1000") {
            $occupancyID = 8;  
        } elseif ($box1['cs_characterofOccupancy'] == "Group I: Recreational, Assembly Occupant Load 1000 or More") {
            $occupancyID = 9;     
        } elseif ($box1['cs_characterofOccupancy'] == "Group J: Agricultural Accessory") {
            $occupancyID = 10;     
        } else{
            $occupancyID = 11;       
        };                        
        $newcsDetails = array(                          
            'application_no' => $csID,
            'zoning_no' => 0,
            'subd_no' => 0,
            'date_of_entry' => Date(Now()),
            'area_no_prov_code' => $csMain['cs_applicationNo'],
            'area_no_bgry_code' => $csMain['cs_areaNo'],          
            'establishment_name' => $appName,
            'scope_of_work' => $scopeID,
            'char_of_occupancy' => $occupancyID,
            'occupancy_classified' => '',           
            'no_of_storey' => 0,
            'no_of_units' => 0,
            'total_estmtd_cst' => 0,           
            'total_flr_area' => 0,           
            'proposed_date_of_const' => Date(Now()),
            'expected_date_of_comp' => Date(Now()),            
            'bldg_classification' => '',      
            'permit_type' => 2,
          );                                 
        DB::table($this->lgu_db.'.eceo_application_details')->insert($newcsDetails);
        $csAppInfo = array(        
            'application_no' => $csID,
            'owner_id' => $appID, 
            'owner_prefix' => $box1['cs_applicantPFix'],  
            'owner_firstname' => $box1['cs_applicantFName'], 
            'owner_middlename' => $box1['cs_applicantMName'], 
            'owner_lastname' => $box1['cs_applicantLName'], 
            'owner_suffix' => $box1['cs_applicantSFix'], 
            'owner_tin' => $box1['cs_applicantTIN'], 
            'owner_type' => $appType, 
            'owner_ppid' => $box1['cs_applicantID'],
            'owner_bid' => $box1['cs_ownedConstruction'],
            'owner_businessname' => $box1['cs_ownedConstruction'],
            'owner_businesstype' => $box1['cs_formOwnership'], 
            'brgy_id' => $box1['cs_addBarangayID'], 
            'addr_no' => $box1['cs_addNo'], 
            'addr_st' => $box1['cs_addStreet'], 
            'addr_brgy' => $box1['cs_addBarangay'], 
            'addr_citymun' => $box1['cs_addCityMun'], 
            'addr_zipcode' => $box1['cs_zipCode'],            
            'tel_no' => $box1['cs_telNo'],
            'permit_type' => 2,
            'occupancy' => $box1['cs_characterofOccupancy']           
          );                         
        DB::table($this->lgu_db.'.eceo_application_info')->insert($csAppInfo);
        $csScopeInfo = array(        
            'application_no' => $csID,
            'scope_id' => $scopeID, 
            'scope_name' => $box1['cs_scopeofWork'],  
            'scope_remarks' => $box1['cs_scopeofRemarks'],
            'permit_type' => 2,           
          );                          
        DB::table($this->lgu_db.'.eceo_bldg_scope')->insert($csScopeInfo);
        if ($box3['cs_architectId'] > 0) {
            $engineerID1 = $box3['cs_architectId'];           
            } else {
            $engineerID1 = 0;
            };  
        $csEngineer1 = array(       
            'application_no' => $csID,
            'person_id' => $engineerID1,
            'title_no' => 11,
            'person_name' => $box3['cs_architectName'],
            'st_no' => $box3['cs_architectAddress'],
            'sig_date' => $box3['cs_architectDatesigned'],
            'sig_prc_no' => $box3['cs_architectPRCNo'],
            'sig_prc_date' => $box3['cs_architectValidityDate'],
            'sig_ptr_no' => $box3['cs_architectPTRNo'],
            'sig_ptr_date' => $box3['cs_architectDateissued'],
            'sig_ptr_place' => $box3['cs_architectIssuedat'],
            'sig_tin_no' => $box3['cs_architectTIN'],
            'sig_ctc_no' => '',
            'sig_ctc_date' => $box3['cs_architectDatesigned'],
            'sig_ctc_place' => '',
            'validity' => '',
            'permit_type' => 2,
          );                         
        DB::table($this->lgu_db.'.eceo_signatory_persons')->insert($csEngineer1);
        if ($box4['cs_architectId'] > 0) {
            $engineerID2 = $box4['cs_architectId'];           
            } else {
            $engineerID2 = 0;
            };  
        $csEngineer2 = array(       
            'application_no' => $csID,
            'person_id' => $engineerID2,
            'title_no' => 5,
            'person_name' => $box4['cs_architectName'],
            'st_no' => $box4['cs_architectAddress'],
            'sig_date' => $box4['cs_architectDatesigned'],
            'sig_prc_no' => $box4['cs_architectPRCNo'],
            'sig_prc_date' => $box4['cs_architectValidityDate'],
            'sig_ptr_no' => $box4['cs_architectPTRNo'],
            'sig_ptr_date' => $box4['cs_architectDateissued'],
            'sig_ptr_place' => $box4['cs_architectIssuedat'],
            'sig_tin_no' => $box4['cs_architectTIN'],
            'sig_ctc_no' => '',
            'sig_ctc_date' => $box4['cs_architectDatesigned'],
            'sig_ctc_place' => '',
            'validity' => '',
            'permit_type' => 2,
          );                        
        DB::table($this->lgu_db.'.eceo_signatory_persons')->insert($csEngineer2);
        if ($box5['cs_buildingOwnerID'] > 0) {
            $ownerID = $box5['cs_buildingOwnerID'];           
            } else {
            $ownerID = 0;
            };  
        $csApplicant = array(        
            'application_no' => $csID,
            'person_id' => $ownerID,
            'title_no' => 1,
            'person_name' => $box5['cs_buildingOwnerName'],
            'st_no' => $box5['cs_buildingOwnerAddress'],
            'sig_date' => $box5['cs_buildingOwnerDate'],
            'sig_prc_no' => '',
            'sig_prc_date' => $box5['cs_buildingOwnerDate'],
            'sig_ptr_no' => '',
            'sig_ptr_date' => $box5['cs_buildingOwnerDate'],
            'sig_ptr_place' => '',
            'sig_tin_no' => $box1['cs_applicantTIN'],
            'sig_ctc_no' => $box5['cs_buildingOwnerCTCNo'],
            'sig_ctc_date' => $box5['cs_buildingOwnerDateissued'],
            'sig_ctc_place' => $box5['cs_buildingOwnerPlaceissued'],
            'validity' => '',
            'permit_type' => 2,
          );                               
        DB::table($this->lgu_db.'.eceo_signatory_persons')->insert($csApplicant);
        if ($box6['cs_lotOwnerID'] > 0) {
            $lotOwnerID = $box6['cs_lotOwnerID'];           
            } else {
            $lotOwnerID = 0;
            };  
        $csLotOwner = array(        
            'application_no' => $csID,
            'person_id' => $lotOwnerID,
            'title_no' => 2,
            'person_name' => $box6['cs_lotOwnerName'],
            'st_no' => $box6['cs_lotOwnerAddress'],
            'sig_date' => $box6['cs_lotOwnerDate'],                   
            'sig_prc_no' => '',            
            'sig_prc_date' => $box6['cs_lotOwnerDate'],            
            'sig_ptr_no' => '',            
            'sig_ptr_date' => $box6['cs_lotOwnerDate'],            
            'sig_ptr_place' => '',
            'sig_tin_no' => '',                   
            'sig_ctc_no' => $box6['cs_lotOwnerCTCNo'],                                  
            'sig_ctc_date' => $box6['cs_lotOwnerDateissued'],            
            'sig_ctc_place' => $box6['cs_lotOwnerPlaceissued'],            
            'validity' => '',
            'permit_type' => 2,
          );                                     
        DB::table($this->lgu_db.'.eceo_signatory_persons')->insert($csLotOwner);
        $csapplicationOthers = array(     
            'application_no' => $csID,
            'permit_type' => 2,
            'measure_length' => '',
            'measure_height' => '',
            'measure_area' => '',
            'measure_others' => '',           
          );                                         
        DB::table($this->lgu_db.'.eceo_application_others')->insert($csapplicationOthers);  
        $csbldgAdditional = array(     
            'application_no' => $csID,
            'permit_type' => 2,
            'type_id' => $box2['cs_natureofWorksID'],
            'type_name' => $box2['cs_natureofWorks'],
            'type_remarks' => $box2['cs_natureofWorksOthers'],
            'type_additional' => 'NATURE',           
          );                                      
        DB::table($this->lgu_db.'.eceo_bldg_additional')->insert($csbldgAdditional);                
        foreach ($csprogressDetails as $row) {          
            $array=array(
              'application_no'=>$csID,
              'permit_type'=> 2,
              'process_id'=>$row['ProgressID'],
              'process_name'=>$row['Progress'],
              'datetime_in'=>$row['In'], 
              'datetime_out'=>$row['Out'],
              'process_by'=>$row['ProcessedByID'],
              'process_emp'=>$row['ProcessedBy'],           
            );                       
         DB::table($this->lgu_db.'.eceo_application_process')->insert($array);
        }        
        $csbuildingApp = array(     
            'application_no' => $csID,
            'notary_date' => Date(Now()),
            'notary_id' => 0,
            'notary_name' => '',
            'notary_year' => '',
            'doc_no' => '',
            'page_no' => '',
            'book_no' => '',
            'series_no' => '',
            'notary_citymun' => '',
            'notary_location' => '',
            'permit_type'=> 2,
          );                                       
        DB::table($this->lgu_db.'.eceo_application_bldg')->insert($csbuildingApp);
        foreach ($csDocDetails as $row) {          
            $array=array(
              'application_no'=>$csID,
              'permit_type'=> 2,
              'type_id'=>$row['csDocumentID'],
              'type_name'=>$row['csDocument'],
              'type_remarks'=>$row['docRemarks'], 
              'type_additional'=>$row['docRemarks'],             
            );                           
         DB::table($this->lgu_db.'.eceo_bldg_documents')->insert($array);
        }      
        $csreceivedBy = array(        
            'application_no' => $csID,
            'permit_type' => 2,
            'sig_id' => $box7['cs_receivedByID'],
            'sig_name' => $box7['cs_receivedBy'],         
            'sig_date' => $box7['cs_receivedByDate'],         
            'sig_count' => 1,
            'sig_remarks' => 'RECEIVED BY',           
          );                     
        DB::table($this->lgu_db.'.eceo_bldg_signatory')->insert($csreceivedBy);
        $cspreparedBy = array(        
            'application_no' => $csID,
            'permit_type' => 2,
            'sig_id' => $box2['cs_preparedByID'],
            'sig_name' => $box2['cs_preparedBy'],         
            'sig_date' => Date(Now()),         
            'sig_count' => 2,
            'sig_remarks' => 'PREPARED BY',           
          );                                 
        DB::table($this->lgu_db.'.eceo_bldg_signatory')->insert($cspreparedBy);
        $civilStructuralPermit = array(
            'bns_id' => $appID,
            'bldg_permit_no' => $csMain['cs_buildingNo'],
            'bp_id' => $csMain['cs_buildingID'],
            'date_save' => Date(Now()),  
            'permit_code' => $box9['cs_issuanceNo'],
            'application_no' => $csID,
            'date_issued' => $box9['cs_issuanceDate'],            
            'bldg_official_name' => $box9['cs_issuancedeptname'],
            'bldg_official_id' => $box9['cs_issuancedeptnameID'],
            'project_id' => $csMain['cs_projectID'],
            'project_name' => $csMain['cs_projectName'],
          );                          
        DB::table($this->lgu_db.'.eceo_civil_permit')->insert($civilStructuralPermit); 
      
    }
    public function update($idx, $csMain, $box1, $box2, $box3, $box4, $box5, $box6, $box7, $csDocDetails, $box8, $csprogressDetails, $box9)
    {             
        if ($box1['cs_ownedConstructionID'] > 0) {
            $appType = 'BUSINESS';
            $appID = $box1['cs_ownedConstructionID'];
            $appName = $box1['cs_ownedConstruction'];
            } else {
            $appType = 'PERSON';           
            $appID = $box1['cs_applicantID'];
            $appName = $box1['cs_applicantName'];
            };            
        $newCivilStructure = array( 
            'application_save_date' => Date(Now()),     
            'application_status' => 'NEW',
            'applicant_type' => $appType,
            'applicant_id' =>  $appID,
            'application_code' => $csMain['cs_applicationNo'],
            'area_code' => $csMain['cs_areaNo'],           
            'applicant_address' => $box1['cs_addBarangay'], 
            'lot_no' => $box1['cs_locLotNo'],
            'blk_no' => $box1['cs_locBlockNo'],           
            'tct_no' => $box1['cs_TCTNo'],
            'property_st' => $box1['cs_locStreet'],
            'property_brgy' => $box1['cs_locBarangay'],
            'property_city' => $box1['cs_locCityMun'],
            'with_td' => 'NO',
            'indigens_status' => 'NO',
            'with_bp' => 'NO',
            'td_no' => $box1['cs_TDNo'],
            'user_id' => Auth::user()->id,
            'permit_code' => $box9['cs_issuanceNo'],
            'bldg_code' => $csMain['cs_buildingNo'],
            'permit_type' => 2,
            'bldg_permit_id' => $csMain['cs_buildingID'],                                       
            );                                          
        DB::table($this->lgu_db.'.eceo_application')->where('application_no',$idx)->where('permit_type',2)->update($newCivilStructure);
        if ($box1['cs_scopeofWork'] == "New Construction") {
            $scopeID = 1;          
        } elseif ($box1['cs_scopeofWork'] == "Erection") {
            $scopeID = 2;
        } elseif ($box1['cs_scopeofWork'] == "Addition") {
            $scopeID = 3; 
        } elseif ($box1['cs_scopeofWork'] == "Alteration") {
            $scopeID = 4; 
        } elseif ($box1['cs_scopeofWork'] == "Renovation") {
            $scopeID = 5; 
        } elseif ($box1['cs_scopeofWork'] == "Conversion") {
            $scopeID = 6; 
        } elseif ($box1['cs_scopeofWork'] == "Repair") {
            $scopeID = 7; 
        } elseif ($box1['cs_scopeofWork'] == "Moving") {
            $scopeID = 8; 
        } elseif ($box1['cs_scopeofWork'] == "Raising") {
            $scopeID = 9; 
        } elseif ($box1['cs_scopeofWork'] == "Accessory Building/Structure") {
            $scopeID = 10;
        } else {
            $scopeID = 11;   
        };

        if ($box1['cs_characterofOccupancy'] == "Group A: Residential, Dwellings") {
            $occupancyID = 1;       
        } elseif ($box1['cs_characterofOccupancy'] == "Group B: Residential Hotel, Apartment") {
            $occupancyID = 2;
        } elseif ($box1['cs_characterofOccupancy'] == "Group C: Educational, Recreational") {
            $occupancyID = 3;   
        } elseif ($box1['cs_characterofOccupancy'] == "Group D: Institutional") {
            $occupancyID = 4;
        } elseif ($box1['cs_characterofOccupancy'] == "Group E: Business and Mercantile") {
            $occupancyID = 5;
        } elseif ($box1['cs_characterofOccupancy'] == "Group F: Industrial") {
            $occupancyID = 6;
        } elseif ($box1['cs_characterofOccupancy'] == "Group G: Industrial Storage and Hazardous") {
            $occupancyID = 7;
        } elseif ($box1['cs_characterofOccupancy'] == "Group H: Recreational, Assembly Occupant Load Less Than 1000") {
            $occupancyID = 8;  
        } elseif ($box1['cs_characterofOccupancy'] == "Group I: Recreational, Assembly Occupant Load 1000 or More") {
            $occupancyID = 9;     
        } elseif ($box1['cs_characterofOccupancy'] == "Group J: Agricultural Accessory") {
            $occupancyID = 10;     
        } else{
            $occupancyID = 11;       
        };                        
        $newcsDetails = array(                          
            'application_no' => $idx,
            'zoning_no' => 0,
            'subd_no' => 0,
            'date_of_entry' => Date(Now()),
            'area_no_prov_code' => $csMain['cs_applicationNo'],
            'area_no_bgry_code' => $csMain['cs_areaNo'],          
            'establishment_name' => $appName,
            'scope_of_work' => $scopeID,
            'char_of_occupancy' => $occupancyID,
            'occupancy_classified' => '',           
            'no_of_storey' => 0,
            'no_of_units' => 0,
            'total_estmtd_cst' => 0,           
            'total_flr_area' => 0,           
            'proposed_date_of_const' => Date(Now()),
            'expected_date_of_comp' => Date(Now()),            
            'bldg_classification' => '',      
            'permit_type' => 2,
          );                                 
        DB::table($this->lgu_db.'.eceo_application_details')->where('application_no',$idx)->where('permit_type',2)->update($newcsDetails);
        $csAppInfo = array(        
            'application_no' => $idx,
            'owner_id' => $appID, 
            'owner_prefix' => $box1['cs_applicantPFix'],  
            'owner_firstname' => $box1['cs_applicantFName'], 
            'owner_middlename' => $box1['cs_applicantMName'], 
            'owner_lastname' => $box1['cs_applicantLName'], 
            'owner_suffix' => $box1['cs_applicantSFix'], 
            'owner_tin' => $box1['cs_applicantTIN'], 
            'owner_type' => $appType, 
            'owner_ppid' => $box1['cs_applicantID'],
            'owner_bid' => $box1['cs_ownedConstruction'],
            'owner_businessname' => $box1['cs_ownedConstruction'],
            'owner_businesstype' => $box1['cs_formOwnership'], 
            'brgy_id' => $box1['cs_addBarangayID'], 
            'addr_no' => $box1['cs_addNo'], 
            'addr_st' => $box1['cs_addStreet'], 
            'addr_brgy' => $box1['cs_addBarangay'], 
            'addr_citymun' => $box1['cs_addCityMun'], 
            'addr_zipcode' => $box1['cs_zipCode'],            
            'tel_no' => $box1['cs_telNo'],
            'permit_type' => 2,
            'occupancy' => $box1['cs_characterofOccupancy']           
          );                         
        DB::table($this->lgu_db.'.eceo_application_info')->where('application_no',$idx)->where('permit_type',2)->update($csAppInfo);
        $csScopeInfo = array(        
            'application_no' => $idx,
            'scope_id' => $scopeID, 
            'scope_name' => $box1['cs_scopeofWork'],  
            'scope_remarks' => $box1['cs_scopeofRemarks'],
            'permit_type' => 2,           
          );                        
        DB::table($this->lgu_db.'.eceo_bldg_scope')->where('application_no',$idx)->where('permit_type',2)->update($csScopeInfo);
        if ($box3['cs_architectId'] > 0) {
            $engineerID1 = $box3['cs_architectId'];           
            } else {
            $engineerID1 = 0;
            };  
        $csEngineer1 = array(       
            'application_no' => $idx,
            'person_id' => $engineerID1,
            'title_no' => 11,
            'person_name' => $box3['cs_architectName'],
            'st_no' => $box3['cs_architectAddress'],
            'sig_date' => $box3['cs_architectDatesigned'],
            'sig_prc_no' => $box3['cs_architectPRCNo'],
            'sig_prc_date' => $box3['cs_architectValidityDate'],
            'sig_ptr_no' => $box3['cs_architectPTRNo'],
            'sig_ptr_date' => $box3['cs_architectDateissued'],
            'sig_ptr_place' => $box3['cs_architectIssuedat'],
            'sig_tin_no' => $box3['cs_architectTIN'],
            'sig_ctc_no' => '',
            'sig_ctc_date' => $box3['cs_architectDatesigned'],
            'sig_ctc_place' => '',
            'validity' => '',
            'permit_type' => 2,
          );                       
        DB::table($this->lgu_db.'.eceo_signatory_persons')->where('application_no',$idx)->where('permit_type',2)->where('title_no',11)->update($csEngineer1);
        if ($box4['cs_architectId'] > 0) {
            $engineerID2 = $box4['cs_architectId'];           
            } else {
            $engineerID2 = 0;
            };  
        $csEngineer2 = array(       
            'application_no' => $idx,
            'person_id' => $engineerID2,
            'title_no' => 5,
            'person_name' => $box4['cs_architectName'],
            'st_no' => $box4['cs_architectAddress'],
            'sig_date' => $box4['cs_architectDatesigned'],
            'sig_prc_no' => $box4['cs_architectPRCNo'],
            'sig_prc_date' => $box4['cs_architectValidityDate'],
            'sig_ptr_no' => $box4['cs_architectPTRNo'],
            'sig_ptr_date' => $box4['cs_architectDateissued'],
            'sig_ptr_place' => $box4['cs_architectIssuedat'],
            'sig_tin_no' => $box4['cs_architectTIN'],
            'sig_ctc_no' => '',
            'sig_ctc_date' => $box4['cs_architectDatesigned'],
            'sig_ctc_place' => '',
            'validity' => '',
            'permit_type' => 2,
          );                    
        DB::table($this->lgu_db.'.eceo_signatory_persons')->where('application_no',$idx)->where('permit_type',2)->where('title_no',5)->update($csEngineer2);
        if ($box5['cs_buildingOwnerID'] > 0) {
            $ownerID = $box5['cs_buildingOwnerID'];           
            } else {
            $ownerID = 0;
            };  
        $csApplicant = array(        
            'application_no' => $idx,
            'person_id' => $ownerID,
            'title_no' => 1,
            'person_name' => $box5['cs_buildingOwnerName'],
            'st_no' => $box5['cs_buildingOwnerAddress'],
            'sig_date' => $box5['cs_buildingOwnerDate'],
            'sig_prc_no' => '',
            'sig_prc_date' => $box5['cs_buildingOwnerDate'],
            'sig_ptr_no' => '',
            'sig_ptr_date' => $box5['cs_buildingOwnerDate'],
            'sig_ptr_place' => '',
            'sig_tin_no' => $box1['cs_applicantTIN'],
            'sig_ctc_no' => $box5['cs_buildingOwnerCTCNo'],
            'sig_ctc_date' => $box5['cs_buildingOwnerDateissued'],
            'sig_ctc_place' => $box5['cs_buildingOwnerPlaceissued'],
            'validity' => '',
            'permit_type' => 2,
          ); 
        DB::table($this->lgu_db.'.eceo_signatory_persons')->where('application_no',$idx)->where('permit_type',2)->where('title_no',1)->update($csApplicant);
        if ($box6['cs_lotOwnerID'] > 0) {
            $lotOwnerID = $box6['cs_lotOwnerID'];           
            } else {
            $lotOwnerID = 0;
            };  
        $csLotOwner = array(        
            'application_no' => $idx,
            'person_id' => $lotOwnerID,
            'title_no' => 2,
            'person_name' => $box6['cs_lotOwnerName'],
            'st_no' => $box6['cs_lotOwnerAddress'],
            'sig_date' => $box6['cs_lotOwnerDate'],                   
            'sig_prc_no' => '',            
            'sig_prc_date' => $box6['cs_lotOwnerDate'],            
            'sig_ptr_no' => '',            
            'sig_ptr_date' => $box6['cs_lotOwnerDate'],            
            'sig_ptr_place' => '',
            'sig_tin_no' => '',                   
            'sig_ctc_no' => $box6['cs_lotOwnerCTCNo'],                                  
            'sig_ctc_date' => $box6['cs_lotOwnerDateissued'],            
            'sig_ctc_place' => $box6['cs_lotOwnerPlaceissued'],            
            'validity' => '',
            'permit_type' => 2,
          );                                     
        DB::table($this->lgu_db.'.eceo_signatory_persons')->where('application_no',$idx)->where('permit_type',2)->where('title_no',2)->update($csLotOwner);
        $csapplicationOthers = array(     
            'application_no' => $idx,
            'permit_type' => 2,
            'measure_length' => '',
            'measure_height' => '',
            'measure_area' => '',
            'measure_others' => '',           
          );                                       
        DB::table($this->lgu_db.'.eceo_application_others')->where('application_no',$idx)->where('permit_type',2)->update($csapplicationOthers);
        $csbldgAdditional = array(     
            'application_no' => $idx,
            'permit_type' => 2,
            'type_id' => $box2['cs_natureofWorksID'],
            'type_name' => $box2['cs_natureofWorks'],
            'type_remarks' => $box2['cs_natureofWorksOthers'],
            'type_additional' => 'NATURE',           
          );                                      
        DB::table($this->lgu_db.'.eceo_bldg_additional')->where('application_no',$idx)->where('permit_type',2)->update($csbldgAdditional);              
        DB::table($this->lgu_db . '.eceo_application_process')->where('application_no', $idx)->delete();
        foreach ($csprogressDetails as $row) {          
            $array=array(
              'application_no'=>$idx,
              'permit_type'=> 2,
              'process_id'=>$row['ProgressID'],
              'process_name'=>$row['Progress'],
              'datetime_in'=>$row['In'], 
              'datetime_out'=>$row['Out'],
              'process_by'=>$row['ProcessedByID'],
              'process_emp'=>$row['ProcessedBy'],           
            );                       
         DB::table($this->lgu_db.'.eceo_application_process')->insert($array);
        }        
        $csbuildingApp = array(     
            'application_no' => $idx,
            'notary_date' => Date(Now()),
            'notary_id' => 0,
            'notary_name' => '',
            'notary_year' => '',
            'doc_no' => '',
            'page_no' => '',
            'book_no' => '',
            'series_no' => '',
            'notary_citymun' => '',
            'notary_location' => '',
            'permit_type'=> 2,
          );                                       
        DB::table($this->lgu_db.'.eceo_application_bldg')->where('application_no',$idx)->where('permit_type',2)->update($csbuildingApp);
        DB::table($this->lgu_db . '.eceo_bldg_documents')->where('application_no', $idx)->delete();
        foreach ($csDocDetails as $row) {          
            $array=array(
              'application_no'=>$idx,
              'permit_type'=> 2,
              'type_id'=>$row['csDocumentID'],
              'type_name'=>$row['csDocument'],
              'type_remarks'=>$row['docRemarks'], 
              'type_additional'=>$row['docRemarks'],             
            );                           
         DB::table($this->lgu_db.'.eceo_bldg_documents')->insert($array);
        }      
        $csreceivedBy = array(        
            'application_no' => $idx,
            'permit_type' => 2,
            'sig_id' => $box7['cs_receivedByID'],
            'sig_name' => $box7['cs_receivedBy'],         
            'sig_date' => $box7['cs_receivedByDate'],         
            'sig_count' => 1,
            'sig_remarks' => 'RECEIVED BY',           
          );                   
        DB::table($this->lgu_db.'.eceo_bldg_signatory')->where('application_no',$idx)->where('permit_type',2)->where('sig_count',1)->update($csreceivedBy);
        $cspreparedBy = array(        
            'application_no' => $idx,
            'permit_type' => 2,
            'sig_id' => $box2['cs_preparedByID'],
            'sig_name' => $box2['cs_preparedBy'],         
            'sig_date' => Date(Now()),         
            'sig_count' => 2,
            'sig_remarks' => 'PREPARED BY',           
          );                                
        DB::table($this->lgu_db.'.eceo_bldg_signatory')->where('application_no',$idx)->where('permit_type',2)->where('sig_count',2)->update($cspreparedBy);
        $civilStructuralPermit = array(
            'bns_id' => $appID,
            'bldg_permit_no' => $csMain['cs_buildingNo'],
            'bp_id' => $csMain['cs_buildingID'],
            'date_save' => Date(Now()),  
            'permit_code' => $box9['cs_issuanceNo'],
            'application_no' => $idx,
            'date_issued' => $box9['cs_issuanceDate'],            
            'bldg_official_name' => $box9['cs_issuancedeptname'],
            'bldg_official_id' => $box9['cs_issuancedeptnameID'],
            'project_id' => $csMain['cs_projectID'],
            'project_name' => $csMain['cs_projectName'],
          );                        
        DB::table($this->lgu_db.'.eceo_civil_permit')->where('application_no',$idx)->update($civilStructuralPermit);
    }  
      
    public function delete(Request $request)
    {  
        $id=$request->id;        
        $data['status'] = 'CANCELLED';
        DB::table($this->lgu_db.'.eceo_application')->where('application_no', $id) ->update($data);
        $reason['Form_name'] ='Civil/Structural Permit';       
        $reason['Trans_ID'] =$id;       
        $reason['Type_'] ='Cancel Record';       
        $reason['Trans_by'] =Auth::user()->id;       
        $this->G->insertReason($reason);  
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }    
    public function printcivilStructurePermitList(Request $request)
    {
        $data = $request->civilStructuralList;        
        $logo = config('variable.logo');        
        try {
            $html_content ='<body>
            '.$logo.'            
            <h2 align="center">Master List for Issuance of Civil/Structural Permits</2>
            <br></br>
            <br></br>
            <table border="1" cellpadding="2">
                <tr align="center" >
                    <th style="width:10%">Application No.</th>
                    <th style="width:8%">Application Date</th>
                    <th style="width:6%">Permit No.</th>
                    <th style="width:15%">Project Name</th>
                    <th style="width:15%">Location</th>
                    <th style="width:15%">Applicant Name</th>
                    <th style="width:8%">TD No.</th>
                    <th style="width:8%">Issued Date</th>
                    <th style="width:7%">OR No</th>
                    <th style="width:8%">Payment Status</th>
                </tr>
                <tbody>';
                foreach($data as $row){                             
                    $html_content .='
                    <tr>
                        <td align="center" style="width:10%">'.$row['Application No'].'</td>
                        <td align="center" style="width:8%">'.$row['Trans Date'].'</td>
                        <td align="center" style="width:6%">'.$row['FP No'].'</td>
                        <td align="left" style="width:15%">'.$row['Project Name'].'</td>
                        <td align="left" style="width:15%">'.$row['Project Location'].'</td>
                        <td align="left" style="width:15%">'.$row['Applicant Name'].'</td>
                        <td align="center" style="width:8%">'.$row['TCT No'].'</td>                 
                        <td align="center" style="width:8%">'.$row['Issued Date'].'</td>
                        <td align="center" style="<width:7%">'.$row['OR No'].'</td>                 
                        <td align="center" style="width:8%">'.$row['Payment Status'].'</td>              
                    </tr>';                }
                $html_content .='</tbody>
            </table>
            </body>';         
            
            PDF::SetTitle('Building Permit Master List');
            PDF::SetFont('times', '', 8);
            PDF::AddPage('L');
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path().'/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printBuildingPermitForm($id)
    {    
         $trans_Name = 'Building Permit';      
         $trans_ID = 0;           
         $pay_ID = 0; 
         $pay_Type = 0;   

         $dataMain = DB::select('call '.$this->lgu_db.'.balodoy_get_buildingpermit_main_print(?)',array($id));
         $dataMaterials = DB::select('call '.$this->lgu_db.'.balodoy_get_buildingpermit_materialsdetails_print(?)',array($id));
         $databox2 = DB::select('call '.$this->lgu_db.'.balodoy_get_buildingpermit_box2_print(?)',array($id));
         $databox3 = DB::select('call '.$this->lgu_db.'.balodoy_get_buildingpermit_box3_print(?)',array($id));
         $databox4 = DB::select('call '.$this->lgu_db.'.balodoy_get_buildingpermit_box4_print(?)',array($id));
         $databox5 = DB::select('call '.$this->lgu_db.'.balodoy_get_buildingpermit_box5_print(?)',array($id));
         $dataDocuments = DB::select('call '.$this->lgu_db.'.balodoy_display_setup_certification_building_permit(?,?,?,?)',array($trans_Name,$trans_ID,$pay_ID,$pay_Type));
         $databox7 = DB::select('call '.$this->lgu_db.'.balodoy_get_buildingpermit_box7_print(?)',array($id));       
        
        foreach($dataMain as $row) { 
            $infoMain = ($row);          
        }
        foreach($dataMaterials as $row) { 
            $infoMaterials = ($row);          
        }
        foreach($databox2 as $row) { 
            $infoBox2 = ($row);          
        } 
        foreach($databox3 as $row) { 
            $infoBox3 = ($row);          
        }
        foreach($databox4 as $row) { 
            $infoBox4 = ($row);          
        }
        foreach($databox5 as $row) { 
            $infoBox5 = ($row);          
        }
        foreach($databox7 as $row) { 
            $infoBox7 = ($row);          
        }
         if ($infoMain->{'Application Type'} == "NEW") {
            $AppNew = '<span style="font-family:zapfdingbats;">4</span>';
            } else {
            $AppNew = '';
            };
        if ($infoMain->{'Application Type'} == "RENEW") {
            $AppRenew = '<span style="font-family:zapfdingbats;">4</span>';
            } else {
            $AppRenew = '';
            };
        if ($infoMain->{'Application Type'} == "AMEND") {
            $AppAmend = '<span style="font-family:zapfdingbats;">4</span>';
            } else {
            $AppAmend = '';
            };
        $newConstruction = '';
        $erection = '';
        $addition = '';
        $alteration = '';
        $renovation = '';
        $conversion = '';
        $repair = '';
        $moving = '';
        $raising = '';
        $accesoryBuilding = '';
        $otherBuilding = '';

        $erectionRemarks = '';
        $additionRemarks = '';
        $alterationRemarks = '';
        $renovationRemarks = '';
        $conversionRemarks = '';
        $repairRemarks = '';
        $movingRemarks = '';
        $raisingRemarks = '';
        $accesoryBuildingRemarks = '';
        $otherBuildingRemarks = '';
       
        if ($infoMain->{'Scope of Work'} == 1) {           
            $newConstruction = '<span style="font-family:zapfdingbats;">4</span>';                     
        } elseif ($infoMain->{'Scope of Work'} == 2) {
            $erection = '<span style="font-family:zapfdingbats;">4</span>';
            $erectionRemarks = $infoMain->{'Scope Remarks'};
        } elseif ($infoMain->{'Scope of Work'} == 3) {
            $addition = '<span style="font-family:zapfdingbats;">4</span>';
            $additionRemarks = $infoMain->{'Scope Remarks'}; 
        } elseif ($infoMain->{'Scope of Work'} == 4) {
            $alteration = '<span style="font-family:zapfdingbats;">4</span>';
            $alterationRemarks = $infoMain->{'Scope Remarks'};
        } elseif ($infoMain->{'Scope of Work'} == 5) {
            $renovation = '<span style="font-family:zapfdingbats;">4</span>';
            $renovationRemarks = $infoMain->{'Scope Remarks'}; 
        } elseif ($infoMain->{'Scope of Work'} == 6) {
            $conversion = '<span style="font-family:zapfdingbats;">4</span>';
            $conversionRemarks = $infoMain->{'Scope Remarks'}; 
        } elseif ($infoMain->{'Scope of Work'} == 7) {
            $repair = '<span style="font-family:zapfdingbats;">4</span>';
            $repairRemarks = $infoMain->{'Scope Remarks'};
        } elseif ($infoMain->{'Scope of Work'} == 8) {
            $moving = '<span style="font-family:zapfdingbats;">4</span>';
            $movingRemarks = $infoMain->{'Scope Remarks'};
        } elseif ($infoMain->{'Scope of Work'} == 9) {
            $raising = '<span style="font-family:zapfdingbats;">4</span>';
            $raisingRemarks = $infoMain->{'Scope Remarks'};
        } elseif ($infoMain->{'Scope of Work'} == 10) {
            $accesoryBuilding = '<span style="font-family:zapfdingbats;">4</span>';
            $accesoryBuildingRemarks = $infoMain->{'Scope Remarks'};
        } else {
             $otherBuilding = '<span style="font-family:zapfdingbats;">4</span>';
             $otherBuildingRemarks = $infoMain->{'Scope Remarks'}; 
        };
        
        $groupA = '';
        $groupB = '';
        $groupC = '';
        $groupD = '';
        $groupE = '';
        $groupF = '';
        $groupG = '';
        $groupH = '';
        $groupI = '';
        $groupJ = '';
        $groupK = ''; 
        $groupKRemarks = '';
        if ($infoMain->{'Character of Occupancy'} == 1) {           
            $groupA = '<span style="font-family:zapfdingbats;">4</span>';                     
        } elseif ($infoMain->{'Character of Occupancy'} == 2) {
            $groupB = '<span style="font-family:zapfdingbats;">4</span>';            
        } elseif ($infoMain->{'Character of Occupancy'} == 3) {
            $groupC = '<span style="font-family:zapfdingbats;">4</span>';          
        } elseif ($infoMain->{'Character of Occupancy'} == 4) {
            $groupD = '<span style="font-family:zapfdingbats;">4</span>';          
        } elseif ($infoMain->{'Character of Occupancy'} == 5) {
            $groupE = '<span style="font-family:zapfdingbats;">4</span>';          
        } elseif ($infoMain->{'Character of Occupancy'} == 6) {
            $groupF = '<span style="font-family:zapfdingbats;">4</span>';           
        } elseif ($infoMain->{'Character of Occupancy'} == 7) {
            $groupG = '<span style="font-family:zapfdingbats;">4</span>';           
        } elseif ($infoMain->{'Character of Occupancy'} == 8) {
            $groupH = '<span style="font-family:zapfdingbats;">4</span>';         
        } elseif ($infoMain->{'Character of Occupancy'} == 9) {
            $groupI = '<span style="font-family:zapfdingbats;">4</span>';           
        } elseif ($infoMain->{'Character of Occupancy'} == 10) {
            $groupJ = '<span style="font-family:zapfdingbats;">4</span>';
        } else {
             $groupK = '<span style="font-family:zapfdingbats;">4</span>';
             $groupKRemarks = $infoMain->{'bp_characterOccupancyOthers'}; 
        };
        
        $logo = config('variable.logo');        
        try {
        $first_page ='<body>
        <table cellspacing="2">
        <tr>
    	    <td style="width:100%" align="left">NBC FORM NO. B - 01</td>         
        </tr>
        '.$logo.'        
        <tr style="height:25px">                        
            <th style="width:100%" align="Center">
                <h3>APPLICATION FOR BUILDING PERMIT</h3>
            </th>                         
        </tr> 
        <br> 
    	<tr>
    	    <td style="width:20%"></td>            
            <td style="width:3%" align="center" border="1">'.$AppNew.'</td>
            <td style="width:17%" align="left">  NEW</td>
            <td style="width:3%" align="center" border="1">'.$AppRenew.'</td>
            <td style="width:17%" align="left">  RENEW</td>
            <td style="width:3%" align="center" border="1">'.$AppAmend.'</td>
            <td style="width:17%" align="left">  AMENDATORY</td>
            <td style="width:20%"></td> 
        </tr>
        <tr style="height:25px">                        
            <td style="width:20%" align="left">
            APPLICATION NO.
            </td>
            <td style="width:58%" align="left">            
            </td>
            <td style="width:22%" align="left">
            BUILDING PERMIT NO           
            </td>                        
        </tr>
        <tr style="height:25px">                        
            <td style="width:20%" align="left" border="1">
            '.$infoMain->{'Application No'}.'
            </td>
            <td style="width:58%" align="left">            
            </td>
            <td style="width:22%" align="left" border="1">
            '.$infoMain->{'Building Permit No'}.'          
            </td>                        
        </tr>  	
    </table>       
    <table>
        <tr>
            <th tyle="width:20%" align="left"> <b>BOX 1  (TO BE ACCOMPLISHED IN PRINT BY THE APPLICANT)</b></th>
        </tr>  
        <tr>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> OWNER/APPLICANT:</th>
            <th style="width:45%;border-top:0.5px solid black;text-align:center"> FULLNAME</th>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black; text-align:left"> TIN</th> 
            <th style="width:20%;font-size:6px"> DO NOT FILL-UP (NSO USE ONLY)</th>            
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:45%;text-align:center">'.$infoMain->{'Applicant Name'}.'</th>
            <th style="width:20%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left">'.$infoMain->{'Applicant TIN'}.'</th> 
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>            
        </tr>
        <tr>
            <th style="width:60%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> FOR CONSTRUCTION OWNED</th>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black; text-align:left"> FORM OF OWNERSHIP</th> 
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:60%;border-left:0.5px solid black;text-align:center">'.$infoMain->{'Business Name'}.'</th>
            <th style="width:20%;border-left:0.5px solid black;border-right:0.5px solid black; text-align:left">'.$infoMain->{'Form of Ownership'}.'</th> 
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>           
        </tr>
        <tr>
            <th style="width:60%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left">BY AN ENTERPRISE</th>
            <th style="width:20%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>           
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"> ADDRESS:</th>
            <th style="width:5%;border-top:0.5px solid black;text-align:center"> NO.</th>
            <th style="width:10%;border-top:0.5px solid black;text-align:center"> STREET</th>
            <th style="width:10%;border-top:0.5px solid black;text-align:center"> BARANGAY</th>
            <th style="width:17%;border-top:0.5px solid black;text-align:center"> CITY/MUNICIPALITY</th>
            <th style="width:8%;border-top:0.5px solid black;text-align:center"> ZIP CODE </th>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"> TELEPHONE NO.</th> 
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:60%;border-left:0.5px solid black;text-align:center">'.$infoMain->{'Applicant Address'}.'</th>
            <th style="width:20%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left">'.$infoMain->{'Applicant TelNo'}.'</th> 
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;font-size:6px;text-align:left">LOCATION OF CONSTRUCTION:</th>
            <th style="width:6%;border-top:0.5px solid black;font-size:6px;text-align:left">LOT NO.</th>
            <th style="width:6%;border-top:0.5px solid black;font-size:6px;text-align:center"><u>'.$infoMain->{'Location LotNo'}.'</u></th>
            <th style="width:7%;border-top:0.5px solid black;font-size:6px;text-align:left">BLK NO.</th>
            <th style="width:6%;border-top:0.5px solid black;font-size:6px;text-align:center"><u>'.$infoMain->{'Location BlockNo'}.'</u></th>
            <th style="width:7%;border-top:0.5px solid black;font-size:6px;text-align:center">TCT NO.</th>
            <th style="width:7%;border-top:0.5px solid black;font-size:6px;text-align:center"><u>'.$infoMain->{'bp_TCTNo'}.'</u></th>
            <th style="width:10%;border-top:0.5px solid black;font-size:6px;text-align:center">TAX DEC. NO.</th>
            <th style="width:11%;border-top:0.5px solid black;border-right:0.5px solid black;font-size:6px;text-align:center"><u>'.$infoMain->{'Taxdec No'}.'</u></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>           
            <th style="width:8%;border-left:0.5px solid black;font-size:6px;text-align:left">STREET</th>
            <th style="width:14%;font-size:6px;text-align:left"><u>'.$infoMain->{'Location Street'}.'</u></th>
            <th style="width:10%;font-size:6px;text-align:left">BARANGAY</th>
            <th style="width:13%;font-size:6px;text-align:center"><u>'.$infoMain->{'Location Barangay'}.'</u></th>
            <th style="width:20%;font-size:6px;text-align:center">CITY/ MUNICIPALITY OF</th>
            <th style="width:15%;font-size:6px;border-right:0.5px solid black;text-align:center"><u>'.$infoMain->{'Location City'}.'</u></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>                     
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black;border-top:0.5px solid black"></th>           
            <th style="width:79%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"><b>SCOPE OF WORK</b></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>
        </tr>    
    </table>
    <table>        
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$newConstruction.'</th>
            <th style="width:1%"></th> 
            <th style="width:15%;font-size:6px" align="left"> NEW CONSTRUCTION</th>
            <th style="width:1%"></th>
            <th style="width:3%" align="center" border="1">'.$renovation.'</th>
            <th style="width:1%"></th> 
            <th style="width:11%;font-size:6px" align="left"> RENOVATION</th>
            <th style="width:1%"></th> 
            <th style="width:13%;border-bottom:0.5px solid black;text-align:left">'.$renovationRemarks.'</th>
            <th style="width:1%"></th>
            <th style="width:3%" align="center" border="1">'.$raising.'</th>
            <th style="width:1%"></th> 
            <th style="width:8%;font-size:6px" align="left"> RAISING</th>
            <th style="width:1%"></th>             
            <th style="width:16%;border-bottom:0.5px solid black;border-right:0.5px solid black;text-align:left">'.$renovationRemarks.'</th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$erection.'</th>
            <th style="width:1%"></th> 
            <th style="width:6%;font-size:6px" align="left">ERECTION</th>
            <th style="width:9%;border-bottom:0.5px solid black;text-align:left">'.$erectionRemarks.'</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$conversion.'</th>
            <th style="width:1%"></th> 
            <th style="width:11%;font-size:6px" align="left">CONVERSION</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left">'.$conversionRemarks.'</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$accesoryBuilding.'</th>
            <th style="width:1%"></th> 
            <th style="width:20%;font-size:6px" align="left">ACCESSORY BUILDING/STRUCTURE</th>            
            <th style="width:5%;border-bottom:0.5px solid black;border-right:0.5px solid black;text-align:left">'.$accesoryBuildingRemarks.'</th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$addition.'</th>
            <th style="width:1%"></th> 
            <th style="width:6%;font-size:6px" align="left">ADDITION</th>
            <th style="width:9%;border-bottom:0.5px solid black;text-align:left">'.$additionRemarks.'</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$repair.'</th>
            <th style="width:1%"></th> 
            <th style="width:8%;font-size:6px" align="left">REPAIR</th>
            <th style="width:17%;border-bottom:0.5px solid black;text-align:left">'.$repairRemarks.'</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$otherBuilding.'</th>
            <th style="width:1%"></th> 
            <th style="width:12%;font-size:6px" align="left">OTHERS (Specify)</th>            
            <th style="width:13%;border-bottom:0.5px solid black;border-right:0.5px solid black;text-align:left">'.$otherBuildingRemarks.'</th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$alteration.'</th>
            <th style="width:1%"></th> 
            <th style="width:8%;font-size:6px" align="left">ALTERATION</th>
            <th style="width:7%;border-bottom:0.5px solid black;text-align:left">'.$alterationRemarks.'</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$moving.'</th>
            <th style="width:1%"></th> 
            <th style="width:8%;font-size:6px" align="left">MOVING</th>
            <th style="width:17%;border-bottom:0.5px solid black;text-align:left">'.$movingRemarks.'</th>
            <th style="width:5%"></th>            
            <th style="width:25%;border-bottom:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black;border-top:0.5px solid black"></th>
            <th style="width:79%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"><b>USE OR CHARACTER OF OCCUPANCY</b></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$groupA.'</th>
            <th style="width:1%"></th> 
            <th style="width:25%;font-size:6px" align="left">GROUP A : RESIDENTIAL, DWELLINGS</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$groupF.'</th>
            <th style="width:1%"></th> 
            <th style="width:14%;font-size:6px" align="left">GROUP F : INDUSTRIAL</th>
            <th style="width:1%"></th>
            <th style="width:3%" align="center" border="1">'.$groupK.'</th>
            <th style="width:1%"></th>
            <th style="width:11%;font-size:6px" align="left">OTHERS (Specify)</th>
            <th style="width:15%;border-bottom:0.5px solid black;border-right:0.5px solid black;text-align:left">'.$groupKRemarks.'</th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;background-color:black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$groupB.'</th>
            <th style="width:1%"></th> 
            <th style="width:25%;font-size:6px" align="left">GROUP B : RESIDENTIAL HOTEL, APARTMENT</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$groupG.'</th>
            <th style="width:1%"></th> 
            <th style="width:45%;border-right:0.5px solid black;font-size:6px" align="left">GROUP G : INDUSTRIAL STORAGE AND HAZARDOUS</th>
            <th style="width:1%"></th>            
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;background-color:black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$groupC.'</th>
            <th style="width:1%"></th> 
            <th style="width:25%;font-size:6px" align="left">GROUP C : EDUCATIONAL, RECREATIONAL</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$groupH.'</th>
            <th style="width:1%"></th> 
            <th style="width:45%;border-right:0.5px solid black;font-size:6px" align="left">GROUP H : RECREATIONAL, ASSEMBLY OCCUPANT LOAD LESS THAN 1000</th>
            <th style="width:1%"></th>            
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$groupD.'</th>
            <th style="width:1%"></th> 
            <th style="width:25%;font-size:6px" align="left">GROUP D : INSTITUTIONAL</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$groupI.'</th>
            <th style="width:1%"></th> 
            <th style="width:45%;border-right:0.5px solid black;font-size:6px" align="left">GROUP I : RECREATIONAL, ASSEMBLY OCCUPANT LOAD 1000 OR MORE</th>
            <th style="width:1%"></th>            
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:1%;border-left:0.5px solid black"></th>    	           
            <th style="width:3%" align="center" border="1">'.$groupE.'</th>
            <th style="width:1%"></th> 
            <th style="width:25%;font-size:6px" align="left">GROUP E : BUSINESS AND MERCANTILE</th>
            <th style="width:1%"></th> 
            <th style="width:3%" align="center" border="1">'.$groupJ.'</th>
            <th style="width:1%"></th> 
            <th style="width:45%;border-right:0.5px solid black;font-size:6px" align="left">GROUP J : AGRICULTURAL, ACCESSORY</th>
            <th style="width:1%"></th>            
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:1%"></th> 
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black;border-top:0.5px solid black"></th> 
            <th style="width:19%;border-top:0.5px solid black;text-align:left">OCCUPANCY CLASSIFIED</th>
            <th style="width:1%;border-top:0.5px solid black"></th>
            <th style="width:18%;border-top:0.5px solid black;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Occupancy Classified'}.'</th>
            <th style="width:5%;border-top:0.5px solid black"></th>
            <th style="width:35%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left">COST ESTIMATES:</th>           
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th> 
            <th style="width:19%;text-align:left">NUMBER OF UNITS</th>
            <th style="width:1%"></th>
            <th style="width:18%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'No of Units'}.'</th>
            <th style="width:5%"></th>
            <th style="width:15%;text-align:left">BUILDING</th>
            <th style="width:5%;text-align:center">Php</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left">'.$infoMain->{'Building Fee'}.'</th>           
            <th style="width:1%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th> 
            <th style="width:19%;text-align:left">TOTAL FLOOR AREA</th>            
            <th style="width:1%"></th>
            <th style="width:10%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Floor Area'}.'</th>
            <th style="width:8%;text-align:left">SQ. MTRS. </th>
            <th style="width:5%"></th>
            <th style="width:15%;text-align:left">ELECTRICAL</th>
            <th style="width:5%;text-align:center">Php</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left">'.$infoMain->{'Electrical Fee'}.'</th>           
            <th style="width:1%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th> 
            <th style="width:27%;text-align:left">PROPOSED DATE OF CONSTRUCTION</th>
            <th style="width:1%"></th>
            <th style="width:10%;border-bottom:0.5px solid black;text-align:center">'.strtoupper(date("m/d/Y", strtotime($infoMain->{'Date of Construction'}))).'</th>
            <th style="width:5%"></th>
            <th style="width:15%;text-align:left">MECHANICAL </th>
            <th style="width:5%;text-align:center">Php</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left">'.$infoMain->{'Mechanical Fee'}.'</th>           
            <th style="width:1%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th> 
            <th style="width:27%;text-align:left">EXPECTED DATE OF COMPLETION</th>
            <th style="width:1%"></th>
            <th style="width:10%;border-bottom:0.5px solid black;text-align:center">'.strtoupper(date("m/d/Y", strtotime($infoMain->{'Date of Completion'}))).'</th>
            <th style="width:5%"></th>
            <th style="width:15%;text-align:left">PLUMBING </th>
            <th style="width:5%;text-align:center">Php</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left">'.$infoMain->{'Plumbing Fee'}.'</th>           
            <th style="width:1%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th> 
            <th style="width:32%;font-size:6px;text-align:left">MATERIAL OF CONST (WOOD, CONCRETE, STEEL, MIXED)</th>
            <th style="width:10%;font-size:6px;border-bottom:0.5px solid black;text-align:left">'.$infoMaterials->{'Materials'}.'</th>
            <th style="width:1%"></th>
            <th style="width:15%;text-align:left">SANITARY </th>
            <th style="width:5%;text-align:center">Php</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left">'.$infoMain->{'Sanitary Fee'}.'</th>           
            <th style="width:1%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th> 
            <th style="width:15%;text-align:left">OTHERS(Specify</th>
            <th style="width:1%"></th>
            <th style="width:22%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Materials Others'}.'</th>
            <th style="width:5%"></th>
            <th style="width:15%;text-align:left">ELECTRONICS </th>
            <th style="width:5%;text-align:center">Php</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left">'.$infoMain->{'Others Fee'}.'</th>           
            <th style="width:1%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:40%;border-left:0.5px solid black"></th>           
            <th style="width:20%;text-align:left"><b>TOTAL ESTIMATED COST</b></th>
            <th style="width:5%;text-align:center">Php</th>
            <th style="width:14%;border-bottom:0.5px solid black;text-align:left"><b>'.$infoMain->{'Total Est. Cost'}.'</b></th>           
            <th style="width:1%;border-right:0.5px solid black"></th>
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:80%;border-top:0.5px solid black;border-left:0.5px solid black"></th>         
            <th style="width:1%"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-top:0.5px solid black;border-bottom:0.5px solid black;background-color:black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;background-color:black;border-bottom:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-bottom:0.5px solid black;border-bottom:0.5px solid black;text-align:left"></th>
            <th style="width:3%;border-left:0.5px solid black;border-right:0.5px solid black;border-bottom:0.5px solid black;text-align:left"></th>
            <th style="width:1%"></th>          
        </tr>
        <tr>
            <th style="width:100%;border-left:0.5px solid black"><b>BOX 2</b></th>           
        </tr>
        <tr>
            <th style="width:1%;border-top:0.5px solid black;border-left:0.5px solid black"></th>           
            <th style="width:99%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"><b>FULL-TIME INSPECTOR AND SUPERVISOR OF CONSTRUCTION WORKS (REPRESENTING THE OWNER)</b></th>       
        </tr>
        <tr>
            <th style="width:50%;border-left:0.5px solid black;border-top:0.5px solid black"></th>
            <th style="width:2%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left"></th>          
            <th style="width:48%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left">Address</th>       
        </tr>
        <tr>
            <th style="width:5%;border-left:0.5px solid black"></th>
            <th style="width:40%" align="center">'.$infoBox2->{'Architech Name'}.'</th>
            <th style="width:5%"></th>
            <th style="width:50%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:center">'.$infoBox2->{'Architech Address'}.'</th>          
        </tr>
        <tr>
            <th style="width:5%;border-left:0.5px solid black"></th>
            <th style="width:40%;border-top:0.5px solid black" align="center"><b>ARCHITECT OR CIVIL ENGINEER</b></th>
            <th style="width:5%"></th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">  PRC No.</th> 
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center">'.$infoBox2->{'Architech PRCNo'}.'</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">  Validity</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:center">'.strtoupper(date("m/d/Y", strtotime($infoBox2->{'Architech PRCDate'}))).'</th>        
        </tr>
        <tr>
            <th style="width:5%;border-left:0.5px solid black"></th>
            <th style="width:40%" align="center">(Signed and Sealed Over Printed Name)</th>
            <th style="width:5%"></th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">  PTR No.</th> 
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center">'.$infoBox2->{'Architech PTRNo'}.'</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">  Date Issued</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:center">'.strtoupper(date("m/d/Y", strtotime($infoBox2->{'Architech PTRDate'}))).'</th>        
        </tr>
        <tr>
            <th style="width:18%;border-left:0.5px solid black"></th>
            <th style="width:7%" align="left">Date</th>
            <th style="width:15%;border-bottom:0.5px solid black" align="left">'.strtoupper(date("m/d/Y", strtotime($infoBox2->{'Architech DateSigned'}))).'</th>
            <th style="width:10%"></th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">  Issued at</th> 
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center">'.$infoBox2->{'Architech PTRPlace'}.'</th>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">  TIN</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:center">'.$infoBox2->{'Architech TIN'}.'</th>        
        </tr>
        <tr>
            <th style="width:50%;border-top:0.5px solid black;border-left:0.5px solid black"><b>  BOX 3</b></th>           
            <th style="width:50%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"><b>  BOX 4</b></th>       
        </tr>
        <tr>
            <th style="width:50%;border-top:0.5px solid black;border-left:0.5px solid black"><b>  APPLICANT: </b></th>
            <th style="width:17%;border-top:0.5px solid black;border-left:0.5px solid black;text-align:left">  WITH MY CONSENT:</th>            
            <th style="width:33%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"><b>  LOT OWNER</b></th>       
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th>
            <th style="width:30%;text-align:center">'.$infoBox3->{'Applicant Name'}.'</th> 
            <th style="width:2%;text-align:center"></th>
            <th style="width:5%;text-align:center">Date</th>          
            <th style="width:10%;border-bottom:0.5px solid black;text-align:left">'.strtoupper(date("m/d/Y", strtotime($infoBox3->{'Applicant DateSigned'}))).'</th>
            <th style="width:1%;border-right:0.5px solid black;text-align:center"></th>
            
            <th style="width:2%;border-left:0.5px solid black"></th>
            <th style="width:30%;text-align:center">'.$infoBox4->{'LotOwner Name'}.'</th> 
            <th style="width:2%;text-align:center"></th>
            <th style="width:5%;text-align:center">Date</th>          
            <th style="width:10%;border-bottom:0.5px solid black;text-align:left">'.strtoupper(date("m/d/Y", strtotime($infoBox4->{'LotOwner DateSigned'}))).'</th>
            <th style="width:1%;border-right:0.5px solid black;text-align:center"></th>      
        </tr>
        <tr>
            <th style="width:2%;border-left:0.5px solid black"></th>
            <th style="width:30%;border-top:0.5px solid black;text-align:center">(Signature Over Printed Name)</th> 
            <th style="width:2%;text-align:center"></th>           
            <th style="width:16%;border-right:0.5px solid black;text-align:center"></th>
            
            <th style="width:2%;border-left:0.5px solid black"></th>
            <th style="width:30%;border-top:0.5px solid black;text-align:center">(Signature Over Printed Name)</th> 
            <th style="width:2%;text-align:center"></th>
            <th style="width:16%;border-right:0.5px solid black;text-align:center"></th>     
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black">  Address</th>
            <th style="width:40%;border-top:0.5px solid black;text-align:left">'.$infoBox3->{'Applicant Address'}.'</th>                      
            <th style="width:10%;border-left:0.5px solid black;border-top:0.5px solid black">  Address</th>
            <th style="width:40%;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left">'.$infoBox4->{'LotOwner Address'}.'</th> 
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center"> CTC No</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center"> Date Issued</th>                      
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center"> Place Issued</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center"> CTC No</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:center"> Date Issued</th>                      
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:center"> Place Issued</th>            
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">'.$infoBox3->{'Applicant CTCNo'}.'</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">'.strtoupper(date("m/d/Y", strtotime($infoBox3->{'Applicant CTCDate'}))).'</th>                      
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">'.$infoBox3->{'Applicant CTCPlace'}.'</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">'.$infoBox4->{'LotOwner CTCNo'}.'</th>
            <th style="width:15%;border-left:0.5px solid black;border-top:0.5px solid black;text-align:left">'.strtoupper(date("m/d/Y", strtotime($infoBox4->{'LotOwner CTCDate'}))).'</th>                      
            <th style="width:20%;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black;text-align:left"> '.$infoBox4->{'LotOwner CTCPlace'}.'</th>            
        </tr>
        <tr>
            <th style="width:100%;border-top:0.5px solid black;border-left:0.5px solid black;border-right:0.5px solid black"><b>  BOX 5</b></th>           
        </tr>
        <tr>
            <th style="width:45%;border-left:0.5px solid black;border-top:0.5px solid black">  REPUBLIC OF THE PHILIPPINES</th>  
            <th style="width:55%;border-top:0.5px solid black;border-right:0.5px solid black">) s.s</th>         
        </tr>
        <tr>
            <th style="width:20%;border-left:0.5px solid black">  CITY/MUNICIPALITY OF</th>
            <th style="width:2%"></th>
            <th style="width:22%;border-bottom:0.5px solid black;text-align:center">'.$infoBox5->{'City/Municipality'}.'</th>  
            <th style="width:1%"></th>
            <th style="width:55%;border-right:0.5px solid black">)</th>         
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black"></th>
            <th style="width:30%;text-align:left">BEFORE ME, at the City/Municipality of</th>
            <th style="width:20%;border-bottom:0.5px solid black;text-align:center">'.$infoBox5->{'City/Municipality'}.'</th>
            <th style="width:5%;text-align:left">  ,on</th>
            <th style="width:18%;border-bottom:0.5px solid black;text-align:center">'.strtoupper(date("m/d/Y", strtotime($infoBox5->{'Applicant DateSigned'}))).'</th>  
            <th style="width:1%"></th>
            <th style="width:15%;text-align:left">personally appeared</th>
            <th style="width:1%;border-right:0.5px solid black"></th>       
        </tr>
        <tr>
            <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black">  the following:</th>  
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black"></th>
            <th style="width:32%;text-align:center">'.$infoBox5->{'Applicant Name'}.'</th>
            <th style="width:2%"></th>
            <th style="width:15%;text-align:center">'.$infoBox5->{'Applicant CTCNo'}.'</th>
            <th style="width:2%"></th>
            <th style="width:15%;text-align:center">'.strtoupper(date("m/d/Y", strtotime($infoBox5->{'Applicant DateSigned'}))).'</th>  
            <th style="width:2%"></th>
            <th style="width:15%;text-align:center">'.$infoBox5->{'Applicant CTCPlace'}.'</th>  
            <th style="width:2%;border-right:0.5px solid black"></th>        
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black"></th>
            <th style="width:32%;border-top:0.5px solid black;text-align:center">APPLICANT</th>
            <th style="width:2%"></th>
            <th style="width:15%;border-top:0.5px solid black;text-align:center">CTC No.</th>
            <th style="width:2%"></th>
            <th style="width:15%;border-top:0.5px solid black;text-align:center">Date Issued</th>  
            <th style="width:2%"></th>
            <th style="width:15%;border-top:0.5px solid black;text-align:center">Place Issued</th>  
            <th style="width:2%;border-right:0.5px solid black"></th>        
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black"></th>
            <th style="width:32%;text-align:center">'.$infoBox5->{'Architech Name'}.'</th>
            <th style="width:2%"></th>
            <th style="width:15%;text-align:center">'.$infoBox5->{'Architech CTCNo'}.'</th>
            <th style="width:2%"></th>
            <th style="width:15%;text-align:center">'.strtoupper(date("m/d/Y", strtotime($infoBox5->{'Architech CTCDate'}))).'</th>  
            <th style="width:2%"></th>
            <th style="width:15%;text-align:center">'.$infoBox5->{'Architech CTCPlace'}.'</th>  
            <th style="width:2%;border-right:0.5px solid black"></th>        
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black"></th>
            <th style="width:32%;border-top:0.5px solid black;text-align:center">LICENSED ARCHITECT OR CIVIL ENGINEER</th>
            <th style="width:2%"></th>
            <th style="width:15%;border-top:0.5px solid black;text-align:center">CTC No.</th>
            <th style="width:2%"></th>
            <th style="width:15%;border-top:0.5px solid black;text-align:center">Date Issued</th>  
            <th style="width:2%"></th>
            <th style="width:15%;border-top:0.5px solid black;text-align:center">Place Issued</th>  
            <th style="width:2%;border-right:0.5px solid black"></th>        
        </tr>
        <tr>
            <th style="width:15%;border-left:0.5px solid black"></th>
            <th style="width:32%;text-align:center;font-size:6px">(Full-Time Inspector and Supervisor of Construction Works)</th>
            <th style="width:53%;border-right:0.5px solid black"></th>        
        </tr>
        <tr>
            <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black">  whose signatures appear hereinabove, known to me to be the same persons who executed this standard prescribed form and acknowledged to me that the same is their</th>                
        </tr>
        <tr>
            <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black">  free and voluntary act and deed.</th>                
        </tr>
        <tr>
            <th style="width:10%;border-left:0.5px solid black"></th>
            <th style="width:90%;border-right:0.5px solid black">  WITNESS MY HAND AND SEAL on the date and place above written.</th>                
        </tr>
        <tr>
            <th style="width:8%;border-left:0.5px solid black;text-align:left">  Doc. No.</th>
            <th style="width:12%;text-align:center">'.$infoBox5->{'Doc No'}.'</th>
            <th style="width:80%;border-right:0.5px solid black"></th>        
        </tr>
        <tr>
            <th style="width:8%;border-left:0.5px solid black;text-align:left">  Page No.</th>
            <th style="width:12%;border-top:0.5px solid black;text-align:center">'.$infoBox5->{'Page No'}.'</th>
            <th style="width:80%;border-right:0.5px solid black"></th>        
        </tr>
        <tr>
            <th style="width:8%;border-left:0.5px solid black;text-align:left">  Book No.</th>
            <th style="width:12%;border-top:0.5px solid black;text-align:center">'.$infoBox5->{'Book No'}.'</th>
            <th style="width:40%"></th>
            <th style="width:30%;text-align:center">'.$infoBox5->{'Notary Name'}.'</th>
            <th style="width:10%;border-right:0.5px solid black"></th>       
        </tr>
        <tr>
            <th style="width:8%;border-left:0.5px solid black;text-align:left">  Series Of</th>
            <th style="width:12%;border-top:0.5px solid black;text-align:center">'.$infoBox5->{'Series No'}.'</th>
            <th style="width:40%"></th>
            <th style="width:25%;border-top:0.5px solid black;text-align:right">NOTARY PUBLIC (Until December</th>
            <th style="width:5%;border-top:0.5px solid black;text-align:center">'.$infoBox5->{'Notary Year'}.'</th>
            <th style="width:10%;border-right:0.5px solid black">)</th>       
        </tr>
        <tr>
            <th style="width:100%;border-left:0.5px solid black;border-bottom:0.5px solid black;border-right:0.5px solid black;text-align:left"></th>
        </tr>               
    </table>';
    $second_page = 
    '<table width ="100%">             
    <tr>
        <th style="width:100%;text-align:left">  <b>BOX 6 (TO BE ACCOMPLISHED BY THE PROCESSING AND EVALUATION DIVISION)</b></th>
    </tr>  
    <thead>
        <tr style="height:25px">                                                
            <th style="width:25%" align="center" border="1">
            <b>ASSESSED FEES</b>
            </th>
            <th style="width:20%" align="center" border="1">
            <b>ASSESSED BY</b>
            </th>
            <th style="width:15%" align="center" border="1">
            <b>AMOUNT DUE</b>
            </th>
            <th style="width:15%" align="center" border="1">
            <b>DATE PAID</b>
            </th> 
            <th style="width:15%" align="center" border="1">
            <b>O.R. NUMBER</b>
            </th>
            <th style="width:10%" align="center" border="1">
            <b>NSO</b>
            </th>                                        
        </tr>
    </thead>
    <tbody>';
    foreach($dataDocuments as $row){
       $second_page .='
        <tr> 
            <td style="width:25%" align="left" border="1">'.$row->{'Assessed Fees'}.'</td>
            <td style="width:20%" align="left" border="1">'.$row->{'Assessed By'}.'</td>
            <td style="width:15%" align="right" border="1">'.$row->{'Amount Due'}.'</td>
            <td style="width:15%" align="left" border="1">'.$row->{'Date Paid'}.'</td>
            <td style="width:15%" align="left" border="1">'.$row->{'OR Number'}.'</td>
            <td style="width:10%" align="left" border="1">'.$row->{'NSO'}.'</td>
        </tr>';
    }
    for ($x = 0; $x < 7; $x++) {
        $second_page .='        
        <tr>         
            <td style="width:25%" align="left" border="1"></td>
            <td style="width:20%" align="left" border="1"></td>
            <td style="width:15%" align="right" border="1"></td>
            <td style="width:15%" align="left" border="1"></td>
            <td style="width:15%" align="left" border="1"></td>
            <td style="width:10%" align="left" border="1"></td>                                
        </tr>';
    }        
    $second_page .='
    </tbody>   
    <tr>
        <th style="width:25%" align="left" border="1"></th>
        <th style="width:20%" align="center" border="1"> TOTAL</th>
        <th style="width:15%" align="right" border="1"> 999,999,999.99</th>
        <th style="width:15%" align="left" border="1"></th>
        <th style="width:15%" align="left" border="1"></th>
        <th style="width:10%" align="left" border="1"></th>       
    </tr>
    <tr>
        <th style="width:100%;text-align:left">  <b>BOX 7 (TO BE ACCOMPLISHED BY THE BUILDING OFFICIAL)</b></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;;border-top:0.5px solid black;border-right:0.5px solid black;font-size:12px;text-align:center"><b>BUILDING PERMIT</b></th>
    </tr>        
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:21%;text-align:left"> BUILDING PERMIT NO.</th>
        <th style="width:2%"></th>
        <th style="width:50%"></th>
        <th style="width:2%"></th>
        <th style="width:21%;text-align:left"> OFFICIAL RECEIPT NO.</th>
        <th style="width:2%;border-right:0.5px solid black"></th>      
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:21%;text-align:center" border="1">'.$infoBox7->{'Issuance No'}.'</th>
        <th style="width:2%"></th>
        <th style="width:50%"></th>
        <th style="width:2%"></th>
        <th style="width:21%;text-align:center" border="1">'.$infoBox7->{'OR No'}.'</th>
        <th style="width:2%;border-right:0.5px solid black"></th>      
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:21%;text-align:left"> DATE ISSUED</th>
        <th style="width:2%"></th>
        <th style="width:50%"></th>
        <th style="width:2%"></th>
        <th style="width:21%;text-align:left"> DATE PAID</th>
        <th style="width:2%;border-right:0.5px solid black"></th>      
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:21%;text-align:center" border="1">'.strtoupper(date("m/d/Y", strtotime($infoBox7->{'Issuance Date'}))).'</th>
        <th style="width:2%"></th>
        <th style="width:50%"></th>
        <th style="width:2%"></th>
        <th style="width:21%;text-align:center" border="1">'.strtoupper(date("m/d/Y", strtotime($infoBox7->{'OR Date'}))).'</th>
        <th style="width:2%;border-right:0.5px solid black"></th>      
    </tr> 
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:21%;text-align:center"> MM DD YY</th>
        <th style="width:2%"></th>
        <th style="width:50%"></th>
        <th style="width:2%"></th>
        <th style="width:21%;text-align:center"> MM DD YY</th>
        <th style="width:2%;border-right:0.5px solid black"></th>      
    </tr>
    <tr>
        <th style="width:10%;border-left:0.5px solid black"></th>
        <th style="width:15%;text-align:left">Permit is issued to</th>
        <th style="width:2%"></th>
        <th style="width:26%;border-bottom:0.5px solid black;text-align:center">'.$infoBox7->{'Issuance Name'}.'</th>
        <th style="width:2%"></th>
        <th style="width:13%;text-align:left"> for the proposed</th>
        <th style="width:2%"></th>
        <th style="width:28%;border-bottom:0.5px solid black;text-align:center">'.$infoBox7->{'Project Name'}.'</th>
        <th style="width:2%;border-right:0.5px solid black"></th> 
    </tr>
    <tr>
        <th style="width:10%;border-left:0.5px solid black"></th>
        <th style="width:15%;text-align:left"></th>
        <th style="width:2%"></th>
        <th style="width:26%;font-size:6px;text-align:center">(Owner/Applicant)</th>
        <th style="width:2%"></th>
        <th style="width:13%;text-align:left"></th>
        <th style="width:2%"></th>
        <th style="width:28%;font-size:6px;text-align:center">(Type of Project)</th>
        <th style="width:2%;border-right:0.5px solid black"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:5%"> Under</th>
        <th style="width:18%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Occupancy Classified'}.'</th>
        <th style="width:1%"></th>
        <th style="width:10%;text-align:left">, of group</th>
        <th style="width:1%"></th>
        <th style="width:10%;border-bottom:0.5px solid black;text-align:center"> A</th>
        <th style="width:1%"></th>
        <th style="width:17%;text-align:left">, located at Lot No.</th>
        <th style="width:1%"></th>
        <th style="width:10%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Location LotNo'}.'</th>
        <th style="width:1%"></th>
        <th style="width:10%;text-align:left"> Block</th>
        <th style="width:1%"></th>
        <th style="width:10%;border-bottom:0.5px solid black;text-align:center"> '.$infoMain->{'Location BlockNo'}.'</th>
        <th style="width:2%;border-right:0.5px solid black"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:5%"></th>
        <th style="width:18%;font-size:6px;text-align:center">(Use or Character of Occupancy)</th>
        <th style="width:1%"></th>
        <th style="width:10%"></th>
        <th style="width:1%"></th>
        <th style="width:10%"></th>
        <th style="width:1%"></th>
        <th style="width:17%"></th>
        <th style="width:1%"></th>
        <th style="width:10%"></th>
        <th style="width:1%"></th>
        <th style="width:10%"></th>
        <th style="width:1%"></th>
        <th style="width:10%"></th>
        <th style="width:2%;border-right:0.5px solid black"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:15%"> OCT/TCT No.</th>
        <th style="width:15%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'bp_TCTNo'}.'</th>
        <th style="width:2%">  ,</th>
        <th style="width:20%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Location Street'}.'</th>
        <th style="width:10%;text-align:left"> Street,</th>    
        <th style="width:2%"></th>
        <th style="width:10%;text-align:left">Barangay</th>
        <th style="width:2%"> ,</th>
        <th style="width:20%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Location Barangay'}.'</th>
        <th style="width:2%;border-right:0.5px solid black"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:20%">,City/Municipality of</th>
        <th style="width:30%;border-bottom:0.5px solid black;text-align:center">'.$infoMain->{'Location City'}.'</th>
        <th style="width:2%"></th>           
        <th style="width:20%;text-align:left"> subject to the following:</th>          
        <th style="width:26%;border-right:0.5px solid black"></th> 
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black"></th>
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">1.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">That under Article 1723 of the Civil Code of the Philippines, the engineer or architect who drew up the plans and specifications for a building/structure is liable for damages if within fifteen (15) years from the completion of the building/structure, the same should collapse due to defect in the plans or specifications or defects in the ground. The engineer or architect who supervises the construction shall be solidarily liable with the contractor should the edifice collapse due to defect in the construction or the use of inferior materials.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">2.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">This permit shall be accompanied by the various applicable ancillary and accessory permits, plans and specifications signed and sealed by the corresponding design professionals who shall be responsible for the comprehensive and correctness of the plans in compliance to the Code and its IRR and to all applicable referral codes and professional regulatory laws.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">3.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">That the proposed construction/erection/addition/alteration/renovation/conversion/repair/moving/demolition, etc. shall be in conformity with the provisions of the National Building Code, and its IRR.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:left"></th>
        <th style="width:4%;font-size:7px;text-align:left">a.</th>                   
        <th style="width:86%;font-size:7px;text-align:left"><span style="text-align:justify">That prior to commencement of the proposed projects and construction an actual relocation survey shall be conducted by a duly licensed Geodetic Engineer.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:left"></th>
        <th style="width:4%;font-size:7px;text-align:left">b.</th>                   
        <th style="width:86%;font-size:7px;text-align:left"><span style="text-align:justify">That before commencing the excavation the person making or causing the excavation to be made shall notify in writing the owner of adjoining property not less than ten (10) days before such excavation is to be made and show how the adjoining property should be protected.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:left"></th>
        <th style="width:4%;font-size:7px;text-align:left">c.</th>                   
        <th style="width:86%;font-size:7px;text-align:left"><span style="text-align:justify">That no person shall use or occupy a street, alley or public sidewalk for the performance of work covered by a building permit.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:left"></th>
        <th style="width:4%;font-size:7px;text-align:left">d.</th>                   
        <th style="width:86%;font-size:7px;text-align:left"><span style="text-align:justify">That no person shall perform any work on any building or structure adjacent to a public way in general use for pedestrian travel, unless the pedestrians are protected.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:left"></th>
        <th style="width:4%;font-size:7px;text-align:left">e.</th>                   
        <th style="width:86%;font-size:7px;text-align:left"><span style="text-align:justify">That the supervising Architect/Civil Engineer shall keep at the jobsite at all times a logbook of daily construction activities wherein the actual daily progress of construction including tests conducted, weather condition and other pertinent data are to be recorded, same shall be made available for scrutiny and comments by the OBO representative during the conduct of his/her inspection pursuant to Section 207 of the National Building Code.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:left"></th>
        <th style="width:4%;font-size:7px;text-align:left">f.</th>                   
        <th style="width:86%;font-size:7px;text-align:left"><span style="text-align:justify">That upon completion of the construction, the said licensed supervising Architect/Civil Engineer shall submit to the Building Official duly signed and sealed logbook, as-built plans and other documents and shall also prepare and submit a Certificate of Completion of the project stating that the construction of the building/structure conform to the provision of the Code, its IRR as well as the plans and specifications.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:left"></th>
        <th style="width:4%;font-size:7px;text-align:left">g.</th>                   
        <th style="width:86%;font-size:7px;text-align:left"><span style="text-align:justify">All such changes, modifications and alterations shall likewise be submitted to the Building Official and the subsequent amendatory permit therefor issued before any work on said changes, modifications and alterations shall be started.  The as-built plans and specifications maybe just an orderly and comprehensive compilation of all documents which include the originally submitted plans and specifications of all amendments thereto as actually built or they may be an entirely new set of plans and specifications accurately describing and/or reflecting therein the building as actually built.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">4.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">That no building/structure shall be used until the Building Official has issued a Certificate of Occupancy therefor as provided in the Code. However, a partial Certificate of Occupancy may be issued for the Use/Occupancy of a portion or portions of a building/structure prior to the completion of the entire building/structure.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">5.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">That this permit shall not serve as an exemption from securing written clearances from various government authorities exercising regulatory function affecting buildings/structures.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">6.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">When the construction is undertaken by contract, the work shall be done by a duly licensed and registered contractor pursuant to the provisions of the Contractor’s License Law (RA 4566).</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">7.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">The Owner/Permittee shall submit a duly accomplished prescribed “Notice of Construction” to the Office of the Building Official prior to any construction activity.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:2%;border-left:0.5px solid black"></th>
        <th style="width:6%;font-size:7px;text-align:center">8.</th>                   
        <th style="width:90%;font-size:7px;text-align:left"><span style="text-align:justify">The Owner/Permittee shall put a Building Permit sign which complies with the prescribed dimensions and information, which shall remain posted on the construction site for the duration of the construction.</span></th> 
        <th style="width:2%;border-right:0.5px solid black;"></th> 
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black"></th>
    </tr>
    <tr>
        <th style="width:5%;border-left:0.5px solid black"></th>
        <th style="width:95%;font-size:10px;border-right:0.5px solid black;text-align:left"><b>PERMIT ISSUED BY:</b></th>                   
    </tr>        
    <tr>
        <th style="width:30%;border-left:0.5px solid black"></th>
        <th style="width:40%;text-align:center">'.$infoBox7->{'Department Head Name'}.'</th>                   
        <th style="width:30%;border-right:0.5px solid black"></th>
    </tr>
    <tr>
        <th style="width:30%;border-left:0.5px solid black"></th>
        <th style="width:40%;border-top:0.5px solid black;text-align:center"><b>BUILDING OFFICIAL</b></th>                   
        <th style="width:30%;border-right:0.5px solid black"></th>
    </tr>
    <tr>
        <th style="width:30%;border-left:0.5px solid black"></th>
        <th style="width:40%;text-align:center">(Signature Over Printed Name)</th>                   
        <th style="width:30%;border-right:0.5px solid black"></th>
    </tr>
    <tr>
        <th style="width:30%;border-left:0.5px solid black"></th>
        <th style="width:16%;text-align:right">Date</th>
        <th style="width:1%"></th>
        <th style="width:23%;text-align:left">'.strtoupper(date("m/d/Y", strtotime($infoBox7->{'Issuance Date'}))).'</th>                   
        <th style="width:30%;border-right:0.5px solid black"></th>
    </tr>
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-right:0.5px solid black;text-align:center">NOTE: THIS PERMIT MAY BE CANCELLED PURSUANT TO SECTIONS 305 AND 306 OF THE “NATIONAL BUILDING CODE”</th>
    </tr>       
    <tr>
        <th style="width:100%;border-left:0.5px solid black;border-bottom:0.5px solid black;border-right:0.5px solid black;text-align:center"></th>
    </tr>
</table>
</body>';
            PDF::SetTitle('Building Permit Application Form');
            PDF::SetFont('times', '', 7.8);
            PDF::AddPage('P','Legal');  
            PDF::SetLineStyle( array( 'width' => 0.7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
            PDF::Line(8,8,PDF::getPageWidth()-8,8);  
            PDF::Line(PDF::getPageWidth()-8,7.4,PDF::getPageWidth()-8,PDF::getPageHeight()-8);
            PDF::Line(8,PDF::getPageHeight()-8,PDF::getPageWidth()-8,PDF::getPageHeight()-8);
            PDF::Line(8,7.4,8,PDF::getPageHeight()-8);
            PDF::writeHTML($first_page, true, true, true, true, '');
            PDF::AddPage();            
            PDF::SetLineStyle( array( 'width' => 0.7, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
            PDF::Line(8,8,PDF::getPageWidth()-8,8);  
            PDF::Line(PDF::getPageWidth()-8,7.4,PDF::getPageWidth()-8,PDF::getPageHeight()-8);
            PDF::Line(8,PDF::getPageHeight()-8,PDF::getPageWidth()-8,PDF::getPageHeight()-8);
            PDF::Line(8,7.4,8,PDF::getPageHeight()-8);
            PDF::writeHTML($second_page, true, true, true, true, '');
            PDF::Output(public_path().'/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printQPass(Request $request) 
   {
     $dataMain = DB::select('call '.$this->lgu_db.'.balodoy_get_names()');  
    
    //     $logo = config('variable.logo'); 
    try { 
    PDF::SetTitle('Access Pass');
    PDF::SetHeaderMargin(10);
    PDF::SetTopMargin(10);
    PDF::setFooterMargin(10);
    PDF::SetFont('times', '', 10);
 
    cons: $result = array_splice($dataMain, 0, 4);  
      
    PDF::AddPage('P', 'Legal'); 
   
    $Template = '
            <table width ="100%">
                <tr>
                    <td width = "50%" height ="50%" style="border:0.5px solid black;" >
                        <table cellpadding ="6">
                            <tr>
                               <th style="width:50%;">
                                    CN: 10000
                                    <br>
                                    <img src="C:\Users\MDL\Desktop\Signatures\back.jpg"  height="30" width="120">
                                </th>
                               <th > 
                               </th>         
                            </tr>
                            <tr >
                                <th colspan ="2"> 
                                    <table width ="100%">
                                       <tr>
                                            <td width = "100%">
                                               <table>
                                                   <tr style="height:25px">
                                                       <th style="width:15%"></th>
                                                       <th style="width:70%;font-size:13px" align="center"><b>'.$result[0]->Name.'</b></th>    
                                                       <th style="width:15%"></th>
                                                   </tr>
                                                   <tr style="height:25px">
                                                       <th style="width:15%"></th>
                                                       <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name of Barangay</th>    
                                                       <th style="width:15%"></th>
                                                    </tr> 
                                                    <tr style="height:25px">
                                                       <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black" align="center"><b>HOUSEHOLD</b></th>    
                                                    </tr>
                                                    <tr style="height:25px">
                                                       <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-bottom:0.5px solid black;border-right:0.5px solid black" align="center"><b>ACCESS PASS</b></th>    
                                                    </tr>
                                                    <tr style="height:25px">
                                                       <th style="width:15%"></th>
                                                       <th style="width:70%;font-size:11px" align="center"><b>'.$result[0]->Name.'</b></th>    
                                                       <th style="width:15%"></th>
                                                    </tr>
                                                    <tr style="height:25px">
                                                       <th style="width:15%"></th>
                                                       <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name and Signature Head of the Family</th>    
                                                       <th style="width:15%"></th>
                                                    </tr>            
                                                    <tr style="height:25px">
                                                       <th style="width:15%"></th>
                                                       <th style="width:70%;font-size:11px" align="center"><b>'.$result[0]->Name.'</b></th>    
                                                       <th style="width:15%"></th>
                                                    </tr>
                                                    <tr style="height:25px">
                                                       <th style="width:15%"></th>
                                                       <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Punong Barangay</th>    
                                                       <th style="width:15%"></th>
                                                    </tr>
                                                    <tr style="height:25px">
                                                       <th style="width:10%"></th>
                                                       <th style="width:80%;font-size:13px" align="center"><b>'.$result[0]->Name.'</b></th>    
                                                       <th style="width:10%"></th>
                                                    </tr>
                                                    <tr style="height:25px">
                                                       <th style="width:15%"></th>
                                                       <th style="width:70%;border-top:0.5px solid black;font-size:10px" align="center">CITY MAYOR</th>    
                                                       <th style="width:15%"></th>
                                                    </tr>   
                                               </table>
                                           </td>
                                        </tr> 
                                    </table>
                                </th>
                            </tr>
                        </table>
                    </td>
                    <td width = "50%" height ="50%" style="border:0.5px solid black;" >
                    <table cellpadding ="6">
                        <tr>
                           <th style="width:50%;">
                                CN: 10000
                                <br>
                                <img src="C:\Users\MDL\Desktop\Signatures\back.jpg"  height="30" width="120">
                            </th>
                           <th > 
                           </th>         
                        </tr>
                        <tr >
                            <th colspan ="2"> 
                                <table width ="100%">
                                   <tr>
                                        <td width = "100%">
                                           <table>
                                               <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;font-size:13px" align="center"><b>'.(array_key_exists(1,$result) == true ? $result[1]->Name : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name of Barangay</th>    
                                                   <th style="width:15%"></th>
                                                </tr> 
                                                <tr style="height:25px">
                                                   <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black" align="center"><b>HOUSEHOLD</b></th>    
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-bottom:0.5px solid black;border-right:0.5px solid black" align="center"><b>ACCESS PASS</b></th>    
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;font-size:11px" align="center"><b>'.(array_key_exists(1,$result) == true ? $result[1]->Name : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name and Signature Head of the Family</th>    
                                                   <th style="width:15%"></th>
                                                </tr>            
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;font-size:11px" align="center"><b>'.(array_key_exists(1,$result) == true ? $result[1]->Name : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Punong Barangay</th>    
                                                   <th style="width:15%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:10%"></th>
                                                   <th style="width:80%;font-size:13px" align="center"><b>'.(array_key_exists(1,$result) == true ? $result[1]->Name : '').'</b></th>    
                                                   <th style="width:10%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:10px" align="center">CITY MAYOR</th>    
                                                   <th style="width:15%"></th>
                                                </tr>   
                                           </table>
                                       </td>
                                    </tr> 
                                </table>
                            </th>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br/>
        ';

    $Template .= '
        <table width ="100%">
            <tr>
                <td width = "50%" height ="50%" style="border:0.5px solid black;" >
                    <table cellpadding ="6">
                        <tr>
                           <th style="width:50%;">
                                CN: 10000
                                <br>
                                <img src="C:\Users\MDL\Desktop\Signatures\back.jpg"  height="30" width="120">
                            </th>
                           <th > 
                           </th>         
                        </tr>
                        <tr >
                            <th colspan ="2"> 
                                <table width ="100%">
                                   <tr>
                                        <td width = "100%">
                                           <table>
                                               <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;font-size:13px" align="center"><b>'.(array_key_exists(2,$result) == true ? $result[2]->Name : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name of Barangay</th>    
                                                   <th style="width:15%"></th>
                                                </tr> 
                                                <tr style="height:25px">
                                                   <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black" align="center"><b>HOUSEHOLD</b></th>    
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-bottom:0.5px solid black;border-right:0.5px solid black" align="center"><b>ACCESS PASS</b></th>    
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;font-size:11px" align="center"><b>'.(array_key_exists(2,$result) == true ? $result[2]->Name : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name and Signature Head of the Family</th>    
                                                   <th style="width:15%"></th>
                                                </tr>            
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;font-size:11px" align="center"><b>'.(array_key_exists(2,$result) == true ? $result[2]->Name : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Punong Barangay</th>    
                                                   <th style="width:15%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:10%"></th>
                                                   <th style="width:80%;font-size:13px" align="center"><b>'.(array_key_exists(2,$result) == true ? $result[2]->Name : '').'</b></th>    
                                                   <th style="width:10%"></th>
                                                </tr>
                                                <tr style="height:25px">
                                                   <th style="width:15%"></th>
                                                   <th style="width:70%;border-top:0.5px solid black;font-size:10px" align="center">CITY MAYOR</th>    
                                                   <th style="width:15%"></th>
                                                </tr>   
                                           </table>
                                       </td>
                                    </tr> 
                                </table>
                            </th>
                        </tr>
                    </table>
                </td>
                <td width = "50%" height ="50%" style="border:0.5px solid black;" >
                <table cellpadding ="6">
                    <tr>
                       <th style="width:50%;">
                            CN: 10000
                            <br>
                            <img src="C:\Users\MDL\Desktop\Signatures\back.jpg"  height="30" width="120">
                        </th>
                       <th > 
                       </th>         
                    </tr>
                    <tr >
                        <th colspan ="2"> 
                            <table width ="100%">
                               <tr>
                                    <td width = "100%">
                                       <table>
                                           <tr style="height:25px">
                                               <th style="width:15%"></th>
                                               <th style="width:70%;font-size:13px" align="center"><b>'.(array_key_exists(3,$result) == true ? $result[3]->Name : '').'</b></th>    
                                               <th style="width:15%"></th>
                                           </tr>
                                           <tr style="height:25px">
                                               <th style="width:15%"></th>
                                               <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name of Barangay</th>    
                                               <th style="width:15%"></th>
                                            </tr> 
                                            <tr style="height:25px">
                                               <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-top:0.5px solid black;border-right:0.5px solid black" align="center"><b>HOUSEHOLD</b></th>    
                                            </tr>
                                            <tr style="height:25px">
                                               <th style="width:90%;font-size:30px;color:white;background-color:red;border-left:0.5px solid black;border-bottom:0.5px solid black;border-right:0.5px solid black" align="center"><b>ACCESS PASS</b></th>    
                                            </tr>
                                            <tr style="height:25px">
                                               <th style="width:15%"></th>
                                               <th style="width:70%;font-size:11px" align="center"><b>'.(array_key_exists(3,$result) == true ? $result[3]->Name : '').'</b></th>    
                                               <th style="width:15%"></th>
                                            </tr>
                                            <tr style="height:25px">
                                               <th style="width:15%"></th>
                                               <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Name and Signature Head of the Family</th>    
                                               <th style="width:15%"></th>
                                            </tr>            
                                            <tr style="height:25px">
                                               <th style="width:15%"></th>
                                               <th style="width:70%;font-size:11px" align="center"><b>'.(array_key_exists(3,$result) == true ? $result[3]->Name : '').'</b></th>    
                                               <th style="width:15%"></th>
                                            </tr>
                                            <tr style="height:25px">
                                               <th style="width:15%"></th>
                                               <th style="width:70%;border-top:0.5px solid black;font-size:11px" align="center">Punong Barangay</th>    
                                               <th style="width:15%"></th>
                                            </tr>
                                            <tr style="height:25px">
                                               <th style="width:10%"></th>
                                               <th style="width:80%;font-size:13px" align="center"><b>'.(array_key_exists(3,$result) == true ? $result[3]->Name : '').'</b></th>    
                                               <th style="width:10%"></th>
                                            </tr>
                                            <tr style="height:25px">
                                               <th style="width:15%"></th>
                                               <th style="width:70%;border-top:0.5px solid black;font-size:10px" align="center">CITY MAYOR</th>    
                                               <th style="width:15%"></th>
                                            </tr>   
                                       </table>
                                   </td>
                                </tr> 
                            </table>
                        </th>
                    </tr>
                </table>
            </td>
        </tr>
    </table>';

    PDF::writeHTML($Template, true, 0, true, 0);   
    if(count($dataMain) > 0){ goto cons; }   

            PDF::Output(public_path().'/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    } 
}
