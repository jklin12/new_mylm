@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse">

    <div class="panel-body">
        <div id="doku-chart" class="widget-chart-full-width nvd3-inverse-mode"></div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="panel panel-inverse">

            <div class="panel-body">
                <div id="payChannel-chart" class="widget-chart-full-width nvd3-inverse-mode"></div>
            </div>
        </div>
    </div>
</div>
<!-- end panel -->
@endsection

@push('scripts')
<script src="/assets/plugins/hightchart/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/modules/variable-pie.js"></script>

<script>
    Highcharts.chart('doku-chart', {
        chart: {
            type: 'column'
        },
        title: {
            align: 'left',
            text: 'Pembayran Doku Pada Tahun <?php echo $year ?>'
        },
        subtitle: {
            align: 'left',
            text: 'Clik Grafik untuk melihat data harian'
        },
        accessibility: {
            announceNewData: {
                enabled: true
            }
        },
        xAxis: {
            type: 'category'
        },
        yAxis: {
            title: {
                text: 'Total percent market share'
            }

        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,

                }
            }
        },

        tooltip: {

            pointFormat: '<span >Rp. </span><b>{point.y}</b> <br/>'
        },

        series: [{
            name: "Pembayaran Doku",
            colorByPoint: true,
            data: <?php echo $monthlyChart ?>
        }],
        drilldown: {
            breadcrumbs: {
                position: {
                    align: 'right'
                }
            },
            series: <?php echo $drilldownData ?>
        }
    });
    Highcharts.chart('payChannel-chart', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Metode Pembayaran Pada, 2020'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                }
            }
        },
        series: [{
            name: 'Bank',
            colorByPoint: true,
            data: <?php echo $payChannelChart?>
        }]
    });
</script>
@endpush