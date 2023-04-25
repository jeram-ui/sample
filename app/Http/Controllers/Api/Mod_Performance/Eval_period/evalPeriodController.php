<?php

namespace App\Http\Controllers\Api\Mod_Performance\Eval_period;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class evalPeriodController extends Controller
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
    }

    public function store(Request $request)
    {
        $formx = $request->formx;


        $id = $formx['id'];
        if ($id > 0) {
            db::table($this->prfrmnce_db . ".evaluation_period")
                ->where('id', $id)
                ->update($formx);

        } else {
            db::table($this->prfrmnce_db . ".evaluation_period")->insert($formx);
            $id = DB::getPdo()->LastInsertId();

        }
    }

    public function removing($id)
    {
        db::table($this->prfrmnce_db . ".evaluation_period")
            ->where('id' , $id)
            ->update(['status' => 1]);
        // $this->G->success();
    }



    // public function store(Request $request)
    // {
    //     $formx = $request->formx;
    //     $id = $formx['id'];
    //     if ($id > 0) {

    //             db::table($this->prfrmnce_db .".evaluation_period")
    //             ->where("id",$id)
    //             ->delete();

    //         foreach ($formx as $key => $value) {
    //             $datx = array(
    //                 'id' => $value['id'],
    //                 'date_from'=>$value['date_from'],
    //                 'date_to'=>$value['date_to'],
    //             );
    //             db::table($this->prfrmnce_db .".evaluation_period")->insert($datx);
    //         }
    //     }
    // }

    public function getEvaluation()
    {
        $list = DB::table($this->prfrmnce_db.'.evaluation_period')
        ->select("*", db::raw('concat(date_from," - ", date_to) as evalName'))
        ->where('status', 0)
        ->get();

        return response()->json(new JsonResponse($list));
    }




 public function print(Request $request){
    try{


        $Template='';
        PDF::SetTitle('Sworn Statement of Assets, Liabilities and Net Worth');
        PDF::SetFont('helvetica', '', 8);
        PDF::AddPage('P');
        PDF::writeHTML($Template, true, 0, true, 0);
        PDF::Output(public_path() . '/prints.pdf', 'F');
        $full_path = public_path() . '/prints.pdf';
        if (\File::exists(public_path() . '/prints.pdf')) {
            $file = \File::get($full_path);
            $type = \File::mimeType($full_path);
            $response = \Response::make($file, 200);
            $response->header("Content-Type", $type);
            return $response;
        }

    } catch (\Exception $e) {
        return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
    }

   }

}
