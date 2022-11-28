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


<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
@include('includes.component.erorr-message')
@include('includes.component.success-message')

@if($datas)
<ul class="nav nav-tabs">
    @foreach($datas as $key => $value)
    <li class="nav-item">
        <a href="#tab-{{$key}}" data-toggle="tab" class="nav-link @php echo $key == '0' ? 'active' : '' @endphp">
            <span class="d-sm-none">{{ $value->sp_code }}</span>
            <span class="d-sm-block d-none">{{ $value->sp_code }}</span>
        </a>
    </li>
    @endforeach
</ul>
<div class="tab-content">
    <!-- begin tab-pane -->
    @foreach($datas as $keys => $values)

    <div class="tab-pane fade @php echo $keys == '0' ? 'active show' : '' @endphp" id="tab-{{ $keys }}">
        <div id="accordion" class="accordion">
            @foreach($arr_field as $key => $value)
            <div class="card ">
                <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-{{$key}}" aria-expanded="true">
                    <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> {{ $values->sp_code. $values->cupkg_status}}

                </div>
                <div id="collapse-{{$key}}" class="collapse show" data-parent="#accordion">
                    <div class="table-responsive">
                        <div class="pull-right mt-2 mr-2">
                            @if($values->cupkg_status == '8' || $values->cupkg_status == '5')
                            <a href="#" class="btn btn-pink " data-toggle="modal" data-target="#reaktivasiModal">Reaktivasi</a>
                            @endif
                        </div>
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
                                @foreach($value['data'] as $keyx => $valuex)
                                <tr>
                                    <td class="col-2">
                                        {{ $valuex['label'] }}
                                    </td>
                                    <td class="text-center">:</td>
                                    <td>
                                        @if($keyx == 'cupkg_status')
                                        <span class="badge badge-{{ $valuex['keyvaldata'][$values->$keyx][1]}}">{{ $valuex['keyvaldata'][$values->$keyx][0]}}</span>
                                        @elseif($valuex['form_type'] == 'select')
                                        {{ $valuex['keyvaldata'][$values->$keyx]}}
                                        @elseif($valuex['form_type'] == 'date')
                                        {{\Carbon\Carbon::parse($values->keyx)->isoFormat('D MMMM Y');}}
                                        @else
                                        {{ $values->$keyx}}
                                        @endif
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

    </div>
    @endforeach
    <!-- end tab-pane -->
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
<div class="modal fade" id="reaktivasiModal" tabindex="-1" role="dialog" aria-labelledby="reaktivasiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reaktivasiModalLabel">Raktivasi Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah anda yakin untuk Reaktivasi pelanggan <b>{{ $cust_number }}</b>
                <form action="{{ route('customer-reaktivasi')}}" method="post" id="raktivasi_form">
                    @csrf
                    <input type="hidden" name="cust_number" value="{{ $cust_number }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" form="raktivasi_form" class="btn btn-pink">Ya</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#btn_pppoe_check').click(function() {
        var element = '';
        var title = $(this).data('title');
        $.get("<?php echo route('cek-status-pppoe', 'cust_number=' . $cust_number) ?>", function(data, status) {
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
        var title = $(this).data('title');
        $.get("<?php echo route('cek-status-olt', 'cust_number=' . $cust_number) ?>", function(data, status) {
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