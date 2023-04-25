<?php

namespace App\Http\Controllers\Api\sample;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ranz;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use PDF;
class sampleController extends Controller
{
    public function index()
    {
        //
    }
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $idx =$request->main['id'];
            $main = $request->main;
            $dtl =$request->dtls;
            $check = $main['checkbox'];
            unset($main['checkbox']);
            if ($idx > 0) {
              $this->update($idx,$main,$dtl,$check);
            }else{
              $this->save($main,$dtl,$check);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function save($main,$dtl,$check){
        // ranz::create($main);
        DB::table('ranz')->insert($main);
        $id = DB::getPdo()->lastInsertId();
        foreach ($dtl as $row) {
         $array = array(
            'main_id' => $id,
            'item_id' =>$row['id'],
            'item_description' =>$row['item_name'],
            'item_amount' =>$row['item_ucost'],
            'item_qty' => $row['item_qty'],
            'item_total' => $row['Total'],
        );
        DB::table('ranz_details')->insert($array);
        }
        foreach($check as $row){
            $chk = array(
                'main_id' => $id,
                'decription'=>$row
            );
            DB::table('ranz_check')->insert($chk);
        }
    }
    public function update($idx,$main,$dtl,$check){
        DB::table('ranz')->where('id', $idx)->update($main);
        DB::table('ranz_details')->where('main_id', $idx)->delete();
        foreach ($dtl as $row) {
         $array = array(
            'main_id' => $idx,
            'item_id' =>$row['id'],
            'item_description' =>$row['item_name'],
            'item_amount' =>$row['item_ucost'],
            'item_qty' => $row['item_qty'],
            'item_total' => $row['Total'],
        );
        DB::table('ranz_details')->insert($array);
        }
        DB::table('ranz_check')->where('main_id', $idx)->delete();
        foreach($check as $row){
            $chk = array(
                'main_id' => $idx,
                'decription'=>$row
            );
            DB::table('ranz_check')->insert($chk);
        }
    }
    public function show()
    {
      $list = DB::table('ranz')->get();
      return response()->json(new JsonResponse($list));
    }
    public function edit($id)
    {
       $data['main'] = DB::table('ranz')->where('id',$id)->get();
       $data['details'] = DB::table('ranz_details')->where('main_id',$id)->get();
       $data['chk'] = DB::table('ranz_check')->select('decription')->where('main_id',$id)->get();
       return response()->json(new JsonResponse($data));
    }
    public function destroy($id)
    {
        //
    }
    public function print(Request $request){
        $data = $request->main;
        $logo = config('variable.logo');
        try {
        $html_content ='
        '.$logo.'
        <h2 align="center">Title</2>
        <br></br>
        <br></br>
        <table border="1"  cellpadding="2">
        <tr align="center" >
        <th >Date</th>
        <th>Time</th>
        <th>Text</th>
        <th>Number</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            $main =($row);
            $html_content .='
            <tr>
            <td>'.$main['trans_date'].'</td>
            <td>'.$main['trans_time'].'</td>
            <td>'.$main['trans_text'].'</td>
            <td>'.$main['trans_desc'].'</td>
            </tr>';
        }
        $html_content .='</tbody>
        </table>';

        PDF::SetTitle('Sample');
        PDF::AddPage();
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status' => 'success']));
    } catch (\Exception $e) {
        return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }

    }

    public function printsample(){
        $Template = '
        <table style="width:100%;">
            <th></th>    
        </table>';
    }
}
