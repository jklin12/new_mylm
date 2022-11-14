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
        <!--<div class="row">
            @foreach($arr_field as $kf=>$vf)

            @if(!$vf['searchable'] && $vf['form_type'] =='select')
            <div class="col-md-4">
                <div class="form-group row m-b-15">
                    <label class="col-form-label col-md-3">{{ $vf['label']}}</label>
                    <div class="col-md-9">
                        <select class="form-control" id="filter_{{$kf}}" name="cupkg_status">
                            <option>--Status--</option>
                            @forelse($vf['keyvaldata'] as $kdata => $vdata)
                            <option value="{{$kdata}}">{{$vdata}}</option>
                            @empty
                            <option value="">Data tidak ditemukan</option>
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
            @endif
            @endforeach

        </div>-->
        <div class="mb-1"></div>
        <br>
        <div class="table-responsive">
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
</div>
<!-- end panel -->

<div class="modal" tabindex="-1" role="dialog" id="modal-cek-payment">
    <div class="modal-dialog modal-md" role="document">
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
                <a href="" class="btn btn-warning">Update Status</a>
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
<script>
    $(function() {
        var table = $('#table-cust').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('qris-list') }}",
                data: function(d) {
                    d.status = $('#filter_cupkg_status').val()
                },
            },
            columns: <?php echo $table_column ?>
        });
        $(document).on("click", ".btn-cek", function(event) {

            var id = $(this).data('id');
            var element = '';
            $.get("<?php echo route('qris-cek', '') ?>"+'/'+id, function(data, status) {
                element += '<tbody>';
                $.each(data, function(k, v) {
                    element += '<tr><td class="col-1">' + k + '</td><td>:</td><td>' + v + '</td></tr>';
                });
                //alert(element);
                element += '</tbody>';

                $('#modal-cek-payment #table-data').html(element);
                $('#modal-cek-payment #title').html('Detail Status Doku');
                $('#modal-cek-payment').modal('show');
            });
        })


        $('.toggle-vis').on('click', function(e) {
            e.preventDefault();

            var column = table.column($(this).attr('data-column'));

            column.visible(!column.visible());
        })
    })
</script>
@endpush