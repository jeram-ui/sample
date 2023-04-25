<?php

namespace App\Http\Controllers\Api\Scheduler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Image;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\log;
class AgendaController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $sched_db;
    private $empid;
    protected $G;
    private $path;
    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->sched_db = $this->G->getSchedulerDb();
        $this->path = env('LGU_FRONT');
    }
    public function store(Request $request)
    {
        $idx =$request->main['id'];
        $data = $request->main;
        $data['user_id'] = Auth::user()->id;
        try {
            DB::beginTransaction();
            if ($idx > 0) {
                db::table($this->sched_db.'.agenda')->where('id', $idx)->update($data);
            } else {
                db::table($this->sched_db.'.agenda')->insert($data);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $th) {
            return response()->json(new JsonResponse(['Message' => $th, 'status' => 'error']));
        }
    }

    public function show(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
 
        $data = DB::table($this->sched_db.'.agenda')
        ->join('users', 'users.id', '=', 'agenda.user_id')
        ->join($this->sched_db.'.tbl_organization_profile', 'tbl_organization_profile.id', '=', 'agenda.org_id')
        ->join('tbl_person_setup', 'tbl_person_setup.user_id', '=', 'agenda.user_id')
        ->select( db::raw('CONCAT("'.$this->path.'/images/client/",users.image_path) AS image_path'),'agenda.id', 'tbl_organization_profile.id as orgId', 'tbl_organization_profile.organization_name', 'agenda.trans_date', 'agenda.session_name', 'agenda.agenda_name', DB::raw('CONCAT(tbl_person_setup.lname, ", ", tbl_person_setup.fname," ",tbl_person_setup.mname) AS full_name'))
        ->whereBetween('trans_date', [$from, $to])
        ->get();

        return response()->json(new JsonResponse($data));
    }

    public function edit($id)
    {
        $data = db::table($this->sched_db.'.agenda')->where('id', $id)->get();
        return response()->json(new JsonResponse($data));
    }

    public function cancel($id)
    {
        $id = $id;
        $data['status'] = 'Cancelled';
        DB::table($this->sched_db.'.agenda')
            ->where('id', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function minutes($id)
    {
        $data['main'] = db::table($this->sched_db.'.agenda_minutes_main')->where('agenda_id', $id)->get();
        $data['details'] = db::table($this->sched_db.'.agenda_minutes_attendance')->where('agenda_id', $id)->get();
        $data['agenda'] = db::table($this->sched_db.'.agenda_minutes_agenda')->where('agenda_id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function displayIncomming()
    {
        $uid= Auth::user()->id;
        $data = db::select('call '.$this->sched_db.'.ransDisplayIncommingEvent(?,?)', array($uid,0));
        return response()->json(new JsonResponse($data));
    }
    public function getRef($date)
    {
        $results = DB::select(DB::raw("SELECT CONCAT('MN',DATE_FORMAT('$date','%y'),'-',LPAD(COUNT(*)+1,5,0)) as 'Ref' FROM  ".$this->sched_db.".agenda_minutes_main WHERE  YEAR(`trans_date`)=YEAR('$date') "));
        return response()->json(new JsonResponse($results));
    }
    public function getRefResolution($date)
    {
        $results = DB::select(DB::raw("SELECT CONCAT('RN',DATE_FORMAT('$date','%y'),'-',LPAD(COUNT(*)+1,5,0)) as 'Ref' FROM  ".$this->sched_db.".agenda_resolution_ord_main WHERE  YEAR(`trans_date`)=YEAR('$date') 
        and trans_type = 'Resolution'"));
        return response()->json(new JsonResponse($results));
    }
    public function getRefOrdinance($date)
    {
        $results = DB::select(DB::raw("SELECT CONCAT('ORD',DATE_FORMAT('$date','%y'),'-',LPAD(COUNT(*)+1,5,0)) as 'Ref' FROM  ".$this->sched_db.".agenda_resolution_ord_main WHERE  YEAR(`trans_date`)=YEAR('$date') 
        and trans_type = 'Ordinance'"));
        return response()->json(new JsonResponse($results));
    }
    public function getAttendance($id)
    {
        $results = DB::table($this->sched_db.".agenda_minutes_attendance")
        ->join('users', 'users.id', '=', 'agenda_minutes_attendance.uid')
        ->join('tbl_person_setup', 'tbl_person_setup.user_id', '=', 'users.id')
        ->select(
            'agenda_minutes_attendance.id',
            'uid AS uid',
            db::raw('get_fullname(tbl_person_setup.pkID) AS name'),
            db::raw('CONCAT("'.$this->path.'/images/client/",users.image_path) AS image')
        )
        ->where($this->sched_db.'.agenda_minutes_attendance.agenda_id', '=', $id)->get();
        return response()->json(new JsonResponse($results));
    }

    public function getMemberPerOrg($orgId)
    {
        $results = DB::table($this->sched_db.'.tbl_member_info')
        ->join('tbl_person_setup', 'tbl_person_setup.pkID', '=', 'tbl_member_info.pkID')
        ->join('users', 'users.id', '=', 'tbl_person_setup.user_id')
        ->select(
            db::raw('0 AS id'),
            'tbl_person_setup.user_id as uid',
            db::raw('get_fullname(tbl_person_setup.pkID) AS name'),
            db::raw('CONCAT("'.$this->path.'/images/client/",users.image_path) AS image')
        )
        ->where('tbl_member_info.orgID', '=', $orgId)
        ->groupBy('tbl_person_setup.user_id')->get()
        ;
        return response()->json(new JsonResponse($results));
    }
    public function getAttendanceResolution(Request $request)
    {
        $agenda_id = $request->id;
        $type= $request->type;
        $results = db::select("SELECT
        agenda_minutes_attendance.`uid` AS 'uid'
        ,get_fullname_by_uid(agenda_minutes_attendance.uid)AS 'name'
        ,IFNULL(att.`att`,0) AS 'att'
        ,IFNULL(att.`sponsor`,0) AS 'sponsor'
        ,IFNULL(att.`co_sponsor`,0) AS 'cosponsor'
        FROM ".$this->sched_db.".agenda_minutes_attendance
        LEFT JOIN
        (
        SELECT 
        `uid`
        ,`att`
        ,`sponsor`
        ,`co_sponsor`
        FROM ".$this->sched_db.".agenda_resolution_ord_details
        WHERE agenda_resolution_ord_details.`agenda_id` = ".$agenda_id."
        and trans_type = '".$type."'
        )att ON(att.uid = agenda_minutes_attendance.`uid`)
        WHERE `agenda_id` = ".$agenda_id."  ");
        return response()->json(new JsonResponse($results));
    }
    public function displayDone()
    {
        $uid= Auth::user()->id;
        $data = db::select('call '.$this->sched_db.'.ransDisplayIncommingEvent(?,?)', array($uid,1));
        return response()->json(new JsonResponse($data));
    }
    public function attend(Request $request)
    {
        $uid = Auth::user()->id;
        $main = $request->main;
        log::debug($main);
        $main['user_id'] = $uid;
        $main['attend'] = 1;
        db::table($this->sched_db.'.agenda_attend')->where('agenda_id', $main['agenda_id'])
        ->where('user_id', $main['user_id'])->delete();
        $data = db::table($this->sched_db.'.agenda_attend')->insert($main);
        return response()->json(new JsonResponse($data));
    }
    public function present(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->data;
            $dummy = $request->dummy;
            $aginda_id =$request->aginda_id;
            $datas = array();
            foreach ($dummy as $key => $value) {
                array_push($datas, $value['id']);
            }
            DB::commit();
            db::table($this->sched_db.'.agenda_minutes_attendance')->whereIn('id', $datas)->delete();
            foreach ($data as $key => $value) {
                $data = array(
                'agenda_id'=>$aginda_id,
                'uid'=>$value['uid'],
            );
                db::table($this->sched_db.'.agenda_minutes_attendance')->insert($data);
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message'=>'Successfully saved!','status'=>'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function disregard(Request $request)
    {
        $uid = Auth::user()->id;
        $main = $request->main;
        $main['user_id'] = $uid;
        $main['attend'] = 2;
        db::table($this->sched_db.'.agenda_attend')->where('agenda_id', $main['agenda_id'])
        ->where('user_id', $main['user_id'])->delete();
        $data = db::table($this->sched_db.'.agenda_attend')->insert($main);
        return response()->json(new JsonResponse($data));
    }
    public function uploadFile(Request $request)
    {
        $agendaId = $request->id;
        $trans_type = $request->trans_type;
        if ($files =  $request->file('file')) {
            foreach ($request->file('file') as $key => $file) {
                $originalImage= $file;
                $thumbnailPath = public_path().'/images/agenda/'. $agendaId ."/";
                $this->G->createFolder($thumbnailPath);
                $time = Str::random(5);
                $originalImage->move($thumbnailPath, $time. '.' .$originalImage->getClientOriginalExtension());
                $data = array(
                    'agenda_id'=>$agendaId,
                    'file_name'=>$originalImage->getClientOriginalName(),
                    'path_name' =>$time. '.' .$originalImage->getClientOriginalExtension(),
                );
                db::table($this->sched_db.'.agenda_minutes_docs')->insert($data);
            }
        }
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
    }
    public function uploaded($id)
    {
        // dd(public_path());
        
        $data = db::table($this->sched_db.'.agenda_minutes_docs')
        ->select('id', 'file_name as description', db::raw('concat("'.$this->path.'/images/agenda/'.$id.'/",path_name) as image'))
        ->where('agenda_id', $id)->get();
        return response()->json(new JsonResponse($data));
    }
    public function uploadedResolution($id)
    {
        $data = db::table($this->sched_db.'.agenda_resolution_ord_docs')
        ->select('id', 'file_name as description', db::raw('concat("'.$this->path.'/images/resolution/'.$id.'/",path_name) as image'))
        ->where('agenda_id', $id)
        ->where('trans_type', 'Resolution')
        ->get();
        return response()->json(new JsonResponse($data));
    }
    public function uploadedOrdinance($id)
    {
        $data = db::table($this->sched_db.'.agenda_resolution_ord_docs')
        ->select('id', 'file_name as description', db::raw('concat("'.$this->path.'/images/ordinance/'.$id.'/",path_name) as image'))
        ->where('agenda_id', $id)
        ->where('trans_type', 'Ordinance')
        ->get();
        return response()->json(new JsonResponse($data));
    }
    public function uploadedDelete($id)
    {
        $data = db::table($this->sched_db.'.agenda_minutes_docs')->where('id', $id)->first();
        if (file_exists(public_path() . '/images/agenda/' . $data->agenda_id.'/'. $data->path_name)) {
            unlink(public_path() . '/images/agenda/' . $data->agenda_id.'/'. $data->path_name);
        }
        $data = db::table($this->sched_db.'.agenda_minutes_docs')
        ->where('id', $id)->delete();
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
    }
    public function uploadedDeleteResolution($id)
    {
        $data = db::table($this->sched_db.'.agenda_resolution_ord_docs')->where('id', $id)->first();
        if (file_exists(public_path() . '/images/resolution/' . $data->agenda_id.'/'. $data->path_name)) {
            unlink(public_path() . '/images/resolution/' . $data->agenda_id.'/'. $data->path_name);
        }
        $data = db::table($this->sched_db.'.agenda_resolution_ord_docs')
        ->where('id', $id)->delete();
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
    }
    public function updateDocs(Request $request)
    {
        $docs['docs'] = $request->main['docs'];
        // dd($request);
        // $idx = $request->main['id'];
        // if ($idx == 0) {
        // db::table($this->sched_db.'.agenda_minutes_main')->insert($request->main);
        // } else {
        db::table($this->sched_db.'.agenda_minutes_main')
            ->where('agenda_id', $request->main['agenda_id'])
            ->update($docs);
        // }
     
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function saveMinutes(Request $request)
    {
        $idx = $request->main['id'];
        $agenda = $request->agenda;
        try {
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->sched_db.'.agenda_minutes_main')->insert($request->main);
                foreach ($agenda as $key => $value) {
                    $datax = array(
                     'agenda_id'=>$request->main['agenda_id'],
                     'agenda'=>$value['agenda'],
                 );
                    db::table($this->sched_db.'.agenda_minutes_agenda')->insert($datax);
                }
            } else {
                db::table($this->sched_db.'.agenda_minutes_main')
                 ->where('agenda_id', $request->main['agenda_id'])
                 ->update($request->main);
                db::table($this->sched_db.'.agenda_minutes_agenda')->where('agenda_id', $request->main['agenda_id'])->delete();
                foreach ($agenda as $key => $value) {
                    $datax = array(
                      'agenda_id'=>$request->main['agenda_id'],
                      'agenda'=>$value['agenda'],
                  );
                    db::table($this->sched_db.'.agenda_minutes_agenda')->insert($datax);
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
    public function uploadFileResolution(Request $request)
    {
        $agendaId = $request->id;
        $trans_type = $request->trans_type;
        if ($files =  $request->file('file')) {
            foreach ($request->file('file') as $key => $file) {
                $originalImage= $file;
                $thumbnailPath = public_path().'/images/'.$trans_type.'/'. $agendaId ."/";
                $this->G->createFolder($thumbnailPath);
                $time = Str::random(5);
                $originalImage->move($thumbnailPath, $time. '.' .$originalImage->getClientOriginalExtension());
                $data = array(
                    'agenda_id'=>$agendaId,
                    'file_name'=>$originalImage->getClientOriginalName(),
                    'path_name' =>$time. '.' .$originalImage->getClientOriginalExtension(),
                    'trans_type'=>$trans_type,
                );
                db::table($this->sched_db.'.agenda_resolution_ord_docs')->insert($data);
            }
        }
        return response()->json(new JsonResponse(['Message'=>'Successfully uploaded','status'=>'success']));
    }
    public function getResolutionData(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $data['main'] = db::table($this->sched_db.'.agenda_resolution_ord_main')
        ->where('agenda_id', $id)
        ->where('trans_type', $type)
        ->get();
        return response()->json(new JsonResponse($data));
    }
    public function saveResolution(Request $request)
    {
        $idx = $request->resolution['id'];
        $attendance = $request->att;
        $main = $request->resolution;
        try {
            DB::beginTransaction();
            if ($idx == 0) {
                db::table($this->sched_db.'.agenda_resolution_ord_main')->insert($main);
                $id = DB::getPdo()->lastInsertId();
                foreach ($attendance as $key => $value) {
                    $datax = array(
                     'res_id' => $id,
                     'agenda_id'=>$main['agenda_id'],
                     'uid'=>$value['uid'],
                     'att'=>$value['att'],
                     'sponsor'=>$value['sponsor'],
                     'co_sponsor'=>$value['cosponsor'],
                     'trans_type'=>$main['trans_type'],
                 );
                    db::table($this->sched_db.'.agenda_resolution_ord_details')->insert($datax);
                }
            } else {
                db::table($this->sched_db.'.agenda_resolution_ord_main')
                 ->where('agenda_id', $main['agenda_id'])
                 ->where('trans_type', $main['trans_type'])
                 ->update($main);
                db::table($this->sched_db.'.agenda_resolution_ord_details')->where('agenda_id', $main['agenda_id'])
                ->where('trans_type', $main['trans_type'])->delete();
                foreach ($attendance as $key => $value) {
                    $datax = array(
                        'res_id' => $idx,
                        'agenda_id'=>$main['agenda_id'],
                        'uid'=>$value['uid'],
                        'att'=>$value['att'],
                        'sponsor'=>$value['sponsor'],
                        'co_sponsor'=>$value['cosponsor'],
                        'trans_type'=>$main['trans_type'],
                  );
                    db::table($this->sched_db.'.agenda_resolution_ord_details')->insert($datax);
                }
            }
            DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $e, 'status' => 'error']));
        }
    }
}
