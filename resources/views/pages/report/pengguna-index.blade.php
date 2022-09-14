@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse mb-3">

    <div class="panel-body">
        <div id="pengguna-chart" class="widget-chart-full-width nvd3-inverse-mode"></div>
    </div>
</div>
<div class="panel panel-inverse">

    <div class="panel-body">
        <div id="am-chart" class="widget-chart-full-width nvd3-inverse-mode"></div>
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
    Highcharts.chart('pengguna-chart', {
        chart: {
            type: 'column'
        },
        title: {
            align: 'left',
            text: '<?php echo $title.', '.$year ?>'
        },
        subtitle: {
            align: 'left',
            text: 'Clik Grafik untuk melihat data berdasarkan POP'
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
                text: 'Jumlah Pelanggan'
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

            pointFormat: '<span > </span><b>{point.y}</b> <br/>'
        },

        series: [{
            name: "Bulan",
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
    Highcharts.chart('am-chart', {
        chart: {
            type: 'column'
        },
        title: {
            align: 'left',
            text: 'Pengguna Baru Berdasarkan AM <?php echo $year ?>'
        },
        subtitle: {
            align: 'left',
            text: 'Clik Grafik untuk melihat data berdasarkan POP'
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
                text: 'Jumlah Pelanggan'
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

            pointFormat: '<span ></span><b>{point.y}</b> <br/>'
        },

        series: [{
            name: "Account Manager",
            colorByPoint: true,
            data: <?php echo $monthlyChartAm ?>
        }],
        drilldown: {
            breadcrumbs: {
                position: {
                    align: 'right'
                }
            },
            series: <?php echo $drilldownDataAm ?>
        }
    });
</script>

 @endpush