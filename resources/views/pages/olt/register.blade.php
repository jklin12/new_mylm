@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
@endpush

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse">
    <div class="panel-body">
        <h5> OLT {{ $olt.'-'.$ip_olt }}</h5>
        <div class="row">
            <div class="col">
                <div class="form-group row m-b-15">
                    <label class="col-form-label col-md-3">SN ONT</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$sn}}">
                    </div>
                </div>
                <div class="form-group row m-b-15">
                    <label class="col-form-label col-md-3">Type</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$type}}">
                    </div>
                </div>
                <div class="form-group row m-b-15">
                    <label class="col-form-label col-md-3">Onu Suggestion</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$onu_index}}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3">Profile</label>
                    <div class="col-md-9">
                        <select class="default-select2 form-control">
                            <option value="">-- Pilih Profile --</option>
                            @forelse($profile as $key => $value)
                            <option value="{{$value[1]}}">{{$value[1]}}</option>
                            @empty
                            <option value="">Data tidak ditemukan</option>
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
            <div class="col">
                <h5># show run int {{ $interface }}</h5>
                @forelse($onu_data as $key => $value)
                <p class="ml-3">
                    @foreach($value as $values)
                    {{ $values }}
                    @endforeach
                </p>
                @empty
                <p>Data tidak ditemukan</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
<!-- end panel -->
@endsection

@push('scripts')
<script src="/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="/assets/plugins/select2/dist/js/select2.min.js"></script>
<script>
    $(function() {
        $(".default-select2").select2();
    })
</script>
@endpush