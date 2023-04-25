<?php

namespace App\Http\Controllers\Api\Mayors;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Storage;
use File;
use PDF;
use Illuminate\Support\Facades\Log;
use ZipArchive;
class referralController extends Controller
{
    private $lgu_db;
    private $hr_db;
    private $trk_db;
    private $empid;
    private $general;
    protected $G;


    public function __construct(GlobalController $global)
    {
        $this->middleware('auth');
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
        $this->trk_db = $this->G->getTrkDb();
        $this->general = $this->G->getGeneralDb();
        $this->signatory = $this->G->signatoryReport();
        $this->LGUName = $this->G->LGUName();
        $this->mayors_db = $this->G->getMayorsDb();
    }

    public function show()
    {
        $list = DB::table($this->mayors_db . '.tbl_referral')
        ->select("*",db::raw('concat(Fname,", ",Mname," ",Lname) as Fullname'))
            ->where('tbl_referral.status', 'Active')
            ->get();
        return response()->json(new JsonResponse($list));
    }
    public function Edit($id)
    {
        $data['formA'] = db::table($this->mayors_db . '.tbl_referral')->where('id', $id)->get();

        return response()->json(new JsonResponse($data));
    }

    public function store(Request $request)
    {
        $form = $request->form;


        $id = $form['id'];
        if ($id > 0) {
            db::table($this->mayors_db . ".tbl_referral")
                ->where('id', $id)
                ->update($form);

        } else {
            $form['ref_no'] = $this->G->generateReferenceDirect('Ref',$this->mayors_db . '.tbl_referral',$this->G->serverdatetime(),'App_date');
            db::table($this->mayors_db . ".tbl_referral")->insert($form);
            $id = DB::getPdo()->LastInsertId();


        }
    }

    public function cancel($id)
    {
        db::table($this->mayors_db . '.tbl_referral')
            ->where('id', $id)
            ->update(['status' => 'CANCELLED']);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }


    public function print(Request $request)
    {
        try {

            $form = $request->itm;

            $infoData = db::table($this->mayors_db . '.tbl_referral')
                ->select("*",db::raw('concat(Fname,", ",Mname," ",Lname) as Fullname'))
                ->where('id', $form['id'])
                ->get();
            $info = "";

            foreach ($infoData as $key => $value) {
                $info = $value;
            }

            $Template = '<table cellpadding="1">
            <tr>
                <th width="32%" align="right">
                <img src="' . public_path() . '/img/logo1.png"  height="30" width="30">
                </th>
                <th width="38%" style="font-size:9pt;  word-spacing:30px" align="center">
                        Republic of the Philippines
                <br />
                ' . env('cityname', false) . '
                <br />

                ' . env('cityaddress', false) . '
                <br />

                    </th>

                <th align="left">
                <img src="' . public_path() . '/img/logo2.png"  height="30" width="45">
                </th>
             </tr>
             <tr>
                <th width="100%" style="font-size:14pt" align="center"><b>REFERRAL  FORM</b>
            </th>
             </tr>
             <tr>
                <td width="13%">Application Date</td>
                <td width="15%" style="border-bottom:1px solid black">'.(!empty($info->App_date) ? (date_format(date_create($info->App_date), "F d, Y")): "").'</td>
                <td width="22%"></td>
                <td width="38%" align="right">Application No.:</td>
                <td width="12%" style="border-bottom:1px solid black">'.$info->ref_no.'</td>
            </tr>
            <br/>
            </table>

            <table cellpadding="2">
                <tr>
                    <td width="7%">Name:</td>
                    <td width="93%" style="border-bottom:1px solid black">'.$info->Fullname.'</td>
                </tr>
                <br/>
                <tr>
                    <td width="12%">Date of Birth:</td>
                    <td width="35%" style="border-bottom:1px solid black">'.(!empty($info->DoB) ? (date_format(date_create($info->DoB), "F d, Y")): "").'</td>
                    <td width="3%"></td>
                    <td width="12%">Cellphone No.</td>
                    <td width="38%" style="border-bottom:1px solid black">'.$info->cell_no.'</td>
                </tr>
                <br/>
                <tr>
                    <td width="16%">Complete Address:</td>
                    <td width="84%" style="border-bottom:1px solid black">'.$info->Address.'</td>
                </tr>
                <br/>
                <tr>
                    <td width="8%">Purpose:</td>
                    <td width="92%"><u style="text-align: justify;">'.$info->purpose.'</u></td>
                </tr>

            </table>
            ';

            PDF::SetTitle('Referral Form');
            PDF::SetFont('helvetica', '', 9);
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
