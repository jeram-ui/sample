<?php

namespace App\Http\Controllers\Api\Mod_HR\SSALNW;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\log;

class SwornController extends Controller
{
    private $lgu_db;
    private $hr_db;


public function __construct(GlobalController $global)
    {
        $this->G = $global;
        $this->lgu_db = $this->G->getLGUDb();
        $this->hr_db = $this->G->getHRDb();
    }

    public function basicinfo(Request $request)
    {
        $list = DB::table($this->hr_db . '.employees')
        ->join($this->hr_db .'.employee_information','employee_information.PPID','employees.SysPK_Empl')
          ->where('SysPK_Empl',Auth::user()->Employee_id)
          ->get();
        return response()->json(new JsonResponse($list));
    }

    public function getsworn(Request $request)
    {
      $list = DB::table($this->hr_db .'.sworn_table')
    //   ->join($this->hr_db .'.sworn_unmarried', 'sworn_table.id', '=', 'sworn_unmarried.mainID')
    //   ->join($this->hr_db .'.sworn_assets', 'sworn_table.id', '=', 'sworn_assets.mainID')
    //   ->leftjoin($this->hr_db .'.sworn_assetsb', 'sworn_table.id', '=', 'sworn_assetsb.mainID')
    //   ->leftjoin($this->hr_db .'.sworn_liabilities', 'sworn_table.id', '=', 'sworn_liabilities.mainID')
    //   ->leftjoin($this->hr_db .'.sworn_businessinterest', 'sworn_table.id', '=', 'sworn_businessinterest.mainID')
    //   ->leftjoin($this->hr_db .'.sworn_relatives', 'sworn_table.id', '=', 'sworn_relatives.mainID')
        ->select('*','sworn_table.id')
        ->where('sworn_table.status', 0)
        ->where('emp_id',Auth::user()->Employee_id)
        ->get();
    // $list="";
      return response()->json(new JsonResponse($list));
    }

    public function Edit($id)
    {
        $data['declarant'] =db::table($this->hr_db .'.sworn_table')->where('id', $id)->get();
        $data['unmarried'] =db::table($this->hr_db .'.sworn_unmarried')->where('mainID', $id)->get();
        $data['assets'] =db::table($this->hr_db .'.sworn_assets')->where('mainID', $id)->get();
        $data['assetsb'] =db::table($this->hr_db .'.sworn_assetsb')->where('mainID', $id)->get();
        $data['liabilities'] =db::table($this->hr_db .'.sworn_liabilities')->where('mainID', $id)->get();
        $data['business'] =db::table($this->hr_db .'.sworn_businessinterest')->where('mainID', $id)->get();
        $data['relative'] =db::table($this->hr_db .'.sworn_relatives')->where('mainID', $id)->get();

        return response()->json(new JsonResponse($data));
    }

