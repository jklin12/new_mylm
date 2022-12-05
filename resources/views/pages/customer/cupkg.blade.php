@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet" />
<link href="/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
@endpush


<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
@include('includes.component.erorr-message')
@include('includes.component.success-message')

@if($datas)
<ul class="nav nav-tabs">
    @foreach($datas as $key => $value)
    <li class="nav-item">
        <a href="#tab-{{$key}}" data-toggle="tab" class="nav-link @php echo $key == '0' ? 'active' : '' @endphp">
            <span class="d-sm-none">{{ $value->sp_code }}</span>
            <span class="d-sm-block d-none">{{ $value->sp_code }}</span>
        </a>
    </li>
    @endforeach
</ul>
<div class="tab-content">
    <!-- begin tab-pane -->
    @foreach($datas as $keys => $values)

    <div class="tab-pane fade @php echo $keys == '0' ? 'active show' : '' @endphp" id="tab-{{ $keys }}">
        <div id="accordion" class="accordion">
            @foreach($arr_field as $key => $value)

            <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-{{$key}}" aria-expanded="true">
                <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> {{ $values->sp_code}}

            </div>
            <div id="collapse-{{$key}}" class="collapse show" data-parent="#accordion">
                <div class="table-responsive">
                    <div class="pull-right mt-2 mr-2">
                        <a href="#" class="btn btn-pink mr-2" data-toggle="modal" data-target="#perubahanLayananModal">Perubahan Layanan</a>
                        @if($values->cupkg_status == '8' || $values->cupkg_status == '5')
                        <a href="#" class="btn btn-pink " data-toggle="modal" data-target="#reaktivasiModal">Reaktivasi</a>
                        @endif

                    </div>
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
                            @foreach($value['data'] as $keyx => $valuex)
                            <tr>
                                <td class="col-2">
                                    {{ $valuex['label'] }}
                                </td>
                                <td class="text-center">:</td>
                                <td>
                                    @if($keyx == 'cupkg_status')
                                    <span class="badge badge-{{ $valuex['keyvaldata'][$values->$keyx][1]}}">{{ $valuex['keyvaldata'][$values->$keyx][0]}}</span>
                                    @elseif($valuex['form_type'] == 'select')
                                    {{ $valuex['keyvaldata'][$values->$keyx]}}
                                    @elseif($valuex['form_type'] == 'date')
                                    {{\Carbon\Carbon::parse($values->keyx)->isoFormat('D MMMM Y');}}
                                    @else
                                    {{ $values->$keyx}}
                                    @endif
                                </td>
                                <td class="with-btn">

                                </td>


                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            @endforeach

            <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-custborrow" aria-expanded="true">
                <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i>Barang Yang Dipinjamkann

            </div>
            <div id="collapse-custborrow" class="collapse show" data-parent="#accordion">
                <div class="table-responsive">
                    <div class="pull-right mt-2 mr-2">

                    </div>
                    <table class="table table-striped m-b-0">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Nilai</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th width="" class="col-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cust_borrow as $key => $value)
                            <tr>
                                <td class="col-2">
                                    {{$value->cb_stuff}}
                                </td>
                                <td class="">{{ ($value->cb_value)}}</td>
                                <td class=""> {{\Carbon\Carbon::parse($value->cb_begin)->isoFormat('D MMMM Y');}}</td>
                                <td class=""> {{ $value->cb_end != '0000-00-00'  ? \Carbon\Carbon::parse($value->cb_end)->isoFormat('D MMMM Y') : '';}}</td>
                                <td class="with-btn">
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-ketidakaktifan" aria-expanded="true">
                <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i>Informasi Ketidakaktifan

            </div>
            <div id="collapse-ketidakaktifan" class="collapse show" data-parent="#accordion">
                <div class="table-responsive">
                    <div class="pull-right mt-2 mr-2">

                    </div>
                    <table class="table table-striped m-b-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Layanan</th>
                                <th>Alasan</th>
                                <th>Info</th>
                                <th width="" class="col-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ke_tidak_aktif as $key => $value)
                            <tr>
                                <td class="col-2">
                                    {{\Carbon\Carbon::parse($value->cuin_date)->isoFormat('D MMMM Y');}}
                                </td>
                                <td class="">{{ $value->sp_code}}</td>
                                <td class="">{{ $value->cuin_reason == 1 ? 'Menunggak' : 'Permintaan Senidiri'}}</td>
                                <td class="">{{ $value->cuin_info}}</td>
                                <td class="with-btn">
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-keaktifan" aria-expanded="true">
                <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i>Informasi Aktif Kembali

            </div>
            <div id="collapse-keaktifan" class="collapse show" data-parent="#accordion">
                <div class="table-responsive">
                    <div class="pull-right mt-2 mr-2">

                    </div>
                    <table class="table table-striped m-b-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Layanan</th>
                                <th>Info</th>
                                <th width="" class="col-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ke_aktif as $key => $value)
                            <tr>
                                <td class="col-2">
                                    {{\Carbon\Carbon::parse($value->cuin_date)->isoFormat('D MMMM Y');}}
                                </td>
                                <td class="">{{ $value->sp_code}}</td>
                                <td class="">{{ $value->cuin_info}}</td>

                                <td class="with-btn">

                                </td>


                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    @endforeach
    <!-- end tab-pane -->

