@extends('layouts.default')

@section('title', 'Blank Page')

@push('css')
<link rel="stylesheet" href="/assets/plugins/select2/dist/css/select2.min.css">
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet" />
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
<div class="pull-right">
    <a href="{{ route('report-olt','reload=1')}}" class="btn btn-pink m-r-5 m-b-5"><i class="fa fa-refresh" aria-hidden="true"></i> Perbarui </a>
    
</div>
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->


<!-- begin panel -->
<div class="row">

    @forelse($data as $key=> $value)
    <!-- begin col-3 -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 bg-pink text-white text-truncate mb-3">
            <!-- begin card-body -->
            <div class="card-body">
                <!-- begin title -->
                <div class="mb-3 text-grey">
                    <b class="mb-3">OLT {{ $value->olt_name }}, IP {{ $value->olt_ip }}</b>
                    <br><b> Suhu : {{ $value->temp }}&#8451, Voltasi : {{ $value->volatge }}, Fan Status : {{ $value->fan_status == 1 ? 'ON' : 'OFF'}}</b> 
                    

                </div>
                <!-- end title -->
                <!-- begin conversion-rate -->
                <div class="d-flex align-items-center mb-1">
                    <h2 class="text-white mb-0"><span data-animation="number" data-value="{{$value->total}}">0</span></h2>
                    <div class="ml-auto">
                        <div id="conversion-rate-sparkline"></div>
                    </div>
                </div>
                <!-- end conversion-rate -->
                <!-- begin info-row -->
                <b>Last Update : {{ \Carbon\Carbon::parse($value->last_update)->isoFormat('D MMMM Y, HH:mm');}}</b>
                <div class="d-flex mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-green f-s-8 mr-2"></i>
                        Working
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $value->jumlah_wroking }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{ precentage($value->jumlah_wroking,$value->total) }}">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->
                <!-- begin info-row -->
                <div class="d-flex mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-grey f-s-8 mr-2"></i>
                        Dyinggasp
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"> <span data-animation="number" data-value="{{ $value->jumlah_dyinggasp }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{  precentage($value->jumlah_dyinggasp,$value->total) }}">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->
                <!-- begin info-row -->
                <div class="d-flex">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-warning f-s-8 mr-2"></i>
                        Offline
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $value->jumlah_offline }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{  precentage($value->jumlah_offline,$value->total) }}">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->
                <!-- begin info-row -->
                <div class="d-flex">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle text-red f-s-8 mr-2"></i>
                        Los
                    </div>
                    <div class="d-flex align-items-center ml-auto">
                        <div class="text-grey f-s-11"><span data-animation="number" data-value="{{ $value->jumlah_los }}">0</span></div>
                        <div class="width-50 text-right pl-2 f-w-600"><span data-animation="number" data-value="{{  precentage($value->jumlah_los,$value->total) }}">0.00</span>%</div>
                    </div>
                </div>
                <!-- end info-row -->


            </div>
            <!-- end card-body -->
        </div>
    </div>
    <!-- end col-3 -->
    @empty
    <div class="col-md-4">
        <div class="alert alert-warning fade show m-b-10">
            <span class="close" data-dismiss="alert">×</span>
            Maaf! Data tidak ditemua.
        </div>
    </div>
    @endforelse


</div>

@endsection

@push('scripts')

<script>


</script>
@endpush