    public function Swornstore(Request $request)
    {
        $form = $request->form;
        $formx = $request->formx;
        $formA = $request->formA;
        $formB = $request->formB;
        $liabilities = $request->liabilities;
        $business = $request->business;
        $relative = $request->relative;
        $id = $form['id'];
        if ($id > 0) {
            db::table($this->hr_db .".sworn_table")
                ->where('id', $id)
                ->update($form);

                db::table($this->hr_db .".sworn_unmarried")
                ->where("mainID",$id)
                ->delete();

        foreach ($formx as $key => $value) {
                    $datx = array(
                        'mainID' => $id,
                        'Fullname'=>$value['Fullname'],
                        'birthdate'=>$value['birthdate'],
                        'age'=>$value['age'],
                    );
                    db::table($this->hr_db .".sworn_unmarried")->insert($datx);
                }

                db::table($this->hr_db .".sworn_assets")->where("mainID", $id)->delete();
        foreach ($formA as $key => $value) {
                    $datx = array(

                    'mainID' => $id,
                    'description1'=>$value['description1'],
                    'kind'=>$value['kind'],
                    'exactLoc'=>$value['exactLoc'],
                    'assessedValue'=>$value['assessedValue'],
                    'CurrentFair'=>$value['CurrentFair'],
                    'AcquisitionYear'=>$value['AcquisitionYear'],
                    'AcquisitionMode'=>$value['AcquisitionMode'],
                    'AcquisitionCost'=>$value['AcquisitionCost'],
                    );
                    db::table($this->hr_db .".sworn_assets")->insert($datx);
                }

                db::table($this->hr_db .".sworn_assetsb")->where("mainID", $id)->delete();
        foreach ($formB as $key => $value) {
                    $datx = array(

                        'mainID' => $id,
                        'Description2'=>$value['Description2'],
                        'YearAcquired'=>$value['YearAcquired'],
                        'AcquisitionCostamount'=>$value['AcquisitionCostamount'],
                    );
                    db::table($this->hr_db .".sworn_assetsb")->insert($datx);
                }
                db::table($this->hr_db .".sworn_liabilities")->where("mainID", $id)->delete();
        foreach ($liabilities as $key => $value) {
                    $datx = array(

                        'mainID' => $id,
                        'nature'=>$value['nature'],
                        'NameCreditor'=>$value['NameCreditor'],
                        'Ounstandingbalance'=>$value['Ounstandingbalance'],
                    );
                    db::table($this->hr_db .".sworn_liabilities")->insert($datx);
                }
                db::table($this->hr_db .".sworn_businessinterest")->where("mainID", $id)->delete();
        foreach ($business as $key => $value) {
                    $datx = array(

                        'mainID' => $id,
                        'BusinessBox'=>$value['BusinessBox'],
                        'NameEntity'=>$value['NameEntity'],
                        'BusinessAddress'=>$value['BusinessAddress'],
                        'NatureBusiness'=>$value['NatureBusiness'],
                        'DateAcqInt'=>$value['DateAcqInt'],
                    );
                    db::table($this->hr_db .".sworn_businessinterest")->insert($datx);
                }
                db::table($this->hr_db .".sworn_relatives")->where("mainID", $id)->delete();
        foreach ($relative as $key => $value) {
                    $datx = array(

                        'mainID' => $id,
                        'relativeBox'=>$value['relativeBox'],
                        'NameRelative'=>$value['NameRelative'],
                        'Relationship'=>$value['Relationship'],
                        'Position1'=>$value['Position1'],
                        'NameAgency'=>$value['NameAgency'],
                    );
                    db::table($this->hr_db .".sworn_relatives")->insert($datx);
                }

        } else {
            db::table($this->hr_db .".sworn_table")->insert($form);
            $id = DB::getPdo()->LastInsertId();

            foreach ($formx as $key => $value) {
                $datx = array(
                    'mainID' => $id,
                    'Fullname'=>$value['Fullname'],
                    'birthdate'=>$value['birthdate'],
                    'age'=>$value['age'],
                );
                db::table($this->hr_db .".sworn_unmarried")->insert($datx);
            }
            // $formx['mainID']=$id;
            // db::table($this->hr_db .".sworn_unmarried")->insert($formx);

            foreach ($formA as $key => $value) {
                $datx = array(

                    'mainID' => $id,
                    'description1'=>$value['description1'],
                    'kind'=>$value['kind'],
                    'exactLoc'=>$value['exactLoc'],
                    'assessedValue'=>$value['assessedValue'],
                    'CurrentFair'=>$value['CurrentFair'],
                    'AcquisitionYear'=>$value['AcquisitionYear'],
                    'AcquisitionMode'=>$value['AcquisitionMode'],
                    'AcquisitionCost'=>$value['AcquisitionCost'],
                );
                db::table($this->hr_db .".sworn_assets")->insert($datx);
            }

            foreach ($formB as $key => $value) {
                $datx = array(

                    'mainID' => $id,
                    'Description2'=>$value['Description2'],
                    'YearAcquired'=>$value['YearAcquired'],
                    'AcquisitionCostamount'=>$value['AcquisitionCostamount'],
                );
                db::table($this->hr_db .".sworn_assetsb")->insert($datx);
            }


            // $formz['mainID']=$id;
            // db::table($this->hr_db .".sworn_assets")->insert($formz);

            foreach ($liabilities as $key => $value) {
                $datx = array(

                    'mainID' => $id,
                    'nature'=>$value['nature'],
                    'NameCreditor'=>$value['NameCreditor'],
                    'Ounstandingbalance'=>$value['Ounstandingbalance'],
                );
                db::table($this->hr_db .".sworn_liabilities")->insert($datx);
            }

            // $liabilities['mainID']=$id;
            // db::table($this->hr_db .".sworn_liabilities")->insert($liabilities);

            foreach ($business as $key => $value) {
                $datx = array(

                    'mainID' => $id,
                    'BusinessBox'=>$value['BusinessBox'],
                    'NameEntity'=>$value['NameEntity'],
                    'BusinessAddress'=>$value['BusinessAddress'],
                    'NatureBusiness'=>$value['NatureBusiness'],
                    'DateAcqInt'=>$value['DateAcqInt'],
                );
                db::table($this->hr_db .".sworn_businessinterest")->insert($datx);
            }

            // $business['mainID']=$id;
            // db::table($this->hr_db .".sworn_businessinterest")->insert($business);

            foreach ($relative as $key => $value) {
                $datx = array(

                    'mainID' => $id,
                    'relativeBox'=>$value['relativeBox'],
                    'NameRelative'=>$value['NameRelative'],
                    'Relationship'=>$value['Relationship'],
                    'Position1'=>$value['Position1'],
                    'NameAgency'=>$value['NameAgency'],


                );
                db::table($this->hr_db .".sworn_relatives")->insert($datx);
            }

            // $relative['mainID']=$id;
            // db::table($this->hr_db .".sworn_relatives")->insert($relative);
        }
    }


