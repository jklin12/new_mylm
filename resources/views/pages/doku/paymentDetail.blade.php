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
<!-- begin panel -->
@include('includes.component.erorr-message')
@include('includes.component.success-message')
<div id="accordion" class="accordion">
    <!-- begin card -->
    @forelse($detail_data as $key => $value)
    <div class="card ">
        <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-{{$key}}" aria-expanded="true">
            <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> {{ $value['title']}}
        </div>
        <div id="collapse-{{$key}}" class="collapse show" data-parent="#accordion" style="">
            <div class="table-responsive">
                <table class="table table-striped m-b-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th width="" class="col-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($value['data'] as $keys => $values)
                        <tr>
                            <td class="col-1">
                                {{ $values[0] }}
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                @if($keys == 'inv_status' || $keys == 'result_msg' || $keys == 'cust_status')
                                <span class="label label-{{$values[1][1]}}">{{$values[1][0]}}</span>

                                @else
                                {{ $values[1] }}
                                @endif
                            </td>
                            <td class="with-btn">
                                @if( $keys == 'result_msg' || $keys =='cust_status' )
                                <button type="button" id="btn_{{$keys}}" data-id="{{$inv_number}}" class="btn btn-pink m-r-5 m-b-5">Cek {{$values[0]}}</button>
                                @endif
                            </td>


                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    @empty
    <div class="alert alert-warning fade show m-b-10">
        <span class="close" data-dismiss="alert">×</span>
        <b>Maaf </b>, Data tidak ditemukan
    </div>
    @endforelse


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
                <a href="{{ route('update-request','inv='.$inv_number) }}" class="btn btn-warning">Update Status</a>
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="modal-cek-pppoe">
    <div class="modal-dialog modal-lg" role="document">
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
                
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btn_result_msg').click(function() {
            var dataId = $(this).data('id');
            //alert(dataId);
            var element = '';
            $.get("<?php echo route('cek-request', 'inv=' . $inv_number) ?>", function(data, status) {
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
        $('#btn_cust_status').click(function() {
            var element = '';
            $.get("<?php echo route('cek-status-pppoe', 'cust_number=' . $cust_number) ?>", function(data, status) {
                element += '<tbody>';
                $.each(data, function(k, v) {
                    element += '<tr><td class="col-2">' + k + '</td><td>:</td><td>' + v + '</td></tr>';
                });
                //alert(element);
                element += '</tbody>';

                $('#modal-cek-pppoe #table-data').html(element);
                $('#modal-cek-pppoe #title').html('Detail Status PPPOE');
                $('#modal-cek-pppoe').modal('show');
            });

        })
    })
</script>
@endpush