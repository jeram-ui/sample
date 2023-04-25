<?php

namespace App\Http\Controllers\Api\Mod_legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;

use PDF;

class courtController extends Controller
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
    public function show()
    {
        $list = db::select("SELECT law_court_setup.*,law_court_type.`court_type`,`law_court_city_type`.`cityName`
        ,CONCAT(law_court_type.`court_type`,', ',`law_court_city_type`.`cityName`,', ',law_court_setup.`Region`)AS 'court_name'
        FROM " . $this->lgu_db . ".law_court_setup
        INNER JOIN " . $this->lgu_db . ".law_court_type ON(law_court_setup.`court_type` = law_court_type.`id`)
        INNER JOIN " . $this->lgu_db . ".law_court_city_type ON(law_court_city_type.`id` = law_court_setup.`Branch_Name`)
        WHERE law_court_setup.status = 0");
        return response()->json(new JsonResponse($list));
    }

    public function showCourtTypeList()
    {
        $list = db::table($this->lgu_db . '.law_court_type')
            ->where('law_court_type.status', '0')->get();
        return response()->json(new JsonResponse($list));
    }

    public function getType()
    {
        $list = db::table($this->lgu_db . '.law_court_type')
            ->where('law_court_type.status', '0')->get();
        return response()->json(new JsonResponse($list));
    }
    public function getCity()
    {
        $list = db::table($this->lgu_db . '.law_court_city_type')
            ->where('status', '0')->get();
        return response()->json(new JsonResponse($list));
    }
    public function store(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_court_setup')->insert($main);
            } else {
                db::table($this->lgu_db . '.law_court_setup')->where('id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function storeCourtType(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_court_type')->insert($main);
            } else {
                db::table($this->lgu_db . '.law_court_type')->where('id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function storeCourtCity(Request $request)
    {
        try {
            $main = $request->form;
            $idx = $main['id'];
            if ($idx == 0) {
                db::table($this->lgu_db . '.law_court_city_type')->insert($main);
            } else {
                db::table($this->lgu_db . '.law_court_city_type')->where('id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function cancelCourtType($id)
    {
        try {
            db::table($this->lgu_db . '.law_court_type')->where('id', $id)->update(['status' => 1]);
            return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function cancelCityType($id)
    {
        try {
            db::table($this->lgu_db . '.law_court_city_type')->where('id', $id)->update(['status' => 1]);
            return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function edit($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.law_court_setup')->where('id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {
        DB::table($this->lgu_db . '.law_court_setup')->where('id', $id)->update(['status' => 1]);
        return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }
}
