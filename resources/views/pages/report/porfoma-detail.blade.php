@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet" />
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet" />
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" />
<style>
    .pagination>li>a,
    .pagination>li>span {
        color: #b64260;
    }

    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        background-color: green;
        border-color: green;
    }

    .page-item.active .page-link {
        z-index: 1;
        color: #fff;
        background-color: #b64260;
        border-color: #b64260;
    }
</style>
@endpush

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse">
    <div class="panel-body">
        <form action="" method="get" id="search-filter">
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
                                <option value="9">Ekslusif</option>
                                <option value="10">CSR</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Status PI</label>
                        <div class="col-md-9">
                            <select name="inv_status" id="filter_inv_status" class="form-control">
                                <option value="">Select Status PI</option>
                                <option value="0">Blum Bayar</option>
                                <option value="1">Lunas</option>
                                <option value="2">Expired</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">WA Terkirim</label>
                        <div class="col-md-9">
                            <select name="inv_status" id="tidak_terkirim" class="form-control">
                                <option value=""></option>
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Bulan</label>
                        <div class="col-md-9">
                            <div class="input-group date" id="month-filter">
                                <input type="text" class="form-control" name="bulan">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Mulai Layanan</label>
                        <div class="col-md-9">
                            <div class="input-group" id="daterange-filter">
                                <input type="text" name="daterange-filter" class="form-control" value="" placeholder="click to select the date range">
                                <span class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3 text-right">
                <button type="submit" class="btn btn-pink"><i class="fa fa-search"></i> Cari</button>
            </div>
        </form>
        <div class="table-responsive table-striped">
            {!! $dataTable->table() !!}
        </div>
    </div>
</div>
<!-- end panel -->
@endsection

@push('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
<script src="/assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script src="/assets/plugins/moment/moment.js"></script>
<script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script src="/vendor/datatables/buttons.server-side.js"></script>
{!! $dataTable->scripts() !!}
<script>
    $(document).ready(function() {


        var reqTable = $('#porfomareportdatatable-table').DataTable();

        $('#month-filter').datepicker({
            format: "mm",
            startView: "months",
            minViewMode: "months"
        });

        $('#search-filter').on('submit', function(e) {
            reqTable.draw();
            e.preventDefault();
        });

        $('#daterange-filter').daterangepicker({
            format: 'MM/DD/YYYY',
            startDate: moment().startOf('month'),
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
            $('#daterange-filter input').val(start.format('YYYY-MM-DD') + ' s/d ' + end.format('YYYY-MM-DD'));
            //$('#filter-form').submit();
        });

        $('#daterange-filter input').val('<?php echo $date ?>');
        $('#month-filter input').val('<?php echo $bulan ?>');
        $('#filter_inv_status').val('<?php echo $pi_status ?>');
        $('#search-filter').submit();
    });
</script>

@endpush