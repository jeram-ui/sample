<?php

namespace App\Http\Controllers\Api\Mod_Mayors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use Storage;
use File;
use PDF;
class AssistanceController extends Controller
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
    }



    public function getAssistedName(Request $request)
    {

        $from = $request->from;
        $to = $request->to;
        $list = db::table($this->myr_db.'.assistance_main')
        ->whereBetween('trans_date',[ $from, $to])
        ->select(db::raw('DISTINCT(`uid`) AS "uid",`assisted_by` AS "name"'))
        ->get()
        ;
      return response()->json(new JsonResponse($list));
    }
    public function purpose(){
      $list = db::table($this->myr_db.'.assistance_main')
      ->whereBetween('trans_date',[ $from, $to])
      ->select(db::raw('DISTINCT(`uid`) AS "uid",`assisted_by` AS "name"'))
      ->get()
      ;
    return response()->json(new JsonResponse($list));
    }
    public function SurveyList(Request $request)
    {

        $from = $request->from;
        $to = $request->to;
        $uid =$request->uid;
        $list = db::select('call '.$this->myr_db.'.rans_get_assistance_survey(?,?,?)',[$from,$to, $uid]);
      return response()->json(new JsonResponse($list));
    }
    public function getGenderCount(Request $request){
     $filter = $request->filter;
     $item = $request->item;
     $from = $filter['from'];
     $to = $filter['to'];
     $uid ='%%';
     $brgy_id = $item['brgy_id'];
     log::debug([$from,$to, $uid,$brgy_id]);
     $list = db::select('call '.$this->myr_db.'.rans_get_assistance_survey_details(?,?,?,?)',[$from,$to, $uid,$brgy_id]);
   return response()->json(new JsonResponse($list));



    }
    public function ref(Request $request)
    {
        // dd($request);
        $pre = 'ASST';
        $table = $this->myr_db . ".assistance_main";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function survey1(Request $request)
    {
        $main_id = $request->main_id;
        $new=$request->new;
        $old=$request->old;
        $group_id = $request->group_id;
        db::table($this->myr_db.".assistance_survey")
        ->where('main_id',$main_id)
        ->where('group_id',$group_id)->delete();
        db::table($this->myr_db.".assistance_survey")
        ->insert(['main_id'=>$main_id,'group_id'=>$request->group_id,'setup_id'=>$new]);
        db::table($this->myr_db.'.assistance_main')->where('id',$main_id)->update(['done_survey'=>1]);
    }
    public function getRating($id)
    {
      $list = db::table($this->myr_db.'.assistance_survey')->where('main_id',$id)->get();
      return response()->json(new JsonResponse($list));
    }
    public function show(Request $request)
    {
        $list = db::table($this->myr_db.'.assistance_main')
        ->select('*',db::raw('CONCAT(`lname`,", ",`fname`," ",ifnull(`mname`,"")) AS name'))
        ->where('stat',0)
        ->whereBetween('trans_date',[$request->from,$request->to])
        ->orderBy('ts', 'desc')
        ->get();
        return response()->json(new JsonResponse($list));
    }

    public function store(Request $request)
    {
        try {

            $main = $request->form;
            $idx = $main['id'];

            if ($idx == 0) {
                $main['uid'] = Auth::user()->id;
               db::table($this->myr_db .'.assistance_main')->insert($main);
            } else {
                $main['upid'] = Auth::user()->id;
                db::table($this->myr_db .'.assistance_main')->where('id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function edit($id)
    {
        $data['main'] = DB::table($this->myr_db.'.assistance_main')->where('id',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {
        DB::table($this->myr_db.'.assistance_main')->where('id',$id)->update(['stat'=>'1']);
      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function upload(Request $request){
        $files = $request->file('file');
        if(!empty($files)){
          $path = hash( 'sha256', time());
          for($i = 0; $i < count($files); $i++){
          $file = $files[$i];
          $filename = $file->getClientOriginalName();
          if(Storage::disk('docs')->put($path.'/'.$filename,  File::get($file))) {
              $data = array(
                'trans_id'=>$request->id,
                'file_name'=>$filename,
                'trans_type'=>'legalOpinion',
                'file_path'=>$path,
                'file_size'=>$file->getSize(),
                'uid'=>Auth::user()->id,
              );
              db::table('docs_upload')->insert($data);
              }
            }
        }
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
    }

       public function  uploaded($id){
        $data = db::table('docs_upload')
        ->where('trans_id', $id)
        ->where('trans_type','legalOpinion')
        ->where('stat', "ACTIVE")
        ->get();
        return response()->json(new JsonResponse($data));
       }
       public function documentView($id){
        $main=DB::table('docs_upload')->where('id',$id)->get();
        foreach ($main as $key => $value ) {
         $file = $value->file_name;
         $path = '../storage/files/document/'.$value->file_path.'/'.$file;
         if (\File::exists($path)) {
         $file = \File::get($path);
         $type = \File::mimeType($path);
         $response = \Response::make($file, 200);
         $response->header("Content-Type", $type);
         return $response;
         }
        }
    }
       public function uploadRemove($id){
        $data = db::table('docs_upload')->where('id', $id)
        ->update(['stat'=>"CANCELLED"])
        ;
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
       }

       public function printList(Request $request)
       {
           $data = $request->data;
           $filter = $request->filter;
          try{
               $html_content ='<h2 align="center">Assistance</h2>';
               if ($filter['from'] === $filter['to']) {
                $html_content .='<h4 align="center">('.date("F j, Y", strtotime($filter['from'])) .' )</h4>';
               }else{
                $html_content .='<h4 align="center">( From '.date("F j", strtotime($filter['from'])) .' - '.date("F j, Y", strtotime($filter['to'])) .' )</h4>';
               }
            //    $html_content .='<h4 align="center">asd</h4>';
            $html_content .= '
          <table border=".5" cellpadding="2" style="width:100%">
            <tr style="text-align:center">
               <th width="39%">Satisfied</th>
               <th  width="20%" >Timely</th>
               <th width="20%">Couteous</th>
               <th width="21%">Total</th>
             </tr>
                  <tr style="text-align:center">
                    <th  width="19%" >Barangay</th>

                    <th  width="4%" >1</th>
                    <th  width="4%">2</th>
                    <th  width="4%">3</th>
                    <th  width="4%">4</th>
                    <th  width="4%">5</th>

                    <th  width="4%" >1</th>
                    <th  width="4%">2</th>
                    <th  width="4%">3</th>
                    <th  width="4%">4</th>
                    <th  width="4%">5</th>

                    <th  width="4%" >1</th>
                    <th  width="4%">2</th>
                    <th  width="4%">3</th>
                    <th  width="4%">4</th>
                    <th  width="4%">5</th>

                    <th  width="6%">Male</th>
                    <th  width="7%">Female</th>
                    <th  width="8%">Total</th>

                  </tr>
                  <tbody>';
                  $SED =0;
                  $SD=0;
                  $SN=0;
                  $SS=0;
                  $SES=0;

                  $TED =0;
                  $TD=0;
                  $TN=0;
                  $TS=0;
                  $TES=0;

                  $CED =0;
                  $CD=0;
                  $CN=0;
                  $CS=0;
                  $CES=0;

                  $Male=0;
                  $Female=0;
                  $total=0;


                  foreach($data as $row){
                   $main =($row);
                   $SED += $main['SED'];
                   $SD += $main['SD'];
                   $SN += $main['SN'];
                   $SS += $main['SS'];
                   $SES += $main['SES'];

                   $TED += $main['TED'];
                   $TD += $main['TD'];
                   $TN += $main['TN'];
                   $TS += $main['TS'];
                   $TES += $main['TES'];

                   $CED += $main['CED'];
                   $CD += $main['CD'];
                   $CN += $main['CN'];
                   $CS += $main['CS'];
                   $CES += $main['CES'];

                   $Male += $main['Male'];
                   $Female += $main['Female'];
                   $total += $main['total'];


                       $html_content .='
                       <tr style="text-align:center">
                       <td style="text-align:left"  width = "19%">'.$main['category'].'</td>
                       <td width = "4%">'.$main['SED'].'</td>
                       <td width = "4%">'.$main['SD'].'</td>
                       <td width = "4%">'.$main['SN'].'</td>
                       <td width = "4%">'.$main['SS'].'</td>
                       <td width = "4%">'.$main['SES'].'</td>
                       <td width = "4%">'.$main['TED'].'</td>
                       <td width = "4%">'.$main['TD'].'</td>
                       <td width = "4%">'.$main['TN'].'</td>
                       <td width = "4%">'.$main['TS'].'</td>
                       <td width = "4%">'.$main['TES'].'</td>
                       <td width = "4%">'.$main['CED'].'</td>
                       <td width = "4%">'.$main['CD'].'</td>
                       <td width = "4%">'.$main['CN'].'</td>
                       <td width = "4%">'.$main['CS'].'</td>
                       <td width = "4%">'.$main['CES'].'</td>
                       <td width = "6%">'.$main['Male'].'</td>
                       <td width = "7%">'.$main['Female'].'</td>
                       <td width = "8%">'.$main['total'].'</td>
                       </tr>';
               }
               $html_content .='
                    <tr style="text-align:center">
                    <td style="text-align:left" width = "19%"><b>Total</b></td>
                    <td width = "4%"><b>'.$SED.'</b></td>
                    <td width = "4%"><b>'.$SD.'</b></td>
                    <td width = "4%"><b>'.$SN.'</b></td>
                    <td width = "4%"><b>'.$SS.'</b></td>
                    <td width = "4%"><b>'.$SES.'</b></td>

                    <td width = "4%"><b>'.$TED.'</b></td>
                    <td width = "4%"><b>'.$TD.'</b></td>
                    <td width = "4%"><b>'.$TN.'</b></td>
                    <td width = "4%"><b>'.$TS.'</b></td>
                    <td width = "4%"><b>'.$TES.'</b></td>

                    <td width = "4%"><b>'.$CED.'</b></td>
                    <td width = "4%"><b>'.$CD.'</b></td>
                    <td width = "4%"><b>'.$CN.'</b></td>
                    <td width = "4%"><b>'.$CS.'</b></td>
                    <td width = "4%"><b>'.$CES.'</b></td>

                    <td width = "6%"><b>'.$Male.'</b></td>
                    <td width = "7%"><b>'.$Female.'</b></td>
                    <td width = "8%"><b>'.$total.'</b></td>
                    </tr>';
               $html_content .='</tbody></table>';

               PDF::SetTitle('Assistance');
               PDF::SetFont('helvetica', '', 9);
            //    PDF::SetMargins(20, 10, 10);
               PDF::SetHeaderMargin(10);
               PDF::SetFooterMargin(10);
               PDF::AddPage('P');
               PDF::writeHTML($html_content, true, 0, true, 0);
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
