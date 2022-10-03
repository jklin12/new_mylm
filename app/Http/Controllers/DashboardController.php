<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];

    public function index(Request $request)
    {

        //print_r(side_menu()['menu']);die;
        $curentDateEnd = $request->has('end') ? $request->input('end') : date('Y-m-d');
        $curentDateStart = $request->has('start') ? $request->input('start') : date('Y-m-01');

        $curentDate['dateEnd'] = $curentDateEnd;
        $curentDate['dateStart'] = $curentDateStart;

        $load['curentDate'] = $curentDate;
        //print_r($curentDate);die;

        $curentRevenue = DB::table('t_pay_request')->select([DB::raw('SUM(amount) as total'), DB::raw('count(id_pay_req) as total_pi')])
            ->whereRaw("DATE_FORMAT(insert_date,'%Y-%m-%d') >= '" . $curentDateStart . "'")
            ->whereRaw("DATE_FORMAT(insert_date,'%Y-%m-%d') <= '" . $curentDateEnd . "'")
            ->where('result_msg', 'SUCCESS')
            ->first();

        //print_r($curentRevenue);die;

        $piData = DB::table('t_invoice_porfoma')
            ->select([DB::raw('count(inv_number) as total_pi'), DB::raw('SUM(CASE WHEN inv_status = 1 THEN 1 ELSE 0 END) as total_pi_lunas'), DB::raw('SUM(CASE WHEN inv_status = 0 THEN 1 ELSE 0 END) as total_pi_tidak_lunas'), DB::raw('SUM(CASE WHEN inv_status = 2 THEN 1 ELSE 0 END) as total_pi_expired')])
            ->where('inv_post', '>=', $curentDateStart)
            ->where('inv_post', '<=', $curentDateEnd)
            ->first();

        //print_r($piData);die;

        $custData = DB::table('t_customer')
            ->select([DB::raw('count(cust_number) as pelanggan_baru'), 'cust_pop'])
            ->where('created', '>=', $curentDateStart . ' 00:00:00')
            ->where('created', '<=', $curentDateEnd . ' 00:00:00')
            ->groupBy('cust_pop')
            ->get();

        $totalPelangganBaru = 0;
        $newCust = [];
        foreach ($custData as $key => $value) {
            $totalPelangganBaru += $value->pelanggan_baru;
            $newCust[$key]['jumlah'] = $value->pelanggan_baru;
            $newCust[$key]['pop'] = $this->arrPop[$value->cust_pop];
        }
        //print_r($newCust);die;


        $chartDoku = DB::table('t_pay_request')->selectRaw('SUM(amount) as total,DATE_FORMAT(insert_date,"%Y-%m-%d") as tanggal')
            ->whereRaw("DATE_FORMAT(insert_date,'%Y-%m-%d') >= '" . $curentDateStart . "'")
            ->whereRaw("DATE_FORMAT(insert_date,'%Y-%m-%d') <= '" . $curentDateEnd . "'")
            //->where('inv_status', 1)
            ->where('result_msg', 'SUCCESS')
            ->leftJoin('t_invoice_porfoma', 't_pay_request.inv_numb', '=', 't_invoice_porfoma.inv_number')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        $labelChartDoku = [];
        $valueChartDoku = [];
        foreach ($chartDoku as $key => $value) {
            $labelChartDoku[] = Carbon::parse($value->tanggal)->isoFormat(' D MMM YY');
            $valueChartDoku[] = $value->total;
        }

        $dataChartDoku['label'] = json_encode($labelChartDoku);
        $dataChartDoku['value'] = json_encode($valueChartDoku);


        $load['curentTotal'] =  $curentRevenue;
        $load['piData'] =  $piData;
        $load['pi_lunas'] =  $this->precentage($piData->total_pi_lunas, $piData->total_pi);
        $load['pi_belum_lunas'] =  $this->precentage($piData->total_pi_tidak_lunas, $piData->total_pi);
        $load['pi_expired'] =  $this->precentage($piData->total_pi_expired, $piData->total_pi);
        $load['total_new_cust'] = $totalPelangganBaru;
        $load['new_cust_data'] = $newCust;
        $load['data_chart_doku'] = $dataChartDoku;

        //print_r($load);die;
        return view('pages/dashboard-v1', $load);
    }

    private function precentage($value, $total)
    {
        return round(($value / $total) * 100, 2);
    }
}
