<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use DataTables;

class CustomerController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
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
                    $actionBtn = '<a href="'.route('customer-detail','?cust='.$row->cust_number).'" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })
                ->rawColumns(['detail'])
                ->make(true);
        }
    }

    public function detail(Request $request)
    {
        $title = 'Detail Pelanggan ' . $request->input('cust');
        $subTitle = '';
        $name = Route::currentRouteName();

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $customer = Customer::leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('t_customer.cust_number', $request->input('cust'))
            ->first();

        $load['datas'] = $customer;

        return view('pages/customer/detail', $load);
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
}
