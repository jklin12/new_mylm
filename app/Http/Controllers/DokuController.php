<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;

class DokuController extends Controller
{
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
    var $arrPiStatus = ['Blum Bayar', 'Lunas', 'Expired'];
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];

    public function paymentRequest()
    {
        $title = 'Data Request Pembayaran';
        $subTitle = 'Via Doku Olny';

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
        $load['table_column'] = json_encode(array_values($tableColumn));

        return view('pages/doku/index', $load);
    }
    public function paymentRequestList(Request $request)
    {

        if ($request->ajax()) {
            $data =   DB::table('t_pay_request')->selectRaw('inv_numb, status_type, result_msg, t_pay_channel.description as channel_pembayaran, cupkg_status,amount,t_customer.cust_number,inv_status')
                ->leftJoin('t_invoice_porfoma', 't_pay_request.inv_numb', '=', 't_invoice_porfoma.inv_number')
                ->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
                ->leftJoin('trel_cust_pkg', function ($join) {
                    $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number')->on('trel_cust_pkg._nomor', '=', 't_invoice_porfoma.sp_nom');
                })
                ->leftJoin('t_pay_channel', 't_pay_request.payment_channel', '=', 't_pay_channel.code')
                ->groupBy('inv_numb', 't_customer.cust_number')
                ->orderByDesc('payment_time')
                ->limit(100)
                ->get();

            return Datatables::of($data)
                ->addIndexColumn()
                /*->editColumn('payment_status', function ($row) {
                    $badges = '';
                    if ($row->result_msg = 'SUCCESS') {
                        $badges = '<span class="label label-green">' . $row->result_msg . '</span>';
                    } else {
                        $badges = '<span class="label label-warning">' . $row->result_msg . '</span>';
                    }
                    return $badges;
                })*/
                ->addColumn('rp_amount', function ($row) {
                    return 'Rp. ' . (intval($row->amount));
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('pay-request-detail', 'inv=' . $row->inv_numb) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })
                ->addColumn('status_cust', function ($row) {
                    return $row->cupkg_status ? $this->arrStatus[$row->cupkg_status] : '';
                })
                ->addColumn('pi_status', function ($row) {
                    return $row->inv_status ? $this->arrPiStatus[$row->inv_status] : '';
                })
                ->rawColumns(['detail', 'status_cust', 'rp_amount', 'pi_status'])
                ->toJson();
            //->make(true);
            //die;
        }
    }


    public function paymentRequestDetail(Request $request)
    {

        $inv_number = $request->input('inv');
        $title = 'Detail pembayaran';
        $subTitle = $inv_number;

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $query = DB::table('t_pay_request')->selectRaw('inv_numb, status_type, result_msg, t_pay_channel.description as channel_pembayaran, cupkg_status,amount,responseCode,paymen_code,insert_date,t_customer.cust_number,t_invoice_porfoma.inv_number,inv_status,t_invoice_porfoma.sp_code,inv_start,inv_end,inv_paid,inv_post,inv_info,cust_name,cust_pop,cust_hp,cust_address,cust_phone,cust_email')
            ->leftJoin('t_invoice_porfoma', 't_pay_request.inv_numb', '=', 't_invoice_porfoma.inv_number')
            ->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
            ->leftJoin('trel_cust_pkg', function ($join) {
                $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number')->on('trel_cust_pkg._nomor', '=', 't_invoice_porfoma.sp_nom');
            })
            ->leftJoin('t_pay_channel', 't_pay_request.payment_channel', '=', 't_pay_channel.code')
            ->groupBy('inv_numb', 't_customer.cust_number')
            ->where('inv_numb', $inv_number)
            ->orderByDesc('payment_time')
            ->limit(100)
            ->get();

        $dataCust = [];
        $dataInv = [];
        $dataPayment = [];
        foreach ($query as $key => $value) {
            $dataCust['cust_name'][] = "Nama Pelanggan";
            $dataCust['cust_name'][] = $value->cust_name;
            $dataCust['cust_number'][] = 'Nomor Pelanggan';
            $dataCust['cust_number'][] = $value->cust_number;
            $dataCust['cust_phone'][] = "Nomor Telpon";
            $dataCust['cust_phone'][] = $value->cust_phone;
            $dataCust['cust_email'][] = "Emaik";
            $dataCust['cust_email'][] = $value->cust_email;
            $dataCust['cust_address'][] = "Alamat Pelanggan";
            $dataCust['cust_address'][] = $value->cust_address;
            $dataCust['cust_status'][] = "Status";
            $dataCust['cust_status'][] = arrCustStatus($value->cupkg_status);
            $dataCust['cust_pop'][] = "POP";
            $dataCust['cust_pop'][] = $this->arrPop[$value->cust_pop];
            $dataCust['sp_code'][] = "Layanan";
            $dataCust['sp_code'][] = $value->sp_code;

            $dataInv['inv_number'][] = "Nomor Invoice";
            $dataInv['inv_number'][] = $value->inv_number;
            $dataInv['inv_status'][] = "Status";
            $dataInv['inv_status'][] = arrPiStatus($value->inv_status);
            $dataInv['inv_start'][] =  "Invoice Start";
            $dataInv['inv_start'][] =  Carbon::parse($value->inv_start)->isoFormat('D MMMM Y');
            $dataInv['inv_end'][] =  "Invoice End";
            $dataInv['inv_end'][] =  Carbon::parse($value->inv_end)->isoFormat('D MMMM Y');
            $dataInv['inv_paid'][] =  "Invoice Paid";
            $dataInv['inv_paid'][] =  Carbon::parse($value->inv_paid)->isoFormat('D MMMM Y, HH:mm');
            $dataInv['inv_post'][] =  "Invoice Posted";
            $dataInv['inv_post'][] =  Carbon::parse($value->inv_end)->isoFormat('D MMMM Y, HH:mm');
            $dataInv['inv_info'][] = "Deskripsi";
            $dataInv['inv_info'][] = $value->inv_info;

            $dataPayment['inv_number'][] = "Nomor Invoice";
            $dataPayment['inv_number'][] = $value->inv_numb;
            $dataPayment['amount'][] = "Jumlah";
            $dataPayment['amount'][] = 'Rp. ' . SchRp($value->amount);
            
            $dataPayment['result_msg'][] = 'Status';
            $dataPayment['result_msg'][] = [$value->result_msg,$value->responseCode == 0000 ? 'green' : 'warning'];
            $dataPayment['payment_channel'][] = "Metode Pembayaran";
            $dataPayment['payment_channel'][] = $value->channel_pembayaran;
            $dataPayment['paymen_code'][] = "Nomor Pembayaran";
            $dataPayment['paymen_code'][] = $value->paymen_code;
            $dataPayment['date_request'][] = "Tanggal Request";
            $dataPayment['date_request'][] = Carbon::parse($value->insert_date)->isoFormat('D MMMM Y, HH:mm');
        }
        $susunData = [];
        if ($dataCust && $dataInv && $dataPayment) {
            $susunData[0]['title'] = 'Data Pelanggan';
            $susunData[0]['data'] = $dataCust;
            $susunData[1]['title'] = 'Data Invoice';
            $susunData[1]['data'] = $dataInv;
            $susunData[2]['title'] = 'Data Pembayaran';
            $susunData[2]['data'] = $dataPayment;
        }
        $load['detail_data'] = $susunData;
        //print_r($susunData);die;

        return view('pages/doku/paymentDetail', $load);
    }

    private function arrField()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor Pelanggan',
                'orderable' => true,
                'searchable' => true
            ],
            'status_cust' => [
                'label' => 'Status Pelanggan',
                'orderable' => true,
                'searchable' => true
            ],
            'inv_numb' => [
                'label' => 'Nomor Invoice',
                'orderable' => true,
                'searchable' => true
            ],
            'pi_status' => [
                'label' => 'Status Invoice',
                'orderable' => true,
                'searchable' => true
            ],
            'result_msg' => [
                'label' => 'Status Pemabayaran',
                'orderable' => false,
                'searchable' => true
            ],

            'channel_pembayaran' => [
                'label' => 'Metode Pembayaran',
                'orderable' => false,
                'searchable' => true
            ],

            'rp_amount' => [
                'label' => 'Total',
                'orderable' => false,
                'searchable' => true
            ],

        ];
    }
}
