@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->

<!-- begin panel -->
<div class="row">
    <div class="col-xl-6 col-md-9">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <div id="status-chart">

            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-9">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <div id="berhenti-chart">

            </div>
        </div>
    </div>
</div>

<!-- end panel -->
@endsection

@push('scripts')
<script src="/assets/plugins/hightchart/highcharts.js"></script>  
<script src="https://code.highcharts.com/modules/variable-pie.js"></script>
<script>
    Highcharts.chart('status-chart', {
        chart: {
            type: 'variablepie'
        },
        title: {
            text: 'SPK Pencabutan <?php echo  $year ?>'
        },
        subtitle: {
            text: 'Total <?php echo  $total_spk ?>'
        },
        tooltip: {
            pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name}</b><br/>' +
                'Jumlah SPK : <b>{point.y}</b><br/>'
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
                    enabled: false
                },
                showInLegend: true
            }
        },

        series: [{
            minPointSize: 10,
            innerSize: '20%',
            zMin: 0,
            name: 'countries',
            data: <?php echo $jumlah_status ?>
        }]
    });

    Highcharts.chart('berhenti-chart', {
        chart: {
            type: 'column'
        },
        title: {
            align: 'left',
            text: 'Lama Berlangganan Dalam Bulan'
        },
        subtitle: {
            align: 'left',
            text: 'Rata rata lama berlangganan <?php echo $average?> bulan'
        },
         
        xAxis: {
            type: 'category'
        },
        yAxis: {
            title: {
                text: 'Jumlah Pelanggan Berhenti'
            }

        },
        legend: {
            enabled: true
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y}'
                }
            }
        },

        tooltip: {
            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> of total<br/>'
        },

        series: [{
            name: "BUlan",
            colorByPoint: true,
            data:<?php echo $chart_bulan?>
        }],
    
    });
</script>

@endpush