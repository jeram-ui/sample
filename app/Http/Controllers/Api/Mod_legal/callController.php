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

class callController extends Controller
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
        $list = db::table($this->lgu_db.'.law_call_made')
        ->where('status','0')->get();
        return response()->json(new JsonResponse($list));
    }
  
    public function store(Request $request) 
    {
        try {
            $main = $request->form;
            $main['lawyer_type'] = "Lead Lawyer";
            $idx = $main['patient_id'];
            if ($idx == 0) {
               db::table($this->lgu_db .'.law_lawyer')->insert($main);
            } else {
              db::table($this->lgu_db .'.law_lawyer')->where('patient_id', $idx)->update($main);
            }
            return response()->json(new JsonResponse(['Message' => 'Transaction Successfully Save.', 'status' => 'success']));
        } catch (\Exception $err) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }  

    public function edit($id) 
    {   
        $data['main'] = DB::table($this->lgu_db.'.law_lawyer')->where('patient_id',$id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function cancel($id)
    {   
        DB::table($this->lgu_db.'.law_lawyer')->where('patient_id',$id)->update(['stat'=>0]);
      return response()->json(new JsonResponse(['Message' => 'Deleted Successfully.', 'status' => 'success']));
    }

   
}                