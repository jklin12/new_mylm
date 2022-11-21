@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" />

@endpush

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse">
    <div class="panel-body">
        <form action="" method="get" id="search-filter">
            <h5>Filter Pencarian</h5>
            <div class="row ">
                <div class="col-md-3">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Status Pelanggan</label>
                        <div class="col-md-9">
                            <select name="cupkg_status" id="filter_cupkg_status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="1">Registrasi</option>
                                <option value="2">Instalasi</option>
                                <option value="3">Setup</option>
                                <option value="4">Sistem Aktif</option>
                                <option value="5">Tidak Aktif</option>
                                <option value="6">Trial</option>
                                <option value="7">Sewa Khusus</option>
                                <option value="8">Blokir</option>
                                <option value="9">Eksklusif</option>
                                <option value="10">CSR</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">POP</label>
                        <div class="col-md-9">
                            <select name="cust_pop" id="filter_cust_pop" class="form-control">
                                <option value="">Select POP</option>
                                @foreach($arr_pop as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Kecamatan</label>
                        <div class="col-md-9">
                            <select name="cust_kecamatan" id="filter_kecamatan" class="form-control">
                                <option value="">Select Kecamatan</option>
                                @foreach($kecamatan as $key => $value)
                                <option value="{{$value->area_name}}">{{$value->area_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Kelurahan</label>
                        <div class="col-md-9">
                            <select name="cust_kelurahan" id="filter_kelurahan" class="form-control">
                                <option value="">Select Kelurahan</option>
                                @foreach($Kelurahan as $key => $value)
                                <option value="{{$value->area_name}}">{{$value->area_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


            </div>
            <h5>Tampil Kolom</h5>
            <div class="row">
                @foreach($arr_field as $kfield =>$vfield)
                <div class="col-md-2">
                    <div class="form-check">
                        <input class="form-check-input toggle-vis" type="checkbox" value="" id="{{ $kfield }}_check" name="column[{{ $kfield }}]" data-column="{{$loop->iteration}}" @php echo $vfield['visible'] ? 'checked' : '' @endphp>
                        <label class="form-check-label" for="{{ $kfield }}_check">
                            {{ $vfield['label'] }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mb-3 text-right">
                <button type="submit" class="btn btn-pink"><i class="fa fa-search"></i> Cari</button>
            </div>
        </form>

        <div class="mb-1"></div>
        <br>
        <div class="table-responsive table-striped">
            {!! $dataTable->table() !!}
        </div>
    </div>
</div>
<!-- end panel -->
<div class="modal fade" id="cuin_modal" tabindex="-1" role="dialog" aria-labelledby="cuin_modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cuin_modalLabel">Informasi Ketidakaktifan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">

                    <tbody>
                        <tr>
                            <td>Tanggal</td>
                            <td class="">:</td>
                            <td class="cuin_date">@mdo</td>
                        </tr>
                        <tr>
                            <td>Alasan</td>
                            <td>:</td>
                            <td class="cuin_reason">Jacob</td>
                        </tr>
                        <tr>
                            <td>Info</td>
                            <td>:</td>
                            <td class="cuin_info">@twitter</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
<script src="/vendor/datatables/buttons.server-side.js"></script>

{!! $dataTable->scripts() !!}
<script>
    $(function() {
        var reqTable = $('#customer-table').DataTable();
        $('#search-filter').on('submit', function(e) {
            reqTable.draw();
            e.preventDefault();
        })
        $('#filter_cupkg_status').val('<?php echo $cupkg_status ?>');
        $('#filter_cust_pop').val('<?php echo $cust_pop ?>');
        $('#search-filter').submit();

        $('#customer-table').on('click', '.status_btn', function() {
            $('#cuin_modal').modal('toggle')
            var date = $(this).data('cuindate')
            var cuinreason = $(this).data('cuinreason')
            var cuininfo = $(this).data('cuininfo')

            $('#cuin_modal .modal-body .cuin_date').html(date)
            $('#cuin_modal .modal-body .cuin_info').html(cuininfo)
            $('#cuin_modal .modal-body .cuin_reason').html(cuinreason)

        })

        $('.toggle-vis').on('change', function(e) {
            e.preventDefault();
            //alert($(this).attr('data-column'))

            // Get the column API object
            var column = reqTable.column($(this).attr('data-column'));

            // Toggle the visibility
            column.visible(!column.visible());
        });
    })
</script>
@endpush