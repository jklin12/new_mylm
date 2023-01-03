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
@endpush

<div class="pull-right">
    <!--<a href="#" class="btn btn-pink " data-toggle="modal" data-target="#importModal">Import Excel</a>-->
</div>
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse">
    <div class="panel-body">
        @include('includes.component.erorr-message')
        @include('includes.component.success-message')
        <div class="table-responsive table-stripped">
            <table id="table-cust" class="table table-striped table-bordered table-td-valign-middle">
                <thead>
                    <tr>
                        <th width="1%"></th>
                        <th >Wi Number</th>
                        @foreach($arr_field as $key=>$value)
                        @if($key < 1)
                        @foreach($value[1] as $vf)
                        @if ($vf['visible'])
                        <th class="text-nowrap">{{ $vf['label'] }}</th>
                        @endif
                        @endforeach
                        @endif
                        @endforeach
                        <th></th>

                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- end panel -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Waiting List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="{{ route('waitinglist-store')}}" method="post" id="wi_form" class="mt-2" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="fileExcel" class="col-form-label">FIle :</label>
                        <div class="custom-file">

                            <input type="file" class="custom-file-input" name="file" id="fileExcel" required>
                            <label class="custom-file-label" for="fileExcel">Choose file...</label>
                            <div class="invalid-feedback">Example invalid file </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" form="wi_form" class="btn btn-pink">Import</button>
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
<script src="/assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script src="/assets/plugins/moment/moment.js"></script>
<script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script src="/vendor/datatables/buttons.server-side.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#table-cust').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Bfrtip',
            buttons: [{
                className: 'btn-pink',
                text: 'Add Waitinglist',
                action: function(e, dt, node, config) {
                    location.href = '<?php echo route('waitinglist-form') ?>';
                }
            }],
            ajax: {
                url: "{{ route('waitinglist-list') }}",
                data: function(d) {},
            },
            columns: <?php echo $table_column ?>
        });



        $('.datetimepicker_input').datepicker({
            format: 'yyyy-mm-dd',
        });


    })
</script>

@endpush