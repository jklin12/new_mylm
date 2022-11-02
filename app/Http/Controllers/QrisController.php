<?php

namespace App\Http\Controllers;

use App\Models\InvoicePorfoma;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use chillerlan\QRCode\{QRCode, QROptions};
use DataTables;

class QrisController extends Controller
{
    /*var $baseUrl = 'https://staging.doku.com/';
    var $clientId = '4553';
    var $clientSecret = 'f0636c2085b95aa06908d3a2ec5851df';
    var $secretKey = 'FooCJBwNS5eelxBt';*/
    
    var $baseUrl = 'https://my.dokuwallet.com/';
    var $clientId = '6978';
    var $clientSecret = 'bf727ccbcb24e8fb66dd376ca139a332';
    var $secretKey = 'qqHpni8dX4pyKvnq';
    

    var $invStatus = ['N' => ['belum lunas', 'danger'], 'S' => ['lunas', 'green']];

    public function index()
    {
        $title = 'Data Request Pembayaran';
        $subTitle = 'Via QRIS Olny';

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
        //dd($load);

        return view('pages/qris/index', $load);
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('t_qr_request')
                ->selectRaw('cust_number,t_invoice_porfoma.inv_number,t_qr_request.*')
                ->leftJoin('t_invoice_porfoma', 't_qr_request.porfoma', '=', 't_invoice_porfoma.inv_number')
                ->leftJoin('t_inv_item_porfoma', function ($join) {
                    $join->on('t_invoice_porfoma.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);
                })
                ->groupByRaw('t_invoice_porfoma.inv_number');

            return Datatables::of($data)
                ->addIndexColumn()

                ->editColumn('txndate', function ($user) {
                    return $user->txndate ? with(new Carbon($user->txndate))->isoFormat('dddd, D MMMM Y H:m') : '';
                })
                ->addColumn('txnstatus', function ($user) {
                    return  '<span class="badge badge-' . $this->invStatus[$user->txnstatus][1] . '">' . $this->invStatus[$user->txnstatus][0] . '</span>';
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="#" class="btn btn-pink btn-icon btn-circle btn-cek" data-id="' . $row->transactionid . '"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })

                ->rawColumns(['detail', 'txnstatus'])
                ->make(true);
        }
    }
    public function auth()
    {

        $url = $this->baseUrl . '/dokupay/h2h/signon';

        $systrace = $this->generateRandomString(20);
        //$systrace = 'N6JFd8dNGLOM0UvOB8cA';
        //echo $systrace;die;

        $postVal['clientId'] = $this->clientId;
        $postVal['clientSecret'] = $this->clientSecret;
        $postVal['systrace'] = $systrace;
        $postVal['words'] = $this->words($systrace);
        $postVal['version'] = '1.0';
        $postVal['responseType'] = '1';

        $curl = curl_init();
        $header = [
            'Content-Type: application/x-www-form-urlencoded',
        ];
        //echo http_build_query($postVal);    


        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postVal));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            return ($error_msg);
            //exit;
        }

        curl_close($curl);
        if ($response) {
            $arr = json_decode($response, true);

            if (isset($arr['responseCode']) && $arr['responseCode'] == '0000') {

                $update = DB::table('t_qris_token')
                    ->update(['status' => 0]);

                $data = array(
                    'token' => $arr['accessToken'],
                    'systrace' => $systrace,
                    'created_at' => date('Y-m-d H:m:s')
                );

                $insert  = DB::table('t_qris_token')
                    ->insert($data);
            }


            return ($arr);
        }

        return false;
    }

    public function generate(Request $request, $inv_number)
    {
        if ($request->ajax()) {


            $status = 0;
            $data['message']  = 'Gagal Generate QR';

            $cekExist = DB::table('t_qr_request')->where('invoice', $inv_number)->where('txnstatus', 'N')->first();
            if ($cekExist) {

                $status = 1;
                $data = [
                    'message' => 'sukses Generate qr',
                    'qr' => '<img src="' . (new QRCode)->render($cekExist->qrCode) . '" alt="QR Code" />',
                ];
                $load['status'] = $status;
                $load['data'] = $data;
                return response()
                    ->json($load);
            } else {
                $invData = DB::table('t_invoice_porfoma')
                    ->selectRaw('cust_number,t_invoice_porfoma.inv_number,inv_due,inv_start,inv_status,sum(t_inv_item_porfoma.ii_amount) as totals')
                    ->where('t_invoice_porfoma.inv_number', $inv_number)
                    ->leftJoin('t_inv_item_porfoma', function ($join) {
                        $join->on('t_invoice_porfoma.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);
                    })
                    ->groupByRaw('t_invoice_porfoma.inv_number')
                    ->orderByDesc('inv_start')->first();
                $amount = $invData->totals;

                $tokenData = DB::table('t_qris_token')->where('status', 1)->orderByDesc('created_at')->first();

               
                //dd($tok->systrace);
                $token = $tokenData->token;
                $systrace = $tokenData->systrace;


                $url = $this->baseUrl . '/dokupay/h2h/generateQris';

                $postVal['clientId'] = $this->clientId;
                $postVal['accessToken'] = $token;
                $postVal['dpMallId'] = $this->clientId;
                $postVal['words'] = $this->words($systrace . $this->clientId, false);
                $postVal['version'] = '3.0';
                $postVal['terminalId'] = 'A01';
                $postVal['amount'] = $amount;
                $postVal['postalCode'] = '99999';
                $postVal['merchantCriteria'] = 'UBE';
                $postVal['feeType'] = '1';

                //dd($postVal);

                $curl = curl_init();
                $header = [
                    'Content-Type: application/x-www-form-urlencoded',
                ];

                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postVal));
                curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($curl);


                if (curl_errno($curl)) {
                    $error_msg = curl_error($curl);
                    return ($error_msg);
                    //exit;
                }

                curl_close($curl);
                if ($response) {
                    $arr = json_decode($response, true);
                    //dd($arr);
                    if ($arr['responseCode'] == '0000') {
                        //dd($arr);
                        $susunData['transactionid'] = $arr['transactionId'];
                        $susunData['porfoma'] = $inv_number;
                        $susunData['qrCode'] = $arr['qrCode'];

                        $insert  = DB::table('t_qr_request')
                            ->insert($susunData);

                        $status = 1;
                        $data = [
                            'message' => 'sukses Generate qr',
                            'qr' => '<img src="' . (new QRCode)->render($arr['qrCode']) . '" alt="QR Code" />',
                        ];
                    } else if ($arr['responseCode'] == '3010') {
                        $this->auth();
                        $this->generate($request, $inv_number);
                    }
                }

                $load['status'] = $status;
                $load['data'] = $data;
                return response()
                    ->json($load);
                //return false;
            }
        }
    }

    public function cekStatus(Request $request, $transactionid)
    {

        $url = $this->baseUrl . '/dokupay/h2h/checkstatusqris';

        $tokenData = DB::table('t_qris_token')->where('status', 1)->orderByDesc('created_at')->first();

        //dd($token->systrace);
        $token = $tokenData->token;
        $systrace = $tokenData->systrace;

        $postVal['clientId'] = $this->clientId;
        $postVal['accessToken'] = $token;
        $postVal['dpMallId'] = $this->clientId;
        $postVal['words'] = $this->words($systrace . $this->clientId . $transactionid, false);
        $postVal['version'] = '3.0';
        $postVal['transactionId'] = $transactionid;

        $curl = curl_init();
        $header = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postVal));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            return ($error_msg);
            //exit;
        }

        curl_close($curl);
        if ($response) {
            $arr = json_decode($response, true);

            dd($arr);
        }
        //dd($postVal);
    }

    public function words($systrace = '', $auth = true)
    {
        if ($auth) {
            $data = $this->clientId . $this->secretKey . $systrace;
        } else {
            $data = $this->clientId . $systrace  . $this->secretKey;
        }
        //echo $data;die;

        return hash_hmac("SHA1", $data, $this->clientSecret);
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function arrField()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
            ],
            
            /*'invoice' => [
                'label' => 'Invoice',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
            ],*/
            'porfoma' => [
                'label' => 'PI',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
            ],'customername' => [
                'label' => 'Nama',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'issuername' => [
                'label' => 'Isuer Name',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'amount' => [
                'label' => 'Jumlah',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'txnstatus' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'select',
                'keyvaldata' => $this->invStatus,
            ],
            'transactionid' => [
                'label' => 'Trasaction ID',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'txndate' => [
                'label' => 'TX Date',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'date',
            ],
        ];
    }
}
