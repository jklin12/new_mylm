<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use DataTables;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
    var $jenisIdentitas = [1 => 'KTP', 'SIM', "Passport", 'Lainya'];
    var $jenisAccount = [1 => 'Personal', 'Perusahaan', "Pemkot", 'Lainya'];
    var $baseUrl = 'https://service-chat.qontak.com/api/open/v1/';
    var $token = 'MtJTjHbFwDO3CHCKnshWujjdovWCx_d8LmPOA2BRd7c';
    var $chanelId = 'f08493be-7d5e-4e08-8c30-30d31557b7f0';


    public function index(Request $request)
    {
        $title = 'Data Pelanggan';
        $subTitle = 'Data seluruh Pelanggan lifemedia';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $arrfield = $this->arrField();
        $i = 0;
        $tableColumn[$i]['data'] = 'DT_RowIndex';
        $tableColumn[$i]['name'] = 'DT_RowIndex';
        $tableColumn[$i]['orderable'] = 'false';
        $tableColumn[$i]['searchable'] = 'false';
        foreach ($arrfield as $key => $value) {
            $i++;
            $tableColumn[$i]['data'] = $key;
            $tableColumn[$i]['name'] = $value['label'];
            $tableColumn[$i]['orderable'] = $value['orderable'];
            $tableColumn[$i]['searchable'] = $value['searchable'];
        }
        $tableColumn[$i + 1]['data'] = 'detail';
        $tableColumn[$i + 1]['name'] = 'detail';

        $load['arr_field'] = $arrfield;
        $load['table_column'] = json_encode($tableColumn);
        //dd($load['table_column']);

        return view('pages/customer/index', $load);
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::select('t_customer.cust_number', 'cust_name', 'cust_address', 'cust_phone', 'sp_code', 'cust_hp', 'cupkg_status', 'cupkg_svc_begin')
                ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
                ->latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('cupkg_status', function ($user) {
                    return $user->cupkg_status ? $this->arrStatus[$user->cupkg_status] : '';
                })
                ->editColumn('cupkg_svc_begin', function ($user) {
                    return $user->cupkg_svc_begin ? with(new Carbon($user->cupkg_svc_begin))->isoFormat('dddd, D MMMM Y') : '';
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('customer-detail', $row->cust_number) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })
                ->rawColumns(['detail'])
                ->make(true);
        }
    }

    public function detail(Request $request, $cust_number)
    {
        $title = 'Detail Pelanggan ' . $cust_number;
        $subTitle = '';
        $name = Route::currentRouteName();

        $messageTemplate = $this->getMessageTemplate();
        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $customer = Customer::leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('t_customer.cust_number', $cust_number)
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
                    $datas[$key] = $this->jenisIdentitas[$value];
                } else  if ($key == 'cupkg_acc_type') {
                    $datas[$key] = $this->jenisAccount[$value];
                } else  if ($key == 'cust_pop') {
                    $datas[$key] = $this->arrPop[$value];
                } else {
                    $datas[$key] = $value;
                }
            }
        }

        //dd($datas);
        $arrfield = $this->arrFieldDetail();
        //dd($arrfield);
        $load['datas'] = $datas;
        $load['arr_field'] = $arrfield;
        $load['message_template'] = $messageTemplate;

        return view('pages/customer/detail', $load);
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
                'value' => $subtitle . ' - ' . $customer->cust_number
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
                    $body[$key]['value'] = 'value_'.$key;
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
                session()->flash('success','Kirim Pesan Berhasil');
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

        $lastInv = DB::table('t_invoice_porfoma')
            ->select('cust_number', 'inv_number', DB::raw('MAX(inv_post) as last_inv'))

            ->groupBy('inv_number');

        $customer = DB::table('t_customer')->selectRaw('t_customer.cust_number, cust_name, trel_cust_pkg.sp_code, cust_hp,cupkg_status, cust_address, cust_phone,cupkg_svc_begin')
            //->where('t_customer.cust_pop', 5)
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->leftJoinSub($lastInv, 't1', function ($join) {
                $join->on('trel_cust_pkg.cust_number', '=', 't1.cust_number');
            })
            ->leftJoin('t_invoice_porfoma as t2', 't1.last_inv', '=', 't2.inv_post')
            ->orderByDesc('created')->get(10);

        print_r($customer);
    }
    private function arrField()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor',
                'orderable' => true,
                'searchable' => true
            ],
            'cust_name' => [
                'label' => 'Nama',
                'orderable' => false,
                'searchable' => true
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => false
            ],
            'cust_hp' => [
                'label' => 'Homepass',
                'orderable' => false,
                'searchable' => true
            ],
            'cupkg_status' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false
            ],
            'cust_address' => [
                'label' => 'Alamat',
                'orderable' => false,
                'searchable' => true
            ],
            'cust_phone' => [
                'label' => 'No Telp',
                'orderable' => false,
                'searchable' => true
            ],
            'cupkg_svc_begin' => [
                'label' => 'Mulai Layanan',
                'orderable' => true,
                'searchable' => false
            ],
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
                'title' => 'Informasi Acount',
                'data' => [
                    'sp_code' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Layanan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],

                    'cupkg_acc_type' => [
                        'form' => true,
                        'form_type' => 'text',
                        'label' => 'Jenis Acount',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true
                    ],
                    'cupkg_svc_begin' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Mulai Layanan',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cupkg_acct_manager' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Account Manager',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],
                    'cupkg_status' => [
                        'form' => true,
                        'form_type' => 'select',
                        'label' => 'Status',
                        'orderable' => true,
                        'searchable' => true,
                        'required' => true,
                    ],

                ],

            ]
        ];
    }
}
