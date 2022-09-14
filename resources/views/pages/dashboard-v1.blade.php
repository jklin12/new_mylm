@extends('layouts.default')

@section('title', 'Dashboard V3')

@push('css')
<link href="/assets/plugins/jvectormap-next/jquery-jvectormap.css" rel="stylesheet" />
<link href="/assets/plugins/nvd3/build/nv.d3.css" rel="stylesheet" />
<link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet" />
@endpush

@section('content')
<!-- begin breadcrumb -->
<ol class="breadcrumb float-xl-right">
	<li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
	<li class="breadcrumb-item"><a href="javascript:;">Dashboard</a></li>
	<li class="breadcrumb-item active">Dashboard v3</li>
</ol>
<!-- end breadcrumb -->
<!-- begin page-header -->
<h1 class="page-header mb-3">Dashboard Lifmedia</h1>
<!-- end page-header -->
<!-- begin daterange-filter -->
<div class="d-sm-flex align-items-center mb-3">
	<a href="#" class="btn btn-inverse btn-pink mr-2 text-truncate" id="daterange-filter">
		<i class="fa fa-calendar fa-fw text-white-transparent-5 ml-n1"></i>
		<span>1 Jun 2020 - 7 Jun 2020</span>
		<b class="caret"></b>
	</a>
	<!--<div class="text-muted f-w-600 mt-2 mt-sm-0">compared to <span id="daterange-prev-date">24 Mar-30 Apr 2020</span></div>-->
</div>
<!-- end daterange-filter -->
<form action="" method="get" id="filter-form">
	<input type="hidden" name="start" id="filter-date-start">
	<input type="hidden" name="end" id="filter-date-end">
