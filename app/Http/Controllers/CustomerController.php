<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\DataTables\Scopes\CustomerScope;
use App\Models\Customer;
use App\Models\HistoryPi;
use App\Models\InvoicePorfoma;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use DataTables;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
    var $jenisIdentitas = [1 => 'KTP', 'SIM', "Passport", 'Lainya'];
    var $jenisAccount = [1 => 'Personal', 'Perusahaan', "Pemkot", 'Lainya'];

    var $baseUrl = 'https://service-chat.qontak.com/api/open/v1/';
    var $token = 'MtJTjHbFwDO3CHCKnshWujjdovWCx_d8LmPOA2BRd7c';
    var $chanelId = 'f08493be-7d5e-4e08-8c30-30d31557b7f0';


    public function index(CustomerDataTable $dataTable, Request $request)
    {
        $title = 'Data Pelanggan';
        $subTitle = 'Data seluruh Pelanggan lifemedia';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $load['arr_pop'] = $this->arrPop;
        $load['cupkg_status'] = $request->has('cupkg_status') ? $request->input('cupkg_status') : '';
        $load['cust_pop'] = $request->has('cust_pop') ? $request->input('cust_pop') : '';
        $load['Kelurahan'] = DB::table('tlkp_kelurahan')->get()->toArray();
        $load['kecamatan'] = DB::table('tlkp_kecamatan')->get()->toArray();
        $load['arr_field'] = $this->arrField();

        //dd($load['kecamatan']);

        return $dataTable->addScope(new CustomerScope($request))->render('pages/customer/index', $load);
    }

    /*public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::select('t_customer.cust_number', 'cust_name', 'cust_address', 'cust_phone', 'sp_code', 'cust_hp', 'cupkg_status', 'cupkg_svc_begin', 'cust_pop', 'cupkg_acct_manager')
                ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
                ->latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('cupkg_status', function ($user) {
                    return $user->cupkg_status ? $this->arrStatus[$user->cupkg_status] : '';
                })
                ->editColumn('cust_pop', function ($user) {
                    return $user->cust_pop ? $this->arrPop[$user->cust_pop] : '';
                })
                ->editColumn('cupkg_svc_begin', function ($user) {
                    return $user->cupkg_svc_begin ? with(new Carbon($user->cupkg_svc_begin))->isoFormat('ddd, D MMM YY') : '';
                })
                ->addColumn('durasi', function ($user) {
                    $interval = '';
                    if ($user->cupkg_status != 5) {
                        $datetime1 = date_create($user->cupkg_svc_begin);
                        $datetime2 = date_create(date('Y-m-d'));
                        $interval = date_diff($datetime1, $datetime2);
                        return $interval->format('%m bulan, %d hari');
                    }
                    return $interval;
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('customer-detail', $row->cust_number) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })

                ->rawColumns(['detail', 'durasi'])
                ->make(true);
        }
    }*/

    public function map(Request $request)
    {
        $title = 'Data Pelanggan';
        $subTitle = 'Data seluruh Pelanggan lifemedia';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;


        $models =  Customer::select('t_customer.cust_number', 'cust_name', 'cust_address', 'cust_phone', 'sp_code', 'cust_hp', 'cupkg_status', 'cupkg_tech_coord')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('cupkg_tech_coord', '!=', '');
        if ($request->has('cupkg_status') && $request->input('cupkg_status')) {
            $models->whereIn('cupkg_status', $request->input('cupkg_status'));
        }
        if ($request->has('sp_code') && $request->input('sp_code')) {
            $models->whereIn('sp_code', $request->input('sp_code'));
        }
        if ($request->has('cust_pop') && $request->input('cust_pop')) {
            $models->whereIn('cust_pop', $request->input('cust_pop'));
        }
        if ($request->has('cust_kecamatan') && $request->input('cust_kecamatan')) {
            $models->whereIn('cust_kecamatan', $request->input('cust_kecamatan'));
        }
        if ($request->has('cust_kelurahan') && $request->input('cust_kelurahan')) {
            $models->whereIn('cust_kelurahan', $request->input('cust_kelurahan'));
        }
        $data = $models->latest()->get();



        $susunData = [];
        foreach ($data as $key => $value) {
            $explodeCoord  = explode(',', $value['cupkg_tech_coord']);
            //print_r($explodeCoord);die;
            $status = arrCustStatus($value['cupkg_status']);
            $susunData[$key]['type'] = 'Feature';
            $susunData[$key]['properties']['description'] = '<strong>' . $value['cust_number'] . '</strong>&nbsp;<span class="badge badge-' . $status[1] . '">' . $status[0] . '</span><p>' . $value['cust_name'] . '<br>' . $value['cust_address'] . '<br>' . $value['cust_phone'] . '<br>' . $value['sp_code'] . '<br></p>';
            $susunData[$key]['properties']['status'] = $this->arrStatus[$value['cupkg_status']];
            $susunData[$key]['geometry']['type'] = 'Point';
            $susunData[$key]['geometry']['coordinates'][] = isset($explodeCoord[1]) ? doubleval($explodeCoord[1]) : 0;
            $susunData[$key]['geometry']['coordinates'][] = isset($explodeCoord[0]) ? doubleval($explodeCoord[0]) : 0;
        }

        $load['datas'] = json_encode($susunData);
        $susunStatus = [];
        foreach (arrCustStatus() as $key => $value) {
            foreach ($value as $keys => $values) {
                $susunStatus[] = $values;
            }
        }
        //dd(count($susunData));
        $load['arr_pop'] = $this->arrPop;
        $load['cupkg_status'] = $request->has('cupkg_status') ? $request->input('cupkg_status') : '';
        $load['cust_pop'] = $request->has('cust_pop') ? $request->input('cust_pop') : '';
        $load['Kelurahan'] = DB::table('tlkp_kelurahan')->get()->toArray();
        $load['kecamatan'] = DB::table('tlkp_kecamatan')->get()->toArray();

        return view('pages/customer/map', $load);
    }

    public function detail(Request $request, $cust_number)
    {
        $title = 'Detail Pelanggan ' . $cust_number;
        $subTitle = '';
        $name = Route::currentRouteName();

        $messageTemplate = $this->getMessageTemplate();
        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $customer = Customer::where('t_customer.cust_number', $cust_number)
            ->first();

        $datas = [];
        if ($customer) {

            $customer = $customer->toArray();
            //dd($customer);
            foreach ($customer as $key => $value) {

                if ($key == 'cust_birth_date') {
                    $datas[$key] = $customer['cust_birth_place'] . ', ' . Carbon::parse($value)->isoFormat('D MMMM Y');
                } else if ($key == 'cupkg_svc_begin') {
                    $datas[$key] = Carbon::parse($value)->isoFormat('D MMMM Y');
                } else if ($key == 'cust_sex') {
                    $datas[$key] = $value == 1 ? 'Laki-Laki' : 'Perempuan';
                } else  if ($key == 'cust_ident_type') {
                    $datas[$key] = isset($this->jenisIdentitas[$value]) ? $this->jenisIdentitas[$value] : '';
                } else  if ($key == 'cupkg_status') {
                    $datas[$key] = $this->arrStatus[$value];
                } else  if ($key == 'cupkg_acc_type') {
                    $datas[$key] = $this->jenisAccount[$value];
                } else  if ($key == 'cust_pop') {
                    $datas[$key] = $this->arrPop[$value];
                } else {
                    $datas[$key] = $value;
                }
            }
            $response = Http::get('http://202.169.224.46:8080/index.php/onu/detailCust/' . $cust_number);
            $response = $response->object();

            $oltData = [];
            if (isset($response->status) && $response->status) {
                $responseData = $response->data;
                $oltData['onu_gpon'] = $responseData->onu_gpon;
                $oltData['onu_olt_ip'] = $responseData->onu_olt_ip;
            }
        }

        //dd($datas);
        $arrfield = $this->arrFieldDetail();
        //dd($arrfield);

        $load['cust_number'] = $cust_number;
        $load['datas'] = $datas;
        $load['arr_field'] = $arrfield;
        $load['message_template'] = $messageTemplate;
        $load['olt_data'] = $oltData ?? '';

        return view('pages/customer/detail', $load);
    }

    public function cupkg($cust_number)
    {
        $title = 'Akun Teknis ' . $cust_number;
        $subTitle = '';

        $customer = Customer::leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('t_customer.cust_number', $cust_number)
            ->get();

        $barangPinjam = DB::table('t_customer_borrow')
            ->where('cust_number', $cust_number)
            ->where('cb_recycle', 2)
            ->get();


        $cuin = DB::table('t_customer_inactive')
            ->where('cust_number', $cust_number)
            ->where('cuin_recycle', 2)
            ->get();
        //dd($customer);

        $ketidakaktifan = [];
        $keaktifan = [];
        foreach ($cuin->toArray() as $key => $value) {

            if ($value->cuin_type == 1) {
                $ketidakaktifan[$key] = $value;
            } elseif ($value->cuin_type == 2) {
                $keaktifan[$key] = $value;
            }
            # code...
        }

        //dd($customer->toArray());

        $arrfield = $this->arrFieldCupkg();

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['cust_number'] = $cust_number;
        $load['datas'] = $customer;
        $load['arr_field'] = $arrfield;
        $load['ke_aktif'] = $keaktifan;
        $load['ke_tidak_aktif'] = $ketidakaktifan;
        $load['cust_borrow'] = $barangPinjam;
        $load['list_layanan'] =  $pkg = DB::table('t_service_pkg')->where('sp_recycle', 2)->get();



        return view('pages/customer/cupkg', $load);
    }

    public function upgrade(Request $request)
    {
        request()->validate(
            [
                'cust_number' => 'required',
                'jenis' => 'required',
                'sp_code' => 'required',
                'inv_start' => 'required',
            ]
        );

        $invStart = $request->input('inv_start') ?? date('Y-m-d');
        $newtMonth   =date('Y-m-d', strtotime($invStart . ' +1 month'));
        $invEnd = date('Y-m-d', strtotime($newtMonth . ' -1 days'));
        $spCode = $request->input('sp_code');
        $jenis = $request->input('jenis');

        $porfoma = DB::table('t_customer')
            ->selectRaw('t_invoice_porfoma.inv_number,t_customer.cust_number,sp_nom,cust_bill_info,cupkg_status,_nomor')
            ->leftJoin('t_invoice_porfoma', 't_customer.cust_number', '=', 't_invoice_porfoma.cust_number')
            ->leftJoin('trel_cust_pkg', function ($join) {
                $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number');
            })
            //->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
            ->whereRaw("t_customer.cust_number = '" . $request['cust_number'] . "'")
            ->orderByDesc('inv_start')
            ->first();
        //dd($porfoma);


        if (isset($porfoma->inv_number)) {
            $lastPi = substr($porfoma->inv_number, -2) + 1;
        } else {
            $lastPi = 1;
        }

        $newPi = "PI" . $request['cust_number'] . date('my') . sprintf("%02d", $lastPi);

        $postVal['inv_number'] = $newPi;
        $postVal['cust_number'] = $porfoma->cust_number;
        $postVal['sp_code'] = $spCode;
        $postVal['inv_type'] = '2';
        $postVal['inv_due'] = $invStart;
        $postVal['inv_post'] = date('Y-m-d H:m:s');
        $postVal['inv_status'] = '0';
        $postVal['inv_start'] = $invStart;
        $postVal['inv_end'] = $invEnd;
        $postVal['inv_currency'] = "IDR";
        //$postVal['wa_sent'] = date('Y-m-d H:m:s');
        //$postVal['wa_sent_number'] = '6285600200913';
        $postVal['sp_nom'] = $porfoma->_nomor ?? '';
        $postVal['reaktivasi_pi'] = $jenis;
        //dd($postVal);


        $insertPi = DB::table('t_invoice_porfoma')->insert($postVal);

        $pkg = DB::table('t_service_pkg')->where('sp_code', $spCode)->first();

        $postValItem[0]['ii_type'] = '2';
        $postValItem[0]['inv_number'] = $newPi;
        $postValItem[0]['ii_info'] = 'Biaya Layanan ' . $spCode . ' Mbps ' . Carbon::parse($postVal['inv_start'])->isoFormat('D MMMM Y') . '-' . Carbon::parse($postVal['inv_end'])->isoFormat('D MMMM Y');
        $postValItem[0]['ii_amount'] = $pkg->sp_reguler;
        $postValItem[0]['ii_order'] = '1';
        $postValItem[0]['ii_recycle'] = '2';

        $postValItem[1]['ii_type'] = '7';
        $postValItem[1]['inv_number'] = $newPi;
        $postValItem[1]['ii_info'] = 'PPN 11 %';
        $postValItem[1]['ii_amount'] = ($pkg->sp_reguler * 11) / 100;
        $postValItem[1]['ii_order'] = '2';
        $postValItem[1]['ii_recycle'] = '2';

        //dd($postVal,$postValItem);
        $insertItemPi = DB::table('t_inv_item_porfoma')->insert($postValItem);

        if (isset($porfoma->cupkg_status) && ($porfoma->cupkg_status == 5)) {
            DB::table('trel_cust_pkg')->where('_nomor', $porfoma->_nomor)->update(['cupkg_status' => 8]);
        }

        session()->flash('success', 'Perubahan Layanan Berhasil Berhasil');
        return redirect(route('porfoma-detail', $newPi));
    }

    public function reaktivasi(Request $request)
    {

        request()->validate(
            [
                'cust_number' => 'required',
            ]
        );

        $type = $request->input('type') ?? '';

        $invStart = $request->input('inv_start') ?? date('Y-m-d');
        $newtMonth   =date('Y-m-d', strtotime($invStart . ' +1 month'));
        $invEnd = date('Y-m-d', strtotime($newtMonth . ' -1 days'));

        $porfoma = DB::table('t_customer')
            ->selectRaw('t_invoice_porfoma.inv_number,t_customer.cust_number,_nomor,cust_bill_info,trel_cust_pkg.sp_code,cupkg_status,cust_pop')
            ->leftJoin('t_invoice_porfoma', 't_customer.cust_number', '=', 't_invoice_porfoma.cust_number')
            ->leftJoin('trel_cust_pkg', function ($join) {
                $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number');
            })

            ->whereRaw("t_customer.cust_number = '" . $request['cust_number'] . "'")
            ->orderByDesc('inv_post')
            ->first();

        //dd($porfoma);
       
        if (isset($porfoma->inv_number)) {
            $lastPi = substr($porfoma->inv_number, -2) + 1;
        } else {
            $lastPi = 1;
        }

        $newPi = "PI" . $request['cust_number'] . date('my') . sprintf("%02d", $lastPi);

        if ($type == 'new') {
            HistoryPi::create(['cust_number'=> $porfoma->cust_number,'inv_number'=> $newPi]);
            if ($porfoma->inv_number ) {
                DB::table('t_invoice_porfoma')->where('inv_number', $porfoma->inv_number)->update(['inv_status' => 2]);
            }    
            
        }

        $postVal['inv_number'] = $newPi;
        $postVal['cust_number'] = $porfoma->cust_number;
        $postVal['sp_code'] = $porfoma->sp_code;
        $postVal['inv_type'] = '2';
        $postVal['inv_due'] = $invStart;
        $postVal['inv_post'] = date('Y-m-d H:m:s');
        $postVal['inv_status'] = '0';
        $postVal['inv_start'] = $invStart;
        $postVal['inv_end'] = $invEnd;
        $postVal['inv_currency'] = "IDR";
        //$postVal['wa_sent'] = date('Y-m-d H:m:s');
        //$postVal['wa_sent_number'] = '6285600200913';
        $postVal['sp_nom'] = $porfoma->_nomor ?? '';
        $postVal['reaktivasi_pi'] = $type == 'new' ? 0 : 1;
        //dd($type, $postVal);

        $insertPi = DB::table('t_invoice_porfoma')->insert($postVal);

        $pkg = DB::table('t_service_pkg')->where('sp_name', $porfoma->sp_code)->first();
        
        if ($porfoma->cust_pop != 3) {
            $biayaLayanan =  $pkg->sp_reguler;
        } else {
            $biayaLayanan = $porfoma->cupkg_bill_regular;
        }
        $postValItem[0]['ii_type'] = '2';
        $postValItem[0]['inv_number'] = $newPi;
        $postValItem[0]['ii_info'] = 'Biaya Layanan ' . $porfoma->sp_code . ' Mbps ' . Carbon::parse($postVal['inv_start'])->isoFormat('D MMMM Y') . '-' . Carbon::parse($postVal['inv_end'])->isoFormat('D MMMM Y');
        $postValItem[0]['ii_amount'] = $biayaLayanan;
        $postValItem[0]['ii_order'] = '1';
        $postValItem[0]['ii_recycle'] = '2';

        $postValItem[1]['ii_type'] = '7';
        $postValItem[1]['inv_number'] = $newPi;
        $postValItem[1]['ii_info'] = 'PPN 11 %';
        $postValItem[1]['ii_amount'] =   ($biayaLayanan * 11) / 100;
        $postValItem[1]['ii_order'] = '2';
        $postValItem[1]['ii_recycle'] = '2';

        //dd($postValItem);

        $insertItemPi = DB::table('t_inv_item_porfoma')->insert($postValItem);

        if (isset($porfoma->cupkg_status) && $porfoma->cupkg_status == 5) {
            DB::table('trel_cust_pkg')->where('_nomor', $porfoma->_nomor)->update(['cupkg_status' => 8]);
        }
        $message = $type == 'new' ? 'Sukses Generate Porfoma' : 'Sukses Reaktifasi';

       

        session()->flash('success', $message);
        return redirect(route('porfoma-detail', $newPi));

        //dd($postVal,$postValItem);
    }

    private function getMessageTemplate()
    {

        $datas = [];

        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ];

        $url = $this->baseUrl . 'templates/whatsapp';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        if (isset($error_msg)) {
            print_r($error_msg);
        }
        curl_close($curl);
        if ($response) {
            $arr = json_decode($response);
            foreach ($arr->data as $key => $value) {
                $datas[$key]['message_id'] = $value->id;
                $datas[$key]['name'] = $value->name;
            }
        }

        //dd($datas);
        return $datas;
    }

    public function messageForm(Request $request, $message_id, $cust_number)
    {

        $load['title'] = 'Follow Up Pelanggan';
        $load['message_id'] = $message_id;
        $load['cust_number'] = $cust_number;
        $subtitle = '';
        $keyForm = [];
        $customer = Customer::find($cust_number);
        try {


            $header = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ];

            $url = $this->baseUrl . 'templates/' . $message_id . '/whatsapp';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
            }
            if (isset($error_msg)) {
                print_r($error_msg);
            }
            curl_close($curl);
            if ($response) {
                $response = json_decode($response);
                //dd($response);

                $matches = array();
                preg_match_all('/\{{([^}]+)\}}/', $response->data->body, $matches);
                $keyForm = isset($matches[1]) ? $matches[1] : [];
                $subtitle = $response->data->name;
                $load['body'] = $response->data->body;
            }

            $required = [];
            $form = [];
            $form['to_name'] = [
                'form' => true,
                'form_type' => 'text',
                'label' => 'To Name',
                'orderable' => true,
                'searchable' => true,
                'required' => true,
                'value' =>  $customer->cust_number . ' - ' . $customer->cust_name
            ];

            if ($customer->cust_hp) {
                $phoneNumber = preg_replace("/[^A-Za-z0-9]/", "", $customer->cust_hp);
            } elseif ($customer->cust_bill_phone) {
                $phoneNumber = preg_replace("/[^A-Za-z0-9]/", "", $customer->cust_bill_phone);
            }

            if (substr($phoneNumber, 0, 1) === '0') {
                $phoneNumber = '62' . substr($phoneNumber, 1);
            } else {
                $phoneNumber = $phoneNumber;
            }

            $form['to_number'] = [
                'form' => true,
                'form_type' => 'text',
                'label' => 'To Number',
                'orderable' => true,
                'searchable' => true,
                'required' => true,
                'value' => $phoneNumber
            ];

            $required['to_name'] = 'required';
            $required['to_number'] = 'required';
            foreach ($keyForm as $key => $value) {
                $required[$value] = 'required';
                $form[$value] = [
                    'form' => true,
                    'form_type' => 'text',
                    'label' => 'Value ' . $value,
                    'orderable' => true,
                    'searchable' => true,
                    'required' => true
                ];
            }


            $load['form'] = $form;
            $load['required'] = $required;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }
        $load['sub_title'] = $subtitle;

        //dd($load);
        return view('pages.customer.message-form', $load);
    }

    public function sendMessage(Request $request, $message_id, $cust_number)
    {


        $toNumber = '';
        $toName = '';
        $body = [];
        foreach ($request->all() as $key => $value) {
            if ($key != '_token') {

                if ($key == 'to_number') {
                    $toNumber = $value;
                } else if ($key == 'to_name') {
                    $toName = $value;
                } else {
                    $body[$key]['key'] = $key;
                    $body[$key]['value_text'] = $value;
                    $body[$key]['value'] = 'value_' . $key;
                }
            }
        }

        $postVal = [
            'to_name' => $toName,
            'to_number' => $toNumber,
            'message_template_id' => $message_id,
            'channel_integration_id' => $this->chanelId,
            "language" => [
                "code" => "id"
            ],
            'parameters' => [
                'body' => array_values($body)
            ]
        ];

        //dd($postVal);
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ];

        $url = $this->baseUrl . 'broadcasts/whatsapp/direct';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postVal));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        if (isset($error_msg)) {
            print_r($error_msg);
        }
        curl_close($curl);
        if ($response) {
            $arr = json_decode($response, true);
            //dd($arr);
            if ($arr['status'] == 'success') {
                session()->flash('success', 'Kirim Pesan Berhasil');
                return redirect(route('customer-detail', $cust_number));
            } else {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['erorr' => 'Gagal Kirim Pesan']);
            }
        }
    }

    public function audit()
    {
        $invdata = DB::table('t_invoice_porfoma')
            ->select(DB::raw('t_invoice_porfoma.cust_number'), 'cupkg_status', 'inv_number', 'inv_post', 'inv_start', 'inv_end', 'inv_status', 'wa_sent', 'wa_sent_number')
            ->leftJoin('trel_cust_pkg', 't_invoice_porfoma.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('inv_status', '0')
            ->whereRaw("MONTH(inv_post) = '09'")
            ->whereRaw("YEAR(inv_post) = '2022'")
            ->get();

        $susunData = [];
        foreach ($invdata->toArray() as $key => $value) {
            $susunData[$key]['cust_number'] = $value->cust_number;
            $susunData[$key]['status_pelanggan'] = isset($this->arrStatus[$value->cupkg_status]) ? $this->arrStatus[$value->cupkg_status] : 'lainya';
            $susunData[$key]['inv_number'] = $value->inv_number;

            $susunData[$key]['terlambar'] =  date_diff(date_create(date('Y-m-d')), date_create($value->inv_start))->format("%a") . ' Hari';
            $susunData[$key]['inv_post'] = $value->inv_post;
            $susunData[$key]['inv_start'] = $value->inv_start;
            $susunData[$key]['inv_end'] = $value->inv_end;
            $susunData[$key]['inv_status'] = $value->inv_status == '0' ? 'Belum Bayar' : 'lainya';
            $susunData[$key]['wa_sent'] = $value->wa_sent;
            $susunData[$key]['wa_sent_number'] = $value->wa_sent_number;
        }
        $result = $this->downloadExcel($susunData);
        print_r($result);
    }
    public function cekTerlmabat()
    {

        $invdata = DB::table('t_invoice_porfoma')
            ->select(DB::raw('t_invoice_porfoma.cust_number'), 'cupkg_status', 'inv_number', 'inv_start', 'inv_end', 'inv_status', 'wa_sent', 'wa_sent_number')
            ->leftJoin('trel_cust_pkg', 't_invoice_porfoma.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('inv_status', '0')
            ->where('inv_start', '<', '2022-10-05')
            ->where('inv_recycle', '<>', '1')
            ->groupBy('inv_number')
            ->get();

        $susunData = [];
        foreach ($invdata->toArray() as $key => $value) {
            $susunData[$key]['cust_number'] = $value->cust_number;
            $susunData[$key]['status_pelanggan'] = isset($this->arrStatus[$value->cupkg_status]) ? $this->arrStatus[$value->cupkg_status] : 'lainya';
            $susunData[$key]['inv_number'] = $value->inv_number;

            $susunData[$key]['terlambar'] =  date_diff(date_create(date('Y-m-d')), date_create($value->inv_start))->format("%a") . ' Hari';
            $susunData[$key]['inv_start'] = $value->inv_start;
            $susunData[$key]['inv_end'] = $value->inv_end;
            $susunData[$key]['inv_status'] = $value->inv_status == '0' ? 'Belum Bayar' : 'lainya';
            $susunData[$key]['wa_sent'] = $value->wa_sent;
            $susunData[$key]['wa_sent_number'] = $value->wa_sent_number;
        }
        $result = $this->downloadExcel($susunData);
        print_r($result);
    }
    private function arrField()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => true
            ],
            'cust_name' => [
                'label' => 'Nama',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => true
            ],
            'cust_kecamatan' => [
                'label' => 'Kecamatan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_kelurahan' => [
                'label' => 'Kelurahan',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_rw' => [
                'label' => 'RW',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_rt' => [
                'label' => 'RT',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_address' => [
                'label' => 'Alamat',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => false
            ],
            'cust_phone' => [
                'label' => 'No Telp',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
                'visible' => true
            ],
            'cust_member_card' => [
                'label' => 'Member Card',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ],
            'cupkg_acct_manager' => [
                'label' => 'AM',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => true
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => true
            ],

            'status_plg' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                //'keyvaldata' => $this->arrStatus
                'visible' => true
            ],
            'cust_pop' => [
                'label' => 'POP',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                'visible' => true
                //'keyvaldata' => $this->arrPop

            ],
            'cupkg_svc_begin' => [
                'label' => 'Mulai Layanan',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => true
            ],
            'durasi' => [
                'label' => 'Lama Langganan',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ],
            'cuin_date' => [
                'label' => 'Tanggal Berhenti',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ],
            'cuin_reason' => [
                'label' => 'Alasan Berhenti',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ],
            'cuin_info' => [
                'label' => 'Info Behenti',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
                'visible' => false
            ]
        ];
    }

    public function arrFieldCupkg()
    {

        return $arrfield = [
            [
                'title' => 'Informasi Account',
                'data' => [
                    'sp_code' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Jenis Layanan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cupkg_acc_type' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Jenis Account',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                        'keyvaldata' => arrJenisAkun()
                    ],
                    'cupkg_svc_begin' => [
                        'form' => true,
                        'form_type' => 'date',
                        'label' => 'Mulai Layanan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cupkg_status' => [
                        'form' => true,
                        'form_type' => 'date',
                        'label' => 'Status Layanan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                        'keyvaldata' => arrCustStatus()
                    ],
                    'cupkg_acct_manager' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Account Manager',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                ]
            ]
        ];
    }
    public function arrFieldDetail()
    {

        return $arrfield = [
            [
                'title' => 'Informasi Pelanggan',
                'data' => [
                    'cust_number' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nomor Pelanggan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],

                    'cust_name' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nama Pelanggan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_pop' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'POP',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_member_card' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Member Card',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_company' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nama Perusahaan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_business' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Jenis Usaha',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_birth_date' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Tempat, Tanggal Lahir',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_sex' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Jenis Kelamin',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_job' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Pekerjaan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_ident_type' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Jenis Identitas',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_ident_number' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nomor Identitas',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                ],

            ],
            [
                'title' => 'Alamat Pelanggan',
                'data' => [
                    'cust_address' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Alamat',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],

                    'cust_zip' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Kode POS',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_city' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Kota',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_prov' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Kota',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_city' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Kabupaten',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_kecamatan' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Kecamatan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_kelurahan' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Kelurahan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_rw' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'RT',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_rt' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'RW',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_hp' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nomor Hp',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_phone' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Nomor Telepon',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_fax' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'FAX',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_email' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Email',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                ],

            ],
            [
                'title' => 'Informasi Penagihan',
                'data' => [
                    'cust_bill_contact' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Kontak Penagihan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_bill_address' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Alamat',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],

                    'cust_bill_zip' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Kode POS',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_bill_city' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Kota',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_bill_prov' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Kab',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_bill_fax' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'FAX',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_bill_hp' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'HP',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_bill_phone' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Phone',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cust_bill_email' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Email',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cust_bill_info' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Keterangan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],

                ],

            ],

        ];
    }

    private function downloadExcel($data)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $arrTitle = ['Nomor Pelanggan', 'Status Pelanggan', 'Nomor PI', 'Terlmabat', 'Tgl terbit', 'Inv Start', 'Inv End', 'Inv Status', 'Terkirim', 'Terkirim Ke'];
        $alphas = range('A', 'Z');
        $dateExport = Carbon::parse(date('Y-m-d H:m:i'))->isoFormat('dddd, D MMMM Y H:mm');

        $sheet->setCellValue('A1', 'Hasil Invoice Belum Bayar'); // Set kolom A1 dengan tulisan "DATA SISWA"
        $sheet->setCellValue('A2', 'Pada ' . $dateExport); // Set kolom A1 dengan tulisan "DATA SISWA"
        $sheet->mergeCells('A1:' . $alphas[count($arrTitle) - 1] . '1'); // Set Merge Cell pada kolom A1 sampai E1
        $sheet->mergeCells('A2:' . $alphas[count($arrTitle) - 1] . '2'); // Set Merge Cell pada kolom A1 sampai E1
        $sheet->getStyle('A1')->getFont()->setBold(true); // Set bold kolom A1
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A4', 'No.');
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        foreach (array_values($arrTitle) as $key => $value) {

            $sheet->setCellValue($alphas[$key + 1] . '4', $value);
            $sheet->getStyle($alphas[$key + 1] . '4')->getFont()->setBold(true);
            $sheet->getStyle($alphas[$key + 1] . '4')->getAlignment()->setHorizontal('center');
        }

        $num = 5;
        $numAlpa = 1;
        foreach ($data as $dKey => $dVal) {
            $sheet->setCellValue('A' . $num, $dKey + 1);
            $sheet->getStyle('A' . $num)->getAlignment()->setHorizontal('center');
            foreach ($dVal as $key => $value) {
                $sheet->setCellValue($alphas[$numAlpa] . $num, $value);
                $sheet->getStyle($alphas[$numAlpa] . $num)->getAlignment()->setHorizontal('left');
                $sheet->getColumnDimension($alphas[$numAlpa])->setAutoSize(true);
                $numAlpa++;
            }
            $numAlpa = 1;
            $num++;
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filePath = 'files/export/Generate Inv' . date(('Y-m-d')) . rand()  . '.xlsx';
        $writer->save($filePath);

        $export['status'] = true;
        $export['data'] = $filePath;
        return $export;
    }
}
