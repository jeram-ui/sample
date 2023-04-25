<?php

namespace App\Http\Controllers\Api\Qr;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\db;
use App\Laravue\JsonResponse;
use PDF;
use Illuminate\Support\Arr;
class vaccineController extends Controller
{
    public function getRef($id)
    {
        $result = db::table('shop_representative')
    ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
    ->select('rep_brgy', 'rep_address', db::raw('CONCAT(barangay.`brgy_code`,"-" ,LPAD(COUNT(*) + 1,4,0)) AS client_guid'), 'barangay.brgy_name')
    ->where('rep_brgy', $id)->get();
        return response()->json(new jsonresponse($result));
    }
    public function onlineApproved(Request $request){
        $form = $request->form;
       $chk = db::table('vaccine_profiling')
       ->where('lastName',$form['lastName'])
       ->where('firstName',$form['firstName'])
       ->where('stat',0)
       ->get();
       if (count($chk)>0) {
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','data'=>$chk,'status'=>'success']));
        //    return response()->json(new jsonresponse($chk));
       }else{
        $datax = db::select("select * from vaccine_profiling_online where id =?",[$form['id']]);
        foreach ($datax as $row) {
            $x =json_decode(json_encode($row),true);
            unset($x['id']);
            unset($x['encoded']);
            $x['uid']=Auth::user()->id;
            db::table('vaccine_profiling')->insert($x);
            db::table('vaccine_profiling_online')->where('id',$form['id'])->update(['encoded'=>1]);
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','data'=>[],'status'=>'success']));
        }
    
       }
    }
    public function onlineApproved2(Request $request){
        $form = $request->form;
        $datax = db::select("select * from vaccine_profiling_online where id =?",[$form['id']]);
        foreach ($datax as $row) {
            $x =json_decode(json_encode($row),true);
            unset($x['id']);
            unset($x['encoded']);
            $x['uid']=Auth::user()->id;
            db::table('vaccine_profiling')->insert($x);
            db::table('vaccine_profiling_online')->where('id',$form['id'])->update(['encoded'=>1]);
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','data'=>[],'status'=>'success']));
        }
    }
    public function getPriority(){
        $result = db::table('group_main')->get();
        return response()->json(new jsonresponse($result));
    }
    public function getSubPriority($id){
        $result = db::table('group_sub')
        ->where('main_id',$id)
        ->get();
        return response()->json(new jsonresponse($result));
    }
    public function getProfessional(){
       $result = db::table('professional')->get();
       return response()->json(new jsonresponse($result));
    }
    public function  getEmployerProvince(){
        $result = db::table('refcitymun')
        ->select(db::raw('CONCAT(citymunCode * 1 ," - ",REPLACE(citymunDesc," ","_")) as province'))
        ->get();
        return response()->json(new jsonresponse($result));
    }
    public function  store(Request $request){
            $main = $request->main;
            $pk = $main['id'];
            DB::beginTransaction();
            if ($pk === 0) {
                $main['uid'] =Auth::user()->id;
                db::table('vaccine_profiling')->insert($main);
                db::table('household_members')->where('id',$main['member_id'])->update(['surveyed'=>1]);
            } else {
                $main['update_uid'] =Auth::user()->id;
                db::table('vaccine_profiling')->where('id', $pk)->update($main);
            }
            DB::commit();
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function  updatedProfile(Request $request){
        $main = $request->main;
        $pk = $main['profile_id'];
        DB::beginTransaction();
        $chk = db::table('vaccine_profiling_details')
        ->where('profile_id',$pk)->count();

        if ($chk == 0) {
            $main['uid'] =Auth::user()->id;
            db::table('vaccine_profiling_details')->insert($main);
        } else {
            $main['uid'] =Auth::user()->id;
            db::table('vaccine_profiling_details')->where('profile_id', $pk)->update($main);
        }
        DB::commit();
    return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
}
    
    public function  storeEnject(Request $request){
        $main = $request->main;
        $pk = $main['profile_id'];
        DB::beginTransaction();

        $chk = db::table('vaccine_entry')
        ->where('profile_id',$pk)->count();

        if ($chk == 0) {
            $idx = db::table('vaccine_profiling')->where('id',  $pk)->first();
            $main['uid'] =Auth::user()->id;
            db::table('vaccine_entry')->insert($main);
            db::table('household_members')->where('id',$idx->member_id)->update(['vaccinated'=>1]);
        } else {
            $main['update_uid'] =Auth::user()->id;
            db::table('vaccine_entry')->where('profile_id', $pk)->update($main);
        }
        DB::commit();
    return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
}
    
    public function list(Request $request){
        $filter = $request->filter;
        $list = db::table('vaccine_profiling')
        ->leftjoin('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
       ->leftjoin('refprovince','refprovince.provCode','=','vaccine_profiling.provinceId')
       ->leftjoin('refcitymun','refcitymun.citymunCode','=','vaccine_profiling.cityId')
       ->leftjoin('refbrgy','refbrgy.id','=','vaccine_profiling.barangayId')
       ->leftjoin('users','users.id','=','vaccine_profiling.uid')
       ->leftjoin('vaccine_profiling_details','vaccine_profiling_details.profile_id','=','vaccine_profiling.id')
       ->select('vaccine_entry.*','vaccine_profiling_details.*','brgy_name','city_name','prov_name','users.name','vaccine_profiling.*','vaccine_entry.id as vacid',db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes") as first_dose'),db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes") as second_dose'),db::raw('if(ifnull(Deferral,"") ="","02_No","01_Yes") as deferral_status'))
       ->where(db::raw('ifnull(HH_No,"")'),'like','%'. $filter['HH_No'].'%')
       ->where(db::raw('ifnull(lastName,"")'),'like',''. $filter['lastName'].'%')
       ->where(db::raw('ifnull(firstName,"")'),'like',''. $filter['firstName'].'%')
       ->where(db::raw('ifnull(middleName,"")'),'like',''. $filter['middleName'].'%')
       ->where(db::raw('ifnull(suffix,"")'),'like','%'. $filter['suffix'].'%')
       ->where(db::raw('ifnull(contact_number,"")'),'like','%'. $filter['contact_number'].'%')
       ->where(db::raw('ifnull(civilStatus,"")'),'like','%'. $filter['civilStatus'].'%')
       ->where(db::raw('ifnull(direct_contact_covid,"")'),'like','%'. $filter['direct_contact_covid'].'%')
       ->where(db::raw('ifnull(region,"")'),'like','%'. $filter['region'].'%')
       ->where(db::raw('ifnull(prov_name,"")'),'like','%'. $filter['prov_name'].'%')
       ->where(db::raw('ifnull(city_name,"")'),'like','%'. $filter['city_name'].'%')
       ->where(db::raw('ifnull(brgy_name,"")'),'like','%'. $filter['brgy_name'].'%')
       ->where(db::raw('ifnull(purok,"")'),'like','%'. $filter['purok'].'%')
       ->where(db::raw('ifnull(profession,"")'),'like','%'. $filter['profession'].'%')
       ->where(db::raw('ifnull(category,"")'),'like','%'. $filter['category'].'%')
       ->where(db::raw('ifnull(categoryID,"")'),'like','%'. $filter['categoryID'].'%')
       ->where(db::raw('ifnull(category_number,"")'),'like','%'. $filter['category_number'].'%')
       ->where(db::raw('ifnull(philhealthid,"")'),'like','%'. $filter['philhealthid'].'%')
       ->where(db::raw('ifnull(pwdid,"")'),'like','%'. $filter['pwdid'].'%')
       ->where(db::raw('ifnull(birthdate,"")'),'like','%'. $filter['birthdate'].'%')
       ->where(db::raw('ifnull(gender,"")'),'like','%'. $filter['gender'].'%')
       ->where(db::raw('ifnull(nameofemployer,"")'),'like','%'. $filter['nameofemployer'].'%')
       ->where(db::raw('ifnull(employerProvince,"")'),'like','%'. $filter['employerProvince'].'%')
       ->where(db::raw('ifnull(employerAddress,"")'),'like','%'. $filter['employerAddress'].'%')
       ->where(db::raw('ifnull(employercontact,"")'),'like','%'. $filter['employercontact'].'%')
       ->where(db::raw('ifnull(pregnancy,"")'),'like','%'. $filter['pregnancy'].'%')
       ->where(db::raw('ifnull(allergy,"")'),'like','%'. $filter['allergy'].'%')
       ->where(db::raw('ifnull(allergy_food,"")'),'like','%'. $filter['allergy_food'].'%')
       ->where(db::raw('ifnull(allergy_insect,"")'),'like','%'. $filter['allergy_insect'].'%')
       ->where(db::raw('ifnull(allergy_latex,"")'),'like','%'. $filter['allergy_latex'].'%')
       ->where(db::raw('ifnull(allergy_mold,"")'),'like','%'. $filter['allergy_mold'].'%')
       ->where(db::raw('ifnull(allergy_pet,"")'),'like','%'. $filter['allergy_pet'].'%')
       ->where(db::raw('ifnull(allergy_pollen,"")'),'like','%'. $filter['allergy_pollen'].'%')
       ->where(db::raw('ifnull(comorbidity,"")'),'like','%'. $filter['comorbidity'].'%')
       ->where(db::raw('ifnull(comorbidity_hepertension,"")'),'like','%'. $filter['comorbidity_hepertension'].'%')
       ->where(db::raw('ifnull(comorbidity_heart,"")'),'like','%'. $filter['comorbidity_heart'].'%')
       ->where(db::raw('ifnull(comorbidity_kidney,"")'),'like','%'. $filter['comorbidity_kidney'].'%')
       ->where(db::raw('ifnull(comorbidity_diabetes,"")'),'like','%'. $filter['comorbidity_diabetes'].'%')
       ->where(db::raw('ifnull(comorbidity_bronchial,"")'),'like','%'. $filter['comorbidity_bronchial'].'%')
       ->where(db::raw('ifnull(immunodeficiency,"")'),'like','%'. $filter['immunodeficiency'].'%')
       ->where(db::raw('ifnull(immunodeficiency_cancer,"")'),'like','%'. $filter['immunodeficiency_cancer'].'%')
       ->where(db::raw('ifnull(immunodeficiency_other,"")'),'like','%'. $filter['immunodeficiency_other'].'%')
       ->where(db::raw('ifnull(covid_classs,"")'),'like','%'. $filter['covid_classs'].'%')
       ->where(db::raw('ifnull(covid_history,"")'),'like','%'. $filter['covid_history'].'%')
       ->where(db::raw('ifnull(datepositive,"")'),'like','%'. $filter['datepositive'].'%')
       ->where(db::raw('ifnull(willing_to_vaccine,"")'),'like','%'. $filter['willing_to_vaccine'].'%')
       ->where(db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['first_dose'].'%')
       ->where(db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['second_dose'].'%')
       ->where(db::raw('ifnull(name,"")'),'like','%'. $filter['name'].'%')
       ->where(db::raw('ifnull(first_dose_date,"")'),'like','%'. $filter['first_dose_date'].'%')
       ->where(db::raw('ifnull(first_dose_vac_manufacturer,"")'),'like','%'. $filter['first_dose_vac_manufacturer'].'%')
       ->where(db::raw('if(ifnull(Deferral,"") ="","02_No","01_Yes")'),'like','%'. $filter['deferral_status'].'%')
       ->where(db::raw('ifnull(def_date,"")'),'like','%'. $filter['def_date'].'%')
       ->where('stat',0)
       ->orderBy('vaccine_profiling.id','asc')
       ;
        $data['list']=$list->skip($request->startRow)->take($request->count)->get();
        return response()->json(new jsonresponse($data));
    }
       public function listEntry(Request $request){
        $filter = $request->filter;
        $list = db::table('vaccine_profiling')
        ->leftjoin('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
       ->leftjoin('refprovince','refprovince.provCode','=','vaccine_profiling.provinceId')
       ->leftjoin('refcitymun','refcitymun.citymunCode','=','vaccine_profiling.cityId')
       ->leftjoin('refbrgy','refbrgy.id','=','vaccine_profiling.barangayId')
       ->leftjoin('users','users.id','=','vaccine_profiling.uid')
       ->select('vaccine_entry.*','brgy_name','city_name','prov_name','users.name','vaccine_profiling.*','vaccine_entry.id as vacid',db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes") as first_dose'))
       ->where(db::raw('ifnull(HH_No,"")'),'like','%'. $filter['HH_No'].'%')
       ->where(db::raw('ifnull(lastName,"")'),'like',''. $filter['lastName'].'%')
       ->where(db::raw('ifnull(firstName,"")'),'like',''. $filter['firstName'].'%')
       ->where(db::raw('ifnull(middleName,"")'),'like',''. $filter['middleName'].'%')
       ->where(db::raw('ifnull(suffix,"")'),'like','%'. $filter['suffix'].'%')
       ->where(db::raw('ifnull(contact_number,"")'),'like','%'. $filter['contact_number'].'%')
       ->where(db::raw('ifnull(civilStatus,"")'),'like','%'. $filter['civilStatus'].'%')
       ->where(db::raw('ifnull(direct_contact_covid,"")'),'like','%'. $filter['direct_contact_covid'].'%')
       ->where(db::raw('ifnull(region,"")'),'like','%'. $filter['region'].'%')
       ->where(db::raw('ifnull(prov_name,"")'),'like','%'. $filter['prov_name'].'%')
       ->where(db::raw('ifnull(city_name,"")'),'like','%'. $filter['city_name'].'%')
       ->where(db::raw('ifnull(brgy_name,"")'),'like','%'. $filter['brgy_name'].'%')
       ->where(db::raw('ifnull(purok,"")'),'like','%'. $filter['purok'].'%')
       ->where(db::raw('ifnull(profession,"")'),'like','%'. $filter['profession'].'%')
       ->where(db::raw('ifnull(category,"")'),'like','%'. $filter['category'].'%')
       ->where(db::raw('ifnull(categoryID,"")'),'like','%'. $filter['categoryID'].'%')
       ->where(db::raw('ifnull(category_number,"")'),'like','%'. $filter['category_number'].'%')
       ->where(db::raw('ifnull(philhealthid,"")'),'like','%'. $filter['philhealthid'].'%')
       ->where(db::raw('ifnull(pwdid,"")'),'like','%'. $filter['pwdid'].'%')
       ->where(db::raw('ifnull(birthdate,"")'),'like','%'. $filter['birthdate'].'%')
       ->where(db::raw('ifnull(gender,"")'),'like','%'. $filter['gender'].'%')
       ->where(db::raw('ifnull(nameofemployer,"")'),'like','%'. $filter['nameofemployer'].'%')
       ->where(db::raw('ifnull(employerProvince,"")'),'like','%'. $filter['employerProvince'].'%')
       ->where(db::raw('ifnull(employerAddress,"")'),'like','%'. $filter['employerAddress'].'%')
       ->where(db::raw('ifnull(employercontact,"")'),'like','%'. $filter['employercontact'].'%')
       ->where(db::raw('ifnull(willing_to_vaccine,"")'),'like','%'. $filter['willing_to_vaccine'].'%')
       ->where(db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['first_dose'].'%')
       ->where('stat',0)
       ->orderBy('vaccine_profiling.id','asc')
       ;
        $data['list']=$list->skip($request->startRow)->take($request->count)->get();
        return response()->json(new jsonresponse($data));
    }
    public function listOnline(Request $request){
        $filter = $request->filter;
        $list = db::table('vaccine_profiling_online')
        // ->leftjoin('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling_online.id')
       ->leftjoin('refprovince','refprovince.provCode','=','vaccine_profiling_online.provinceId')
       ->leftjoin('refcitymun','refcitymun.citymunCode','=','vaccine_profiling_online.cityId')
       ->leftjoin('refbrgy','refbrgy.id','=','vaccine_profiling_online.barangayId')
    //    ->leftjoin('users','users.id','=','vaccine_profiling_online.uid')
    //    ->leftjoin('vaccine_profiling_details','vaccine_profiling_details.profile_id','=','vaccine_profiling_online.id')
       ->select('brgy_name','city_name','prov_name','vaccine_profiling_online.*')
       ->where(db::raw('ifnull(HH_No,"")'),'like','%'. $filter['HH_No'].'%')
       ->where(db::raw('ifnull(lastName,"")'),'like','%'. $filter['lastName'].'%')
       ->where(db::raw('ifnull(firstName,"")'),'like','%'. $filter['firstName'].'%')
       ->where(db::raw('ifnull(middleName,"")'),'like','%'. $filter['middleName'].'%')
       ->where(db::raw('ifnull(suffix,"")'),'like','%'. $filter['suffix'].'%')
       ->where(db::raw('ifnull(contact_number,"")'),'like','%'. $filter['contact_number'].'%')
       ->where(db::raw('ifnull(civilStatus,"")'),'like','%'. $filter['civilStatus'].'%')
       ->where(db::raw('ifnull(direct_contact_covid,"")'),'like','%'. $filter['direct_contact_covid'].'%')
       ->where(db::raw('ifnull(region,"")'),'like','%'. $filter['region'].'%')
       ->where(db::raw('ifnull(prov_name,"")'),'like','%'. $filter['prov_name'].'%')
       ->where(db::raw('ifnull(city_name,"")'),'like','%'. $filter['city_name'].'%')
       ->where(db::raw('ifnull(brgy_name,"")'),'like','%'. $filter['brgy_name'].'%')
       ->where(db::raw('ifnull(purok,"")'),'like','%'. $filter['purok'].'%')
       ->where(db::raw('ifnull(profession,"")'),'like','%'. $filter['profession'].'%')
       ->where(db::raw('ifnull(category,"")'),'like','%'. $filter['category'].'%')
       ->where(db::raw('ifnull(categoryID,"")'),'like','%'. $filter['categoryID'].'%')
       ->where(db::raw('ifnull(category_number,"")'),'like','%'. $filter['category_number'].'%')
       ->where(db::raw('ifnull(philhealthid,"")'),'like','%'. $filter['philhealthid'].'%')
       ->where(db::raw('ifnull(pwdid,"")'),'like','%'. $filter['pwdid'].'%')
       ->where(db::raw('ifnull(birthdate,"")'),'like','%'. $filter['birthdate'].'%')
       ->where(db::raw('ifnull(gender,"")'),'like','%'. $filter['gender'].'%')
       ->where(db::raw('ifnull(nameofemployer,"")'),'like','%'. $filter['nameofemployer'].'%')
       ->where(db::raw('ifnull(employerProvince,"")'),'like','%'. $filter['employerProvince'].'%')
       ->where(db::raw('ifnull(employerAddress,"")'),'like','%'. $filter['employerAddress'].'%')
       ->where(db::raw('ifnull(employercontact,"")'),'like','%'. $filter['employercontact'].'%')
       ->where(db::raw('ifnull(pregnancy,"")'),'like','%'. $filter['pregnancy'].'%')
       ->where(db::raw('ifnull(allergy,"")'),'like','%'. $filter['allergy'].'%')
       ->where(db::raw('ifnull(allergy_food,"")'),'like','%'. $filter['allergy_food'].'%')
       ->where(db::raw('ifnull(allergy_insect,"")'),'like','%'. $filter['allergy_insect'].'%')
       ->where(db::raw('ifnull(allergy_latex,"")'),'like','%'. $filter['allergy_latex'].'%')
       ->where(db::raw('ifnull(allergy_mold,"")'),'like','%'. $filter['allergy_mold'].'%')
       ->where(db::raw('ifnull(allergy_pet,"")'),'like','%'. $filter['allergy_pet'].'%')
       ->where(db::raw('ifnull(allergy_pollen,"")'),'like','%'. $filter['allergy_pollen'].'%')
       ->where(db::raw('ifnull(comorbidity,"")'),'like','%'. $filter['comorbidity'].'%')
       ->where(db::raw('ifnull(comorbidity_hepertension,"")'),'like','%'. $filter['comorbidity_hepertension'].'%')
       ->where(db::raw('ifnull(comorbidity_heart,"")'),'like','%'. $filter['comorbidity_heart'].'%')
       ->where(db::raw('ifnull(comorbidity_kidney,"")'),'like','%'. $filter['comorbidity_kidney'].'%')
       ->where(db::raw('ifnull(comorbidity_diabetes,"")'),'like','%'. $filter['comorbidity_diabetes'].'%')
       ->where(db::raw('ifnull(comorbidity_bronchial,"")'),'like','%'. $filter['comorbidity_bronchial'].'%')
       ->where(db::raw('ifnull(immunodeficiency,"")'),'like','%'. $filter['immunodeficiency'].'%')
       ->where(db::raw('ifnull(immunodeficiency_cancer,"")'),'like','%'. $filter['immunodeficiency_cancer'].'%')
       ->where(db::raw('ifnull(immunodeficiency_other,"")'),'like','%'. $filter['immunodeficiency_other'].'%')
       ->where(db::raw('ifnull(covid_classs,"")'),'like','%'. $filter['covid_classs'].'%')
       ->where(db::raw('ifnull(covid_history,"")'),'like','%'. $filter['covid_history'].'%')
       ->where(db::raw('ifnull(datepositive,"")'),'like','%'. $filter['datepositive'].'%')
       ->where(db::raw('ifnull(willing_to_vaccine,"")'),'like','%'. $filter['willing_to_vaccine'].'%')
       ->where("encoded",0)
       ->where('stat',0)
       ->orderBy('lastName','asc')
       ->orderBy('firstName','asc')
       ;
        $data['list']=$list->skip($request->startRow)->take($request->count)->get();
        return response()->json(new jsonresponse($data));
    }
    public function listCount(Request $request){
        $filter = $request->filter;
        $list = db::table('vaccine_profiling')
        ->leftjoin('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
       ->leftjoin('refprovince','refprovince.provCode','=','vaccine_profiling.provinceId')
       ->leftjoin('refcitymun','refcitymun.citymunCode','=','vaccine_profiling.cityId')
       ->leftjoin('refbrgy','refbrgy.id','=','vaccine_profiling.barangayId')
       ->leftjoin('users','users.id','=','vaccine_profiling.uid')
       ->leftjoin('vaccine_profiling_details','vaccine_profiling_details.profile_id','=','vaccine_profiling.id')
       ->where(db::raw('ifnull(HH_No,"")'),'like','%'. $filter['HH_No'].'%')
       ->where(db::raw('ifnull(lastName,"")'),'like',''. $filter['lastName'].'%')
       ->where(db::raw('ifnull(firstName,"")'),'like',''. $filter['firstName'].'%')
       ->where(db::raw('ifnull(middleName,"")'),'like',''. $filter['middleName'].'%')
       ->where(db::raw('ifnull(suffix,"")'),'like','%'. $filter['suffix'].'%')
       ->where(db::raw('ifnull(contact_number,"")'),'like',''. $filter['contact_number'].'%')
       ->where(db::raw('ifnull(civilStatus,"")'),'like','%'. $filter['civilStatus'].'%')
       ->where(db::raw('ifnull(direct_contact_covid,"")'),'like','%'. $filter['direct_contact_covid'].'%')
       ->where(db::raw('ifnull(region,"")'),'like','%'. $filter['region'].'%')
       ->where(db::raw('ifnull(prov_name,"")'),'like','%'. $filter['prov_name'].'%')
       ->where(db::raw('ifnull(city_name,"")'),'like','%'. $filter['city_name'].'%')
       ->where(db::raw('ifnull(brgy_name,"")'),'like','%'. $filter['brgy_name'].'%')
       ->where(db::raw('ifnull(purok,"")'),'like','%'. $filter['purok'].'%')
       ->where(db::raw('ifnull(profession,"")'),'like','%'. $filter['profession'].'%')
       ->where(db::raw('ifnull(category,"")'),'like','%'. $filter['category'].'%')
       ->where(db::raw('ifnull(categoryID,"")'),'like','%'. $filter['categoryID'].'%')
       ->where(db::raw('ifnull(category_number,"")'),'like','%'. $filter['category_number'].'%')
       ->where(db::raw('ifnull(philhealthid,"")'),'like','%'. $filter['philhealthid'].'%')
       ->where(db::raw('ifnull(pwdid,"")'),'like','%'. $filter['pwdid'].'%')
       ->where(db::raw('ifnull(birthdate,"")'),'like','%'. $filter['birthdate'].'%')
       ->where(db::raw('ifnull(gender,"")'),'like','%'. $filter['gender'].'%')
       ->where(db::raw('ifnull(nameofemployer,"")'),'like','%'. $filter['nameofemployer'].'%')
       ->where(db::raw('ifnull(employerProvince,"")'),'like','%'. $filter['employerProvince'].'%')
       ->where(db::raw('ifnull(employerAddress,"")'),'like','%'. $filter['employerAddress'].'%')
       ->where(db::raw('ifnull(employercontact,"")'),'like','%'. $filter['employercontact'].'%')
       ->where(db::raw('ifnull(pregnancy,"")'),'like','%'. $filter['pregnancy'].'%')
       ->where(db::raw('ifnull(allergy,"")'),'like','%'. $filter['allergy'].'%')
       ->where(db::raw('ifnull(allergy_food,"")'),'like','%'. $filter['allergy_food'].'%')
       ->where(db::raw('ifnull(allergy_insect,"")'),'like','%'. $filter['allergy_insect'].'%')
       ->where(db::raw('ifnull(allergy_latex,"")'),'like','%'. $filter['allergy_latex'].'%')
       ->where(db::raw('ifnull(allergy_mold,"")'),'like','%'. $filter['allergy_mold'].'%')
       ->where(db::raw('ifnull(allergy_pet,"")'),'like','%'. $filter['allergy_pet'].'%')
       ->where(db::raw('ifnull(allergy_pollen,"")'),'like','%'. $filter['allergy_pollen'].'%')
       ->where(db::raw('ifnull(comorbidity,"")'),'like','%'. $filter['comorbidity'].'%')
       ->where(db::raw('ifnull(comorbidity_hepertension,"")'),'like','%'. $filter['comorbidity_hepertension'].'%')
       ->where(db::raw('ifnull(comorbidity_heart,"")'),'like','%'. $filter['comorbidity_heart'].'%')
       ->where(db::raw('ifnull(comorbidity_kidney,"")'),'like','%'. $filter['comorbidity_kidney'].'%')
       ->where(db::raw('ifnull(comorbidity_diabetes,"")'),'like','%'. $filter['comorbidity_diabetes'].'%')
       ->where(db::raw('ifnull(comorbidity_bronchial,"")'),'like','%'. $filter['comorbidity_bronchial'].'%')
       ->where(db::raw('ifnull(immunodeficiency,"")'),'like','%'. $filter['immunodeficiency'].'%')
       ->where(db::raw('ifnull(immunodeficiency_cancer,"")'),'like','%'. $filter['immunodeficiency_cancer'].'%')
       ->where(db::raw('ifnull(immunodeficiency_other,"")'),'like','%'. $filter['immunodeficiency_other'].'%')
       ->where(db::raw('ifnull(covid_classs,"")'),'like','%'. $filter['covid_classs'].'%')
       ->where(db::raw('ifnull(covid_history,"")'),'like','%'. $filter['covid_history'].'%')
       ->where(db::raw('ifnull(datepositive,"")'),'like','%'. $filter['datepositive'].'%')
       ->where(db::raw('ifnull(willing_to_vaccine,"")'),'like','%'. $filter['willing_to_vaccine'].'%')
       ->where(db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['first_dose'].'%')
       ->where(db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['second_dose'].'%')
       ->where(db::raw('ifnull(name,"")'),'like','%'. $filter['name'].'%')
       ->where(db::raw('ifnull(first_dose_date,"")'),'like','%'. $filter['first_dose_date'].'%')
       ->where(db::raw('ifnull(first_dose_vac_manufacturer,"")'),'like','%'. $filter['first_dose_vac_manufacturer'].'%')
       ->where(db::raw('if(ifnull(Deferral,"") ="","02_No","01_Yes")'),'like','%'. $filter['deferral_status'].'%')
       ->where(db::raw('ifnull(def_date,"")'),'like','%'. $filter['def_date'].'%')
       ->where('stat',0)
       ->orderBy('vaccine_profiling.id','asc')
       ;
        $data['count']=$list->count();
        // $data['count'] = 0;
        return response()->json(new jsonresponse($data));
    }
    public function listCountOnline(Request $request){
        $filter = $request->filter;
        $list = db::table('vaccine_profiling_online')
        // ->leftjoin('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
       ->leftjoin('refprovince','refprovince.provCode','=','vaccine_profiling_online.provinceId')
       ->leftjoin('refcitymun','refcitymun.citymunCode','=','vaccine_profiling_online.cityId')
       ->leftjoin('refbrgy','refbrgy.id','=','vaccine_profiling_online.barangayId')
    //    ->leftjoin('users','users.id','=','vaccine_profiling.uid')
    //    ->leftjoin('vaccine_profiling_details','vaccine_profiling_details.profile_id','=','vaccine_profiling.id')
    //    ->select('vaccine_entry.*','vaccine_profiling_details.*','brgy_name','city_name','prov_name','users.name','vaccine_profiling.*','vaccine_entry.id as vacid',db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes") as first_dose'),db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes") as second_dose'))
       ->where(db::raw('ifnull(HH_No,"")'),'like','%'. $filter['HH_No'].'%')
       ->where(db::raw('ifnull(lastName,"")'),'like','%'. $filter['lastName'].'%')
       ->where(db::raw('ifnull(firstName,"")'),'like','%'. $filter['firstName'].'%')
       ->where(db::raw('ifnull(middleName,"")'),'like','%'. $filter['middleName'].'%')
       ->where(db::raw('ifnull(suffix,"")'),'like','%'. $filter['suffix'].'%')
       ->where(db::raw('ifnull(contact_number,"")'),'like','%'. $filter['contact_number'].'%')
       ->where(db::raw('ifnull(civilStatus,"")'),'like','%'. $filter['civilStatus'].'%')
       ->where(db::raw('ifnull(direct_contact_covid,"")'),'like','%'. $filter['direct_contact_covid'].'%')
       ->where(db::raw('ifnull(region,"")'),'like','%'. $filter['region'].'%')
       ->where(db::raw('ifnull(prov_name,"")'),'like','%'. $filter['prov_name'].'%')
       ->where(db::raw('ifnull(city_name,"")'),'like','%'. $filter['city_name'].'%')
       ->where(db::raw('ifnull(brgy_name,"")'),'like','%'. $filter['brgy_name'].'%')
       ->where(db::raw('ifnull(purok,"")'),'like','%'. $filter['purok'].'%')
       ->where(db::raw('ifnull(profession,"")'),'like','%'. $filter['profession'].'%')
       ->where(db::raw('ifnull(category,"")'),'like','%'. $filter['category'].'%')
       ->where(db::raw('ifnull(categoryID,"")'),'like','%'. $filter['categoryID'].'%')
       ->where(db::raw('ifnull(category_number,"")'),'like','%'. $filter['category_number'].'%')
       ->where(db::raw('ifnull(philhealthid,"")'),'like','%'. $filter['philhealthid'].'%')
       ->where(db::raw('ifnull(pwdid,"")'),'like','%'. $filter['pwdid'].'%')
       ->where(db::raw('ifnull(birthdate,"")'),'like','%'. $filter['birthdate'].'%')
       ->where(db::raw('ifnull(gender,"")'),'like','%'. $filter['gender'].'%')
       ->where(db::raw('ifnull(nameofemployer,"")'),'like','%'. $filter['nameofemployer'].'%')
       ->where(db::raw('ifnull(employerProvince,"")'),'like','%'. $filter['employerProvince'].'%')
       ->where(db::raw('ifnull(employerAddress,"")'),'like','%'. $filter['employerAddress'].'%')
       ->where(db::raw('ifnull(employercontact,"")'),'like','%'. $filter['employercontact'].'%')
       ->where(db::raw('ifnull(pregnancy,"")'),'like','%'. $filter['pregnancy'].'%')
       ->where(db::raw('ifnull(allergy,"")'),'like','%'. $filter['allergy'].'%')
       ->where(db::raw('ifnull(allergy_food,"")'),'like','%'. $filter['allergy_food'].'%')
       ->where(db::raw('ifnull(allergy_insect,"")'),'like','%'. $filter['allergy_insect'].'%')
       ->where(db::raw('ifnull(allergy_latex,"")'),'like','%'. $filter['allergy_latex'].'%')
       ->where(db::raw('ifnull(allergy_mold,"")'),'like','%'. $filter['allergy_mold'].'%')
       ->where(db::raw('ifnull(allergy_pet,"")'),'like','%'. $filter['allergy_pet'].'%')
       ->where(db::raw('ifnull(allergy_pollen,"")'),'like','%'. $filter['allergy_pollen'].'%')
       ->where(db::raw('ifnull(comorbidity,"")'),'like','%'. $filter['comorbidity'].'%')
       ->where(db::raw('ifnull(comorbidity_hepertension,"")'),'like','%'. $filter['comorbidity_hepertension'].'%')
       ->where(db::raw('ifnull(comorbidity_heart,"")'),'like','%'. $filter['comorbidity_heart'].'%')
       ->where(db::raw('ifnull(comorbidity_kidney,"")'),'like','%'. $filter['comorbidity_kidney'].'%')
       ->where(db::raw('ifnull(comorbidity_diabetes,"")'),'like','%'. $filter['comorbidity_diabetes'].'%')
       ->where(db::raw('ifnull(comorbidity_bronchial,"")'),'like','%'. $filter['comorbidity_bronchial'].'%')
       ->where(db::raw('ifnull(immunodeficiency,"")'),'like','%'. $filter['immunodeficiency'].'%')
       ->where(db::raw('ifnull(immunodeficiency_cancer,"")'),'like','%'. $filter['immunodeficiency_cancer'].'%')
       ->where(db::raw('ifnull(immunodeficiency_other,"")'),'like','%'. $filter['immunodeficiency_other'].'%')
       ->where(db::raw('ifnull(covid_classs,"")'),'like','%'. $filter['covid_classs'].'%')
       ->where(db::raw('ifnull(covid_history,"")'),'like','%'. $filter['covid_history'].'%')
       ->where(db::raw('ifnull(datepositive,"")'),'like','%'. $filter['datepositive'].'%')
       ->where(db::raw('ifnull(willing_to_vaccine,"")'),'like','%'. $filter['willing_to_vaccine'].'%')
    //    ->where('encoded',0)
    //    ->where(db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['first_dose'].'%')
    //    ->where(db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['second_dose'].'%')
    //    ->where(db::raw('ifnull(name,"")'),'like','%'. $filter['name'].'%')
    //    ->where(db::raw('ifnull(first_dose_date,"")'),'like','%'. $filter['first_dose_date'].'%')
    //    ->where(db::raw('ifnull(first_dose_vac_manufacturer,"")'),'like','%'. $filter['first_dose_vac_manufacturer'].'%')
      ->where("encoded",0)
       ->where('stat',0);
        $data['count']=$list->count();
        return response()->json(new jsonresponse($data));
    }
    public function listExport(Request $request){
        $filter = $request->filter;
        $list = db::table('vaccine_profiling')
       ->leftjoin('refprovince','refprovince.provCode','=','vaccine_profiling.provinceId')
       ->leftjoin('refcitymun','refcitymun.citymunCode','=','vaccine_profiling.cityId')
       ->leftjoin('refbrgy','refbrgy.id','=','vaccine_profiling.barangayId')
       ->leftjoin('users','users.id','=','vaccine_profiling.uid')
       ->leftjoin('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
       ->leftjoin('vaccine_profiling_details','vaccine_profiling_details.profile_id','=','vaccine_profiling.id')
       ->select('vaccine_entry.*','vaccine_profiling_details.*','brgy_name','city_name','prov_name','users.name','vaccine_profiling.*','vaccine_entry.id as vacid',db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes") as first_dose'),db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes") as second_dose'),db::raw('if(ifnull(Deferral,"") ="","02_No","01_Yes") as deferral_status'))
       ->where(db::raw('ifnull(HH_No,"")'),'like','%'. $filter['HH_No'].'%')
       ->where(db::raw('ifnull(lastName,"")'),'like','%'. $filter['lastName'].'%')
       ->where(db::raw('ifnull(firstName,"")'),'like','%'. $filter['firstName'].'%')
       ->where(db::raw('ifnull(middleName,"")'),'like','%'. $filter['middleName'].'%')
       ->where(db::raw('ifnull(suffix,"")'),'like','%'. $filter['suffix'].'%')
       ->where(db::raw('ifnull(contact_number,"")'),'like','%'. $filter['contact_number'].'%')
       ->where(db::raw('ifnull(civilStatus,"")'),'like','%'. $filter['civilStatus'].'%')
       ->where(db::raw('ifnull(direct_contact_covid,"")'),'like','%'. $filter['direct_contact_covid'].'%')
       ->where(db::raw('ifnull(region,"")'),'like','%'. $filter['region'].'%')
       ->where(db::raw('ifnull(prov_name,"")'),'like','%'. $filter['prov_name'].'%')
       ->where(db::raw('ifnull(city_name,"")'),'like','%'. $filter['city_name'].'%')
       ->where(db::raw('ifnull(brgy_name,"")'),'like','%'. $filter['brgy_name'].'%')
       ->where(db::raw('ifnull(purok,"")'),'like','%'. $filter['purok'].'%')
       ->where(db::raw('ifnull(profession,"")'),'like','%'. $filter['profession'].'%')
       ->where(db::raw('ifnull(category,"")'),'like','%'. $filter['category'].'%')
       ->where(db::raw('ifnull(categoryID,"")'),'like','%'. $filter['categoryID'].'%')
       ->where(db::raw('ifnull(category_number,"")'),'like','%'. $filter['category_number'].'%')
       ->where(db::raw('ifnull(philhealthid,"")'),'like','%'. $filter['philhealthid'].'%')
       ->where(db::raw('ifnull(pwdid,"")'),'like','%'. $filter['pwdid'].'%')
       ->where(db::raw('ifnull(birthdate,"")'),'like','%'. $filter['birthdate'].'%')
       ->where(db::raw('ifnull(gender,"")'),'like','%'. $filter['gender'].'%')
       ->where(db::raw('ifnull(nameofemployer,"")'),'like','%'. $filter['nameofemployer'].'%')
       ->where(db::raw('ifnull(employerProvince,"")'),'like','%'. $filter['employerProvince'].'%')
       ->where(db::raw('ifnull(employerAddress,"")'),'like','%'. $filter['employerAddress'].'%')
       ->where(db::raw('ifnull(employercontact,"")'),'like','%'. $filter['employercontact'].'%')
       ->where(db::raw('ifnull(pregnancy,"")'),'like','%'. $filter['pregnancy'].'%')
       ->where(db::raw('ifnull(allergy,"")'),'like','%'. $filter['allergy'].'%')
       ->where(db::raw('ifnull(allergy_food,"")'),'like','%'. $filter['allergy_food'].'%')
       ->where(db::raw('ifnull(allergy_insect,"")'),'like','%'. $filter['allergy_insect'].'%')
       ->where(db::raw('ifnull(allergy_latex,"")'),'like','%'. $filter['allergy_latex'].'%')
       ->where(db::raw('ifnull(allergy_mold,"")'),'like','%'. $filter['allergy_mold'].'%')
       ->where(db::raw('ifnull(allergy_pet,"")'),'like','%'. $filter['allergy_pet'].'%')
       ->where(db::raw('ifnull(allergy_pollen,"")'),'like','%'. $filter['allergy_pollen'].'%')
       ->where(db::raw('ifnull(comorbidity,"")'),'like','%'. $filter['comorbidity'].'%')
       ->where(db::raw('ifnull(comorbidity_hepertension,"")'),'like','%'. $filter['comorbidity_hepertension'].'%')
       ->where(db::raw('ifnull(comorbidity_heart,"")'),'like','%'. $filter['comorbidity_heart'].'%')
       ->where(db::raw('ifnull(comorbidity_kidney,"")'),'like','%'. $filter['comorbidity_kidney'].'%')
       ->where(db::raw('ifnull(comorbidity_diabetes,"")'),'like','%'. $filter['comorbidity_diabetes'].'%')
       ->where(db::raw('ifnull(comorbidity_bronchial,"")'),'like','%'. $filter['comorbidity_bronchial'].'%')
       ->where(db::raw('ifnull(immunodeficiency,"")'),'like','%'. $filter['immunodeficiency'].'%')
       ->where(db::raw('ifnull(immunodeficiency_cancer,"")'),'like','%'. $filter['immunodeficiency_cancer'].'%')
       ->where(db::raw('ifnull(immunodeficiency_other,"")'),'like','%'. $filter['immunodeficiency_other'].'%')
       ->where(db::raw('ifnull(covid_classs,"")'),'like','%'. $filter['covid_classs'].'%')
       ->where(db::raw('ifnull(covid_history,"")'),'like','%'. $filter['covid_history'].'%')
       ->where(db::raw('ifnull(datepositive,"")'),'like','%'. $filter['datepositive'].'%')
       ->where(db::raw('ifnull(willing_to_vaccine,"")'),'like','%'. $filter['willing_to_vaccine'].'%')
       ->where(db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['first_dose'].'%')
       ->where(db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['second_dose'].'%')
       ->where(db::raw('ifnull(first_dose_vac_manufacturer,"")'),'like','%'. $filter['first_dose_vac_manufacturer'].'%')
       ->where(db::raw('ifnull(name,"")'),'like','%'. $filter['name'].'%')
       ->where(db::raw('if(ifnull(Deferral,"") ="","02_No","01_Yes")'),'like','%'. $filter['deferral_status'].'%')
       ->where(db::raw('ifnull(def_date,"")'),'like','%'. $filter['def_date'].'%')
       ->where('stat',0);
        $data=$list->get();
        // return Excel::download(new vaccineExport($data), 'disney.xlsx');
        return response()->json(new jsonresponse($data));
    }
    public function listExportOnline(Request $request){
        $filter = $request->filter;
        $list = db::table('vaccine_profiling_online')
       ->leftjoin('refprovince','refprovince.provCode','=','vaccine_profiling_online.provinceId')
       ->leftjoin('refcitymun','refcitymun.citymunCode','=','vaccine_profiling_online.cityId')
       ->leftjoin('refbrgy','refbrgy.id','=','vaccine_profiling_online.barangayId')
    //    ->leftjoin('users','users.id','=','vaccine_profiling_online.uid')
    //    ->leftjoin('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling_online.id')
    //    ->leftjoin('vaccine_profiling_details','vaccine_profiling_details.profile_id','=','vaccine_profiling_online.id')
       ->select('brgy_name','city_name','prov_name','vaccine_profiling_online.*')
       ->where(db::raw('ifnull(HH_No,"")'),'like','%'. $filter['HH_No'].'%')
       ->where(db::raw('ifnull(lastName,"")'),'like','%'. $filter['lastName'].'%')
       ->where(db::raw('ifnull(firstName,"")'),'like','%'. $filter['firstName'].'%')
       ->where(db::raw('ifnull(middleName,"")'),'like','%'. $filter['middleName'].'%')
       ->where(db::raw('ifnull(suffix,"")'),'like','%'. $filter['suffix'].'%')
       ->where(db::raw('ifnull(contact_number,"")'),'like','%'. $filter['contact_number'].'%')
       ->where(db::raw('ifnull(civilStatus,"")'),'like','%'. $filter['civilStatus'].'%')
       ->where(db::raw('ifnull(direct_contact_covid,"")'),'like','%'. $filter['direct_contact_covid'].'%')
       ->where(db::raw('ifnull(region,"")'),'like','%'. $filter['region'].'%')
       ->where(db::raw('ifnull(prov_name,"")'),'like','%'. $filter['prov_name'].'%')
       ->where(db::raw('ifnull(city_name,"")'),'like','%'. $filter['city_name'].'%')
       ->where(db::raw('ifnull(brgy_name,"")'),'like','%'. $filter['brgy_name'].'%')
       ->where(db::raw('ifnull(purok,"")'),'like','%'. $filter['purok'].'%')
       ->where(db::raw('ifnull(profession,"")'),'like','%'. $filter['profession'].'%')
       ->where(db::raw('ifnull(category,"")'),'like','%'. $filter['category'].'%')
       ->where(db::raw('ifnull(categoryID,"")'),'like','%'. $filter['categoryID'].'%')
       ->where(db::raw('ifnull(category_number,"")'),'like','%'. $filter['category_number'].'%')
       ->where(db::raw('ifnull(philhealthid,"")'),'like','%'. $filter['philhealthid'].'%')
       ->where(db::raw('ifnull(pwdid,"")'),'like','%'. $filter['pwdid'].'%')
       ->where(db::raw('ifnull(birthdate,"")'),'like','%'. $filter['birthdate'].'%')
       ->where(db::raw('ifnull(gender,"")'),'like','%'. $filter['gender'].'%')
       ->where(db::raw('ifnull(nameofemployer,"")'),'like','%'. $filter['nameofemployer'].'%')
       ->where(db::raw('ifnull(employerProvince,"")'),'like','%'. $filter['employerProvince'].'%')
       ->where(db::raw('ifnull(employerAddress,"")'),'like','%'. $filter['employerAddress'].'%')
       ->where(db::raw('ifnull(employercontact,"")'),'like','%'. $filter['employercontact'].'%')
       ->where(db::raw('ifnull(pregnancy,"")'),'like','%'. $filter['pregnancy'].'%')
       ->where(db::raw('ifnull(allergy,"")'),'like','%'. $filter['allergy'].'%')
       ->where(db::raw('ifnull(allergy_food,"")'),'like','%'. $filter['allergy_food'].'%')
       ->where(db::raw('ifnull(allergy_insect,"")'),'like','%'. $filter['allergy_insect'].'%')
       ->where(db::raw('ifnull(allergy_latex,"")'),'like','%'. $filter['allergy_latex'].'%')
       ->where(db::raw('ifnull(allergy_mold,"")'),'like','%'. $filter['allergy_mold'].'%')
       ->where(db::raw('ifnull(allergy_pet,"")'),'like','%'. $filter['allergy_pet'].'%')
       ->where(db::raw('ifnull(allergy_pollen,"")'),'like','%'. $filter['allergy_pollen'].'%')
       ->where(db::raw('ifnull(comorbidity,"")'),'like','%'. $filter['comorbidity'].'%')
       ->where(db::raw('ifnull(comorbidity_hepertension,"")'),'like','%'. $filter['comorbidity_hepertension'].'%')
       ->where(db::raw('ifnull(comorbidity_heart,"")'),'like','%'. $filter['comorbidity_heart'].'%')
       ->where(db::raw('ifnull(comorbidity_kidney,"")'),'like','%'. $filter['comorbidity_kidney'].'%')
       ->where(db::raw('ifnull(comorbidity_diabetes,"")'),'like','%'. $filter['comorbidity_diabetes'].'%')
       ->where(db::raw('ifnull(comorbidity_bronchial,"")'),'like','%'. $filter['comorbidity_bronchial'].'%')
       ->where(db::raw('ifnull(immunodeficiency,"")'),'like','%'. $filter['immunodeficiency'].'%')
       ->where(db::raw('ifnull(immunodeficiency_cancer,"")'),'like','%'. $filter['immunodeficiency_cancer'].'%')
       ->where(db::raw('ifnull(immunodeficiency_other,"")'),'like','%'. $filter['immunodeficiency_other'].'%')
       ->where(db::raw('ifnull(covid_classs,"")'),'like','%'. $filter['covid_classs'].'%')
       ->where(db::raw('ifnull(covid_history,"")'),'like','%'. $filter['covid_history'].'%')
       ->where(db::raw('ifnull(datepositive,"")'),'like','%'. $filter['datepositive'].'%')
       ->where(db::raw('ifnull(willing_to_vaccine,"")'),'like','%'. $filter['willing_to_vaccine'].'%')
    //    ->where(db::raw('IF(first_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['first_dose'].'%')
    //    ->where(db::raw('IF(second_dose_date IS NULL,"02_No","01_Yes")'),'like','%'. $filter['second_dose'].'%')
    //    ->where(db::raw('ifnull(first_dose_vac_manufacturer,"")'),'like','%'. $filter['first_dose_vac_manufacturer'].'%')
    //    ->where(db::raw('ifnull(name,"")'),'like','%'. $filter['name'].'%')
       ->where('stat',0);
        $data=$list->get();
        return response()->json(new jsonresponse($data));
    }
    
    public function vaccineCount(){
        
    }
    public function edit($id)
    {
        $data['main']= db::table('vaccine_profiling')->where('id', $id)->get();
        return response()->json(new jsonresponse($data));
    }
    public function vaccineEdit($id)
    {
        $data['main']= db::table('vaccine_entry')->where('profile_id', $id)->get();
        return response()->json(new jsonresponse($data));
    }
    
    public function profileEdit($id)
    {
        $data = db::table('vaccine_profiling_details')->where('profile_id', $id)->get();
        return response()->json(new jsonresponse($data));
    }
    

    
    public function cancelOnline($id)
    {
        db::table('vaccine_profiling_online')->where('id', $id)->update(['stat'=>1,'uid'=>Auth::user()->id]);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function cancel($id)
    {
        db::table('vaccine_profiling')->where('id', $id)->update(['stat'=>1,'uid'=>Auth::user()->id]);
        $idx = db::table('vaccine_profiling')->where('id', $id)->first();
        db::table('household_members')->where('id', $idx->member_id)->update(['surveyed'=>1]);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function getvaccinators ()
    {
        $data = db::table('vaccinator')->get();
        return response()->json(new jsonresponse($data));
    }
    public function getPercentage()
    {
        $data = db::select('call vaccine_percentage');
        return response()->json(new jsonresponse($data));
    }
    public function getPercentageDetails(Request $request)
    {
        $data = db::select('call dash_get_vaccinated_by_date(?)',[$request->category]);
        return response()->json(new jsonresponse($data));
    }
    public function getvacinatedPerCategory(Request $request)
    {
        $data = db::select('call dash_get_vaccinated_by_date_name(?,?)',[$request->category,$request->date]);
        return response()->json(new jsonresponse($data));
    }
    
    public function getDashManufacturerFirst()
    {
        $data = db::table('vaccine_profiling')
        ->select('vaccine_entry.first_dose_vac_manufacturer as manufacturer',db::raw('COUNT(*) as count'))
        ->join('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
        ->whereNotNull('first_dose_vac_manufacturer')
        ->groupBy('vaccine_entry.first_dose_vac_manufacturer')->get();
         return response()->json(new jsonresponse($data));
    }
    public function getDashManufacturerFirstGroupByDate()
    {
        $data = db::table('vaccine_profiling')
        ->select('vaccine_entry.first_dose_vac_manufacturer as manufacturer','first_dose_date as date',db::raw('COUNT(*) as count'))
        ->join('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
        ->whereNotNull('first_dose_vac_manufacturer')
        ->groupBy('vaccine_entry.first_dose_vac_manufacturer','vaccine_entry.first_dose_date')
        ->get();
         return response()->json(new jsonresponse($data));
    }
    public function getDashManufacturerFirstPerDate(Request $request)
    {
        $data = $request->data;
        $data = db::table('vaccine_profiling')
        ->select('vaccine_entry.first_dose_vac_manufacturer as manufacturer','first_dose_date as date',db::raw('COUNT(*) as count'))
        ->join('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
        ->where('first_dose_vac_manufacturer',$data->manufacturer)
        ->where('first_dose_date',$data->date)
        ->get();
         return response()->json(new jsonresponse($data));
    }
    public function printVaccineCard($id)
    {
        // try {
        //     PDF::SetTitle('Vaccine Card');
        //     PDF::SetHeaderMargin(1);
        //     PDF::SetTopMargin(1);
        //     PDF::SetMargins(2, 2, 2, 2);
        //     PDF::SetFont('Helvetica', '', 10);
        //     $dataMain = db::table('vaccine_profiling')
        //     ->join('vaccine_entry','vaccine_entry.profile_id','=','vaccine_profiling.id')
        //     ->where('vaccine_entry.profile_id',$id)->get()
        //     ;
        //     foreach ($dataMain as $key => $value) {
        //         $result = $value;
        //     }

        //     $result;
        //    log::debug($result->profile_id);
 
        //     PDF::AddPage('L', array(215.9,330.2));
        //     // -- set new background ---
        //     $bMargin = PDF::getBreakMargin();
        //     $auto_page_break = PDF::getAutoPageBreak();
        //     PDF::SetAutoPageBreak(false, 0);
        //     $img_file = public_path().'/HALF LONG3.jpg';
        //     PDF::Image($img_file, 0, 0, 330.2, 215.9 , '', '', '', false, 300, '', false, false, 0);
        //     PDF::SetAutoPageBreak($auto_page_break, $bMargin);
        //     PDF::setPageMark();
        //     PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
        //      $params1 = PDF::serializeTCPDFtagParameters(array( $result->profile_id , 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
        //     //  $params1 = PDF::serializeTCPDFtagParameters(array($result[0]->code , 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
         
        //      // $mask =PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', '', '', false, 300, '', true);
        //     // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', 'http://www.tcpdf.org', '', false, 300, '', false, $mask);
        //     // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 105, 40, 40, '', '', '', true, 72);
        //     // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 35, 245, 40, 40, '', '', '', true, 72);
        //     // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 245, 40, 40, '', '', '', true, 72);
        //     log::debug($params1);
        //     $Template = '
           
        //     <table width ="100%"  cellpadding ="2" >
        //     <tr>
           
        //     </tr>
        // </table>
     
        //         ';
        //         log::debug($Template);
        //     PDF::writeHTML($Template, true, 0, true, 0);
        //     PDF::Output(public_path().'/vac.pdf', 'F');
        //     return response()->json(new JsonResponse(['status' => 'success']));
        // } catch (\Exception $e) {
        //     return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        // }


        try {
            PDF::SetTitle('Vaccine Card');
            PDF::SetHeaderMargin(2);
            PDF::SetTopMargin(2);
            PDF::SetMargins(2, 2, 2, 2);
            PDF::SetFont('Helvetica', '', 10);
            $dataMain = db::select("SELECT 
           *,SUBSTRING(vaccine_profiling.middleName,1,1) as'mname',ucase(barangay) as 'brgy',date_format(vaccine_profiling.birthdate,'%m/%d/%Y') as 'dob'
           ,REPLACE(UCASE(SUBSTRING(vaccine_profiling.category,4,LENGTH(vaccine_profiling.category))),'_',' ') AS 'cat'
           ,date_format(vaccine_entry.first_dose_date,'%m') as 'fm'
           ,date_format(vaccine_entry.first_dose_date,'%d') as 'fd'
           ,date_format(vaccine_entry.first_dose_date,'%Y') as 'fy'
           ,date_format(vaccine_entry.second_dose_date,'%m') as 'sm'
           ,date_format(vaccine_entry.second_dose_date,'%d') as 'sd'
           ,date_format(vaccine_entry.second_dose_date,'%Y') as 'sy'
           ,lpad(vaccine_entry.id,10,0) as 'ref'
           ,DATE_FORMAT(second_dose_date_scheduled,'%m/%d/%Y') AS 'sched'
            FROM `vaccine_profiling`
            INNER JOIN `vaccine_entry` 
            ON(vaccine_entry.`profile_id` = vaccine_profiling.`id`)
            where vaccine_entry.profile_id = ".$id."
            ");
          
            log::debug($dataMain);

            foreach ($dataMain as $key => $value) {
                $result = $value;
            }
           log::debug($result->id);
            // PDF::AddPage('P');
            PDF::AddPage('L', array(230.9,330.2));
                // -- set new background ---
                $bMargin = PDF::getBreakMargin();
                $auto_page_break = PDF::getAutoPageBreak();
                PDF::SetAutoPageBreak(false, 0);
                $img_file = public_path().'/HALF LONG3.jpg';
                PDF::Image($img_file, 0, 0, 330.2, 215.9 , '', '', '', false, 300, '', false, false, 0);
                PDF::SetAutoPageBreak($auto_page_break, $bMargin);
                PDF::setPageMark();
                PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            $params1 = PDF::serializeTCPDFtagParameters(array( $id, 'QRCODE,H','', '', 20, 20, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            // $mask =PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', '', '', false, 300, '', true);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', 'http://www.tcpdf.org', '', false, 300, '', false, $mask);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 105, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 35, 245, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 245, 40, 40, '', '', '', true, 72);
        
            $Template = '
                    <table width ="100%"  cellpadding ="2" >
                        <tr>
                            <td width = "100%" >
                                <table cellpadding ="1" >
                                    <tr>
                                       <th style="width:53%;">
                                          
                                       </th>
                                       <th  style="width:47%;" ><tcpdf method="write2DBarcode" params="'.$params1.'" />'.$result->ref.'</th>          
                                          
                                    </tr>
                                    
                                <br>
                                    <br>
                                    <br>
                                    <br>
                                  
                                    
                                 
                            
                                    <tr>
                                        <th colspan ="2"> 
                                            <table width ="100%">
                                               <tr>
                                               <td  width = "53%"></td>
                                                    <td width = "47%">
                                                       <table>
                                                           <tr style="height:30px">
                                                               <th style="width:35%;font-size:10px" align="left">'.$result->lastName.'</th>    
                                                               <th style="width:43%;font-size:10px" align="left">'.$result->firstName.'</th>  
                                                               <th style="width:10%;font-size:10px" align="left">'.$result->mname.'</th>  
                                                               <th style="width:7%;font-size:10px" align="left">'.$result->suffix.''.'</th>  
                                                           </tr>
                                                           <br>
                                                           <tr style="height:20px">
                                                               <th style="width:5%"></th>
                                                               <th style="width:66%;font-size:10px" align="center">'.$result->purok.", ".$result->brgy." ".$result->cityName.'</th>    
                                                               <th style="width:29%;font-size:10px" align="left">'.$result->contact_number.'</th>
                                                            </tr> 

                                                            <tr style="height:25px">
                                                              <th style="width:33%;font-size:10px;" align="center">'.$result->dob.'</th>    
                                                              <th style="width:33%;font-size:10px;" align="center">'.$result->philhealthid."".'</th> 
                                                              <th style="width:33%;font-size:10px;" align="center">'.$result->cat."".'</th> 
                                                            </tr>
                                                            <br>
                                                            <br>
                                                            
                                                           <tr style="height:10px">
                                                             <th style="width:26%;font-size:9px;"></th>
                                                             <th style="width:3%;;font-size:9px;" align="left">'.$result->fm.'</th>   
                                                             <th style="width:4%;;font-size:9px;" align="left">'.$result->fd.'</th>  
                                                             <th style="width:5%;;font-size:9px;" align="left">'.$result->fy.'</th>  
                                                             <th style="width:25%;font-size:9px;">'.$result->first_dose_vac_manufacturer.' </th>
                                                             <th style="width:15%;font-size:9px;"  align="center">'.$result->first_dose_vac_batch.' </th>
                                                             <th style="width:15%;font-size:9px;"  align="center">'.$result->first_dose_vac_lot.' </th>
                                                            </tr>
                                                         <br>
                                                         <tr style="height:10px">
                                                            <th style="width:26%;font-size:9px;"></th>
                                                            <th style="width:50%;;font-size:10px;" align="center">'.$result->first_dose_vac_vacinator.'</th>
                                                         </tr>
                                                         <br>
                                                         <tr style="height:10px">
                                                          <th style="width:26%;font-size:9px;"></th>
                                                          <th style="width:3%;;font-size:9px;" align="left">'.$result->sm.'</th>   
                                                          <th style="width:4%;;font-size:9px;" align="left">'.$result->sd.'</th>  
                                                          <th style="width:5%;;font-size:9px;" align="left">'.$result->sy.'</th>  
                                                          <th style="width:25%;font-size:9px;">'.$result->second_dose_vac_manufacturer.'</th>
                                                          <th style="width:15%;font-size:9px;"  align="center">'.$result->second_dose_vac_batch.'</th>
                                                          <th style="width:15%;font-size:9px;"  align="center">'.$result->second_dose_vac_lot.'</th>
                                                        </tr>
                                                        <br>
                                                        <tr style="height:10px">
                                                           <th style="width:26%;font-size:9px;" align="center">'.$result->sched.'</th>
                                                           <th style="width:50%;;font-size:10px;" align="center">'.$result->second_dose_vac_vacinator.'</th>
                                                        </tr>
                                                        <tr style="height:40px">
                                                          <th style="width:17%;font-size:9px;" align="center"></th>
                                                           <th style="width:55%;;font-size:10px;height:10px" align="left">'.$result->facility.'</th>
                                                           <th style="width:20%;;font-size:10px;height:10px" align="left">'.$result->facility_no.'</th>
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
                   <br/>
                   <br/>
                 
               
                
                ';
                $Template .=' <table width ="100%"  cellpadding ="2" >
                <tr>
                    <td width = "100%" >
                        <table cellpadding ="1" >
                            <tr>
                               <th style="width:53%;">
                                  
                               </th>
                               <th style="width:47%;" ><tcpdf method="write2DBarcode" params="'.$params1.'" />'.$result->ref.'</th>          
                            </tr>
                      
                            <br>
                            <br>
                            <br>
                            <br>
                          
                        
                            <tr>
                                <th colspan ="2"> 
                                    <table width ="100%">
                                       <tr>
                                       <td  width = "53%"></td>
                                            <td width = "47%">
                                               <table>
                                                   <tr style="height:30px">
                                                       <th style="width:35%;font-size:10px" align="left">'.$result->lastName.'</th>    
                                                       <th style="width:43%;font-size:10px" align="left">'.$result->firstName.'</th>  
                                                       <th style="width:10%;font-size:10px" align="left">'.$result->mname.'</th>  
                                                       <th style="width:7%;font-size:10px" align="left">'.$result->suffix.''.'</th>  
                                                   </tr>
                                                   <br>
                                                   <tr style="height:20px">
                                                       <th style="width:5%"></th>
                                                       <th style="width:66%;font-size:10px" align="center">'.$result->purok.", ".$result->brgy." ".$result->cityName.'</th>    
                                                       <th style="width:29%;font-size:10px" align="left">'.$result->contact_number.'</th>
                                                    </tr> 

                                                    <tr style="height:25px">
                                                      <th style="width:33%;font-size:10px;" align="center">'.$result->dob.'</th>    
                                                      <th style="width:33%;font-size:10px;" align="center">'.$result->philhealthid."".'</th> 
                                                      <th style="width:33%;font-size:10px;" align="center">'.$result->cat."".'</th> 
                                                    </tr>
                                                    <br>
                                                    <br>
                                                    
                                                   <tr style="height:10px">
                                                     <th style="width:26%;font-size:9px;"></th>
                                                     <th style="width:3%;;font-size:9px;" align="left">'.$result->fm.'</th>   
                                                     <th style="width:4%;;font-size:9px;" align="left">'.$result->fd.'</th>  
                                                     <th style="width:5%;;font-size:9px;" align="left">'.$result->fy.'</th>  
                                                     <th style="width:25%;font-size:9px;">'.$result->first_dose_vac_manufacturer.' </th>
                                                     <th style="width:15%;font-size:9px;"  align="center">'.$result->first_dose_vac_batch.' </th>
                                                     <th style="width:15%;font-size:9px;"  align="center">'.$result->first_dose_vac_lot.' </th>
                                                    </tr>
                                                 <br>
                                                 <tr style="height:10px">
                                                    <th style="width:26%;font-size:9px;"></th>
                                                    <th style="width:50%;;font-size:10px;" align="center">'.$result->first_dose_vac_vacinator.'</th>
                                                 </tr>
                                                 <br>
                                                 <tr style="height:10px">
                                                  <th style="width:26%;font-size:9px;"></th>
                                                  <th style="width:3%;;font-size:9px;" align="left">'.$result->sm.'</th>   
                                                  <th style="width:4%;;font-size:9px;" align="left">'.$result->sd.'</th>  
                                                  <th style="width:5%;;font-size:9px;" align="left">'.$result->sy.'</th>  
                                                  <th style="width:25%;font-size:9px;">'.$result->second_dose_vac_manufacturer.'</th>
                                                  <th style="width:15%;font-size:9px;"  align="center">'.$result->second_dose_vac_batch.'</th>
                                                  <th style="width:15%;font-size:9px;"  align="center">'.$result->second_dose_vac_lot.'</th>
                                                </tr>
                                                <br>
                                                <tr style="height:10px">
                                                   <th style="width:26%;font-size:9px;" align="center">'.$result->sched.'</th>
                                                   <th style="width:50%;;font-size:10px;" align="center">'.$result->second_dose_vac_vacinator.'</th>
                                                </tr>
                                                <tr style="height:40px">
                                                   <th style="width:17%;font-size:5px;"></th>
                                                   <th style="width:55%;;font-size:10px;height:10px" align="left">'.$result->facility.'</th>
                                                   <th style="width:20%;;font-size:10px;height:10px" align="left">'.$result->facility_no.'</th>
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
           ';
          
            PDF::writeHTML($Template, true, 0, true, 0);
      
            PDF::Output(public_path().'/vac.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
