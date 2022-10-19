@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
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

        <div class="mb-1"></div>
        <br>
        <div class="table-responsive">
            <table id="table-cust" class="table table-striped table-bordered table-td-valign-middle">
                <thead>
                    <th>Ip OLT</th>
                    <th>OLT</th>
                    <th>PORT OLT</th>
                    <th>Type</th>
                    <th>SN</th>
                    <th>Password</th>
                    <th></th>
                </thead>
                <tbody>
                    @forelse($data as $key => $value)
                    <tr>
                        @foreach($value as $values)
                        <td>{{$values}}</td>
                        @endforeach
                        <td><a href="{{route('olt-register', ('olt='.$value[1].'&ip_olt='.$value[0].'&interface='.$value[2].'&sn='.$value[4].'&type='.$value[3])) }}" class="btn btn-pink btn-icon btn-circle"><i class="fa fa-search-plus"></i></a></td>
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
@endsection

@push('scripts')
<script src="/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script>
    $(function() {

    })
</script>
@endpush