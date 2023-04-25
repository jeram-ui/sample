<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Api\Scheduler;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\GlobalController;
use App\Laravue\JsonResponse;
use PDF;

class MemorialListingcontroller extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $sched_db;
    private $empid;
    protected $G;

    public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
    }
    public function show(Request $request)
    {
        try {
            $data =  db::table($this->lgu_db.'.appointments')
                ->select('*',
                        db::raw('DATEDIFF(DATE_ADD(date_of_death,INTERVAL 5 YEAR),NOW()) AS days_left'),
                        db::raw("DATE_ADD(`date_of_death`,INTERVAL 5 YEAR )AS 'Expiration'"),
                        db::raw("DATE_ADD(`date_of_death`,INTERVAL 10 YEAR )AS 'Expirationten'"))
                ->where('Status', 0)
                ->where('sched_group', 7)
                ->orderBy("date_of_death", "asc")
                ->get();
            return response()->json(new jsonresponse($data));
        } catch (\Exception $e) {

            return response()->json(new jsonresponse(['Message' => 'Error Saving Data!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
}
