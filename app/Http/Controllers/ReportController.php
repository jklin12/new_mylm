<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];


    public function penggunaBaru(Request $request)
    {
        $title = "Report Pelanggan Lifemedia";
        $subTitle = '';

        $year = $request->has('tahun') ? $request->input('tahun') : date('Y');

        $monthlyCustomer = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, MONTH(created) as bulan')
            //->leftJoin('trel_cust_pkg','t_customer.cust_number','=','trel_cust_pkg.cust_number')
            //->where('cupkg_status', '4')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $susunChartCust = [];
        foreach ($monthlyCustomer as $key => $value) {

            $susunChartCust[$key]['name'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $susunChartCust[$key]['y'] = $value->total;
            $susunChartCust[$key]['drilldown'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
        }
        //print_r($susunChartCust);die;

        $monthlyCustomerPop = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, MONTH(created) as bulan,cust_pop')
            //->leftJoin('trel_cust_pkg','t_customer.cust_number','=','trel_cust_pkg.cust_number')
            //->where('cupkg_status', '4')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('bulan', 'cust_pop')
            ->orderBy('bulan')
            ->get();

        $susundrilldown = [];
        foreach ($monthlyCustomerPop as $key => $value) {
            $susundrilldown[$value->bulan]['name'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $susundrilldown[$value->bulan]['id'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $susundrilldown[$value->bulan]['data'][$key][] = $this->arrPop[$value->cust_pop];
            $susundrilldown[$value->bulan]['data'][$key][] = $value->total;
        }
        foreach ($susundrilldown as $key => $value) {
            $susundrilldown[$key]['name'] = $value['name'];
            $susundrilldown[$key]['id'] = $value['id'];
            $susundrilldown[$key]['data'] = array_values($value['data']);
        }
        //print_r($susundrilldown);die;

        $monthlyAm = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, cupkg_acct_manager')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            //->where('cupkg_status', '4')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('cupkg_acct_manager')
            ->orderBy('total')
            ->get();
        //print_r($monthlyAm);die;
        $susunChartAm = [];
        foreach ($monthlyAm as $key => $value) {

            if ($value->total > 10) {
                $susunChartAm[$key]['name'] = $value->cupkg_acct_manager;
                $susunChartAm[$key]['y'] = $value->total;
                $susunChartAm[$key]['drilldown'] = $value->cupkg_acct_manager;
            }
        }

        $monthlyAmPop = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, cupkg_acct_manager,cust_pop')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            //->where('cupkg_status', '4')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('cupkg_acct_manager', 'cust_pop')
            ->orderBy('total')
            ->get();

        $susundrilldownAm = [];
        foreach ($monthlyAmPop as $key => $value) {
            if ($value->total > 10) {
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['name'] = $value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key;
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['id'] = $value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key;
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['data'][$key][] = $this->arrPop[$value->cust_pop];
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['data'][$key][] = $value->total;
            }
        }
        foreach ($susundrilldownAm as $key => $value) {
            $susundrilldownAm[$key]['name'] = $value['name'];
            $susundrilldownAm[$key]['id'] = $value['id'];
            $susundrilldownAm[$key]['data'] = array_values($value['data']);
        }

        $allPelanggan = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, cupkg_status')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('cupkg_status', '!=', '7')
            //->where('cupkg_status', '!=', '9')
            ->where('cupkg_status', '!=', '10')
            //->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('cupkg_status')
            ->orderBy('total')
            ->get();

        $totalPelanggan = 0;
        $PelangganByStatus = [];
        foreach ($allPelanggan as $key => $value) {
            $totalPelanggan += $value->total;
            $PelangganByStatus[$key]['total'] = $value->total;
            $PelangganByStatus[$key]['status'] = $this->arrStatus[$value->cupkg_status];
        }

        $custBySpcode = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, sp_code')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('cupkg_status', '!=', '7')
            ->where('cupkg_status', '!=', '9')
            ->where('cupkg_status', '!=', '10')
            //->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('sp_code')
            ->orderBy('total')
            ->get();
        //print_r($custBySpcode);die;
        $susunSpcode = [];
        $spCodejumalh = [];
        foreach ($custBySpcode as $key => $value) {
            $susunSpcode[$key]['name'] = $value->sp_code;
            $susunSpcode[$key]['y'] = $value->total;
            $spCodejumalh[] = $value->total;
        }
        $max = max(array_keys($spCodejumalh));

        $susunSpcode[$max]['sliced'] = true;
        $susunSpcode[$max]['selected'] = true;
        //dd($susunSpcode);

        $custByPop = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, cust_pop')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('cupkg_status', '!=', '7')
            ->where('cupkg_status', '!=', '9')
            ->where('cupkg_status', '!=', '10')
            //->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('cust_pop')
            ->orderBy('total')
            ->get();

        $susunCustPop = [];

        foreach ($custByPop as $key => $value) {
            $susunCustPop[$key]['name'] = $this->arrPop[$value->cust_pop];
            $susunCustPop[$key]['y'] = $value->total;
        }

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['year'] = $year;

        $load['monthlyChart'] = json_encode($susunChartCust);
        $load['drilldownData'] = json_encode(array_values($susundrilldown));

        $load['monthlyChartAm'] = json_encode(array_values($susunChartAm));
        $load['drilldownDataAm'] = json_encode(array_values($susundrilldownAm));
        $load['chartSpcode'] = json_encode($susunSpcode);
        $load['chartPop'] = json_encode($susunCustPop);

        $load['totalPelanggan'] = $totalPelanggan;
        $load['PelangganByStatus'] = $PelangganByStatus;


        return view('pages/report/pengguna-index', $load);
    }

    private function precentage($value, $total)
    {
        return round(($value / $total) * 100, 2);
    }
    public function porfoma(Request $request)
    {
        $filter =  $request->input('filter');
        $xplodeFilter = explode('-', $filter);

        $year = isset($xplodeFilter[1]) && $xplodeFilter[1] ? $xplodeFilter[1] : date('Y');
        $month = isset($xplodeFilter[0]) && $xplodeFilter[0] ? $xplodeFilter[0] : date('m');

        $title = "Report Porfoma Lifemedia";
        $subTitle = 'Bulan ' . Carbon::parse($year . '-' . $month . '-01')->isoFormat('MMMM YY');;

        $porfomaLunas = DB::table('t_invoice_porfoma')
            ->selectRaw('sum(t_inv_item_porfoma.ii_amount) as amount,t_invoice_porfoma.inv_number')
            ->leftJoin('t_inv_item_porfoma', function ($join) {
                $join->on('t_invoice_porfoma.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);;
            })
            ->whereRaw("MONTH(inv_start) = '" . $month . "'")
            ->whereRaw("YEAR(inv_start) = '" . $year . "'")
            ->where('inv_status', '1')
            //->groupBy('t_invoice_porfoma.inv_number')
            ->first();

        $porfomaStatus = DB::table('t_invoice_porfoma')
            ->select([DB::raw('count(inv_number) as total_pi'), DB::raw('SUM(CASE WHEN inv_status = 1 THEN 1 ELSE 0 END) as total_pi_lunas'), DB::raw('SUM(CASE WHEN inv_status = 0 THEN 1 ELSE 0 END) as total_pi_tidak_lunas'), DB::raw('SUM(CASE WHEN inv_status = 2 THEN 1 ELSE 0 END) as total_pi_expired')])
            ->whereRaw("MONTH(inv_post) = '" . $month . "'")
            ->whereRaw("YEAR(inv_post) = '" . $year . "'")
            //->where('inv_status', '1')
            //->groupBy('inv_status')
            ->first();
        //dd($porfomaStatus);

        $porfomaChart = DB::table('t_invoice_porfoma')
            ->select([DB::raw('count(inv_number) as total_pi'), DB::raw('SUM(CASE WHEN inv_status = 1 THEN 1 ELSE 0 END) as total_pi_lunas'), DB::raw('SUM(CASE WHEN inv_status = 0 THEN 1 ELSE 0 END) as total_pi_tidak_lunas'), DB::raw("SUM(CASE WHEN wa_sent_number != '' THEN 1 ELSE 0 END) as pi_terkirim"), DB::raw("inv_start as tanggal")])
            ->whereRaw("MONTH(inv_start) = '" . $month . "'")
            ->whereRaw("YEAR(inv_start) = '" . $year . "'")
            //->where('inv_status', '1')
            ->groupByRaw("inv_start")
            ->orderBy('inv_start')
            ->get();
        //dd($porfomaChart);
        $susunChart = [];
        $chartValue[0]['name'] = 'Total Porfoma';
        $chartValue[1]['name'] = 'Porfoma Lunas';
        $chartValue[2]['name'] = 'Porfoma Belum Lunas';
        $chartValue[3]['name'] = 'Porfoma Terkirim';
        foreach ($porfomaChart as $key => $value) {
            $chartLabel[] = Carbon::parse($value->tanggal)->isoFormat(' D MMM YY');
            $chartValue[0]['data'][$key] = intval($value->total_pi);
            $chartValue[1]['data'][$key] = intval($value->total_pi_lunas);
            $chartValue[2]['data'][$key] = intval($value->total_pi_tidak_lunas);
            $chartValue[3]['data'][$key] = intval($value->pi_terkirim);
        }
        $susunChart['label'] = json_encode($chartLabel);
        $susunChart['value'] = json_encode($chartValue);

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['year'] = $year;
        $load['month'] =  Carbon::parse($year . '-' . $month . '-01')->isoFormat('MMMM');;
        $load['porfomaLunas'] = $porfomaLunas;
        $load['piData'] =  $porfomaStatus;
        $load['pi_lunas'] =  $this->precentage($porfomaStatus->total_pi_lunas, $porfomaStatus->total_pi);
        $load['pi_belum_lunas'] =  $this->precentage($porfomaStatus->total_pi_tidak_lunas, $porfomaStatus->total_pi);
        $load['pi_expired'] =  $this->precentage($porfomaStatus->total_pi_expired, $porfomaStatus->total_pi);
        $load['porfomaChart'] = $susunChart;

        return view('pages/report/porfoma-index', $load);
    }
    public function spk(Request $request)
    {

        $filter =  $request->input('filter');
        $xplodeFilter = explode('-', $filter);

        $year = isset($xplodeFilter[1]) && $xplodeFilter[1] ? $xplodeFilter[1] : date('Y');
        $month = isset($xplodeFilter[0]) && $xplodeFilter[0] ? $xplodeFilter[0] : date('m');

        $title = "Report Surat Perintah Kerja";
        $subTitle = 'Bulan ' . Carbon::parse($year . '-' . $month . '-01')->isoFormat('MMMM YY');

        $spkBlocking = DB::table('t_field_task')
            //->selectRaw('count(ft_number) as jumlah,ft_status')
            ->select([DB::raw('count(ft_number) as total_spk'), DB::raw('SUM(CASE WHEN ft_status = 0 THEN 1 ELSE 0 END) as spk_tunggu'), DB::raw('SUM(CASE WHEN ft_status = 2 THEN 1 ELSE 0 END) as spk_ok'), DB::raw('SUM(CASE WHEN ft_status = 3 THEN 1 ELSE 0 END) as spk_batal')])
            ->whereRaw("MONTH(ft_received) = '" . $month . "'")
            ->whereRaw("YEAR(ft_received) = '" . $year . "'")
            ->where('ft_type', '9')
            //->where('ft_type', '5')
            ->first();

        $spkPencabutan = DB::table('t_field_task')
            //->selectRaw('count(ft_number) as jumlah,ft_status')
            ->select([DB::raw('count(ft_number) as total_spk'), DB::raw('SUM(CASE WHEN ft_status = 0 THEN 1 ELSE 0 END) as spk_tunggu'), DB::raw('SUM(CASE WHEN ft_status = 1 THEN 1 ELSE 0 END) as spk_pelaksanaan'),DB::raw('SUM(CASE WHEN ft_status = 2 THEN 1 ELSE 0 END) as spk_ok'), DB::raw('SUM(CASE WHEN ft_status = 3 THEN 1 ELSE 0 END) as spk_batal')])
            ->whereRaw("MONTH(ft_received) = '" . $month . "'")
            ->whereRaw("YEAR(ft_received) = '" . $year . "'")
            ->where('ft_type', '5')
            ->first();

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['year'] = $year;
        $load['month'] =  Carbon::parse($year . '-' . $month . '-01')->isoFormat('MMMM');
        $load['spk_blocking'] = $spkBlocking;
        $load['spk_pencabutan'] = $spkPencabutan;

        return view('pages/report/spk-index', $load);
    }

    public function Olt(Request $request){

        $load['title'] = 'Summary Data OlT';
        $load['sub_title'] = '';

        if ($request->has('reload')) {
            Http::get('http://202.169.224.46:8080/index.php/onu', ['connect_timeout' => 120]);
            return redirect(route('report-index'));
        }

        $response = Http::get('http://202.169.224.46:8080/index.php/onu/summary');

        if ($response->status() == 200) {
            //dd($response->object());

            $load['data'] = $response->object();
        }

        return view('pages/report/olt-index', $load);
    }
}
