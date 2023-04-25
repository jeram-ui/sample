<?php

namespace App\Http\Controllers\Api\Scheduler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class positionController extends Controller
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
    public function displayData()
    {  
        $list = DB::table( $this->sched_db.'.tbl_position_info')
        ->join( $this->sched_db.'.tbl_organization_profile', 'tbl_position_info.orgID', '=', 'tbl_organization_profile.id')
        ->select(DB::raw("tbl_position_info.id, organization_name as 'Name of Organization', position as 'Position Description', effectivity, remarks, transStat"))
        ->where('transStat','!=','Deleted')
        ->get();
        return response()->json(new JsonResponse($list));
    }
    public function filterData(Request $request)
    {  
        $dateFr = $request->from;
        $dateTo = $request->to;
        $list = DB::table( $this->sched_db.'.tbl_position_info')
                ->join( $this->sched_db.'.tbl_organization_profile', 'tbl_position_info.orgID', '=', 'tbl_organization_profile.id')
                ->select(DB::raw("tbl_position_info.id, organization_name as 'Name of Organization', position as 'Position Description', effectivity, remarks, transStat"))
                     ->where('transStat', 'Active')
                    //  ->whereBetween('effectivity', [$dateFr, $dateTo])
                     ->get();
        return response()->json(new JsonResponse($list));  
    }
    public function customData(Request $request)
    {   
        $dateFr = $request->from;
        $dateTo = $request->to;
        $org = "";
        if ($request->org == 'All' || $request->org == '' ) {
            $org = "%";
        } else {
            $org = $request->org;
        }
        $list = DB::table( $this->sched_db.'.tbl_position_info')
                ->join( $this->sched_db.'.tbl_organization_profile', 'tbl_position_info.orgID', '=', 'tbl_organization_profile.id')
                ->select(DB::raw("tbl_position_info.id, organization_name as 'Name of Organization', position as 'Position Description', effectivity, remarks, transStat"))
                    //  ->where('transStat', 'Active')
                     ->where('orgID','LIKE', $org)
                    //  ->whereBetween('effectivity', [$dateFr, $dateTo])
                     ->get();
        return response()->json(new JsonResponse($list));  
    }
    public function save(Request $request)
    {   
        $main = $request->main;
        $posCount = 1;
        $cnt = 0;
        foreach ($main as $row) { 
            if ($cnt > 0) {
                if ($row['posID'] = $main[$cnt]['posID']) {
                    $posCount = $posCount + 1;
                } else {
                    $posCount = 1;
                }
            }    
            $result  = array(
                'posID' => $row['posID'],
                'orgID' => $row['orgID'],
                'pos_count' => $posCount,
                'position' => $row['position'],
                'remarks' => $row['remarks'],
            );
        DB::table( $this->sched_db.'.tbl_position_info')->insert($result); 
        $cnt = $cnt + 1;  
        }
        if ($result)
        {
        return response()->json(new JsonResponse([ 'msg' => 'Saved Successfully']));
        }
        return response()->json(new JsonResponse([ 'msg' => 'Saving Unsuccessfull']));
    }
    public function cancel($id)
    {  
        $data['transStat'] = 'Cancelled';
        DB::table( $this->sched_db.'.tbl_position_info')
            ->where('id', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Saved Successfully.', 'status' => 'success']));
    }
    public function printList(Request $request) {
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
        <th width = "30%">Organization</th>
        <th width = "20%">Position</th>
        <th width = "10%">Effectivity</th>
        <th width = "30%">Remarks</th>
        <th width = "10%">Status</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            //object declaration
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "30%">'.$main['Name of Organization'].'</td>
            <td width = "20%">'.$main['Position Description'].'</td>
            <td width = "10%">'.$main['effectivity'].'</td>
            <td width = "30%">'.$main['remarks'].'</td>
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
    public function store(Request $request)
    {
        //
    }

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
    public function update(Request $request, $id)
    {
        //
    }

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
}
