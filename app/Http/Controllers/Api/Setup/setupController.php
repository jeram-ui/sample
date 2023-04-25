<?php

namespace App\Http\Controllers\Api\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class setupController extends Controller
{
    //start of controller
    public function displayData()
    {  
        $item = DB::table('setup')
                    ->where('transStat','!=','Deleted')
                    ->get();
        return response()->json(new JsonResponse($item));
    }
    public function filterData(Request $request)
    {  
        $dateFr = $request->from;
        $dateTo = $request->to;
        $list = DB::table('setup')
                     ->where('transStat', 'Active')
                     ->whereBetween('transDate', [$dateFr, $dateTo])
                     ->get();
        return response()->json(new JsonResponse($list));  
    }
    public function customData(Request $request)
    {  
        $dateFr = $request->from;
        $dateTo = $request->to;
        $group = "";
        if ($request->group == 'All' || $request->group == '' ) {
            $group = "%";
        } else {
            $group = $request->group;
        }
        $list = DB::table('setup')
                     ->where('transStat', 'Active')
                     ->where('groupType','LIKE', $group)
                     ->whereBetween('transDate', [$dateFr, $dateTo])
                     ->get();
        return response()->json(new JsonResponse($list));  
    }
    public function printMain(Request $request) {
        $data = $request->main;
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">Cumulative Data</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "10%">ID</th>
        <th width = "15%">Data</th>
        <th width = "15%">Group</th>
        <th width = "50%">Description</th>
        <th width = "10%">Status</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            //object declaration
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "10%">'.$main['id'].'</td>
            <td width = "15%">'.$main['transDate'].'</td>
            <td width = "15%">'.$main['groupType'].'</td>
            <td width = "50%">'.$main['description'].'</td>
            <td width = "10%">'.$main['transStat'].'</td>
            </tr>';
        }
        $html_content .='</tbody>
        </table>';
        PDF::SetTitle('Sample');
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
         }catch (\Exception $e) {
             return response()->json(new JsonResponse(['status'=>'error']));
         }
    }
    public function printDtl(Request $request) {
        //$data = $request->id;
        $data = DB::table('setup')
                    ->where('id',$request->id)
                    ->get();
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">Cumulative Data</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "10%">ID</th>
        <th width = "15%">Data</th>
        <th width = "15%">Group</th>
        <th width = "50%">Description</th>
        <th width = "10%">Status</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            //array declaration
            $dtl =($row);   
            $html_content .='
            <tr>
            <td width = "10%">'.$dtl->id.'</td>
            <td width = "15%">'.$dtl->transDate.'</td>
            <td width = "15%">'.$dtl->groupType.'</td>
            <td width = "50%">'.$dtl->description.'</td>
            <td width = "10%">'.$dtl->transStat.'</td>
            </tr>';
        }
        $html_content .='</tbody>
        </table>';
        PDF::SetTitle('Sample');
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
         }catch (\Exception $e) {
             return response()->json(new JsonResponse(['status'=>'error']));
         }
    }
    public function groupData()
    {  
        $item = DB::table('setup')
                     ->select('groupType','transStat')
                     ->where('transStat', 'Active')
                     ->groupBy('groupType')
                     ->get();
        return response()->json(new JsonResponse($item));
    }
    public function maxNum()
    {  
        $item = DB::table('setup')->select("id", DB::raw("CONCAT('FRM-','00',COUNT(id)+1,'-',DATE_FORMAT(NOW(),'%Y')) as maxID"))->get();
        return response()->json(new JsonResponse($item));
    }
    public function editData($id) {
        $data['main'] = DB::table('setup')->where('id',$id)->get();
        $data['detail'] = DB::table('setup')
            ->select('setup.id as id',
            'setup.transDate',
            'setup.groupType',
            'setup.description',
            'setup.remarks')
            ->where('id',$id)
            ->get();
        return response()->json(new JsonResponse($data));
    }
    public function viewData($id) 
    {   
        $list = DB::table('setup')
                    ->where('id',$id)
                    ->get();
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request) 
    {
        try {
            //DB::beginTransaction();
            $pkId = $request->idx;
            $description = $request->description;
            $remarks = $request->remarks;
            $main = $request->main;
            if ($pkId>0) {
                # updated
                $this->update($pkId, $main, $description, $remarks);
            } else {
                # saved
                $this->save($main);
            }
            //DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction Complete.', 'status' => 'success']));
        } catch (\Exception $err) {
            //DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }

    }
    //public function save(Request $request)
    public function save($main)
    {  
        // $main = $request->main;
        // $remarks = $request->remarks;
        foreach ($main as $row) {
            $result  = array(
                'transDate' => $row['transDate'],
                'groupType' => $row['groupType'],
                'description' => $row['description'],
                'remarks' => $row['remarks'],
            );
        DB::table('setup')->insert($result);    
        }
        if ($result)
        {
        return response()->json(new JsonResponse([ 'msg' => 'Saved Successfully']));
        }
        return response()->json(new JsonResponse([ 'msg' => 'Saving Unsuccessfull']));
    }
    //public function update(Request $request)
    public function update($pkId, $main, $description, $remarks)
    {   
        // $main = $request->main;
        // $pkId = $request->main['id'];
        // $remarks = $request->remarks;
        foreach ($main as $row) {
            $result = DB::table('setup')
                    ->where('id', $pkId)
                    ->update([
                    'transDate' => $row['transDate'],
                    'groupType' => $row['groupType'],
                    'description' => $description,
                    'remarks' => $remarks
                    ]);
        }   
        if ($result)
        {
        return response()->json(new JsonResponse([ 'msg' => 'Updated Successfully']));
        }
        return response()->json(new JsonResponse([ 'msg' => 'Updating Unsuccessfull']));
    }
    public function modify(Request $request)
    {
        $main = $request->main;
        $id = $request->main['id'];
        DB::table('setup')
            ->where('id', $id)
            ->update([
                'transDate'  => $main['transDate'],
                'groupType'  => $main['groupType'],
                'description' => $main['description'],
                'remarks'  => $main['remarks']
              ]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function cancel($id)
    {
        $data['transStat'] = 'Cancelled';
        DB::table('setup')
            ->where('id', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function print(Request $request) {
        //$data = $request->main;
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">Cumulative Data</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "10%">ID</th>
        <th width = "15%">Data</th>
        <th width = "15%">Group</th>
        <th width = "50%">Description</th>
        <th width = "10%">Status</th>
        </tr>
        <tbody>';
        //foreach($data as $row){
            //$main =($row);   
            $html_content .='
            <tr>
            <td width = "10%"></td>
            <td width = "15%"></td>
            <td width = "15%"></td>
            <td width = "50%"></td>
            <td width = "10%"></td>
            </tr>';
        //}
        $html_content .='</tbody>
        </table>';
        PDF::SetTitle('Sample');
        PDF::AddPage('L');
        PDF::writeHTML($html_content, true, true, true, true, '');
        PDF::Output(public_path().'/print.pdf', 'F');
        return response()->json(new JsonResponse(['status'=>'success']));
         }catch (\Exception $e) {
             return response()->json(new JsonResponse(['status'=>'error']));
         }
    }
//end of controller
}
