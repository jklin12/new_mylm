@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')


@if($inv_status == 0)
<div class="pull-right">

    <a href="#" data-id="{{$wiData['wi_number']}}" data-name="{{$wiData['wi_name']}}" class="btn btn-pink m-r-5 m-b-5" data-toggle="modal" data-target="#cetakInvModal">Konfirmasi</a>

</div>
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
        @if($key < 3)
        <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-wi{{$key}}" aria-expanded="true">
            <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i>{{$value[0]}}
        </div>

        <div id="collapse-wi{{$key}}" class="collapse show" data-parent="#accordion">
            <div class="table-responsive">
                <table class="table table-striped m-b-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th width="" class="col-1"></th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($value[1] as $kf => $vf)
                        @if($vf['visible'])
                        <tr>
                            <td class="">
                                {{ $vf['label'] }}
                            </td>
                            <td class="text-center">:</td>
                            <td>

                                @if($vf['form_type']=='select')
                                {!! ($vf['keyvaldata'][$wiData[$kf]]) ?? $wiData[$kf] !!}
                                @else
                                {!! $wiData[$kf] !!}
                                @endif

                            </td>
                            <td class="with-btn">

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
                            <td>{{ $v->wi_file_title }}</td>
                            <td><img src="{{url($v->wi_file_name)}}" alt="Image" width="100" /></td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @if($datas)
    <div class="card ">
        <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-0" aria-expanded="true">
            <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> Data Invoice
        </div>
        <div id="collapse-0" class="collapse show" data-parent="#accordion">
            <div class="table-responsive">
                <table class="table table-striped m-b-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="col-1" width=""></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="col-1">
                                Link Pembayaran
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                <a href="{{ $url}}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">klik disini</a>
                            </td>
                            <td class="with-btn">
                            </td>
                        </tr>
                        <tr>
                            <td class="col-1">
                                Nomor Invoice
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_number']}}
                            </td>
                            <td class="with-btn">
                            </td>
                        </tr>
                        <tr>
                            <td class="col-1">
                                Layanan
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['sp_code']}}
                            </td>
                            <td class="with-btn">
                            </td>
                        </tr>
                        <tr>
                            <td class="col-1">
                                Status
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_status']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Awal Periode
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_start']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Akhir Periode
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_end']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Jatuh Tempo
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_start']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Posted
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_post']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Dibayar
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_paid']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Info
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_info']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Update
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_updated']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        <tr>
                            <td class="col-1">
                                Update By
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas['inv_updated_by']}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>

                    </tbody>
                </table>
            </div>
            <hr>
            <h1 class="page-header"><small>&nbsp;Invoice Item</small></h1>
            <div class="table-responsive">
                <table class="table table-striped m-b-0">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>info</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($data_summary as $k => $v)
                        <tr>
                            <td>{{ $v['ii_order'] }}</td>
                            <td>{{ $v['ii_type'] }}</td>
                            <td>{{ $v['ii_amount'] }}</td>
                            <td>{{ $v['ii_info'] }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td>{{ $datas['totals']}}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @else
    <div class="alert alert-warning fade show m-b-10">
        <span class="close" data-dismiss="alert">×</span>
        <b>Maaf </b>, Data tidak ditemukan
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