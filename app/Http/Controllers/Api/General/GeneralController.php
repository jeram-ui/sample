<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use PDF;

class GeneralController extends Controller
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

     /**
     * @OA\Get(   
     *    path="General/Business/getBusinessStatus",
     *    summary="Business Status",
     *    operationId="getBusinessStatus",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getBusinessStatus()
    {
      $list =  DB::select('call '. $this->lgu_db .'.btax_bstatus()');
      return response()->json(new JsonResponse($list));
    }

    /**
     * @OA\Get(   
     *    path="General/Business/getBusinessType",
     *    summary="Type of Business",
     *    operationId="getBusinessType",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getBusinessType()
    {
      $list =  DB::select('call '. $this->lgu_db .'.btax_bustype()');
      return response()->json(new JsonResponse($list));
    }

    /**
     * @OA\Get(   
     *    path="General/Business/getBusinessKind",
     *    summary="Kind of Businesses",
     *    operationId="getBusinessKind",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getBusinessKind()
    {
      $list =  DB::select('call '. $this->lgu_db .'.jay_display_cto_kind_business_setup()');
      return response()->json(new JsonResponse($list));
    }

    /**
     * @OA\Get(   
     *    path="General/Business/getofficeType",
     *    summary="Office Types",
     *    operationId="getofficeType",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getofficeType()
    {
        $list = DB::select('call '.$this->lgu_db.'.jay_display_ebplo_office_type_setup');
        return response()->json(new JsonResponse($list));
    }

    /**
     * @OA\Get(   
     *    path="General/Business/getBSPType",
     *    summary="BSP Types",
     *    operationId="getBSPType",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getBSPType()
    {
        $list = DB::select('call '.$this->lgu_db.'.jay_display_ebplo_bsp_setup');
        return response()->json(new JsonResponse($list));
    }
    /**
     * @OA\Get(   
     *    path="General/Business/getClassification",
     *    summary="Business Classification",
     *    operationId="getClassification",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getClassification()
    {
    $classification =  DB::select('call ' . $this->lgu_db . '.btax_classification()');
    return response()->json(new JsonResponse($classification));
    }
     /**
     * @OA\Get(   
     *    path="General/Others/getAlhabeticalFilter",
     *    summary="Alphabetical Letter",
     *    operationId="getAlhabeticalFilter",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getAlhabeticalFilter()
    {       
        $list =  DB::select('call '.$this->lgu_db.'.jay_temp_abstract_a_z()');      
        return response()->json(new JsonResponse($list));
    }
    
    /**
     * @OA\Get(   
     *    path="General/Others/getQuarter",
     *    summary="Display Per Quarter",
     *    operationId="getQuarter",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getQuarter()
  {
    $quarter =  DB::select('call ' . $this->lgu_db . '.btax_quarter()');
    return response()->json(new JsonResponse($quarter));
  }

/**
     * @OA\Get(   
     *    path="General/Market/getMarketBillType",
     *    summary="Market Billing Type",
     *    operationId="getMarketBillType",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getMarketBillType()
    {       
      $item = DB::table(''.$this->lgu_db.'.tbl_market_bill')->where('status','Active')->get();
      return response()->json(new JsonResponse($item));
    }
    /**
     * @OA\Get(   
     *    path="General/Market/getBuildingList",
     *    summary="List of Building",
     *    operationId="getBuildingList",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getBuildingList()
    {
        $list = DB::select('call '.$this->lgu_db.'.spl_getBldg_Property_joy');
        return response()->json(new JsonResponse($list));
    }
    /**
     * @OA\Get(   
     *    path="General/Market/getFloorBlock/{id}",
     *    summary="List of Floor or Block",
     *    operationId="getFloorBlock",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getFloorBlock($id)
    {
        $list = DB::select('call '.$this->lgu_db.'.spl_display_floor_block_joy(?)',array($id));
        return response()->json(new JsonResponse($list));
    }
    /**
     * @OA\Get(   
     *    path="General/Market/getBldgOwner/{id}",
     *    summary="List of Building Owner",
     *    operationId="getBldgOwner",
     *    tags={"General Data"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function getBldgOwner($id)
    {
        $list = DB::select('call '.$this->lgu_db.'.spl_getBill_Name_joy1(?)',array($id));
        return response()->json(new JsonResponse($list));
    }
  // end of controller
}