</div>

@else
<div class="alert alert-warning fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <b>Maaf </b>, Data tidak ditemukan
</div>
@endif
<!-- end panel -->
<div class="modal" tabindex="-1" role="dialog" id="modal-cek-pppoe">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped" id="table-data">

                </table>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reaktivasiModal" tabindex="-1" role="dialog" aria-labelledby="reaktivasiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reaktivasiModalLabel">Raktivasi Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah anda yakin untuk Reaktivasi pelanggan <b>{{ $cust_number }}</b>
                <form action="{{ route('customer-reaktivasi')}}" method="post" id="raktivasi_form" class="mt-2">
                    @csrf
                    <input type="hidden" name="cust_number" value="{{ $cust_number }}">
                    <div class="form-group row">
                        <label class="col-lg-2 col-form-label">Periode</label>
                        <div class="col-lg-8">
                            <div class="row row-space-10">
                                <div class="col-xs-6 mb-2 mb-sm-0">
                                    <input type="text" class="form-control datetimepicker_input" name="inv_start" placeholder="Mulai Layanan" />
                                </div>
                                <!--<div class="col-xs-6">
                                    <input type="text" class="form-control datetimepicker_input"  name="inv_end" placeholder="Akhir Layanan" />
                                </div>-->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" form="raktivasi_form" class="btn btn-pink">Ya</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="perubahanLayananModal" tabindex="-1" role="dialog" aria-labelledby="perubahanLayananModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="perubahanLayananModalLabel">Perubahan Layanan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="{{ route('customer-upgrade')}}" method="post" id="perubahanlayanan_form" class="mt-2">
                    @csrf
                    <input type="hidden" name="cust_number" value="{{ $cust_number }}">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Jenis Perubahan</label>
                        <div class="col-md-9">
                            <select class="form-control" name="jenis">
                                <option>--Pilih Jenis--</option>
                                <option value="2">Upgrade</option>
                                <option value="3">Downgrade</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row m-b-15">
                        <label class="col-lg-3 col-md-3 col-form-label">Layanan</label>
                        <div class="col-lg-9">
                            <select class="default-select2 form-control" name="sp_code">
                                <option>--Pilih Layanan--</option>
                                @foreach($list_layanan as $key => $value)
                                <option value="{{$value->sp_code}}">{{$value->sp_name}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">Periode</label>
                        <div class="col-lg-9">
                            <div class="row row-space-10">
                                <div class="col-xs-6 mb-2 mb-sm-0">
                                    <input type="text" class="form-control datetimepicker_input" name="inv_start" placeholder="Mulai Layanan" />
                                </div>
                                <!--<div class="col-xs-6">
                                    <input type="text" class="form-control datetimepicker_input"  name="inv_end" placeholder="Akhir Layanan" />
                                </div>-->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" form="perubahanlayanan_form" class="btn btn-pink">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="/assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script src="/assets/plugins/select2/dist/js/select2.min.js"></script>
<script>
    $(".default-select2").select2();
    $('#btn_pppoe_check').click(function() {
        var element = '';
        var title = $(this).data('title');
        $.get("<?php echo route('cek-status-pppoe', 'cust_number=' . $cust_number) ?>", function(data, status) {
            element += '<tbody>';
            $.each(data, function(k, v) {
                element += '<tr><td class="col-2">' + k + '</td><td>:</td><td>' + v + '</td></tr>';
            });
            //alert(element);
            element += '</tbody>';

            $('#modal-cek-pppoe #table-data').html(element);
            $('#modal-cek-pppoe #title').html(title);
            $('#modal-cek-pppoe').modal('show');
        });

    })
    $('.datetimepicker_input').datepicker({
        format: 'yyyy-mm-dd',
    });
    

    $('#btn_olt_check').click(function() {
        var element = '';
        var title = $(this).data('title');
        $.get("<?php echo route('cek-status-olt', 'cust_number=' . $cust_number) ?>", function(data, status) {
            element += '<tbody>';
            $.each(data, function(k, v) {
                element += '<tr><td class="col-2">' + k + '</td><td>:</td><td>' + v + '</td></tr>';
            });
            //alert(element);
            element += '</tbody>';

            $('#modal-cek-pppoe #table-data').html(element);
            $('#modal-cek-pppoe #title').html(title);
            $('#modal-cek-pppoe').modal('show');

        });



    })
</script>
@endpush