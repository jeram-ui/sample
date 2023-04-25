<?php

namespace App\Http\Controllers\Api\Qr;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\db;
use App\Laravue\JsonResponse;
use PDF;
use App\Http\Controllers\Api\GlobalController;
class readerController extends Controller
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
        $this->myr_db = $this->G->getMayorsDb();
        $this->qr_db = $this->G->getQRDb();
    }
    public function show($id)
    {
        try {
            DB::beginTransaction();
            $data['result'] = db::table( $this->qr_db.'.household_members')->where('client_guid', $id)->where('status', 0)->get();
            $data['info']=  db::table($this->qr_db.'.shop_representative')
            ->join($this->qr_db.'.barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
            ->select('brgy_name','rep_address','rep_brgy')
            ->where('client_guid', $id)->first();
            DB::commit();
            return response()->json(new JsonResponse($data));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function validated(Request $request)
    {
        // log::debug($request->main['id']);
        $main = $request->main;
        $location = $request->location;
        $data = array(
            'uid'=>  Auth::user()->id,
            'members_id'=>$main['id'],
            'member_guid'=>$main['client_guid'],
            'lat'=>$location['lat'],
            'long'=>$location['long'],
        );
        db::table('pass')->insert($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'sucess']));
    }
    public function validatedBag(Request $request)
    {
        // log::debug($request->main['id']);
        $main = $request->main;
        $location = $request->location;
        $data = array(
            'uid'=>  Auth::user()->id,
            'members_id'=>$main['id'],
            'member_guid'=>$main['client_guid'],
            'lat'=>$location['lat'],
            'long'=>$location['long'],
        );
        db::table('pass_ecobag')->insert($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'sucess']));
    }
    public function history(Request $request)
    {
        $result = db::table('pass')
        ->join('household_members', 'household_members.id', '=', 'pass.members_id')
        ->join('shop_representative', 'shop_representative.client_guid', '=', 'household_members.client_guid')
        ->join('users', 'users.id', '=', 'pass.uid')
        ->join('facility', 'facility.id', '=', 'users.facility_id')
        ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
        ->leftJoin('department', 'department.id', '=', 'pass.department_id')
        ->select('household_members.id', 'household_members.lastName', 'household_members.firstName', 'ts', 'users.name', 'pass.id as idx', 'barangay.brgy_name', db::raw('if(pass.department_id > 0,department.department,facility.facility_name )as facility'))
        ->whereBetween(db::raw('date(pass.ts)'), [$request->from,$request->to])
        ->where('facility.id', $request->facility)
        ->orderBy('pass.id', 'desc')
        ->get();
        return response()->json(new JsonResponse($result));
    }
    public function dashboard(Request $request)
    {
        $data['facility'] = db::table('pass')
        ->join('users', 'users.id', '=', 'pass.uid')
        ->join('facility', 'facility.id', '=', 'users.facility_id')
        ->join('shop_representative', 'shop_representative.client_guid', '=', 'pass.member_guid')
        ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
        ->select(
            'barangay.brgy_name',
            'facility.facility_name as facility',
            DB::raw('count(*) as count'),
            db::raw("SUM(CASE WHEN TIME(pass.`ts`)BETWEEN '06:00:00' AND '12:00:00' THEN 1 ELSE 0 END) as '6-12AM'"),
            db::raw("SUM(CASE WHEN TIME(pass.`ts`)BETWEEN '12:00:00' AND '18:00:00' THEN 1 ELSE 0 END) as '12-6PM'"),
            db::raw("SUM(CASE WHEN TIME(pass.`ts`)BETWEEN '18:00:00' AND '24:00:00' THEN 1 ELSE 0 END) as '6-12PM'")
        )
        ->where('facility.id', $request->facility)
        ->whereBetween(db::raw('date(pass.ts)'), [$request->from,$request->to])
        ->groupBy(DB::raw('barangay.id'))
        ->get();

        $data['summary'] = db::table('pass')
        ->join('users', 'users.id', '=', 'pass.uid')
        ->join('facility', 'facility.id', '=', 'users.facility_id')
        ->leftJoin('department', 'department.id', '=', 'pass.department_id')
        ->select(
            db::raw('concat(facility.facility_name,"  ",ifnull(department.department,"")) as facility'),
            db::raw('count(pass.id) as count')
        )
        ->whereBetween(db::raw('date(pass.ts)'), [$request->from,$request->to])
        ->groupBy('facility.id', 'pass.department_id')
        ->get();
        return response()->json(new JsonResponse($data));
    }
    public function dashboardEcobag(Request $request)
    {
        $data['facility'] = db::table('pass_ecobag')
        ->join('users', 'users.id', '=', 'pass_ecobag.uid')
        ->join('facility', 'facility.id', '=', 'users.facility_id')
        ->join('shop_representative', 'shop_representative.client_guid', '=', 'pass_ecobag.member_guid')
        ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
        ->select(
            'barangay.brgy_name',
            'facility.facility_name as facility',
            DB::raw('count(*) as count'),
            db::raw("SUM(CASE WHEN TIME(pass_ecobag.`ts`)BETWEEN '06:00:00' AND '12:00:00' THEN 1 ELSE 0 END) as '6-12AM'"),
            db::raw("SUM(CASE WHEN TIME(pass_ecobag.`ts`)BETWEEN '12:00:00' AND '18:00:00' THEN 1 ELSE 0 END) as '12-6PM'"),
            db::raw("SUM(CASE WHEN TIME(pass_ecobag.`ts`)BETWEEN '18:00:00' AND '24:00:00' THEN 1 ELSE 0 END) as '6-12PM'")
        )
        ->where('facility.id', $request->facility)
        ->whereBetween(db::raw('date(pass_ecobag.ts)'), [$request->from,$request->to])
        ->groupBy(DB::raw('barangay.id'))
        ->get();

        $data['summary'] = db::table('pass_ecobag')
        ->join('users', 'users.id', '=', 'pass_ecobag.uid')
        ->join('facility', 'facility.id', '=', 'users.facility_id')
        ->leftJoin('department', 'department.id', '=', 'pass_ecobag.department_id')
        ->select(
            db::raw('concat(facility.facility_name,"  ",ifnull(department.department,"")) as facility'),
            db::raw('count(pass_ecobag.id) as count')
        )
        ->whereBetween(db::raw('date(pass_ecobag.ts)'), [$request->from,$request->to])
        ->groupBy('facility.id', 'pass_ecobag.department_id')
        ->get();
        return response()->json(new JsonResponse($data));
    }
    public function details($id)
    {
        $result = db::table('pass')
        ->join('household_members', 'household_members.id', '=', 'pass.members_id')
        ->join('users', 'users.id', '=', 'pass.uid')
        ->select('household_members.id', 'household_members.lastName', 'household_members.firstName', 'ts', 'users.name', 'pass.id as idx')
        ->where('pass.member_guid', $id)
        ->orderBy('pass.id', 'desc')
        ->get();
        return response()->json(new JsonResponse($result));
    }
    public function NamePerBarangay($id)
    {
        $result = db::table('household_members')
       ->join('shop_representative', 'shop_representative.client_guid', '=', 'household_members.client_guid')
       ->where('shop_representative.rep_brgy', $id)
       ->select('household_members.lastName', 'household_members.firstName', 'household_members.client_guid')
       ->get();
        return response()->json(new JsonResponse($result));
    }
    
    public function barangay()
    {
        $result = db::table('barangay')
        ->join('shop_representative', 'shop_representative.rep_brgy', '=', 'barangay.id')
        ->join('household_members', 'household_members.client_guid', '=', 'shop_representative.client_guid')
        ->select('barangay.*', DB::raw('count(shop_representative.rep_id) as housecount'))
        ->groupBy('barangay.id')
        ->orderBy('brgy_name')
        ->get();
        return response()->json(new JsonResponse($result));
    }
    public function getPurok($id)
    {
        $result = db::table('barangay')
        ->join('shop_representative', 'shop_representative.rep_brgy', '=', 'barangay.id')
        ->where('shop_representative.rep_brgy', $id)
        ->select('shop_representative.rep_address as purok', 'shop_representative.rep_address as id')
        ->groupBy('shop_representative.rep_address')
        ->orderBy('rep_address')
        ->get();
        return response()->json(new JsonResponse($result));
    }
    public function LeaderPerBarangay(Request $request)
    {
        // $result = db::table('household_members')
        // ->join('shop_representative', 'shop_representative.client_guid', '=', 'household_members.client_guid')
        // ->join('barangay', 'barangay.id', 'shop_representative.rep_brgy')
        // // ->leftJoin()
        // ->where('shop_representative.rep_brgy', $request->brgyid)
        // ->where('shop_representative.rep_address', 'like', $request->prkid."%")
        // ->where('household_members.member_type', 'Puno ng Pamilya')

        // ->select('shop_representative.rep_id', 'household_members.lastName', 'household_members.firstName', 'rep_address', 'household_members.client_guid as idx', 'household_members.client_guid', 'household_members.id', 'shop_representative.rep_brgy', 'barangay.brgy_name')
        // ->get();;
        // log::debug([$request->brgyid,$request->prkid]);
        $result = db::select('call getBarangayMembers(?,?)', [$request->brgyid,$request->prkid]);
        return response()->json(new JsonResponse($result));
    }
    public function checkID($id)
    {
        $result = db::table('shop_representative')->where('client_guid', $id)->get();
        return response()->json(new JsonResponse($result));
    }
    public function GetRelationship()
    {
        $result = db::table('relationship')->get();
        return response()->json(new JsonResponse($result));
    }
    public function removeMember($id)
    {
        $data = array('status'=>1);
        db::table('household_members')->where('id', $id)->update($data);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function checkingname(Request $request)
    {
        $result = db::table('household_members')
        ->where('lastName', $request->lastName)
        ->where('firstName', $request->firstName)
        ->where('status', 0)
        ->get();
        return response()->json(new jsonresponse($result));
    }
    public function saveMember(Request $request)
    {
        $datax = $request->data;
        $datax['uid'] = Auth::user()->id;
        try {
            DB::beginTransaction();
            $check = db::table('shop_representative')
        ->select(db::raw('count(client_guid) as count'))
        ->where('client_guid', $request->data['client_guid'])
        ->first()->count
        ;

            if ($check == 0) {
                $data = array(
                'client_guid'=>$request->data['client_guid'],
                'rep_address'=>$request->main['rep_address'],
                'rep_brgy'=>$request->main['rep_brgy'],
            );
                db::table('shop_representative')->insert($data);
                db::table('household_members')->insert($datax);
            } else {
                db::table('household_members')->insert($datax);
            }
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getMember($id)
    {
        $result = db::table('household_members')->where('members_id', $id)
        ->join('shop_representative', 'shop_representative.client_guid', '=', 'household_members.client_guid')
        ->join('relationship', 'relationship.description', '=', 'household_members.member_type')
        ->select('household_members.*', 'shop_representative.rep_address')
        ->where('household_members.status', 0)
        ->get();
        return response()->json(new jsonresponse($result));
    }
    public function UpdateMember(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->data;
            $guid = "";
            foreach ($data as $key => $row) {
                $row['uid'] = Auth::user()->id;
            
                unset($row['rep_address']);
                db::table('household_members')->where('id', $row['id'])->update($row);
                $guid =  $row['client_guid'];
            }
        
            $main = array(
                    'rep_address'=>$request->main['rep_address']
                );
            db::table('shop_representative')->where('client_guid', $guid)->update($main);
        
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function getRef($id)
    {
        $result = db::table('shop_representative')
    ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
    ->select('rep_brgy', 'rep_address', db::raw('CONCAT(barangay.`brgy_code`,"-" ,LPAD(COUNT(*) + 1,4,0)) AS client_guid'), 'barangay.brgy_name')
    ->where('rep_brgy', $id)->get();
        return response()->json(new jsonresponse($result));
    }
    public function Departments(Request $request)
    {
        try {
            $post = db::table('department')->get();
            return response()->json(new jsonresponse($post));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printSample($id)
    {
        try {
            PDF::SetTitle('Access Pass');
            PDF::SetHeaderMargin(1);
            PDF::SetTopMargin(1);
            PDF::SetMargins(2, 2, 2, 2);
            PDF::SetFont('Helvetica', '', 10);
            $dataMain = db::select("SELECT 
            CONCAT(`lastName`,', ',`firstName`,' ',ifnull(middleName,''),' ',ifnull(suffix,'')) AS 'Name',
            `barangay`.`brgy_captain` AS 'captain',
            shop_representative.`client_guid` AS 'code',
            barangay.`brgy_name` AS 'barangay',
            `shop_representative`.`rep_address` as 'purok'
            FROM `household_members`
            INNER JOIN `shop_representative` 
            ON(household_members.`client_guid` = shop_representative.`client_guid`)
            INNER JOIN `barangay` ON(barangay.`id` = shop_representative.`rep_brgy`)
            WHERE `shop_representative`.`rep_brgy` = $id 
            and household_members.status = 0
            and household_members.member_type ='Puno ng Pamilya'
            ");

            cons: $result = array_splice($dataMain, 0, 4);
            // PDF::AddPage('P');
            PDF::AddPage('P', array(215.9,330.2));
            // -- set new background ---

            $bMargin = PDF::getBreakMargin();
            $auto_page_break = PDF::getAutoPageBreak();
            PDF::SetAutoPageBreak(false, 0);
            $img_file = public_path().'/BLANK 3.png';
            PDF::Image($img_file, 0, 0, 215.9, 330.2, '', '', '', false, 300, '', false, false, 0);
            PDF::SetAutoPageBreak($auto_page_break, $bMargin);
            PDF::setPageMark();
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            $params1 = PDF::serializeTCPDFtagParameters(array(array_key_exists(0, $result) == true ? $result[0]->code : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $params2 = PDF::serializeTCPDFtagParameters(array(array_key_exists(1, $result) == true ? $result[1]->code : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $params3 = PDF::serializeTCPDFtagParameters(array(array_key_exists(2, $result) == true ? $result[2]->code : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $params4 = PDF::serializeTCPDFtagParameters(array(array_key_exists(3, $result) == true ? $result[3]->code : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
          
            // $mask =PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', '', '', false, 300, '', true);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', 'http://www.tcpdf.org', '', false, 300, '', false, $mask);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 105, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 35, 245, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 245, 40, 40, '', '', '', true, 72);
        
            $Template = '
                    <table width ="100%"  cellpadding ="2" >
                        <tr>
                            <td width = "50%" >
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                                <table cellpadding ="1" >
                                    <tr>
                                       <th style="width:67%;">
                                           <table>
                                              <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]->barangay : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                               <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]->purok : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                                   <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                   <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                    <th style="width:11%"></th>
                                                    <th style="width:70%;font-size:15px" align="center"><b> HN: '.(array_key_exists(0, $result) == true ? $result[0]->code : '').'</b></th>    
                                                    <th style="width:15%"></th>
                                                </tr>
                                            </table>
                                       </th>
                                       <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params1.'" /></th>          
                                    </tr>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <tr>
                                        <th colspan ="2"> 
                                            <table width ="100%">
                                               <tr>
                                                    <td width = "100%">
                                                       <table>
                                                           <tr style="height:25px">
                
                                                               <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]->Name : '').'</b></th>    
                                                          
                                                           </tr>
                                                           <tr style="height:25px">
                                                               <th style="width:15%"></th>
                                                               <th style="width:70%; border-top:0.5px solid black;" align="center">Household Head</th>    
                                                               <th style="width:5%"></th>
                                                            </tr> 
                                                    
                                                            <br>
                                                            <tr style="height:25px">
                                                           
                                                            <th style="width:100%;font-size:15px;" align="center">
                                                            <b>'.(array_key_exists(0, $result) == true ? $result[0]->captain : '').'</b>
                                                            </th>    
                                                   
                                                             </tr>

                                                           <tr style="height:25px">
                                                             <th style="width:17%"></th>
                                                             <th style="width:70%;" align="center">Punong Barangay</th>   
                                                             <th style="width:3%"></th>
                                                         </tr>
                                                       </table>
                                                   </td>
                                                </tr> 
                                            </table>
                                        </th>
                                    </tr>
                                </table>
                            </td>
                            <td width = "50%" >
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                         
                                <table cellpadding ="1" >
                                    <tr>
                                       <th style="width:67%;">
                                           <table>
                                              <tr style="height:25px;border-top:0.5px solid black;font-size:17px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]->barangay : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                                  <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                  <th style="width:11%"></th>
                                                  <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]->purok : '').'</b></th>    
                                                  <th style="width:15%"></th>
                                               </tr>
                                                <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                  <th style="width:11%"></th>
                                                  <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                  <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                    <th style="width:11%"></th>
                                                    <th style="width:70%;font-size:15px" align="center"><b>HN: '.(array_key_exists(1, $result) == true ? $result[1]->code : '').'</b></th>    
                                                    <th style="width:15%"></th>
                                                </tr>
                                            </table>
                                       </th>
                                       <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params2.'" /></th>          
                                       </tr>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <tr>
                                        <th colspan ="2"> 
                                            <table width ="100%">
                                               <tr>
                                                    <td width = "100%">
                                                       <table>
                                                           <tr style="height:25px">
                                                              
                                                            <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]->Name : '').'</b></th>    
                                                         
                                                           </tr>
                                                           <tr style="height:25px">
                                                               <th style="width:14%"></th>
                                                               <th style="width:70%;border-top:0.5px solid black" align="center">Household Head</th>    
                                                               <th style="width:6%"></th>
                                                            </tr> 
                                                            <br>
                                                            <tr style="height:25px">
                                                            
                                                              <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]->captain : '').'</b></th>    
                                                            
                                                            </tr>
                                                           <tr style="height:25px">
                                                             <th style="width:17%"></th>
                                                             <th style="width:70%;" align="center">Punong Barangay</th>     
                                                             <th style="width:3%"></th>
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
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                
                
                ';
        
            $Template .= '
                <table width ="100%"  cellpadding ="2" >
                    <tr>
                        <td width = "50%" >
                        <br>
                        <br>
                        <br>
                        <br>
                        <br/>
                        <br/>
                        <br>
                            <table cellpadding ="1" >
                                <tr>
                                   <th style="width:67%;">
                                       <table>
                                          <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]->barangay : '').'</b></th>    
                                               <th style="width:15%"></th>
                                           </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                           <th style="width:15%"></th>
                                           </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]->purok : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                                <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                <th style="width:15%"></th>
                                            </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:15px" align="center"><b>HN: '.(array_key_exists(2, $result) == true ? $result[2]->code : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                       
                                        </table>
                                   </th>
                                   <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params3.'" /></th>          
                                   </tr>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <tr>
                                    <th colspan ="2"> 
                                        <table width ="100%">
                                           <tr>
                                                <td width = "100%">
                                                   <table>
                                                       <tr style="height:25px">
                                                         
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]->Name : '').'</b></th>    
                                                     
                                                       </tr>
                                                       <tr style="height:25px">
                                                           <th style="width:15%"></th>
                                                           <th style="width:70%;border-top:0.5px solid black" align="center">Household Head</th>       
                                                           <th style="width:5%"></th>
                                                        </tr> 
                                                        <br>    
                                                        <tr style="height:25px">
                                                       
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]->captain : '').'</b></th>    
                                                 
                                                    </tr>
                                                    <tr style="height:25px">
                                                        <th style="width:17%"></th>
                                                        <th style="width:70%;" align="center">Punong Barangay</th>   
                                                        <th style="width:3%"></th>
                                                     </tr>
                                                   </table>
                                               </td>
                                            </tr> 
                                        </table>
                                    </th>
                                </tr>
                            </table>
                        </td>
                        <td width = "50%" >
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                            <table cellpadding ="1" >
                                <tr>
                                   <th style="width:67%;">
                                       <table>
                                          <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]->barangay : '').'</b></th>    
                                               <th style="width:15%"></th>
                                           </tr>
                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                           <th style="width:15%"></th>
                                           </tr>
                                           
                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]->purok : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                            <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                <th style="width:15%"></th>
                                            </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:15px" align="center"><b>HN: '.(array_key_exists(3, $result) == true ? $result[3]->code : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                          
                                        </table>
                                   </th>
                                   <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params4.'" /></th>          
                                   </tr>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <tr>
                                    <th colspan ="2"> 
                                        <table width ="100%">
                                           <tr>
                                                <td width = "100%">
                                                   <table>
                                                       <tr style="height:25px">
                                                   
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]->Name : '').'</b></th>    
                                                        
                                                       </tr>
                                                       <tr style="height:25px">
                                                           <th style="width:14%"></th>
                                                           <th style="width:70%;border-top:0.5px solid black" align="center">Household Head</th>    
                                                           <th style="width:6%"></th>
                                                        </tr> 
                                                        <br>    
                                                        <tr style="height:25px">
                                                    
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]->captain : '').'</b></th>    
                                                       
                                                    </tr>
                                                    <tr style="height:25px">
                                                        <th style="width:17%"></th>
                                                        <th style="width:70%;" align="center">Punong Barangay</th>     
                                                        <th style="width:3%"></th>
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
            ';
            PDF::writeHTML($Template, true, 0, true, 0);
            if (count($dataMain) > 0) {
                goto cons;
            }
        
            PDF::Output(public_path().'/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function printSampleIndividual($id)
    {
        try {
            PDF::SetTitle('Access Pass');
            PDF::SetHeaderMargin(1);
            PDF::SetTopMargin(1);
            PDF::SetMargins(2, 2, 2, 2);
            PDF::SetFont('Helvetica', '', 10);
            $dataMain = db::select("SELECT 
            CONCAT(`lastName`,', ',`firstName`,' ',ifnull(middleName,''), ' ',ifnull(suffix,'')) AS 'Name',
            `barangay`.`brgy_captain` AS 'captain',
            shop_representative.`client_guid` AS 'code',
            barangay.`brgy_name` AS 'barangay',
            `shop_representative`.`rep_address` as 'purok'
            FROM `household_members`
            INNER JOIN `shop_representative` 
            ON(household_members.`client_guid` = shop_representative.`client_guid`)
            INNER JOIN `barangay` ON(barangay.`id` = shop_representative.`rep_brgy`)
            WHERE `shop_representative`.`client_guid` = '".$id."' and member_type = 'Puno ng Pamilya'
            and household_members.status = 0
            ");

            $result = $dataMain;
            // log::debug($result);
            // PDF::AddPage('P');
            PDF::AddPage('P', array(215.9,330.2));
            // -- set new background ---

            $bMargin = PDF::getBreakMargin();
            $auto_page_break = PDF::getAutoPageBreak();
            PDF::SetAutoPageBreak(false, 0);
            $img_file = public_path().'/1.png';
            PDF::Image($img_file, 0, 0, 215.9, 330.2, '', '', '', false, 300, '', false, false, 0);
            PDF::SetAutoPageBreak($auto_page_break, $bMargin);
            PDF::setPageMark();
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            $params1 = PDF::serializeTCPDFtagParameters(array(array_key_exists(0, $result) == true ? $result[0]->code : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            // $mask =PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', '', '', false, 300, '', true);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', 'http://www.tcpdf.org', '', false, 300, '', false, $mask);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 105, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 35, 245, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 245, 40, 40, '', '', '', true, 72);
        
            $Template = '
                    <table width ="100%"  cellpadding ="2" >
                        <tr>
                            <td width = "50%" >
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                                <table cellpadding ="1" >
                                    <tr>
                                       <th style="width:67%;">
                                           <table>
                                              <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]->barangay : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                               <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]->purok : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                                   <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                   <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                    <th style="width:11%"></th>
                                                    <th style="width:70%;font-size:15px" align="center"><b> HN: '.(array_key_exists(0, $result) == true ? $result[0]->code : '').'</b></th>    
                                                    <th style="width:15%"></th>
                                                </tr>
                                            </table>
                                       </th>
                                       <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params1.'" /></th>          
                                    </tr>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <tr>
                                        <th colspan ="2"> 
                                            <table width ="100%">
                                               <tr>
                                                    <td width = "100%">
                                                       <table>
                                                           <tr style="height:25px">
                                                              
                                                               <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]->Name : '').'</b></th>    
                                                           
                                                           </tr>
                                                           <tr style="height:25px">
                                                               <th style="width:15%"></th>
                                                               <th style="width:70%;border-top:0.5px solid black;" align="center">Household Head</th>    
                                                               <th style="width:5%"></th>
                                                            </tr> 
                                                    
                                                            <br>
                                                            <tr style="height:25px">
                                                            
                                                            <th style="width:100%;font-size:15px;" align="center">
                                                            <b>'.(array_key_exists(0, $result) == true ? $result[0]->captain : '').'</b>
                                                            </th>    
                                                             
                                                             </tr>

                                                           <tr style="height:25px">
                                                             <th style="width:17%"></th>
                                                             <th style="width:70%;" align="center">Punong Barangay</th>   
                                                             <th style="width:3%"></th>
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
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                
                
                ';
        
          
            PDF::writeHTML($Template, true, 0, true, 0);
      
            PDF::Output(public_path().'/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
    public function PrintMultiple(Request $request)
    {
        try {
            PDF::SetTitle('Access Pass');
            PDF::SetHeaderMargin(1);
            PDF::SetTopMargin(1);
            PDF::SetMargins(2, 2, 2, 2);
            PDF::SetFont('Helvetica', '', 10);
            $dataMain = db::table('household_members')
           ->join('shop_representative', 'shop_representative.client_guid', '=', 'household_members.client_guid')
           ->join('barangay', 'barangay.id', '=', 'shop_representative.rep_brgy')
           ->select(
               db::raw('CONCAT(`lastName`,", ",`firstName`," ",ifnull(middleName,"")," ",ifnull(suffix,"")) Name'),
               'barangay.brgy_captain AS captain',
               'shop_representative.client_guid AS code',
               'barangay.brgy_name AS barangay',
               'shop_representative.rep_address as purok'
           )
           ->where('member_type', 'Puno ng Pamilya')
           ->where('status', 0)
           ->whereIn('shop_representative.client_guid', $request->main)
           ->get();

            //    $dataMain = db::select("SELECT
            //    CONCAT(`lastName`,', ',`firstName`,' ',ifnull(middleName,''),' ',ifnull(suffix,'')) AS 'Name',
            //    `barangay`.`brgy_captain` AS 'captain',
            //    shop_representative.`client_guid` AS 'code',
            //    barangay.`brgy_name` AS 'barangay',
            //    `shop_representative`.`rep_address` as 'purok'
            //    FROM `household_members`
            //    INNER JOIN `shop_representative`
            //    ON(household_members.`client_guid` = shop_representative.`client_guid`)
            //    INNER JOIN `barangay` ON(barangay.`id` = shop_representative.`rep_brgy`)
            //    and household_members.status = 0
            //    and household_members.member_type ='Puno ng Pamilya'
            //    and shop_representative.client_guid in(?)
            //    ",[ $request->main]);
            
            // log::debug(json_decode($dataMain, true));
            $dataMain = json_decode($dataMain, true);
            cons: $result = array_splice($dataMain, 0, 4);
            log::debug($result);
            // PDF::AddPage('P');
            // log::debug(array_key_exists(0, $result) == true ? $result[0]['code'] : 'asd');
            PDF::AddPage('P', array(215.9,330.2));
            // -- set new background ---

            $bMargin = PDF::getBreakMargin();
            $auto_page_break = PDF::getAutoPageBreak();
            PDF::SetAutoPageBreak(false, 0);
            $img_file = public_path().'/BLANK 3.png';
            PDF::Image($img_file, 0, 0, 215.9, 330.2, '', '', '', false, 300, '', false, false, 0);
            PDF::SetAutoPageBreak($auto_page_break, $bMargin);
            PDF::setPageMark();
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            $params1 = PDF::serializeTCPDFtagParameters(array(array_key_exists(0, $result) == true ? $result[0]['code'] : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $params2 = PDF::serializeTCPDFtagParameters(array(array_key_exists(1, $result) == true ? $result[1]['code'] : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $params3 = PDF::serializeTCPDFtagParameters(array(array_key_exists(2, $result) == true ? $result[2]['code'] : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
            $params4 = PDF::serializeTCPDFtagParameters(array(array_key_exists(3, $result) == true ? $result[3]['code'] : '', 'QRCODE,H','', '', 25, 25, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
          
            // $mask =PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', '', '', false, 300, '', true);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 50, 140, 100, '', '', 'http://www.tcpdf.org', '', false, 300, '', false, $mask);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 105, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 35, 245, 40, 40, '', '', '', true, 72);
            // PDF::Image(public_path().(array_key_exists(0, $result) == true ? $result[0]->sig : ''), 130, 245, 40, 40, '', '', '', true, 72);
        
            $Template = '
                    <table width ="100%"  cellpadding ="2" >
                        <tr>
                            <td width = "50%" >
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                               <br>
                                <table cellpadding ="1" >
                                    <tr>
                                       <th style="width:67%;">
                                           <table>
                                              <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]['barangay'] : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                               <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]['purok'] : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                                   <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                   <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                    <th style="width:11%"></th>
                                                    <th style="width:70%;font-size:15px" align="center"><b> HN: '.(array_key_exists(0, $result) == true ? $result[0]['code'] : '').'</b></th>    
                                                    <th style="width:15%"></th>
                                                </tr>
                                            </table>
                                       </th>
                                       <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params1.'" /></th>          
                                    </tr>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <tr>
                                        <th colspan ="2"> 
                                            <table width ="100%">
                                               <tr>
                                                    <td width = "100%">
                                                       <table>
                                                           <tr style="height:25px">
                
                                                               <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(0, $result) == true ? $result[0]['Name'] : '').'</b></th>    
                                                          
                                                           </tr>
                                                           <tr style="height:25px">
                                                               <th style="width:15%"></th>
                                                               <th style="width:70%; border-top:0.5px solid black;" align="center">Household Head</th>    
                                                               <th style="width:5%"></th>
                                                            </tr> 
                                                    
                                                            <br>
                                                            <tr style="height:25px">
                                                           
                                                            <th style="width:100%;font-size:15px;" align="center">
                                                            <b>'.(array_key_exists(0, $result) == true ? $result[0]['captain'] : '').'</b>
                                                            </th>    
                                                   
                                                             </tr>

                                                           <tr style="height:25px">
                                                             <th style="width:17%"></th>
                                                             <th style="width:70%;" align="center">Punong Barangay</th>   
                                                             <th style="width:3%"></th>
                                                         </tr>
                                                       </table>
                                                   </td>
                                                </tr> 
                                            </table>
                                        </th>
                                    </tr>
                                </table>
                            </td>
                            <td width = "50%" >
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                         
                                <table cellpadding ="1" >
                                    <tr>
                                       <th style="width:67%;">
                                           <table>
                                              <tr style="height:25px;border-top:0.5px solid black;font-size:17px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]['barangay'] : '').'</b></th>    
                                                   <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                   <th style="width:11%"></th>
                                                   <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                                  <th style="width:15%"></th>
                                               </tr>
                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                  <th style="width:11%"></th>
                                                  <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]['purok'] : '').'</b></th>    
                                                  <th style="width:15%"></th>
                                               </tr>
                                                <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                  <th style="width:11%"></th>
                                                  <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                  <th style="width:15%"></th>
                                               </tr>

                                               <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                    <th style="width:11%"></th>
                                                    <th style="width:70%;font-size:15px" align="center"><b>HN: '.(array_key_exists(1, $result) == true ? $result[1]['code'] : '').'</b></th>    
                                                    <th style="width:15%"></th>
                                                </tr>
                                            </table>
                                       </th>
                                       <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params2.'" /></th>          
                                       </tr>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <br>
                                       <tr>
                                        <th colspan ="2"> 
                                            <table width ="100%">
                                               <tr>
                                                    <td width = "100%">
                                                       <table>
                                                           <tr style="height:25px">
                                                              
                                                            <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]['Name'] : '').'</b></th>    
                                                         
                                                           </tr>
                                                           <tr style="height:25px">
                                                               <th style="width:14%"></th>
                                                               <th style="width:70%;border-top:0.5px solid black" align="center">Household Head</th>    
                                                               <th style="width:6%"></th>
                                                            </tr> 
                                                            <br>
                                                            <tr style="height:25px">
                                                            
                                                              <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(1, $result) == true ? $result[1]['captain'] : '').'</b></th>    
                                                            
                                                            </tr>
                                                           <tr style="height:25px">
                                                             <th style="width:17%"></th>
                                                             <th style="width:70%;" align="center">Punong Barangay</th>     
                                                             <th style="width:3%"></th>
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
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                   <br/>
                
                
                ';
        
            $Template .= '
                <table width ="100%"  cellpadding ="2" >
                    <tr>
                        <td width = "50%" >
                        <br>
                        <br>
                        <br>
                        <br>
                        <br/>
                        <br/>
                        <br>
                            <table cellpadding ="1" >
                                <tr>
                                   <th style="width:67%;">
                                       <table>
                                          <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]['barangay'] : '').'</b></th>    
                                               <th style="width:15%"></th>
                                           </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                           <th style="width:15%"></th>
                                           </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]['purok'] : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                                <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                <th style="width:15%"></th>
                                            </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:15px" align="center"><b>HN: '.(array_key_exists(2, $result) == true ? $result[2]['code'] : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                       
                                        </table>
                                   </th>
                                   <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params3.'" /></th>          
                                   </tr>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <tr>
                                    <th colspan ="2"> 
                                        <table width ="100%">
                                           <tr>
                                                <td width = "100%">
                                                   <table>
                                                       <tr style="height:25px">
                                                         
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]['Name'] : '').'</b></th>    
                                                     
                                                       </tr>
                                                       <tr style="height:25px">
                                                           <th style="width:15%"></th>
                                                           <th style="width:70%;border-top:0.5px solid black" align="center">Household Head</th>       
                                                           <th style="width:5%"></th>
                                                        </tr> 
                                                        <br>    
                                                        <tr style="height:25px">
                                                       
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(2, $result) == true ? $result[2]['captain'] : '').'</b></th>    
                                                 
                                                    </tr>
                                                    <tr style="height:25px">
                                                        <th style="width:17%"></th>
                                                        <th style="width:70%;" align="center">Punong Barangay</th>   
                                                        <th style="width:3%"></th>
                                                     </tr>
                                                   </table>
                                               </td>
                                            </tr> 
                                        </table>
                                    </th>
                                </tr>
                            </table>
                        </td>
                        <td width = "50%" >
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                            <table cellpadding ="1" >
                                <tr>
                                   <th style="width:67%;">
                                       <table>
                                          <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;border-bottom:0.5px solid black;font-size:17px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]['barangay'] : '').'</b></th>    
                                               <th style="width:15%"></th>
                                           </tr>
                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                               <th style="width:11%"></th>
                                               <th style="width:70%;font-size:11px" align="center">Name of Barangay</th>    
                                           <th style="width:15%"></th>
                                           </tr>
                                           
                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;border-bottom:0.5px solid black;font-size:15px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]['purok'] : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                            <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:11px" align="center">Name of Purok</th>    
                                                <th style="width:15%"></th>
                                            </tr>

                                           <tr style="height:25px;border-top:0.5px solid black;font-size:15px">
                                                <th style="width:11%"></th>
                                                <th style="width:70%;font-size:15px" align="center"><b>HN: '.(array_key_exists(3, $result) == true ? $result[3]['code'] : '').'</b></th>    
                                                <th style="width:15%"></th>
                                            </tr>
                                          
                                        </table>
                                   </th>
                                   <th style="width:33%;" ><tcpdf method="write2DBarcode" params="'.$params4.'" /></th>          
                                   </tr>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <br>
                                   <tr>
                                    <th colspan ="2"> 
                                        <table width ="100%">
                                           <tr>
                                                <td width = "100%">
                                                   <table>
                                                       <tr style="height:25px">
                                                   
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]['Name'] : '').'</b></th>    
                                                        
                                                       </tr>
                                                       <tr style="height:25px">
                                                           <th style="width:14%"></th>
                                                           <th style="width:70%;border-top:0.5px solid black" align="center">Household Head</th>    
                                                           <th style="width:6%"></th>
                                                        </tr> 
                                                        <br>    
                                                        <tr style="height:25px">
                                                    
                                                        <th style="width:100%;font-size:15px" align="center"><b>'.(array_key_exists(3, $result) == true ? $result[3]['captain'] : '').'</b></th>    
                                                       
                                                    </tr>
                                                    <tr style="height:25px">
                                                        <th style="width:17%"></th>
                                                        <th style="width:70%;" align="center">Punong Barangay</th>     
                                                        <th style="width:3%"></th>
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
            ';
            PDF::writeHTML($Template, true, 0, true, 0);
            if (count($dataMain) > 0) {
                goto cons;
            }
        
            PDF::Output(public_path().'/prints.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }
}
