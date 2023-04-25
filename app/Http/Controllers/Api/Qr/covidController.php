<?php

namespace App\Http\Controllers\Api\Qr;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\db;
use App\Laravue\JsonResponse;
use App\Http\Controllers\Api\GlobalController;

class covidController extends Controller
{
    protected $G;
    public function __construct(GlobalController $global)
    {
        $this->G = $global;
    }
    public function update(Request $request)
    {
        $main = $request->main;
        $remarks =$main['finalRemarks'];
        $pk = $main['id'];
        $final ="RECOVERED";
        if ($remarks ==='RECOVERED') {
         $final = "RECOVERED";
             $datax = array(
               'date_positive'=>$main['date_positive'],
               'date_deceased'=>null,
               'date_recovered'=>$main['date_recovered'],
               'finalRemarks'=>$final
           );
        }else{
            $final = "DECEASED";
            $datax = array(
              'date_positive'=>$main['date_positive'],
              'date_deceased'=>$main['date_recovered'],
              'date_recovered'=>null,
              'finalRemarks'=>$final
          );
        }
       
        db::table('covid_entry_main')->where('id', $pk)->update($datax);
        return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
    }
    public function store(Request $request)
    {
        try {
            $main = $request->main;
            $swab = $request->swab;
            $pk = $main['id'];
            $type = $main['finalRemarks'];
            DB::beginTransaction();
            if ($pk === 0) {
                $main['date_positive'] =  $this->G->serverdatetime();
                db::table('covid_entry_main')->insert($main);
                $pk = DB::getPdo()->lastInsertId();
                foreach ($swab as $row) {
                    $swab = array(
                         'entry_id' => $pk,
                         'date_swab'=>$row['date_swab'],
                         'date_result'=>$row['date_result'],
                     );
                    DB::table('covid_entry_date_swab')->insert($swab);
                }
            } else {
                db::table('covid_entry_main')->where('id', $pk)->update($main);
                db::table('covid_entry_date_swab')->where('entry_id', $pk)->delete();
                foreach ($swab as $row) {
                    $swab = array(
                        'entry_id' => $pk,
                        'date_swab'=>$row['date_swab'],
                        'date_result'=>$row['date_result'],
                    );
                    DB::table('covid_entry_date_swab')->insert($swab);
                }
            }
            DB::commit();
            return response()->json(new jsonresponse(['Message' => 'Transaction Completed Successfully!','status'=>'success']));
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
    
    public function edit($id)
    {
        $data['main']= db::table('covid_entry_main')->where('id', $id)->get();
        $data['swab']= db::table('covid_entry_date_swab')->where('entry_id', $id)->get();
        return response()->json(new jsonresponse($data));
    }
    public function list()
    {
        $result = db::table('covid_entry_main')
        ->select('*', db::raw('concat(lastName,", ",ifnull(firstName,"")," ",ifnull(middleName,"")) as name'))
        ->where('status', 0)
        ->get();
        return response()->json(new jsonresponse($result));
    }
}
