@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet" />
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
       <!-- <div class="pull-right ">
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
        </div>-->
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
        <div class="table-responsive">
            @if(Auth::user()->level >= 8)
            <div class="dt-buttons btn-group flex-wrap mb-2">
                <button class="btn btn-secondary btn-pink" tabindex="0" aria-controls="table-cust" type="button" id="addBtn"><span>Add Porfoma</span>
                </button>
            </div>
            @endif
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
@if(Auth::user()->level >= 8)
<div class="modal fade" id="reaktivasiModal" tabindex="-1" role="dialog" aria-labelledby="reaktivasiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reaktivasiModalLabel">Porforma Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah anda yakin untuk menerbitkan porforma baru <b>{{ $cust_number }}? <br> Porfoma lama akan expired.</b>
                <form action="{{ route('customer-reaktivasi')}}" method="post" id="raktivasi_form" class="mt-2">
                    @csrf
                    <input type="hidden" name="type" value="new">
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
@endif
@endsection

@push('scripts')
<script src="/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
<script src="/assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script>
    $(function() {
        var table = $('#table-cust').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('porfoma-list',$cust_number) }}",
                data: function(d) {
                    d.status = $('#filter_cupkg_status').val()
                },
            },
            columns: <?php echo $table_column ?>
        });


        $('.toggle-vis').on('click', function(e) {
            e.preventDefault();

            var column = table.column($(this).attr('data-column'));

            column.visible(!column.visible());
        })

        $('#addBtn').click(function() {
            $('#reaktivasiModal').modal('toggle')
        })
        $('.datetimepicker_input').datepicker({
            format: 'yyyy-mm-dd',
        });

    })
</script>
@endpush