    public function sworncancel($id)
    {
        db::table($this->hr_db . '.sworn_table')
            ->where('id', $id)
            ->update(['status' => 1]);
      return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

 public function print(Request $request){
    try{
        $form = $request->itm;
        $sworn = db::table($this->hr_db .'.sworn_table')
        ->select('*','id','Fname','Firstname','MName')
        ->where('id', $form['id'] )
        ->get();
        $swornData ="";

        $sworn_unmarried = db::table($this->hr_db . '.sworn_unmarried')
        ->where('mainID', $form['id'] )
        ->get();
        // $swornformx ="";

        $sworn_assets = db::table($this->hr_db . '.sworn_assets')
        ->where('mainID', $form['id'] )
        ->get();

        $sworn_assetsb = db::table($this->hr_db . '.sworn_assetsb')
        ->where('mainID', $form['id'] )
        ->get();

        $sworn_liabilities = db::table($this->hr_db . '.sworn_liabilities')
        ->where('mainID', $form['id'] )
        ->get();

        $sworn_businessinterest = db::table($this->hr_db . '.sworn_businessinterest')
        ->where('mainID', $form['id'] )
        ->get();

        $sworn_relatives = db::table($this->hr_db . '.sworn_relatives')
        ->where('mainID', $form['id'] )
        ->get();

    foreach ($sworn as $key => $value) {
            log::debug($value->Fname);
            $swornData= $value;
        }
    $unmarried ="";
    foreach ($sworn_unmarried as $key => $value) {
            $unmarried .=' <tr>
            <td width="5%"></td>
            <td width="40%" align="center" style="font-size:8pt; border-bottom:1px solid black;">'.$value->Fullname.'</td>
            <td width="4%"></td>
            <td width="25%" align="center" style="font-size:8pt; border-bottom:1px solid black;">'.$value->birthdate.'</td>
            <td width="4%"></td>
            <td width="20%" align="center" style="font-size:8pt; border-bottom:1px solid black;">'.$value->age.'</td>
            </tr>' ;
        }
            if(count($sworn_unmarried)< 4){
                for($i = count($sworn_unmarried); $i<4; $i++){
                    $unmarried .=' <tr>
            <td width="5%"></td>
            <td width="40%" align="center" style="font-size:8pt; border-bottom:1px solid black;"></td>
            <td width="4%"></td>
            <td width="25%" align="center" style="font-size:8pt; border-bottom:1px solid black;"></td>
            <td width="4%"></td>
            <td width="20%" align="center" style="font-size:8pt; border-bottom:1px solid black;"></td>
            </tr>' ;
                }
            }

    $assets = "";
    $assetsTOtal=0;
    foreach ($sworn_assets as $key => $value) {
        $assetsTOtal = $assetsTOtal + $value->AcquisitionCost;
            $assets .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->description1.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->kind.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->exactLoc.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. number_format($value->assessedValue, 2).'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. number_format($value->CurrentFair, 2).'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->AcquisitionYear.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->AcquisitionMode.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. number_format($value->AcquisitionCost, 2).'</td>
        </tr>';
        }

        if(count($sworn_assets)< 4){
            for($i = count($sworn_assets); $i<4; $i++){
                $assets .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
        </tr>';
        }
    }
    $assetsb = "";
    $assestbTotal=0;
    foreach ($sworn_assetsb as $key => $value) {
        $assestbTotal= $assestbTotal + $value->AcquisitionCostamount;
            $assetsb .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->Description2.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->YearAcquired.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. number_format($value->AcquisitionCostamount,2).'</td>
        </tr>';
        }

         if(count($sworn_assetsb)< 4){
            for($i = count($sworn_assetsb); $i<4; $i++){
                $assetsb .= '<tr>
                <td style="font-size:7pt;" height="30px" align="center"></td>
                <td style="font-size:7pt;" height="30px" align="center"></td>
                <td style="font-size:7pt;" height="30px" align="center"></td>
            </tr>';
        }
    }

    $liabilities = "";
    $liabilitiesTotal = 0;
    $Totalall = 0;
    foreach ($sworn_liabilities as $key => $value) {
        $liabilitiesTotal = $liabilitiesTotal + $value->Ounstandingbalance;
        $Totalall = $assetsTOtal + $assestbTotal - $liabilitiesTotal;
            $liabilities .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->nature.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->NameCreditor.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. number_format($value->Ounstandingbalance,2).'</td>
        </tr>';
        }
        if(count($sworn_liabilities)< 4){
            for($i = count($sworn_liabilities); $i<4; $i++){
                $liabilities .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
        </tr>';
        }
    }

    $business = "";
    foreach ($sworn_businessinterest as $key => $value) {
            $business .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->NameEntity.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->BusinessAddress.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->NatureBusiness.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->DateAcqInt.'</td>
        </tr>';
        }
        if(count($sworn_businessinterest)< 4){
            for($i = count($sworn_businessinterest); $i<4; $i++){
                $business .= '<tr>
                <td style="font-size:7pt;" height="30px" align="center"></td>
                <td style="font-size:7pt;" height="30px" align="center"></td>
                <td style="font-size:7pt;" height="30px" align="center"></td>
                <td style="font-size:7pt;" height="30px" align="center"></td>
            </tr>';
        }
    }

    $Relative = "";
    foreach ($sworn_relatives as $key => $value) {
            $Relative .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->NameRelative.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->Relationship.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->Position1.'</td>
            <td style="font-size:7pt;" height="30px" align="center">'. $value->NameAgency.'</td>
        </tr>';
        }
        if(count($sworn_relatives)< 4){
            for($i = count($sworn_relatives); $i<4; $i++){
                $Relative .= '<tr>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
            <td style="font-size:7pt;" height="30px" align="center"></td>
        </tr>';
        }
    }

        $Template='<table style="width:100%;">
        <tr>
           <td style="font:11pt;" align="center">
                <b>SWORN STATEMENT OF ASSETS, LIABILITIES AND NET WORTH</b>
                <br />
             <table width="100%">

            <tr>
                <td width="20%"> </td>
                <td width="15%" align="right"> As of </td>
                <td width="30%" style="border-bottom:1px solid black" align="center">April 30, 2022</td>
                <td width="35%"> </td>
            </tr>

             </table>
           </td>
        </tr>
                <tr>
                    <td width="100%" align="center" style="font-size:7pt">(Required by R.A. 6713)</td>
                </tr>
<br/>
        <tr>
            <td width="100%" style="font-size:8pt"><b> Note: </b> <i> Husband and wife who are both public officials and employees may file the required statements jointly or seprately. </i> </td>
        </tr>
    <table width="100%">
        <tr>
            <td width="13%"> </td>
            <td width="25%" style="font-size:9pt"><input type="checkbox" checked="'.$swornData->jointFiling.'" name="1" value="1"> Joint Filing</td>
            <td width="25%" style="font-size:9pt"><input type="checkbox" checked="'.$swornData->Sfiling.'" name="2" value="2"> Separate Filing</td>
            <td width="25%" style="font-size:9pt"><input type="checkbox" checked="'.$swornData->NApplicable.'" name="2" value="2"> Not Applicable</td>
            <td width="13%" style="font-size:8pt"> </td>
        </tr>
    </table>
<br />
<br />
    <table width="100%">
        <tr>
            <td width="12%"  style="font-size:8pt"><b> DECLARANT: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="40%" align="center">'.$swornData->Fname.', &nbsp; &nbsp;&nbsp;&nbsp; '.$swornData->Firstname.' &nbsp; &nbsp;&nbsp;&nbsp; '.$swornData->MName.'.</td>
            <td width="5%"> </td>
            <td width="18%"  style="font-size:8pt"><b> POSITION: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="25%" align="center">'.$swornData->position.'</td>
        </tr>
    </table>
    <table width="60%">
        <tr>
            <td width="20%"></td>
            <td width="27%" style="font-size:8pt;">
             (Family Name)
            </td>

            <td width="27%" style="font-size:8pt;">(First Name)</td>
            <td width="12%" style="font-size:8pt;">(M.I)</td>
            <td width="9%"> </td>
            <td width="30%" style="font-size:8pt;"><b> AGENCY/OFFICE: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="42%" align="center">'.$swornData->AOffice.'</td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td width="12%"  style="font-size:8pt"><b> ADDRESS: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="40%" align="center">'.$swornData->address.'</td>
            <td width="5%"> </td>
            <td width="18%" style="font-size:8pt;"><b> OFFICE ADDRESS: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="25%" align="center">'.$swornData->OAddress.'</td>
        </tr>


    </table>
    <table width="100%">
        <tr>
            <td width="12%"  style="font-size:9pt"></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="40%"></td>
            <td width="5%"> </td>
            <td width="18%" style="font-size:9pt;"></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="25%"></td>
        </tr>

    </table>
    <table width="100%">
        <tr>
            <td width="12%"  style="font-size:9pt"><b> Spouse: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="40%" align="center">'.$swornData->spouse.'</td>
            <td width="5.5%"> </td>
            <td width="17.5%"  style="font-size:8pt"><b>POSITION: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="25%" align="center">'.$swornData->Sposition.'</td>
        </tr>
    </table>

    <table width="60%">
        <tr>
            <td width="20%"></td>
            <td width="27%" style="font-size:8pt;">
                (Family Name)
            </td>

            <td width="27%" style="font-size:8pt;">(First Name)</td>
            <td width="12%" style="font-size:8pt;">(M.I)</td>
            <td width="9%"> </td>
            <td width="30%" style="font-size:8pt;"><b> AGENCY/OFFICE: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="42%" align="cemter">'.$swornData->SAOffice.'</td>
        </tr>
    </table>

    <table>
        <tr>
            <td width="12%"  style="font-size:9pt"></td>
            <td width="40%"></td>
            <td width="5%"> </td>
            <td width="18%" style="font-size:8pt;"><b> OFFICE ADDRESS: </b></td>
            <td style="border-bottom: 1px solid black; font-size:8pt" width="25%" align="center">'.$swornData->SOAddress.'</td>
        </tr>
    </table>
    <br />

    <table width="100%"  cellpadding="2">
        <tr>
            <td width="100%" style="border-bottom:1px solid black;"></td>
        </tr>

        <tr>
            <td width="100%" style="border-top:1px solid black; "></td>
        </tr>
    </table>

    <table style="width=100%;">
        <tr>

            <th width="100%" style="font-size:9pt;" align="center">
                <b><u>UNMARRIED CHILDREN BELOW EIGHTEEN (18) YEARS OF AGE LIVING IN DECLARANTS HOUSEHOLD</u></b>
            </th>

        </tr>
    </table>

    <table width="100%">
        <tr>
        <br />
            <td width="45%" align="center" style="font-size:8pt;"> &nbsp;&nbsp;&nbsp;&nbsp; <b>NAME</b> </td>
            <td width="30%" align="center" style="font-size:8pt;"><b> DATE OF BIRTH </b></td>
            <td width="25%" align="center" style="font-size:8pt;"><b> AGE </b></td>
        </tr>
        '.$unmarried.'
    </table>

    <table width="100%" cellpadding="2">
            <tr>
            <br />
                <td width="100%" style="border-bottom:1px solid black"></td>
            </tr>

            <tr>
                <td width="100%" style="border-top:1px solid black"></td>
            </tr>
    </table>

        <table style="width=100%;">
            <tr>

                <th width="100%" style="font-size:10pt;" align="center">
                    <b><u>ASSETS, LIABILITIES AND NETWORTH</u></b>
                </th>


            </tr>
            <tr>
                <td width="20%"></td>
                <td width="60%" align="center" style="font-size:8pt;" >
                <i>(Including those of the spouse and unmarried children below eighteen (18) years of age living and declaratns household)</i>
                </td>
                <td width="20%"></td>
            </tr>
            <tr>
                <td width="20%" style="font-size:10pt;"><b>1. ASSETS    </b></td>
            </tr>
            <tr>
                <td width="2%"></td>
                <td width="20%" style="font-size:10pt;"><b>a. &nbsp;&nbsp; Real Properties*    </b></td>
            </tr>

        </table>

        <table width="100%" border="1" cellpadding="2">
        <tr>
            <th  rowspan="2" style="font-size:7pt; background-color:grey;" align="center"> <b>DESCRIPTION</b> (e.g. lot, house and lot, condominium and improvements) </th>
            <th  rowspan="2" style="font-size:7pt; background-color:grey;"  align="center"> <b> KIND</b> <br> (e.g. residential, commercial, industrial, agricultural and mixed use)</th>
            <th  rowspan="2" style="font-size:8pt; background-color:grey;"  align="center"> <b> <br> EXACT LOCATION </b> </th>
            <th  style="font-size:7pt; background-color:grey;"  align="center"><b> ASSESED VALUE </br></th>
            <th  style="font-size:7pt; background-color:grey;"  align="center"><b> CURRENT FAIR MARKET VALUE </b></th>
            <th  colspan="2" style="font-size:8pt; background-color:grey;"  align="center" height="30"><b> ACQUISITION </b></th>
            <th  rowspan="2" style="font-size:8pt; background-color:grey;"  align="center"><b><br>  ACQUISITION COST </b></th>

        </tr>
        <tr>
            <td colspan="2" style="font-size:7pt; background-color:grey;" align="center">(As found in the Tax Declaration of Real Property)</td>
            <td style="font-size:7pt; background-color:grey;"  align="center"><b><br> YEAR </b></td>
            <td style="font-size:7pt; background-color:grey;"  align="center"><b><br> MODE </b></td>
        </tr>
        '.$assets.'
    </table>
    <br />

    <table>
        <tr>
            <td width="76%"></td>
            <td width="9%" style="font-size:10pt;"><b> Subtotal:</b></td>
            <td width="15%" style="border-bottom:1px solid black;" align="center">'.number_format($assetsTOtal,2).'</td>
        </tr>
    </table>

    <table>
        <tr>
            <td width="2%"></td>
            <td width="30%" style="font-size:10pt;"><b>b. &nbsp;&nbsp; Personal Properties* </b></td>
        </tr>
    </table>
    <br />
    <br />
    <table width="100%" border="1" cellpadding="2">
    <tr>
        <th style="font-size:8pt; background-color:grey;" height="30px"  align="center"><b> DESCRIPTION </b></th>
        <th style="font-size:8pt; background-color:grey;"  align="center"><b> YEAR ACQUIRED </b></th>
        <th style="font-size:8pt; background-color:grey;"  align="center"><b> ACQUISITION COST/AMOUNT </b></th>
    </tr>
        '.$assetsb.'
</table>
    <table>
        <tr>
            <td width="76%"></td>
            <td width="9%" style="font-size:10pt;"><b> Subtotal:</b></td>
            <td width="15%" style="border-bottom:1px solid black;" align="center">'.number_format($assestbTotal,2).'</td>

        </tr>
        <tr>
            <td width="76%" ></td>
            <td width="9%" style="font-size:10pt;"></td>
            <td width="15%" style="border-top:1px solid black;" ></td>
        </tr>
    </table>

    <table width="100%">
    <br />
    <br />
        <tr>

            <td width="2%"></td>
            <td width="30%" style="font-size:10pt;"><b> 2. &nbsp;&nbsp; LIABILITIES</b></td>

        </tr>
    </table>
    <table width="100%" border="1" cellpadding="2">
    <tr>
        <th style="font-size:8pt; background-color:grey;" height="20px"  align="center"><b> NATURE </b></th>
        <th style="font-size:8pt; background-color:grey;"  align="center"><b> NAME OF CREDITORS </b></th>
        <th style="font-size:8pt; background-color:grey;"  align="center"><b> OUTSTANDING BALANCE </b></th>
    </tr>
        '.$liabilities.'
</table>

    <table>
    <tr><br />
        <td width="76%"></td>
        <td width="9%" style="font-size:10pt;"><b> Subtotal:</b></td>
        <td width="15%" style="border-bottom:1px solid black;" align="center">'.number_format($liabilitiesTotal,2).'</td>
    </tr>

    <tr>
        <td width="41%"></td>
        <td width="44%" style="font-size:10pt;"><b> NET WORTH: Total Assets less Total Liabilities =</b></td>
        <td width="15%" style="border-bottom:1px solid black;" align="center">'.number_format($Totalall,2).'</td>
    </tr>
    <tr>
        <td width="2%"></td>
        <td width="50%" style="font-size:10pt;"><i> * Additional sheet/s may be used, if necessary.</i></td>

    </tr>
    </table>

    <table style="width=100%;">
    <tr>
    <br />
    <br />


        <th style="font-size:10pt;" align="center">
            <b> BUSINESS INTERESTS AND FINANCIAL CONNECTIONS</b>
        </th>
    </tr>
    <tr>
        <td width="20%"></td>
        <td width="60%" align="center" style="font-size:8pt;" >
        <i>(of Declarant/Declarants spouse/ Unmarried Children Below Eighteen (18)years of Age Living in Declarants Household)</i>
        </td>
        <td width="20%"></td>
    </tr>
    <tr>
        <td width="20%"></td>
        <td width="60%" align="center" style="font-size:8pt;" >
        <i><input type="checkbox" check="true" name="1" value="1"><b> I/We do not have any business interest or financial connection. </b></i>
        </td>
        <td width="20%"></td>
    </tr>
    </table>
<br />
<br />
<table width="100%" border="1" cellpadding="2">
    <tr>
        <th  style="font-size:7pt; background-color:grey;"  align="center"><b> NAME OF ENTITY/BUSINESS ENTERPRISE </b></th>
        <th  style="font-size:7pt; background-color:grey;"  align="center"><b> BUSINESS ADDRESS </b></th>
        <th  style="font-size:7pt; background-color:grey;"  align="center"><b> NATURE OF BUSINESS INTEREST &/OR FINANCIAL CONNECTION </b></th>
        <th  style="font-size:7pt; background-color:grey;"  align="center"><b> DATE OF ACQUISITION OF INTEREST OR CONNECTION </b></th>
    </tr>
        '.$business.'
</table>
<br />
<br />
<table style="width=100%;">
<tr>
<br>

<th style="font-size:10pt;" align="center">
        <b> RELATIVES IN THE GOVERNMENT SERVICE</b>
    </th>
</tr>
<tr>
    <td width="20%"></td>
    <td width="60%" align="center" style="font-size:8pt;" >
    <i>(Within the Fourth Degree of Consanguiniy or Affinity. Include also Bilas,Balae and Inso)</i>
    </td>
    <td width="20%"></td>
</tr>
<tr>
    <td width="20%"></td>
    <td width="60%" align="center" style="font-size:8pt;" >
    <i><input type="checkbox" check="true" name="1" value="1"><b> I/We do not know of any relative/s in the government service) </b></i>
    </td>
    <td width="20%"></td>
</tr>
</table>

<br />
<br />

<table width="100%" border="1" cellpadding="2">
        <tr>

            <th  style="font-size:7pt; background-color:grey;"  align="center"><b> NAME OF RELATIVE </b></th>
            <th  style="font-size:7pt; background-color:grey;"  align="center"><b> RELATIONSHIP </b></th>
            <th  style="font-size:7pt; background-color:grey;"  align="center"><b>  POSITION </b></th>
            <th  style="font-size:7pt; background-color:grey;"  align="center"><b> NAME OF AGENCY/OFFICE AND ADDRESS </b></th>
        </tr>
        '.$Relative.'
</table>

<br />

<p style="font-size:10pt; text-align:justify"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; I hereby certify that these are true and correct statements of my assets, liabilities, net worth,
 business interests and financial connections, including those of my spouse ad unmarried children below eigthteen (18)
 years of age living in my household, and that to the best of my knowledge, the above-enumerated are names of my relatives in the government within the fourth civil degree of consanguinity or affinity. </p>


<p style="font-size:10pt; text-align:justify"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;
    I hereby authorize the Ombudsman or his/her duly authorized representative
    to obtain and secure from all appropriate government agencies, including the Bureau of Internal Revenue such to include those of my spouse and unmarried children below 18 years of age living with me in my household covering prvious years to include the year I first assumed office in government. </p>

    <table width="100%">
    <tr>
        <td width="9%" align="left" style="font-size:9pt;"> Date: </td>
        <td width="30%" style="border-bottom:1px solid black"> </td>
    </tr>
</table>
<table width="100%">
    <tr>
    <br>
    <td width="45%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>
    <td width="10%"> </td>
    <td width="45%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>

    </tr>
</table>

<table width="100%">
<tr>

    <td width="45%" align="left" style="font-size:7pt;" align="center">(Signature of Declarant)</td>
    <td width="10%"> </td>
    <td width="45%" align="left" style="font-size:7pt;" align="center"> (Signature of Co-Declarant/Spouse) </td>

</tr>
</table>

<table width="100%">

<tr>
<br>
    <td width="18%" style="font-size:8pt"> Government Issued ID: </td>
    <td width="27%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>
    <td width="10%"></td>
    <td width="18%" style="font-size:8pt"> Government Issued ID: </td>
    <td width="27%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>

</tr>
<tr>
    <td width="18%" style="font-size:8pt"> ID No.: </td>
    <td width="27%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>
    <td width="10%"></td>
    <td width="18%" style="font-size:8pt"> ID No.:  </td>
    <td width="27%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>

</tr>

<tr>
    <td width="18%" style="font-size:8pt">Date Issued: </td>
    <td width="27%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>
    <td width="10%"></td>
    <td width="18%" style="font-size:8pt"> Date Issued:  </td>
    <td width="27%" align="left" style="font-size:7pt; border-bottom:1px solid black"></td>

</tr>

</table>

<table width="100%">
<tr>
<br />
    <td width="42%" style="font-size:10pt"><b> &nbsp;&nbsp;&nbsp; SUBSCRIBED AND SWORN</b> to before me this  </td>
    <td width="5%" style="border-bottom:1px solid black"></td>
    <td width="6%"> day of</td>
    <td width="6%" style="border-bottom:1px solid black"> </td>
    <td width="40%" style="font-size:10pt">, affiant exhibiting to me the above-stated </td>
    </tr>
<tr>

    <td style="font-size:10pt"> government issued identification card. </td>

</tr>
</table>

</table>';
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
