@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header 
<div class="row"> 
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
             
            <div class="card-body">
                 
                <div class="mb-3 text-grey">
                    <b class="mb-3">Total Pelanggan</b>
                    <span class="ml-2"><i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-title="Total Pelanggan" data-placement="top" data-content="Total Pelanggan Kecuali Sewa Khusus dan CSR" data-original-title="" title=""></i></span>
                </div>
                 <div class="d-flex align-items-center mb-1">
                    <h2 class="text-white mb-0"><span data-animation="number" data-value="{{$totalPelanggan}}">0</span></h2>
                    <div class="ml-auto">
                        <div id="conversion-rate-sparkline"></div>
                    </div>
                </div>
                 @foreach($PelangganByStatus as $value)
                 <div class="d-flex mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-blue f-s-8 mr-2"></i>
                        {{ $value['status'] }}
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $value['total'] }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="@php echo precentage($value['total'],$totalPelanggan) @endphp">0</span>%</div>
                    </div>
                </div>
                 @endforeach

            </div>
             
        </div>
    </div>
     
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
             <div class="card-body">
                 <div class="mb-3 text-grey">
                    <b class="mb-3">Pelanggan Baru Bulan {{$month}}</b>

                </div>
                 <div class="d-flex align-items-center mb-1">
                    <h2 class="text-white mb-0"><span data-animation="number" data-value="{{$totalthis_month}}">0</span></h2>
                    <div class="ml-auto">
                        <div id="conversion-rate-sparkline"></div>
                    </div>
                </div>
                 @foreach($amthis_month as $value)
                 <div class="d-flex mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-blue f-s-8 mr-2"></i>
                        {{ $value->cupkg_acct_manager }}
                    </div>
                    <div class="d-flex align-items-center ml-auto">

                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{ $value->total }}">0</span></div>
                    </div>
                </div>
                 @endforeach

            </div>
            </div>
    </div>
</div>
-->
<!-- begin panel -->
<div class="row">
    <div class="col-xl-6 col-md-9">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <div id="in-bound">

            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-9">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <div id="out-bound">

            </div>
        </div>
    </div>
</div>
<div class="panel panel-inverse mb-3">

    <div class="panel-body">
        <div id="Pelanggan-chart" class="widget-chart-full-width nvd3-inverse-mode"></div>
    </div>
</div>
<!-- begin panel -->
<div class="row">
    <div class="col-xl-6 col-md-9">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <div id="spcode-chart">

            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-9">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <div id="pop-chart">

            </div>
        </div>
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
<script src="/assets/plugins/hightchart/modules/data.js"></script>
<script src="/assets/plugins/hightchart/modules/drilldown.js"></script>
<script src="/assets/plugins/hightchart/modules/exporting.js"></script>
<script src="/assets/plugins/hightchart/modules/export-data.js"></script>
<script src="/assets/plugins/hightchart/modules/accessibility.js"></script>
<script src="/assets/plugins/hightchart/modules/variable-pie.js"></script>
<script>
    Highcharts.chart('in-bound', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'In Stream Pelanggan '
        },
        subtitle: {
            text: 'Jumlah Total <?php echo $totalInBoud?>'
        },
        tooltip: {
            pointFormat: 'Jumlah: <b>{point.y}</b><br>' +
                'Presentase: <b>{point.percentage:.1f}%</b>'
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
            name: 'Status Pelanggan',
            colorByPoint: true,
            data: <?php echo $inBoundChart ?>,
            point: {
                events: {
                    click: function() {
                        window.location.href = "<?php echo route('customer-index') ?>" + "?cupkg_status="+this.x
                    }
                }
            }
        }]
    });
    Highcharts.chart('out-bound', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Out Stream Pelanggan'
        },
        subtitle:{
            text: 'Jumlah Total <?php echo $totalOuBoud?>'
        },
        tooltip: {
            pointFormat: 'Jumlah: <b>{point.y}</b><br>' +
                'Presentase: <b>{point.percentage:.1f}%</b>'
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
            name: 'Status Pelanggan',
            colorByPoint: true,
            data: <?php echo $outBoundChart ?>,
            point: {
                events: {
                    click: function() {
                        window.location.href = "<?php echo route('customer-index') ?>" + "?cupkg_status="+this.x
                    }
                }
            }
        }]
    });
    Highcharts.chart('spcode-chart', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Jenis Layanan Pelanggan, 2020'
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
            name: 'Layanan',
            colorByPoint: true,
            data: <?php echo $chartSpcode ?>
        }]
    });

    Highcharts.chart('pop-chart', {
        chart: {
            type: 'column'
        },
        title: {
            align: 'left',
            text: 'Pop Pelanggan, <?php echo  $year ?>'
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
            name: "POP",
            colorByPoint: true,
            data: <?php echo $chartPop ?>
        }],

    });
    Highcharts.chart('Pelanggan-chart', {
        chart: {
            type: 'column'
        },
        title: {
            align: 'left',
            text: 'Pertambahan Pelanggan Baru, <?php echo  $year ?>'
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
            text: 'Pelanggan Baru Berdasarkan AM <?php echo $year ?>'
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