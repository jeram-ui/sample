<?php

namespace App\Http\Controllers\Api\TaxPayerReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GlobalController;

use PDF;

class taxPayerController extends Controller
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

    public function index()
    {
    }
    public function store(Request $request)
    {
    }
    public function save($main, $dtl, $check)
    {
    }
    public function update($idx, $main, $dtl, $check)
    {
    }
    public function TaxPayerReport(Request $request)
    {   
        $date = $request->from;

        $barangay = 'All';
        $businessType = 'All';
        $kindofBusiness = 'All';
        if ($request->barangay == 'All') {
            $barangay = 'All';
        } else {
            $barangay = $request->barangay;
        }
        if ($request->businessType == 'All') {
            $businessType = 'All';
        } else {
            $businessType = $request->businessType;
        }
        if ($request->businessStatus == '') {
            $businessStatus = '%';
        } else {
            $businessStatus = $request->businessStatus;
        }
        {
            $list = DB::select('call '.$this->lgu_db.'.jay_ebplo_display_business_taxpayer_report1(?,?,?,?,?,?)',array($request->from,$request->to,$request->brgy,$request->businessType,$request->businessStatus,$request->kindofBusiness));
            return response()->json(new JsonResponse($list));
        }
    }
    public function kindofBusiness()
    {
        $list = DB::select('call '.$this->lgu_db.'.jay_display_cto_kind_business_setup');
        return response()->json(new JsonResponse($list));
    }

    public function printsample()
    {
        $Template = '
        <table style="width:100%;">
            <th></th>    
        </table>';
    }

    public function businessPaymentMasterList(Request $request)
    {    
        $list = DB::select('call '.$this->lgu_db.'.spl_display_business_list_payment_status(?,?,?,?,?,?,?)',array($request->from,$request->to,$request->brgy,$request->businessType,$request->businessStatus,$request->paymentStatus,$request->businessCategory));
        return response()->json(new JsonResponse($list));
    }
    public function taxpayerReportPrint(Request $request){
        $data = $request->main;
        $filter = $request->filter;
        $from = date("F j, Y", strtotime($filter['from'])) ;
        $to =  date("F j, Y", strtotime($filter['to']));

        if ($filter['filter'] =="Year") {
            $filters = "Year ". date("Y", strtotime($filter['from'])) ;
        }elseif ($filter['filter'] =="Month") {
            $filters = "Month of ". date("F Y", strtotime($filter['from'])) ;
        }else {
            $filters = "As of ".  $from .' - '. $to;
        }
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">TAX PAYER REPORT</h2>
        <h3 align="center">'.$filters.'</h3>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "16%">Name of Owner</th>
        <th width = "15%">Name of Business</th>
        <th width = "10%">Business Address</th>
        <th width = "9%">Contact No.</th>
        <th width = "11%">Type of Business</th>
        <th width = "10%">Kind of Business</th>
        <th width = "10%">Date Registered</th>
        <th width = "9%">Date Released</th>
        <th width = "11%">Status of Business</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "16%">'.$main['Tax Payers Name'].'</td>
            <td width = "15%">'.$main['Business Name'].'</td>
            <td width = "10%">'.$main['Address'].'</td>
            <td width = "9%">'.$main['Contact No'].'</td>
            <td width = "11%">'.$main['Type of Ownership'].'</td>
            <td width = "10%">'.$main['Kind of Business'].'</td>
            <td width = "10%">'.$main['Date of Application'].'</td>
            <td width = "9%">'.$main['Date of Issuance'].'</td>
            <td width = "11%">'.$main['business_status'].'</td>
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
}
