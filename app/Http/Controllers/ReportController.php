<?php

namespace App\Http\Controllers;

use App\DataTables\PorfomaReport2DataTable;
use App\DataTables\PorfomaReportDatatable;
use App\DataTables\Scopes\ReportPorfomaScope;
use App\Models\InvoicePorfoma;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use DataTables;

class ReportController extends Controller
{
    var $arrPop = ['Bogor Valley', 'LIFEMEDIA', 'HABITAT', 'SINDUADI', 'GREENNET', 'X-LIFEMEDIA', 'LDP LIFEMEDIA', 'LDP X-LIFEMEDIA', 'JIP', 'Jogja Tronik', 'LDP JIP'];
    var $arrPiStatus = ['Blum Bayar', 'Lunas', 'Expired'];
    var $arrStatus = [1 => 'Registrasi', 'Instalasi', 'Setup', 'Sistem Aktif', 'Tidak Aktif', 'Trial', 'Sewa Khusus', 'Blokir', 'Ekslusif', 'CSR'];
    var $spkStatus = ['Tunggu', 'Pelaksanaan', 'OK', 'Batal', 'Reschdule'];


    public function penggunaBaru(Request $request)
    {
        $title = "Report Pelanggan Lifemedia";
        $subTitle = '';


        $year = $request->has('tahun') ? $request->input('tahun') : date('Y');
        $month = $request->has('bulan') ? $request->input('bulan') : date('m');

        $monthlyCustomer = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, MONTH(created) as bulan')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
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


        $monthlyCustomerweek = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, MONTH(created) as bulan,WEEK(created) as minggu')
            //->leftJoin('trel_cust_pkg','t_customer.cust_number','=','trel_cust_pkg.cust_number')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('bulan', 'minggu')
            ->orderBy('bulan')
            ->get();
        $monthlyCustomerPop = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, MONTH(created) as bulan,WEEK(created) as minggu,cust_pop')
            //->leftJoin('trel_cust_pkg','t_customer.cust_number','=','trel_cust_pkg.cust_number')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('minggu', 'cust_pop')
            ->orderBy('minggu')
            ->get();


        $susundrilldown = [];
        foreach ($monthlyCustomerweek as $key => $value) {
            $susundrilldown[$value->bulan]['name'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $susundrilldown[$value->bulan]['id'] = Carbon::parse($year . '-' . $value->bulan . '-01')->isoFormat('MMM YY');
            $date = Carbon::now();
            $week = $date->setISODate(date('y'), $value->minggu);
            $weekStartDate = $week->startOfWeek()->format('d-M');
            $weekEndDate = $week->endOfWeek()->format('d-M');
            $susundrilldown[$value->bulan]['data'][$key]['name'] = $weekStartDate . ' s/d ' . $weekEndDate;
            $susundrilldown[$value->bulan]['data'][$key]['y'] = $value->total;
            $susundrilldown[$value->bulan]['data'][$key]['drilldown'] = $weekStartDate . ' s/d ' . $weekEndDate;
        }
        $susundrilldown2 = [];
        foreach ($monthlyCustomerPop as $key => $value) {
            $week = $date->setISODate(date('y'), $value->minggu);
            $weekStartDate = $week->startOfWeek()->format('d-M');
            $weekEndDate = $week->endOfWeek()->format('d-M');
            $susundrilldown2[$value->minggu]['id'] = $weekStartDate . ' s/d ' . $weekEndDate;
            $susundrilldown2[$value->minggu]['data'][$key][] = $this->arrPop[$value->cust_pop];
            $susundrilldown2[$value->minggu]['data'][$key][] = $value->total;
        }


        foreach ($susundrilldown as $key => $value) {
            $susundrilldown[$key]['name'] = $value['name'];
            $susundrilldown[$key]['id'] = $value['id'];
            $susundrilldown[$key]['data'] = array_values($value['data']);
        }

        foreach ($susundrilldown2 as $key => $value) {
            $susundrilldown2[$key]['id'] = $value['id'];
            $susundrilldown2[$key]['data'] = array_values($value['data']);
        }

        $susundrilldown = array_merge_recursive($susundrilldown,( $susundrilldown2));
        //dd($susundrilldown);

        $monthlyAm = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, cupkg_acct_manager')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            //->where('cupkg_status', '4')
            //->where('sp_code', '!=', 'Life Vision - K')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('cupkg_acct_manager',)
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
            ->selectRaw('COUNT(t_customer.cust_number) as total, cupkg_acct_manager,MONTH(created) as bulan')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            //->where('sp_code','!=', 'Life Vision - K')
            ->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('cupkg_acct_manager', 'bulan')
            ->orderBy('bulan')
            ->get();

        $susundrilldownAm = [];
        $amthismonth = [];
        $totalThisMonth = 0;
        foreach ($monthlyAmPop as $key => $value) {
            if ($value->bulan == $month) {
                $totalThisMonth += $value->total;
                $amthismonth[$key] = $value;
            }
            if ($value->total > 1) {
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['name'] = $value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key;
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['id'] = $value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key;
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['data'][$key][] = Carbon::parse('2022-' . $value->bulan . '-01')->isoFormat('MMMM');
                $susundrilldownAm[$value->cupkg_acct_manager ? $value->cupkg_acct_manager : $key]['data'][$key][] = $value->total;
            }
        }
        foreach ($susundrilldownAm as $key => $value) {
            $susundrilldownAm[$key]['name'] = $value['name'];
            $susundrilldownAm[$key]['id'] = $value['id'];
            $susundrilldownAm[$key]['data'] = array_values($value['data']);
        }

        arsort($amthismonth);
        $load['amthis_month'] = $amthismonth;
        $load['totalthis_month'] = $totalThisMonth;
        $load['month'] = Carbon::parse('2022-' . $month . '-01')->isoFormat('MMMM YYYY');

        //dd($load);

        $allPelanggan = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, cupkg_status')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            //->where('cupkg_status', '!=', '7')
            //->where('cupkg_status', '!=', '9')
            //->where('cupkg_status', '!=', '10')
            //->whereRaw("YEAR(created) = '" . $year . "'")
            ->groupBy('cupkg_status')
            ->orderBy('total')
            ->get();

        $totalPelanggan = 0;
        $PelangganByStatus = [];
        $inBoundChart = [];
        $outBoundChart = [];
        $totalInBound = 0;
        $totalOutBound = 0;
        foreach ($allPelanggan as $key => $value) {

            if ($value->cupkg_status != 5 && $value->cupkg_status != 8) {
                $inBoundChart[$key]['name'] = isset($this->arrStatus[$value->cupkg_status]) ? $this->arrStatus[$value->cupkg_status] : '';
                $inBoundChart[$key]['x'] = $value->cupkg_status;
                $inBoundChart[$key]['y'] = $value->total;
                $totalInBound += $value->total;
            } else {
                $outBoundChart[$key]['name'] = isset($this->arrStatus[$value->cupkg_status]) ? $this->arrStatus[$value->cupkg_status] : '';
                $outBoundChart[$key]['x'] = $value->cupkg_status;
                $outBoundChart[$key]['y'] = $value->total;
                $outBoundChart[$key]['drilldown'] = $this->arrStatus[$value->cupkg_status];
                $totalOutBound += $value->total;
            }
            if ($value->cupkg_status) {
                $totalPelanggan += $value->total;
                $PelangganByStatus[$key]['total'] = $value->total;
                $PelangganByStatus[$key]['status'] = isset($this->arrStatus[$value->cupkg_status]) ? $this->arrStatus[$value->cupkg_status] : '';
            }
        }


        $custBySpcode = DB::table('t_customer')
            ->selectRaw('COUNT(t_customer.cust_number) as total, sp_code')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            //->where('cupkg_status', '!=', '7')
            //->where('cupkg_status', '!=', '9')
            //->where('cupkg_status', '!=', '10')
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

        $load['inBoundChart'] = json_encode(array_values($inBoundChart));
        $load['outBoundChart'] = json_encode(array_values($outBoundChart));
        $load['totalInBoud'] = $totalInBound;
        $load['totalOuBoud'] = $totalOutBound;

        return view('pages/report/pengguna-index', $load);
    }

    private function precentage($value, $total)
    {
        return round(($value / $total) * 100, 2);
    }
    public function invoice(Request $request)
    {
        $year = isset($xplodeFilter[1]) && $xplodeFilter[1] ? $xplodeFilter[1] : date('Y');
        $month = isset($xplodeFilter[0]) && $xplodeFilter[0] ? $xplodeFilter[0] : date('m');

        $title = "Report Invoice Lifemedia";
        $subTitle = 'Sampai Bulan ' . Carbon::parse($year . '-' . $month . '-01')->isoFormat('MMMM YY');;

        $invoiceMonthlyChart = DB::table('t_invoice')
            ->select([DB::raw('SUM(CASE WHEN inv_status = 1 THEN t_inv_item.ii_amount ELSE 0 END) as total_pi_lunas'), DB::raw('SUM(CASE WHEN inv_status = 0 THEN t_inv_item.ii_amount ELSE 0 END) as total_pi_tidak_lunas'), DB::raw("SUM(CASE WHEN inv_status = 2 THEN t_inv_item.ii_amount ELSE 0 END) as total_pi_expired"), DB::raw("month(inv_start) as bulan")])
            ->leftJoin('t_inv_item', function ($join) {
                $join->on('t_invoice.inv_number', '=', 't_inv_item.inv_number')->where('ii_recycle', '<>', 1);;
            })
            ->whereRaw("MONTH(inv_start) != '0'")
            ->whereRaw("MONTH(inv_start) <= '" . $month . "'")
            ->whereRaw("YEAR(inv_start) = '" . $year . "'")
            //->where('inv_status', '1')
            ->groupByRaw("MONTH(inv_start)")
            ->orderBy('bulan')
            ->get();

        $susunInvMonthly = [];
        $chartInvValueMonthly[0]['name'] = 'Porfoma Lunas';
        $chartInvValueMonthly[1]['name'] = 'Porfoma Belum Lunas';
        $chartInvValueMonthly[2]['name'] = 'Porfoma Expired';
        foreach ($invoiceMonthlyChart as $key => $value) {
            $chartInvLabelMonthly[] = Carbon::parse(date('Y') . "-" . $value->bulan)->isoFormat('MMM YY');
            $chartInvValueMonthly[0]['data'][$key] = intval($value->total_pi_lunas);
            $chartInvValueMonthly[1]['data'][$key] = intval($value->total_pi_tidak_lunas);
            $chartInvValueMonthly[2]['data'][$key] = intval($value->total_pi_expired);
        }
        $susunInvMonthly['label'] = json_encode($chartInvLabelMonthly);
        $susunInvMonthly['value'] = json_encode($chartInvValueMonthly);

        //dd($susunInvMonthly);

        $porfomaMonthlyChart = DB::table('t_invoice_porfoma')
            ->select([DB::raw('SUM(CASE WHEN inv_status = 1 THEN t_inv_item_porfoma.ii_amount ELSE 0 END) as total_pi_lunas'), DB::raw('SUM(CASE WHEN inv_status = 0 THEN t_inv_item_porfoma.ii_amount ELSE 0 END) as total_pi_tidak_lunas'), DB::raw("SUM(CASE WHEN inv_status = 2 THEN t_inv_item_porfoma.ii_amount ELSE 0 END) as total_pi_expired"), DB::raw("month(inv_start) as bulan")])
            ->leftJoin('t_inv_item_porfoma', function ($join) {
                $join->on('t_invoice_porfoma.inv_number', '=', 't_inv_item_porfoma.inv_number')->where('ii_recycle', '<>', 1);;
            })
            ->whereRaw("MONTH(inv_start) != '0'")
            ->whereRaw("MONTH(inv_start) <= '" . $month . "'")
            ->whereRaw("YEAR(inv_start) = '" . $year . "'")
            //->where('inv_status', '1')
            ->groupByRaw("MONTH(inv_start)")
            ->orderBy('bulan')
            ->get();
        $susunChartMonthly = [];
        $chartValueMonthly[0]['name'] = 'Porfoma Lunas';
        $chartValueMonthly[1]['name'] = 'Porfoma Belum Lunas';
        $chartValueMonthly[2]['name'] = 'Porfoma Expired';
        foreach ($porfomaMonthlyChart as $key => $value) {
            $chartLabelMonthly[] = Carbon::parse(date('Y') . "-" . $value->bulan)->isoFormat('MMM YY');
            $chartValueMonthly[0]['data'][$key] = intval($value->total_pi_lunas);
            $chartValueMonthly[1]['data'][$key] = intval($value->total_pi_tidak_lunas);
            $chartValueMonthly[2]['data'][$key] = intval($value->total_pi_expired);
        }
        $susunChartMonthly['label'] = json_encode($chartLabelMonthly);
        $susunChartMonthly['value'] = json_encode($chartValueMonthly);

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['year'] = $year;
        $load['month'] =  Carbon::parse($year . '-' . $month . '-01')->isoFormat('MMMM');;
        $load['porfomaChartMonthly'] = $susunChartMonthly;
        $load['invoiceChartMonthly'] = $susunInvMonthly;

        return view('pages/report/invoice-index', $load);
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
        //dd($porfomaLunas);

        $porfomaStatus = DB::table('t_invoice_porfoma')
            ->select([DB::raw('count(inv_number) as total_pi'), DB::raw('SUM(CASE WHEN inv_status = 1 THEN 1 ELSE 0 END) as total_pi_lunas'), DB::raw('SUM(CASE WHEN inv_status = 0 THEN 1 ELSE 0 END) as total_pi_tidak_lunas'), DB::raw('SUM(CASE WHEN inv_status = 2 THEN 1 ELSE 0 END) as total_pi_expired')])
            ->whereRaw("MONTH(inv_start) = '" . $month . "'")
            ->whereRaw("YEAR(inv_start) = '" . $year . "'")
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
            $chartLabel[] = Carbon::parse($value->tanggal)->isoFormat('D MMM YY');
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

    public function porfomaDetail(PorfomaReportDataTable $dataTable, Request $request)
    {
        $title = 'Datail Report Porfoma';
        $subTitle = '';

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['date'] = $request->has('inv_start') ?  $request->input('inv_start') . ' s/d ' . $request->input('inv_start') : '';
        $load['pi_status'] = $request->has('pi_status') ?  $request->input('pi_status') : '';
        $load['bulan'] = $request->has('bulan') ?  $request->input('bulan') : '';
        //dd($load);

        return $dataTable->addScope(new ReportPorfomaScope($request))->render('pages/report/porfoma-detail', $load);
    }

    public function porfomaDetailOld(PorfomaReportDatatable $dataTable, $date)
    {

        $title = 'Data Porfoma';
        $subTitle = 'Periode Mulai ' . Carbon::parse($date)->isoFormat('D MMMM YYYY');

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;

        $arrfield = $this->arrFieldPorfoma();
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
        $load['dates'] = $date;
        //dd($tableColumn);

        return view('pages/report/porfoma-detail', $load);


        /*$porfoma = InvoicePorfoma::where('inv_start', $date)
            ->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
            ->leftJoin('trel_cust_pkg', function ($join) {
                $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number')->on('trel_cust_pkg._nomor', '=', 't_invoice_porfoma.sp_nom');
            })
            ->paginate(10);
        dd($porfoma);*/
    }

    public function porfomaList(Request $request, $date)
    {
        if ($request->ajax()) {
            $builder =  InvoicePorfoma::where('inv_start', $date)
                ->leftJoin('t_customer', 't_invoice_porfoma.cust_number', '=', 't_customer.cust_number')
                ->leftJoin('trel_cust_pkg', function ($join) {
                    $join->on('t_customer.cust_number', '=', 'trel_cust_pkg.cust_number')->on('trel_cust_pkg._nomor', '=', 't_invoice_porfoma.sp_nom');
                });

            if ($request->has('filter_cupkg_status')) {
                $builder->where('cupkg_status', $request->input('filter_cupkg_status'));
            }
            if ($request->has('filter_inv_status')) {
                $builder->where('inv_status', $request->input('filter_inv_status'));
            }
            $porfoma = $builder->latest()->get();

            return Datatables::of($porfoma)
                ->addIndexColumn()
                ->editColumn('cupkg_status', function ($user) {
                    return $user->cupkg_status ? $this->arrStatus[$user->cupkg_status] : '';
                })
                ->editColumn('inv_status', function ($user) {
                    return isset($user->inv_status) ? $this->arrPiStatus[$user->inv_status] :  $user->inv_status;
                })
                ->editColumn('inv_post', function ($user) {
                    return Carbon::parse($user->inv_post)->isoFormat('D MMMM YYYY HH:mm');
                })
                ->editColumn('inv_start', function ($user) {
                    return Carbon::parse($user->inv_post)->isoFormat('D MMMM YYYY');
                })
                ->addColumn('detail', function ($row) {
                    $actionBtn = '<a href="' . route('customer-detail', $row->cust_number) . '" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a>';
                    return $actionBtn;
                })
                ->rawColumns(['detail'])

                ->make(true);
        }
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
            ->select([DB::raw('count(ft_number) as total_spk'), DB::raw('SUM(CASE WHEN ft_status = 0 THEN 1 ELSE 0 END) as spk_tunggu'), DB::raw('SUM(CASE WHEN ft_status = 1 THEN 1 ELSE 0 END) as spk_pelaksanaan'), DB::raw('SUM(CASE WHEN ft_status = 2 THEN 1 ELSE 0 END) as spk_ok'), DB::raw('SUM(CASE WHEN ft_status = 3 THEN 1 ELSE 0 END) as spk_batal')])
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

    public function pelangganBerhenti(Request $request)
    {

        $filter =  $request->input('filter');
        $xplodeFilter = explode('-', $filter);

        $year = isset($xplodeFilter[1]) && $xplodeFilter[1] ? $xplodeFilter[1] : date('Y');


        $title = "Pelanggan Berhenti ";
        $subTitle = 'Berdasarakn SPK Pencabutan ' . $year;


        $spkPencabutan = DB::table('t_field_task')
            //->selectRaw('count(ft_number) as jumlah,ft_status')
            ->select(DB::raw("t_customer.cust_number,trel_cust_pkg.sp_code,DATE_FORMAT(ft_received,'%Y-%m-%d') as tgl_spk"), 'cupkg_status', 'cupkg_svc_begin', 'ft_status')
            ->whereRaw("YEAR(ft_received) = '" . $year . "'")
            ->leftJoin('t_customer', 't_field_task.cust_number', '=', 't_customer.cust_number')
            ->leftJoin('trel_cust_pkg', 't_customer.cust_number', '=', 'trel_cust_pkg.cust_number')
            ->where('ft_type', '5')
            ->groupBy(DB::raw("t_customer.cust_number"))
            //->limit('10')
            ->get()->toArray();
        //dd($spkPencabutan);

        $aktifKembali = 0;
        $totalPencabutan = 0;
        $status[0] = 1;
        $status[1] = 1;
        $status[2] = 1;
        //$status[3] = 1;
        $status[4] = 1;
        $dateDiv = [];
        foreach ($spkPencabutan as $key => $value) {
            $totalPencabutan += 1;
            if ($value->cupkg_status == 4) {
                $aktifKembali += 1;
            } else {
                if ($value->ft_status == 0) {
                    $status[0] += 1;
                } elseif ($value->ft_status == 1) {
                    $status[1] += 1;
                } elseif ($value->ft_status == 2) {
                    $status[2] += 1;
                } elseif ($value->ft_status == 3) {
                    $aktifKembali += 1;
                } elseif ($value->ft_status == 4) {
                    $status[4] += 1;
                }
                $interval = date_diff(date_create($value->tgl_spk), date_create($value->cupkg_svc_begin));

                $dateDiv[] = $interval->format('%m');
            }
        }
        $numbers = array();

        foreach ($dateDiv as $field) {
            if (isset($numbers[$field])) {
                $numbers[$field] += 1;
            } else {
                $numbers[$field] = 1;
            }
        }

        $sumMonth = 0;
        foreach ($numbers as $key => $value) {
            $sumMonth += $key;
        }

        foreach ($status as $key => $value) {
            $susunStatus[$key]['name'] = $this->spkStatus[$key];
            $susunStatus[$key]['y'] = $value;
            $susunStatus[$key]['z'] = 90;
        }

        $susunStatus[5]['name'] = 'Aktif Kembali';
        $susunStatus[5]['y'] = $aktifKembali;
        $susunStatus[5]['z'] = 235;
        //dd($susunStatus);

        $susunChartBulan = [];
        sort($numbers);
        foreach ($numbers as $key => $value) {
            $susunChartBulan[$key]['name'] =  $key;
            $susunChartBulan[$key]['y'] =  $value;
        }

        $load['title'] = $title;
        $load['sub_title'] = $subTitle;
        $load['year'] = $year;
        $load['total_spk'] = $totalPencabutan;
        $load['aktif_kembali'] = $aktifKembali;
        $load['jumlah_status'] = json_encode(array_values($susunStatus));
        $load['average'] = round(array_sum($numbers) / $sumMonth, 2);
        $load['chart_bulan'] = json_encode(array_values($susunChartBulan));

        //dd($load);

        return view('pages/report/pelanggan-berhenti', $load);
    }

    public function Olt(Request $request)
    {

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

    private function arrFieldPorfoma()
    {
        return [
            'cust_number' => [
                'label' => 'Nomor',
                'orderable' => true,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'cust_name' => [
                'label' => 'Nama',
                'orderable' => false,
                'searchable' => true,
                'form_type' => 'text',
            ],
            'sp_code' => [
                'label' => 'Layanan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'cupkg_status' => [
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                'keyvaldata' => $this->arrStatus
            ],
            'inv_number' => [
                'label' => 'Nomor PI',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'text',

            ],
            'inv_status' => [
                'label' => 'Status PI',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'select',
                'keyvaldata' => $this->arrPiStatus
            ],
            'inv_post' => [
                'label' => 'Posted',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'date',
            ],
            'inv_start' => [
                'label' => 'Mulai Layanan',
                'orderable' => false,
                'searchable' => false,
                'form_type' => 'date',
            ],
            'wa_sent' => [
                'label' => 'Kirim Invoice',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
            ],
            'wa_sent_number' => [
                'label' => 'Kirim Nomor',
                'orderable' => true,
                'searchable' => false,
                'form_type' => 'text',
            ],
        ];
    }
}
