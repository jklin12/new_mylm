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
    })
</script>
@endpush