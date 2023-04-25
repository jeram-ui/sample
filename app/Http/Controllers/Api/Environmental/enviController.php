<?php

namespace App\Http\Controllers\Api\Environmental;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\GlobalController;
use Illuminate\Support\Facades\Auth;
use PDF;

class enviController extends Controller
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
        $this->general = $this->G->getGeneralDb();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function businessList(Request $request)
    {
        // dd($request);
        //$date = date("Y", strtotime($date));
        $dateNow = date("Y", strtotime($request->now));
        // Log::debug($dateNow);
        $business = $request->business;
        if ($request->business == 'All') {
            $business = '%';
        } else {
            $business = $request->business;
        }
        $list = DB::select('call ' . $this->lgu_db . '.jay_get_forAssessment_business_list1(?,?)', array($dateNow, $business));
        // $list = DB::select('call ' . $this->lgu_db . '.jay_get_forAssessment_business_list(?,?)', array($dateNow, $business));
        return response()->json(new JsonResponse($list));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function displayData()
    {
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_envi_certificate_brylle_All');
        return response()->json(new JsonResponse($list));
        //
    }

    public function filterData(Request $request)
    {
        $dateFr = $request->from;
        $dateTo = $request->to;
        $list = DB::select('call ' . $this->lgu_db . '.spl_display_envi_certificate_brylle1(?,?)', array($dateFr, $dateTo));
        return response()->json(new JsonResponse($list));
    }

    public function printMain(Request $request)
    {

        $data = $request->main;
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        <h2 align="center">Commercial/Industrial Establishment Report</2>
        <br></br>
        <br></br>
        <table border="1" cellpadding="2">
        <tr align="center">
        <th width = "6%">Application No.</th>
        <th width = "6%">Application Date</th>
        <th width = "5%">CEC No.</th>
        <th width = "10%">Business Name</th>
        <th width = "10%">Name of Owner</th>
        <th width = "10%">Business Address</th>
        <th width = "7%">Purpose</th>
        <th width = "7%">Business Category</th>
        <th width = "7%">Validity</th>
        <th width = "7%">Date of Issuance</th>
        <th width = "7%">Certificate Fee</th>
        <th width = "5%">OR No.</th>
        <th width = "6%">OR Date</th>
        <th width = "7%">Payment Status</th>
        </tr>
        <tbody>';
            foreach ($data as $row) {

                $main = ($row);
                $html_content .= '
            <tr>
            <td width = "6%">' . $main['ref_no'] . '</td>
            <td width = "6%">' . $main['app_date'] . '</td>
            <td width = "5%">' . $main['cec_no'] . '</td>
            <td width = "10%">' . $main['bus_name'] . '</td>
            <td width = "10%">' . $main['owner_name'] . '</td>
            <td width = "10%">' . $main['bus_address'] . '</td>
            <td width = "7%">' . $main['purpose'] . '</td>
            <td width = "7%">' . $main['bus_cat'] . '</td>
            <td width = "7%">' . $main['valid'] . '</td>
            <td width = "7%">' . $main['Issued Date'] . '</td>
            <td width = "7%">' . $main['Certificate Fee'] . '</td>
            <td width = "5%">' . $main['OR No'] . '</td>
            <td width = "6%">' . $main['OR Date'] . '</td>
            <td width = "7%">' . $main['Payment Status'] . '</td>
            </tr>';
            }
            $html_content .= '</tbody>
        </table>';
            PDF::SetTitle('Sample');
            PDF::AddPage('L');
            PDF::SetFont('times', '', 8);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['status' => 'error']));
        }
    }

    public function enviCertPrint($ID)
    {
        // $info = $request->id;
        //dd($ID);
        $data = DB::select('call ' . $this->lgu_db . '.spl_display_envi_certificate_brylle_id(?)', array($ID));
        $sigdata = DB::table($this->general . '.signatory_logs')
            ->where('form_id', '=', $ID)
            ->where('form_name', '=', 'Environmental Certificate')
            ->get();
        //dd($sigdata);
        foreach ($sigdata as $row) {
            $infosig = ($row);
        }
        //dd($infosig);
        foreach ($data as $row) {
            $info = ($row);
            // $date = $info->{'app_date'};
        }
        $logo = config('variable.logo');
        try {
            $html_content = '
        ' . $logo . '
        
        <h2 align="center"> CERTIFICATE </h2>
        <br>
        <br>
        <style>
        table{
            width:100%;
            padding:3px;
        }
        .caption-label{width: 15%
        }
        .caption-label-center{text-align: center;
        }
        .caption-line{width: 35%;
        border-bottom: 1px solid black;
        }          
        </style>
        <body>
        <table width ="100%">
        <br>
        <br>
        <tr style="height:25px" align="center">   
            <td style="width:5%">
            </td>
            <td style="width:50%">
            This CERTIFICATE is being issued upon the request of
            </td> 
            <td style="width:45%; border-bottom: 1px solid black" align="center">
            ' . $info->{'owner_name'} . '
            </td>                   
        </tr> 

        <tr style="height:25px" align="center">   
            <td style="width:10%">   
            owner of  
            </td>  
            <td style="width:80%; border-bottom: 1px solid black" align="center">                            
            ' . $info->{'bus_name'} . '
            </td>   
            <td style="width:10%" align="left">                            
            located at
            </td>                 
         </tr> 

         <tr style="height:25px" align="left">
            <td style="width:100%; border-bottom: 1px solid black" align="center">
            ' . $info->{'bus_address'} . '
            </td>   
        </tr> 
         
         <tr style="line-height:20px">
            <td style="width:100%"><span style="text-align:justify;">Davao City, has submitted the required Solid Waste Management Plans, Programs and implementation of the provisions of R.A. 9003, otherwise known as "THE ECOLOGICAL SOLID WASTE MANAGEMENT ACT OF 2000" and City Ordinance No. 0361-10, otherwise known as the "DAVAO CITY ECOLOGICAL SOLID WASTE MANAGEMENT ORDINANCE OF 2009" in order to lessen the potential adverse impacts of solid waste on the environment, subject to periodic monitoring of this Office.</span>
            </td> 
         </tr> 

         <tr style="line-height:25px">   
            <td style="width:5%"  align="left"> 
            </td> 
            <td style="width:50%" align="left">
            This CERTIFICATE is being issued upon the request of
            </td> 
            <td style="width:45%; border-bottom: 1px solid black" align="center">
            ' . $info->{'owner_name'} . '
            </td>                     
        </tr> 
        <tr style="height:25px">
            <td style="width:25%" align="left"> 
            in connection with the
            </td>
            <td style="width:75%; border-bottom: 1px solid black" align="center">
            ' . $info->{'purpose'} . '
            </td>
        </tr> 

        <tr style="height:25px">
           <td style="width:50%" align="left"> 
            of his/her Business Permit for the year
            </td>   
            <td style="width:50%; border-bottom: 1px solid black" align="center">                            
            ' . date("Y") . '
            </td>    
        </tr>
        <tr style="height:25px" align="center">   
            <td style="width:5%"> 
            </td> 
            <td style="width:20%">
            Issued this
            </td>    
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            ' . date("d") . '
            </td>
            <td style="width:10%">
            day of
            </td>
            <td style="width:15%; border-bottom: 1px solid black" align="center">                            
            ' . date("M") . '
            </td>
            <td style="width:10%; border-bottom: 1px solid black" align="center">                            
            ' . date("Y") . '
            </td>
            <td style="width:30%">
            at Davao City.
            </td>                    
        </tr>
         
        <br>
        <br>
        <br>
         <tr style="height:25px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
            ' . $infosig->{'head_name'} . '
            </td>                             
        </tr>

        <tr style="height:25px" align="left">   
            <td style="width:70%">            
            </td> 
            <td style="width:30%" align="center">
            ' . $infosig->{'head_position'} . '
            </td>                             
        </tr>

    </table>';
            PDF::SetTitle('CERTIFICATE');
            PDF::AddPage('P');
            PDF::SetFont('times', '', 10);
            PDF::writeHTML($html_content, true, true, true, true, '');
            PDF::Output(public_path() . '/print.pdf', 'F');
            return response()->json(new JsonResponse(['status' => 'success']));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse(['errormsg' => $e, 'status' => 'error']));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function cancelData($id)
    {
        $data['status'] = 'Cancelled';
        DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_main')
            ->where('id', $id)
            ->update($data);
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }
    public function store(Request $request)
    {
        try {
            //DB::beginTransaction();
            //dd($request->details);
            // dd($request);
            $id = $request->main['id'];
            $main = $request->main;
            $cto = $request->cto;
            $details = $request->details;
            if ($id > 0) {
                $this->update($id, $main, $details);
            } else {
                $this->save($main, $details, $cto);
            }
            //DB::commit();
            return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
        } catch (\Exception $err) {
            //DB::rollBack();
            return response()->json(new JsonResponse(['Message' => 'Error!', 'errormsg' => $err, 'status' => 'error']));
        }
    }
    public function save($main, $details, $cto)
    {

        //$row = $request->main; 
        //$details = $request->details;
        // $row = $main; 
        //         $main = array(
        //           'bus_id'  => $row['bus_id'],
        //           'owner_id'  => $row['owner_id'],
        //           'brgy_id' => $row['brgy_id'],
        //           'ref_no'  => $row['ref_no'],
        //           'app_date'  => $row['app_date'],
        //           'cec_no' => $row['cec_no'],
        //           'bus_name'  => $row['bus_name'],
        //           'bus_address'  => $row['bus_address'],
        //           'owner_name' => $row['owner_name'],
        //           'bus_cat'  => $row['bus_cat'],
        //           'purpose' => $row['purpose'],
        //           'valid' => $row['valid'],
        //         );

        //dd($cto);
        DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_main')->insert($main);
        $id = DB::getPdo()->lastInsertId();

        foreach ($details as $row) {
            $array = array(
                'main_id' => $id,
                'chk' => $row['chk'],
                'req_id' => 0,
                'req' => $row['req'],
            );
            DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_req')->insert($array);
        }



        foreach ($cto as $row) {
            //dd($row);
            if ($row['Include'] == true) {
                // dd($row);
                $bill = array(
                    'payer_type' => 'Business',
                    'payer_id' => $main['bus_num'],
                    'business_application_id' => $main['bus_id'],
                    'account_code' => $row['Account Code'],
                    'bill_description' => $row['Account Description'],
                    'net_amount' => $row['Initial Amount'],
                    'bill_amount' => $row['Fee Amount'],
                    'bill_month' => $main['app_date'],
                    'bill_number' => $main['cec_no'],
                    'transaction_type' => 'Environmental Certificate',
                    'ref_id' => $id,
                    'bill_id' => $id,
                    'include_from' => 'Others',
                );
                DB::table($this->lgu_db . '.cto_general_billing')->insert($bill);
            }

            $signatory = DB::select('Call ' . $this->lgu_db . '.cvl_get_signatory_mayor_head()');
            //dd($signatory);
            foreach ($signatory as $row) {
                $sign = array(
                    'form_id' => $id,
                    'form_name' => 'Environmental Certificate',
                    'bns_id' => $main['bus_id'],
                    'pp_id' => 0,
                    'user_id' => Auth::user()->id,
                    'head_id' => $row->envi_id,
                    'head_name' => $row->envi_name,
                    'head_position' => $row->envi_pos,
                    'mayor_id' => $row->mayor_id,
                    'mayor_name' => $row->mayor_name,
                    'mayor_position' => $row->mayor_pos,
                );
                // dd($this->lgu_db.'.signatory_logs');
                //dd($sign);
                DB::table($this->general . '.signatory_logs')->insert($sign);
            }
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction completed successfully.', 'status' => 'success']));
    }

    public function editData($id)
    {
        $data['main'] = DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_main')->where('id', $id)->get();
        $data['details'] = DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_req')
            ->select(
                'id as id',
                'chk',
                'req_id',
                'req'
            )
            ->where('main_id', $id)
            ->get();
        return response()->json(new JsonResponse($data));
    }

    public function update($id, $main, $details)
    {

        //$main = $request->main;
        //$details = $request->details;
        //$id = $request->main['id'];
        DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_main')
            ->where('id', $id)
            ->update([
                'bus_id'  => $main['bus_id'],
                'owner_id'  => $main['owner_id'],
                'brgy_id' => $main['brgy_id'],
                'ref_no'  => $main['ref_no'],
                'app_date'  => $main['app_date'],
                'cec_no' => $main['cec_no'],
                'bus_name'  => $main['bus_name'],
                'bus_address'  => $main['bus_address'],
                'owner_name' => $main['owner_name'],
                'bus_cat'  => $main['bus_cat'],
                'purpose' => $main['purpose'],
                'valid' => $main['valid'],
            ]);

        DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_req')
            ->where('main_id', $id)
            ->delete();

        foreach ($details as $row) {
            $array = array(
                'main_id' => $id,
                'chk' => $row['chk'],
                'req_id' => 0,
                'req' => $row['req'],
            );
            DB::table($this->lgu_db . '.tbl_cenro_envi_certificate_req')->insert($array);
        }
        return response()->json(new JsonResponse(['Message' => 'Transaction Completed Successfully.', 'status' => 'success']));
    }
}
