@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
@endpush
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<div class="d-sm-flex align-items-center mb-3">
    <a href="{{ route('bukti_tf.create') }}" class="btn btn-pink mr-2 text-truncate" id="add-btn">
        <i class="fa fa-plus fa-fw text-white-transparent-5 ml-n1"></i>
        Tambah Data
    </a>

</div>
@include('includes.component.erorr-message')
@include('includes.component.success-message')

<!-- begin panel -->
<div class="panel panel-inverse">

    <div class="panel-body">
        <div class="pull-right ">
            <div class="dropdown dropleft">
                <a class=" dropdown-toggle" href="javascript:;" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Tampil Kolom
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    @php $i = 1 @endphp
                    @foreach($arr_field as $kf=>$vf)
                    <a class="dropdown-item toggle-vis" data-column="{{ $i }}" id="view_{{ $kf }}" href="#"><input type="checkbox" name="checkbox">&nbsp; {{ $vf['label'] }}</a>
                    @php $i++ @endphp
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mb-1"></div>
        <br>
        <table id="table-cust" class="table table-striped table-bordered table-td-valign-middle">
            <thead>
                <tr>
                    <th width="1%"></th>
                    @foreach($arr_field as $vf)
                    <th class="text-nowrap">{{ $vf['label'] }}</th>
                    @endforeach
                    <th></th>

                </tr>
            </thead>
        </table>
    </div>
</div>
<!-- end panel -->

<div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <!-- Modal heading -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalDeleteLabel">
                    Hapus Data ?
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <!-- Modal body with image -->
            <div class="modal-body">
                <form action="" method="POST" id="delete-form">
                @csrf
                @method('DELETE')
                </form>
                Apakah yakin menghapus bukti transfer <b id="delete-title"></b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                <button type="submit" form="delete-form" class="btn btn-danger">Ya</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImage" tabindex="-1" role="dialog" aria-labelledby="modalImageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Modal heading -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalImageLabel">
                    Bukti transfer
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <!-- Modal body with image -->
            <div class="modal-body">
                <img class="img-fluid" src="gfg.png" id="imagex" />
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
<script>
    $(function() {
        $('#modalDelete').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var title = button.data('title')
            var route = button.data('route')

            $("#delete-title").html(title);
            $("#delete-form").attr("action", route);
        })
        $('#modalImage').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var img = button.data('img')

            $("#imagex").attr("src", img);
        })
        var table = $('#table-cust').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('bukti-tf-list') }}",
            columns: <?php echo $table_column ?>
        });

        $('.toggle-vis').on('click', function(e) {
            e.preventDefault();

            var column = table.column($(this).attr('data-column'));

            column.visible(!column.visible());
        })
    })
</script>
@endpush