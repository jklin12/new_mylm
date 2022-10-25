@extends('layouts.default')

@section('title', 'Blank Page')

@push('css')
<link rel="stylesheet" href="/assets/plugins/select2/dist/css/select2.min.css">
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet" />
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" />
@endpush

@section('content')
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->


<form action="" method="get" id="filter-form">
    <div class="d-sm-flex align-items-center mb-3">
        <a href="#" class="btn btn-inverse btn-pink mr-2 text-truncate" id="datepicker">
            <i class="fa fa-calendar fa-fw text-white-transparent-5 ml-n1"></i>
            <span>{{ $month}}</span>
            <b class="caret"></b>
            <input id="reservationDate" type="hidden" name="filter" />
        </a>

        <!--<div class="text-muted f-w-600 mt-2 mt-sm-0">compared to <span id="daterange-prev-date">24 Mar-30 Apr 2020</span></div>-->
    </div>
</form>



<!-- begin panel -->
<div class="row mb-3">
    <div class="col-xl">
        <div id="invoice-chart-monthly" class="widget-chart-full-width nvd3-inverse-mode"></div>
    </div>
</div>

<!-- begin panel -->
<div class="row mb-3">
    <div class="col-xl">
        <div id="porfoma-chart-monthly" class="widget-chart-full-width nvd3-inverse-mode"></div>
    </div>
</div>


 
<!-- end panel -->
@endsection

@push('scripts')
<script src="/assets/plugins/hightchart/highcharts.js"></script>
<script src="/assets/plugins/select2/dist/js/select2.min.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/modules/variable-pie.js"></script>
<script src="/assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>
<script>
    $("#datepicker").datepicker({
        format: "mm-yyyy",
        startView: "months",
        minViewMode: "months"
    }).on('changeDate', function(ev) {
        $('#filter-form').submit();
    });
    Highcharts.chart('invoice-chart-monthly', {
        chart: {
            type: 'area',
        },
        title: {
            text: 'Grafik Invoice'
        },

        xAxis: {
            categories: <?php echo $invoiceChartMonthly['label'] ?>,
            crosshair: true,
        },
        yAxis: {
            title: {
                useHTML: true,
                text: 'Jumlah'
            }
        },

        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            },
            series: {
                dataLabels: {
                    enabled: true,

                },
                cursor: 'pointer',
                
            }
        },
        series: <?php echo $invoiceChartMonthly['value'] ?>,
    });
    Highcharts.chart('porfoma-chart-monthly', {
        chart: {
            type: 'area',
        },
        title: {
            text: 'Grafik Porfoma'
        },

        xAxis: {
            categories: <?php echo $porfomaChartMonthly['label'] ?>,
            crosshair: true,
        },
        yAxis: {
            title: {
                useHTML: true,
                text: 'Jumlah'
            }
        },

        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            },
            series: {
                dataLabels: {
                    enabled: true,

                },
                cursor: 'pointer',
                
            }
        },
        series: <?php echo $porfomaChartMonthly['value'] ?>,
    });
</script>
@endpush