<?php

namespace App\Http\Controllers\Api\Qr;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\db;
use App\Laravue\JsonResponse;
use App\Http\Controllers\Api\GlobalController;
class dashboardController extends Controller
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
        $this->QRDb = $this->G->getQRDb();
    }


    
    public function showTotal()
    {
        $total = db::select('call getPopulation()');
        return response()->json(new JsonResponse($total));
    }
    public function showPopulationCount(Request $request)
    {
        $filters  = json_decode($request->filter, true);
        // log::debug($filters['client_guid']);
         $query = db::table($this->QRDb.'.shop_representative')
         ->join($this->QRDb.'.household_members', 'household_members.client_guid', '=', 'shop_representative.client_guid')
         ->join($this->QRDb.'.barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
         ->select(DB::raw('count(household_members.id) as count'))
         ->where('household_members.status', '0')
        ->Where('household_members.members_id', 'like', "%".$filters['client_guid']."%")
        ->Where('rep_address', 'like', "%".$filters['rep_address']."%")
        ->Where('brgy_name', 'like', "%".$filters['Barangay']."%")
        ->Where(db::raw('ifnull(household_members.lastName,"")'), 'like', "%".$filters['lastName']."%")
        ->Where(db::raw('ifnull(household_members.firstName,"")'), 'like', "%".$filters['firstName']."%")
        ->Where(db::raw('ifnull(household_members.middleName,"")'), 'like', "%".$filters['middleName']."%")
         ->Where(db::raw('ifnull(household_members.member_type,"")'), 'like', "%".$filters['member_type']."%")
         ->Where(db::raw('ifnull(getAge(household_members.birthdate),"")'), 'like', "%".$filters['age']."%")
         ->Where(db::raw('CAST(CONCAT(IFNULL(groups,""),IFNULL(groups2,""))AS CHAR(225))'), 'like', "%".$filters['groups']."%");

        if ($request->type ==="nobirth") {
            $query->whereNull(db::raw('getAge(household_members.birthdate)'));
        }
        if ($request->type ==="18down") {
            $query ->where(db::raw('getAge(household_members.birthdate)'), '<=', '18');
        }
        if ($request->type ==="mid") {
            $query ->whereBetween(db::raw('getAge(household_members.birthdate)'), [19,59]);
        }
        if ($request->type ==="60up") {
            $query ->where(db::raw('getAge(household_members.birthdate)'), '>=', 60);
        }
        $results = $query->get();
        return response()->json(new JsonResponse($results));
    }
    public function deceased_by_age(){
        $result = db::select('call dash_by_DECEASED_age()');
        return response()->json(new JsonResponse($result));
    }
    public function case_by_age(){
        $result = db::select('call dash_by_age()');
        return response()->json(new JsonResponse($result));
    }
    public function current_status(){
        $result = db::select("SELECT finalRemarks AS 'status',COUNT(*) AS 'count' FROM `covid_entry_main` GROUP BY `finalRemarks`");
        return response()->json(new JsonResponse($result));
    }
    public function deceased_by_gender(){
        $result = db::select("SELECT sex AS 'status',COUNT(*) AS 'count' FROM `covid_entry_main` GROUP BY `sex`");
        return response()->json(new JsonResponse($result));
    }
    public function showPopulationList(Request $request)
    {
        $filters  = json_decode($request->filter, true);
        $query = db::table($this->QRDb.'.shop_representative')
        ->join($this->QRDb.'.household_members', 'household_members.client_guid', '=', 'shop_representative.client_guid')
        ->join($this->QRDb.'.barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
        ->where($this->QRDb.'.household_members.status', '0')
       ->Where('household_members.members_id', 'like', "%".$filters['client_guid']."%")
      ->Where('rep_address', 'like', "%".$filters['rep_address']."%")
       ->Where('brgy_name', 'like', "%".$filters['Barangay']."%")
       ->Where(db::raw('household_members.lastName'), 'like', "%".$filters['lastName']."%")
       ->Where(db::raw('household_members.firstName'), 'like', "%".$filters['firstName']."%")
       ->Where(db::raw('ifnull(household_members.middleName,"")'), 'like', "%".$filters['middleName']."%")
        ->Where(db::raw('ifnull(household_members.member_type,"")'), 'like', "%".$filters['member_type']."%")
        ->Where(db::raw('ifnull(getAge(household_members.birthdate),"")'), 'like', "%".$filters['age']."%")
        ->Where(db::raw('CAST(CONCAT(IFNULL(groups,""),IFNULL(groups2,""))AS CHAR(225))'), 'like', "%".$filters['groups']."%");
        
        if ($request->type ==="nobirth") {
            $query->whereNull(db::raw('getAge(household_members.birthdate)'));
        }
        if ($request->type ==="18down") {
            $query ->where(db::raw('getAge(household_members.birthdate)'), '<=', '18');
        }
        if ($request->type ==="mid") {
            $query ->whereBetween(db::raw('getAge(household_members.birthdate)'), [19,59]);
        }
        if ($request->type ==="60up") {
            $query ->where(db::raw('getAge(household_members.birthdate)'), '>=', 60);
        }
        $query->select('household_members.client_guid','household_members.id',db::raw('CONCAT(IFNULL(groups,""),IFNULL(groups2,"")) as groups'),db::raw('ifnull(prefix,"") as prefix'),db::raw('ifnull(suffix,"") as suffix') ,'household_members.id', 'brgy_name', 'rep_address','gender','contact_number','birthdate','rep_brgy', 'firstName', 'middleName', 'lastName', 'member_type', db::raw($this->QRDb.'.getAge(household_members.birthdate)*1 as age'));
        if ($request->descending ==="true") {
            $query->orderBy($request->sortBy, 'desc');
        } else {
            $query->orderBy($request->sortBy, 'asc');
        }
        $query ->skip($request->startRow)->take(15);
        $results = $query->get();
        return response()->json(new JsonResponse($results));
    }
    public function showPopulationListPerBrgy(Request $request){
        log::debug($request);
        $filters  = $request->filter;
        $query = db::table('shop_representative')
        ->join('household_members', 'household_members.client_guid', '=', 'shop_representative.client_guid')
        ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
        ->where('household_members.status', '0')
        ->where('shop_representative.rep_brgy',$request->brgyid)
       ->Where('household_members.members_id', 'like', "%".$filters['client_guid']."%")
       ->Where('rep_address', 'like', "%".$filters['rep_address']."%")
       ->Where('brgy_name', 'like', "%".$filters['Barangay']."%")
       ->Where(db::raw('ifnull(household_members.lastName,"")'), 'like', "%".$filters['lastName']."%")
       ->Where(db::raw('ifnull(household_members.firstName,"")'), 'like', "%".$filters['firstName']."%")
       ->Where(db::raw('ifnull(household_members.middleName,"")'), 'like', "%".$filters['middleName']."%")
        ->Where(db::raw('ifnull(household_members.member_type,"")'), 'like', "%".$filters['member_type']."%")
        ->Where(db::raw('ifnull(getAge(household_members.birthdate),"")'), 'like', "%".$filters['age']."%")
        ->Where(db::raw('CONCAT(IFNULL(groups,""),IFNULL(groups2,""))'), 'like', "%".$filters['groups']."%");
      
        if ($request->type ==="NO BIRTH") {
            $query->whereNull(db::raw('getAge(household_members.birthdate)'));
        }
        if ($request->type ==="0-18") {
            $query ->where(db::raw('getAge(household_members.birthdate)'), '<=', '18');
        }
        if ($request->type ==="18-60") {
            $query ->whereBetween(db::raw('getAge(household_members.birthdate)'), [19,59]);
        }
        if ($request->type ==="60 UP") {
            $query ->where(db::raw('getAge(household_members.birthdate)'), '>=', 60);
        }
        $query->select('household_members.client_guid',db::raw('CONCAT(IFNULL(groups,""),IFNULL(groups2,"")) as groups'),'prefix','suffix','contact_number','gender', 'brgy_name', 'rep_address', 'firstName', 'middleName', 'lastName', 'member_type', db::raw('getAge(household_members.birthdate)*1 as age'));
        if ($request->descending ==="true") {
            $query->orderBy($request->sortBy, 'desc');
        } else {
            $query->orderBy($request->sortBy, 'asc');
        }
        // $query ->skip($request->startRow)->take(15);
        $results = $query->get();
        return response()->json(new JsonResponse($results));
    }
    public function barangay(){
        $results  = db::table('barangay')
        ->select('id','brgy_name')->get();
        return response()->json(new JsonResponse($results));
    }
    public function barangayPopulation($id){
        $results  = db::select('call getPopulationPerbrgy(?)',[$id]);
        return response()->json(new JsonResponse($results));
    }
    public function updateGroup(Request $request){
   $group =  $request->groups;
   if ($group === 'UCT') {
    db::table('household_members')->where('id', $request->id)->update(['groups'=> 'UCT','groups2'=>'']);
   }elseif ($group === 'PANTAWID'){
    db::table('household_members')->where('id', $request->id)->update(['groups2'=> 'PANTAWID','groups'=>'']);
   }else{
    db::table('household_members')->where('id', $request->id)->update(['groups2'=> '','groups'=>'']);
   }
 
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
     
    
}
