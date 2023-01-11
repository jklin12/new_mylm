@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')


@if($inv_status == 0)

@endif
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
@include('includes.component.erorr-message')
@include('includes.component.success-message')


@if ($errors->any())
<div class="alert alert-danger fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div id="accordion" class="accordion">
    @if($wiData)
    <div class="card ">
        @foreach($arr_field as $key=>$value)
        @if($key < 3) <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-wi{{$key}}" aria-expanded="true">
            <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i>{{$value[0]}}
    </div>

    <div id="collapse-wi{{$key}}" class="collapse show" data-parent="#accordion">
        <div class="table-responsive">
            <table class="table table-striped mx-2 my-2">

                <tbody>

                    @foreach($value[1] as $kf => $vf)
                    @if($vf['visible'])
                    <tr>
                        <td class="w-25">
                            <strong> {{ $vf['label'] }}</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td>

                            @if($vf['form_type']=='select' || $vf['form_type']=='select2')
                            {!! ($vf['keyvaldata'][$wiData[$kf]]) ?? $wiData[$kf] !!}
                            @elseif($vf['form_type']=='select_bsn')
                            {!! ($vf['keyvaldata2'][$wiData[$kf]]) ?? $wiData[$kf] !!}
                            @elseif($vf['form_type']=='date')
                            {!! Carbon\Carbon::parse($wiData[$kf])->isoFormat('D MMMM Y') !!}
                            @else
                            {!! $wiData[$kf] !!}
                            @endif

                        </td>
                        
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif

@if($datas)
<div class="card ">
    <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-0" aria-expanded="true">
        <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> Data Invoice
    </div>
    <div id="collapse-0" class="collapse show" data-parent="#accordion">
        @foreach($data_summary as $key=>$value)
        <div class="table-responsive px-2 py-2">
            <div class="pull-right">
                <a href="http://webhook.lifemedia.id/checkout?code={{ $value['code']}}" data-target="_blank" class="btn btn-sm btn-pink m-r-5 m-b-5">Kirim Invoice</a>
            </div>
            <h1 class="page-header"><small>&nbsp;Invoice {{ $value['inv_number']}}</small></h1>
            <table class="table table-striped m-b-0">
                <tbody>
                    <tr>
                        <td class="col-1">
                            <strong>Nomor Invoice</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['inv_number']}}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Layanan</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['sp_code']}}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Status</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            <h5><span class="badge badge-'{{ $value['inv_status'][1]}}">{{ $value['inv_status'][0]}}</span></h5>

                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Awal Periode</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['inv_start']}}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Akhir Periode</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['inv_end']}}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Jatuh Tempo</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['inv_start']}}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Posted</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['inv_post']}}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Dibayar</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['inv_paid']}}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-1">
                            <strong>Info</strong>
                        </td>
                        <td class="text-center">:</td>
                        <td colspan="2">
                            {{ $value['inv_info']}}
                        </td>
                    </tr>
                    <tr>
                        <th>Nomor</th>
                        <!--<th>Tipe</th>-->
                        <th>Jumlah</th>
                        <th>info</th>
                    </tr>
                    @foreach($value['item'] as $k => $v)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <!--td>{{ $v['ii_type'] }}</td>-->
                        <td>{{ $v['ii_amount'] }}</td>
                        <td>{{ $v['ii_info'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
        @endforeach
    </div>
</div>

@else
<div class="alert alert-warning fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <b>Maaf </b>, Data tidak ditemukan
</div>
@endif

@if($data_file)
<div class="card ">
    <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-file" aria-expanded="true">
        <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> Berkas Pelanggan
    </div>
    <div id="collapse-file" class="collapse show" data-parent="#accordion">
        <div class="table-responsive">
            <table class="table  m-b-0">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($data_file as $k => $v)
                    <tr>
                        <td><strong>{{ $v->wi_file_title }}</strong></td>
                        <td><img src="{{url($v->wi_file_name)}}" alt="Image" width="100" /></td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

</div>
<!-- end panel -->

<div class="modal fade" id="cetakInvModal" tabindex="-1" role="dialog" aria-labelledby="cetakInvModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cetakInvModalLabel">Terbitkan Porfoma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah anda yakin untuk menerbitkan porfoma pelanggan <b id="porfoma_nama"></b>?
                <form action="{{ route('waitinglist-konfirmasi')}}" method="post" id="konfirmasi_form" class="mt-2">
                    @csrf
                    <input type="hidden" name="wi_number" id="input_wi_id" value="">
                    <!--<div class="form-group row">
                        <label class="col-lg-2 col-form-label">Periode</label>
                        <div class="col-lg-10">
                            <div class="row row-space-10">
                                <div class="col-xs-6 mb-2 mb-sm-0">
                                    <input type="text" class="form-control datetimepicker_input" name="inv_start" placeholder="Mulai Layanan" />
                                </div>
                                <div class="col-xs-6">
                                    <input type="text" class="form-control datetimepicker_input"  name="inv_end" placeholder="Akhir Layanan" />
                                </div>-
                            </div>
                        </div>
                    </div>-->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" form="konfirmasi_form" class="btn btn-pink">Ya</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $('#cetakInvModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var id = button.data('id');
        var name = button.data('name');

        $('#porfoma_nama').html(name);
        $('#input_wi_id').val(id);

    })
</script>
@endpush