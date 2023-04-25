<?php

namespace App\Http\Controllers\Api\Assessor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;

class assessorFAAScontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    private $gen_db;
    protected $G;

    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->gen_db = $this->G->getGeneralDb();
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

    public function displayFAASLandData(Request $request)
    { 
        // dd($request);
        // dd($statustype);
        $statustype = 'RETIRED';
        $proptype = 'LAND';
        $list = DB::select('call ' . $this->lgu_db . '.spl_displayfaasmasterlist_ecao_mj(?,?)', array($statustype, $proptype));
        return response()->json(new JsonResponse($list));
    }

    public function filterFAASLandData(Request $request)
    {
        $statustype = $request->faas_status;
        $proptype = $request->prop_type;
        $list = DB::select('call ' . $this->lgu_db . '.spl_displayfaasmasterlist_ecao_mj(?,?)', array($statustype, $proptype));
        return response()->json(new JsonResponse($list));
    }
 

    public function printMainFAASLand(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">FAAS Master List</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "10%">ARP No. /TD No.</th>
        <th width = "13%">PIN</th>
        <th width = "13%">Owner Name</th>
        <th width = "10%">Kind/Class</th>
        <th width = "7%">Lot/Block No.</th>
        <th width = "10%">Property Location</th>
        <th width = "7%">Area (sqm/has)</th>
        <th width = "10%">Market Value</th>
        <th width = "10%">Assessed Value</th>
        <th width = "10%">Prev. TD No.</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td style="width:10%" align="center">' . $main['ARP NO'] . '</td>
            <td style="width:13%" align="center">' . $main['PIN'] . '</td>
            <td width = "13%">' . $main['OWNER NAME'] . '</td>
            <td width = "10%">' . $main['KIND/CLASS'] . '</td>
            <td style="width:7%" align="center">' . $main['LOT NO/BLOCK NO'] . '</td>
            <td width = "10%">' . $main['LOCATION OF PROPERTY'] . '</td>
            <td style="width:7%" align="center">' . $main['AREA'] . '</td>
            <td style="width:10%" align="right">' . $main['MARKET VALUE'] . '</td>
            <td style="width:10%" align="right">' . $main['ASSESSED VALUE'] . '</td>
            <td style="width:10%" align="center">' . $main['PREV. TD NO'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Certification');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
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
    public function getcitymundisnew() {
        $list = DB::select('SELECT * FROM ' . $this->lgu_db . '.lgu_city_mun_setup');
        return response()->json(new JsonResponse($list));
    }

    public function displayClassification()
    {
        $data = DB::select('CALL '.$this->lgu_db . '.display_ecao_classification');   
        return response()->json(new JsonResponse($data));
        
    }
    public function displaySubClassification($classid)
    {      
        //dd($classid);
        $list = DB::select('CALL '.$this->lgu_db . '.display_ecao_subclassification(?)',array($classid));          
        return response()->json(new JsonResponse($list));
        
    }

    public function displayKind() 
    {
        // dd($kindid);
        $list = DB::select('CALL '.$this->lgu_db . '.display_ecao_kind_setup');
        return response()->json(new JsonResponse($list));
    }

    public function displayYear($kindid)
    {      
        //dd($classid);
        $list = DB::select('CALL '.$this->lgu_db . '.display_ecao_kind_dtl(?)',array($kindid));          
        return response()->json(new JsonResponse($list));
        
    }
    public function displaySignatory() 
    {
        $list = DB::select('CALL '.$this->lgu_db . '.display_SIGNATORYTYPE()'); 
        return response()->json(new JsonResponse($list));

    }
    public function displaySignatoryEmp() 
    {
        $list = DB::select('CALL '.$this->lgu_db . '.display_usersEmployee_gigil()'); 
        return response()->json(new JsonResponse($list));
    }
}
