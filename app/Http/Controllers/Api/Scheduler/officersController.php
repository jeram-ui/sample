<?php

namespace App\Http\Controllers\Api\Scheduler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;

use PDF;

class officersController extends Controller
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

    // public function list($id) {
    //     $list = tbl_official_info::where('id',$id)->get();
    //     return response()->json(new JsonResponse($list));
    // }

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

    public function organization()
    {
        $list = DB::select('call ' . $this->sched_db . '.spl_display_organization');
        return response()->json(new JsonResponse($list));
    }

    public function member($id)
    {
        $list = DB::select('call ' . $this->sched_db . '.spl_display_member_info_org(' . $id . ')');
        return response()->json(new JsonResponse($list));
    }

    public function position()
    {
        $list = DB::select('call ' . $this->sched_db . '.spl_display_position_info');
        return response()->json(new JsonResponse($list));
    }

    public function save(Request $request)
    {
        //dd($request['main']);
        $main = $request->main;
        foreach ($main as $row) {
            $main = array(
                'posID'  => $row['positionId'],
                'position'  => $row['position'],
                'pkID' => $row['memberId'],
                'personName'  => $row['member'],
                'termFrom'  => $row['from'],
                'termTo'  => $row['to'],
                'effectivity'  => $row['effectivity'],
                'orgID'  => $row['organizationId'],
                'organization' => $row['organization'],
            );
            DB::table($this->sched_db . '.tbl_official_info')->insert($main);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction updated successfully']));
    }

    public function updateData(Request $request)
    {
        // dd($request->main); $request->main['member']
        $main = $request->main;
        $id = $request->main['id'];
        $result = DB::table($this->sched_db . '.tbl_official_info')
            ->where('id', $id)
            ->update([
                'posID'  => $main['positionId'],
                'position'  => $main['position'],
                'pkID' => $main['memberId'],
                'personName'  => $main['member']
            ]);
        if ($result) {
            return response()->json(new JsonResponse(['msg' => 'Transaction updated successfully']));
        }
        return response()->json(new JsonResponse(['msg' => 'Transaction not updated successfully']));
    }

    public function editData($idx)
    {
        $list = DB::select('call ' . $this->sched_db . '.spl_display_official_info_id(?)', array($idx));
        return response()->json(new JsonResponse($list));
    }
    // end of controller
}
