@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
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

<div class="pull-right">
    <a href="javascript:;" id="btn_pppoe_check" data-title="Status PPPOE" class="btn btn-pink m-r-5 m-b-5">Check PPPOE</a>
    <a href="javascript:;" id="btn_olt_check" data-title="Status OLT" class="btn btn-pink m-r-5 m-b-5">Check OLT</a>
    <div class="btn-group dropdown m-r-5 m-b-5">
        <a href="#" data-toggle="dropdown" class="btn btn-pink dropdown-toggle" aria-expanded="false">Follow Up Chat&nbsp;<b class="caret"></b></a>

        <ul class="dropdown-menu dropdown-menu-right scrollable-menu" role="menu">
            @forelse($message_template as $key => $value)
            <li><a href="{{ route('customer-message-form',[$value['message_id'],$datas['cust_number']])}}" class="dropdown-item">{{ $value['name'] }}</a></li>
            @empty
            <li><a href="javascript:;" class="dropdown-item">Tidak ada Template</a></li>
            @endforelse
        </ul>
    </div>

</div>
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
@include('includes.component.erorr-message')
@include('includes.component.success-message')

@if($datas)
<div id="accordion" class="accordion">
    @foreach($arr_field as $key => $value)
    <div class="card ">
        <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-{{$key}}" aria-expanded="true">
            <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> {{ $value['title']}}
        </div>
        <div id="collapse-{{$key}}" class="collapse show" data-parent="#accordion">
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
                            <td class="col-2">
                                {{ $values['label'] }}
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $datas[$keys]}}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>
    </div>
    @endforeach
</div>
@else
<div class="alert alert-warning fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <b>Maaf </b>, Data tidak ditemukan
</div>
@endif
<!-- end panel -->
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
    $('#btn_pppoe_check').click(function() {
        var element = '';
        var title =  $(this).data('title');
        $.get("<?php echo route('cek-status-pppoe', 'cust_number=' . $datas['cust_number']) ?>", function(data, status) {
            element += '<tbody>';
            $.each(data, function(k, v) {
                element += '<tr><td class="col-2">' + k + '</td><td>:</td><td>' + v + '</td></tr>';
            });
            //alert(element);
            element += '</tbody>';

            $('#modal-cek-pppoe #table-data').html(element);
            $('#modal-cek-pppoe #title').html(title);
            $('#modal-cek-pppoe').modal('show');
        });

    })

    $('#btn_olt_check').click(function() {
        var element = '';
        var title =  $(this).data('title');
        $.get("<?php echo route('cek-status-olt', 'cust_number=' . $datas['cust_number']) ?>", function(data, status) {
            element += '<tbody>';
            $.each(data, function(k, v) {
                element += '<tr><td class="col-2">' + k + '</td><td>:</td><td>' + v + '</td></tr>';
            });
            //alert(element);
            element += '</tbody>';

            $('#modal-cek-pppoe #table-data').html(element);
            $('#modal-cek-pppoe #title').html(title);
            $('#modal-cek-pppoe').modal('show');
        });

    })
</script>
@endpush