</form>
<!-- begin row -->
<div class="row">
	<!-- begin col-6 -->
	<div class="col-xl-6">
		<!-- begin card -->
		<div class="card border-0 bg-pink text-white mb-3 overflow-hidden">
			<!-- begin card-body -->
			<div class="card-body">
				<!-- begin row -->
				<div class="row">
					<!-- begin col-7 -->
					<div class="col-xl-7 col-lg-8">
						<!-- begin title -->
						<div class="text-grey">
							<b>TOTAL PEMBAYARAN DOKU</b>

						</div>
						<!-- end title -->
						<div class="mb-3 text-grey">
							{{ \Carbon\Carbon::parse($curentDate["dateStart"])->isoFormat('dddd, D MMMM').' s/d '.\Carbon\Carbon::parse($curentDate["dateEnd"])->isoFormat('dddd, D MMMM Y') }}
						</div>
						<!-- begin total-sales -->
						<div class="d-flex mb-1">
							<h2 class="mb-0">RP<span data-animation="number" data-value="{{ ($curentTotal->total) }}">0.00</span></h2>
							<div class="ml-auto mt-n1 mb-n1">
								<div id="total-sales-sparkline"></div>
							</div>
						</div>
						<!-- end total-sales -->
						<!-- begin percentage -->

						<!-- end percentage -->
						<hr class="bg-white-transparent-2" />
						<!-- begin row -->
						<div class="row text-truncate">
							<!-- begin col-6 -->
							<div class="col-6">
								<div class="f-s-12 text-grey">Total Porfoma Dibayar</div>
								<div class="f-s-18 m-b-5 f-w-600 p-b-1" data-animation="number" data-value="{{ $curentTotal->total_pi}}">0</div>
								<div class="progress progress-xs rounded-lg bg-dark-darker m-b-5">
									<div class="progress-bar progress-bar-striped rounded-right bg-teal" data-animation="width" data-value="55%" style="width: 0%"></div>
								</div>
							</div>
							<!-- end col-6 -->
							<!-- begin col-6 
							<div class="col-6">
								<div class="f-s-12 text-grey">Avg. sales per order</div>
								<div class="f-s-18 m-b-5 f-w-600 p-b-1">$<span data-animation="number" data-value="41.20">0.00</span></div>
								<div class="progress progress-xs rounded-lg bg-dark-darker m-b-5">
									<div class="progress-bar progress-bar-striped rounded-right" data-animation="width" data-value="55%" style="width: 0%"></div>
								</div>
							</div>
							 end col-6 -->
						</div>
						<!-- end row -->
					</div>
					<!-- end col-7 -->
					<!-- begin col-5 -->
					<div class="col-xl-5 col-lg-4 align-items-center d-flex justify-content-center">
						<img src="/assets/img/svg/img-1.svg" height="150px" class="d-none d-lg-block" />
					</div>
					<!-- end col-5 -->
				</div>
				<!-- end row -->
			</div>
			<!-- end card-body -->
		</div>
		<!-- end card -->
	</div>
	<!-- end col-6 -->
	<!-- begin col-6 -->
	<div class="col-xl-6">
		<!-- begin row -->
		<div class="row">
			<!-- begin col-6 -->
			<div class="col-sm-6">
				<!-- begin card -->
				<div class="card border-0 bg-pink text-white text-truncate mb-3">
					<!-- begin card-body -->
					<div class="card-body">
						<!-- begin title -->
						<div class="mb-3 text-grey">
							<b class="mb-3">Rating Porfoma</b>
							<span class="ml-2"><i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-title="Conversion Rate" data-placement="top" data-content="Percentage of sessions that resulted in orders from total number of sessions." data-original-title="" title=""></i></span>
						</div>
						<!-- end title -->
						<!-- begin conversion-rate -->
						<div class="d-flex align-items-center mb-1">
							<h2 class="text-white mb-0"><span data-animation="number" data-value="{{ $piData->total_pi}}">0.00</span></h2>
							<div class="ml-auto">
								<div id="conversion-rate-sparkline"></div>
							</div>
						</div>
						<!-- end conversion-rate -->
						<!-- begin percentage -->

						<!-- end percentage -->
						<!-- begin info-row -->
						<div class="d-flex mb-2">
							<div class="d-flex align-items-center">
								<i class="fa fa-circle text-blue f-s-8 mr-2"></i>
								Porfoma Lunas
							</div>
							<div class="d-flex align-items-center ml-auto">
								<div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $piData->total_pi_lunas }}">0</span></div>
								<div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{ $pi_lunas }}">0.00</span>%</div>
							</div>
						</div>
						<!-- end info-row -->
						<!-- begin info-row -->
						<div class="d-flex mb-2">
							<div class="d-flex align-items-center">
								<i class="fa fa-circle text-warning f-s-8 mr-2"></i>
								Porfoma Belum Lunas
							</div>
							<div class="d-flex align-items-center ml-auto">
								<div class="text-grey f-s-11"> <span data-animation="number" data-value="{{ $piData->total_pi_tidak_lunas }}">0</span></div>
								<div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{ $pi_belum_lunas }}">0.00</span>%</div>
							</div>
						</div>
						<!-- end info-row -->
						<!-- begin info-row -->
						<div class="d-flex">
							<div class="d-flex align-items-center">
								<i class="fa fa-circle text-aqua f-s-8 mr-2"></i>
								Porfoma Expired
							</div>
							<div class="d-flex align-items-center ml-auto">
								<div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $piData->total_pi_expired }}">0</span></div>
								<div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{ $pi_expired }}">0.00</span>%</div>
							</div>
						</div>
						<!-- end info-row -->
					</div>
					<!-- end card-body -->
				</div>
				<!-- end card -->
			</div>
			<!-- end col-6 -->
			<!-- begin col-6 -->
			<div class="col-sm-6">
				<!-- begin card -->
				<div class="card border-0 bg-pink text-white text-truncate mb-3">
					<!-- begin card-body -->
					<div class="card-body">
						<!-- begin title -->
						<div class="mb-3 text-grey">
							<b class="mb-3">PENGGUNA BARU</b>
							<span class="ml-2"><i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-title="Store Sessions" data-placement="top" data-content="Number of sessions on your online store. A session is a period of continuous activity from a visitor." data-original-title="" title=""></i></span>
						</div>
						<!-- end title -->
						<!-- begin store-session -->
						<div class="d-flex align-items-center mb-1">
							<h2 class="text-white mb-0"><span data-animation="number" data-value="{{$total_new_cust}}">0</span></h2>
							<div class="ml-auto">
								<div id="store-session-sparkline"></div>
							</div>
						</div>
						<!-- end store-session -->

						@forelse ($new_cust_data as $value)
						<!-- begin info-row -->
						<div class="d-flex mb-2">
							<div class="d-flex align-items-center">
								<i class="fa fa-circle text-teal f-s-8 mr-2"></i>
								{{$value['pop']}}
							</div>
							<div class="d-flex align-items-center ml-auto">

								<div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{$value['jumlah']}}">0</span></div>
							</div>
						</div>
						<!-- end info-row -->
						@empty
						<!-- begin info-row -->
						<div class="d-flex mb-2">
							<div class="d-flex align-items-center">
								<i class="fa fa-circle text-blue f-s-8 mr-2"></i>
								Belum ada pengguna baru
							</div>

						</div>
						<!-- end info-row -->
						@endforelse

					</div>
					<!-- end card-body -->
				</div>
				<!-- end card -->
			</div>
			<!-- end col-6 -->
		</div>
		<!-- end row -->
	</div>
	<!-- end col-6 -->
