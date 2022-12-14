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
<div class="row">
    <!-- begin col-3 -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <!-- begin card-body -->
            <div class="card-body">
                <!-- begin title -->
                <div class="mb-3 text-grey">
                    <b class="mb-3">Total SPK Blocking</b>
                    <span class="ml-2"><i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-title="Total Pelanggan" data-placement="top" data-content="Total Porforma Status Bayar" data-original-title="" title=""></i></span>
                </div>
                <!-- end title -->
                <!-- begin conversion-rate -->
                <div class="d-flex align-items-center mb-1">
                    <h2 class="text-white mb-0"><span data-animation="number" data-value="{{$spk_blocking->total_spk}}">0</span></h2>
                    <div class="ml-auto">
                        <div id="conversion-rate-sparkline"></div>
                    </div>
                </div>
                <!-- end conversion-rate -->
              
                <!-- begin info-row -->
                <div class="d-flex mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-warning f-s-8 mr-2"></i>
                        SPK Tunggu
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"> <span data-animation="number" data-value="{{ $spk_blocking->spk_tunggu }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="@php echo precentage($spk_blocking->spk_tunggu,$spk_blocking->total_spk) @endphp">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->
                <!-- begin info-row -->
                <div class="d-flex">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-aqua f-s-8 mr-2"></i>
                        SPK OKE
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $spk_blocking->spk_ok }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="@php echo precentage($spk_blocking->spk_ok,$spk_blocking->total_spk) @endphp">0.00</span>%</div>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-aqua f-s-8 mr-2"></i>
                        SPK Batal
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $spk_blocking->spk_batal }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="@php echo precentage($spk_blocking->spk_batal,$spk_blocking->total_spk) @endphp">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->


            </div>
            <!-- end card-body -->
        </div>
    </div>
    <!-- end col-3 -->
    <!-- begin col-3 -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <!-- begin card-body -->
            <div class="card-body">
                <!-- begin title -->
                <div class="mb-3 text-grey">
                    <b class="mb-3">Total SPK Pencabutan</b>
                    <span class="ml-2"><i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-title="Total Pelanggan" data-placement="top" data-content="Total Porforma Status Bayar" data-original-title="" title=""></i></span>
                </div>
                <!-- end title -->
                <!-- begin conversion-rate -->
                <div class="d-flex align-items-center mb-1">
                    <h2 class="text-white mb-0"><span data-animation="number" data-value="{{$spk_pencabutan->total_spk}}">0</span></h2>
                    <div class="ml-auto">
                        <div id="conversion-rate-sparkline"></div>
                    </div>
                </div>
                <!-- end conversion-rate -->
              
                <!-- begin info-row -->
                <div class="d-flex mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-warning f-s-8 mr-2"></i>
                        SPK Tunggu
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"> <span data-animation="number" data-value="{{ $spk_pencabutan->spk_tunggu }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="@php echo precentage($spk_pencabutan->spk_tunggu,$spk_pencabutan->total_spk) @endphp">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->
                <!-- begin info-row -->
                <div class="d-flex">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-aqua f-s-8 mr-2"></i>
                        SPK OKE
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $spk_pencabutan->spk_ok }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="@php echo precentage($spk_pencabutan->spk_ok,$spk_pencabutan->total_spk) @endphp">0.00</span>%</div>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-aqua f-s-8 mr-2"></i>
                        SPK Batal
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $spk_pencabutan->spk_batal }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="@php echo precentage($spk_pencabutan->spk_batal,$spk_pencabutan->total_spk) @endphp">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->


            </div>
            <!-- end card-body -->
        </div>
    </div>
    <!-- end col-3 -->


</div>

<div class="row">
    <div class="col">
        <div class="panel panel-inverse">

            <div class="panel-body">
                <div id="porfoma-chart" class="widget-chart-full-width nvd3-inverse-mode"></div>
            </div>
        </div>
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

<script>
    $("#datepicker").datepicker({
        format: "mm-yyyy",
        startView: "months",
        minViewMode: "months"
    }).on('changeDate', function(ev) {
        $('#filter-form').submit();
    });
     
</script>
@endpush