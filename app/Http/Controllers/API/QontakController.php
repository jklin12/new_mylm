<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QontakController extends Controller
{
    public function getCustName(Request $request)
    {

        $status = false;
        $message = 'Nomor telpon tidak ditemukan';

        $validateUser = Validator::make(
            $request->all(),
            [
                'phone_number' => 'required|phone_number|numeric',
            ],
            [
                'phone_number.phone_number' => 'Phone number format invalid',

            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $custData = Customer::where('cust_phone', '+' . $request->input('phone_number'))
            // ->where('cust_bill_phone', '+'.$request->input('phone_number'))
            ->first();


        $data = [];
        if (isset($custData->cust_name)) {
            $status = true;
            $message = 'Nomor telpon Ditemukan';
            $data['cust_name'] = $custData->cust_name;
        }



        $load['status'] = $status;
        $load['message'] = $message;
        $load['data'] = $data;

        return response()
            ->json($load);
    }

    public function getBillInfo(Request $request)
    {
        $status = false;
        $message = 'Nomor pelanggan tidak ditemukan';

        $validateUser = Validator::make(
            $request->all(),
            [
                'cust_number' => 'required|alpha_num',
            ],

        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $data = [];
        $latestInv = DB::table('t_invoice_porfoma')
            ->select('cust_number', 'inv_number', DB::raw('MAX(inv_post) as last_inv'))
            ->groupBy('inv_number');

        $piData = DB::table('t_customer')
            ->selectRaw('t2.sp_nom,t2.inv_number, t2.inv_status,t2.inv_start,t2.inv_end,t_customer.cust_number,t_customer.cust_name,t_customer.cust_email,t_customer.cust_address,t_customer.cust_city,t_customer.cust_prov,t_customer.cust_zip,t_customer.cust_hp,sum(t_inv_item_porfoma.ii_amount) as totals,sp_code ')
            ->leftJoinSub($latestInv, 't1', function ($join) {
                $join->on('t_customer.cust_number', '=', 't1.cust_number');
            })

            ->leftJoin('t_invoice_porfoma as t2', 't1.inv_number', '=', 't2.inv_number')

            ->leftJoin('t_inv_item_porfoma', function ($join) {
                $join->on('t2.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);;
            })
            ->whereRaw("t_customer.cust_number = '" . $request->input('cust_number') . "'")
            ->groupBy('t2.inv_number')
            ->orderByDesc('t2.inv_start')
            ->first();

        if (isset($piData->inv_number)) {


            $url = '';
            $statusWord = 'Lunas';
            if ($piData->inv_status == 0) {
                $originalCode = $piData->inv_number . ';' . $piData->inv_number;
                $encryptionCode = urlencode(base64_encode($originalCode));
                $url = 'https://pay.lifemedia.id/pay?code=' . $encryptionCode;
                $statusWord = 'Belum Lunas';
            } elseif ($piData->inv_status == 2) {
                $statusWord = 'Expired';
            }

            $status = true;
            $message = 'Invoice Pelanggan Ditemukan';

            $data['cust_number'] = $piData->cust_number;
            $data['cust_name'] = $piData->cust_name;
            $data['inv_number'] = $piData->inv_status;
            $data['inv_status_word'] = $statusWord;
            $data['date_start'] = Carbon::parse($piData->inv_start)->isoFormat('D MMMM Y');
            $data['date_end'] = Carbon::parse($piData->inv_end)->isoFormat('D MMMM Y');
            $data['sp_code'] = $piData->sp_code;
            $data['total'] = $piData->totals;
            $data['url'] = $url;
            $data['message'] = '*Info Billing Life media*';
        }



        $load['status'] = $status;
        $load['message'] = $message;
        $load['data'] = $data;

        return response()
            ->json($load);
    }

    public function postComplain(Request $request)
    {
        $status = false;
        $message = 'Nomor pelanggan tidak ditemukan';

        $validateUser = Validator::make(
            $request->all(),
            [
                'cust_number' => 'required|alpha_num',
                'cmp_type' => 'required|numeric',
                'description' => 'required',
            ],

        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $data = [];
        $custData = Customer::where('t_customer.cust_number', $request->input('cust_number'))
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')->first();

        if (isset($custData->cust_name)) {
            $status = true;
            $lastCmp = DB::table('t_complain')
                ->whereRaw("MONTH(cmp_received) = '" . date('m') . "'")
                ->whereRaw("YEAR(cmp_received) = '" . date('Y') . "'")
                ->orderByDesc('cmp_number')
                ->first();
            $explodeCmpNumb = explode('/', $lastCmp->cmp_number);
            $lastNum = substr($explodeCmpNumb[0], -5);

            $newCmpNum = 'KEL' . sprintf('%05d', $lastNum + 1) . '/' . date('m') . '/' . date('y');

            $subject =  $request->input('cmp_type') == 1 ? 'Intenet' : 'TV' ;

            $postVal['cmp_number'] = $newCmpNum;
            $postVal['cust_number'] = $custData->cust_number;
            $postVal['sp_code'] = $custData->sp_code;
            $postVal['cmp_subject'] ='Keluhan ' . $subject. ' Dari WA BOT';
            $postVal['cmp_complain1'] = $request->input('cmp_type');
            
            $postVal['cmp_received'] = date('Y-m-d H:m:s');
            $postVal['cmp_desc'] = $request->input('description');
            $postVal['cmp_via'] = 4;
            $postVal['cmp_type'] = 12;
            $postVal['cmp_priority'] = 2;
            $postVal['cmp_source'] = 2;
            
            $insert = DB::table('t_complain')->insert($postVal);
            if ($insert) {
                $status = true;
                $message = 'Add Complain success';
                $data['cust_number'] = $custData->cust_number;
                $data['sp_code'] = $custData->sp_code;
                $data['cust_name'] = $custData->cust_name;
                $data['cust_address'] = $custData->cust_address;
                $data['cust_phone'] = $custData->cust_phone;
                $data['cmp_type'] = $postVal['cmp_complain1'];
                $data['description'] = $postVal['cmp_desc'];
            }
        }

        $load['status'] = $status;
        $load['message'] = $message;
        $load['data'] = $data;

        return response()
            ->json($load);
    }
}
