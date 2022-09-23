<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Http;
use \RouterOS\Client;
use \RouterOS\Query;

class DokuController extends Controller
{
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
    var $arrPiStatus = ['Blum Bayar', 'Lunas', 'Expired'];
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];

    var $mallid = 9265;
    var $sharedKey = 'Zjv828WzoQGJ';

    var $payChannel = [32 => 'CIMB', 33 => 'Danamon', 34 => 'BRI', 35 => 'Alfamart', 38 => 'BNI', 41 => 'Mandiri'];
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
                //->where('inv_numb','PIJP001229092214')
                ->orderByDesc('payment_time')
                //->limit(1000)
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
                    return isset($this->arrPiStatus[$row->inv_status]) ? $this->arrPiStatus[$row->inv_status] : $row->inv_status;
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
            $dataPayment['result_msg'][] = [$value->result_msg, $value->responseCode == 0000 ? 'green' : 'warning'];
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
        $load['inv_number'] = $inv_number;
        $load['cust_number'] = $dataCust['cust_number'][1];
        //print_r($susunData);die;

        return view('pages/doku/paymentDetail', $load);
    }

    public function voidRequest(Request $request)
    {

        $inv_number = $request->input('inv');
        if ($inv_number) {
            $query = DB::table('t_pay_request')
                ->leftJoin('t_pay_session', 't_pay_request.inv_numb', '=', 't_pay_session.inv_number')
                ->where('inv_numb', $inv_number)
                ->first();

            $postVal['MALLID'] = $this->mallid;
            $postVal['CHAINMERCHANT'] = 'NA';
            $postVal['TRANSIDMERCHANT'] = $query->inv_numb;
            $postVal['SESSIONID'] = $query->session_id;
            $postVal['WORDS'] =  sha1($this->mallid . $this->sharedKey . $query->inv_numb . $query->session_id);
            $postVal['PAYMENTCHANNEL'] = $query->payment_channel;
            //print_r($postVal);die;

            $response = Http::asForm()->post('https://pay.doku.com/Suite/VoidRequest', $postVal);
            print_r($response->body());
            die;
            $xml = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml);
            $arrResponse =  json_decode($json, true);
            $susunData = [];
            foreach ($susunData as $key => $value) {
                # code...
            }

            print_r($arrResponse);
        }
    }

    public function cekRequest(Request $request)
    {

        $inv_number = $request->input('inv');
        if ($request->ajax() && $inv_number) {
            $query = DB::table('t_pay_request')
                ->leftJoin('t_pay_session', 't_pay_request.inv_numb', '=', 't_pay_session.inv_number')
                ->where('inv_numb', $inv_number)
                ->first();

            $postVal['MALLID'] = $this->mallid;
            $postVal['CHAINMERCHANT'] = 'NA';
            $postVal['TRANSIDMERCHANT'] = $query->inv_numb;
            $postVal['SESSIONID'] = $query->session_id;
            $postVal['WORDS'] =  sha1($this->mallid . $this->sharedKey . $query->inv_numb);
            $postVal['PAYMENTCHANNEL'] = $query->payment_channel;
            //print_r($postVal);die;

            $response = Http::asForm()->post('https://gts.doku.com/Suite/CheckStatus', $postVal);

            $xml = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml);
            $arrResponse =  json_decode($json, true);

            $susunData = [];

            $susunData['TRANSIDMERCHANT'] = $arrResponse['TRANSIDMERCHANT'];
            $susunData['AMOUNT'] = $arrResponse['AMOUNT'];
            $susunData['PAYMENTCODE'] = $arrResponse['PAYMENTCODE'];
            $susunData['RESULTMSG'] = $arrResponse['RESULTMSG'];
            $susunData['PAYMENTCHANNEL'] = $this->payChannel[$arrResponse['PAYMENTCHANNEL']];


            return response()
                ->json($susunData);
        }
    }

    public function updateRequest(Request $request)
    {
        $inv_number = $request->input('inv');
        if ($inv_number) {

            $query = DB::table('t_pay_request')->selectRaw('inv_numb, status_type, result_msg, t_pay_channel.description as channel_pembayaran, cupkg_status,amount,responseCode,paymen_code,insert_date,t_customer.cust_number,t_invoice_porfoma.inv_number,inv_status,t_invoice_porfoma.sp_nom,t_invoice_porfoma.sp_code,inv_start,inv_end,inv_paid,inv_post,inv_info,cust_name,cust_pop,cust_hp,cust_address,cust_phone,cust_email')
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
                ->first();

            if ($query->responseCode != '0000') {
                $requestData['responseCode'] = '0000';
                $requestData['result_msg'] = 'SUCCESS';
                //echo 'update status pembayaran\n';
                DB::table('t_pay_request')
                    ->where('inv_numb', $inv_number)
                    ->update($requestData);
            }

            if ($query->cupkg_status != 4) {
                //echo 'update status pelanggan\n';

                $getLastSpk = DB::table('t_field_task')
                    ->select('ft_number')
                    ->where('ft_number', 'like', 'SP0%')
                    ->whereRaw('MONTH(ft_received) =' . date('m'))
                    ->whereRaw('YEAR(ft_received) =' . date('Y'))
                    ->orderByDesc('ft_number')
                    ->first();
                //print_r($getLastSpk);
                if (isset($getLastSpk->ft_number)) {
                    $explodeSpkNumber = explode('/', $getLastSpk->ft_number);
                    //echo $getLastSpk->ft_number.'<br>';
                    $newSpkNumber = sprintf("%05d", substr($explodeSpkNumber[0], 2) + 1);
                    //echo 'SP'.$newSpkNumber."/NOC/".date('m').'/'.date('y');die;

                    $ftNumber =  'SP' . $newSpkNumber . "/NOC/" . date('m') . '/' . date('y');
                    $postVal['ft_number'] = $ftNumber;
                    $postVal['ft_received'] = date('Y-m-d H:i:s');
                    $postVal['ft_type'] = 2;
                    $postVal['cust_number'] = $query->cust_number;
                    $postVal['sp_code'] = $query->sp_code;
                    $postVal['sp_nom'] = $query->sp_nom;
                    $postVal['ft_recycle'] = 2;
                    $postVal['ft_reactive'] = 1;
                    $postVal['ft_desc'] = 'SPK Setup Genertae by doku at ' . Carbon::parse(date('Y-m-d H:m:i'))->isoFormat('D MMMM Y, HH:mm');

                    //print_r($postVal);
                    DB::table('t_field_task')->insert($postVal);

                    $status = $this->openBlocking($query->cust_number);
                    if ($status) {
                        $updateVal['ft_status'] = '2';
                        $updateVal['ft_updated'] = date('Y-m-d H:m:i');
                        $updateVal['ft_solved'] = date('Y-m-d H:m:i');
                        $updateVal['ft_updated_by'] = "admin";
                        $updateVal['ft_desc'] = $postVal['ft_desc'] . ' <br> ' . 'Setup done by sistem at ' . Carbon::parse(date('Y-m-d H:m:i'))->isoFormat('D MMMM Y, HH:mm');

                        //print_r($updateVal);
                        DB::table('t_field_task')
                            ->where('ft_number', $ftNumber)
                            ->update($updateVal);

                        DB::table('trel_cust_pkg')
                            ->where('cust_number', $query->cust_number)
                            ->update(['cupkg_status' => 4]);
                    }else{
                        DB::table('trel_cust_pkg')
                            ->where('cust_number', $query->cust_number)
                            ->update(['cupkg_status' => 3]);
                    }
                }
            }

            if ($query->inv_status == 0) {
                $message =  'Di bayar dengan ' . $query->channel_pembayaran . ' <br>Diupdate mylm pada ' . Carbon::parse(date('Y-m-d H:m:i'))->isoFormat('D MMMM Y, HH:mm');

                $piData['inv_status'] = 1;
                $piData['inv_pay_method'] = $query->channel_pembayaran == '35' ? '13' : '12';
                $piData['inv_paid'] = date('Y-m-d H:m:i');
                $piData['inv_info'] = $message;
                print_r($piData);

                DB::table('t_invoice_porfoma')
                    ->where('inv_number', $query->inv_number)
                    ->update($piData);
            }
            $request->session()->flash('success', 'Update Data Berhasil!');
        }
        return redirect()->back();
    }

    private function  openBlocking($cust_number = '')
    {
        $client = new Client([
            'host' => '202.169.224.19',
            'user' => 'faris123',
            'pass' => 'faris123',
            'port' => 9778,
        ]);

        $status = false;

        if ($cust_number) {
            $query =
                (new Query('/ppp/secret/print'))
                ->where('name', $cust_number);

            // Send query and read response from RouterOS
            $response = $client->query($query)->read();

            $message = '';
            $id = '';

            if (isset($response[0])) {

                foreach ($response[0] as $keys => $value) {

                    if ($keys == 'comment') {
                        $explodeComent = explode('|', $value);
                        if ($explodeComent) {
                            //print_r($explodeComent);
                            if (end($explodeComent)) {
                                $message .= end($explodeComent);
                            } else {
                                $result = array_slice($explodeComent, 0, -1, true);
                                $message .= end($result) . ' | ';
                            }
                        }
                    }
                    if ($keys == '.id') {
                        $id = $value;
                    }
                }

                $message .= 'Setup by Mylm at ' . (date('Y-m-d H:i:s')) . ' | ';

                if ($id) {

                    $openBlocking = (new Query('/ppp/secret/enable'))
                        ->equal('.id', $id);
                    $response = $client->query($openBlocking)->read();

                    $setComent = (new Query('/ppp/secret/set'))
                        ->equal('.id', $id)
                        ->equal('comment', $message);
                    $response = $client->query($setComent)->read();

                    $status = true;
                }
            }
        }

        return $status;
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
