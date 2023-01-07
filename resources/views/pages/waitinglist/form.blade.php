@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')

@push('css')
<link rel="stylesheet" href="/assets/plugins/select2/dist/css/select2.min.css">
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet" />
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" />
@endpush


<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
@include('includes.component.erorr-message')
@include('includes.component.success-message')


<div class="panel panel-inverse">
    <div class="panel-body">
        @include('includes.component.erorr-message')
        @include('includes.component.success-message')
        <form action="{{ route('waitinglist-store')}}" method="POST" enctype="multipart/form-data">
            @if($action == 'addData')
            <input type="hidden" name="action" value="{{ $action}}">
            @csrf
            @endif

            @foreach($arr_field as $keyfield => $vfield)
            <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center mb-2">
                <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> {{$vfield[0]}}
            </div>
            @foreach($vfield[1] as $kf => $vf)
            <div class="form-group row m-b-15">
                <label class="col-form-label col-md-3">{{ $vf['label'] }}</label>
                <div class="col-md-9">
                    @if($vf['form_type'] == 'text')
                    <input type="text" name="data[{{ $kf }}]" id="input_{{ $kf }}" class="form-control m-b-5" placeholder="Masukan {{ $vf['label'] }}">
                    @elseif($vf['form_type'] == 'date')
                    <input type="text" name="data[{{ $kf }}]" id="input_{{ $kf }}" class="form-control m-b-5 datetimepicker_input" placeholder="Masukan {{ $vf['label'] }}" />
                    @elseif($vf['form_type'] == 'area')
                    <textarea class="form-control" name="data[{{ $kf }}]" id="input_{{ $kf }}" rows="3"></textarea>
                    @elseif($vf['form_type'] == 'select')
                    <select class="default-select2 form-control m-b-5" name="data[{{ $kf }}]" id="input_{{ $kf }}">
                        <option value="">Pilih {{ $vf['label'] }}</option>
                        @foreach($vf['keyvaldata'] as $kvdata => $vdata)
                        <option value="{{ $kvdata }}">{{$vdata}}</option>
                        @endforeach
                    </select>

                    @elseif($vf['form_type'] == 'select_bsn')
                    <select class="default-select2 form-control m-b-5" name="data[{{ $kf }}]" id="input_{{ $kf }}">
                        <option value="">Pilih {{ $vf['label'] }}</option>
                        @foreach($vf['keyvaldata'] as $kvdata => $vdata)
                        <optgroup label="{{$vdata['parent']}}">
                            @if(isset($vdata['child']))
                            @foreach($vdata['child'] as $kchild => $vchild) 
                            <option value="{{ $kchild }}">{{$vchild}}</option>
                            @endforeach
                            @endif
                        </optgroup>

                        @endforeach
                    </select>
                    
                    @elseif($vf['form_type'] == 'file')
                    <input type="file" name="{{ $kf }}" id="input_{{ $kf }}" class="form-control m-b-5" placeholder="Masukan {{ $vf['label'] }}">
                    @endif
                </div>
            </div>
            @endforeach
            @endforeach
            <div class="pull-right">
                <button type="submit" class="btn btn-pink">Simpan</button>
            </div>
        </form>
    </div>
</div>


@endsection

@push('scripts')
<script src="/assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script src="/assets/plugins/moment/moment.js"></script>
<script src="/assets/plugins/select2/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $(".default-select2").select2();
        $('#input_wi_address').keyup(function() {
            var value = $(this).val();
            $('#input_wi_bill_address').val(value);
        });

        $('#input_wi_zip_code').keyup(function() {
            var value = $(this).val();
            $('#input_wi_bill_zip_code').val(value);
        });
        $('#input_wi_phone').keyup(function() {
            var value = $(this).val();
            $('#input_wi_bill_phone').val(value);
        });
        $('#input_wi_telp').keyup(function() {
            var value = $(this).val();
            $('#input_wi_bill_telp').val(value);
        });
        $('#input_wi_email').keyup(function() {
            var value = $(this).val();
            $('#input_wi_bill_email').val(value);
        });
        $('#input_wi_prov').change(function() {
            var value = $(this).val();
            //$('#input_wi_bill_prov').val(value).change();
            $("#input_wil_bill_prov").val(value).trigger('change');
        });
        $('#input_wi_city').change(function() {
            var value = $(this).val();
            //$('#input_wi_bill_city').val(value).change();
            $("#input_wi_bill_city").val(value).trigger('change');
        });
        $('.datetimepicker_input').datepicker({
            format: 'yyyy-mm-dd',
            orientation: 'bottom'
        });
    })
</script>
@endpush