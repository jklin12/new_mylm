<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];

    public function penggunaBaru(Request $request)
    {
        $title = "Pertambahan Pengguna Baru";
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

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['year'] = $year;

        $load['monthlyChart'] = json_encode($susunChartCust);
        $load['drilldownData'] = json_encode(array_values($susundrilldown));

        $load['monthlyChartAm'] = json_encode(array_values($susunChartAm));
        $load['drilldownDataAm'] = json_encode(array_values($susundrilldownAm));

        return view('pages/report/pengguna-index', $load);
    }
}