</div>
<!-- end row -->
<!-- begin row -->
<div class="row">
	<!-- begin col-8 -->
	<div class="col-xl col-lg">
		<!-- begin card -->
		<div class="card bg-pink border-0 text-white mb-3">

			<div class="card-body p-0">
				<div id="doku-chart" class="widget-chart-full-width nvd3-inverse-mode"></div>
			</div>
		</div>
		<!-- end card -->
	</div>
	<!-- end col-8 -->

</div>
<!-- end row -->

@endsection


@push('scripts')
<script src="/assets/plugins/d3/d3.min.js"></script>
<script src="/assets/plugins/nvd3/build/nv.d3.js"></script>
<script src="/assets/plugins/jvectormap-next/jquery-jvectormap.min.js"></script>
<script src="/assets/plugins/jvectormap-next/jquery-jvectormap-world-mill.js"></script>
<script src="/assets/plugins/apexcharts/dist/apexcharts.min.js"></script>
<script src="/assets/plugins/moment/moment.js"></script>
<script src="/assets/plugins/hightchart/highcharts.js"></script>
<script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script src="/assets/js/demo/dashboard-v3.js"></script>

<script>
	$(document).ready(function() {
		handleDateRangeFilter();
		Highcharts.chart('doku-chart', {
			chart: {
				type: 'line'
			},
			title: {
				text: 'Grafik Pembayaran VIA DOKU'
			},
			subtitle: {
				text: '<?php echo  \Carbon\Carbon::parse($curentDate["dateStart"])->isoFormat('dddd, D MMMM').' s/d '.\Carbon\Carbon::parse($curentDate["dateEnd"])->isoFormat('dddd, D MMMM Y') ?>'
			},

			xAxis: {
				categories: <?php echo  $data_chart_doku["label"] ?>
			},
			yAxis: {
				title: {
					text: 'Total Pendapatan'
				}
			},
			plotOptions: {
				line: {
					dataLabels: {
						enabled: true
					},
					enableMouseTracking: false
				}
			},
			series: [{
				name: 'Doku',
				data: <?php echo  $data_chart_doku["value"] ?>
			}, ]
		});
	});

	var handleDateRangeFilter = function() {
		$('#daterange-filter span').html(moment('{{$curentDate["dateStart"]}}').format('D MMMM YYYY') + ' - ' + moment('{{$curentDate["dateEnd"]}}').format('D MMMM YYYY'));
		//$('#daterange-prev-date').html(moment('{{$compareDate["dateStart"]}}').format('D MMMM') + ' - ' + moment('{{$compareDate["dateEnd"]}}').format('D MMMM YYYY'));

		$('#daterange-filter').daterangepicker({
			format: 'MM/DD/YYYY',
			startDate: moment().subtract(7, 'days'),
			endDate: moment(),
			minDate: '01/06/2020',
			maxDate: '31/12/2024',
			dateLimit: {
				days: 60
			},
			showDropdowns: true,
			showWeekNumbers: true,
			timePicker: false,
			timePickerIncrement: 1,
			timePicker12Hour: true,
			ranges: {
				'Today': [moment(), moment()],
				'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			},
			opens: 'right',
			drops: 'down',
			buttonClasses: ['btn', 'btn-sm'],
			applyClass: 'btn-primary',
			cancelClass: 'btn-default',
			separator: ' to ',
			locale: {
				applyLabel: 'Submit',
				cancelLabel: 'Cancel',
				fromLabel: 'From',
				toLabel: 'To',
				customRangeLabel: 'Custom',
				daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
				monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				firstDay: 1
			}
		}, function(start, end, label) {
			$('#daterange-filter span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
			$('#filter-date-start').val(start.format('YYYY-MM-D'));
			$('#filter-date-end').val(end.format('YYYY-MM-D'));
			$('#filter-form').submit();
		});
	};
</script>
@endpush