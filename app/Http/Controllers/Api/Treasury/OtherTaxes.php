<?php

namespace App\Http\Controllers\Api\Treasury;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class OtherTaxes extends Controller
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
    // start of controller 
    public function getmarketDelinquency(Request $request)
    {
        $all = "%";
        $person = "Person";
        $billing = "%";
        $type = "%";
        $biller = "%";
        $bldg = "%";
        $subbldg = "%";
        if ($request->bldg == '' || $request->bldg == 'All') {
            $bldg = "%";
        } else {
            $bldg = $request->bldg;
        }
        if ($request->subbldg == '' || $request->subbldg == 'All') {
            $subbldg = "%";
        } else {
            $subbldg = $request->subbldg;
        }
        if ($request->billing == '' || $request->billing == 'All') {
            $billing = "%";
        } else {
            $billing = $request->billing;
        }
        if ($request->type == '' || $request->type == 'All') {
            $type = "%";
        } else {
            $type = $request->type;
        }
        if ($request->biller == '' || $request->biller == 'All') {
            $biller = "%";
        } else {
            $biller = $request->biller;
        }
        $date = $request->to;
        $dataPenalty = DB::select('call '.$this->lgu_db.'.spl_getMarketPenalty_joy(?)',array($date));
        foreach($dataPenalty as $row) {
            $Ppenalty_type = $row->{'penalty_type'};
            $Prental = $row->{'rental'};
            $Pwater = $row->{'water'};
            $Pelectricity = $row->{'electricity'};
            $Pothers = $row->{'others'};
            $Pmultiply = $row->{'multiply_days'};
            $PnoOfNotice = $row->{'no_of_notice'};
            $Pinterest_type = $row->{'interest_type'};
            $Pinterest_rental = $row->{'rental_interest'};
            $Pinterest_water = $row->{'water_interest'};
            $Pinterest_electricity = $row->{'electricity_interest'};
            $Pinterest_others = $row->{'others_interest'};
            $Pinterest_multiply = $row->{'interest_multiply_days'};
        }
        $dataDiscount = DB::select('call '.$this->lgu_db.'.spl_getMarketDiscount_joy(?)',array($date));
        foreach($dataDiscount as $row) {
            $Dpenalty_type = $row->{'penalty_type'};
            $Drental = $row->{'rental'};
            $Dwater = $row->{'water'};
            $Delectricity = $row->{'electricity'};
            $Dothers = $row->{'others'};
        }
        
        $list = DB::select('call '.$this->lgu_db.'.spl_Billing_Delinquency_joy1(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',array($request->from,$request->to,$billing,$biller,$type,$all,$person,$all,$person,$bldg,$subbldg,$all,$Ppenalty_type,$Prental,$Pwater,$Pelectricity,$Pothers,$Drental,$Dwater,$Delectricity,$Dothers,$Pinterest_type,$Pinterest_rental,$Pinterest_water,$Pinterest_electricity,$Pinterest_others,$Pmultiply,$Pinterest_multiply,$Dpenalty_type,$all));
        return response()->json(new JsonResponse($list));
    }

    public function marketDelinquencyPrint(Request $request){
        $data = $request->main;
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">Market Tax Delinquency</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Bill No</th>
        <th width = "9%">Due Date</th>
        <th width = "12%">Property Name</th>
        <th width = "8%">Lot/Unit No</th>
        <th width = "17%">Registered Business</th>
        <th width = "13%">Tenant/Occupant</th>
        <th width = "11%">Type</th>
        <th width = "14%">Unpaid Period</th>
        <th width = "9%">Amount Due</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "9%">'.$main['Bill No'].'</td>
            <td width = "9%">'.$main['Due Date'].'</td>
            <td width = "12%">'.$main['Property'].'</td>
            <td width = "8%">'.$main['Unit'].'</td>
            <td width = "17%">'.$main['Bill Name'].'</td>
            <td width = "13%">'.$main['Billed For'].'</td>
            <td width = "11%">'.$main['Type'].'</td>
            <td width = "14%">'.$main['Unpaid Period'].'</td>
            <td width = "9%">'.$main['Total Due'].'</td>
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
    public function getmarketMasterlist(Request $request)
    {
        $barangay = "All";
        $type = "All";
        $tenant = "All";
        $bldg = "All";
        $subbldg = "All";
        if ($request->bldg == '') {
            $bldg = "All";
        } else {
            $bldg = $request->bldg;
        }
        if ($request->subbldg == '') {
            $subbldg = "All";
        } else {
            $subbldg = $request->subbldg;
        }
        if ($request->barangay == 'All') {
            $barangay = "All";
        } else {
            $barangay = $request->barangay;
        }
        if ($request->type == 'All') {
            $type = "All";
        } else {
            $type = $request->type;
        }
        if ($request->tenant == '') {
            $tenant = "%";
        } else {
            $tenant = $request->tenant;
        }
      
        $list = DB::select('call '.$this->lgu_db.'.jay_display_cto_rental_master_list1(?,?,?,?,?,?,?)',array($request->from,$request->to,$bldg,$subbldg,$barangay,$type,$tenant));
        return response()->json(new JsonResponse($list));
    }
    public function marketMasterlistPrint(Request $request){
        $data = $request->main;
        $logo = config('variable.logo');
        try {
        $html_content = '
        '.$logo.'
        <h2 align="center">Market Tax Masterlist</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "9%">Rental Code</th>
        <th width = "9%">Effectivity</th>
        <th width = "12%">Property Name</th>
        <th width = "9%">Block/Floor</th>
        <th width = "9%">Lot/Unit No</th>
        <th width = "18%">Registered Business</th>
        <th width = "17%">Tenant/Occupant</th>
        <th width = "9%">Section</th>
        <th width = "9%">Rental</th>
        </tr>
        <tbody>';
        foreach($data as $row){
            $main =($row);   
            $html_content .='
            <tr>
            <td width = "9%">'.$main['Rental Code'].'</td>
            <td width = "9%">'.$main['Date of Application'].'</td>
            <td width = "12%">'.$main['Building Name'].'</td>
            <td width = "9%">'.$main['Floor No'].'</td>
            <td width = "9%">'.$main['Stall No'].'</td>
            <td width = "18%">'.$main['Registered Owner'].'</td>
            <td width = "17%">'.$main['Tenants Name'].'</td>
            <td width = "9%">'.$main['Category'].'</td>
            <td width = "9%">'.$main['Rental Amount'].'</td>
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
    // end of controller
}
