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


@if($inv_status == 0)
<div class="pull-right">
    <a href="javascript:;" id="btn_qris" class="btn btn-pink m-r-5 m-b-5">Qris</a>
</div>
@endif
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
        @if($key == 0)
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
                            <td class="col-1">
                                {{ $values['label'] }}
                            </td>
                            <td class="text-center">:</td>
                            <td>
                                {!! $datas[$keys] !!}
                            </td>
                            <td class="with-btn">

                            </td>


                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div id="collapse-{{$key}}" class="collapse show" data-parent="#accordion">
            <div class="table-responsive">
                <table class="table table-striped m-b-0">
                    <thead>
                        <tr>
                            @foreach($value['data'] as $keys => $values)
                            <th>{{ $values['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data_summary as $k => $v)
                        <tr>
                            @foreach($value['data'] as $keys => $values)
                            <td>{{ $v[$keys] }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td>{{ $datas['totals']}}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif
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
<div class="modal" tabindex="-1" role="dialog" id="modal-qris">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="/assets/img/svg/QRIS_logo.svg.png" alt="" srcset="" class="img-fluid w-50 ">
                <div id="qr-container" class="text-center"></div>
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
    $('#btn_qris').click(function() {
      
        $.get("<?php echo route('qris-generate', $datas['inv_number']) ?>", function(data, status) {
            console.log(data['data']['qr']);
            if (data['status']) {
                $('#qr-container').html(data['data']['qr'])
            }
        })

        $('#modal-qris').modal('show')

    });
</script>
@endpush