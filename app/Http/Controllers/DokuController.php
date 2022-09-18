<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;

class DokuController extends Controller
{
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
    var $arrPiStatus = ['Blum Bayar','Lunas','Expired'];
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
                    $actionBtn = '<a href="' . route('customer-detail', '?inv=' . $row->inv_numb) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })
                ->addColumn('status_cust', function ($row) {
                    return $row->cupkg_status ? $this->arrStatus[$row->cupkg_status] : '';
                })
                ->addColumn('pi_status', function ($row) {
                    return $row->inv_status ? $this->arrPiStatus[$row->inv_status] : '';
                })
                ->rawColumns(['detail', 'status_cust','rp_amount','pi_status'])
                ->toJson();
                //->make(true);
            //die;
        }
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
