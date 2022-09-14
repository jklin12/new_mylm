<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportDokuController extends Controller
{
    public function index(Request $request)
    {

        $title = "Data Pembyaran Melalui Doku";
        $subTitle = '';

        $year = $request->has('tahun') ? $request->input('tahun') : date('Y');

        $monthlyRevenue = DB::table('t_pay_request')
            ->selectRaw('SUM(amount) as total, MONTH(insert_date) as bulan')
            ->where('result_msg', 'SUCCESS')
            ->whereRaw("YEAR(insert_date) = '" . $year . "'")
            ->groupBy('bulan')
            ->get();
        //$labelMonthChart = [];
        //$valueMonthChart = [];
        $susunChartDoku = [];
        foreach ($monthlyRevenue as $key => $value) {
            //$labelMonthChart[] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            //$valueMonthChart[] = $value->total;

            $susunChartDoku[$key]['name'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $susunChartDoku[$key]['y'] = $value->total;
            $susunChartDoku[$key]['drilldown'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
        }

        $dailyRevenue = DB::table('t_pay_request')
            ->selectRaw("SUM(amount) as total, DATE_FORMAT(insert_date,'%Y-%m-%d') as tanggal,MONTH(insert_date) as bulan")
            ->where('result_msg', 'SUCCESS')
            ->whereRaw("YEAR(insert_date) = '" . $year . "'")
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $susundrilldown = [];
        foreach ($dailyRevenue as $key => $value) {
            $susundrilldown[$value->bulan]['name'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $susundrilldown[$value->bulan]['id'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $susundrilldown[$value->bulan]['data'][$key][] = Carbon::parse($value->tanggal)->isoFormat('DD MMM YY');
            $susundrilldown[$value->bulan]['data'][$key][] = $value->total;
        }
        foreach ($susundrilldown as $key => $value) {
            $susundrilldown[$key]['name'] = $value['name'];
            $susundrilldown[$key]['id'] = $value['id'];
            $susundrilldown[$key]['data'] = array_values($value['data']);
        }
        //print_r($susundrilldown);die;

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['year'] = $year;
        //$monthlyChat['label'] = json_encode($labelMonthChart);
        //$monthlyChat['value'] = json_encode($valueMonthChart);
        $payChannel = DB::table('t_pay_request')
            ->selectRaw("count(id_pay_req) as jumlah,description")
            ->where('result_msg', 'SUCCESS')
            ->whereRaw("YEAR(insert_date) = '" . $year . "'")
            ->join('t_pay_channel','t_pay_request.payment_channel','=','t_pay_channel.code')
            ->groupBy('payment_channel')
            ->orderBy('payment_channel')
            ->get();

        $susunPaychannel = [];
        foreach ($payChannel as $key => $value) {
            $susunPaychannel[$key]['name'] = $value->description;
            $susunPaychannel[$key]['y'] = $value->jumlah;
            if ($value->description == 'Alfamart') {
                $susunPaychannel[$key]['sliced'] = 'true';
                $susunPaychannel[$key]['selected'] = 'true';
            }
        }
        

        $load['monthlyChart'] = json_encode($susunChartDoku);
        $load['payChannelChart'] = json_encode($susunPaychannel);
        $load['drilldownData'] = json_encode(array_values($susundrilldown));

        return view('pages/report/doku-index', $load);
    }
}
