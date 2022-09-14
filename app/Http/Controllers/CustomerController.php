<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Data Pengguna';
        $subTitle = 'Data seluruh pengguna lifemedia';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        //$datas = ImportInvoiceResult::latest()->get();
        //$load['datas']= $datas;
        $offset = $request->has('offset') ? $request->input('offset') : 10;


        $customer = Customer::selectRaw('t_customer.cust_number, cust_name, sp_code, cust_hp,cupkg_status, cust_address, cust_phone')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->orderByDesc('created')
            ->paginate($offset);

        $load['datas'] = $customer;

        return view('pages/customer/index', $load);
    }
    public function detail( Request $request)
    {
        $title = 'Detail Pengguna ' . $request->input('cust');
        $subTitle = '';
        $name = Route::currentRouteName();

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $customer = Customer::leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('t_customer.cust_number',$request->input('cust'))
            ->first();

        $load['datas'] = $customer;

        return view('pages/customer/detail', $load);
    }

    public function audit()
    {

        $lastInv = DB::table('t_invoice_porfoma')
            ->select('cust_number', 'inv_number', DB::raw('MAX(inv_post) as last_inv'))

            ->groupBy('inv_number');

        $customer = DB::table('t_customer')->selectRaw('t_customer.cust_number, cust_name, trel_cust_pkg.sp_code, cust_hp,cupkg_status, cust_address, cust_phone')
            //->where('t_customer.cust_pop', 5)
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->leftJoinSub($lastInv, 't1', function ($join) {
                $join->on('trel_cust_pkg.cust_number', '=', 't1.cust_number');
            })
            ->leftJoin('t_invoice_porfoma as t2', 't1.last_inv', '=', 't2.inv_post')
            ->orderByDesc('created')->get(10);

        print_r($customer);
    }
}
