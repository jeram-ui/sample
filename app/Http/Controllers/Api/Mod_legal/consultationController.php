<?php

namespace App\Http\Controllers\Api\Mod_legal;

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
class consultationController extends Controller
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
    public function getRef(Request $request)
    {
        // dd($request);
        $pre = 'CN';
        $table = $this->lgu_db . ".law_consultation";
        $date = $request->date;
        $refDate = 'trans_date';
        $data = $this->G->generateReference($pre, $table, $date, $refDate);
        return response()->json(new JsonResponse(['data' => $data]));
    }
    public function show(Request $request)
    {
        $list = db::table($this->lgu_db.'.law_consultation')
        ->select('*',db::raw('CONCAT(`lname`,", ",`fname`," ",`mname`) AS name'))
        ->where('status',0)
        ->whereBetween('trans_date',[$request->from,$request->to])
        ->orderBy('trans_date', 'desc')
        ->get();
        return response()->json(new JsonResponse($list));
    }
    public function showForCase(Request $request){
        $list = db::table($this->lgu_db.'.law_consultation')
        ->select('*',db::raw('CONCAT(`lname`,", ",`fname`," ",`mname`) AS name'))
        ->where('status',0)
        ->where('with_case','1')
        ->orderBy('trans_date', 'desc')
        ->get();
        return response()->json(new JsonResponse($list));
    }
   public function updateCase($id){
    db::table($this->lgu_db.'.law_consultation')->where('id',$id)->update(['with_case'=>'1']);
    return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
   }
    public function store(Request $request) 
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_consultation')->insert($main);
            } else {
                db::table($this->lgu_db .'.law_consultation')->where('id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Completed.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  


    public function edit($id) 
    {   
        $data['main'] = DB::table($this->lgu_db.'.law_consultation')->where('id',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {   
        DB::table($this->lgu_db.'.law_consultation')->where('id',$id)->update(['status'=>'1']);
      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
    public function printform($id){
    $data = db::table($this->lgu_db.'.law_consultation')->where('id',$id)
    ->select('*',db::raw($this->lgu_db.'.mj_get_age(bdate)as age'))
    ->first();
        $logo = $this->G->printHeader('City Legal Office');
        try {
        $html_content = '
        '.$logo.'
        ';
        $html_content .= '
        <h3 align="center">Client Information Sheet</h3>'
        ;
        $html_content .= '
<table style ="width:100%" cellpadding = "2" >
<tr>
 <td>Ref. No: '. $data->ref_no.'</td>
 <td align ="right">Date: '.date("d/m/Y", strtotime($data->trans_date)) .'</td>
</tr>
<br/>
<br/>
<tr>
<td width ="10%" >Name :</td>
<td width ="40%" style="border-bottom: 1px solid black" >'.$data->fname." ". $data->mname." ".$data->lname.'</td>

<td width ="10%">Age :</td>
<td width ="10%" style="border-bottom: 1px solid black" >'.$data->age.'</td>

<td width ="10%">Gender :</td>
<td width ="20%" style="border-bottom: 1px solid black">'.$data->gender.'</td>
</tr>

<tr>
<td width ="13%" >Address :</td>
<td width ="57%" style="border-bottom: 1px solid black" >'.$data->purok_name." ". $data->barangay_name.'</td>
<td width ="13%">Contact # :</td>
<td width ="17%" style="border-bottom: 1px solid black" >'.$data->contact_no.'</td>
</tr>

<tr>
<td width ="33%" >Subject matter complained of:</td>
<td width ="67%" style="border-bottom: 1px solid black" ></td>
</tr>
<tr>
<td width ="100%" style="border-bottom: 1px solid black" ></td>
</tr>

<tr>
<td width ="25%" >Supporting Documents:</td>
<td width ="75%" style="border-bottom: 1px solid black" ></td>
</tr>
<tr>
<td width ="100%" style="border-bottom: 1px solid black" ></td>
</tr>

<tr>
<td width ="15%" >Legal Advice:</td>
<td width ="85%" style="border-bottom: 1px solid black" ></td>
</tr>

<tr>
<td width ="100%" style="border-bottom: 1px solid black" ></td>
</tr>
<tr>
<td width ="100%" style="border-bottom: 1px solid black" ></td>
</tr>
</table>
        ';
     
        PDF::SetTitle('Comsultation');
        PDF::AddPage('P');
        PDF::writeHTML($html_content, true, true, true, true, '');

    
        PDF::SetXY(80, 78);
        $subject = '<p style="text-indent: 180px">'.$data->subject_matter.'</p>';
        PDF::writeHTML($subject, true, false, false, false, '');

        PDF::SetXY(80, 90);
        $docs = '<p style="text-indent: 140px">'.$data->supporting_documents.'</p>';
        PDF::writeHTML($docs, true, false, false, false, '');

        PDF::SetXY(80, 105);
        $advice = '<p style="text-indent: 85px">'.$data->legal_advice.'</p>';
        PDF::writeHTML($advice, true, false, false, false, '');

        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
        }catch (\Exception $e) {
            return response()->json(new JsonResponse(['status'=>'error']));
        }
    }
    public function printlist(Request $request){
        $data = $request->main;
        $filter = $request->filter;
        log::debug( $data);
        log::debug( $filter);
        $from = date("F j, Y", strtotime($filter['from']));
        $to =  date("F j, Y", strtotime($filter['to']));
        $filters = $from ." - ". $to;

        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 style="width:14%;text-align:center;font-size:13px">Consultation Summary</h2>
        <h3 style="width:14%;text-align:center;font-size:11px">'.$filters.'</h3>
        <br></br>
        <br></br>
        <table border=".5" cellpadding="2">
        <tr align="center">
        <th style = "width:7%;font-size:9px">Ref No.</th>
        <th style = "width:7%;font-size:9px">Date</th>
        <th style = "width:18%;font-size:9px">last Name</th>
        <th style = "width:17%;font-size:9px">Given Name</th>
        <th style = "width:17%;font-size:9px">M.I</th>
        <th style = "width:15%;font-size:9px">Purok</th>
        <th style = "width:17%;font-size:9px">Barangay</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            
            $main =($row);   
            $html_content .='
            <tr>
            <td style="width:7%;text-align:left;font-size:8px">'.$main['ref_no'].'</td>
            <td style="width:7%;text-align:left;font-size:8px">'.$main['trans_date'].'</td>
            <td style="width:18%;text-align:left;font-size:8px">'.$main['lname'].'</td>
            <td style="width:17%;text-align:left;font-size:8px">'.$main['fname'].'</td>
            <td style="width:17%;text-align:left;font-size:8px">'.$main['mname'].'</td>
            <td style="width:15%;text-align:left;font-size:8px">'.$main['purok_name'].'</td>
            <td style="width:17%;text-align:left;font-size:9px">'.$main['barangay_name'].'</td>
            </tr>';
        }
        $html_content .='</tbody>
        </table>';
    
        PDF::SetTitle('Consultation');
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
        }catch (\Exception $e) {
            return response()->json(new JsonResponse(['status'=>'error']));
        }
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
                'consult_id'=>$request->id,
                'file_name'=>$filename,
                'file_path'=>$path,
                'file_size'=>$file->getSize(),
                'uid'=>Auth::user()->id,
              );
              db::table($this->lgu_db.'.law_consultation_docs')->insert($data);           
              }
            }
        }
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
       }
       public function  uploaded($id){
        $data = db::table($this->lgu_db.'.law_consultation_docs')
        ->where('consult_id', $id)
        ->where('stat', "ACTIVE")
        ->get();
        return response()->json(new JsonResponse($data));
       }
       public function documentView($id){
        $main=DB::table($this->lgu_db.'.law_consultation_docs')->where('id',$id)->get();
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
        $data = db::table($this->lgu_db.'.law_consultation_docs')->where('id', $id)
        ->update(['stat'=>"CANCELLED"])
        ;
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
       }
   
}                