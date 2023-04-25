<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class BusinessInquiry extends Controller
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

    public function businessInfo(Request $request)
    {
        $busId = $request->busId;
     
        $data['main'] = DB::select('call '.$this->lgu_db.'.spl_jay_ebplo_Display_inquiry_new(?)', array($busId));
        
        $busNum =$data['main'][0]->{'business_number'};
        $data['detail'] = DB::select('call '.$this->lgu_db.'.jay_ebplo_DisplayBPA_dtail(?)', array($busNum));
   
        return response()->json(new JsonResponse($data));
    }

    public function transInquiry(Request $request)
    {
        $busId = $request->busId;
        $taxYear = $request->taxYear;
        
        $data['main'] = DB::select('call '.$this->lgu_db.'.jay_display_trasaction_inquiry(?,?)', array($busId,$taxYear));
             
        return response()->json(new JsonResponse($data));
    }

    public function ledgerHistory(Request $request)
    {
        $busId = $request->busId;
        $taxYear = $request->taxYear;
        
        $data['main'] = DB::select('call '.$this->lgu_db.'.jay_display_business_transaction_history(?,?)', array($busId,$taxYear));
     
        return response()->json(new JsonResponse($data));
    }

    public function inspectorate(Request $request)
    {
        $busName = $request->busName;
        $taxYear = $request->taxYear;
        
        $data['main'] = DB::select('call '.$this->lgu_db.'.spl_validation_tbl_num_jay(?,?)', array($busName,$taxYear));
             
        return response()->json(new JsonResponse($data));
    }
   
    // end of controller
}
