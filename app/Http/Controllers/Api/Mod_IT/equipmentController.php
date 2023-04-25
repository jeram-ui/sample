<?php

namespace App\Http\Controllers\Api\Mod_IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class equipmentController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $prfrmnce_db;


    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->prfrmnce_db = $this->G->getPerformance();
        $this->trk_db = $this->G->getTrkDb();
    }

    public function store(Request $request)
    {
        $form = $request->form;
        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->trk_db . '.it_equipment')
                ->where('id', $id)
                ->update($form);


        } else {
            DB::table($this->trk_db . '.it_equipment')->insert($form);
            $id = DB::getPdo()->LastInsertId();
        }
        return  $this->G->success();
    }
    public function storeSpecification(Request $request)
    {
        $form = $request->form;
        $id = $form['id'];
        if ($id > 0) {

            DB::table($this->trk_db . '.it_equipment_specifications')
                ->where('id', $id)
                ->update($form);


        } else {
            DB::table($this->trk_db . '.it_equipment_specifications')->insert($form);
            $id = DB::getPdo()->LastInsertId();
        }
        return  $this->G->success();
    }
    public function getEquipment(Request $request)
    {
        $list = DB::table($this->trk_db . '.it_equipment')
            ->where('it_equipment.status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getEquiptList()
    {
        $list = DB::table($this->trk_db . '.it_equipment')
            ->where('it_equipment.status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function getEquipmentsList()
    {
        $list = DB::table($this->trk_db . '.it_equipment')
            ->where('it_equipment.status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    // public function getSpecification(Request $request)
    // {
    //     $equipt_id = $request->equipment_id;
    //     $list = DB::select("call " . $this->trk_db . '.GetEquipmentSpecifications(?)', [$equipt_id]);
    //     return response()->json(new JsonResponse($list));
    // }

    public function removing($id)
    {
        db::table($this->trk_db . ".it_equipment_specifications")
            ->where('id' , $id)
            ->update(['status' => 1]);
        // $this->G->success();
    }
    public function removingEquip($id)
    {
        db::table($this->trk_db . ".it_equipment")
            ->where('id' , $id)
            ->update(['status' => 1]);
        // $this->G->success();
    }

    public function getSpecification($id)
    {
        $list = DB::table($this->trk_db . '.it_equipment_specifications')
            ->where('equipment_id',$id)
            ->where('it_equipment_specifications.status', 0)
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function cancel($id)
    {
        db::table($this->trk_db . '.it_equipment_specifications')
            ->where('id', $id)
            ->update(['it_equipment_specifications.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function CancelEquipment($id)
    {
        db::table($this->trk_db . '.it_equipment')
            ->where('id', $id)
            ->update(['it_equipment.status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    function Edit($id)
    {
        $list['formA'] = db::table($this->trk_db . '.it_equipment')
            // ->join($this->hr_db . '.employee_information', 'employee_information.PPID', 'it_preventive_maintenance.are_owner')
            // ->leftJoin($this->trk_db . '.it_equipment', 'it_equipment.id', 'it_preventive_maintenance.equipment')
            ->where("id", $id)
            ->get();


        return response()->json(new JsonResponse($data));
        log::debug($id);
    }
    
}
