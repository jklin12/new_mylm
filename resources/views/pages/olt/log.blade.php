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

        <div class="table-responsive">
            <table id="table-onu" class="table table-striped table-bordered table-td-valign-middle">
                <thead>
                    <th>No.</th>
                    <th>OLT</th>
                    <th>Ip OLT</th>
                    <th>Name</th>
                    <th>Onu</th>
                    <th>Created At</th>
                    <th>Command</th>


                </thead>
                <tbody>
                    @forelse($data as $key => $value)
                    <tr>
                        <td>{{ $loop->iteration  }}</td>
                        <td>{{ $value['olt']}}</td>
                        <td>{{ $value['ip_olt']}}</td>
                        <td>{{ $value['register_log_name']}}</td>
                        <td>{{ $value['register_onu']}}</td>
                        <td>{{ $value['created_at']}}</td>
                        <td class="text-center w-1"><a href="javascript:;" class="btn btn-pink btn-icon btn-circle btnCommand" data-toggle="modal" data-target="#commandModal" data-command="{{$value['register_log_command']}}"><i class="fa fa-search-plus"></i></a></td>
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

<div class="modal fade" id="commandModal" tabindex="-1" role="dialog" aria-labelledby="commandModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commandModalLabel">Log Command</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea name="" id="command-container" class="form-control" rows="20"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

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

            $('#table-onu').DataTable();


            $('.btnCommand').click(function() {
                var command = $(this).data('command');
                var element = '';

                $.each(command, function(index, value) {
                    element += value + '\r\n';
                })

                $('#commandModal #command-container').html(element);
            })
        });
    })
</script>
@endpush