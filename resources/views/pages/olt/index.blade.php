@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
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
        <form action="" method="get">
            <div class="row">
                <div class="col-md">
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">OLT</label>
                        <div class="col-lg-8">
                            <select class="multiple-select2 form-control" multiple="multiple" name="olt">
                                @foreach($data_olt as $olt)
                                <option value="{{$olt['ip']}}">{{$olt['name']}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Cust Number</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control m-b-5" placeholder="" name="cust_number">

                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <button type="submit" class="btn btn-pink m-r-5 m-b-5">Cari</button>
                </div>
            </div>

        </form>
        <div class="mb-1"></div>
        <br>
        <div class="table-responsive">
            <table id="table-onu" class="table table-striped table-bordered table-td-valign-middle">
                <thead>
                    <th>No.</th>
                    <th>OLT</th>
                    <th>Ip OLT</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>SN</th>
                    <th>GPON ONU</th>
                    <th>Status</th>
                    <th>Last Online</th>
                    <th>Last Offline</th>
                    <th>Power</th>
                    <th>Last Update </th>
                    <th></th>

                </thead>
                <tbody>
                    @forelse($data as $key => $value)
                    <tr>
                        @foreach($value as $values)
                        <td>{{$values}}</td>
                        @endforeach
                        <td>
                            <div class="btn-group m-r-5 m-b-5 show">
                                <a href="javascript:;" class="btn btn-default">Telnet</a>
                                <a href="#" data-toggle="dropdown" class="btn btn-default dropdown-toggle" aria-expanded="true"><b class="caret"></b></a>
                                <div class="dropdown-menu dropdown-menu-right " x-placement="bottom-end">
                                    <a href="javascript:;" class="dropdown-item btn-detail" data-olt="{{ $value['onu_olt']}}" data-interface="{{ $value['onu_gpon']}}">Detail Info</a>
                                    <a href="javascript:;" class="dropdown-item btn-config" data-olt="{{ $value['onu_olt']}}" data-interface="{{ $value['onu_gpon']}}">Run Interface </a>
                                    <a href="javascript:;" class="dropdown-item btn-power" data-olt="{{ $value['onu_olt']}}" data-interface="{{ $value['onu_gpon']}}">Power Attenuation </a>
                                </div>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <div class="col-md-4">
                        <div class="alert alert-warning fade show m-b-10">
                            <span class="close" data-dismiss="alert">×</span>
                            Maaf! Data tidak ditemua.
                        </div>
                    </div>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- end panel -->
<div class="modal" tabindex="-1" role="dialog" id="modal-response">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title">Remote OLT</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="" id="response-container"></div>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="/assets/plugins/select2/dist/js/select2.min.js"></script>
<script>
    $(function() {
        $(document).ready(function() {
            $(".multiple-select2").select2({
                placeholder: "Select OLT"
            });

            $('#table-onu').DataTable();

            $('.btn-config').click(function() {
                var olt = $(this).data('olt')
                var interface = $(this).data('interface')

                $.post("<?php echo route('olt-api') ?>", {
                        _token: "{{ csrf_token() }}",
                        olt: olt,
                        interface: interface,
                        url: "cekConfig"
                    },
                    function(data, status) {

                        $('#modal-response #response-container').html(data);
                        $('#modal-response #title').html("Show Config");
                        $('#modal-response').modal('show');
                    });
            })

            $('.btn-detail').click(function() {
                var olt = $(this).data('olt')
                var interface = $(this).data('interface')

                $.post("<?php echo route('olt-api') ?>", {
                        _token: "{{ csrf_token() }}",
                        olt: olt,
                        interface: interface,
                        url: "detailInfo"
                    },
                    function(data, status) {

                        $('#modal-response #response-container').html(data);
                        $('#modal-response #title').html("Detail Info");
                        $('#modal-response').modal('show');
                    });
            })
            $('.btn-power').click(function() {
                var olt = $(this).data('olt')
                var interface = $(this).data('interface')

                $.post("<?php echo route('olt-api') ?>", {
                        _token: "{{ csrf_token() }}",
                        olt: olt,
                        interface: interface,
                        url: "attenuation"
                    },
                    function(data, status) {

                        $('#modal-response #response-container').html(data);
                        $('#modal-response #title').html("Detail Info");
                        $('#modal-response').modal('show');
                    });
            })
        });
    })
</script>
@endpush